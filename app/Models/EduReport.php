<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EduReport extends Model
{
    protected $table = 'edu_reports';

    protected $fillable = [
        'report_month',
        'report_year',
        'reporter_name',
        'status',
        'evaluation',
        'highlights',
        'challenges',
        'request',
        'proposals',
        'activities_notes',
    ];

    /**
     * Lấy báo cáo theo tháng/năm, tạo mới nếu chưa có.
     */
    public static function forMonth(int $month, int $year): ?self
    {
        return self::where('report_month', $month)
                   ->where('report_year', $year)
                   ->first();
    }
}
