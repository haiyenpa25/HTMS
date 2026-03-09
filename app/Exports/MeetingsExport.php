<?php

namespace App\Exports;

use App\Models\Meeting;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MeetingsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Meeting::with('department')
            ->when(!empty($this->filters['type']), fn($q) => $q->where('type', $this->filters['type']))
            ->when(!empty($this->filters['date_from']), fn($q) => $q->whereDate('date', '>=', $this->filters['date_from']))
            ->when(!empty($this->filters['date_to']), fn($q) => $q->whereDate('date', '<=', $this->filters['date_to']))
            ->orderBy('date')
            ->orderBy('time');

        return $query->get()->map(function ($meeting) {
            return [
                'id'           => $meeting->id,
                'type'         => match($meeting->type) {
                    'church'     => 'Hội Thánh',
                    'department' => 'Ban Ngành',
                    'holiday'    => 'Lễ Đặc Biệt',
                    default      => $meeting->type,
                },
                'department'   => $meeting->department?->name ?? '(Hội Thánh)',
                'date'         => $meeting->date,
                'time'         => $meeting->time,
                'topic'        => $meeting->topic ?? '',
                'memory_verse' => $meeting->memory_verse ?? '',
                'quiz_passage' => $meeting->quiz_passage ?? '',
                'scripture'    => $meeting->scripture ?? '',
                'preacher'     => $meeting->preacher ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID Buổi Nhóm',
            'Loại',
            'Ban Ngành',
            'Ngày (YYYY-MM-DD)',
            'Giờ',
            'Chủ Đề',
            'Câu Gốc',
            'Phân Đoạn Đố KT',
            'Phân Đoạn',
            'Giảng Viên',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header row style
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Freeze header row
        $sheet->freezePane('A2');

        // Auto-filter
        $sheet->setAutoFilter('A1:J1');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 14,
            'C' => 22,
            'D' => 18,
            'E' => 10,
            'F' => 30,
            'G' => 25,
            'H' => 25,
            'I' => 25,
            'J' => 20,
        ];
    }

    public function title(): string
    {
        return 'Danh Sách Buổi Nhóm';
    }
}
