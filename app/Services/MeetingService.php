<?php

namespace App\Services;

use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class MeetingService
{
    /**
     * Create a single meeting or multiple bulk meetings
     */
    public function createMeetings(array $data, int $weeksToGenerate = 1)
    {
        $createdMeetings = [];
        $startDate = Carbon::parse($data['date']);

        DB::beginTransaction();

        try {
            for ($i = 0; $i < $weeksToGenerate; $i++) {
                $currentDate = $startDate->copy()->addWeeks($i)->format('Y-m-d');
                
                // Prevent duplicate meeting for same unit, same date and time
                $exists = Meeting::where('type', $data['type'])
                    ->when($data['type'] === 'department', function($q) use ($data) {
                        return $q->where('department_id', $data['department_id']);
                    })
                    ->where('date', $currentDate)
                    ->where('time', $data['time'])
                    ->exists();

                if ($exists) {
                    throw new Exception("Lịch nhóm ngày {$currentDate} lúc {$data['time']} đã bị trùng lặp. Vui lòng kiểm tra lại.");
                }

                $meetingData = array_merge($data, ['date' => $currentDate]);
                $meeting = Meeting::create($meetingData);
                
                $createdMeetings[] = $meeting;

                // Note: Automatic duty roster template application has been disabled.
                // Users can manually apply a template to the meeting later from the Duty Roster interface.
            }

            DB::commit();
            return $createdMeetings;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
