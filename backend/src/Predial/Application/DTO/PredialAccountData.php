<?php

namespace MunicipalSaas\Predial\Application\DTO;

final readonly class PredialAccountData
{
    public function __construct(
        public int $id,
        public int $municipalityId,
        public string $propertyKey,
        public string $ownerName,
        public string $address,
        public string $currentBalance,
        public string $overdueBalance,
        public string $status,
    ) {
    }

    public function totalBalance(): string
    {
        return number_format((float) $this->currentBalance + (float) $this->overdueBalance, 2, '.', '');
    }
}
