<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'household_id', 'member_code', 'full_name', 'email', 'phone', 
        'address', 'visit_location', 'date_of_birth', 'gender', 'member_type',
        'faith_date', 'is_baptized', 'baptism_date', 'joined_date',
        'attendance_status', 'status', 'general_notes'
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
}
