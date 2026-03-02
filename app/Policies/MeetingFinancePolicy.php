<?php

namespace App\Policies;

use App\Models\MeetingFinance;
use App\Models\User;

class MeetingFinancePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MeetingFinance $meetingFinance): bool
    {
        return $user->can('view', $meetingFinance->meeting);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MeetingFinance $meetingFinance): bool
    {
        return $user->can('update', $meetingFinance->meeting);
    }

    public function delete(User $user, MeetingFinance $meetingFinance): bool
    {
        return $user->can('delete', $meetingFinance->meeting);
    }

    /**
     * Determine whether the user can approve the finance record.
     */
    public function approve(User $user, MeetingFinance $meetingFinance): bool
    {
        return $user->hasRole('Pastor');
    }
}
