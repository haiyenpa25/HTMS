<template>
  <div class="h-screen bg-gray-50 flex flex-col relative w-full overflow-hidden font-sans text-gray-900">
    <!-- Header: Portal Navigation and Admin Back -->
    <header class="bg-blue-600 text-white shadow-md relative z-20 shrink-0">
       <div class="px-4 py-3 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full flex items-center justify-between">
          <div class="flex items-center space-x-3">
             <!-- Active Department Selector -->
             <button 
                @click="isSwitcherOpen = true"
                class="flex items-center space-x-2 text-left group hover:bg-blue-700 p-1.5 -ml-1.5 rounded-xl transition-colors focus:outline-none"
             >
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                   <svg v-if="department" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                   <svg v-else class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                   <h1 class="text-sm sm:text-base font-black leading-tight">{{ department?.name || 'Chưa chọn Ban' }}</h1>
                   <p class="text-[10px] sm:text-xs text-blue-200 font-medium">
                      {{ portalType === 'activities' ? 'Cổng Sinh Hoạt' : portalType === 'ministry' ? 'Cổng Mục Vụ' : 'Cổng Chấp Sự' }}
                   </p>
                </div>
                <!-- Only show chevron if there are multiple departments to switch or user is pastor -->
                <svg v-if="(availableDepartments && availableDepartments.length > 1) || isGlobalAdmin" class="w-4 h-4 text-blue-300 group-hover:text-white transition-colors block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
             </button>
          </div>

          <!-- Actions & Admin Back -->
          <div class="flex items-center space-x-2">
             <Link v-if="isGlobalAdmin" :href="route('dashboard')" class="text-xs font-bold bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg flex items-center transition-colors shadow-sm">
                Quản trị <span class="hidden sm:inline ml-1"> Hệ thống</span>
                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
             </Link>
             <Link :href="route('logout')" method="post" as="button" class="text-white bg-white/10 hover:text-red-200 transition-colors p-2 rounded-full hover:bg-white/20" title="Đăng xuất">
                <svg class="w-5 h-5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0v-1m6-10V7a3 3 0 00-6 0v1"></path></svg>
             </Link>
          </div>
       </div>

       <!-- Mobile Context Tabs (Optional based on design) -->
       <div v-if="department && !hideNav" class="px-2 pb-0 flex overflow-x-auto no-scrollbar border-t border-white/10 max-w-7xl mx-auto w-full">
           <!-- Activities Portal Links -->
           <template v-if="portalType === 'activities'">
               <Link :href="route('portal.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('portal.index') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                   Bảng điều khiển
               </Link>
               <Link v-if="deptFeatures && deptFeatures['attendance']" :href="route('portal.attendance.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="[route().current('portal.attendance.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white', (!authPermissions || !authPermissions['attendance']) ? 'opacity-50 pointer-events-none' : '']">
                   Điểm danh
               </Link>
               <Link v-if="deptFeatures && deptFeatures['visitation']" :href="route('portal.visitation.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="[route().current('portal.visitation.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white', (!authPermissions || !authPermissions['visitation']) ? 'opacity-50 pointer-events-none' : '']">
                   Thăm viếng
               </Link>
               <Link v-if="deptFeatures && (deptFeatures['members'] || deptFeatures['thanh-vien'])" :href="route('portal.members.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="[route().current('portal.members.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white', (!authPermissions || !(authPermissions['members'] || authPermissions['thanh-vien'])) ? 'opacity-50 pointer-events-none' : '']">
                   Thành viên
               </Link>
               <Link v-if="deptFeatures && deptFeatures['reports']" :href="route('portal.reports.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="[route().current('portal.reports.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white', (!authPermissions || !authPermissions['reports']) ? 'opacity-50 pointer-events-none' : '']">
                   Báo cáo
               </Link>
                <Link v-if="deptFeatures && deptFeatures['finance']" :href="route('portal.finance.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="[route().current('portal.finance.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white', (!authPermissions || !authPermissions['finance']) ? 'opacity-50 pointer-events-none' : '']">
                    Tài chính
                </Link>
                <Link :href="route('duty-rooster.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('duty-rooster.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                    Phân công
                </Link>
           </template>

           <!-- Ministry Portal Links -->
           <template v-else-if="portalType === 'ministry'">
               <Link :href="route('ministry.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('ministry.index') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                   Bảng điều khiển
               </Link>
               <Link v-if="deptFeatures && (deptFeatures['members'] || deptFeatures['thanh-vien'])" :href="route('ministry.members.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="[route().current('ministry.members.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white', (!authPermissions || !(authPermissions['members'] || authPermissions['thanh-vien'])) ? 'opacity-50 pointer-events-none' : '']">
                   Thành viên
               </Link>
                <Link v-if="deptFeatures && deptFeatures['visitation']" :href="route('ministry.visitation.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="[route().current('ministry.visitation.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white', (!authPermissions || !authPermissions['visitation']) ? 'opacity-50 pointer-events-none' : '']">
                     Thăm viếng
                 </Link>
                 <!-- Education features integrated into Ministry -->
                 <Link v-if="deptFeatures && deptFeatures['education-classes']" :href="route('ministry.education.classes')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="[route().current('ministry.education.classes') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white', (!authPermissions || !authPermissions['education-classes']) ? 'opacity-50 pointer-events-none' : '']">
                     Lớp Học
                 </Link>
                 <Link v-if="deptFeatures && deptFeatures['education-report']" :href="route('ministry.education.report')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="[route().current('ministry.education.report') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white', (!authPermissions || !authPermissions['education-report']) ? 'opacity-50 pointer-events-none' : '']">
                     Báo Cáo GD
                 </Link>
                 <Link :href="route('duty-rooster.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('duty-rooster.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                     Phân công
                 </Link>
           </template>

           <!-- Education (CĐGD) Portal Links -->
           <template v-else-if="portalType === 'education'">
               <Link :href="route('ministry.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2 border-transparent text-blue-200 hover:text-white flex items-center gap-1">
                   <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                   Mục Vụ
               </Link>
               <Link :href="route('education.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('education.index') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                   Tổng quan
               </Link>
               <Link :href="route('education.classes')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('education.classes') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                   Quản lý lớp
               </Link>
               <Link :href="route('education.report')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('education.report') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                   Báo cáo
               </Link>
           </template>

           <!-- Finance Portal Links -->
           <template v-else-if="portalType === 'finance'">
               <Link :href="route('finance.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('finance.index') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                   Bảng điều khiển
               </Link>
               <Link :href="route('finance.transactions.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('finance.transactions.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                   Sổ Cầm Quỹ
               </Link>
               <Link :href="route('finance.reports.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('finance.reports.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                   Báo cáo tháng
               </Link>
            </template>

            <!-- Deacon Portal Links -->
            <template v-else-if="portalType === 'deacon'">
                <Link :href="route('deacon.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('deacon.index') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                    Bảng điều khiển
                </Link>
                <!-- Thư Ký Links -->
                <template v-if="department?.id === 'secretary'">
                    <Link :href="route('deacon.attendance')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('deacon.attendance') || route().current('deacon.attendance.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                        Điểm danh
                    </Link>
                    <Link :href="route('deacon.report')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('deacon.report') || route().current('deacon.report.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                        Báo cáo
                    </Link>
                </template>
                <!-- Thủ Quỹ Links -->
                <template v-if="department?.id === 'treasurer'">
                    <Link v-if="route().has('finance.funds.index')" :href="route('finance.funds.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('finance.funds.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                        Quản lý Quỹ
                    </Link>
                    <Link :href="route('finance.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('finance.index') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                        Tài chính
                    </Link>
                    <Link v-if="route().has('finance.reports.index')" :href="route('finance.reports.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('finance.reports.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                        Báo cáo
                    </Link>
                </template>

                <!-- MAC: Thành viên (Global link available in Deacon too) -->
                <template v-if="deptFeatures && (deptFeatures['members'] || deptFeatures['thanh-vien'])">
                    <Link :href="route('ministry.members.index')" 
                        class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" 
                        :class="[
                            route().current('ministry.members.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white',
                            (!authPermissions || !(authPermissions['members'] || authPermissions['thanh-vien'])) ? 'opacity-50 grayscale pointer-events-none' : ''
                        ]">
                        Thành viên
                    </Link>
                    <Link :href="route('duty-rooster.index')" class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2" :class="route().current('duty-rooster.*') ? 'border-white text-white' : 'border-transparent text-blue-200 hover:text-white'">
                        Phân công
                    </Link>
                </template>
            </template>
       </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-x-hidden overflow-y-auto w-full relative pb-safe">
        <div class="max-w-7xl mx-auto w-full px-3 sm:px-6 lg:px-8">
            <!-- Flash Message -->
            <div v-if="$page.props.flash.error" class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm font-bold rounded-r-xl shadow-sm animate-in fade-in slide-in-from-top-2">
                {{ $page.props.flash.error }}
            </div>
            <slot />
        </div>
    </main>

  </div>

  <!-- Global Context Switcher SlideOver -->
  <SlideOver v-model="isSwitcherOpen" title="Chuyển đổi Ban ngành" size="md">
      <template #default>
          <div class="p-6 space-y-8">
              <div v-for="(depts, block) in allDeptsGrouped" :key="block">
                  <template v-if="depts.length > 0">
                      <h3 class="flex items-center gap-2 text-xs font-black uppercase tracking-widest mb-4" :class="blockInfo[block]?.color">
                          <span class="w-8 h-8 rounded-lg flex items-center justify-center bg-current opacity-10" :class="blockInfo[block]?.bg"></span>
                          <span>{{ blockInfo[block]?.icon }} {{ blockInfo[block]?.name }}</span>
                      </h3>
                      <div class="space-y-2">
                          <button v-for="dept in depts" :key="dept.id"
                              @click="switchDept(dept.id)"
                              class="w-full text-left p-4 rounded-2xl border-2 transition-all flex items-center justify-between group"
                              :class="department?.id === dept.id && portalType === (block === 'leadership' ? 'deacon' : block) 
                                  ? 'border-blue-500 bg-blue-50 ring-4 ring-blue-50' 
                                  : 'border-gray-100 bg-white hover:border-blue-300 hover:bg-gray-50'">
                              <div class="flex items-center space-x-4">
                                  <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm transition-colors"
                                      :class="department?.id === dept.id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500 group-hover:bg-blue-100 group-hover:text-blue-600'">
                                      {{ dept.name.charAt(0) }}
                                  </div>
                                  <div class="min-w-0">
                                      <h4 class="text-sm font-black truncate" :class="department?.id === dept.id ? 'text-blue-900' : 'text-gray-900'">{{ dept.name }}</h4>
                                      <p v-if="department?.id === dept.id" class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">Đang hoạt động</p>
                                      <p v-else class="text-[10px] text-gray-400 font-medium">Bấm để chuyển sang</p>
                                  </div>
                              </div>
                              <svg v-if="department?.id === dept.id" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                              </svg>
                              <svg v-else class="w-4 h-4 text-gray-300 group-hover:text-blue-400 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                              </svg>
                          </button>
                      </div>
                  </template>
              </div>
              <!-- Empty state if no depts -->
              <div v-if="Object.values(allDeptsGrouped).every(d => d.length === 0)" class="text-center py-12">
                   <p class="text-gray-400 font-bold">Bạn chưa được phân quyền vào Ban ngành nào.</p>
              </div>
          </div>
      </template>
  </SlideOver>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const deptFeatures = computed(() => page.props.departmentFeatures || {});
const authPermissions = computed(() => page.props.userPermissions || {});

const props = defineProps({
    department: Object,
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
    userPermissions: {
        type: Object,
        default: () => ({})
    },
    portalType: {
        type: String,
        default: 'activities'
    },
    hideNav: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['open-switcher']);

// ── Global Switcher Logic ──────────────────────────────────────────────────
import SlideOver from '@/Components/SlideOver.vue';
import { ref } from 'vue';

const isSwitcherOpen = ref(false);
const allDeptsGrouped = computed(() => page.props.allAvailableDepartments || {});

const blockInfo = {
    activities: { name: 'Ban Ngành Sinh Hoạt', icon: '🎯', color: 'text-blue-600', bg: 'bg-blue-50' },
    ministry:   { name: 'Ban Ngành Mục Vụ',   icon: '⛪', color: 'text-emerald-600', bg: 'bg-emerald-50' },
    leadership: { name: 'Ban Chấp Sự / Lãnh Đạo', icon: '🛡', color: 'text-amber-600', bg: 'bg-amber-50' }
};

const switchDept = (deptId) => {
    router.post(route('portal.switch-context'), { department_id: deptId }, {
        preserveScroll: true,
        onSuccess: () => { isSwitcherOpen.value = false; }
    });
};
</script>

<style scoped>
/* Mobile safe area padding for modern devices */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .pb-safe {
        padding-bottom: env(safe-area-inset-bottom);
    }
}
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
