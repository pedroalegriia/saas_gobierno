<?php

namespace MunicipalSaas\Payments\Application\DTO;

final readonly class PaymentData
{
    public function __construct(
        public int $id,
        public int $municipalityId,
        public int $captureLineId,
        public string $gateway,
        public string $method,
        public string $amount,
        public ?string $reference,
        public string $status,
    ) {
    }
}
