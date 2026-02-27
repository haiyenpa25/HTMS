<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */
    public function index(Request $request): Response
    {
        $query = Department::query()->withCount(['teams', 'members']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('block')) {
            $query->where('block', $request->block);
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $departments = $query->orderBy('name')->get();

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
            'filters' => $request->only(['search', 'block', 'status']),
        ]);
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'nullable|string|max:50|unique:departments,code',
            'block' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Department::create($validated);

        return redirect()->back()->with('success', 'Đã tạo ban ngành thành công.');
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'code' => 'nullable|string|max:50|unique:departments,code,' . $department->id,
            'block' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        return redirect()->back()->with('success', 'Đã cập nhật ban ngành thành công.');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        if ($department->teams()->count() > 0 || $department->members()->count() > 0) {
            return redirect()->back()->with('error', 'Không thể xóa ban ngành đang có tổ hoặc thành viên.');
        }

        $department->delete();

        return redirect()->back()->with('success', 'Đã xóa ban ngành thành công.');
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department): Response
    {
        // Load relationships: teams, members with their OrgRoles, and features
        $department->load(['teams', 'members', 'supervisors']);
        
        // Eager load roles for members
        $membersWithRoles = $department->members()->withPivot(['org_role_id', 'id'])->get();
        // Since we don't strictly have the OrgRole relation deeply nested easily with pivot here,
        // we map it efficiently
        $orgRoles = \App\Models\OrgRole::all()->keyBy('id');
        
        $mappedMembers = $membersWithRoles->map(function ($member) use ($orgRoles) {
            $role = $orgRoles->get($member->pivot->org_role_id);
            return [
                'id' => $member->id,
                'full_name' => $member->full_name,
                'phone' => $member->phone,
                'email' => $member->email,
                'role' => $role ? $role->name : 'Thành viên',
                'team_id' => null, // Placeholder for team associations if we add team pivot
                'pivot_id' => $member->pivot->id,
            ];
        });

        return Inertia::render('Departments/Show', [
            'department' => $department,
            'teams' => $department->teams,
            'members' => $mappedMembers,
            'availableRoles' => $orgRoles->values(),
        ]);
    }

    /**
     * Teams Management within Department
     */
    public function storeTeam(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $department->teams()->create($validated);

        return redirect()->back()->with('success', 'Đã tạo Tổ thành công.');
    }

    public function updateTeam(Request $request, Department $department, \App\Models\Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $team->update($validated);

        return redirect()->back()->with('success', 'Đã cập nhật Tổ thành công.');
    }

    public function destroyTeam(Department $department, \App\Models\Team $team)
    {
        if ($team->members()->count() > 0) {
            return redirect()->back()->with('error', 'Không thể xóa tổ đang có thành viên.');
        }

        $team->delete();

        return redirect()->back()->with('success', 'Đã xóa Tổ thành công.');
    }

    public function assignMember(Request $request, Department $department)
    {
        $validated = $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:members,id',
            'org_role_id' => 'required|exists:org_roles,id',
            'team_id' => 'nullable|exists:teams,id',
        ]);

        foreach ($validated['member_ids'] as $memberId) {
            $exists = $department->members()->where('member_id', $memberId)->exists();
            if (!$exists) {
                $department->members()->attach($memberId, [
                    'org_role_id' => $validated['org_role_id'],
                    'model_type' => Department::class,
                ]);
            } else {
                $department->members()->updateExistingPivot($memberId, [
                    'org_role_id' => $validated['org_role_id'],
                ]);
            }

            if (!empty($validated['team_id'])) {
                $team = \App\Models\Team::find($validated['team_id']);
                if ($team && !$team->members()->where('member_id', $memberId)->exists()) {
                    $team->members()->attach($memberId, [
                        'org_role_id' => $validated['org_role_id'],
                        'model_type' => \App\Models\Team::class,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Đã thêm thành viên vào ban thành công.');
    }

    public function removeMember(Department $department, \App\Models\Member $member)
    {
        $department->members()->wherePivot('model_type', Department::class)->detach($member->id);

        $teamIds = $department->teams()->pluck('id');
        if ($teamIds->isNotEmpty()) {
            foreach($teamIds as $teamId) {
                \App\Models\Team::find($teamId)->members()->wherePivot('model_type', \App\Models\Team::class)->detach($member->id);
            }
        }

        return redirect()->back()->with('success', 'Đã tháo gỡ thành viên khỏi ban.');
    }

    /**
     * Update Feature Keys
     */
    public function updateFeatures(Request $request, Department $department)
    {
        // Require pastor role check visually or in middleware
        $validated = $request->validate([
            'feature_keys' => 'nullable|array',
        ]);

        $department->update([
            'feature_keys' => $validated['feature_keys'] ?? []
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật chức năng cho ban ngành.');
    }
}
