<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Department;
use App\Models\OrgRole;
use App\Models\OrgMembership;
use Inertia\Response;

class UserPermissionController extends Controller
{
    const DEFAULT_PERMISSIONS = [
        'manage_members'    => true,
        'manage_attendance' => true,
        'manage_funds'      => false,
        'manage_reports'    => false,
    ];

    /**
     * Display the User Permissions page.
     */
    public function index(Request $request): Response
    {
        $query = User::query()->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(25)->through(fn ($u) => [
            'id'    => $u->id,
            'name'  => $u->name,
            'email' => $u->email,
        ]);

        $departments = Department::orderBy('name')->get()->map(fn ($d) => [
            'id'    => $d->id,
            'name'  => $d->name,
            'code'  => $d->code,
            'block' => $d->block,
        ]);

        $orgRoles = OrgRole::orderBy('level', 'desc')->get()->map(fn ($r) => [
            'id'    => $r->id,
            'name'  => $r->name,
            'code'  => $r->code,
            'level' => $r->level,
        ]);

        return Inertia::render('Admin/UserPermissions', [
            'users'       => $users,
            'departments' => $departments,
            'orgRoles'    => $orgRoles,
            'filters'     => ['search' => $request->input('search')],
        ]);
    }

    /**
     * Get memberships + global roles for a user (AJAX endpoint)
     */
    public function show(User $user)
    {
        $member = $user->member;

        if (!$member) {
            return response()->json([
                'memberships'  => [],
                'global_roles' => $user->getRoleNames(),
                'member'       => null,
            ]);
        }

        $memberships = OrgMembership::where('member_id', $member->id)
            ->where('is_active', true)
            ->get()
            ->map(fn ($m) => [
                'id'          => $m->id,
                'model_type'  => $m->model_type,
                'model_id'    => $m->model_id,
                'org_role_id' => $m->org_role_id,
                'permissions' => $m->permissions ?? self::DEFAULT_PERMISSIONS,
            ]);

        return response()->json([
            'memberships'  => $memberships,
            'global_roles' => $user->getRoleNames(),
            'member'       => [
                'id'        => $member->id,
                'full_name' => $member->full_name,
                'code'      => $member->code ?? null,
            ],
        ]);
    }

    /**
     * Save full permission set for a user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'global_roles'                => 'array',
            'memberships'                 => 'array',
            'memberships.*.model_type'    => 'required|string',
            'memberships.*.model_id'      => 'required|integer',
            'memberships.*.org_role_id'   => 'required|integer',
            'memberships.*.permissions'   => 'nullable|array',
            'memberships.*.permissions.*' => 'boolean',
        ]);

        // 1. Sync Spatie global roles
        $user->syncRoles($validated['global_roles'] ?? []);

        // 2. Ensure Member record exists
        $member = $user->member;
        if (!$member) {
            $member = \App\Models\Member::create([
                'user_id'   => $user->id,
                'full_name' => $user->name,
                'gender'    => 'male',
            ]);
        }

        // 3. Delete old memberships, recreate with new permissions
        OrgMembership::where('member_id', $member->id)->delete();

        foreach ($validated['memberships'] ?? [] as $mem) {
            OrgMembership::create([
                'member_id'   => $member->id,
                'model_type'  => $mem['model_type'],
                'model_id'    => $mem['model_id'],
                'org_role_id' => $mem['org_role_id'],
                'is_active'   => true,
                'join_date'   => now(),
                'permissions' => $mem['permissions'] ?? self::DEFAULT_PERMISSIONS,
            ]);
        }

        return redirect()->back()->with('success', 'Phân quyền đã được cập nhật thành công.');
    }
}
