<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visitor extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'phone',
        'email',
        'birth_date',
        'address',
        'first_visit_date',
        'invited_by',
        'prayer_requests',
        'status',
        'assigned_to'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'first_visit_date' => 'date',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function followups()
    {
        return $this->hasMany(VisitorFollowup::class);
    }
}
