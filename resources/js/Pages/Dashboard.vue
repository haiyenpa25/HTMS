<template>
  <component :is="currentLayout">
    <template #header>Dashboard Mục Sư</template>

    <div class="py-4 space-y-6 max-w-7xl mx-auto">

      <!-- ══ HEADER + FILTER ══ -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h1 class="text-2xl font-black text-gray-900">👨‍💼 Tổng Quan Hội Thánh</h1>
          <p class="text-xs text-gray-500 mt-0.5">Tháng {{ localMonth }}/{{ localYear }} · Cập nhật lúc {{ nowTime }}</p>
        </div>
        <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
          <select v-model="localMonth" @change="reload" class="text-sm font-medium border-none focus:ring-0 p-0 text-gray-700">
            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
          </select>
          <input v-model="localYear" @change="reload" type="number" min="2020" max="2099" class="w-16 text-sm border-none focus:ring-0 p-0 text-center font-medium text-gray-700">
        </div>
      </div>

      <!-- ══ KPI CARDS ══ -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-4 text-white shadow-lg shadow-blue-100">
          <p class="text-[10px] font-bold uppercase tracking-wider text-blue-200">Tổng Tín Hữu</p>
          <p class="text-3xl font-black mt-1">{{ kpi.total_members }}</p>
          <p class="text-xs text-blue-200 mt-1">{{ kpi.active_members }} Chính thức</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-4 text-white shadow-lg shadow-emerald-100">
          <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-100">Mới Tháng Này</p>
          <p class="text-3xl font-black mt-1">{{ kpi.new_this_month }}</p>
          <p class="text-xs text-emerald-100 mt-1">thành viên gia nhập</p>
        </div>
        <div class="rounded-2xl p-4 shadow-sm bg-white border border-gray-100">
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">BC Chờ Duyệt</p>
          <p class="text-3xl font-black mt-1" :class="kpi.pending_reports > 0 ? 'text-amber-600' : 'text-gray-900'">{{ kpi.pending_reports }}</p>
          <p class="text-xs text-gray-400 mt-1">báo cáo ban ngành</p>
        </div>
        <div class="rounded-2xl p-4 shadow-sm bg-white border border-gray-100">
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Thăm Viếng</p>
          <p class="text-3xl font-black mt-1 text-gray-900">{{ visitation_stats.total }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ visitation_stats.completed }} hoàn thành / {{ visitation_stats.planned }} kế hoạch</p>
        </div>
      </div>

      <!-- ══ SECTION 1: BÁO CÁO CHỜ DUYỆT ══ -->
      <div v-if="pending_reports.length > 0" class="bg-amber-50 rounded-2xl border border-amber-200 overflow-hidden">
        <div class="px-5 py-3 bg-amber-500 flex items-center justify-between">
          <h3 class="text-sm font-black text-white">🔔 BÁO CÁO CHỜ DUYỆT</h3>
          <span class="bg-white/30 text-white text-xs font-bold px-3 py-1 rounded-full">{{ pending_reports.length }} báo cáo</span>
        </div>
        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <div v-for="r in pending_reports" :key="r.id"
            class="bg-white rounded-xl px-4 py-3 border border-amber-100 flex items-center justify-between shadow-sm">
            <div>
              <p class="text-sm font-bold text-gray-900">{{ r.dept_name }}</p>
              <p class="text-xs text-gray-500">Tháng {{ r.month }}/{{ r.year }} · Nộp: {{ r.submitted_at }}</p>
            </div>
            <a href="/portal/reports" class="text-amber-600 hover:text-amber-800 text-xs font-bold px-3 py-1 bg-amber-50 rounded-lg border border-amber-200">Duyệt</a>
          </div>
        </div>
      </div>

      <!-- ══ SECTION 2: BẢNG BUỔI NHÓM ══ -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-blue-900 flex items-center justify-between">
          <h3 class="text-sm font-black text-white">📅 CÁC BUỔI NHÓM THÁNG {{ localMonth }}/{{ localYear }}</h3>
          <div class="flex gap-2">
            <button @click="meetingFilter = 'all'" class="text-xs font-bold px-3 py-1 rounded-full transition-colors"
              :class="meetingFilter === 'all' ? 'bg-white text-blue-900' : 'text-blue-200 hover:bg-white/10'">Tất cả</button>
            <button @click="meetingFilter = 'church'" class="text-xs font-bold px-3 py-1 rounded-full transition-colors"
              :class="meetingFilter === 'church' ? 'bg-white text-blue-900' : 'text-blue-200 hover:bg-white/10'">Hội Thánh</button>
            <button @click="meetingFilter = 'department'" class="text-xs font-bold px-3 py-1 rounded-full transition-colors"
              :class="meetingFilter === 'department' ? 'bg-white text-blue-900' : 'text-blue-200 hover:bg-white/10'">Ban Ngành</button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-blue-50">
              <tr>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900">Ngày</th>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900">Ban ngành</th>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900">Chủ đề</th>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900 hidden lg:table-cell">Câu gốc</th>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900 hidden md:table-cell">Diễn giả</th>
                <th class="px-4 py-2.5 text-center text-xs font-bold text-blue-900">HD</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="m in filteredMeetings" :key="m.id" class="hover:bg-blue-50/40">
                <td class="px-4 py-2.5 whitespace-nowrap">
                  <p class="text-xs font-black text-gray-900">{{ m.date }}</p>
                  <p class="text-[10px] text-gray-400 capitalize">{{ m.day }}</p>
                </td>
                <td class="px-4 py-2.5">
                  <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                    :class="m.type === 'church' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'">
                    {{ m.dept_name }}
                  </span>
                </td>
                <td class="px-4 py-2.5 text-xs text-gray-800 max-w-[160px] truncate">{{ m.topic || '—' }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-600 hidden lg:table-cell max-w-[130px] truncate">{{ m.memory_verse || m.scripture || '—' }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-600 hidden md:table-cell">{{ m.speaker || '—' }}</td>
                <td class="px-4 py-2.5 text-center text-sm font-black text-amber-700">{{ m.attendance > 0 ? m.attendance : '—' }}</td>
              </tr>
              <tr v-if="filteredMeetings.length === 0">
                <td colspan="6" class="px-4 py-6 text-center text-xs text-gray-400">Chưa có buổi nhóm nào trong tháng này</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ══ SECTION 3: BIỂU ĐỒ HIỆN DIỆN ══ -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Biểu đồ ban ngành -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <h3 class="text-sm font-bold text-gray-900 mb-1">📊 Hiện Diện Ban Ngành Theo Tuần</h3>
          <p class="text-[10px] text-gray-400 mb-4">Mỗi đường = 1 ban ngành · Tuần 1-5 trong tháng</p>
          <apexchart v-if="dept_att_series.length > 0" type="line" height="240"
            :options="deptLineOpts" :series="dept_att_series" />
          <div v-else class="h-56 flex items-center justify-center text-gray-300">
            <p class="text-xs">Chưa có dữ liệu</p>
          </div>
        </div>
        <!-- Biểu đồ hội thánh -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <h3 class="text-sm font-bold text-gray-900 mb-1">⛪ Hiện Diện Hội Thánh Theo Tuần</h3>
          <p class="text-[10px] text-gray-400 mb-4">Số lượng tham dự buổi nhóm hội thánh mỗi tuần</p>
          <apexchart type="area" height="240"
            :options="churchLineOpts" :series="[church_att_line]" />
        </div>
      </div>

      <!-- ══ SECTION 4: CƠ ĐỐC GIÁO DỤC ══ -->
      <div class="space-y-4">
        <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
          📚 <span>BAN CƠ ĐỐC GIÁO DỤC</span>
        </h3>

        <!-- 3 loại lớp CGDG -->
        <div v-for="(cgdgGroup, key) in cgdg" :key="key" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-5 py-3 bg-indigo-900 flex items-center justify-between">
            <h4 class="text-sm font-black text-white">{{ cgdgGroup.label }}</h4>
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ cgdgGroup.classes.length }} lớp</span>
          </div>
          <div v-if="cgdgGroup.classes.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-indigo-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-bold text-indigo-900">Lớp</th>
                  <th class="px-4 py-2 text-center text-xs font-bold text-indigo-900">Số buổi</th>
                  <th class="px-4 py-2 text-center text-xs font-bold text-indigo-900">Tổng HD</th>
                  <th class="px-4 py-2 text-left text-xs font-bold text-indigo-900 hidden md:table-cell">Buổi gần nhất</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="cls in cgdgGroup.classes" :key="cls.class_id" class="hover:bg-indigo-50/40">
                  <td class="px-4 py-2.5 text-xs font-bold text-gray-900">{{ cls.class_name }}</td>
                  <td class="px-4 py-2.5 text-center text-sm text-gray-500">{{ cls.sessions.length }}</td>
                  <td class="px-4 py-2.5 text-center text-sm font-black text-amber-700">{{ cls.total }}</td>
                  <td class="px-4 py-2.5 text-xs text-gray-600 hidden md:table-cell">
                    <span v-if="cls.sessions.length > 0">
                      {{ cls.sessions[cls.sessions.length - 1].date }} — {{ cls.sessions[cls.sessions.length - 1].topic || '—' }}
                    </span>
                    <span v-else class="text-gray-300">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="px-5 py-6 text-center text-xs text-gray-400">Chưa có dữ liệu cho tháng này</div>
        </div>
      </div>

      <!-- ══ SECTION 5: SINH NHẬT ══ -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-pink-600 flex items-center justify-between">
          <h3 class="text-sm font-black text-white">🎂 SINH NHẬT THÁNG {{ localMonth }}</h3>
          <span class="bg-white/30 text-white text-xs font-bold px-3 py-1 rounded-full">{{ birthdays.length }} người</span>
        </div>
        <div v-if="birthdays.length > 0">
          <!-- This week highlight -->
          <div v-if="birthdaysThisWeek.length > 0" class="px-5 py-3 bg-pink-50 border-b border-pink-100">
            <p class="text-xs font-bold text-pink-800 mb-2">🎉 Sinh nhật tuần này:</p>
            <div class="flex flex-wrap gap-2">
              <span v-for="b in birthdaysThisWeek" :key="b.id"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-pink-100 text-pink-900 text-xs font-bold rounded-full border border-pink-200">
                {{ b.is_today ? '🎈 ' : '' }}{{ b.full_name }} ({{ b.birth_day }} · {{ b.age }} tuổi)
              </span>
            </div>
          </div>
          <!-- Full list -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-bold text-gray-600">Tên</th>
                  <th class="px-4 py-2 text-center text-xs font-bold text-gray-600">Ngày SN</th>
                  <th class="px-4 py-2 text-center text-xs font-bold text-gray-600">Tuổi</th>
                  <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 hidden sm:table-cell">SĐT</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="b in birthdays" :key="b.id"
                  :class="b.is_today ? 'bg-pink-50' : b.is_this_week ? 'bg-pink-50/40' : 'hover:bg-gray-50/40'">
                  <td class="px-4 py-2.5">
                    <div class="flex items-center gap-2">
                      <span class="text-xs font-bold text-gray-900">{{ b.full_name }}</span>
                      <span v-if="b.is_today" class="text-xs bg-pink-500 text-white px-2 py-0.5 rounded-full font-bold">Hôm nay!</span>
                      <span v-else-if="b.is_this_week" class="text-xs bg-pink-100 text-pink-700 px-2 py-0.5 rounded-full font-bold">Tuần này</span>
                    </div>
                  </td>
                  <td class="px-4 py-2.5 text-center text-sm font-black text-pink-700">{{ b.birth_day }}</td>
                  <td class="px-4 py-2.5 text-center text-xs text-gray-600">{{ b.age }}</td>
                  <td class="px-4 py-2.5 text-xs text-gray-500 hidden sm:table-cell">{{ b.phone || '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else class="px-5 py-8 text-center text-xs text-gray-400">Không có sinh nhật trong tháng {{ localMonth }}</div>
      </div>

      <!-- ══ SECTION 6: THĂM VIẾNG ══ -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-purple-800 flex items-center justify-between">
          <h3 class="text-sm font-black text-white">🤝 THĂM VIẾNG THÁNG {{ localMonth }}</h3>
          <div class="flex gap-2 text-xs font-bold">
            <span class="bg-white/20 text-white px-3 py-1 rounded-full">{{ visitation_stats.total }} lượt</span>
            <span class="bg-green-400/30 text-green-200 px-3 py-1 rounded-full">✓ {{ visitation_stats.completed }}</span>
          </div>
        </div>
        <div v-if="visitations.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600">Ngày</th>
                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600">Tín hữu</th>
                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 hidden md:table-cell">Ban ngành</th>
                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 hidden lg:table-cell">Lý do</th>
                <th class="px-4 py-2 text-center text-xs font-bold text-gray-600">Trạng thái</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="v in visitations" :key="v.id" class="hover:bg-purple-50/30">
                <td class="px-4 py-2.5 text-xs font-medium text-gray-700 whitespace-nowrap">{{ v.visit_date }}</td>
                <td class="px-4 py-2.5 text-xs font-bold text-gray-900">{{ v.member_name }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-600 hidden md:table-cell">{{ v.dept_name }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-500 hidden lg:table-cell max-w-[200px] truncate">{{ v.reason }}</td>
                <td class="px-4 py-2.5 text-center">
                  <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                    :class="v.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'">
                    {{ v.status === 'completed' ? 'Đã thăm' : 'Kế hoạch' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="px-5 py-6 text-center text-xs text-gray-400">Chưa có hoạt động thăm viếng trong tháng này</div>
      </div>

      <!-- ══ SECTION 7 + 8: TÍN HỮU MỚI & NGÀY ĐẶC BIỆT ══ -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Tín hữu mới -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-5 py-3 bg-emerald-700 flex items-center justify-between">
            <h3 class="text-sm font-black text-white">✝️ TÍN HỮU MỚI TIN CHÚA</h3>
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ new_members_30.length + new_members_90.length }}</span>
          </div>

          <!-- 30 ngày -->
          <div v-if="new_members_30.length > 0">
            <div class="px-4 py-2 bg-emerald-50 border-b border-emerald-100">
              <p class="text-xs font-black text-emerald-800">🌟 Trong 30 ngày qua ({{ new_members_30.length }} người)</p>
            </div>
            <table class="min-w-full divide-y divide-gray-100">
              <tbody>
                <tr v-for="m in new_members_30" :key="m.id" class="hover:bg-gray-50/40">
                  <td class="px-4 py-2.5 text-xs font-bold text-gray-900">{{ m.full_name }}</td>
                  <td class="px-4 py-2.5 text-xs text-emerald-700 font-medium text-right">{{ m.faith_date }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- 90 ngày -->
          <div v-if="new_members_90.length > 0">
            <div class="px-4 py-2 bg-gray-50 border-y border-gray-100">
              <p class="text-xs font-bold text-gray-600">30-90 ngày trước ({{ new_members_90.length }} người)</p>
            </div>
            <table class="min-w-full divide-y divide-gray-100">
              <tbody>
                <tr v-for="m in new_members_90" :key="m.id" class="hover:bg-gray-50/40">
                  <td class="px-4 py-2.5 text-xs font-medium text-gray-700">{{ m.full_name }}</td>
                  <td class="px-4 py-2.5 text-xs text-gray-500 text-right">{{ m.faith_date }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="new_members_30.length === 0 && new_members_90.length === 0" class="px-5 py-6 text-center text-xs text-gray-400">
            Không có tín hữu mới trong 90 ngày qua
          </div>
        </div>

        <!-- Ngày đặc biệt -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-5 py-3 bg-amber-600 flex items-center justify-between">
            <h3 class="text-sm font-black text-white">🌟 NGÀY ĐẶC BIỆT THÁNG {{ localMonth }}</h3>
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ special_dates.length }}</span>
          </div>
          <div v-if="special_dates.length > 0">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-amber-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-bold text-amber-900">Tên</th>
                  <th class="px-4 py-2 text-left text-xs font-bold text-amber-900">Loại</th>
                  <th class="px-4 py-2 text-center text-xs font-bold text-amber-900">Ngày</th>
                  <th class="px-4 py-2 text-center text-xs font-bold text-amber-900">Số năm</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="d in special_dates" :key="`${d.id}-${d.type}`" class="hover:bg-amber-50/30">
                  <td class="px-4 py-2.5 text-xs font-bold text-gray-900">{{ d.full_name }}</td>
                  <td class="px-4 py-2.5">
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                      :class="d.type === 'baptism' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'">
                      {{ d.label }}
                    </span>
                  </td>
                  <td class="px-4 py-2.5 text-center text-xs font-black text-amber-700">{{ d.date }}</td>
                  <td class="px-4 py-2.5 text-center text-xs text-gray-500">{{ d.years > 0 ? d.years + ' năm' : 'Năm nay' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="px-5 py-6 text-center text-xs text-gray-400">
            Không có ngày đặc biệt trong tháng {{ localMonth }}
          </div>
        </div>
      </div>

    </div>
  </component>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import VueApexCharts from 'vue3-apexcharts';

const apexchart = VueApexCharts;

const props = defineProps({
    filters: Object,
    kpi: Object,
    pending_reports: Array,
    pending_approvals_count: Number,
    meetings: Array,
    dept_att_series: Array,
    church_att_line: Object,
    cgdg: Object,
    birthdays: Array,
    visitations: Array,
    visitation_stats: Object,
    new_members_30: Array,
    new_members_90: Array,
    special_dates: Array,
});

const page = usePage();
const currentLayout = computed(() => {
    if (typeof window === 'undefined') return AuthenticatedLayout;
    return window.innerWidth < 768 ? MobileLayout : AuthenticatedLayout;
});

const localMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const localYear  = ref(props.filters?.year  || new Date().getFullYear());
const nowTime    = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
const reload = () => router.get('/', { month: localMonth.value, year: localYear.value }, { preserveState: true, replace: true });

// Meeting filter
const meetingFilter = ref('all');
const filteredMeetings = computed(() => {
    if (meetingFilter.value === 'all') return props.meetings || [];
    return (props.meetings || []).filter(m => m.type === meetingFilter.value);
});

// Birthday filter
const birthdaysThisWeek = computed(() => (props.birthdays || []).filter(b => b.is_this_week || b.is_today));

// ══ CHART OPTIONS ══
const WEEKS = ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4', 'Tuần 5'];

const deptLineOpts = {
    chart: { type: 'line', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    stroke: { curve: 'smooth', width: 2.5 },
    xaxis: { categories: WEEKS, labels: { style: { fontSize: '10px' } } },
    yaxis: { labels: { style: { fontSize: '10px' } }, min: 0 },
    legend: { position: 'bottom', fontSize: '10px' },
    colors: ['#6366f1', '#ec4899', '#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6'],
    tooltip: { y: { formatter: v => v + ' người' } },
    grid: { strokeDashArray: 4 },
};

const churchLineOpts = {
    chart: { type: 'area', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    stroke: { curve: 'smooth', width: 2.5 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.02 } },
    xaxis: { categories: WEEKS, labels: { style: { fontSize: '10px' } } },
    yaxis: { labels: { style: { fontSize: '10px' } }, min: 0 },
    colors: ['#2563eb'],
    tooltip: { y: { formatter: v => v + ' người' } },
    grid: { strokeDashArray: 4 },
};
</script>
