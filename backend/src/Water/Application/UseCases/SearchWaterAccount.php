<?php

namespace MunicipalSaas\Water\Application\UseCases;

use MunicipalSaas\Water\Application\DTO\WaterAccountData;
use MunicipalSaas\Water\Application\Repositories\WaterAccountRepositoryInterface;

final readonly class SearchWaterAccount
{
    public function __construct(
        private WaterAccountRepositoryInterface $accounts,
    ) {
    }

    public function execute(int $municipalityId, string $contractNumber): ?WaterAccountData
    {
        return $this->accounts->findByContractNumber($municipalityId, $contractNumber);
    }
}
