<?php

namespace MunicipalSaas\Tenants\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MunicipalSaas\Tenants\Application\DTO\MunicipalityData;

final class MunicipalityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var MunicipalityData $tenant */
        $tenant = $this->resource;

        return [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'domain' => $tenant->domain,
            'logo' => $tenant->logo,
            'primary_color' => $tenant->primaryColor,
            'secondary_color' => $tenant->secondaryColor,
            'status' => $tenant->status,
            'settings' => $tenant->settings,
        ];
    }
}
