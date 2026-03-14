<template>
  <component :is="currentLayout">
    <template #header>Lịch Sự Kiện Trực Quan</template>

    <div class="py-4 space-y-4 w-full">

      <!-- Hero Banner -->
      <div class="rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-700 p-6 sm:p-8 text-white relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 opacity-10 pointer-events-none flex items-center justify-end pr-8">
          <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div class="relative z-10">
          <p class="text-[10px] sm:text-xs font-bold uppercase tracking-[0.2em] text-teal-200 mb-1 sm:mb-2">HỆ THỐNG × LỊCH SỰ KIỆN</p>
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div>
                  <h1 class="text-xl sm:text-3xl font-black tracking-tight text-white">Lịch Tổng Hợp Hội Thánh</h1>
                  <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-teal-100 max-w-lg">Lịch Nhóm, Lịch Trực và tất cả Hoạt động chung được hiển thị trực quan.</p>
              </div>
              
              <div v-if="canManageEvents" class="shrink-0">
                  <button @click="openEventModal" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 text-white text-sm font-bold rounded-xl transition-all shadow-sm ring-1 ring-inset ring-white/20 backdrop-blur-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tạo Sự Kiện
                  </button>
              </div>
          </div>
        </div>
      </div>

      <!-- Bảng màu Phân loại logic Sự kiện -->
      <div class="bg-white px-3 py-3 sm:px-4 sm:py-3 rounded-xl shadow-sm flex flex-wrap gap-2 sm:gap-4 items-center mb-4 text-[10px] sm:text-xs font-semibold ring-1 ring-gray-100">
          <div class="flex items-center gap-1.5 sm:gap-2 px-2 py-1 rounded bg-purple-50 text-purple-700 w-full sm:w-auto"><span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-purple-500 inline-block"></span> Thờ Phượng & Đại Lễ</div>
          <div class="flex items-center gap-1.5 sm:gap-2 px-2 py-1 rounded bg-blue-50 text-blue-700 w-full sm:w-auto"><span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-blue-500 inline-block"></span> Sinh Hoạt & Học KT</div>
          <div class="flex items-center gap-1.5 sm:gap-2 px-2 py-1 rounded bg-orange-50 text-orange-700 w-full sm:w-auto"><span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full bg-orange-500 inline-block"></span> Công Tác Lãnh Đạo</div>
          <div class="flex items-center gap-1.5 sm:gap-2 px-2 py-1 rounded bg-emerald-50 text-emerald-700 w-full sm:w-auto"><span class="w-2 h-2 rounded-full border border-emerald-500 bg-white"></span><span class="w-3 h-3 rounded-full leading-none text-white text-[8px] flex items-center justify-center font-bold bg-emerald-500">T</span> Lịch Trực Sự Vụ</div>
      </div>

      <!-- FullCalendar Area -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden ring-1 ring-gray-100 p-2 sm:p-4">
        <FullCalendar class="custom-calendar text-sm" ref="fullCalendar" :options="calendarOptions" />
      </div>
    </div>

    <!-- S L I D E  O V E R : Thêm / Sửa Sự Kiện -->
    <SlideOver v-model="isModalOpen" :title="isEditing ? 'Chỉnh sửa Sự kiện' : 'Tạo Sự kiện Mới'" size="md">
      <form id="eventForm" @submit.prevent="submitForm">
        <div class="space-y-4">
            <div>
              <InputLabel for="title" value="Tên Sự kiện *" />
              <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full text-sm" required placeholder="VD: Lễ Tiệc Thánh, Rút thăm chia tổ..." />
              <InputError :message="form.errors.title" class="mt-2" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <InputLabel for="start_time" value="Bắt đầu *" />
                <TextInput id="start_time" v-model="form.start_time" type="datetime-local" class="mt-1 block w-full text-sm" required />
                <InputError :message="form.errors.start_time" class="mt-2" />
              </div>
              <div>
                <InputLabel for="end_time" value="Kết thúc" />
                <TextInput id="end_time" v-model="form.end_time" type="datetime-local" class="mt-1 block w-full text-sm" />
                <InputError :message="form.errors.end_time" class="mt-2" />
              </div>
            </div>

            <div class="flex items-center mt-2">
              <input type="checkbox" id="is_all_day" v-model="form.is_all_day" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
              <label for="is_all_day" class="ml-2 block text-sm text-gray-900 font-medium">Sự kiện cả ngày (All day)</label>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <InputLabel for="type" value="Loại Sự Kiện *" />
                <select id="type" v-model="form.type" @change="updateColorByType" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm font-medium" required>
                  <option value="worship" class="text-purple-700 bg-purple-50">Thờ Phượng & Đại Lễ (Chúa Nhật, Phục Sinh...)</option>
                  <option value="fellowship" class="text-blue-700 bg-blue-50">Sinh Hoạt & Học KT (Lớp TCĐ, Nhóm Ngành...)</option>
                  <option value="administration" class="text-orange-700 bg-orange-50">Công Tác Lãnh Đạo (Họp, Cầu nguyện...)</option>
                  <option value="other" class="text-gray-700 bg-gray-50">Hoạt động Khác</option>
                </select>
                <InputError :message="form.errors.type" class="mt-2" />
              </div>
            </div>
            
            <div>
              <InputLabel for="location" value="Địa điểm" />
              <TextInput id="location" v-model="form.location" type="text" class="mt-1 block w-full text-sm" placeholder="VD: Phòng Thanh Niên, Băng ghế phải..." />
            </div>

            <div class="space-y-3">
              <InputLabel value="Ai được xem Sự Kiện này? *" />
              
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="relative flex cursor-pointer rounded-lg border bg-white p-3 shadow-sm focus:outline-none" :class="form.scope_type === 'global' ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200'">
                  <input type="radio" v-model="form.scope_type" value="global" class="sr-only" />
                  <span class="flex flex-1">
                    <span class="flex flex-col">
                      <span class="block text-sm font-medium text-gray-900">Toàn Hệ Thống</span>
                      <span class="mt-1 flex items-center text-xs text-gray-500">Ai cũng thấy</span>
                    </span>
                  </span>
                  <svg class="h-5 w-5 text-blue-600" :class="form.scope_type === 'global' ? 'block' : 'hidden'" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                  </svg>
                </label>

                <label class="relative flex cursor-pointer rounded-lg border bg-white p-3 shadow-sm focus:outline-none" :class="form.scope_type === 'internal' ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200'">
                  <input type="radio" v-model="form.scope_type" value="internal" class="sr-only" />
                  <span class="flex flex-1">
                    <span class="flex flex-col">
                      <span class="block text-sm font-medium text-gray-900">Nội Bộ</span>
                      <span class="mt-1 flex items-center text-xs text-gray-500">Có chung tài khoản</span>
                    </span>
                  </span>
                  <svg class="h-5 w-5 text-blue-600" :class="form.scope_type === 'internal' ? 'block' : 'hidden'" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                </label>

                <label class="relative flex cursor-pointer rounded-lg border bg-white p-3 shadow-sm focus:outline-none" :class="form.scope_type === 'department' ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200'">
                  <input type="radio" v-model="form.scope_type" value="department" class="sr-only" />
                  <span class="flex flex-1">
                    <span class="flex flex-col">
                      <span class="block text-sm font-medium text-gray-900">Ban Ngành</span>
                      <span class="mt-1 flex items-center text-xs text-gray-500">Sinh hoạt cục bộ</span>
                    </span>
                  </span>
                  <svg class="h-5 w-5 text-blue-600" :class="form.scope_type === 'department' ? 'block' : 'hidden'" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                </label>

                <label class="relative flex cursor-pointer rounded-lg border bg-white p-3 shadow-sm focus:outline-none" :class="form.scope_type === 'leadership' ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200'" v-if="canManageEvents">
                  <input type="radio" v-model="form.scope_type" value="leadership" class="sr-only" />
                  <span class="flex flex-1">
                    <span class="flex flex-col">
                      <span class="block text-sm font-medium text-gray-900">Lãnh Đạo</span>
                      <span class="mt-1 flex items-center text-xs text-gray-500">Mục sư/Chấp sự</span>
                    </span>
                  </span>
                  <svg class="h-5 w-5 text-blue-600" :class="form.scope_type === 'leadership' ? 'block' : 'hidden'" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                </label>
              </div>

              <!-- Extra drop down for department -->
              <div v-if="form.scope_type === 'department'" class="mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200 animate-fade-in-up">
                  <InputLabel for="scope_id" value="Chọn Ban Ngành áp dụng *" class="text-xs text-gray-500 uppercase tracking-wider mb-1" />
                  <select id="scope_id" v-model="form.scope_id" class="block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" required>
                    <option value="" disabled selected>-- Chọn một Ban Ngành --</option>
                    <option v-for="dept in $page.props.departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                  </select>
                  <InputError :message="form.errors.scope_id" class="mt-2" />
              </div>
            </div>

            <div>
              <InputLabel for="description" value="Ghi chú chi tiết" />
              <textarea id="description" v-model="form.description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" placeholder="Mục đích, thành phần tham dự..."></textarea>
            </div>
          </div>
      </form>
      <template #footer>
        <div class="flex justify-between w-full">
          <div>
            <button v-if="isEditing" type="button" @click="deleteEvent" class="px-4 py-2 text-sm text-red-600 font-bold hover:bg-red-50 rounded-lg transition-colors">Xoá Sự kiện</button>
          </div>
          <div class="flex gap-3">
            <SecondaryButton type="button" @click="closeModal">Hủy</SecondaryButton>
            <PrimaryButton form="eventForm" type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
              {{ isEditing ? 'Cập nhật' : 'Tạo mới' }}
            </PrimaryButton>
          </div>
        </div>
      </template>
    </SlideOver>

    <!-- S L I D E  O V E R : Xem Chi Tiết Sự Kiện (Buổi nhóm/Trực/...) -->
    <SlideOver v-model="isDetailModalOpen" title="Chi tiết Sự kiện" size="sm">
      <div>
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" :style="`background-color: ${selectedEvent?.backgroundColor}`">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-black text-gray-900 leading-tight">
              {{ selectedEvent?.title }}
              <span v-if="selectedEvent?.extendedProps?.scope_type === 'department'" class="ml-2 inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">Ban: {{ selectedEvent?.extendedProps?.department_name }}</span>
            </h2>
            <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5" v-if="selectedEvent?.extendedProps?.location">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
              {{ selectedEvent?.extendedProps?.location }}
            </p>
          </div>
        </div>
        
        <div class="space-y-3 pt-3 border-t border-gray-100">
          <div class="flex gap-2">
            <span class="text-xs font-bold text-gray-400 w-20 shrink-0">Bắt đầu:</span>
            <span class="text-sm text-gray-900 font-medium">{{ formatDateTime(selectedEvent?.start) }}</span>
          </div>
          <div class="flex gap-2" v-if="selectedEvent?.end">
            <span class="text-xs font-bold text-gray-400 w-20 shrink-0">Kết thúc:</span>
            <span class="text-sm text-gray-900 font-medium">{{ formatDateTime(selectedEvent?.end) }}</span>
          </div>
          <div class="flex gap-2" v-if="selectedEvent?.extendedProps?.description">
            <span class="text-xs font-bold text-gray-400 w-20 shrink-0">Chi tiết:</span>
            <span class="text-sm text-gray-900 whitespace-pre-wrap leading-relaxed">{{ selectedEvent?.extendedProps?.description }}</span>
          </div>
        </div>

      </div>
      <template #footer>
        <div class="flex justify-end gap-2 w-full">
          <button v-if="canEditSelectedEvent" type="button" @click="deleteEventFromDetail" class="px-4 py-2 text-sm text-red-600 font-bold hover:bg-red-50 rounded-lg transition-colors border border-red-200">
            Xoá
          </button>
          <button v-if="canEditSelectedEvent" type="button" @click="editEvent(selectedEvent)" class="px-4 py-2 text-sm text-blue-600 font-bold hover:bg-blue-50 rounded-lg transition-colors border border-blue-200">
            Chỉnh sửa
          </button>
          <PrimaryButton type="button" @click="closeDetailModal">Đóng lại</PrimaryButton>
        </div>
      </template>
    </SlideOver>
  </component>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SlideOver from '@/Components/SlideOver.vue';

// FullCalendar Imports
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';

const page = usePage();
const currentLayout = computed(() => {
    if (typeof window === 'undefined') return AuthenticatedLayout;
    return window.innerWidth < 768 ? MobileLayout : AuthenticatedLayout;
});

const canManageEvents = computed(() => {
    const role = page.props.auth?.user?.role || '';
    return role === 'Super_Admin' || role === 'Pastor' || role === 'Admin';
});

// FullCalendar Setup
const fullCalendar = ref(null);
const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,listMonth'
    },
    buttonText: {
        today: 'Hôm nay',
        month: 'Tháng',
        week: 'Tuần',
        day: 'Ngày',
        list: 'Danh sách'
    },
    locale: 'vi',
    firstDay: 1, // Thứ 2
    height: 'auto',
    nowIndicator: true,
    dayMaxEvents: true,
    selectable: canManageEvents.value,
    editable: false,
    
    // Fetch API
    events: route('calendar.api.events'),
    
    // Callbacks
    select: (info) => {
        if (!canManageEvents.value) return;
        form.reset();
        // Convert dates for input datetime-local
        const start = new Date(info.startStr);
        start.setHours(8, 0, 0, 0); // Default to 8am if month view clicked
        form.start_time = parseLocalDateToInput(start);

        if(info.endStr) {
           const end = new Date(info.endStr);
           if(info.allDay) end.setDate(end.getDate() - 1); // FullCalendar select all-day end is exclusive +1 day
           end.setHours(17, 0, 0, 0);
           form.end_time = parseLocalDateToInput(end);
        }
        
        form.is_all_day = info.allDay;
        isEditing.value = false;
        selectedEventId.value = null;
        isModalOpen.value = true;
    },
    eventClick: (info) => {
        selectedEvent.value = info.event;
        isDetailModalOpen.value = true;
    }
});

// Modals State
const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedEventId = ref(null);
const isDetailModalOpen = ref(false);
const selectedEvent = ref(null);

// Form
const form = useForm({
    title: '',
    description: '',
    start_time: '',
    end_time: '',
    is_all_day: false,
    type: 'worship',
    color: '#8b5cf6', // Default worship purple
    location: '',
    scope_type: 'global',
    scope_id: '',
});

const updateColorByType = () => {
    switch (form.type) {
        case 'worship': form.color = '#8b5cf6'; break; // Purple
        case 'fellowship': form.color = '#3b82f6'; break; // Blue
        case 'administration': form.color = '#f97316'; break; // Orange
        default: form.color = '#64748b'; break; // Gray
    }
};

const openEventModal = () => {
    form.reset();
    form.start_time = parseLocalDateToInput(new Date());
    isEditing.value = false;
    selectedEventId.value = null;
    isModalOpen.value = true;
};

const closeModal = () => isModalOpen.value = false;
const closeDetailModal = () => isDetailModalOpen.value = false;

const canEditSelectedEvent = computed(() => {
    if (!selectedEvent.value) return false;
    const type = selectedEvent.value.extendedProps.type;
    return type === 'event' && canManageEvents.value;
});

const editEvent = (evt) => {
    closeDetailModal();
    form.title = evt.title;
    form.start_time = parseLocalDateToInput(evt.start);
    form.end_time = evt.end ? parseLocalDateToInput(evt.end) : '';
    form.is_all_day = evt.allDay;
    form.type = 'worship'; // fallback logic
    form.color = evt.backgroundColor;
    form.location = evt.extendedProps.location || '';
    form.scope_type = evt.extendedProps.scope_type || 'global';
    form.scope_id = evt.extendedProps.scope_id || '';
    form.description = evt.extendedProps.description || '';
    
    selectedEventId.value = evt.extendedProps.db_id;
    isEditing.value = true;
    isModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('calendar.events.update', selectedEventId.value), {
            onSuccess: () => {
                closeModal();
                refreshCalendar();
            }
        });
    } else {
        form.post(route('calendar.events.store'), {
            onSuccess: () => {
                closeModal();
                refreshCalendar();
            }
        });
    }
};

const deleteEvent = () => {
    if (confirm('Xóa vĩnh viễn sự kiện này khỏi Lịch?')) {
        router.delete(route('calendar.events.destroy', selectedEventId.value), {
            onSuccess: () => {
                closeModal();
                refreshCalendar();
            }
        });
    }
};

const deleteEventFromDetail = () => {
    if (!selectedEvent.value) return;
    if (confirm('Xóa vĩnh viễn sự kiện này khỏi Lịch?')) {
        router.delete(route('calendar.events.destroy', selectedEvent.value.extendedProps.db_id), {
            onSuccess: () => {
                closeDetailModal();
                refreshCalendar();
            }
        });
    }
};

const refreshCalendar = () => {
    if (fullCalendar.value) {
        fullCalendar.value.getApi().refetchEvents();
    }
};

// Utils
const formatDateTime = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return new Intl.DateTimeFormat('vi-VN', { dateStyle: 'full', timeStyle: 'short' }).format(d);
};

const parseLocalDateToInput = (date) => {
    if (!date) return '';
    const d = new Date(date);
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0,16);
};
</script>

<style>
/* Custom FullCalendar Overrides for UI matches */
.custom-calendar {
    --fc-border-color: transparent !important; /* Xóa viền Table */
}
.custom-calendar .fc-theme-standard td, .custom-calendar .fc-theme-standard th, .custom-calendar .fc-theme-standard .fc-scrollgrid {
    border: none !important;
}
.custom-calendar .fc-daygrid-day-frame {
    border: 1px solid #f3f4f6; /* Viền ô nhẹ nhàng */
    border-radius: 0.75rem; /* bo góc từng ô grid */
    margin: 2px;
    min-height: 100px;
    background-color: white;
    transition: all 0.2s;
}
.custom-calendar .fc-daygrid-day-frame:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    border-color: #e5e7eb;
}
/* Today highlight */
.custom-calendar .fc-day-today .fc-daygrid-day-frame {
    background-color: #f0fdfa !important; /* Emerald 50 */
    border-color: #5eead4 !important; /* Emerald 300 */
}
/* Empty days */
.custom-calendar .fc-day-other .fc-daygrid-day-frame {
    background-color: #f9fafb;
    opacity: 0.5;
}

.custom-calendar .fc-toolbar-title {
    font-size: 1.1rem !important;
    font-weight: 800 !important;
    color: #111827;
}
@media (min-width: 640px) {
    .custom-calendar .fc-toolbar-title {
        font-size: 1.25rem !important;
    }
}
.custom-calendar .fc-button-primary {
    background-color: white !important;
    border: 1px solid #e5e7eb !important;
    color: #374151 !important;
    font-weight: 600 !important;
    padding: 0.25rem 0.5rem !important;
    font-size: 0.75rem !important;
    border-radius: 0.5rem !important;
    text-transform: capitalize !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
@media (min-width: 640px) {
    .custom-calendar .fc-button-primary {
        padding: 0.35rem 0.75rem !important;
        font-size: 0.875rem !important;
    }
}
.custom-calendar .fc-button-primary:hover {
    background-color: #f9fafb !important;
    color: #111827 !important;
}
.custom-calendar .fc-button-active, .custom-calendar .fc-button-primary:active {
    background-color: #dbf4ff !important; /* Light blue */
    border-color: #93c5fd !important;
    color: #1d4ed8 !important;
}
.custom-calendar .fc-header-toolbar {
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem !important;
}
.custom-calendar .fc-toolbar-chunk:nth-child(2) {
    width: 100%;
    order: 3;
    display: flex;
    justify-content: center;
    margin-top: 0.5rem;
}
@media (min-width: 640px) {
    .custom-calendar .fc-toolbar-chunk:nth-child(2) {
        width: auto;
        order: unset;
        margin-top: 0;
    }
    .custom-calendar .fc-header-toolbar {
        flex-wrap: nowrap;
    }
}
.custom-calendar .fc-event {
    cursor: pointer;
    border-radius: 0.375rem; /* rounded-md */
    padding: 2px 4px;
    margin: 1px 2px;
    border: none !important; /* Override inline borders to rely solely on background color */
    font-weight: 600;
    font-size: 0.7rem !important;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    transition: transform 0.1s;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
@media (min-width: 640px) {
    .custom-calendar .fc-event {
        padding: 2px 6px;
        margin: 1px 4px;
        font-size: 0.75rem !important;
    }
}
.custom-calendar .fc-event:hover {
    transform: scale(1.02);
}
/* View Headers (Mon, Tue) */
.custom-calendar .fc-col-header-cell-cushion {
    font-weight: 600;
    font-size: 0.875rem;
    color: #6b7280;
    padding: 0.75rem 0;
    text-transform: uppercase;
}
.custom-calendar .fc-daygrid-day-number {
    font-weight: 700;
    color: #1f2937;
    padding: 0.5rem;
}
</style>