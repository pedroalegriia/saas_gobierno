<?php

namespace MunicipalSaas\Reports\Presentation\Http\Controllers;

use App\Policies\TreasuryPolicy;
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

        $rows = DB::table('payments as p')
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
}
