<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Feature;
use App\Models\FeatureDepartment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SystemFeatureController extends Controller
{
    /**
     * Display the System Features Configuration Page (Tab Tính Năng).
     */
    public function index()
    {
        $features = Feature::cachedAll();
        $departments = Department::cachedAll()
            ->sortBy('name')
            ->values()
            ->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'block' => $d->block, 'code' => $d->code]);

        // Get all current assignments mapping Level 1
        $assignments = FeatureDepartment::cachedAll();

        return Inertia::render('Admin/SystemFeatures', [
            'features' => $features,
            'departments' => $departments,
            'assignments' => $assignments,
        ]);
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'feature_id'     => 'required|exists:features,id',
            'scope'          => 'required|in:global,block,specific',
            'block_type'     => 'nullable|string',
            'is_active'      => 'required|boolean',
            'department_ids' => 'array',
            'department_ids.*' => 'integer|exists:departments,id',
        ]);

        $featureId  = $validated['feature_id'];
        $scope      = $validated['scope'];
        $blockType  = $validated['block_type'] ?? null;
        $isActive   = $validated['is_active'];

        // 1. DELETE ALL existing configs for this feature to prevent "ghost" records
        FeatureDepartment::where('feature_id', $featureId)->delete();

        // 2. Insert new configuration based on scope
        if ($scope === 'global') {
            FeatureDepartment::create([
                'feature_id'    => $featureId,
                'block_type'    => null,
                'department_id' => null,
                'scope'         => 'global',
                'is_active'     => $isActive,
            ]);
        } elseif ($scope === 'block') {
            FeatureDepartment::create([
                'feature_id'    => $featureId,
                'block_type'    => $blockType,
                'department_id' => null,
                'scope'         => 'block',
                'is_active'     => $isActive,
            ]);
        } else { // specific
            $deptIds = $validated['department_ids'] ?? [];
            if (empty($deptIds)) {
                 // Even if specific, if no depts are selected, we just don't have records
                 // but we already deleted old ones, so it effectively disables the feature.
            } else {
                $rows = [];
                foreach ($deptIds as $deptId) {
                    $rows[] = [
                        'feature_id'    => $featureId,
                        'block_type'    => $blockType,
                        'department_id' => $deptId,
                        'scope'         => 'specific',
                        'is_active'     => true,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ];
                }
                FeatureDepartment::insert($rows);
            }
        }

        return response()->json(['success' => true, 'message' => 'Đã lưu cấu hình tính năng thành công.']);
    }


    /**
     * Create a new Feature in the system.
     */
    public function storeFeature(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:features,slug',
            'icon' => 'nullable|string',
            'portal_type' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        Feature::create($validated);

        return back()->with('success', 'Đã thêm tính năng mới thành công.');
    }
}
