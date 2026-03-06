<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeaconMonthlyReport extends Model
{
    protected $fillable = [
        'report_month', 'report_year',
        // YouTube stats
        'yt_subscribers', 'yt_new_subscribers', 'yt_views', 'yt_watch_hours',
        // Text fields (legacy names preserved for DB compat)
        'announcements', 'summary_notes',
        // New fields (reporter, evaluation, plans)
        'reporter_name', 'evaluation', 'proposals', 'notes',
        // Meta
        'status', 'submitted_by', 'unlock_requested',
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
