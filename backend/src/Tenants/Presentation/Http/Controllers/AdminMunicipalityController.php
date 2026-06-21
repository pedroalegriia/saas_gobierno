<?php

namespace MunicipalSaas\Tenants\Presentation\Http\Controllers;

use App\Policies\SuperAdminPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final readonly class AdminMunicipalityController
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
            'data' => DB::table('municipalities')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $request->user() || ! $this->superAdminPolicy->manage($request->user())) {
            return new JsonResponse(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate($this->rules());
        $id = DB::table('municipalities')->insertGetId([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'domain' => $validated['domain'] ?? null,
            'logo' => $validated['logo'] ?? null,
            'primary_color' => $validated['primary_color'] ?? '#0F4C81',
            'secondary_color' => $validated['secondary_color'] ?? '#B08D57',
            'status' => $validated['status'] ?? 'ACTIVE',
            'settings_json' => json_encode($validated['settings'] ?? []),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return new JsonResponse(['data' => DB::table('municipalities')->find($id)], 201);
    }

    public function update(int $municipality, Request $request): JsonResponse
    {
        if (! $request->user() || ! $this->superAdminPolicy->manage($request->user())) {
            return new JsonResponse(['message' => 'No autorizado.'], 403);
        }

        $validated = $request->validate($this->rules($municipality));
        DB::table('municipalities')
            ->where('id', $municipality)
            ->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'domain' => $validated['domain'] ?? null,
                'logo' => $validated['logo'] ?? null,
                'primary_color' => $validated['primary_color'] ?? '#0F4C81',
                'secondary_color' => $validated['secondary_color'] ?? '#B08D57',
                'status' => $validated['status'] ?? 'ACTIVE',
                'settings_json' => json_encode($validated['settings'] ?? []),
                'updated_at' => now(),
            ]);

        return new JsonResponse(['data' => DB::table('municipalities')->find($municipality)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?int $municipalityId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:80', Rule::unique('municipalities', 'slug')->ignore($municipalityId)],
            'domain' => ['nullable', 'string', 'max:180', Rule::unique('municipalities', 'domain')->ignore($municipalityId)],
            'logo' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'secondary_color' => ['nullable', 'string', 'max:7'],
            'status' => ['nullable', Rule::in(['ACTIVE', 'INACTIVE', 'SUSPENDED'])],
            'settings' => ['nullable', 'array'],
        ];
    }
}
