<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DeaconTermAssignment extends Model
{
    protected $fillable = [
        'deacon_id', 'department_id',
        'term_from', 'term_to', 'term_label',
        'notes', 'assigned_by',
    ];

    protected $casts = [
        'term_from' => 'integer',
        'term_to'   => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────
    public function deacon()
    {
        return $this->belongsTo(Member::class, 'deacon_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // ── Scopes ─────────────────────────────────────────────────

    /**
     * Nhiệm kỳ hiện tại: term_from <= currentYear <= term_to
     */
    public function scopeCurrentTerm(Builder $query): Builder
    {
        $currentYear = (int) ChurchSetting::get('current_term_year', date('Y'));
        return $query->where('term_from', '<=', $currentYear)
                     ->where('term_to', '>=', $currentYear);
    }

    /**
     * Lọc theo nhiệm kỳ cụ thể (term_from năm)
     */
    public function scopeForTerm(Builder $query, int $termFrom): Builder
    {
        return $query->where('term_from', $termFrom);
    }

    /**
     * Danh sách chấp sự của 1 ban trong nhiệm kỳ hiện tại
     */
    public static function getCurrentDeaconForDept(int $deptId): ?self
    {
        return static::currentTerm()
            ->where('department_id', $deptId)
            ->with('deacon')
            ->first();
    }

    /**
     * Danh sách ban mà 1 Chấp Sự đang phụ trách (nhiệm kỳ hiện tại)
     */
    public static function getDeptsForDeacon(int $memberId): \Illuminate\Database\Eloquent\Collection
    {
        return static::currentTerm()
            ->where('deacon_id', $memberId)
            ->with('department')
            ->get();
    }
}
