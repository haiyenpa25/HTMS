<template>
  <PortalLayout
      :department="department"
      :available-departments="availableDepartments"
      :is-global-admin="isGlobalAdmin"
      portal-type="deacon"
      @open-switcher="isSwitchOpen = true"
  >
    <div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-2">

      <!-- Header -->
      <div class="flex items-center justify-between mb-2">
        <div>
          <h2 class="text-xl font-black text-gray-900 tracking-tight">Điểm Danh Buổi Nhóm Hội Thánh</h2>
          <p class="text-sm text-gray-500 font-medium mt-1">Chọn buổi nhóm để ghi nhận số lượng tham dự.</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 flex items-center space-x-4 overflow-x-auto">
        <div class="flex items-center space-x-2 shrink-0">
          <span class="text-sm font-bold text-gray-700">Tháng:</span>
          <select v-model="filters.month"
            class="border-gray-200 rounded-xl text-sm font-medium focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2 pl-3 pr-8">
            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
          </select>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
          <span class="text-sm font-bold text-gray-700">Năm:</span>
          <select v-model="filters.year"
            class="border-gray-200 rounded-xl text-sm font-medium focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2 pl-3 pr-8">
            <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
        <div class="ml-auto shrink-0">
          <span class="text-xs font-medium text-gray-400">{{ meetings.data?.length || 0 }} buổi nhóm</span>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!meetings.data || meetings.data.length === 0"
        class="bg-white rounded-3xl shadow-sm p-8 border border-gray-100 flex flex-col items-center justify-center text-center">
        <div class="w-16 h-16 mb-4 rounded-full bg-gray-50 text-gray-400 flex items-center justify-center">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900">Chưa có buổi nhóm nào</h3>
        <p class="text-gray-500 text-sm mt-1 max-w-sm">
          Không tìm thấy buổi nhóm Hội Thánh nào cho Tháng {{ filters.month }}/{{ filters.year }}.
        </p>
      </div>

      <!-- Card List of Meetings -->
      <div v-else class="space-y-4">
        <div
          v-for="meeting in meetings.data"
          :key="meeting.id"
          class="block bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all group cursor-pointer relative"
          @click="router.get(route('deacon.attendance.show', meeting.id))"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-start space-x-4 pr-16">
              <!-- Date Icon -->
              <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex flex-col items-center justify-center shrink-0 border border-emerald-100 group-hover:bg-emerald-500 group-hover:text-white group-hover:border-emerald-500 transition-colors">
                <span class="text-[10px] font-bold uppercase leading-none mb-0.5">
                  {{ new Date(meeting.date).toLocaleDateString('vi-VN', { month: 'short' }) }}
                </span>
                <span class="text-lg font-black leading-none">{{ new Date(meeting.date).getDate() }}</span>
              </div>

              <!-- Info -->
              <div class="min-w-0">
                <h3 class="font-bold text-gray-900 text-base sm:text-lg group-hover:text-emerald-700 transition-colors truncate">
                  {{ meeting.topic || 'Buổi Nhóm Chủ Nhật' }}
                </h3>
                <div class="flex items-center flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500 font-medium mt-1">
                  <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ meeting.time ? meeting.time.substring(0,5) : '--:--' }}
                  </span>
                  <span v-if="meeting.speaker_name || meeting.preacher" class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ meeting.speaker_name || meeting.preacher }}
                  </span>
                  <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">Hội Thánh</span>
                </div>
                <!-- Attendance preview if recorded -->
                <div v-if="meeting.record" class="mt-2 flex items-center gap-3 text-xs">
                  <span class="bg-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded-full">
                    ✓ {{ meeting.record.total_present }} hiện diện
                  </span>
                  <span v-if="meeting.record.total_online > 0" class="bg-blue-100 text-blue-700 font-bold px-2 py-0.5 rounded-full">
                    {{ meeting.record.total_online }} online
                  </span>
                </div>
              </div>
            </div>

            <!-- Status + Arrow -->
            <div class="absolute right-4 top-4 sm:right-5 sm:top-5 flex items-center gap-2">
              <span v-if="meeting.record"
                class="text-[10px] font-black px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 hidden sm:inline-flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Đã ghi
              </span>
              <span v-else class="text-[10px] font-black px-2 py-1 rounded-full bg-amber-100 text-amber-700 hidden sm:inline-flex">
                Chờ nhập
              </span>
              <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 text-gray-400 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-colors shrink-0">
                <svg class="w-4 h-4" transform="rotate(-45)" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="meetings.links && meetings.links.length > 3" class="flex justify-center mt-6 overflow-x-auto pb-2">
          <div class="flex space-x-1 shrink-0">
            <template v-for="(link, i) in meetings.links" :key="i">
              <Link v-if="link.url" :href="link.url"
                class="px-3 py-1 text-sm font-medium rounded-lg transition-colors border"
                :class="link.active ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'"
                v-html="link.label" />
              <span v-else class="px-3 py-1 text-sm font-medium rounded-lg border border-transparent text-gray-400" v-html="link.label" />
            </template>
          </div>
        </div>
      </div>

    </div>

    <!-- Context Switcher -->
    <SlideOver v-model="isSwitchOpen" title="Chuyển đổi Vai Trò Chấp Sự" size="md">
      <template #default>
        <div class="p-6 space-y-5">
          <p class="text-sm text-gray-500 font-medium">Chọn vai trò để làm việc.</p>
          <div class="space-y-2">
            <div v-for="dept in availableDepartments" :key="dept.id"
              @click="switchDept(dept.id)"
              class="w-full text-left p-4 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between group"
              :class="department?.id === dept.id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-white hover:border-gray-300 hover:bg-gray-50'"
            >
              <div class="flex items-center space-x-4 shrink-0">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-sm"
                  :class="department?.id === dept.id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'">
                  {{ dept.name.charAt(0) }}
                </div>
                <div>
                  <h4 class="text-sm font-black" :class="department?.id === dept.id ? 'text-blue-900' : 'text-gray-900'">{{ dept.name }}</h4>
                  <span v-if="department?.id === dept.id" class="text-xs text-blue-600 font-bold">Đang hoạt động</span>
                </div>
              </div>
              <button v-if="department?.id !== dept.id" class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">Chọn</button>
              <svg v-else class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
          </div>
        </div>
      </template>
    </SlideOver>

  </PortalLayout>
</template>

<script setup>
import { reactive, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import SlideOver from '@/Components/SlideOver.vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  meetings:             { type: Object, default: () => ({ data: [], links: [] }) },
  department:           { type: Object, default: () => ({}) },
  availableDepartments: { type: Array,  default: () => [] },
  isGlobalAdmin:        { type: Boolean, default: false },
  filters:              { type: Object, default: () => ({}) },
});

const isSwitchOpen = ref(false);

const currentYear = new Date().getFullYear();
const availableYears = [currentYear - 2, currentYear - 1, currentYear, currentYear + 1];

const filters = reactive({
  month: props.filters?.month || new Date().getMonth() + 1,
  year:  props.filters?.year  || currentYear,
});

// Auto-filter when month/year changes (same as Portal pattern)
watch(filters, debounce(() => {
  router.get(route('deacon.attendance'), {
    month: filters.month,
    year:  filters.year,
  }, { preserveState: true, replace: true });
}, 300));

const switchDept = (roleId) => {
  router.post(route('deacon.switch-role'), { role: roleId }, {
    preserveScroll: true,
    onSuccess: () => { isSwitchOpen.value = false; }
  });
};
</script>
