<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\DeaconAttendanceRecord;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\SecretaryMonthlyNote;
use App\Models\SocialPlatformStat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * P2 — Cổng Thư Ký Hội Thánh (Secretary Portal)
 * Tách biệt khỏi Deacon Portal để rõ ràng về phạm vi.
 */
class SecretaryPortalController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // Dashboard chính P2
    // ══════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        // ── Điểm danh CN gần đây (8 tuần) ─────────────────────────
        $attendanceHistory = DeaconAttendanceRecord::with('meeting:id,date,topic')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get()
            ->map(fn($r) => [
                'id'                => $r->id,
                'meeting_date'      => $r->meeting?->date?->format('d/m/Y'),
                'total_present'     => $r->total_present,
                'total_male'        => $r->total_male,
                'total_female'      => $r->total_female,
                'total_children'    => $r->total_children,
                'guests_count'      => $r->guests_count,
                'dept_total'        => $r->dept_total,
                'youtube_live_count'=> $r->youtube_live_count,
                'incident_note'     => $r->incident_note,
            ]);

        // ── Tổng hợp tháng hiện tại ────────────────────────────────
        $monthSummary = $this->buildMonthSummary($month, $year);

        // ── Tổng hợp tháng trước (để tính delta) ──────────────────
        $prevMonth = $month === 1 ? 12 : $month - 1;
        $prevYear  = $month === 1 ? $year - 1 : $year;
        $prevSummary = $this->buildMonthSummary($prevMonth, $prevYear);

        // ── Xu hướng attendance 8 tuần (chart) ────────────────────
        $chartData = $attendanceHistory->sortBy('meeting_date')->map(fn($r) => [
            'date'              => substr($r['meeting_date'] ?? '', 0, 5),
            'total'             => $r['total_present'],
            'male'              => $r['total_male'],
            'female'            => $r['total_female'],
            'children'          => $r['total_children'],
            'dept_total'        => $r['dept_total'],
            'youtube_live_count'=> $r['youtube_live_count'],
        ])->values();

        // ── MXH snapshot ──────────────────────────────────────────
        $socialLatest = SocialPlatformStat::getLatestSnapshot();
        $socialFormatted = [];
        foreach (SocialPlatformStat::PLATFORMS as $key => $label) {
            $platformData = $socialLatest[$key] ?? collect();
            $socialFormatted[$key] = [
                'label'   => $label,
                'metrics' => collect(SocialPlatformStat::METRICS)->map(function($mLabel, $mKey) use ($platformData) {
                    $row = $platformData->get($mKey);
                    return [
                        'label' => $mLabel,
                        'count' => $row?->count ?? null,
                        'date'  => $row?->recorded_date?->format('d/m/Y'),
                    ];
                })->values(),
            ];
        }

        // ── Ghi chú tháng ─────────────────────────────────────────
        $monthNote = SecretaryMonthlyNote::findOrEmpty($month, $year);

        // ── Số liệu thống kê nhanh ────────────────────────────────
        $sundayMeetings = Meeting::where('type', 'church')
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get(['id']);

        $attendedCount = DeaconAttendanceRecord::whereIn('meeting_id', $sundayMeetings->pluck('id'))->count();

        return Inertia::render('Secretary/Dashboard', [
            'attendance_history' => $attendanceHistory->values(),
            'chart_data'         => $chartData,
            'social_latest'      => $socialFormatted,
            'month_summary'      => $monthSummary,
            'prev_summary'       => $prevSummary,
            'month_note'         => [
                'announcements' => $monthNote->announcements,
                'next_plan'     => $monthNote->next_plan,
            ],
            'filters' => ['month' => $month, 'year' => $year],
            'stats'   => [
                'sundays_this_month'  => $sundayMeetings->count(),
                'attendance_recorded' => $attendedCount,
                'platforms_tracked'   => count(array_filter($socialFormatted,
                    fn($p) => collect($p['metrics'])->whereNotNull('count')->isNotEmpty()
                )),
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // Điểm danh Chủ Nhật
    // ══════════════════════════════════════════════════════════════
    public function attendance(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $meetings = Meeting::where('type', 'church')
            ->orderBy('date', 'desc')
            ->take(16)
            ->get(['id', 'date', 'topic'])
            ->map(fn($m) => [
                'id'    => $m->id,
                'date'  => $m->date->format('d/m/Y'),
                'label' => $m->date->locale('vi')->isoFormat('dddd, D/M/Y') . ($m->topic ? " — {$m->topic}" : ''),
            ]);

        $history = DeaconAttendanceRecord::with(['meeting:id,date,topic', 'recorder:id,name'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(fn($r) => [
                'id'                => $r->id,
                'meeting_id'        => $r->meeting_id,
                'meeting_date'      => $r->meeting?->date?->format('d/m/Y'),
                'total_present'     => $r->total_present,
                'total_male'        => $r->total_male,
                'total_female'      => $r->total_female,
                'total_children'    => $r->total_children,
                'guests_count'      => $r->guests_count,
                'dept_total'        => $r->dept_total,
                'unaccounted'       => $r->unaccounted,
                'dept_breakdown'    => $r->dept_breakdown ?? [],
                'notes'             => $r->notes,
                'incident_note'     => $r->incident_note,
                'youtube_live_count'=> $r->youtube_live_count,
                'recorded_at'       => $r->recorded_at?->format('d/m/Y H:i'),
                'recorder_name'     => $r->recorder?->name,
            ]);

        $chartData = $history->sortBy('meeting_date')->map(fn($r) => [
            'date'              => substr($r['meeting_date'] ?? '', 0, 5),
            'total'             => $r['total_present'],
            'dept_total'        => $r['dept_total'],
            'youtube_live_count'=> $r['youtube_live_count'],
            'male'              => $r['total_male'],
            'female'            => $r['total_female'],
            'children'          => $r['total_children'],
            'guests'            => $r['guests_count'],
        ])->values();

        // Tổng hợp tháng hiện tại (cho widget tóm tắt)
        $monthSummary = $this->buildMonthSummary($month, $year);
        $prevMonth    = $month === 1 ? 12 : $month - 1;
        $prevYear     = $month === 1 ? $year - 1 : $year;
        $prevSummary  = $this->buildMonthSummary($prevMonth, $prevYear);

        $activitiesDepts = Department::where('block', 'activities')
            ->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Secretary/Attendance', [
            'meetings'         => $meetings,
            'history'          => $history->values(),
            'chart_data'       => $chartData,
            'activities_depts' => $activitiesDepts,
            'month_summary'    => $monthSummary,
            'prev_summary'     => $prevSummary,
            'filters'          => ['month' => $month, 'year' => $year],
        ]);
    }

    public function storeAttendance(Request $request)
    {
        $validated = $request->validate([
            'meeting_id'        => 'required|exists:meetings,id',
            'total_present'     => 'required|integer|min:0',
            'total_male'        => 'nullable|integer|min:0',
            'total_female'      => 'nullable|integer|min:0',
            'total_children'    => 'nullable|integer|min:0',
            'guests_count'      => 'nullable|integer|min:0',
            'dept_breakdown'    => 'nullable|array',
            'dept_breakdown.*'  => 'integer|min:0',
            'notes'             => 'nullable|string|max:500',
            'incident_note'     => 'nullable|string|max:1000',
            'youtube_live_count'=> 'nullable|integer|min:0',
        ]);

        DeaconAttendanceRecord::updateOrCreate(
            ['meeting_id' => $validated['meeting_id']],
            [
                'recorded_by'       => $request->user()->id,
                'total_present'     => $validated['total_present'],
                'total_male'        => $validated['total_male'] ?? 0,
                'total_female'      => $validated['total_female'] ?? 0,
                'total_children'    => $validated['total_children'] ?? 0,
                'guests_count'      => $validated['guests_count'] ?? 0,
                'dept_breakdown'    => $validated['dept_breakdown'] ?? null,
                'notes'             => $validated['notes'] ?? null,
                'incident_note'     => $validated['incident_note'] ?? null,
                'youtube_live_count'=> $validated['youtube_live_count'] ?? null,
                'recorded_at'       => now(),
            ]
        );

        return back()->with('success', 'Đã lưu điểm danh Chủ Nhật.');
    }

    // ══════════════════════════════════════════════════════════════
    // Ghi chú tháng (thông báo + kế hoạch)
    // ══════════════════════════════════════════════════════════════
    public function storeMonthNote(Request $request)
    {
        $validated = $request->validate([
            'month'         => 'required|integer|between:1,12',
            'year'          => 'required|integer|min:2020|max:2099',
            'announcements' => 'nullable|string|max:3000',
            'next_plan'     => 'nullable|string|max:3000',
        ]);

        $note = SecretaryMonthlyNote::firstOrNew([
            'month' => $validated['month'],
            'year'  => $validated['year'],
        ]);

        $note->fill([
            'announcements' => $validated['announcements'],
            'next_plan'     => $validated['next_plan'],
            'created_by'    => $note->exists ? $note->created_by : $request->user()->id,
            'updated_by'    => $request->user()->id,
        ])->save();

        return back()->with('success', 'Đã lưu ghi chú tháng.');
    }

    // ══════════════════════════════════════════════════════════════
    // Số liệu Mạng Xã Hội
    // ══════════════════════════════════════════════════════════════
    public function socialStats(Request $request)
    {
        $platforms = SocialPlatformStat::PLATFORMS;
        $metrics   = SocialPlatformStat::METRICS;

        // Latest per platform
        $latest = [];
        foreach (array_keys($platforms) as $platform) {
            $latest[$platform] = SocialPlatformStat::where('platform', $platform)
                ->orderBy('recorded_date', 'desc')
                ->take(1)->get()->keyBy('metric');
        }

        // Previous month stats (để tính delta)
        $prevMonth = now()->month === 1 ? 12 : now()->month - 1;
        $prevYear  = now()->month === 1 ? now()->year - 1 : now()->year;
        $prevDate  = Carbon::create($prevYear, $prevMonth, 1)->endOfMonth();

        $prevStats = [];
        foreach (array_keys($platforms) as $platform) {
            $prevStats[$platform] = SocialPlatformStat::where('platform', $platform)
                ->where('recorded_date', '<=', $prevDate->toDateString())
                ->orderBy('recorded_date', 'desc')
                ->take(1)->get()->keyBy('metric');
        }

        // Chart data
        $chartMetrics = ['subscribers', 'followers', 'members'];
        $chartData = [];
        foreach (array_keys($platforms) as $platform) {
            $chartData[$platform] = SocialPlatformStat::where('platform', $platform)
                ->whereIn('metric', $chartMetrics)
                ->orderBy('recorded_date', 'asc')
                ->take(16)->get()->groupBy('metric');
        }

        return Inertia::render('Secretary/SocialStats', [
            'platforms'   => $platforms,
            'metrics'     => $metrics,
            'latest'      => $latest,
            'prev_stats'  => $prevStats,
            'chart_data'  => $chartData,
        ]);
    }

    public function storeSocialStats(Request $request)
    {
        $validated = $request->validate([
            'recorded_date'    => 'required|date',
            'stats'            => 'required|array',
            'stats.*.platform' => 'required|string',
            'stats.*.metric'   => 'required|string',
            'stats.*.count'    => 'required|integer|min:0',
        ]);

        foreach ($validated['stats'] as $stat) {
            SocialPlatformStat::updateOrCreate(
                [
                    'platform'      => $stat['platform'],
                    'metric'        => $stat['metric'],
                    'recorded_date' => $validated['recorded_date'],
                ],
                ['count' => $stat['count'], 'recorded_by' => $request->user()->id]
            );
        }

        return back()->with('success', 'Đã lưu số liệu MXH.');
    }

    // ══════════════════════════════════════════════════════════════
    // Báo cáo tháng — In / Xuất
    // ══════════════════════════════════════════════════════════════
    public function report(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        // Lấy tất cả buổi nhóm CN trong tháng
        $sundayMeetings = Meeting::where('type', 'church')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get(['id', 'date', 'topic']);

        // Records điểm danh trong tháng
        $records = DeaconAttendanceRecord::with(['meeting:id,date', 'recorder:id,name'])
            ->whereIn('meeting_id', $sundayMeetings->pluck('id'))
            ->get()
            ->keyBy('meeting_id');

        // Tạo dữ liệu bảng điểm danh từng CN
        $attendanceRows = $sundayMeetings->map(function($m) use ($records) {
            $rec = $records->get($m->id);
            return [
                'meeting_id'        => $m->id,
                'date'              => $m->date->format('d/m/Y'),
                'date_label'        => 'CN ' . $m->date->format('d/m/Y'),
                'total_present'     => $rec?->total_present,
                'youtube_live_count'=> $rec?->youtube_live_count,
                'total_male'        => $rec?->total_male,
                'total_female'      => $rec?->total_female,
                'total_children'    => $rec?->total_children,
                'guests_count'      => $rec?->guests_count,
                'dept_total'        => $rec?->dept_total ?? 0,
                'incident_note'     => $rec?->incident_note,
                'recorder_name'     => $rec?->recorder?->name,
                'has_record'        => !is_null($rec),
            ];
        });

        // Tổng hợp tháng
        $monthSummary = $this->buildMonthSummary($month, $year);
        $prevMonth    = $month === 1 ? 12 : $month - 1;
        $prevYear     = $month === 1 ? $year - 1 : $year;
        $prevSummary  = $this->buildMonthSummary($prevMonth, $prevYear);

        // YouTube snapshot tháng này
        $ytLatest = SocialPlatformStat::where('platform', 'youtube')
            ->whereBetween('recorded_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('recorded_date', 'desc')
            ->take(1)->get()->keyBy('metric');

        // Nếu không có data tháng này, lấy dữ liệu mới nhất
        if ($ytLatest->isEmpty()) {
            $ytLatest = SocialPlatformStat::where('platform', 'youtube')
                ->orderBy('recorded_date', 'desc')
                ->take(1)->get()->keyBy('metric');
        }

        // YouTube tháng trước (để tính delta)
        $prevYtDate = Carbon::create($prevYear, $prevMonth, 1)->endOfMonth();
        $ytPrev = SocialPlatformStat::where('platform', 'youtube')
            ->where('recorded_date', '<=', $prevYtDate->toDateString())
            ->orderBy('recorded_date', 'desc')
            ->take(1)->get()->keyBy('metric');

        // Ghi chú tháng
        $monthNote = SecretaryMonthlyNote::findOrEmpty($month, $year);

        // Người báo cáo = người đăng nhập hiện tại
        $reporter = $request->user();

        // Tính TB YouTube live (nếu có data)
        $ytLiveValues = $attendanceRows->whereNotNull('youtube_live_count')->pluck('youtube_live_count');
        $ytLiveAvg    = $ytLiveValues->isNotEmpty() ? round($ytLiveValues->avg(), 1) : null;

        return Inertia::render('Secretary/Report', [
            'month'           => $month,
            'year'            => $year,
            'reporter_name'   => $reporter?->name ?? '',
            'attendance_rows' => $attendanceRows->values(),
            'month_summary'   => $monthSummary,
            'prev_summary'    => $prevSummary,
            'yt_latest'       => $ytLatest->map(fn($r) => ['metric' => $r->metric, 'count' => $r->count]),
            'yt_prev'         => $ytPrev->map(fn($r) => ['metric' => $r->metric, 'count' => $r->count]),
            'yt_live_avg'     => $ytLiveAvg,
            'month_note'      => [
                'announcements' => $monthNote->announcements,
                'next_plan'     => $monthNote->next_plan,
            ],
            'filters' => ['month' => $month, 'year' => $year],
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // Helper: Tổng hợp tháng
    // ══════════════════════════════════════════════════════════════
    private function buildMonthSummary(int $month, int $year): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $sundayMeetingIds = Meeting::where('type', 'church')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('id');

        $records = DeaconAttendanceRecord::whereIn('meeting_id', $sundayMeetingIds)
            ->with('meeting:id,date')
            ->get();

        if ($records->isEmpty()) {
            return [
                'month'   => $month, 'year' => $year,
                'count'   => 0, 'avg' => 0, 'max' => 0, 'min' => 0,
                'max_date'=> null, 'min_date' => null,
                'total_recorded' => 0,
                'yt_live_avg'    => null,
            ];
        }

        $totals  = $records->pluck('total_present');
        $maxRec  = $records->sortByDesc('total_present')->first();
        $minRec  = $records->sortBy('total_present')->first();
        $avg     = round($totals->avg(), 1);

        // TB YouTube live trong tháng
        $ytLiveValues = $records->whereNotNull('youtube_live_count')->pluck('youtube_live_count');
        $ytLiveAvg    = $ytLiveValues->isNotEmpty() ? round($ytLiveValues->avg(), 1) : null;

        return [
            'month'          => $month,
            'year'           => $year,
            'count'          => $records->count(),
            'avg'            => $avg,
            'max'            => $maxRec->total_present,
            'min'            => $minRec->total_present,
            'max_date'       => $maxRec->meeting?->date?->format('d/m'),
            'min_date'       => $minRec->meeting?->date?->format('d/m'),
            'total_recorded' => $records->count(),
            'yt_live_avg'    => $ytLiveAvg,
        ];
    }
}
