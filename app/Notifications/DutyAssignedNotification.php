<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DutyAssignedNotification extends Notification
{
    use Queueable;

    public $meeting;
    public $roleName;
    public $dateStr;

    public function __construct($meeting, $roleName)
    {
        $this->meeting = $meeting;
        $this->roleName = $roleName;
        $this->dateStr = $meeting->meeting_date->format('d/m/Y');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'duty_assigned',
            'title' => 'Bạn được phân công trực',
            'message' => "Vai trò {$this->roleName} vào buổi nhóm ngày {$this->dateStr} ({$this->meeting->department->name}).",
            'action_url' => route('duty-rooster.show', $this->meeting->id),
            'icon' => 'calendar',
            'color' => 'text-emerald-500',
            'bg_color' => 'bg-emerald-100',
        ];
    }
}
