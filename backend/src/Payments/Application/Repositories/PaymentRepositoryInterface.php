<?php

namespace MunicipalSaas\Payments\Application\Repositories;

use MunicipalSaas\Payments\Application\DTO\PaymentData;
use MunicipalSaas\Payments\Application\DTO\PaymentResultData;

interface PaymentRepositoryInterface
{
    public function createFromGatewayResult(
        int $municipalityId,
        int $captureLineId,
        string $method,
        PaymentResultData $result,
    ): PaymentData;
}
