<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Member;
use App\Models\OrgMembership;
use App\Models\OrgRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PortalMemberController extends Controller
{
    private function mapRoleCodeToFrontend($code) {
        $map = [
            'tb' => 'TruongBan',
            'pb' => 'PhoBan',
            'tk' => 'ThuKy',
            'tq' => 'ThuQuy',
            'uv' => 'UyVien',
            'bv' => 'Member',
            'cs' => 'Member',
            'tkhu' => 'Member',
            'ptk' => 'Member',
            'tqht' => 'Member',
            'ptq' => 'Member',
        ];
        return $map[$code] ?? 'Member';
    }

    private function mapFrontendToRoleCode($frontend) {
        $map = [
            'TruongBan' => 'tb',
            'PhoBan' => 'pb',
            'ThuKy' => 'tk',
            'ThuQuy' => 'tq',
            'UyVien' => 'uv',
            'Member' => 'bv'
        ];
        return $map[$frontend] ?? 'bv';
    }

    private function getContext()
    {
        $isMinistry = request()->is('ministry/*');
        return [
            'type' => $isMinistry ? 'ministry' : 'portal',
            'session_key' => $isMinistry ? 'active_ministry_dept_id' : 'active_portal_dept_id',
            'route_prefix' => $isMinistry ? 'ministry.members' : 'portal.members',
            'base_route' => $isMinistry ? 'ministry.index' : 'portal.index',
        ];
    }

    /**
     * Display the portal members view (Board and All Members)
     */
    public function index(Request $request)
    {
        $context = $this->getContext();
        $departmentId = tap(session($context['session_key']), function ($id) use ($context) {
            if (!$id) abort(redirect()->route($context['base_route']));
        });

        $department = Department::findOrFail($departmentId);
        
        // Ensure user has access to portal first, then check specific members permission
        Gate::authorize('access_portal', [Department::class, $department]);
        Gate::authorize('portal_view_all_members', Member::class);

        // Required generic portal layout info
        $availableDepartments = $this->getAvailableDepartments();
        $teams = $department->teams()->select('id', 'name')->get();

        // 1. Board Members: People with an active leadership role in this department
        $boardRoleCodes = ['tb', 'pb', 'tk', 'tq', 'uv'];
        $boardRoleIds = OrgRole::whereIn('code', $boardRoleCodes)->pluck('id')->toArray();
        
        $boardMembersWithPivot = Member::whereHas('memberships', function($q) use ($departmentId, $boardRoleIds) {
            $q->where('model_type', Department::class)
              ->where('model_id', $departmentId)
              ->whereIn('org_role_id', $boardRoleIds);
        })->with(['memberships' => function($q) use ($departmentId) {
            $q->where('model_type', Department::class)
              ->where('model_id', $departmentId)
              ->with('role');
        }, 'teams' => function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        }])->get();

        // Map it nicely for the frontend cards
        $boardMembers = $boardMembersWithPivot->map(function($member) {
            $membership = $member->memberships->first();
            $frontendRole = $membership && $membership->role ? $this->mapRoleCodeToFrontend($membership->role->code) : 'Member';
            $team = $member->teams->first();
            
            return [
                'id' => $member->id,
                'full_name' => $member->full_name,
                'firstName' => explode(' ', $member->full_name)[count(explode(' ', $member->full_name))-1],
                'phone' => $member->phone,
                'email' => $member->email,
                'org_role' => $frontendRole,
                'team_id' => $team->id ?? null,
                'team_name' => $team->name ?? null,
                'joined_date' => $membership ? $membership->join_date : null,
                'status' => $member->status,
                'avatar_url' => $member->avatar_url ?? null,
            ];
        });

        // 2. All Members of this department (Paginated, Filterable)
        $search = $request->input('search');
        $allMembersQuery = Member::whereHas('memberships', function($q) use ($departmentId) {
            $q->where('model_type', Department::class)
              ->where('model_id', $departmentId);
        })->with(['memberships' => function($q) use ($departmentId) {
             $q->where('model_type', Department::class)
               ->where('model_id', $departmentId)
               ->with('role');
        }, 'teams' => function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        }]);

        if ($search) {
            $allMembersQuery->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $allMembers = $allMembersQuery->paginate(15)->through(function ($member) {
             $membership = $member->memberships->first();
             $frontendRole = $membership && $membership->role ? $this->mapRoleCodeToFrontend($membership->role->code) : 'Member';
             $team = $member->teams->first();
             
             return [
                 'id' => $member->id,
                 'full_name' => $member->full_name,
                 'phone' => $member->phone,
                 'email' => $member->email,
                 'status' => $member->status,
                 'gender' => $member->gender,
                 'org_role' => $frontendRole,
                 'team_id' => $team->id ?? null,
                 'team_name' => $team->name ?? null,
             ];
        })->withQueryString();

        return Inertia::render('Portal/Members/Index', [
            'department' => $department,
            'teams' => $teams,
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin' => auth()->user()->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin']),
            'boardMembers' => $boardMembers,
            'members' => $allMembers,
            'filters' => [
                'search' => $search
            ],
            'routePrefix' => $context['route_prefix'],
            'portalType' => $context['type'],
        ]);
    }

    /**
     * Update a member's organizational role and team within the portal
     */
    public function updateRole(Request $request, Member $member)
    {
        $context = $this->getContext();
        $departmentId = tap(session($context['session_key']), function ($id) use ($context) {
            if (!$id) abort(redirect()->route($context['base_route']));
        });

        // Check permission to manage the board
        Gate::authorize('portal_manage_board', Member::class);

        $validated = $request->validate([
            'org_role' => 'required|string|in:TruongBan,PhoBan,ThuKy,ThuQuy,UyVien,Member',
            'team_id' => 'nullable|integer',
        ]);

        $roleCode = $this->mapFrontendToRoleCode($validated['org_role']);
        $roleId = OrgRole::where('code', $roleCode)->value('id');
        
        if (!$roleId) {
            return back()->with('error', 'Chức danh không hợp lệ trong hệ thống.');
        }

        \DB::transaction(function () use ($departmentId, $member, $roleId, $validated) {
            // 1. Update or Create the department membership role
            OrgMembership::updateOrCreate(
                ['model_type' => Department::class, 'model_id' => $departmentId, 'member_id' => $member->id],
                ['org_role_id' => $roleId]
            );

            // 2. Manage team assignment
            $department = Department::findOrFail($departmentId);
            $departmentTeamIds = $department->teams()->pluck('id')->toArray();
            
            // Clear existing team memberships for this department
            if (!empty($departmentTeamIds)) {
                OrgMembership::where('member_id', $member->id)
                    ->where('model_type', \App\Models\Team::class)
                    ->whereIn('model_id', $departmentTeamIds)
                    ->delete();
            }

            // Assign to new team if provided
            if (!empty($validated['team_id'])) {
                $teamRoleId = OrgRole::where('code', 'bv')->value('id');
                if (in_array($validated['team_id'], $departmentTeamIds) && $teamRoleId) {
                    OrgMembership::create([
                        'model_type' => \App\Models\Team::class,
                        'model_id' => $validated['team_id'],
                        'member_id' => $member->id,
                        'org_role_id' => $teamRoleId,
                    ]);
                }
            }
        });

        return back()->with('success', 'Đã cập nhật chức danh và tổ thành công.');
    }

    /**
     * Bulk assign members to a specific team
     */
    public function bulkAssignTeam(Request $request)
    {
        $context = $this->getContext();
        $departmentId = tap(session($context['session_key']), function ($id) use ($context) {
            if (!$id) abort(redirect()->route($context['base_route']));
        });

        Gate::authorize('portal_manage_board', Member::class);

        $validated = $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|exists:members,id',
            'team_id' => 'required|integer|exists:teams,id',
        ]);

        $department = Department::findOrFail($departmentId);
        $departmentTeamIds = $department->teams()->pluck('id')->toArray();

        // Ensure the target team belongs to the active department
        if (!in_array($validated['team_id'], $departmentTeamIds)) {
            return back()->with('error', 'Tổ này không thuộc ban ngành hiện tại.');
        }

        $teamRoleId = OrgRole::where('code', 'bv')->value('id');
        if (!$teamRoleId) {
            return back()->with('error', 'Không tìm thấy mã chức danh Thành viên hệ thống.');
        }

        \DB::transaction(function () use ($validated, $departmentTeamIds, $teamRoleId) {
            // Remove standard team assignments for these members within the current department
            OrgMembership::whereIn('member_id', $validated['member_ids'])
                ->where('model_type', \App\Models\Team::class)
                ->whereIn('model_id', $departmentTeamIds)
                ->delete();

            // Insert new team assignments
            $insertData = [];
            foreach ($validated['member_ids'] as $memberId) {
                $insertData[] = [
                    'model_type' => \App\Models\Team::class,
                    'model_id' => $validated['team_id'],
                    'member_id' => $memberId,
                    'org_role_id' => $teamRoleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            OrgMembership::insert($insertData);
        });

        return back()->with('success', 'Đã phân tổ hàng loạt thành công.');
    }

    private function getAvailableDepartments()
    {
        $isMinistry = request()->is('ministry/*');
        $block = $isMinistry ? 'ministry' : 'activities';

        if (auth()->user()->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin'])) {
            return Department::where('block', $block)->get();
        } 
        
        return Department::where('block', $block)
            ->whereHas('memberships', function($q) {
                $q->where('member_id', auth()->user()->member_id ?? 0);
            })->get();
    }
}

