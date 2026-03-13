<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DutyRosterLayout from '@/Layouts/DutyRosterLayout.vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import axios from 'axios';

const props = defineProps({
  meetings:             Array,
  departments:          Array,
  templates:            Array,
  currentMonth:         String,
  filters:              Object,
  meetingTypes:         Array,
  // Portal layout props (only set when accessed via portal/* routes)
  isPortal:             { type: Boolean, default: false },
  portalType:           { type: String, default: 'activities' },
  department:           { type: Object, default: null },
  availableDepartments: { type: Array, default: () => [] },
  isGlobalAdmin:        { type: Boolean, default: false },
});

const page  = usePage();
const flash = computed(() => page.props.flash);

const helpSteps = [
  { color:'indigo', num:1, title:'Tạo / chỉnh sửa Template', desc:'Vào Template - tick các vị trí cần có trong buổi lễ. Có thể thêm vai trò mới bất kỳ lúc nào.' },
  { color:'orange', num:2, title:'Áp dụng mẫu vào buổi nhóm', desc:'Bấm "Áp dụng mẫu" trên card buổi nhóm, chọn mẫu phù hợp, bấm xác nhận.' },
  { color:'emerald', num:3, title:'Phân công nhân sự', desc:'Bấm "Phân công" - chọn người từ hội thánh hoặc từ ban ngành cho từng vị trí.' },
  { color:'gray', num:4, title:'In bản thảo / Gửi thông báo', desc:'In bản thảo để dán bảng, hoặc gửi thông báo cho nhân sự biết lịch phục vụ.' },
];

const meetingTypeLabels = { church: 'Hội Thánh', department: 'Ban Ngành' };

// ── Filters ──────────────────────────────────────────
const selectedMonth   = ref(props.currentMonth);
const filterStatus    = ref('all');
const filterTopic     = ref('');
const filterType      = ref(props.filters?.meeting_type || '');

const monthLabel = computed(() => {
  const [y, m] = selectedMonth.value.split('-');
  return new Date(y, m - 1).toLocaleDateString('vi-VN', { month: 'long', year: 'numeric' });
});

const requiredSlots = m => m.duty_assignments?.length || 0;
const assignedCount = m => m.duty_assignments?.filter(a => a.member_id).length || 0;
const pct = m => requiredSlots(m) > 0 ? Math.round(assignedCount(m) / requiredSlots(m) * 100) : 0;

const filteredMeetings = computed(() => {
  let list = props.meetings || [];
  if (filterTopic.value.trim()) {
    const q = filterTopic.value.toLowerCase();
    list = list.filter(m => (m.topic||'').toLowerCase().includes(q));
  }
  if (filterStatus.value === 'complete') list = list.filter(m => pct(m) === 100);
  if (filterStatus.value === 'pending')  list = list.filter(m => pct(m) > 0 && pct(m) < 100);
  if (filterStatus.value === 'empty')    list = list.filter(m => pct(m) === 0);
  return list;
});

const activeFilterCount = computed(() =>
  (filterStatus.value !== 'all' ? 1 : 0) + (filterTopic.value ? 1 : 0) + (filterType.value ? 1 : 0)
);

const resetFilters = () => {
  filterStatus.value = 'all';
  filterTopic.value  = '';
  if (filterType.value) {
    filterType.value = '';
    router.get(route('duty-rooster.index'), { month: selectedMonth.value }, { preserveScroll: true });
  }
};

const changeMonth = (delta) => {
  const [y, m] = selectedMonth.value.split('-').map(Number);
  const d = new Date(y, m - 1 + delta, 1);
  selectedMonth.value = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
  router.get(route('duty-rooster.index'), { month: selectedMonth.value, meeting_type: filterType.value }, { preserveScroll: true });
};

const applyTypeFilter = () => {
  router.get(route('duty-rooster.index'), { month: selectedMonth.value, meeting_type: filterType.value }, { preserveScroll: true });
};

const fmt = (dateStr) => {
  const d = new Date(dateStr);
  return {
    dayLabel: d.getDay() === 0 ? 'CN' : `T${d.getDay()+1}`,
    day:   d.getDate(),
    month: String(d.getMonth()+1).padStart(2,'0'),
    year:  d.getFullYear(),
  };
};

const dayColor = (dateStr) => {
  const day = new Date(dateStr).getDay();
  return ({ 0:'bg-indigo-600 text-white', 6:'bg-violet-600 text-white', 5:'bg-blue-600 text-white' })[day] || 'bg-gray-700 text-white';
};

// ── Apply template modal ──────────────────────────────
const applyModal   = ref(false);
const applyMeeting = ref(null);
const applyTplId   = ref(null);
const applying     = ref(false);
const applySuccess = ref(null);

const openApply = (meeting) => {
  applyMeeting.value = meeting;
  applyTplId.value   = props.templates[0]?.id || null;
  applySuccess.value = null;
  applyModal.value   = true;
};

const doApply = async () => {
  if (!applyMeeting.value || !applyTplId.value) return;
  applying.value = true;
  try {
    await axios.post(route('duty-rooster.templates.apply'), {
      meeting_id:  applyMeeting.value.id,
      template_id: applyTplId.value,
    });
    applySuccess.value = 'Đã áp dụng mẫu phân công thành công!';
    setTimeout(() => { applyModal.value=false; applySuccess.value=null; router.reload({ only:['meetings'] }); }, 1500);
  } finally { applying.value = false; }
};

const showHelp = ref(false);
</script>

<template>
  <PortalLayout v-if="isPortal"
    :department="department"
    :available-departments="availableDepartments"
    :is-global-admin="isGlobalAdmin"
    :portal-type="portalType"
  >
    <Head title="Lịch Phân Công" />

    <transition name="toast">
      <div v-if="flash?.message" class="fixed top-4 right-4 z-[100] bg-emerald-600 text-white text-sm font-semibold px-4 py-3 rounded-2xl shadow-xl">
        {{ flash.message }}
      </div>
    </transition>

    <!-- Page content inside Portal sidebar layout -->
    <div class="space-y-5">
      <!-- Header -->
      <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
          <h2 class="text-2xl font-black text-gray-900 truncate">Lịch Phân Công</h2>
          <p class="text-sm font-medium mt-1 text-gray-500">
            Quản lý phân công nhân sự theo từng buổi lễ
            <span v-if="department"> · {{ department.name }}</span>
          </p>
        </div>
        <Link
          :href="isPortal ? route('portal.duty-rooster.templates.index') : route('duty-rooster.templates.index')"
          class="shrink-0 flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm"
        >
          <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
          <span class="hidden sm:inline">Template</span>
        </Link>
      </div>

      <!-- Month nav -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-3 flex items-center justify-between">
        <button @click="changeMonth(-1)" class="w-9 h-9 rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 flex items-center justify-center transition-all">
          <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </button>
        <div class="text-center">
          <p class="text-base font-black text-gray-900 capitalize">{{ monthLabel }}</p>
          <p class="text-xs text-gray-400">{{ filteredMeetings.length }}/{{ meetings?.length || 0 }} buổi nhóm</p>
        </div>
        <button @click="changeMonth(1)" class="w-9 h-9 rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 flex items-center justify-center transition-all">
          <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        </button>
      </div>

      <!-- Filters bar -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-3 space-y-2.5">
        <div class="flex items-center gap-3 flex-wrap">
          <div class="flex-1 min-w-[160px] relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input v-model="filterTopic" type="text" placeholder="Tìm theo chủ đề..."
              class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-indigo-400 focus:border-indigo-400" />
          </div>
          <div class="flex gap-1 shrink-0">
            <button
              v-for="opt in [{val:'all',label:'Tất cả'},{val:'empty',label:'Chưa phân'},{val:'pending',label:'Đang phân'},{val:'complete',label:'Xong'}]"
              :key="opt.val"
              @click="filterStatus=opt.val"
              class="px-2.5 py-1.5 text-xs font-bold rounded-lg transition-all"
              :class="filterStatus===opt.val ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100'">
              {{ opt.label }}
            </button>
          </div>
          <button v-if="activeFilterCount" @click="resetFilters" class="text-xs font-bold text-red-400 hover:text-red-600 px-2 shrink-0">× Xóa</button>
        </div>
      </div>

      <!-- Empty -->
      <div v-if="!filteredMeetings.length" class="bg-white rounded-3xl border-2 border-dashed border-gray-200 py-14 text-center">
        <p class="text-gray-400 text-sm font-medium">Không có buổi nhóm nào phù hợp cho tháng này</p>
        <p class="text-xs text-gray-300 mt-1">Buổi nhóm Ban Ngành sẽ hiển thị ở đây sau khi được tạo.</p>
      </div>

      <!-- Meeting list -->
      <div v-else class="space-y-3">
        <div
          v-for="meeting in filteredMeetings"
          :key="meeting.id"
          class="block bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-200 group cursor-pointer relative overflow-hidden"
        >
          <div class="h-1 w-full" :class="pct(meeting)===100?'bg-emerald-400':pct(meeting)>0?'bg-indigo-400':'bg-gray-100'"></div>
          <div class="p-4 sm:p-5 flex items-center gap-4 sm:gap-5">
            <div
              class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center shrink-0 border transition-colors duration-300"
              :class="pct(meeting)===100 ? 'bg-emerald-500 border-emerald-500 text-white' : 'bg-indigo-50 border-indigo-100 text-indigo-600 group-hover:bg-indigo-500 group-hover:border-indigo-500 group-hover:text-white'"
            >
              <span class="text-[10px] font-black uppercase tracking-wide leading-none mb-1 opacity-80">
                {{ new Date(meeting.date).toLocaleDateString('vi-VN', { month: 'short' }).replace('.', '') }}
              </span>
              <span class="text-xl font-black leading-none">{{ new Date(meeting.date).getDate() }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-1.5 mb-1">
                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-violet-100 text-violet-700">Ban Ngành</span>
                <span class="text-[10px] font-bold text-gray-400">
                  {{ new Date(meeting.date).toLocaleDateString('vi-VN', { weekday: 'short' }) }}
                  {{ meeting.time?.substring(0,5) }}
                </span>
              </div>
              <h3 class="font-bold text-gray-900 text-sm sm:text-base group-hover:text-indigo-700 transition-colors truncate">
                {{ meeting.topic || `Buổi nhóm ${new Date(meeting.date).getDate()}/${new Date(meeting.date).getMonth()+1}` }}
              </h3>
              <div class="flex items-center gap-2 mt-2">
                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden max-w-[160px]">
                  <div class="h-full rounded-full transition-all" :class="pct(meeting)===100?'bg-emerald-400':pct(meeting)>0?'bg-indigo-400':'bg-gray-200'" :style="`width:${pct(meeting)}%`"></div>
                </div>
                <span class="text-[11px] font-bold" :class="pct(meeting)===100?'text-emerald-600':pct(meeting)>0?'text-indigo-600':'text-gray-400'">
                  {{ assignedCount(meeting) }}/{{ requiredSlots(meeting) }} vị trí · {{ pct(meeting) }}%
                </span>
              </div>
            </div>
            <div class="shrink-0 flex items-center gap-2">
              <button
                v-if="templates?.length"
                @click.stop="openApply(meeting)"
                class="w-8 h-8 rounded-full bg-white text-gray-400 hover:bg-orange-50 hover:text-orange-600 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100 shadow-sm border border-gray-100"
                title="Áp dụng mẫu"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5"/></svg>
              </button>
              <Link
                :href="route('portal.duty-rooster.show', meeting.id)"
                @click.stop
                class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors flex items-center justify-center"
                title="Phân công nhân sự"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Apply template modal (shared) -->
    <div v-if="applyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="applyModal=false"></div>
      <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md p-6 z-10">
        <div class="flex items-start justify-between mb-5">
          <div>
            <h3 class="text-xl font-black text-gray-900">Áp dụng mẫu phân công</h3>
            <p class="text-sm text-gray-500 mt-0.5">Buổi: <span class="font-bold text-indigo-700">{{ applyMeeting?.topic || applyMeeting?.date }}</span></p>
          </div>
          <button @click="applyModal=false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div v-if="applySuccess" class="text-center py-6">
          <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <p class="text-emerald-700 font-bold">{{ applySuccess }}</p>
        </div>
        <div v-else class="space-y-4">
          <div class="space-y-2">
            <label v-for="tpl in templates" :key="tpl.id" class="flex items-center gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition-all" :class="applyTplId===tpl.id ? 'border-orange-400 bg-orange-50' : 'border-gray-200 hover:border-orange-200'">
              <input type="radio" :value="tpl.id" v-model="applyTplId" class="accent-orange-500" />
              <div>
                <p class="font-bold text-sm text-gray-900">{{ tpl.name }}</p>
                <p class="text-xs text-gray-400">{{ tpl.roles?.length || 0 }} vị trí</p>
              </div>
            </label>
          </div>
          <p class="text-xs text-blue-700 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3">Áp dụng sẽ tạo sẵn các vị trí. Bạn vẫn cần chọn người sau đó.</p>
          <div class="flex gap-3">
            <button @click="applyModal=false" class="flex-1 py-2.5 text-sm font-bold text-gray-500 bg-gray-100 rounded-xl">Hủy</button>
            <button @click="doApply" :disabled="!applyTplId||applying"
              class="flex-1 py-2.5 text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-xl disabled:opacity-40 flex items-center justify-center gap-2">
              <svg v-if="applying" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"/><path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              {{ applying ? 'Đang áp dụng...' : 'Áp dụng mẫu này' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </PortalLayout>

  <DutyRosterLayout v-else title="Lịch Phân Công">

    <Head title="Lịch Phân Công" />

    <transition name="toast">
      <div v-if="flash?.message" class="fixed top-4 right-4 z-[100] bg-emerald-600 text-white text-sm font-semibold px-4 py-3 rounded-2xl shadow-xl">
        {{ flash.message }}
      </div>
    </transition>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

      <!-- Header -->
      <div class="flex items-center justify-between mb-5 gap-2">
        <div class="min-w-0">
          <h1 class="text-xl sm:text-2xl font-black text-gray-900 truncate">Lịch Phân Công</h1>
          <p class="hidden sm:block text-xs text-gray-400 mt-0.5">Quản lý phân công nhân sự theo từng buổi lễ</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <button @click="showHelp=true" class="w-9 h-9 rounded-xl border border-gray-200 bg-white hover:border-indigo-300 hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 flex items-center justify-center font-black text-sm transition-all shadow-sm">?</button>
          <Link :href="route('duty-rooster.templates.index')" class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-sm transition-all">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
            <span class="hidden sm:inline">Quản lý Template</span>
          </Link>
        </div>
      </div>

      <!-- Month nav -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-3 mb-3 flex items-center justify-between">
        <button @click="changeMonth(-1)" class="w-9 h-9 rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 flex items-center justify-center transition-all">
          <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </button>
        <div class="text-center">
          <p class="text-base font-black text-gray-900 capitalize">{{ monthLabel }}</p>
          <p class="text-xs text-gray-400">{{ filteredMeetings.length }}/{{ meetings?.length || 0 }} buổi nhóm</p>
        </div>
        <button @click="changeMonth(1)" class="w-9 h-9 rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 flex items-center justify-center transition-all">
          <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        </button>
      </div>

      <!-- Filters bar -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-3 mb-5 space-y-2.5">
        <div class="flex items-center gap-3 flex-wrap">
          <!-- Search -->
          <div class="flex-1 min-w-[160px] relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input v-model="filterTopic" type="text" placeholder="Tìm theo chủ đề..."
              class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-indigo-400 focus:border-indigo-400" />
          </div>

          <!-- Meeting type dropdown -->
          <select v-model="filterType" @change="applyTypeFilter"
            class="py-2 pl-3 pr-8 text-sm border border-gray-200 rounded-xl focus:ring-indigo-400 focus:border-indigo-400 font-bold text-gray-700 shrink-0">
            <option value="">-- Loại buổi nhóm --</option>
            <option v-for="t in meetingTypes" :key="t" :value="t">
              {{ meetingTypeLabels[t] || t }}
            </option>
          </select>

          <!-- Completion status pills -->
          <div class="flex gap-1 shrink-0">
            <button
              v-for="opt in [{val:'all',label:'Tất cả'},{val:'empty',label:'Chưa phân'},{val:'pending',label:'Đang phân'},{val:'complete',label:'Xong'}]"
              :key="opt.val"
              @click="filterStatus=opt.val"
              class="px-2.5 py-1.5 text-xs font-bold rounded-lg transition-all"
              :class="filterStatus===opt.val ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100'">
              {{ opt.label }}
            </button>
          </div>

          <button v-if="activeFilterCount" @click="resetFilters" class="text-xs font-bold text-red-400 hover:text-red-600 px-2 shrink-0">x Xóa</button>
        </div>

        <!-- Active filter tags -->
        <div v-if="activeFilterCount" class="flex items-center gap-2 pt-1 border-t border-gray-100">
          <span class="text-[10px] text-gray-400 font-bold uppercase">Đang lọc:</span>
          <span v-if="filterType" class="text-[10px] bg-violet-100 text-violet-700 font-bold px-2 py-0.5 rounded-full">{{ meetingTypeLabels[filterType]||filterType }}</span>
          <span v-if="filterStatus !== 'all'" class="text-[10px] bg-indigo-100 text-indigo-700 font-bold px-2 py-0.5 rounded-full">{{ {empty:'Chưa phân',pending:'Đang phân',complete:'Hoàn thành'}[filterStatus] }}</span>
          <span v-if="filterTopic" class="text-[10px] bg-gray-100 text-gray-700 font-bold px-2 py-0.5 rounded-full truncate max-w-[150px]">"{{ filterTopic }}"</span>
        </div>
      </div>

      <!-- Empty -->
      <div v-if="!filteredMeetings.length" class="bg-white rounded-3xl border-2 border-dashed border-gray-200 py-14 text-center">
        <p class="text-gray-400 text-sm font-medium">Không có buổi nhóm nào phù hợp</p>
        <button v-if="activeFilterCount" @click="resetFilters" class="mt-2 text-xs text-indigo-500 font-bold hover:underline">Xóa bộ lọc</button>
      </div>

      <!-- Meeting list — Attendance-style cards -->
      <div v-else class="space-y-3">
        <div
          v-for="meeting in filteredMeetings"
          :key="meeting.id"
          class="block bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-200 group cursor-pointer relative overflow-hidden"
        >
          <!-- Progress top bar -->
          <div class="h-1 w-full" :class="pct(meeting)===100?'bg-emerald-400':pct(meeting)>0?'bg-indigo-400':'bg-gray-100'"></div>

          <div class="p-4 sm:p-5 flex items-center gap-4 sm:gap-5">
            <!-- Date Badge (Attendance style) -->
            <div
              class="w-14 h-14 rounded-2xl flex flex-col items-center justify-center shrink-0 border transition-colors duration-300"
              :class="pct(meeting)===100
                ? 'bg-emerald-500 border-emerald-500 text-white'
                : 'bg-indigo-50 border-indigo-100 text-indigo-600 group-hover:bg-indigo-500 group-hover:border-indigo-500 group-hover:text-white'"
            >
              <span class="text-[10px] font-black uppercase tracking-wide leading-none mb-1 opacity-80">
                {{ new Date(meeting.date).toLocaleDateString('vi-VN', { month: 'short' }).replace('.', '') }}
              </span>
              <span class="text-xl font-black leading-none">{{ new Date(meeting.date).getDate() }}</span>
            </div>

            <!-- Main content -->
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-center gap-1.5 mb-1">
                <span
                  class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md"
                  :class="meeting.type==='church' ? 'bg-indigo-100 text-indigo-700' : 'bg-violet-100 text-violet-700'"
                >{{ meetingTypeLabels[meeting.type] || meeting.type }}</span>
                <span class="text-[10px] font-bold text-gray-400">
                  {{ new Date(meeting.date).toLocaleDateString('vi-VN', { weekday: 'short' }) }}
                  {{ meeting.time?.substring(0,5) }}
                </span>
              </div>
              <h3 class="font-bold text-gray-900 text-sm sm:text-base group-hover:text-indigo-700 transition-colors truncate">
                {{ meeting.topic || `Buổi nhóm ${new Date(meeting.date).getDate()}/${new Date(meeting.date).getMonth()+1}` }}
              </h3>
              <!-- Progress bar inline -->
              <div class="flex items-center gap-2 mt-2">
                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden max-w-[160px]">
                  <div
                    class="h-full rounded-full transition-all"
                    :class="pct(meeting)===100?'bg-emerald-400':pct(meeting)>0?'bg-indigo-400':'bg-gray-200'"
                    :style="`width:${pct(meeting)}%`"
                  ></div>
                </div>
                <span class="text-[11px] font-bold" :class="pct(meeting)===100?'text-emerald-600':pct(meeting)>0?'text-indigo-600':'text-gray-400'">
                  {{ assignedCount(meeting) }}/{{ requiredSlots(meeting) }} vị trí · {{ pct(meeting) }}%
                </span>
              </div>
            </div>

            <!-- Actions — hover-visible -->
            <div class="shrink-0 flex items-center gap-2">
              <!-- Apply Template -->
              <button
                v-if="templates?.length"
                @click.stop="openApply(meeting)"
                class="w-8 h-8 rounded-full bg-white text-gray-400 hover:bg-orange-50 hover:text-orange-600 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100 shadow-sm border border-gray-100"
                title="Áp dụng mẫu"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5"/></svg>
              </button>
              <!-- Go to assign -->
              <Link
                :href="route('duty-rooster.show', meeting.id)"
                @click.stop
                class="w-8 h-8 rounded-full bg-gray-50 text-gray-400 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors flex items-center justify-center"
                title="Phân công nhân sự"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              </Link>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Apply template modal -->
    <div v-if="applyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="applyModal=false"></div>
      <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md p-6 z-10">
        <div class="flex items-start justify-between mb-5">
          <div>
            <h3 class="text-xl font-black text-gray-900">Áp dụng mẫu phân công</h3>
            <p class="text-sm text-gray-500 mt-0.5">Buổi: <span class="font-bold text-indigo-700">{{ applyMeeting?.topic || applyMeeting?.date }}</span></p>
          </div>
          <button @click="applyModal=false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div v-if="applySuccess" class="text-center py-6">
          <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <p class="text-emerald-700 font-bold">{{ applySuccess }}</p>
        </div>
        <div v-else class="space-y-4">
          <div class="space-y-2">
            <label v-for="tpl in templates" :key="tpl.id"
              class="flex items-center gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition-all"
              :class="applyTplId===tpl.id ? 'border-orange-400 bg-orange-50' : 'border-gray-200 hover:border-orange-200'">
              <input type="radio" :value="tpl.id" v-model="applyTplId" class="accent-orange-500" />
              <div>
                <p class="font-bold text-sm text-gray-900">{{ tpl.name }}</p>
                <p class="text-xs text-gray-400">{{ tpl.roles?.length || 0 }} vị trí</p>
              </div>
            </label>
          </div>
          <p class="text-xs text-blue-700 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3">Áp dụng sẽ tạo sẵn các vị trí. Bạn vẫn cần chọn người sau đó.</p>
          <div class="flex gap-3">
            <button @click="applyModal=false" class="flex-1 py-2.5 text-sm font-bold text-gray-500 bg-gray-100 rounded-xl">Hủy</button>
            <button @click="doApply" :disabled="!applyTplId||applying"
              class="flex-1 py-2.5 text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-xl disabled:opacity-40 flex items-center justify-center gap-2">
              <svg v-if="applying" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"/><path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              {{ applying ? 'Đang áp dụng...' : 'Áp dụng mẫu này' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Help modal -->
    <div v-if="showHelp" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showHelp=false"></div>
      <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg p-7 z-10">
        <div class="flex items-start justify-between mb-6">
          <h3 class="text-xl font-black text-gray-900">Hướng dẫn sử dụng</h3>
          <button @click="showHelp=false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="space-y-3">
          <div v-for="(step,i) in helpSteps" :key="i"
            class="flex gap-4 p-4 rounded-2xl border"
            :class="{'bg-indigo-50 border-indigo-100':step.color==='indigo','bg-orange-50 border-orange-100':step.color==='orange','bg-emerald-50 border-emerald-100':step.color==='emerald','bg-gray-50 border-gray-200':step.color==='gray'}">
            <div class="w-8 h-8 rounded-xl text-white font-black flex items-center justify-center shrink-0 text-sm"
              :class="{'bg-indigo-600':step.color==='indigo','bg-orange-500':step.color==='orange','bg-emerald-600':step.color==='emerald','bg-gray-700':step.color==='gray'}">{{ step.num }}</div>
            <div>
              <p class="font-bold text-sm text-gray-900">{{ step.title }}</p>
              <p class="text-xs text-gray-600 mt-0.5">{{ step.desc }}</p>
            </div>
          </div>
        </div>
        <div class="mt-5 pt-4 border-t border-gray-100 flex justify-between text-xs text-gray-400">
          <span>Dữ liệu lưu tự động sau mỗi thay đổi</span>
          <button @click="showHelp=false" class="font-bold text-gray-600">Đóng</button>
        </div>
      </div>
    </div>

  </DutyRosterLayout>
</template>

<style scoped>
.toast-enter-active,.toast-leave-active{transition:all .3s ease;}
.toast-enter-from,.toast-leave-to{opacity:0;transform:translateY(-6px);}
</style>