<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetLoan extends Model
{
    protected $fillable = [
        'asset_id',
        'borrower_id',
        'department_id',
        'borrowed_at',
        'expected_return_date',
        'returned_at',
        'status',
        'borrow_notes',
        'return_notes',
        'issued_by',
        'received_by'
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'expected_return_date' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
