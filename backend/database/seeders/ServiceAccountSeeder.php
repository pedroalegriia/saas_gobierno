<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ServiceAccountSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('predial_accounts')->upsert([
            [
                'municipality_id' => 1,
                'property_key' => 'COL-001-002-003',
                'owner_name' => 'Maria Lopez',
                'address' => 'Av. Ayuntamiento 100, Centro',
                'current_balance' => 1250.00,
                'overdue_balance' => 450.00,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['municipality_id', 'property_key'], ['owner_name', 'address', 'current_balance', 'overdue_balance', 'status', 'updated_at']);

        DB::table('water_accounts')->upsert([
            [
                'municipality_id' => 1,
                'contract_number' => 'AGU-COL-000123',
                'customer_name' => 'Maria Lopez',
                'address' => 'Av. Ayuntamiento 100, Centro',
                'current_balance' => 320.00,
                'overdue_balance' => 120.00,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['municipality_id', 'contract_number'], ['customer_name', 'address', 'current_balance', 'overdue_balance', 'status', 'updated_at']);

        DB::table('traffic_fines')->upsert([
            [
                'municipality_id' => 1,
                'folio' => 'MUL-COL-2026-0001',
                'plate' => 'ABC123A',
                'offender_name' => 'Maria Lopez',
                'amount' => 980.00,
                'status' => 'PENDING',
                'violation_date' => '2026-01-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['municipality_id', 'folio'], ['plate', 'offender_name', 'amount', 'status', 'violation_date', 'updated_at']);
    }
}
