<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'type', 'department_id', 'date', 'time',
        'topic', 'memory_verse', 'quiz_passage', 'scripture', 'preacher', 'speaker_id',
        'attendance_marked',
    ];

    public function attendanceRecord()
    {
        return $this->hasOne(DeaconAttendanceRecord::class);
    }

    public function speaker()
    {
        return $this->belongsTo(Speaker::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function personnel()
    {
        return $this->hasMany(MeetingPersonnel::class);
    }

    public function report()
    {
        return $this->hasOne(MeetingReport::class);
    }

    public function finances()
    {
        return $this->hasMany(MeetingFinance::class);
    }

    public function attendanceSummaries()
    {
        return $this->hasMany(MeetingAttendanceSummary::class);
    }

    public function attendances()
    {
        return $this->hasMany(MeetingAttendance::class);
    }

    /**
     * Scope a query to only include meetings accessible by the given user.
     */
    public function scopeAccessibleBy($query, $user)
    {
        if ($user->hasRole(['BTS_Admin', 'Pastor', 'Super_Admin'])) {
            return $query; // can see all
        }

        // Leader and Members can see their own department's meetings
        return $query->where(function($q) use ($user) {
            $q->whereNull('department_id'); // Allow viewing church-wide meetings if needed, or maybe restrict? Assuming church meetings might be public.
            
            if ($user->member) {
                $q->orWhereIn('department_id', function ($sub) use ($user) {
                    $sub->select('model_id')
                        ->from('org_memberships')
                        ->where('model_type', Department::class)
                        ->where('member_id', $user->member->id);
                });
            }
        });
    }
}

