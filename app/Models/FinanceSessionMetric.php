<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceSessionMetric extends Model
{
    protected $guarded = [];

    protected $casts = [
        'period_date' => 'date',
        'attendance_count' => 'integer',
        'tithe_count' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function transactions()
    {
        return $this->hasMany(FinanceTransaction::class, 'session_metrics_id');
    }
}
