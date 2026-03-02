<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingPersonnel extends Model
{
    protected $fillable = [
        'meeting_id', 'member_id', 'role_name'
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
