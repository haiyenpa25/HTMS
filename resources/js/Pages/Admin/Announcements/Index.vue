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
                <button @click="showCreateSlide = true" class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Đăng bản tin mới
                </button>
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

        <!-- Create Announcement Slide Over -->
        <SlideOver v-model="showCreateSlide" title="Soạn Bản Tin Mới" description="Tạo và gửi bản tin đến các đối tượng trong hệ thống." size="lg">
            <form id="create-announcement-form" @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-black text-gray-700 mb-2 uppercase tracking-wide">Tiêu đề Bản tin</label>
                    <input type="text" v-model="form.title" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition px-4 py-3" placeholder="Nhập tiêu đề ngắn gọn...">
                    <div v-if="form.errors.title" class="text-red-500 text-xs font-medium mt-1">{{ form.errors.title }}</div>
                </div>

                <div>
                    <label class="block text-sm font-black text-gray-700 mb-2 uppercase tracking-wide">Nội dung chi tiết</label>
                    <div class="border border-gray-200 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition shadow-sm">
                        <QuillEditor v-model:content="form.content" contentType="html" theme="snow" class="bg-white min-h-[200px]" placeholder="Nhập nội dung bản tin (soạn thảo có định dạng)" />
                    </div>
                    <div v-if="form.errors.content" class="text-red-500 text-xs font-medium mt-1">{{ form.errors.content }}</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-5 bg-gray-50/50 rounded-xl border border-gray-100">
                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2 uppercase tracking-wide">Phạm vi gửi</label>
                        <select v-model="form.scope_type" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-medium">
                            <option value="global">Toàn Hệ thống (Global)</option>
                            <option value="department">Riêng theo Ban ngành</option>
                        </select>
                    </div>
                    <div v-if="form.scope_type === 'department'">
                        <label class="block text-sm font-black text-gray-700 mb-2 uppercase tracking-wide">Chọn Ban ngành</label>
                        <select v-model="form.scope_id" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-medium">
                            <option value="" disabled>-- Chọn Ban ngành --</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                        </select>
                        <div v-if="form.errors.scope_id" class="text-red-500 text-xs font-medium mt-1">{{ form.errors.scope_id }}</div>
                    </div>
                </div>
            </form>
            <template #footer>
                <div class="flex justify-end gap-3 w-full">
                    <button type="button" @click="showCreateSlide = false" class="px-5 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-xl font-bold hover:bg-gray-50 transition shadow-sm">Hủy bỏ</button>
                    <button type="submit" form="create-announcement-form" :disabled="form.processing" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-sm flex items-center justify-center min-w-[120px]" :class="{ 'opacity-75 cursor-not-allowed': form.processing }">
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Gửi Bản Tin
                    </button>
                </div>
            </template>
        </SlideOver>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

defineProps({
    announcements: Object,
    departments: Array
});

const showCreateSlide = ref(false);

const form = useForm({
    title: '',
    content: '',
    scope_type: 'global',
    scope_id: '',
    expires_at: null,
});

const submit = () => {
    form.post(route('admin.announcements.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateSlide.value = false;
            form.reset();
        }
    });
};
</script>
