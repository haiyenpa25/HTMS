<template>
  <div class="secretary-dashboard">
    <!-- Header -->
    <div class="portal-header">
      <div class="header-badge">P2</div>
      <div>
        <h1>📋 Cổng Thư Ký Hội Thánh</h1>
        <p class="subtitle">Điểm danh · Truyền thông xã hội · Nội vụ</p>
      </div>
      <div class="header-nav">
        <a :href="route('secretary.attendance.index')" class="nav-btn">📝 Điểm Danh CN</a>
        <a :href="route('secretary.social-stats.index')" class="nav-btn">📊 MXH</a>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
      <div class="stat-card stat-blue">
        <div class="stat-icon">🗓</div>
        <div>
          <div class="stat-val">{{ stats.sundays_this_month }}</div>
          <div class="stat-label">Chủ Nhật tháng này</div>
        </div>
      </div>
      <div class="stat-card stat-green">
        <div class="stat-icon">✅</div>
        <div>
          <div class="stat-val">{{ stats.attendance_recorded }}</div>
          <div class="stat-label">Buổi đã ghi điểm danh</div>
        </div>
      </div>
      <div class="stat-card stat-purple">
        <div class="stat-icon">📡</div>
        <div>
          <div class="stat-val">{{ stats.platforms_tracked }}</div>
          <div class="stat-label">Nền tảng MXH đang theo dõi</div>
        </div>
      </div>
      <div class="stat-card stat-indigo" v-if="latestAttendance">
        <div class="stat-icon">👥</div>
        <div>
          <div class="stat-val">{{ latestAttendance.total_present }}</div>
          <div class="stat-label">Người CN tuần gần nhất</div>
        </div>
      </div>
    </div>

    <div class="content-grid">
      <!-- Left: Chart -->
      <div class="chart-panel">
        <h2 class="panel-title">📈 Xu Hướng Điểm Danh (8 tuần)</h2>
        <div v-if="chart_data.length" class="mini-chart">
          <div v-for="(pt, i) in chart_data" :key="i" class="bar-col">
            <div class="bar-stack">
              <div class="bar-seg bar-children" :style="{ height: barPx(pt.children) + 'px' }" :title="'Thiếu nhi: ' + pt.children"></div>
              <div class="bar-seg bar-female"   :style="{ height: barPx(pt.female)   + 'px' }" :title="'Nữ: ' + pt.female"></div>
              <div class="bar-seg bar-male"     :style="{ height: barPx(pt.male)     + 'px' }" :title="'Nam: ' + pt.male"></div>
            </div>
            <div class="bar-label">{{ pt.date }}</div>
            <div class="bar-total">{{ pt.total }}</div>
          </div>
        </div>
        <div v-else class="empty-chart">Chưa có dữ liệu điểm danh</div>

        <!-- Legend -->
        <div class="chart-legend">
          <span class="leg-item"><span class="leg-dot" style="background:#1e3a5f"></span>Nam</span>
          <span class="leg-item"><span class="leg-dot" style="background:#db2777"></span>Nữ</span>
          <span class="leg-item"><span class="leg-dot" style="background:#0891b2"></span>Thiếu Nhi</span>
        </div>
      </div>

      <!-- Right: Social & History -->
      <div class="right-panels">
        <!-- Social snapshot -->
        <div class="social-panel">
          <h2 class="panel-title">📡 MXH Snapshot</h2>
          <div v-for="(pdata, pkey) in social_latest" :key="pkey" class="social-row">
            <div class="social-platform">{{ pdata.label }}</div>
            <div class="social-metrics">
              <span
                v-for="m in pdata.metrics.filter(m => m.count !== null)"
                :key="m.label"
                class="social-chip"
              >
                {{ m.label }}: <strong>{{ m.count?.toLocaleString() }}</strong>
              </span>
              <span v-if="!pdata.metrics.filter(m => m.count !== null).length" class="no-data">
                Chưa có dữ liệu
              </span>
            </div>
          </div>
          <a :href="route('secretary.social-stats.index')" class="view-all-link">Nhập / xem chi tiết →</a>
        </div>

        <!-- Recent attendance -->
        <div class="recent-panel">
          <h2 class="panel-title">🗂 Điểm Danh Gần Đây</h2>
          <div v-if="!attendance_history.length" class="empty-msg">Chưa có dữ liệu</div>
          <div v-for="rec in attendance_history.slice(0, 5)" :key="rec.id" class="recent-row">
            <div class="recent-date">{{ rec.meeting_date }}</div>
            <div class="recent-chips">
              <span class="chip chip-total">👥 {{ rec.total_present }}</span>
              <span class="chip chip-m">♂ {{ rec.total_male }}</span>
              <span class="chip chip-f">♀ {{ rec.total_female }}</span>
              <span v-if="rec.total_children" class="chip chip-c">🧒 {{ rec.total_children }}</span>
              <span v-if="rec.guests_count" class="chip chip-g">🙋 {{ rec.guests_count }}</span>
            </div>
          </div>
          <a :href="route('secretary.attendance.index')" class="view-all-link">Nhập / xem tất cả →</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { route } from 'ziggy-js'

const props = defineProps({
  attendance_history: { type: Array, default: () => [] },
  chart_data:         { type: Array, default: () => [] },
  social_latest:      { type: Object, default: () => ({}) },
  stats:              { type: Object, default: () => ({}) },
})

const latestAttendance = computed(() => props.attendance_history[0] ?? null)

const maxTotal = computed(() => {
  if (!props.chart_data.length) return 1
  return Math.max(...props.chart_data.map(p => p.total), 1)
})

function barPx(count) {
  return Math.round(((count || 0) / maxTotal.value) * 80)
}
</script>

<style scoped>
.secretary-dashboard {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 16px;
  font-family: 'Inter', sans-serif;
}

/* Header */
.portal-header {
  display: flex;
  align-items: center;
  gap: 16px;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: white;
  border-radius: 16px;
  padding: 22px 28px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}
.header-badge {
  font-size: 1.1rem;
  font-weight: 800;
  background: rgba(255,255,255,0.2);
  border: 2px solid rgba(255,255,255,0.4);
  border-radius: 10px;
  padding: 6px 12px;
  letter-spacing: 1px;
}
.portal-header h1 { margin: 0; font-size: 1.4rem; }
.subtitle { margin: 4px 0 0; opacity: 0.8; font-size: 0.88rem; }
.header-nav { margin-left: auto; display: flex; gap: 10px; flex-wrap: wrap; }
.nav-btn {
  padding: 8px 16px;
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.3);
  color: white;
  border-radius: 10px;
  text-decoration: none;
  font-size: 0.88rem;
  font-weight: 500;
  transition: background 0.15s;
}
.nav-btn:hover { background: rgba(255,255,255,0.25); }

/* Stats Row */
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
.stat-card {
  background: white;
  border-radius: 12px;
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.stat-icon { font-size: 1.8rem; }
.stat-val { font-size: 1.8rem; font-weight: 700; line-height: 1; }
.stat-label { font-size: 0.75rem; color: #6b7280; margin-top: 3px; }
.stat-blue .stat-val   { color: #1e40af; }
.stat-green .stat-val  { color: #065f46; }
.stat-purple .stat-val { color: #7c3aed; }
.stat-indigo .stat-val { color: #4338ca; }

/* Content Grid */
.content-grid { display: grid; grid-template-columns: 1fr 380px; gap: 20px; }
.chart-panel, .social-panel, .recent-panel {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 20px;
}
.panel-title { font-size: 1rem; font-weight: 600; color: #1f2937; margin: 0 0 16px; }

/* Bar Chart */
.mini-chart { display: flex; align-items: flex-end; gap: 8px; height: 100px; margin-bottom: 10px; }
.bar-col { display: flex; flex-direction: column; align-items: center; gap: 2px; flex: 1; }
.bar-stack { display: flex; flex-direction: column-reverse; align-items: center; width: 100%; gap: 1px; }
.bar-seg { width: 100%; min-height: 2px; border-radius: 2px 2px 0 0; transition: height 0.3s; }
.bar-male     { background: #1e3a5f; }
.bar-female   { background: #db2777; }
.bar-children { background: #0891b2; }
.bar-label { font-size: 0.65rem; color: #9ca3af; }
.bar-total { font-size: 0.7rem; font-weight: 600; color: #374151; }
.empty-chart { text-align: center; color: #9ca3af; font-size: 0.9rem; padding: 30px; }
.chart-legend { display: flex; gap: 14px; justify-content: center; margin-top: 4px; }
.leg-item { font-size: 0.75rem; color: #6b7280; display: flex; align-items: center; gap: 5px; }
.leg-dot { width: 10px; height: 10px; border-radius: 2px; }

/* Right panels */
.right-panels { display: flex; flex-direction: column; gap: 16px; }

/* Social */
.social-row { border-bottom: 1px solid #f3f4f6; padding: 10px 0; }
.social-row:last-of-type { border-bottom: none; }
.social-platform { font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 5px; }
.social-metrics { display: flex; flex-wrap: wrap; gap: 6px; }
.social-chip { font-size: 0.75rem; background: #f3f4f6; padding: 2px 8px; border-radius: 10px; color: #374151; }
.no-data { font-size: 0.78rem; color: #9ca3af; font-style: italic; }

/* Recent */
.recent-row { border-bottom: 1px solid #f3f4f6; padding: 10px 0; }
.recent-row:last-of-type { border-bottom: none; }
.recent-date { font-size: 0.82rem; font-weight: 600; color: #1f2937; margin-bottom: 6px; }
.recent-chips { display: flex; flex-wrap: wrap; gap: 5px; }
.chip { font-size: 0.72rem; padding: 2px 7px; border-radius: 10px; font-weight: 500; }
.chip-total  { background: #e0e7ff; color: #3730a3; }
.chip-m      { background: #dbeafe; color: #1e40af; }
.chip-f      { background: #fce7f3; color: #9d174d; }
.chip-c      { background: #d1fae5; color: #065f46; }
.chip-g      { background: #fef9c3; color: #713f12; }
.empty-msg { text-align: center; color: #9ca3af; font-size: 0.9rem; padding: 16px; }

.view-all-link {
  display: block;
  text-align: right;
  font-size: 0.8rem;
  color: #6366f1;
  text-decoration: none;
  margin-top: 10px;
  font-weight: 500;
}
.view-all-link:hover { text-decoration: underline; }

@media (max-width: 900px) {
  .stats-row { grid-template-columns: 1fr 1fr; }
  .content-grid { grid-template-columns: 1fr; }
}
</style>
