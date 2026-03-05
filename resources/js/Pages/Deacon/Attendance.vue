<template>
  <PortalLayout
      :department="department"
      :available-departments="availableDepartments"
      :is-global-admin="isGlobalAdmin"
      portal-type="deacon"
      @open-switcher="isSwitchOpen = true"
  >
    <div class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto space-y-6">

      <!-- Page Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-2xl font-black text-gray-900">Điểm Danh Hội Thánh</h2>
          <p class="text-sm text-gray-500 mt-0.5">Quản lý số liệu điểm danh các buổi nhóm Chủ Nhật & Lễ đặc biệt.</p>
        </div>
        <!-- Summary badge -->
        <div class="flex items-center gap-3 shrink-0">
          <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-2 text-center">
            <p class="text-xs text-emerald-600 font-bold">Đã ghi nhận</p>
            <p class="text-xl font-black text-emerald-700">{{ recordedCount }}/{{ meetings.length }}</p>
          </div>
          <div class="bg-amber-50 border border-amber-200 rounded-2xl px-4 py-2 text-center">
            <p class="text-xs text-amber-600 font-bold">TB tháng này</p>
            <p class="text-xl font-black text-amber-700">{{ avgPresent || '—' }}</p>
          </div>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Tháng</label>
          <select v-model="form.month" class="border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            <option v-for="m in monthOptions" :key="m" :value="m">Tháng {{ m }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-500 mb-1">Năm</label>
          <select v-model="form.year" class="border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
        <button @click="applyFilter" class="px-5 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
          Lọc
        </button>
        <div class="ml-auto">
          <span class="text-xs text-gray-400 font-medium">
            Hiển thị {{ meetings.length }} buổi nhóm tháng {{ filterMonth }}/{{ filterYear }}
          </span>
        </div>
      </div>

      <!-- Meetings List -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div v-if="meetings.length === 0" class="p-12 text-center">
          <div class="w-16 h-16 mx-auto bg-gray-50 text-gray-300 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
          </div>
          <h3 class="text-lg font-bold text-gray-700">Không có buổi nhóm nào</h3>
          <p class="text-sm text-gray-400 mt-1">Chưa có buổi nhóm Hội Thánh trong tháng {{ filterMonth }}/{{ filterYear }}.</p>
        </div>

        <table v-else class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
              <th class="text-left px-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wider">Ngày</th>
              <th class="text-left px-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wider hidden sm:table-cell">Chủ đề / Diễn giả</th>
              <th class="text-center px-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wider">Hiện diện</th>
              <th class="text-center px-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wider hidden md:table-cell">Youtube</th>
              <th class="text-center px-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wider">Trạng thái</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50">
            <tr v-for="m in meetings" :key="m.id" class="hover:bg-blue-50/30 transition-colors group">
              <td class="px-4 py-3.5 whitespace-nowrap">
                <div class="font-black text-gray-900">{{ formatDate(m.date) }}</div>
                <div class="text-xs text-gray-400">{{ m.time?.substring(0,5) }}</div>
              </td>
              <td class="px-4 py-3.5 hidden sm:table-cell">
                <div class="font-bold text-gray-800 leading-tight">{{ m.topic || '—' }}</div>
                <div class="text-xs text-gray-400 mt-0.5">{{ m.speaker_name || m.preacher || '—' }}</div>
              </td>
              <td class="px-4 py-3.5 text-center">
                <span v-if="m.record" class="text-2xl font-black text-gray-900">{{ m.record.total_present }}</span>
                <span v-else class="text-gray-300 text-lg font-bold">—</span>
              </td>
              <td class="px-4 py-3.5 text-center hidden md:table-cell">
                <span v-if="m.record" class="text-lg font-bold text-blue-600">{{ m.record.total_online || '—' }}</span>
                <span v-else class="text-gray-300">—</span>
              </td>
              <td class="px-4 py-3.5 text-center">
                <span v-if="m.record" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                  Đã ghi
                </span>
                <span v-else class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                  Chờ nhập
                </span>
              </td>
              <td class="px-4 py-3.5 text-right">
                <Link :href="route('deacon.attendance.show', m.id)"
                  class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all"
                  :class="m.record ? 'text-blue-600 bg-blue-50 hover:bg-blue-100' : 'text-white bg-blue-600 hover:bg-blue-700 shadow-sm'"
                >
                  {{ m.record ? 'Sửa' : 'Nhập' }}
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </Link>
              </td>
            </tr>
          </tbody>
          <!-- Footer tổng kết -->
          <tfoot v-if="meetings.length > 0">
            <tr class="bg-gray-50 border-t border-gray-200">
              <td colspan="2" class="px-4 py-3 text-sm font-black text-gray-600">TRUNG BÌNH</td>
              <td class="px-4 py-3 text-center text-sm font-black text-gray-900">{{ avgPresent || '—' }}</td>
              <td class="px-4 py-3 text-center text-sm font-black text-blue-700 hidden md:table-cell">{{ avgOnline || '—' }}</td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>

    </div>

    <!-- Context Switcher -->
    <SlideOver v-model="isSwitchOpen" title="Chuyển đổi Vai Trò Chấp Sự" size="md">
      <template #default>
        <div class="p-6 space-y-3">
          <p class="text-sm text-gray-500">Chọn vai trò để tiếp tục.</p>
          <div v-for="dept in availableDepartments" :key="dept.id"
            @click="switchDept(dept.id)"
            class="p-4 rounded-xl border-2 cursor-pointer transition-all flex items-center justify-between"
            :class="department.id === dept.id ? 'border-amber-500 bg-amber-50' : 'border-gray-100 hover:border-gray-300'"
          >
            <h4 class="font-black text-sm" :class="department.id === dept.id ? 'text-amber-900' : 'text-gray-900'">{{ dept.name }}</h4>
            <svg v-if="department.id === dept.id" class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          </div>
        </div>
      </template>
    </SlideOver>

  </PortalLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import SlideOver from '@/Components/SlideOver.vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
  meetings:             { type: Array,  default: () => [] },
  department:           { type: Object, default: () => ({}) },
  availableDepartments: { type: Array,  default: () => [] },
  isGlobalAdmin:        { type: Boolean, default: false },
  filterMonth:          { type: Number, default: new Date().getMonth() + 1 },
  filterYear:           { type: Number, default: new Date().getFullYear() },
  yearOptions:          { type: Array,  default: () => [] },
  monthOptions:         { type: Array,  default: () => [] },
});

const isSwitchOpen = ref(false);
const form = ref({ month: props.filterMonth, year: props.filterYear });

const recordedCount = computed(() => props.meetings.filter(m => m.record).length);
const avgPresent = computed(() => {
  const recorded = props.meetings.filter(m => m.record);
  if (!recorded.length) return 0;
  return Math.round(recorded.reduce((sum, m) => sum + m.record.total_present, 0) / recorded.length);
});
const avgOnline = computed(() => {
  const recorded = props.meetings.filter(m => m.record && m.record.total_online);
  if (!recorded.length) return 0;
  return Math.round(recorded.reduce((sum, m) => sum + m.record.total_online, 0) / recorded.length);
});

const formatDate = (d) => {
  if (!d) return '';
  const dt = new Date(d);
  const days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
  return `${days[dt.getDay()]} ${String(dt.getDate()).padStart(2,'0')}/${String(dt.getMonth()+1).padStart(2,'0')}/${dt.getFullYear()}`;
};

const applyFilter = () => {
  router.get(route('deacon.attendance'), { month: form.value.month, year: form.value.year }, { preserveScroll: true });
};

const switchDept = (roleId) => {
  router.post(route('deacon.switch-role'), { role: roleId }, {
    preserveScroll: true,
    onSuccess: () => { isSwitchOpen.value = false; }
  });
};
</script>
