<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Announcement;
use App\Models\Department;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('author:id,name,email')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->through(function ($a) {
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'content' => mb_strimwidth(strip_tags($a->content), 0, 100, '...'),
                    'author' => $a->author ? $a->author->name : 'Hệ thống',
                    'scope_type' => $a->scope_type,
                    'scope_name' => $a->scope_type === 'department' && $a->scope_id 
                                    ? Department::find($a->scope_id)?->name 
                                    : ($a->scope_type === 'global' ? 'Toàn Hội Thánh' : 'Tất cả'),
                    'created_at' => $a->created_at->format('Y-m-d H:i:s'),
                    'expires_at' => $a->expires_at ? $a->expires_at->format('Y-m-d H:i:s') : null,
                ];
            });

        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => $announcements,
        ]);
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);
        
        return Inertia::render('Admin/Announcements/Create', [
            'departments' => $departments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'scope_type' => 'required|in:global,department',
            'scope_id' => 'nullable|required_if:scope_type,department|integer',
            'expires_at' => 'nullable|date',
        ]);

        $validated['author_id'] = $request->user()->id;

        Announcement::create($validated);

        return redirect()->route('admin.announcements.index')->with('success', 'Đã đăng bản tin thành công.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', 'Đã xóa bản tin.');
    }
}
