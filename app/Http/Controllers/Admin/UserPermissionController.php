<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Feature;
use App\Models\User;
use App\Models\UserDepartmentFeature;
use App\Services\PortalService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserPermissionController extends Controller
{
    public function __construct(private PortalService $service) {}

    // ══════════════════════════════════════════════════════════════
    // INDEX — Trang quản lý phân quyền
    // ══════════════════════════════════════════════════════════════

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
            'roles' => $u->getRoleNames(),
        ]);

        // Danh sách tất cả departments với features của họ
        $departments = Department::orderBy('name')->get()->map(fn ($d) => [
            'id'    => $d->id,
            'name'  => $d->name,
            'code'  => $d->code,
            'block' => $d->block,
        ]);

        // Tất cả features (10 features chuẩn MAC)
        $features = Feature::orderBy('portal_type')->orderBy('name')->get()->map(fn ($f) => [
            'id'          => $f->id,
            'name'        => $f->name,
            'slug'        => $f->slug,
            'icon'        => $f->icon,
            'portal_type' => $f->portal_type,
            'description' => $f->description,
        ]);

        // Preselect user nếu có user_id param (mobile direct link)
        $preselectUser = null;
        if ($userId = $request->input('user_id')) {
            $u = User::find($userId);
            if ($u) {
                $preselectUser = ['id' => $u->id, 'name' => $u->name, 'email' => $u->email];
            }
        }

        return Inertia::render('Admin/UserPermissions', [
            'users'         => $users,
            'departments'   => $departments,
            'features'      => $features,
            'filters'       => ['search' => $request->input('search')],
            'preselectUser' => $preselectUser,
            'systemConfig'  => \App\Models\FeatureDepartment::all(),
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // SHOW — Lấy toàn bộ MAC matrix cho 1 user (AJAX)
    // ══════════════════════════════════════════════════════════════

    public function show(User $user)
    {
        // Lấy tất cả dòng user_department_features của user này
        $rows = UserDepartmentFeature::with(['department:id,name,code,block', 'feature:id,name,slug,icon,portal_type'])
            ->where('user_id', $user->id)
            ->get()
            ->map(fn ($r) => [
                'department_id' => $r->department_id,
                'feature_id'    => $r->feature_id,
                'dept_type'     => $r->dept_type,
                'is_enabled'    => $r->is_enabled,
                'access_level'  => $r->access_level,
                'department'    => $r->department,
                'feature'       => $r->feature,
            ]);

        return response()->json([
            'user'          => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'global_roles'  => $user->getRoleNames(),
            'is_super_admin' => $user->isSuperAdmin(),
            'permissions'   => $rows,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // TOGGLE — Bật/tắt 1 feature cho user (AJAX)
    // ══════════════════════════════════════════════════════════════

    public function toggle(Request $request, User $user)
    {
        $validated = $request->validate([
            'department_id' => 'required|integer|exists:departments,id',
            'feature_id'    => 'required|integer|exists:features,id',
            'is_enabled'    => 'required|boolean',
            'access_level'  => 'sometimes|in:view,manage',
        ]);

        $dept = Department::find($validated['department_id']);

        $row = $this->service->upsertFeature(
            userId:      $user->id,
            deptId:      $validated['department_id'],
            featureId:   $validated['feature_id'],
            isEnabled:   $validated['is_enabled'],
            accessLevel: $validated['access_level'] ?? 'view',
            deptType:    $dept->block ?? 'activities',
        );

        return response()->json([
            'success'    => true,
            'is_enabled' => $row->is_enabled,
            'access_level' => $row->access_level,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // BULK — Cập nhật roles toàn cục (Spatie)
    // ══════════════════════════════════════════════════════════════

    public function updateRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => 'string',
        ]);

        $user->syncRoles($validated['roles'] ?? []);

        return response()->json(['success' => true, 'roles' => $user->getRoleNames()]);
    }

    // ══════════════════════════════════════════════════════════════
    // GRANT SUPER — Phân quyền full cho 1 user (Admin action)
    // ══════════════════════════════════════════════════════════════

    public function grantFull(User $user)
    {
        $count = $this->service->grantSuperadminFullAccess($user);

        return response()->json([
            'success' => true,
            'granted' => $count,
            'message' => "Đã cấp {$count} quyền truy cập đầy đủ cho {$user->name}.",
        ]);
    }
}
