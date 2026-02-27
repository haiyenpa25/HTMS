<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Assuming User model is in App\Models namespace

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id', 'requester_type', 'type', 'content', 
        'status', 'approver_id', 'rejection_reason'
    ];

    public function requester()
    {
        return $this->morphTo();
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
