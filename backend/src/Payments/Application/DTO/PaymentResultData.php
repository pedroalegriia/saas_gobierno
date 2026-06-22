<?php

namespace MunicipalSaas\Payments\Application\DTO;

final readonly class PaymentResultData
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $gateway,
        public string $reference,
        public string $status,
        public string $amount,
        public array $metadata = [],
    ) {
    }
}
