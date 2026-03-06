<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDepartmentFeature extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'feature_id',
        'dept_type',
        'is_enabled',
        'access_level',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    // ── Relationships ───────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}
