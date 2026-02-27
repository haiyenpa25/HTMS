<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Member;
use Illuminate\Auth\Access\HandlesAuthorization;

class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Kiểm tra quyền xem thông tin của $targetMember
     */
    public function view(User $user, Member $targetMember)
    {
        return $user->canViewMember($targetMember);
    }
}
