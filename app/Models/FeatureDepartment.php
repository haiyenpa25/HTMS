<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureDepartment extends Model
{
    use HasFactory;

    protected $table = 'feature_department';

    protected $fillable = [
        'feature_id',
        'block_type',   // 'activities', 'ministry', 'leadership'
        'department_id', // nullable (null = all depts in block)
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
