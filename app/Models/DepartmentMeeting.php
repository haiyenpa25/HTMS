<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentMeeting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meeting_date'         => 'date',
        'attendance_morning'   => 'integer',
        'attendance_afternoon' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function transactions()
    {
        return $this->hasMany(DepartmentTransaction::class, 'department_meeting_id');
    }

    /** Total attendance = max(morning, afternoon) or sum depending on logic */
    public function getTotalAttendanceAttribute(): int
    {
        return max($this->attendance_morning, $this->attendance_afternoon);
    }

    /** Session income (approved) */
    public function getSessionIncomeAttribute(): int
    {
        return $this->transactions()->where('type', 'income')->where('status', 'approved')->sum('amount');
    }

    /** Session expense (approved) */
    public function getSessionExpenseAttribute(): int
    {
        return $this->transactions()->where('type', 'expense')->where('status', 'approved')->sum('amount');
    }

    /** Tồn tuần = Thu - Chi */
    public function getSessionBalanceAttribute(): int
    {
        return $this->session_income - $this->session_expense;
    }
}
