<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberSensitive extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id', 'id_card_number', 'financial_notes', 'health_notes', 
        'background_notes', 'prayer_concerns', 'pastoral_notes', 
        'occupation', 'marital_status'
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
