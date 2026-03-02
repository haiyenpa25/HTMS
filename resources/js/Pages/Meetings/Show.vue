<template>
  <component :is="currentLayout">
    <template #header>Chi tiết Buổi nhóm</template>

    <div class="py-4 space-y-6">
      
      <!-- Go back Header -->
      <div class="flex items-center space-x-4">
        <Link :href="route('meetings.index')" class="p-2 bg-white border border-gray-200 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition-colors shadow-sm">
           <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </Link>
        <div>
           <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ meeting.topic || 'Buổi nhóm chưa có chủ đề' }}</h1>
           <div class="flex items-center text-sm font-medium text-gray-500 mt-1 space-x-2">
              <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider" :class="meeting.type === 'church' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'">
                {{ meeting.type === 'church' ? 'Hội Thánh chung' : 'Ban Ngành' }}
              </span>
              <span>•</span>
              <span class="flex items-center">
                 <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                 {{ formatDate(meeting.date) }} lúc {{ meeting.time }}
              </span>
              <template v-if="meeting.department">
                 <span>•</span>
                 <span class="font-bold text-gray-700">{{ meeting.department.name }}</span>
              </template>
           </div>
        </div>
      </div>

      <!-- Quick Info Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
         <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-4">
            <div class="flex-shrink-0 w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-blue-600">
               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
               <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Tổng tham dự</p>
               <p class="text-xl font-bold text-gray-900 mt-0.5">{{ meeting.type === 'church' ? (meeting.report?.total_attendance || 0) : (meeting.report?.total_members_present || 0) }}</p>
            </div>
         </div>
         <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-4">
            <div class="flex-shrink-0 w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-600">
               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <div class="w-full truncate">
               <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Kinh Thánh</p>
               <p class="text-[13px] font-bold text-gray-900 mt-1 truncate block">{{ meeting.scripture || '-' }}</p>
            </div>
         </div>
         <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-4">
            <div class="flex-shrink-0 w-12 h-12 bg-purple-50 rounded-full flex items-center justify-center text-purple-600">
               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <div class="w-full truncate">
               <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Câu Gốc</p>
               <p class="text-[13px] font-bold text-gray-900 mt-1 truncate block">{{ meeting.memory_verse || '-' }}</p>
            </div>
         </div>
         <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-4">
            <div class="flex-shrink-0 w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center text-amber-600">
               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
            </div>
            <div class="w-full truncate">
               <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Sứ điệp</p>
               <p class="text-sm font-bold text-gray-900 mt-1 truncate block">{{ meeting.preacher || '-' }}</p>
            </div>
         </div>
      </div>

      <!-- Tabs Navigation using existing pattern -->
      <div class="bg-white px-2 py-2 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto hide-scrollbar sticky top-0 z-20">
         <div class="flex space-x-2 min-w-max">
            <button 
               @click="activeTab = 'content'" 
               class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all relative overflow-hidden"
               :class="activeTab === 'content' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100'"
            >
               Nội dung & Phân công
            </button>
            <button 
               @click="activeTab = 'attendance'" 
               class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all relative overflow-hidden flex items-center space-x-2"
               :class="activeTab === 'attendance' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100'"
            >
               <span>Điểm danh</span>
            </button>
            <button 
               @click="activeTab = 'finances'" 
               class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all relative overflow-hidden flex items-center space-x-2"
               :class="activeTab === 'finances' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100'"
            >
               <span>Tài chính</span>
            </button>
         </div>
      </div>

      <!-- Tab Content Area -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 min-h-[400px]">
         
         <!-- Tab: Nội dung & Phân công -->
         <div v-if="activeTab === 'content'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
            <!-- Content & Actions implementation is deferred to next steps -->
            <div class="mb-4 flex items-center justify-between">
               <h3 class="font-black text-gray-900 text-lg tracking-tight">Chi tiết Phân công</h3>
               <button class="px-4 py-2 border-2 border-dashed border-gray-300 text-gray-500 font-bold rounded-xl hover:border-blue-500 hover:text-blue-600 transition-colors text-sm">
                  + Gán nhân sự
               </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
               <!-- Church meeting personnel skeleton -->
               <template v-if="meeting.type === 'church'">
                 <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 flex justify-between items-center group">
                    <div>
                       <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Hướng dẫn chương trình</p>
                       <p class="text-sm font-bold text-gray-900"><i>(Đang cập nhật)</i></p>
                    </div>
                    <button class="text-xs text-blue-600 font-bold opacity-0 group-hover:opacity-100 hover:underline">Sửa</button>
                 </div>
                 <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 flex justify-between items-center group">
                    <div>
                       <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Ban hát dẫn</p>
                       <p class="text-sm font-bold text-gray-900"><i>(Đang cập nhật)</i></p>
                    </div>
                    <button class="text-xs text-blue-600 font-bold opacity-0 group-hover:opacity-100 hover:underline">Sửa</button>
                 </div>
               </template>
               <template v-else>
                 <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 flex justify-between items-center group">
                    <div>
                       <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-1">Tổ phụ trách</p>
                       <p class="text-sm font-bold text-gray-900"><i>(Đang cập nhật)</i></p>
                    </div>
                    <button class="text-xs text-blue-600 font-bold opacity-0 group-hover:opacity-100 hover:underline">Sửa</button>
                 </div>
               </template>
            </div>
         </div>

         <!-- Tab: Điểm danh -->
         <div v-if="activeTab === 'attendance'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
            <h3 class="font-black text-gray-900 text-lg tracking-tight mb-4">Ghi nhận Điểm danh</h3>
            <p class="text-gray-500 text-sm mb-6">Chức năng quản lý danh sách có mặt, tính toán báo cáo chuyên hệ thống.</p>
         </div>

         <!-- Tab: Tài chính -->
         <div v-if="activeTab === 'finances'" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
            <h3 class="font-black text-gray-900 text-lg tracking-tight mb-4">Tài chính (Thu/Chi)</h3>
            <p class="text-gray-500 text-sm mb-6">Toàn bộ dữ liệu thu/chi tại đây cần được trạng thái Approved mới hiển thị lên Tóm tắt Kế toán chung.</p>
         </div>

      </div>

    </div>
  </component>
</template>

<script setup>
import { ref, computed, markRaw, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const props = defineProps({
  meeting: {
    type: Object,
    required: true
  }
});

const isMobile = ref(window.innerWidth < 768);
const currentLayout = computed(() => isMobile.value ? markRaw(MobileLayout) : markRaw(AuthenticatedLayout));

const updateScreenSize = () => {
    isMobile.value = window.innerWidth < 768;
};

onMounted(() => window.addEventListener('resize', updateScreenSize));
onUnmounted(() => window.removeEventListener('resize', updateScreenSize));

const activeTab = ref('content');

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>
