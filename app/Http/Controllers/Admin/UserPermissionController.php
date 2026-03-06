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
    /**
     * Display the User Permissions Tree View page.
     */
    public function index(Request $request): Response
    {
        // 1. Fetch all users
        $query = User::query()->orderBy('name');
        
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->paginate(20)->through(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ];
        });

        // 2. Data source for Tree View
        $departments = Department::orderBy('name')->get()->map(function($d) {
            return [
                'id' => $d->id,
                'name' => $d->name,
                'code' => $d->code,
                'block' => $d->block,
            ];
        });

        $orgRoles = OrgRole::orderBy('level', 'desc')->get()->map(function($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'code' => $r->code,
                'level' => $r->level,
            ];
        });

        return Inertia::render('Admin/UserPermissions', [
            'users' => $users,
            'departments' => $departments,
            'orgRoles' => $orgRoles,
            'filters' => ['search' => $request->input('search')],
        ]);
    }

    /**
     * Get memberships for a specific user to populate the Tree View checkboxes
     */
    public function show(User $user)
    {
        // Get user's active org memberships
        // We link org_memberships to member, so we need the user's member ID first.
        $member = $user->member;
        
        if (!$member) {
            return response()->json(['memberships' => [], 'global_roles' => $user->getRoleNames()]);
        }

        $memberships = OrgMembership::where('member_id', $member->id)
            ->where('is_active', true)
            ->get()
            ->map(function($m) {
                return [
                    'id' => $m->id,
                    'model_type' => $m->model_type,
                    'model_id' => $m->model_id,
                    'org_role_id' => $m->org_role_id,
                ];
            });

        return response()->json([
            'memberships' => $memberships,
            'global_roles' => $user->getRoleNames(),
        ]);
    }

    /**
     * Saves the complex permission mapping from the Tree View
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'global_roles' => 'array',
            'memberships' => 'array',
            'memberships.*.model_type' => 'required|string',
            'memberships.*.model_id' => 'required|integer',
            'memberships.*.org_role_id' => 'required|integer',
        ]);

        // 1. Update Spatie Global Roles (Super Admin, Pastor)
        $user->syncRoles($validated['global_roles'] ?? []);

        // 2. Ensure Member record exists for org memberships
        $member = $user->member;
        if (!$member) {
            $member = \App\Models\Member::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'gender' => 'male', // default fallback
            ]);
        }

        // 3. Sync Org Memberships
        // Simplest strategy: Delete active memberships, then recreate based on new selection.
        OrgMembership::where('member_id', $member->id)->delete();

        if (!empty($validated['memberships'])) {
            foreach ($validated['memberships'] as $mem) {
                OrgMembership::create([
                    'member_id' => $member->id,
                    'model_type' => $mem['model_type'],
                    'model_id' => $mem['model_id'],
                    'org_role_id' => $mem['org_role_id'],
                    'is_active' => true,
                    'join_date' => now(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Phân quyền cho người dùng đã được cập nhật thành công.');
    }
}
