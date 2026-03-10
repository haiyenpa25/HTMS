<template>
  <TransitionRoot appear :show="show" as="template">
    <Dialog as="div" class="relative z-[100]" @close="closeModal">
      <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-start justify-center p-4 text-center sm:pt-24">
          <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
            <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all border border-gray-100">
              
              <!-- Search Input -->
              <div class="relative flex items-center px-4 py-4 border-b border-gray-100">
                <svg class="w-6 h-6 text-gray-400 absolute left-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input 
                  type="text" 
                  ref="searchInput"
                  v-model="query"
                  @input="handleSearch"
                  @keydown.down.prevent="moveSelection(1)"
                  @keydown.up.prevent="moveSelection(-1)"
                  @keydown.enter.prevent="selectCurrent"
                  class="w-full pl-12 pr-4 py-3 bg-transparent border-none focus:ring-0 text-lg font-medium text-gray-800 placeholder-gray-400"
                  placeholder="Tìm kiếm Tín hữu, Ban ngành, Buổi nhóm..."
                />
                <button @click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                  <span class="text-xs font-bold px-1 py-0.5 border border-gray-200 rounded">ESC</span>
                </button>
              </div>

              <!-- Results list -->
              <div class="max-h-[60vh] overflow-y-auto p-2">
                <div v-if="isLoading" class="p-6 text-center text-gray-500">
                  <svg class="animate-spin h-6 w-6 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Đang tìm kiếm...
                </div>
                
                <div v-else-if="query.length > 0 && results.length === 0" class="p-12 text-center">
                  <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  <p class="text-gray-500 font-medium">Không tìm thấy kết quả nào cho "{{ query }}"</p>
                </div>
                
                <div v-else-if="results.length > 0" class="space-y-1">
                  <!-- Grouped by Type visually without formal grouping -->
                  <div 
                    v-for="(item, index) in results" 
                    :key="index"
                    @mouseenter="selectedIndex = index"
                    @click="goToUrl(item.url)"
                    class="flex items-center gap-4 px-4 py-3 rounded-xl cursor-pointer transition-colors"
                    :class="selectedIndex === index ? 'bg-blue-50' : 'hover:bg-gray-50'"
                  >
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center" :class="selectedIndex === index ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-500'">
                      <!-- Icons -->
                      <svg v-if="item.icon === 'user'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                      <svg v-else-if="item.icon === 'office-building'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-gray-900 truncate">{{ item.title }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ item.type }}</span>
                      </div>
                      <p class="text-xs font-medium text-gray-500 truncate mt-0.5">{{ item.subtitle }}</p>
                    </div>
                    <div v-show="selectedIndex === index" class="flex-shrink-0 text-blue-600">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                  </div>
                </div>
                
                <div v-if="query.length === 0" class="p-8">
                  <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Gợi ý tìm kiếm</p>
                  <div class="flex flex-wrap gap-2">
                    <span @click="query = 'Mục sư'; handleSearch()" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200 cursor-pointer transition-colors">Mục sư</span>
                    <span @click="query = 'Chấp sự'; handleSearch()" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200 cursor-pointer transition-colors">Chấp sự</span>
                    <span @click="query = 'Thanh Tráng'; handleSearch()" class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200 cursor-pointer transition-colors">Thanh Tráng</span>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div class="bg-gray-50 border-t border-gray-100 px-4 py-3 flex items-center justify-between text-xs text-gray-500">
                <div class="flex items-center gap-4">
                  <div class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded shadow-sm text-gray-600">↑</kbd> <kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded shadow-sm text-gray-600">↓</kbd> <span>để chọn</span></div>
                  <div class="flex items-center gap-1"><kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded shadow-sm text-gray-600">Enter</kbd> <span>để mở</span></div>
                </div>
                <div class="font-bold text-gray-400">CMS Search</div>
              </div>

            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue';

const props = defineProps({
    show: Boolean
});

const emit = defineEmits(['close']);

const query = ref('');
const results = ref([]);
const isLoading = ref(false);
const selectedIndex = ref(0);
const searchInput = ref(null);

let debounceTimeout = null;

const closeModal = () => {
    emit('close');
    setTimeout(() => {
        query.value = '';
        results.value = [];
        selectedIndex.value = 0;
    }, 200);
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        nextTick(() => {
            searchInput.value?.focus();
        });
    }
});

const handleSearch = () => {
    clearTimeout(debounceTimeout);
    if (query.value.trim().length < 2) {
        results.value = [];
        isLoading.value = false;
        return;
    }
    
    isLoading.value = true;
    debounceTimeout = setTimeout(async () => {
        try {
            const response = await axios.get('/api/search', { params: { q: query.value.trim() } });
            results.value = response.data;
            selectedIndex.value = 0;
        } catch (error) {
            console.error('Search error', error);
        } finally {
            isLoading.value = false;
        }
    }, 300);
};

const moveSelection = (direction) => {
    if (results.value.length === 0) return;
    
    selectedIndex.value += direction;
    if (selectedIndex.value < 0) {
        selectedIndex.value = results.value.length - 1;
    } else if (selectedIndex.value >= results.value.length) {
        selectedIndex.value = 0;
    }
};

const selectCurrent = () => {
    if (results.value.length > 0 && selectedIndex.value >= 0 && selectedIndex.value < results.value.length) {
        goToUrl(results.value[selectedIndex.value].url);
    }
};

const goToUrl = (url) => {
    emit('close');
    router.visit(url);
};
</script>
