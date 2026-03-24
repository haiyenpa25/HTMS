<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ChronicleEntry;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;


class ChronicleController extends Controller
{
    private function getContext()
    {
        $isMinistry = request()->is('ministry/*');

        return [
            'type'         => $isMinistry ? 'ministry' : 'activities',
            'session_key'  => $isMinistry ? 'active_ministry_dept_id' : 'active_portal_dept_id',
            'route_prefix' => $isMinistry ? 'ministry.chronicles' : 'portal.chronicles',
            'base_route'   => $isMinistry ? 'ministry.index' : 'portal.index',
        ];
    }

    private function getDeptId(array $context): int
    {
        return tap(session($context['session_key']), function ($id) use ($context) {
            if (!$id) abort(redirect()->route($context['base_route']));
        });
    }

    public function index(Request $request)
    {
        $context = $this->getContext();
        $departmentId = $this->getDeptId($context);
        $department = Department::findOrFail($departmentId);

        $this->authorizeFeature('chronicles');

        
        // Middleware `portal.access:chronicles,*` handles feature permission check

        $query = ChronicleEntry::with(['subject', 'creator', 'department'])
            ->where('department_id', $departmentId)
            ->orderBy('occurred_at', 'desc')
            ->orderBy('created_at', 'desc');

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

        $statsQuery = ChronicleEntry::where('department_id', $departmentId);
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'history' => (clone $statsQuery)->where('category', 'history')->count(),
            'leadership' => (clone $statsQuery)->where('category', 'leadership')->count(),
            'other' => (clone $statsQuery)->whereNotIn('category', ['history', 'leadership'])->count(),
        ];

        // Lấy danh sách phòng ban cho layout Portal
        $availableDepartments = app(\App\Services\PortalService::class)->getAvailableDepartments(auth()->user(), $context['type']);

        return Inertia::render('Admin/Chronicles/Index', [
            'entries' => $entries,
            'filters' => $request->only(['category', 'type', 'search']),
            'stats' => $stats,
            'availableDepartments' => $availableDepartments,
            'isPortal' => true,
            'department' => $department,
            'routePrefix' => $context['route_prefix'] . '.',
            'portalType' => $context['type']
        ]);
    }

    public function store(Request $request)
    {
        $context = $this->getContext();
        $departmentId = $this->getDeptId($context);
        $department = Department::findOrFail($departmentId);

        $this->authorizeManage('chronicles');

        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'occurred_at' => 'required|date',
            'ended_at' => 'nullable|date|after_or_equal:occurred_at',
            'subject_type' => 'nullable|string',
            'subject_id' => 'nullable|integer',
            'meta_data' => 'nullable|array'
        ]);


        $validated['department_id'] = $departmentId;
        $validated['type'] = 'manual';
        $validated['created_by'] = $request->user()->id;

        ChronicleEntry::create($validated);

        return back()->with('success', 'Đã lưu trữ thành công sự kiện vào Sổ Tay Ban Ngành.');
    }

    public function update(Request $request, ChronicleEntry $chronicle)
    {
        $context = $this->getContext();
        $departmentId = $this->getDeptId($context);
        $department = Department::findOrFail($departmentId);

        $this->authorizeManage('chronicles');


        if ($chronicle->type === 'auto') {
            abort(403, 'Không thể chỉnh sửa các dữ liệu lưu sử tự động từ hệ thống.');
        }

        if ($chronicle->department_id !== $departmentId) {
            abort(403, 'Bạn không thể chỉnh sửa Sổ Tay của ban ngành khác.');
        }

        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'occurred_at' => 'required|date',
            'ended_at' => 'nullable|date|after_or_equal:occurred_at',
            'subject_type' => 'nullable|string',
            'subject_id' => 'nullable|integer',
            'meta_data' => 'nullable|array'
        ]);

        $chronicle->update($validated);

        return back()->with('success', 'Đã cập nhật sự kiện lịch sử.');
    }

    public function destroy(ChronicleEntry $chronicle)
    {
        $context = $this->getContext();
        $departmentId = $this->getDeptId($context);
        $department = Department::findOrFail($departmentId);

        $this->authorizeManage('chronicles');


        if ($chronicle->type === 'auto') {
            abort(403, 'Không thể xóa các dữ liệu lưu sử tự động từ hệ thống.');
        }

        if ($chronicle->department_id !== $departmentId) {
            abort(403, 'Bạn không thể xóa Sổ Tay của ban ngành khác.');
        }

        $chronicle->delete();

        return back()->with('success', 'Đã xóa sự kiện khỏi Sổ Tay Ban Ngành.');
    }
}
