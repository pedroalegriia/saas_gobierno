<?php

namespace MunicipalSaas\CaptureLines\Application\DTO;

use MunicipalSaas\Shared\Domain\Enums\ServiceType;

final readonly class CreateCaptureLineData
{
    public function __construct(
        public int $municipalityId,
        public string $municipalitySlug,
        public ServiceType $serviceType,
        public int $serviceId,
    ) {
    }
}
