<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Donation extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'fund_id',
        'user_id',
        'type',
        'amount',
        'donation_date',
        'payment_method',
        'reference_number',
        'notes',
        'recorded_by'
    ];

    protected $casts = [
        'donation_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class); // The giver
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by'); // The treasurer
    }
}
