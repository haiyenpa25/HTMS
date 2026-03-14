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

        $availableDepartments = app(\App\Services\PortalService::class)->getAvailableDepartments(auth()->user(), 'activities');


        return Inertia::render('Portal/Assignments/Index', [
            'department' => $department,
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin' => auth()->user()->isSuperAdmin(),
        ]);
    }
}

