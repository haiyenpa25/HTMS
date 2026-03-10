<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import DutyRosterLayout from '@/Layouts/DutyRosterLayout.vue';
import axios from 'axios';

const props = defineProps({
  meetings:     Array,
  departments:  Array,
  templates:    Array,
  currentMonth: String,
  filters:      Object,
  meetingTypes: Array,
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

const totalRoles = computed(() =>
  props.departments.reduce((s, d) => s + (d.duty_roles?.length || 0), 0)
);
const assignedCount = m => m.duty_assignments?.filter(a => a.member_id).length || 0;
const pct = m => totalRoles.value > 0 ? Math.round(assignedCount(m) / totalRoles.value * 100) : 0;

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
  <DutyRosterLayout title="Lịch Phân Công">
    <Head title="Lịch Phân Công" />

    <transition name="toast">
      <div v-if="flash?.message" class="fixed top-4 right-4 z-[100] bg-emerald-600 text-white text-sm font-semibold px-4 py-3 rounded-2xl shadow-xl">
        {{ flash.message }}
      </div>
    </transition>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

      <!-- Header -->
      <div class="flex items-center justify-between mb-5">
        <div>
          <h1 class="text-2xl font-black text-gray-900">Lịch Phân Công</h1>
          <p class="text-xs text-gray-400 mt-0.5">Quản lý phân công nhân sự theo từng buổi lễ</p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="showHelp=true" class="w-9 h-9 rounded-xl border border-gray-200 bg-white hover:border-indigo-300 hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 flex items-center justify-center font-black text-sm transition-all shadow-sm">?</button>
          <Link :href="route('duty-rooster.templates.index')" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-sm transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
            Quản lý Template
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

      <!-- Meeting cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="meeting in filteredMeetings" :key="meeting.id"
          class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all overflow-hidden">
          <div class="h-1.5 w-full" :class="pct(meeting)===100?'bg-emerald-400':pct(meeting)>50?'bg-amber-400':pct(meeting)>0?'bg-indigo-400':'bg-gray-200'"></div>
          <div class="p-5">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center shrink-0" :class="dayColor(meeting.date)">
                  <span class="text-[9px] font-bold opacity-80 uppercase">{{ fmt(meeting.date).dayLabel }}</span>
                  <span class="text-lg font-black leading-none">{{ fmt(meeting.date).day }}</span>
                </div>
                <div class="min-w-0">
                  <div class="flex items-center gap-1.5 mb-0.5">
                    <span v-if="meeting.type"
                      class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-full shrink-0"
                      :class="meeting.type==='church' ? 'bg-indigo-100 text-indigo-700' : 'bg-violet-100 text-violet-700'">
                      {{ meetingTypeLabels[meeting.type]||meeting.type }}
                    </span>
                  </div>
                  <p class="font-black text-gray-900 group-hover:text-indigo-700 text-sm truncate max-w-[130px]">{{ meeting.topic || `Buổi nhóm ${fmt(meeting.date).day}/${fmt(meeting.date).month}` }}</p>
                  <p class="text-xs text-gray-400">{{ fmt(meeting.date).month }}/{{ fmt(meeting.date).year }} · {{ meeting.time?.substring(0,5) }}</p>
                </div>
              </div>
              <span class="shrink-0 text-xs font-black px-2 py-0.5 rounded-full" :class="pct(meeting)===100?'bg-emerald-100 text-emerald-700':pct(meeting)>0?'bg-amber-100 text-amber-700':'bg-gray-100 text-gray-500'">{{ pct(meeting) }}%</span>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden mb-1.5">
              <div class="h-full rounded-full transition-all" :class="pct(meeting)===100?'bg-emerald-400':pct(meeting)>0?'bg-indigo-400':'bg-gray-200'" :style="`width:${pct(meeting)}%`"></div>
            </div>
            <p class="text-[11px] text-gray-400 mb-4">{{ assignedCount(meeting) }}/{{ totalRoles }} vị trí đã phân</p>
            <div class="flex gap-2">
              <button v-if="templates?.length" @click.stop="openApply(meeting)"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-bold text-orange-600 bg-orange-50 hover:bg-orange-100 rounded-xl border border-orange-200">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5"/></svg>
                Áp dụng mẫu
              </button>
              <Link :href="route('duty-rooster.show', meeting.id)"
                class="flex-1 flex items-center justify-center gap-1 py-2 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-xl border border-indigo-200">
                Phân công
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
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
