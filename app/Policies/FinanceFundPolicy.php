<?php

namespace App\Policies;

use App\Models\FinanceFund;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FinanceFundPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ?FinanceFund $financeFund = null): bool
    {
        if (!$user->isSuperAdmin()) {
            return false;
        }

        if (!$financeFund) {
            return true;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($financeFund->owner_type === 'department') {
            return session('active_finance_dept_id') == $financeFund->owner_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ?FinanceFund $financeFund = null): bool
    {
        if (!$user->isSuperAdmin() && (!$user->isSuperAdmin())) {
            return false;
        }

        if (!$financeFund) {
            return true;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($financeFund->owner_type === 'department') {
            return session('active_finance_dept_id') == $financeFund->owner_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ?FinanceFund $financeFund = null): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ?FinanceFund $financeFund = null): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ?FinanceFund $financeFund = null): bool
    {
        return false;
    }
}
