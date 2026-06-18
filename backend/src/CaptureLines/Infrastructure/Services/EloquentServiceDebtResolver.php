<?php

namespace MunicipalSaas\CaptureLines\Infrastructure\Services;

use MunicipalSaas\CaptureLines\Application\Services\ServiceDebtResolverInterface;
use MunicipalSaas\Predial\Infrastructure\Persistence\Eloquent\PredialAccountModel;
use MunicipalSaas\Shared\Domain\Enums\ServiceType;
use MunicipalSaas\TrafficFines\Infrastructure\Persistence\Eloquent\TrafficFineModel;
use MunicipalSaas\Water\Infrastructure\Persistence\Eloquent\WaterAccountModel;

final readonly class EloquentServiceDebtResolver implements ServiceDebtResolverInterface
{
    public function amountFor(int $municipalityId, ServiceType $serviceType, int $serviceId): ?string
    {
        return match ($serviceType) {
            ServiceType::Predial => $this->predialAmount($municipalityId, $serviceId),
            ServiceType::Water => $this->waterAmount($municipalityId, $serviceId),
            ServiceType::TrafficFine => $this->trafficFineAmount($municipalityId, $serviceId),
        };
    }

    private function predialAmount(int $municipalityId, int $serviceId): ?string
    {
        $account = PredialAccountModel::query()
            ->where('municipality_id', $municipalityId)
            ->find($serviceId);

        return $account ? number_format((float) $account->current_balance + (float) $account->overdue_balance, 2, '.', '') : null;
    }

    private function waterAmount(int $municipalityId, int $serviceId): ?string
    {
        $account = WaterAccountModel::query()
            ->where('municipality_id', $municipalityId)
            ->find($serviceId);

        return $account ? number_format((float) $account->current_balance + (float) $account->overdue_balance, 2, '.', '') : null;
    }

    private function trafficFineAmount(int $municipalityId, int $serviceId): ?string
    {
        $fine = TrafficFineModel::query()
            ->where('municipality_id', $municipalityId)
            ->where('status', 'PENDING')
            ->find($serviceId);

        return $fine ? (string) $fine->amount : null;
    }
}
