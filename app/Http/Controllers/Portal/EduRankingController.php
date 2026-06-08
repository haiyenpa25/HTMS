<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\EduClass;
use App\Models\EduSessionRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EduRankingController extends Controller
{
    /**
     * Bảng xếp hạng trong 1 lớp học
     * Xếp theo avg_score (điểm trung bình) — công bằng cho cả người đi nhiều và ít buổi
     */
    public function classRanking(Request $request, EduClass $eduClass)
    {
        $this->authorizeFeature('education-classes');

        // Filter theo khoảng thời gian (tuần/tháng/kỳ học/tùy chọn)
        $filterType = $request->input('filter', 'all'); // all, month, season
        $startDate  = $request->input('start_date');
        $endDate    = $request->input('end_date');

        // Build query cho sessions trong lớp
        $sessionQuery = $eduClass->sessions();

        if ($filterType === 'month') {
            $month = (int) $request->input('month', now()->month);
            $year  = (int) $request->input('year', now()->year);
            $sessionQuery->whereMonth('session_date', $month)->whereYear('session_date', $year);
        } elseif ($filterType === 'season' && $eduClass->is_seasonal) {
            if ($eduClass->season_start && $eduClass->season_end) {
                $sessionQuery->whereBetween('session_date', [
                    $eduClass->season_start->toDateString(),
                    $eduClass->season_end->toDateString(),
                ]);
            }
        } elseif ($filterType === 'custom' && $startDate && $endDate) {
            $sessionQuery->whereBetween('session_date', [$startDate, $endDate]);
        }

        $sessionIds = $sessionQuery->pluck('id');
        $totalSessions = $sessionIds->count();

        if ($totalSessions === 0) {
            return Inertia::render('Portal/Education/Ranking', [
                'edu_class'      => $this->classInfo($eduClass),
                'rankings'       => [],
                'total_sessions' => 0,
                'filter'         => $filterType,
                'route_prefix'   => $this->getRoutePrefix(),
            ]);
        }

        // Query xếp hạng — group by member_id
        $rankings = DB::table('edu_session_records')
            ->whereIn('edu_session_id', $sessionIds)
            ->select(
                'member_id',
                DB::raw('COUNT(*) as total_records'),
                DB::raw('SUM(CASE WHEN attendance = "present" THEN 1 ELSE 0 END) as sessions_present'),
                DB::raw('SUM(CASE WHEN memorized_verse = 1 THEN 1 ELSE 0 END) as verses_memorized'),
                DB::raw('COUNT(quiz_score) as quiz_attempts'),
                DB::raw('COALESCE(AVG(CASE WHEN quiz_score IS NOT NULL THEN quiz_score END), NULL) as avg_score'),
                DB::raw('COALESCE(SUM(quiz_score), 0) as total_score')
            )
            ->groupBy('member_id')
            ->orderByRaw('avg_score DESC, sessions_present DESC')
            ->get();

        // Lấy tên thành viên
        $memberIds = $rankings->pluck('member_id');
        $members = \App\Models\Member::whereIn('id', $memberIds)
            ->get(['id', 'full_name', 'phone'])
            ->keyBy('id');

        // Map + tính thêm chỉ số
        $rankedList = $rankings->map(function ($row, $index) use ($members, $totalSessions) {
            $member = $members->get($row->member_id);
            $attendanceRate = $totalSessions > 0
                ? round(($row->sessions_present / $totalSessions) * 100, 1)
                : 0;

            return [
                'rank'             => $index + 1,
                'member_id'        => $row->member_id,
                'full_name'        => $member?->full_name ?? '—',
                'sessions_present' => (int) $row->sessions_present,
                'sessions_total'   => $totalSessions,
                'attendance_rate'  => $attendanceRate,
                'verses_memorized' => (int) $row->verses_memorized,
                'quiz_attempts'    => (int) $row->quiz_attempts,
                'avg_score'        => $row->avg_score !== null ? round($row->avg_score, 1) : null,
                'total_score'      => (int) $row->total_score,
                'is_top3'          => $index < 3,
                'medal'            => match($index) {
                    0 => 'gold',
                    1 => 'silver',
                    2 => 'bronze',
                    default => null,
                },
            ];
        })->values();

        return Inertia::render('Portal/Education/Ranking', [
            'edu_class'      => $this->classInfo($eduClass),
            'rankings'       => $rankedList,
            'total_sessions' => $totalSessions,
            'filter'         => $filterType,
            'month'          => $request->input('month', now()->month),
            'year'           => $request->input('year', now()->year),
            'route_prefix'   => $this->getRoutePrefix(),
        ]);
    }

    private function classInfo(EduClass $class): array
    {
        return [
            'id'           => $class->id,
            'name'         => $class->name,
            'class_type'   => $class->class_type,
            'is_seasonal'  => $class->is_seasonal,
            'season_name'  => $class->season_name,
            'season_start' => $class->season_start?->toDateString(),
            'season_end'   => $class->season_end?->toDateString(),
            'category'     => $class->class_category,
            'category_label'=> $class->category_label,
        ];
    }

    // Helper methods dùng chung với EducationController
    protected function authorizeFeature(string $slug): void
    {
        $user = request()->user();
        if ($user->isSuperAdmin()) return;

        $departmentId = (int) session('active_ministry_dept_id');
        $hasFeature = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $departmentId)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($f) => $f->where('slug', $slug))
            ->exists();

        if (!$hasFeature) abort(403, 'Bạn không có quyền truy cập tính năng này.');
    }

    protected function getRoutePrefix(): string
    {
        return 'ministry'; // education routes dùng prefix ministry
    }
}
