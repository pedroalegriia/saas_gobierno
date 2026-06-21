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
use MunicipalSaas\Receipts\Application\Services\ReceiptIssuer;

final readonly class PaymentController
{
    public function __construct(
        private StartPayment $startPayment,
        private PaymentGatewayInterface $paymentGateway,
        private ReceiptIssuer $receiptIssuer,
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
        if ($gateway === 'openpay' && ! $this->validOpenPaySignature($request)) {
            $this->recordWebhookEvent($gateway, $request, 'INVALID_SIGNATURE', null, 'Firma de webhook invalida.');

            return new JsonResponse(['message' => 'Firma de webhook invalida.'], 401);
        }

        $payload = array_merge($request->all(), ['gateway' => $gateway]);
        $result = $this->paymentGateway->handleWebhook($payload);
        $eventId = $this->recordWebhookEvent($gateway, $request, 'RECEIVED', $result->reference);

        try {
            DB::transaction(function () use ($result, $payload): void {
                $payment = DB::table('payments')
                    ->where('reference', $result->reference)
                    ->lockForUpdate()
                    ->first();

                if ($payment === null) {
                    Log::channel('webhooks')->warning('Payment webhook without local payment reference.', $payload);
                    throw new InvalidArgumentException('Referencia de pago no encontrada.');
                }

                DB::table('payments')
                    ->where('id', $payment->id)
                    ->update([
                        'status' => $result->status,
                        'paid_at' => $result->status === PaymentStatus::Paid->value ? now() : $payment->paid_at,
                        'metadata' => json_encode(array_merge((array) json_decode($payment->metadata ?? '[]', true), [
                            'webhook' => $payload,
                        ])),
                        'updated_at' => now(),
                    ]);

                if ($result->status === PaymentStatus::Paid->value) {
                    DB::table('capture_lines')
                        ->where('id', $payment->capture_line_id)
                        ->update([
                            'status' => CaptureLineStatus::Paid->value,
                            'updated_at' => now(),
                        ]);

                    $this->receiptIssuer->issueForPayment((int) $payment->id);
                }
            });

            $this->markWebhookEventProcessed($eventId, 'PROCESSED');
        } catch (\Throwable $exception) {
            $this->markWebhookEventProcessed($eventId, 'FAILED', $exception->getMessage());

            return new JsonResponse(['message' => 'Webhook recibido pero no procesado.', 'error' => $exception->getMessage()], 202);
        }

        return new JsonResponse(['message' => 'Webhook aceptado.'], 202);
    }

    private function validOpenPaySignature(Request $request): bool
    {
        $secret = (string) config('payments.openpay.webhook_secret');
        if ($secret === '') {
            return true;
        }

        $signature = $request->header('X-OpenPay-Signature')
            ?? $request->header('X-Openpay-Signature')
            ?? $request->header('OpenPay-Signature');

        if (! is_string($signature) || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    private function recordWebhookEvent(
        string $gateway,
        Request $request,
        string $status,
        ?string $paymentReference,
        ?string $error = null,
    ): int {
        return (int) DB::table('payment_webhook_events')->insertGetId([
            'municipality_id' => $request->attributes->get('tenant_id'),
            'gateway' => $gateway,
            'event_id' => (string) ($request->input('id') ?? $request->input('event_id') ?? ''),
            'payment_reference' => $paymentReference,
            'status' => $status,
            'headers' => json_encode($request->headers->all()),
            'payload' => $request->getContent(),
            'error_message' => $error,
            'processed_at' => $status === 'INVALID_SIGNATURE' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function markWebhookEventProcessed(int $eventId, string $status, ?string $error = null): void
    {
        DB::table('payment_webhook_events')
            ->where('id', $eventId)
            ->update([
                'status' => $status,
                'error_message' => $error,
                'processed_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
