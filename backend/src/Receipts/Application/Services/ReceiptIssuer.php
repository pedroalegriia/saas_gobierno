<?php

namespace MunicipalSaas\Receipts\Application\Services;

use Illuminate\Support\Facades\DB;
use MunicipalSaas\Shared\Domain\Enums\ServiceType;

final readonly class ReceiptIssuer
{
    public function issueForPayment(int $paymentId): ?string
    {
        $payment = DB::table('payments as p')
            ->join('capture_lines as cl', 'cl.id', '=', 'p.capture_line_id')
            ->where('p.id', $paymentId)
            ->select([
                'p.id',
                'p.municipality_id',
                'p.reference',
                'p.amount',
                'p.status',
                'cl.folio as capture_line_folio',
                'cl.service_type',
                'cl.service_id',
            ])
            ->first();

        if (! $payment || $payment->status !== 'PAID') {
            return null;
        }

        $existing = DB::table('receipts')
            ->where('payment_id', $paymentId)
            ->first();

        if ($existing) {
            return $existing->folio;
        }

        $folio = 'REC-' . substr((string) $payment->capture_line_folio, 0, 3) . '-' . now()->format('Y') . str_pad((string) $paymentId, 6, '0', STR_PAD_LEFT);

        DB::table('receipts')->insert([
            'municipality_id' => $payment->municipality_id,
            'payment_id' => $paymentId,
            'folio' => $folio,
            'concept' => $this->concept((string) $payment->service_type, (int) $payment->service_id),
            'amount' => $payment->amount,
            'pdf_path' => 'receipts/auto/' . $folio . '.pdf',
            'issued_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $folio;
    }

    private function concept(string $serviceType, int $serviceId): string
    {
        return match ($serviceType) {
            ServiceType::Predial->value => 'Pago de predial #' . $serviceId,
            ServiceType::Water->value => 'Pago de agua #' . $serviceId,
            ServiceType::TrafficFine->value => 'Pago de multa #' . $serviceId,
            default => 'Pago municipal #' . $serviceId,
        };
    }
}
