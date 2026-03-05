<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EduSession;
use App\Models\EduSessionRecord;

class BackfillAttendanceCount extends Command
{
    protected $signature = 'edu:backfill-attendance';
    protected $description = 'Backfill total_present/total_absent on EduSession from EduSessionRecord rows';

    public function handle()
    {
        $sessions = EduSession::all();
        $count = 0;

        foreach ($sessions as $session) {
            $present = EduSessionRecord::where('edu_session_id', $session->id)
                ->where('attendance', 'present')
                ->count();
            $absent = EduSessionRecord::where('edu_session_id', $session->id)
                ->where('attendance', '!=', 'present')
                ->count();

            // Only update if there are records
            if ($present + $absent > 0) {
                $session->update([
                    'attendance_mode' => 'checkin',
                    'total_present'   => $present,
                    'total_absent'    => $absent,
                ]);
                $count++;
                $this->line("Session #{$session->id}: present={$present}, absent={$absent}");
            }
        }

        $this->info("Done! Updated {$count} sessions.");
        return 0;
    }
}
