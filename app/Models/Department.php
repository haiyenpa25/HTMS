<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Department extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('department_management')
            ->setDescriptionForEvent(fn(string $eventName) => "Ban ngành đã được {$eventName}");
    }

    protected $fillable = ['name', 'block', 'parent_id', 'code', 'description', 'is_active', 'feature_keys', 'available_features'];

    public static function cachedAll()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('system_departments_model', function () {
            return static::all();
        });
    }

    protected static function booted()
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('system_departments_model'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('system_departments_model'));
    }

    protected $casts = [
        'is_active' => 'boolean',
        'feature_keys' => 'array',
        'available_features' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function members()
    {
        return $this->belongsToMany(Member::class, 'org_memberships', 'model_id', 'member_id')
                    ->wherePivot('model_type', Department::class);
    }

    public function supervisors()
    {
        return $this->belongsToMany(Member::class, 'department_supervisors');
    }

    public function supervisedBy()
    {
        return $this->belongsTo(Member::class, 'supervisor_id');
    }

    public function dutyRoles()
    {
        return $this->hasMany(DepartmentRole::class);
    }

    public function rosterTemplates()
    {
        return $this->hasMany(RosterTemplate::class);
    }

    public function chronicles()
    {
        return $this->hasMany(ChronicleEntry::class);
    }
}
