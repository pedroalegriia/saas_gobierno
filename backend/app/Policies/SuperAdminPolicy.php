<?php

namespace App\Policies;

use App\Models\User;

final class SuperAdminPolicy
{
    public function manage(User $user): bool
    {
        return $user->role === 'super_admin' && $user->status === 'ACTIVE';
    }
}
