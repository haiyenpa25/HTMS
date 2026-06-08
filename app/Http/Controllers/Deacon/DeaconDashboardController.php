<?php

namespace App\Http\Controllers\Deacon;

use App\Http\Controllers\Controller;
use App\Models\CareRequest;
use App\Models\DeaconTermAssignment;
use App\Models\Department;
use App\Models\DepartmentReport;
use App\Models\Meeting;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeaconDashboardController extends Controller
{
    /**
     * Dashboard chính — hiển thị ban ngành Chấp Sự phụ trách (nhiệm kỳ hiện tại)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Lấy Member record của user hiện tại
        $member = Member::where('user_id', $user->id)->first();

        // Lấy danh sách ban mà Chấp Sự này phụ trách trong nhiệm kỳ hiện tại
        $myAssignments = DeaconTermAssignment::currentTerm()
            ->where('deacon_id', $member?->id ?? 0)
            ->with('department')
            ->get();

        $myDepts = $myAssignments->map(fn($a) => $a->department)->filter();

        // Tình trạng từng ban
        $today = Carbon::today();
        $twoWeeksAgo = $today->copy()->subDays(14);
        $deptStats = $myDepts->map(function ($dept) use ($today, $twoWeeksAgo) {
            $lastMeeting = Meeting::where('department_id', $dept->id)
                ->where('type', 'department')
                ->orderBy('date', 'desc')
                ->first();

            $pendingReports = DepartmentReport::where('department_id', $dept->id)
                ->whereIn('status', ['submitted'])
                ->count();

            $pendingCare = 0;
            if (class_exists(\App\Models\CareRequest::class)) {
                $pendingCare = \App\Models\CareRequest::where('department_id', $dept->id)
                    ->where('status', 'pending')
                    ->count();
            }

            // Alert: bỏ nhóm quá 2 tuần
            $skippedMeeting = !$lastMeeting || Carbon::parse($lastMeeting->date)->lt($twoWeeksAgo);

            return [
                'id'               => $dept->id,
                'name'             => $dept->name,
                'block'            => $dept->block,
                'last_meeting_date'=> $lastMeeting?->date?->format('d/m/Y'),
                'last_meeting_topic'=> $lastMeeting?->topic,
                'pending_reports'  => $pendingReports,
                'pending_care'     => $pendingCare,
                'skipped_meeting'  => $skippedMeeting,
                'days_since_meeting'=> $lastMeeting
                    ? Carbon::parse($lastMeeting->date)->diffInDays($today)
                    : null,
            ];
        })->values();

        // Tổng hợp alerts
        $alerts = [];

        foreach ($deptStats as $ds) {
            if ($ds['skipped_meeting']) {
                $alerts[] = [
                    'type'    => 'warning',
                    'icon'    => '📅',
                    'message' => "Ban {$ds['name']} chưa nhóm trong " . ($ds['days_since_meeting'] ?? '?') . " ngày",
                    'dept_id' => $ds['id'],
                ];
            }
            if ($ds['pending_reports'] > 0) {
                $alerts[] = [
                    'type'    => 'info',
                    'icon'    => '📋',
                    'message' => "Ban {$ds['name']} có {$ds['pending_reports']} báo cáo chờ nhận xét",
                    'dept_id' => $ds['id'],
                ];
            }
            if ($ds['pending_care'] > 0) {
                $alerts[] = [
                    'type'    => 'urgent',
                    'icon'    => '🤝',
                    'message' => "Ban {$ds['name']} có {$ds['pending_care']} người cần chăm sóc",
                    'dept_id' => $ds['id'],
                ];
            }
        }

        // Báo cáo của các ban mình phụ trách (tháng hiện tại + tháng trước)
        $deptIds = $myDepts->pluck('id');
        $reports = DepartmentReport::whereIn('department_id', $deptIds)
            ->whereIn('status', ['submitted', 'approved', 'reviewed'])
            ->with('department:id,name')
            ->orderBy('report_year', 'desc')
            ->orderBy('report_month', 'desc')
            ->take(20)
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'dept_name'    => $r->department?->name ?? '—',
                'dept_id'      => $r->department_id,
                'report_month' => $r->report_month,
                'report_year'  => $r->report_year,
                'status'       => $r->status,
                'submitted_at' => $r->updated_at?->format('d/m/Y'),
                'reviewer_note'=> $r->reviewer_note ?? null,
            ]);

        return Inertia::render('Deacon/Dashboard', [
            'dept_stats'      => $deptStats,
            'alerts'          => $alerts,
            'recent_reports'  => $reports,
            'my_dept_ids'     => $deptIds->values(),
            'has_assignments' => $myAssignments->isNotEmpty(),
        ]);
    }

    /**
     * Lưu nhận xét của Chấp Sự cho báo cáo của ban mình phụ trách
     */
    public function reviewReport(Request $request, int $reportId)
    {
        $user = $request->user();
        $member = Member::where('user_id', $user->id)->first();

        $report = DepartmentReport::findOrFail($reportId);

        // Chắc chắn Chấp Sự này phụ trách ban đó
        $authorized = DeaconTermAssignment::currentTerm()
            ->where('deacon_id', $member?->id ?? 0)
            ->where('department_id', $report->department_id)
            ->exists();

        if (!$authorized && !$user->isSuperAdmin()) {
            abort(403, 'Bạn không phụ trách ban ngành này.');
        }

        $data = $request->validate([
            'reviewer_note' => 'required|string|max:2000',
        ]);

        $report->update([
            'reviewer_note' => $data['reviewer_note'],
            'status'        => 'reviewed',
            'reviewed_by'   => $user->id,
            'reviewed_at'   => now(),
        ]);

        return back()->with('success', 'Đã lưu nhận xét báo cáo.');
    }
}
