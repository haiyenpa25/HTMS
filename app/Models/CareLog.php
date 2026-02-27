<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareLog extends Model
{
    protected $fillable = ['member_id', 'caregiver_id', 'visit_date', 'summary', 'notes', 'is_sensitive'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function caregiver()
    {
        return $this->belongsTo(Member::class, 'caregiver_id');
    }
}
