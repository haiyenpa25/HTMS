<?php

namespace App\Exports;

use App\Models\Meeting;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;

class MeetingExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    protected Meeting $meeting;

    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting->load(['dutyAssignments.role.department', 'dutyAssignments.member']);
    }

    public function title(): string
    {
        return substr($this->meeting->topic ?? 'Phân Công', 0, 31);
    }

    public function headings(): array
    {
        return ['STT', 'Ban / Phần', 'Vị trí', 'Suất', 'Người phụ trách', 'Trạng thái'];
    }

    public function collection(): Collection
    {
        $rows = collect();
        $stt  = 1;

        // Group assignments by dept and sort: Section I first
        $assignments = $this->meeting->duty_assignments ?? collect();

        // Sort: Chương Trình Lễ first
        $sorted = collect($assignments)->sortBy(function ($a) {
            $section = $a->role->section ?? '';
            return $section === 'Chương Trình Lễ' ? 0 : 1;
        });

        foreach ($sorted as $asgn) {
            $role    = $asgn->role;
            $dept    = $role->department;
            $section = $role->section ?? $dept?->name ?? '—';
            $person  = $asgn->member?->full_name ?? '(Chưa phân)';
            $status  = $asgn->member_id ? '✓ Đã phân' : '✗ Chưa phân';

            $rows->push([
                $stt++,
                $section,
                $role->name,
                $asgn->slot ?? 1,
                $person,
                $status,
            ]);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row bold + indigo background
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
