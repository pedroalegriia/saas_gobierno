<?php

namespace MunicipalSaas\CaptureLines\Presentation\Http\Controllers;

use App\Policies\TreasuryPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use MunicipalSaas\CaptureLines\Application\DTO\CreateCaptureLineData;
use MunicipalSaas\CaptureLines\Application\UseCases\CreateCaptureLine;
use MunicipalSaas\CaptureLines\Presentation\Http\Requests\CreateTreasuryCaptureLineRequest;
use MunicipalSaas\CaptureLines\Presentation\Http\Resources\CaptureLineResource;
use MunicipalSaas\Shared\Domain\Enums\ServiceType;

final readonly class TreasuryCaptureLineController
{
    public function __construct(
        private CreateCaptureLine $createCaptureLine,
        private TreasuryPolicy $treasuryPolicy,
    ) {
    }

    public function store(CreateTreasuryCaptureLineRequest $request): JsonResponse
    {
        if (! $request->user() || ! $this->treasuryPolicy->generateCaptureLine($request->user(), $request->attributes->get('tenant'))) {
            return new JsonResponse(['message' => 'No autorizado para generar lineas de captura.'], 403);
        }

        $serviceType = ServiceType::from((string) $request->validated('service_type'));
        $lookup = trim((string) $request->validated('lookup'));
        $municipalityId = (int) $request->attributes->get('tenant_id');
        $service = $this->resolveService($municipalityId, $serviceType, $lookup);

        if ($service === null) {
            return new JsonResponse(['message' => 'No se encontro un adeudo vigente con el dato proporcionado.'], 404);
        }

        try {
            $captureLine = $this->createCaptureLine->execute(new CreateCaptureLineData(
                municipalityId: $municipalityId,
                municipalitySlug: (string) $request->attributes->get('tenant_slug'),
                serviceType: $serviceType,
                serviceId: (int) $service['id'],
            ));
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], 422);
        }

        $oxxoReference = null;
        if ((bool) ($request->validated('include_oxxo_reference') ?? true)) {
            $oxxoReference = $this->ensureOpenPayOxxoReference($municipalityId, $captureLine->id, $captureLine->folio, $captureLine->amount);
        }

        DB::table('audit_logs')->insert([
            'municipality_id' => $municipalityId,
            'user_id' => $request->user()?->id,
            'action' => 'capture_line.generated',
            'entity' => 'capture_lines',
            'entity_id' => $captureLine->folio,
            'old_value' => null,
            'new_value' => json_encode([
                'service_type' => $serviceType->value,
                'lookup' => $lookup,
                'openpay_reference' => $oxxoReference['reference'] ?? null,
            ]),
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);

        return new JsonResponse([
            'capture_line' => (new CaptureLineResource($captureLine))->resolve($request),
            'citizen' => $service,
            'delivery' => [
                'payment_link' => rtrim((string) config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:4200')), '/') . '/pagar/' . $captureLine->folio,
                'document_url' => rtrim((string) config('app.url'), '/') . '/api/v1/capture-lines/' . $captureLine->folio . '/document',
                'expires_at' => $captureLine->expirationDate,
            ],
            'openpay' => $oxxoReference,
        ], 201);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveService(int $municipalityId, ServiceType $serviceType, string $lookup): ?array
    {
        return match ($serviceType) {
            ServiceType::Predial => $this->predialService($municipalityId, $lookup),
            ServiceType::Water => $this->waterService($municipalityId, $lookup),
            ServiceType::TrafficFine => $this->trafficFineService($municipalityId, $lookup),
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function predialService(int $municipalityId, string $propertyKey): ?array
    {
        $row = DB::table('predial_accounts')
            ->where('municipality_id', $municipalityId)
            ->where('property_key', $propertyKey)
            ->first();

        return $row ? [
            'id' => (int) $row->id,
            'reference' => $row->property_key,
            'name' => $row->owner_name,
            'address' => $row->address,
            'service_type' => ServiceType::Predial->value,
        ] : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function waterService(int $municipalityId, string $contractNumber): ?array
    {
        $row = DB::table('water_accounts')
            ->where('municipality_id', $municipalityId)
            ->where('contract_number', $contractNumber)
            ->first();

        return $row ? [
            'id' => (int) $row->id,
            'reference' => $row->contract_number,
            'name' => $row->customer_name,
            'address' => $row->address,
            'service_type' => ServiceType::Water->value,
        ] : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function trafficFineService(int $municipalityId, string $folioOrPlate): ?array
    {
        $row = DB::table('traffic_fines')
            ->where('municipality_id', $municipalityId)
            ->where('status', 'PENDING')
            ->where(fn ($query) => $query
                ->where('folio', $folioOrPlate)
                ->orWhere('plate', strtoupper($folioOrPlate)))
            ->orderByDesc('violation_date')
            ->first();

        return $row ? [
            'id' => (int) $row->id,
            'reference' => $row->folio,
            'name' => $row->offender_name ?? 'Contribuyente',
            'address' => 'Placa ' . $row->plate,
            'service_type' => ServiceType::TrafficFine->value,
        ] : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function ensureOpenPayOxxoReference(int $municipalityId, int $captureLineId, string $folio, string $amount): array
    {
        $reference = 'OP-OXXO-' . $folio;
        $paynetReference = trim(chunk_split((string) abs(crc32($folio . '|openpay|oxxo')), 4, ' '));
        $now = now();

        DB::table('payments')->updateOrInsert(
            ['reference' => $reference],
            [
                'municipality_id' => $municipalityId,
                'capture_line_id' => $captureLineId,
                'gateway' => 'openpay',
                'method' => 'oxxo_cash',
                'amount' => $amount,
                'status' => 'PENDING',
                'paid_at' => null,
                'metadata' => json_encode([
                    'provider' => 'openpay',
                    'method' => 'oxxo_cash',
                    'paynet_reference' => $paynetReference,
                    'store' => 'OXXO',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );

        return [
            'gateway' => 'openpay',
            'method' => 'oxxo_cash',
            'reference' => $reference,
            'paynet_reference' => $paynetReference,
            'store' => 'OXXO',
            'amount' => $amount,
            'expires_at' => Carbon::today()->addDays((int) config('domain.capture_line_expiration_days', 15))->toDateString(),
            'instructions' => 'Presenta esta referencia en caja OXXO e indica que realizaras un pago OpenPay/Paynet.',
        ];
    }
}
