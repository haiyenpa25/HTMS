<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'block', 'parent_id', 'code', 'description', 'is_active', 'feature_keys'];

    protected $casts = [
        'is_active' => 'boolean',
        'feature_keys' => 'array',
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
}
