<?php

namespace MunicipalSaas\Predial\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MunicipalSaas\Predial\Application\DTO\PredialAccountData;

final class PredialAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var PredialAccountData $account */
        $account = $this->resource;

        return [
            'id' => $account->id,
            'property_key' => $account->propertyKey,
            'owner_name' => $account->ownerName,
            'address' => $account->address,
            'current_balance' => $account->currentBalance,
            'overdue_balance' => $account->overdueBalance,
            'total_balance' => $account->totalBalance(),
            'status' => $account->status,
        ];
    }
}
