<?php

namespace MunicipalSaas\TrafficFines\Application\UseCases;

use MunicipalSaas\TrafficFines\Application\Repositories\TrafficFineRepositoryInterface;

final readonly class SearchTrafficFines
{
    public function __construct(
        private TrafficFineRepositoryInterface $fines,
    ) {
    }

    /**
     * @return list<\MunicipalSaas\TrafficFines\Application\DTO\TrafficFineData>
     */
    public function execute(int $municipalityId, ?string $folio, ?string $plate): array
    {
        return $this->fines->search($municipalityId, $folio, $plate);
    }
}
