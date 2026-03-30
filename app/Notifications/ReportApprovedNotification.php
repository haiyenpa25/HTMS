<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\DepartmentReport;

class ReportApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly DepartmentReport $report,
        public readonly string $departmentName,
        public readonly string $period,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'report_approved',
            'title'           => '✅ Báo cáo đã được duyệt',
            'message'         => "Báo cáo {$this->period} của ban {$this->departmentName} đã được phê duyệt.",
            'report_id'       => $this->report->id,
            'department_name' => $this->departmentName,
            'period'          => $this->period,
        ];
    }
}
