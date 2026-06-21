<?php

namespace MunicipalSaas\Users\Presentation\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use MunicipalSaas\Users\Presentation\Http\Requests\LoginRequest;

final readonly class AuthController
{
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check((string) $validated['password'], $user->password)) {
            $this->audit($request, null, 'auth.login_failed', [
                'email' => $validated['email'],
                'reason' => 'invalid_credentials',
            ]);

            return new JsonResponse(['message' => 'Credenciales invalidas.'], 422);
        }

        $tenantId = $request->attributes->get('tenant_id');
        if ($user->role !== 'super_admin' && (int) $user->municipality_id !== (int) $tenantId) {
            $this->audit($request, (int) $user->id, 'auth.login_denied', [
                'email' => $validated['email'],
                'reason' => 'tenant_mismatch',
            ]);

            return new JsonResponse(['message' => 'Usuario no autorizado para este municipio.'], 403);
        }

        $token = $user->createToken('api')->plainTextToken;
        $this->audit($request, (int) $user->id, 'auth.login_success', [
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return new JsonResponse([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'expires_in' => null,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $newValue
     */
    private function audit(LoginRequest $request, ?int $userId, string $action, array $newValue): void
    {
        DB::table('audit_logs')->insert([
            'municipality_id' => $request->attributes->get('tenant_id'),
            'user_id' => $userId,
            'action' => $action,
            'entity' => 'auth',
            'entity_id' => $newValue['email'] ?? null,
            'old_value' => null,
            'new_value' => json_encode($newValue),
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);
    }
}
