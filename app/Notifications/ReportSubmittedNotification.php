<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportSubmittedNotification extends Notification
{
    use Queueable;

    public $report;
    public $deptName;
    public $reportMonth;
    public $reportYear;

    public function __construct($report, $deptName)
    {
        $this->report = $report;
        $this->deptName = $deptName;
        $this->reportMonth = $report->month;
        $this->reportYear = $report->year;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'report_submitted',
            'title' => "Báo cáo Ban {$this->deptName}",
            'message' => "Báo cáo tháng {$this->reportMonth}/{$this->reportYear} vừa được nộp và đang chờ duyệt.",
            'action_url' => route('portal.reports.index'),
            'icon' => 'document-text', // Vue icon binding
            'color' => 'text-blue-500',
            'bg_color' => 'bg-blue-100',
        ];
    }
}
