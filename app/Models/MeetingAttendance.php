<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingAttendance extends Model
{
    protected $fillable = [
        'meeting_id',
        'member_id',
        'status', // 'present', 'absent', 'excused'
        'memorized_verse',
        'quiz_score'
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
