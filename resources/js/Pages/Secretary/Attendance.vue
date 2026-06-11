<template>
  <div class="sec-attendance">

    <!-- Header -->
    <div class="sec-header">
      <div class="sec-header-icon">📝</div>
      <div class="sec-header-text">
        <h1>Điểm Danh Chủ Nhật</h1>
        <p>Ghi và theo dõi số liệu điểm danh toàn Hội Thánh</p>
      </div>
      <div class="sec-header-nav">
        <Link :href="route('secretary.dashboard')" class="btn-nav">← Dashboard</Link>
        <Link :href="route('secretary.social-stats.index')" class="btn-nav">📡 MXH</Link>
      </div>
    </div>

    <!-- Chart + Form Grid -->
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

          <!-- Tổng hiện diện -->
          <div class="form-group">
            <label class="form-label">Tổng Số Hiện Diện *</label>
            <input
              type="number" v-model.number="form.total_present"
              class="form-input form-input-lg" min="0" placeholder="0" required
            >
          </div>

          <!-- Breakdown 2 cols -->
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
          </div>

          <!-- Breakdown ban ngành (nếu có) -->
          <div v-if="activities_depts.length > 0" class="form-group">
            <label class="form-label">📦 Phân Chia Theo Ban (tùy chọn)</label>
            <div class="dept-grid">
              <div v-for="dept in activities_depts" :key="dept.id" class="dept-item">
                <span class="dept-name">{{ dept.name }}</span>
                <input
                  type="number"
                  v-model.number="form.dept_breakdown[dept.id]"
                  class="form-input dept-input" min="0" placeholder="0"
                >
              </div>
            </div>
          </div>

          <!-- Live total preview -->
          <div v-if="form.total_present > 0" class="att-preview">
            <div class="preview-row">
              <span>Tổng hiện diện</span>
              <strong class="preview-total">{{ form.total_present }} người</strong>
            </div>
            <div v-if="genderSum > 0" class="preview-row preview-sub">
              <span>Nam + Nữ + Thiếu Nhi</span>
              <span>{{ form.total_male || 0 }} + {{ form.total_female || 0 }} + {{ form.total_children || 0 }} = {{ genderSum }}</span>
            </div>
            <div v-if="form.guests_count > 0" class="preview-row preview-sub">
              <span>Thân Hữu</span>
              <span>+{{ form.guests_count }}</span>
            </div>
          </div>

          <!-- Ghi chú -->
          <div class="form-group">
            <label class="form-label">📝 Ghi Chú</label>
            <textarea v-model="form.notes" class="form-textarea" rows="2" placeholder="Ghi chú thêm..."></textarea>
          </div>

          <!-- Success/Error flash -->
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
                <div class="bar-seg bar-children" :style="{ height: barPx(pt.children) + 'px' }" :title="'Thiếu nhi: ' + pt.children"></div>
                <div class="bar-seg bar-female" :style="{ height: barPx(pt.female) + 'px' }" :title="'Nữ: ' + pt.female"></div>
                <div class="bar-seg bar-male" :style="{ height: barPx(pt.male) + 'px' }" :title="'Nam: ' + pt.male"></div>
                <div class="bar-seg bar-guests" :style="{ height: barPx(pt.guests) + 'px' }" :title="'Thân hữu: ' + pt.guests"></div>
              </div>
              <div class="bar-label">{{ pt.date }}</div>
              <div class="bar-total">{{ pt.total }}</div>
            </div>
          </div>
          <div class="chart-legend">
            <span class="leg-i"><span class="leg-dot" style="background:#1e3a5f"></span>Nam</span>
            <span class="leg-i"><span class="leg-dot" style="background:#db2777"></span>Nữ</span>
            <span class="leg-i"><span class="leg-dot" style="background:#0891b2"></span>TN</span>
            <span class="leg-i"><span class="leg-dot" style="background:#f59e0b"></span>Thân Hữu</span>
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

    <!-- Lịch Sử Table -->
    <div class="history-panel">
      <h2 class="panel-title" style="padding:20px 20px 0">🗂 Lịch Sử Điểm Danh</h2>
      <div v-if="history.length === 0" class="history-empty">Chưa có dữ liệu điểm danh</div>
      <div v-else class="history-table-wrap">
        <table class="history-table">
          <thead>
            <tr>
              <th>Ngày</th>
              <th>Tổng</th>
              <th>Nam</th>
              <th>Nữ</th>
              <th>TN</th>
              <th>Thân Hữu</th>
              <th>Người ghi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="rec in history" :key="rec.id">
              <td class="td-date">{{ rec.meeting_date }}</td>
              <td><span class="chip chip-total">{{ rec.total_present }}</span></td>
              <td>{{ rec.total_male }}</td>
              <td>{{ rec.total_female }}</td>
              <td>{{ rec.total_children }}</td>
              <td>
                <span v-if="rec.guests_count" class="chip chip-guest">{{ rec.guests_count }}</span>
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

const page = usePage();

const props = defineProps({
  meetings:         { type: Array, default: () => [] },
  history:          { type: Array, default: () => [] },
  chart_data:       { type: Array, default: () => [] },
  activities_depts: { type: Array, default: () => [] },
});

// Form state
const submitting = ref(false);
const form = ref({
  meeting_id:      '',
  total_present:   0,
  total_male:      0,
  total_female:    0,
  total_children:  0,
  guests_count:    0,
  dept_breakdown:  {},
  notes:           '',
});

// Live sum
const genderSum = computed(() =>
  (form.value.total_male || 0) + (form.value.total_female || 0) + (form.value.total_children || 0)
);

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
  return Math.round(((count || 0) / maxTotal.value) * 100);
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
        dept_breakdown: {}, notes: '',
      };
    },
    onFinish: () => submitting.value = false,
  });
}
</script>

<style scoped>
.sec-attendance {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 16px;
  font-family: 'Inter', sans-serif;
}

/* Header */
.sec-header {
  display: flex;
  align-items: center;
  gap: 16px;
  background: linear-gradient(135deg, #1d4ed8, #4338ca);
  color: white;
  border-radius: 20px;
  padding: 24px 28px;
  margin-bottom: 24px;
  flex-wrap: wrap;
  box-shadow: 0 8px 24px rgba(29, 78, 216, 0.2);
}
.sec-header-icon { font-size: 2rem; }
.sec-header-text h1 { margin: 0; font-size: 1.5rem; font-weight: 800; }
.sec-header-text p { margin: 4px 0 0; opacity: 0.8; font-size: 0.88rem; }
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

/* Main grid */
.att-grid {
  display: grid;
  grid-template-columns: 420px 1fr;
  gap: 20px;
  margin-bottom: 20px;
}
@media (max-width: 900px) { .att-grid { grid-template-columns: 1fr; } }

/* Form panel */
.att-form-panel, .att-chart-panel, .history-panel {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  overflow: hidden;
}
.att-form-panel { padding: 24px; }
.att-chart-panel { padding: 20px; }

.panel-title { font-size: 1rem; font-weight: 700; color: #1f2937; margin: 0 0 18px; }

/* Form elements */
.att-form { display: flex; flex-direction: column; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 0.8rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.05em; }
.form-select, .form-input, .form-textarea {
  border: 1.5px solid #d1d5db;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 0.95rem;
  color: #1f2937;
  transition: border-color 0.15s, box-shadow 0.15s;
  outline: none;
}
.form-select:focus, .form-input:focus, .form-textarea:focus {
  border-color: #1d4ed8;
  box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
}
.form-input-lg { font-size: 1.5rem; font-weight: 700; text-align: center; padding: 14px; }
.form-textarea { resize: vertical; }
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* Dept breakdown */
.dept-grid { display: flex; flex-direction: column; gap: 8px; }
.dept-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 12px; background: #f9fafb; border-radius: 8px; }
.dept-name { font-size: 0.85rem; font-weight: 600; color: #374151; flex: 1; }
.dept-input { width: 80px; text-align: center; padding: 6px 8px; }

/* Preview box */
.att-preview {
  background: linear-gradient(135deg, #eff6ff, #eef2ff);
  border: 1.5px solid #bfdbfe;
  border-radius: 12px;
  padding: 14px 16px;
}
.preview-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: #1e40af; }
.preview-sub { opacity: 0.75; font-size: 0.82rem; margin-top: 4px; }
.preview-total { font-size: 1.3rem; font-weight: 800; color: #1e3a8a; }

/* Flash */
.flash-success {
  background: #d1fae5;
  border: 1px solid #a7f3d0;
  color: #065f46;
  border-radius: 10px;
  padding: 12px 16px;
  font-size: 0.88rem;
  font-weight: 600;
}

/* Submit button */
.btn-submit {
  width: 100%;
  background: linear-gradient(135deg, #1d4ed8, #4338ca);
  color: white;
  border: none;
  border-radius: 12px;
  padding: 14px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.15s, transform 0.1s;
  box-shadow: 0 4px 12px rgba(29,78,216,0.3);
}
.btn-submit:hover { opacity: 0.92; }
.btn-submit:active { transform: scale(0.99); }
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }

/* Chart */
.chart-wrap { display: flex; flex-direction: column; gap: 8px; }
.bar-chart { display: flex; align-items: flex-end; gap: 5px; height: 120px; }
.bar-col { display: flex; flex-direction: column; align-items: center; gap: 2px; flex: 1; min-width: 0; }
.bar-stack { display: flex; flex-direction: column-reverse; align-items: center; width: 100%; gap: 1px; }
.bar-seg { width: 100%; min-height: 2px; border-radius: 2px 2px 0 0; transition: height 0.4s ease; }
.bar-male     { background: #1e3a5f; }
.bar-female   { background: #db2777; }
.bar-children { background: #0891b2; }
.bar-guests   { background: #f59e0b; }
.bar-label { font-size: 0.6rem; color: #9ca3af; margin-top: 3px; white-space: nowrap; }
.bar-total { font-size: 0.65rem; font-weight: 700; color: #374151; }
.chart-empty { text-align: center; color: #9ca3af; font-size: 0.9rem; padding: 40px 20px; }
.chart-legend { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; margin-top: 4px; }
.leg-i { font-size: 0.75rem; color: #6b7280; display: flex; align-items: center; gap: 5px; }
.leg-dot { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }

/* Quick stats */
.quick-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 16px; }
.qs-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; text-align: center; }
.qs-val { font-size: 1.5rem; font-weight: 800; line-height: 1; }
.qs-lbl { font-size: 0.72rem; color: #6b7280; margin-top: 4px; }
.qs-blue .qs-val { color: #1e40af; }
.qs-green .qs-val { color: #065f46; }
.qs-purple .qs-val { color: #7c3aed; }

/* History */
.history-panel { margin-top: 0; }
.history-empty { text-align: center; color: #9ca3af; padding: 40px; font-size: 0.9rem; }
.history-table-wrap { overflow-x: auto; }
.history-table { width: 100%; border-collapse: collapse; }
.history-table th {
  background: #f8fafc;
  border-bottom: 2px solid #e5e7eb;
  padding: 12px 16px;
  font-size: 0.78rem;
  font-weight: 700;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  text-align: left;
}
.history-table td { padding: 13px 16px; font-size: 0.88rem; color: #374151; border-bottom: 1px solid #f3f4f6; }
.history-table tr:last-child td { border-bottom: none; }
.history-table tr:hover td { background: #f9fafb; }
.td-date { font-weight: 700; color: #1f2937; white-space: nowrap; }
.td-recorder { color: #6b7280; font-size: 0.82rem; }
.chip { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }
.chip-total { background: #dbeafe; color: #1e40af; }
.chip-guest { background: #fef3c7; color: #92400e; }
.text-gray { color: #d1d5db; }
</style>
