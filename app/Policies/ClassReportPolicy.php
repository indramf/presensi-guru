<?php

namespace App\Policies;

use App\Models\ClassReport;
use App\Models\User;

class ClassReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('class_reports.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClassReport $classReport): bool
    {
        return $user->hasPermissionTo('class_reports.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('class_reports.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClassReport $classReport): bool
    {
        return $user->hasPermissionTo('class_reports.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClassReport $classReport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ClassReport $classReport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ClassReport $classReport): bool
    {
        return false;
    }
}
