<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillMemoryVerseCount extends Command
{
    protected $signature   = 'backfill:memory-verse-count';
    protected $description = 'Backfill memory_verse_count in meeting_attendance_summaries from existing meeting_attendances data';

    public function handle(): int
    {
        $this->info('Backfilling memory_verse_count from meeting_attendances...');

        // For each summary, count how many present attendees have memorized_verse = true
        $summaries = DB::table('meeting_attendance_summaries')->get();

        $updated = 0;
        foreach ($summaries as $summary) {
            $count = DB::table('meeting_attendances')
                ->where('meeting_id', $summary->meeting_id)
                ->where('status', 'present')
                ->where('memorized_verse', 1)
                ->count();

            if ($count !== (int) $summary->memory_verse_count) {
                DB::table('meeting_attendance_summaries')
                    ->where('id', $summary->id)
                    ->update(['memory_verse_count' => $count]);
                $updated++;
            }
        }

        $this->info("Done. Updated {$updated} / {$summaries->count()} summaries.");
        return 0;
    }
}
