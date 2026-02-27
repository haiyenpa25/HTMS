<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['department_id', 'name', 'code', 'description', 'is_active'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function members()
    {
        return $this->belongsToMany(Member::class, 'org_memberships', 'model_id', 'member_id')
                    ->wherePivot('model_type', Team::class);
    }
}
