<template>
    <Head title="Hộp Thư / Thông Báo" />

    <component :is="layout">
        <template #header>
            Thông báo của bạn
        </template>

        <div class="max-w-4xl mx-auto py-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h2 class="text-lg font-black text-gray-800 tracking-tight">Tất cả thông báo</h2>
                    <button @click="markAllAsRead" class="text-sm font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-xl transition-colors">
                        Đánh dấu tất cả đã đọc
                    </button>
                </div>

                <div v-if="loading" class="p-12 text-center text-gray-500 flex flex-col items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Đang tải danh sách...
                </div>
                
                <div v-else-if="notifications.length === 0" class="p-16 text-center text-gray-500 flex flex-col items-center bg-gray-50/30">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-6 shadow-sm border border-gray-200">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                    <p class="font-bold text-gray-600 text-lg">Bạn không có thông báo nào</p>
                    <p class="text-sm text-gray-400 mt-1">Các thông báo mới sẽ xuất hiện ở đây.</p>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div v-for="notif in notifications" :key="notif.id" 
                         @click="handleNotificationClick(notif)"
                         class="p-5 sm:p-6 hover:bg-gray-50 cursor-pointer transition-all duration-200 group flex gap-5"
                         :class="{ 'bg-blue-50/30 border-l-4 border-l-blue-500': !notif.read_at, 'border-l-4 border-l-transparent': notif.read_at }">
                         
                        <div class="flex-shrink-0 mt-1">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-sm border" :class="notif.data.bg_color || 'bg-white border-gray-200'">
                                <svg v-if="notif.type === 'announcement'" class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                                <svg v-else class="w-6 h-6" :class="notif.data.color || 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1.5 flex-wrap gap-2">
                                <h3 class="text-base font-bold transition-colors pr-4" :class="!notif.read_at ? 'text-gray-900 group-hover:text-blue-700' : 'text-gray-700'">{{ notif.data.title }}</h3>
                                <span class="text-xs font-semibold text-gray-400 whitespace-nowrap bg-gray-100 px-2 py-0.5 rounded-md">{{ new Date(notif.created_at).toLocaleString('vi-VN') }}</span>
                            </div>
                            <div class="text-sm text-gray-600 leading-relaxed max-w-3xl" v-html="notif.data.content || notif.data.message"></div>
                        </div>

                        <div class="flex-shrink-0 flex items-center w-4 justify-center">
                            <div v-if="!notif.read_at" class="w-3 h-3 bg-blue-600 rounded-full shadow-sm shadow-blue-200"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const page = usePage();
const layout = computed(() => {
    return AuthenticatedLayout;
});

const notifications = ref([]);
const loading = ref(true);

const fetchAllNotifications = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('api.notifications.list', { limit: 'all' }));
        notifications.value = response.data.notifications;
    } catch (e) {
        console.error('Lỗi lấy danh sách thông báo', e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchAllNotifications();
});

const handleNotificationClick = (notif) => {
    if (!notif.read_at) {
        axios.post(route('notifications.read', { id: notif.id }), { type: notif.type })
             .then(() => fetchAllNotifications());
    }
    if (notif.data.action_url) {
        router.visit(notif.data.action_url);
    }
};

const markAllAsRead = () => {
    router.post(route('notifications.mark-all-read'), {}, {
        preserveScroll: true,
        onSuccess: () => fetchAllNotifications()
    });
};
</script>
