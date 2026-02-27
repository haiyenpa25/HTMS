<template>
  <component :is="currentLayout">
    <template #header>
      Chi tiết Ban Ngành
    </template>

    <div class="py-4 space-y-6 max-w-5xl mx-auto">
      <!-- Nút quay lại & Thao tác -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <Link :href="route('departments.index')" class="flex items-center text-sm text-gray-500 hover:text-blue-600 transition-colors group w-fit">
          <svg class="w-5 h-5 mr-1 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Quay lại danh sách
        </Link>
        <div class="flex items-center space-x-2">
           <button class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-50 shadow-sm transition-all hidden sm:block">
             Báo cáo nhanh
           </button>
           <button class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 shadow-md shadow-blue-100 transition-all">
             Chỉnh sửa
           </button>
        </div>
      </div>

      <!-- Department Header Card -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="h-40 bg-gradient-to-br from-indigo-500 via-purple-600 to-purple-700 relative">
          <div class="absolute inset-0 bg-grid-white/[0.1] bg-[size:16px_16px]"></div>
          <div class="absolute bottom-4 right-6 flex space-x-2">
             <span :class="[
                department.is_active ? 'bg-green-500 text-white border-green-400' : 'bg-red-500 text-white border-red-400',
                'px-3 py-1 bg-opacity-90 backdrop-blur-md text-[10px] uppercase font-black rounded-full border shadow-sm flex items-center'
             ]">
                <span class="w-1.5 h-1.5 rounded-full bg-white mr-1.5 animate-pulse" v-if="department.is_active"></span>
                {{ department.is_active ? 'Đang hoạt động' : 'Tạm ngưng' }}
             </span>
             <span v-if="department.code" class="px-3 py-1 bg-white/20 backdrop-blur-md text-white text-[10px] uppercase font-black rounded-full border border-white/20">
                {{ department.code }}
             </span>
          </div>
        </div>
        
        <div class="px-4 sm:px-8 pb-8 relative">
          <div class="flex flex-col md:flex-row md:items-end -mt-16 sm:space-x-8">
            <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-3xl bg-white p-1.5 shadow-xl mx-auto md:mx-0">
              <div class="w-full h-full rounded-2xl bg-gradient-to-tr from-indigo-100 to-purple-100 text-indigo-700 flex items-center justify-center text-4xl sm:text-5xl font-black">
                {{ department.name.charAt(0) }}
              </div>
            </div>
            <div class="mt-4 sm:mt-6 md:mt-0 flex-1 pb-1 text-center md:text-left">
              <h1 class="text-2xl sm:text-3xl font-black text-gray-900 leading-none mb-2">{{ department.name }}</h1>
              <div class="flex flex-wrap items-center justify-center md:justify-start gap-y-2 gap-x-6 text-sm text-gray-500 font-bold">
                <div class="flex items-center">
                  <div class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></div>
                  Khối: {{ blockLabels[department.block] || department.block }}
                </div>
                <div class="flex items-center text-gray-400">
                  <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                  Tổ chức
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex items-center space-x-1 bg-gray-100 p-1.5 rounded-2xl w-full sm:w-fit overflow-x-auto no-scrollbar mx-auto sm:mx-0">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'px-5 sm:px-6 py-2.5 rounded-xl text-xs sm:text-sm font-black transition-all whitespace-nowrap',
            activeTab === tab.id 
              ? 'bg-white text-blue-700 shadow-sm' 
              : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200'
          ]"
        >
          <div class="flex items-center justify-center">
             {{ tab.name }}
             <span v-if="tab.count !== undefined" :class="[
                activeTab === tab.id ? 'bg-blue-100 text-blue-600' : 'bg-gray-200 text-gray-600',
                'ml-2 py-0.5 px-2 rounded-full text-[10px] font-bold'
             ]">
                {{ tab.count }}
             </span>
          </div>
        </button>
      </div>

      <!-- Tab Content Area -->
      <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
         <!-- Render active tab content via dynamic component -->
         <keep-alive>
            <component 
               :is="activeTabComponent" 
               :department="department"
               :teams="teams"
               :members="members"
               :availableRoles="availableRoles"
               @refresh="refreshData"
            />
         </keep-alive>
      </div>

    </div>
  </component>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import TeamsTab from './Tabs/TeamsTab.vue';
import MembersTab from './Tabs/MembersTab.vue';
import FeaturesTab from './Tabs/FeaturesTab.vue';

const props = defineProps({
  department: Object,
  teams: Array,
  members: Array,
  availableRoles: Array,
});

// Layout state handling
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);
const handleResize = () => { windowWidth.value = window.innerWidth; };
onMounted(() => { window.addEventListener('resize', handleResize); });
onUnmounted(() => { window.removeEventListener('resize', handleResize); });

const currentLayout = computed(() => {
   return windowWidth.value < 768 ? MobileLayout : AuthenticatedLayout;
});

const blockLabels = {
  ministry: 'Mục vụ',
  leadership: 'Lãnh đạo',
  fellowship: 'Đội nhóm'
};

const activeTab = ref('teams');

const tabs = computed(() => [
  { id: 'teams', name: 'Quản lý Tổ', count: props.teams?.length || 0 },
  { id: 'members', name: 'Ban viên', count: props.members?.length || 0 },
  { id: 'features', name: 'Chức năng' },
]);

const activeTabComponent = computed(() => {
   switch (activeTab.value) {
      case 'teams': return TeamsTab;
      case 'members': return MembersTab;
      case 'features': return FeaturesTab;
      default: return TeamsTab;
   }
});

const refreshData = () => {
   router.reload({ only: ['teams', 'members', 'department'] });
};

</script>
