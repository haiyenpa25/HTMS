<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('department')->latest();

        // Filters
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('brand', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $assets = $query->paginate(15)->withQueryString();
        $departments = Department::cachedAll();
        
        // Thống kê sơ bộ
        $stats = [
            'total' => Asset::count(),
            'in_use' => Asset::where('status', 'in_use')->count(),
            'maintenance' => Asset::where('status', 'maintenance')->count(),
            'lost' => Asset::where('status', 'lost')->count(),
            'total_value' => Asset::sum('purchase_price')
        ];

        return Inertia::render('Admin/Assets/Index', [
            'assets' => $assets,
            'departments' => $departments,
            'filters' => $request->only(['search', 'category', 'status']),
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:assets',
            'category' => 'required|in:electronics,furniture,musical,books,vehicle,other',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:new,in_use,maintenance,broken,lost,liquidated',
            'department_id' => 'nullable|exists:departments,id',
            'notes' => 'nullable|string'
        ]);

        Asset::create($validated);

        return redirect()->back()->with('success', 'Tài sản đã được thêm mới.');
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:assets,code,' . $asset->id,
            'category' => 'required|in:electronics,furniture,musical,books,vehicle,other',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:new,in_use,maintenance,broken,lost,liquidated',
            'department_id' => 'nullable|exists:departments,id',
            'notes' => 'nullable|string'
        ]);

        $asset->update($validated);

        return redirect()->back()->with('success', 'Thông tin tài sản đã được cập nhật.');
    }

    public function destroy(Asset $asset)
    {
        if ($asset->loans()->where('status', 'borrowing')->exists()) {
            return redirect()->back()->with('error', 'Không thể xóa tài sản đang được mượn.');
        }
        
        $asset->delete();
        return redirect()->back()->with('success', 'Tài sản đã được xoá.');
    }

    // --- LOAN MANAGEMENT ---
    public function fetchLoans(Asset $asset)
    {
        $loans = $asset->loans()
            ->with(['borrower:id,name', 'department:id,name', 'issuer:id,name', 'receiver:id,name'])
            ->latest()
            ->get();
            
        return response()->json($loans);
    }
    
    public function searchBorrowers(Request $request)
    {
        $query = $request->get('q');
        if (!$query || strlen($query) < 2) return response()->json([]);
        
        $users = User::where('name', 'like', "%{$query}%")
                     ->orWhere('email', 'like', "%{$query}%")
                     ->select('id', 'name', 'email')
                     ->take(10)
                     ->get();
                     
        return response()->json($users);
    }

    public function loanAsset(Request $request, Asset $asset)
    {
        if ($asset->loans()->where('status', 'borrowing')->exists()) {
            return redirect()->back()->with('error', 'Tài sản này đang được người khác mượn.');
        }

        $validated = $request->validate([
            'borrower_id' => 'required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'borrowed_at' => 'required|date',
            'expected_return_date' => 'required|date|after_or_equal:borrowed_at',
            'borrow_notes' => 'nullable|string'
        ]);

        $asset->loans()->create([
            'borrower_id' => $validated['borrower_id'],
            'department_id' => $validated['department_id'],
            'borrowed_at' => $validated['borrowed_at'],
            'expected_return_date' => $validated['expected_return_date'],
            'borrow_notes' => $validated['borrow_notes'],
            'issued_by' => Auth::id(),
            'status' => 'borrowing'
        ]);
        
        // Chuyển trạng thái tài sản thành đang sử dụng nếu nó là Mới
        if ($asset->status === 'new') {
            $asset->update(['status' => 'in_use']);
        }

        return redirect()->back()->with('success', 'Đã ghi nhận mượn tài sản.');
    }

    public function returnAsset(Request $request, AssetLoan $loan)
    {
        if ($loan->status !== 'borrowing' && $loan->status !== 'overdue') {
            return redirect()->back()->with('error', 'Cập nhật không hợp lệ.');
        }

        $validated = $request->validate([
            'returned_at' => 'required|date',
            'status' => 'required|in:returned,lost',
            'return_notes' => 'nullable|string',
            'asset_status' => 'required|in:in_use,maintenance,broken,lost' // Cập nhật luôn trạng thái thiết bị 
        ]);

        $loan->update([
            'status' => $validated['status'],
            'returned_at' => $validated['status'] === 'lost' ? null : $validated['returned_at'], // Báo mất thì không có ngày trả
            'return_notes' => $validated['return_notes'],
            'received_by' => Auth::id()
        ]);
        
        // Cập nhật tình trạng máy móc về kho
        $loan->asset->update([
            'status' => $validated['status'] === 'lost' ? 'lost' : $validated['asset_status']
        ]);

        return redirect()->back()->with('success', 'Đã ghi nhận trả tài sản về kho.');
    }
}
