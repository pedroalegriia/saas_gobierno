<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MunicipalSaas\CaptureLines\Application\Repositories\CaptureLineRepositoryInterface;
use MunicipalSaas\CaptureLines\Application\Services\ServiceDebtResolverInterface;
use MunicipalSaas\CaptureLines\Infrastructure\Persistence\Eloquent\EloquentCaptureLineRepository;
use MunicipalSaas\CaptureLines\Infrastructure\Services\EloquentServiceDebtResolver;
use MunicipalSaas\Payments\Application\Gateways\PaymentGatewayInterface;
use MunicipalSaas\Payments\Application\Repositories\PaymentRepositoryInterface;
use MunicipalSaas\Payments\Infrastructure\Gateways\CompositePaymentGateway;
use MunicipalSaas\Payments\Infrastructure\Gateways\MercadoPagoPaymentGateway;
use MunicipalSaas\Payments\Infrastructure\Gateways\OpenPayApiClient;
use MunicipalSaas\Payments\Infrastructure\Gateways\OpenPayPaymentGateway;
use MunicipalSaas\Payments\Infrastructure\Gateways\StripePaymentGateway;
use MunicipalSaas\Payments\Infrastructure\Persistence\Eloquent\EloquentPaymentRepository;
use MunicipalSaas\Predial\Application\Repositories\PredialAccountRepositoryInterface;
use MunicipalSaas\Predial\Infrastructure\Persistence\Eloquent\EloquentPredialAccountRepository;
use MunicipalSaas\Tenants\Application\Repositories\MunicipalityRepositoryInterface;
use MunicipalSaas\Tenants\Infrastructure\Persistence\Eloquent\EloquentMunicipalityRepository;
use MunicipalSaas\TrafficFines\Application\Repositories\TrafficFineRepositoryInterface;
use MunicipalSaas\TrafficFines\Infrastructure\Persistence\Eloquent\EloquentTrafficFineRepository;
use MunicipalSaas\Water\Application\Repositories\WaterAccountRepositoryInterface;
use MunicipalSaas\Water\Infrastructure\Persistence\Eloquent\EloquentWaterAccountRepository;

final class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MunicipalityRepositoryInterface::class, EloquentMunicipalityRepository::class);
        $this->app->bind(PredialAccountRepositoryInterface::class, EloquentPredialAccountRepository::class);
        $this->app->bind(WaterAccountRepositoryInterface::class, EloquentWaterAccountRepository::class);
        $this->app->bind(TrafficFineRepositoryInterface::class, EloquentTrafficFineRepository::class);
        $this->app->bind(CaptureLineRepositoryInterface::class, EloquentCaptureLineRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, EloquentPaymentRepository::class);
        $this->app->bind(ServiceDebtResolverInterface::class, EloquentServiceDebtResolver::class);
        $this->app->singleton(PaymentGatewayInterface::class, fn () => new CompositePaymentGateway([
            new OpenPayPaymentGateway(new OpenPayApiClient()),
            new MercadoPagoPaymentGateway(),
            new StripePaymentGateway(),
        ]));
    }
}
