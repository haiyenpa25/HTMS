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
     * Rule:
     * - If a feature has NO Level 1 config at all → default ALLOWED (backward compat).
     * - If a feature has Level 1 config:
     *     - Department-specific config wins over Block-level config.
     *     - If is_active = false, it's restricted.
     *
     * Return: [ 'attendance' => true, 'finance' => false, ... ]
     *
     * @param Department $department
     * @return array<string, bool>
     */
    public function getAvailableFeaturesForDepartment(Department $department): array
    {
        $block = $department->block;

        // 1. Get Category (Block) Assignments (where department_id is null = applies to all in block)
        $categoryAssignments = FeatureDepartment::where('block_type', $block)
            ->whereNull('department_id')
            ->get()
            ->keyBy('feature_id');

        // 2. Get Specific Department Assignments (overrides block-level)
        $deptAssignments = FeatureDepartment::where('block_type', $block)
            ->where('department_id', $department->id)
            ->get()
            ->keyBy('feature_id');

        $finalAccess = [];
        $features = Feature::all();

        foreach ($features as $feature) {
            $hasDeptConfig      = $deptAssignments->has($feature->id);
            $hasCategoryConfig  = $categoryAssignments->has($feature->id);
            $hasAnyConfig       = $hasDeptConfig || $hasCategoryConfig;

            if (!$hasAnyConfig) {
                // NO Level 1 config exists → default ALLOW (backward compat, don't break existing permissions)
                $finalAccess[$feature->slug] = true;
                continue;
            }

            // Specific Department Override check first
            if ($hasDeptConfig) {
                $finalAccess[$feature->slug] = $deptAssignments->get($feature->id)->is_active;
            } 
            // Fallback to Category (Block) check
            elseif ($hasCategoryConfig) {
                $finalAccess[$feature->slug] = $categoryAssignments->get($feature->id)->is_active;
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
        return $accessMap[$featureSlug] ?? true; // Default: allow if no config
    }
}
