<?php

namespace MunicipalSaas\TrafficFines\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MunicipalSaas\TrafficFines\Application\DTO\TrafficFineData;

final class TrafficFineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var TrafficFineData $fine */
        $fine = $this->resource;

        return [
            'id' => $fine->id,
            'folio' => $fine->folio,
            'plate' => $fine->plate,
            'offender_name' => $fine->offenderName,
            'amount' => $fine->amount,
            'status' => $fine->status,
            'violation_date' => $fine->violationDate,
        ];
    }
}
