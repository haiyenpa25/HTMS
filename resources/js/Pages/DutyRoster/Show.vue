<script setup>
import { ref, computed, reactive } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import DutyRosterLayout from '@/Layouts/DutyRosterLayout.vue';
import axios from 'axios';

const props = defineProps({
  meeting:      Object,
  departments:  Array,
  members:      Array,
  deptMembers:  Object,
  templates:    Array,
  authDeptIds:  Array,
});

// ── State ──────────────────────────────────────────────────
const openPicker  = ref(null);   // "roleId-slot" key
const pickerMode  = reactive({});  // key → 'church' | 'dept'
const searchText  = reactive({});  // key → search string
const expanded    = ref({});
const showModal   = ref(false);
const modalDept   = ref(null);
const warnMsg     = ref(null);

const page  = usePage();
const flash = computed(() => page.props.flash);
const roleForm = useForm({ name: '', section: '', max_count: 1 });

// ── Date helpers ───────────────────────────────────────────
const fmtDate = computed(() => {
  if (!props.meeting?.date) return {};
  const d   = new Date(props.meeting.date);
  const pad = n => String(n).padStart(2,'0');
  return {
    short:   `${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()}`,
    weekday: d.toLocaleDateString('vi-VN', { weekday: 'long' }),
    month:   `${d.getFullYear()}-${pad(d.getMonth()+1)}`,
  };
});

// ── Assignment helpers ─────────────────────────────────────
const canEdit = id => !props.authDeptIds?.length || props.authDeptIds.includes(id);
const getAsgn = (rid, slot=1) =>
  props.meeting.duty_assignments?.find(a => a.department_role_id === rid && (a.slot ?? 1) === slot);
const getName = (rid, slot=1) => getAsgn(rid, slot)?.member?.full_name || null;
const slotArr = r => Array.from({ length: r.max_count || 1 }, (_,i) => i+1);

// ── Progress ───────────────────────────────────────────────
const totalSlots = computed(() =>
  props.departments.reduce((s,d) => s + (d.duty_roles||[]).reduce((a,r) => a+(r.max_count||1),0), 0)
);
const filled = computed(() => (props.meeting.duty_assignments||[]).filter(a => a.member_id).length);
const pct    = computed(() => totalSlots.value > 0 ? Math.round(filled.value/totalSlots.value*100) : 0);

// ── Section split ──────────────────────────────────────────
const mainItems = computed(() => {
  const list = [];
  props.departments.forEach(dept => {
    (dept.duty_roles||[]).forEach(role => {
      if (role.section === 'Chương Trình Lễ')
        slotArr(role).forEach(slot => list.push({ role, slot, dept }));
    });
  });
  return list;
});
const supportDepts = computed(() =>
  props.departments.filter(d => (d.duty_roles||[]).some(r => r.section !== 'Chương Trình Lễ'))
);
const supportRoles = dept => (dept.duty_roles||[]).filter(r => r.section !== 'Chương Trình Lễ');
const hasMain = computed(() => mainItems.value.length > 0);

// ── Picker ─────────────────────────────────────────────────
const pKey = (rid, s) => `${rid}-${s}`;

const togP = (rid, s, deptId) => {
  const k = pKey(rid,s);
  if (openPicker.value === k) { openPicker.value = null; return; }
  openPicker.value = k;
  if (!pickerMode[k]) {
    pickerMode[k] = (props.deptMembers?.[deptId]||[]).length > 0 ? 'dept' : 'church';
  }
};
const closeP   = () => { openPicker.value = null; };
const setMode  = (k, m) => { pickerMode[k] = m; };

const pickerList = (rid, s, deptId) => {
  const k    = pKey(rid,s);
  const mode = pickerMode[k] || 'church';
  const pool = mode === 'dept' ? (props.deptMembers?.[deptId] || props.members) : props.members;
  const q    = (searchText[k]||'').toLowerCase().trim();
  return q ? pool.filter(m => m.full_name.toLowerCase().includes(q)).slice(0,15) : pool.slice(0,15);
};

const hasDeptMbrs = deptId => (props.deptMembers?.[deptId]||[]).length > 0;

// ── Actions ────────────────────────────────────────────────
const assign = async (rid, slot, mid) => {
  openPicker.value = null; warnMsg.value = null;
  try {
    const r = await axios.post(route('duty-rooster.assignments.store'), {
      meeting_id: props.meeting.id, department_role_id: rid, slot, member_id: mid||null,
    });
    if (r.data?.warning) { warnMsg.value = r.data.warning; setTimeout(()=>warnMsg.value=null,5e3); }
    router.reload({ only: ['meeting'] });
  } catch(e) {
    if (e.response?.data?.warning) { warnMsg.value=e.response.data.warning; setTimeout(()=>warnMsg.value=null,5e3); }
  }
};
const clear   = (rid,s) => assign(rid,s,null);
const addRole = () => {
  roleForm.post(route('duty-rooster.roles.store', modalDept.value.id), {
    onSuccess: () => { roleForm.reset(); showModal.value=false; },
  });
};
const print = () => window.print();
</script>

<template>
  <DutyRosterLayout :title="`${meeting.topic||'Phân công'} – ${fmtDate.short}`">
    <Head :title="`${meeting.topic||'Phân Công'} – ${fmtDate.short}`" />

    <!-- Toast -->
    <transition name="toast">
      <div v-if="warnMsg||flash?.message"
        class="fixed top-4 right-4 z-[100] max-w-sm shadow-xl rounded-2xl px-4 py-3 text-sm font-semibold text-white no-print"
        :class="warnMsg?'bg-amber-500':'bg-emerald-500'">
        {{ warnMsg||flash?.message }}
      </div>
    </transition>

    <!-- Overlay to close any open picker -->
    <div v-if="openPicker" class="fixed inset-0 z-[39]" @click="closeP"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 pb-20">

      <!-- Header -->
      <div class="flex items-start justify-between gap-4 mb-6">
        <div>
          <p class="text-xs text-gray-400 flex items-center gap-1 mb-1">
            <Link :href="route('duty-rooster.index',{month:fmtDate.month})" class="hover:text-indigo-600">Lịch phân công</Link>
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <span>Chi tiết</span>
          </p>
          <h1 class="text-2xl font-black text-gray-900">{{ meeting.topic||fmtDate.weekday }}</h1>
          <div class="flex flex-wrap gap-2 mt-1.5 text-xs">
            <span class="bg-indigo-50 text-indigo-700 font-semibold px-2.5 py-1 rounded-full">📋 {{ templates[0]?.name||'Thủ công' }}</span>
            <span class="bg-gray-100 text-gray-600 font-semibold px-2.5 py-1 rounded-full">📅 {{ fmtDate.short }} · {{ meeting.time?.substring(0,5) }}</span>
          </div>
        </div>
        <div class="flex gap-2 shrink-0 no-print">
          <button @click="print" class="flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-gray-600 bg-white border border-gray-200 hover:border-gray-300 rounded-xl shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/></svg>
            In
          </button>
          <button class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-xl shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
            Gửi thông báo
          </button>
        </div>
      </div>

      <!-- Progress -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-8">
        <div class="flex items-center justify-between mb-2.5">
          <span class="text-sm font-bold text-gray-600">📊 Tiến độ hoàn thành</span>
          <span class="text-xl font-black" :class="pct===100?'text-emerald-600':'text-orange-500'">{{ pct }}%</span>
        </div>
        <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden mb-1.5">
          <div class="h-full rounded-full transition-all duration-700" :class="pct===100?'bg-emerald-500':'bg-orange-500'" :style="`width:${pct}%`"></div>
        </div>
        <p class="text-xs text-gray-400">{{ filled }}/{{ totalSlots }} vị trí đã được phân bổ nhân sự</p>
      </div>

      <!-- ═══════ SECTION I — CHƯƠNG TRÌNH LỄ ═══════ -->
      <div v-if="hasMain" class="mb-10">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-sm font-black shadow-sm">I</div>
          <div>
            <h2 class="text-base font-black text-gray-900 uppercase tracking-wide">Chương Trình Lễ</h2>
            <p class="text-[11px] text-gray-400">{{ mainItems.length }} vị trí · Bấm để chọn nhân sự phụ trách</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="item in mainItems" :key="`m-${item.role.id}-${item.slot}`"
            class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-visible">
            <div class="h-1 rounded-t-2xl" :class="getName(item.role.id,item.slot)?'bg-emerald-400':'bg-gray-100'"></div>
            <div class="flex items-center gap-3 px-4 pt-4 pb-2">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :class="getName(item.role.id,item.slot)?'bg-orange-100':'bg-gray-100'">
                <svg class="w-4 h-4" :class="getName(item.role.id,item.slot)?'text-orange-600':'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </div>
              <h3 class="font-black text-gray-900 text-sm truncate flex-1">{{ item.role.name }}<span v-if="item.role.max_count>1" class="text-gray-400 font-normal text-xs"> – {{ item.slot }}</span></h3>
            </div>

            <div class="px-4 pb-4">
              <!-- Assigned chip -->
              <div v-if="getName(item.role.id,item.slot)"
                class="flex items-center justify-between bg-orange-50 border border-orange-200 rounded-xl px-3 py-2.5">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-orange-500 flex items-center justify-center text-[10px] font-black text-white shrink-0">
                    {{ getName(item.role.id,item.slot).slice(0,2).toUpperCase() }}
                  </div>
                  <span class="text-sm font-bold text-orange-900 truncate max-w-[130px]">{{ getName(item.role.id,item.slot) }}</span>
                </div>
                <button v-if="canEdit(item.dept.id)" @click.stop="clear(item.role.id,item.slot)" class="text-orange-300 hover:text-red-500 no-print shrink-0">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </div>

              <!-- Picker trigger -->
              <div v-else class="relative">
                <button @click.stop="togP(item.role.id,item.slot,item.dept.id)"
                  class="w-full flex items-center justify-between px-3 py-2.5 border border-dashed border-gray-300 rounded-xl text-sm text-gray-400 hover:border-orange-400 hover:bg-orange-50/50 group transition-all">
                  <span>Chọn từ danh sách</span>
                  <div class="w-6 h-6 border-2 border-orange-300 group-hover:bg-orange-500 group-hover:border-orange-500 rounded-full flex items-center justify-center transition-all shrink-0">
                    <svg class="w-3 h-3 text-orange-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                  </div>
                </button>

                <!-- Inline dropdown -->
                <div v-if="openPicker===pKey(item.role.id,item.slot)"
                  class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-2xl shadow-2xl z-[50] overflow-hidden" @click.stop>
                  <!-- Mode tabs -->
                  <div class="flex border-b border-gray-100">
                    <button @click="setMode(pKey(item.role.id,item.slot),'church')"
                      class="flex-1 py-2 text-[11px] font-black transition-colors"
                      :class="(pickerMode[pKey(item.role.id,item.slot)]||'church')==='church' ? 'bg-indigo-50 text-indigo-700 border-b-2 border-indigo-500' : 'text-gray-400 hover:text-gray-600'">
                      Hội Thánh
                    </button>
                    <button @click="setMode(pKey(item.role.id,item.slot),'dept')" :disabled="!hasDeptMbrs(item.dept.id)"
                      class="flex-1 py-2 text-[11px] font-black transition-colors disabled:opacity-30"
                      :class="(pickerMode[pKey(item.role.id,item.slot)]||'church')==='dept' ? 'bg-emerald-50 text-emerald-700 border-b-2 border-emerald-500' : 'text-gray-400 hover:text-gray-600'">
                      {{ item.dept.name }}
                    </button>
                  </div>
                  <div class="px-3 py-2 border-b border-gray-100">
                    <input v-model="searchText[pKey(item.role.id,item.slot)]" type="text" placeholder="Tìm tên nhân sự..."
                      class="w-full text-xs border-0 p-0 focus:ring-0 bg-transparent placeholder-gray-400" />
                  </div>
                  <div class="max-h-48 overflow-y-auto">
                    <button v-for="m in pickerList(item.role.id,item.slot,item.dept.id)" :key="m.id"
                      @click.stop="assign(item.role.id,item.slot,m.id)"
                      class="w-full flex items-center gap-2.5 px-3 py-2 hover:bg-orange-50 text-left">
                      <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[9px] font-black text-gray-600 shrink-0">{{ m.full_name.slice(0,2).toUpperCase() }}</div>
                      <span class="text-xs font-medium text-gray-700 truncate">{{ m.full_name }}</span>
                    </button>
                    <p v-if="!pickerList(item.role.id,item.slot,item.dept.id).length" class="text-xs text-gray-400 text-center py-3">Không tìm thấy nhân sự</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════ SECTION II — BAN HỖ TRỢ ═══════ -->
      <div v-if="supportDepts.length">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-2xl flex items-center justify-center text-sm font-black shadow-sm" :class="hasMain?'bg-indigo-600 text-white':'bg-orange-500 text-white'">
            {{ hasMain?'II':'I' }}
          </div>
          <div>
            <h2 class="text-base font-black text-gray-900 uppercase tracking-wide">{{ hasMain?'Ban Hỗ Trợ':'Phân Công Nhân Sự' }}</h2>
            <p class="text-[11px] text-gray-400">{{ supportDepts.length }} ban · Bấm để mở từng ban và phân công</p>
          </div>
        </div>

        <div class="space-y-2">
          <div v-for="dept in supportDepts" :key="dept.id"
            class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-visible" :class="expanded[dept.id]?'border-indigo-200 shadow-md':''">

            <button @click="expanded[dept.id]=!expanded[dept.id]"
              class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-gray-50/60 transition-colors">
              <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="expanded[dept.id]?'rotate-90 text-indigo-500':''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
              <span class="font-black text-gray-800 text-sm flex-1">{{ dept.name }}</span>
              <span v-if="canEdit(dept.id)" class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 shrink-0">Có quyền sửa</span>
              <div class="hidden sm:flex gap-1 flex-wrap max-w-xs">
                <span v-for="role in supportRoles(dept).slice(0,4)" :key="role.id"
                  class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded-full shrink-0"
                  :class="getName(role.id,1)?'bg-emerald-100 text-emerald-700':'bg-gray-100 text-gray-400'">
                  {{ role.name.length>7?role.name.slice(0,7)+'…':role.name }}
                </span>
              </div>
              <div class="shrink-0 ml-2 text-right">
                <p class="text-xs font-black" :class="supportRoles(dept).filter(r=>slotArr(r).some(s=>getName(r.id,s))).length===supportRoles(dept).length&&supportRoles(dept).length?'text-emerald-600':'text-gray-400'">
                  {{ supportRoles(dept).filter(r=>slotArr(r).some(s=>getName(r.id,s))).length }}/{{ supportRoles(dept).length }}
                </p>
                <p class="text-[9px] text-gray-400">vị trí</p>
              </div>
            </button>

            <div v-if="expanded[dept.id]" class="border-t border-gray-100 px-5 py-5 bg-gray-50/30 rounded-b-2xl">
              <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <template v-for="role in supportRoles(dept)" :key="role.id">
                  <div v-for="slot in slotArr(role)" :key="`s-${role.id}-${slot}`" class="relative">
                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-wider mb-1.5 truncate">{{ role.name }}<span v-if="role.max_count>1"> ({{ slot }})</span></p>

                    <!-- Assigned -->
                    <div v-if="getName(role.id,slot)"
                      class="flex items-center gap-1.5 bg-emerald-50 border border-emerald-200 rounded-xl px-2.5 py-2">
                      <div class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center text-[8px] font-black text-white shrink-0">{{ getName(role.id,slot).slice(0,2).toUpperCase() }}</div>
                      <span class="text-xs font-bold text-emerald-800 truncate flex-1">{{ getName(role.id,slot) }}</span>
                      <button v-if="canEdit(dept.id)" @click.stop="clear(role.id,slot)" class="text-emerald-300 hover:text-red-400 no-print shrink-0">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                      </button>
                    </div>

                    <!-- Picker -->
                    <div v-else class="relative">
                      <button @click.stop="togP(role.id,slot,dept.id)" :disabled="!canEdit(dept.id)"
                        class="w-full flex items-center justify-between px-2.5 py-2 bg-white border border-gray-200 rounded-xl text-xs text-gray-400 hover:border-indigo-300 hover:bg-indigo-50/30 disabled:opacity-40 group transition-all">
                        <span>Chọn...</span>
                        <svg class="w-3.5 h-3.5 text-indigo-400 group-hover:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                      </button>

                      <!-- Inline dropdown -->
                      <div v-if="openPicker===pKey(role.id,slot)"
                        class="absolute top-full left-0 mt-1 bg-white border border-gray-200 rounded-2xl shadow-2xl z-[50] overflow-hidden" style="min-width:220px" @click.stop>
                        <div class="flex border-b border-gray-100">
                          <button @click="setMode(pKey(role.id,slot),'church')"
                            class="flex-1 py-2 text-[11px] font-black transition-colors"
                            :class="(pickerMode[pKey(role.id,slot)]||'church')==='church' ? 'bg-indigo-50 text-indigo-700 border-b-2 border-indigo-500' : 'text-gray-400'">
                            Hội Thánh
                          </button>
                          <button @click="setMode(pKey(role.id,slot),'dept')" :disabled="!hasDeptMbrs(dept.id)"
                            class="flex-1 py-2 text-[11px] font-black transition-colors disabled:opacity-30"
                            :class="(pickerMode[pKey(role.id,slot)]||'church')==='dept' ? 'bg-emerald-50 text-emerald-700 border-b-2 border-emerald-500' : 'text-gray-400'">
                            {{ dept.name }}
                          </button>
                        </div>
                        <div class="px-3 py-2 border-b border-gray-100">
                          <input v-model="searchText[pKey(role.id,slot)]" type="text" placeholder="Tìm nhân sự..."
                            class="w-full text-xs border-0 p-0 focus:ring-0 bg-transparent" />
                        </div>
                        <div class="max-h-44 overflow-y-auto">
                          <button v-for="m in pickerList(role.id,slot,dept.id)" :key="m.id"
                            @click.stop="assign(role.id,slot,m.id)"
                            class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-indigo-50 text-left">
                            <div class="w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center text-[8px] font-black text-gray-600 shrink-0">{{ m.full_name.slice(0,2).toUpperCase() }}</div>
                            <span class="text-xs font-medium text-gray-700 truncate">{{ m.full_name }}</span>
                          </button>
                          <p v-if="!pickerList(role.id,slot,dept.id).length" class="text-xs text-gray-400 text-center py-2">Không tìm thấy</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>

                <!-- Add role -->
                <div v-if="canEdit(dept.id)" class="no-print">
                  <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Thêm</p>
                  <button @click="modalDept=dept; showModal=true"
                    class="w-full flex items-center justify-between px-2.5 py-2 border-2 border-dashed border-gray-200 hover:border-orange-300 hover:bg-orange-50/30 rounded-xl group transition-all">
                    <span class="text-xs text-gray-400 group-hover:text-orange-500">Thêm vị trí...</span>
                    <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Fixed bottom bar -->
    <div class="fixed bottom-0 inset-x-0 z-30 bg-white/95 backdrop-blur-md border-t border-gray-100 shadow-[0_-4px_20px_rgba(0,0,0,0.05)] px-5 py-3 no-print">
      <div class="max-w-6xl mx-auto flex items-center justify-between">
        <p class="text-xs text-gray-400">Đã phân <strong class="text-gray-700">{{ filled }}/{{ totalSlots }}</strong> vị trí · Lưu tự động</p>
        <div class="flex gap-2">
          <Link :href="route('duty-rooster.index',{month:fmtDate.month})" class="px-3 py-2 text-xs font-bold text-gray-500 hover:text-gray-700">← Quay lại</Link>
          <button @click="print" class="px-5 py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl hover:bg-indigo-700 shadow-sm">In bản thảo</button>
        </div>
      </div>
    </div>

    <!-- Add Role Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 no-print">
      <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showModal=false"></div>
      <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 z-10">
        <h3 class="text-base font-black text-gray-900 mb-1">Thêm vị trí</h3>
        <p class="text-xs text-gray-400 mb-4">Ban: <span class="font-bold text-indigo-600">{{ modalDept?.name }}</span></p>
        <div class="space-y-3 mb-5">
          <input v-model="roleForm.name" type="text" placeholder="Tên vị trí..." class="w-full text-sm rounded-xl border-gray-200 focus:ring-indigo-400 focus:border-indigo-400" @keyup.enter="addRole" />
          <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-gray-600 shrink-0">Số người: <strong>{{ roleForm.max_count }}</strong></span>
            <input v-model.number="roleForm.max_count" type="range" min="1" max="10" class="flex-1 accent-indigo-600" />
          </div>
        </div>
        <div class="flex gap-2">
          <button @click="showModal=false" class="flex-1 py-2.5 text-sm font-bold text-gray-500 bg-gray-100 rounded-xl">Hủy</button>
          <button @click="addRole" :disabled="!roleForm.name.trim()||roleForm.processing" class="flex-1 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl disabled:opacity-40">Thêm</button>
        </div>
      </div>
    </div>

  </DutyRosterLayout>
</template>

<style>
@media print { .no-print { display: none !important; } }
.toast-enter-active,.toast-leave-active{transition:all .3s ease;}
.toast-enter-from,.toast-leave-to{opacity:0;transform:translateY(-6px);}
</style>
