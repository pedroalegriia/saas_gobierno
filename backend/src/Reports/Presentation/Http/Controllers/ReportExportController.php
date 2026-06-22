<?php

namespace MunicipalSaas\Reports\Presentation\Http\Controllers;

use App\Policies\TreasuryPolicy;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

final readonly class ReportExportController
{
    public function __construct(
        private TreasuryPolicy $treasuryPolicy,
    ) {
    }

    public function paymentsCsv(Request $request): Response
    {
        if (! $request->user() || ! $this->treasuryPolicy->exportReports($request->user(), $request->attributes->get('tenant'))) {
            return new Response('No autorizado.', 403);
        }

        $rows = $this->paymentsQuery($request)
            ->orderByDesc('p.created_at')
            ->get();

        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['referencia', 'linea_captura', 'servicio', 'gateway', 'metodo', 'estatus', 'importe', 'pagado_en']);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->reference,
                $row->capture_line_folio,
                $row->service_type,
                $row->gateway,
                $row->method,
                $row->status,
                $row->amount,
                $row->paid_at,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="pagos.csv"',
        ]);
    }

    public function paymentsPdf(Request $request): Response
    {
        if (! $request->user() || ! $this->treasuryPolicy->exportReports($request->user(), $request->attributes->get('tenant'))) {
            return new Response('No autorizado.', 403);
        }

        $rows = $this->paymentsQuery($request)->orderByDesc('p.created_at')->get();
        $total = '$' . number_format((float) $rows->sum('amount'), 2, '.', ',');
        $municipality = $request->attributes->get('tenant');
        $tableRows = $rows->map(fn (object $row): string => sprintf(
            '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>$%s</td></tr>',
            e($row->reference),
            e($row->service_type),
            e($row->gateway),
            e($row->status),
            number_format((float) $row->amount, 2, '.', ',')
        ))->implode('');

        $html = <<<HTML
<!doctype html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: Helvetica, Arial, sans-serif; color: #172033; margin: 28px; }
    h1 { color: #0f4c81; margin-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 24px; font-size: 12px; }
    th, td { border-bottom: 1px solid #d8dee8; padding: 8px; text-align: left; }
    th { background: #f1f5f9; }
    .total { font-size: 28px; color: #0f4c81; font-weight: bold; }
  </style>
</head>
<body>
  <h1>Reporte de pagos</h1>
  <p>{$municipality->name}</p>
  <p class="total">{$total}</p>
  <table>
    <thead><tr><th>Referencia</th><th>Servicio</th><th>Gateway</th><th>Estatus</th><th>Importe</th></tr></thead>
    <tbody>{$tableRows}</tbody>
  </table>
</body>
</html>
HTML;

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'landscape');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="pagos.pdf"',
        ]);
    }

    private function paymentsQuery(Request $request)
    {
        return DB::table('payments as p')
            ->join('capture_lines as cl', 'cl.id', '=', 'p.capture_line_id')
            ->select([
                'p.reference',
                'p.gateway',
                'p.method',
                'p.status',
                'p.amount',
                'p.paid_at',
                'cl.folio as capture_line_folio',
                'cl.service_type',
            ])
            ->where('p.municipality_id', (int) $request->attributes->get('tenant_id'))
            ->when($request->query('from'), fn ($query, string $from) => $query->whereDate('p.created_at', '>=', $from))
            ->when($request->query('to'), fn ($query, string $to) => $query->whereDate('p.created_at', '<=', $to))
            ->when($request->query('service_type'), fn ($query, string $serviceType) => $query->where('cl.service_type', $serviceType))
            ->when($request->query('gateway'), fn ($query, string $gateway) => $query->where('p.gateway', $gateway))
            ->when($request->query('method'), fn ($query, string $method) => $query->where('p.method', $method))
            ->when($request->query('status'), fn ($query, string $status) => $query->where('p.status', $status));
    }
}
