<?php

namespace MunicipalSaas\Tenants\Presentation\Http\Controllers;

use Illuminate\Http\Request;
use MunicipalSaas\Tenants\Presentation\Http\Resources\MunicipalityResource;

final readonly class TenantController
{
    public function __invoke(Request $request): MunicipalityResource
    {
        return new MunicipalityResource($request->attributes->get('tenant'));
    }
}
