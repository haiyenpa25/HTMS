<template>
  <component :is="currentLayout">
    <template #header>
      <h2 class="font-black text-xl text-gray-900 leading-tight tracking-tight">
         Quản lý Diễn giả
      </h2>
    </template>

    <div class="py-4 space-y-6 w-full">

      <!-- Hero Banner -->
      <div class="rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-700 p-6 sm:p-8 text-white relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 opacity-10 pointer-events-none flex items-center justify-end pr-8">
          <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
        </div>
        <div class="relative z-10">
          <p class="text-xs font-bold uppercase tracking-[0.2em] text-violet-200 mb-1">NỘI DUNG × GIẢNG LUẬN</p>
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Quản lý Diễn Giả</h1>
          <p class="mt-2 text-sm text-violet-200">Danh sách các mục sư, giảng sư và khách mời tham gia giảng luận cho Hội Thánh.</p>
        </div>
        <div class="absolute top-5 right-5 sm:top-6 sm:right-6 z-10">
          <button @click="openCreateSlideOver" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-sm font-bold rounded-xl transition-all backdrop-blur-sm border border-white/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Thêm Diễn giả
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
          <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Tổng diễn giả</p>
          <p class="text-2xl font-black text-gray-900">{{ speakers.total }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm">
          <p class="text-xs font-bold text-emerald-400 uppercase tracking-widest mb-1">Người trong HT</p>
          <p class="text-2xl font-black text-emerald-700">{{ speakers.data.filter(s => !s.is_external).length }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-violet-100 shadow-sm">
          <p class="text-xs font-bold text-violet-400 uppercase tracking-widest mb-1">Khách mời</p>
          <p class="text-2xl font-black text-violet-700">{{ speakers.data.filter(s => s.is_external).length }}</p>
        </div>
      </div>

       <DataToolbar 
          v-model:search="filterForm.search"
          :show-filters="showFilters"
          search-placeholder="Tìm tên, SĐT diễn giả..."
          @toggle-filters="showFilters = !showFilters"
          @search="applyFilters"
       >
          <template #actions>
             <PrimaryButton @click="openCreateSlideOver" class="hidden sm:flex">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Thêm Diễn giả
             </PrimaryButton>
             
             <!-- Mobile FAB -->
             <button @click="openCreateSlideOver" class="sm:hidden fixed bottom-20 right-4 w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-blue-700 active:scale-95 transition-all z-40">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
             </button>
          </template>

          <template #filters>
             <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                   <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Phân loại</label>
                   <select 
                      v-model="filterForm.type" 
                      @change="applyFilters"
                      class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   >
                      <option value="">Tất cả</option>
                      <option value="internal">Người trong HT</option>
                      <option value="external">Khách mời</option>
                   </select>
                </div>
             </div>
          </template>
       </DataToolbar>

       <!-- Data View -->
       <div v-if="windowWidth >= 768" class="bg-white rounded-2xl shadow-sm border border-gray-200/60 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm whitespace-nowrap">
             <thead class="bg-gray-50/50 border-b border-gray-100 text-gray-500 font-bold uppercase tracking-wider text-[11px]">
                <tr>
                   <th class="px-6 py-4">Tên Diễn Giả</th>
                   <th class="px-6 py-4">SĐT Liên Hệ</th>
                   <th class="px-6 py-4">Phân Loại</th>
                   <th class="px-6 py-4">Lượt giảng</th>
                   <th class="px-6 py-4 text-right">Thao tác</th>
                </tr>
             </thead>
             <tbody class="divide-y divide-gray-100">
                <tr v-for="speaker in speakers.data" :key="speaker.id" class="hover:bg-gray-50/50 transition-colors group">
                   <td class="px-6 py-4">
                      <div class="flex items-center">
                         <div class="h-10 w-10 shrink-0 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-lg">
                            {{ speaker.full_name.charAt(0) }}
                         </div>
                         <div class="ml-4">
                            <div class="font-black text-gray-900">{{ speaker.title ? speaker.title + ' ' : '' }}{{ speaker.full_name }}</div>
                            <div v-if="speaker.birth_year" class="text-xs text-gray-500">Sinh năm: {{ speaker.birth_year }}</div>
                         </div>
                      </div>
                   </td>
                   <td class="px-6 py-4">
                      <div class="text-gray-900 font-medium" v-if="speaker.phone">{{ speaker.phone }}</div>
                      <div class="text-gray-400 text-xs" v-else>Không có SĐT</div>
                   </td>
                   <td class="px-6 py-4">
                      <span v-if="speaker.is_external" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700">Khách mời</span>
                      <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">Trong HT</span>
                      <div v-if="speaker.managed_church" class="text-xs text-gray-500 mt-1 max-w-[150px] truncate" :title="speaker.managed_church">
                         {{ speaker.managed_church }}
                      </div>
                   </td>
                   <td class="px-6 py-4">
                      <div class="inline-flex items-center justify-center bg-gray-100 px-3 py-1 rounded-full text-xs font-bold text-gray-600">
                         {{ speaker.preaching_count }} lượt
                      </div>
                   </td>
                   <td class="px-6 py-4 text-right">
                      <button @click="openEditSlideOver(speaker)" class="text-gray-400 hover:text-blue-600 p-2 rounded-lg hover:bg-blue-50 transition-colors">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                      </button>
                   </td>
                </tr>
                <tr v-if="speakers.data.length === 0">
                   <td colspan="5" class="px-6 py-12 text-center text-gray-500 bg-gray-50">Không tìm thấy diễn giả nào.</td>
                </tr>
             </tbody>
          </table>
        </div>
       </div>

       <!-- Mobile View (Cards) -->
       <div v-else class="grid grid-cols-1 gap-4">
          <div 
             v-for="speaker in speakers.data" 
             :key="speaker.id" 
             class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden"
          >
             <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3">
                   <div class="h-10 w-10 shrink-0 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-lg">
                      {{ speaker.full_name.charAt(0) }}
                   </div>
                   <div>
                      <h3 class="font-black text-gray-900 leading-tight text-base">{{ speaker.title ? speaker.title + ' ' : '' }}{{ speaker.full_name }}</h3>
                      <div class="flex items-center mt-1 space-x-2">
                         <span v-if="speaker.is_external" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700">Khách mời</span>
                         <span v-else class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">Nội bộ</span>
                         <span class="text-xs text-gray-500">{{ speaker.preaching_count }} lượt</span>
                      </div>
                   </div>
                </div>
                <button @click="openEditSlideOver(speaker)" class="text-gray-400 hover:text-blue-600 bg-gray-50 p-2 rounded-full relative z-20">
                   <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                </button>
             </div>

             <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between relative z-20">
                <div v-if="speaker.managed_church" class="text-xs text-gray-500 truncate max-w-[150px]">
                   {{ speaker.managed_church }}
                </div>
                <div v-else></div>

                <a v-if="speaker.phone && !speaker.phone.includes('*')" :href="'tel:' + speaker.phone" class="inline-flex items-center justify-center px-3 py-1.5 bg-green-50 text-green-700 rounded-lg text-xs font-bold hover:bg-green-100 transition-colors">
                   <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                   Gọi điện
                </a>
             </div>
          </div>
          <div v-if="speakers.data.length === 0" class="py-12 text-center text-gray-500 bg-white rounded-xl border border-dashed border-gray-300">
             Không có diễn giả nào.
          </div>
       </div>

       <!-- Pagination -->
       <div v-if="speakers.links && speakers.data.length > 0" class="flex justify-center mt-6">
          <div class="flex space-x-1 bg-white rounded-xl p-1 shadow-sm border border-gray-200">
             <template v-for="(link, i) in speakers.links" :key="i">
                <Link 
                   v-if="link.url"
                   :href="link.url"
                   class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors"
                   :class="link.active ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                   v-html="link.label"
                />
                <span 
                   v-else
                   class="px-3 py-1.5 text-sm font-medium rounded-lg text-gray-400 cursor-not-allowed"
                   v-html="link.label"
                ></span>
             </template>
          </div>
       </div>
    </div>

    <!-- SlideOver Form -->
    <SlideOver 
       v-model="isSlideOverOpen"
       :title="selectedSpeaker ? 'Chỉnh sửa Diễn giả' : 'Thêm Diễn giả mới'"
       description="Cập nhật thông tin diễn giả hoặc khách mời giảng luận"
    >
       <SpeakerForm 
          v-if="isSlideOverOpen"
          :speaker="selectedSpeaker"
          @close="closeSlideOver"
          @success="handleSuccess"
       />
    </SlideOver>

  </component>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import DataToolbar from '@/Components/DataToolbar.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SlideOver from '@/Components/SlideOver.vue';
import SpeakerForm from './Partials/SpeakerForm.vue';

const props = defineProps({
  speakers: Object,
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

const showFilters = ref(false);

const filterForm = ref({
   search: props.filters.search || '',
   type: props.filters.type || '',
});

const applyFilters = debounce(() => {
   router.get(route('speakers.index'), filterForm.value, {
      preserveState: true,
      preserveScroll: true,
      replace: true
   });
}, 300);

const isSlideOverOpen = ref(false);
const selectedSpeaker = ref(null);

const openCreateSlideOver = () => {
   selectedSpeaker.value = null;
   isSlideOverOpen.value = true;
};

const openEditSlideOver = (speaker) => {
   selectedSpeaker.value = speaker;
   isSlideOverOpen.value = true;
};

const closeSlideOver = () => {
   isSlideOverOpen.value = false;
   selectedSpeaker.value = null;
};

const handleSuccess = () => {
   // Reload is handled by Inertia on success, just optionally close SlideOver
   closeSlideOver();
};
</script>