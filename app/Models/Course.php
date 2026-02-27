<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['name', 'description'];

    public function members()
    {
        return $this->belongsToMany(Member::class, 'member_courses')->withPivot('status', 'completion_date');
    }
}
