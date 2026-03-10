<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentRole extends Model
{
    protected $fillable = ['department_id', 'name', 'section', 'sort_order', 'max_count', 'is_active'];


    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignments()
    {
        return $this->hasMany(DutyAssignment::class);
    }
}
