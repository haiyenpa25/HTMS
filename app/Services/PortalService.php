<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Feature;
use App\Models\User;
use App\Models\UserDepartmentFeature;
use Illuminate\Support\Collection;

class PortalService
{
    // ══════════════════════════════════════════════════════════════
    // FEATURE LIST — Lấy danh sách tính năng cho Dashboard
    // ══════════════════════════════════════════════════════════════

    /**
     * Trả về collection các features đã enabled, groupBy dept_type.
     * Super_Admin/Pastor nhận được TẤT CẢ features.
     *
     * @return Collection<string, Collection<UserDepartmentFeature>>
     */
    public function getFeaturesGrouped(User $user): Collection
    {
        if ($user->isSuperAdmin()) {
            // Super: tất cả features dưới mọi department
            return UserDepartmentFeature::with(['feature', 'department:id,name,block,code'])
                ->where('is_enabled', true)
                ->get()
                ->groupBy('dept_type');
        }

        // Normal user: chỉ lấy features của mình, enabled
        return UserDepartmentFeature::with(['feature', 'department:id,name,block,code'])
            ->where('user_id', $user->id)
            ->where('is_enabled', true)
            ->get()
            ->groupBy('dept_type');
    }

    /**
     * Lấy danh sách features enabled cho 1 department cụ thể.
     */
    public function getDeptFeatures(User $user, int $deptId): Collection
    {
        if ($user->isSuperAdmin()) {
            return Feature::cachedAll();
        }

        return UserDepartmentFeature::with('feature')
            ->where('user_id', $user->id)
            ->where('department_id', $deptId)
            ->where('is_enabled', true)
            ->get()
            ->pluck('feature');
    }

    /**
     * Lấy danh sách feature slugs user có quyền trong 1 department.
     * MAC V2: Kế thừa Level 1 (dept features) mặc định, user record là override.
     */
    public function getAllowedFeaturesForDept(User $user, int $deptId): array
    {
        $department = Department::find($deptId);
        if (!$department) return [];

        $service = app(\App\Services\FeatureAssignmentService::class);
        $level1Map = $service->getAvailableFeaturesForDepartment($department);

        if ($user->isSuperAdmin()) {
            // SuperAdmin gets all features that are enabled at Level 1
            return array_keys(array_filter($level1Map, fn($v) => $v !== false));
        }

        // Start with Level 1 defaults (all features dept is configured for)
        $allowed = array_keys(array_filter($level1Map, fn($v) => $v !== false));
        $allowed = array_flip($allowed); // slug => true map for fast lookup

        // Apply user-level overrides (explicit records)
        $overrides = UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $deptId)
            ->with('feature')
            ->get();

        foreach ($overrides as $override) {
            if (!$override->feature) continue;
            $slug = $override->feature->slug;
            if ($override->is_enabled) {
                $allowed[$slug] = true; // Grant explicitly
            } else {
                unset($allowed[$slug]); // Revoke explicitly
            }
        }

        return array_keys($allowed);
    }

    // ══════════════════════════════════════════════════════════════
    // ACCESS CHECK — Middleware dùng method này
    // ══════════════════════════════════════════════════════════════

    /**
     * Kiểm tra user có thể truy cập feature cụ thể trong department.
     * MAC V2: Level 1 (dept config) &rarr; Level 2 (user override override).
     * - Nếu không có user record: kế thừa từ Level 1 (dept cấp thì user có).
     * - Nếu có user record: override với giá trị tường minh (có thể true hoặc false).
     */
    public function canAccess(User $user, int $deptId, string $featureSlug): bool
    {
        // God Mode: Super_Admin bypass tất cả
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Level 1: Check System Configuration (feature_department)
        $department = Department::find($deptId);
        if (!$department) return false;
        
        $systemAccess = app(\App\Services\FeatureAssignmentService::class)
            ->isFeatureEnabledForDepartment($department, $featureSlug);
            
        if (!$systemAccess) {
            return false; // Dept không có feature này → không ai có quyền
        }

        // Level 2: Check User-level override (user_department_features)
        $featureId = $this->resolveFeatureId($featureSlug);
        if (!$featureId) return true; // Feature exists in Level 1 but no ID? Still allow.

        $userRecord = UserDepartmentFeature::where([
            'user_id'       => $user->id,
            'department_id' => $deptId,
            'feature_id'    => $featureId,
        ])->first();

        // No explicit record → inherit Level 1 (ALLOW by default since dept has it)
        if ($userRecord === null) {
            return true;
        }

        // Explicit record exists → respect the override
        return (bool) $userRecord->is_enabled;
    }

    /**
     * Toggle (upsert) 1 feature permission cho user.
     * Được gọi từ UserPermissionController::toggle().
     */
    public function upsertFeature(
        int $userId,
        int $deptId,
        int $featureId,
        bool $isEnabled,
        string $accessLevel = 'view',
        string $deptType = 'activities'
    ): UserDepartmentFeature {
        return UserDepartmentFeature::updateOrCreate(
            [
                'user_id'       => $userId,
                'department_id' => $deptId,
                'feature_id'    => $featureId,
            ],
            [
                'dept_type'    => $deptType,
                'is_enabled'   => $isEnabled,
                'access_level' => $accessLevel,
            ]
        );
    }

    /**
     * Seed superadmin với full access cho tất cả features + departments.
     */
    public function grantSuperadminFullAccess(User $superAdmin): int
    {
        $features     = Feature::all();
        $departments  = Department::where('is_active', true)->get();
        $count        = 0;

        foreach ($departments as $dept) {
            foreach ($features as $feature) {
                UserDepartmentFeature::updateOrCreate(
                    [
                        'user_id'       => $superAdmin->id,
                        'department_id' => $dept->id,
                        'feature_id'    => $feature->id,
                    ],
                    [
                        'dept_type'    => $dept->block ?? 'activities',
                        'is_enabled'   => true,
                        'access_level' => 'manage',
                    ]
                );
                $count++;
            }
        }

        return $count;
    }

    // ── Private Helpers ─────────────────────────────────────────────

    private ?array $slugMap = null;

    public function resolveFeatureId(string $slug): ?int
    {
        if ($this->slugMap === null) {
            $this->slugMap = Feature::pluck('id', 'slug')->toArray();
        }
        return $this->slugMap[$slug] ?? null;
    }

    /**
     * Lấy danh sách departments user có quyền truy cập (theo Block).
     * Kết hợp cả Membership (legacy) và MAC (UserDepartmentFeature).
     */
    public function getAvailableDepartments(User $user, string $block = 'activities'): Collection
    {
        if ($user->isSuperAdmin()) {
            return Department::where('block', $block)->orderBy('name')->get();
        }

        $deptIds = collect();

        // 1. MAC path
        $macDeptIds = UserDepartmentFeature::where('user_id', $user->id)
            ->where('is_enabled', true)
            ->whereHas('department', fn($q) => $q->where('block', $block))
            ->pluck('department_id');
        $deptIds = $deptIds->merge($macDeptIds);

        // 2. Legacy Membership path
        if ($user->member_id) {
            $memberDeptIds = \App\Models\OrgMembership::where('member_id', $user->member_id)
                ->where('model_type', Department::class)
                ->whereHasMorph('model', [Department::class], fn($q) => $q->where('block', $block))
                ->pluck('model_id');
            $deptIds = $deptIds->merge($memberDeptIds);
        }

        return Department::whereIn('id', $deptIds->unique())
            ->where('block', $block)
            ->orderBy('name')
            ->get();
    }
    /**
     * Lấy danh sách departments user có quyền truy cập, GROUPED BY BLOCK.
     * Dùng cho Global Switcher.
     */
    public function getAllAvailableDepartmentsGrouped(User $user): array
    {
        $blocks = ['activities', 'ministry', 'leadership'];
        $grouped = [];

        foreach ($blocks as $block) {
            if ($block === 'leadership') {
                // Ban Chấp Sự / Lãnh Đạo sử dụng logic Role chứ không phải bảng departments
                if ($user->isSuperAdmin() || $user->isSuperAdmin()) {
                    $grouped[$block] = collect([
                        ['id' => 'secretary', 'name' => 'Thư Ký Hội Thánh', 'block' => 'leadership', 'code' => 'SEC'],
                        ['id' => 'treasurer', 'name' => 'Thủ Quỹ Hội Thánh', 'block' => 'leadership', 'code' => 'TRE']
                    ]);
                } else {
                    $grouped[$block] = collect([]);
                }
            } else {
                $grouped[$block] = $this->getAvailableDepartments($user, $block)
                    ->map(fn($d) => [
                        'id'    => $d->id,
                        'name'  => $d->name,
                        'block' => $d->block,
                        'code'  => $d->code,
                    ]);
            }
        }

        return $grouped;
    }
}
