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
        return $user->isSuperAdmin() || $user->hasPermissionTo('view_finance') || $user->hasPermissionTo('create_finance');
    }

    public function view(User $user, ?FinanceTransaction $financeTransaction = null): bool
    {
        if ($user->isSuperAdmin()) return true;
        
        if (!$user->hasPermissionTo('view_finance') && !$user->hasPermissionTo('create_finance')) {
            return false;
        }

        if (!$financeTransaction) return true;

        $fund = $financeTransaction->fund;
        if ($fund && $fund->owner_type === 'department') {
            return session('active_finance_dept_id') == $fund->owner_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Debugging Spatie
        dd($user->getAllPermissions()->toArray());
        
        return $user->isSuperAdmin() || $user->hasPermissionTo('create_finance');
    }

    public function update(User $user, ?FinanceTransaction $financeTransaction = null): bool
    {
        if ($user->isSuperAdmin()) return true;
        
        if (!$user->hasPermissionTo('create_finance')) {
            return false;
        }

        if (!$financeTransaction) return true;
        
        if ($financeTransaction->status === 'approved' && !$user->hasPermissionTo('approve_finance')) {
            return false;
        }

        $fund = $financeTransaction->fund;
        if ($fund && $fund->owner_type === 'department') {
            return session('active_finance_dept_id') == $fund->owner_id;
        }

        return false;
    }

    public function delete(User $user, ?FinanceTransaction $financeTransaction = null): bool
    {
         return $this->update($user, $financeTransaction);
    }
    
    public function approve(User $user, ?FinanceTransaction $financeTransaction = null): bool
    {
        if ($user->isSuperAdmin()) return true;
        
        if (!$user->hasPermissionTo('approve_finance')) {
            return false;
        }

        if (!$financeTransaction) return true;

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
        return $this->delete($user, $financeTransaction);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ?FinanceTransaction $financeTransaction = null): bool
    {
        return $this->delete($user, $financeTransaction);
    }
}
