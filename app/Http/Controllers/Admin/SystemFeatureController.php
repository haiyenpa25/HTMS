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
        $features = Feature::all();
        $departments = Department::select('id', 'name', 'block', 'code')->orderBy('name')->get();

        // Get all current assignments mapping Level 1
        $assignments = FeatureDepartment::all();

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

        if ($scope === 'global') {
            // Remove all existing global + block + specific configs for this feature, then add global
            FeatureDepartment::where('feature_id', $featureId)->delete();
            FeatureDepartment::create([
                'feature_id'    => $featureId,
                'block_type'    => null,
                'department_id' => null,
                'scope'         => 'global',
                'is_active'     => $isActive,
            ]);

        } elseif ($scope === 'block') {
            // Remove global + same block configs for this feature, then add block-level
            FeatureDepartment::where('feature_id', $featureId)
                ->where(function ($q) use ($blockType) {
                    $q->where('block_type', $blockType)
                      ->orWhereNull('block_type');
                })
                ->delete();
            FeatureDepartment::create([
                'feature_id'    => $featureId,
                'block_type'    => $blockType,
                'department_id' => null,
                'scope'         => 'block',
                'is_active'     => $isActive,
            ]);

        } else { // specific
            // Remove existing specific configs for this feature + block, then insert new ones
            FeatureDepartment::where('feature_id', $featureId)
                ->where('block_type', $blockType)
                ->whereNotNull('department_id')
                ->delete();

            $deptIds = $request->input('department_ids', []);
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
            if (!empty($rows)) {
                FeatureDepartment::insert($rows);
            }
        }

        return back()->with('success', 'Đã lưu cấu hình tính năng thành công.');
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
