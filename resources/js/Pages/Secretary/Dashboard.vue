<template>
  <div class="secretary-dashboard">

    <!-- Header -->
    <div class="portal-header">
      <div class="header-badge">TK</div>
      <div>
        <h1>📋 Cổng Thư Ký Hội Thánh</h1>
        <p class="subtitle">Điểm danh · Truyền thông · Báo cáo tháng</p>
      </div>
      <div class="header-nav">
        <a :href="route('secretary.attendance.index')" class="nav-btn">📝 Điểm Danh CN</a>
        <a :href="route('secretary.social-stats.index')" class="nav-btn">📡 MXH</a>
        <a :href="route('secretary.report', { month: localMonth, year: localYear })" class="nav-btn nav-btn-report">📄 Báo Cáo Tháng</a>
      </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="stats-row">
      <div class="stat-card stat-blue">
        <div class="stat-icon">🗓</div>
        <div>
          <div class="stat-val">{{ stats.sundays_this_month }}</div>
          <div class="stat-label">CN tháng này</div>
        </div>
      </div>
      <div class="stat-card stat-green">
        <div class="stat-icon">✅</div>
        <div>
          <div class="stat-val">{{ stats.attendance_recorded }}</div>
          <div class="stat-label">Buổi đã ghi điểm danh</div>
        </div>
      </div>
      <div class="stat-card stat-indigo" v-if="latestAttendance">
        <div class="stat-icon">👥</div>
        <div>
          <div class="stat-val">{{ latestAttendance.total_present }}</div>
          <div class="stat-label">CN tuần gần nhất</div>
        </div>
      </div>
      <div class="stat-card stat-purple">
        <div class="stat-icon">📡</div>
        <div>
          <div class="stat-val">{{ stats.platforms_tracked }}</div>
          <div class="stat-label">Nền tảng MXH đang theo dõi</div>
        </div>
      </div>
    </div>

    <!-- Month Report Section -->
    <div class="month-report-card">
      <!-- Navigator -->
      <div class="month-nav">
        <button @click="goMonth(-1)" class="nav-arrow">‹</button>
        <div class="month-title">
          📅 Tháng {{ localMonth }}/{{ localYear }}
        </div>
        <button @click="goMonth(1)" class="nav-arrow">›</button>
      </div>

      <!-- 2 column summary -->
      <div class="month-summary-grid">
        <!-- Điểm danh -->
        <div class="summary-block">
          <div class="summary-block-title">👥 Điểm Danh</div>
          <div v-if="month_summary.count > 0">
            <div class="summary-big">{{ month_summary.avg }}</div>
            <div class="summary-label">TB hiện diện / CN</div>
            <div class="summary-delta" :class="attendanceDelta >= 0 ? 'delta-up' : 'delta-down'">
              {{ attendanceDelta >= 0 ? '▲' : '▼' }} {{ Math.abs(attendanceDelta) }} so tháng trước
            </div>
            <div class="summary-detail">
              <span>Cao: <strong>{{ month_summary.max }}</strong> (CN{{ month_summary.max_date }})</span>
              <span>Thấp: <strong>{{ month_summary.min }}</strong> (CN{{ month_summary.min_date }})</span>
              <span>Ghi: {{ month_summary.count }}/{{ stats.sundays_this_month }} buổi</span>
            </div>
          </div>
          <div v-else class="summary-empty">Chưa có dữ liệu tháng này</div>
        </div>

        <!-- YouTube -->
        <div class="summary-block">
          <div class="summary-block-title">📺 YouTube</div>
          <template v-if="youtubeLatest">
            <div class="yt-metrics">
              <div class="yt-row" v-if="youtubeLatest.subscribers !== null">
                <span class="yt-label">Đăng ký tích lũy</span>
                <div class="yt-val-wrap">
                  <strong class="yt-val">{{ youtubeLatest.subscribers?.toLocaleString('vi-VN') }}</strong>
                  <span v-if="ytDelta.subscribers !== null"
                    class="yt-delta"
                    :class="ytDelta.subscribers >= 0 ? 'delta-up' : 'delta-down'"
                  >
                    {{ ytDelta.subscribers >= 0 ? '+' : '' }}{{ ytDelta.subscribers }} mới
                  </span>
                </div>
              </div>
              <div class="yt-row" v-if="youtubeLatest.views !== null">
                <span class="yt-label">Lượt xem 28 ngày</span>
                <div class="yt-val-wrap">
                  <strong class="yt-val">{{ youtubeLatest.views?.toLocaleString('vi-VN') }}</strong>
                  <span v-if="ytDelta.views !== null"
                    class="yt-delta"
                    :class="ytDelta.views >= 0 ? 'delta-up' : 'delta-down'"
                  >
                    {{ ytDelta.views >= 0 ? '▲' : '▼' }} {{ Math.abs(ytDelta.views) }}
                  </span>
                </div>
              </div>
              <div class="yt-row" v-if="youtubeLatest.watch_hours !== null">
                <span class="yt-label">Giờ xem</span>
                <strong class="yt-val">{{ youtubeLatest.watch_hours }}h</strong>
              </div>
            </div>
          </template>
          <div v-else class="summary-empty">Chưa nhập số liệu YouTube</div>
          <a :href="route('secretary.social-stats.index')" class="view-link">Nhập / Xem chi tiết →</a>
        </div>
      </div>

      <!-- Ghi chú tháng -->
      <div class="month-notes-section">
        <div class="notes-grid">
          <div class="note-block">
            <label class="note-label">📢 Thông Báo / Đề Nghị / Góp Ý</label>
            <textarea
              v-model="noteForm.announcements"
              class="note-textarea"
              rows="3"
              placeholder="Góp ý, thông báo từ buổi họp Ban Chấp Sự..."
            ></textarea>
          </div>
          <div class="note-block">
            <label class="note-label">📆 Kế Hoạch Tháng Tới</label>
            <textarea
              v-model="noteForm.next_plan"
              class="note-textarea"
              rows="3"
              placeholder="Đại Hội Đồng, lễ đặc biệt, sự kiện..."
            ></textarea>
          </div>
        </div>
        <div class="notes-actions">
          <div v-if="noteSaved" class="note-flash">✅ Đã lưu ghi chú</div>
          <button @click="saveNote" :disabled="noteSaving" class="btn-save-note">
            {{ noteSaving ? 'Đang lưu...' : '💾 Lưu Ghi Chú Tháng' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Content Grid: Chart + Recent -->
    <div class="content-grid">
      <!-- Chart -->
      <div class="chart-panel">
        <h2 class="panel-title">📈 Xu Hướng Điểm Danh (8 tuần)</h2>
        <div v-if="chart_data.length" class="mini-chart">
          <div v-for="(pt, i) in chart_data" :key="i" class="bar-col">
            <div class="bar-stack">
              <div class="bar-seg bar-total" :style="{ height: barPx(pt.total) + 'px' }" :title="'Tổng: ' + pt.total"></div>
            </div>
            <div class="bar-label">{{ pt.date }}</div>
            <div class="bar-total-n">{{ pt.total }}</div>
          </div>
        </div>
        <div v-else class="empty-chart">Chưa có dữ liệu điểm danh</div>
      </div>

      <!-- Right panels -->
      <div class="right-panels">
        <!-- Recent incidents -->
        <div class="incident-panel" v-if="recentIncidents.length">
          <h2 class="panel-title">⚠️ Sự Cố Gần Đây</h2>
          <div v-for="rec in recentIncidents" :key="rec.id" class="incident-row">
            <div class="incident-date">{{ rec.meeting_date }}</div>
            <div class="incident-text">{{ rec.incident_note }}</div>
          </div>
        </div>

        <!-- Recent attendance -->
        <div class="recent-panel">
          <h2 class="panel-title">🗂 Điểm Danh Gần Đây</h2>
          <div v-if="!attendance_history.length" class="empty-msg">Chưa có dữ liệu</div>
          <div v-for="rec in attendance_history.slice(0, 5)" :key="rec.id" class="recent-row">
            <div class="recent-date">{{ rec.meeting_date }}</div>
            <div class="recent-chips">
              <span class="chip chip-total">👥 {{ rec.total_present }}</span>
              <span v-if="rec.dept_total > 0" class="chip chip-dept" :title="'Ban: ' + rec.dept_total">🏛 {{ rec.dept_total }}</span>
              <span class="chip chip-m">♂ {{ rec.total_male }}</span>
              <span class="chip chip-f">♀ {{ rec.total_female }}</span>
              <span v-if="rec.total_children" class="chip chip-c">🧒 {{ rec.total_children }}</span>
            </div>
          </div>
          <a :href="route('secretary.attendance.index')" class="view-all-link">Nhập / xem tất cả →</a>
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
  attendance_history: { type: Array,  default: () => [] },
  chart_data:         { type: Array,  default: () => [] },
  social_latest:      { type: Object, default: () => ({}) },
  month_summary:      { type: Object, default: () => ({}) },
  prev_summary:       { type: Object, default: () => ({}) },
  month_note:         { type: Object, default: () => ({}) },
  stats:              { type: Object, default: () => ({}) },
  filters:            { type: Object, default: () => ({}) },
});

const localMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const localYear  = ref(props.filters?.year  || new Date().getFullYear());

// Note form
const noteForm  = ref({ announcements: props.month_note?.announcements || '', next_plan: props.month_note?.next_plan || '' });
const noteSaving = ref(false);
const noteSaved  = ref(false);

// ── Computed ──────────────────────────────────────────────────
const latestAttendance = computed(() => props.attendance_history[0] ?? null);

const recentIncidents = computed(() =>
  props.attendance_history.filter(r => r.incident_note).slice(0, 3)
);

const attendanceDelta = computed(() => {
  const cur = props.month_summary?.avg || 0;
  const prv = props.prev_summary?.avg  || 0;
  return Math.round((cur - prv) * 10) / 10;
});

// YouTube metrics từ social_latest
const youtubeLatest = computed(() => {
  const yt = props.social_latest?.youtube;
  if (!yt) return null;
  const metrics = {};
  yt.metrics?.forEach(m => {
    if (m.count !== null) metrics[m.label.toLowerCase().replace(/ /g,'_')] = m.count;
  });
  // Map cụ thể
  const subs = yt.metrics?.find(m => m.label === 'Đăng ký')?.count ?? null;
  const views = yt.metrics?.find(m => m.label === 'Lượt xem')?.count ?? null;
  const hours = yt.metrics?.find(m => m.label === 'Giờ xem')?.count ?? null;
  if (subs === null && views === null) return null;
  return { subscribers: subs, views, watch_hours: hours };
});

// YouTube delta (placeholder — sẽ có prev_stats từ SocialStats page)
const ytDelta = computed(() => ({ subscribers: null, views: null }));

// Chart helpers
const maxTotal = computed(() => {
  if (!props.chart_data.length) return 1;
  return Math.max(...props.chart_data.map(p => p.total), 1);
});
function barPx(count) {
  return Math.round(((count || 0) / maxTotal.value) * 80);
}

// Month navigation
function goMonth(delta) {
  let m = localMonth.value + delta;
  let y = localYear.value;
  if (m < 1)  { m = 12; y--; }
  if (m > 12) { m = 1;  y++; }
  localMonth.value = m;
  localYear.value  = y;
  router.get(route('secretary.dashboard'), { month: m, year: y }, { preserveState: false, replace: true });
}

// Save note
function saveNote() {
  noteSaving.value = true;
  router.post(route('secretary.month-note.store'), {
    month: localMonth.value,
    year:  localYear.value,
    announcements: noteForm.value.announcements,
    next_plan:     noteForm.value.next_plan,
  }, {
    preserveScroll: true,
    onSuccess: () => { noteSaved.value = true; setTimeout(() => noteSaved.value = false, 3000); },
    onFinish:  () => noteSaving.value = false,
  });
}
</script>

<style scoped>
.secretary-dashboard {
  max-width: 1280px;
  margin: 0 auto;
  padding: 24px 16px;
  font-family: 'Inter', sans-serif;
}

/* ── Header ─────────────────────────────────── */
.portal-header {
  display: flex; align-items: center; gap: 16px;
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: white; border-radius: 20px; padding: 22px 28px;
  margin-bottom: 20px; flex-wrap: wrap;
  box-shadow: 0 8px 24px rgba(79,70,229,0.2);
}
.header-badge {
  font-size: 1rem; font-weight: 800;
  background: rgba(255,255,255,0.2);
  border: 2px solid rgba(255,255,255,0.4);
  border-radius: 10px; padding: 6px 12px; letter-spacing: 1px;
}
.portal-header h1 { margin: 0; font-size: 1.4rem; font-weight: 800; }
.subtitle { margin: 4px 0 0; opacity: 0.8; font-size: 0.88rem; }
.header-nav { margin-left: auto; display: flex; gap: 10px; flex-wrap: wrap; }
.nav-btn {
  padding: 8px 16px; background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.3); color: white;
  border-radius: 10px; text-decoration: none; font-size: 0.88rem;
  font-weight: 600; transition: background 0.15s;
}
.nav-btn:hover { background: rgba(255,255,255,0.25); }
.nav-btn-report {
  background: rgba(255,255,255,0.25);
  border-color: rgba(255,255,255,0.5);
  font-weight: 700;
}

/* ── Stats Row ──────────────────────────────── */
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
.stat-card {
  background: white; border-radius: 14px; padding: 18px;
  display: flex; align-items: center; gap: 12px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.stat-icon { font-size: 1.8rem; }
.stat-val   { font-size: 1.8rem; font-weight: 800; line-height: 1; }
.stat-label { font-size: 0.75rem; color: #6b7280; margin-top: 3px; }
.stat-blue .stat-val   { color: #1e40af; }
.stat-green .stat-val  { color: #065f46; }
.stat-purple .stat-val { color: #7c3aed; }
.stat-indigo .stat-val { color: #4338ca; }
@media (max-width: 900px) { .stats-row { grid-template-columns: 1fr 1fr; } }

/* ── Month Report Card ──────────────────────── */
.month-report-card {
  background: white; border: 1px solid #e5e7eb;
  border-radius: 20px; padding: 24px;
  margin-bottom: 20px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.05);
}

.month-nav {
  display: flex; align-items: center; justify-content: center;
  gap: 20px; margin-bottom: 20px;
}
.month-title { font-size: 1.1rem; font-weight: 800; color: #1f2937; }
.nav-arrow {
  width: 36px; height: 36px; border-radius: 50%;
  background: #f3f4f6; border: 1px solid #e5e7eb;
  font-size: 1.2rem; cursor: pointer; display: flex;
  align-items: center; justify-content: center;
  transition: background 0.15s;
}
.nav-arrow:hover { background: #e0e7ff; }

.month-summary-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 20px; margin-bottom: 20px;
}
@media (max-width: 700px) { .month-summary-grid { grid-template-columns: 1fr; } }

.summary-block {
  background: #f9fafb; border: 1px solid #e5e7eb;
  border-radius: 14px; padding: 18px;
}
.summary-block-title {
  font-size: 0.8rem; font-weight: 800; color: #6b7280;
  text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;
}
.summary-big   { font-size: 2.5rem; font-weight: 900; color: #1f2937; line-height: 1; }
.summary-label { font-size: 0.78rem; color: #6b7280; margin-top: 4px; }
.summary-delta {
  font-size: 0.85rem; font-weight: 700; margin-top: 8px;
}
.delta-up   { color: #059669; }
.delta-down { color: #dc2626; }
.summary-detail {
  display: flex; flex-wrap: wrap; gap: 10px;
  margin-top: 10px; font-size: 0.78rem; color: #374151;
}
.summary-empty { color: #9ca3af; font-size: 0.85rem; font-style: italic; }

/* YouTube metrics */
.yt-metrics { display: flex; flex-direction: column; gap: 10px; }
.yt-row { display: flex; justify-content: space-between; align-items: center; }
.yt-label { font-size: 0.8rem; color: #6b7280; }
.yt-val-wrap { display: flex; align-items: center; gap: 8px; }
.yt-val { font-size: 1rem; font-weight: 700; color: #1f2937; }
.yt-delta { font-size: 0.75rem; font-weight: 700; padding: 2px 6px; border-radius: 6px; }
.view-link {
  display: block; text-align: right; font-size: 0.8rem;
  color: #6366f1; text-decoration: none; margin-top: 12px; font-weight: 500;
}
.view-link:hover { text-decoration: underline; }

/* Notes section */
.month-notes-section {
  border-top: 1px solid #f3f4f6; padding-top: 20px;
}
.notes-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 12px; }
@media (max-width: 700px) { .notes-grid { grid-template-columns: 1fr; } }
.note-block { display: flex; flex-direction: column; gap: 6px; }
.note-label { font-size: 0.78rem; font-weight: 700; color: #374151; }
.note-textarea {
  border: 1.5px solid #d1d5db; border-radius: 10px;
  padding: 10px 12px; font-size: 0.88rem; color: #1f2937;
  resize: vertical; outline: none; transition: border-color 0.15s;
  font-family: inherit;
}
.note-textarea:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.1); }
.notes-actions { display: flex; align-items: center; gap: 12px; justify-content: flex-end; }
.note-flash { font-size: 0.85rem; color: #065f46; font-weight: 600; }
.btn-save-note {
  background: linear-gradient(135deg, #4f46e5, #7c3aed);
  color: white; border: none; border-radius: 10px;
  padding: 10px 20px; font-size: 0.9rem; font-weight: 700;
  cursor: pointer; transition: opacity 0.15s;
  box-shadow: 0 4px 12px rgba(79,70,229,0.25);
}
.btn-save-note:hover { opacity: 0.9; }
.btn-save-note:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Content Grid ───────────────────────────── */
.content-grid {
  display: grid; grid-template-columns: 1fr 360px; gap: 20px;
}
@media (max-width: 900px) { .content-grid { grid-template-columns: 1fr; } }
.chart-panel, .recent-panel, .incident-panel {
  background: white; border: 1px solid #e5e7eb;
  border-radius: 16px; padding: 20px;
}
.right-panels { display: flex; flex-direction: column; gap: 16px; }
.panel-title { font-size: 1rem; font-weight: 700; color: #1f2937; margin: 0 0 16px; }

/* Chart */
.mini-chart { display: flex; align-items: flex-end; gap: 6px; height: 90px; margin-bottom: 10px; }
.bar-col { display: flex; flex-direction: column; align-items: center; gap: 2px; flex: 1; }
.bar-stack { display: flex; flex-direction: column-reverse; align-items: center; width: 100%; }
.bar-seg { width: 100%; min-height: 2px; border-radius: 3px 3px 0 0; transition: height 0.3s; }
.bar-total { background: linear-gradient(180deg, #4f46e5, #818cf8); }
.bar-label   { font-size: 0.62rem; color: #9ca3af; }
.bar-total-n { font-size: 0.68rem; font-weight: 700; color: #374151; }
.empty-chart { text-align: center; color: #9ca3af; font-size: 0.9rem; padding: 30px; }

/* Incidents */
.incident-row { border-bottom: 1px solid #fef3c7; padding: 8px 0; }
.incident-row:last-child { border-bottom: none; }
.incident-date { font-size: 0.78rem; font-weight: 700; color: #92400e; margin-bottom: 3px; }
.incident-text { font-size: 0.82rem; color: #374151; }

/* Recent */
.recent-row { border-bottom: 1px solid #f3f4f6; padding: 10px 0; }
.recent-row:last-of-type { border-bottom: none; }
.recent-date { font-size: 0.82rem; font-weight: 700; color: #1f2937; margin-bottom: 6px; }
.recent-chips { display: flex; flex-wrap: wrap; gap: 5px; }
.chip { font-size: 0.72rem; padding: 2px 7px; border-radius: 10px; font-weight: 600; }
.chip-total { background: #e0e7ff; color: #3730a3; }
.chip-dept  { background: #fef3c7; color: #92400e; }
.chip-m     { background: #dbeafe; color: #1e40af; }
.chip-f     { background: #fce7f3; color: #9d174d; }
.chip-c     { background: #d1fae5; color: #065f46; }
.empty-msg { text-align: center; color: #9ca3af; font-size: 0.9rem; padding: 16px; }
.view-all-link {
  display: block; text-align: right; font-size: 0.8rem;
  color: #6366f1; text-decoration: none; margin-top: 10px; font-weight: 500;
}
.view-all-link:hover { text-decoration: underline; }
</style>
