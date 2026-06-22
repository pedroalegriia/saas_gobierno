<?php

namespace MunicipalSaas\Predial\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use MunicipalSaas\Predial\Application\UseCases\SearchPredialAccount;
use MunicipalSaas\Predial\Presentation\Http\Requests\SearchPredialAccountRequest;
use MunicipalSaas\Predial\Presentation\Http\Resources\PredialAccountResource;

final readonly class PredialAccountController
{
    public function __construct(
        private SearchPredialAccount $searchPredialAccount,
    ) {
    }

    public function search(SearchPredialAccountRequest $request): PredialAccountResource|JsonResponse
    {
        $account = $this->searchPredialAccount->execute(
            municipalityId: (int) $request->attributes->get('tenant_id'),
            propertyKey: (string) $request->validated('property_key'),
        );

        if ($account === null) {
            return new JsonResponse(['message' => 'Cuenta predial no encontrada.'], 404);
        }

        return new PredialAccountResource($account);
    }
}
