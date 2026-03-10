<template>
  <div class="space-y-5">

    <!-- ══ ROLE SWITCHER ══════════════════════════════════════════ -->
    <div class="grid grid-cols-2 gap-3">
      <!-- Thư Ký -->
      <button @click="$emit('switch-role', 'secretary')"
        :class="activeRole === 'secretary'
          ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 ring-2 ring-indigo-400'
          : 'bg-white text-gray-700 border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50'"
        class="flex flex-col items-center gap-2 rounded-2xl px-4 py-5 transition-all duration-200 cursor-pointer">
        <span class="text-2xl">🗂️</span>
        <div class="text-center">
          <p class="font-black text-sm">Thư Ký</p>
          <p :class="activeRole === 'secretary' ? 'text-indigo-200' : 'text-gray-400'" class="text-xs">Hội Thánh</p>
        </div>
        <span v-if="activeRole === 'secretary'" class="w-2 h-2 rounded-full bg-white/80"></span>
      </button>

      <!-- Thủ Quỹ (link sang finance-portal) -->
      <button @click="$emit('switch-role', 'treasurer')"
        class="flex flex-col items-center gap-2 rounded-2xl px-4 py-5 transition-all duration-200 cursor-pointer bg-white text-gray-700 border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50">
        <span class="text-2xl">💰</span>
        <div class="text-center">
          <p class="font-black text-sm">Thủ Quỹ</p>
          <p class="text-xs text-gray-400">Hội Thánh</p>
        </div>
        <span class="text-xs text-emerald-500 font-bold">→ Finance Portal</span>
      </button>
    </div>

    <!-- ══ SECRETARY DASHBOARD ════════════════════════════════════ -->
    <template v-if="activeRole === 'secretary'">

      <!-- KPI thống kê nhanh -->
      <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-4 py-4 text-center">
          <p class="text-2xl font-black text-indigo-700">{{ stats.total_members }}</p>
          <p class="text-xs text-gray-500 mt-1">Tổng TH</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-4 py-4 text-center">
          <p class="text-2xl font-black text-emerald-600">{{ stats.official_count }}</p>
          <p class="text-xs text-gray-500 mt-1">Chính thức</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-4 py-4 text-center">
          <p class="text-2xl font-black" :class="stats.pending_reports > 0 ? 'text-amber-600' : 'text-gray-400'">{{ stats.pending_reports }}</p>
          <p class="text-xs text-gray-500 mt-1">Đang chờ duyệt</p>
        </div>
      </div>

      <!-- Báo cáo chờ duyệt -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 bg-amber-500 flex items-center justify-between">
          <h3 class="text-sm font-black text-white">📋 Báo Cáo Chờ Duyệt</h3>
          <span class="bg-white/30 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ pendingReports.length }}</span>
        </div>
        <div v-if="pendingReports.length > 0">
          <div v-for="r in pendingReports" :key="r.id"
            class="flex items-center justify-between px-4 py-3 border-b border-gray-50 hover:bg-amber-50/40 transition-colors">
            <div>
              <p class="text-sm font-bold text-gray-900">{{ r.dept_name }}</p>
              <p class="text-xs text-gray-400">Tháng {{ r.month }}/{{ r.year }} · Nộp {{ r.submitted_at }}</p>
            </div>
            <Link :href="route('portal.reports.index')"
              class="text-xs font-bold text-amber-600 bg-amber-50 px-3 py-1.5 rounded-lg hover:bg-amber-100 transition-colors">
              Xem
            </Link>
          </div>
        </div>
        <div v-else class="px-5 py-6 text-center text-xs text-gray-400">
          ✅ Không có báo cáo nào đang chờ duyệt
        </div>
      </div>

      <!-- Điểm danh tổng hợp các ban tháng này -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 bg-indigo-700 flex items-center justify-between">
          <h3 class="text-sm font-black text-white">📊 Điểm Danh Tháng {{ currentMonth }}</h3>
        </div>
        <div v-if="attendanceSummary.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-indigo-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-bold text-indigo-900">Ban Ngành</th>
                <th class="px-4 py-2 text-center text-xs font-bold text-indigo-900">Buổi</th>
                <th class="px-4 py-2 text-center text-xs font-bold text-indigo-900">Tổng HD</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="dept in attendanceSummary" :key="dept.dept_name" class="hover:bg-indigo-50/30">
                <td class="px-4 py-2.5 text-xs font-bold text-gray-800">{{ dept.dept_name }}</td>
                <td class="px-4 py-2.5 text-center text-sm text-gray-500">{{ dept.session_count }}</td>
                <td class="px-4 py-2.5 text-center text-sm font-black text-indigo-700">{{ dept.total_att }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="px-5 py-6 text-center text-xs text-gray-400">Chưa có dữ liệu</div>
      </div>

      <!-- Quản lý Tín Hữu -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 bg-slate-700 flex items-center justify-between">
          <h3 class="text-sm font-black text-white">👤 Quản Lý Hội Thánh</h3>
        </div>
        <div class="grid grid-cols-2 gap-2 p-4">
          <Link :href="route('members.index')"
            class="flex items-center gap-3 bg-slate-50 hover:bg-slate-100 transition-colors rounded-xl px-4 py-3.5 border border-slate-200">
            <span class="text-xl">👥</span>
            <div>
              <p class="text-sm font-bold text-gray-900">Tín Hữu</p>
              <p class="text-xs text-gray-400">Danh sách & quản lý</p>
            </div>
          </Link>
          <Link :href="route('departments.index')"
            class="flex items-center gap-3 bg-slate-50 hover:bg-slate-100 transition-colors rounded-xl px-4 py-3.5 border border-slate-200">
            <span class="text-xl">🏛️</span>
            <div>
              <p class="text-sm font-bold text-gray-900">Ban Ngành</p>
              <p class="text-xs text-gray-400">Cơ cấu tổ chức</p>
            </div>
          </Link>
          <Link :href="route('meetings.index')"
            class="flex items-center gap-3 bg-slate-50 hover:bg-slate-100 transition-colors rounded-xl px-4 py-3.5 border border-slate-200">
            <span class="text-xl">📅</span>
            <div>
              <p class="text-sm font-bold text-gray-900">Buổi Nhóm</p>
              <p class="text-xs text-gray-400">Lịch sinh hoạt HT</p>
            </div>
          </Link>
          <Link :href="route('portal.reports.index')"
            class="flex items-center gap-3 bg-amber-50 hover:bg-amber-100 transition-colors rounded-xl px-4 py-3.5 border border-amber-200">
            <span class="text-xl">📋</span>
            <div>
              <p class="text-sm font-bold text-gray-900">Báo Cáo Ban</p>
              <p class="text-xs text-gray-400">Duyệt báo cáo</p>
            </div>
          </Link>
        </div>
      </div>

    </template>

  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  activeRole:        { type: String, default: 'secretary' },
  stats:             Object,
  pendingReports:    { type: Array, default: () => [] },
  attendanceSummary: { type: Array, default: () => [] },
  currentMonth:      String,
});

defineEmits(['switch-role']);
</script>