<template>
  <div class="church-attendance">
    <!-- Header -->
    <div class="page-header">
      <div class="header-icon">📋</div>
      <div>
        <h1>Điểm Danh Chủ Nhật</h1>
        <p class="subtitle">Thư Ký nhập số liệu tổng + từng ban báo</p>
      </div>
    </div>

    <div class="content-grid">
      <!-- Left: Form nhập -->
      <div class="form-panel">
        <h2 class="panel-title">📝 Nhập Điểm Danh</h2>

        <!-- Chọn buổi nhóm -->
        <div class="field-group">
          <label class="field-label">Buổi Nhóm <span class="required">*</span></label>
          <select v-model="form.meeting_id" class="select-input">
            <option value="">-- Chọn buổi nhóm --</option>
            <option v-for="m in meetings" :key="m.id" :value="m.id">{{ m.label }}</option>
          </select>
        </div>

        <!-- Điểm danh tổng -->
        <div class="card-section">
          <h3 class="card-section-title">👁 Thư Ký Đếm Thực Tế (bao gồm khách)</h3>
          <div class="count-grid">
            <div class="count-field">
              <label>Nam</label>
              <input type="number" v-model.number="form.total_male" min="0" class="num-input" @input="calcTotal" />
            </div>
            <div class="count-field">
              <label>Nữ</label>
              <input type="number" v-model.number="form.total_female" min="0" class="num-input" @input="calcTotal" />
            </div>
            <div class="count-field">
              <label>Thiếu Nhi</label>
              <input type="number" v-model.number="form.total_children" min="0" class="num-input" @input="calcTotal" />
            </div>
            <div class="count-field">
              <label>Khách</label>
              <input type="number" v-model.number="form.guests_count" min="0" class="num-input" @input="calcTotal" />
            </div>
          </div>
          <div class="total-display">
            Tổng Thực Tế: <strong>{{ form.total_present }} người</strong>
          </div>
        </div>

        <!-- Điểm danh từng ban -->
        <div v-if="activities_depts.length" class="card-section">
          <h3 class="card-section-title">🏛 Điểm Danh Từng Ban (ban báo)</h3>
          <div v-for="dept in activities_depts" :key="dept.id" class="dept-count-row">
            <label class="dept-count-label">{{ dept.name }}</label>
            <input
              type="number"
              v-model.number="deptBreakdown[dept.id]"
              min="0"
              class="num-input num-input--small"
              placeholder="0"
            />
          </div>
          <div class="breakdown-total" :class="{ 'mismatch': deptTotal !== form.total_present }">
            <span>Tổng từ ban: <strong>{{ deptTotal }} người</strong></span>
            <span v-if="deptTotal !== form.total_present" class="mismatch-note">
              (Chênh lệch: {{ Math.abs(form.total_present - deptTotal) }} · thường do khách/không thuộc ban)
            </span>
          </div>
        </div>

        <!-- Ghi chú -->
        <div class="field-group">
          <label class="field-label">Ghi Chú</label>
          <textarea v-model="form.notes" class="textarea-input" rows="2" placeholder="Ghi chú buổi nhóm (nếu có)..." />
        </div>

        <button class="btn-submit" @click="submit" :disabled="!form.meeting_id || submitting">
          <span v-if="submitting">⏳ Đang lưu...</span>
          <span v-else>💾 Lưu Điểm Danh</span>
        </button>
      </div>

      <!-- Right: Biểu đồ + lịch sử -->
      <div class="right-panel">
        <!-- Mini chart (last 8 weeks) -->
        <div class="chart-panel" v-if="chart_data.length">
          <h3 class="panel-title">📈 Xu Hướng 8 Tuần</h3>
          <div class="mini-chart">
            <div v-for="point in chart_data" :key="point.date" class="chart-bar-wrap">
              <div class="chart-bar" :style="{ height: barHeight(point.total) + 'px' }" :title="point.date + ': ' + point.total"></div>
              <div class="chart-label">{{ point.date }}</div>
              <div class="chart-val">{{ point.total }}</div>
            </div>
          </div>
        </div>

        <!-- Lịch sử -->
        <div class="history-panel">
          <h3 class="panel-title">🗂 Lịch Sử Điểm Danh</h3>
          <div v-if="history.length === 0" class="empty-msg">Chưa có dữ liệu</div>
          <div v-for="rec in history" :key="rec.id" class="history-row">
            <div class="hist-date">{{ rec.meeting_date }}</div>
            <div class="hist-stats">
              <span class="stat-chip">👥 {{ rec.total_present }}</span>
              <span class="stat-chip chip-male">♂ {{ rec.total_male }}</span>
              <span class="stat-chip chip-female">♀ {{ rec.total_female }}</span>
              <span v-if="rec.total_children" class="stat-chip chip-child">🧒 {{ rec.total_children }}</span>
              <span v-if="rec.guests_count" class="stat-chip chip-guest">🙋 {{ rec.guests_count }}</span>
            </div>
            <div v-if="rec.dept_total > 0" class="hist-breakdown">
              Ban báo: {{ rec.dept_total }} · Chênh: {{ rec.unaccounted }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  meetings:         { type: Array, default: () => [] },
  history:          { type: Array, default: () => [] },
  chart_data:       { type: Array, default: () => [] },
  activities_depts: { type: Array, default: () => [] },
})

const submitting = ref(false)
const deptBreakdown = ref({})

const form = ref({
  meeting_id:     '',
  total_present:  0,
  total_male:     0,
  total_female:   0,
  total_children: 0,
  guests_count:   0,
  notes:          '',
})

function calcTotal() {
  form.value.total_present =
    (form.value.total_male || 0) +
    (form.value.total_female || 0) +
    (form.value.total_children || 0) +
    (form.value.guests_count || 0)
}

const deptTotal = computed(() => {
  return Object.values(deptBreakdown.value).reduce((sum, v) => sum + (parseInt(v) || 0), 0)
})

const maxTotal = computed(() => {
  if (!props.chart_data.length) return 1
  return Math.max(...props.chart_data.map(p => p.total), 1)
})

function barHeight(total) {
  return Math.round((total / maxTotal.value) * 80)
}

function submit() {
  if (!form.value.meeting_id) return
  submitting.value = true
  router.post(route('deacon.church-attendance.store'), {
    ...form.value,
    dept_breakdown: deptBreakdown.value,
  }, {
    onFinish: () => { submitting.value = false },
    onSuccess: () => {
      form.value = { meeting_id: '', total_present: 0, total_male: 0, total_female: 0, total_children: 0, guests_count: 0, notes: '' }
      deptBreakdown.value = {}
    },
  })
}
</script>

<style scoped>
.church-attendance {
  max-width: 1100px;
  margin: 0 auto;
  padding: 24px 16px;
  font-family: 'Inter', sans-serif;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 14px;
  background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
  color: white;
  border-radius: 14px;
  padding: 22px 28px;
  margin-bottom: 24px;
}
.header-icon { font-size: 2rem; }
.page-header h1 { margin: 0; font-size: 1.5rem; font-weight: 700; }
.subtitle { margin: 4px 0 0; opacity: 0.8; font-size: 0.9rem; }

.content-grid { display: grid; grid-template-columns: 1fr 380px; gap: 20px; }

/* Form Panel */
.form-panel, .right-panel > div {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 20px;
}
.panel-title { font-size: 1rem; font-weight: 600; color: #1e3a5f; margin: 0 0 16px; }

.field-group { margin-bottom: 16px; }
.field-label { display: block; font-size: 0.85rem; font-weight: 500; color: #374151; margin-bottom: 6px; }
.required { color: #ef4444; }
.select-input, .textarea-input {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
  font-family: inherit;
  background: white;
  box-sizing: border-box;
}
.select-input:focus, .textarea-input:focus { outline: none; border-color: #2d6a9f; }

/* Card sections */
.card-section {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 14px;
  margin-bottom: 16px;
}
.card-section-title { font-size: 0.85rem; font-weight: 600; color: #374151; margin: 0 0 12px; }

.count-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.count-field { display: flex; flex-direction: column; gap: 4px; }
.count-field label { font-size: 0.78rem; color: #6b7280; font-weight: 500; }
.num-input {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 1rem;
  text-align: center;
  font-weight: 600;
  box-sizing: border-box;
}
.num-input--small { width: 80px; text-align: center; }

.total-display {
  margin-top: 10px;
  text-align: center;
  font-size: 1.1rem;
  color: #1e3a5f;
  background: #dbeafe;
  padding: 8px;
  border-radius: 8px;
}

.dept-count-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.dept-count-label { font-size: 0.88rem; color: #374151; }

.breakdown-total {
  margin-top: 10px;
  font-size: 0.88rem;
  color: #374151;
  background: #f0fdf4;
  padding: 8px 10px;
  border-radius: 6px;
}
.breakdown-total.mismatch { background: #fff8e1; }
.mismatch-note { color: #92400e; font-size: 0.8rem; margin-left: 8px; }

.btn-submit {
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: opacity 0.2s;
}
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

/* Right Panel */
.right-panel { display: flex; flex-direction: column; gap: 16px; }
.chart-panel, .history-panel { background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 18px; }

.mini-chart { display: flex; align-items: flex-end; gap: 8px; height: 100px; padding-top: 10px; }
.chart-bar-wrap { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; }
.chart-bar { width: 100%; background: linear-gradient(180deg, #2d6a9f, #1e3a5f); border-radius: 4px 4px 0 0; min-height: 4px; }
.chart-label { font-size: 0.65rem; color: #9ca3af; }
.chart-val { font-size: 0.7rem; font-weight: 600; color: #374151; }

.history-row { border-bottom: 1px solid #f3f4f6; padding: 10px 0; }
.history-row:last-child { border-bottom: none; }
.hist-date { font-size: 0.85rem; font-weight: 600; color: #1f2937; margin-bottom: 6px; }
.hist-stats { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 4px; }
.stat-chip {
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 10px;
  background: #e5e7eb;
  color: #374151;
  font-weight: 500;
}
.chip-male   { background: #dbeafe; color: #1e40af; }
.chip-female { background: #fce7f3; color: #9d174d; }
.chip-child  { background: #d1fae5; color: #065f46; }
.chip-guest  { background: #fef9c3; color: #713f12; }
.hist-breakdown { font-size: 0.78rem; color: #6b7280; }
.empty-msg { text-align: center; color: #9ca3af; font-size: 0.9rem; padding: 20px; }

@media (max-width: 768px) {
  .content-grid { grid-template-columns: 1fr; }
}
</style>
