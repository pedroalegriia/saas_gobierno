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
                'id' => 1,
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
            [
                'id' => 2,
                'municipality_id' => 1,
                'property_key' => 'COL-004-118-021',
                'owner_name' => 'Carlos Ramirez',
                'address' => 'Calle Madero 215, Centro',
                'current_balance' => 2180.00,
                'overdue_balance' => 0.00,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'municipality_id' => 1,
                'property_key' => 'COL-009-332-104',
                'owner_name' => 'Constructora Pacifico',
                'address' => 'Blvd. Camino Real 820, Jardines Vista Hermosa',
                'current_balance' => 8460.00,
                'overdue_balance' => 1930.00,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'municipality_id' => 1,
                'property_key' => 'COL-020-145-017',
                'owner_name' => 'Ana Torres',
                'address' => 'Privada Cedros 44, Residencial Esmeralda',
                'current_balance' => 0.00,
                'overdue_balance' => 0.00,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['municipality_id', 'property_key'], ['owner_name', 'address', 'current_balance', 'overdue_balance', 'status', 'updated_at']);

        DB::table('water_accounts')->upsert([
            [
                'id' => 1,
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
            [
                'id' => 2,
                'municipality_id' => 1,
                'contract_number' => 'AGU-COL-000984',
                'customer_name' => 'Hotel Jardin Central',
                'address' => 'Portal Morelos 18, Centro',
                'current_balance' => 6420.00,
                'overdue_balance' => 780.00,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'municipality_id' => 1,
                'contract_number' => 'AGU-COL-001420',
                'customer_name' => 'Luis Mendoza',
                'address' => 'Av. Tecnologico 505, Las Viboras',
                'current_balance' => 510.00,
                'overdue_balance' => 0.00,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'municipality_id' => 1,
                'contract_number' => 'AGU-COL-002010',
                'customer_name' => 'Mercado Municipal Local 14',
                'address' => 'Mercado Obregon Local 14, Centro',
                'current_balance' => 1220.00,
                'overdue_balance' => 340.00,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['municipality_id', 'contract_number'], ['customer_name', 'address', 'current_balance', 'overdue_balance', 'status', 'updated_at']);

        DB::table('traffic_fines')->upsert([
            [
                'id' => 1,
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
            [
                'id' => 2,
                'municipality_id' => 1,
                'folio' => 'MUL-COL-2026-0002',
                'plate' => 'JRD442B',
                'offender_name' => 'Carlos Ramirez',
                'amount' => 1245.00,
                'status' => 'PAID',
                'violation_date' => '2026-02-09',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'municipality_id' => 1,
                'folio' => 'MUL-COL-2026-0003',
                'plate' => 'KLM908C',
                'offender_name' => 'Sofia Beltran',
                'amount' => 740.00,
                'status' => 'PENDING',
                'violation_date' => '2026-04-21',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'municipality_id' => 1,
                'folio' => 'MUL-COL-2026-0004',
                'plate' => 'TAX331D',
                'offender_name' => 'Transporte Centro Norte',
                'amount' => 1865.00,
                'status' => 'PAID',
                'violation_date' => '2026-05-30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['municipality_id', 'folio'], ['plate', 'offender_name', 'amount', 'status', 'violation_date', 'updated_at']);
    }
}
