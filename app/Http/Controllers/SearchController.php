<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Department;
use App\Models\Meeting;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q'));
        if (strlen($q) < 2) return response()->json([]);

        $results = [];

        // Search Members (Top 5)
        $members = Member::where('full_name', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->limit(5)->get();
        foreach ($members as $m) {
            $results[] = [
                'type' => 'Tín hữu',
                'title' => $m->full_name,
                'subtitle' => $m->phone,
                'url' => route('members.show', $m->id),
                'icon' => 'user'
            ];
        }

        // Search Departments (Top 3)
        $depts = Department::where('name', 'like', "%{$q}%")
            ->limit(3)->get();
        foreach ($depts as $d) {
            $results[] = [
                'type' => 'Ban ngành',
                'title' => $d->name,
                'subtitle' => 'Phân ban: ' . ($d->block === 'ministry' ? 'Mục vụ' : 'Sinh hoạt'),
                'url' => route('departments.show', $d->id),
                'icon' => 'office-building'
            ];
        }

        // Search Meetings (Top 3, upcoming only)
        $meetings = Meeting::where('topic', 'like', "%{$q}%")
            ->where('date', '>=', now()->toDateString())
            ->limit(3)->get();
        foreach ($meetings as $m) {
            $results[] = [
                'type' => 'Buổi nhóm',
                'title' => $m->topic ?? 'Buổi nhóm chung',
                'subtitle' => \Carbon\Carbon::parse($m->date)->format('d/m/Y') . ' - ' . ($m->type === 'church' ? 'Hội thánh' : 'Ban ngành'),
                'url' => route('meetings.show', $m->id),
                'icon' => 'calendar'
            ];
        }

        return response()->json($results);
    }
}
