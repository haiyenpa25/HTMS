<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    protected $fillable = ['name', 'address', 'notes', 'head_member_id'];

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function head()
    {
        return $this->belongsTo(Member::class, 'head_member_id');
    }
}
