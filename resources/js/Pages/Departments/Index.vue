<template>
  <component :is="currentLayout">
    <template #header>
      Danh sách Ban ngành
    </template>

    <div class="py-4 space-y-12">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl font-black text-gray-900">Cơ cấu Tổ chức</h2>
          <p class="text-sm text-gray-500 mt-1">Hệ thống các Ban ngành và Khối sinh hoạt tại HTTL Thạnh Mỹ Lợi.</p>
        </div>
      </div>

      <!-- Toolbar (Search, Filters, View Switcher) -->
      <DataToolbar 
        v-model:search="search"
        v-model:viewMode="viewMode"
        storageKey="departments_view_mode"
        placeholder="Tìm kiếm theo tên hoặc mã ban ngành..."
      >
        <template #actions>
          <PrimaryButton>
            + Khai báo Ban mới
          </PrimaryButton>
        </template>
      </DataToolbar>

      <div v-for="(group, blockKey) in filteredGroups" :key="blockKey" class="space-y-6">
        <div class="flex items-center space-x-4">
          <div class="h-px flex-1 bg-gray-100"></div>
          <h3 class="text-sm font-black uppercase tracking-[0.2em] text-gray-400">
            {{ blockNames[blockKey] || blockKey }}
          </h3>
          <div class="h-px flex-1 bg-gray-100"></div>
        </div>

        <!-- BẢNG (LIST VIEW) -->
        <div v-show="viewMode === 'list' && windowWidth >= 768" class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden animate-in fade-in duration-300">
           <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                 <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên Ban / Mã</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Trực thuộc</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Quy mô</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                 </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                 <tr v-for="dept in group" :key="dept.id" class="hover:bg-gray-50 transition-colors group/row">
                    <td class="px-6 py-4 whitespace-nowrap">
                       <div class="flex items-center">
                          <div class="flex-shrink-0 h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                             {{ dept.name.charAt(0) }}
                          </div>
                          <div class="ml-4">
                             <div class="text-sm font-bold text-gray-900">{{ dept.name }}</div>
                             <div class="text-xs text-gray-500 font-mono">{{ dept.code }}</div>
                          </div>
                       </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                       <span v-if="dept.parent" class="bg-gray-100 px-2 py-1 rounded text-xs">{{ dept.parent.name }}</span>
                       <span v-else class="italic opacity-50">Độc lập</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                       <span class="font-bold text-gray-900">{{ dept.members_count }}</span> <span class="text-gray-500 text-xs">tín hữu</span> &bull; 
                       <span class="font-bold text-gray-900">{{ dept.teams_count }}</span> <span class="text-gray-500 text-xs">tổ</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                       <StatusBadge :status="dept.is_active ? 'success' : 'gray'">
                          {{ dept.is_active ? 'Hoạt động' : 'Tạm ngưng' }}
                       </StatusBadge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                       <Link :href="route('departments.show', dept.id)" class="text-blue-600 hover:text-blue-900 font-bold bg-blue-50 px-3 py-1 rounded-lg transition-colors">Chi tiết</Link>
                    </td>
                 </tr>
              </tbody>
           </table>
        </div>

        <!-- LƯỚI (GRID VIEW) & MOBILE -->
        <div v-show="viewMode === 'grid' || windowWidth < 768" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 animate-in fade-in duration-300">
          <AppCard v-for="dept in group" :key="dept.id" class="hover:shadow-md transition-shadow group">

            <!-- Thẻ Card thiết kế nhỏ gọn -->
            <div class="flex items-start justify-between mb-3 border-b border-gray-50 pb-3">
              <div class="flex items-center space-x-3">
                 <div class="p-2 rounded-xl transition-colors" :class="blockColors[blockKey] || 'bg-blue-50 text-blue-600'">
                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path v-if="blockKey === 'leadership'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                     <path v-else-if="blockKey === 'activities'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                     <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                   </svg>
                 </div>
                 <div>
                    <h3 class="text-sm font-bold text-gray-900 leading-tight">{{ dept.name }}</h3>
                    <p class="text-[11px] text-gray-400 font-mono">{{ dept.code }}</p>
                 </div>
              </div>
            </div>
            
            <div v-if="dept.parent" class="mb-3 flex items-center text-[10px] text-gray-500 bg-gray-50 px-2 py-1 rounded w-fit italic tracking-wide">
               <svg class="w-3 h-3 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 10h10a8 8 0 018 8v2M3 10l4 4m-4-4l4-4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
               Trực thuộc {{ dept.parent.name }}
            </div>
            
            <div class="flex items-center justify-between mt-auto pt-3">
              <div class="flex items-center space-x-3 text-xs">
                 <div class="flex items-center text-gray-500">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="font-bold">{{ dept.members_count }}</span>
                 </div>
                 <div class="flex items-center text-gray-500">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span class="font-bold">{{ dept.teams_count }}</span>
                 </div>
              </div>
              <Link :href="route('departments.show', dept.id)" class="text-[11px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-800 px-3 py-1.5 rounded-lg transition-colors group-hover:underline">
                Chi tiết &rarr;
              </Link>
            </div>
          </AppCard>
        </div>
      </div>

      <div v-if="departments.length === 0" class="py-20 text-center bg-white rounded-2xl border border-dashed border-gray-200">
         <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
         </svg>
         <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa có ban ngành nào</h3>
         <p class="mt-1 text-sm text-gray-500">Bắt đầu bằng cách khai báo ban ngành đầu tiên cho Hội Thánh.</p>
      </div>
    </div>
  </component>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import DataToolbar from '@/Components/DataToolbar.vue';

const props = defineProps({
  departments: Array
});

const search = ref('');
const viewMode = ref('grid'); // Sẽ tự đồng bộ từ DataToolbar

const blockNames = {
  leadership: 'I. Ban Lãnh đạo',
  activities: 'II. Khối Sinh hoạt',
  ministry: 'III. Khối Mục vụ'
};

const blockColors = {
  leadership: 'bg-purple-50 text-purple-600',
  activities: 'bg-orange-50 text-orange-600',
  ministry: 'bg-blue-50 text-blue-600'
};

const filteredGroups = computed(() => {
  const order = ['leadership', 'activities', 'ministry'];
  const groups = {};
  
  // Lọc theo search
  const keyword = search.value.toLowerCase().trim();
  const matchedDepts = props.departments.filter(dept => {
     if(!keyword) return true;
     return dept.name.toLowerCase().includes(keyword) || dept.code.toLowerCase().includes(keyword);
  });
  
  // Initialize groups in correct order
  order.forEach(key => groups[key] = []);
  
  matchedDepts.forEach(dept => {
    const key = dept.block || 'ministry';
    if (!groups[key]) groups[key] = [];
    groups[key].push(dept);
  });

  // Remove empty groups
  Object.keys(groups).forEach(key => {
    if (groups[key].length === 0) delete groups[key];
  });

  return groups;
});

// Nhận diện kích thước màn hình
const windowWidth = ref(window.innerWidth);
const updateWidth = () => windowWidth.value = window.innerWidth;
onMounted(() => window.addEventListener('resize', updateWidth));
onUnmounted(() => window.removeEventListener('resize', updateWidth));

const currentLayout = computed(() => {
  return windowWidth.value >= 768 ? AuthenticatedLayout : MobileLayout;
});
</script>
