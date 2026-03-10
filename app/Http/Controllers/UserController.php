<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use App\Models\OrgMembership;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search     = $request->input('search');
        $blockFilter = $request->input('block');
        $deptFilter  = $request->input('department_id');

        $users = User::with(['roles', 'member.memberships.model'])
            ->when($search, fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            )
            ->when($deptFilter, function ($q) use ($deptFilter) {
                // Users whose member has a membership in this department
                $q->whereHas('member.memberships', function ($mq) use ($deptFilter) {
                    $mq->where('model_type', Department::class)
                       ->where('model_id', $deptFilter);
                });
            })
            ->when($blockFilter && !$deptFilter, function ($q) use ($blockFilter) {
                // Filter by block (department type)
                $q->whereHas('member.memberships', function ($mq) use ($blockFilter) {
                    $mq->where('model_type', Department::class)
                       ->whereHasMorph('model', [Department::class], fn($dq) => $dq->where('block', $blockFilter));
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($user) {
                $depts = [];
                if ($user->member && $user->member->memberships) {
                    $depts = $user->member->memberships->map(fn($m) =>
                        $m->model && class_basename($m->model_type) === 'Department' ? $m->model->name : null
                    )->filter()->unique()->values()->toArray();
                }

                return [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'phone'       => $user->member->phone ?? '',
                    'role'        => $user->roles->first()?->name ?? 'Guest',
                    'departments' => implode(', ', $depts) ?: 'Chưa tham gia',
                    'created_at'  => $user->created_at->format('d/m/Y'),
                ];
            });

        $roles = \Illuminate\Support\Facades\Cache::remember('system_roles_pluck_name', 3600, function() {
            return Role::pluck('name');
        });

        // Departments for filter select
        $departments = Department::cachedAll()
            ->where('is_active', true)
            ->sortBy('block')
            ->sortBy('name')
            ->values()
            ->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'block' => $d->block]);

        $blockLabels = [
            'activities' => '🏃 Ban Sinh Hoạt',
            'ministry'   => '🙏 Ban Mục Vụ',
            'leadership' => '🏛️ Ban Chấp Sự',
        ];

        $features = \App\Models\Feature::cachedAll()->map(fn ($f) => [
            'id'          => $f->id,
            'name'        => $f->name,
            'slug'        => $f->slug,
            'icon'        => $f->icon,
            'portal_type' => $f->portal_type,
            'description' => $f->description,
        ]);

        $systemConfig = \App\Models\FeatureDepartment::cachedAll();

        return Inertia::render('Users/Index', [
            'users'        => $users,
            'roles'        => $roles,
            'departments'  => $departments,
            'blockLabels'  => $blockLabels,
            'filters'      => $request->only(['search', 'block', 'department_id']),
            'features'     => $features,
            'systemConfig' => $systemConfig,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role'     => 'nullable|string|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        \App\Models\Member::create([
            'user_id'   => $user->id,
            'full_name' => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'gender'    => 'male',
        ]);

        if (!empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        return redirect()->back()->with('message', 'Đã tạo tài khoản thành công.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'role'     => 'nullable|string|exists:roles,name',
        ]);

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        $member = \App\Models\Member::firstOrCreate(
            ['user_id' => $user->id],
            ['full_name' => $validated['name'], 'email' => $validated['email'], 'gender' => 'male']
        );
        $member->full_name = $validated['name'];
        $member->email     = $validated['email'];
        $member->phone     = $validated['phone'] ?? null;
        $member->save();

        if (!empty($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        } else {
            $user->syncRoles([]);
        }

        return redirect()->back()->with('message', 'Cập nhật tài khoản thành công.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không thể tự xóa tài khoản của mình.');
        }
        $user->delete();
        return redirect()->back()->with('message', 'Xóa tài khoản thành công.');
    }
}
