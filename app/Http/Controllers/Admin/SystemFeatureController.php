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
            'feature_id' => 'required|exists:features,id',
            'block_type' => 'required|string',
            'target_mode' => 'required|in:all,specific',
            'is_active' => 'required|boolean',
            'department_ids' => 'array',
            'department_ids.*' => 'integer|exists:departments,id',
        ]);

        $featureId = $validated['feature_id'];
        $blockType = $validated['block_type'];
        $targetMode = $validated['target_mode'];
        $isActive = $validated['is_active'];

        // Start by clearing old config for this feature + block
        FeatureDepartment::where('feature_id', $featureId)
            ->where('block_type', $blockType)
            ->delete();

        if ($targetMode === 'all') {
            // mode = all -> specific record where department_id is null
            FeatureDepartment::create([
                'feature_id' => $featureId,
                'block_type' => $blockType,
                'department_id' => null,
                'is_active' => $isActive,
            ]);
        } else {
            // mode = specific -> create record for each selected dept ID (always true if selected)
            $deptIds = $request->input('department_ids', []);
            $insertData = [];
            foreach ($deptIds as $deptId) {
                $insertData[] = [
                    'feature_id' => $featureId,
                    'block_type' => $blockType,
                    'department_id' => $deptId,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($insertData)) {
                FeatureDepartment::insert($insertData);
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
