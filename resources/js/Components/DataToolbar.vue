<template>
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 w-full">
    <!-- Phân vùng bên trái: Tìm kiếm linh hoạt -->
    <div class="relative w-full md:w-96 flex-shrink-0">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>
      <input
        :value="search"
        @input="$emit('update:search', $event.target.value)"
        type="text"
        :placeholder="placeholder"
        class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 sm:text-sm transition-all shadow-sm"
      />
    </div>

    <!-- Phân vùng bên phải: Bộ Lọc & Layout Switcher -->
    <div class="flex items-center space-x-3 overflow-x-auto pb-1 md:pb-0 no-scrollbar">
       <!-- Nơi nhúng các filter tùy chỉnh (Slot) -->
       <slot name="filters"></slot>
       
       <!-- Grid / List Switcher -->
       <div class="flex items-center bg-gray-100 rounded-lg p-1 border border-gray-200 shadow-inner shrink-0">
          <button 
             type="button"
             @click="toggleMode('grid')"
             class="p-1.5 rounded-md transition-all shadow-sm flex items-center justify-center h-8 w-8"
             :class="viewMode === 'grid' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
             title="Hiển thị dạng thẻ (Lưới)"
          >
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
          </button>
          <button 
             type="button"
             @click="toggleMode('list')"
             class="p-1.5 rounded-md transition-all flex items-center justify-center h-8 w-8"
             :class="viewMode === 'list' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
             title="Hiển thị dạng danh sách (Bảng)"
          >
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
          </button>
       </div>
       
       <!-- Action Button (Slot) -->
       <div class="shrink-0 pl-1 border-l border-gray-100 hidden md:block">
          <slot name="actions"></slot>
       </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

const props = defineProps({
  search: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Tìm kiếm nhanh...'
  },
  viewMode: {
    type: String,
    default: 'list'  // 'list' or 'grid'
  },
  storageKey: {
    type: String,
    default: 'app_view_mode' // Key dùng lưu xuống LocalStorage
  }
});

const emit = defineEmits(['update:search', 'update:viewMode']);

const toggleMode = (mode) => {
   emit('update:viewMode', mode);
   if (window.localStorage) {
       window.localStorage.setItem(props.storageKey, mode);
   }
};

onMounted(() => {
   // Phục hồi lựa chọn của người dùng
   if (window.localStorage) {
       const savedMode = window.localStorage.getItem(props.storageKey);
       if (savedMode && (savedMode === 'list' || savedMode === 'grid')) {
           emit('update:viewMode', savedMode);
       }
   }
});
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
