<?php

namespace MunicipalSaas\Payments\Application\UseCases;

use InvalidArgumentException;
use MunicipalSaas\CaptureLines\Application\Repositories\CaptureLineRepositoryInterface;
use MunicipalSaas\Payments\Application\DTO\PaymentData;
use MunicipalSaas\Payments\Application\DTO\PaymentRequestData;
use MunicipalSaas\Payments\Application\Gateways\PaymentGatewayInterface;
use MunicipalSaas\Payments\Application\Repositories\PaymentRepositoryInterface;
use MunicipalSaas\Payments\Domain\Events\PaymentAuthorized;

final readonly class StartPayment
{
    public function __construct(
        private CaptureLineRepositoryInterface $captureLines,
        private PaymentGatewayInterface $paymentGateway,
        private PaymentRepositoryInterface $payments,
    ) {
    }

    public function execute(PaymentRequestData $request): PaymentData
    {
        $captureLine = $this->captureLines->findPendingByFolio(
            $request->municipalityId,
            $request->captureLineFolio,
        );

        if ($captureLine === null) {
            throw new InvalidArgumentException('Linea de captura no encontrada, pagada o vencida.');
        }

        $result = $this->paymentGateway->charge($request, $captureLine);

        $payment = $this->payments->createFromGatewayResult(
            municipalityId: $request->municipalityId,
            captureLineId: $captureLine->id,
            method: $request->method,
            result: $result,
        );

        PaymentAuthorized::dispatch($payment);

        return $payment;
    }
}
