<?php

namespace MunicipalSaas\Predial\Application\UseCases;

use MunicipalSaas\Predial\Application\DTO\PredialAccountData;
use MunicipalSaas\Predial\Application\Repositories\PredialAccountRepositoryInterface;

final readonly class SearchPredialAccount
{
    public function __construct(
        private PredialAccountRepositoryInterface $accounts,
    ) {
    }

    public function execute(int $municipalityId, string $propertyKey): ?PredialAccountData
    {
        return $this->accounts->findByPropertyKey($municipalityId, $propertyKey);
    }
}
