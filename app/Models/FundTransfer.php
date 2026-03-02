<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundTransfer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'transfer_date' => 'date',
        'amount' => 'integer',
    ];

    public function fromFund()
    {
        return $this->belongsTo(FinanceFund::class, 'from_fund_id');
    }

    public function toFund()
    {
        return $this->belongsTo(FinanceFund::class, 'to_fund_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
