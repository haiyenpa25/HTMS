<template>
  <PortalLayout
      :department="department"
      :available-departments="availableDepartments"
      :is-global-admin="isGlobalAdmin"
      portal-type="deacon"
      @open-switcher="() => {}"
  >
    <div class="p-4 sm:p-6 lg:p-8 max-w-6xl mx-auto space-y-8">

      <!-- ═══════════════════════════════════════════════════
           PAGE HEADER
      ═══════════════════════════════════════════════════ -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-2xl font-black text-gray-900">Báo Cáo Chương Trình Sinh Hoạt</h2>
          <p class="text-sm text-gray-500 mt-0.5">Tháng {{ filterMonth }}/{{ filterYear }} — Thư Ký Hội Thánh</p>
        </div>
        <!-- Month/Year filter -->
        <div class="flex items-end gap-2 shrink-0">
          <div>
            <label class="block text-xs font-bold text-gray-400 mb-1">Tháng</label>
            <select v-model="monthFilter" class="border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold">
              <option v-for="m in 12" :key="m" :value="m">{{ m }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-400 mb-1">Năm</label>
            <select v-model="yearFilter" class="border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold">
              <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
            </select>
          </div>
          <button @click="applyFilter" class="px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-colors">Lọc</button>
          <span :class="report.status === 'submitted' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'" class="px-3 py-1.5 rounded-xl text-xs font-black capitalize">
            {{ report.status === 'submitted' ? '✓ Đã nộp' : '📝 Nháp' }}
          </span>
        </div>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3 text-emerald-700 text-sm font-bold flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ $page.props.flash.success }}
      </div>

      <!-- ═══════════════════════════════════════════════════
           SECTION 1: YOUTUBE STATS
      ═══════════════════════════════════════════════════ -->
      <section>
        <SectionHeader icon="📺" title="Kênh Truyền Thông YouTube" />
        <form @submit.prevent="saveReport" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 space-y-5">
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="field in ytFields" :key="field.key" class="space-y-1.5">
              <label class="block text-xs font-black text-gray-500 uppercase tracking-wider">{{ field.label }}</label>
              <div class="relative">
                <input v-model.number="ytForm[field.key]" type="number" min="0"
                  class="w-full border-2 rounded-2xl px-4 py-3 text-xl font-black text-center focus:outline-none transition-colors"
                  :class="field.color + ' focus:border-opacity-80'"
                  placeholder="0"
                />
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-lg">{{ field.icon }}</span>
              </div>
            </div>
          </div>

          <!-- Announcements -->
          <div>
            <label class="block text-sm font-black text-gray-700 mb-1.5">📢 Thông Báo, Đề Nghị & Kế Hoạch Khác</label>
            <textarea v-model="ytForm.announcements" rows="3"
              class="w-full border-2 border-gray-200 rounded-2xl px-4 py-3 text-sm text-gray-700 focus:outline-none focus:border-blue-300 resize-none"
              placeholder="Nhập các thông báo, đề nghị hoặc kế hoạch..."
            ></textarea>
          </div>

          <button type="submit" :disabled="savingReport"
            class="px-6 py-2.5 bg-blue-600 text-white text-sm font-black rounded-xl hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-50 flex items-center gap-2">
            <svg v-if="savingReport" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            {{ savingReport ? 'Đang lưu...' : '💾 Lưu số liệu' }}
          </button>
        </form>
      </section>

      <!-- ═══════════════════════════════════════════════════
           SECTION 2: MEETING TABLE
      ═══════════════════════════════════════════════════ -->
      <section>
        <SectionHeader icon="📋" title="Bảng Buổi Nhóm Hội Thánh" />
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
          <div v-if="meetings.length === 0" class="p-8 text-center text-gray-400">
            <p class="font-bold">Chưa có buổi nhóm nào trong tháng này</p>
            <p class="text-sm mt-1">Hãy nhập số liệu điểm danh trước.</p>
          </div>
          <div v-else>
            <!-- Summary row at top -->
            <div class="grid grid-cols-4 divide-x divide-gray-100 border-b border-gray-100">
              <div class="p-4 text-center">
                <p class="text-xs font-bold text-gray-400">Số buổi</p>
                <p class="text-2xl font-black text-gray-900">{{ meetings.length }}</p>
              </div>
              <div class="p-4 text-center">
                <p class="text-xs font-bold text-gray-400">TB Hiện diện</p>
                <p class="text-2xl font-black text-emerald-600">{{ avgPresent }}</p>
              </div>
              <div class="p-4 text-center">
                <p class="text-xs font-bold text-gray-400">TB Online</p>
                <p class="text-2xl font-black text-blue-600">{{ avgOnline }}</p>
              </div>
              <div class="p-4 text-center">
                <p class="text-xs font-bold text-gray-400">Tổng cộng</p>
                <p class="text-2xl font-black text-gray-900">{{ totalPresent }}</p>
              </div>
            </div>
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                  <th class="text-left px-4 py-3 text-xs font-black text-gray-500 uppercase">Ngày</th>
                  <th class="text-left px-4 py-3 text-xs font-black text-gray-500 uppercase hidden sm:table-cell">Chủ đề</th>
                  <th class="text-left px-4 py-3 text-xs font-black text-gray-500 uppercase hidden md:table-cell">Kinh Thánh</th>
                  <th class="text-left px-4 py-3 text-xs font-black text-gray-500 uppercase hidden md:table-cell">Câu gốc</th>
                  <th class="text-left px-4 py-3 text-xs font-black text-gray-500 uppercase hidden sm:table-cell">Diễn giả</th>
                  <th class="text-center px-4 py-3 text-xs font-black text-gray-500 uppercase">Hiện diện</th>
                  <th class="text-center px-4 py-3 text-xs font-black text-gray-500 uppercase hidden md:table-cell">Online</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="m in meetings" :key="m.id" class="hover:bg-gray-50/50">
                  <td class="px-4 py-3 font-bold text-gray-800 whitespace-nowrap">{{ formatDate(m.date) }}</td>
                  <td class="px-4 py-3 text-gray-700 hidden sm:table-cell">{{ m.topic || '—' }}</td>
                  <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ m.scripture || '—' }}</td>
                  <td class="px-4 py-3 text-gray-500 text-xs hidden md:table-cell italic">{{ m.memory_verse || '—' }}</td>
                  <td class="px-4 py-3 text-gray-600 hidden sm:table-cell">{{ m.speaker_name || m.preacher || '—' }}</td>
                  <td class="px-4 py-3 text-center">
                    <span v-if="m.total_present" class="font-black text-emerald-600 text-lg">{{ m.total_present }}</span>
                    <span v-else class="text-gray-300 font-bold">—</span>
                  </td>
                  <td class="px-4 py-3 text-center hidden md:table-cell">
                    <span v-if="m.total_online" class="font-bold text-blue-600">{{ m.total_online }}</span>
                    <span v-else class="text-gray-300">—</span>
                  </td>
                </tr>
              </tbody>
              <tfoot v-if="meetings.length">
                <tr class="bg-blue-50 border-t-2 border-blue-100">
                  <td colspan="5" class="px-4 py-3 text-sm font-black text-blue-800">TRUNG BÌNH</td>
                  <td class="px-4 py-3 text-center font-black text-emerald-700 text-base">{{ avgPresent }}</td>
                  <td class="px-4 py-3 text-center font-black text-blue-700 text-base hidden md:table-cell">{{ avgOnline }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </section>

      <!-- ═══════════════════════════════════════════════════
           SECTION 3: CHARTS
      ═══════════════════════════════════════════════════ -->
      <section>
        <SectionHeader icon="📊" title="Biểu Đồ Thống Kê" />
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

          <!-- Bar chart: Avg per month (6 months) -->
          <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h4 class="text-sm font-black text-gray-700 mb-4">Trung Bình Tham Dự Theo Tháng</h4>
            <div class="h-52 flex items-end gap-2 px-2">
              <div v-for="(d, i) in chartData" :key="i" class="flex-1 flex flex-col items-center gap-1 group">
                <span class="text-[10px] font-black text-gray-500 opacity-0 group-hover:opacity-100 transition-opacity">{{ d.present }}</span>
                <div class="w-full rounded-t-lg transition-all duration-500"
                  :style="{ height: barHeight(d.present) + 'px' }"
                  :class="i === chartData.length - 1 ? 'bg-blue-600' : 'bg-blue-200 group-hover:bg-blue-400'"
                ></div>
                <span class="text-[10px] font-bold text-gray-400 text-center leading-tight">{{ d.label }}</span>
              </div>
            </div>
            <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
              <span class="w-3 h-3 rounded-sm bg-blue-600 inline-block"></span> Tháng hiện tại
              <span class="w-3 h-3 rounded-sm bg-blue-200 inline-block ml-2"></span> Tháng trước
            </div>
          </div>

          <!-- Pie chart: dept breakdown -->
          <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
            <h4 class="text-sm font-black text-gray-700 mb-4">Phân Bổ Điểm Danh Theo Ban</h4>
            <div v-if="deptAttendance.length === 0" class="h-52 flex items-center justify-center text-gray-300">
              <div class="text-center">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                <p class="text-sm font-bold">Chưa có dữ liệu điểm danh theo ban</p>
              </div>
            </div>
            <div v-else class="flex gap-6">
              <!-- SVG Pie -->
              <svg class="w-40 h-40 shrink-0" viewBox="0 0 42 42">
                <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#f3f4f6" stroke-width="4"></circle>
                <circle v-for="(seg, i) in pieSegments" :key="i"
                  cx="21" cy="21" r="15.915" fill="transparent"
                  :stroke="pieColors[i % pieColors.length]"
                  stroke-width="4"
                  :stroke-dasharray="`${seg.value} ${100 - seg.value}`"
                  :stroke-dashoffset="seg.offset"
                  style="transform:rotate(-90deg);transform-origin:center"
                />
              </svg>
              <!-- Legend -->
              <div class="flex-1 space-y-2 overflow-y-auto max-h-44">
                <div v-for="(d, i) in deptAttendance" :key="i" class="flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full shrink-0" :style="{ background: pieColors[i % pieColors.length] }"></span>
                    <span class="font-bold text-gray-700 truncate max-w-[100px]">{{ d.dept_name }}</span>
                  </div>
                  <span class="font-black text-gray-900">{{ deptPercent(d.total) }}%</span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </section>

      <!-- ═══════════════════════════════════════════════════
           SECTION 4: INCIDENTS
      ═══════════════════════════════════════════════════ -->
      <section>
        <div class="flex items-center justify-between mb-4">
          <SectionHeader icon="⚠️" title="Ghi Nhận Sự Cố Và Giải Quyết" />
          <button @click="showIncidentForm = true"
            class="px-4 py-2 bg-orange-500 text-white text-xs font-black rounded-xl hover:bg-orange-600 transition-colors shadow-sm flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Thêm sự cố
          </button>
        </div>

        <!-- Add form modal inline -->
        <div v-if="showIncidentForm" class="bg-orange-50 border-2 border-orange-200 rounded-3xl p-5 mb-4 space-y-4">
          <h4 class="font-black text-orange-900">Thêm sự cố mới</h4>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="col-span-2 sm:col-span-1">
              <label class="text-xs font-black text-gray-600 mb-1 block">Tuần</label>
              <select v-model="incidentForm.week_label" class="w-full border-2 border-orange-200 rounded-xl px-3 py-2 text-sm font-bold bg-white">
                <option value="Tuần 1">Tuần 1</option>
                <option value="Tuần 2">Tuần 2</option>
                <option value="Tuần 3">Tuần 3</option>
                <option value="Tuần 4">Tuần 4</option>
                <option value="Tuần 5">Tuần 5</option>
              </select>
            </div>
            <div class="col-span-2 sm:col-span-1">
              <label class="text-xs font-black text-gray-600 mb-1 block">Trạng thái</label>
              <select v-model="incidentForm.status" class="w-full border-2 border-orange-200 rounded-xl px-3 py-2 text-sm font-bold bg-white">
                <option value="pending">Chưa xử lý</option>
                <option value="in_progress">Đang xử lý</option>
                <option value="resolved">Đã xử lý</option>
              </select>
            </div>
          </div>
          <div>
            <label class="text-xs font-black text-gray-600 mb-1 block">Mô tả sự cố</label>
            <textarea v-model="incidentForm.incident_description" rows="2" class="w-full border-2 border-orange-200 rounded-xl px-3 py-2 text-sm resize-none" placeholder="Mô tả sự cố xảy ra..."></textarea>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="text-xs font-black text-gray-600 mb-1 block">Giải pháp đã áp dụng</label>
              <textarea v-model="incidentForm.resolution" rows="2" class="w-full border-2 border-orange-200 rounded-xl px-3 py-2 text-sm resize-none" placeholder="Giải pháp..."></textarea>
            </div>
            <div>
              <label class="text-xs font-black text-gray-600 mb-1 block">Hướng giải quyết tiếp theo</label>
              <textarea v-model="incidentForm.direction" rows="2" class="w-full border-2 border-orange-200 rounded-xl px-3 py-2 text-sm resize-none" placeholder="Hướng giải quyết..."></textarea>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <button @click="submitIncident" :disabled="savingIncident" class="px-5 py-2 bg-orange-500 text-white text-sm font-black rounded-xl hover:bg-orange-600 transition-colors disabled:opacity-50">
              {{ savingIncident ? 'Đang lưu...' : 'Lưu sự cố' }}
            </button>
            <button @click="showIncidentForm = false" class="px-5 py-2 border border-gray-200 text-gray-600 text-sm font-bold rounded-xl hover:bg-gray-50">Huỷ</button>
          </div>
        </div>

        <!-- Incident list -->
        <div class="space-y-3">
          <div v-if="incidentList.length === 0" class="bg-white rounded-2xl border border-gray-100 p-6 text-center text-gray-400">
            <p class="font-bold text-sm">Không có sự cố nào trong tháng này 🎉</p>
          </div>
          <div v-for="inc in incidentList" :key="inc.id"
            class="bg-white rounded-2xl border shadow-sm overflow-hidden"
            :class="inc.status === 'resolved' ? 'border-emerald-200' : inc.status === 'in_progress' ? 'border-amber-200' : 'border-red-200'"
          >
            <div class="px-5 py-3 flex items-center justify-between"
              :class="inc.status === 'resolved' ? 'bg-emerald-50' : inc.status === 'in_progress' ? 'bg-amber-50' : 'bg-red-50'"
            >
              <div class="flex items-center gap-3">
                <span class="text-xs font-black px-2.5 py-1 rounded-full"
                  :class="inc.status === 'resolved' ? 'bg-emerald-500 text-white' : inc.status === 'in_progress' ? 'bg-amber-500 text-white' : 'bg-red-500 text-white'"
                >
                  {{ statusLabel(inc.status) }}
                </span>
                <span class="font-black text-gray-800">{{ inc.week_label }}</span>
              </div>
              <div class="flex items-center gap-2">
                <select v-model="inc.status" @change="updateIncident(inc)"
                  class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white font-bold focus:outline-none">
                  <option value="pending">Chưa xử lý</option>
                  <option value="in_progress">Đang xử lý</option>
                  <option value="resolved">Đã xử lý</option>
                </select>
                <button @click="deleteIncident(inc)" class="text-red-400 hover:text-red-600 p-1 rounded-lg hover:bg-red-50 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
              <div>
                <p class="text-xs font-black text-gray-400 uppercase mb-1">Sự cố</p>
                <p class="text-gray-700">{{ inc.incident_description || '—' }}</p>
              </div>
              <div>
                <p class="text-xs font-black text-gray-400 uppercase mb-1">Giải pháp</p>
                <p class="text-gray-700">{{ inc.resolution || '—' }}</p>
              </div>
              <div>
                <p class="text-xs font-black text-gray-400 uppercase mb-1">Hướng giải quyết</p>
                <p class="text-gray-700">{{ inc.direction || '—' }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ═══════════════════════════════════════════════════
           SECTION 5: AI ANALYSIS
      ═══════════════════════════════════════════════════ -->
      <section>
        <SectionHeader icon="🤖" title="Nhận Xét & Đánh Giá" />
        <div class="bg-gradient-to-br from-indigo-900 to-blue-900 rounded-3xl p-6 text-white shadow-xl space-y-4">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-xl">🤖</div>
            <div>
              <h4 class="font-black text-white">Phân Tích Tháng {{ filterMonth }}/{{ filterYear }}</h4>
              <p class="text-blue-300 text-xs font-medium">Dựa trên số liệu điểm danh và thống kê</p>
            </div>
          </div>
          <div class="space-y-3">
            <div v-for="(note, i) in aiInsights" :key="i"
              class="bg-white/10 rounded-2xl px-4 py-3 flex items-start gap-3 text-sm">
              <span class="text-lg mt-0.5 shrink-0">{{ note.icon }}</span>
              <div>
                <p class="font-bold text-white">{{ note.title }}</p>
                <p class="text-blue-200 text-xs mt-0.5">{{ note.body }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

    </div>
  </PortalLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

// ─── Simple Section Header component (inline) ───────────────
const SectionHeader = {
  props: ['icon', 'title'],
  template: `<div class="flex items-center gap-2 mb-3">
    <span class="text-xl">{{ icon }}</span>
    <h3 class="text-lg font-black text-gray-900">{{ title }}</h3>
    <div class="flex-1 h-px bg-gray-100 ml-2"></div>
  </div>`,
};

const props = defineProps({
  report:         { type: Object, required: true },
  meetings:       { type: Array,  default: () => [] },
  chartData:      { type: Array,  default: () => [] },
  deptAttendance: { type: Array,  default: () => [] },
  department:           { type: Object,  default: () => ({}) },
  availableDepartments: { type: Array,   default: () => [] },
  isGlobalAdmin:        { type: Boolean, default: false },
  filterMonth:    { type: Number, default: new Date().getMonth() + 1 },
  filterYear:     { type: Number, default: new Date().getFullYear() },
  yearOptions:    { type: Array,  default: () => [] },
});

// ─── Filter ─────────────────────────────────────────────────
const monthFilter = ref(props.filterMonth);
const yearFilter  = ref(props.filterYear);
const applyFilter = () => {
  router.get(route('deacon.report'), { month: monthFilter.value, year: yearFilter.value }, { preserveScroll: true });
};

// ─── YouTube form ────────────────────────────────────────────
const ytForm = ref({
  yt_subscribers:     props.report.yt_subscribers     ?? 0,
  yt_new_subscribers: props.report.yt_new_subscribers ?? 0,
  yt_views:           props.report.yt_views           ?? 0,
  yt_watch_hours:     props.report.yt_watch_hours     ?? 0,
  announcements:      props.report.announcements      ?? '',
});
const ytFields = [
  { key: 'yt_subscribers',     label: 'Đăng ký hiện tại', icon: '👥', color: 'border-blue-200 text-blue-700' },
  { key: 'yt_new_subscribers', label: 'Đăng ký mới',      icon: '➕', color: 'border-emerald-200 text-emerald-700' },
  { key: 'yt_views',           label: 'Số lượt xem',      icon: '👁', color: 'border-purple-200 text-purple-700' },
  { key: 'yt_watch_hours',     label: 'Giờ xem',          icon: '⏱', color: 'border-orange-200 text-orange-700' },
];
const savingReport = ref(false);
const saveReport = () => {
  savingReport.value = true;
  router.post(route('deacon.report.save'), { report_id: props.report.id, ...ytForm.value }, {
    onFinish: () => { savingReport.value = false; },
  });
};

// ─── Meetings stats ──────────────────────────────────────────
const recordedMeetings = computed(() => props.meetings.filter(m => m.total_present > 0));
const avgPresent = computed(() => {
  if (!recordedMeetings.value.length) return 0;
  return Math.round(recordedMeetings.value.reduce((s, m) => s + m.total_present, 0) / recordedMeetings.value.length);
});
const avgOnline = computed(() => {
  const r = recordedMeetings.value.filter(m => m.total_online > 0);
  if (!r.length) return 0;
  return Math.round(r.reduce((s, m) => s + m.total_online, 0) / r.length);
});
const totalPresent = computed(() => recordedMeetings.value.reduce((s, m) => s + m.total_present, 0));

const formatDate = (d) => {
  if (!d) return '';
  const dt = new Date(d);
  const days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
  return `${days[dt.getDay()]} ${String(dt.getDate()).padStart(2,'0')}/${String(dt.getMonth()+1).padStart(2,'0')}`;
};

// ─── Bar chart ───────────────────────────────────────────────
const maxPresent = computed(() => Math.max(...props.chartData.map(d => d.present), 1));
const barHeight = (val) => Math.max(val / maxPresent.value * 160, val > 0 ? 4 : 0);

// ─── Pie chart ───────────────────────────────────────────────
const pieColors = ['#3b82f6','#10b981','#f59e0b','#8b5cf6','#ef4444','#06b6d4','#f97316'];
const deptTotal = computed(() => props.deptAttendance.reduce((s, d) => s + d.total, 0));
const deptPercent = (val) => deptTotal.value ? Math.round(val / deptTotal.value * 100) : 0;
const pieSegments = computed(() => {
  let offset = 25; // start from top
  return props.deptAttendance.map(d => {
    const pct = deptPercent(d.total);
    const seg = { value: pct, offset };
    offset = offset - pct + 100;
    if (offset >= 100) offset -= 100;
    return seg;
  });
});

// ─── Incidents ───────────────────────────────────────────────
const incidentList = ref([...props.report.incidents]);
const showIncidentForm = ref(false);
const savingIncident = ref(false);
const incidentForm = ref({ week_label: 'Tuần 1', incident_description: '', resolution: '', direction: '', status: 'pending' });

const submitIncident = () => {
  savingIncident.value = true;
  router.post(route('deacon.incident.store'), {
    report_id: props.report.id,
    ...incidentForm.value,
  }, {
    onSuccess: () => { showIncidentForm.value = false; savingIncident.value = false; },
    onError: () => { savingIncident.value = false; },
  });
};

const updateIncident = (inc) => {
  router.put(route('deacon.incident.update', inc.id), {
    week_label:           inc.week_label,
    incident_description: inc.incident_description,
    resolution:           inc.resolution,
    direction:            inc.direction,
    status:               inc.status,
  }, { preserveScroll: true });
};

const deleteIncident = (inc) => {
  if (!confirm('Xác nhận xoá sự cố này?')) return;
  router.delete(route('deacon.incident.destroy', inc.id), { preserveScroll: true });
};

const statusLabel = (s) => ({ pending: 'Chưa xử lý', in_progress: 'Đang xử lý', resolved: '✓ Đã xử lý' })[s] || s;

// ─── AI Insights ─────────────────────────────────────────────
const aiInsights = computed(() => {
  const insights = [];
  const avg = avgPresent.value;
  const prevAvg = props.chartData.length >= 2
    ? props.chartData[props.chartData.length - 2]?.present || 0
    : 0;

  if (avg === 0) {
    insights.push({ icon: '📭', title: 'Chưa có số liệu', body: 'Chưa có số liệu điểm danh tháng này. Hãy nhập số liệu cho từng buổi nhóm.' });
    return insights;
  }

  // Attendance trend
  if (prevAvg > 0) {
    const diff = avg - prevAvg;
    if (diff > 0) {
      insights.push({ icon: '📈', title: `Tăng ${diff} người so với tháng trước`, body: `Trung bình tháng này: ${avg} người. Tháng trước: ${prevAvg} người. Xu hướng tích cực, tiếp tục duy trì!` });
    } else if (diff < 0) {
      insights.push({ icon: '📉', title: `Giảm ${Math.abs(diff)} người so với tháng trước`, body: `Trung bình tháng này: ${avg} người. Tháng trước: ${prevAvg} người. Cần xem xét nguyên nhân và có kế hoạch khích lệ.` });
    } else {
      insights.push({ icon: '➡️', title: 'Ổn định so với tháng trước', body: `Trung bình tháng này và tháng trước cùng: ${avg} người.` });
    }
  }

  // YouTube
  const subs = ytForm.value.yt_subscribers;
  const newSubs = ytForm.value.yt_new_subscribers;
  if (subs > 0) {
    insights.push({ icon: '📺', title: `Kênh YouTube: ${subs.toLocaleString()} đăng ký`, body: `Có ${newSubs || 0} đăng ký mới trong tháng. ${newSubs >= 10 ? 'Tốt! Tăng trưởng ổn định.' : 'Cần đẩy mạnh quảng bá kênh.'}` });
  }

  // Incidents
  const unresolved = incidentList.value.filter(i => i.status !== 'resolved').length;
  if (incidentList.value.length === 0) {
    insights.push({ icon: '✅', title: 'Không có sự cố trong tháng', body: 'Tháng này không ghi nhận sự cố nào. Chương trình diễn ra suôn sẻ.' });
  } else if (unresolved > 0) {
    insights.push({ icon: '⚠️', title: `${unresolved} sự cố chưa được giải quyết`, body: 'Hãy ưu tiên xử lý các sự cố còn tồn đọng trước buổi nhóm tiếp theo.' });
  } else {
    insights.push({ icon: '🔧', title: 'Tất cả sự cố đã được xử lý', body: `Có ${incidentList.value.length} sự cố xảy ra nhưng đã được xử lý hoàn tất.` });
  }

  // Online vs present ratio
  if (avgOnline.value > 0 && avg > 0) {
    const ratio = Math.round(avgOnline.value / avg * 100);
    insights.push({ icon: '🌐', title: `Tỷ lệ Online/Trực tiếp: ${ratio}%`, body: `${ratio > 80 ? 'Lượng xem online cao, cần đảm bảo chất lượng phát sóng.' : ratio > 40 ? 'Cân bằng tốt giữa kênh trực tiếp và online.' : 'Lượng xem online còn thấp, cần quảng bá kênh YouTube hơn.'}` });
  }

  return insights;
});
</script>
