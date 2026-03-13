<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DutyRosterLayout from '@/Layouts/DutyRosterLayout.vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const props = defineProps({
  templates:            Array,
  departments:          Array,
  isPortal:             Boolean,
  portalType:           String,
  routePrefix:          { type: String, default: 'duty-rooster.' },
  department:           Object,
  availableDepartments: Array,
  isGlobalAdmin:        Boolean,
});

const deleteId   = ref(null);
const deleting   = ref(false);

const confirmDel = (id) => { deleteId.value = id; };
const cancelDel  = () => { deleteId.value = null; };
const doDelete   = () => {
  deleting.value = true;
  router.delete(route(props.routePrefix + 'templates.destroy', deleteId.value), {
    onSuccess: () => { deleteId.value = null; },
    onFinish:  () => { deleting.value = false; },
  });
};

const tplToDelete = () => props.templates.find(t => t.id === deleteId.value);
</script>

<template>
  <component
    :is="isPortal ? PortalLayout : DutyRosterLayout"
    v-bind="isPortal
      ? { department, availableDepartments, isGlobalAdmin, portalType }
      : { title: 'Mẫu Phân Công' }"
  >
    <Head title="Mẫu Phân Công" />

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8 gap-2">
        <div class="min-w-0">
          <h1 class="text-xl sm:text-2xl font-black text-gray-900 truncate">Quản lý Template Phân công</h1>
          <p class="hidden sm:block text-sm text-gray-500 mt-1">Tạo và quản lý các mẫu vị trí phục vụ tái sử dụng</p>
        </div>
        <Link :href="route(routePrefix + 'templates.create')"
          class="flex items-center gap-1.5 sm:gap-2 px-3 sm:px-5 py-2 sm:py-2.5 bg-orange-500 text-white font-bold text-xs sm:text-sm rounded-xl hover:bg-orange-600 transition-all shadow-sm shrink-0">
          <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          <span class="hidden sm:inline">Tạo mẫu mới</span>
        </Link>
      </div>

      <!-- Empty state -->
      <div v-if="templates.length === 0" class="text-center py-24 bg-white rounded-3xl border-2 border-dashed border-gray-200">
        <div class="w-20 h-20 bg-orange-50 rounded-3xl flex items-center justify-center mx-auto mb-5">
          <svg class="w-10 h-10 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
        </div>
        <h3 class="text-lg font-black text-gray-900 mb-2">Chưa có mẫu nào</h3>
        <p class="text-sm text-gray-400 mb-6">Tạo mẫu đầu tiên để tái sử dụng cho các buổi lễ</p>
        <Link :href="route(routePrefix + 'templates.create')"
          class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 text-white font-bold text-sm rounded-xl hover:bg-orange-600 transition-all">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          Tạo mẫu đầu tiên
        </Link>
      </div>

      <!-- Template grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="tpl in templates" :key="tpl.id"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-orange-100 transition-all group overflow-hidden">
          <Link :href="route(routePrefix + 'templates.show', tpl.id)" class="block p-5">
            <div class="flex items-start justify-between mb-4">
              <div class="w-10 h-10 bg-orange-50 rounded-2xl flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
              </div>
              <svg class="w-4 h-4 text-gray-300 group-hover:text-orange-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </div>
            <h3 class="font-black text-gray-900 group-hover:text-orange-700 transition-colors">{{ tpl.name }}</h3>
            <p class="text-xs text-gray-400 mt-1">{{ tpl.roles?.length || 0 }} vai trò</p>
            <!-- Role tags -->
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
          <!-- Action bar -->
          <div class="px-5 pb-4 flex gap-2">
            <Link :href="route(routePrefix + 'templates.show', tpl.id)"
              class="flex-1 py-2 text-xs font-bold text-center text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-xl">
              Chỉnh sửa
            </Link>
            <button @click.prevent="confirmDel(tpl.id)"
              class="px-3 py-2 text-xs font-bold text-red-500 bg-red-50 hover:bg-red-100 rounded-xl border border-red-100 transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete confirmation modal -->
    <div v-if="deleteId" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="cancelDel"></div>
      <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 z-10">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
          </div>
          <div>
            <h3 class="text-base font-black text-gray-900">Xóa template này?</h3>
            <p class="text-xs text-gray-500">Hành động không thể hoàn tác</p>
          </div>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl px-4 py-3 mb-5 text-xs text-red-700">
          Template <strong>"{{ tplToDelete()?.name }}"</strong> sẽ bị xóa vĩnh viễn cùng các vai trò liên kết.
        </div>
        <div class="flex gap-3">
          <button @click="cancelDel" class="flex-1 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl">Hủy</button>
          <button @click="doDelete" :disabled="deleting"
            class="flex-1 py-2.5 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-xl disabled:opacity-50 flex items-center justify-center gap-2">
            <svg v-if="deleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"/><path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            {{ deleting ? 'Đang xóa...' : 'Xóa template' }}
          </button>
        </div>
      </div>
    </div>

  </component>
</template>