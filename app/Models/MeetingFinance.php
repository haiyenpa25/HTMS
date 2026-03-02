<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingFinance extends Model
{
    protected $fillable = [
        'meeting_id', 'amount', 'type', 'category', 'status'
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * Scope a query to only include approved finances.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
