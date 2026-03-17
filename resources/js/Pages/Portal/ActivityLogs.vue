<script setup>
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ActivityLogItem from '@/Components/ActivityLogItem.vue';

const page = usePage();
const props = defineProps({
  department: Object,
  activities: Object,
});

// Using same layout variables
const portalType = page.url.includes('/ministry') ? 'ministry' : 'activities';
</script>

<template>
  <PortalLayout title="Nhật Ký Ban Ngành">
    <Head title="Nhật Ký Hoạt Động" />

    <div class="px-4 py-8 lg:p-8 max-w-5xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4 border-b border-gray-100 pb-4">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-indigo-50 text-indigo-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
          <h1 class="text-2xl font-black text-gray-900 tracking-tight">Nhật Ký Hoạt Động</h1>
          <p class="text-sm text-gray-500 font-medium">Theo dõi chỉnh sửa dữ liệu, báo cáo, và các tác vụ khác thuộc về ban {{ department.name }}.</p>
        </div>
      </div>

      <!-- Timeline Component -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
          <div v-if="activities.data.length === 0" class="text-center py-16 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="font-bold text-gray-500">Chưa có nhật ký nào được ghi lại cho ban ngành này.</p>
          </div>

          <!-- Timeline -->
          <div v-else class="relative border-l-2 border-indigo-50 ml-6 space-y-8 pb-4">
            <div v-for="log in activities.data" :key="log.id" class="relative pl-8 group">
              <ActivityLogItem :log="log" idPrefix="portal-log" />
            </div>
          </div>
        </div>
        
        <!-- Paginator -->
        <div v-if="activities.links && activities.data.length > 0" class="px-6 py-4 border-t border-gray-50 bg-gray-50/30">
          <Pagination :links="activities.links" />
        </div>
      </div>
    </div>
  </PortalLayout>
</template>
