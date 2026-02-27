<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Church extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone_number', 'email', 'logo_path', 'settings'];

    protected $casts = [
        'settings' => 'array',
    ];
}
