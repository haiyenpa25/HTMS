<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-bold text-xl text-slate-800 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Nhật ký Hoạt động (Audit Logs)
        </h2>
      </div>
    </template>

    <div class="py-6">
      <div class="w-full sm:px-6 lg:px-8">
        
        <!-- Filters -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
                <input v-model="form.search" type="text" placeholder="Tìm kiếm theo mô tả, người thao tác..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" @keyup.enter="filterLogs">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            
            <div class="sm:w-48">
                <select v-model="form.event" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tất cả thao tác</option>
                    <option v-for="evt in events" :key="evt" :value="evt">{{ formatEvent(evt) }}</option>
                </select>
            </div>

            <div class="sm:w-48">
                <input v-model="form.date" type="date" class="w-full px-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <button @click="filterLogs" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Lọc</button>
            <button @click="clearFilters" v-if="hasFilters" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm hover:bg-slate-200 transition">Xóa lọc</button>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Thời gian</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Người thao tác</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Mô tả</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Thao tác</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Đối tượng</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Chi tiết</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-200">
                <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-slate-900">{{ log.created_at_human }}</div>
                    <div class="text-xs text-slate-500">{{ log.created_at }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs ring-2 ring-white">
                            {{ log.causer_name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-bold text-slate-900">{{ log.causer_name }}</div>
                        </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-slate-900 font-medium">{{ log.description }}</div>
                    <div class="text-xs text-slate-500" v-if="log.log_name !== 'default'">Kênh: {{ log.log_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="eventClass(log.event)">
                      {{ formatEvent(log.event) || 'Unknown' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-slate-500 font-mono">{{ log.subject_type || '-' }}</div>
                    <div class="text-xs text-slate-400" v-if="log.subject_id">ID: {{ log.subject_id }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button @click="viewDetails(log)" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-md transition-colors inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        Xem Data
                    </button>
                  </td>
                </tr>
                <tr v-if="logs.data.length === 0">
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">Không tìm thấy nhật ký tương ứng.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between" v-if="logs.links.length > 3">
            <div class="flex-1 flex justify-between sm:hidden">
              <Link :href="logs.prev_page_url" class="relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50" :class="{'opacity-50 cursor-not-allowed': !logs.prev_page_url}">Trước</Link>
              <Link :href="logs.next_page_url" class="ml-3 relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50" :class="{'opacity-50 cursor-not-allowed': !logs.next_page_url}">Sau</Link>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div><p class="text-sm text-slate-700">Hiển thị <span class="font-medium">{{ logs.from || 0 }}</span> đến <span class="font-medium">{{ logs.to || 0 }}</span> trong <span class="font-medium">{{ logs.total }}</span> kết quả</p></div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <template v-for="(link, p) in logs.links" :key="p">
                    <Link 
                        v-if="link.url"
                        :href="link.url" 
                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors"
                        :class="[
                            link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-slate-300 text-slate-500 hover:bg-slate-50',
                            p === 0 ? 'rounded-l-md' : '',
                            p === logs.links.length - 1 ? 'rounded-r-md' : ''
                        ]"
                        v-html="link.label"
                    />
                    <span v-else class="relative inline-flex items-center px-4 py-2 border border-slate-300 bg-white text-sm font-medium text-slate-400" :class="[p === 0 ? 'rounded-l-md' : '', p === logs.links.length - 1 ? 'rounded-r-md' : '']" v-html="link.label"></span>
                  </template>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Details SlideOver -->
    <SlideOver :model-value="!!selectedLog" @update:model-value="v => !v && (selectedLog = null)" :title="`Chi tiết dữ liệu (#${selectedLog?.id})`" size="md">

            <div v-if="selectedLog" class="space-y-4">
                <div v-if="selectedLog.properties.old && Object.keys(selectedLog.properties.old).length > 0">
                   <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Dữ liệu Cũ (Old)</h4>
                   <pre class="bg-red-50 text-red-800 p-4 rounded-lg text-xs overflow-x-auto border border-red-100 font-mono">{{ JSON.stringify(selectedLog.properties.old, null, 2) }}</pre>
                </div>
                <div v-if="selectedLog.properties.attributes && Object.keys(selectedLog.properties.attributes).length > 0">
                   <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Dữ liệu Mới (Attributes)</h4>
                   <pre class="bg-emerald-50 text-emerald-800 p-4 rounded-lg text-xs overflow-x-auto border border-emerald-100 font-mono">{{ JSON.stringify(selectedLog.properties.attributes, null, 2) }}</pre>
                </div>
                <div v-if="!selectedLog.properties.old && !selectedLog.properties.attributes">
                   <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Data Properties</h4>
                   <pre class="bg-slate-50 text-slate-800 p-4 rounded-lg text-xs overflow-x-auto border border-slate-200 font-mono">{{ JSON.stringify(selectedLog.properties, null, 2) }}</pre>
                </div>
            </div>
      <template #footer>
        <div class="flex justify-end w-full">
            <button @click="selectedLog = null" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 font-medium">Đóng lại</button>
        </div>
      </template>
    </SlideOver>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';

const props = defineProps({
    logs: Object,
    events: Array,
    filters: Object,
});

const form = useForm({
    search: props.filters.search || '',
    event: props.filters.event || '',
    date: props.filters.date || '',
});

const selectedLog = ref(null);

const hasFilters = computed(() => {
    return form.search !== '' || form.event !== '' || form.date !== '';
});

const filterLogs = () => {
    router.get(route('admin.activity.logs'), {
        search: form.search,
        event: form.event,
        date: form.date,
    }, { preserveState: true, preserveScroll: true });
};

const clearFilters = () => {
    form.search = '';
    form.event = '';
    form.date = '';
    filterLogs();
};

const viewDetails = (log) => {
    selectedLog.value = log;
};

const formatEvent = (event) => {
    const map = {
        'created': 'Tạo mới',
        'updated': 'Cập nhật',
        'deleted': 'Đã xóa',
        'restored': 'Khôi phục',
    };
    return map[event] || event;
};

const eventClass = (event) => {
    const map = {
        'created': 'bg-emerald-100 text-emerald-800',
        'updated': 'bg-blue-100 text-blue-800',
        'deleted': 'bg-red-100 text-red-800',
        'restored': 'bg-purple-100 text-purple-800',
    };
    return map[event] || 'bg-slate-100 text-slate-800';
};
</script>