<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('tenant-sensitive', function (Request $request) {
            $tenant = $request->attributes->get('tenant_slug', 'public');

            return [
                Limit::perMinute(30)->by($tenant . '|' . $request->ip()),
            ];
        });
    }
}
