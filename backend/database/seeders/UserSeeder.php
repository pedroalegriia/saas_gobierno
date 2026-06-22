<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->upsert([
            [
                'id' => 1,
                'municipality_id' => null,
                'name' => 'Super Admin',
                'email' => 'admin@pagosmunicipales.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'municipality_id' => 1,
                'name' => 'Tesoreria Colima',
                'email' => 'tesoreria@colima.gob.mx',
                'password' => Hash::make('password'),
                'role' => 'treasury',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'municipality_id' => 1,
                'name' => 'Caja Colima',
                'email' => 'caja@colima.gob.mx',
                'password' => Hash::make('password'),
                'role' => 'cashier',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'municipality_id' => 1,
                'name' => 'Auditoria Colima',
                'email' => 'auditoria@colima.gob.mx',
                'password' => Hash::make('password'),
                'role' => 'auditor',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['id'], ['municipality_id', 'name', 'email', 'password', 'role', 'status', 'updated_at']);
    }
}
