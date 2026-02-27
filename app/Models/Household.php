<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    protected $fillable = ['name', 'address', 'notes'];

    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
