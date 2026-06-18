<?php

namespace MunicipalSaas\Water\Infrastructure\Persistence\Eloquent;

use MunicipalSaas\Water\Application\DTO\WaterAccountData;
use MunicipalSaas\Water\Application\Repositories\WaterAccountRepositoryInterface;

final readonly class EloquentWaterAccountRepository implements WaterAccountRepositoryInterface
{
    public function findByContractNumber(int $municipalityId, string $contractNumber): ?WaterAccountData
    {
        $model = WaterAccountModel::query()
            ->where('municipality_id', $municipalityId)
            ->where('contract_number', $contractNumber)
            ->first();

        return $model ? new WaterAccountData(
            id: (int) $model->id,
            municipalityId: (int) $model->municipality_id,
            contractNumber: $model->contract_number,
            customerName: $model->customer_name,
            address: $model->address,
            currentBalance: (string) $model->current_balance,
            overdueBalance: (string) $model->overdue_balance,
            status: $model->status,
        ) : null;
    }
}
