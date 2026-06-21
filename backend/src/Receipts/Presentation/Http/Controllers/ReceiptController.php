<?php

namespace MunicipalSaas\Receipts\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MunicipalSaas\Shared\Presentation\Pdf\OfficialDocumentRenderer;

final readonly class ReceiptController
{
    public function __construct(
        private OfficialDocumentRenderer $documents,
    ) {
    }

    public function download(string $folio, Request $request): Response|JsonResponse
    {
        $receipt = DB::table('receipts as r')
            ->join('payments as p', 'p.id', '=', 'r.payment_id')
            ->where('r.municipality_id', (int) $request->attributes->get('tenant_id'))
            ->where(fn ($query) => $query
                ->where('r.folio', $folio)
                ->orWhere('p.reference', $folio))
            ->select([
                'r.folio',
                'r.concept',
                'r.amount',
                'r.issued_at',
                'p.reference as payment_reference',
            ])
            ->first();

        if (! $receipt) {
            return new JsonResponse(['message' => 'Recibo no encontrado.'], 404);
        }

        $municipality = $request->attributes->get('tenant');
        $verificationUrl = rtrim((string) config('app.url'), '/') . '/api/v1/receipts/' . $receipt->folio;

        $pdf = $this->documents->receipt([
            'municipality' => $municipality->name,
            'primary_color' => $municipality->primaryColor,
            'secondary_color' => $municipality->secondaryColor,
            'folio' => $receipt->folio,
            'concept' => $receipt->concept,
            'amount' => (string) $receipt->amount,
            'issued_at' => (string) $receipt->issued_at,
            'payment_reference' => $receipt->payment_reference,
            'verification_url' => $verificationUrl,
        ]);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="recibo-' . $receipt->folio . '.pdf"',
        ]);
    }
}
