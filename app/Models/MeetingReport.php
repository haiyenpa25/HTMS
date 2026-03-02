<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingReport extends Model
{
    protected $fillable = [
        'meeting_id', 'total_attendance', 'department_attendances',
        'total_members_present', 'bible_quiz_correct_count', 'memory_verse_memorized_count'
    ];

    protected $casts = [
        'department_attendances' => 'array',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
