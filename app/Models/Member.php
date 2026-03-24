<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\OrgRole;
use App\Models\Department;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\ChronicleEntry;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Member extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('member_management')
            ->setDescriptionForEvent(fn(string $eventName) => "Hồ sơ tín hữu đã được {$eventName}");
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Chính thức');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    protected $fillable = [
        'user_id', 'household_id', 'member_code', 'full_name', 'email', 'phone', 
        'address', 'visit_location', 'latitude', 'longitude', 'date_of_birth', 'gender', 'member_type',
        'faith_date', 'is_baptized', 'baptism_date', 'joined_date',
        'attendance_status', 'status', 'general_notes',
        'pending_dept_id', 'submitted_by_user_id',
    ];

    protected $appends = ['marital_status'];

    protected $casts = [
        'is_baptized' => 'boolean',
        'date_of_birth' => 'date',
        'faith_date' => 'date',
        'baptism_date' => 'date',
        'joined_date' => 'date',
    ];

    public function getMaritalStatusAttribute()
    {
        return $this->sensitiveInfo->marital_status ?? null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function sensitiveInfo()
    {
        return $this->hasOne(MemberSensitive::class);
    }

    public function careLogs()
    {
        return $this->hasMany(CareLog::class);
    }

    public function visitations()
    {
        return $this->hasMany(Visitation::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'member_courses')->withPivot('status', 'completion_date');
    }

    public function talents()
    {
        return $this->belongsToMany(Talent::class, 'member_talents')->withPivot('notes');
    }

    public function relatedTo()
    {
        return $this->belongsToMany(Member::class, 'relationships', 'member_id', 'related_member_id')->withPivot('type');
    }

    public function relatedFrom()
    {
        return $this->belongsToMany(Member::class, 'relationships', 'related_member_id', 'member_id')->withPivot('type');
    }


    public function departments()
    {
        return $this->belongsToMany(Department::class, 'org_memberships', 'member_id', 'model_id')
                    ->wherePivot('model_type', Department::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'org_memberships', 'member_id', 'model_id')
                    ->wherePivot('model_type', Team::class);
    }

    public function memberships()
    {
        return $this->hasMany(OrgMembership::class);
    }

    public function attendances()
    {
        return $this->hasMany(MeetingAttendance::class);
    }

    /**
     * Get all chronicle entries explicitly related to this member profile.
     */
    public function chronicles(): MorphMany
    {
        return $this->morphMany(ChronicleEntry::class, 'subject');
    }

    public function hasOrgRoleIn($departmentId, array $roles)
    {
        $roleCodes = array_map(function($r) {
            $map = [
                'TruongBan' => 'tb',
                'PhoBan' => 'pb',
                'ThuKy' => 'tk',
                'ThuQuy' => 'tq',
                'UyVien' => 'uv',
                'Member' => 'bv'
            ];
            return $map[$r] ?? $r;
        }, $roles);

        $roleIds = OrgRole::whereIn('code', $roleCodes)->pluck('id');

        return $this->memberships()
            ->where('model_type', Department::class)
            ->where('model_id', $departmentId)
            ->whereIn('org_role_id', $roleIds)
            ->exists();
    }

    public function faithJourneys()
    {
        return $this->hasMany(FaithJourney::class)->orderBy('event_date', 'asc');
    }
}
