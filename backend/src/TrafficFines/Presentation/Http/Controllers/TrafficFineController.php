<?php

namespace MunicipalSaas\TrafficFines\Presentation\Http\Controllers;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use MunicipalSaas\TrafficFines\Application\UseCases\SearchTrafficFines;
use MunicipalSaas\TrafficFines\Presentation\Http\Requests\SearchTrafficFineRequest;
use MunicipalSaas\TrafficFines\Presentation\Http\Resources\TrafficFineResource;

final readonly class TrafficFineController
{
    public function __construct(
        private SearchTrafficFines $searchTrafficFines,
    ) {
    }

    public function search(SearchTrafficFineRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $fines = $this->searchTrafficFines->execute(
            municipalityId: (int) $request->attributes->get('tenant_id'),
            folio: $validated['folio'] ?? null,
            plate: $validated['plate'] ?? null,
        );

        return TrafficFineResource::collection($fines);
    }
}
