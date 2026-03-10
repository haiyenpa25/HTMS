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

                // Fetch default roles from the first duty roster template for this block/department
                $templateId = \App\Models\DutyRosterTemplate::where('block_type', $data['type'] === 'general' ? 'general' : ($data['type'] === 'department' ? \App\Models\Department::find($data['department_id'])?->block : 'activities'))
                                ->orderBy('id', 'asc') // Hoặc chọn default template
                                ->value('id');
                                
                if ($templateId) {
                    $templateRoles = \App\Models\DutyRosterTemplateRole::where('template_id', $templateId)->get();
                    $assignments = [];
                    foreach ($templateRoles as $role) {
                        $assignments[] = [
                            'meeting_id' => $meeting->id,
                            'role_id' => $role->role_id,
                            'user_id' => null, // Needs assignment by admin later, or auto-assign if possible
                            'role_name' => \App\Models\DutyRosterRole::find($role->role_id)?->name ?? 'Unknown',
                            'required_people' => $role->required_people,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    if (!empty($assignments)) {
                        \App\Models\MeetingAssignment::insert($assignments);
                    }
                }
            }

            DB::commit();
            return $createdMeetings;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
