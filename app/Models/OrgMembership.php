<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Member;
use App\Models\OrgRole;

class OrgMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id', 'org_role_id', 'model_id', 'model_type', 
        'join_date', 'leave_date', 'is_active', 'permissions'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permissions' => 'array',
        'join_date' => 'date',
        'leave_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function role()
    {
        return $this->belongsTo(OrgRole::class, 'org_role_id');
    }

    public function model()
    {
        return $this->morphTo();
    }
}
