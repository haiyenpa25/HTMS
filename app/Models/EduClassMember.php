<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EduClassMember extends Model
{
    protected $fillable = ['edu_class_id', 'member_id', 'role', 'joined_at'];

    protected $casts = ['joined_at' => 'date'];

    public function eduClass()
    {
        return $this->belongsTo(EduClass::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
