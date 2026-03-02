<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Department;
use Illuminate\Support\Facades\Gate;

class AssignmentsController extends Controller
{
    public function index(Request $request)
    {
        $departmentId = session('active_portal_dept_id');
        if (!$departmentId) {
            return redirect()->route('portal.index');
        }

        $department = Department::findOrFail($departmentId);
        Gate::authorize('access_portal', [Department::class, $department]);

        $availableDepartments = [];
        if (auth()->user()->hasRole(['Pastor', 'Super_Admin'])) {
            $availableDepartments = Department::where('block', 'activities')->get();
        } else {
            $memberId = auth()->user()->member_id;
            if ($memberId) {
                $availableDepartments = Department::whereIn('id', function($query) use ($memberId) {
                    $query->select('model_id')
                          ->from('org_memberships')
                          ->where('model_type', Department::class)
                          ->where('member_id', $memberId);
                })->where('block', 'activities')->get();
            }
        }

        return Inertia::render('Portal/Assignments/Index', [
            'department' => $department,
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin' => auth()->user()->hasRole(['Pastor', 'Super_Admin']),
        ]);
    }
}

