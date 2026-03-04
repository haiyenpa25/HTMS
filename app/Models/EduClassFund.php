<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EduClassFund extends Model
{
    protected $fillable = ['edu_class_id', 'name', 'description'];

    public function eduClass()
    {
        return $this->belongsTo(EduClass::class);
    }

    public function transactions()
    {
        return $this->hasMany(EduClassTransaction::class);
    }

    /** Current balance: sum income - sum expense (approved only) */
    public function getBalanceAttribute(): int
    {
        $income  = $this->transactions()->where('type', 'income')->where('status', 'approved')->sum('amount');
        $expense = $this->transactions()->where('type', 'expense')->where('status', 'approved')->sum('amount');
        return $income - $expense;
    }
}
