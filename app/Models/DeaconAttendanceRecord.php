<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeaconAttendanceRecord extends Model
{
    protected $fillable = [
        'meeting_id', 'recorded_by',
        'total_present', 'total_online', 'total_children', 'total_visitors',
        'notes',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
