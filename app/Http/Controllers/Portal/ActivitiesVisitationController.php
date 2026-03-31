<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Department;
use App\Models\Member;
use App\Models\Visitation;
use Illuminate\Support\Facades\Gate;

class ActivitiesVisitationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $departmentId = session('active_portal_dept_id');
        
        if (!$departmentId) {
            abort(403, 'Vui lòng chọn một ban ngành sinh hoạt.');
        }

        $department = Department::findOrFail($departmentId);

        $query = Visitation::with(['member', 'visitors', 'department'])
                           ->where('department_id', $departmentId)
                           ->where('visitation_type', 'department');

        // Apply filters
        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('member', function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }
        
        // Quick Date Options (1 month, 3 months, 6 months, 1 year)
        if ($request->filled('period')) {
            $now = now();
            if ($request->period === '1m') $query->where('visit_date', '>=', $now->copy()->subMonth());
            elseif ($request->period === '3m') $query->where('visit_date', '>=', $now->copy()->subMonths(3));
            elseif ($request->period === '6m') $query->where('visit_date', '>=', $now->copy()->subMonths(6));
            elseif ($request->period === '1y') $query->where('visit_date', '>=', $now->copy()->subYear());
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('visit_date', [$request->start_date, $request->end_date]);
        }
        
        $query->orderBy('visit_date', 'desc');

        $visitations = $query->paginate(15)->through(function ($visitation) use ($user) {
            // Sensitive Content Filtering
            if (!Gate::allows('viewSensitiveContent', $visitation)) {
                $visitation->content = '*** (Chỉ Mục sư & Người thăm viếng được xem) ***';
            }
            return $visitation;
        });

        // Fetch members for the dropdown: Only members of this department
        $membersQuery = Member::query()->whereHas('memberships', function($q) use ($departmentId) {
            $q->where('model_id', $departmentId)
              ->where('model_type', Department::class);
        });
        
        $members = $membersQuery->with(['memberships' => function($q) use ($departmentId) {
            $q->where('model_id', $departmentId)
              ->where('model_type', Department::class)
              ->with('role');
        }])->orderBy('full_name')->get(['id', 'full_name', 'phone', 'address', 'latitude', 'longitude', 'visit_location']);

        // Suggestions Logic
        $last3Meetings = \App\Models\Meeting::where('department_id', $departmentId)
            ->orderBy('date', 'desc')
            ->take(3)
            ->pluck('id');
            
        $suggestions = collect();
        if ($last3Meetings->count() > 0) {
            $suggestedMembers = Member::whereHas('memberships', function($q) use ($departmentId) {
                $q->where('model_id', $departmentId)->where('model_type', Department::class);
            })->with(['visitations' => function($q) use ($departmentId) {
                $q->where('department_id', $departmentId)->orderBy('visit_date', 'desc');
            }, 'attendances' => function($q) use ($last3Meetings) {
                $q->whereIn('meeting_id', $last3Meetings);
            }])->get();

            foreach ($suggestedMembers as $m) {
                $lastVisit = $m->visitations->first();
                $lastVisitDate = $lastVisit ? \Carbon\Carbon::parse($lastVisit->visit_date) : null;
                $monthsSinceLastVisit = $lastVisitDate ? $lastVisitDate->diffInMonths(now()) : 999;
                
                $absentCount = 0;
                foreach ($last3Meetings as $mId) {
                    $att = $m->attendances->firstWhere('meeting_id', $mId);
                    if ($att && $att->status === 'absent') {
                        $absentCount++;
                    } elseif (!$att) {
                        $absentCount++;
                    }
                }
                
                $priority = 'normal';
                $reasons = [];
                
                if ($absentCount >= 3) {
                    $reasons[] = 'Vắng mặt 3 lần liên tiếp';
                }
                if ($monthsSinceLastVisit >= 6) {
                    $reasons[] = 'Chưa được thăm > 6 tháng';
                }
                
                if ($absentCount >= 3 && $monthsSinceLastVisit >= 6) {
                    $priority = 'high'; // Red
                } elseif (count($reasons) > 0) {
                    $priority = 'medium'; // Yellow
                }
                
                if ($priority !== 'normal') {
                    $suggestions->push([
                        'id' => $m->id,
                        'full_name' => $m->full_name,
                        'phone' => $m->phone,
                        'priority' => $priority,
                        'reasons' => $reasons,
                    ]);
                }
            }
        }
        
        // Sort suggestions: high first, then medium
        $suggestions = $suggestions->sortBy(function($s) {
            return $s['priority'] === 'high' ? 0 : 1;
        })->values();

        // Suggestions for this specific department only (last 3 meetings absences + not visited 6mo)
        $recentMeetingIds = \App\Models\Meeting::where('department_id', $departmentId)
            ->orderBy('date', 'desc')
            ->take(3)
            ->pluck('id')
            ->toArray();

        $suggestions = collect();
        if (count($recentMeetingIds) > 0) {
            $deptMembers = Member::whereHas('memberships', function($q) use ($departmentId) {
                $q->where('model_type', Department::class)->where('model_id', $departmentId);
            })->with([
                'visitations' => fn($q) => $q->orderBy('visit_date', 'desc')->take(1),
                'attendances' => fn($q) => $q->whereIn('meeting_id', $recentMeetingIds),
            ])->get(['id', 'full_name', 'phone', 'address', 'latitude', 'longitude', 'visit_location']);

            foreach ($deptMembers as $m) {
                $lastVisit = $m->visitations->first();
                $monthsSinceLastVisit = $lastVisit ? \Carbon\Carbon::parse($lastVisit->visit_date)->diffInMonths(now()) : 999;
                $absentCount = 0;
                foreach ($recentMeetingIds as $mId) {
                    $att = $m->attendances->firstWhere('meeting_id', $mId);
                    if (!$att || $att->status === 'absent') $absentCount++;
                }
                $priority = 'normal';
                $reasons = [];
                
                // If they were visited VERY recently (e.g., < 2 months ago), do not suggest them again
                if ($monthsSinceLastVisit < 2) {
                    continue;
                }
                
                if ($absentCount >= 3) $reasons[] = 'Vắng nhóm 3 lần liên tiếp';
                if ($monthsSinceLastVisit >= 6 && $monthsSinceLastVisit < 999) $reasons[] = 'Chưa được thăm > 6 tháng';
                if ($absentCount >= 3 && $monthsSinceLastVisit >= 6) $priority = 'high';
                elseif (count($reasons) > 0) $priority = 'medium';
                if ($priority !== 'normal') {
                    $suggestions->push([
                        'id' => $m->id, 'full_name' => $m->full_name, 'phone' => $m->phone,
                        'priority' => $priority, 'reasons' => $reasons,
                        'address' => $m->address, 'visit_location' => $m->visit_location,
                        'latitude' => $m->latitude, 'longitude' => $m->longitude,
                    ]);
                }
            }
            $suggestions = $suggestions->sortBy(fn($s) => $s['priority'] === 'high' ? 0 : 1)->values();
        }

        $dbReasons = \App\Models\VisitationReason::whereNull('department_id')
            ->when($departmentId, fn($q) => $q->orWhere('department_id', $departmentId))
            ->get();
            
        $reasons = $dbReasons->pluck('name')->toArray();
        if (empty($reasons)) {
            $reasons = ['ốm đau', 'mới tin Chúa', 'khích lệ', 'khác'];
        }

        return Inertia::render('Portal/Visitation/Index', [
            'visitations' => $visitations,
            'members' => $members,
            'suggestions' => $suggestions,
            'filters' => $request->only(['reason', 'period', 'search', 'start_date', 'end_date']),
            'canManage' => Gate::allows('manage_visitations') || $user->isSuperAdmin() || $user->isSuperAdmin(),
            'visitationTypes' => ['department' => 'Ban Ngành'],
            'reasons' => $reasons,
            'dbReasons' => $dbReasons,
            'department' => $department,
            'isGlobalAdmin' => $user->isSuperAdmin(),
            'routePrefix' => 'portal.visitation',
            'portalType' => 'activities',
        ]);
    }

    public function store(Request $request)
    {
        $departmentId = session('active_portal_dept_id');
        if (!$departmentId) abort(403);

        $validated = $request->validate([
            'visitation_type' => 'required|in:department',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:members,id',
            'visit_date' => 'required|date',
            'reason' => 'required|string',
            'prayer_points' => 'nullable|string',
            'content' => 'nullable|string',
            'gifts' => 'nullable|string',
            'status' => 'required|in:planned,completed,cancelled',
            'priority' => 'required|in:high,medium,normal',
            'visitors' => 'required|array',
            'visitors.*' => 'exists:members,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);
        
        $validated['department_id'] = $departmentId;

        $baseData = \Illuminate\Support\Arr::except($validated, ['latitude', 'longitude', 'member_ids']);
        
        foreach ($request->member_ids as $memberId) {
            $data = $baseData;
            $data['member_id'] = $memberId;
            $visitation = Visitation::create($data);
            $visitation->visitors()->sync($request->visitors);
            
            if ($request->filled('latitude') && $request->filled('longitude')) {
                Member::where('id', $memberId)->update([
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]);
            }
        }

        return redirect()->back()->with('message', 'Đã thêm báo cáo thăm viếng ban ngành thành công!');
    }

    public function update(Request $request, Visitation $visitation)
    {
        if ($visitation->department_id !== session('active_portal_dept_id')) {
            abort(403, 'Không có quyền chỉnh sửa mục này.');
        }

        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'visit_date' => 'required|date',
            'reason' => 'required|string',
            'prayer_points' => 'nullable|string',
            'content' => 'nullable|string',
            'gifts' => 'nullable|string',
            'status' => 'required|in:planned,completed,cancelled',
            'priority' => 'required|in:high,medium,normal',
            'visitors' => 'required|array',
            'visitors.*' => 'exists:members,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $visitation->update(\Illuminate\Support\Arr::except($validated, ['latitude', 'longitude']));
        $visitation->visitors()->sync($request->visitors);
        
        if ($request->filled('latitude') && $request->filled('longitude')) {
            Member::where('id', $validated['member_id'])->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);
        }

        return redirect()->back()->with('message', 'Đã cập nhật báo cáo thăm viếng ban ngành!');
    }

    public function quickComplete(Visitation $visitation)
    {
        if ($visitation->department_id !== session('active_portal_dept_id')) {
            abort(403, 'Không có quyền cập nhật mục này.');
        }

        $visitation->update(['status' => 'completed']);

        return redirect()->back()->with('message', 'Đã hoàn thành chuyến thăm viếng!');
    }

    public function destroy(Visitation $visitation)
    {
        if ($visitation->department_id !== session('active_portal_dept_id')) {
            abort(403, 'Không có quyền xóa mục này.');
        }

        $visitation->visitors()->detach();
        $visitation->delete();

        return redirect()->back()->with('message', 'Đã xóa báo cáo thăm viếng ban ngành!');
    }
}
