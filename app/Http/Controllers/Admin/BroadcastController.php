<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailBroadcast;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendBroadcastEmail;

class BroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = EmailBroadcast::with('creator')->latest()->paginate(15);
        $roles = Role::all(['id', 'name']);
        $departments = Department::all(['id', 'name']);
        
        return Inertia::render('Admin/Broadcasts/Index', [
            'broadcasts' => $broadcasts,
            'roles' => $roles,
            'departments' => $departments
        ]);
    }

    public function create()
    {
        $roles = Role::all(['id', 'name']);
        $departments = Department::all(['id', 'name']);
        
        return Inertia::render('Admin/Broadcasts/Create', [
            'roles' => $roles,
            'departments' => $departments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'target_roles' => 'nullable|array',
            'target_departments' => 'nullable|array',
            'action' => 'required|in:save,send' // save as draft or send now
        ]);

        $broadcast = EmailBroadcast::create([
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'target_roles' => $validated['target_roles'] ?? null,
            'target_departments' => $validated['target_departments'] ?? null,
            'status' => 'draft',
            'created_by' => Auth::id()
        ]);

        if ($validated['action'] === 'send') {
            $this->dispatchBroadcast($broadcast);
            return redirect()->route('admin.broadcasts.index')->with('success', 'Bản tin đã được đưa vào hàng đợi gửi đi.');
        }

        return redirect()->route('admin.broadcasts.index')->with('success', 'Đã lưu Bản tin nháp.');
    }
    
    public function send(EmailBroadcast $broadcast)
    {
        if ($broadcast->status !== 'draft' && $broadcast->status !== 'failed') {
            return back()->with('error', 'Chỉ có thể gửi Bản tin ở trạng thái Nháp hoặc Lỗi.');
        }
        
        $this->dispatchBroadcast($broadcast);
        return back()->with('success', 'Bản tin đang được gửi đi...');
    }

    private function dispatchBroadcast(EmailBroadcast $broadcast)
    {
        $broadcast->update(['status' => 'sending']);
        
        // Dispatch job in background
        SendBroadcastEmail::dispatch($broadcast);
    }
    
    public function destroy(EmailBroadcast $broadcast)
    {
        if ($broadcast->status === 'sending') {
            return back()->with('error', 'Không thể xoá Bản tin đang gửi.');
        }
        $broadcast->delete();
        return back()->with('success', 'Đã xoá Bản tin.');
    }
}
