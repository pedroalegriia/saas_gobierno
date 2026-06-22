<?php

namespace MunicipalSaas\Tenants\Presentation\Http\Controllers;

use App\Policies\SuperAdminPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final readonly class AdminGlobalMetricsController
{
    public function __construct(
        private SuperAdminPolicy $superAdminPolicy,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (! $request->user() || ! $this->superAdminPolicy->manage($request->user())) {
            return new JsonResponse(['message' => 'No autorizado.'], 403);
        }

        return new JsonResponse([
            'municipalities' => DB::table('municipalities')->count(),
            'active_municipalities' => DB::table('municipalities')->where('status', 'ACTIVE')->count(),
            'users' => DB::table('users')->count(),
            'monthly_revenue' => '$' . number_format((float) DB::table('payments')
                ->where('status', 'PAID')
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'), 2, '.', ','),
            'pending_capture_lines' => DB::table('capture_lines')->where('status', 'PENDING')->count(),
            'top_municipalities' => DB::table('payments as p')
                ->join('municipalities as m', 'm.id', '=', 'p.municipality_id')
                ->selectRaw('m.name, SUM(p.amount) as amount')
                ->where('p.status', 'PAID')
                ->groupBy('m.name')
                ->orderByDesc('amount')
                ->limit(5)
                ->get(),
        ]);
    }
}
