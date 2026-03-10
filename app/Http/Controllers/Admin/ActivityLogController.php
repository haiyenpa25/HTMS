<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    /**
     * Hiển thị màn hình Nhật ký hoạt động toàn hệ thống
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $eventFilter = $request->input('event');
        $dateFilter = $request->input('date');

        $query = Activity::with('causer')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhereHas('causer', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($eventFilter) {
            $query->where('event', $eventFilter);
        }

        if ($dateFilter) {
            $query->whereDate('created_at', $dateFilter);
        }

        $logs = $query->paginate(30)->withQueryString()->through(function($log) {
            return [
                'id' => $log->id,
                'log_name' => $log->log_name,
                'description' => $log->description,
                'event' => $log->event,
                'subject_type' => $log->subject_type ? class_basename($log->subject_type) : null,
                'subject_id' => $log->subject_id,
                'causer_name' => $log->causer ? $log->causer->name : 'Hệ thống',
                'properties' => $log->properties,
                'created_at' => $log->created_at->format('d/m/Y H:i:s'),
                'created_at_human' => $log->created_at->diffForHumans(),
            ];
        });

        $events = Activity::select('event')->distinct()->pluck('event')->filter();

        return Inertia::render('Admin/ActivityLogs', [
            'logs' => $logs,
            'events' => $events,
            'filters' => $request->only(['search', 'event', 'date']),
        ]);
    }
}
