<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    /**
     * Display a listing of the members.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Use the scope defined in models/policies if available
        // For now, we'll fetch all and the front-end will display based on shared data
        // In a real app, you'd apply the filter here:
        // $members = Member::with(['user', 'departments', 'teams'])->whereCanView($user)->paginate(10);
        
        $members = Member::with(['departments', 'teams', 'sensitiveInfo'])
            ->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('member_code', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->marital_status, function ($query, $marital_status) {
                $query->whereHas('sensitiveInfo', function($q) use ($marital_status) {
                    $q->where('marital_status', $marital_status);
                });
            })
            ->when($request->is_baptized !== null, function ($query) use ($request) {
                $query->where('is_baptized', filter_var($request->is_baptized, FILTER_VALIDATE_BOOLEAN));
            })
            ->when($request->age_from, function ($query, $age) {
                $query->where('date_of_birth', '<=', now()->subYears($age));
            })
            ->when($request->join_time, function ($query, $time) {
                $now = now();
                switch ($time) {
                    case '3_months':
                        $query->where('joined_date', '>=', clone $now->subMonths(3));
                        break;
                    case '6_months':
                        $query->whereBetween('joined_date', [clone $now->subMonths(6), clone $now->subMonths(3)]);
                        break;
                    case '1_year':
                        $query->whereBetween('joined_date', [clone $now->subYear(), clone $now->subMonths(6)]);
                        break;
                    case '2_years_plus':
                        $query->where('joined_date', '<', clone $now->subYears(2));
                        break;
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Members/Index', [
            'members' => $members,
            'filters' => $request->only(['search', 'status', 'marital_status', 'is_baptized', 'join_time', 'age_from']),
        ]);
    }

    /**
     * API endpoint to get members with optional filters.
     */
    public function apiIndex(Request $request)
    {
        $members = Member::with(['sensitiveInfo'])
            ->when($request->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('member_code', 'like', "%{$search}%");
                });
            })
            ->when($request->marital_status, function ($query, $marital_status) {
                $query->whereHas('sensitiveInfo', function($q) use ($marital_status) {
                    $q->where('marital_status', $marital_status);
                });
            })
            ->when($request->age_from, function ($query, $age) {
                $query->where('date_of_birth', '<=', now()->subYears($age));
            })
            ->orderBy('full_name')
            ->limit(100)
            ->get();

        return response()->json($members->map(function ($member) {
            return [
                'id' => $member->id,
                'full_name' => $member->full_name,
                'phone' => $member->phone,
                'date_of_birth' => $member->date_of_birth,
                'marital_status' => $member->sensitiveInfo ? $member->sensitiveInfo->marital_status : 'Độc thân',
            ];
        }));
    }

    /**
     * Store a newly created member in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:members,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:Nam,Nữ',
            'address' => 'nullable|string|max:255',
            'status' => 'required|string',
            'marital_status' => 'nullable|string',
            'is_baptized' => 'boolean',
        ]);

        // Generate member code: TH + 2 dígits year + 4 random digits
        $memberCode = 'TH' . now()->format('y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        while (Member::where('member_code', $memberCode)->exists()) {
            $memberCode = 'TH' . now()->format('y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }

        $member = Member::create([
            'member_code' => $memberCode,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'address' => $validated['address'],
            'status' => $validated['status'],
            'is_baptized' => $validated['is_baptized'],
            'joined_date' => now()->format('Y-m-d'),
        ]);

        $member->sensitiveInfo()->create([
            'marital_status' => $validated['marital_status'] ?? 'Khác',
        ]);

        return redirect()->back()->with('message', 'Tạo tín hữu thành công!');
    }

    /**
     * Display the specified member.
     */
    public function show(Request $request, Member $member): Response
    {
        $user = $request->user();
        $isPastor = $user->hasRole(['Pastor', 'Super_Admin']);

        $member->load([
            'user',
            'household',
            'courses',
            'talents',
            'relatedTo',
            'relatedFrom',
            // Visitations as Care Log — load with visitors
            'visitations' => function($query) {
                $query->with(['visitors'])->orderBy('visit_date', 'desc');
            },
            // Memberships: org role in each department/team
            'memberships' => function($query) {
                $query->with(['role', 'model'])->where('is_active', true);
            },
            // Last attendance
            'attendances' => function($query) {
                $query->with('meeting')->orderBy('meeting_id', 'desc')->limit(1);
            },
        ]);

        if ($isPastor) {
            $member->load('sensitiveInfo');
        }
        
        return Inertia::render('Members/Show', [
            'member' => $member,
            'auth_roles' => $user->getRoleNames(),
            'isPastor' => $isPastor,
        ]);
    }

    /**
     * Update the specified member in storage.
     */
    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:members,email,' . $member->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:Nam,Nữ',
            'address' => 'nullable|string|max:255',
            'status' => 'required|string',
            'is_baptized' => 'boolean',
            'faith_date' => 'nullable|date',
            'baptism_date' => 'nullable|date',
            'general_notes' => 'nullable|string',
            
            // Sensitive Info (only pastors can see and update these typically, but we accept them and check role)
            'marital_status' => 'nullable|string',
            'prayer_concerns' => 'nullable|string',
            'pastoral_notes' => 'nullable|string',
            'occupation' => 'nullable|string',
        ]);

        $member->update([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'address' => $validated['address'],
            'status' => $validated['status'],
            'is_baptized' => $validated['is_baptized'],
            'faith_date' => $validated['faith_date'] ?? null,
            'baptism_date' => $validated['baptism_date'] ?? null,
            'general_notes' => $validated['general_notes'] ?? null,
        ]);

        if ($request->user()->hasRole('Pastor')) {
            $member->sensitiveInfo()->updateOrCreate(
                ['member_id' => $member->id],
                [
                    'marital_status' => $validated['marital_status'] ?? 'Khác',
                    'prayer_concerns' => $validated['prayer_concerns'] ?? null,
                    'pastoral_notes' => $validated['pastoral_notes'] ?? null,
                    'occupation' => $validated['occupation'] ?? null,
                ]
            );
        }

        return redirect()->back()->with('message', 'Cập nhật tín hữu thành công!');
    }
}
