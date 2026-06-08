<template>
  <div class="portal-links-section">
    <h2 class="section-title">🚪 Chuyển Đến Cổng Thông Tin</h2>
    <p class="section-desc">Truy cập nhanh tất cả cổng quản lý — P2 Thư Ký · P3 Thủ Quỹ · P4 Chấp Sự · P5 Mục Vụ</p>

    <div class="portals-grid">
      <a
        v-for="portal in links"
        :key="portal.id"
        :href="portal.route"
        class="portal-card"
        :class="'portal-' + portal.color"
      >
        <div class="portal-icon">{{ portal.icon }}</div>
        <div class="portal-info">
          <div class="portal-id">{{ portal.id }}</div>
          <div class="portal-label">{{ portal.label }}</div>
          <div class="portal-desc">{{ portal.description }}</div>
        </div>
        <div class="portal-arrow">→</div>
      </a>
    </div>
  </div>
</template>

<script setup>
defineProps({
  links: { type: Array, default: () => [] },
})
</script>

<style scoped>
.portal-links-section {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 24px;
  margin-bottom: 24px;
}
.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 6px;
}
.section-desc {
  font-size: 0.82rem;
  color: #6b7280;
  margin: 0 0 18px;
}

.portals-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.portal-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  border-radius: 12px;
  text-decoration: none;
  border: 1.5px solid transparent;
  transition: all 0.18s;
  cursor: pointer;
}
.portal-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.portal-icon { font-size: 1.8rem; flex-shrink: 0; }
.portal-info { flex: 1; }
.portal-id   { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; }
.portal-label { font-size: 0.95rem; font-weight: 600; color: inherit; }
.portal-desc  { font-size: 0.75rem; opacity: 0.75; margin-top: 2px; }
.portal-arrow { font-size: 1.1rem; opacity: 0.6; flex-shrink: 0; }

/* Color Variants */
.portal-indigo {
  background: linear-gradient(135deg, #eef2ff, #e0e7ff);
  border-color: #c7d2fe;
  color: #3730a3;
}
.portal-indigo:hover { border-color: #6366f1; background: linear-gradient(135deg, #e0e7ff, #c7d2fe); }

.portal-emerald {
  background: linear-gradient(135deg, #ecfdf5, #d1fae5);
  border-color: #a7f3d0;
  color: #065f46;
}
.portal-emerald:hover { border-color: #059669; background: linear-gradient(135deg, #d1fae5, #a7f3d0); }

.portal-blue {
  background: linear-gradient(135deg, #eff6ff, #dbeafe);
  border-color: #bfdbfe;
  color: #1e40af;
}
.portal-blue:hover { border-color: #3b82f6; background: linear-gradient(135deg, #dbeafe, #bfdbfe); }

.portal-purple {
  background: linear-gradient(135deg, #faf5ff, #ede9fe);
  border-color: #ddd6fe;
  color: #5b21b6;
}
.portal-purple:hover { border-color: #7c3aed; background: linear-gradient(135deg, #ede9fe, #ddd6fe); }

.portal-slate {
  background: linear-gradient(135deg, #f8fafc, #f1f5f9);
  border-color: #e2e8f0;
  color: #334155;
}
.portal-slate:hover { border-color: #64748b; background: linear-gradient(135deg, #f1f5f9, #e2e8f0); }

.portal-orange {
  background: linear-gradient(135deg, #fff7ed, #fed7aa);
  border-color: #fdba74;
  color: #9a3412;
}
.portal-orange:hover { border-color: #f97316; background: linear-gradient(135deg, #fed7aa, #fdba74); }

@media (max-width: 900px) {
  .portals-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 600px) {
  .portals-grid { grid-template-columns: 1fr; }
}
</style>
