<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EduSessionRecord extends Model
{
    protected $fillable = [
        'edu_session_id', 'member_id',
        'attendance', 'memorized_verse', 'quiz_score',
    ];

    protected $casts = ['memorized_verse' => 'boolean'];

    public function session()
    {
        return $this->belongsTo(EduSession::class, 'edu_session_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
