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
        
        $query = CareRequest::with(['user', 'assignee'])->latest();

        if (!$isPastor) {
            // Tín hữu chỉ thấy yêu cầu của chính mỉnh
            $query->where('user_id', $user->id);
            // Kể cả Ban Chăm sóc/Deacon nếu không phải Pastor thì không xem được yêu cầu Tư vấn (Is_private)
            if ($user->isSuperAdmin()) {
                // Hoặc được assign, hoặc do chính mình tạo
                $query->orWhere(function($q) use ($user) {
                    $q->where('assigned_to', $user->id)->where('is_private', false);
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
            'isPastor' => $isPastor
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:prayer,counseling,feedback,support',
            'priority' => 'required|in:low,normal,high,urgent',
            'is_private' => 'boolean'
        ]);

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
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'assigned_to' => 'nullable|exists:users,id'
        ]);
        
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
