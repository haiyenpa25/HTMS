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
            'filters' => $request->only(['search', 'status', 'marital_status', 'is_baptized', 'join_time']),
        ]);
    }

    /**
     * Display the specified member.
     */
    public function show(Request $request, Member $member): Response
    {
        $user = $request->user();
        $isPastor = $user->hasRole('Pastor');

        $loadRelations = [
            'user', 
            'departments', 
            'teams', 
            'household',
            'courses',
            'talents',
            'relatedTo',
            'relatedFrom',
            'careLogs' => function($query) use ($isPastor) {
                $query->orderBy('visit_date', 'desc');
                if (!$isPastor) {
                    $query->where('is_sensitive', false);
                }
            }
        ];

        if ($isPastor) {
            $loadRelations[] = 'sensitiveInfo';
        }

        $member->load($loadRelations);
        
        return Inertia::render('Members/Show', [
            'member' => $member,
            'auth_roles' => $user->getRoleNames(),
        ]);
    }
}
