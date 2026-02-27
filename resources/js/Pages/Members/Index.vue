<template>
  <component :is="currentLayout">
    <template #header>
      Quản lý Nhân sự
    </template>

    <div class="py-4 space-y-6">
      <!-- Toolbar (Search, Filters, View Switcher) -->
      <DataToolbar 
        v-model:search="search"
        v-model:viewMode="viewMode"
        storageKey="members_view_mode"
        placeholder="Tìm tên, SĐT, email hoặc mã tín hữu..."
      >
        <template #filters>
           <button type="button" @click="showFilters = !showFilters" class="ml-2 flex flex-col md:flex-row md:items-center justify-center space-x-2 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-100 transition-colors">
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
              <span>Lọc nâng cao</span>
              <span v-if="activeFilterCount > 0" class="ml-1 bg-blue-100 text-blue-700 py-0.5 px-2 rounded-full text-[10px]">{{ activeFilterCount }}</span>
           </button>
        </template>
        <template #actions>
          <PrimaryButton>
            + Thêm Tín hữu
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
            <!-- Tình trạng -->
            <div class="space-y-1">
               <label class="text-xs font-bold text-gray-500">Phân loại</label>
               <select v-model="filterForm.status" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50">
                  <option value="">Tất cả</option>
                  <option value="Chính thức">Tín hữu Chính thức</option>
                  <option value="Thân hữu">Thân hữu</option>
                  <option value="Chưa rõ">Khác</option>
               </select>
            </div>
            
            <!-- Hôn nhân -->
            <div class="space-y-1">
               <label class="text-xs font-bold text-gray-500">Hôn nhân</label>
               <select v-model="filterForm.marital_status" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50">
                  <option value="">Tất cả</option>
                  <option value="Độc thân">Độc thân</option>
                  <option value="Đã kết hôn">Đã kết hôn</option>
                  <option value="Góa">Góa</option>
                  <option value="Khác">Khác</option>
               </select>
            </div>
            
            <!-- Báp-têm -->
            <div class="space-y-1">
               <label class="text-xs font-bold text-gray-500">Báp-têm</label>
               <select v-model="filterForm.is_baptized" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50">
                  <option value="">Tất cả</option>
                  <option value="true">Đã nhận Báp-têm</option>
                  <option value="false">Chưa nhận</option>
               </select>
            </div>
            
            <!-- Thâm niên sinh hoạt -->
            <div class="space-y-1">
               <label class="text-xs font-bold text-gray-500">Thâm niên sinh hoạt</label>
               <select v-model="filterForm.join_time" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50">
                  <option value="">Tất cả thời gian</option>
                  <option value="3_months">Mới gia nhập (3 tháng đổ lại)</option>
                  <option value="6_months">3 đến 6 tháng</option>
                  <option value="1_year">6 tháng đến 1 năm</option>
                  <option value="2_years_plus">Lâu năm (trên 2 năm)</option>
               </select>
            </div>
         </div>
      </div>

      <!-- Desktop Table -->
      <!-- Desktop Table View -->
      <div v-show="viewMode === 'list' && windowWidth >= 768" class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden animate-in fade-in duration-300">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hồ sơ Cầm tay</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Liên hệ</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Gia thế & Tâm linh</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Trực thuộc</th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="member in members.data" :key="member.id" class="hover:bg-blue-50/50 transition-colors group/row">
              <!-- Cột 1: Profiler -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-700 rounded-full flex items-center justify-center font-black text-lg shadow-inner ring-2 ring-white">
                    {{ member.full_name ? member.full_name.charAt(0) : '?' }}
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-black text-gray-900 leading-tight">
                       {{ member.full_name }}
                       <span v-if="member.gender === 'Nữ'" class="ml-1 text-pink-400" title="Nữ">♀</span>
                       <span v-if="member.gender === 'Nam'" class="ml-1 text-blue-400" title="Nam">♂</span>
                    </div>
                    <div class="text-[11px] text-gray-500 font-mono mt-0.5">{{ member.member_code }}</div>
                    <div class="mt-1 flex items-center space-x-2">
                       <span v-if="member.status === 'Chính thức'" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800">Chính thức</span>
                       <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-800">{{ member.status || 'Thân hữu' }}</span>
                    </div>
                  </div>
                </div>
              </td>
              
              <!-- Cột 2: Liên hệ -->
              <td class="px-6 py-4 whitespace-nowrap">
                 <div class="flex flex-col space-y-1">
                    <div class="flex items-center text-sm text-gray-700 font-medium">
                       <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                       {{ member.phone || 'Chưa cập nhật' }}
                    </div>
                    <div class="flex items-center text-xs text-gray-500">
                       <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                       {{ member.email || '—' }}
                    </div>
                 </div>
              </td>
              
              <!-- Cột 3: Tình trạng -->
              <td class="px-6 py-4 whitespace-nowrap">
                 <div class="flex flex-col space-y-1">
                    <div class="flex items-center text-xs text-gray-700">
                       <span class="w-16 font-bold text-gray-500">Báp-têm:</span>
                       <span v-if="member.is_baptized" class="text-blue-600 font-bold flex items-center">
                          <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Đã nhận
                       </span>
                       <span v-else class="text-gray-400">Chưa</span>
                    </div>
                    <div class="flex items-center text-xs text-gray-700">
                       <span class="w-16 font-bold text-gray-500">Hôn nhân:</span>
                       <span>{{ member.marital_status || '—' }}</span>
                    </div>
                 </div>
              </td>

              <!-- Cột 4: Trực thuộc -->
              <td class="px-6 py-4">
                 <div class="flex flex-wrap gap-1 max-w-[200px]">
                    <span v-for="dept in member.departments" :key="dept.id" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-200">
                       {{ dept.name }}
                    </span>
                    <span v-if="!member.departments || member.departments.length === 0" class="text-xs text-gray-400 italic">Chưa phân ban</span>
                 </div>
              </td>

              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <Link :href="route('members.show', member.id)" class="text-gray-600 bg-gray-100 hover:bg-gray-200 hover:text-black px-4 py-2 rounded-lg font-bold shadow-sm transition-colors cursor-pointer inline-flex items-center">
                  Hồ sơ
                </Link>
              </td>
            </tr>
            <tr v-if="members.data.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                 Không tìm thấy tín hữu nào phù hợp.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile List -->
      <!-- Grid View & Mobile List -->
      <div v-show="viewMode === 'grid' || windowWidth < 768" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 animate-in fade-in duration-300">
        <div v-for="member in members.data" :key="member.id" class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm hover:shadow-md hover:border-blue-100 transition-all flex flex-col relative group">
          
          <!-- Header (Avatar + Name) -->
          <div class="flex items-start space-x-3 mb-4">
             <div class="shrink-0 h-10 w-10 bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-700 rounded-xl flex items-center justify-center font-black shadow-sm ring-1 ring-black/5">
                {{ member.full_name ? member.full_name.charAt(0) : '?' }}
             </div>
             <div class="flex-1 min-w-0">
                <h3 class="text-sm font-black text-gray-900 truncate">
                   {{ member.full_name }}
                   <span v-if="member.gender === 'Nữ'" class="text-pink-400 font-normal">♀</span>
                   <span v-if="member.gender === 'Nam'" class="text-blue-400 font-normal">♂</span>
                </h3>
                <p class="text-[11px] text-gray-400 font-mono">{{ member.member_code }}</p>
             </div>
             
             <!-- Status Dot -->
             <div class="shrink-0 w-2.5 h-2.5 rounded-full mt-1" :class="member.status === 'Chính thức' ? 'bg-green-500' : 'bg-yellow-400'" :title="member.status"></div>
          </div>

          <!-- Dữ liệu rút gọn -->
          <div class="space-y-2 mb-3 mt-auto">
             <div class="flex items-center text-xs text-gray-600">
                <svg class="w-3.5 h-3.5 mr-2 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                <span class="truncate">{{ member.phone || 'Chưa cập nhật' }}</span>
             </div>
             
             <!-- Badges info -->
             <div class="flex flex-wrap gap-1.5 pt-1">
                <span v-if="member.is_baptized" class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                   Báp-têm
                </span>
                <span v-if="member.marital_status" class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-100">
                   {{ member.marital_status }}
                </span>
             </div>
          </div>
          
          <div class="pt-3 border-t border-gray-50 flex items-center justify-between">
             <span class="text-[10px] text-gray-400 font-medium italic truncate max-w-[120px]">
                {{ member.departments?.[0]?.name || 'Không có ban' }}
             </span>
             <Link :href="route('members.show', member.id)" class="text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 hover:text-black px-3 py-1.5 rounded-lg transition-colors inline-block">
                Hồ sơ &rarr;
             </Link>
          </div>
        </div>
        
        <div v-if="members.data.length === 0" class="col-span-full py-12 text-center text-gray-500 bg-white rounded-xl border border-dashed border-gray-300">
           Không tìm thấy tín hữu nào phù hợp.
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="members.links.length > 3" class="flex justify-center mt-6">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <template v-for="(link, k) in members.links" :key="k">
             <Link
               v-if="link.url"
               :href="link.url"
               v-html="link.label"
               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
               :class="{ 'bg-blue-50 text-blue-600 border-blue-500 z-10': link.active }"
             />
             <span
               v-else
               v-html="link.label"
               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-400"
             />
          </template>
        </nav>
      </div>
    </div>
  </component>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import DataToolbar from '@/Components/DataToolbar.vue';

const props = defineProps({
  members: Object,
  filters: Object,
});

const search = ref(props.filters.search || '');
const viewMode = ref('list'); // Sẽ tự đồng bộ từ DataToolbar
const showFilters = ref(false);

const filterForm = ref({
   status: props.filters.status || '',
   marital_status: props.filters.marital_status || '',
   is_baptized: props.filters.is_baptized || '',
   join_time: props.filters.join_time || ''
});

const activeFilterCount = computed(() => {
   return Object.values(filterForm.value).filter(val => val !== '').length;
});

const resetFilters = () => {
   filterForm.value.status = '';
   filterForm.value.marital_status = '';
   filterForm.value.is_baptized = '';
   filterForm.value.join_time = '';
};

// Debounce search and filters
watch([search, filterForm], ([newSearch, newForm]) => {
  router.get(
    route('members.index'),
    { search: newSearch, ...newForm },
    { preserveState: true, preserveScroll: true, replace: true }
  );
}, { deep: true, debounce: 300 });

// Nhận diện kích thước màn hình
const windowWidth = ref(window.innerWidth);
const updateWidth = () => windowWidth.value = window.innerWidth;
onMounted(() => window.addEventListener('resize', updateWidth));
onUnmounted(() => window.removeEventListener('resize', updateWidth));

const currentLayout = computed(() => {
  return windowWidth.value >= 768 ? AuthenticatedLayout : MobileLayout;
});
</script>
