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

    /**
     * Upsert a feature assignment configuration.
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'feature_id' => 'required|exists:features,id',
            'block_type' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'is_active' => 'required|boolean',
        ]);

        $assignment = FeatureDepartment::updateOrCreate(
            [
                'feature_id' => $validated['feature_id'],
                'block_type' => $validated['block_type'],
                'department_id' => $validated['department_id'],
            ],
            [
                'is_active' => $validated['is_active'],
            ]
        );

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
