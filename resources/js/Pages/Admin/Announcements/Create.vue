<template>
    <Head title="Soạn Bản Tin" />

    <AuthenticatedLayout>
        <template #header>
            Soạn Bản Tin Mới
        </template>

        <div class="max-w-4xl mx-auto py-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-6 md:p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2 uppercase tracking-wide">Tiêu đề Bản tin</label>
                        <input type="text" v-model="form.title" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition px-4 py-3" placeholder="Nhập tiêu đề ngắn gọn...">
                        <div v-if="form.errors.title" class="text-red-500 text-xs font-medium mt-1">{{ form.errors.title }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-2 uppercase tracking-wide">Nội dung chi tiết</label>
                        <textarea v-model="form.content" rows="6" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition px-4 py-3" placeholder="Nhập nội dung bản tin (bạn có thể sử dụng các thẻ HTML cơ bản hoặc xuống dòng)"></textarea>
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

                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                        <Link :href="route('admin.announcements.index')" class="px-5 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-xl font-bold hover:bg-gray-50 transition shadow-sm">Hủy bỏ</Link>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-sm flex items-center justify-center min-w-[120px]" :class="{ 'opacity-75 cursor-not-allowed': form.processing }">
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Gửi Bản Tin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    departments: Array
});

const form = useForm({
    title: '',
    content: '',
    scope_type: 'global',
    scope_id: '',
    expires_at: null,
});

const submit = () => {
    form.post(route('admin.announcements.store'));
};
</script>
