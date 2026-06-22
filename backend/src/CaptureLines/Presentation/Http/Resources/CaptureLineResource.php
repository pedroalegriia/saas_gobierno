<?php

namespace MunicipalSaas\CaptureLines\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MunicipalSaas\CaptureLines\Application\DTO\CaptureLineData;

final class CaptureLineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CaptureLineData $captureLine */
        $captureLine = $this->resource;

        return [
            'id' => $captureLine->id,
            'folio' => $captureLine->folio,
            'service_type' => $captureLine->serviceType,
            'service_id' => $captureLine->serviceId,
            'amount' => $captureLine->amount,
            'expiration_date' => $captureLine->expirationDate,
            'status' => $captureLine->status,
        ];
    }
}
