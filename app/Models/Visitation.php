<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visitation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'visitation_type',
        'department_id',
        'visit_date',
        'reason',
        'content',
        'prayer_points',
        'gifts',
        'status',
        'priority',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function visitors()
    {
        return $this->belongsToMany(Member::class, 'visitation_visitors', 'visitation_id', 'visitor_id');
    }
}
