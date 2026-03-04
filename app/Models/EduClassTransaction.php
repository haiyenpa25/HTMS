<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EduClassTransaction extends Model
{
    protected $fillable = [
        'edu_class_fund_id', 'edu_session_id', 'type',
        'amount', 'category', 'description', 'transaction_date', 'status',
    ];

    protected $casts = ['transaction_date' => 'date'];

    public function fund()
    {
        return $this->belongsTo(EduClassFund::class, 'edu_class_fund_id');
    }

    public function session()
    {
        return $this->belongsTo(EduSession::class, 'edu_session_id');
    }
}
