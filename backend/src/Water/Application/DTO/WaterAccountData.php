<?php

namespace MunicipalSaas\Water\Application\DTO;

final readonly class WaterAccountData
{
    public function __construct(
        public int $id,
        public int $municipalityId,
        public string $contractNumber,
        public string $customerName,
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
