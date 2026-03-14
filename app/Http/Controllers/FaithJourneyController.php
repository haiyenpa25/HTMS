<?php

namespace App\Http\Controllers;

use App\Models\FaithJourney;
use Illuminate\Http\Request;

class FaithJourneyController extends Controller
{
    /**
     * Store a newly created faith journey event.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'event_date' => 'required|date',
            'event_type' => 'required|string',
            'description' => 'nullable|string',
            'related_person_or_church' => 'nullable|string'
        ]);

        FaithJourney::create($validated);

        return back()->with('message', 'Đã thêm mốc sự kiện vào Hành trình.');
    }

    public function update(Request $request, FaithJourney $faithJourney)
    {
        $validated = $request->validate([
            'event_date' => 'required|date',
            'event_type' => 'required|string',
            'description' => 'nullable|string',
            'related_person_or_church' => 'nullable|string'
        ]);

        $faithJourney->update($validated);

        return back()->with('message', 'Đã cập nhật mốc sự kiện Hành trình.');
    }

    /**
     * Remove the specified faith journey event.
     */
    public function destroy(FaithJourney $faithJourney)
    {
        $faithJourney->delete();

        return back()->with('message', 'Đã xóa mốc sự kiện khỏi Hành trình.');
    }
}
