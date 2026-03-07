<template>
  <PortalLayout 
      :department="activeDepartment" 
      :available-departments="availableDepartments"
      :is-global-admin="isGlobalAdmin"
      portal-type="ministry"
      @open-switcher="isSwitchOpen = true"
  >
       <!-- Empty State -->
       <div v-if="!activeDepartment" class="h-full flex flex-col items-center justify-center p-6 text-center min-h-[60vh]">
           <div class="w-24 h-24 mb-6 rounded-3xl bg-blue-100 flex items-center justify-center text-blue-500 shadow-inner">
               <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
           </div>
           <h2 class="text-xl font-black text-gray-900 mb-2">Chào mừng đến Cổng Ban Mục Vụ</h2>
           <p class="text-sm text-gray-500 max-w-xs mb-8">
              {{ isGlobalAdmin ? 'Bạn có quyền quản lý toàn hệ thống. Hãy chọn một Ban Mục Vụ để làm việc.' : 'Bạn chưa được phân bổ vào Ban Mục Vụ nào.' }}
           </p>
           <button v-if="isGlobalAdmin" @click="isSwitchOpen = true" 
              class="px-6 py-3 bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-200 font-bold hover:bg-blue-700 transition-all text-sm flex items-center">
             <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
             Chọn Ban Mục Vụ
           </button>
       </div>

       <!-- Dashboard Content -->
       <div v-else class="w-full p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl mx-auto">
           
           <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
              
              <!-- Card: Thành viên (Tất cả ban) -->
              <Link v-if="$page.props.userPermissions && $page.props.userPermissions['members']" :href="route('ministry.members.index')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-blue-200 transition-all active:scale-95">
                  <div class="w-16 h-16 mb-3 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"></path></svg>
                  </div>
                  <h3 class="font-bold text-gray-900 leading-tight">Thành viên<br><span class="text-xs font-medium text-blue-600">Thông tin ban viên</span></h3>
              </Link>

              <!-- Cards Thăm Viếng -->
              <template v-if="$page.props.userPermissions && $page.props.userPermissions['visitation']">
                  <Link :href="route('ministry.visitation.index')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-amber-200 transition-all active:scale-95">
                      <div class="w-16 h-16 mb-3 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors">
                          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"></path></svg>
                      </div>
                      <h3 class="font-bold text-gray-900 leading-tight">Thăm viếng<br><span class="text-xs font-medium text-amber-600">Lịch trình &amp; Báo cáo</span></h3>
                  </Link>
                  <Link :href="route('ministry.visitation.index')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-rose-200 transition-all active:scale-95">
                      <div class="w-16 h-16 mb-3 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center group-hover:bg-rose-500 group-hover:text-white transition-colors">
                          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"></path></svg>
                      </div>
                      <h3 class="font-bold text-gray-900 leading-tight">Đề xuất<br><span class="text-xs font-medium text-rose-600">Thăm viếng thông minh</span></h3>
                  </Link>
              </template>

              <!-- Cards Cơ Đốc Giáo Dục -->
              <template v-if="$page.props.userPermissions && $page.props.userPermissions['education-classes']">
                  <!-- Quản lý lớp -->
                  <Link :href="route('ministry.education.classes')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-indigo-200 transition-all active:scale-95">
                      <div class="w-16 h-16 mb-3 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                      </div>
                      <h3 class="font-bold text-gray-900 leading-tight">Lớp Học<br><span class="text-xs font-medium text-indigo-600">Danh sách & Thiết lập</span></h3>
                  </Link>
                  <!-- Điểm danh -->
                  <Link :href="route('ministry.education.classes')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-green-200 transition-all active:scale-95">
                      <div class="w-16 h-16 mb-3 rounded-2xl bg-green-50 text-green-500 flex items-center justify-center group-hover:bg-green-600 group-hover:text-white transition-colors">
                          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                      </div>
                      <h3 class="font-bold text-gray-900 leading-tight">Điểm Danh<br><span class="text-xs font-medium text-green-600">Vào lớp → Chọn buổi</span></h3>
                  </Link>
              </template>
              
              <template v-if="$page.props.userPermissions && $page.props.userPermissions['finance']">
                  <!-- Tiền dâng -->
                  <Link :href="route('ministry.education.classes')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-emerald-200 transition-all active:scale-95">
                      <div class="w-16 h-16 mb-3 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                      </div>
                      <h3 class="font-bold text-gray-900 leading-tight">Tài Chính<br><span class="text-xs font-medium text-emerald-600">Thu chi các lớp</span></h3>
                  </Link>
              </template>

              <template v-if="$page.props.userPermissions && $page.props.userPermissions['reports']">
                  <!-- Báo cáo -->
                  <Link :href="route('ministry.education.report')" class="bg-white rounded-[1.5rem] p-5 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center group hover:shadow-md hover:border-blue-200 transition-all active:scale-95">
                      <div class="w-16 h-16 mb-3 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                      </div>
                      <h3 class="font-bold text-gray-900 leading-tight">Báo Cáo<br><span class="text-xs font-medium text-blue-600">Thống kê theo tháng</span></h3>
                  </Link>
              </template>

              <!-- Khi không có tính năng nào -->
              <template v-if="!$page.props.userPermissions || Object.values($page.props.userPermissions).filter(Boolean).length === 0">
                  <div class="col-span-2 lg:col-span-3 bg-gray-50/40 border border-gray-200 rounded-2xl p-6 text-center text-sm text-gray-500 font-medium">
                      <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                      Ban này hiện chưa có tính năng nào được phép truy cập.
                  </div>
              </template>

           </div>
           
           <div class="mt-4 bg-blue-50/50 rounded-3xl p-5 border border-blue-50 shadow-sm">
              <h3 class="text-sm font-black text-blue-900 tracking-wider mb-1 flex items-center">
                  <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  Thông tin Ban Mục Vụ
              </h3>
              <p class="text-blue-800 text-sm">Đang xem chức năng của <strong>{{ activeDepartment.name }}</strong>. Mọi dữ liệu được lưu riêng cho ban này.</p>
           </div>
       </div>

    <!-- Context Switcher SlideOver -->
    <SlideOver v-model="isSwitchOpen" title="Chuyển đổi Ban Mục Vụ" size="md">
        <template #default>
            <div class="p-6 space-y-5">
               <p class="text-sm text-gray-500 font-medium">Chọn một Ban Mục Vụ để làm việc.</p>
               <div class="space-y-2">
                  <div v-for="dept in availableDepartments" :key="dept.id"
                    @click="switchDept(dept.id)"
                    class="w-full text-left p-4 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between group"
                    :class="activeDepartment?.id === dept.id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-white hover:border-gray-300 hover:bg-gray-50'">
                     <div class="flex items-center space-x-3 shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-sm shrink-0"
                            :class="activeDepartment?.id === dept.id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'">
                           {{ dept.name.charAt(0) }}
                        </div>
                        <div>
                           <h4 class="text-sm font-black" :class="activeDepartment?.id === dept.id ? 'text-blue-900' : 'text-gray-900'">{{ dept.name }}</h4>
                           <span v-if="activeDepartment?.id === dept.id" class="text-xs text-blue-600 font-bold">Đang hoạt động</span>
                        </div>
                     </div>
                     <button v-if="activeDepartment?.id !== dept.id" class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">Chọn</button>
                     <svg v-else class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                  </div>
               </div>
            </div>
        </template>
    </SlideOver>

  </PortalLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import SlideOver from '@/Components/SlideOver.vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
    activeDepartment: Object,
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
    userPermissions: { type: Object, default: () => ({}) },
});

// Shortcut to department code for template conditions
const deptCode = computed(() => props.activeDepartment?.code ?? '');

const isSwitchOpen = ref(!props.activeDepartment && props.isGlobalAdmin);

const switchDept = (deptId) => {
    router.post(route('ministry.switch-context'), { department_id: deptId }, {
        preserveScroll: true,
        onSuccess: () => { isSwitchOpen.value = false; }
    });
};
</script>
