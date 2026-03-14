<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DocsController extends Controller
{
    public function setup()
    {
        return Inertia::render('Docs/Setup');
    }

    public function overview()
    {
        return Inertia::render('Docs/Overview');
    }

    public function dutyRoster()
    {
        return Inertia::render('Docs/DutyRoster');
    }

    public function departments()
    {
        return redirect()->route('help.departments.members');
    }

    public function deptMembers() { return Inertia::render('Docs/Departments/Members'); }
    public function deptAttendance() { return Inertia::render('Docs/Departments/Attendance'); }
    public function deptVisitation() { return Inertia::render('Docs/Departments/Visitation'); }
    public function deptAssignments() { return Inertia::render('Docs/Departments/Assignments'); }
    public function deptFinance() { return Inertia::render('Docs/Departments/Finance'); }
    public function deptReports() { return Inertia::render('Docs/Departments/Reports'); }

    public function meetings()
    {
        return Inertia::render('Docs/Meetings');
    }

    public function sysadmin()
    {
        return Inertia::render('Docs/SysAdmin');
    }

    public function leadership()
    {
        return Inertia::render('Docs/Leadership');
    }

    public function education()
    {
        return Inertia::render('Docs/Education');
    }

    public function portals()
    {
        return Inertia::render('Docs/Portals');
    }

    public function members()
    {
        return Inertia::render('Docs/Members');
    }

    public function finance()
    {
        return Inertia::render('Docs/Finance');
    }
}
