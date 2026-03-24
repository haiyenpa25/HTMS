<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Member;

class MemberApprovalResultNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Member $member,
        private string $result,
        private ?string $reason = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $approved = $this->result === 'approved';
        return [
            'type'      => 'member_approval_result',
            'title'     => $approved ? "Khách mới đã được duyệt ✓" : "Khách mới bị từ chối",
            'message'   => $approved
                ? "\"{$this->member->full_name}\" đã trở thành tín hữu chính thức."
                : "\"{$this->member->full_name}\" bị từ chối." . ($this->reason ? " Lý do: {$this->reason}" : ''),
            'result'    => $this->result,
            'reason'    => $this->reason,
        ];
    }
}
