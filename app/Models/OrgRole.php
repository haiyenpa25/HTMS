<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgRole extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'level'];

    public function memberships()
    {
        return $this->hasMany(OrgMembership::class);
    }
}
