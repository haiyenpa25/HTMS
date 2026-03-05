<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeaconMonthlyReport extends Model
{
    protected $fillable = [
        'report_month', 'report_year',
        'yt_subscribers', 'yt_new_subscribers', 'yt_views', 'yt_watch_hours',
        'announcements', 'summary_notes', 'status', 'submitted_by',
    ];

    public function incidents()
    {
        return $this->hasMany(DeaconReportIncident::class, 'deacon_report_id');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
