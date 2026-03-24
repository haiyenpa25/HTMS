<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Member;
use App\Models\Department;
use App\Models\User;

class PendingMemberNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Member $pendingMember,
        private Department $department,
        private User $submitter
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'        => 'pending_member',
            'title'       => "Có khách mới cần duyệt",
            'message'     => "\"{$this->pendingMember->full_name}\" được {$this->submitter->name} thêm từ {$this->department->name}.",
            'member_id'   => $this->pendingMember->id,
            'dept_id'     => $this->department->id,
            'dept_name'   => $this->department->name,
            'action_url'  => '/users?tab=pending',
        ];
    }
}
