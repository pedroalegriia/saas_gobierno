<?php

namespace MunicipalSaas\Water\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MunicipalSaas\Water\Application\DTO\WaterAccountData;

final class WaterAccountResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var WaterAccountData $account */
        $account = $this->resource;

        return [
            'id' => $account->id,
            'contract_number' => $account->contractNumber,
            'customer_name' => $account->customerName,
            'address' => $account->address,
            'current_balance' => $account->currentBalance,
            'overdue_balance' => $account->overdueBalance,
            'total_balance' => $account->totalBalance(),
            'status' => $account->status,
        ];
    }
}
