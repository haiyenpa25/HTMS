<?php

namespace App\Imports;

use App\Models\Meeting;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithStartRow;

class MeetingsImport implements ToArray, WithStartRow
{
    public int   $updatedCount = 0;
    public int   $skippedCount = 0;
    public array $errors       = [];

    // Data starts at row 2 (row 1 = heading)
    public function startRow(): int
    {
        return 2;
    }

    public function array(array $rows): void
    {
        foreach ($rows as $index => $row) {
            // Skip empty rows
            if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                continue;
            }

            // Columns (0-based):
            // 0: id, 1: type(vi), 2: department, 3: date, 4: time,
            // 5: topic, 6: memory_verse, 7: quiz_passage, 8: scripture, 9: preacher

            $id = isset($row[0]) ? intval($row[0]) : null;

            if (!$id) {
                $rowNum = $index + 2;
                $this->errors[] = "Hàng {$rowNum}: Bỏ qua — không có ID.";
                $this->skippedCount++;
                continue;
            }

            $meeting = Meeting::find($id);

            if (!$meeting) {
                $this->errors[] = "Hàng " . ($index + 2) . ": Không tìm thấy buổi nhóm ID #{$id}.";
                $this->skippedCount++;
                continue;
            }

            // Only update non-empty cells so you can leave a column blank to skip it
            $updates = [];

            $this->maybeUpdate($updates, 'topic',        $row[5] ?? null);
            $this->maybeUpdate($updates, 'memory_verse', $row[6] ?? null);
            $this->maybeUpdate($updates, 'quiz_passage', $row[7] ?? null);
            $this->maybeUpdate($updates, 'scripture',    $row[8] ?? null);
            $this->maybeUpdate($updates, 'preacher',     $row[9] ?? null);

            // Optional: update date/time if provided (and valid)
            if (!empty($row[3])) {
                $date = \Carbon\Carbon::parse($row[3]);
                if ($date) $updates['date'] = $date->format('Y-m-d');
            }
            if (!empty($row[4])) {
                $updates['time'] = $row[4];
            }

            if (!empty($updates)) {
                $meeting->update($updates);
                $this->updatedCount++;
            } else {
                $this->skippedCount++;
            }
        }
    }

    private function maybeUpdate(array &$updates, string $field, $value): void
    {
        // Accept the value if it's explicitly set (even empty string clears it)
        // Use a special marker: if cell contains '-' → clear the field
        if ($value === null) return;
        $v = trim((string)$value);
        $updates[$field] = ($v === '-') ? null : ($v === '' ? null : $v);
    }
}
