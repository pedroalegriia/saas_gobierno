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

final readonly class CaptureLineController
{
    public function __construct(
        private CreateCaptureLine $createCaptureLine,
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

        return new Response($this->htmlDocument(
            municipalityName: $municipality->name,
            folio: $captureLine->folio,
            serviceType: $captureLine->service_type,
            amount: (string) $captureLine->amount,
            expirationDate: (string) $captureLine->expiration_date,
            paymentLink: $paymentLink,
        ), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
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
}
