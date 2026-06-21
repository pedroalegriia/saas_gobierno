<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PaymentWebhookProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_openpay_webhook_records_event_marks_payment_and_issues_receipt(): void
    {
        DB::table('municipalities')->insert([
            'id' => 1,
            'name' => 'Municipio de Colima',
            'slug' => 'colima',
            'primary_color' => '#0F4C81',
            'secondary_color' => '#B08D57',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('capture_lines')->insert([
            'id' => 55,
            'municipality_id' => 1,
            'folio' => 'COL-PRE-2026000099',
            'service_type' => 'PREDIAL',
            'service_id' => 1,
            'amount' => 1000,
            'expiration_date' => now()->addDay()->toDateString(),
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payments')->insert([
            'id' => 77,
            'municipality_id' => 1,
            'capture_line_id' => 55,
            'gateway' => 'openpay',
            'method' => 'oxxo_cash',
            'amount' => 1000,
            'reference' => 'COL-PRE-2026000099',
            'status' => 'PENDING',
            'metadata' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/payments/webhooks/openpay', [
            'id' => 'evt_test',
            'transaction' => [
                'order_id' => 'COL-PRE-2026000099',
                'status' => 'completed',
                'amount' => 1000,
            ],
        ], ['Host' => 'localhost']);

        $response->assertAccepted();

        $this->assertDatabaseHas('payment_webhook_events', [
            'gateway' => 'openpay',
            'event_id' => 'evt_test',
            'payment_reference' => 'COL-PRE-2026000099',
            'status' => 'PROCESSED',
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => 77,
            'status' => 'PAID',
        ]);

        $this->assertDatabaseHas('capture_lines', [
            'id' => 55,
            'status' => 'PAID',
        ]);

        $this->assertDatabaseHas('receipts', [
            'payment_id' => 77,
        ]);
    }
}
