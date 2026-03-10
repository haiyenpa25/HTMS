<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RosterTemplateRole extends Model
{
    protected $fillable = ['roster_template_id', 'department_role_id'];

    public function template()
    {
        return $this->belongsTo(RosterTemplate::class, 'roster_template_id');
    }

    public function departmentRole()
    {
        return $this->belongsTo(DepartmentRole::class, 'department_role_id');
    }
}
