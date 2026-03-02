<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceTransaction extends Model
{
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
