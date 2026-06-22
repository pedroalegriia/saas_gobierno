<?php

namespace MunicipalSaas\Receipts\Application\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use MunicipalSaas\Payments\Domain\Events\PaymentAuthorized;

final class GenerateReceiptWhenPaymentAuthorized implements ShouldQueue
{
    public function handle(PaymentAuthorized $event): void
    {
        // The PDF emitter will persist receipts and notify the citizen in the next implementation phase.
    }
}
