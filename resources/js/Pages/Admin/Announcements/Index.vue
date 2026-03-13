<template>
    <Head title="Quản lý Bản Tin" />

    <AuthenticatedLayout>
        <template #header>
            Quản lý Bản Tin Hệ Thống
        </template>

        <div class="max-w-7xl mx-auto py-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-black text-gray-800 tracking-tight">Danh sách Bản tin</h1>
                    <p class="text-sm text-gray-500 mt-1">Gửi thông báo đến Toàn hội thánh hoặc từng ban ngành cụ thể</p>
                </div>
                <Link :href="route('admin.announcements.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Đăng bản tin mới
                </Link>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100/50">
                            <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-wider">Tiêu đề</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-wider">Phạm vi</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-wider">Mô tả ngắn</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                            <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-wider text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in announcements.data" :key="item.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-800">{{ item.title }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold leading-5 shadow-sm"
                                      :class="item.scope_type === 'global' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'">
                                    {{ item.scope_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ item.content }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-500">{{ item.created_at }}</td>
                            <td class="px-6 py-4 text-right">
                                <Link as="button" method="delete" :href="route('admin.announcements.destroy', item.id)" 
                                      class="text-red-500 hover:text-red-700 font-bold text-sm bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors"
               preserve-scroll>
                                    Xóa
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="announcements.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">Chưa có bản tin nào.</td>
                        </tr>
                    </tbody>
                </table>
                <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100" v-if="announcements.links.length > 3">
                    <div class="flex flex-wrap -mb-1">
                        <template v-for="(link, p) in announcements.links" :key="p">
                            <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-2 text-sm leading-4 text-gray-400 border rounded font-medium" v-html="link.label" />
                            <Link v-else class="mr-1 mb-1 px-4 py-2 text-sm leading-4 border rounded font-medium focus:border-indigo-500 focus:text-indigo-500 transition-colors"
                                :class="{ 'bg-indigo-600 text-white border-transparent': link.active, 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200': !link.active }" 
                                :href="link.url" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    announcements: Object
});
</script>
