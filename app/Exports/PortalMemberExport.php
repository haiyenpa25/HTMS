<?php

namespace App\Exports;

use App\Models\Member;
use App\Models\Department;
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

class PortalMemberExport implements FromArray, WithStyles, WithTitle, WithEvents
{
    protected $departmentId;
    protected $members;

    public function __construct(int $departmentId)
    {
        $this->departmentId = $departmentId;

        // Get members of this department
        $memberIds = OrgMembership::where('model_type', 'App\\Models\\Department')
            ->where('model_id', $departmentId)
            ->where('is_active', true)
            ->pluck('member_id');

        $this->members = Member::whereIn('id', $memberIds)
            ->whereNull('deleted_at')
            ->orderBy('full_name')
            ->get();
    }

    public function array(): array
    {
        $department = Department::find($this->departmentId);
        $deptName = $department ? $department->name : 'Ban Ngành';

        // Info rows
        $rows = [
            ['DANH SÁCH THÀNH VIÊN'],
            ['Ban Ngành:', $deptName],
            ['Ngày Xuất:', now()->format('d/m/Y H:i')],
            [], // blank row
            // Header
            ['ID Thành Viên', 'Họ Tên', 'Số Điện Thoại', 'Email', 'Giới Tính (Nam/Nữ)', 'Ngày Sinh (DD/MM/YYYY)', 'Tổ', 'Chức Danh'],
        ];

        // Member rows
        foreach ($this->members as $member) {
             // Get team name
             $teamMembership = OrgMembership::where('member_id', $member->id)
                 ->where('model_type', 'App\\Models\\Team')
                 ->where('is_active', true)
                 ->with('model')
                 ->first();
             $teamName = $teamMembership?->model?->name ?? '';

             // Get Role
             $deptMembership = OrgMembership::where('member_id', $member->id)
                 ->where('model_type', 'App\\Models\\Department')
                 ->where('model_id', $this->departmentId)
                 ->where('is_active', true)
                 ->with('role')
                 ->first();
             $roleCode = $deptMembership?->role?->code ?? '';
             
             // Map role to friendly name
             $map = [
                'tb' => 'TruongBan', 'pb' => 'PhoBan', 'tk' => 'ThuKy',
                'tq' => 'ThuQuy', 'uv' => 'UyVien', 'bv' => 'Member'
             ];
             $roleName = $map[$roleCode] ?? 'Member';

            $rows[] = [
                $member->id,
                $member->full_name,
                $member->phone,
                $member->email,
                $member->gender === 'male' ? 'Nam' : 'Nữ',
                $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->format('d/m/Y') : '',
                $teamName,
                $roleName,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        // Title
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Info rows (2-3)
        $sheet->getStyle('A2:H3')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2FF']],
            'font' => ['size' => 10],
        ]);
        $sheet->getStyle('A2:A3')->applyFromArray(['font' => ['bold' => true]]);

        // Header row (row 5)
        $sheet->getStyle('A5:H5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '047857']]],
        ]);

        // Data rows
        $lastRow = 5 + count($this->members);
        if ($lastRow > 5) {
            $sheet->getStyle("A6:H{$lastRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
            ]);

            // Alternate rows
            for ($r = 6; $r <= $lastRow; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:H{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']],
                    ]);
                }
            }
        }

        $sheet->freezePane('A6');

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(30);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(20);
                $sheet->getColumnDimension('H')->setWidth(20);
            },
        ];
    }

    public function title(): string
    {
        return 'Thành Viên';
    }
}
