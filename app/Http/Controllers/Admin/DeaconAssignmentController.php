<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChurchSetting;
use App\Models\DeaconTermAssignment;
use App\Models\Department;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeaconAssignmentController extends Controller
{
    public function __construct()
    {
        // Chỉ SuperAdmin mới quản lý phân công Chấp Sự
        $this->middleware(function ($request, $next) {
            if (!$request->user()?->isSuperAdmin()) {
                abort(403, 'Chỉ SuperAdmin mới có quyền này.');
            }
            return $next($request);
        });
    }

    /**
     * Trang quản lý phân công nhiệm kỳ
     */
    public function index(Request $request)
    {
        $currentTermYear = ChurchSetting::currentTermYear();

        // Danh sách tất cả các nhiệm kỳ đã có
        $termYears = DeaconTermAssignment::select('term_from', 'term_to', 'term_label')
            ->distinct()
            ->orderBy('term_from', 'desc')
            ->get()
            ->map(fn($t) => [
                'from'  => $t->term_from,
                'to'    => $t->term_to,
                'label' => $t->term_label ?? "Nhiệm kỳ {$t->term_from}–{$t->term_to}",
            ])
            ->unique(fn($t) => $t['from'] . '-' . $t['to'])
            ->values();

        // Term đang xem (default: hiện tại)
        $viewFrom = (int) $request->input('term_from', $currentTermYear);

        // Tất cả ban ngành đang hoạt động
        $departments = Department::where('is_active', true)
            ->orderBy('block')
            ->orderBy('name')
            ->get(['id', 'name', 'block']);

        // Assignments của nhiệm kỳ đang xem
        $assignments = DeaconTermAssignment::where('term_from', $viewFrom)
            ->with(['deacon:id,full_name,phone', 'department:id,name'])
            ->get()
            ->keyBy('department_id');

        // Tất cả Chấp Sự: lấy members có org_role code = 'cs' (Chấp Sự) qua OrgMembership
        // Fallback: nếu chưa có OrgRole code 'cs', lấy tất cả members đang hoạt động
        $deaconRoleId = \App\Models\OrgRole::where('code', 'cs')->value('id');
        if ($deaconRoleId) {
            $deaconMemberIds = \App\Models\OrgMembership::where('org_role_id', $deaconRoleId)
                ->where('is_active', true)
                ->pluck('member_id');
            $deacons = Member::whereIn('id', $deaconMemberIds)
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'phone', 'member_type'])
                ->map(fn($m) => [
                    'id'          => $m->id,
                    'full_name'   => $m->full_name,
                    'phone'       => $m->phone,
                    'member_type' => $m->member_type,
                ]);
        } else {
            // Fallback: tất cả members (dùng khi chưa setup OrgRole)
            $deacons = Member::orderBy('full_name')
                ->get(['id', 'full_name', 'phone', 'member_type'])
                ->map(fn($m) => [
                    'id'          => $m->id,
                    'full_name'   => $m->full_name,
                    'phone'       => $m->phone,
                    'member_type' => $m->member_type,
                ]);
        }

        // Kết hợp dept + assignment hiện tại
        $deptAssignments = $departments->map(fn($dept) => [
            'dept_id'       => $dept->id,
            'dept_name'     => $dept->name,
            'dept_block'    => $dept->block,
            'assignment'    => $assignments->get($dept->id) ? [
                'id'              => $assignments->get($dept->id)->id,
                'deacon_id'       => $assignments->get($dept->id)->deacon_id,
                'deacon_name'     => $assignments->get($dept->id)->deacon?->full_name,
                'term_from'       => $assignments->get($dept->id)->term_from,
                'term_to'         => $assignments->get($dept->id)->term_to,
                'term_label'      => $assignments->get($dept->id)->term_label,
                'notes'           => $assignments->get($dept->id)->notes,
            ] : null,
        ])->values();

        return Inertia::render('Admin/DeaconAssignments', [
            'dept_assignments'  => $deptAssignments,
            'deacons'           => $deacons,
            'term_years'        => $termYears,
            'current_term_year' => $currentTermYear,
            'viewing_term_from' => $viewFrom,
        ]);
    }

    /**
     * Tạo hoặc cập nhật phân công 1 ban trong nhiệm kỳ
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'deacon_id'     => 'required|exists:members,id',
            'term_from'     => 'required|integer|min:2000|max:2100',
            'term_to'       => 'required|integer|min:2000|max:2100|gte:term_from',
            'term_label'    => 'nullable|string|max:100',
            'notes'         => 'nullable|string|max:500',
        ]);

        $label = $validated['term_label']
            ?? "Nhiệm kỳ {$validated['term_from']}–{$validated['term_to']}";

        DeaconTermAssignment::updateOrCreate(
            [
                'department_id' => $validated['department_id'],
                'term_from'     => $validated['term_from'],
            ],
            [
                'deacon_id'  => $validated['deacon_id'],
                'term_to'    => $validated['term_to'],
                'term_label' => $label,
                'notes'      => $validated['notes'] ?? null,
                'assigned_by'=> $request->user()->id,
            ]
        );

        return back()->with('success', 'Đã cập nhật phân công Chấp Sự.');
    }

    /**
     * Khởi tạo nhiệm kỳ mới — copy từ nhiệm kỳ hiện tại
     */
    public function initNewTerm(Request $request)
    {
        $validated = $request->validate([
            'term_from'     => 'required|integer|min:2000|max:2100',
            'term_to'       => 'required|integer|min:2000|max:2100|gte:term_from',
            'copy_from_year'=> 'nullable|integer|min:2000',
        ]);

        $newFrom = $validated['term_from'];
        $newTo   = $validated['term_to'];
        $label   = "Nhiệm kỳ {$newFrom}–{$newTo}";

        // Tránh tạo trùng
        $exists = DeaconTermAssignment::where('term_from', $newFrom)->exists();
        if ($exists) {
            return back()->withErrors(['term_from' => 'Nhiệm kỳ này đã tồn tại.']);
        }

        // Copy từ nhiệm kỳ cũ nếu có
        if ($validated['copy_from_year'] ?? null) {
            $oldAssignments = DeaconTermAssignment::where('term_from', $validated['copy_from_year'])->get();
            foreach ($oldAssignments as $old) {
                DeaconTermAssignment::create([
                    'department_id' => $old->department_id,
                    'deacon_id'     => $old->deacon_id,
                    'term_from'     => $newFrom,
                    'term_to'       => $newTo,
                    'term_label'    => $label,
                    'notes'         => null,
                    'assigned_by'   => $request->user()->id,
                ]);
            }
        }

        // Cập nhật current_term_year nếu là nhiệm kỳ mới nhất
        if ($newFrom >= ChurchSetting::currentTermYear()) {
            ChurchSetting::set('current_term_year', $newFrom);
        }

        return back()->with('success', "Đã khởi tạo nhiệm kỳ {$label}.");
    }

    /**
     * Xóa 1 phân công cụ thể
     */
    public function destroy(Request $request, int $id)
    {
        DeaconTermAssignment::findOrFail($id)->delete();
        return back()->with('success', 'Đã xóa phân công.');
    }
}
