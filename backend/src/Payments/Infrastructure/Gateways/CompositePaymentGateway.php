<?php

namespace MunicipalSaas\Payments\Infrastructure\Gateways;

use InvalidArgumentException;
use MunicipalSaas\CaptureLines\Application\DTO\CaptureLineData;
use MunicipalSaas\Payments\Application\DTO\PaymentRequestData;
use MunicipalSaas\Payments\Application\DTO\PaymentResultData;
use MunicipalSaas\Payments\Application\Gateways\PaymentGatewayInterface;

final readonly class CompositePaymentGateway implements PaymentGatewayInterface
{
    /**
     * @param list<PaymentGatewayInterface> $gateways
     */
    public function __construct(
        private array $gateways,
    ) {
    }

    public function supports(string $gateway): bool
    {
        return $this->gatewayFor($gateway) !== null;
    }

    public function charge(PaymentRequestData $request, CaptureLineData $captureLine): PaymentResultData
    {
        $gateway = $this->gatewayFor($request->gateway);
        if ($gateway === null) {
            throw new InvalidArgumentException('Gateway de pago no soportado.');
        }

        return $gateway->charge($request, $captureLine);
    }

    public function handleWebhook(array $payload): PaymentResultData
    {
        $gateway = $this->gatewayFor((string) ($payload['gateway'] ?? ''));
        if ($gateway === null) {
            throw new InvalidArgumentException('Gateway de webhook no soportado.');
        }

        return $gateway->handleWebhook($payload);
    }

    private function gatewayFor(string $gateway): ?PaymentGatewayInterface
    {
        foreach ($this->gateways as $candidate) {
            if ($candidate->supports($gateway)) {
                return $candidate;
            }
        }

        return null;
    }
}
