<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */
    public function index(): Response
    {
        $departments = Department::with(['parent'])
            ->withCount(['teams', 'members'])
            ->get();

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
        ]);
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department): Response
    {
        $department->load(['teams', 'members.user', 'supervisors.user']);
        
        return Inertia::render('Departments/Show', [
            'department' => $department,
        ]);
    }
}
