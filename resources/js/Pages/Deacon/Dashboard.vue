<template>
  <div class="deacon-dashboard">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-icon">⛪</div>
        <div>
          <h1>Chấp Sự Dashboard</h1>
          <p class="subtitle">Nhiệm kỳ hiện tại · Tổng quan ban ngành phụ trách</p>
        </div>
      </div>
    </div>

    <!-- No Assignments -->
    <div v-if="!has_assignments" class="empty-state">
      <div class="empty-icon">📋</div>
      <h2>Chưa được phân công</h2>
      <p>Bạn chưa được phân công phụ trách ban ngành nào trong nhiệm kỳ này.<br>Liên hệ Mục Sư hoặc Ban Thư Ký để được cập nhật.</p>
    </div>

    <template v-else>
      <!-- Alerts Section -->
      <div v-if="alerts.length" class="alerts-section">
        <h2 class="section-title">🔔 Thông Báo Cần Chú Ý</h2>
        <div class="alerts-grid">
          <div
            v-for="(alert, i) in alerts"
            :key="i"
            class="alert-card"
            :class="'alert-' + alert.type"
          >
            <span class="alert-icon">{{ alert.icon }}</span>
            <span class="alert-msg">{{ alert.message }}</span>
          </div>
        </div>
      </div>

      <!-- Departments Grid -->
      <div class="section">
        <h2 class="section-title">🏛 Ban Ngành Phụ Trách</h2>
        <div class="dept-grid">
          <div
            v-for="dept in dept_stats"
            :key="dept.id"
            class="dept-card"
            :class="{ 'dept-warning': dept.skipped_meeting, 'dept-alert': dept.pending_reports > 0 }"
          >
            <!-- Dept header -->
            <div class="dept-header">
              <div class="dept-name">{{ dept.name }}</div>
              <div class="dept-block badge" :class="'block-' + dept.block">{{ blockLabel(dept.block) }}</div>
            </div>

            <!-- Stats row -->
            <div class="dept-stats">
              <div class="stat-item" :class="{ 'stat-warn': dept.skipped_meeting }">
                <span class="stat-icon">📅</span>
                <div>
                  <div class="stat-label">Buổi nhóm cuối</div>
                  <div class="stat-value">{{ dept.last_meeting_date || 'Chưa có' }}</div>
                  <div v-if="dept.days_since_meeting !== null" class="stat-sub">
                    {{ dept.days_since_meeting }} ngày trước
                  </div>
                </div>
              </div>

              <div class="stat-item" :class="{ 'stat-warn': dept.pending_reports > 0 }">
                <span class="stat-icon">📋</span>
                <div>
                  <div class="stat-label">Báo cáo chờ</div>
                  <div class="stat-value">{{ dept.pending_reports }}</div>
                </div>
              </div>

              <div class="stat-item" :class="{ 'stat-urgent': dept.pending_care > 0 }">
                <span class="stat-icon">🤝</span>
                <div>
                  <div class="stat-label">Cần chăm sóc</div>
                  <div class="stat-value">{{ dept.pending_care }}</div>
                </div>
              </div>
            </div>

            <!-- Status badge -->
            <div class="dept-status">
              <span v-if="dept.skipped_meeting" class="status-badge status-danger">⚠ Chưa nhóm lâu</span>
              <span v-else-if="dept.pending_reports > 0" class="status-badge status-warning">📋 Có báo cáo mới</span>
              <span v-else class="status-badge status-ok">✓ Ổn định</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Reports -->
      <div class="section">
        <h2 class="section-title">📊 Báo Cáo Gần Đây</h2>
        <div v-if="recent_reports.length === 0" class="empty-mini">Chưa có báo cáo nào từ các ban bạn phụ trách.</div>
        <div v-else class="reports-list">
          <div v-for="report in recent_reports" :key="report.id" class="report-row">
            <div class="report-info">
              <span class="report-dept">{{ report.dept_name }}</span>
              <span class="report-period">Tháng {{ report.report_month }}/{{ report.report_year }}</span>
            </div>
            <div class="report-meta">
              <span class="report-date">{{ report.submitted_at }}</span>
              <span class="report-status" :class="'status-' + report.status">
                {{ statusLabel(report.status) }}
              </span>
            </div>
            <!-- Nhận xét nhanh -->
            <div v-if="report.status === 'submitted'" class="review-section">
              <textarea
                v-model="reviewNotes[report.id]"
                class="review-input"
                placeholder="Nhập nhận xét của bạn..."
                rows="2"
              />
              <button
                class="btn-review"
                @click="submitReview(report.id)"
                :disabled="!reviewNotes[report.id]"
              >
                Gửi Nhận Xét
              </button>
            </div>
            <div v-else-if="report.reviewer_note" class="reviewed-note">
              <span class="note-label">Nhận xét:</span> {{ report.reviewer_note }}
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  dept_stats:      { type: Array, default: () => [] },
  alerts:          { type: Array, default: () => [] },
  recent_reports:  { type: Array, default: () => [] },
  my_dept_ids:     { type: Array, default: () => [] },
  has_assignments: { type: Boolean, default: false },
})

const reviewNotes = ref({})

function blockLabel(block) {
  const map = { activities: 'Sinh Hoạt', ministry: 'Mục Vụ', finance: 'Tài Chính', global: 'Toàn Hệ Thống' }
  return map[block] || block
}

function statusLabel(status) {
  const map = { submitted: 'Đã nộp', reviewed: 'Đã nhận xét', approved: 'Đã duyệt', pending: 'Chưa nộp' }
  return map[status] || status
}

function submitReview(reportId) {
  router.post(route('deacon.reports.review', reportId), {
    reviewer_note: reviewNotes.value[reportId],
  }, {
    onSuccess: () => { reviewNotes.value[reportId] = '' },
  })
}
</script>

<style scoped>
.deacon-dashboard {
  max-width: 1100px;
  margin: 0 auto;
  padding: 24px 16px;
  font-family: 'Inter', sans-serif;
}

/* Header */
.page-header {
  background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 100%);
  border-radius: 16px;
  padding: 28px 32px;
  margin-bottom: 28px;
  color: white;
}
.header-content {
  display: flex;
  align-items: center;
  gap: 16px;
}
.header-icon { font-size: 2.5rem; }
.page-header h1 { margin: 0; font-size: 1.7rem; font-weight: 700; }
.subtitle { margin: 4px 0 0; opacity: 0.8; font-size: 0.95rem; }

/* Sections */
.section { margin-bottom: 32px; }
.section-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e3a5f;
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Alerts */
.alerts-section { margin-bottom: 28px; }
.alerts-grid { display: flex; flex-direction: column; gap: 8px; }
.alert-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  border-radius: 10px;
  font-size: 0.92rem;
}
.alert-warning { background: #fff8e1; border-left: 4px solid #f59e0b; color: #78350f; }
.alert-info    { background: #e0f2fe; border-left: 4px solid #0284c7; color: #0c4a6e; }
.alert-urgent  { background: #fef2f2; border-left: 4px solid #ef4444; color: #7f1d1d; }
.alert-icon { font-size: 1.1rem; }

/* Dept Grid */
.dept-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)); gap: 16px; }
.dept-card {
  background: white;
  border: 1.5px solid #e5e7eb;
  border-radius: 14px;
  padding: 18px;
  transition: box-shadow 0.2s;
}
.dept-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.dept-warning { border-color: #f59e0b; background: #fffbeb; }
.dept-alert   { border-color: #3b82f6; }

.dept-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
.dept-name { font-weight: 600; font-size: 1rem; color: #1f2937; }
.badge { font-size: 0.72rem; padding: 3px 8px; border-radius: 20px; font-weight: 500; }
.block-activities { background: #d1fae5; color: #065f46; }
.block-ministry   { background: #dbeafe; color: #1e40af; }
.block-finance    { background: #fef9c3; color: #713f12; }

.dept-stats { display: flex; gap: 12px; margin-bottom: 12px; }
.stat-item {
  flex: 1;
  display: flex;
  gap: 6px;
  align-items: flex-start;
  background: #f9fafb;
  border-radius: 8px;
  padding: 8px;
}
.stat-warn { background: #fff8e1; }
.stat-urgent { background: #fef2f2; }
.stat-icon { font-size: 1rem; }
.stat-label { font-size: 0.7rem; color: #6b7280; }
.stat-value { font-size: 1rem; font-weight: 700; color: #111827; }
.stat-sub { font-size: 0.7rem; color: #9ca3af; }

.dept-status { display: flex; }
.status-badge { font-size: 0.78rem; padding: 4px 10px; border-radius: 20px; font-weight: 500; }
.status-ok      { background: #d1fae5; color: #065f46; }
.status-warning { background: #fef9c3; color: #713f12; }
.status-danger  { background: #fef2f2; color: #991b1b; }

/* Reports */
.reports-list { display: flex; flex-direction: column; gap: 10px; }
.report-row {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 14px 16px;
}
.report-info { display: flex; gap: 12px; align-items: center; margin-bottom: 6px; }
.report-dept { font-weight: 600; color: #1f2937; }
.report-period { font-size: 0.85rem; color: #6b7280; }
.report-meta { display: flex; gap: 10px; align-items: center; margin-bottom: 8px; }
.report-date { font-size: 0.82rem; color: #9ca3af; }
.report-status { font-size: 0.8rem; padding: 2px 8px; border-radius: 10px; }
.status-submitted { background: #dbeafe; color: #1e40af; }
.status-reviewed  { background: #d1fae5; color: #065f46; }
.status-approved  { background: #dcfce7; color: #166534; }

.review-section { display: flex; gap: 8px; align-items: flex-end; }
.review-input {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.88rem;
  resize: none;
  font-family: inherit;
}
.btn-review {
  padding: 8px 16px;
  background: #1e3a5f;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.85rem;
  cursor: pointer;
  font-weight: 500;
  white-space: nowrap;
}
.btn-review:disabled { opacity: 0.5; cursor: not-allowed; }
.reviewed-note { font-size: 0.85rem; color: #374151; background: #f0fdf4; padding: 8px 12px; border-radius: 8px; }
.note-label { font-weight: 600; }

/* Empty */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}
.empty-icon { font-size: 3rem; margin-bottom: 12px; }
.empty-state h2 { font-size: 1.3rem; color: #374151; margin-bottom: 8px; }
.empty-mini { color: #9ca3af; font-size: 0.9rem; padding: 20px; text-align: center; }
</style>
