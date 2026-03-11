<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Member;
use App\Models\OrgMembership;
use App\Models\OrgRole;
use App\Exports\PortalMemberExport;
use App\Imports\PortalMemberImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
        $isDeacon   = request()->is('deacon/*');

        if ($isDeacon) {
            return [
                'type'         => 'deacon',
                'session_key'  => 'active_deacon_dept_id',
                'route_prefix' => 'deacon.members',
                'base_route'   => 'deacon.index',
            ];
        }

        return [
            'type'         => $isMinistry ? 'ministry' : 'activities',
            'session_key'  => $isMinistry ? 'active_ministry_dept_id' : 'active_portal_dept_id',
            'route_prefix' => $isMinistry ? 'ministry.members' : 'portal.members',
            'base_route'   => $isMinistry ? 'ministry.index' : 'portal.index',
        ];
    }

    /**
     * Helper: get the current dept ID based on portal context
     */
    private function getDeptId(array $context): int
    {
        if ($context['type'] === 'deacon') {
            return (int) session($context['session_key'], 1);
        }
        return tap(session($context['session_key']), function ($id) use ($context) {
            if (!$id) abort(redirect()->route($context['base_route']));
        });
    }

    /**
     * Display the portal members view (Board and All Members)
     */
    public function index(Request $request)
    {
        $context = $this->getContext();

        // Deacon portal: fallback sang dept cứng (Ban Chấp Sự ID=1) nếu chưa set session
        if ($context['type'] === 'deacon') {
            $departmentId = session($context['session_key'], 1); // mặc định Ban Chấp Sự
        } else {
            $departmentId = tap(session($context['session_key']), function ($id) use ($context) {
                if (!$id) abort(redirect()->route($context['base_route']));
            });
        }

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
                'is_active' => $membership ? $membership->is_active : 1,
                'avatar_url' => $member->avatar_url ?? null,
                'user_id' => $member->user_id,
            ];
        });

        // 2. All Members of this department (Paginated, Filterable)
        $search  = $request->input('search');
        $teamId  = $request->input('team_id');
        $orgRole = $request->input('org_role'); // frontend key (TruongBan, PhoBan, ...)
        $activeStatus = $request->input('active_status'); // 'active' or 'inactive'

        // Map frontend role key → backend org_role code
        $roleCodeMap = [
            'TruongBan' => 'tb', 'PhoBan' => 'pb', 'ThuKy' => 'tk',
            'ThuQuy' => 'tq', 'UyVien' => 'uv', 'Member' => 'bv',
        ];
        $roleCode = $orgRole ? ($roleCodeMap[$orgRole] ?? null) : null;

        $allMembersQuery = Member::whereHas('memberships', function($q) use ($departmentId, $teamId, $roleCode, $activeStatus) {
            $q->where('model_type', Department::class)
              ->where('model_id', $departmentId);
            // Filter by org_role code
            if ($roleCode) {
                $q->whereHas('role', fn($r) => $r->where('code', $roleCode));
            }
            // Filter by active status
            if ($activeStatus === 'active') {
                $q->where('is_active', 1);
            } elseif ($activeStatus === 'inactive') {
                $q->where('is_active', 0);
            }
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

        // Filter by team
        if ($teamId) {
            $allMembersQuery->whereHas('teams', function($q) use ($teamId, $departmentId) {
                $q->where('teams.id', $teamId)
                  ->where('department_id', $departmentId);
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
                 'is_active' => $membership ? $membership->is_active : 1,
                 'gender' => $member->gender,
                 'org_role' => $frontendRole,
                 'team_id' => $team->id ?? null,
                 'team_name' => $team->name ?? null,
                 'user_id' => $member->user_id,
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
                'search'   => $search,
                'team_id'  => $teamId ?? null,
                'org_role' => $orgRole ?? null,
                'active_status' => $activeStatus ?? null,
            ],
            'routePrefix' => $context['route_prefix'],
            'portalType' => $context['type'],
        ]);
    }

    /**
     * Toggle active status of a member in the current department
     */
    public function toggleActiveStatus(Request $request, Member $member)
    {
        $context = $this->getContext();
        $departmentId = $this->getDeptId($context);

        Gate::authorize('portal_manage_board', Member::class);

        $membership = OrgMembership::where('model_type', Department::class)
            ->where('model_id', $departmentId)
            ->where('member_id', $member->id)
            ->firstOrFail();

        $membership->is_active = !$membership->is_active;
        $membership->save();

        $statusStr = $membership->is_active ? 'khôi phục sinh hoạt' : 'tạm dừng sinh hoạt';
        return back()->with('success', "Đã {$statusStr} cho tín hữu {$member->full_name}.");
    }

    /**
     * Update a member's organizational role and team within the portal
     */
    public function updateRole(Request $request, Member $member)
    {
        $context = $this->getContext();
        $departmentId = $this->getDeptId($context);


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
        $departmentId = $this->getDeptId($context);

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

    /**
     * Bulk toggle active status of members in the current department
     */
    public function bulkToggleActive(Request $request)
    {
        $context = $this->getContext();
        $departmentId = $this->getDeptId($context);

        Gate::authorize('portal_manage_board', Member::class);

        $validated = $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|exists:members,id',
            'is_active' => 'required|boolean',
        ]);

        OrgMembership::where('model_type', Department::class)
            ->where('model_id', $departmentId)
            ->whereIn('member_id', $validated['member_ids'])
            ->update(['is_active' => $validated['is_active']]);

        $statusStr = $validated['is_active'] ? 'khôi phục' : 'tạm dừng';
        return back()->with('success', "Đã {$statusStr} sinh hoạt cho " . count($validated['member_ids']) . " tín hữu.");
    }

    private function getAvailableDepartments()
    {
        $isMinistry = request()->is('ministry/*');
        $isDeacon   = request()->is('deacon/*');

        if ($isDeacon) {
            $block = 'leadership';
        } elseif ($isMinistry) {
            $block = 'ministry';
        } else {
            $block = 'activities';
        }

        return app(\App\Services\PortalService::class)->getAvailableDepartments(auth()->user(), $block);
    }

    /**
     * Remove a member from the current department portal
     */
    public function removeMember(Request $request, Member $member)
    {
        $context = $this->getContext();
        $departmentId = $this->getDeptId($context);

        Gate::authorize('portal_manage_board', Member::class);


        \DB::transaction(function () use ($departmentId, $member) {
            // Remove from department
            OrgMembership::where('member_id', $member->id)
                ->where('model_type', Department::class)
                ->where('model_id', $departmentId)
                ->delete();

            // Remove from teams within department
            $department = Department::findOrFail($departmentId);
            $departmentTeamIds = $department->teams()->pluck('id')->toArray();
            
            if (!empty($departmentTeamIds)) {
                OrgMembership::where('member_id', $member->id)
                    ->where('model_type', \App\Models\Team::class)
                    ->whereIn('model_id', $departmentTeamIds)
                    ->delete();
            }
        });

        return back()->with('success', 'Đã xóa tín hữu khỏi ban ngành.');
    }

    /**
     * Bulk remove members from the current department portal
     */
    public function bulkRemove(Request $request)
    {
        $context = $this->getContext();
        $departmentId = $this->getDeptId($context);

        Gate::authorize('portal_manage_board', Member::class);

        $validated = $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|exists:members,id'
        ]);

        \DB::transaction(function () use ($departmentId, $validated) {
            $memberIds = $validated['member_ids'];

            // Remove from department
            OrgMembership::whereIn('member_id', $memberIds)
                ->where('model_type', Department::class)
                ->where('model_id', $departmentId)
                ->delete();

            // Remove from teams within department
            $department = Department::findOrFail($departmentId);
            $departmentTeamIds = $department->teams()->pluck('id')->toArray();
            
            if (!empty($departmentTeamIds)) {
                OrgMembership::whereIn('member_id', $memberIds)
                    ->where('model_type', \App\Models\Team::class)
                    ->whereIn('model_id', $departmentTeamIds)
                    ->delete();
            }
        });

        return back()->with('success', 'Đã xóa hàng loạt tín hữu khỏi ban ngành.');
    }

    /**
     * Create a user account for a specific member to access the Member Portal
     */
    public function createUserAccount(Request $request, Member $member)
    {
        // Require Manage Board permission or Global Admin
        Gate::authorize('portal_manage_board', Member::class);

        if ($member->user_id) {
            return back()->with('error', 'Tín hữu này đã có tài khoản rồi.');
        }

        if (empty($member->email)) {
            return back()->with('error', 'Tín hữu bắt buộc phải có Email để tạo tài khoản.');
        }

        // Check if email already exists in users table
        if (User::where('email', $member->email)->exists()) {
            return back()->with('error', 'Email này đã tồn tại trong hệ thống tài khoản.');
        }

        \DB::transaction(function () use ($member) {
            // Generate standard user account
            $user = User::create([
                'name' => $member->full_name,
                'email' => $member->email,
                'password' => Hash::make('12345678'), // Default password
            ]);

            // Assign standard 'Member' role
            $user->assignRole('Member');

            // Link user to member
            $member->update(['user_id' => $user->id]);
        });

        return back()->with('success', 'Đã tạo tài khoản cho ' . $member->full_name . ' thành công! Mật khẩu mặc định: 12345678');
    }

    /**
     * Export member list template for the active department.
     */
    public function exportTemplate()
    {
        $context = $this->getContext();
        $departmentId = $this->getDeptId($context);

        Gate::authorize('portal_view_all_members', Member::class);

        $department = Department::findOrFail($departmentId);
        $dateStr = now()->format('Y-m-d');
        $deptSlug = \Illuminate\Support\Str::slug($department->name);
        $filename = "danh-sach-thanh-vien-{$deptSlug}-{$dateStr}.xlsx";

        return Excel::download(
            new PortalMemberExport($departmentId),
            $filename
        );
    }

    /**
     * Import members from Excel file for the active department.
     */
    public function import(Request $request)
    {
        $context = $this->getContext();
        $departmentId = $this->getDeptId($context);

        Gate::authorize('portal_manage_board', Member::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        try {
            $import = new PortalMemberImport($departmentId);
            Excel::import($import, $request->file('file'));

            $msg = "Import thành công {$import->importedCount} thành viên cập nhật vào CSDL.";
            if (!empty($import->errors)) {
                $msg .= ' Bỏ qua ' . $import->skippedCount . ' dòng lỗi.';
            }

            return back()->with('success', $msg)->with('import_errors', $import->errors);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Lỗi import: ' . $e->getMessage()]);
        }
    }
}

