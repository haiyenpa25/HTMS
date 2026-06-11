<?php

namespace App\Http\Controllers;

use App\Models\CareRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CareController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isPastor = $user->isSuperAdmin();
        $isPortal = $request->routeIs('portal.*') || $request->routeIs('ministry.*');
        $departmentId = $request->session()->get('active_department_id');
        
        $query = CareRequest::with(['user', 'assignee', 'department'])->latest();

        if ($isPortal && $departmentId) {
            // Portal: Xem yêu cầu thuộc Ban ngành này
            $query->where('department_id', $departmentId);
            if (!$isPastor) {
                $query->where(function($q) use ($user) {
                    $q->where('is_private', false)
                      ->orWhere('user_id', $user->id)
                      ->orWhere('assigned_to', $user->id);
                });
            }
        } else {
            // Admin / Global
            if (!$isPastor) {
                // Tín hữu chỉ thấy yêu cầu của chính mình hoặc được assign
                $query->where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('assigned_to', $user->id);
                });
            }
        }

        // Lọc category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        
        // Lọc status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(15)->withQueryString();

        return Inertia::render('Care/Index', [
            'requests' => $requests,
            'filters' => $request->only(['category', 'status']),
            'isPastor' => $isPastor,
            'isPortal' => $isPortal
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:prayer,counseling,feedback,support',
            'priority' => 'required|in:low,normal,high,urgent',
            'is_private' => 'boolean',
            'department_id' => 'nullable|exists:departments,id'
        ]);

        $isPortal = $request->routeIs('portal.*') || $request->routeIs('ministry.*');
        $deptId = $isPortal ? $request->session()->get('active_department_id') : ($validated['department_id'] ?? null);

        // Nếu là tư vấn thì luôn coi là ưu tiên và mật
        if ($validated['category'] === 'counseling') {
            $validated['is_private'] = true;
            if ($validated['priority'] === 'normal') $validated['priority'] = 'high';
        }

        CareRequest::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'is_private' => $validated['is_private'] ?? false,
            'department_id' => $deptId,
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Yêu cầu của bạn đã được gửi. Chúng tôi sẽ sớm liên hệ hoặc ghi nhớ trong lời cầu nguyện.');
    }

    public function updateStatus(Request $request, CareRequest $careRequest)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $careRequest->assigned_to !== $user->id) {
            abort(403, 'Bạn không có quyền chuyển trạng thái yêu cầu này.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string'
        ]);

        $careRequest->update($validated);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái yêu cầu.');
    }
    
    public function assign(Request $request, CareRequest $careRequest)
    {
        $user = Auth::user();

        // SuperAdmin: luôn được assign toàn cục
        if ($user->isSuperAdmin()) {
            $validated = $request->validate(['assigned_to' => 'nullable|exists:users,id']);
            $careRequest->update(['assigned_to' => $validated['assigned_to']]);
            return redirect()->back()->with('success', 'Đã phân công nhân sự xử lý.');
        }

        // Portal Leader: resolve session key theo đúng portal type
        $sessionKey = 'active_portal_dept_id';  // default: activities portal
        if ($request->routeIs('ministry.*')) {
            $sessionKey = 'active_ministry_dept_id';
        } elseif ($request->routeIs('deacon.*')) {
            $sessionKey = 'active_deacon_dept_id';
        }

        $departmentId = $request->session()->get($sessionKey);
        $isPortal = $request->routeIs('portal.*') || $request->routeIs('ministry.*') || $request->routeIs('deacon.*');

        if (!$isPortal || !$departmentId) {
            abort(403, 'Bạn không có quyền phân công yêu cầu này.');
        }

        // Care request phải thuộc department này (và phải có department_id — không cho assign request chưa gán ban)
        if (!$careRequest->department_id || $careRequest->department_id !== (int) $departmentId) {
            abort(403, 'Bạn chỉ có thể phân công yêu cầu trong phạm vi ban ngành của mình.');
        }

        $validated = $request->validate(['assigned_to' => 'nullable|exists:users,id']);
        $careRequest->update(['assigned_to' => $validated['assigned_to']]);

        return redirect()->back()->with('success', 'Đã phân công nhân sự xử lý.');
    }

    public function destroy(CareRequest $careRequest)
    {
        $user = Auth::user();
        if ($user->id !== $careRequest->user_id && !$user->isSuperAdmin()) {
            abort(403);
        }
        
        $careRequest->delete();
        return redirect()->back()->with('success', 'Đã huỷ bỏ/xóa yêu cầu.');
    }
}
