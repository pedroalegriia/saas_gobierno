<?php

namespace MunicipalSaas\CaptureLines\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use MunicipalSaas\CaptureLines\Application\DTO\CreateCaptureLineData;
use MunicipalSaas\CaptureLines\Application\UseCases\CreateCaptureLine;
use MunicipalSaas\CaptureLines\Presentation\Http\Requests\CreateCaptureLineRequest;
use MunicipalSaas\CaptureLines\Presentation\Http\Resources\CaptureLineResource;
use MunicipalSaas\Shared\Domain\Enums\ServiceType;
use MunicipalSaas\Shared\Presentation\Pdf\OfficialDocumentRenderer;

final readonly class CaptureLineController
{
    public function __construct(
        private CreateCaptureLine $createCaptureLine,
        private OfficialDocumentRenderer $documents,
    ) {
    }

    public function store(CreateCaptureLineRequest $request): CaptureLineResource|JsonResponse
    {
        try {
            $captureLine = $this->createCaptureLine->execute(new CreateCaptureLineData(
                municipalityId: (int) $request->attributes->get('tenant_id'),
                municipalitySlug: (string) $request->attributes->get('tenant_slug'),
                serviceType: ServiceType::from((string) $request->validated('service_type')),
                serviceId: (int) $request->validated('service_id'),
            ));
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], 422);
        }

        return new CaptureLineResource($captureLine);
    }

    public function document(string $folio, Request $request): Response|JsonResponse
    {
        $captureLine = DB::table('capture_lines')
            ->where('municipality_id', (int) $request->attributes->get('tenant_id'))
            ->where('folio', $folio)
            ->first();

        if (! $captureLine) {
            return new JsonResponse(['message' => 'Linea de captura no encontrada.'], 404);
        }

        $municipality = $request->attributes->get('tenant');
        $paymentLink = rtrim((string) config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:4200')), '/') . '/pagar/' . $captureLine->folio;
        $this->auditDocument($request, $captureLine->folio, 'capture_line.document_viewed');

        return new Response($this->htmlDocument(
            municipalityName: $municipality->name,
            folio: $captureLine->folio,
            serviceType: $captureLine->service_type,
            amount: (string) $captureLine->amount,
            expirationDate: (string) $captureLine->expiration_date,
            paymentLink: $paymentLink,
        ), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function pdf(string $folio, Request $request): Response|JsonResponse
    {
        $captureLine = DB::table('capture_lines')
            ->where('municipality_id', (int) $request->attributes->get('tenant_id'))
            ->where('folio', $folio)
            ->first();

        if (! $captureLine) {
            return new JsonResponse(['message' => 'Linea de captura no encontrada.'], 404);
        }

        $municipality = $request->attributes->get('tenant');
        $service = $this->serviceData((int) $request->attributes->get('tenant_id'), $captureLine->service_type, (int) $captureLine->service_id);
        $paymentLink = rtrim((string) config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:4200')), '/') . '/pagar/' . $captureLine->folio;

        $pdf = $this->documents->captureLine([
            'municipality' => $municipality->name,
            'primary_color' => $municipality->primaryColor,
            'secondary_color' => $municipality->secondaryColor,
            'folio' => $captureLine->folio,
            'service_type' => $captureLine->service_type,
            'amount' => (string) $captureLine->amount,
            'expiration_date' => (string) $captureLine->expiration_date,
            'payment_link' => $paymentLink,
            'citizen_name' => $service['name'],
            'citizen_reference' => $service['reference'],
        ]);
        $this->auditDocument($request, $captureLine->folio, 'capture_line.pdf_reprinted');

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="linea-' . $captureLine->folio . '.pdf"',
        ]);
    }

    private function htmlDocument(
        string $municipalityName,
        string $folio,
        string $serviceType,
        string $amount,
        string $expirationDate,
        string $paymentLink,
    ): string {
        $formattedAmount = '$' . number_format((float) $amount, 2, '.', ',');

        return <<<HTML
<!doctype html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <title>Linea de captura {$folio}</title>
  <style>
    body { font-family: Arial, sans-serif; color: #172033; margin: 32px; }
    .sheet { border: 1px solid #d8dee8; border-radius: 18px; padding: 28px; max-width: 760px; margin: auto; }
    .header { border-bottom: 4px solid #0f4c81; padding-bottom: 18px; margin-bottom: 24px; }
    h1 { margin: 0; color: #0f4c81; }
    dl { display: grid; grid-template-columns: 180px 1fr; gap: 12px; }
    dt { font-weight: bold; color: #64748b; }
    dd { margin: 0; font-weight: bold; }
    .amount { font-size: 34px; color: #0f4c81; }
    .footer { margin-top: 28px; padding: 18px; background: #f1f5f9; border-radius: 12px; }
    @media print { body { margin: 0; } .sheet { border: none; } }
  </style>
</head>
<body>
  <main class="sheet">
    <section class="header">
      <p>{$municipalityName}</p>
      <h1>Linea de captura</h1>
    </section>
    <dl>
      <dt>Folio</dt><dd>{$folio}</dd>
      <dt>Servicio</dt><dd>{$serviceType}</dd>
      <dt>Importe</dt><dd class="amount">{$formattedAmount}</dd>
      <dt>Vigencia</dt><dd>{$expirationDate}</dd>
      <dt>Link de pago</dt><dd>{$paymentLink}</dd>
    </dl>
    <section class="footer">
      Presenta este documento o comparte el link de pago con el contribuyente.
    </section>
  </main>
</body>
</html>
HTML;
    }

    /**
     * @return array{name: string, reference: string}
     */
    private function serviceData(int $municipalityId, string $serviceType, int $serviceId): array
    {
        $row = match ($serviceType) {
            'PREDIAL' => DB::table('predial_accounts')
                ->selectRaw('owner_name as name, property_key as reference')
                ->where('municipality_id', $municipalityId)
                ->where('id', $serviceId)
                ->first(),
            'WATER' => DB::table('water_accounts')
                ->selectRaw('customer_name as name, contract_number as reference')
                ->where('municipality_id', $municipalityId)
                ->where('id', $serviceId)
                ->first(),
            'TRAFFIC_FINE' => DB::table('traffic_fines')
                ->selectRaw('COALESCE(offender_name, "Contribuyente") as name, folio as reference')
                ->where('municipality_id', $municipalityId)
                ->where('id', $serviceId)
                ->first(),
            default => null,
        };

        return [
            'name' => $row->name ?? 'Contribuyente',
            'reference' => $row->reference ?? 'N/A',
        ];
    }

    private function auditDocument(Request $request, string $folio, string $action): void
    {
        DB::table('audit_logs')->insert([
            'municipality_id' => $request->attributes->get('tenant_id'),
            'user_id' => $request->user()?->id,
            'action' => $action,
            'entity' => 'capture_lines',
            'entity_id' => $folio,
            'old_value' => null,
            'new_value' => json_encode([
                'folio' => $folio,
                'user_agent' => $request->userAgent(),
            ]),
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);
    }
}
