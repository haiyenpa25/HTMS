<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberContribution extends Model
{
    protected $guarded = [];

    protected $casts = [
        'people_count' => 'integer',
        'amount' => 'integer',
    ];

    public function transaction()
    {
        return $this->belongsTo(FinanceTransaction::class, 'transaction_id');
    }
}
