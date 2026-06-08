<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\DeaconAttendanceRecord;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\SocialPlatformStat;
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
        $today      = Carbon::today();
        $thisMonth  = $today->month;
        $thisYear   = $today->year;

        // ── Điểm danh CN gần đây (8 tuần) ─────────────────────────
        $attendanceHistory = DeaconAttendanceRecord::with('meeting:id,date,topic')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'meeting_date'    => $r->meeting?->date?->format('d/m/Y'),
                'meeting_topic'   => $r->meeting?->topic,
                'total_present'   => $r->total_present,
                'total_male'      => $r->total_male,
                'total_female'    => $r->total_female,
                'total_children'  => $r->total_children,
                'guests_count'    => $r->guests_count,
                'dept_total'      => $r->dept_total,
                'recorded_at'     => $r->recorded_at?->format('d/m H:i'),
            ]);

        // ── Xu hướng attendance 8 tuần (chart) ────────────────────
        $chartData = $attendanceHistory->sortBy('meeting_date')->map(fn($r) => [
            'date'     => substr($r['meeting_date'] ?? '', 0, 5),
            'total'    => $r['total_present'],
            'male'     => $r['total_male'],
            'female'   => $r['total_female'],
            'children' => $r['total_children'],
        ])->values();

        // ── Số liệu MXH mới nhất ──────────────────────────────────
        $socialLatest = SocialPlatformStat::getLatestSnapshot();
        $socialFormatted = [];
        foreach (SocialPlatformStat::PLATFORMS as $key => $label) {
            $platformData = $socialLatest[$key] ?? collect();
            $socialFormatted[$key] = [
                'label' => $label,
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

        // ── Thống kê tháng này ────────────────────────────────────
        $monthStart = Carbon::create($thisYear, $thisMonth, 1)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        $sundayMeetings = Meeting::where('type', 'church')
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('date')
            ->get(['id', 'date', 'topic']);

        $sundayCount = $sundayMeetings->count();
        $attendedCount = DeaconAttendanceRecord::whereIn('meeting_id', $sundayMeetings->pluck('id'))->count();

        // ── Danh sách ban để nhập breakdown ───────────────────────
        $activitiesDepts = Department::where('block', 'activities')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Secretary/Dashboard', [
            'attendance_history' => $attendanceHistory->values(),
            'chart_data'         => $chartData,
            'social_latest'      => $socialFormatted,
            'stats' => [
                'sundays_this_month'   => $sundayCount,
                'attendance_recorded'  => $attendedCount,
                'platforms_tracked'    => count(array_filter($socialFormatted, fn($p) => collect($p['metrics'])->whereNotNull('count')->isNotEmpty())),
            ],
            'activities_depts' => $activitiesDepts,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // Điểm danh Chủ Nhật
    // ══════════════════════════════════════════════════════════════
    public function attendance(Request $request)
    {
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
            ->take(16)
            ->get()
            ->map(fn($r) => [
                'id'             => $r->id,
                'meeting_id'     => $r->meeting_id,
                'meeting_date'   => $r->meeting?->date?->format('d/m/Y'),
                'total_present'  => $r->total_present,
                'total_male'     => $r->total_male,
                'total_female'   => $r->total_female,
                'total_children' => $r->total_children,
                'guests_count'   => $r->guests_count,
                'dept_total'     => $r->dept_total,
                'unaccounted'    => $r->unaccounted,
                'dept_breakdown' => $r->dept_breakdown ?? [],
                'notes'          => $r->notes,
                'recorded_at'    => $r->recorded_at?->format('d/m/Y H:i'),
                'recorder_name'  => $r->recorder?->name,
            ]);

        $chartData = $history->sortBy('meeting_date')->map(fn($r) => [
            'date'    => substr($r['meeting_date'] ?? '', 0, 5),
            'total'   => $r['total_present'],
            'male'    => $r['total_male'],
            'female'  => $r['total_female'],
            'children'=> $r['total_children'],
            'guests'  => $r['guests_count'],
        ])->values();

        $activitiesDepts = Department::where('block', 'activities')
            ->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Secretary/Attendance', [
            'meetings'          => $meetings,
            'history'           => $history->values(),
            'chart_data'        => $chartData,
            'activities_depts'  => $activitiesDepts,
        ]);
    }

    public function storeAttendance(Request $request)
    {
        $validated = $request->validate([
            'meeting_id'       => 'required|exists:meetings,id',
            'total_present'    => 'required|integer|min:0',
            'total_male'       => 'nullable|integer|min:0',
            'total_female'     => 'nullable|integer|min:0',
            'total_children'   => 'nullable|integer|min:0',
            'guests_count'     => 'nullable|integer|min:0',
            'dept_breakdown'   => 'nullable|array',
            'dept_breakdown.*' => 'integer|min:0',
            'notes'            => 'nullable|string|max:500',
        ]);

        DeaconAttendanceRecord::updateOrCreate(
            ['meeting_id' => $validated['meeting_id']],
            [
                'recorded_by'    => $request->user()->id,
                'total_present'  => $validated['total_present'],
                'total_male'     => $validated['total_male'] ?? 0,
                'total_female'   => $validated['total_female'] ?? 0,
                'total_children' => $validated['total_children'] ?? 0,
                'guests_count'   => $validated['guests_count'] ?? 0,
                'dept_breakdown' => $validated['dept_breakdown'] ?? null,
                'notes'          => $validated['notes'] ?? null,
                'recorded_at'    => now(),
            ]
        );

        return back()->with('success', 'Đã lưu điểm danh Chủ Nhật.');
    }

    // ══════════════════════════════════════════════════════════════
    // Số liệu Mạng Xã Hội
    // ══════════════════════════════════════════════════════════════
    public function socialStats(Request $request)
    {
        $platforms = SocialPlatformStat::PLATFORMS;
        $metrics   = SocialPlatformStat::METRICS;

        $latest = [];
        foreach (array_keys($platforms) as $platform) {
            $latest[$platform] = SocialPlatformStat::where('platform', $platform)
                ->orderBy('recorded_date', 'desc')
                ->take(1)->get()->keyBy('metric');
        }

        $chartMetrics = ['subscribers', 'followers', 'members'];
        $chartData = [];
        foreach (array_keys($platforms) as $platform) {
            $chartData[$platform] = SocialPlatformStat::where('platform', $platform)
                ->whereIn('metric', $chartMetrics)
                ->orderBy('recorded_date', 'asc')
                ->take(16)->get()->groupBy('metric');
        }

        return Inertia::render('Secretary/SocialStats', [
            'platforms'  => $platforms,
            'metrics'    => $metrics,
            'latest'     => $latest,
            'chart_data' => $chartData,
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
}
