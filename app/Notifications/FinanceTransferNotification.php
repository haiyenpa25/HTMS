<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FinanceTransferNotification extends Notification
{
    use Queueable;

    public $transfer;
    public $amount;
    public $fromFundName;

    public function __construct($transfer, $fromFundName)
    {
        $this->transfer = $transfer;
        $this->amount = number_format($transfer->amount, 0, ',', '.');
        $this->fromFundName = $fromFundName;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'finance_transfer',
            'title' => 'Yêu cầu chuyển quỹ',
            'message' => "Bạn nhận được yêu cầu nhập {$this->amount}đ từ quỹ {$this->fromFundName}. Vui lòng xác nhận.",
            'action_url' => route('finance.transactions.index'),
            'icon' => 'currency-dollar',
            'color' => 'text-amber-500',
            'bg_color' => 'bg-amber-100',
        ];
    }
}
