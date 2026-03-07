<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_id',
        'target_type', // 'block' or 'department'
        'target_id',   // string block name or int department id
        'is_active',   // true/false
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}
