<?php

namespace MunicipalSaas\Shared\Presentation\Pdf;

use Dompdf\Dompdf;
use Dompdf\Options;

final readonly class OfficialDocumentRenderer
{
    /**
     * @param array<string, mixed> $data
     */
    public function captureLine(array $data): string
    {
        return $this->render($this->layout(
            title: 'Linea de captura',
            municipality: (string) $data['municipality'],
            primaryColor: (string) $data['primary_color'],
            secondaryColor: (string) $data['secondary_color'],
            body: $this->captureLineBody($data),
        ));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function receipt(array $data): string
    {
        return $this->render($this->layout(
            title: 'Recibo oficial',
            municipality: (string) $data['municipality'],
            primaryColor: (string) $data['primary_color'],
            secondaryColor: (string) $data['secondary_color'],
            body: $this->receiptBody($data),
        ));
    }

    private function render(string $html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter');
        $dompdf->render();

        return $dompdf->output();
    }

    private function layout(string $title, string $municipality, string $primaryColor, string $secondaryColor, string $body): string
    {
        return <<<HTML
<!doctype html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: Helvetica, Arial, sans-serif; color: #172033; }
    .sheet { padding: 28px; }
    .header { border-bottom: 5px solid {$primaryColor}; padding-bottom: 18px; margin-bottom: 24px; }
    .brand { display: table; width: 100%; }
    .seal { display: table-cell; width: 76px; vertical-align: middle; }
    .seal div { width: 62px; height: 62px; border-radius: 16px; background: {$primaryColor}; color: #fff; text-align: center; line-height: 62px; font-weight: bold; font-size: 20px; }
    .brand-copy { display: table-cell; vertical-align: middle; }
    .brand-copy h1 { margin: 0; color: {$primaryColor}; font-size: 25px; }
    .brand-copy p { margin: 4px 0 0; color: #64748b; font-size: 13px; }
    .badge { display: inline-block; border-radius: 999px; padding: 7px 12px; background: {$secondaryColor}; color: #fff; font-size: 11px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
    .panel { border: 1px solid #d8dee8; border-radius: 18px; padding: 20px; margin-bottom: 18px; }
    .grid { display: table; width: 100%; }
    .col { display: table-cell; vertical-align: top; }
    .col + .col { padding-left: 18px; }
    dl { display: table; width: 100%; margin: 0; }
    .row { display: table-row; }
    dt, dd { display: table-cell; padding: 8px 0; border-bottom: 1px solid #edf1f7; }
    dt { width: 34%; color: #64748b; font-weight: bold; }
    dd { margin: 0; font-weight: bold; }
    .amount { color: {$primaryColor}; font-size: 34px; font-weight: bold; }
    .qr { width: 132px; height: 132px; border: 1px solid #d8dee8; }
    .barcode { margin-top: 12px; height: 56px; white-space: nowrap; overflow: hidden; }
    .bar { display: inline-block; height: 56px; margin-right: 2px; background: #111827; }
    .footer { color: #64748b; font-size: 11px; line-height: 1.5; margin-top: 20px; border-top: 1px solid #d8dee8; padding-top: 14px; }
  </style>
</head>
<body>
  <main class="sheet">
    <section class="header">
      <div class="brand">
        <div class="seal"><div>MX</div></div>
        <div class="brand-copy">
          <span class="badge">Documento oficial</span>
          <h1>{$title}</h1>
          <p>{$municipality}</p>
        </div>
      </div>
    </section>
    {$body}
    <section class="footer">
      Este documento fue generado por la plataforma de pagos municipales. Verifica folio, importe y vigencia antes de realizar el pago.
    </section>
  </main>
</body>
</html>
HTML;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function captureLineBody(array $data): string
    {
        $amount = $this->money((float) $data['amount']);
        $qr = $this->qrImage((string) $data['payment_link']);
        $barcode = $this->barcode((string) $data['folio']);

        return <<<HTML
<section class="panel">
  <div class="grid">
    <div class="col">
      <dl>
        <div class="row"><dt>Folio</dt><dd>{$data['folio']}</dd></div>
        <div class="row"><dt>Servicio</dt><dd>{$data['service_type']}</dd></div>
        <div class="row"><dt>Contribuyente</dt><dd>{$data['citizen_name']}</dd></div>
        <div class="row"><dt>Referencia</dt><dd>{$data['citizen_reference']}</dd></div>
        <div class="row"><dt>Vigencia</dt><dd>{$data['expiration_date']}</dd></div>
      </dl>
      <p class="amount">{$amount}</p>
    </div>
    <div class="col" style="width:150px;text-align:center;">
      <img class="qr" src="{$qr}" alt="QR de pago">
      <p style="font-size:11px;color:#64748b;">Escanea para pagar</p>
    </div>
  </div>
  {$barcode}
  <p style="font-size:12px;color:#64748b;">Link de pago: {$data['payment_link']}</p>
</section>
HTML;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function receiptBody(array $data): string
    {
        $amount = $this->money((float) $data['amount']);
        $qr = $this->qrImage((string) $data['verification_url']);
        $barcode = $this->barcode((string) $data['folio']);

        return <<<HTML
<section class="panel">
  <div class="grid">
    <div class="col">
      <dl>
        <div class="row"><dt>Recibo</dt><dd>{$data['folio']}</dd></div>
        <div class="row"><dt>Referencia de pago</dt><dd>{$data['payment_reference']}</dd></div>
        <div class="row"><dt>Concepto</dt><dd>{$data['concept']}</dd></div>
        <div class="row"><dt>Fecha de emision</dt><dd>{$data['issued_at']}</dd></div>
      </dl>
      <p class="amount">{$amount}</p>
    </div>
    <div class="col" style="width:150px;text-align:center;">
      <img class="qr" src="{$qr}" alt="QR de verificacion">
      <p style="font-size:11px;color:#64748b;">Verificacion</p>
    </div>
  </div>
  {$barcode}
</section>
HTML;
    }

    private function qrImage(string $value): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . rawurlencode($value);
    }

    private function barcode(string $value): string
    {
        $bars = '';
        foreach (str_split($value) as $index => $char) {
            $width = (ord($char) + $index) % 3 + 1;
            $bars .= '<span class="bar" style="width:' . $width . 'px"></span>';
        }

        return '<div class="barcode">' . $bars . '</div><p style="font-size:11px;letter-spacing:2px;">' . $value . '</p>';
    }

    private function money(float $amount): string
    {
        return '$' . number_format($amount, 2, '.', ',');
    }
}
