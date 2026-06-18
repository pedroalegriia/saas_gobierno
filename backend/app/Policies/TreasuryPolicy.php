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

        return $user->role === 'treasury'
            && (int) $user->municipality_id === $tenant->id
            && $user->status === 'ACTIVE';
    }
}
