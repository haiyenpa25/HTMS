<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChronicleEntry;
use App\Models\Department;
use App\Models\User;
use App\Services\PortalService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChronicleController extends Controller
{
    /**
     * Display the Church Chronicles timeline view with filtering options.
     */
    public function index(Request $request)
    {
        $query = ChronicleEntry::with(['subject', 'creator', 'department'])
            ->orderBy('occurred_at', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply MAC V2 Data Scope
        $scope = $this->resolveScope($request);

        if (!$scope['is_global']) {
            if (!empty($scope['department_ids'])) {
                $query->whereIn('department_id', $scope['department_ids']);
            } else {
                $query->where('id', 0); // No scope scenario
            }
        } else {
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->query('department_id'));
            }
        }

        // Apply filters if any
        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $entries = $query->paginate(20)->withQueryString();

        // Stats Query needs identical scoping
        $statsQuery = ChronicleEntry::query();
        if (!$scope['is_global']) {
            if (!empty($scope['department_ids'])) {
                $statsQuery->whereIn('department_id', $scope['department_ids']);
            } else {
                $statsQuery->where('id', 0);
            }
        } else {
            if ($request->filled('department_id')) {
                $statsQuery->where('department_id', $request->query('department_id'));
            }
        }

        // Calculate Overview Statistics
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'history' => (clone $statsQuery)->where('category', 'history')->count(),
            'leadership' => (clone $statsQuery)->where('category', 'leadership')->count(),
            'other' => (clone $statsQuery)->whereNotIn('category', ['history', 'leadership'])->count(),
        ];

        return Inertia::render('Admin/Chronicles/Index', [
            'entries' => $entries,
            'filters' => $request->only(['category', 'type', 'search', 'department_id']),
            'stats' => $stats,
            'availableDepartments' => $scope['is_global'] ? Department::select('id', 'name')->get() : Department::whereIn('id', $scope['department_ids'])->select('id', 'name')->get(),
            'isPortal' => !$scope['is_global'],
            'department' => !$scope['is_global'] ? Department::find($request->query('department_id') ?? $scope['department_ids'][0] ?? null) : null,
        ]);
    }

    /**
     * Store a manually entered historical event.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'occurred_at' => 'required|date',
            'ended_at' => 'nullable|date|after_or_equal:occurred_at',
            
            // Polymorphic Subject logic for explicit tagging
            'subject_type' => 'nullable|string',
            'subject_id' => 'nullable|integer',
            'department_id' => 'nullable|integer|exists:departments,id',
            'meta_data' => 'nullable|array'
        ]);

        $scope = $this->resolveScope($request);
        
        if (!$scope['is_global']) {
            $requestedDeptId = (int) $request->input('department_id');
            if (empty($scope['department_ids']) || !in_array($requestedDeptId, $scope['department_ids'])) {
                abort(403, 'Bạn không có quyền lưu Sổ Tay cho ban ngành này.');
            }
            $validated['department_id'] = $requestedDeptId;
        }

        $validated['type'] = 'manual';
        $validated['created_by'] = $request->user()->id;

        ChronicleEntry::create($validated);

        return back()->with('success', 'Đã lưu trữ thành công sự kiện vào Sổ Tay Hội Thánh.');
    }

    /**
     * Update an existing manual historical event (Cannot edit auto generated system ones).
     */
    public function update(Request $request, ChronicleEntry $chronicle)
    {
        if ($chronicle->type === 'auto') {
            abort(403, 'Không thể chỉnh sửa các dữ liệu lưu sử tự động từ hệ thống.');
        }

        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'occurred_at' => 'required|date',
            'ended_at' => 'nullable|date|after_or_equal:occurred_at',
            'subject_type' => 'nullable|string',
            'subject_id' => 'nullable|integer',
            'department_id' => 'nullable|integer|exists:departments,id',
            'meta_data' => 'nullable|array'
        ]);

        $scope = $this->resolveScope($request);
        
        if (!$scope['is_global']) {
            if (empty($scope['department_ids']) || !in_array($chronicle->department_id, $scope['department_ids'])) {
                abort(403, 'Bạn không thể chỉnh sửa Sổ Tay của ban ngành khác.');
            }
            // Enforce lockdown
            $validated['department_id'] = $chronicle->department_id;
        }

        $chronicle->update($validated);

        return back()->with('success', 'Đã cập nhật sự kiện lịch sử.');
    }

    /**
     * Delete a manual historical event.
     */
    public function destroy(ChronicleEntry $chronicle)
    {
        if ($chronicle->type === 'auto') {
            abort(403, 'Không thể xóa các dữ liệu lưu sử tự động từ hệ thống.');
        }

        $scope = $this->resolveScope(request());

        if (!$scope['is_global']) {
            if (empty($scope['department_ids']) || !in_array($chronicle->department_id, $scope['department_ids'])) {
                abort(403, 'Bạn không thể xóa Sổ Tay của ban ngành khác.');
            }
        }

        $chronicle->delete();

        return back()->with('success', 'Đã xóa sự kiện khỏi Sổ Tay Hội Thánh.');
    }

    /**
     * Resolve MAC V2 Data Scope for Chronicles across all portal blocks.
     */
    private function resolveScope(Request $request): array
    {
        $user = $request->user();
        $isGlobal = $user->isSuperAdmin();
        $departmentIds = [];

        if (!$isGlobal) {
            $portalService = app(PortalService::class);
            $potentialDepts = array_merge(
                $portalService->getAvailableDepartments($user, 'activities')->pluck('id')->toArray(),
                $portalService->getAvailableDepartments($user, 'ministry')->pluck('id')->toArray()
            );
            
            foreach (array_unique($potentialDepts) as $deptId) {
                if ($portalService->canAccess($user, $deptId, 'module_chronicles') || $portalService->canAccess($user, $deptId, 'chronicles')) {
                    $departmentIds[] = $deptId;
                }
            }
        }

        return [
            'is_global' => $isGlobal,
            'department_ids' => $departmentIds
        ];
    }
}
