<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EduSession extends Model
{
    protected $fillable = [
        'edu_class_id', 'session_date', 'lesson_number', 'lesson_series',
        'topic', 'scripture', 'notes',
        'attendance_mode', 'total_present', 'total_absent',
        'teacher_id',
        // Bible quiz fields
        'book', 'total_questions', 'grader_id', 'photo_path',
    ];

    protected $casts = ['session_date' => 'date'];

    public function eduClass()
    {
        return $this->belongsTo(EduClass::class);
    }

    public function records()
    {
        return $this->hasMany(EduSessionRecord::class);
    }

    public function transactions()
    {
        return $this->hasMany(EduClassTransaction::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Member::class, 'teacher_id');
    }

    public function grader()
    {
        return $this->belongsTo(Member::class, 'grader_id');
    }
}
