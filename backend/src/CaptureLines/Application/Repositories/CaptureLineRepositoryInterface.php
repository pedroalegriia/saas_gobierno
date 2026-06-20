<?php

namespace MunicipalSaas\CaptureLines\Application\Repositories;

use MunicipalSaas\CaptureLines\Application\DTO\CaptureLineData;
use MunicipalSaas\Shared\Domain\Enums\ServiceType;

interface CaptureLineRepositoryInterface
{
    public function reserveNextSequence(int $municipalityId, ServiceType $serviceType, int $year): int;

    public function create(
        int $municipalityId,
        string $folio,
        ServiceType $serviceType,
        int $serviceId,
        string $amount,
        string $expirationDate,
    ): CaptureLineData;

    public function findPendingByFolio(int $municipalityId, string $folio): ?CaptureLineData;
}
