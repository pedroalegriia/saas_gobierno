<?php

namespace MunicipalSaas\Payments\Infrastructure\Persistence\Eloquent;

use MunicipalSaas\Payments\Application\DTO\PaymentData;
use MunicipalSaas\Payments\Application\DTO\PaymentResultData;
use MunicipalSaas\Payments\Application\Repositories\PaymentRepositoryInterface;

final readonly class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function createFromGatewayResult(
        int $municipalityId,
        int $captureLineId,
        string $method,
        PaymentResultData $result,
    ): PaymentData {
        $model = PaymentModel::query()->create([
            'municipality_id' => $municipalityId,
            'capture_line_id' => $captureLineId,
            'gateway' => $result->gateway,
            'method' => $method,
            'amount' => $result->amount,
            'reference' => $result->reference,
            'status' => $result->status,
            'metadata' => $result->metadata,
        ]);

        return new PaymentData(
            id: (int) $model->id,
            municipalityId: (int) $model->municipality_id,
            captureLineId: (int) $model->capture_line_id,
            gateway: $model->gateway,
            method: $model->method,
            amount: (string) $model->amount,
            reference: $model->reference,
            status: $model->status,
        );
    }
}
