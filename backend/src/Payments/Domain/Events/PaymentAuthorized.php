<?php

namespace MunicipalSaas\Payments\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use MunicipalSaas\Payments\Application\DTO\PaymentData;

final class PaymentAuthorized
{
    use Dispatchable;

    public function __construct(
        public readonly PaymentData $payment,
    ) {
    }
}
