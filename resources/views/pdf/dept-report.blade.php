<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1a1a1a; background: #fff; }
    .page { padding: 30px 40px; }

    /* Header */
    .header { text-align: center; border-bottom: 3px solid #059669; padding-bottom: 16px; margin-bottom: 20px; }
    .header .church-name { font-size: 14px; font-weight: bold; text-transform: uppercase; color: #065f46; letter-spacing: 1px; }
    .header .report-title { font-size: 20px; font-weight: bold; color: #111; margin-top: 6px; }
    .header .meta { font-size: 11px; color: #555; margin-top: 4px; }

    /* Section */
    .section-title { background: #ecfdf5; border-left: 4px solid #059669; padding: 6px 12px; font-size: 12px; font-weight: bold; text-transform: uppercase; color: #065f46; margin: 16px 0 10px; }

    /* Table */
    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th { background: #059669; color: white; padding: 7px 8px; text-align: center; font-weight: bold; font-size: 10px; }
    td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: center; vertical-align: middle; }
    tr:nth-child(even) td { background: #f9fafb; }
    .td-left { text-align: left; }
    .td-num { font-weight: bold; }

    /* Summary */
    .summary-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 14px 18px; margin-top: 18px; }
    .summary-box .sum-row { display: flex; justify-content: space-between; padding: 3px 0; font-size: 11px; }
    .summary-box .sum-label { color: #374151; }
    .summary-box .sum-val { font-weight: bold; color: #059669; }

    /* Signature */
    .signature { margin-top: 40px; display: flex; justify-content: space-between; }
    .sig-box { text-align: center; width: 45%; }
    .sig-box .sig-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #374151; }
    .sig-box .sig-line { border-top: 1px solid #9ca3af; margin-top: 50px; padding-top: 6px; font-size: 10px; color: #6b7280; }

    /* Footer */
    .footer { text-align: center; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px; font-size: 9px; color: #9ca3af; }

    .badge-verse { background: #fffbeb; border: 1px solid #fcd34d; padding: 1px 6px; border-radius: 4px; font-size: 10px; color: #92400e; }
</style>
</head>
<body>
<div class="page">

    <!-- Header -->
    <div class="header">
        <div class="church-name">Hội Thánh Tin Lành Thanh Mỹ Lợi</div>
        <div class="report-title">BÁO CÁO SINH HOẠT BAN NGÀNH</div>
        <div class="meta">
            Ban: <strong>{{ $department->name }}</strong> &nbsp;|&nbsp;
            Kỳ báo cáo: <strong>Tháng {{ $month }}/{{ $year }}</strong>
            @if($generatedAt)
            &nbsp;|&nbsp; Xuất lúc: {{ $generatedAt }}
            @endif
        </div>
    </div>

    <!-- Section A: Hoi Thanh Chung -->
    @if(count($churchMeetings) > 0)
    <div class="section-title">A. BUỔI NHÓM HỘI THÁNH CHUNG</div>
    <table>
        <thead>
            <tr>
                <th style="width:8%">Tuần</th>
                <th style="width:12%">Ngày</th>
                <th style="width:30%">Chủ đề / Diễn giả</th>
                <th style="width:20%">Câu gốc</th>
                <th style="width:10%">Tham dự</th>
                <th style="width:10%">Câu gốc</th>
                <th style="width:10%">Thu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($churchMeetings as $row)
            <tr>
                <td>W{{ $row['week_no'] }}</td>
                <td>{{ $row['date'] }}</td>
                <td class="td-left">
                    {{ $row['topic'] ?: '—' }}
                    @if($row['speaker']) <br><span style="color:#6b7280;font-size:10px">{{ $row['speaker'] }}</span>@endif
                </td>
                <td class="td-left" style="font-size:10px;font-style:italic">{{ $row['memory_verse'] ?: '—' }}</td>
                <td class="td-num">{{ $row['attendance'] ?: '—' }}</td>
                <td class="td-num">{{ $row['memory_verse_count'] ?: '—' }}</td>
                <td class="td-num">{{ $row['income'] ? number_format($row['income']) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="summary-box" style="margin-top:10px;">
        <div class="sum-row"><span class="sum-label">Số buổi:</span><span class="sum-val">{{ count($churchMeetings) }}</span></div>
        <div class="sum-row"><span class="sum-label">TB Tham dự:</span><span class="sum-val">{{ $avgChurch }} người/buổi</span></div>
        <div class="sum-row"><span class="sum-label">TB Thuộc câu gốc:</span><span class="sum-val">{{ $avgVerseChurch }} người/buổi</span></div>
        <div class="sum-row"><span class="sum-label">Tổng thu:</span><span class="sum-val">{{ number_format($totalIncomeChurch) }} đ</span></div>
    </div>
    @endif

    <!-- Section B: Ban Nganh -->
    @if(count($deptMeetings) > 0)
    <div class="section-title" style="margin-top:22px;">B. BUỔI NHÓM BAN NGÀNH</div>
    <table>
        <thead>
            <tr>
                <th style="width:8%">Tuần</th>
                <th style="width:12%">Ngày</th>
                <th style="width:30%">Chủ đề / Diễn giả</th>
                <th style="width:20%">Câu gốc</th>
                <th style="width:10%">Tham dự</th>
                <th style="width:10%">Câu gốc</th>
                <th style="width:10%">Thu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deptMeetings as $row)
            <tr>
                <td>W{{ $row['week_no'] }}</td>
                <td>{{ $row['date'] }}</td>
                <td class="td-left">
                    {{ $row['topic'] ?: '—' }}
                    @if($row['speaker']) <br><span style="color:#6b7280;font-size:10px">{{ $row['speaker'] }}</span>@endif
                </td>
                <td class="td-left" style="font-size:10px;font-style:italic">{{ $row['memory_verse'] ?: '—' }}</td>
                <td class="td-num">{{ $row['attendance'] ?: '—' }}</td>
                <td class="td-num">{{ $row['memory_verse_count'] ?: '—' }}</td>
                <td class="td-num">{{ $row['income'] ? number_format($row['income']) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="summary-box" style="margin-top:10px;">
        <div class="sum-row"><span class="sum-label">Số buổi:</span><span class="sum-val">{{ count($deptMeetings) }}</span></div>
        <div class="sum-row"><span class="sum-label">TB Tham dự:</span><span class="sum-val">{{ $avgDept }} người/buổi</span></div>
        <div class="sum-row"><span class="sum-label">TB Thuộc câu gốc:</span><span class="sum-val">{{ $avgVerseDept }} người/buổi</span></div>
        <div class="sum-row"><span class="sum-label">Tổng thu:</span><span class="sum-val">{{ number_format($totalIncomeDept) }} đ</span></div>
    </div>
    @endif

    <!-- Signature -->
    <div class="signature">
        <div class="sig-box">
            <div class="sig-title">Ban Trưởng Sinh Hoạt</div>
            <div class="sig-line">(Ký và ghi rõ họ tên)</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">Thư Ký Ban Chấp Sự</div>
            <div class="sig-line">(Ký và ghi rõ họ tên)</div>
        </div>
    </div>

    <div class="footer">Báo cáo được tạo tự động bởi Hệ thống Quản lý Hội thánh HTMS &nbsp;·&nbsp; {{ $generatedAt }}</div>
</div>
</body>
</html>
