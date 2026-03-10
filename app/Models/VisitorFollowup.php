<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorFollowup extends Model
{
    protected $fillable = [
        'visitor_id',
        'user_id',
        'type',
        'contact_date',
        'notes',
        'outcome'
    ];

    protected $casts = [
        'contact_date' => 'date',
    ];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
