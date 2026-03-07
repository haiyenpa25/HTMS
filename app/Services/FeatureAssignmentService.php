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

        // -- Priority 3: Global configs (block_type IS NULL, department_id IS NULL) --
        $globalAssignments = FeatureDepartment::whereNull('block_type')
            ->whereNull('department_id')
            ->get()
            ->keyBy('feature_id');

        // -- Priority 2: Block-level configs (block_type = X, department_id IS NULL) --
        $blockAssignments = FeatureDepartment::where('block_type', $block)
            ->whereNull('department_id')
            ->get()
            ->keyBy('feature_id');

        // -- Priority 1: Specific department configs (department_id = X) --
        $deptAssignments = FeatureDepartment::where('department_id', $department->id)
            ->get()
            ->keyBy('feature_id');

        $finalAccess = [];
        $features = Feature::all();

        foreach ($features as $feature) {
            $hasDeptConfig   = $deptAssignments->has($feature->id);
            $hasBlockConfig  = $blockAssignments->has($feature->id);
            $hasGlobalConfig = $globalAssignments->has($feature->id);
            $hasAnyConfig    = $hasDeptConfig || $hasBlockConfig || $hasGlobalConfig;

            if (!$hasAnyConfig) {
                // NO config at all → default ALLOW (backward compat)
                $finalAccess[$feature->slug] = true;
                continue;
            }

            // Apply in priority order
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
