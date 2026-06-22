<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DemoTreasuryDashboardSeeder extends Seeder
{
    private const MUNICIPALITY_ID = 1;

    public function run(): void
    {
        $today = Carbon::today();

        DB::table('capture_lines')->upsert($this->captureLines($today), ['folio'], [
            'municipality_id',
            'service_type',
            'service_id',
            'amount',
            'expiration_date',
            'status',
            'updated_at',
        ]);

        DB::table('folio_sequences')->upsert([
            $this->folioSequence('PREDIAL', 2026, 6),
            $this->folioSequence('WATER', 2026, 5),
            $this->folioSequence('TRAFFIC_FINE', 2026, 3),
        ], ['municipality_id', 'service_type', 'year'], ['last_number', 'updated_at']);

        DB::table('payments')->upsert($this->payments($today), ['reference'], [
            'municipality_id',
            'capture_line_id',
            'gateway',
            'method',
            'amount',
            'status',
            'paid_at',
            'metadata',
            'updated_at',
        ]);

        DB::table('receipts')->upsert($this->receipts($today), ['folio'], [
            'municipality_id',
            'payment_id',
            'concept',
            'amount',
            'pdf_path',
            'issued_at',
            'updated_at',
        ]);

        DB::table('audit_logs')
            ->where('municipality_id', self::MUNICIPALITY_ID)
            ->where('entity_id', 'demo')
            ->delete();

        DB::table('audit_logs')->insert($this->auditLogs($today));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function captureLines(Carbon $today): array
    {
        return [
            $this->captureLine(1, 'COL-PRE-2026000001', 'PREDIAL', 1, 1700.00, 'PAID', $today->copy()->subDays(9)),
            $this->captureLine(2, 'COL-AGU-2026000001', 'WATER', 1, 440.00, 'PAID', $today->copy()->subDays(8)),
            $this->captureLine(3, 'COL-MUL-2026000001', 'TRAFFIC_FINE', 2, 1245.00, 'PAID', $today->copy()->subDays(7)),
            $this->captureLine(4, 'COL-PRE-2026000002', 'PREDIAL', 2, 2180.00, 'PAID', $today->copy()->subDays(6)),
            $this->captureLine(5, 'COL-AGU-2026000002', 'WATER', 2, 7200.00, 'PAID', $today->copy()->subDays(5)),
            $this->captureLine(6, 'COL-PRE-2026000003', 'PREDIAL', 3, 10390.00, 'PAID', $today->copy()->subDays(4)),
            $this->captureLine(7, 'COL-MUL-2026000002', 'TRAFFIC_FINE', 4, 1865.00, 'PAID', $today->copy()->subDays(3)),
            $this->captureLine(8, 'COL-AGU-2026000003', 'WATER', 3, 510.00, 'PAID', $today->copy()->subDays(2)),
            $this->captureLine(9, 'COL-PRE-2026000004', 'PREDIAL', 1, 1700.00, 'PAID', $today->copy()->subDay()),
            $this->captureLine(10, 'COL-AGU-2026000004', 'WATER', 4, 1560.00, 'PAID', $today),
            $this->captureLine(11, 'COL-PRE-2026000005', 'PREDIAL', 3, 10390.00, 'PAID', $today),
            $this->captureLine(12, 'COL-MUL-2026000003', 'TRAFFIC_FINE', 3, 740.00, 'PENDING', $today),
            $this->captureLine(13, 'COL-AGU-2026000005', 'WATER', 2, 7200.00, 'PENDING', $today),
            $this->captureLine(14, 'COL-PRE-2026000006', 'PREDIAL', 2, 2180.00, 'PENDING', $today),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function payments(Carbon $today): array
    {
        return [
            $this->payment(1, 1, 'stripe', 'credit_card', 1700.00, 'PAY-COL-000001', $today->copy()->subMonths(5)->setDay(14)),
            $this->payment(2, 2, 'openpay', 'debit_card', 440.00, 'PAY-COL-000002', $today->copy()->subMonths(4)->setDay(18)),
            $this->payment(3, 3, 'mercadopago', 'spei', 1245.00, 'PAY-COL-000003', $today->copy()->subMonths(3)->setDay(9)),
            $this->payment(4, 4, 'stripe', 'credit_card', 2180.00, 'PAY-COL-000004', $today->copy()->subMonths(2)->setDay(22)),
            $this->payment(5, 5, 'openpay', 'debit_card', 7200.00, 'PAY-COL-000005', $today->copy()->subMonths(1)->setDay(12)),
            $this->payment(6, 6, 'stripe', 'spei', 10390.00, 'PAY-COL-000006', $today->copy()->subDays(4)),
            $this->payment(7, 7, 'mercadopago', 'credit_card', 1865.00, 'PAY-COL-000007', $today->copy()->subDays(3)),
            $this->payment(8, 8, 'openpay', 'debit_card', 510.00, 'PAY-COL-000008', $today->copy()->subDays(2)),
            $this->payment(9, 9, 'stripe', 'credit_card', 1700.00, 'PAY-COL-000009', $today->copy()->subDay()),
            $this->payment(10, 10, 'openpay', 'spei', 1560.00, 'PAY-COL-000010', $today->copy()->setTime(10, 15)),
            $this->payment(11, 11, 'stripe', 'credit_card', 10390.00, 'PAY-COL-000011', $today->copy()->setTime(13, 40)),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function receipts(Carbon $today): array
    {
        $concepts = [
            1 => 'Pago de predial COL-001-002-003',
            2 => 'Pago de agua AGU-COL-000123',
            3 => 'Pago de multa MUL-COL-2026-0002',
            4 => 'Pago de predial COL-004-118-021',
            5 => 'Pago de agua AGU-COL-000984',
            6 => 'Pago de predial COL-009-332-104',
            7 => 'Pago de multa MUL-COL-2026-0004',
            8 => 'Pago de agua AGU-COL-001420',
            9 => 'Pago de predial COL-001-002-003',
            10 => 'Pago de agua AGU-COL-002010',
            11 => 'Pago de predial COL-009-332-104',
        ];

        return array_map(function (int $paymentId) use ($concepts, $today): array {
            $amounts = [1 => 1700.00, 2 => 440.00, 3 => 1245.00, 4 => 2180.00, 5 => 7200.00, 6 => 10390.00, 7 => 1865.00, 8 => 510.00, 9 => 1700.00, 10 => 1560.00, 11 => 10390.00];
            $issuedAt = $paymentId >= 10 ? $today->copy()->setTime(14, $paymentId === 10 ? 5 : 12) : $today->copy()->subDays(11 - $paymentId)->setTime(12, 0);

            return [
                'id' => $paymentId,
                'municipality_id' => self::MUNICIPALITY_ID,
                'payment_id' => $paymentId,
                'folio' => sprintf('REC-COL-2026%06d', $paymentId),
                'concept' => $concepts[$paymentId],
                'amount' => $amounts[$paymentId],
                'pdf_path' => sprintf('receipts/colima/REC-COL-2026%06d.pdf', $paymentId),
                'issued_at' => $issuedAt,
                'created_at' => $issuedAt,
                'updated_at' => now(),
            ];
        }, range(1, 11));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function auditLogs(Carbon $today): array
    {
        return [
            $this->auditLog('dashboard.viewed', 'reports.dashboard', $today->copy()->setTime(9, 0)),
            $this->auditLog('payment.confirmed', 'payments', $today->copy()->setTime(10, 15)),
            $this->auditLog('receipt.issued', 'receipts', $today->copy()->setTime(14, 12)),
        ];
    }

    private function captureLine(
        int $id,
        string $folio,
        string $serviceType,
        int $serviceId,
        float $amount,
        string $status,
        Carbon $createdAt,
    ): array {
        return [
            'id' => $id,
            'municipality_id' => self::MUNICIPALITY_ID,
            'folio' => $folio,
            'service_type' => $serviceType,
            'service_id' => $serviceId,
            'amount' => $amount,
            'expiration_date' => $createdAt->copy()->addDays(15)->toDateString(),
            'status' => $status,
            'created_at' => $createdAt,
            'updated_at' => now(),
        ];
    }

    private function payment(
        int $id,
        int $captureLineId,
        string $gateway,
        string $method,
        float $amount,
        string $reference,
        Carbon $paidAt,
    ): array {
        return [
            'id' => $id,
            'municipality_id' => self::MUNICIPALITY_ID,
            'capture_line_id' => $captureLineId,
            'gateway' => $gateway,
            'method' => $method,
            'amount' => $amount,
            'reference' => $reference,
            'status' => 'PAID',
            'paid_at' => $paidAt,
            'metadata' => json_encode(['demo' => true, 'presentation' => 'colima']),
            'created_at' => $paidAt,
            'updated_at' => now(),
        ];
    }

    private function folioSequence(string $serviceType, int $year, int $lastNumber): array
    {
        return [
            'municipality_id' => self::MUNICIPALITY_ID,
            'service_type' => $serviceType,
            'year' => $year,
            'last_number' => $lastNumber,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function auditLog(string $action, string $entity, Carbon $createdAt): array
    {
        return [
            'municipality_id' => self::MUNICIPALITY_ID,
            'user_id' => 2,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => 'demo',
            'old_value' => null,
            'new_value' => json_encode(['seeded' => true]),
            'ip' => '127.0.0.1',
            'created_at' => $createdAt,
        ];
    }
}
