<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DutyAssignment extends Model
{
    protected $fillable = ['meeting_id', 'department_role_id', 'slot', 'member_id', 'notes'];


    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function role()
    {
        return $this->belongsTo(DepartmentRole::class, 'department_role_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
