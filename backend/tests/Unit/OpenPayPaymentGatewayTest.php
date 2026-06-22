<?php

namespace Tests\Unit;

use MunicipalSaas\Payments\Infrastructure\Gateways\OpenPayApiClient;
use MunicipalSaas\Payments\Infrastructure\Gateways\OpenPayPaymentGateway;
use PHPUnit\Framework\TestCase;

final class OpenPayPaymentGatewayTest extends TestCase
{
    public function test_it_maps_completed_webhook_to_paid_result(): void
    {
        $gateway = new OpenPayPaymentGateway(new OpenPayApiClient());

        $result = $gateway->handleWebhook([
            'gateway' => 'openpay',
            'transaction' => [
                'order_id' => 'COL-PRE-2026000001',
                'status' => 'completed',
                'amount' => 1700,
            ],
        ]);

        self::assertSame('openpay', $result->gateway);
        self::assertSame('COL-PRE-2026000001', $result->reference);
        self::assertSame('PAID', $result->status);
        self::assertSame('1700', $result->amount);
    }
}
