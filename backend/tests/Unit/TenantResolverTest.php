<?php

namespace Tests\Unit;

use MunicipalSaas\Tenants\Application\DTO\MunicipalityData;
use MunicipalSaas\Tenants\Application\Repositories\MunicipalityRepositoryInterface;
use MunicipalSaas\Tenants\Application\Services\TenantResolver;
use PHPUnit\Framework\TestCase;

final class TenantResolverTest extends TestCase
{
    public function test_it_resolves_by_custom_domain(): void
    {
        $repository = new class implements MunicipalityRepositoryInterface {
            public function findActiveByDomain(string $domain): ?MunicipalityData
            {
                return $domain === 'pagos.colima.gob.mx'
                    ? new MunicipalityData(1, 'Colima', 'colima', $domain, null, '#000000', '#FFFFFF', 'ACTIVE', [])
                    : null;
            }

            public function findActiveBySlug(string $slug): ?MunicipalityData
            {
                return null;
            }
        };

        $resolver = new TenantResolver($repository);

        self::assertSame('colima', $resolver->resolve('pagos.colima.gob.mx')->slug);
    }
}
