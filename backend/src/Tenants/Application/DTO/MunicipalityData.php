<?php

namespace MunicipalSaas\Tenants\Application\DTO;

final readonly class MunicipalityData
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?string $domain,
        public ?string $logo,
        public string $primaryColor,
        public string $secondaryColor,
        public string $status,
        public array $settings,
    ) {
    }
}
