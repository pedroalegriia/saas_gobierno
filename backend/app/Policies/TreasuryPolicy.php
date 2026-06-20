<?php

namespace App\Policies;

use App\Models\User;
use MunicipalSaas\Tenants\Application\DTO\MunicipalityData;

final class TreasuryPolicy
{
    public function viewDashboard(User $user, MunicipalityData $tenant): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        return in_array($user->role, ['treasury', 'auditor'], true)
            && (int) $user->municipality_id === $tenant->id
            && $user->status === 'ACTIVE';
    }

    public function generateCaptureLine(User $user, MunicipalityData $tenant): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        return in_array($user->role, ['treasury', 'cashier'], true)
            && (int) $user->municipality_id === $tenant->id
            && $user->status === 'ACTIVE';
    }

    public function exportReports(User $user, MunicipalityData $tenant): bool
    {
        return $this->viewDashboard($user, $tenant);
    }
}
