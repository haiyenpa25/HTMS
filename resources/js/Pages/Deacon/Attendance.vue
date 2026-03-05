<template>
  <PortalLayout 
      :department="department" 
      :available-departments="availableDepartments"
      :is-global-admin="isGlobalAdmin"
      portal-type="deacon"
      @open-switcher="isSwitchOpen = true"
  >
       <div class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto space-y-6">
           <div class="flex items-center justify-between">
               <div>
                   <h2 class="text-2xl font-black text-gray-900">Điểm Danh Hội Thánh</h2>
                   <p class="text-sm text-gray-500 mt-1">Quản lý điểm danh các buổi nhóm chung của toàn Hội Thánh.</p>
               </div>
           </div>

           <!-- Meeting List -->
           <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
               <div v-if="meetings.length === 0" class="p-12 text-center">
                   <div class="w-16 h-16 mx-auto bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mb-4">
                       <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                   </div>
                   <h3 class="text-lg font-bold text-gray-900">Chưa có buổi nhóm Hội Thánh nào</h3>
                   <p class="text-gray-500 mt-1">Các buổi nhóm (type: church) sẽ hiện ở đây.</p>
               </div>
               
               <ul v-else class="divide-y divide-gray-100">
                   <li v-for="meeting in meetings" :key="meeting.id" class="p-4 sm:p-5 hover:bg-gray-50 transition-colors">
                       <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                           <div class="flex items-start gap-4">
                               <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" :class="meeting.attendance_marked ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'">
                                   <svg v-if="meeting.attendance_marked" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                   <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                               </div>
                               <div>
                                   <h4 class="font-bold text-gray-900 text-base sm:text-lg">{{ meeting.title || 'Buổi nhóm HT' }}</h4>
                                   <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-sm text-gray-500">
                                       <span class="flex items-center gap-1">
                                           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                           {{ meeting.date }}
                                       </span>
                                       <span class="flex items-center gap-1">
                                           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                           {{ meeting.time.substring(0,5) }}
                                       </span>
                                   </div>
                               </div>
                           </div>
                           <div class="shrink-0 flex items-center justify-end">
                               <span v-if="meeting.attendance_marked" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                   Đã điểm danh
                               </span>
                               <span v-else class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                   Chưa điểm danh
                               </span>
                               <button class="ml-3 px-4 py-2 border border-gray-200 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                   Xem
                                   <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                               </button>
                           </div>
                       </div>
                   </li>
               </ul>
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
                    :class="department.id === dept.id ? 'border-amber-500 bg-amber-50' : 'border-gray-100 bg-white hover:border-gray-300 hover:bg-gray-50'"
                  >
                     <div class="flex items-center space-x-4 shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-black" :class="department.id === dept.id ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200'">
                           <svg v-if="dept.id === 'secretary'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                           <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                           <h4 class="text-sm font-black" :class="department.id === dept.id ? 'text-amber-900' : 'text-gray-900'">{{ dept.name }}</h4>
                        </div>
                     </div>
                     <button v-if="department.id !== dept.id" @click.stop="switchDept(dept.id)" class="px-3 py-1.5 text-xs font-bold text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-lg transition-colors">Chọn</button>
                     <svg v-if="department.id === dept.id" class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
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
  meetings:             { type: Array,  default: () => [] },
  department:           { type: Object, default: () => ({}) },
  availableDepartments: { type: Array,  default: () => [] },
  isGlobalAdmin:        { type: Boolean, default: false },
});

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
