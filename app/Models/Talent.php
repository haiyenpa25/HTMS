<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Talent extends Model
{
    protected $table = 'talents';
    protected $fillable = ['name'];

    public function members()
    {
        return $this->belongsToMany(Member::class, 'member_talents')->withPivot('notes');
    }
}
