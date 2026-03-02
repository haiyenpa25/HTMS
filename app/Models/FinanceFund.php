<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceFund extends Model
{
    protected $guarded = [];

    protected $fillable = ['name', 'description', 'owner_id', 'owner_type'];

    public function owner()
    {
        return $this->morphTo();
    }

    public function transactions()
    {
        return $this->hasMany(FinanceTransaction::class, 'fund_id');
    }

    public function transfersOut()
    {
        return $this->hasMany(FundTransfer::class, 'from_fund_id');
    }

    public function transfersIn()
    {
        return $this->hasMany(FundTransfer::class, 'to_fund_id');
    }

    // Calculate current real balance
    public function getBalanceAttribute(): int
    {
        $income = $this->transactions()->where('type', 'income')->where('status', 'approved')->sum('amount');
        $expense = $this->transactions()->where('type', 'expense')->where('status', 'approved')->sum('amount');
        $transfersOut = $this->transfersOut()->where('status', 'approved')->sum('amount');
        $transfersIn = $this->transfersIn()->where('status', 'approved')->sum('amount');
        return $income - $expense - $transfersOut + $transfersIn;
    }
}
