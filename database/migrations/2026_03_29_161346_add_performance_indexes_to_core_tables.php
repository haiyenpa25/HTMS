<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function createIndexIfNotExists(string $table, string $index, array $columns): void
    {
        $exists = DB::select(
            "SELECT COUNT(*) AS cnt FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND INDEX_NAME = ?",
            [$table, $index]
        )[0]->cnt > 0;

        if (!$exists) {
            $cols = implode('`, `', $columns);
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$index}` (`{$cols}`)");
        }
    }

    public function up(): void
    {
        // meetings: date+type and department_id+date
        $this->createIndexIfNotExists('meetings', 'meetings_date_type_index', ['date', 'type']);
        $this->createIndexIfNotExists('meetings', 'meetings_department_id_date_index', ['department_id', 'date']);

        // meeting_attendance_summaries: department_id
        $this->createIndexIfNotExists('meeting_attendance_summaries', 'mas_department_id_index', ['department_id']);

        // meeting_attendances: status and memorized_verse
        $this->createIndexIfNotExists('meeting_attendances', 'ma_meeting_status_index', ['meeting_id', 'status']);
        $this->createIndexIfNotExists('meeting_attendances', 'ma_memorized_verse_index', ['meeting_id', 'memorized_verse']);

        // members: status
        $this->createIndexIfNotExists('members', 'members_status_index', ['status']);

        // finance_transactions: fund_id+type (không có meeting_id)
        $this->createIndexIfNotExists('finance_transactions', 'ft_fund_type_index', ['fund_id', 'type']);
    }

    public function down(): void
    {
        $drops = [
            'meetings' => ['meetings_date_type_index', 'meetings_department_id_date_index'],
            'meeting_attendance_summaries' => ['mas_department_id_index'],
            'meeting_attendances' => ['ma_meeting_status_index', 'ma_memorized_verse_index'],
            'members' => ['members_status_index'],
            'finance_transactions' => ['ft_fund_type_index'],
        ];

        foreach ($drops as $table => $indexes) {
            foreach ($indexes as $index) {
                $exists = DB::select(
                    "SELECT COUNT(*) AS cnt FROM information_schema.STATISTICS
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
                    [$table, $index]
                )[0]->cnt > 0;

                if ($exists) {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
                }
            }
        }
    }
};
