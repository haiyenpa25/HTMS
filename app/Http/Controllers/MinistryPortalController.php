<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Department;
use App\Models\OrgMembership;
use Inertia\Inertia;

class MinistryPortalController extends Controller
{
    /**
     * Entry point for the Ministry Portal.
     */
    public function index(Request $request)
    {
        $user         = $request->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $service      = app(\App\Services\FeatureAssignmentService::class);

        $activeDeptId = session('active_ministry_dept_id');

        // ── Lấy danh sách departments user có quyền vào ────────────────
        if ($isSuperAdmin) {
            $availableDepartments = Department::where('block', 'ministry')
                ->select('id', 'name', 'code')->orderBy('name')->get();
        } else {
            // Lấy tất cả quyền Level 2 của user này
            $userFeatureRecords = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
                ->where('is_enabled', true)
                ->whereHas('department', fn ($q) => $q->where('block', 'ministry'))
                ->with(['feature', 'department'])
                ->get()
                ->groupBy('department_id');

            $validDeptIds = [];
            
            foreach ($userFeatureRecords as $deptId => $ufRecords) {
                $dept = $ufRecords->first()->department;
                if (!$dept) continue;
                
                $level1Map = $service->getAvailableFeaturesForDepartment($dept);
                
                $hasValidFeature = false;
                foreach ($ufRecords as $uf) {
                    if (!$uf->feature) continue;
                    $slug = $uf->feature->slug;
                    if ($level1Map[$slug] ?? true) {
                        $hasValidFeature = true;
                        break;
                    }
                }
                
                if ($hasValidFeature) {
                    $validDeptIds[] = $deptId;
                }
            }

            $availableDepartments = Department::whereIn('id', $validDeptIds)
                ->where('block', 'ministry')
                ->select('id', 'name', 'code')->orderBy('name')->get();
        }

        // Auto-set active dept
        if (!$activeDeptId && $availableDepartments->isNotEmpty()) {
            $activeDeptId = $availableDepartments->first()->id;
            session(['active_ministry_dept_id' => $activeDeptId]);
        }

        $activeDepartment = $request->attributes->get('activeDepartment') ?? Department::find($activeDeptId);

        return Inertia::render('Ministry/Dashboard', [
            'activeDepartment'     => $activeDepartment,
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin'        => $isSuperAdmin,
        ]);
    }

    /**
     * Switch context method via POST Request.
     */
    public function switchContext(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id'
        ]);

        $user   = $request->user();
        $deptId = $validated['department_id'];
        $dept   = Department::findOrFail($deptId);

        if (!$user->isSuperAdmin()) {
            // Check if user has membership OR feature access in this target department
            $isMember = \App\Models\Member::where('user_id', $user->id)
                ->whereHas('departments', fn($q) => $q->where('departments.id', $deptId))
                ->exists();

            $hasFeatures = false;
            if (!$isMember) {
                $service = app(\App\Services\FeatureAssignmentService::class);
                $level1Map = $service->getAvailableFeaturesForDepartment($dept);
                
                $userFeatures = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
                    ->where('department_id', $deptId)
                    ->where('is_enabled', true)
                    ->with('feature')
                    ->get();
                    
                foreach ($userFeatures as $uf) {
                    if (!$uf->feature) continue;
                    $slug = $uf->feature->slug;
                    // Default allow if no Level 1 config exists
                    if ($level1Map[$slug] ?? true) {
                        $hasFeatures = true;
                        break;
                    }
                }
            }
            
            if (!$isMember && !$hasFeatures) {
                abort(403, 'Bạn không có quyền truy cập ban ngành này.');
            }
        }

        // Logic switch context và redirect
        if ($dept->block === 'ministry') {
            session(['active_ministry_dept_id' => $deptId]);
            return redirect()->route('ministry.index');
        } elseif ($dept->block === 'activities') {
            session(['active_portal_dept_id' => $deptId]);
            return redirect()->route('portal.index');
        } elseif ($dept->block === 'leadership') {
            session(['active_deacon_dept_id' => $deptId]);
            return redirect()->route('deacon.index');
        }

        return redirect()->route('dashboard');
    }

    /**
     * View Activity Logs specific to a Ministry department.
     */
    public function logs(Request $request)
    {
        $deptId = session('active_ministry_dept_id');
        if (!$deptId) abort(403);
        
        $department = Department::findOrFail($deptId);

        // 1. Ensure user has access
        $user = $request->user();
        if (!$user->isSuperAdmin()) {
            $isMember = \App\Models\Member::where('user_id', $user->id)
                ->whereHas('departments', fn($q) => $q->where('departments.id', $department->id))
                ->exists();
            
            $hasFeatures = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
                    ->where('department_id', $department->id)
                    ->where('is_enabled', true)->exists();

            if (!$isMember && !$hasFeatures) {
                abort(403, 'Bạn không có quyền xem nhật ký ban ngành này.');
            }
        }

        // 2. Fetch polymorphic logs
        $query = \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])->latest();

        // Prepare IDs for relationships that belong to this department
        $financeFundIds = \App\Models\FinanceFund::where('owner_type', 'department')
            ->where('owner_id', $department->id)->pluck('id')->toArray();
            
        $financeTransactionIds = [];
        if (!empty($financeFundIds)) {
            $financeTransactionIds = \App\Models\FinanceTransaction::whereIn('fund_id', $financeFundIds)->pluck('id')->toArray();
        }

        $meetingIds = \App\Models\Meeting::where('department_id', $department->id)->pluck('id')->toArray();
        $attendanceIds = \App\Models\MeetingAttendance::whereIn('meeting_id', $meetingIds)->pluck('id')->toArray();

        // 3. Filter only for models that belong to the department
        $query->where(function($q) use ($department, $financeTransactionIds, $meetingIds, $attendanceIds) {
            // FinanceTransactions
            if (!empty($financeTransactionIds)) {
                $q->orWhere(function($subq) use ($financeTransactionIds) {
                    $subq->where('subject_type', 'App\Models\FinanceTransaction')
                         ->whereIn('subject_id', $financeTransactionIds);
                });
            }
            // Meetings
            if (!empty($meetingIds)) {
                $q->orWhere(function($subq) use ($meetingIds) {
                    $subq->where('subject_type', 'App\Models\Meeting')
                         ->whereIn('subject_id', $meetingIds);
                });
            }
            // Attendance
            if (!empty($attendanceIds)) {
                $q->orWhere(function($subq) use ($attendanceIds) {
                    $subq->where('subject_type', 'App\Models\MeetingAttendance')
                         ->whereIn('subject_id', $attendanceIds);
                });
            }
            // Department Details themselves
            $q->orWhere(function($subq) use ($department) {
                $subq->where('subject_type', 'App\Models\Department')
                     ->where('subject_id', $department->id);
            });
            // Member roles assigned to this department
            $q->orWhere(function($subq) use ($department) {
                $subq->where('subject_type', 'App\Models\UserDepartmentRole')
                     ->where('subject_id', $department->id); // Usually pivot logs are different, assuming subject is dept.
            });
            // Member logic: activities on Members who are in this department
            $q->orWhere(function($subq) use ($department) {
                $subq->where('subject_type', 'App\Models\Member')
                     ->whereIn('subject_id', \App\Models\OrgMembership::where('department_id', $department->id)->pluck('member_id'));
            });
        });

        $activities = $query->paginate(20)->through(function ($log) {
            $causer = $log->causer;
            return [
                'id' => $log->id,
                'description' => $log->description,
                'event' => $log->event,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'subject_label' => $this->trans_subject_type($log->subject_type),
                'causer_name' => $causer ? $causer->name : 'Hệ thống',
                'properties' => $log->properties,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'human_time' => $log->created_at->diffForHumans(),
            ];
        });

        return Inertia::render('Portal/ActivityLogs', [
            'department' => $department,
            'activities' => $activities,
        ]);
    }

    private function trans_subject_type($type)
    {
        if (!$type) return 'Khác';
        $basename = class_basename($type);
        $map = [
            'User' => 'Tài khoản', 'Member' => 'Hồ sơ Tín hữu', 'Department' => 'Ban ngành',
            'FinanceTransaction' => 'GD Tài chính', 'Meeting' => 'Buổi nhóm',
            'Attendance' => 'Điểm danh', 'CareTicket' => 'Phiếu YC'
        ];
        return $map[$basename] ?? $basename;
    }
}

