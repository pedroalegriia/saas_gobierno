<?php

namespace MunicipalSaas\Payments\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use MunicipalSaas\Shared\Domain\Enums\CaptureLineStatus;
use MunicipalSaas\Shared\Domain\Enums\PaymentStatus;
use MunicipalSaas\Payments\Application\DTO\PaymentRequestData;
use MunicipalSaas\Payments\Application\Gateways\PaymentGatewayInterface;
use MunicipalSaas\Payments\Application\UseCases\StartPayment;
use MunicipalSaas\Payments\Presentation\Http\Requests\CreatePaymentRequest;
use MunicipalSaas\Payments\Presentation\Http\Resources\PaymentResource;

final readonly class PaymentController
{
    public function __construct(
        private StartPayment $startPayment,
        private PaymentGatewayInterface $paymentGateway,
    ) {
    }

    public function store(CreatePaymentRequest $request): PaymentResource|JsonResponse
    {
        $validated = $request->validated();

        try {
            $payment = $this->startPayment->execute(new PaymentRequestData(
                municipalityId: (int) $request->attributes->get('tenant_id'),
                captureLineFolio: (string) $validated['capture_line_folio'],
                gateway: (string) $validated['gateway'],
                method: (string) $validated['method'],
                paymentToken: (string) ($validated['payment_token'] ?? ''),
            ));
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], 422);
        }

        return (new PaymentResource($payment))->response()->setStatusCode(202);
    }

    public function webhook(string $gateway, Request $request): JsonResponse
    {
        $payload = array_merge($request->all(), ['gateway' => $gateway]);
        $result = $this->paymentGateway->handleWebhook($payload);

        DB::transaction(function () use ($result, $payload): void {
            $payment = DB::table('payments')
                ->where('reference', $result->reference)
                ->lockForUpdate()
                ->first();

            if ($payment === null) {
                Log::channel('webhooks')->warning('Payment webhook without local payment reference.', $payload);

                return;
            }

            DB::table('payments')
                ->where('id', $payment->id)
                ->update([
                    'status' => PaymentStatus::Paid->value,
                    'paid_at' => now(),
                    'metadata' => json_encode(array_merge((array) json_decode($payment->metadata ?? '[]', true), [
                        'webhook' => $payload,
                    ])),
                    'updated_at' => now(),
                ]);

            DB::table('capture_lines')
                ->where('id', $payment->capture_line_id)
                ->update([
                    'status' => CaptureLineStatus::Paid->value,
                    'updated_at' => now(),
                ]);
        });

        return new JsonResponse(['message' => 'Webhook aceptado.'], 202);
    }
}
