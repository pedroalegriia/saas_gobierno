<?php

namespace MunicipalSaas\TrafficFines\Application\Repositories;

use MunicipalSaas\TrafficFines\Application\DTO\TrafficFineData;

interface TrafficFineRepositoryInterface
{
    /**
     * @return list<TrafficFineData>
     */
    public function search(int $municipalityId, ?string $folio, ?string $plate): array;
}
