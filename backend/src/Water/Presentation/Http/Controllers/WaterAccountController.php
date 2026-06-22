<?php

namespace MunicipalSaas\Water\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use MunicipalSaas\Water\Application\UseCases\SearchWaterAccount;
use MunicipalSaas\Water\Presentation\Http\Requests\SearchWaterAccountRequest;
use MunicipalSaas\Water\Presentation\Http\Resources\WaterAccountResource;

final readonly class WaterAccountController
{
    public function __construct(
        private SearchWaterAccount $searchWaterAccount,
    ) {
    }

    public function search(SearchWaterAccountRequest $request): WaterAccountResource|JsonResponse
    {
        $account = $this->searchWaterAccount->execute(
            municipalityId: (int) $request->attributes->get('tenant_id'),
            contractNumber: (string) $request->validated('contract_number'),
        );

        if ($account === null) {
            return new JsonResponse(['message' => 'Cuenta de agua no encontrada.'], 404);
        }

        return new WaterAccountResource($account);
    }
}
