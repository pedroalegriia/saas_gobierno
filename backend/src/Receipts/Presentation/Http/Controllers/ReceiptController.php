<?php

namespace MunicipalSaas\Receipts\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class ReceiptController
{
    public function download(string $folio, Request $request): JsonResponse
    {
        return new JsonResponse([
            'message' => 'La generacion PDF oficial se conectara al emisor de recibos.',
            'folio' => $folio,
            'municipality_id' => $request->attributes->get('tenant_id'),
        ]);
    }
}
