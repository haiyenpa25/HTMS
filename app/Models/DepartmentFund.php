<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentFund extends Model
{
    protected $guarded = [];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function transactions()
    {
        return $this->hasMany(DepartmentTransaction::class, 'department_fund_id');
    }

    /**
     * Calculate real-time balance: sum(income approved) - sum(expense approved)
     */
    public function getBalanceAttribute(): int
    {
        $income  = $this->transactions()->where('type', 'income')->where('status', 'approved')->sum('amount');
        $expense = $this->transactions()->where('type', 'expense')->where('status', 'approved')->sum('amount');
        return $income - $expense;
    }
}
