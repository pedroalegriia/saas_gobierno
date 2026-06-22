<?php

namespace MunicipalSaas\CaptureLines\Application\DTO;

final readonly class CaptureLineData
{
    public function __construct(
        public int $id,
        public int $municipalityId,
        public string $folio,
        public string $serviceType,
        public int $serviceId,
        public string $amount,
        public string $expirationDate,
        public string $status,
    ) {
    }
}
