<?php

namespace MunicipalSaas\Shared\Application\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final readonly class AuditLogger
{
    /**
     * @param array<string, mixed>|null $oldValue
     * @param array<string, mixed>|null $newValue
     */
    public function record(
        Request $request,
        string $action,
        string $entity,
        ?string $entityId = null,
        ?array $oldValue = null,
        ?array $newValue = null,
    ): void {
        DB::table('audit_logs')->insert([
            'municipality_id' => $request->attributes->get('tenant_id'),
            'user_id' => $request->user()?->id,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'old_value' => $oldValue ? json_encode($oldValue) : null,
            'new_value' => $newValue ? json_encode($newValue) : null,
            'ip' => $request->ip(),
            'created_at' => now(),
        ]);
    }
}
