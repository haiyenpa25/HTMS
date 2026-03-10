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
        'block_type',    // 'activities', 'ministry', 'leadership', or null (global)
        'department_id', // null = all depts in block or global
        'scope',         // 'global', 'block', 'specific'
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function cachedAll()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('system_feature_assignments', function () {
            return static::with('feature', 'department')->get();
        });
    }

    protected static function booted()
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('system_feature_assignments'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('system_feature_assignments'));
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
