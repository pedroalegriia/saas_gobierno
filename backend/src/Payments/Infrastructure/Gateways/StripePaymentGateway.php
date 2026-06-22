<?php

namespace MunicipalSaas\Payments\Infrastructure\Gateways;

use MunicipalSaas\CaptureLines\Application\DTO\CaptureLineData;
use MunicipalSaas\Payments\Application\DTO\PaymentRequestData;
use MunicipalSaas\Payments\Application\DTO\PaymentResultData;
use MunicipalSaas\Payments\Application\Gateways\PaymentGatewayInterface;
use MunicipalSaas\Shared\Domain\Enums\PaymentStatus;

final readonly class StripePaymentGateway implements PaymentGatewayInterface
{
    public function supports(string $gateway): bool
    {
        return $gateway === 'stripe';
    }

    public function charge(PaymentRequestData $request, CaptureLineData $captureLine): PaymentResultData
    {
        return new PaymentResultData(
            gateway: 'stripe',
            reference: 'st_' . $captureLine->folio,
            status: PaymentStatus::Authorized->value,
            amount: $captureLine->amount,
            metadata: ['capture_line' => $captureLine->folio],
        );
    }

    public function handleWebhook(array $payload): PaymentResultData
    {
        return new PaymentResultData(
            gateway: 'stripe',
            reference: (string) ($payload['reference'] ?? $payload['id'] ?? ''),
            status: PaymentStatus::Paid->value,
            amount: (string) ($payload['amount'] ?? '0.00'),
            metadata: $payload,
        );
    }
}
