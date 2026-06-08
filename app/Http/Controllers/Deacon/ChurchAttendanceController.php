<?php

namespace App\Http\Controllers\Deacon;

use App\Http\Controllers\Controller;
use App\Models\DeaconAttendanceRecord;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\SocialPlatformStat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChurchAttendanceController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // CHURCH ATTENDANCE — Điểm danh Chủ Nhật tổng
    // ══════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        // Các buổi nhóm Chủ Nhật (type=church) 8 tuần gần nhất
        $meetings = Meeting::where('type', 'church')
            ->orderBy('date', 'desc')
            ->take(16)
            ->get(['id', 'date', 'topic', 'type'])
            ->map(fn($m) => [
                'id'    => $m->id,
                'date'  => $m->date->format('d/m/Y'),
                'label' => $m->date->locale('vi')->isoFormat('dddd, D/M/Y') . ($m->topic ? " — {$m->topic}" : ''),
            ]);

        // Lịch sử điểm danh đã lưu
        $history = DeaconAttendanceRecord::with(['meeting:id,date,topic', 'recorder:id,name'])
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get()
            ->map(fn($r) => [
                'id'             => $r->id,
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

        // Biểu đồ xu hướng 8 tuần
        $chartData = DeaconAttendanceRecord::with('meeting:id,date')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get()
            ->sortBy(fn($r) => $r->meeting?->date)
            ->map(fn($r) => [
                'date'    => $r->meeting?->date?->format('d/m'),
                'total'   => $r->total_present,
                'male'    => $r->total_male,
                'female'  => $r->total_female,
                'children'=> $r->total_children,
                'guests'  => $r->guests_count,
            ])->values();

        // Danh sách ban sinh hoạt để nhập breakdown
        $activities_depts = Department::where('block', 'activities')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Deacon/ChurchAttendance', [
            'meetings'          => $meetings,
            'history'           => $history,
            'chart_data'        => $chartData,
            'activities_depts'  => $activities_depts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meeting_id'    => 'required|exists:meetings,id',
            'total_present' => 'required|integer|min:0',
            'total_male'    => 'nullable|integer|min:0',
            'total_female'  => 'nullable|integer|min:0',
            'total_children'=> 'nullable|integer|min:0',
            'guests_count'  => 'nullable|integer|min:0',
            'dept_breakdown'=> 'nullable|array',
            'dept_breakdown.*' => 'integer|min:0',
            'notes'         => 'nullable|string|max:500',
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
    // SOCIAL STATS — Số liệu nền tảng MXH
    // ══════════════════════════════════════════════════════════════

    public function socialStats(Request $request)
    {
        $platforms = SocialPlatformStat::PLATFORMS;
        $metrics   = SocialPlatformStat::METRICS;

        // Snapshot mới nhất
        $latest = [];
        foreach (array_keys($platforms) as $platform) {
            $rows = SocialPlatformStat::where('platform', $platform)
                ->orderBy('recorded_date', 'desc')
                ->take(1)
                ->get()
                ->keyBy('metric');
            $latest[$platform] = $rows;
        }

        // Lịch sử 8 tuần gần nhất (line chart) — chỉ các metrics chính
        $chartMetrics = ['subscribers', 'followers', 'members'];
        $chartData = [];
        foreach (array_keys($platforms) as $platform) {
            $history = SocialPlatformStat::where('platform', $platform)
                ->whereIn('metric', $chartMetrics)
                ->orderBy('recorded_date', 'asc')
                ->take(16)
                ->get()
                ->groupBy('metric');
            $chartData[$platform] = $history;
        }

        return Inertia::render('Deacon/SocialStats', [
            'platforms'  => $platforms,
            'metrics'    => $metrics,
            'latest'     => $latest,
            'chart_data' => $chartData,
        ]);
    }

    public function storeSocialStats(Request $request)
    {
        $validated = $request->validate([
            'recorded_date' => 'required|date',
            'stats'         => 'required|array',
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
                [
                    'count'       => $stat['count'],
                    'recorded_by' => $request->user()->id,
                ]
            );
        }

        return back()->with('success', 'Đã lưu số liệu nền tảng MXH.');
    }
}
