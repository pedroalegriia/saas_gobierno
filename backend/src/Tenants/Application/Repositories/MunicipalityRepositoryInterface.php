<?php

namespace MunicipalSaas\Tenants\Application\Repositories;

use MunicipalSaas\Tenants\Application\DTO\MunicipalityData;

interface MunicipalityRepositoryInterface
{
    public function findActiveByDomain(string $domain): ?MunicipalityData;

    public function findActiveBySlug(string $slug): ?MunicipalityData;
}
