<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index(): Response
    {
        $roles = Role::withCount('users', 'permissions')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'users_count' => $role->users_count,
                    'permissions_count' => $role->permissions_count,
                ];
            });

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Display the specified role and its permissions matrix.
     */
    public function show(Role $role): Response
    {
        // Get all permissions
        $allPermissions = Permission::orderBy('name')->get();
        
        // Group permissions by their resource (e.g 'view users' -> 'users')
        $groupedPermissions = $allPermissions->groupBy(function ($perm) {
            $parts = explode(' ', $perm->name);
            return count($parts) > 1 ? $parts[1] : 'other';
        });
        
        $role->load('permissions:id,name');
        
        return Inertia::render('Roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => $role->users()->count(),
                'permissions' => $role->permissions,
            ],
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    /**
     * Update the specified role's permissions.
     */
    public function update(Request $request, Role $role)
    {
        if ($role->name === 'Super_Admin') {
            return redirect()->back()->with('message', 'Role Super Admin có toàn quyền hệ thống, không cần gán Permissions thủ công.');
        }

        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->back()->with('message', 'Cập nhật phân quyền thành công.');
    }
}

