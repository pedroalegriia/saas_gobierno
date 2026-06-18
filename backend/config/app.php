<?php

return [
    'name' => env('APP_NAME', 'SaaS Gobierno Municipal'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'America/Mexico_City',
    'locale' => 'es_MX',
    'fallback_locale' => 'es',
    'faker_locale' => 'es_MX',
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'providers' => [
        App\Providers\AppServiceProvider::class,
        App\Providers\DomainServiceProvider::class,
        App\Providers\EventServiceProvider::class,
    ],
];
