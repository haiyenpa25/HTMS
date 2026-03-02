<?php

namespace App\Policies;

use App\Models\FinanceTransaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FinanceTransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_finance');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ?FinanceTransaction $financeTransaction = null): bool
    {
        if (!$user->hasPermissionTo('view_finance')) {
            return false;
        }

        if (!$financeTransaction) {
            return true;
        }

        if ($user->hasRole(['Super_Admin', 'Pastor'])) {
            return true;
        }

        // Must own the fund
        $fund = $financeTransaction->fund;
        if ($fund && $fund->owner_type === 'department') {
            return session('active_finance_dept_id') == $fund->owner_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_finance');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ?FinanceTransaction $financeTransaction = null): bool
    {
        if (!$user->hasPermissionTo('create_finance') && !$user->hasPermissionTo('manage_finance')) {
            return false;
        }

        if (!$financeTransaction) {
            return true;
        }
        
        // Cannot edit approved transactions unless you are an admin
        if ($financeTransaction->status === 'approved' && !$user->hasPermissionTo('approve_finance')) {
            return false;
        }

        if ($user->hasRole(['Super_Admin', 'Pastor'])) {
            return true;
        }

        $fund = $financeTransaction->fund;
        if ($fund && $fund->owner_type === 'department') {
            return session('active_finance_dept_id') == $fund->owner_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ?FinanceTransaction $financeTransaction = null): bool
    {
         // Same rules as update
         return $this->update($user, $financeTransaction);
    }
    
    public function approve(User $user, ?FinanceTransaction $financeTransaction = null): bool
    {
        if (!$user->hasPermissionTo('approve_finance')) {
            return false;
        }

        if (!$financeTransaction) {
            return true;
        }

        if ($user->hasRole(['Super_Admin', 'Pastor'])) {
            return true;
        }

        $fund = $financeTransaction->fund;
        if ($fund && $fund->owner_type === 'department') {
            return session('active_finance_dept_id') == $fund->owner_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ?FinanceTransaction $financeTransaction = null): bool
    {
        return clone $this->delete($user, $financeTransaction);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ?FinanceTransaction $financeTransaction = null): bool
    {
        return clone $this->delete($user, $financeTransaction);
    }
}
