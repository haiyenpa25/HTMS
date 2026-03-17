<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DocsController extends Controller
{
    public function auth(string $mode)
    {
        return Inertia::render('Docs/Auth', ['mode' => $mode]);
    }

    public function setup(string $mode)
    {
        return Inertia::render('Docs/Setup', ['mode' => $mode]);
    }

    public function overview(string $mode)
    {
        return Inertia::render('Docs/Overview', ['mode' => $mode]);
    }

    public function dutyRoster(string $mode)
    {
        return Inertia::render('Docs/DutyRoster', ['mode' => $mode]);
    }

    public function deptIntro(string $mode) { return Inertia::render('Docs/Departments/Intro', ['mode' => $mode]); }
    public function deptMembers(string $mode) { return Inertia::render('Docs/Departments/Members', ['mode' => $mode]); }
    public function deptAttendance(string $mode) { return Inertia::render('Docs/Departments/Attendance', ['mode' => $mode]); }
    public function deptVisitation(string $mode) { return Inertia::render('Docs/Departments/Visitation', ['mode' => $mode]); }
    public function deptAssignments(string $mode) { return Inertia::render('Docs/Departments/Assignments', ['mode' => $mode]); }
    public function deptFinance(string $mode) { return Inertia::render('Docs/Departments/Finance', ['mode' => $mode]); }
    public function deptReports(string $mode) { return Inertia::render('Docs/Departments/Reports', ['mode' => $mode]); }

    public function portalIntro(string $mode) { return Inertia::render('Docs/Portals/Intro', ['mode' => $mode]); }

    public function meetings(string $mode) { return Inertia::render('Docs/Meetings', ['mode' => $mode]); }
    
    public function adminUsers(string $mode) { return Inertia::render('Docs/Admin/Users', ['mode' => $mode]); }
    public function adminFeatures(string $mode) { return Inertia::render('Docs/Admin/Features', ['mode' => $mode]); }
    public function adminPermissions(string $mode) { return Inertia::render('Docs/Admin/Permissions', ['mode' => $mode]); }

    public function sysadmin(string $mode) { return Inertia::render('Docs/SysAdmin', ['mode' => $mode]); }
    public function leadership(string $mode) { return Inertia::render('Docs/Leadership', ['mode' => $mode]); }
    public function education(string $mode) { return Inertia::render('Docs/Education', ['mode' => $mode]); }
    public function portals(string $mode) { return Inertia::render('Docs/Portals', ['mode' => $mode]); }
    public function members(string $mode) { return Inertia::render('Docs/Members', ['mode' => $mode]); }
    public function finance(string $mode) { return Inertia::render('Docs/Finance', ['mode' => $mode]); }
}
