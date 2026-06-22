<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AuthLoginAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success_returns_token_and_records_audit(): void
    {
        DB::table('municipalities')->insert([
            'id' => 1,
            'name' => 'Municipio de Colima',
            'slug' => 'colima',
            'primary_color' => '#0F4C81',
            'secondary_color' => '#B08D57',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => 10,
            'municipality_id' => 1,
            'name' => 'Tesoreria',
            'email' => 'tesoreria@colima.gob.mx',
            'password' => Hash::make('password'),
            'role' => 'treasury',
            'status' => 'ACTIVE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'tesoreria@colima.gob.mx',
            'password' => 'password',
        ], ['Host' => 'localhost']);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.role', 'treasury');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => 10,
            'action' => 'auth.login_success',
            'entity' => 'auth',
        ]);
    }
}
