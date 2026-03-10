<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FinanceTransaction extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('finance_transaction')
            ->setDescriptionForEvent(fn(string $eventName) => "Giao dịch tài chính đã được {$eventName}");
    }

    protected $guarded = [];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function fund()
    {
        return $this->belongsTo(FinanceFund::class, 'fund_id');
    }

    public function sessionMetric()
    {
        return $this->belongsTo(FinanceSessionMetric::class, 'session_metrics_id');
    }

    public function contributions()
    {
        return $this->hasMany(MemberContribution::class, 'transaction_id');
    }
}
