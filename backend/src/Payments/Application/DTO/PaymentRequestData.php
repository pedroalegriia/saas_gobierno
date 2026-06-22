<?php

namespace MunicipalSaas\Payments\Application\DTO;

final readonly class PaymentRequestData
{
    public function __construct(
        public int $municipalityId,
        public string $captureLineFolio,
        public string $gateway,
        public string $method,
        public string $paymentToken,
    ) {
    }
}
