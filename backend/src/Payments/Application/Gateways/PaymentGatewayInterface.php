<?php

namespace MunicipalSaas\Payments\Application\Gateways;

use MunicipalSaas\CaptureLines\Application\DTO\CaptureLineData;
use MunicipalSaas\Payments\Application\DTO\PaymentRequestData;
use MunicipalSaas\Payments\Application\DTO\PaymentResultData;

interface PaymentGatewayInterface
{
    public function supports(string $gateway): bool;

    public function charge(PaymentRequestData $request, CaptureLineData $captureLine): PaymentResultData;

    /**
     * @param array<string, mixed> $payload
     */
    public function handleWebhook(array $payload): PaymentResultData;
}
