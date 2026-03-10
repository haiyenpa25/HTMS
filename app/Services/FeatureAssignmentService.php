<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Feature;
use App\Models\FeatureDepartment;

class FeatureAssignmentService
{
    /**
     * Resolve the final feature access map for a given department.
     *
     * Priority (highest → lowest):
     *  1. Specific department rule (scope = 'specific', dept = ID)
     *  2. Block-level rule (scope = 'block', block_type = X, dept = null)
     *  3. Global rule (scope = 'global', block_type = null, dept = null)
     *  4. Default: ALLOW (backward compat — no config at all)
     *
     * @param Department $department
     * @return array<string, bool>
     */
    public function getAvailableFeaturesForDepartment(Department $department): array
    {
        $block = $department->block;
        $allConfigs = FeatureDepartment::cachedAll();

        // -- Priority 3: Global configs (block_type IS NULL, department_id IS NULL) --
        $globalAssignments = $allConfigs->whereNull('block_type')
            ->whereNull('department_id')
            ->keyBy('feature_id');

        // -- Priority 2: Block-level configs (block_type = X, department_id IS NULL) --
        $blockAssignments = $allConfigs->where('block_type', $block)
            ->whereNull('department_id')
            ->keyBy('feature_id');

        // -- Priority 1: Specific department configs (department_id = X) --
        $deptAssignments = $allConfigs->where('department_id', $department->id)
            ->keyBy('feature_id');

        $finalAccess = [];
        $features = Feature::cachedAll();

        // Lấy tất cả feature_id đã có config trong hệ thống (bất kỳ block/dept nào)
        // Dùng để phân biệt "chưa cấu hình bao giờ" vs "cấu hình cho block khác"
        $configuredFeatureIds = $allConfigs->pluck('feature_id')->unique()->flip();

        foreach ($features as $feature) {
            $hasDeptConfig   = $deptAssignments->has($feature->id);
            $hasBlockConfig  = $blockAssignments->has($feature->id);
            $hasGlobalConfig = $globalAssignments->has($feature->id);
            $hasAnyConfig    = $hasDeptConfig || $hasBlockConfig || $hasGlobalConfig;

            if (!$hasAnyConfig) {
                if ($configuredFeatureIds->has($feature->id)) {
                    // Tính năng đã được cấu hình cho block/dept KHÁC nhưng không phải cho dept này
                    // → DENY: ẩn card. VD: "members" cho activities, ban ministry sẽ không thấy
                    $finalAccess[$feature->slug] = false;
                } else {
                    // Tính năng hoàn toàn chưa được cấu hình ở bất kỳ đâu
                    // → ALLOW (backward compat: hiển thị mặc định cho tất cả)
                    $finalAccess[$feature->slug] = true;
                }
                continue;
            }

            // Áp dụng theo thứ tự ưu tiên: specific > block > global
            if ($hasDeptConfig) {
                $finalAccess[$feature->slug] = (bool) $deptAssignments->get($feature->id)->is_active;
            } elseif ($hasBlockConfig) {
                $finalAccess[$feature->slug] = (bool) $blockAssignments->get($feature->id)->is_active;
            } elseif ($hasGlobalConfig) {
                $finalAccess[$feature->slug] = (bool) $globalAssignments->get($feature->id)->is_active;
            }
        }

        return $finalAccess;
    }

    /**
     * Quick check if a specific feature is enabled for a department.
     */
    public function isFeatureEnabledForDepartment(Department $department, string $featureSlug): bool
    {
        $accessMap = $this->getAvailableFeaturesForDepartment($department);
        return $accessMap[$featureSlug] ?? true;
    }
}
