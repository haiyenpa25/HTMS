<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecretaryMonthlyNote extends Model
{
    protected $fillable = [
        'month', 'year',
        'announcements', 'next_plan',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'month' => 'integer',
        'year'  => 'integer',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Lấy hoặc tạo ghi chú tháng
     */
    public static function findOrEmpty(int $month, int $year): self
    {
        return static::firstOrNew(['month' => $month, 'year' => $year]);
    }
}
