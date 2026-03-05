<template>
  <PortalLayout 
      :department="department" 
      :available-departments="availableDepartments"
      :is-global-admin="isGlobalAdmin"
      portal-type="deacon"
      @open-switcher="isSwitchOpen = true"
  >
       <!-- Empty State (just in case) -->
       <div v-if="!activeRole" class="h-full flex flex-col items-center justify-center p-6 text-center animate-in fade-in zoom-in-95 duration-500 min-h-[60vh]">
           <div class="w-24 h-24 mb-6 rounded-3xl bg-blue-100 flex items-center justify-center text-blue-500 shadow-inner">
               <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
           </div>
           <h2 class="text-xl font-black text-gray-900 mb-2">Ban Chấp Sự</h2>
           <p class="text-sm text-gray-500 max-w-xs mb-8">Vui lòng chọn vai trò để tiếp tục.</p>
       </div>

       <!-- Dashboard Content -->
       <div v-else class="w-full p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl mx-auto">
           
           <!-- Grid Cards for Secretary -->
           <div v-if="activeRole === 'secretary'" class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
              
              <!-- Card: Điểm danh HT -->
              <Link :href="route('deacon.attendance')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-emerald-200 transition-all active:scale-95 duration-200 relative">
                  <!-- Notification Badge -->
                  <div v-if="pendingAttendance > 0" class="absolute top-4 right-4 bg-amber-500 text-white text-[10px] font-black w-6 h-6 rounded-full flex items-center justify-center shadow-md animate-pulse">
                      {{ pendingAttendance }}
                  </div>
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Điểm danh<br><span class="text-xs font-medium text-emerald-600">Buổi nhóm HT</span></h3>
              </Link>

              <!-- Card: Reports -->
              <Link :href="route('deacon.report')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-purple-200 transition-all active:scale-95 duration-200 relative">
                  <!-- Notification Badge -->
                  <div v-if="pendingReports.length > 0" class="absolute top-4 right-4 bg-red-500 text-white text-[10px] font-black w-6 h-6 rounded-full flex items-center justify-center shadow-md animate-pulse">
                      {{ pendingReports.length }}
                  </div>
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-purple-50 text-purple-500 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Báo cáo<br><span class="text-xs font-medium text-purple-600">Các ban trực thuộc</span></h3>
              </Link>
           </div>
           
           <!-- Grid Cards for Treasurer -->
           <div v-if="activeRole === 'treasurer'" class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
              <!-- Card: Funds -->
              <Link v-if="route().has('finance.funds.index')" :href="route('finance.funds.index')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-emerald-200 transition-all active:scale-95 duration-200 relative">
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Quản lý Quỹ<br><span class="text-xs font-medium text-emerald-600">{{ funds.length }} quỹ</span></h3>
              </Link>

              <!-- Card: Finance -->
              <Link :href="route('finance.index')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-rose-200 transition-all active:scale-95 duration-200 relative">
                  <div v-if="pendingTx > 0" class="absolute top-4 right-4 bg-amber-500 text-white text-[10px] font-black w-6 h-6 rounded-full flex items-center justify-center shadow-md animate-pulse">
                      {{ pendingTx }}
                  </div>
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Tài chính<br><span class="text-xs font-medium text-rose-600">Thu chi Hội Thánh</span></h3>
              </Link>

              <!-- Card: Reports Finance -->
              <Link v-if="route().has('finance.reports.index')" :href="route('finance.reports.index')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-indigo-200 transition-all active:scale-95 duration-200 relative">
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Báo cáo<br><span class="text-xs font-medium text-indigo-600">Báo cáo tài chính</span></h3>
              </Link>
           </div>
           
           <div class="mt-8 bg-amber-50 rounded-3xl p-6 border border-amber-100 shadow-sm">
              <h3 class="text-sm font-black text-amber-900 tracking-wider mb-2 px-1 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  Thông tin Cổng Nội Bộ Ban Chấp Sự
              </h3>
              <p class="text-amber-800 text-sm pl-1">Bạn đang xem các chức năng dành riêng cho vai trò <strong>{{ department.name }}</strong>. Mọi thao tác sẽ có tác động ở cấp độ toàn Hội Thánh.</p>
           </div>
       </div>

    <!-- Context Switcher SlideOver -->
    <SlideOver v-model="isSwitchOpen" title="Chuyển đổi Vai Trò Chấp Sự" size="md">
        <template #default>
            <div class="p-6 space-y-5">
               <p class="text-sm text-gray-500 font-medium">Bạn có quyền truy cập vào các Vai Trò dưới đây. Hãy chọn một Vai Trò để làm việc.</p>
               
               <div class="space-y-2">
                  <div 
                    v-for="dept in availableDepartments" 
                    :key="dept.id"
                    @click="switchDept(dept.id)"
                    class="w-full text-left p-4 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between group"
                    :class="activeRole === dept.id ? 'border-amber-500 bg-amber-50' : 'border-gray-100 bg-white hover:border-gray-300 hover:bg-gray-50'"
                  >
                     <div class="flex items-center space-x-4 shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-black" :class="activeRole === dept.id ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'">
                           <svg v-if="dept.id === 'secretary'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                           <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                           <h4 class="text-sm font-black" :class="activeRole === dept.id ? 'text-amber-900' : 'text-gray-900'">{{ dept.name }}</h4>
                           <span v-if="activeRole === dept.id" class="text-[10px] sm:text-xs text-amber-600 font-bold mt-0.5 inline-block">Đang hoạt động</span>
                        </div>
                     </div>
                     <button v-if="activeRole !== dept.id" @click.stop="switchDept(dept.id)" class="px-3 py-1.5 text-xs font-bold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">Chọn</button>
                     <svg v-if="activeRole === dept.id" class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                  </div>
               </div>
            </div>
        </template>
    </SlideOver>

  </PortalLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import SlideOver from '@/Components/SlideOver.vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
  activeRole:           { type: String, default: 'secretary' },
  department:           { type: Object, default: () => ({}) },
  availableDepartments: { type: Array,  default: () => [] },
  isGlobalAdmin:        { type: Boolean, default: false },
  totalMembers:         { type: Number, default: 0 },
  currentMonth:         { type: String, default: '' },
  // Secretary
  pendingAttendance:    { type: Number, default: 0 },
  lastMeeting:          { type: Object, default: null },
  pendingReports:       { type: Array,  default: () => [] },
  // Treasurer
  funds:                { type: Array,  default: () => [] },
  totalIncome:          { type: Number, default: 0 },
  totalExpense:         { type: Number, default: 0 },
  pendingTx:            { type: Number, default: 0 },
});

// Chú ý: ở PortalLayout sẽ hiện SlideOver nếu isSwitchOpen = true
const isSwitchOpen = ref(false);

const switchDept = (roleId) => {
    router.post(route('deacon.switch-role'), { role: roleId }, {
        preserveScroll: true,
        onSuccess: () => {
            isSwitchOpen.value = false;
        }
    });
};
</script>
