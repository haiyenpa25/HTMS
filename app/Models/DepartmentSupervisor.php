<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department;
use App\Models\Member;

class DepartmentSupervisor extends Model
{
    use HasFactory;

    protected $fillable = ['department_id', 'member_id'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
