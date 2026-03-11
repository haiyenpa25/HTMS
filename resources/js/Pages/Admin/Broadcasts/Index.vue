<template>
  <component :is="currentLayout">
    <template #header>Chiến dịch Truyền Thông (Email Broadcasting)</template>

    <div class="py-4 space-y-6 w-full">
        <div class="bg-gradient-to-br from-indigo-900 to-purple-900 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden">
           <div class="absolute right-0 top-0 opacity-10 translate-x-12 -translate-y-12">
               <svg class="h-64 w-64" fill="currentColor" viewBox="0 0 24 24"><path d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0l-8 5-8-5h16zm0 12H4V8l8 5 8-5v10z"/></svg>
           </div>
           
           <div class="flex flex-col md:flex-row justify-between items-start md:items-end relative z-10">
               <div>
                   <h2 class="text-3xl font-black mb-2">Thư Tín & Thông Báo</h2>
                   <p class="text-indigo-200 text-sm max-w-xl">Soạn thảo và gửi Email hàng loạt tới tất cả Tín hữu hoặc theo từng Ban ngành cụ thể. Hệ thống sẽ tự động gửi ngầm để không làm nghẽn máy chủ.</p>
               </div>
               <div class="mt-6 md:mt-0">
                   <Link :href="route('admin.broadcasts.create')" class="bg-white text-indigo-900 hover:bg-indigo-50 font-bold py-3 px-6 rounded-xl shadow transition-colors inline-flex items-center">
                       <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                       Soạn Bản Tin Mới
                   </Link>
               </div>
           </div>
        </div>

        <!-- Lịch sử Broadcasts -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h3 class="font-black text-gray-800 text-sm uppercase tracking-wider">Lịch sử Chiến dịch</h3>
            </div>
            
            <div class="overflow-x-auto print:p-0">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-[11px] text-gray-500 uppercase font-black tracking-wider">
                  <tr>
                    <th class="px-6 py-4 text-left">Chủ Đề (Subject)</th>
                    <th class="px-6 py-4 text-center">Trạng Thái</th>
                    <th class="px-6 py-4 text-left">Gửi Tới</th>
                    <th class="px-6 py-4 text-center">Tiến Độ / Thống Kê</th>
                    <th class="px-6 py-4 text-left">Thời Gian</th>
                    <th class="px-6 py-4 text-right">Thao Tác</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                  <tr v-for="item in broadcasts.data" :key="item.id" class="hover:bg-indigo-50/20 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-900 text-sm max-w-xs truncate" :title="item.subject">{{ item.subject }}</div>
                        <div class="text-[10px] text-gray-400 mt-1 uppercase font-mono">Tác giả: {{ item.creator?.name || 'Vô danh' }}</div>
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        <span class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full" :class="getStatusColor(item.status)">
                            {{ getStatusLabel(item.status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs text-gray-600">
                            <span v-if="!item.target_roles && !item.target_departments" class="font-bold text-indigo-600">Mọi Người (All Members)</span>
                            <div v-else class="flex flex-col gap-1">
                                <span v-if="item.target_roles" class="text-[10px] px-1.5 py-0.5 bg-gray-100 border border-gray-200 rounded truncate max-w-[150px]" :title="item.target_roles.join(', ')">Vai trò: {{ item.target_roles.length }} selected</span>
                                <span v-if="item.target_departments" class="text-[10px] px-1.5 py-0.5 bg-indigo-50 border border-indigo-100 rounded text-indigo-700 truncate max-w-[150px]" :title="item.target_departments.join(', ')">Ban: {{ item.target_departments.length }} selected</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        <div class="flex items-center justify-center space-x-3 text-xs font-mono">
                            <div class="flex flex-col items-center" title="Tổng số nhận">
                                <span class="text-gray-400">Total</span>
                                <span class="font-bold text-gray-700">{{ item.total_recipients }}</span>
                            </div>
                            <div class="flex flex-col items-center" title="Thành công">
                                <span class="text-emerald-400">✓</span>
                                <span class="font-bold text-emerald-600">{{ item.success_count }}</span>
                            </div>
                            <div class="flex flex-col items-center" title="Thất bại">
                                <span class="text-red-400">✗</span>
                                <span class="font-bold text-red-600">{{ item.failed_count }}</span>
                            </div>
                        </div>
                        <!-- Progress bar demo if sending -->
                        <div v-if="item.status === 'sending'" class="w-full bg-gray-200 rounded-full h-1.5 mt-2 overflow-hidden">
                             <div class="bg-blue-600 h-1.5 rounded-full animate-pulse" :style="`width: ${ (item.success_count + item.failed_count) / (item.total_recipients || 1) * 100 }%`"></div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-xs font-bold text-gray-700">{{ item.sent_at ? new Date(item.sent_at).toLocaleString('vi-VN') : '---' }}</div>
                        <div class="text-[10px] text-gray-400">Created: {{ new Date(item.created_at).toLocaleDateString('vi-VN') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end space-x-2">
                           <button v-if="item.status === 'draft' || item.status === 'failed'" @click="sendBroadcast(item.id)" class="text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded text-[11px] font-bold uppercase transition-colors">
                              Gửi Ngay
                           </button>
                           <button v-if="item.status !== 'sending'" @click="deleteBroadcast(item.id)" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded transition-colors" title="Xoá">
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                           </button>
                        </div>
                    </td>
                  </tr>
                  <tr v-if="broadcasts.data.length === 0">
                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                      <div class="flex flex-col items-center justify-center">
                         <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"></path></svg>
                         Chưa có chiến dịch Email nào. Hãy tạo mới!
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Footer Pagination -->
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-end print:hidden">
               <Pagination :links="broadcasts.links" />
            </div>
        </div>
    </div>
  </component>
</template>

<script setup>
import { computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const currentLayout = computed(() => {
    if (typeof window === 'undefined') return AuthenticatedLayout;
    return window.innerWidth < 768 ? MobileLayout : AuthenticatedLayout;
});

const props = defineProps(['broadcasts']);

const getStatusLabel = (status) => {
    const map = { draft: 'Bản Nháp', sending: 'Đang Gửi', completed: 'Hoàn Tất', failed: 'Lỗi/Có Lỗi' };
    return map[status] || status;
};

const getStatusColor = (status) => {
    const map = { 
        draft: 'bg-gray-100 text-gray-600', 
        sending: 'bg-blue-100 text-blue-700 animate-pulse', 
        completed: 'bg-emerald-100 text-emerald-800', 
        failed: 'bg-red-100 text-red-800' 
    };
    return map[status] || 'bg-gray-100 text-gray-800';
};

const sendBroadcast = (id) => {
    if(confirm('Bạn có chắc chắn muốn đưa Thư này vào hàng đợi máy chủ để gửi hàng loạt đi? Quá trình sẽ diễn ra dưới nền.')) {
        router.post(route('admin.broadcasts.send', id));
    }
};

const deleteBroadcast = (id) => {
    if(confirm('Xoá bản ghi lưu trữ thư này vĩnh viễn?')) {
        router.delete(route('admin.broadcasts.destroy', id));
    }
};
</script>