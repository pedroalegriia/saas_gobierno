<?php

use Illuminate\Support\Facades\Route;
use MunicipalSaas\CaptureLines\Presentation\Http\Controllers\CaptureLineController;
use MunicipalSaas\CaptureLines\Presentation\Http\Controllers\TreasuryCaptureLineController;
use MunicipalSaas\Payments\Presentation\Http\Controllers\PaymentController;
use MunicipalSaas\Predial\Presentation\Http\Controllers\PredialAccountController;
use MunicipalSaas\Receipts\Presentation\Http\Controllers\ReceiptController;
use MunicipalSaas\Reports\Presentation\Http\Controllers\ReportExportController;
use MunicipalSaas\Reports\Presentation\Http\Controllers\TreasuryDashboardController;
use MunicipalSaas\Tenants\Presentation\Http\Controllers\TenantController;
use MunicipalSaas\TrafficFines\Presentation\Http\Controllers\TrafficFineController;
use MunicipalSaas\Users\Presentation\Http\Controllers\AuthController;
use MunicipalSaas\Water\Presentation\Http\Controllers\WaterAccountController;

Route::prefix('v1')->group(function (): void {
    Route::get('tenant', TenantController::class);
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:tenant-sensitive');

    Route::get('predial/accounts/search', [PredialAccountController::class, 'search']);
    Route::get('water/accounts/search', [WaterAccountController::class, 'search']);
    Route::get('traffic-fines/search', [TrafficFineController::class, 'search']);

    Route::post('capture-lines', [CaptureLineController::class, 'store'])
        ->middleware('throttle:tenant-sensitive');
    Route::get('capture-lines/{folio}/document', [CaptureLineController::class, 'document']);

    Route::post('payments', [PaymentController::class, 'store'])
        ->middleware('throttle:tenant-sensitive');
    Route::post('payments/webhooks/{gateway}', [PaymentController::class, 'webhook'])
        ->withoutMiddleware(['auth:sanctum']);

    Route::get('receipts/{folio}', [ReceiptController::class, 'download']);

    Route::middleware(['auth:sanctum'])->group(function (): void {
        Route::get('treasury/dashboard', TreasuryDashboardController::class);
        Route::get('treasury/reports/payments.csv', [ReportExportController::class, 'paymentsCsv']);
        Route::post('treasury/capture-lines', [TreasuryCaptureLineController::class, 'store'])
            ->middleware('throttle:tenant-sensitive');
    });
});
