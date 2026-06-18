<?php

namespace MunicipalSaas\Tenants\Infrastructure\Persistence\Eloquent;

use MunicipalSaas\Tenants\Application\DTO\MunicipalityData;
use MunicipalSaas\Tenants\Application\Repositories\MunicipalityRepositoryInterface;

final readonly class EloquentMunicipalityRepository implements MunicipalityRepositoryInterface
{
    public function findActiveByDomain(string $domain): ?MunicipalityData
    {
        $model = MunicipalityModel::query()
            ->where('domain', $domain)
            ->where('status', 'ACTIVE')
            ->first();

        return $model ? $this->toData($model) : null;
    }

    public function findActiveBySlug(string $slug): ?MunicipalityData
    {
        $model = MunicipalityModel::query()
            ->where('slug', $slug)
            ->where('status', 'ACTIVE')
            ->first();

        return $model ? $this->toData($model) : null;
    }

    private function toData(MunicipalityModel $model): MunicipalityData
    {
        return new MunicipalityData(
            id: (int) $model->id,
            name: $model->name,
            slug: $model->slug,
            domain: $model->domain,
            logo: $model->logo,
            primaryColor: $model->primary_color,
            secondaryColor: $model->secondary_color,
            status: $model->status,
            settings: $model->settings_json ?? [],
        );
    }
}
