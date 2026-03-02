<?php

namespace App\Http\Controllers;

use App\Models\Speaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SpeakerController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Speaker::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('managed_church', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            if ($request->type === 'external') {
                $query->where('is_external', true);
            } elseif ($request->type === 'internal') {
                $query->where('is_external', false);
            }
        }

        $speakers = $query->orderBy('full_name')->paginate(15)->withQueryString();

        // Load Preaching history for visualization if needed, but we can do lazy loading
        // on the frontend or just send count. Here we send a basic collection.
        $speakers->getCollection()->transform(function ($speaker) {
            $speaker->preaching_count = $speaker->meetings()->count();
            return $speaker;
        });

        // If the user isn't allowed to see phones, mask them
        if (!$request->user()->can('viewPhone', Speaker::class)) {
            $speakers->getCollection()->transform(function ($speaker) {
                $speaker->phone = $speaker->phone ? '***-***-****' : null;
                return $speaker;
            });
        }

        return \Inertia\Inertia::render('Speakers/Index', [
            'speakers' => $speakers,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    /**
     * API Endpoint for Autocomplete/Select.
     */
    public function apiIndex(Request $request)
    {
        $query = Speaker::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
        }

        $speakers = $query->orderBy('full_name')->limit(20)->get(['id', 'title', 'full_name', 'phone', 'is_external']);
        
        return response()->json($speakers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Speaker::class);

        $validated = $request->validate([
            'title' => 'nullable|string|max:100',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'birth_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'managed_church' => 'nullable|string|max:255',
            'is_external' => 'boolean',
            'member_id' => 'nullable|exists:members,id'
        ]);

        Speaker::create($validated);

        return redirect()->back()->with('success', 'Đã thêm diễn giả thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Speaker $speaker)
    {
        Gate::authorize('view', $speaker);

        // For detailed view: return preaching history
        $speaker->load('member:id,full_name,gender');
        $history = $speaker->getPreachingHistory();

        return \Inertia\Inertia::render('Speakers/Show', [
            'speaker' => $speaker,
            'history' => $history
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Speaker $speaker)
    {
        Gate::authorize('update', $speaker);

        $validated = $request->validate([
            'title' => 'nullable|string|max:100',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'birth_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'managed_church' => 'nullable|string|max:255',
            'is_external' => 'boolean',
            'member_id' => 'nullable|exists:members,id'
        ]);

        $speaker->update($validated);

        return redirect()->back()->with('success', 'Đã cập nhật thông tin diễn giả.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Speaker $speaker)
    {
        Gate::authorize('delete', $speaker);

        $speaker->delete();
        return redirect()->route('speakers.index')->with('success', 'Đã xóa diễn giả.');
    }
}
