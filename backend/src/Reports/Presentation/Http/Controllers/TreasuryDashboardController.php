<?php

namespace MunicipalSaas\Reports\Presentation\Http\Controllers;

use App\Policies\TreasuryPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return new JsonResponse([
            'municipality_id' => $request->attributes->get('tenant_id'),
            'kpis' => [
                'daily_revenue' => '0.00',
                'monthly_revenue' => '0.00',
                'predial_collected' => '0.00',
                'water_collected' => '0.00',
                'fines_collected' => '0.00',
                'pending_payments' => 0,
            ],
            'charts' => [
                'revenue_by_day' => [],
                'revenue_by_month' => [],
                'distribution_by_service' => [],
            ],
            'tables' => [
                'latest_payments' => [],
                'recent_debts' => [],
                'issued_receipts' => [],
            ],
        ]);
    }
}
