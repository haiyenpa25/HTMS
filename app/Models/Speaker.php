<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speaker extends Model
{
    /** @use HasFactory<\Database\Factories\SpeakerFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'full_name',
        'phone',
        'birth_year',
        'managed_church',
        'is_external',
        'member_id'
    ];

    protected $casts = [
        'is_external' => 'boolean',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }

    /**
     * Get preaching history: list of meetings this speaker has taught at.
     */
    public function getPreachingHistory()
    {
        return $this->meetings()
            ->with('department')
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();
    }
}
