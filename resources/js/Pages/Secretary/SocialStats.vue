<template>
  <div class="sec-social">

    <!-- Header -->
    <div class="sec-header">
      <div class="sec-header-icon">📡</div>
      <div class="sec-header-text">
        <h1>Số Liệu Mạng Xã Hội</h1>
        <p>Theo dõi tăng trưởng YouTube · Facebook · Zalo · Instagram</p>
      </div>
      <div class="sec-header-nav">
        <Link :href="route('secretary.dashboard')" class="btn-nav">← Dashboard</Link>
        <Link :href="route('secretary.attendance.index')" class="btn-nav">📝 Điểm Danh</Link>
      </div>
    </div>

    <!-- Snapshot cards -->
    <div class="snapshot-grid">
      <div
        v-for="(pLabel, pKey) in platforms"
        :key="pKey"
        class="snapshot-card"
        :class="'platform-' + pKey"
      >
        <div class="snapshot-platform">
          <span class="platform-emoji">{{ platformEmoji(pKey) }}</span>
          <span class="platform-name">{{ pLabel }}</span>
        </div>
        <div class="snapshot-metrics">
          <template v-for="(mLabel, mKey) in metrics" :key="mKey">
            <div v-if="getLatest(pKey, mKey) !== null" class="metric-chip">
              <div class="metric-val">{{ getLatest(pKey, mKey)?.toLocaleString('vi-VN') ?? '—' }}</div>
              <div class="metric-lbl">{{ mLabel }}</div>
            </div>
          </template>
          <div v-if="!hasAnyData(pKey)" class="no-data-msg">Chưa có dữ liệu</div>
        </div>
      </div>
    </div>

    <!-- Input Form + Chart -->
    <div class="social-main-grid">
      <!-- Form nhập liệu -->
      <div class="social-form-panel">
        <h2 class="panel-title">📥 Nhập Số Liệu Mới</h2>
        <form @submit.prevent="submitStats" class="social-form">
          <div class="form-group">
            <label class="form-label">Ngày ghi nhận *</label>
            <input type="date" v-model="form.recorded_date" class="form-input" required>
          </div>

          <!-- Matrix nhập liệu -->
          <div class="stats-matrix">
            <div v-for="(pLabel, pKey) in platforms" :key="pKey" class="matrix-platform">
              <div class="matrix-platform-header">
                <span>{{ platformEmoji(pKey) }}</span>
                <strong>{{ pLabel }}</strong>
              </div>
              <div class="matrix-metrics">
                <div v-for="(mLabel, mKey) in metrics" :key="mKey" class="matrix-metric">
                  <label class="matrix-label">{{ mLabel }}</label>
                  <input
                    type="number"
                    v-model.number="form.stats[pKey][mKey]"
                    class="form-input matrix-input"
                    min="0" placeholder="—"
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- Flash -->
          <div v-if="$page.props.flash?.success" class="flash-success">
            ✅ {{ $page.props.flash.success }}
          </div>

          <button type="submit" :disabled="submitting" class="btn-submit">
            <span v-if="submitting">Đang lưu...</span>
            <span v-else>💾 Lưu Số Liệu</span>
          </button>
        </form>
      </div>

      <!-- Chart trends -->
      <div class="social-chart-panel">
        <h2 class="panel-title">📈 Xu Hướng Tăng Trưởng</h2>

        <div v-for="(pLabel, pKey) in platforms" :key="pKey" class="trend-section">
          <h3 class="trend-title">{{ platformEmoji(pKey) }} {{ pLabel }}</h3>
          <div v-if="hasTrendData(pKey)" class="trend-chart">
            <div v-for="(mKey, idx) in trendMetrics" :key="mKey">
              <div v-if="getTrendData(pKey, mKey).length > 1" class="mini-trend">
                <div class="trend-metric-label">{{ metrics[mKey] }}</div>
                <div class="trend-bars">
                  <div
                    v-for="(pt, pi) in getTrendData(pKey, mKey)"
                    :key="pi"
                    class="trend-bar-col"
                  >
                    <div class="trend-bar" :style="{ height: trendBarPx(pKey, mKey, pt.count) + 'px' }" :title="pt.count?.toLocaleString('vi-VN')"></div>
                    <div class="trend-bar-date">{{ pt.date }}</div>
                  </div>
                </div>
                <div class="trend-minmax">
                  <span class="trend-latest">{{ getTrendData(pKey, mKey).at(-1)?.count?.toLocaleString('vi-VN') ?? '—' }} (mới nhất)</span>
                  <span v-if="getTrendGrowth(pKey, mKey) !== null" class="trend-growth" :class="getTrendGrowth(pKey, mKey) >= 0 ? 'growth-up' : 'growth-down'">
                    {{ getTrendGrowth(pKey, mKey) >= 0 ? '↑' : '↓' }} {{ Math.abs(getTrendGrowth(pKey, mKey)) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="trend-empty">Chưa đủ dữ liệu để vẽ xu hướng</div>
        </div>
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
  platforms:   { type: Object, default: () => ({}) },
  metrics:     { type: Object, default: () => ({}) },
  latest:      { type: Object, default: () => ({}) },
  chart_data:  { type: Object, default: () => ({}) },
});

// Emojis map
const emojiMap = { youtube: '🎬', facebook: '👍', zalo: '💬', instagram: '📸' };
function platformEmoji(key) { return emojiMap[key] || '📡'; }

// Latest value — props.latest[platform] là keyed Collection (keyed by metric)
function getLatest(platform, metric) {
  const data = props.latest[platform];
  if (!data) return null;
  // Laravel keyBy('metric') trả về object với key = metric
  const entry = data[metric];
  if (entry === undefined || entry === null) return null;
  return entry.count ?? null;
}

function hasAnyData(platform) {
  const data = props.latest[platform];
  if (!data) return false;
  return Object.values(data).some(e => e?.count !== null && e?.count !== undefined);
}

// Trend data
const trendMetrics = ['subscribers', 'followers', 'members'];

function getTrendData(platform, metric) {
  const d = props.chart_data?.[platform]?.[metric];
  if (!d || !Array.isArray(d)) return [];
  return d.map(row => ({
    count: row.count,
    date: row.recorded_date ? row.recorded_date.slice(5) : '', // MM-DD
  }));
}

function hasTrendData(platform) {
  return trendMetrics.some(m => getTrendData(platform, m).length > 0);
}

function trendBarMax(platform, metric) {
  const data = getTrendData(platform, metric);
  return data.length ? Math.max(...data.map(d => d.count || 0), 1) : 1;
}

function trendBarPx(platform, metric, count) {
  return Math.round(((count || 0) / trendBarMax(platform, metric)) * 60);
}

function getTrendGrowth(platform, metric) {
  const data = getTrendData(platform, metric);
  if (data.length < 2) return null;
  return (data.at(-1)?.count || 0) - (data.at(-2)?.count || 0);
}

// Form
const submitting = ref(false);

// Build initial form from platforms & metrics
function buildInitialStats() {
  const stats = {};
  Object.keys(props.platforms).forEach(pKey => {
    stats[pKey] = {};
    Object.keys(props.metrics).forEach(mKey => {
      stats[pKey][mKey] = '';
    });
  });
  return stats;
}

const form = ref({
  recorded_date: new Date().toISOString().slice(0, 10),
  stats: buildInitialStats(),
});

function submitStats() {
  // Flatten to array
  const statsArray = [];
  Object.keys(form.value.stats).forEach(pKey => {
    Object.keys(form.value.stats[pKey]).forEach(mKey => {
      const val = form.value.stats[pKey][mKey];
      if (val !== '' && val !== null && val >= 0) {
        statsArray.push({ platform: pKey, metric: mKey, count: parseInt(val) });
      }
    });
  });

  if (!statsArray.length) {
    alert('Vui lòng nhập ít nhất một số liệu!');
    return;
  }

  submitting.value = true;
  router.post(route('secretary.social-stats.store'), {
    recorded_date: form.value.recorded_date,
    stats: statsArray,
  }, {
    preserveScroll: true,
    onSuccess: () => { form.value.stats = buildInitialStats(); },
    onFinish: () => submitting.value = false,
  });
}
</script>

<style scoped>
.sec-social {
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
  background: linear-gradient(135deg, #7c3aed, #a855f7);
  color: white;
  border-radius: 20px;
  padding: 24px 28px;
  margin-bottom: 24px;
  flex-wrap: wrap;
  box-shadow: 0 8px 24px rgba(124, 58, 237, 0.25);
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

/* Snapshot cards */
.snapshot-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}
.snapshot-card {
  background: white;
  border: 1.5px solid #e5e7eb;
  border-radius: 16px;
  padding: 18px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  transition: box-shadow 0.2s;
}
.snapshot-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
.snapshot-platform { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; }
.platform-emoji { font-size: 1.4rem; }
.platform-name { font-size: 0.9rem; font-weight: 800; color: #1f2937; }
.snapshot-metrics { display: flex; flex-wrap: wrap; gap: 8px; }
.metric-chip { background: #f3f4f6; border-radius: 10px; padding: 8px 10px; min-width: 70px; }
.metric-val { font-size: 1.1rem; font-weight: 800; color: #1f2937; }
.metric-lbl { font-size: 0.68rem; color: #6b7280; margin-top: 2px; }
.no-data-msg { font-size: 0.8rem; color: #9ca3af; font-style: italic; }

/* Platform colors */
.platform-youtube .snapshot-platform .platform-name { color: #dc2626; }
.platform-facebook .snapshot-platform .platform-name { color: #1d4ed8; }
.platform-zalo .snapshot-platform .platform-name { color: #0ea5e9; }
.platform-instagram .snapshot-platform .platform-name { color: #9333ea; }

/* Main grid */
.social-main-grid {
  display: grid;
  grid-template-columns: 440px 1fr;
  gap: 20px;
}
@media (max-width: 900px) { .social-main-grid { grid-template-columns: 1fr; } }

/* Panels */
.social-form-panel, .social-chart-panel {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 24px;
}
.panel-title { font-size: 1rem; font-weight: 700; color: #1f2937; margin: 0 0 18px; }

/* Form */
.social-form { display: flex; flex-direction: column; gap: 16px; }
.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-label { font-size: 0.78rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.05em; }
.form-input {
  border: 1.5px solid #d1d5db;
  border-radius: 10px;
  padding: 9px 12px;
  font-size: 0.9rem;
  color: #1f2937;
  outline: none;
  transition: border-color 0.15s;
}
.form-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,0.1); }

/* Stats matrix */
.stats-matrix { display: flex; flex-direction: column; gap: 12px; max-height: 460px; overflow-y: auto; }
.matrix-platform { background: #fafafa; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; }
.matrix-platform-header { display: flex; align-items: center; gap: 6px; margin-bottom: 10px; font-size: 0.88rem; }
.matrix-platform-header strong { font-weight: 700; color: #1f2937; }
.matrix-metrics { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.matrix-metric { display: flex; flex-direction: column; gap: 4px; }
.matrix-label { font-size: 0.68rem; font-weight: 600; color: #6b7280; }
.matrix-input { padding: 6px 8px; font-size: 0.88rem; text-align: center; }

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

/* Submit */
.btn-submit {
  width: 100%;
  background: linear-gradient(135deg, #7c3aed, #a855f7);
  color: white;
  border: none;
  border-radius: 12px;
  padding: 14px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.15s;
  box-shadow: 0 4px 12px rgba(124,58,237,0.3);
}
.btn-submit:hover { opacity: 0.9; }
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }

/* Trend section */
.trend-section { margin-bottom: 24px; }
.trend-title { font-size: 0.88rem; font-weight: 700; color: #374151; margin: 0 0 10px; }
.mini-trend { margin-bottom: 16px; }
.trend-metric-label { font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 6px; }
.trend-bars { display: flex; align-items: flex-end; gap: 4px; height: 70px; }
.trend-bar-col { display: flex; flex-direction: column; align-items: center; gap: 2px; flex: 1; }
.trend-bar { width: 100%; background: linear-gradient(to top, #7c3aed, #c4b5fd); border-radius: 3px 3px 0 0; min-height: 2px; transition: height 0.3s; }
.trend-bar-date { font-size: 0.55rem; color: #9ca3af; white-space: nowrap; overflow: hidden; }
.trend-minmax { display: flex; justify-content: space-between; align-items: center; margin-top: 4px; }
.trend-latest { font-size: 0.75rem; font-weight: 600; color: #374151; }
.trend-growth { font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 10px; }
.growth-up { background: #d1fae5; color: #065f46; }
.growth-down { background: #fee2e2; color: #b91c1c; }
.trend-empty { font-size: 0.82rem; color: #9ca3af; font-style: italic; padding: 12px 0; }
</style>
