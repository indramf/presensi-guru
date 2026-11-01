<?php

namespace App\Policies;

use App\Models\SecurityAlert;
use App\Models\User;

class SecurityAlertPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('security_alerts.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SecurityAlert $securityAlert): bool
    {
        return $user->hasPermissionTo('security_alerts.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SecurityAlert $securityAlert): bool
    {
        return $user->hasPermissionTo('security_alerts.review');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SecurityAlert $securityAlert): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SecurityAlert $securityAlert): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SecurityAlert $securityAlert): bool
    {
        return false;
    }
}
