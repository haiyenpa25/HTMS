<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'integer',
    ];

    public function fund()
    {
        return $this->belongsTo(DepartmentFund::class, 'department_fund_id');
    }

    public function meeting()
    {
        return $this->belongsTo(DepartmentMeeting::class, 'department_meeting_id');
    }
}
