<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EduClass extends Model
{
    protected $fillable = ['department_id', 'name', 'description', 'is_active', 'class_type'];

    protected $casts = ['is_active' => 'boolean'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /** All members (students + teachers) */
    public function classMembers()
    {
        return $this->hasMany(EduClassMember::class);
    }

    /** Only students */
    public function students()
    {
        return $this->hasMany(EduClassMember::class)->where('role', 'student');
    }

    /** Only teachers */
    public function teachers()
    {
        return $this->hasMany(EduClassMember::class)->where('role', 'teacher');
    }

    /** All Member rows via pivot */
    public function membersList()
    {
        return $this->belongsToMany(Member::class, 'edu_class_members')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function sessions()
    {
        return $this->hasMany(EduSession::class);
    }

    public function funds()
    {
        return $this->hasMany(EduClassFund::class);
    }

    /** Check if a member is a teacher of this class */
    public function hasTeacher(int $memberId): bool
    {
        return $this->classMembers()
            ->where('member_id', $memberId)
            ->where('role', 'teacher')
            ->exists();
    }
}
