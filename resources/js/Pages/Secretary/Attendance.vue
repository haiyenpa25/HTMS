<template>
  <div class="sec-attendance">

    <!-- Header -->
    <div class="sec-header">
      <div class="sec-header-icon">📝</div>
      <div class="sec-header-text">
        <h1>Điểm Danh Chúa Nhật</h1>
        <p>Ghi nhận và đối chiếu số liệu Thư Ký & Ban Ngành</p>
      </div>
      <div class="sec-header-nav">
        <Link :href="route('secretary.dashboard')" class="btn-nav">← Dashboard</Link>
        <Link :href="route('secretary.social-stats.index')" class="btn-nav">📡 MXH</Link>
      </div>
    </div>

    <!-- Month Summary Bar -->
    <div v-if="month_summary.count > 0" class="month-bar">
      <div class="month-bar-title">
        📅 Tháng {{ month_summary.month }}/{{ month_summary.year }}
      </div>
      <div class="month-stats">
        <div class="ms-item">
          <span class="ms-val">{{ month_summary.avg }}</span>
          <span class="ms-lbl">TB hiện diện</span>
        </div>
        <div class="ms-item" :class="avgDelta >= 0 ? 'ms-up' : 'ms-down'">
          <span class="ms-val">{{ avgDelta >= 0 ? '▲' : '▼' }} {{ Math.abs(avgDelta) }}</span>
          <span class="ms-lbl">vs tháng trước</span>
        </div>
        <div class="ms-item ms-sep">
          <span class="ms-val">{{ month_summary.max }}</span>
          <span class="ms-lbl">Cao nhất (CN{{ month_summary.max_date }})</span>
        </div>
        <div class="ms-item">
          <span class="ms-val">{{ month_summary.min }}</span>
          <span class="ms-lbl">Thấp nhất (CN{{ month_summary.min_date }})</span>
        </div>
        <div class="ms-item ms-sep">
          <span class="ms-val">{{ month_summary.count }}</span>
          <span class="ms-lbl">Buổi đã ghi</span>
        </div>
      </div>
    </div>

    <!-- Main Grid: Form + Chart -->
    <div class="att-grid">

      <!-- LEFT: Form nhập liệu -->
      <div class="att-form-panel">
        <h2 class="panel-title">📋 Nhập Điểm Danh</h2>

        <form @submit.prevent="submitForm" class="att-form">
          <!-- Chọn buổi nhóm -->
          <div class="form-group">
            <label class="form-label">Buổi Nhóm *</label>
            <select v-model="form.meeting_id" class="form-select" required>
              <option value="">— Chọn buổi nhóm —</option>
              <option v-for="m in meetings" :key="m.id" :value="m.id">{{ m.label }}</option>
            </select>
          </div>

          <!-- 2 khối song song -->
          <div class="dual-grid">

            <!-- Khối trái: Thư Ký đếm -->
            <div class="dual-block block-tk">
              <div class="block-header">
                <span class="block-icon">📌</span>
                <span class="block-title">Thư Ký Đếm</span>
              </div>

              <div class="form-group">
                <label class="form-label">Tổng Hiện Diện *</label>
                <input
                  type="number" v-model.number="form.total_present"
                  class="form-input form-input-lg tk-total" min="0" placeholder="0" required
                >
              </div>

              <div class="form-row-2">
                <div class="form-group">
                  <label class="form-label">♂ Nam</label>
                  <input type="number" v-model.number="form.total_male" class="form-input" min="0" placeholder="0">
                </div>
                <div class="form-group">
                  <label class="form-label">♀ Nữ</label>
                  <input type="number" v-model.number="form.total_female" class="form-input" min="0" placeholder="0">
                </div>
                <div class="form-group">
                  <label class="form-label">🧒 Thiếu Nhi</label>
                  <input type="number" v-model.number="form.total_children" class="form-input" min="0" placeholder="0">
                </div>
                <div class="form-group">
                  <label class="form-label">🙋 Thân Hữu</label>
                  <input type="number" v-model.number="form.guests_count" class="form-input" min="0" placeholder="0">
                </div>
                <div class="form-group yt-live-group">
                  <label class="form-label">📺 YouTube Live</label>
                  <input type="number" v-model.number="form.youtube_live_count" class="form-input form-input-yt" min="0" placeholder="Số người xem online">
                </div>
              </div>
            </div>

            <!-- Khối phải: Tổng ban ngành -->
            <div class="dual-block block-dept">
              <div class="block-header">
                <span class="block-icon">📊</span>
                <span class="block-title">Tổng Ban Ngành</span>
              </div>

              <div v-if="activities_depts.length > 0" class="dept-list">
                <div v-for="dept in activities_depts" :key="dept.id" class="dept-item">
                  <span class="dept-name">{{ dept.name }}</span>
                  <input
                    type="number"
                    v-model.number="form.dept_breakdown[dept.id]"
                    class="form-input dept-input" min="0" placeholder="0"
                  >
                </div>
              </div>

              <!-- Tổng ban + chênh lệch (auto) -->
              <div class="dept-summary">
                <div class="dept-total-row">
                  <span>Tổng ban:</span>
                  <strong class="dept-total-val">{{ deptTotal }}</strong>
                </div>
                <div v-if="form.total_present > 0" class="dept-diff-row" :class="diffClass">
                  <span>Chênh lệch:</span>
                  <strong>{{ diffLabel }}</strong>
                </div>
              </div>
            </div>

          </div><!-- /dual-grid -->

          <!-- Ghi chú sự cố -->
          <div class="form-group">
            <label class="form-label">⚠️ Ghi Chú Sự Cố (nếu có)</label>
            <textarea
              v-model="form.incident_note"
              class="form-textarea" rows="2"
              placeholder="Mất điện, sự cố âm thanh, thay đổi chương trình..."
            ></textarea>
          </div>

          <!-- Ghi chú chung -->
          <div class="form-group">
            <label class="form-label">📝 Ghi Chú Khác</label>
            <textarea v-model="form.notes" class="form-textarea" rows="2" placeholder="Ghi chú thêm..."></textarea>
          </div>

          <!-- Flash -->
          <div v-if="$page.props.flash?.success" class="flash-success">
            ✅ {{ $page.props.flash.success }}
          </div>

          <button type="submit" :disabled="submitting" class="btn-submit">
            <span v-if="submitting">Đang lưu...</span>
            <span v-else>💾 Lưu Điểm Danh</span>
          </button>
        </form>
      </div>

      <!-- RIGHT: Chart -->
      <div class="att-chart-panel">
        <h2 class="panel-title">📈 Xu Hướng 16 Tuần</h2>
        <div v-if="chart_data.length > 0" class="chart-wrap">
          <div class="bar-chart">
            <div v-for="(pt, i) in chart_data" :key="i" class="bar-col">
              <div class="bar-stack">
                <!-- Thư Ký đếm (tổng thật) -->
                <div class="bar-seg bar-total-bg" :style="{ height: barPx(pt.total) + 'px' }" :title="'TK: ' + pt.total"></div>
              </div>
              <!-- Dấu dept_total (nếu khác total) -->
              <div v-if="pt.dept_total && pt.dept_total !== pt.total"
                class="bar-dept-marker"
                :style="{ bottom: barPx(pt.dept_total) + 'px' }"
                :title="'Ban: ' + pt.dept_total"
              ></div>
              <div class="bar-label">{{ pt.date }}</div>
              <div class="bar-total">{{ pt.total }}</div>
            </div>
          </div>
          <div class="chart-legend">
            <span class="leg-i"><span class="leg-dot" style="background:#4f46e5"></span>TK đếm</span>
            <span class="leg-i"><span class="leg-dot leg-dot-line" style="background:#f59e0b"></span>Ban ngành</span>
          </div>
        </div>
        <div v-else class="chart-empty">Chưa có dữ liệu. Hãy nhập buổi đầu tiên!</div>

        <!-- Quick Stats -->
        <div v-if="history.length > 0" class="quick-stats">
          <div class="qs-card qs-blue">
            <div class="qs-val">{{ history.length }}</div>
            <div class="qs-lbl">Buổi đã ghi</div>
          </div>
          <div class="qs-card qs-green">
            <div class="qs-val">{{ maxAttendance }}</div>
            <div class="qs-lbl">Cao nhất</div>
          </div>
          <div class="qs-card qs-purple">
            <div class="qs-val">{{ avgAttendance }}</div>
            <div class="qs-lbl">Trung bình</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bảng Lịch Sử -->
    <div class="history-panel">
      <h2 class="panel-title" style="padding:20px 20px 0">🗂 Lịch Sử Điểm Danh</h2>
      <div v-if="history.length === 0" class="history-empty">Chưa có dữ liệu điểm danh</div>
      <div v-else class="history-table-wrap">
        <table class="history-table">
          <thead>
            <tr>
              <th>Ngày</th>
              <th>TK Đếm</th>
              <th>📺 YT Live</th>
              <th>Tổng Ban</th>
              <th>Chênh lệch</th>
              <th>Nam</th>
              <th>Nữ</th>
              <th>TN</th>
              <th>Thân Hữu</th>
              <th>Sự Cố</th>
              <th>Người ghi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rec in history" :key="rec.id">
              <td class="td-date">{{ rec.meeting_date }}</td>
              <td><span class="chip chip-total">{{ rec.total_present }}</span></td>
              <td>
                <span v-if="rec.youtube_live_count != null" class="chip chip-yt">📺 {{ rec.youtube_live_count }}</span>
                <span v-else class="text-gray">—</span>
              </td>
              <td>
                <span v-if="rec.dept_total > 0" class="chip chip-dept">{{ rec.dept_total }}</span>
                <span v-else class="text-gray">—</span>
              </td>
              <td>
                <span v-if="rec.dept_total > 0"
                  class="chip"
                  :class="rec.unaccounted > 0 ? 'chip-diff-pos' : (rec.unaccounted < 0 ? 'chip-diff-neg' : 'chip-diff-zero')"
                >
                  {{ rec.unaccounted > 0 ? '+' : '' }}{{ rec.unaccounted }}
                </span>
                <span v-else class="text-gray">—</span>
              </td>
              <td>{{ rec.total_male || '—' }}</td>
              <td>{{ rec.total_female || '—' }}</td>
              <td>{{ rec.total_children || '—' }}</td>
              <td>
                <span v-if="rec.guests_count" class="chip chip-guest">{{ rec.guests_count }}</span>
                <span v-else class="text-gray">—</span>
              </td>
              <td class="td-incident">
                <span v-if="rec.incident_note" class="incident-badge" :title="rec.incident_note">⚠️ Có sự cố</span>
                <span v-else class="text-gray">—</span>
              </td>
              <td class="td-recorder">{{ rec.recorder_name || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps({
  meetings:         { type: Array,  default: () => [] },
  history:          { type: Array,  default: () => [] },
  chart_data:       { type: Array,  default: () => [] },
  activities_depts: { type: Array,  default: () => [] },
  month_summary:    { type: Object, default: () => ({}) },
  prev_summary:     { type: Object, default: () => ({}) },
  filters:          { type: Object, default: () => ({}) },
});

// Form state
const submitting = ref(false);
const form = ref({
  meeting_id:         '',
  total_present:      0,
  total_male:         0,
  total_female:       0,
  total_children:     0,
  guests_count:       0,
  youtube_live_count: null,
  dept_breakdown:     {},
  notes:              '',
  incident_note:      '',
});

// ── Computed ──────────────────────────────────────────────────
const deptTotal = computed(() =>
  Object.values(form.value.dept_breakdown || {}).reduce((s, v) => s + (parseInt(v) || 0), 0)
);

const diff = computed(() => (form.value.total_present || 0) - deptTotal.value);

const diffLabel = computed(() => {
  const d = diff.value;
  if (d === 0) return 'Khớp ✓';
  return d > 0 ? `+${d} (TK nhiều hơn)` : `${d} (Ban nhiều hơn)`;
});

const diffClass = computed(() => {
  const d = diff.value;
  if (d === 0) return 'diff-match';
  return d > 0 ? 'diff-pos' : 'diff-neg';
});

const avgDelta = computed(() => {
  if (!props.prev_summary?.avg) return 0;
  return Math.round(((props.month_summary?.avg || 0) - (props.prev_summary?.avg || 0)) * 10) / 10;
});

// Stats
const maxAttendance = computed(() =>
  props.history.length ? Math.max(...props.history.map(r => r.total_present)) : 0
);
const avgAttendance = computed(() => {
  if (!props.history.length) return 0;
  const total = props.history.reduce((s, r) => s + (r.total_present || 0), 0);
  return Math.round(total / props.history.length);
});

// Chart helpers
const maxTotal = computed(() =>
  props.chart_data.length ? Math.max(...props.chart_data.map(p => p.total || 0), 1) : 1
);
function barPx(count) {
  if (!maxTotal.value || maxTotal.value <= 0) return 0;
  return Math.round(((count || 0) / maxTotal.value) * 110);
}

// Submit
function submitForm() {
  if (!form.value.meeting_id) return;
  submitting.value = true;
  router.post(route('secretary.attendance.store'), form.value, {
    preserveScroll: true,
    onSuccess: () => {
      form.value = {
        meeting_id: '', total_present: 0, total_male: 0,
        total_female: 0, total_children: 0, guests_count: 0,
        youtube_live_count: null,
        dept_breakdown: {}, notes: '', incident_note: '',
      };
    },
    onFinish: () => submitting.value = false,
  });
}
</script>

<style scoped>
.sec-attendance {
  max-width: 1280px;
  margin: 0 auto;
  padding: 24px 16px;
  font-family: 'Inter', sans-serif;
}

/* ── Header ─────────────────────────────────────────── */
.sec-header {
  display: flex;
  align-items: center;
  gap: 16px;
  background: linear-gradient(135deg, #1d4ed8, #4338ca);
  color: white;
  border-radius: 20px;
  padding: 24px 28px;
  margin-bottom: 20px;
  flex-wrap: wrap;
  box-shadow: 0 8px 24px rgba(29, 78, 216, 0.2);
}
.sec-header-icon { font-size: 2rem; }
.sec-header-text h1 { margin: 0; font-size: 1.5rem; font-weight: 800; }
.sec-header-text p  { margin: 4px 0 0; opacity: 0.8; font-size: 0.88rem; }
.sec-header-nav { margin-left: auto; display: flex; gap: 10px; flex-wrap: wrap; }
.btn-nav {
  padding: 8px 16px;
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.3);
  color: white;
  border-radius: 12px;
  text-decoration: none;
  font-size: 0.88rem;
  font-weight: 600;
  transition: background 0.15s;
}
.btn-nav:hover { background: rgba(255,255,255,0.25); }

/* ── Month Summary Bar ───────────────────────────────── */
.month-bar {
  background: white;
  border: 1px solid #e0e7ff;
  border-radius: 16px;
  padding: 14px 20px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
  box-shadow: 0 2px 8px rgba(79,70,229,0.07);
}
.month-bar-title { font-size: 0.9rem; font-weight: 700; color: #4338ca; white-space: nowrap; }
.month-stats { display: flex; gap: 24px; flex-wrap: wrap; }
.ms-item { text-align: center; }
.ms-val { display: block; font-size: 1.3rem; font-weight: 800; color: #1f2937; line-height: 1; }
.ms-lbl { display: block; font-size: 0.7rem; color: #6b7280; margin-top: 2px; }
.ms-sep { border-left: 1px solid #e5e7eb; padding-left: 24px; }
.ms-up .ms-val   { color: #059669; }
.ms-down .ms-val { color: #dc2626; }

/* ── Main grid ───────────────────────────────────────── */
.att-grid {
  display: grid;
  grid-template-columns: 520px 1fr;
  gap: 20px;
  margin-bottom: 20px;
}
@media (max-width: 980px) { .att-grid { grid-template-columns: 1fr; } }

.att-form-panel, .att-chart-panel, .history-panel {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  overflow: hidden;
}
.att-form-panel { padding: 24px; }
.att-chart-panel { padding: 20px; }
.panel-title { font-size: 1rem; font-weight: 700; color: #1f2937; margin: 0 0 18px; }

/* ── Form ────────────────────────────────────────────── */
.att-form { display: flex; flex-direction: column; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 0.78rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.04em; }
.form-select, .form-input, .form-textarea {
  border: 1.5px solid #d1d5db;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 0.95rem;
  color: #1f2937;
  transition: border-color 0.15s, box-shadow 0.15s;
  outline: none;
  background: white;
}
.form-select:focus, .form-input:focus, .form-textarea:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}
.form-input-lg { font-size: 1.8rem; font-weight: 800; text-align: center; padding: 14px; }
.form-textarea { resize: vertical; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

/* ── Dual blocks ─────────────────────────────────────── */
.dual-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
@media (max-width: 640px) { .dual-grid { grid-template-columns: 1fr; } }

.dual-block {
  border-radius: 14px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.block-tk   { background: #eff6ff; border: 2px solid #bfdbfe; }
.block-dept { background: #fefce8; border: 2px solid #fde68a; }

.block-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
}
.block-icon  { font-size: 1.1rem; }
.block-title { font-size: 0.85rem; font-weight: 800; color: #1f2937; text-transform: uppercase; letter-spacing: 0.05em; }

.tk-total { border-color: #3b82f6 !important; color: #1d4ed8 !important; }

/* Dept list */
.dept-list { display: flex; flex-direction: column; gap: 6px; }
.dept-item { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.dept-name { font-size: 0.82rem; font-weight: 600; color: #374151; flex: 1; }
.dept-input { width: 72px; text-align: center; padding: 6px 8px; font-size: 0.9rem; }

/* Dept summary */
.dept-summary {
  background: rgba(0,0,0,0.04);
  border-radius: 10px;
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.dept-total-row, .dept-diff-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #374151;
}
.dept-total-val { font-size: 1.1rem; font-weight: 800; color: #1f2937; }
.diff-match { color: #059669; }
.diff-pos   .dept-diff-row { color: #2563eb; }
.diff-neg   .dept-diff-row { color: #dc2626; }

/* Flash & submit */
.flash-success {
  background: #d1fae5; border: 1px solid #a7f3d0; color: #065f46;
  border-radius: 10px; padding: 12px 16px; font-size: 0.88rem; font-weight: 600;
}
.btn-submit {
  width: 100%;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: white; border: none; border-radius: 12px;
  padding: 14px; font-size: 1rem; font-weight: 700; cursor: pointer;
  transition: opacity 0.15s, transform 0.1s;
  box-shadow: 0 4px 12px rgba(79,70,229,0.3);
}
.btn-submit:hover { opacity: 0.92; }
.btn-submit:active { transform: scale(0.99); }
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Chart ───────────────────────────────────────────── */
.chart-wrap { display: flex; flex-direction: column; gap: 8px; }
.bar-chart {
  display: flex; align-items: flex-end;
  gap: 5px; height: 120px; position: relative;
}
.bar-col {
  display: flex; flex-direction: column;
  align-items: center; gap: 2px; flex: 1; min-width: 0;
  position: relative;
}
.bar-stack {
  display: flex; flex-direction: column-reverse;
  align-items: center; width: 100%; gap: 1px;
}
.bar-seg { width: 100%; min-height: 2px; border-radius: 3px 3px 0 0; transition: height 0.4s ease; }
.bar-total-bg { background: linear-gradient(180deg, #4f46e5, #818cf8); }

/* dept marker line */
.bar-dept-marker {
  position: absolute;
  left: 0; right: 0;
  height: 2px;
  background: #f59e0b;
  border-radius: 1px;
}

.bar-label { font-size: 0.6rem; color: #9ca3af; margin-top: 3px; white-space: nowrap; }
.bar-total { font-size: 0.65rem; font-weight: 700; color: #374151; }
.chart-empty { text-align: center; color: #9ca3af; font-size: 0.9rem; padding: 40px 20px; }
.chart-legend { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; margin-top: 4px; }
.leg-i { font-size: 0.75rem; color: #6b7280; display: flex; align-items: center; gap: 5px; }
.leg-dot { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }
.leg-dot-line { height: 3px; border-radius: 1px; }

/* Quick stats */
.quick-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 16px; }
.qs-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; text-align: center; }
.qs-val { font-size: 1.5rem; font-weight: 800; line-height: 1; }
.qs-lbl { font-size: 0.72rem; color: #6b7280; margin-top: 4px; }
.qs-blue .qs-val   { color: #1e40af; }
.qs-green .qs-val  { color: #065f46; }
.qs-purple .qs-val { color: #7c3aed; }

/* ── History Table ───────────────────────────────────── */
.history-panel { margin-top: 0; }
.history-empty { text-align: center; color: #9ca3af; padding: 40px; font-size: 0.9rem; }
.history-table-wrap { overflow-x: auto; }
.history-table { width: 100%; border-collapse: collapse; }
.history-table th {
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
  padding: 11px 14px;
  font-size: 0.75rem;
  font-weight: 700;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  text-align: left;
  white-space: nowrap;
}
.history-table td {
  padding: 11px 14px;
  font-size: 0.85rem;
  color: #374151;
  border-bottom: 1px solid #f3f4f6;
}
.history-table tr:last-child td { border-bottom: none; }
.history-table tr:hover td { background: #fafafa; }
.td-date     { font-weight: 700; color: #1f2937; white-space: nowrap; }
.td-recorder { color: #6b7280; font-size: 0.8rem; }
.td-incident { max-width: 120px; }

.chip {
  display: inline-flex; align-items: center;
  padding: 3px 10px; border-radius: 20px;
  font-size: 0.8rem; font-weight: 700;
}
.chip-total    { background: #e0e7ff; color: #3730a3; }
.chip-yt       { background: #fee2e2; color: #991b1b; }
.chip-dept     { background: #fef3c7; color: #92400e; }
.chip-diff-pos { background: #dbeafe; color: #1e40af; }
.chip-diff-neg { background: #fee2e2; color: #991b1b; }
.chip-diff-zero{ background: #d1fae5; color: #065f46; }
.chip-guest    { background: #fef9c3; color: #713f12; }
.text-gray     { color: #d1d5db; }

/* YouTube live input */
.yt-live-group { margin-top: 4px; }
.form-input-yt { border-color: #fca5a5 !important; color: #dc2626 !important; }
.incident-badge {
  font-size: 0.78rem; color: #92400e;
  cursor: pointer;
}
</style>
