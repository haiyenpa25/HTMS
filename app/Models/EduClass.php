<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EduClass extends Model
{
    protected $fillable = [
        'department_id', 'name', 'description', 'is_active', 'class_type',
        'class_category', 'is_seasonal', 'season_name', 'season_start', 'season_end',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_seasonal' => 'boolean',
        'season_start'=> 'date',
        'season_end'  => 'date',
    ];

    // Phân loại lứa tuổi
    public const CATEGORIES = [
        'au_nhi'     => 'Ấu Nhi',
        'thieu_nhi'  => 'Thiếu Nhi',
        'thieu_nien' => 'Thiếu Niên',
        'thanh_nien' => 'Thanh Niên',
        'trung_nien' => 'Trung Niên',
        'nguoi_lon'  => 'Người Lớn',
        'mixed'      => 'Tổng Hợp',
    ];

    // Class type labels
    public const CLASS_TYPES = [
        'sunday_school' => 'Trường Chủ Nhật (Hằng Tuần)',
        'gospel'        => 'Giáo Lý Báp-Têm',
        'bible_quiz'    => 'Kinh Thánh Trắc Nghiệm',
        'seasonal'      => 'Lớp Theo Mùa (TKMT, v.v.)',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /** All members (students + teachers) */
    public function classMembers()
    {
        return $this->hasMany(EduClassMember::class);
    }

    /** Only students */
    public function students()
    {
        return $this->hasMany(EduClassMember::class)->where('role', 'student');
    }

    /** Only teachers */
    public function teachers()
    {
        return $this->hasMany(EduClassMember::class)->where('role', 'teacher');
    }

    /** Only treasurers (thủ quỹ lớp) */
    public function treasurers()
    {
        return $this->hasMany(EduClassMember::class)->where('role', 'treasurer');
    }

    // ── Scopes ──────────────────────────────────────────────────

    /** Lớp hằng tuần thường xuyên (không phải mùa) */
    public function scopeRegular(Builder $query): Builder
    {
        return $query->where('is_seasonal', false);
    }

    /** Lớp theo mùa (TKMT, v.v.) */
    public function scopeSeasonal(Builder $query): Builder
    {
        return $query->where('is_seasonal', true);
    }

    /** Lọc theo lứa tuổi */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('class_category', $category);
    }

    /** Lọc theo loại lớp */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('class_type', $type);
    }

    /** Lớp đang trong mùa học (trong date range) */
    public function scopeActiveSeason(Builder $query): Builder
    {
        $today = now()->toDateString();
        return $query->where('is_seasonal', true)
                     ->where('season_start', '<=', $today)
                     ->where('season_end', '>=', $today);
    }

    /** Category label dễ đọc */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->class_category] ?? $this->class_category ?? '—';
    }

    /** All Member rows via pivot */
    public function membersList()
    {
        return $this->belongsToMany(Member::class, 'edu_class_members')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function sessions()
    {
        return $this->hasMany(EduSession::class);
    }

    public function funds()
    {
        return $this->hasMany(EduClassFund::class);
    }

    /** Check if a member is a teacher of this class */
    public function hasTeacher(int $memberId): bool
    {
        return $this->classMembers()
            ->where('member_id', $memberId)
            ->where('role', 'teacher')
            ->exists();
    }
}
