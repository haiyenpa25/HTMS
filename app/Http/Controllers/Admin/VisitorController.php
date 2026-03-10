<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use App\Models\VisitorFollowup;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $query = Visitor::with(['assignedTo', 'followups' => function($q) {
            $q->latest('contact_date')->limit(1); // Lấy lần chăm sóc gần nhất
        }])->latest();

        // Filters
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $visitors = $query->paginate(15)->withQueryString();
        
        // Thống kê Phễu (Funnel)
        $funnel = [
            'total' => Visitor::count(),
            'new' => Visitor::where('status', 'new')->count(),
            'contacted' => Visitor::where('status', 'contacted')->count(),
            'studying' => Visitor::where('status', 'studying')->count(),
            'baptized' => Visitor::where('status', 'baptized')->count(),
        ];

        // Lấy danh sách nhân sự có thể phân công (Mục sư, Chấp sự, Ban Thanh niên...)
        // Tạm thời lấy tất cả user trừ member bình thường
        $assignableUsers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super_Admin', 'Admin', 'Thư_Ký', 'Trưởng_Ban']);
        })->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Visitors/Index', [
            'visitors' => $visitors,
            'filters' => $request->only(['search', 'status']),
            'funnel' => $funnel,
            'assignableUsers' => $assignableUsers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'first_visit_date' => 'nullable|date',
            'invited_by' => 'nullable|string|max:255',
            'prayer_requests' => 'nullable|string',
            'status' => 'required|in:new,contacted,studying,baptized,lost',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        Visitor::create($validated);

        return redirect()->back()->with('success', 'Đã thêm hồ sơ Thân Hữu mới.');
    }

    public function update(Request $request, Visitor $visitor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'first_visit_date' => 'nullable|date',
            'invited_by' => 'nullable|string|max:255',
            'prayer_requests' => 'nullable|string',
            'status' => 'required|in:new,contacted,studying,baptized,lost',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $visitor->update($validated);

        return redirect()->back()->with('success', 'Hồ sơ Thân hữu đã được cập nhật.');
    }

    public function destroy(Visitor $visitor)
    {
        $visitor->delete();
        return redirect()->back()->with('success', 'Đã xóa Thân hữu khỏi danh sách.');
    }

    // --- FOLLOW UPS ---
    public function storeFollowup(Request $request, Visitor $visitor)
    {
        $validated = $request->validate([
            'type' => 'required|in:call,visit,message,meeting',
            'contact_date' => 'required|date',
            'notes' => 'required|string',
            'outcome' => 'required|in:positive,neutral,negative,no_answer',
            'update_status' => 'nullable|in:new,contacted,studying,baptized,lost'
        ]);

        $visitor->followups()->create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'contact_date' => $validated['contact_date'],
            'notes' => $validated['notes'],
            'outcome' => $validated['outcome'],
        ]);

        // Nếu có yêu cầu chuyển phase
        if ($request->has('update_status') && $request->update_status) {
            $visitor->update(['status' => $request->update_status]);
        }

        return redirect()->back()->with('success', 'Đã lưu lại chi tiết lần chăm sóc.');
    }

    public function getFollowups(Visitor $visitor)
    {
        return response()->json(
            $visitor->followups()->with('user:id,name')->latest('contact_date')->get()
        );
    }
}
