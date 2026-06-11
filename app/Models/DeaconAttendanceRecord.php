<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeaconAttendanceRecord extends Model
{
    protected $fillable = [
        'meeting_id', 'recorded_by',
        // Điểm danh tổng (Thư Ký đếm thực tế — bao gồm cả khách)
        'total_present', 'total_male', 'total_female',
        'total_online', 'total_children', 'guests_count',
        // Điểm danh từng ban (ban trưởng báo — JSON: {"dept_id": count})
        'dept_breakdown',
        'notes', 'incident_note', 'youtube_live_count', 'recorded_at',
    ];

    protected $casts = [
        'dept_breakdown' => 'array',
        'recorded_at'    => 'datetime',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Tổng điểm danh từ các ban báo lên
     */
    public function getDeptTotalAttribute(): int
    {
        if (!$this->dept_breakdown) return 0;
        return array_sum($this->dept_breakdown);
    }

    /**
     * Chênh lệch giữa tổng thực tế và tổng từ ban (thường là khách + người không thuộc ban nào)
     */
    public function getUnaccountedAttribute(): int
    {
        return max(0, $this->total_present - $this->dept_total);
    }
}
