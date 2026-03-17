<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminPortalLayout from '@/Layouts/AdminPortalLayout.vue';
import debounce from 'lodash/debounce';
import Pagination from '@/Components/Pagination.vue';
import ActivityLogItem from '@/Components/ActivityLogItem.vue';

const props = defineProps({
  activities: Object,
  filters: Object,
  filterOptions: Object,
});

// State for filters
const form = ref({
  event: props.filters.event || '',
  causer_id: props.filters.causer_id || '',
  subject_type: props.filters.subject_type || '',
});

// Watch filters and reload
watch(form, debounce((value) => {
  router.get(route('admin.activity.index'), value, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
}, 300), { deep: true });

const clearFilters = () => {
  form.value = { event: '', causer_id: '', subject_type: '' };
};
</script>

<template>
  <AdminPortalLayout title="Nhật Ký Hoạt Động" activeTab="activity_logs">
    <!-- Define custom tabs for Admin Layout instead of default Users/Roles -->
    <template #tabs>
      <div class="flex">
        <div class="px-4 py-3 text-sm font-bold border-b-2 border-white text-white">
          Lịch Sử Hệ Thống Thống Nhất
        </div>
      </div>
    </template>

    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Filters Header -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
        <div class="flex flex-col sm:flex-row gap-4 items-end">
          <div class="flex-1 w-full space-y-1">
            <label class="text-xs font-bold text-gray-500 uppercase">Hành động</label>
            <select v-model="form.event" class="w-full bg-gray-50 border-gray-200 rounded-xl text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500">
              <option value="">Tất cả</option>
              <option v-for="opt in filterOptions.events" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>
          
          <div class="flex-1 w-full space-y-1">
            <label class="text-xs font-bold text-gray-500 uppercase">Phân hệ (Model)</label>
            <select v-model="form.subject_type" class="w-full bg-gray-50 border-gray-200 rounded-xl text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500">
              <option value="">Toàn bộ Hệ thống</option>
              <option v-for="opt in filterOptions.modules" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>

          <div class="flex-1 w-full space-y-1">
            <label class="text-xs font-bold text-gray-500 uppercase">Người thực hiện (ID)</label>
            <input type="text" v-model="form.causer_id" placeholder="Nhập ID User..." 
                   class="w-full bg-gray-50 border-gray-200 rounded-xl text-sm font-medium focus:ring-indigo-500 focus:border-indigo-500"/>
          </div>

          <div class="shrink-0 flex gap-2 w-full sm:w-auto mt-2 sm:mt-0">
            <button @click="clearFilters" v-if="form.event || form.causer_id || form.subject_type"
                    class="px-4 py-2.5 bg-gray-100 text-gray-600 hover:bg-gray-200 font-bold text-sm rounded-xl transition-colors whitespace-nowrap">
              Xoá bộ lọc
            </button>
          </div>
        </div>
      </div>

      <!-- Activity Timeline -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 bg-gray-50/50">
          <div>
            <h2 class="text-lg font-black text-gray-900">Chuỗi Sự Kiện Cục Bộ</h2>
            <p class="text-xs text-gray-500 mt-1font-medium">Hiển thị lịch sử hoạt động của mọi người dùng theo thời gian thực.</p>
          </div>
        </div>

        <div class="p-6">
          <div v-if="activities.data.length === 0" class="text-center py-12 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="font-bold">Không tìm thấy nhật ký nào phù hợp</p>
          </div>

          <!-- Timeline Array -->
          <div v-else class="relative border-l-2 border-gray-100 ml-4 md:ml-6 space-y-8 pb-4 mt-6">
            <div v-for="log in activities.data" :key="log.id" class="relative pl-6 md:pl-8 group">
              <ActivityLogItem :log="log" idPrefix="admin-log" />
            </div>
          </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="activities.links && activities.data.length > 0" class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
          <Pagination :links="activities.links" />
        </div>
      </div>
    </div>
  </AdminPortalLayout>
</template>
