<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingAttendanceSummary extends Model
{
    protected $fillable = [
        'meeting_id',
        'department_id',
        'manual_count',
        'notes',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
