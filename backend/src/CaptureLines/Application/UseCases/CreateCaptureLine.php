<?php

namespace MunicipalSaas\CaptureLines\Application\UseCases;

use Illuminate\Support\Carbon;
use InvalidArgumentException;
use MunicipalSaas\CaptureLines\Application\DTO\CaptureLineData;
use MunicipalSaas\CaptureLines\Application\DTO\CreateCaptureLineData;
use MunicipalSaas\CaptureLines\Application\Repositories\CaptureLineRepositoryInterface;
use MunicipalSaas\CaptureLines\Application\Services\ServiceDebtResolverInterface;

final readonly class CreateCaptureLine
{
    public function __construct(
        private CaptureLineRepositoryInterface $captureLines,
        private ServiceDebtResolverInterface $debtResolver,
    ) {
    }

    public function execute(CreateCaptureLineData $data): CaptureLineData
    {
        $amount = $this->debtResolver->amountFor($data->municipalityId, $data->serviceType, $data->serviceId);
        if ($amount === null || (float) $amount <= 0) {
            throw new InvalidArgumentException('No existe adeudo vigente para generar linea de captura.');
        }

        $year = (int) now()->format('Y');
        $sequence = $this->captureLines->nextSequence($data->municipalityId, $data->serviceType, $year);
        $folio = sprintf(
            '%s-%s-%d%06d',
            strtoupper(substr($data->municipalitySlug, 0, 3)),
            $data->serviceType->folioSegment(),
            $year,
            $sequence,
        );

        return $this->captureLines->create(
            municipalityId: $data->municipalityId,
            folio: $folio,
            serviceType: $data->serviceType,
            serviceId: $data->serviceId,
            amount: $amount,
            expirationDate: Carbon::today()
                ->addDays((int) config('domain.capture_line_expiration_days', 15))
                ->toDateString(),
        );
    }
}
