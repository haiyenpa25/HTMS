<template>
  <div class="h-screen bg-gray-50 flex flex-col relative w-full overflow-hidden font-sans text-gray-900">
    <!-- Header: Portal Navigation and Admin Back -->
    <header class="bg-blue-600 text-white shadow-md relative z-20 shrink-0">
       <div class="px-4 py-3 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full flex items-center justify-between">
          <div class="flex items-center space-x-3">
             <!-- Active Department Selector -->
             <button 
                @click="emit('open-switcher')"
                class="flex items-center space-x-2 text-left group hover:bg-blue-700 p-1.5 -ml-1.5 rounded-xl transition-colors focus:outline-none"
             >
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                   <svg v-if="department" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                   <svg v-else class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                   <h1 class="text-sm sm:text-base font-black leading-tight">{{ department?.name || 'Chưa chọn Ban' }}</h1>
                   <p class="text-[10px] sm:text-xs text-blue-200 font-medium">Cổng Nội Bộ</p>
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
            <slot />
        </div>
    </main>

  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
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
</script>

<style scoped>
/* Mobile safe area padding for modern devices */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .pb-safe {
        padding-bottom: env(safe-area-inset-bottom);
    }
}
</style>
