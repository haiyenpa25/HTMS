<template>
  <component :is="currentLayout">
    <template #header>
      Tổ chức Buổi Nhóm
    </template>

    <div class="py-4 space-y-6">
      <!-- Toolbar -->
      <DataToolbar 
        v-model:search="filterForm.search"
        v-model:viewMode="viewMode"
        storageKey="meetings_view_mode"
        placeholder="Tìm theo chủ đề, người hướng dẫn..."
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
            + Lên lịch Buổi nhóm
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
            <!-- Phân loại -->
            <div class="space-y-1">
               <label class="text-xs font-bold text-gray-500">Loại buổi nhóm</label>
               <select v-model="filterForm.type" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50">
                  <option value="">Tất cả</option>
                  <option value="church">Hội Thánh</option>
                  <option value="department">Ban Ngành</option>
               </select>
            </div>
            
            <!-- Từ ngày -->
            <div class="space-y-1">
               <label class="text-xs font-bold text-gray-500">Từ ngày</label>
               <input type="date" v-model="filterForm.date_from" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50" />
            </div>

            <!-- Đến ngày -->
            <div class="space-y-1">
               <label class="text-xs font-bold text-gray-500">Đến ngày</label>
               <input type="date" v-model="filterForm.date_to" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50" />
            </div>
         </div>
      </div>

      <!-- Data List -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Table View (Desktop & Grid mixed) -->
        <div v-show="viewMode === 'list'" class="overflow-x-auto min-h-[500px]">
          <table class="min-w-full divide-y divide-gray-200 hidden md:table">
            <thead class="bg-gray-50/50">
              <tr>
                <th scope="col" class="px-6 py-4 text-left text-[11px] font-black text-gray-500 uppercase tracking-widest">Ngày / Giờ</th>
                <th scope="col" class="px-6 py-4 text-left text-[11px] font-black text-gray-500 uppercase tracking-widest">Loại / Chủ đề</th>
                <th scope="col" class="px-6 py-4 text-left text-[11px] font-black text-gray-500 uppercase tracking-widest">Diễn giả</th>
                <th scope="col" class="px-6 py-4 text-left text-[11px] font-black text-gray-500 uppercase tracking-widest">Ban ngành</th>
                <th scope="col" class="px-6 py-4 text-right text-[11px] font-black text-gray-500 uppercase tracking-widest">Hành động</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr v-for="meeting in meetings" :key="meeting.id" class="hover:bg-blue-50/50 transition-colors group cursor-pointer" @click="goToMeeting(meeting.id)">
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900 border border-gray-200 px-3 py-1 rounded w-max bg-gray-50">{{ formatDate(meeting.date) }}</div>
                  <div class="text-xs text-gray-500 font-medium mt-1">{{ meeting.time }}</div>
                </td>
                <td class="px-6 py-5">
                  <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider" :class="meeting.type === 'church' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'">
                      {{ meeting.type === 'church' ? 'Hội Thánh' : 'Ban Ngành' }}
                    </span>
                    <span class="text-sm font-black text-gray-900 line-clamp-1 truncate block max-w-xs">{{ meeting.topic || '(Chưa có chủ đề)' }}</span>
                  </div>
                  <div class="text-xs text-gray-500 mt-1 line-clamp-1 max-w-sm truncate" v-if="meeting.scripture">
                    KT: {{ meeting.scripture }}
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span class="text-sm text-gray-700 font-medium">{{ meeting.preacher || '-' }}</span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span v-if="meeting.department" class="text-xs bg-gray-100 text-gray-700 font-bold px-2 py-1 rounded">{{ meeting.department.name }}</span>
                  <span v-else class="text-xs text-gray-400 font-medium">-</span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-right">
                   <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button @click.stop="openEditSlideOver(meeting)" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Chỉnh sửa">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                      </button>
                   </div>
                </td>
              </tr>
              <tr v-if="meetings.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                  <div class="flex flex-col items-center justify-center space-y-3">
                     <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                     <p class="text-sm font-medium">Không tìm thấy buổi nhóm nào.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Mobile Cards View for List Mode -->
          <div class="md:hidden divide-y divide-gray-100">
             <div v-for="meeting in meetings" :key="'mob-'+meeting.id" class="p-5 flex items-start space-x-4 relative group hover:bg-gray-50 transition-colors cursor-pointer" @click="goToMeeting(meeting.id)">
               <div class="flex-1 min-w-0 flex flex-col justify-between py-1">
                 <div>
                    <div class="flex items-center space-x-2 mb-1">
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider" :class="meeting.type === 'church' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'">
                        {{ meeting.type === 'church' ? 'HT' : 'BN' }}
                      </span>
                      <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ formatDate(meeting.date) }} • {{ meeting.time }}</p>
                    </div>
                    <h4 class="text-base font-black text-gray-900 truncate leading-tight mt-1">{{ meeting.topic || '(Chưa có chủ đề)' }}</h4>
                    <p class="text-xs text-gray-500 line-clamp-1 mt-1 font-medium" v-if="meeting.scripture">KT: {{ meeting.scripture }}</p>
                 </div>
                 <div class="mt-3 flex items-center gap-2 flex-wrap">
                    <span v-if="meeting.preacher" class="inline-flex items-center text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded-md">
                      Mục sư: {{ meeting.preacher }}
                    </span>
                 </div>
               </div>
               
               <!-- Quick Actions menu trigger (mobile) -->
               <button @click.stop="openEditSlideOver(meeting)" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-colors relative z-10">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
               </button>
             </div>
             <div v-if="meetings.length === 0" class="p-10 text-center">
                <p class="text-sm font-medium text-gray-500">Chưa có dữ liệu.</p>
             </div>
          </div>
        </div>

        <!-- Grid View -->
        <div v-show="viewMode === 'grid'" class="p-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="meeting in meetings" :key="'grid-'+meeting.id" class="bg-white border border-gray-200 rounded-2xl p-5 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative group cursor-pointer" @click="goToMeeting(meeting.id)">
               <div class="flex justify-between items-start mb-4">
                  <div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider mb-2" :class="meeting.type === 'church' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'">
                      {{ meeting.type === 'church' ? 'Hội Thánh' : 'Ban Ngành' }}
                    </span>
                    <h3 class="text-lg font-black text-gray-900 leading-tight line-clamp-2" :title="meeting.topic">{{ meeting.topic || '(Chưa có chủ đề)' }}</h3>
                  </div>
                  <button @click.stop="openEditSlideOver(meeting)" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg opacity-0 group-hover:opacity-100 transition-all z-10">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                  </button>
               </div>
               
               <div class="space-y-2 mt-4 text-sm font-medium">
                  <div class="flex items-center text-gray-600">
                     <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                     {{ formatDate(meeting.date) }} • {{ meeting.time }}
                  </div>
                  <div class="flex items-center text-gray-600 line-clamp-1" v-if="meeting.scripture">
                     <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                     {{ meeting.scripture }}
                  </div>
                  <div class="flex items-center text-gray-600" v-if="meeting.department">
                     <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                     {{ meeting.department.name }}
                  </div>
               </div>
            </div>
          </div>
          <div v-if="meetings.length === 0" class="text-center py-12">
             <p class="text-gray-500 font-medium">Không tìm thấy dữ liệu góc nhìn Lưới.</p>
          </div>
        </div>

      </div>
    </div>

    <!-- Khung tạo/sửa trượt từ phải (SlideOver) -->
    <SlideOver 
      v-model="isSlideOverOpen" 
      :title="selectedMeeting ? 'Chỉnh sửa Buổi nhóm' : 'Lên lịch Buổi nhóm'" 
      :description="selectedMeeting ? 'Cập nhật lại thông tin buổi nhóm' : 'Tạo buổi nhóm mới cho Hội Thánh hoặc Ban Ngành'"
    >
      <MeetingForm 
        v-if="isSlideOverOpen" 
        :meeting="selectedMeeting" 
        @close="closeSlideOver" 
        @success="handleSuccess"
      />
    </SlideOver>

  </component>
</template>

<script setup>
import { ref, computed, watch, markRaw } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import DataToolbar from '@/Components/DataToolbar.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SlideOver from '@/Components/SlideOver.vue';
import MeetingForm from './Partials/MeetingForm.vue';

// Desktop/Mobile layout switching
const isMobile = ref(window.innerWidth < 768);
const currentLayout = computed(() => isMobile.value ? markRaw(MobileLayout) : markRaw(AuthenticatedLayout));

window.addEventListener('resize', debounce(() => {
  isMobile.value = window.innerWidth < 768;
}, 250));

const props = defineProps({
  meetings: Array,
  filters: Object,
});

const viewMode = ref('list');
const showFilters = ref(false);

const filterForm = ref({
  search: props.filters?.search || '',
  type: props.filters?.type || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
});

const activeFilterCount = computed(() => {
  let count = 0;
  if (filterForm.value.type) count++;
  if (filterForm.value.date_from) count++;
  if (filterForm.value.date_to) count++;
  return count;
});

const resetFilters = () => {
  filterForm.value.type = '';
  filterForm.value.date_from = '';
  filterForm.value.date_to = '';
};

watch(filterForm, debounce((newVal) => {
  router.get(route('meetings.index'), newVal, { preserveState: true, replace: true });
}, 300), { deep: true });

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

// SlideOver State
const isSlideOverOpen = ref(false);
const selectedMeeting = ref(null);

const openCreateSlideOver = () => {
  selectedMeeting.value = null;
  isSlideOverOpen.value = true;
};

const openEditSlideOver = (meeting) => {
  selectedMeeting.value = meeting;
  isSlideOverOpen.value = true;
};

const closeSlideOver = () => {
  isSlideOverOpen.value = false;
  setTimeout(() => {
    selectedMeeting.value = null;
  }, 300);
};

const handleSuccess = () => {
  router.reload({ only: ['meetings'] });
};

const goToMeeting = (id) => {
  router.get(route('meetings.show', id));
};
</script>
