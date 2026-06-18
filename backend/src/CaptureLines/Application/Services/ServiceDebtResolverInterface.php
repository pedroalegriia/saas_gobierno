<?php

namespace MunicipalSaas\CaptureLines\Application\Services;

use MunicipalSaas\Shared\Domain\Enums\ServiceType;

interface ServiceDebtResolverInterface
{
    public function amountFor(int $municipalityId, ServiceType $serviceType, int $serviceId): ?string;
}
