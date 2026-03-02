<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Department;
use App\Models\Member;
use App\Models\Visitation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class VisitationController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view_visitations');

        $user = auth()->user();
        $departmentId = session('active_ministry_dept_id');
        
        if ($departmentId) {
            $department = Department::findOrFail($departmentId);
            // Since this is ministry context, we should probably check access_department_portal but ensure it's ministry
            Gate::authorize('access_portal', [Department::class, $department]);
        }

        $query = Visitation::with(['member', 'visitors', 'department']);

        // Data Isolation
        if (!$user->hasRole(['Pastor', 'Super_Admin', 'Visitation_Staff'])) {
            // Department leads only see their department's visitations
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            } else {
                // If no active department and not a generic staff, show nothing or just their own
                $query->whereRaw('1 = 0'); 
            }
        }

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
        
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('visit_date', $request->month)
                  ->whereYear('visit_date', $request->year);
        } else if ($request->filled('period')) {
            $now = now();
            if ($request->period === '1m') {
                $query->where('visit_date', '>=', $now->copy()->subMonth());
            } elseif ($request->period === '3m') {
                $query->where('visit_date', '>=', $now->copy()->subMonths(3));
            } elseif ($request->period === '6m') {
                $query->where('visit_date', '>=', $now->copy()->subMonths(6));
            } elseif ($request->period === '1y') {
                $query->where('visit_date', '>=', $now->copy()->subYear());
            }
        }

        $query->orderBy('visit_date', 'desc');

        $visitations = $query->paginate(15)->through(function ($visitation) use ($user) {
            // Sensitive Content Filtering
            if (!Gate::allows('viewSensitiveContent', $visitation)) {
                $visitation->content = '*** (Chỉ Mục sư & Người thăm viếng được xem) ***';
            }
            return $visitation;
        });
        
        // Allowed to manage?
        $canManage = Gate::allows('manage_visitations') || $user->hasPermissionTo('create_visitation_requests');

        $membersQuery = Member::query();
        if ($departmentId) {
             $membersQuery->whereHas('memberships', function($q) use ($departmentId) {
                 $q->where('model_type', Department::class)
                   ->where('model_id', $departmentId);
             });
        }
        
        $members = $membersQuery->with(['memberships' => function($q) use ($departmentId) {
            if ($departmentId) {
                $q->where('model_type', Department::class)
                  ->where('model_id', $departmentId)
                  ->with('role');
            }
        }])->orderBy('full_name')->get(['id', 'full_name', 'phone']);

        // Smart Suggestions List
        $suggestions = collect();
        if (($departmentId && Department::find($departmentId)->code === 'BTV') || $user->hasRole(['Pastor', 'Super_Admin', 'Visitation_Staff'])) {
            $ministryDepts = Department::where('block', 'ministry')->pluck('id')->toArray();
            
            $recentMeetingsByDept = \App\Models\Meeting::whereIn('department_id', $ministryDepts)
                ->orderBy('date', 'desc')
                ->get()
                ->groupBy('department_id')
                ->map(fn($meetings) => $meetings->take(3)->pluck('id')->toArray())
                ->toArray();
                
            $recentMeetingIds = collect($recentMeetingsByDept)->flatten()->toArray();

            $suggestedMembers = Member::with(['visitations' => function($q) {
                $q->orderBy('visit_date', 'desc');
            }, 'attendances' => function($q) use ($recentMeetingIds) {
                $q->whereIn('meeting_id', $recentMeetingIds);
            }, 'memberships' => function($q) use ($ministryDepts) {
                $q->whereIn('model_id', $ministryDepts)->where('model_type', Department::class);
            }])->get(['id', 'full_name', 'phone', 'address', 'latitude', 'longitude', 'visit_location']);

            foreach ($suggestedMembers as $m) {
                $lastVisit = $m->visitations->first();
                $lastVisitDate = $lastVisit ? \Carbon\Carbon::parse($lastVisit->visit_date) : null;
                $monthsSinceLastVisit = $lastVisitDate ? $lastVisitDate->diffInMonths(now()) : 999;

                $missed3InAnyDept = false;
                foreach ($m->memberships as $membership) {
                    $deptId = $membership->model_id;
                    if (isset($recentMeetingsByDept[$deptId]) && count($recentMeetingsByDept[$deptId]) >= 3) {
                        $absentCount = 0;
                        foreach ($recentMeetingsByDept[$deptId] as $mId) {
                            $att = $m->attendances->firstWhere('meeting_id', $mId);
                            if (!$att || $att->status === 'absent') {
                                $absentCount++;
                            }
                        }
                        if ($absentCount >= 3) {
                            $missed3InAnyDept = true;
                            break;
                        }
                    }
                }

                $priority = 'normal';
                $reasons = [];

                if ($missed3InAnyDept) {
                    $reasons[] = 'Vắng nhóm 3 lần liên tiếp';
                }
                if ($monthsSinceLastVisit >= 6) {
                    $reasons[] = 'Chưa được thăm > 6 tháng';
                }

                if ($missed3InAnyDept && $monthsSinceLastVisit >= 6) {
                    $priority = 'high';
                } elseif (count($reasons) > 0) {
                    $priority = 'medium';
                }

                if ($priority !== 'normal') {
                    $suggestions->push([
                        'id' => $m->id,
                        'full_name' => $m->full_name,
                        'phone' => $m->phone,
                        'priority' => $priority,
                        'reasons' => $reasons,
                        'address' => $m->address,
                        'visit_location' => $m->visit_location,
                        'latitude' => $m->latitude,
                        'longitude' => $m->longitude,
                    ]);
                }
            }

            $suggestions = $suggestions->sortBy(function($s) {
                return $s['priority'] === 'high' ? 0 : 1;
            })->values();
        }

        return Inertia::render('Portal/Visitation/Index', [
            'visitations' => $visitations,
            'members' => $members,
            'suggestions' => $suggestions,
            'filters' => $request->only(['reason', 'period', 'search', 'month', 'year']),
            'canManage' => $canManage,
            'visitationTypes' => ['church' => 'Hội Thánh', 'department' => 'Ban Ngành'],
            'reasons' => ['ốm đau', 'mới tin Chúa', 'khích lệ', 'khác'],
            // pass some department context
            'department' => $departmentId ? Department::find($departmentId) : null,
            'isGlobalAdmin' => $user->hasRole(['Pastor', 'Super_Admin', 'Visitation_Staff']),
            'routePrefix' => 'ministry.visitation',
            'portalType' => 'ministry',
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Visitation::class);

        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'visitation_type' => 'required|in:church,department',
            'visit_date' => 'required|date',
            'reason' => 'required|in:ốm đau,mới tin Chúa,khích lệ,khác',
            'content' => 'nullable|string',
            'prayer_points' => 'nullable|string',
            'gifts' => 'nullable|string',
            'status' => 'required|in:planned,completed,cancelled',
            'priority' => 'required|in:high,medium,normal',
            'visitor_ids' => 'required|array|min:1',
            'visitor_ids.*' => 'exists:members,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $departmentId = session('active_ministry_dept_id');
        if ($validated['visitation_type'] === 'department') {
            $validated['department_id'] = $departmentId;
        } else {
             $validated['department_id'] = null;
        }

        DB::transaction(function () use ($validated) {
            $visitation = Visitation::create(\Illuminate\Support\Arr::except($validated, ['latitude', 'longitude']));
            $visitation->visitors()->sync($validated['visitor_ids']);
            
            if (isset($validated['latitude']) && isset($validated['longitude'])) {
                Member::where('id', $validated['member_id'])->update([
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Đã lưu thông tin thăm viếng thành công.');
    }

    public function update(Request $request, Visitation $visitation)
    {
        Gate::authorize('update', $visitation);

        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'visitation_type' => 'required|in:church,department',
            'visit_date' => 'required|date',
            'reason' => 'required|in:ốm đau,mới tin Chúa,khích lệ,khác',
            'content' => 'nullable|string',
            'prayer_points' => 'nullable|string',
            'gifts' => 'nullable|string',
            'status' => 'required|in:planned,completed,cancelled',
            'priority' => 'required|in:high,medium,normal',
            'visitor_ids' => 'required|array|min:1',
            'visitor_ids.*' => 'exists:members,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $departmentId = session('active_ministry_dept_id');
        if ($validated['visitation_type'] === 'department') {
            $validated['department_id'] = $departmentId;
        } else {
             $validated['department_id'] = null;
        }

        DB::transaction(function () use ($visitation, $validated) {
            $visitation->update(\Illuminate\Support\Arr::except($validated, ['latitude', 'longitude']));
            $visitation->visitors()->sync($validated['visitor_ids']);
            
            if (isset($validated['latitude']) && isset($validated['longitude'])) {
                Member::where('id', $validated['member_id'])->update([
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                ]);
            }
        });

        return redirect()->back()->with('success', 'Đã cập nhật thông tin thăm viếng thành công.');
    }

    public function destroy(Visitation $visitation)
    {
        Gate::authorize('delete', $visitation);
        
        $visitation->delete();

        return redirect()->back()->with('success', 'Đã xoá thông tin thăm viếng.');
    }
}

