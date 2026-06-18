<?php

namespace MunicipalSaas\Tenants\Application\Services;

use MunicipalSaas\Shared\Domain\Exceptions\TenantNotFoundException;
use MunicipalSaas\Tenants\Application\DTO\MunicipalityData;
use MunicipalSaas\Tenants\Application\Repositories\MunicipalityRepositoryInterface;

final readonly class TenantResolver
{
    public function __construct(
        private MunicipalityRepositoryInterface $municipalities,
    ) {
    }

    public function resolve(string $host): MunicipalityData
    {
        $normalizedHost = strtolower(explode(':', $host)[0]);

        $municipality = $this->municipalities->findActiveByDomain($normalizedHost);
        if ($municipality !== null) {
            return $municipality;
        }

        $slug = $this->extractSlug($normalizedHost);
        if ($slug !== null) {
            $municipality = $this->municipalities->findActiveBySlug($slug);
            if ($municipality !== null) {
                return $municipality;
            }
        }

        if ($this->isLocalHost($normalizedHost)) {
            $municipality = $this->municipalities->findActiveBySlug((string) config('domain.local_tenant_slug', 'colima'));
            if ($municipality !== null) {
                return $municipality;
            }
        }

        throw TenantNotFoundException::forHost($host);
    }

    private function extractSlug(string $host): ?string
    {
        $baseDomain = config('domain.tenant_base_domain');
        if (! str_ends_with($host, '.' . $baseDomain)) {
            return null;
        }

        $candidate = substr($host, 0, -strlen('.' . $baseDomain));

        return $candidate !== '' && ! str_contains($candidate, '.') ? $candidate : null;
    }

    private function isLocalHost(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
}
