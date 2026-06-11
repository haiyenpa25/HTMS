<template>
  <div class="report-wrapper">

    <!-- Toolbar (ẩn khi in) -->
    <div class="report-toolbar no-print">
      <div class="toolbar-left">
        <a :href="route('secretary.dashboard')" class="btn-back">← Dashboard</a>
        <div class="month-nav">
          <button @click="goMonth(-1)" class="nav-arrow">‹</button>
          <span class="month-label">Tháng {{ month }}/{{ year }}</span>
          <button @click="goMonth(1)" class="nav-arrow">›</button>
        </div>
      </div>
      <div class="toolbar-right">
        <button @click="printReport" class="btn-print">🖨 In Báo Cáo</button>
      </div>
    </div>

    <!-- Báo cáo chính (cả màn hình lẫn in) -->
    <div class="report-page" id="report-content">

      <!-- Tiêu đề -->
      <div class="report-title">
        BÁO CÁO CHƯƠNG TRÌNH SINH HOẠT HỘI THÁNH
      </div>
      <div class="report-meta">
        <div><span class="meta-label">Người báo cáo:</span> <span class="meta-val reporter-name">{{ reporter_name }}</span></div>
        <div class="month-badge">Tháng {{ month.toString().padStart(2,'0') }}.{{ year }}</div>
      </div>

      <!-- Bảng điểm danh + YouTube tổng hợp -->
      <div class="section-title">SỐ LƯỢNG NHÓM SÁNG CHỦ NHẬT</div>
      <div class="attendance-layout">

        <!-- Bảng điểm danh từng CN -->
        <table class="att-table">
          <thead>
            <tr>
              <th>Chủ Nhật</th>
              <th>Hiện diện</th>
              <th>Kênh YouTube</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in attendance_rows" :key="row.meeting_id" :class="{ 'row-no-data': !row.has_record }">
              <td class="td-date">{{ row.date_label }}</td>
              <td class="td-num">
                <span v-if="row.has_record">{{ row.total_present }}</span>
                <span v-else class="no-data">—</span>
              </td>
              <td class="td-num">
                <span v-if="row.youtube_live_count != null">{{ row.youtube_live_count }}</span>
                <span v-else class="no-data">—</span>
              </td>
            </tr>
            <!-- Trung bình -->
            <tr class="row-avg" v-if="month_summary.count > 0">
              <td class="td-label-avg">TRUNG BÌNH</td>
              <td class="td-num td-avg">{{ month_summary.avg }}</td>
              <td class="td-num td-avg">{{ month_summary.yt_live_avg ?? '—' }}</td>
            </tr>
            <!-- So với tháng trước -->
            <tr class="row-compare" v-if="month_summary.count > 0 && prev_summary.count > 0">
              <td class="td-label-compare" rowspan="2">So với tháng trước:</td>
              <td class="td-num">{{ prev_summary.avg }}</td>
              <td class="td-num">{{ prev_summary.yt_live_avg ?? '—' }}</td>
            </tr>
            <tr class="row-delta" v-if="month_summary.count > 0 && prev_summary.count > 0">
              <td class="td-num" :class="attendanceDelta >= 0 ? 'delta-up' : 'delta-down'">
                {{ attendanceDelta >= 0 ? 'Tăng' : 'Giảm' }} {{ Math.abs(attendanceDelta) }}
              </td>
              <td class="td-num" :class="ytLiveDelta >= 0 ? 'delta-up' : 'delta-down'">
                <template v-if="month_summary.yt_live_avg && prev_summary.yt_live_avg">
                  {{ ytLiveDelta >= 0 ? 'Tăng' : 'Giảm' }} {{ Math.abs(ytLiveDelta) }}
                </template>
                <span v-else class="no-data">—</span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- YouTube tổng hợp tháng -->
        <div class="yt-summary-box">
          <div class="yt-title">Về Kênh Truyền Thông Youtube</div>
          <table class="yt-table">
            <tbody>
              <tr>
                <td>Tổng số đăng ký hiện tại</td>
                <td class="yt-val">{{ formatNum(ytMetric('subscribers')) }}</td>
              </tr>
              <tr>
                <td>Tổng số đăng ký mới</td>
                <td class="yt-val">{{ ytMetric('new_subscribers') != null ? ytMetric('new_subscribers') : '—' }}</td>
              </tr>
              <tr>
                <td>Số giờ xem</td>
                <td class="yt-val">{{ ytMetric('watch_hours') != null ? ytMetric('watch_hours') + 'h' : '—' }}</td>
              </tr>
              <tr>
                <td>Số lượt xem trong 28 ngày</td>
                <td class="yt-val">{{ formatNum(ytMetric('views')) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Ghi nhận sự cố -->
      <div class="section-title section-purple">GHI NHẬN SỰ CỐ VÀ GIẢI QUYẾT</div>
      <table class="incident-table">
        <thead>
          <tr>
            <th class="th-incident-label">Chủ Nhật</th>
            <th v-for="row in attendance_rows" :key="row.meeting_id">{{ row.date_label }}</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="td-label-left"> </td>
            <td v-for="row in attendance_rows" :key="row.meeting_id" class="td-incident-cell">
              {{ row.incident_note || '' }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Thông báo + kế hoạch -->
      <div class="section-title section-purple">THÔNG BÁO - ĐỀ NGHỊ VÀ KẾ HOẠCH KHÁC</div>
      <div class="notes-block">
        <div v-if="month_note.announcements" class="notes-content">
          <div class="notes-row" v-for="(line, i) in announcementLines" :key="i">
            <span class="bullet">–</span> {{ line }}
          </div>
        </div>
        <div v-else class="notes-placeholder">(Chưa có thông báo)</div>
      </div>

      <div v-if="month_note.next_plan" class="notes-block next-plan-block">
        <div class="notes-plan-title">Kế hoạch tháng tới:</div>
        <div class="notes-content">
          <div class="notes-row" v-for="(line, i) in nextPlanLines" :key="i">
            {{ line }}
          </div>
        </div>
      </div>

      <!-- Chữ ký -->
      <div class="signature-row">
        <div class="sig-block">
          <div class="sig-label">Người Báo Cáo</div>
          <div class="sig-name">{{ reporter_name }}</div>
        </div>
        <div class="sig-block">
          <div class="sig-label">Ngày Báo Cáo</div>
          <div class="sig-name">{{ reportDate }}</div>
        </div>
      </div>
    </div>

    <!-- Tóm tắt nhanh (chỉ hiện trên màn hình, ẩn khi in) -->
    <div class="screen-summary no-print">
      <div class="sum-card" v-if="month_summary.count > 0">
        <div class="sum-icon">👥</div>
        <div>
          <div class="sum-val">{{ month_summary.avg }}</div>
          <div class="sum-lbl">TB Hiện Diện</div>
        </div>
        <div class="sum-delta" :class="attendanceDelta >= 0 ? 'delta-up' : 'delta-down'">
          {{ attendanceDelta >= 0 ? '▲' : '▼' }} {{ Math.abs(attendanceDelta) }}
        </div>
      </div>
      <div class="sum-card" v-if="month_summary.yt_live_avg">
        <div class="sum-icon">📺</div>
        <div>
          <div class="sum-val">{{ month_summary.yt_live_avg }}</div>
          <div class="sum-lbl">TB YouTube Live</div>
        </div>
      </div>
      <div class="sum-card">
        <div class="sum-icon">🗓</div>
        <div>
          <div class="sum-val">{{ attendance_rows.length }}</div>
          <div class="sum-lbl">Chủ Nhật trong tháng</div>
        </div>
      </div>
      <div class="sum-card" v-if="month_summary.max > 0">
        <div class="sum-icon">⬆️</div>
        <div>
          <div class="sum-val">{{ month_summary.max }}</div>
          <div class="sum-lbl">Cao nhất (CN{{ month_summary.max_date }})</div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps({
  month:           { type: Number, default: () => new Date().getMonth() + 1 },
  year:            { type: Number, default: () => new Date().getFullYear() },
  reporter_name:   { type: String, default: '' },
  attendance_rows: { type: Array,  default: () => [] },
  month_summary:   { type: Object, default: () => ({}) },
  prev_summary:    { type: Object, default: () => ({}) },
  yt_latest:       { type: Object, default: () => ({}) },
  yt_prev:         { type: Object, default: () => ({}) },
  yt_live_avg:     { type: Number, default: null },
  month_note:      { type: Object, default: () => ({}) },
  filters:         { type: Object, default: () => ({}) },
});

// ── Computed ──────────────────────────────────────────────────────

const attendanceDelta = computed(() => {
  const cur = props.month_summary?.avg || 0;
  const prv = props.prev_summary?.avg  || 0;
  return Math.round((cur - prv) * 10) / 10;
});

const ytLiveDelta = computed(() => {
  const cur = props.month_summary?.yt_live_avg || 0;
  const prv = props.prev_summary?.yt_live_avg  || 0;
  return Math.round((cur - prv) * 10) / 10;
});

const announcementLines = computed(() =>
  (props.month_note?.announcements || '').split('\n').filter(l => l.trim())
);

const nextPlanLines = computed(() =>
  (props.month_note?.next_plan || '').split('\n').filter(l => l.trim())
);

const reportDate = computed(() => {
  const now = new Date();
  return `${now.getDate().toString().padStart(2,'0')}/${(now.getMonth()+1).toString().padStart(2,'0')}/${now.getFullYear()}`;
});

// YouTube metric helper
function ytMetric(metricKey) {
  const found = Object.values(props.yt_latest).find(m => m.metric === metricKey);
  return found?.count ?? null;
}

function formatNum(val) {
  if (val == null) return '—';
  return Number(val).toLocaleString('vi-VN');
}

// Navigation
function goMonth(delta) {
  let m = props.month + delta;
  let y = props.year;
  if (m < 1)  { m = 12; y--; }
  if (m > 12) { m = 1;  y++; }
  router.get(route('secretary.report'), { month: m, year: y }, { preserveState: false });
}

function printReport() {
  window.print();
}
</script>

<style scoped>
/* ── Wrapper ─────────────────────────────────────────────── */
.report-wrapper {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px 16px;
  font-family: 'Arial', sans-serif;
}

/* ── Toolbar (màn hình) ──────────────────────────────────── */
.report-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 20px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 14px 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.toolbar-left  { display: flex; align-items: center; gap: 16px; }
.toolbar-right { display: flex; gap: 10px; }

.btn-back {
  padding: 8px 16px;
  background: #f3f4f6; border: 1px solid #e5e7eb;
  border-radius: 10px; text-decoration: none;
  color: #374151; font-size: 0.88rem; font-weight: 600;
  transition: background 0.15s;
}
.btn-back:hover { background: #e5e7eb; }

.month-nav { display: flex; align-items: center; gap: 12px; }
.month-label { font-size: 1rem; font-weight: 800; color: #1f2937; }
.nav-arrow {
  width: 32px; height: 32px; border-radius: 50%;
  background: #f3f4f6; border: 1px solid #e5e7eb;
  font-size: 1.1rem; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.15s;
}
.nav-arrow:hover { background: #e0e7ff; }

.btn-print {
  padding: 9px 20px;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: white; border: none; border-radius: 10px;
  font-size: 0.9rem; font-weight: 700; cursor: pointer;
  box-shadow: 0 4px 12px rgba(79,70,229,0.25);
  transition: opacity 0.15s;
}
.btn-print:hover { opacity: 0.9; }

/* ── Report Page ─────────────────────────────────────────── */
.report-page {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 32px 36px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.06);
}

.report-title {
  text-align: center;
  font-size: 1.15rem;
  font-weight: 900;
  color: #dc2626;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  margin-bottom: 10px;
}

.report-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  font-size: 0.9rem;
}
.meta-label { color: #6b7280; }
.meta-val   { font-weight: 700; color: #1f2937; }
.reporter-name { color: #e69138; }
.month-badge {
  font-size: 1rem; font-weight: 800; color: #34a853;
}

/* ── Section Title ───────────────────────────────────────── */
.section-title {
  background: #1f4e79;
  color: white;
  font-size: 0.85rem;
  font-weight: 800;
  padding: 8px 14px;
  margin: 20px 0 12px;
  text-align: center;
  border-radius: 4px;
}
.section-purple { background: #7030a0; }

/* ── Attendance Layout ───────────────────────────────────── */
.attendance-layout {
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 20px;
  align-items: start;
}
@media (max-width: 700px) { .attendance-layout { grid-template-columns: 1fr; } }

/* ── Attendance Table ────────────────────────────────────── */
.att-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.88rem;
}
.att-table th {
  background: #1f4e79;
  color: white;
  padding: 8px 12px;
  border: 1px solid #1f4e79;
  text-align: center;
  font-weight: 700;
}
.att-table td {
  border: 1px solid #000;
  padding: 7px 12px;
  font-size: 0.88rem;
}
.td-date { text-align: right; color: #000; font-weight: 500; }
.td-num  { text-align: center; font-weight: 600; color: #1f2937; }

.row-no-data td { color: #9ca3af; font-style: italic; }
.no-data { color: #9ca3af; }

.row-avg td { background: #f9cb9c; font-weight: 800; font-size: 0.9rem; }
.td-label-avg { text-align: left; font-weight: 800; }
.td-avg { color: #1f2937; }

.row-compare td, .row-delta td {
  font-size: 0.85rem;
}
.td-label-compare { font-weight: 700; font-size: 0.85rem; }
.delta-up   { color: #1565c0; font-weight: 800; }
.delta-down { background: #cc0000; color: white !important; font-weight: 800; }

/* ── YouTube Summary ─────────────────────────────────────── */
.yt-summary-box {
  border: 1px solid #1f4e79;
  border-radius: 6px;
  overflow: hidden;
}
.yt-title {
  background: #1f4e79;
  color: white;
  text-align: center;
  font-size: 0.8rem;
  font-weight: 700;
  padding: 7px;
}
.yt-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.yt-table td {
  padding: 7px 10px;
  border-bottom: 1px solid #e5e7eb;
  color: #1f2937;
}
.yt-table tr:last-child td { border-bottom: none; }
.yt-val { text-align: right; font-weight: 800; color: #1f4e79; }

/* ── Incident Table ──────────────────────────────────────── */
.incident-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.82rem;
  margin-bottom: 4px;
}
.incident-table th {
  background: #1f4e79;
  color: white;
  padding: 7px 8px;
  border: 1px solid #1f4e79;
  text-align: center;
  font-size: 0.8rem;
}
.th-incident-label { background: #7030a0; }
.td-label-left { background: #7030a0; color: white; width: 80px; vertical-align: top; }
.td-incident-cell {
  border: 1px solid #000;
  padding: 6px 8px;
  min-height: 50px;
  vertical-align: top;
  font-size: 0.8rem;
  color: #374151;
}

/* ── Notes Block ─────────────────────────────────────────── */
.notes-block {
  border: 1px solid #000;
  padding: 10px 14px;
  min-height: 36px;
  font-size: 0.88rem;
  color: #1f2937;
  margin-bottom: 6px;
}
.notes-row { margin-bottom: 4px; }
.bullet { margin-right: 4px; }
.notes-placeholder { color: #9ca3af; font-style: italic; }
.next-plan-block { border-top: none; }
.notes-plan-title { font-size: 0.8rem; color: #6b7280; margin-bottom: 6px; font-weight: 600; }

/* ── Signature ───────────────────────────────────────────── */
.signature-row {
  display: flex;
  justify-content: space-between;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e5e7eb;
}
.sig-block { text-align: center; }
.sig-label { font-size: 0.78rem; color: #6b7280; font-weight: 600; margin-bottom: 30px; }
.sig-name  { font-size: 0.9rem; font-weight: 700; color: #1f2937; border-top: 1px solid #000; padding-top: 4px; }

/* ── Screen Summary Cards ────────────────────────────────── */
.screen-summary {
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  margin-top: 20px;
}
.sum-card {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  flex: 1;
  min-width: 160px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.sum-icon { font-size: 1.6rem; }
.sum-val  { font-size: 1.6rem; font-weight: 900; color: #1f2937; line-height: 1; }
.sum-lbl  { font-size: 0.72rem; color: #6b7280; margin-top: 2px; }
.sum-delta { font-size: 0.85rem; font-weight: 700; margin-left: auto; }

/* ── Print Styles ────────────────────────────────────────── */
@media print {
  .no-print { display: none !important; }
  body { margin: 0; padding: 0; }
  .report-wrapper { max-width: 100%; padding: 0; }
  .report-page {
    border: none;
    box-shadow: none;
    border-radius: 0;
    padding: 10mm 15mm;
  }
  .report-title { font-size: 13pt; }
  .att-table, .incident-table, .yt-table { font-size: 10pt; }
  .section-title { font-size: 10pt; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .att-table th  { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .row-avg td    { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .delta-down    { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .th-incident-label { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .td-label-left { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .yt-title      { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
