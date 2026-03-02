<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentReport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'upcoming_plan' => 'array',
        'report_month'  => 'integer',
        'report_year'   => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        return "Tháng {$this->report_month}/{$this->report_year}";
    }
}
