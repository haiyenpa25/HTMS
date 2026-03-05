<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeaconReportIncident extends Model
{
    protected $fillable = [
        'deacon_report_id', 'week_label',
        'incident_description', 'resolution', 'direction', 'status',
    ];

    public function report()
    {
        return $this->belongsTo(DeaconMonthlyReport::class, 'deacon_report_id');
    }
}
