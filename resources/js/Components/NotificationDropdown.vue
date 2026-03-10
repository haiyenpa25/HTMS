<template>
  <div class="relative">
    <button @click="isOpen = !isOpen" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none transition-colors rounded-full hover:bg-gray-100">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
      <span v-if="unreadCount > 0" class="absolute top-1 right-1 flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-500 border-2 border-white rounded-full">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown menu -->
    <div v-if="isOpen" @click.away="isOpen = false" class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl overflow-hidden z-50 border border-gray-100 transform origin-top-right transition-all">
      <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
        <h3 class="text-sm font-black text-gray-800">Thông báo</h3>
        <button v-if="unreadCount > 0" @click="markAllAsRead" class="text-xs font-bold text-blue-600 hover:text-blue-800">Đánh dấu tất cả đã đọc</button>
      </div>
      
      <div class="max-h-96 overflow-y-auto">
        <div v-if="notifications.length === 0" class="px-4 py-8 text-center text-gray-500 text-sm">
          Bạn không có thông báo mới nào.
        </div>
        <div v-else class="divide-y divide-gray-100">
          <div v-for="notif in notifications" :key="notif.id" 
               @click="handleNotificationClick(notif)"
               class="px-4 py-3 hover:bg-gray-50 cursor-pointer flex gap-3 transition-colors group">
               
            <div class="flex-shrink-0 mt-1">
              <div class="w-8 h-8 rounded-full flex items-center justify-center" :class="notif.data.bg_color || 'bg-gray-100'">
                <!-- Conditional Icon based on notif.data.icon could go here, fallback to a bell -->
                <svg v-if="notif.data.icon === 'document-text'" class="w-4 h-4" :class="notif.data.color || 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <svg v-else-if="notif.data.icon === 'calendar'" class="w-4 h-4" :class="notif.data.color || 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <svg v-else-if="notif.data.icon === 'currency-dollar'" class="w-4 h-4" :class="notif.data.color || 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <svg v-else class="w-4 h-4" :class="notif.data.color || 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
              </div>
            </div>
            
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-gray-900 group-hover:text-blue-600 truncate">{{ notif.data.title }}</p>
              <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ notif.data.message }}</p>
              <p class="text-[10px] text-gray-400 mt-1 font-medium">{{ new Date(notif.created_at).toLocaleString('vi-VN') }}</p>
            </div>
            
            <div class="flex-shrink-0 flex items-center">
               <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 text-center">
        <!-- Optional see all link -->
        <span class="text-xs text-gray-400 font-medium">CMS Notifications</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const isOpen = ref(false);

const notifications = computed(() => page.props.auth?.user?.unread_notifications || []);
const unreadCount = computed(() => page.props.auth?.user?.unread_notifications_count || 0);

const handleNotificationClick = (notif) => {
    // Mark as read API call
    router.post(route('notifications.read', notif.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
            if (notif.data.action_url) {
                router.visit(notif.data.action_url);
            }
        }
    });
};

const markAllAsRead = () => {
    router.post(route('notifications.mark-all-read'), {}, {
        preserveScroll: true,
        onSuccess: () => {
             isOpen.value = false;
        }
    });
};
</script>
