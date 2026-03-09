<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\MeetingAttendanceSummary;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AttendanceImport implements ToArray, WithStartRow
{
    public int $importedCount = 0;
    public int $skippedCount  = 0;
    public array $errors      = [];

    protected int $meetingId;
    protected int $departmentId;

    public function __construct(int $meetingId, int $departmentId)
    {
        $this->meetingId    = $meetingId;
        $this->departmentId = $departmentId;
    }

    // Data starts at row 7 (rows 1-5 = info, row 6 = header)
    public function startRow(): int
    {
        return 7;
    }

    public function array(array $rows): void
    {
        $meeting = Meeting::findOrFail($this->meetingId);
        $presentCount = 0;

        foreach ($rows as $index => $row) {
            // Skip completely empty rows
            if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $memberId     = isset($row[0]) ? intval($row[0]) : null;
            $hdValue      = isset($row[3]) ? strtolower(trim((string)$row[3])) : '';
            $cgValue      = isset($row[4]) ? strtolower(trim((string)$row[4])) : '';

            if (!$memberId) {
                $this->errors[] = "Hàng " . ($index + 7) . ": Không có member_id.";
                $this->skippedCount++;
                continue;
            }

            // Validate member exists
            $member = Member::find($memberId);
            if (!$member) {
                $this->errors[] = "Hàng " . ($index + 7) . ": Không tìm thấy thành viên ID #{$memberId}.";
                $this->skippedCount++;
                continue;
            }

            $isPresent      = in_array($hdValue, ['x', '1', 'có', 'yes', 'true', 'p']);
            $memorizedVerse = in_array($cgValue, ['x', '1', 'có', 'yes', 'true']);

            // Upsert attendance record
            MeetingAttendance::updateOrCreate(
                [
                    'meeting_id' => $this->meetingId,
                    'member_id'  => $memberId,
                ],
                [
                    'status'          => $isPresent ? 'present' : 'absent',
                    'memorized_verse' => $memorizedVerse,
                ]
            );

            if ($isPresent) {
                $presentCount++;
            }
            $this->importedCount++;
        }

        // Update or create attendance summary
        MeetingAttendanceSummary::updateOrCreate(
            [
                'meeting_id'    => $this->meetingId,
                'department_id' => $this->departmentId,
            ],
            [
                'manual_count' => $presentCount,
            ]
        );
    }
}
