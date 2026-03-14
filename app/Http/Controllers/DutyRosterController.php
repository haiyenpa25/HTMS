<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentRole;
use App\Models\DutyAssignment;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\RosterTemplate;
use App\Models\RosterTemplateRole;
use App\Exports\MeetingExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class DutyRosterController extends Controller
{
    private function resolvePortalContext(Request $request): array
    {
        $routeName = $request->route()->getName() ?? '';
        $isPortalRoute   = str_starts_with($routeName, 'portal.');
        $isMinistryRoute = str_starts_with($routeName, 'ministry.');

        if ($isPortalRoute || $isMinistryRoute) {
            $deptId     = session('active_portal_dept_id');
            $department = $deptId ? Department::find($deptId) : null;
            $portalType = $isMinistryRoute ? 'ministry' : 'activities';
        } else {
            $deptContext = $request->attributes->get('department');
            $department  = $deptContext ?? null;
            $portalType  = $request->attributes->get('portalType', 'deacon');
        }

        $routePrefix = 'duty-rooster.';
        if (str_starts_with($routeName, 'portal.')) $routePrefix = 'portal.duty-rooster.';
        elseif (str_starts_with($routeName, 'ministry.')) $routePrefix = 'ministry.duty-rooster.';
        elseif (str_starts_with($routeName, 'deacon.')) $routePrefix = 'deacon.duty-rooster.';

        $portalProps = [];
        if ($isPortalRoute || $isMinistryRoute) {
            $availableDepartments = app(\App\Services\PortalService::class)
                ->getAvailableDepartments(auth()->user(), $portalType);
            $portalProps = [
                'department'           => $department,
                'availableDepartments' => $availableDepartments,
                'isGlobalAdmin'        => auth()->user()?->hasAnyRole(['Super_Admin', 'Pastor', 'BTS_Admin']) ?? false,
                'portalType'           => $portalType,
            ];
        }

        return [
            'isPortal'    => $isPortalRoute || $isMinistryRoute,
            'department'  => $department,
            'thisDeptId'  => $department?->id,
            'portalType'  => $portalType,
            'routePrefix' => $routePrefix,
            'portalProps' => $portalProps,
        ];
    }

    // ── Export Meeting Assignments to Excel ────────────────
    public function exportMeeting(Meeting $meeting)
    {
        $filename = 'phan-cong-' . $meeting->date . '-' . str($meeting->topic ?? 'hop')->slug() . '.xlsx';
        return Excel::download(new MeetingExport($meeting), $filename);
    }

    public function index(Request $request)
    {
        $month       = $request->input('month', now()->format('Y-m'));
        $meetingType = $request->input('meeting_type', '');
        $startDate   = Carbon::parse($month)->startOfMonth();
        $endDate     = Carbon::parse($month)->endOfMonth();

        $ctx = $this->resolvePortalContext($request);
        $thisDeptId = $ctx['thisDeptId'];
        $isPortalRoute = $ctx['isPortal'];

        // ── Meetings query ──────────────────────────────────────────────
        $meetingsQuery = Meeting::whereBetween('date', [$startDate, $endDate])
            ->with(['dutyAssignments.role.department', 'dutyAssignments.member'])
            ->orderBy('date')->orderBy('time');

        if ($isPortalRoute) {
            // Portal context: only show THIS department's meetings (not church meetings)
            if ($thisDeptId) {
                $meetingsQuery->where('department_id', $thisDeptId)
                              ->where('type', 'department');
            }
        } else {
            // Deacon/other context: church + dept
            if ($thisDeptId) {
                $meetingsQuery->where(function ($query) use ($thisDeptId) {
                    $query->where('type', 'church')
                          ->orWhere('department_id', $thisDeptId);
                });
            }
        }

        if ($meetingType) {
            $meetingsQuery->where('type', $meetingType);
        }

        $meetings     = $meetingsQuery->get();

        $deptsQuery = Department::with(['dutyRoles' => fn($q) => $q->orderBy('section')->orderBy('sort_order')]);
        $templatesQuery = RosterTemplate::with('roles.departmentRole');

        if ($thisDeptId) {
            $deptsQuery->where('id', $thisDeptId);
            $templatesQuery->where('type', 'department')->where('department_id', $thisDeptId);
        } else {
            $templatesQuery->where('type', 'church');
        }

        $departments  = $deptsQuery->get();
        $templates    = $templatesQuery->get();
        $meetingTypes = Meeting::distinct()->pluck('type')->filter()->values()->toArray();

        return Inertia::render('DutyRoster/HolisticView', array_merge([
            'meetings'      => $meetings,
            'departments'   => $departments,
            'currentMonth'  => $month,
            'templates'     => $templates,
            'meetingTypes'  => $meetingTypes,
            'filters'       => ['meeting_type' => $meetingType],
            'isPortal'      => $ctx['isPortal'],
            'portalType'    => $ctx['portalType'],
            'routePrefix'   => $ctx['routePrefix'],
        ], $ctx['portalProps']));
    }


    public function show(Request $request, Meeting $meeting)
    {
        $meeting->load(['dutyAssignments.role.department', 'dutyAssignments.member', 'department']);

        $ctx = $this->resolvePortalContext($request);
        $thisDeptId = $ctx['thisDeptId'];

        // Verify Access
        if ($thisDeptId && $meeting->type === 'department' && $meeting->department_id !== $thisDeptId) {
            abort(403, 'Bạn không có quyền truy cập buổi nhóm của ban ngành khác.');
        }

        // Only departments that have roles
        $deptsQuery = Department::with([
            'dutyRoles' => fn($q) => $q->orderBy('section')->orderBy('sort_order')
        ]);
        if ($thisDeptId) {
             if ($meeting->type !== 'church') {
                 $deptsQuery->where('id', $thisDeptId);
             }
        }
        $departments = $deptsQuery->get();

        $members   = Member::orderBy('full_name')->get(['id', 'full_name']);
        
        $templatesQuery = RosterTemplate::with('roles.departmentRole');
        if ($thisDeptId) {
            $templatesQuery->where('type', 'department')->where('department_id', $thisDeptId);
        } else {
            $templatesQuery->where('type', 'church');
        }
        $templates = $templatesQuery->get();

        // Speakers list
        $speakers  = DB::table('speakers')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'title', 'is_external']);

        // Build dept→members map
        $deptMembersRaw = DB::table('org_memberships')
            ->join('members', 'members.id', '=', 'org_memberships.member_id')
            ->whereNull('members.deleted_at')
            ->where('org_memberships.model_type', 'App\\Models\\Department')
            ->where('org_memberships.is_active', 1)
            ->select('org_memberships.model_id as department_id', 'members.id', 'members.full_name')
            ->orderBy('members.full_name')
            ->get();
        $deptMembers = $deptMembersRaw->groupBy('department_id')
            ->map(fn($group) => $group->map(fn($m) => ['id' => $m->id, 'full_name' => $m->full_name]))
            ->toArray();

        // Determine which departments the logged-in user can edit
        $user = auth()->user();
        $authDeptIds = [];
        if ($user) {
            if ($user->hasAnyRole(['Super_Admin', 'Pastor', 'BTS_Admin'])) {
                $authDeptIds = $departments->pluck('id')->toArray();
            } else {
                $authDeptIds = DB::table('org_memberships')
                    ->where('member_id', $user->member_id ?? 0)
                    ->where('model_type', 'App\\Models\\Department')
                    ->pluck('model_id')->toArray();
            }
        }

        return Inertia::render('DutyRoster/Show', array_merge([
            'meeting'     => $meeting,
            'departments' => $departments,
            'members'     => $members,
            'speakers'    => $speakers,
            'deptMembers' => $deptMembers,
            'templates'   => $templates,
            'authDeptIds' => $authDeptIds,
            'isPortal'    => $ctx['isPortal'],
            'portalType'  => $ctx['portalType'],
            'routePrefix' => $ctx['routePrefix'],
        ], $ctx['portalProps']));
    }


    public function templatesIndex(Request $request)
    {
        $ctx = $this->resolvePortalContext($request);
        $thisDeptId = $ctx['thisDeptId'];

        $templatesQuery = RosterTemplate::with(['roles.departmentRole.department']);
        $deptsQuery = Department::with(['dutyRoles' => fn($q) => $q->orderBy('section')->orderBy('sort_order')]);

        if ($thisDeptId) {
            $templatesQuery->where('type', 'department')->where('department_id', $thisDeptId);
            $deptsQuery->where('id', $thisDeptId);
        } else {
            $templatesQuery->where('type', 'church');
        }

        $templates = $templatesQuery->get();
        $departments = $deptsQuery->get();

        return Inertia::render('DutyRoster/Templates/Index', array_merge([
            'templates'   => $templates,
            'departments' => $departments,
            'isPortal'    => $ctx['isPortal'],
            'portalType'  => $ctx['portalType'],
            'routePrefix' => $ctx['routePrefix'],
        ], $ctx['portalProps']));
    }

    public function templateCreate(Request $request)
    {
        $ctx = $this->resolvePortalContext($request);
        $thisDeptId = $ctx['thisDeptId'];

        $deptsQuery = Department::with(['dutyRoles' => fn($q) => $q->orderBy('section')->orderBy('sort_order')]);
        if ($thisDeptId) {
            $deptsQuery->where('id', $thisDeptId);
        }
        $departments = $deptsQuery->get();
        
        return Inertia::render('DutyRoster/Templates/Create', array_merge([
            'departments'   => $departments,
            'defaultType'   => $thisDeptId ? 'department' : 'church',
            'defaultDeptId' => $thisDeptId,
            'isPortal'      => $ctx['isPortal'],
            'portalType'    => $ctx['portalType'],
            'routePrefix'   => $ctx['routePrefix'],
        ], $ctx['portalProps']));
    }

    // ── Template Show/Edit ─────────────────────────────────
    public function templateShow(Request $request, RosterTemplate $template)
    {
        $ctx = $this->resolvePortalContext($request);
        $thisDeptId = $ctx['thisDeptId'];

        // Verify Access
        if ($thisDeptId) {
            if ($template->type === 'department' && $template->department_id !== $thisDeptId) {
                abort(403, 'Bạn không thể chỉnh sửa mẫu phân công của ban ngành khác.');
            }
        }

        $template->load(['roles.departmentRole.department']);
        
        $deptsQuery = Department::with(['dutyRoles' => fn($q) => $q->orderBy('section')->orderBy('sort_order')]);
        if ($thisDeptId && $template->type === 'department') {
            $deptsQuery->where('id', $thisDeptId); // Only allow adding their own roles to department template
        }
        $departments = $deptsQuery->get();

        // Get dept IDs that have roles in this template
        $participatingDeptIds = $template->roles
            ->map(fn($r) => $r->departmentRole?->department_id)
            ->filter()->unique()->values()->toArray();

        return Inertia::render('DutyRoster/Templates/Show', array_merge([
            'template'             => $template,
            'departments'          => $departments,
            'participatingDeptIds' => $participatingDeptIds,
            'isPortal'             => $ctx['isPortal'],
            'portalType'           => $ctx['portalType'],
            'routePrefix'          => $ctx['routePrefix'],
        ], $ctx['portalProps']));
    }

    // ── Store Template (POST) ──────────────────────────────
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'type'          => 'required|in:church,department',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $template = RosterTemplate::create($validated);

        $ctx = $this->resolvePortalContext($request);
        if ($request->expectsJson()) {
            return response()->json(['id' => $template->id, 'message' => 'Đã tạo mẫu.']);
        }

        return redirect()->route($ctx['routePrefix'] . 'templates.show', $template)
            ->with('success', 'Đã tạo mẫu phân công mới.');
    }

    // ── Update Template (PUT) ──────────────────────────────
    public function updateTemplate(Request $request, RosterTemplate $template)
    {
        $validated = $request->validate([
            'name'             => 'sometimes|required|string|max:255',
            'description'      => 'nullable|string',
            'type'             => 'sometimes|required|in:church,department',
            'department_id'    => 'nullable|exists:departments,id',
            'dept_ids'         => 'sometimes|array',      // participating dept IDs
            'dept_ids.*'       => 'exists:departments,id',
        ]);

        $template->update(array_filter($validated, fn($v, $k) => in_array($k, ['name', 'description']), ARRAY_FILTER_USE_BOTH));

        return back()->with('success', 'Đã cập nhật mẫu.');
    }

    // ── Apply Template to a Meeting ────────────────────────
    public function applyTemplate(Request $request)
    {
        $validated = $request->validate([
            'meeting_id'  => 'required|exists:meetings,id',
            'template_id' => 'required|exists:roster_templates,id',
        ]);

        $template = RosterTemplate::with('roles.departmentRole')->find($validated['template_id']);
        if (!$template) return back()->with('error', 'Không tìm thấy mẫu.');

        // Clear existing empty positions to avoid slot accumulation from prior template misapplications
        DutyAssignment::where('meeting_id', $validated['meeting_id'])
            ->whereNull('member_id')
            ->delete();

        foreach ($template->roles as $templateRole) {
            $maxCount = $templateRole->departmentRole->max_count ?? 1;
            for ($slot = 1; $slot <= $maxCount; $slot++) {
                DutyAssignment::firstOrCreate([
                    'meeting_id'         => $validated['meeting_id'],
                    'department_role_id' => $templateRole->department_role_id,
                    'slot'               => $slot,
                ]);
            }
        }

        return back()->with('success', 'Đã áp dụng mẫu phân công.');
    }

    // ── Delete Template ────────────────────────────────────
    public function deleteTemplate(RosterTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Đã xóa mẫu phân công.');
    }

    // ── Add Role to Template ───────────────────────────────
    public function addTemplateRole(Request $request, RosterTemplate $template)
    {
        $validated = $request->validate(['role_id' => 'required|exists:department_roles,id']);
        $template->roles()->firstOrCreate(['department_role_id' => $validated['role_id']]);
        return response()->json(['ok' => true]);
    }

    // ── Remove Role from Template ──────────────────────────
    public function removeTemplateRole(RosterTemplate $template, DepartmentRole $role)
    {
        $template->roles()->where('department_role_id', $role->id)->delete();
        return response()->json(['ok' => true]);
    }

    // ── Store Role ─────────────────────────────────────────
    public function storeRole(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'section'    => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'max_count'  => 'nullable|integer|min:1|max:50',
        ]);

        $role = $department->dutyRoles()->create([
            'name'       => $validated['name'],
            'section'    => $validated['section'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'max_count'  => $validated['max_count'] ?? 1,
        ]);

        if ($request->expectsJson()) {
            return response()->json($role->fresh());
        }

        return back()->with('success', 'Đã thêm vai trò mới.');
    }

    // ── Delete Role ────────────────────────────────────────
    public function destroyRole(DepartmentRole $role)
    {
        $role->delete();
        return back()->with('success', 'Đã xóa vai trò.');
    }

    // ── Store Assignment ───────────────────────────────────
    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'meeting_id'         => 'required|exists:meetings,id',
            'department_role_id' => 'required|exists:department_roles,id',
            'slot'               => 'nullable|integer|min:1',
            'member_id'          => 'nullable|exists:members,id',
            'notes'              => 'nullable|string',
        ]);

        $slot = $validated['slot'] ?? 1;

        // Collision: warn if member already has another role in same meeting
        if ($validated['member_id']) {
            $exists = DutyAssignment::where('meeting_id', $validated['meeting_id'])
                ->where('member_id', $validated['member_id'])
                ->where('department_role_id', '!=', $validated['department_role_id'])
                ->with('role')->first();

            if ($exists) {
                DutyAssignment::updateOrCreate(
                    ['meeting_id' => $validated['meeting_id'], 'department_role_id' => $validated['department_role_id'], 'slot' => $slot],
                    ['member_id' => $validated['member_id'], 'notes' => $validated['notes'] ?? null]
                );
                return response()->json([
                    'message' => 'Đã cập nhật.',
                    'warning' => "Nhân sự này cũng phụ trách '{$exists->role->name}' trong cùng buổi lễ.",
                ]);
            }
        }

        DutyAssignment::updateOrCreate(
            ['meeting_id' => $validated['meeting_id'], 'department_role_id' => $validated['department_role_id'], 'slot' => $slot],
            ['member_id' => $validated['member_id'] ?? null, 'notes' => $validated['notes'] ?? null]
        );

        if (!empty($validated['member_id'])) {
            $member = \App\Models\Member::with('user')->find($validated['member_id']);
            if ($member && $member->user) {
                $meeting = \App\Models\Meeting::with('department')->find($validated['meeting_id']);
                $role = \App\Models\DepartmentRole::find($validated['department_role_id']);
                if ($meeting && $role) {
                    $member->user->notify(new \App\Notifications\DutyAssignedNotification($meeting, $role->name));
                }
            }
        }

        return response()->json(['message' => 'Đã cập nhật phân công.']);
    }

    // ── Copy Week ──────────────────────────────────────────
    public function copyWeek(Request $request)
    {
        $sourceAssignments = DutyAssignment::where('meeting_id', $request->source_meeting_id)->get();
        foreach ($sourceAssignments as $a) {
            DutyAssignment::updateOrCreate(
                ['meeting_id' => $request->target_meeting_id, 'department_role_id' => $a->department_role_id, 'slot' => $a->slot ?? 1],
                ['member_id' => $a->member_id, 'notes' => $a->notes]
            );
        }
        return back()->with('success', 'Đã sao chép phân công từ buổi trước.');
    }
}
