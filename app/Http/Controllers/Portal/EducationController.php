<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EduClass;
use App\Models\EduClassFund;
use App\Models\EduClassMember;
use App\Models\EduClassTransaction;
use App\Models\EduSession;
use App\Models\EduSessionRecord;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EducationController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // DASHBOARD — Portal tổng quan của Ban CĐGD
    // ══════════════════════════════════════════════════════════════
    public function dashboard(Request $request)
    {
        $user = $request->user();
        Gate::authorize('viewAny', EduClass::class);

        $departmentId = session('active_ministry_dept_id');
        $department   = $departmentId ? Department::find($departmentId) : null;
        $isAdmin = $user->hasRole(['Super_Admin', 'Pastor']) || $user->hasPermissionTo('manage_edu_classes');

        $classQuery = EduClass::with([
            'teachers.member:id,full_name',
            'students.member:id,full_name',
        ])->withCount([
            'classMembers as students_count' => fn($q) => $q->where('role', 'student'),
            'sessions as session_count',
        ])->where('is_active', true);

        if ($departmentId) {
            $classQuery->where('department_id', $departmentId);
        }

        if (!$isAdmin && $user->member_id) {
            $classQuery->whereHas('classMembers', fn($q) =>
                $q->where('member_id', $user->member_id)->where('role', 'teacher')
            );
        }

        $classes = $classQuery->get()->map(fn($c) => [
            'id'             => $c->id,
            'name'           => $c->name,
            'students_count' => $c->students_count,
            'session_count'  => $c->session_count,
            'teachers'       => $c->teachers->map(fn($t) => [
                'id'        => $t->member->id ?? null,
                'full_name' => $t->member->full_name ?? '—',
            ])->filter(fn($t) => $t['id'] !== null)->values(),
        ]);

        $stats = [
            'total_classes'  => $classes->count(),
            'total_students' => $classes->sum('students_count'),
            'total_sessions' => $classes->sum('session_count'),
        ];

        return Inertia::render('Portal/Education/Dashboard', [
            'classes'            => $classes,
            'stats'              => $stats,
            'department'         => $department,
            'availableDepartments' => $this->getAvailableDepts($user),
            'isGlobalAdmin'      => $user->hasRole(['Super_Admin', 'Pastor']),
            'isAdmin'            => $isAdmin,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // INDEX — Danh sách lớp học (Class Management page)
    // ══════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $user = $request->user();
        Gate::authorize('viewAny', EduClass::class);

        $departmentId = session('active_ministry_dept_id');
        $department   = $departmentId ? Department::find($departmentId) : null;

        $isAdmin = $user->hasRole(['Super_Admin', 'Pastor'])
                || $user->hasPermissionTo('manage_edu_classes');

        $classQuery = EduClass::with([
            'teachers.member:id,full_name,phone',
            'students.member:id,full_name,phone',
            'sessions' => fn($q) => $q->orderBy('session_date', 'desc')->take(1),
        ])->withCount(['classMembers as student_count' => fn($q) => $q->where('role', 'student')]);

        if ($departmentId) {
            $classQuery->where('department_id', $departmentId);
        }

        if (!$isAdmin && $user->member_id) {
            $classQuery->whereHas('classMembers', fn($q) =>
                $q->where('member_id', $user->member_id)->where('role', 'teacher')
            );
        }

        $classes = $classQuery->where('is_active', true)->withCount(['sessions as session_count'])->get()->map(fn($c) => [
            'id'            => $c->id,
            'name'          => $c->name,
            'description'   => $c->description,
            'student_count' => $c->student_count,
            'students_count' => $c->student_count,
            'session_count' => $c->session_count,
            'teachers'      => $c->teachers->map(fn($t) => [
                'id'        => $t->member->id ?? null,
                'full_name' => $t->member->full_name ?? '—',
            ]),
            'students_list' => $c->students->map(fn($s) => [
                'id'        => $s->member->id ?? null,
                'full_name' => $s->member->full_name ?? '—',
                'phone'     => $s->member->phone ?? null,
            ])->filter(fn($s) => $s['id'] !== null)->values(),
            'last_session'  => $c->sessions->first() ? [
                'date'          => $c->sessions->first()->session_date->toDateString(),
                'lesson_number' => $c->sessions->first()->lesson_number,
                'topic'         => $c->sessions->first()->topic,
            ] : null,
            'can_mark_attendance' => Gate::allows('markAttendance', $c),
            'can_record_offering' => Gate::allows('recordOffering', $c),
        ]);

        $allMembers = Member::select('id', 'full_name', 'phone', 'member_type')
            ->orderBy('full_name')
            ->with([]) // eager: n/a
            ->get()
            ->map(fn($m) => [
                'id'          => $m->id,
                'full_name'   => $m->full_name,
                'phone'       => $m->phone,
                'member_type' => $m->member_type,
            ]);

        // Departments for filter (all active)
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'block'])
            ->map(fn($d) => [
                'id'    => $d->id,
                'name'  => $d->name,
                'block' => $d->block,
            ]);

        // Department member mapping: member_id => [dept_ids]
        $deptMemberMap = \DB::table('org_memberships')
            ->where('model_type', 'App\\Models\\Department')
            ->get(['member_id', 'model_id'])
            ->groupBy('member_id')
            ->map(fn($rows) => $rows->pluck('model_id')->values());

        $allMembers = $allMembers->map(function($m) use ($deptMemberMap) {
            $m['department_ids'] = $deptMemberMap[$m['id']]?->toArray() ?? [];
            return $m;
        });

        return Inertia::render('Portal/Education/Index', [
            'classes'     => $classes,
            'department'  => $department,
            'isAdmin'     => $isAdmin,
            'portalType'  => 'education',
            'availableDepartments' => $this->getAvailableDepts($user),
            'isGlobalAdmin' => $user->hasRole(['Super_Admin', 'Pastor']),
            'allMembers'  => $allMembers,
            'departments' => $departments,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // SESSIONS LIST — Quản lý buổi học của 1 lớp
    // ══════════════════════════════════════════════════════════════
    public function sessions(Request $request, EduClass $eduClass)
    {
        Gate::authorize('view', $eduClass);

        $sessions = EduSession::where('edu_class_id', $eduClass->id)
            ->orderBy('session_date', 'desc')
            ->get()
            ->map(function ($s) {
                // Đếm học viên có mặt
                $presentCount = EduSessionRecord::where('edu_session_id', $s->id)
                    ->where('attendance', 'present')->count();
                // Tổng tiền thu
                $totalIncome = \App\Models\EduClassTransaction::where('edu_session_id', $s->id)
                    ->where('type', 'income')->sum('amount');
                // Điểm trung bình (bible_quiz)
                $scores = EduSessionRecord::where('edu_session_id', $s->id)
                    ->whereNotNull('quiz_score')->pluck('quiz_score');
                $avgScore = $scores->count() > 0 ? round($scores->average(), 1) : null;

                return [
                    'id'              => $s->id,
                    'session_date'    => $s->session_date->toDateString(),
                    'lesson_number'   => $s->lesson_number,
                    'lesson_series'   => $s->lesson_series,
                    'topic'           => $s->topic,
                    'scripture'       => $s->scripture,
                    'book'            => $s->book,
                    'total_questions' => $s->total_questions,
                    'attendance_mode' => $s->attendance_mode ?? 'checkin',
                    'total_present'   => $s->total_present,
                    'present_count'   => $s->attendance_mode === 'quick' ? ($s->total_present ?? 0) : $presentCount,
                    'total_income'    => $totalIncome,
                    'avg_score'       => $avgScore,
                ];
            });

        return Inertia::render('Portal/Education/SessionList', [
            'eduClass'  => [
                'id'         => $eduClass->id,
                'name'       => $eduClass->name,
                'class_type' => $eduClass->class_type,
            ],
            'sessions'  => $sessions,
            'canManage' => Gate::allows('markAttendance', $eduClass),
            'portalType' => 'education',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // STORE MEMBER — Thêm 1 thành viên vào lớp
    // ══════════════════════════════════════════════════════════════
    public function storeMember(Request $request, EduClass $eduClass)
    {
        Gate::authorize('markAttendance', $eduClass);

        $data = $request->validate([
            'member_id' => 'required|integer|exists:members,id',
            'role'      => 'in:student,teacher',
            'joined_at' => 'nullable|date',
        ]);

        \App\Models\EduClassMember::firstOrCreate(
            [
                'edu_class_id' => $eduClass->id,
                'member_id'    => $data['member_id'],
            ],
            [
                'role'      => $data['role'] ?? 'student',
                'joined_at' => $data['joined_at'] ?? now()->toDateString(),
            ]
        );

        return back()->with('success', 'Thêm thành viên thành công.');
    }

    // ══════════════════════════════════════════════════════════════
    // BULK STORE MEMBERS — Thêm nhiều thành viên cùng lúc
    // ══════════════════════════════════════════════════════════════
    public function bulkStoreMember(Request $request, EduClass $eduClass)
    {
        Gate::authorize('markAttendance', $eduClass);

        $data = $request->validate([
            'member_ids'   => 'required|array|min:1',
            'member_ids.*' => 'integer|exists:members,id',
            'role'         => 'in:student,teacher',
        ]);

        $role  = $data['role'] ?? 'student';
        $today = now()->toDateString();
        $count = 0;

        foreach ($data['member_ids'] as $memberId) {
            $record = \App\Models\EduClassMember::firstOrCreate(
                [
                    'edu_class_id' => $eduClass->id,
                    'member_id'    => $memberId,
                ],
                [
                    'role'      => $role,
                    'joined_at' => $today,
                ]
            );
            if ($record->wasRecentlyCreated) $count++;
        }

        return back()->with('success', "Đã thêm {$count} thành viên vào lớp.");
    }

    // ══════════════════════════════════════════════════════════════
    // DESTROY MEMBER — Xóa 1 thành viên khỏi lớp
    // ══════════════════════════════════════════════════════════════
    public function destroyMember(EduClass $eduClass, Member $member)
    {
        Gate::authorize('markAttendance', $eduClass);

        \App\Models\EduClassMember::where('edu_class_id', $eduClass->id)
            ->where('member_id', $member->id)
            ->delete();

        return back()->with('success', 'Đã xóa thành viên khỏi lớp.');
    }

    // ══════════════════════════════════════════════════════════════
    // CREATE SESSION — Tạo buổi học mới (theo Chủ Nhật hoặc ngày bất kỳ)
    // ══════════════════════════════════════════════════════════════
    public function createSession(Request $request, EduClass $eduClass)
    {
        Gate::authorize('markAttendance', $eduClass);

        $validated = $request->validate([
            'session_date'   => 'required|date',
            'lesson_number'  => 'nullable|integer|min:1',
            'lesson_series'  => 'nullable|string|max:255',
            'topic'          => 'nullable|string|max:255',
            'scripture'      => 'nullable|string|max:255',
        ]);

        // Kiểm tra trùng ngày
        $exists = EduSession::where('edu_class_id', $eduClass->id)
            ->where('session_date', $validated['session_date'])
            ->exists();
        if ($exists) {
            return back()->withErrors(['session_date' => 'Buổi học ngày này đã tồn tại.']);
        }

        $session = EduSession::create(array_merge($validated, [
            'edu_class_id' => $eduClass->id,
        ]));

        return redirect()
            ->route('education.session.view', [$eduClass->id, $session->id])
            ->with('success', 'Đã tạo buổi học. Bắt đầu điểm danh!');
    }

    // ══════════════════════════════════════════════════════════════
    // DESTROY SESSION — Xóa buổi học (và cascade records)
    // ══════════════════════════════════════════════════════════════
    public function destroySession(EduClass $eduClass, EduSession $eduSession)
    {
        Gate::authorize('markAttendance', $eduClass);
        $eduSession->delete(); // cascade via DB constraint
        return back()->with('success', 'Đã xóa buổi học.');
    }

    // ══════════════════════════════════════════════════════════════
    // BULK DESTROY SESSIONS — Xóa nhiều buổi học cùng lúc
    // ══════════════════════════════════════════════════════════════
    public function bulkDestroySession(Request $request, EduClass $eduClass)
    {
        Gate::authorize('markAttendance', $eduClass);

        $validated = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:edu_sessions,id',
        ]);

        // Chỉ xóa sessions thuộc lớp này (tránh xóa nhầm)
        $count = EduSession::where('edu_class_id', $eduClass->id)
            ->whereIn('id', $validated['ids'])
            ->delete();

        return back()->with('success', "Đã xóa {$count} buổi học.");
    }

    // ══════════════════════════════════════════════════════════════
    // SESSION (OLD) — Keep for backward compat / redirect
    // ══════════════════════════════════════════════════════════════

    public function session(Request $request, EduClass $eduClass)
    {
        Gate::authorize('view', $eduClass);

        $canMarkAttendance = Gate::allows('markAttendance', $eduClass);
        $canRecordOffering = Gate::allows('recordOffering', $eduClass);

        // ── Auto-resolve Sunday date ────────────────────────────────
        // Lớp CN thường: tìm CN gần nhất (đã qua hoặc hôm nay)
        // Lớp gospel: có thể linh hoạt, vẫn dùng CN gần nhất nhưng có thêm nút tạo mới
        $targetDate = $request->get('date'); // allow override via query param

        if ($targetDate) {
            $sunday = \Carbon\Carbon::parse($targetDate);
        } else {
            // Tìm CN gần nhất (hôm nay hoặc CN trước)
            $today  = now()->startOfDay();
            $sunday = $today->copy();
            while ($sunday->dayOfWeek !== 0) { // 0 = Chủ Nhật
                $sunday->subDay();
            }
        }

        $session = EduSession::firstOrCreate(
            ['edu_class_id' => $eduClass->id, 'session_date' => $sunday->toDateString()],
            ['lesson_number' => null, 'topic' => null, 'scripture' => null, 'lesson_series' => null]
        );

        // CN trước và CN sau
        $prevSunday = $sunday->copy()->subWeek()->toDateString();
        $nextSunday = $sunday->copy()->addWeek()->toDateString();
        // Không cho navigate đến CN tương lai (trǫu gospel thì allow)
        $canGoNext  = $eduClass->class_type === 'gospel' || $sunday->copy()->addWeek()->lte(now());

        // Load học viên + records
        $students = $eduClass->classMembers()
            ->where('role', 'student')
            ->with('member:id,full_name,phone')
            ->get()
            ->map(function ($cm) use ($session) {
                $record = EduSessionRecord::where('edu_session_id', $session->id)
                    ->where('member_id', $cm->member_id)
                    ->first();
                return [
                    'member_id'       => $cm->member_id,
                    'full_name'       => $cm->member->full_name ?? '—',
                    'phone'           => $cm->member->phone ?? null,
                    'attendance'      => $record?->attendance ?? 'absent',
                    'memorized_verse' => (bool) ($record?->memorized_verse ?? false),
                    'quiz_score'      => $record?->quiz_score,
                    'record_id'       => $record?->id,
                ];
            });

        // Quỹ + giao dịch buổi này
        $funds = $eduClass->funds()->with([
            'transactions' => fn($q) => $q->where('edu_session_id', $session->id)
        ])->get()->map(fn($f) => [
            'id'           => $f->id,
            'name'         => $f->name,
            'balance'      => $f->balance,
            'transactions' => $f->transactions->map(fn($t) => [
                'id'          => $t->id,
                'type'        => $t->type,
                'amount'      => $t->amount,
                'category'    => $t->category,
                'description' => $t->description,
            ])->values(),
        ]);

        // Load giáo viên trong lớp
        $teachers = $eduClass->classMembers()
            ->where('role', 'teacher')
            ->with('member:id,full_name')
            ->get()
            ->map(fn($cm) => ['id' => $cm->member_id, 'full_name' => $cm->member->full_name ?? '—']);

        // Teacher phụ trách buổi này
        $session->loadMissing('teacher:id,full_name');

        return Inertia::render('Portal/Education/Session', [
            'eduClass'          => [
                'id'         => $eduClass->id,
                'name'       => $eduClass->name,
                'class_type' => $eduClass->class_type,
            ],
            'session'           => [
                'id'             => $session->id,
                'session_date'   => $session->session_date->toDateString(),
                'lesson_number'  => $session->lesson_number,
                'lesson_series'  => $session->lesson_series,
                'topic'          => $session->topic,
                'scripture'      => $session->scripture,
                'notes'          => $session->notes,
                'attendance_mode'=> $session->attendance_mode ?? 'checkin',
                'total_present'  => $session->total_present,
                'total_absent'   => $session->total_absent,
                'teacher_id'     => $session->teacher_id,
                'teacher_name'   => $session->teacher?->full_name,
            ],
            'navigation'        => [
                'prev_sunday'  => $prevSunday,
                'next_sunday'  => $nextSunday,
                'can_go_next'  => $canGoNext,
                'current_date' => $sunday->toDateString(),
            ],
            'teachers'          => $teachers,
            'students'          => $students,
            'funds'             => $funds,
            'canMarkAttendance' => $canMarkAttendance,
            'canRecordOffering' => $canRecordOffering,
            'portalType'        => 'education',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // SESSION BY ID — Xem buổi học theo session_id cụ thể
    // ══════════════════════════════════════════════════════════════
    public function sessionById(Request $request, EduClass $eduClass, EduSession $eduSession)
    {
        Gate::authorize('view', $eduClass);

        $canMarkAttendance = Gate::allows('markAttendance', $eduClass);
        $canRecordOffering = Gate::allows('recordOffering', $eduClass);

        // Load students with their records for THIS session
        $students = $eduClass->classMembers()
            ->where('role', 'student')
            ->with('member:id,full_name,phone')
            ->get()
            ->map(function ($cm) use ($eduSession) {
                $record = EduSessionRecord::where('edu_session_id', $eduSession->id)
                    ->where('member_id', $cm->member_id)
                    ->first();
                return [
                    'member_id'       => $cm->member_id,
                    'full_name'       => $cm->member->full_name ?? '—',
                    'phone'           => $cm->member->phone ?? null,
                    'attendance'      => $record?->attendance ?? 'absent',
                    'memorized_verse' => (bool) ($record?->memorized_verse ?? false),
                    'quiz_score'      => $record?->quiz_score,
                    'record_id'       => $record?->id,
                ];
            });

        $funds = $eduClass->funds()->with([
            'transactions' => fn($q) => $q->where('edu_session_id', $eduSession->id)
        ])->get()->map(fn($f) => [
            'id'           => $f->id,
            'name'         => $f->name,
            'balance'      => $f->balance,
            'transactions' => $f->transactions->map(fn($t) => [
                'id'          => $t->id,
                'type'        => $t->type,
                'amount'      => $t->amount,
                'category'    => $t->category,
                'description' => $t->description,
            ])->values(),
        ]);

        $allSessions = EduSession::where('edu_class_id', $eduClass->id)
            ->orderBy('session_date', 'desc')
            ->get(['id', 'session_date', 'lesson_number', 'topic'])
            ->map(fn($s) => [
                'id'            => $s->id,
                'session_date'  => $s->session_date->toDateString(),
                'lesson_number' => $s->lesson_number,
                'topic'         => $s->topic,
                'is_today'      => $s->session_date->isToday(),
            ]);

        // Load giáo viên trong lớp
        $teachers = $eduClass->classMembers()
            ->where('role', 'teacher')
            ->with('member:id,full_name')
            ->get()
            ->map(fn($cm) => ['id' => $cm->member_id, 'full_name' => $cm->member->full_name ?? '—']);

        $eduSession->loadMissing('teacher:id,full_name', 'grader:id,full_name');

        return Inertia::render('Portal/Education/Session', [
            'eduClass'          => [
                'id'         => $eduClass->id,
                'name'       => $eduClass->name,
                'class_type' => $eduClass->class_type ?? 'sunday_school',
            ],
            'session'           => [
                'id'              => $eduSession->id,
                'session_date'    => $eduSession->session_date->toDateString(),
                'lesson_number'   => $eduSession->lesson_number,
                'lesson_series'   => $eduSession->lesson_series,
                'topic'           => $eduSession->topic,
                'scripture'       => $eduSession->scripture,
                'notes'           => $eduSession->notes,
                'attendance_mode' => $eduSession->attendance_mode ?? 'checkin',
                'total_present'   => $eduSession->total_present,
                'total_absent'    => $eduSession->total_absent,
                'teacher_id'      => $eduSession->teacher_id,
                'teacher_name'    => $eduSession->teacher?->full_name,
                // Bible quiz fields
                'book'            => $eduSession->book,
                'total_questions' => $eduSession->total_questions,
                'grader_id'       => $eduSession->grader_id,
                'grader_name'     => $eduSession->grader?->full_name,
                'photo_path'      => $eduSession->photo_path
                    ? asset('storage/' . $eduSession->photo_path)
                    : null,
            ],
            'teachers'          => $teachers,
            'students'          => $students,
            'funds'             => $funds,
            'allSessions'       => $allSessions,
            'canMarkAttendance' => $canMarkAttendance,
            'canRecordOffering' => $canRecordOffering,
            'portalType'        => 'ministry',
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // UPDATE SESSION INFO (lesson_number, topic, scripture, notes)
    // ══════════════════════════════════════════════════════════════
    public function updateSession(Request $request, EduClass $eduClass, EduSession $eduSession)
    {
        Gate::authorize('markAttendance', $eduClass);

        $validated = $request->validate([
            'lesson_number'  => 'nullable|integer|min:0',
            'lesson_series'  => 'nullable|string|max:255',
            'topic'          => 'nullable|string|max:255',
            'scripture'      => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
            'teacher_id'     => 'nullable|exists:members,id',
            // Bible quiz fields
            'book'           => 'nullable|string|max:100',
            'total_questions'=> 'nullable|integer|min:1|max:200',
            'grader_id'      => 'nullable|exists:members,id',
            'photo'          => 'nullable|image|max:5120', // max 5MB
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('edu/quiz-photos', 'public');
            $validated['photo_path'] = $path;
        }
        unset($validated['photo']);

        $eduSession->update($validated);

        return back()->with('success', 'Đã cập nhật thông tin bài học.');
    }

    // ══════════════════════════════════════════════════════════════
    // SAVE ATTENDANCE — Lưu điểm danh + điểm thi toàn bộ học viên
    // ══════════════════════════════════════════════════════════════
    public function saveAttendance(Request $request, EduClass $eduClass, EduSession $eduSession)
    {
        Gate::authorize('markAttendance', $eduClass);

        $mode = $request->input('mode', 'checkin');

        if ($mode === 'quick') {
            // ── Quick mode: chỉ lưu số tổng ─────────────────────────
            $validated = $request->validate([
                'total_present' => 'required|integer|min:0',
                'total_absent'  => 'nullable|integer|min:0',
            ]);
            $eduSession->update([
                'attendance_mode' => 'quick',
                'total_present'   => $validated['total_present'],
                'total_absent'    => $validated['total_absent'] ?? 0,
            ]);
            return back()->with('success', 'Đã lưu điểm danh nhanh.');
        }

        // ── Check-in mode: lưu từng học viên ────────────────────────
        $validated = $request->validate([
            'records'                   => 'required|array',
            'records.*.member_id'       => 'required|exists:members,id',
            'records.*.attendance'      => 'required|in:present,absent,excused',
            'records.*.memorized_verse' => 'nullable|boolean',
            'records.*.quiz_score'      => 'nullable|integer|min:0|max:100',
        ]);

        DB::transaction(function () use ($validated, $eduSession) {
            foreach ($validated['records'] as $rec) {
                EduSessionRecord::updateOrCreate(
                    ['edu_session_id' => $eduSession->id, 'member_id' => $rec['member_id']],
                    [
                        'attendance'      => $rec['attendance'],
                        'memorized_verse' => $rec['memorized_verse'] ?? false,
                        'quiz_score'      => $rec['quiz_score'] ?? null,
                    ]
                );
            }
        });

        $eduSession->update([
            'attendance_mode' => 'checkin',
            'total_present'   => null,
            'total_absent'    => null,
        ]);

        return back()->with('success', 'Đã lưu điểm danh thành công.');
    }


    // ══════════════════════════════════════════════════════════════
    // STORE OFFERING — Nhập tiền dâng buổi học
    // ══════════════════════════════════════════════════════════════
    public function storeOffering(Request $request, EduClass $eduClass, EduSession $eduSession)
    {
        Gate::authorize('recordOffering', $eduClass);

        $validated = $request->validate([
            'edu_class_fund_id' => 'required|exists:edu_class_funds,id',
            'type'              => 'required|in:income,expense',
            'amount'            => 'required|integer|min:1',
            'category'          => 'nullable|string|max:255',
            'description'       => 'nullable|string',
        ]);

        // Đảm bảo quỹ thuộc lớp này
        $fund = EduClassFund::where('id', $validated['edu_class_fund_id'])
            ->where('edu_class_id', $eduClass->id)
            ->firstOrFail();

        EduClassTransaction::create([
            'edu_class_fund_id' => $fund->id,
            'edu_session_id'    => $eduSession->id,
            'type'              => $validated['type'],
            'amount'            => $validated['amount'],
            'category'          => $validated['category'] ?? null,
            'description'       => $validated['description'] ?? null,
            'transaction_date'  => $eduSession->session_date,
            'status'            => 'approved', // Không cần duyệt
        ]);

        return back()->with('success', 'Đã ghi nhận tiền dâng.');
    }

    // ══════════════════════════════════════════════════════════════
    // DELETE OFFERING
    // ══════════════════════════════════════════════════════════════
    public function destroyOffering(EduClass $eduClass, EduSession $eduSession, EduClassTransaction $transaction)
    {
        Gate::authorize('recordOffering', $eduClass);
        $transaction->delete();
        return back()->with('success', 'Đã xóa giao dịch.');
    }

    // ══════════════════════════════════════════════════════════════
    // MANAGE CLASSES (Admin only)
    // ══════════════════════════════════════════════════════════════
    public function store(Request $request)
    {
        Gate::authorize('create', EduClass::class);

        $departmentId = session('active_ministry_dept_id');
        if (!$departmentId) abort(400, 'Chưa chọn ban CĐGD.');

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_type'  => 'nullable|in:sunday_school,gospel,bible_quiz',
        ]);

        $class = EduClass::create([
            'department_id' => $departmentId,
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'class_type'    => $validated['class_type'] ?? 'sunday_school',
        ]);

        // Chỉ tạo quỹ tiền dâng cho lớp có thu tiền dâng (không phải gospel)
        if ($class->class_type !== 'gospel') {
            EduClassFund::create([
                'edu_class_id' => $class->id,
                'name'         => 'Quỹ Tiền Dâng',
                'description'  => 'Tiền dâng hàng tuần của lớp',
            ]);
        }

        return back()->with('success', 'Đã tạo lớp học mới.');
    }

    public function update(Request $request, EduClass $eduClass)
    {
        Gate::authorize('update', $eduClass);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_type'  => 'nullable|in:sunday_school,gospel,bible_quiz',
            'is_active'   => 'boolean',
        ]);

        $eduClass->update($validated);
        return back()->with('success', 'Đã cập nhật lớp học.');
    }

    // ══════════════════════════════════════════════════════════════
    // REPORT — Báo cáo tháng theo format Excel
    // ══════════════════════════════════════════════════════════════
    public function report(Request $request)
    {
        $user = $request->user();
        Gate::authorize('viewAny', EduClass::class);

        $departmentId = session('active_ministry_dept_id');
        $department   = $departmentId ? Department::find($departmentId) : null;

        // Lấy tháng từ query param, mặc định tháng hiện tại
        $month = $request->get('month', now()->format('Y-m'));
        [$year, $mon] = explode('-', $month);
        $startDate = \Carbon\Carbon::create($year, $mon, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        // Lấy tất cả lớp có trong kỳ (có session trong tháng)
        $classQuery = EduClass::with([
            'teachers.member:id,full_name',
            'funds.transactions' => fn($q) => $q->whereBetween('transaction_date', [$startDate, $endDate]),
        ])->where('is_active', true);

        if ($departmentId) {
            $classQuery->where('department_id', $departmentId);
        }

        $classes = $classQuery->get();

        // Lấy các Chủ Nhật trong tháng (session định kỳ chủ nhật)
        $sundays = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            if ($cursor->dayOfWeek === 0) { // 0 = Chủ Nhật
                $sundays[] = $cursor->toDateString();
            }
            $cursor->addDay();
        }
        // Nếu không có Chủ Nhật nào, lấy tất cả ngày có session
        if (empty($sundays)) {
            $sundays = EduSession::whereHas('eduClass', fn($q) => $q->where('is_active', true))
                ->whereBetween('session_date', [$startDate, $endDate])
                ->orderBy('session_date')->distinct()->pluck('session_date')->map->toDateString()->toArray();
        }

        // Lấy sessions + records cho từng lớp trong tháng
        $classData = [];
        foreach ($classes as $cls) {
            $sessions = EduSession::with([
                'records' => fn($q) => $q->select('edu_session_id','member_id','attendance','memorized_verse','quiz_score'),
                'teacher:id,full_name',
            ])
                ->where('edu_class_id', $cls->id)
                ->whereBetween('session_date', [$startDate, $endDate])
                ->orderBy('session_date')
                ->get()
                ->keyBy(fn($s) => $s->session_date->toDateString());

            // Tài chính tháng
            $totalIncome  = 0;
            $totalExpense = 0;
            foreach ($cls->funds as $fund) {
                foreach ($fund->transactions as $tx) {
                    if ($tx->type === 'income') $totalIncome += $tx->amount;
                    else $totalExpense += $tx->amount;
                }
            }

            // Dữ liệu theo từng ngày tổ chức
            $sessionsData = [];
            foreach ($sundays as $dateStr) {
                $sess = $sessions[$dateStr] ?? null;
                $presentCount = $sess ? $sess->records->where('attendance', 'present')->count() : 0;
                $sessIncome = 0;
                if ($sess) {
                    foreach ($cls->funds as $fund) {
                        $sessIncome += $fund->transactions
                            ->where('edu_session_id', $sess->id)
                            ->where('type', 'income')
                            ->sum('amount');
                    }
                }
                $sessionsData[$dateStr] = [
                    'present'      => $presentCount,
                    'income'       => $sessIncome,
                    'has_data'     => $sess !== null,
                    'teacher_name' => $sess?->teacher?->full_name,
                    'topic'        => $sess?->topic,
                    'lesson_number'=> $sess?->lesson_number,
                ];
            }

            // Quiz data cho bible_quiz class
            $quizData = [];
            if ($cls->class_type === 'bible_quiz') {
                // Lấy danh sách học viên và điểm
                $studentIds = $cls->classMembers()->where('role','student')->pluck('member_id');
                $students = Member::whereIn('id', $studentIds)->select('id','full_name')->orderBy('full_name')->get();
                foreach ($students as $student) {
                    $attended = 0;
                    $totalScore = 0;
                    $scoreCount = 0;
                    foreach ($sessions as $sess) {
                        $record = $sess->records->firstWhere('member_id', $student->id);
                        if ($record && $record->attendance === 'present') $attended++;
                        if ($record && $record->quiz_score !== null) { $totalScore += $record->quiz_score; $scoreCount++; }
                    }
                    $quizData[] = [
                        'name'        => $student->full_name,
                        'attended'    => $attended,
                        'total_sessions' => $sessions->count(),
                        'avg_score'   => $scoreCount > 0 ? round($totalScore / $scoreCount, 1) : null,
                        'is_faithful' => $sessions->count() > 0 && $attended >= $sessions->count(), // trung tín: không vắng
                    ];
                }
            }

            // Tính trung bình
            $daysWithData = collect($sessionsData)->filter(fn($d) => $d['has_data']);
            $avgPresent = $daysWithData->count() > 0 ? round($daysWithData->avg('present'), 1) : 0;

            $classData[] = [
                'id'            => $cls->id,
                'name'          => $cls->name,
                'class_type'    => $cls->class_type,
                'sessions'      => $sessionsData,
                'total_income'  => $totalIncome,
                'total_expense' => $totalExpense,
                'avg_present'   => $avgPresent,
                'quiz_data'     => $quizData,
                'total_students' => $cls->classMembers()->where('role', 'student')->count(),
                'teachers'      => $cls->teachers->map(fn($t) => $t->member->full_name ?? '')->filter()->join(', '),
            ];
        }

        // Lấy báo cáo nhận xét tháng này
        [$y, $m] = explode('-', $month);
        $eduReport = \App\Models\EduReport::where('report_month', (int)$m)
            ->where('report_year', (int)$y)->first();

        $canManageReport = $user->hasRole(['Super_Admin', 'Pastor', 'Department_Lead', 'Team_Lead'])
            || $user->hasPermissionTo('create_reports') || $user->can('markAttendance', new EduClass);

        return Inertia::render('Portal/Education/Report', [
            'department'          => $department,
            'availableDepartments' => $this->getAvailableDepts($user),
            'isGlobalAdmin'       => $user->hasRole(['Super_Admin', 'Pastor']),
            'canManageReport'     => $canManageReport,
            'canApproveReport'    => $user->hasRole(['Super_Admin', 'Pastor']),
            'month'               => $month,
            'sundays'             => $sundays,
            'classData'           => $classData,
            'eduReport'           => $eduReport,
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // REPORT — Lưu nhận xét / đánh giá tháng
    // ══════════════════════════════════════════════════════════════
    public function saveReport(Request $request)
    {
        Gate::authorize('viewAny', EduClass::class);

        $validated = $request->validate([
            'report_month'     => 'required|integer|min:1|max:12',
            'report_year'      => 'required|integer|min:2020|max:2099',
            'reporter_name'    => 'nullable|string|max:100',
            'status'           => 'nullable|in:draft,submitted',
            'evaluation'       => 'nullable|string|max:2000',
            'highlights'       => 'nullable|string|max:2000',
            'challenges'       => 'nullable|string|max:2000',
            'request'          => 'nullable|string|max:2000',
            'proposals'        => 'nullable|string|max:2000',
            'activities_notes' => 'nullable|string|max:2000',
        ]);

        \App\Models\EduReport::updateOrCreate(
            ['report_month' => $validated['report_month'], 'report_year' => $validated['report_year']],
            $validated
        );

        return back()->with('success', 'Đã lưu báo cáo.');
    }

    // ══════════════════════════════════════════════════════════════
    // REPORT — Duyệt báo cáo
    // ══════════════════════════════════════════════════════════════
    public function approveReport(Request $request, \App\Models\EduReport $eduReport)
    {
        Gate::authorize('viewAny', EduClass::class);
        $user = $request->user();
        if (!$user->hasRole(['Super_Admin', 'Pastor'])) {
            abort(403, 'Chỉ Quản lý / Mục sư mới được duyệt báo cáo.');
        }
        $eduReport->update(['status' => 'approved']);
        return back()->with('success', 'Báo cáo đã được duyệt.');
    }

    // ══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════
    private function getAvailableDepts($user): \Illuminate\Support\Collection
    {
        return Department::where('block', 'ministry')
            ->select('id', 'name')
            ->get();
    }
}
