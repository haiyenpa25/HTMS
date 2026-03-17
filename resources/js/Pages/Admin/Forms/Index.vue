<script setup>
import { ref } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    templates: Array
});

const isModalOpen = ref(false);
const editingForm = ref(null);
const fileInput = ref(null);

const form = useForm({
    title: '',
    description: '',
    file: null,
});

const openModal = (template = null) => {
    editingForm.value = template;
    if (template) {
        form.title = template.title;
        form.description = template.description || '';
        form.file = null; // Can't prepopulate file input
    } else {
        form.reset();
        form.clearErrors();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
    editingForm.value = null;
    if (fileInput.value) {
        fileInput.value.value = null;
    }
};

const handleFileChange = (e) => {
    form.file = e.target.files[0];
};

const submit = () => {
    if (editingForm.value) {
        // Use POST with _method = PUT for file uploads in Laravel
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route('admin.forms-manager.update', editingForm.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.forms-manager.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDelete = (template) => {
    if (confirm('Bạn có chắc chắn muốn xóa biểu mẫu này? File đính kèm cũng sẽ bị xóa khỏi hệ thống.')) {
        router.delete(route('admin.forms-manager.destroy', template.id), {
            preserveScroll: true,
        });
    }
};

const formatSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(date);
};
</script>

<template>
    <Head title="Quản lý Biểu mẫu" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Quản lý Biểu mẫu Diễn đàn</h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header Control -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 px-4 sm:px-0">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-slate-900 border-l-4 border-slate-800 pl-3">Kho Biểu Mẫu Chuẩn</h1>
                        <p class="text-sm text-gray-500 mt-2 pl-4">Quản lý và tải lên các file mẫu (đơn từ, báo cáo...) cho ban ngành tải về sử dụng.</p>
                    </div>
                    <button @click="openModal()" class="inline-flex items-center px-4 py-2 bg-slate-800 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700 active:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tải lên Biểu Mẫu
                    </button>
                </div>

                <!-- Main Content / Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên biểu mẫu</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Mô tả</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">File đính kèm</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày đăng</th>
                                    <th scope="col" class="relative px-6 py-4"><span class="sr-only">Hành động</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="template in templates" :key="template.id" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="text-sm font-bold text-slate-800">{{ template.title }}</div>
                                    </td>
                                    <td class="px-6 py-5 hidden md:table-cell">
                                        <div class="text-sm text-gray-500 line-clamp-2 max-w-sm">{{ template.description || '--' }}</div>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-gray-400 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            <div class="flex flex-col">
                                                <span class="text-sm text-slate-700 truncate max-w-[150px] lg:max-w-[200px]">{{ template.file_name }}</span>
                                                <span class="text-xs text-gray-500 mt-0.5">{{ formatSize(template.file_size) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(template.created_at) }}
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end items-center space-x-2">
                                            <a :href="route('admin.forms-manager.download', template.id)" target="_blank" class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 p-2 rounded-lg transition-colors border border-transparent hover:border-emerald-200" title="Tải xuống">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </a>
                                            <button @click="openModal(template)" class="text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-200 p-2 rounded-lg transition-colors border border-transparent hover:border-slate-300" title="Chỉnh sửa">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </button>
                                            <button @click="confirmDelete(template)" class="text-red-500 hover:text-red-800 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors border border-transparent hover:border-red-200" title="Xóa">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="templates.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center text-gray-500">
                                        <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="mt-4 font-bold text-gray-900 text-lg">Chưa có biểu mẫu nào</p>
                                        <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">Thiết lập các văn bản mẫu để thành viên có thể tải về. Bấm nút "Tải lên Biểu Mẫu" để bắt đầu.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Thêm/Sửa Biểu Mẫu -->
        <Modal :show="isModalOpen" @close="closeModal" maxWidth="md">
            <div class="px-6 py-5">
                <h3 class="text-xl font-bold text-slate-800 border-b pb-4 mb-5 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                    {{ editingForm ? 'Cập nhật Biểu Mẫu' : 'Thêm Biểu Mẫu Mới' }}
                </h3>
                
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="title" value="Tên biểu mẫu *" class="font-bold text-gray-700" />
                        <TextInput id="title" type="text" class="mt-1 block w-full py-2" v-model="form.title" required autofocus placeholder="VD: Đơn xin làm Báp-tem" />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Ghi chú về biểu mẫu (Không bắt buộc)" class="font-bold text-gray-700" />
                        <textarea id="description" v-model="form.description" class="mt-1 block w-full border-gray-300 focus:border-slate-500 focus:ring-slate-500 rounded-lg shadow-sm" rows="3" placeholder="Hướng dẫn cho tín hữu cách ghi thông tin..."></textarea>
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>

                    <div>
                        <InputLabel for="file" value="File đính kèm (Word/PDF/Excel) *" class="font-bold text-gray-700" />
                        <div class="mt-2 flex items-center justify-center w-full">
                            <label for="file" class="flex flex-col items-center justify-center w-full h-36 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-slate-50 transition-colors" :class="{'border-slate-400 bg-slate-50': form.file}">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg v-if="!form.file && (!editingForm || !editingForm.file_name)" class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    <svg v-else class="w-10 h-10 mb-3 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    
                                    <p v-if="form.file" class="mb-2 text-sm text-slate-800 font-bold truncate px-6 max-w-sm">{{ form.file.name }}</p>
                                    <p v-else-if="editingForm" class="mb-2 text-sm text-gray-600 font-medium px-6 text-center">Đã lưu: <span class="font-bold text-slate-800">{{ editingForm.file_name }}</span><br/><span class="text-xs text-gray-400">Chọn lại để tải file mới lên</span></p>
                                    <p v-else class="mb-2 text-sm text-gray-600 font-medium px-4"><span class="font-bold text-slate-700">Trỏ vào đây</span> để tải file từ máy tính</p>
                                    
                                    <p v-if="!form.file" class="text-xs text-gray-400 font-medium tracking-wide">Tối đa lưu trữ 20MB</p>
                                </div>
                                <input id="file" type="file" ref="fileInput" class="hidden" @change="handleFileChange" :required="!editingForm" />
                            </label>
                        </div>
                        <InputError class="mt-2" :message="form.errors.file" />
                    </div>

                    <div class="pt-5 flex items-center justify-end space-x-3 border-t">
                        <SecondaryButton @click="closeModal" type="button" class="py-2.5">Hủy bỏ</SecondaryButton>
                        <PrimaryButton :class="{ 'opacity-25 bg-slate-400': form.processing, 'bg-slate-800 hover:bg-slate-700': !form.processing }" :disabled="form.processing" class="py-2.5">
                            {{ editingForm ? 'Lưu thay đổi' : 'Tải lên hoàn tất' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
