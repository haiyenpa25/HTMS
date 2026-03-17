<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the system-wide activity logs.
     * Only accessible by SuperAdmins via middleware.
     */
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])->latest();

        // Optional filtering by event type (created, updated, deleted)
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Optional filtering by causer (user_id)
        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        // Optional filtering by specific module/subject type
        if ($request->filled('subject_type')) {
            $query->where('subject_type', 'like', "%{$request->subject_type}%");
        }

        $activities = $query->paginate(20)->through(function ($log) {
            $causer = $log->causer;
            
            // Format Subject Type cleanly (e.g. App\Models\FinanceTransaction -> Phiếu Kế Toán)
            $subjectLabel = $this->translateSubjectType($log->subject_type);

            return [
                'id' => $log->id,
                'description' => $log->description,
                'event' => $log->event,
                'subject_type' => $log->subject_type,
                'subject_label' => $subjectLabel,
                'subject_id' => $log->subject_id,
                'causer_name' => $causer ? $causer->name : 'Hệ thống',
                'causer_email' => $causer ? $causer->email : '',
                'properties' => $log->properties,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'human_time' => $log->created_at->diffForHumans(),
            ];
        });

        // Basic stats for filter dropdowns (Optional enhancement)
        $events = [
            ['value' => 'created', 'label' => 'Tạo mới'],
            ['value' => 'updated', 'label' => 'Cập nhật'],
            ['value' => 'deleted', 'label' => 'Xoá']
        ];
        $modules = [
            ['value' => 'User', 'label' => 'Người dùng'],
            ['value' => 'Member', 'label' => 'Tín hữu'],
            ['value' => 'Department', 'label' => 'Ban ngành'],
            ['value' => 'FinanceTransaction', 'label' => 'Tài chính'],
            ['value' => 'Meeting', 'label' => 'Buổi nhóm'],
            ['value' => 'Attendance', 'label' => 'Điểm danh'],
            ['value' => 'CareTicket', 'label' => 'Phiếu yêu cầu'],
        ];

        return Inertia::render('Admin/ActivityLogs/Index', [
            'activities' => $activities,
            'filters' => $request->only('event', 'causer_id', 'subject_type'),
            'filterOptions' => [
                'events' => $events,
                'modules' => $modules
            ]
        ]);
    }

    /**
     * Helper to translate Spatie Model Paths to Vietnamese Labels
     */
    private function translateSubjectType($type)
    {
        if (!$type) return 'Khác';
        
        $basename = class_basename($type);
        
        $map = [
            'User' => 'Tài khoản',
            'Member' => 'Hồ sơ Tín hữu',
            'Department' => 'Ban ngành',
            'FinanceTransaction' => 'GD Tài chính',
            'Meeting' => 'Buổi nhóm',
            'Attendance' => 'Điểm danh',
            'CareTicket' => 'Phiếu YC',
            'Asset' => 'Thiết bị',
            'Visitor' => 'Thân hữu',
            'Document' => 'Tài liệu',
        ];

        return $map[$basename] ?? $basename;
    }
}
