<template>
  <div class="ranking-page">
    <!-- Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="back-btn" @click="$inertia.visit(route('ministry.education.sessions', edu_class.id))">← Danh sách buổi học</div>
        <h1>🏆 Bảng Xếp Hạng</h1>
        <p class="subtitle">{{ edu_class.name }} · Xếp theo điểm trung bình quiz</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
      <div class="filter-group">
        <button
          v-for="f in filterOptions"
          :key="f.value"
          class="filter-btn"
          :class="{ active: currentFilter === f.value }"
          @click="applyFilter(f.value)"
        >{{ f.label }}</button>
      </div>

      <div class="filter-meta">
        <span class="sessions-count">📚 Tổng cộng: {{ total_sessions }} buổi học</span>
      </div>
    </div>

    <!-- Empty -->
    <div v-if="rankings.length === 0" class="empty-state">
      <div class="empty-icon">📊</div>
      <h2>Chưa có dữ liệu xếp hạng</h2>
      <p>Bắt đầu nhập điểm quiz cho các buổi học để xem bảng xếp hạng.</p>
    </div>

    <!-- Ranking Table -->
    <template v-else>
      <!-- Top 3 Podium -->
      <div class="podium" v-if="rankings.length >= 3">
        <div class="podium-slot podium-2" v-if="rankings[1]">
          <div class="podium-medal">🥈</div>
          <div class="podium-name">{{ rankings[1].full_name }}</div>
          <div class="podium-score">{{ rankings[1].avg_score ?? '—' }}</div>
          <div class="podium-attendance">{{ rankings[1].sessions_present }}/{{ rankings[1].sessions_total }} buổi</div>
          <div class="podium-base podium-base-2">2</div>
        </div>
        <div class="podium-slot podium-1" v-if="rankings[0]">
          <div class="podium-medal">🥇</div>
          <div class="podium-name">{{ rankings[0].full_name }}</div>
          <div class="podium-score">{{ rankings[0].avg_score ?? '—' }}</div>
          <div class="podium-attendance">{{ rankings[0].sessions_present }}/{{ rankings[0].sessions_total }} buổi</div>
          <div class="podium-base podium-base-1">1</div>
        </div>
        <div class="podium-slot podium-3" v-if="rankings[2]">
          <div class="podium-medal">🥉</div>
          <div class="podium-name">{{ rankings[2].full_name }}</div>
          <div class="podium-score">{{ rankings[2].avg_score ?? '—' }}</div>
          <div class="podium-attendance">{{ rankings[2].sessions_present }}/{{ rankings[2].sessions_total }} buổi</div>
          <div class="podium-base podium-base-3">3</div>
        </div>
      </div>

      <!-- Full Table -->
      <div class="ranking-table-wrap">
        <table class="ranking-table">
          <thead>
            <tr>
              <th class="rank-col">Hạng</th>
              <th>Tên</th>
              <th class="center">Đi Học</th>
              <th class="center">Tỷ Lệ</th>
              <th class="center">Thuộc Câu</th>
              <th class="center">Điểm TB</th>
              <th class="center">Tổng Điểm</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="row in rankings"
              :key="row.member_id"
              class="ranking-row"
              :class="{ 'top3': row.is_top3 }"
            >
              <td class="rank-cell">
                <span v-if="row.medal === 'gold'"   class="medal">🥇</span>
                <span v-else-if="row.medal === 'silver'" class="medal">🥈</span>
                <span v-else-if="row.medal === 'bronze'" class="medal">🥉</span>
                <span v-else class="rank-num">{{ row.rank }}</span>
              </td>
              <td class="name-cell">{{ row.full_name }}</td>
              <td class="center">
                <span class="attendance-pill">{{ row.sessions_present }}/{{ row.sessions_total }}</span>
              </td>
              <td class="center">
                <div class="rate-bar-wrap">
                  <div class="rate-bar" :style="{ width: row.attendance_rate + '%' }"></div>
                  <span class="rate-text">{{ row.attendance_rate }}%</span>
                </div>
              </td>
              <td class="center">
                <span class="verse-count" :class="{ 'verse-good': row.verses_memorized > 0 }">
                  {{ row.verses_memorized }}
                </span>
              </td>
              <td class="center score-cell">
                <span v-if="row.avg_score !== null" class="score-badge" :class="scoreClass(row.avg_score)">
                  {{ row.avg_score }}
                </span>
                <span v-else class="no-score">—</span>
              </td>
              <td class="center">
                <span class="total-score">{{ row.total_score }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Legend -->
      <div class="legend">
        <span>📊 Xếp theo điểm trung bình (Avg Score) — Điểm cao + Đi học đều = xếp hạng tốt</span>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  edu_class:      { type: Object, required: true },
  rankings:       { type: Array, default: () => [] },
  total_sessions: { type: Number, default: 0 },
  filter:         { type: String, default: 'all' },
  month:          { type: Number, default: null },
  year:           { type: Number, default: null },
  route_prefix:   { type: String, default: 'ministry' },
})

const currentFilter = ref(props.filter)

const filterOptions = computed(() => {
  const opts = [
    { value: 'all',   label: 'Tất Cả' },
    { value: 'month', label: 'Tháng Này' },
  ]
  if (props.edu_class.is_seasonal) {
    opts.push({ value: 'season', label: `📅 ${props.edu_class.season_name || 'Mùa Học Này'}` })
  }
  return opts
})

function applyFilter(filterVal) {
  currentFilter.value = filterVal
  router.get(route('ministry.education.ranking', props.edu_class.id), {
    filter: filterVal,
    month: props.month,
    year: props.year,
  }, { preserveState: false })
}

function scoreClass(score) {
  if (score >= 9)  return 'score-excellent'
  if (score >= 7)  return 'score-good'
  if (score >= 5)  return 'score-average'
  return 'score-poor'
}
</script>

<style scoped>
.ranking-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 24px 16px;
  font-family: 'Inter', sans-serif;
}

/* Header */
.page-header {
  background: linear-gradient(135deg, #7c3aed, #4f46e5);
  color: white;
  border-radius: 14px;
  padding: 22px 28px;
  margin-bottom: 24px;
}
.back-btn { font-size: 0.82rem; opacity: 0.8; cursor: pointer; margin-bottom: 8px; }
.back-btn:hover { opacity: 1; }
.page-header h1 { margin: 0; font-size: 1.5rem; }
.subtitle { margin: 4px 0 0; opacity: 0.8; font-size: 0.9rem; }

/* Filters */
.filter-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
.filter-group { display: flex; gap: 8px; }
.filter-btn {
  padding: 7px 16px;
  border: 1.5px solid #d1d5db;
  border-radius: 20px;
  background: white;
  font-size: 0.85rem;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
}
.filter-btn.active { background: #7c3aed; color: white; border-color: #7c3aed; }
.sessions-count { font-size: 0.85rem; color: #6b7280; }

/* Podium */
.podium {
  display: flex;
  justify-content: center;
  align-items: flex-end;
  gap: 16px;
  margin-bottom: 28px;
  padding: 20px;
  background: linear-gradient(135deg, #f5f3ff, #ede9fe);
  border-radius: 16px;
}
.podium-slot { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.podium-medal { font-size: 2rem; }
.podium-name { font-size: 0.88rem; font-weight: 600; color: #1f2937; text-align: center; max-width: 100px; }
.podium-score { font-size: 1.1rem; font-weight: 700; color: #7c3aed; }
.podium-attendance { font-size: 0.72rem; color: #6b7280; }
.podium-base {
  width: 70px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  font-weight: 700;
  color: white;
  border-radius: 6px 6px 0 0;
}
.podium-base-1 { background: #f59e0b; height: 70px; }
.podium-base-2 { background: #94a3b8; height: 50px; }
.podium-base-3 { background: #b45309; height: 35px; }

/* Table */
.ranking-table-wrap { overflow-x: auto; margin-bottom: 16px; }
.ranking-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.ranking-table th {
  padding: 10px 12px;
  border-bottom: 2px solid #e5e7eb;
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #6b7280;
  font-weight: 600;
  text-align: left;
  background: #f9fafb;
}
.ranking-table th.center { text-align: center; }
.ranking-row td {
  padding: 12px;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: middle;
}
.ranking-row:hover { background: #fafafa; }
.ranking-row.top3 { background: linear-gradient(90deg, #faf5ff, white); }

.rank-col { width: 50px; text-align: center; }
.rank-cell { text-align: center; }
.medal { font-size: 1.3rem; }
.rank-num { font-weight: 700; color: #6b7280; font-size: 1rem; }
.name-cell { font-weight: 500; color: #1f2937; }
.center { text-align: center; }

.attendance-pill {
  font-size: 0.82rem;
  background: #e5e7eb;
  padding: 2px 8px;
  border-radius: 10px;
}

.rate-bar-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  justify-content: center;
}
.rate-bar {
  height: 6px;
  background: linear-gradient(90deg, #7c3aed, #4f46e5);
  border-radius: 3px;
  min-width: 4px;
  max-width: 60px;
}
.rate-text { font-size: 0.78rem; color: #374151; min-width: 36px; }

.verse-count { font-size: 0.88rem; color: #6b7280; }
.verse-good  { color: #059669; font-weight: 600; }

.score-badge { font-size: 0.88rem; font-weight: 700; padding: 3px 8px; border-radius: 8px; }
.score-excellent { background: #d1fae5; color: #065f46; }
.score-good      { background: #dbeafe; color: #1e40af; }
.score-average   { background: #fef9c3; color: #713f12; }
.score-poor      { background: #fef2f2; color: #991b1b; }
.no-score { color: #d1d5db; }

.total-score { font-weight: 600; color: #374151; }

.legend {
  text-align: center;
  font-size: 0.8rem;
  color: #9ca3af;
  padding: 12px;
}

/* Empty */
.empty-state { text-align: center; padding: 60px; color: #6b7280; }
.empty-icon { font-size: 3rem; margin-bottom: 12px; }
.empty-state h2 { font-size: 1.2rem; color: #374151; }
</style>
