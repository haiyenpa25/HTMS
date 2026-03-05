<template>
  <div class="max-w-2xl mx-auto space-y-5">

    <!-- ══ CONTEXT SWITCHER (giống portal.index) ══════════════════ -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="px-5 py-3 bg-gradient-to-r from-amber-600 to-amber-700">
        <p class="text-xs font-black text-amber-100 uppercase tracking-widest">Ban Chấp Sự · Cổng Nội Bộ</p>
      </div>
      <div class="p-4 grid grid-cols-2 gap-3">
        <!-- Thư Ký -->
        <button @click="$emit('switch-role', 'secretary')"
          :class="activeRole === 'secretary'
            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 ring-2 ring-indigo-300'
            : 'bg-gray-50 text-gray-700 border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50'"
          class="flex items-center gap-3 rounded-2xl px-4 py-4 transition-all duration-200 text-left">
          <div :class="activeRole === 'secretary' ? 'bg-white/20' : 'bg-indigo-100'" class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" :class="activeRole === 'secretary' ? 'text-white' : 'text-indigo-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <div>
            <p class="font-black text-sm">Thư Ký</p>
            <p :class="activeRole === 'secretary' ? 'text-indigo-200' : 'text-gray-400'" class="text-xs">Hội Thánh</p>
          </div>
        </button>

        <!-- Thủ Quỹ -->
        <button @click="$emit('switch-role', 'treasurer')"
          :class="activeRole === 'treasurer'
            ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-200 ring-2 ring-emerald-300'
            : 'bg-gray-50 text-gray-700 border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50'"
          class="flex items-center gap-3 rounded-2xl px-4 py-4 transition-all duration-200 text-left">
          <div :class="activeRole === 'treasurer' ? 'bg-white/20' : 'bg-emerald-100'" class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" :class="activeRole === 'treasurer' ? 'text-white' : 'text-emerald-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="font-black text-sm">Thủ Quỹ</p>
            <p :class="activeRole === 'treasurer' ? 'text-emerald-200' : 'text-gray-400'" class="text-xs">Hội Thánh</p>
          </div>
        </button>
      </div>
    </div>

    <!-- ══ THƯ KÝ: Feature Grid ═══════════════════════════════════ -->
    <template v-if="activeRole === 'secretary'">

      <!-- KPI nhanh -->
      <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-3 py-4 text-center">
          <p class="text-xl font-black text-indigo-700">{{ totalMembers }}</p>
          <p class="text-xs text-gray-400 mt-0.5">Tổng TH</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-3 py-4 text-center">
          <p class="text-xl font-black" :class="pendingAttendance > 0 ? 'text-amber-500' : 'text-gray-400'">{{ pendingAttendance }}</p>
          <p class="text-xs text-gray-400 mt-0.5">Chờ Điểm Danh</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-3 py-4 text-center">
          <p class="text-xl font-black" :class="pendingReports.length > 0 ? 'text-red-500' : 'text-gray-400'">{{ pendingReports.length }}</p>
          <p class="text-xs text-gray-400 mt-0.5">BC Chờ Duyệt</p>
        </div>
      </div>

      <!-- Feature Grid -->
      <div class="grid grid-cols-2 gap-3">
        <!-- Điểm danh HT -->
        <Link :href="route('deacon.attendance')"
          class="bg-white rounded-2xl border border-indigo-100 shadow-sm p-5 hover:bg-indigo-50 hover:shadow-md hover:border-indigo-200 transition-all duration-200 group">
          <div class="w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center mb-3 group-hover:bg-indigo-200 transition-colors">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
          </div>
          <h3 class="font-black text-gray-900 text-sm mb-1">Điểm Danh HT</h3>
          <p class="text-xs text-gray-400">Buổi nhóm Hội Thánh</p>
          <div v-if="pendingAttendance > 0" class="mt-2 inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
            {{ pendingAttendance }} chờ
          </div>
        </Link>

        <!-- Báo cáo -->
        <Link :href="route('portal.reports.index')"
          class="bg-white rounded-2xl border border-purple-100 shadow-sm p-5 hover:bg-purple-50 hover:shadow-md hover:border-purple-200 transition-all duration-200 group">
          <div class="w-12 h-12 rounded-2xl bg-purple-100 flex items-center justify-center mb-3 group-hover:bg-purple-200 transition-colors">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <h3 class="font-black text-gray-900 text-sm mb-1">Báo Cáo</h3>
          <p class="text-xs text-gray-400">Báo cáo các ban ngành</p>
          <div v-if="pendingReports.length > 0" class="mt-2 inline-flex items-center gap-1 bg-red-100 text-red-600 text-xs font-bold px-2 py-0.5 rounded-full">
            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
            {{ pendingReports.length }} chờ duyệt
          </div>
        </Link>
      </div>

      <!-- Báo cáo chờ duyệt list -->
      <div v-if="pendingReports.length > 0" class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-amber-500 flex items-center justify-between">
          <h3 class="text-sm font-black text-white">Báo Cáo Đang Chờ Duyệt</h3>
          <span class="bg-white/30 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ pendingReports.length }}</span>
        </div>
        <div v-for="r in pendingReports" :key="r.id" class="flex items-center justify-between px-4 py-3 border-b border-gray-50 last:border-0">
          <div>
            <p class="text-sm font-bold text-gray-900">{{ r.dept_name }}</p>
            <p class="text-xs text-gray-400">Tháng {{ r.month }}/{{ r.year }}</p>
          </div>
          <Link :href="route('portal.reports.index')" class="text-xs font-bold text-amber-600 bg-amber-50 px-3 py-1.5 rounded-lg hover:bg-amber-100">Xem</Link>
        </div>
      </div>

    </template>

    <!-- ══ THỦ QUỸ: Feature Grid ══════════════════════════════════ -->
    <template v-if="activeRole === 'treasurer'">

      <!-- KPI -->
      <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-3 py-4 text-center">
          <p class="text-base font-black text-emerald-700">{{ formatMoney(totalIncome) }}</p>
          <p class="text-xs text-gray-400 mt-0.5">Thu tháng {{ currentMonth }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-3 py-4 text-center">
          <p class="text-base font-black text-red-600">{{ formatMoney(totalExpense) }}</p>
          <p class="text-xs text-gray-400 mt-0.5">Chi tháng {{ currentMonth }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-3 py-4 text-center">
          <p class="text-xl font-black" :class="pendingTx > 0 ? 'text-amber-500' : 'text-gray-400'">{{ pendingTx }}</p>
          <p class="text-xs text-gray-400 mt-0.5">Chờ Duyệt</p>
        </div>
      </div>

      <!-- Feature Grid -->
      <div class="grid grid-cols-2 gap-3">
        <!-- Quản lý Quỹ -->
        <Link v-if="route().has('finance.funds.index')" :href="route('finance.funds.index')"
          class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-5 hover:bg-emerald-50 hover:shadow-md transition-all duration-200 group">
          <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center mb-3 group-hover:bg-emerald-200 transition-colors">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
            </svg>
          </div>
          <h3 class="font-black text-gray-900 text-sm mb-1">Quản Lý Quỹ</h3>
          <p class="text-xs text-gray-400">{{ funds.length }} quỹ đang hoạt động</p>
        </Link>

        <!-- Tài Chính -->
        <Link :href="route('finance.index')"
          class="bg-white rounded-2xl border border-teal-100 shadow-sm p-5 hover:bg-teal-50 hover:shadow-md transition-all duration-200 group">
          <div class="w-12 h-12 rounded-2xl bg-teal-100 flex items-center justify-center mb-3 group-hover:bg-teal-200 transition-colors">
            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h3 class="font-black text-gray-900 text-sm mb-1">Tài Chính</h3>
          <p class="text-xs text-gray-400">Thu chi Hội Thánh</p>
          <div v-if="pendingTx > 0" class="mt-2 inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">
            {{ pendingTx }} giao dịch chờ
          </div>
        </Link>

        <!-- Báo cáo tài chính -->
        <Link v-if="route().has('finance.reports.index')" :href="route('finance.reports.index')"
          class="bg-white rounded-2xl border border-purple-100 shadow-sm p-5 hover:bg-purple-50 hover:shadow-md transition-all duration-200 group col-span-2">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-purple-100 flex items-center justify-center shrink-0 group-hover:bg-purple-200 transition-colors">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div>
              <h3 class="font-black text-gray-900">Báo Cáo Tài Chính</h3>
              <p class="text-xs text-gray-400 mt-0.5">Báo cáo thu chi theo tháng</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </div>
        </Link>
      </div>

      <!-- Danh sách quỹ -->
      <div v-if="funds.length > 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-emerald-600">
          <h3 class="text-sm font-black text-white">Số Dư Các Quỹ</h3>
        </div>
        <div v-for="f in funds" :key="f.id" class="flex items-center justify-between px-4 py-3 border-b border-gray-50 last:border-0">
          <p class="text-sm font-bold text-gray-800">{{ f.name }}</p>
          <p class="text-sm font-black" :class="f.balance >= 0 ? 'text-emerald-700' : 'text-red-600'">
            {{ formatMoney(f.balance) }}đ
          </p>
        </div>
      </div>

    </template>

  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  activeRole:        { type: String, default: 'secretary' },
  totalMembers:      { type: Number, default: 0 },
  currentMonth:      { type: String, default: '' },
  pendingAttendance: { type: Number, default: 0 },
  lastMeeting:       { type: Object, default: null },
  pendingReports:    { type: Array,  default: () => [] },
  funds:             { type: Array,  default: () => [] },
  totalIncome:       { type: Number, default: 0 },
  totalExpense:      { type: Number, default: 0 },
  pendingTx:         { type: Number, default: 0 },
});

defineEmits(['switch-role']);

const formatMoney = (val) => {
  if (!val) return '0';
  return new Intl.NumberFormat('vi-VN').format(val);
};
</script>
