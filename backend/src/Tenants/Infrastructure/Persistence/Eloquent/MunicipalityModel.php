<?php

namespace MunicipalSaas\Tenants\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class MunicipalityModel extends Model
{
    protected $table = 'municipalities';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo',
        'primary_color',
        'secondary_color',
        'status',
        'settings_json',
    ];

    protected $casts = [
        'settings_json' => 'array',
    ];
}
