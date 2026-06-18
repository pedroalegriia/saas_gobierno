<?php

namespace MunicipalSaas\Reports\Presentation\Http\Controllers;

use App\Policies\TreasuryPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final readonly class TreasuryDashboardController
{
    public function __construct(
        private TreasuryPolicy $treasuryPolicy,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (! $request->user() || ! $this->treasuryPolicy->viewDashboard($request->user(), $request->attributes->get('tenant'))) {
            return new JsonResponse(['message' => 'No autorizado.'], 403);
        }

        $municipalityId = (int) $request->attributes->get('tenant_id');
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();

        return new JsonResponse([
            'municipality_id' => $municipalityId,
            'kpis' => [
                'daily_revenue' => $this->formatMoney($this->paidRevenue($municipalityId, $today->copy()->startOfDay(), $today->copy()->endOfDay())),
                'monthly_revenue' => $this->formatMoney($this->paidRevenue($municipalityId, $monthStart, $today->copy()->endOfDay())),
                'predial_collected' => $this->formatMoney($this->paidRevenueByService($municipalityId, 'PREDIAL', $monthStart, $today->copy()->endOfDay())),
                'water_collected' => $this->formatMoney($this->paidRevenueByService($municipalityId, 'WATER', $monthStart, $today->copy()->endOfDay())),
                'fines_collected' => $this->formatMoney($this->paidRevenueByService($municipalityId, 'TRAFFIC_FINE', $monthStart, $today->copy()->endOfDay())),
                'pending_payments' => $this->pendingCaptureLines($municipalityId),
            ],
            'charts' => [
                'revenue_by_day' => $this->revenueByDay($municipalityId, $today),
                'revenue_by_month' => $this->revenueByMonth($municipalityId, $today),
                'distribution_by_service' => $this->distributionByService($municipalityId, $monthStart, $today->copy()->endOfDay()),
            ],
            'tables' => [
                'latest_payments' => $this->latestPayments($municipalityId),
                'recent_debts' => $this->recentDebts($municipalityId),
                'issued_receipts' => $this->issuedReceipts($municipalityId),
            ],
        ]);
    }

    private function paidRevenue(int $municipalityId, Carbon $start, Carbon $end): float
    {
        return (float) DB::table('payments')
            ->where('municipality_id', $municipalityId)
            ->where('status', 'PAID')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');
    }

    private function paidRevenueByService(int $municipalityId, string $serviceType, Carbon $start, Carbon $end): float
    {
        return (float) DB::table('payments as p')
            ->join('capture_lines as cl', 'cl.id', '=', 'p.capture_line_id')
            ->where('p.municipality_id', $municipalityId)
            ->where('p.status', 'PAID')
            ->where('cl.service_type', $serviceType)
            ->whereBetween('p.paid_at', [$start, $end])
            ->sum('p.amount');
    }

    private function pendingCaptureLines(int $municipalityId): int
    {
        return DB::table('capture_lines')
            ->where('municipality_id', $municipalityId)
            ->where('status', 'PENDING')
            ->whereDate('expiration_date', '>=', Carbon::today())
            ->count();
    }

    /**
     * @return list<array{label: string, date: string, amount: float}>
     */
    private function revenueByDay(int $municipalityId, Carbon $today): array
    {
        $start = $today->copy()->subDays(11)->startOfDay();
        $rows = DB::table('payments')
            ->selectRaw('DATE(paid_at) as bucket, SUM(amount) as total')
            ->where('municipality_id', $municipalityId)
            ->where('status', 'PAID')
            ->whereBetween('paid_at', [$start, $today->copy()->endOfDay()])
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        return collect(range(0, 11))
            ->map(function (int $offset) use ($start, $rows): array {
                $date = $start->copy()->addDays($offset);
                $key = $date->toDateString();

                return [
                    'label' => $date->format('d M'),
                    'date' => $key,
                    'amount' => (float) ($rows[$key] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{label: string, month: string, amount: float}>
     */
    private function revenueByMonth(int $municipalityId, Carbon $today): array
    {
        $start = $today->copy()->subMonths(5)->startOfMonth();
        $rows = DB::table('payments')
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as bucket, SUM(amount) as total")
            ->where('municipality_id', $municipalityId)
            ->where('status', 'PAID')
            ->whereBetween('paid_at', [$start, $today->copy()->endOfMonth()])
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        return collect(range(0, 5))
            ->map(function (int $offset) use ($start, $rows): array {
                $date = $start->copy()->addMonths($offset);
                $key = $date->format('Y-m');

                return [
                    'label' => $date->format('M Y'),
                    'month' => $key,
                    'amount' => (float) ($rows[$key] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{service_type: string, label: string, amount: float, percentage: int}>
     */
    private function distributionByService(int $municipalityId, Carbon $start, Carbon $end): array
    {
        $rows = DB::table('payments as p')
            ->join('capture_lines as cl', 'cl.id', '=', 'p.capture_line_id')
            ->selectRaw('cl.service_type, SUM(p.amount) as total')
            ->where('p.municipality_id', $municipalityId)
            ->where('p.status', 'PAID')
            ->whereBetween('p.paid_at', [$start, $end])
            ->groupBy('cl.service_type')
            ->pluck('total', 'service_type');

        $total = max(1, (float) $rows->sum());

        return collect(['PREDIAL', 'WATER', 'TRAFFIC_FINE'])
            ->map(fn (string $serviceType): array => [
                'service_type' => $serviceType,
                'label' => $this->serviceLabel($serviceType),
                'amount' => (float) ($rows[$serviceType] ?? 0),
                'percentage' => (int) round(((float) ($rows[$serviceType] ?? 0) / $total) * 100),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function latestPayments(int $municipalityId): array
    {
        return DB::table('payments as p')
            ->join('capture_lines as cl', 'cl.id', '=', 'p.capture_line_id')
            ->select([
                'p.reference',
                'p.amount',
                'p.status',
                'p.gateway',
                'p.method',
                'p.paid_at',
                'cl.folio as capture_line_folio',
                'cl.service_type',
            ])
            ->where('p.municipality_id', $municipalityId)
            ->orderByDesc('p.paid_at')
            ->limit(6)
            ->get()
            ->map(fn (object $row): array => [
                'icon' => $this->serviceIcon($row->service_type),
                'title' => 'Pago ' . strtolower($this->serviceLabel($row->service_type)) . ' confirmado',
                'subtitle' => $row->capture_line_folio . ' · ' . strtoupper($row->gateway),
                'amount' => $this->formatMoney((float) $row->amount),
                'status' => $row->status,
                'paid_at' => $row->paid_at,
                'reference' => $row->reference,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function recentDebts(int $municipalityId): array
    {
        $predial = DB::table('predial_accounts')
            ->selectRaw("'home_work' as icon, 'Predial' as service, property_key as reference, owner_name as citizen, (current_balance + overdue_balance) as amount")
            ->where('municipality_id', $municipalityId)
            ->whereRaw('(current_balance + overdue_balance) > 0')
            ->limit(3)
            ->get();

        $water = DB::table('water_accounts')
            ->selectRaw("'water_drop' as icon, 'Agua potable' as service, contract_number as reference, customer_name as citizen, (current_balance + overdue_balance) as amount")
            ->where('municipality_id', $municipalityId)
            ->whereRaw('(current_balance + overdue_balance) > 0')
            ->limit(3)
            ->get();

        $fines = DB::table('traffic_fines')
            ->selectRaw("'traffic' as icon, 'Multas' as service, folio as reference, offender_name as citizen, amount")
            ->where('municipality_id', $municipalityId)
            ->where('status', 'PENDING')
            ->limit(3)
            ->get();

        return $predial
            ->merge($water)
            ->merge($fines)
            ->sortByDesc(fn (object $row): float => (float) $row->amount)
            ->take(6)
            ->map(fn (object $row): array => [
                'icon' => $row->icon,
                'title' => $row->service . ' pendiente',
                'subtitle' => $row->reference . ' · ' . ($row->citizen ?? 'Ciudadano'),
                'amount' => $this->formatMoney((float) $row->amount),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function issuedReceipts(int $municipalityId): array
    {
        return DB::table('receipts as r')
            ->join('payments as p', 'p.id', '=', 'r.payment_id')
            ->select(['r.folio', 'r.concept', 'r.amount', 'r.issued_at', 'p.reference'])
            ->where('r.municipality_id', $municipalityId)
            ->orderByDesc('r.issued_at')
            ->limit(6)
            ->get()
            ->map(fn (object $row): array => [
                'icon' => 'receipt_long',
                'title' => $row->folio,
                'subtitle' => $row->concept . ' · ' . $row->reference,
                'amount' => $this->formatMoney((float) $row->amount),
                'issued_at' => $row->issued_at,
            ])
            ->values()
            ->all();
    }

    private function formatMoney(float $amount): string
    {
        return '$' . number_format($amount, 2, '.', ',');
    }

    private function serviceLabel(string $serviceType): string
    {
        return match ($serviceType) {
            'PREDIAL' => 'Predial',
            'WATER' => 'Agua potable',
            'TRAFFIC_FINE' => 'Multas',
            default => 'Servicio',
        };
    }

    private function serviceIcon(string $serviceType): string
    {
        return match ($serviceType) {
            'PREDIAL' => 'home_work',
            'WATER' => 'water_drop',
            'TRAFFIC_FINE' => 'traffic',
            default => 'payments',
        };
    }
}
