<template>
  <PortalLayout
      :department="department"
      :available-departments="availableDepartments"
      :is-global-admin="isGlobalAdmin"
      portal-type="deacon"
      @open-switcher="() => {}"
  >
    <div class="p-4 sm:p-6 lg:p-8 max-w-3xl mx-auto space-y-6">

      <!-- Back breadcrumb -->
      <div class="flex items-center gap-2">
        <Link :href="route('deacon.attendance', { month: new Date(meeting.date).getMonth()+1, year: new Date(meeting.date).getFullYear() })"
          class="flex items-center gap-1.5 text-sm font-bold text-blue-600 hover:text-blue-800 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          Quay lại danh sách
        </Link>
        <span class="text-gray-300">/</span>
        <span class="text-sm text-gray-500">Điểm danh</span>
      </div>

      <!-- Meeting Card -->
      <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-3xl p-6 text-white shadow-xl shadow-blue-200">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-1">Buổi nhóm Hội Thánh</p>
            <h2 class="text-2xl font-black leading-tight">{{ meeting.topic || 'Buổi Nhóm Chủ Nhật' }}</h2>
            <p v-if="meeting.memory_verse" class="text-blue-100 text-sm mt-1 italic">"{{ meeting.memory_verse }}"</p>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center shrink-0 ml-4">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
          </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-blue-100">
          <span class="flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ formatDate(meeting.date) }}
          </span>
          <span v-if="meeting.scripture" class="flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            {{ meeting.scripture }}
          </span>
          <span v-if="meeting.speaker_name || meeting.preacher" class="flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            {{ meeting.speaker_name || meeting.preacher }}
          </span>
        </div>
        <div v-if="prevMonthAvg > 0" class="mt-4 bg-white/10 rounded-2xl px-4 py-2 inline-flex items-center gap-2 text-sm">
          <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
          TB tháng trước: <strong class="font-black">{{ prevMonthAvg }}</strong> người
        </div>
      </div>

      <!-- Flash success -->
      <div v-if="$page.props.flash?.success" class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3 text-emerald-700 text-sm font-bold flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ $page.props.flash.success }}
      </div>

      <!-- Attendance Form -->
      <form @submit.prevent="submit" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-base font-black text-gray-900">Nhập Số Liệu Điểm Danh</h3>
          <span v-if="record" class="text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full">Đã có dữ liệu – cập nhật</span>
          <span v-else class="text-xs font-bold text-amber-600 bg-amber-50 px-3 py-1 rounded-full">Chưa nhập</span>
        </div>
        <div class="p-6 grid grid-cols-2 sm:grid-cols-2 gap-5">
          <!-- Total Present -->
          <div class="col-span-2 sm:col-span-1">
            <label class="block text-sm font-black text-gray-700 mb-1.5">
              <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                Tổng Hiện Diện
                <span class="text-red-500">*</span>
              </span>
            </label>
            <input v-model.number="form.total_present" type="number" min="0"
              class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-2xl font-black text-gray-900 text-center focus:outline-none focus:border-blue-400 transition-colors"
              placeholder="0"
            />
          </div>

          <!-- Online -->
          <div class="col-span-2 sm:col-span-1">
            <label class="block text-sm font-black text-gray-700 mb-1.5">
              <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span>
                Xem Online (Youtube)
              </span>
            </label>
            <input v-model.number="form.total_online" type="number" min="0"
              class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-2xl font-black text-blue-700 text-center focus:outline-none focus:border-blue-400 transition-colors"
              placeholder="0"
            />
          </div>

          <!-- Children -->
          <div>
            <label class="block text-sm font-black text-gray-700 mb-1.5">
              <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-purple-400 inline-block"></span>
                Trẻ em
              </span>
            </label>
            <input v-model.number="form.total_children" type="number" min="0"
              class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-xl font-bold text-center focus:outline-none focus:border-purple-300 transition-colors"
              placeholder="0"
            />
          </div>

          <!-- Visitors -->
          <div>
            <label class="block text-sm font-black text-gray-700 mb-1.5">
              <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-orange-400 inline-block"></span>
                Khách
              </span>
            </label>
            <input v-model.number="form.total_visitors" type="number" min="0"
              class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-xl font-bold text-center focus:outline-none focus:border-orange-300 transition-colors"
              placeholder="0"
            />
          </div>

          <!-- Notes -->
          <div class="col-span-2">
            <label class="block text-sm font-black text-gray-700 mb-1.5">Ghi chú</label>
            <textarea v-model="form.notes" rows="2"
              class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 focus:outline-none focus:border-blue-300 transition-colors resize-none"
              placeholder="Ghi chú thêm về buổi nhóm này..."
            ></textarea>
          </div>
        </div>

        <!-- Summary preview -->
        <div v-if="form.total_present > 0" class="mx-6 mb-4 bg-gray-50 rounded-2xl p-4 grid grid-cols-4 gap-2 text-center">
          <div>
            <p class="text-xs text-gray-500 font-bold">Hiện diện</p>
            <p class="text-xl font-black text-emerald-600">{{ form.total_present }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-bold">Online</p>
            <p class="text-xl font-black text-blue-600">{{ form.total_online || 0 }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-bold">Trẻ em</p>
            <p class="text-xl font-black text-purple-600">{{ form.total_children || 0 }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 font-bold">Khách</p>
            <p class="text-xl font-black text-orange-600">{{ form.total_visitors || 0 }}</p>
          </div>
        </div>

        <div class="px-6 pb-6">
          <button type="submit" :disabled="submitting || form.total_present === ''"
            class="w-full py-3.5 bg-blue-600 text-white font-black rounded-2xl hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-50 text-base flex items-center justify-center gap-2">
            <svg v-if="submitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            {{ submitting ? 'Đang lưu...' : (record ? '🔄 Cập nhật số liệu' : '💾 Lưu điểm danh') }}
          </button>
        </div>
      </form>

    </div>
  </PortalLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
  meeting:              { type: Object, required: true },
  record:               { type: Object, default: null },
  department:           { type: Object, default: () => ({}) },
  availableDepartments: { type: Array,  default: () => [] },
  isGlobalAdmin:        { type: Boolean, default: false },
  prevMonthAvg:         { type: Number, default: 0 },
});

const submitting = ref(false);
const form = ref({
  total_present:  props.record?.total_present  ?? '',
  total_online:   props.record?.total_online   ?? 0,
  total_children: props.record?.total_children ?? 0,
  total_visitors: props.record?.total_visitors ?? 0,
  notes:          props.record?.notes          ?? '',
});

const formatDate = (d) => {
  if (!d) return '';
  const dt = new Date(d);
  const days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
  return `${days[dt.getDay()]} ${String(dt.getDate()).padStart(2,'0')}/${String(dt.getMonth()+1).padStart(2,'0')}/${dt.getFullYear()}`;
};

const submit = () => {
  if (submitting.value) return;
  submitting.value = true;
  router.post(route('deacon.attendance.store', props.meeting.id), form.value, {
    onFinish: () => { submitting.value = false; },
  });
};
</script>
