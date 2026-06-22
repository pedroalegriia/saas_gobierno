<?php

namespace MunicipalSaas\Predial\Application\Repositories;

use MunicipalSaas\Predial\Application\DTO\PredialAccountData;

interface PredialAccountRepositoryInterface
{
    public function findByPropertyKey(int $municipalityId, string $propertyKey): ?PredialAccountData;
}
