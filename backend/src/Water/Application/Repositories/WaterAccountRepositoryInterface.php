<?php

namespace MunicipalSaas\Water\Application\Repositories;

use MunicipalSaas\Water\Application\DTO\WaterAccountData;

interface WaterAccountRepositoryInterface
{
    public function findByContractNumber(int $municipalityId, string $contractNumber): ?WaterAccountData;
}
