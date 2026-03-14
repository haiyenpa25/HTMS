<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaithJourney extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id', 'event_date', 'event_type', 'description', 'related_person_or_church'
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
