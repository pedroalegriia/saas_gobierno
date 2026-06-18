<?php

namespace MunicipalSaas\Predial\Infrastructure\Persistence\Eloquent;

use MunicipalSaas\Predial\Application\DTO\PredialAccountData;
use MunicipalSaas\Predial\Application\Repositories\PredialAccountRepositoryInterface;

final readonly class EloquentPredialAccountRepository implements PredialAccountRepositoryInterface
{
    public function findByPropertyKey(int $municipalityId, string $propertyKey): ?PredialAccountData
    {
        $model = PredialAccountModel::query()
            ->where('municipality_id', $municipalityId)
            ->where('property_key', $propertyKey)
            ->first();

        return $model ? new PredialAccountData(
            id: (int) $model->id,
            municipalityId: (int) $model->municipality_id,
            propertyKey: $model->property_key,
            ownerName: $model->owner_name,
            address: $model->address,
            currentBalance: (string) $model->current_balance,
            overdueBalance: (string) $model->overdue_balance,
            status: $model->status,
        ) : null;
    }
}
