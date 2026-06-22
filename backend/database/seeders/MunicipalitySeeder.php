<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class MunicipalitySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('municipalities')->upsert([
            [
                'id' => 1,
                'name' => 'Municipio de Colima',
                'slug' => 'colima',
                'domain' => 'pagos.colima.gob.mx',
                'logo' => '/assets/tenants/colima/logo.svg',
                'primary_color' => '#0F4C81',
                'secondary_color' => '#B08D57',
                'status' => 'ACTIVE',
                'settings_json' => json_encode(['currency' => 'MXN', 'timezone' => 'America/Mexico_City']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Municipio de Tecoman',
                'slug' => 'tecoman',
                'domain' => null,
                'logo' => '/assets/tenants/tecoman/logo.svg',
                'primary_color' => '#14532D',
                'secondary_color' => '#F59E0B',
                'status' => 'ACTIVE',
                'settings_json' => json_encode(['currency' => 'MXN', 'timezone' => 'America/Mexico_City']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['id'], ['name', 'slug', 'domain', 'logo', 'primary_color', 'secondary_color', 'status', 'settings_json', 'updated_at']);
    }
}
