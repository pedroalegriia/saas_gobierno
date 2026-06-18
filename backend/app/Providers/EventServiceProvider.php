<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use MunicipalSaas\Payments\Domain\Events\PaymentAuthorized;
use MunicipalSaas\Receipts\Application\Listeners\GenerateReceiptWhenPaymentAuthorized;

final class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PaymentAuthorized::class => [
            GenerateReceiptWhenPaymentAuthorized::class,
        ],
    ];
}
