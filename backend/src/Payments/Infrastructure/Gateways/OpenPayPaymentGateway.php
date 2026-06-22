<?php

namespace MunicipalSaas\Payments\Infrastructure\Gateways;

use MunicipalSaas\CaptureLines\Application\DTO\CaptureLineData;
use MunicipalSaas\Payments\Application\DTO\PaymentRequestData;
use MunicipalSaas\Payments\Application\DTO\PaymentResultData;
use MunicipalSaas\Payments\Application\Gateways\PaymentGatewayInterface;
use MunicipalSaas\Shared\Domain\Enums\PaymentStatus;

final readonly class OpenPayPaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        private OpenPayApiClient $client,
    ) {
    }

    public function supports(string $gateway): bool
    {
        return $gateway === 'openpay';
    }

    public function charge(PaymentRequestData $request, CaptureLineData $captureLine): PaymentResultData
    {
        if ($this->client->enabled()) {
            return $this->realCharge($request, $captureLine);
        }

        if ($request->method === 'oxxo_cash') {
            return new PaymentResultData(
                gateway: 'openpay',
                reference: 'OP-OXXO-' . $captureLine->folio,
                status: PaymentStatus::Pending->value,
                amount: $captureLine->amount,
                metadata: [
                    'capture_line' => $captureLine->folio,
                    'method' => 'oxxo_cash',
                    'paynet_reference' => $this->paynetReference($captureLine->folio),
                ],
            );
        }

        return new PaymentResultData(
            gateway: 'openpay',
            reference: 'op_' . $captureLine->folio,
            status: PaymentStatus::Authorized->value,
            amount: $captureLine->amount,
            metadata: ['capture_line' => $captureLine->folio],
        );
    }

    public function handleWebhook(array $payload): PaymentResultData
    {
        $transaction = $payload['transaction'] ?? $payload;
        $reference = (string) (
            $payload['reference']
            ?? $transaction['order_id']
            ?? $transaction['id']
            ?? $payload['id']
            ?? ''
        );
        $status = (string) ($transaction['status'] ?? $payload['status'] ?? 'completed');

        return new PaymentResultData(
            gateway: 'openpay',
            reference: $reference,
            status: in_array($status, ['completed', 'paid', 'charge.succeeded'], true)
                ? PaymentStatus::Paid->value
                : PaymentStatus::Pending->value,
            amount: (string) ($transaction['amount'] ?? $payload['amount'] ?? '0.00'),
            metadata: $payload,
        );
    }

    private function realCharge(PaymentRequestData $request, CaptureLineData $captureLine): PaymentResultData
    {
        $payload = [
            'method' => $this->openPayMethod($request->method),
            'amount' => (float) $captureLine->amount,
            'description' => 'Pago municipal ' . $captureLine->folio,
            'order_id' => $captureLine->folio,
            'customer' => [
                'name' => 'Contribuyente',
                'last_name' => 'Municipal',
                'email' => 'pagos@example.test',
                'phone_number' => '0000000000',
            ],
        ];

        if (in_array($request->method, ['credit_card', 'debit_card'], true)) {
            $payload['source_id'] = $request->paymentToken;
            $payload['device_session_id'] = $request->paymentToken;
        }

        if ($request->method === 'oxxo_cash') {
            $payload['due_date'] = $captureLine->expirationDate . 'T23:59:59-06:00';
        }

        $response = $this->client->createCharge($payload);
        $paymentMethod = is_array($response['payment_method'] ?? null) ? $response['payment_method'] : [];
        $reference = $captureLine->folio;

        return new PaymentResultData(
            gateway: 'openpay',
            reference: $reference,
            status: $request->method === 'oxxo_cash' ? PaymentStatus::Pending->value : PaymentStatus::Authorized->value,
            amount: (string) ($response['amount'] ?? $captureLine->amount),
            metadata: [
                'openpay' => $response,
                'paynet_reference' => $paymentMethod['reference'] ?? null,
                'barcode_url' => $paymentMethod['barcode_url'] ?? null,
                'method' => $request->method,
                'capture_line' => $captureLine->folio,
            ],
        );
    }

    private function openPayMethod(string $method): string
    {
        return match ($method) {
            'oxxo_cash' => 'store',
            'spei' => 'bank_account',
            default => 'card',
        };
    }

    private function paynetReference(string $folio): string
    {
        return trim(chunk_split((string) abs(crc32($folio . '|openpay|oxxo')), 4, ' '));
    }
}
