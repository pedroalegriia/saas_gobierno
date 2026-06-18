<?php

namespace MunicipalSaas\Users\Presentation\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use MunicipalSaas\Users\Presentation\Http\Requests\LoginRequest;

final readonly class AuthController
{
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check((string) $validated['password'], $user->password)) {
            return new JsonResponse(['message' => 'Credenciales invalidas.'], 422);
        }

        $tenantId = $request->attributes->get('tenant_id');
        if ($user->role !== 'super_admin' && (int) $user->municipality_id !== (int) $tenantId) {
            return new JsonResponse(['message' => 'Usuario no autorizado para este municipio.'], 403);
        }

        $token = $user->createToken('api')->plainTextToken;

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
}
