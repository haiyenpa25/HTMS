<?php

namespace App\Policies;

use App\Models\Speaker;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SpeakerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_speakers'); // Assumes spatie permission or basic true if authenticated
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Speaker $speaker): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_speakers');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Speaker $speaker): bool
    {
        return $user->hasPermissionTo('manage_speakers');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Speaker $speaker): bool
    {
        return $user->hasPermissionTo('manage_speakers');
    }

    /**
     * Determine whether the user can view sensitive info like phone.
     */
    public function viewPhone(User $user, ?Speaker $speaker = null): bool
    {
        return $user->hasRole(['Pastor', 'Thư ký', 'Super_Admin']);
    }
}

