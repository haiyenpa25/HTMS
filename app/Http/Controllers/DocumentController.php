<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isPortal = $request->routeIs('portal.*') || $request->routeIs('ministry.*');
        $departmentId = $request->session()->get('active_department_id');
        
        $query = Document::with(['uploader', 'department']);

        if ($isPortal && $departmentId) {
            // Portal Context: Chỉ xem tài liệu của Ban ngành này
            $query->where('department_id', $departmentId);
            if (!$user->isSuperAdmin()) {
                $query->where(function($q) use ($user) {
                    $q->whereIn('visibility', ['public', 'internal', 'leadership'])
                      ->orWhere('uploaded_by', $user->id);
                });
            }
        } else {
            // Admin / Global Context
            if (!$user->isSuperAdmin()) {
                $query->whereIn('visibility', ['public', 'internal'])
                      ->orWhere('uploaded_by', $user->id);
            }
        }

        // Lọc category nếu có
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }
        
        // Search text
        if ($request->has('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $documents = $query->latest()->paginate(15)->through(fn($doc) => [
            'id' => $doc->id,
            'title' => $doc->title,
            'description' => $doc->description,
            'file_type' => $doc->file_type,
            'file_size' => $this->formatBytes($doc->file_size),
            'category' => $doc->category,
            'visibility' => $doc->visibility,
            'uploader' => $doc->uploader->name ?? '?',
            'department' => $doc->department->name ?? null,
            'download_url' => route('documents.download', $doc->id),
            'created_at' => $doc->created_at->format('d/m/Y H:i'),
            'can_delete' => $user->isSuperAdmin() || $doc->uploaded_by === $user->id,
        ]);

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'filters' => $request->only(['search', 'category']),
            'isPortal' => $isPortal,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used, modal based
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:20480', // 20MB max
            'category' => 'required|in:general,policy,meeting_minute,manual,form,other',
            'visibility' => 'required|in:public,internal,leadership,private',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('documents', $fileName, 'local'); // Storage/app/documents (Not public)

        $isPortal = $request->routeIs('portal.*') || $request->routeIs('ministry.*');
        $deptId = $isPortal ? $request->session()->get('active_department_id') : $request->department_id;

        Document::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'category' => $request->category,
            'visibility' => $request->visibility,
            'uploaded_by' => Auth::id(),
            'department_id' => $deptId,
        ]);

        return redirect()->back()->with('success', 'Tài liệu đã được đăng tải thành công.');
    }

    /**
     * Download the specified resource.
     */
    public function download(Document $document)
    {
        $user = Auth::user();
        
        // Kiểm tra quyền (Tương tự view)
        $canDownload = false;
        if ($user->isSuperAdmin() || $document->uploaded_by === $user->id) {
            $canDownload = true;
        } elseif ($document->visibility === 'public') {
            $canDownload = true;
        } elseif ($document->visibility === 'internal') {
            $canDownload = true; // Any logged-in user can download internal
        }
        
        if (!$canDownload) {
            abort(403, 'Bạn không có quyền tải tài liệu này.');
        }

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File không tồn tại trên máy chủ.');
        }

        return Storage::disk('local')->download($document->file_path, $document->title . '.' . $document->file_type);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        if (Auth::id() !== $document->uploaded_by && !Auth::user()->isSuperAdmin()) {
            abort(403, 'Bạn không có quyền xóa tài liệu này.');
        }

        // Tùy chọn: Xóa hẳn file cứng hoặc chỉ soft-deletes record (Hiện tại đang giữ file cứng để audit if needed)
        // Storage::disk('local')->delete($document->file_path);
        
        $document->delete();

        return redirect()->back()->with('success', 'Tài liệu đã được xóa.');
    }
    
    // Helper function
    private function formatBytes($bytes, $precision = 2) { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
      
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
      
        $bytes /= pow(1024, $pow);
      
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    } 
}
