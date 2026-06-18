<?php

namespace MunicipalSaas\CaptureLines\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use MunicipalSaas\CaptureLines\Application\DTO\CreateCaptureLineData;
use MunicipalSaas\CaptureLines\Application\UseCases\CreateCaptureLine;
use MunicipalSaas\CaptureLines\Presentation\Http\Requests\CreateCaptureLineRequest;
use MunicipalSaas\CaptureLines\Presentation\Http\Resources\CaptureLineResource;
use MunicipalSaas\Shared\Domain\Enums\ServiceType;

final readonly class CaptureLineController
{
    public function __construct(
        private CreateCaptureLine $createCaptureLine,
    ) {
    }

    public function store(CreateCaptureLineRequest $request): CaptureLineResource|JsonResponse
    {
        try {
            $captureLine = $this->createCaptureLine->execute(new CreateCaptureLineData(
                municipalityId: (int) $request->attributes->get('tenant_id'),
                municipalitySlug: (string) $request->attributes->get('tenant_slug'),
                serviceType: ServiceType::from((string) $request->validated('service_type')),
                serviceId: (int) $request->validated('service_id'),
            ));
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], 422);
        }

        return new CaptureLineResource($captureLine);
    }
}
