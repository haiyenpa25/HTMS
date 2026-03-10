<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DutyRosterLayout from '@/Layouts/DutyRosterLayout.vue';

const props = defineProps({
  templates: Array,
  departments: Array,
});
</script>

<template>
  <DutyRosterLayout title="Mẫu Phân Công">
    <Head title="Mẫu Phân Công" />

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-2xl font-black text-gray-900">Quản lý Template Phân công</h1>
          <p class="text-sm text-gray-500 mt-1">Tạo và quản lý các mẫu vị trí phục vụ tái sử dụng</p>
        </div>
        <Link :href="route('duty-rooster.templates.create')"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 text-white font-bold text-sm rounded-xl hover:bg-orange-600 transition-all shadow-sm">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          Tạo mẫu mới
        </Link>
      </div>

      <!-- Empty state -->
      <div v-if="templates.length === 0" class="text-center py-24 bg-white rounded-3xl border-2 border-dashed border-gray-200">
        <div class="w-20 h-20 bg-orange-50 rounded-3xl flex items-center justify-center mx-auto mb-5">
          <svg class="w-10 h-10 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
        </div>
        <h3 class="text-lg font-black text-gray-900 mb-2">Chưa có mẫu nào</h3>
        <p class="text-sm text-gray-400 mb-6">Tạo mẫu đầu tiên để tái sử dụng cho các buổi lễ</p>
        <Link :href="route('duty-rooster.templates.create')"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 text-white font-bold text-sm rounded-xl hover:bg-orange-600 transition-all">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          Tạo mẫu đầu tiên
        </Link>
      </div>

      <!-- Template grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <Link v-for="tpl in templates" :key="tpl.id"
          :href="route('duty-rooster.templates.show', tpl.id)"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-orange-100 transition-all group p-5 cursor-pointer">
          <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 bg-orange-50 rounded-2xl flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
            </div>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-orange-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
          </div>
          <h3 class="font-black text-gray-900 group-hover:text-orange-700 transition-colors">{{ tpl.name }}</h3>
          <p class="text-xs text-gray-400 mt-1">{{ tpl.roles?.length || 0 }} vai trò</p>
          <!-- Dept tags -->
          <div class="flex flex-wrap gap-1 mt-3">
            <span v-for="role in (tpl.roles || []).slice(0,3)" :key="role.id"
              class="text-[10px] font-bold px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">
              {{ role.departmentRole?.name }}
            </span>
            <span v-if="(tpl.roles?.length || 0) > 3" class="text-[10px] font-bold px-2 py-0.5 bg-gray-100 text-gray-400 rounded-full">
              +{{ tpl.roles.length - 3 }}
            </span>
          </div>
        </Link>
      </div>
    </div>
  </DutyRosterLayout>
</template>
