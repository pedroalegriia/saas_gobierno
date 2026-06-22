<?php

namespace MunicipalSaas\Payments\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MunicipalSaas\Payments\Application\DTO\PaymentData;

final class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var PaymentData $payment */
        $payment = $this->resource;

        return [
            'id' => $payment->id,
            'capture_line_id' => $payment->captureLineId,
            'gateway' => $payment->gateway,
            'method' => $payment->method,
            'amount' => $payment->amount,
            'reference' => $payment->reference,
            'status' => $payment->status,
        ];
    }
}
