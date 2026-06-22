<?php

namespace MunicipalSaas\Users\Presentation\Http\Controllers;

use App\Models\User;
use App\Policies\SuperAdminPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final readonly class AdminUserController
{
    public function __construct(
        private SuperAdminPolicy $superAdminPolicy,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        if (! $request->user() || ! $this->superAdminPolicy->manage($request->user())) {
            return new JsonResponse(['message' => 'No autorizado.'], 403);
        }

        return new JsonResponse([
            'data' => User::query()
                ->withCasts(['created_at' => 'datetime'])
                ->orderBy('name')
                ->get(['id', 'municipality_id', 'name', 'email', 'role', 'status', 'created_at']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $request->user() || ! $this->superAdminPolicy->manage($request->user())) {
            return new JsonResponse(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate($this->rules());
        $user = User::query()->create($validated);

        return new JsonResponse(['data' => $user->only(['id', 'municipality_id', 'name', 'email', 'role', 'status'])], 201);
    }

    public function update(int $user, Request $request): JsonResponse
    {
        if (! $request->user() || ! $this->superAdminPolicy->manage($request->user())) {
            return new JsonResponse(['message' => 'No autorizado.'], 403);
        }

        $model = User::query()->findOrFail($user);
        $validated = $request->validate($this->rules($user, updating: true));
        if (($validated['password'] ?? null) === null) {
            unset($validated['password']);
        }

        $model->update($validated);

        return new JsonResponse(['data' => $model->only(['id', 'municipality_id', 'name', 'email', 'role', 'status'])]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?int $userId = null, bool $updating = false): array
    {
        return [
            'municipality_id' => ['nullable', 'integer', 'exists:municipalities,id'],
            'name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$updating ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['super_admin', 'treasury', 'cashier', 'auditor', 'citizen'])],
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE', 'BLOCKED'])],
        ];
    }
}
