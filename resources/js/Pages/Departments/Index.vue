<template>
  <component :is="currentLayout">
    <template #header>
      Danh sách Ban Ngành
    </template>

    <div class="py-4 space-y-6">
      
      <!-- Toolbar (Search, Filters, View Switcher) -->
      <DataToolbar 
        v-model:search="filterForm.search"
        v-model:viewMode="viewMode"
        storageKey="departments_view_mode"
        placeholder="Tìm theo tên ban hoặc mã ngắn..."
      >
        <template #filters>
           <button type="button" @click="showFilters = !showFilters" class="ml-2 flex flex-col md:flex-row md:items-center justify-center space-x-2 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-100 transition-colors">
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
              <span>Lọc nâng cao</span>
              <span v-if="activeFilterCount > 0" class="ml-1 bg-blue-100 text-blue-700 py-0.5 px-2 rounded-full text-[10px]">{{ activeFilterCount }}</span>
           </button>
        </template>
        <template #actions>
          <PrimaryButton @click="openCreateSlideOver">
            + Tạo Ban mới
          </PrimaryButton>
        </template>
      </DataToolbar>

      <!-- Panel Bộ Lọc -->
      <div v-show="showFilters" class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm animate-in slide-in-from-top-4 duration-200">
         <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Tiêu chí Lọc</h3>
            <button @click="resetFilters" class="text-xs font-bold text-red-600 hover:text-red-800 hover:underline">Xóa tất cả lọc</button>
         </div>
         <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Phân loại Khối -->
            <div class="space-y-1">
               <label class="text-xs font-bold text-gray-500">Khối hoạt động</label>
               <select v-model="filterForm.block" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50">
                  <option value="">Tất cả khối</option>
                  <option value="leadership">Lãnh đạo</option>
                  <option value="ministry">Mục vụ</option>
                  <option value="activities">Sinh hoạt</option>
               </select>
            </div>
            
            <!-- Trạng thái -->
            <div class="space-y-1">
               <label class="text-xs font-bold text-gray-500">Trạng thái</label>
               <select v-model="filterForm.status" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50">
                  <option value="">Tất cả</option>
                  <option value="active">Đang hoạt động</option>
                  <option value="inactive">Tạm ngưng</option>
               </select>
            </div>
         </div>
      </div>

      <!-- Data List -->
      <!-- Only list view implemented for departments currently -->
      <div v-show="viewMode === 'list' || windowWidth < 768" class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden animate-in fade-in duration-300">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
              <tr>
                <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên Ban / Đội</th>
                <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Phân Loại</th>
                <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Số Tổ</th>
                <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Ban Viên</th>
                <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng Thái</th>
                <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr v-for="dept in departments" :key="dept.id" class="hover:bg-blue-50/30 transition-colors group/row">
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-indigo-100 to-purple-100 text-indigo-700 rounded-full flex items-center justify-center font-black text-lg shadow-inner ring-2 ring-white">
                      {{ dept.name.charAt(0) }}
                    </div>
                    <div class="ml-4">
                      <Link :href="route('departments.show', dept.id)" class="text-sm font-black text-gray-900 hover:text-blue-600 transition-colors leading-tight">
                        {{ dept.name }}
                      </Link>
                      <div class="text-[10px] text-gray-500 mt-0.5 font-bold sm:hidden">{{ blockLabels[dept.block] || dept.block }}</div>
                      <div class="text-[11px] text-gray-500 font-mono mt-0.5 hidden sm:block">{{ dept.code || 'N/A' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-gray-100 text-gray-800 border border-gray-200/50">
                    {{ blockLabels[dept.block] || dept.block }}
                  </span>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center hidden md:table-cell">
                  <div class="text-sm font-bold text-gray-900">{{ dept.teams_count }}</div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center">
                  <div class="inline-flex items-center justify-center bg-blue-50 text-blue-700 font-black text-xs min-w-[2rem] h-8 rounded-full border border-blue-100 px-2">
                    {{ dept.members_count }}
                  </div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center">
                  <span v-if="dept.is_active" class="inline-flex items-center px-2 py-1 rounded-full text-[10px] sm:px-2.5 font-bold bg-green-100 text-green-800">
                    Hoạt động
                  </span>
                  <span v-else class="inline-flex items-center px-2 py-1 rounded-full text-[10px] sm:px-2.5 font-bold bg-red-100 text-red-800">
                    Ngưng
                  </span>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                   <div class="flex items-center justify-end space-x-1 sm:space-x-2 sm:opacity-0 group-hover/row:opacity-100 transition-opacity">
                      <button @click="openEditSlideOver(dept)" class="text-blue-600 hover:text-blue-900 font-bold p-1.5 hover:bg-blue-50 rounded-lg transition-colors tooltip" title="Chỉnh sửa Ban">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                      </button>
                      <button v-if="dept.block === 'activities'" @click="goToPortal(dept.id)" class="text-blue-500 hover:text-blue-700 font-bold p-1.5 hover:bg-blue-50 rounded-lg transition-colors tooltip" title="Vào Cổng Sinh Hoạt">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                      </button>
                      <Link :href="route('departments.show', dept.id)" class="text-gray-400 hover:text-gray-900 font-bold p-1.5 hover:bg-gray-100 rounded-lg transition-colors tooltip" title="Quản lý Tổ & Ban viên">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                      </Link>
                   </div>
                </td>
              </tr>
              <tr v-if="departments.length === 0">
                 <td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">
                    <div class="flex flex-col items-center justify-center">
                       <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                       <p class="font-medium">Không tìm thấy ban ngành nào khớp với bộ lọc.</p>
                       <button @click="resetFilters" class="mt-2 text-blue-600 hover:underline font-bold">Xóa bộ lọc</button>
                    </div>
                 </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Grid View & Mobile List -->
      <div v-show="viewMode === 'grid' || windowWidth < 768" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 animate-in fade-in duration-300">
         <div v-for="dept in departments" :key="'grid-'+dept.id" class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm hover:shadow-md hover:border-blue-100 transition-all flex flex-col relative group">
            
            <!-- Header (Avatar + Name) -->
            <div class="flex items-start space-x-3 mb-4">
               <div class="shrink-0 h-10 w-10 bg-gradient-to-br from-indigo-50 to-purple-50 text-indigo-700 rounded-xl flex items-center justify-center font-black shadow-sm ring-1 ring-black/5">
                  {{ dept.name.charAt(0) }}
               </div>
               <div class="flex-1 min-w-0">
                  <h3 class="text-sm font-black text-gray-900 line-clamp-2">
                     {{ dept.name }}
                  </h3>
                  <p class="text-[11px] text-gray-400 font-mono mt-0.5">{{ dept.code || 'N/A' }}</p>
               </div>
               
               <!-- Status Dot -->
               <div class="shrink-0 w-2.5 h-2.5 rounded-full mt-1" :class="dept.is_active ? 'bg-green-500' : 'bg-red-500'" :title="dept.is_active ? 'Hoạt động' : 'Tạm ngưng'"></div>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-2 gap-3 mb-4 mt-auto">
               <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100/50 flex flex-col items-center justify-center">
                  <div class="text-[10px] uppercase font-bold text-gray-400 mb-0.5 tracking-wider">Số Tổ</div>
                  <div class="text-base font-black text-gray-900">{{ dept.teams_count }}</div>
               </div>
               <div class="bg-blue-50/50 p-2.5 rounded-xl border border-blue-100/50 flex flex-col items-center justify-center">
                  <div class="text-[10px] uppercase font-bold text-blue-400 mb-0.5 tracking-wider">Ban Viên</div>
                  <div class="text-base font-black text-blue-700">{{ dept.members_count }}</div>
               </div>
            </div>
            
            <!-- Footer -->
            <div class="pt-3 border-t border-gray-50 flex items-center justify-between">
               <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 truncate max-w-[120px]">
                 {{ blockLabels[dept.block] || dept.block }}
               </span>
               <div class="flex space-x-2 relative z-20">
                  <button @click.prevent="openEditSlideOver(dept)" class="text-gray-400 hover:text-blue-600 transition-colors p-1.5 rounded-lg hover:bg-blue-50" title="Sửa">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                  </button>
                  <button v-if="dept.block === 'activities'" @click.prevent="goToPortal(dept.id)" class="text-xs font-bold text-blue-700 bg-blue-100 hover:bg-blue-200 px-3 py-1.5 rounded-lg transition-colors inline-block text-center relative z-20">
                     Vào Cổng
                  </button>
                  <Link :href="route('departments.show', dept.id)" class="text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 hover:text-black px-3 py-1.5 rounded-lg transition-colors inline-block text-center relative z-20">
                     Quản lý
                  </Link>
               </div>
            </div>
            <Link :href="route('departments.show', dept.id)" class="absolute inset-0 z-0"></Link>
            <div class="relative z-10 pointer-events-none absolute inset-0"></div>
         </div>
         <div v-if="departments.length === 0" class="col-span-full py-12 text-center text-gray-500 bg-white rounded-xl border border-dashed border-gray-300">
            Không có dữ liệu.
         </div>
      </div>

    </div>

    <!-- Unified SlideOver for Create/Edit -->
    <SlideOver 
       v-model="isSlideOverOpen"
       title="Quản lý Ban Ngành"
       description="Thực hiện thiết lập các thông tin ban ngành"
    >
       <DepartmentForm 
          :department="selectedDepartment"
          @close="closeSlideOver"
          @success="handleSuccess"
       />
    </SlideOver>

  </component>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import DataToolbar from '@/Components/DataToolbar.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SlideOver from '@/Components/SlideOver.vue';
import DepartmentForm from './Partials/DepartmentForm.vue';

const props = defineProps({
  departments: Array,
  filters: Object,
});

// Layout Handling
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024);
const handleResize = () => { windowWidth.value = window.innerWidth; };
onMounted(() => { window.addEventListener('resize', handleResize); });
onUnmounted(() => { window.removeEventListener('resize', handleResize); });

const currentLayout = computed(() => {
   return windowWidth.value < 768 ? MobileLayout : AuthenticatedLayout;
});

// Views and Filters
const viewMode = ref(localStorage.getItem('departments_view_mode') || 'list');
const showFilters = ref(false);

const blockLabels = {
  ministry: 'Mục vụ',
  leadership: 'Lãnh đạo',
  activities: 'Sinh hoạt'
};

const filterForm = ref({
   search: props.filters.search || '',
   block: props.filters.block || '',
   status: props.filters.status || '',
});

const goToPortal = (deptId) => {
    router.post(route('portal.switch-context'), { department_id: deptId });
};

const activeFilterCount = computed(() => {
   let count = 0;
   if (filterForm.value.block) count++;
   if (filterForm.value.status) count++;
   return count;
});

const resetFilters = () => {
   filterForm.value.block = '';
   filterForm.value.status = '';
   filterForm.value.search = '';
};

// Watch over filters and debounce router push
watch(filterForm, debounce((newVal) => {
   router.get(route('departments.index'), newVal, { preserveState: true, replace: true });
}, 300), { deep: true });

// Slide-Over State
const isSlideOverOpen = ref(false);
const selectedDepartment = ref(null);

const openCreateSlideOver = () => {
   selectedDepartment.value = null;
   isSlideOverOpen.value = true;
};

const openEditSlideOver = (dept) => {
   selectedDepartment.value = dept;
   isSlideOverOpen.value = true;
};

const closeSlideOver = () => {
   isSlideOverOpen.value = false;
   setTimeout(() => {
      selectedDepartment.value = null;
   }, 300); // Wait for animation
};

const handleSuccess = () => {};

</script>
