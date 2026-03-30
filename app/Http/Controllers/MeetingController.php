<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Exports\MeetingsExport;
use App\Imports\MeetingsImport;
use Maatwebsite\Excel\Facades\Excel;

class MeetingController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Meeting::query()->accessibleBy($request->user())->with('department');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $meetings = $query->orderByDesc('date')->orderByDesc('time')->get();

        // Get departments user has access to, for the Create/Edit Form
        $departments = \App\Models\Department::query()
            ->when(!$request->user()->isSuperAdmin(), function ($q) use ($request) {
                $q->whereIn('id', function ($sq) use ($request) {
                    $sq->select('department_id')
                       ->from('department_supervisors')
                       ->where('user_id', $request->user()->id);
                });
            })->get(['id', 'name']);

        return \Inertia\Inertia::render('Meetings/Index', [
            'meetings' => $meetings,
            'departments' => $departments,
            'filters' => $request->only(['type', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Meeting::class);

        $validated = $request->validate([
            'type' => 'required|in:church,department,holiday',
            'department_id' => 'nullable|required_if:type,department|exists:departments,id',
            'date' => 'required|date',
            'time' => 'required',
            'topic' => 'nullable|string|max:255',
            'memory_verse' => 'nullable|string|max:255',
            'quiz_passage' => 'nullable|string',
            'scripture' => 'nullable|string|max:255',
            'preacher' => 'nullable|string|max:255',
            'bulk_weeks' => 'nullable|integer|min:1|max:52'
        ]);

        try {
            $service = new \App\Services\MeetingService();
            $weeks = $request->input('bulk_weeks', 1);
            
            $service->createMeetings($validated, $weeks);

            $msg = $weeks > 1 ? "Đã tạo hàng loạt $weeks buổi nhóm thành công." : "Đã tạo buổi nhóm thành công.";
            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['date' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Meeting $meeting)
    {
        Gate::authorize('view', $meeting);

        $meeting->load(['department', 'personnel.member', 'report', 'finances']);
        return \Inertia\Inertia::render('Meetings/Show', [
            'meeting' => $meeting,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Meeting $meeting)
    {
        Gate::authorize('update', $meeting);

        $validated = $request->validate([
            'type' => 'required|in:church,department,holiday',
            'department_id' => 'nullable|required_if:type,department|exists:departments,id',
            'date' => 'required|date',
            'time' => 'required',
            'topic' => 'nullable|string|max:255',
            'memory_verse' => 'nullable|string|max:255',
            'quiz_passage' => 'nullable|string',
            'scripture' => 'nullable|string|max:255',
            'preacher' => 'nullable|string|max:255',
        ]);

        // Prevent duplicate when editing date/time
        $exists = Meeting::where('type', $validated['type'])
            ->when($validated['type'] === 'department', function($q) use ($validated) {
                return $q->where('department_id', $validated['department_id']);
            })
            ->where('date', $validated['date'])
            ->where('time', $validated['time'])
            ->where('id', '!=', $meeting->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors(['date' => "Lịch nhóm ngày {$validated['date']} lúc {$validated['time']} đã bị trùng lặp."])->withInput();
        }

        $meeting->update($validated);

        return redirect()->back()->with('success', 'Cập nhật buổi nhóm thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Meeting $meeting)
    {
        Gate::authorize('delete', $meeting);

        $meeting->delete();
        return redirect()->route('meetings.index')->with('success', 'Đã xóa buổi nhóm.');
    }

    /**
     * Toggle trạng thái nghỉ/hủy buổi nhóm.
     * Buổi bị cancel sẽ không được tính vào TB điểm danh.
     */
    public function toggleCancel(Request $request, Meeting $meeting)
    {
        Gate::authorize('update', $meeting);

        $validated = $request->validate([
            'cancelled_note' => 'nullable|string|max:255',
        ]);

        $meeting->update([
            'is_cancelled'   => !$meeting->is_cancelled,
            'cancelled_note' => $meeting->is_cancelled ? null : ($validated['cancelled_note'] ?? null),
        ]);

        $state = $meeting->fresh()->is_cancelled ? 'nghỉ' : 'hoạt động';
        return back()->with('success', "Buổi nhóm đã được đánh dấu là \"{$state}\".");
    }

    /**
     * Export meeting list to Excel.
     */
    public function export(Request $request)
    {
        $filters  = $request->only(['type', 'date_from', 'date_to']);
        $ids      = $request->has('ids') ? array_filter(array_map('intval', explode(',', $request->ids))) : [];
        $filename = 'danh-sach-buoi-nhom-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new MeetingsExport($filters, $ids), $filename);
    }

    /**
     * Import meetings from Excel to bulk-update topic, scripture, etc.
     */
    public function import(Request $request)
    {
        Gate::authorize('create', Meeting::class);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $import = new MeetingsImport();
            Excel::import($import, $request->file('file'));

            $msg = "Đã cập nhật thành công {$import->updatedCount} buổi nhóm.";
            if ($import->skippedCount > 0) {
                $msg .= " Bỏ qua {$import->skippedCount} dòng.";
            }

            return back()->with('success', $msg)->with('import_errors', $import->errors);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Lỗi import: ' . $e->getMessage()]);
        }
    }
}
