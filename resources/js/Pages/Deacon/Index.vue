<template>
  <PortalLayout 
      :department="department" 
      :available-departments="availableDepartments"
      :is-global-admin="isGlobalAdmin"
      portal-type="deacon"
      @open-switcher="isSwitchOpen = true"
  >
       <!-- Empty State -->
       <div v-if="!activeRole" class="h-full flex flex-col items-center justify-center p-6 text-center animate-in fade-in zoom-in-95 duration-500 min-h-[60vh]">
           <div class="w-24 h-24 mb-6 rounded-3xl bg-amber-100 flex items-center justify-center text-amber-500 shadow-inner">
               <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
           </div>
           <h2 class="text-xl font-black text-gray-900 mb-2">L�nh �?o H?i Th�nh</h2>
           <p class="text-sm text-gray-500 max-w-xs mb-8">Vui l�ng ch?n ch?c v? d? ti?p t?c.</p>
       </div>

       <!-- Dashboard Content -->
       <div v-else class="w-full p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl mx-auto">

           <!-- Role label -->
           <div class="flex items-center gap-3">
               <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center">
                   <svg v-if="activeRole === 'secretary'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                   </svg>
                   <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                   </svg>
               </div>
               <div>
                   <p class="text-xs text-gray-500 font-medium uppercase tracking-widest">�ang xem v?i ch?c v?</p>
                   <h2 class="text-lg font-black text-gray-900">{{ roleLabel }}</h2>
               </div>
           </div>
           
           <!-- Cards Grid: c? d?nh (�i?m danh HT, B�o c�o) + card d?ng t? deptFeatures -->
           <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
              
              <!-- Card: �i?m danh HT (Secretary only) -->
              <Link v-if="activeRole === 'secretary'" :href="route('deacon.attendance')" 
                class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-amber-200 transition-all active:scale-95 duration-200 relative">
                  <div v-if="pendingAttendance > 0" class="absolute top-4 right-4 bg-amber-500 text-white text-[10px] font-black w-6 h-6 rounded-full flex items-center justify-center shadow-md animate-pulse">
                      {{ pendingAttendance }}
                  </div>
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">�i?m danh<br><span class="text-xs font-medium text-amber-600">Bu?i nh�m HT</span></h3>
              </Link>

              <!-- Card: B�o c�o ban ng�nh (Secretary) -->
              <Link v-if="activeRole === 'secretary'" :href="route('deacon.report')" 
                class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-amber-200 transition-all active:scale-95 duration-200 relative">
                  <div v-if="pendingReports.length > 0" class="absolute top-4 right-4 bg-amber-500 text-white text-[10px] font-black w-6 h-6 rounded-full flex items-center justify-center shadow-md animate-pulse">
                      {{ pendingReports.length }}
                  </div>
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">B�o c�o<br><span class="text-xs font-medium text-amber-600">C�c ban tr?c thu?c</span></h3>
              </Link>

              <!-- Card: Qu?n l� Qu? (Treasurer) -->
              <Link v-if="activeRole === 'treasurer' && route().has('finance.funds.index')" :href="route('finance.funds.index')" 
                class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-amber-200 transition-all active:scale-95 duration-200 relative">
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Qu?n l� Qu?<br><span class="text-xs font-medium text-amber-600">{{ funds.length }} qu?</span></h3>
              </Link>

              <!-- Card: T�i ch�nh HT (Treasurer) -->
              <Link v-if="activeRole === 'treasurer'" :href="route('finance.index')" 
                class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-rose-200 transition-all active:scale-95 duration-200 relative">
                  <div v-if="pendingTx > 0" class="absolute top-4 right-4 bg-amber-500 text-white text-[10px] font-black w-6 h-6 rounded-full flex items-center justify-center shadow-md animate-pulse">{{ pendingTx }}</div>
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">T�i ch�nh<br><span class="text-xs font-medium text-rose-600">Thu chi H?i Th�nh</span></h3>
              </Link>

              <!-- Card: B�o c�o TC (Treasurer) -->
              <Link v-if="activeRole === 'treasurer' && route().has('finance.reports.index')" :href="route('finance.reports.index')" 
                class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-amber-200 transition-all active:scale-95 duration-200 relative">
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">B�o c�o<br><span class="text-xs font-medium text-amber-600">B�o c�o t�i ch�nh</span></h3>
              </Link>

              <!-- Extra dynamic cards t? deptFeatures (t�nh nang du?c g�n ri�ng cho Ban Ch?p S?) -->
              <template v-for="card in visibleExtraCards" :key="'extra-' + card.key">
                <Link :href="card.href"
                  class="bg-white rounded-[1.5rem] p-5 shadow-sm border flex flex-col items-center justify-center text-center group transition-all"
                  :class="can(card.key) ? `border-gray-100 hover:shadow-md ${card.hoverBorder} active:scale-95` : 'opacity-50 grayscale border-gray-100 pointer-events-none cursor-not-allowed'">
                  <div class="w-16 h-16 mb-3 rounded-2xl flex items-center justify-center transition-colors"
                    :class="can(card.key) ? `${card.bg} ${card.text} ${card.hoverBg} group-hover:text-white` : 'bg-gray-100 text-gray-400'">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                      <g v-html="card.icon"></g>
                    </svg>
                  </div>
                  <h3 class="font-bold leading-tight" :class="can(card.key) ? 'text-gray-900' : 'text-gray-400'">
                    {{ card.label }}<br>
                    <span class="text-xs font-medium" :class="can(card.key) ? card.sub : 'text-gray-400'">{{ card.subtitle }}</span>
                  </h3>
                </Link>
              </template>
           </div>
            
           <div class="mt-8 bg-amber-50 rounded-3xl p-6 border border-amber-100 shadow-sm">
              <h3 class="text-sm font-black text-amber-900 tracking-wider mb-2 px-1 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  C?ng N?i B? � L�nh �?o H?i Th�nh
              </h3>
              <p class="text-amber-800 text-sm pl-1">B?n dang l�m vi?c v?i ch?c v? <strong>{{ roleLabel }}</strong>. M?i thao t�c s? c� t�c d?ng ? c?p d? to�n H?i Th�nh.</p>
           </div>
       </div>

    <!-- Context Switcher SlideOver -->
    <SlideOver v-model="isSwitchOpen" title="Chuy?n d?i Ch?c V?" size="md">
        <template #default>
            <div class="p-6 space-y-5">
               <p class="text-sm text-gray-500 font-medium">Ch?n ch?c v? c?n l�m vi?c.</p>
               
               <div class="space-y-2">
                  <div 
                    v-for="role in availableRoles" 
                    :key="role.id"
                    @click="switchRole(role.id)"
                    class="w-full text-left p-4 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between group"
                    :class="activeRole === role.id ? 'border-amber-500 bg-amber-50' : 'border-gray-100 bg-white hover:border-gray-300 hover:bg-gray-50'"
                  >
                     <div class="flex items-center space-x-4 shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-black" 
                          :class="activeRole === role.id ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'">
                           <svg v-if="role.id === 'secretary'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                           <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                           <h4 class="text-sm font-black" :class="activeRole === role.id ? 'text-amber-900' : 'text-gray-900'">{{ role.name }}</h4>
                           <p class="text-xs text-gray-500 mt-0.5">{{ role.desc }}</p>
                           <span v-if="activeRole === role.id" class="text-[10px] sm:text-xs text-amber-600 font-bold mt-0.5 inline-block">? �ang ho?t d?ng</span>
                        </div>
                     </div>
                     <button v-if="activeRole !== role.id" @click.stop="switchRole(role.id)" class="px-3 py-1.5 text-xs font-bold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">Ch?n</button>
                     <svg v-if="activeRole === role.id" class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                  </div>
               </div>
            </div>
        </template>
    </SlideOver>

  </PortalLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import SlideOver from '@/Components/SlideOver.vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const page = usePage();
const deptFeatures = computed(() => page.props.departmentFeatures || {});
const authPermissions = computed(() => page.props.userPermissions || {});

const props = defineProps({
  activeRole:           { type: String, default: 'secretary' },
  department:           { type: Object, default: () => ({}) },
  availableDepartments: { type: Array,  default: () => [] },
  isGlobalAdmin:        { type: Boolean, default: false },
  totalMembers:         { type: Number, default: 0 },
  currentMonth:         { type: String, default: '' },
  pendingAttendance:    { type: Number, default: 0 },
  lastMeeting:          { type: Object, default: null },
  pendingReports:       { type: Array,  default: () => [] },
  funds:                { type: Array,  default: () => [] },
  totalIncome:          { type: Number, default: 0 },
  totalExpense:         { type: Number, default: 0 },
  pendingTx:            { type: Number, default: 0 },
});

// �?c activeRole t? Inertia shared (EnsureDeaconContext shares 'activeDeaconRole')
// Fallback v? prop n?u kh�ng c�
const activeRole = computed(() => page.props.activeDeaconRole ?? props.activeRole);

const isSwitchOpen = ref(false);

// Check Level 2 user permission
const can = (key) => {
    if (props.isGlobalAdmin) return true;
    return authPermissions.value?.[key] === true
        || (key === 'members' && authPermissions.value?.['thanh-vien'] === true);
};

// Vai tr� v� t�n hi?n th?
const availableRoles = [
    { id: 'secretary', name: 'Thu k� H?i Th�nh',  desc: '�i?m danh, b�o c�o ban ng�nh' },
    { id: 'treasurer', name: 'Th? Qu? H?i Th�nh', desc: 'Qu?n l� t�i ch�nh, thu chi' },
];

const roleLabel = computed(() => {
    return availableRoles.find(r => r.id === props.activeRole)?.name ?? props.activeRole;
});

const switchRole = (roleId) => {
    router.post(route('deacon.switch-role'), { role: roleId }, {
        preserveScroll: true,
        onSuccess: () => { isSwitchOpen.value = false; }
    });
};

// -- C�c card t�nh nang "extra" du?c g�n d?ng cho Ban Ch?p S? qua FeatureDepartment --
// Nh?ng card c? d?nh (�i?m danh HT, B�o c�o, Qu?, TC) d� hardcode ? tr�n.
// ? d�y ch? render c�c t�nh nang TH�M du?c c?u h�nh qua SystemFeaturesTab.
const FIXED_KEYS_SECRETARY  = ['attendance', 'reports'];
const FIXED_KEYS_TREASURER  = ['finance'];

const allExtraCards = [
    {
        key: 'members',
        label: 'Thành Viên',
        subtitle: 'Thông tin ban viên',
        href: route('deacon.members.index'),
        bg: 'bg-blue-50', text: 'text-blue-500', hoverBg: 'group-hover:bg-blue-600',
        hoverBorder: 'hover:border-blue-200', sub: 'text-blue-600',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>`,
    },
    {
        key: 'education-classes',
        label: 'Lớp Học',
        subtitle: 'CĐGD & Trường CN',
        href: route('ministry.education.classes'),
        bg: 'bg-indigo-50', text: 'text-indigo-500', hoverBg: 'group-hover:bg-indigo-600',
        hoverBorder: 'hover:border-indigo-200', sub: 'text-indigo-600',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>`,
    },
    {
        key: 'visitation',
        label: 'Thăm Viếng',
        subtitle: 'Lịch trình hội thánh',
        href: route('ministry.visitation.index'),
        bg: 'bg-amber-50', text: 'text-amber-500', hoverBg: 'group-hover:bg-amber-500',
        hoverBorder: 'hover:border-amber-200', sub: 'text-amber-600',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>`,
    },
];

// Chỉ render extra cards nếu: deptFeatures[slug] = true VÀ không phải là fixed card
const visibleExtraCards = computed(() => {
    const features = deptFeatures.value;
    const fixedKeys = props.activeRole === 'secretary' ? FIXED_KEYS_SECRETARY : FIXED_KEYS_TREASURER;
    return allExtraCards.filter(card => {
        if (fixedKeys.includes(card.key)) return false; // d� c� card c?ng
        return features[card.key] === true
            || (card.key === 'members' && features['thanh-vien'] === true);
    });
});
</script>