<?php

namespace App\Exports;

use App\Models\Meeting;
use App\Models\Member;
use App\Models\MeetingAttendance;
use App\Models\OrgMembership;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceTemplateExport implements FromArray, WithStyles, WithTitle, WithEvents
{
    protected $meeting;
    protected $members;
    protected $existingAttendances;

    public function __construct(Meeting $meeting, int $departmentId)
    {
        $this->meeting = $meeting;

        // Get members of this department
        $memberIds = OrgMembership::where('model_type', 'App\\Models\\Department')
            ->where('model_id', $departmentId)
            ->where('is_active', true)
            ->pluck('member_id');

        $members = Member::whereIn('id', $memberIds)
            ->whereNull('deleted_at')
            ->get();

        $this->members = $members->sortBy(function($member) {
            $parts = explode(' ', trim($member->full_name));
            return end($parts) . ' ' . $member->full_name;
        })->values();

        // Load existing attendances for this meeting
        $this->existingAttendances = MeetingAttendance::where('meeting_id', $meeting->id)
            ->whereIn('member_id', $memberIds)
            ->get()
            ->keyBy('member_id');
    }

    public function array(): array
    {
        $meetingDate = $this->meeting->date instanceof \Carbon\Carbon
            ? $this->meeting->date->format('d/m/Y')
            : $this->meeting->date;

        $meetingType = match($this->meeting->type) {
            'church'     => 'Hội Thánh',
            'department' => 'Ban Ngành',
            'holiday'    => 'Lễ Đặc Biệt',
            default      => $this->meeting->type,
        };

        // Info rows
        $rows = [
            ['TEMPLATE ĐIỂM DANH BUỔI NHÓM'],
            ['ID Buổi Nhóm:', $this->meeting->id, '', 'Loại:', $meetingType],
            ['Ngày:', $meetingDate, '', 'Chủ Đề:', $this->meeting->topic ?? ''],
            ['Câu Gốc:', $this->meeting->memory_verse ?? '', '', 'Phân Đoạn:', $this->meeting->scripture ?? ''],
            [], // blank row
            // Header
            ['member_id', 'Họ Tên', 'Tổ', 'Hiện Diện (x=có, trống=vắng)', 'Câu Gốc (x=thuộc)', 'Ghi Chú'],
        ];

        // Member rows
        foreach ($this->members as $member) {
            $attendance = $this->existingAttendances->get($member->id);

            // Get team name
            $teamMembership = OrgMembership::where('member_id', $member->id)
                ->where('model_type', 'App\\Models\\Team')
                ->where('is_active', true)
                ->with('model')
                ->first();
            $teamName = $teamMembership?->model?->name ?? '';

            $rows[] = [
                $member->id,
                $member->full_name,
                $teamName,
                $attendance && $attendance->status === 'present' ? 'x' : '',
                $attendance && $attendance->memorized_verse ? 'x' : '',
                '', // notes
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Title
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Info rows (2-4)
        $sheet->getStyle('A2:F4')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2FF']],
            'font' => ['size' => 10],
        ]);
        $sheet->getStyle('A2:A4')->applyFromArray([
            'font' => ['bold' => true],
        ]);
        $sheet->getStyle('D2:D4')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        // Header row (row 6)
        $sheet->getStyle('A6:F6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '047857']]],
        ]);

        // Data rows
        $lastRow = 6 + count($this->members);
        if ($lastRow > 6) {
            $sheet->getStyle("A7:F{$lastRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
            ]);

            // Alternate rows
            for ($r = 7; $r <= $lastRow; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:F{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                    ]);
                }
            }
        }

        $sheet->freezePane('A7');

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(28);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(28);
                $sheet->getColumnDimension('E')->setWidth(22);
                $sheet->getColumnDimension('F')->setWidth(22);
            },
        ];
    }

    public function title(): string
    {
        return 'Điểm Danh';
    }
}
