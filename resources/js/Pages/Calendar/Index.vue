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
          <p class="text-xs font-bold uppercase tracking-[0.2em] text-teal-200 mb-1">HỆ THỐNG × LỊCH SỰ KIỆN</p>
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Lịch Tổng Hợp Hội Thánh</h1>
          <p class="mt-2 text-sm text-teal-200">Lịch Nhóm, Lịch Trực và tất cả Hoạt động chung được hiển thị trực quan.</p>
        </div>
        <div class="absolute top-5 right-5 sm:top-6 sm:right-6 z-10" v-if="canManageEvents">
          <button @click="openEventModal" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-sm font-bold rounded-xl transition-all backdrop-blur-sm border border-white/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tạo Sự kiện
          </button>
        </div>
      </div>

      <!-- Chú thích Lịch -->
      <div class="bg-white px-4 py-3 rounded-xl shadow-sm border border-gray-100 flex flex-wrap gap-4 items-center mb-4 text-xs font-medium">
          <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Sự kiện chung HT</div>
          <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Buổi Nhóm Định kỳ</div>
          <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full leading-none text-white text-[8px] flex items-center justify-center font-bold bg-amber-500">T</span> Lịch Trực Cá nhân</div>
      </div>

      <!-- FullCalendar Area -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 lg:p-6 overflow-hidden">
        <FullCalendar class="custom-calendar text-sm" ref="fullCalendar" :options="calendarOptions" />
      </div>
    </div>

    <!-- M O D A L : Thêm / Sửa Sự Kiện -->
    <Modal :show="isModalOpen" @close="closeModal" maxWidth="md">
      <div class="p-6">
        <h2 class="text-lg font-black text-gray-900 mb-4">{{ isEditing ? 'Chỉnh sửa Sự kiện' : 'Tạo Sự kiện Mới' }}</h2>
        <form @submit.prevent="submitForm">
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
              <div>
                <InputLabel for="type" value="Loại Sự Kiện *" />
                <select id="type" v-model="form.type" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" required>
                  <option value="church_service">Lễ Thờ Phượng / Tiệc Thánh</option>
                  <option value="seminar">Hội đồng / Bồi linh</option>
                  <option value="meeting">Họp Chung (Ban CTS...)</option>
                  <option value="holiday">Ngày Lễ</option>
                  <option value="other">Khác</option>
                </select>
                <InputError :message="form.errors.type" class="mt-2" />
              </div>
              <div>
                <InputLabel for="color" value="Màu nhận diện" />
                <select id="color" v-model="form.color" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" required>
                  <option value="#3b82f6" class="text-blue-500 font-bold">Xanh dương (Mặc định)</option>
                  <option value="#ef4444" class="text-red-500 font-bold">Đỏ (Quan trọng)</option>
                  <option value="#8b5cf6" class="text-purple-500 font-bold">Tím (Đặc biệt)</option>
                  <option value="#f97316" class="text-orange-500 font-bold">Cam (Lễ)</option>
                  <option value="#64748b" class="text-slate-500 font-bold">Xám (Phụ)</option>
                </select>
              </div>
            </div>
            
            <div>
              <InputLabel for="location" value="Địa điểm" />
              <TextInput id="location" v-model="form.location" type="text" class="mt-1 block w-full text-sm" placeholder="VD: Phòng Thanh Niên, Nhà Thờ..." />
            </div>

            <div>
              <InputLabel for="visibility" value="Ai được nhìn thấy? *" />
              <select id="visibility" v-model="form.visibility" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" required>
                <option value="public">Công khai toàn hệ thống</option>
                <option value="internal">Tín hữu có tài khoản</option>
                <option value="leadership">Chỉ Mục sư & Ban Chấp sự</option>
              </select>
              <InputError :message="form.errors.visibility" class="mt-2" />
            </div>

            <div>
              <InputLabel for="description" value="Ghi chú chi tiết" />
              <textarea id="description" v-model="form.description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" placeholder="Mục đích, thành phần tham dự..."></textarea>
            </div>
          </div>

          <div class="mt-6 flex justify-between">
            <div>
              <button v-if="isEditing" type="button" @click="deleteEvent" class="px-4 py-2 text-sm text-red-600 font-bold hover:bg-red-50 rounded-lg transition-colors">Xoá Sự kiện</button>
            </div>
            <div class="flex gap-3">
              <SecondaryButton @click="closeModal">Hủy</SecondaryButton>
              <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ isEditing ? 'Cập nhật' : 'Tạo mới' }}
              </PrimaryButton>
            </div>
          </div>
        </form>
      </div>
    </Modal>

    <!-- M O D A L : Xem Chi Tiết Sự Kiện (Buổi nhóm/Trực/...) -->
    <Modal :show="isDetailModalOpen" @close="closeDetailModal" maxWidth="sm">
      <div class="p-6">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" :style="`background-color: ${selectedEvent?.backgroundColor}`">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-black text-gray-900 leading-tight">{{ selectedEvent?.title }}</h2>
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

        <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end gap-2">
          <button v-if="canEditSelectedEvent" @click="editEvent(selectedEvent)" class="px-4 py-2 text-sm text-blue-600 font-bold hover:bg-blue-50 rounded-lg transition-colors border border-blue-200">
            Chỉnh sửa
          </button>
          <PrimaryButton @click="closeDetailModal">Đóng lại</PrimaryButton>
        </div>
      </div>
    </Modal>
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
import Modal from '@/Components/Modal.vue';

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
    const roles = page.props.auth?.user?.roles || [];
    return roles.includes('Super_Admin') || roles.includes('Pastor');
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
    type: 'church_service',
    color: '#3b82f6',
    location: '',
    visibility: 'public',
});

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
    form.type = 'church_service'; // default logic, technically should fetch real type if DB had it
    form.color = evt.backgroundColor;
    form.location = evt.extendedProps.location || '';
    form.visibility = evt.extendedProps.visibility || 'public';
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
.custom-calendar .fc-toolbar-title {
    font-size: 1.25rem !important;
    font-weight: 900 !important;
    color: #111827;
}
.custom-calendar .fc-button-primary {
    background-color: #f3f4f6 !important;
    border-color: #e5e7eb !important;
    color: #4b5563 !important;
    font-weight: 700 !important;
    text-transform: capitalize !important;
}
.custom-calendar .fc-button-primary:hover {
    background-color: #e5e7eb !important;
    color: #1f2937 !important;
}
.custom-calendar .fc-button-active {
    background-color: #dbeafe !important;
    border-color: #bfdbfe !important;
    color: #1d4ed8 !important;
}
.custom-calendar .fc-event {
    cursor: pointer;
    border-radius: 4px;
    padding: 1px 3px;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>