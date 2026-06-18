<?php

namespace MunicipalSaas\TrafficFines\Application\DTO;

final readonly class TrafficFineData
{
    public function __construct(
        public int $id,
        public int $municipalityId,
        public string $folio,
        public string $plate,
        public ?string $offenderName,
        public string $amount,
        public string $status,
        public string $violationDate,
    ) {
    }
}
