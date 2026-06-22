<?php

namespace MunicipalSaas\Tenants\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MunicipalSaas\Shared\Domain\Exceptions\TenantNotFoundException;
use MunicipalSaas\Tenants\Application\Services\TenantResolver;
use Symfony\Component\HttpFoundation\Response;

final readonly class ResolveTenantMiddleware
{
    public function __construct(
        private TenantResolver $resolver,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $tenant = $this->resolver->resolve($request->getHost());
        } catch (TenantNotFoundException) {
            return new JsonResponse(['message' => 'Municipio no encontrado o inactivo.'], 404);
        }

        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('tenant_id', $tenant->id);
        $request->attributes->set('tenant_slug', $tenant->slug);
        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
