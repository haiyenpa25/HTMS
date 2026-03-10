<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailBroadcast extends Model
{
    protected $fillable = [
        'subject',
        'content',
        'target_roles',
        'target_departments',
        'status',
        'total_recipients',
        'success_count',
        'failed_count',
        'created_by',
        'sent_at'
    ];

    protected $casts = [
        'target_roles' => 'array',
        'target_departments' => 'array',
        'sent_at' => 'datetime'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
