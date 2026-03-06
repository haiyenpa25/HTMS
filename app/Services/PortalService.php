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
            return Feature::all();
        }

        return UserDepartmentFeature::with('feature')
            ->where('user_id', $user->id)
            ->where('department_id', $deptId)
            ->where('is_enabled', true)
            ->get()
            ->pluck('feature');
    }

    // ══════════════════════════════════════════════════════════════
    // ACCESS CHECK — Middleware dùng method này
    // ══════════════════════════════════════════════════════════════

    /**
     * Kiểm tra user có thể truy cập feature cụ thể trong department.
     * Dùng trong PortalAccessMiddleware per-request.
     */
    public function canAccess(User $user, int $deptId, string $featureSlug): bool
    {
        // God Mode: Super_Admin, Pastor bypass tất cả
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Cache slugToId để tránh lookup DB mỗi request
        $featureId = $this->resolveFeatureId($featureSlug);
        if (!$featureId) return false;

        return UserDepartmentFeature::where([
            'user_id'       => $user->id,
            'department_id' => $deptId,
            'feature_id'    => $featureId,
            'is_enabled'    => true,
        ])->exists();
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

    private function resolveFeatureId(string $slug): ?int
    {
        if ($this->slugMap === null) {
            $this->slugMap = Feature::pluck('id', 'slug')->toArray();
        }
        return $this->slugMap[$slug] ?? null;
    }
}
