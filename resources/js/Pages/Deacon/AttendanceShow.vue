<template>
  <PortalLayout
      :department="department"
      :available-departments="availableDepartments"
      :is-global-admin="isGlobalAdmin"
      portal-type="deacon"
      :hide-nav="true"
      @open-switcher="() => {}"
  >
    <!-- Sticky Header (same pattern as Portal/Attendance/Show.vue) -->
    <div class="bg-white border-b border-gray-100 sticky top-0 z-10 px-4 py-3 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full flex items-center justify-between">
      <Link
        :href="route('deacon.attendance', { month: new Date(meeting.date).getMonth()+1, year: new Date(meeting.date).getFullYear() })"
        class="flex items-center text-gray-500 hover:text-gray-900 font-bold text-sm transition-colors"
      >
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Quay lại
      </Link>
      <div class="text-right">
        <h2 class="text-sm font-black text-gray-900 truncate max-w-[150px] sm:max-w-xs">{{ meeting.topic || 'Buổi Nhóm Hội Thánh' }}</h2>
        <p class="text-[10px] sm:text-xs text-gray-500 font-medium">{{ formattedDate }}</p>
      </div>
    </div>

    <div class="pb-28 max-w-3xl mx-auto mt-4 px-0 sm:px-4 lg:px-8">

      <!-- Meeting Info Card -->
      <div class="bg-white sm:rounded-[2rem] shadow-sm border-x-0 sm:border border-gray-100 overflow-hidden">

        <!-- Meeting details bar -->
        <div class="bg-gray-50 border-b border-gray-100 px-6 py-4">
          <h3 class="font-bold text-gray-900 text-sm">Điểm danh tổng — Báo cáo số lượng Hội Thánh</h3>
          <div class="flex flex-wrap gap-x-5 gap-y-1 mt-1 text-xs text-gray-500">
            <span v-if="meeting.scripture" class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
              {{ meeting.scripture }}
            </span>
            <span v-if="meeting.memory_verse" class="italic">{{ meeting.memory_verse }}</span>
            <span v-if="meeting.speaker_name || meeting.preacher" class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              {{ meeting.speaker_name || meeting.preacher }}
            </span>
          </div>
        </div>

        <div class="p-4 sm:p-8 space-y-6">

          <!-- Flash -->
          <div v-if="$page.props.flash?.success" class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3 text-emerald-700 text-sm font-bold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $page.props.flash.success }}
          </div>

          <!-- Comparison badge -->
          <div v-if="prevMonthAvg > 0" class="flex items-center justify-between bg-gray-50 p-4 rounded-2xl border border-gray-100">
            <div>
              <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Đối chiếu tháng trước</p>
              <div class="flex items-center gap-3">
                <span class="text-sm text-gray-600">TB tháng trước: <strong class="text-gray-900">{{ prevMonthAvg }} người</strong></span>
              </div>
            </div>
            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
              </svg>
            </div>
          </div>

          <!-- Main input: Total Present (big number — same style as Portal) -->
          <div>
            <label class="block text-sm font-bold text-gray-900 mb-2">Số lượng người tham dự thực tế</label>
            <p class="text-xs text-gray-500 mb-4">Số người Thư Ký đếm được trong buổi nhóm Hội Thánh.</p>
            <div class="relative max-w-sm mx-auto sm:mx-0">
              <input
                type="number"
                v-model="form.total_present"
                min="0"
                class="block w-full text-center text-4xl sm:text-5xl font-black text-emerald-600 border-2 border-gray-200 rounded-[2rem] py-6 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all placeholder:text-gray-300"
                placeholder="0"
              />
              <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none">
                <span class="text-gray-400 font-bold text-sm">Người</span>
              </div>
            </div>
            <div v-if="form.errors.total_present" class="text-red-500 text-xs mt-2 font-medium">{{ form.errors.total_present }}</div>
          </div>

          <!-- Secondary inputs -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">
                <span class="flex items-center gap-1.5">
                  <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>
                  Xem Online (YouTube)
                </span>
              </label>
              <input v-model.number="form.total_online" type="number" min="0"
                class="block w-full text-center text-xl font-bold text-blue-600 border-2 border-gray-200 rounded-2xl py-4 focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition-all"
                placeholder="0" />
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">
                <span class="flex items-center gap-1.5">
                  <span class="w-2.5 h-2.5 rounded-full bg-orange-400 inline-block"></span>
                  Khách
                </span>
              </label>
              <input v-model.number="form.total_visitors" type="number" min="0"
                class="block w-full text-center text-xl font-bold text-orange-600 border-2 border-gray-200 rounded-2xl py-4 focus:ring-2 focus:ring-orange-100 focus:border-orange-400 transition-all"
                placeholder="0" />
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-bold text-gray-900 mb-2">Ghi chú (Tùy chọn)</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="block w-full border-gray-200 rounded-2xl shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm resize-none transition-colors"
              placeholder="Ghi chú về buổi nhóm (vd: Có nhiều khách mời, thời tiết xấu...)"
            ></textarea>
            <div v-if="form.errors.notes" class="text-red-500 text-xs mt-2 font-medium">{{ form.errors.notes }}</div>
          </div>

        </div>
      </div>
    </div>

    <!-- Floating Submit Button (same pattern as Portal/Attendance/Show.vue) -->
    <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/80 backdrop-blur-md border-t border-gray-100 z-20"
      style="padding-bottom: calc(1rem + env(safe-area-inset-bottom));">
      <div class="max-w-3xl mx-auto flex items-center justify-between">
        <div class="hidden sm:block">
          <p class="text-sm font-bold text-gray-900">Buổi nhóm Hội Thánh</p>
          <p class="text-xs text-gray-500">{{ formattedDate }}</p>
        </div>
        <button
          @click="submit"
          :disabled="form.processing"
          class="w-full sm:w-auto px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-black rounded-xl shadow-lg shadow-emerald-600/20 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
        >
          <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ form.processing ? 'Đang lưu...' : (record ? 'Cập nhật điểm danh' : 'Lưu điểm danh') }}
        </button>
      </div>
    </div>

  </PortalLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
  meeting:              { type: Object, required: true },
  record:               { type: Object, default: null },
  department:           { type: Object, default: () => ({}) },
  availableDepartments: { type: Array,  default: () => [] },
  isGlobalAdmin:        { type: Boolean, default: false },
  prevMonthAvg:         { type: Number, default: 0 },
});

// useForm — Inertia's reactive form helper (same as Portal pattern)
const form = useForm({
  total_present:  props.record?.total_present  ?? '',
  total_online:   props.record?.total_online   ?? 0,
  total_visitors: props.record?.total_visitors ?? 0,
  notes:          props.record?.notes          ?? '',
});

const formattedDate = computed(() => {
  if (!props.meeting.date) return '';
  const d = new Date(props.meeting.date);
  return d.toLocaleDateString('vi-VN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});

const submit = () => {
  form.post(route('deacon.attendance.store', props.meeting.id), {
    preserveScroll: true,
    preserveState: true,
  });
};
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>
