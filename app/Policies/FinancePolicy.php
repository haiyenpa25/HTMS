<?php

namespace App\Policies;

use App\Models\FinanceTransaction;
use App\Models\FundTransfer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FinancePolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user = null): bool
    {
        return $user?->hasPermissionTo('view_finance') ?? false;
    }

    public function view(User $user, ?FinanceTransaction $transaction = null): bool
    {
        return $user->hasPermissionTo('view_finance');
    }

    public function create(?User $user = null): bool
    {
        return $user?->hasPermissionTo('create_finance') ?? false;
    }

    public function update(User $user, ?FinanceTransaction $transaction = null): bool
    {
        return $user->hasPermissionTo('manage_finance');
    }

    public function delete(User $user, ?FinanceTransaction $transaction = null): bool
    {
        return $user->hasPermissionTo('manage_finance');
    }

    public function approve(User $user, ?FinanceTransaction $transaction = null): bool
    {
        return $user->hasPermissionTo('approve_finance');
    }

    public function transfer(User $user, ?FundTransfer $transfer = null): bool
    {
        return $user->hasPermissionTo('manage_finance');
    }

    public function report(?User $user = null): bool
    {
        return $user?->hasPermissionTo('report_finance') ?? false;
    }
}
