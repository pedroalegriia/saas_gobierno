<?php

namespace MunicipalSaas\TrafficFines\Infrastructure\Persistence\Eloquent;

use MunicipalSaas\TrafficFines\Application\DTO\TrafficFineData;
use MunicipalSaas\TrafficFines\Application\Repositories\TrafficFineRepositoryInterface;

final readonly class EloquentTrafficFineRepository implements TrafficFineRepositoryInterface
{
    public function search(int $municipalityId, ?string $folio, ?string $plate): array
    {
        return TrafficFineModel::query()
            ->where('municipality_id', $municipalityId)
            ->when($folio, fn ($query) => $query->where('folio', $folio))
            ->when($plate, fn ($query) => $query->where('plate', strtoupper($plate)))
            ->limit(25)
            ->get()
            ->map(fn (TrafficFineModel $model) => new TrafficFineData(
                id: (int) $model->id,
                municipalityId: (int) $model->municipality_id,
                folio: $model->folio,
                plate: $model->plate,
                offenderName: $model->offender_name,
                amount: (string) $model->amount,
                status: $model->status,
                violationDate: $model->violation_date->toDateString(),
            ))
            ->all();
    }
}
