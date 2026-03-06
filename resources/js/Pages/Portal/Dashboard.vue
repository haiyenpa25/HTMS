<template>
  <PortalLayout 
      :department="activeDepartment" 
      :available-departments="availableDepartments"
      :is-global-admin="isGlobalAdmin"
      portal-type="activities"
      @open-switcher="isSwitchOpen = true"
  >
       <!-- Empty State (No Department Selected or No Access) -->
       <div v-if="!activeDepartment" class="h-full flex flex-col items-center justify-center p-6 text-center animate-in fade-in zoom-in-95 duration-500 min-h-[60vh]">
           <div class="w-24 h-24 mb-6 rounded-3xl bg-blue-100 flex items-center justify-center text-blue-500 shadow-inner">
               <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
           </div>
           <h2 class="text-xl font-black text-gray-900 mb-2">Chào mừng đến Cổng Ban Sinh Hoạt</h2>
           <p class="text-sm text-gray-500 max-w-xs mb-8">
              {{ isGlobalAdmin ? 'Bạn có quyền quản lý toàn hệ thống. Hãy chọn một Ban Sinh Hoạt để xem dữ liệu báo cáo chi tiết.' : 'Bạn chưa được phân bổ vào Ban Sinh Hoạt nào.' }}
           </p>
           <button 
             v-if="isGlobalAdmin" 
             @click="isSwitchOpen = true" 
             class="px-6 py-3 bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-200 font-bold hover:bg-blue-700 hover:-translate-y-0.5 transition-all text-sm flex items-center text-shadow-sm"
           >
             <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
             Chọn Ban Sinh Hoạt
           </button>
       </div>

       <!-- Dashboard Content -->
       <div v-else class="w-full p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl mx-auto">
           
           <!-- Grid Cards -->
           <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
              
              <!-- Card: Attendance -->
              <Link :href="route('portal.attendance.index')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-emerald-200 transition-all active:scale-95 duration-200">
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Điểm danh<br><span class="text-xs font-medium text-emerald-600">Nhóm & Quản lý Tổ</span></h3>
              </Link>
              
              <!-- Card: Visitation (Localized) -->
              <Link :href="route('portal.visitation.index')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-amber-200 transition-all active:scale-95 duration-200">
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Thăm viếng<br><span class="text-xs font-medium text-amber-600">Nội bộ ban ngành</span></h3>
              </Link>

              <!-- Card: Members -->
              <Link :href="route('portal.members.index')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-blue-200 transition-all active:scale-95 duration-200">
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Thành viên<br><span class="text-xs font-medium text-blue-600">Thông tin ban viên</span></h3>
              </Link>
              
              <!-- Card: Assignments -->
              <Link :href="route('portal.assignments.index')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-indigo-200 transition-all active:scale-95 duration-200">
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Phân công<br><span class="text-xs font-medium text-indigo-600">Nhân sự nhóm</span></h3>
              </Link>

              <!-- Card: Reports -->
              <Link :href="route('portal.reports.index')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-purple-200 transition-all active:scale-95 duration-200">
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-purple-50 text-purple-500 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Báo cáo<br><span class="text-xs font-medium text-purple-600">Thống kê hoạt động</span></h3>
              </Link>
              
              <!-- Card: Finance -->
              <Link :href="route('portal.finance.index')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-rose-200 transition-all active:scale-95 duration-200">
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors duration-300">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Tài chính<br><span class="text-xs font-medium text-rose-600">Thu chi & Quỹ</span></h3>
              </Link>

           </div>
           
           <div class="mt-8 bg-blue-50/50 rounded-3xl p-6 border border-blue-50 shadow-sm">
              <h3 class="text-sm font-black text-blue-900 tracking-wider mb-2 px-1 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  Thông tin Ban
              </h3>
              <p class="text-blue-800 text-sm pl-1">Bạn đang xem các chức năng dành riêng cho {{ activeDepartment.name }}. Mọi thao tác điểm danh, thống kê, và phân bổ nhân sự sẽ chỉ áp dụng trong phạm vi ban sinh hoạt này.</p>
           </div>
       </div>

    <!-- Context Switcher SlideOver -->
    <SlideOver v-model="isSwitchOpen" title="Chuyển đổi Ban Sinh Hoạt" size="md">
        <template #default>
            <div class="p-6 space-y-5">
               <p class="text-sm text-gray-500 font-medium">Bạn có quyền truy cập vào các Ban Sinh Hoạt dưới đây. Hãy chọn một Ban để làm việc.</p>
               
               <div class="space-y-2">
                  <div 
                    v-for="dept in availableDepartments" 
                    :key="dept.id"
                    @click="switchDept(dept.id)"
                    class="w-full text-left p-4 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between group"
                    :class="activeDepartment?.id === dept.id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-white hover:border-gray-300 hover:bg-gray-50'"
                  >
                     <div class="flex items-center space-x-4 shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-black" :class="activeDepartment?.id === dept.id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'">
                           {{ dept.name.charAt(0) }}
                        </div>
                        <div>
                           <h4 class="text-sm font-black" :class="activeDepartment?.id === dept.id ? 'text-blue-900' : 'text-gray-900'">{{ dept.name }}</h4>
                           <span v-if="activeDepartment?.id === dept.id" class="text-[10px] sm:text-xs text-blue-600 font-bold mt-0.5 inline-block">Đang hoạt động</span>
                        </div>
                     </div>
                     <button v-if="activeDepartment?.id !== dept.id" @click.stop="switchDept(dept.id)" class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">Chọn</button>
                     <svg v-if="activeDepartment?.id === dept.id" class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
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
    activeDepartment: Object,
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
});

const isSwitchOpen = ref(!props.activeDepartment && props.isGlobalAdmin);

const switchDept = (deptId) => {
    router.post(route('portal.switch-context'), { department_id: deptId }, {
        preserveScroll: true,
        onSuccess: () => {
            isSwitchOpen.value = false;
        }
    });
};
</script>
