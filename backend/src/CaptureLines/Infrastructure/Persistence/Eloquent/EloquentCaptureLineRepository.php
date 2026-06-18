<?php

namespace MunicipalSaas\CaptureLines\Infrastructure\Persistence\Eloquent;

use Illuminate\Support\Carbon;
use MunicipalSaas\CaptureLines\Application\DTO\CaptureLineData;
use MunicipalSaas\CaptureLines\Application\Repositories\CaptureLineRepositoryInterface;
use MunicipalSaas\Shared\Domain\Enums\CaptureLineStatus;
use MunicipalSaas\Shared\Domain\Enums\ServiceType;

final readonly class EloquentCaptureLineRepository implements CaptureLineRepositoryInterface
{
    public function nextSequence(int $municipalityId, ServiceType $serviceType, int $year): int
    {
        return CaptureLineModel::query()
            ->where('municipality_id', $municipalityId)
            ->where('service_type', $serviceType->value)
            ->whereYear('created_at', $year)
            ->count() + 1;
    }

    public function create(
        int $municipalityId,
        string $folio,
        ServiceType $serviceType,
        int $serviceId,
        string $amount,
        string $expirationDate,
    ): CaptureLineData {
        $model = CaptureLineModel::query()->create([
            'municipality_id' => $municipalityId,
            'folio' => $folio,
            'service_type' => $serviceType->value,
            'service_id' => $serviceId,
            'amount' => $amount,
            'expiration_date' => $expirationDate,
            'status' => CaptureLineStatus::Pending->value,
        ]);

        return $this->toData($model);
    }

    public function findPendingByFolio(int $municipalityId, string $folio): ?CaptureLineData
    {
        $model = CaptureLineModel::query()
            ->where('municipality_id', $municipalityId)
            ->where('folio', $folio)
            ->where('status', CaptureLineStatus::Pending->value)
            ->whereDate('expiration_date', '>=', Carbon::today())
            ->first();

        return $model ? $this->toData($model) : null;
    }

    private function toData(CaptureLineModel $model): CaptureLineData
    {
        return new CaptureLineData(
            id: (int) $model->id,
            municipalityId: (int) $model->municipality_id,
            folio: $model->folio,
            serviceType: $model->service_type,
            serviceId: (int) $model->service_id,
            amount: (string) $model->amount,
            expirationDate: $model->expiration_date->toDateString(),
            status: $model->status,
        );
    }
}
