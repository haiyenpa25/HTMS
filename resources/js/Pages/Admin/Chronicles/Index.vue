<script setup>
import { ref, watch, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SlideOver from '@/Components/SlideOver.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { debounce } from 'lodash';

const props = defineProps({
    entries: Object,
    filters: Object,
    stats: Object,
    availableDepartments: { type: Array, default: () => [] },
    isPortal: { type: Boolean, default: false },
    department: { type: Object, default: null },
    routePrefix: { type: String, default: 'admin.chronicles.' },
    portalType: { type: String, default: 'ministry' }
});

// -- Filtering State
const searchForm = useForm({
    search: props.filters.search || '',
    category: props.filters.category || '',
    type: props.filters.type || '',
    department_id: props.filters.department_id || '',
});

// Auto-submit search when typed
watch(() => searchForm, debounce(() => {
    router.get(route(props.routePrefix + 'index'), {
        search: searchForm.search,
        category: searchForm.category,
        type: searchForm.type,
        department_id: searchForm.department_id,
    }, { preserveState: true, preserveScroll: true });
}, 300), { deep: true });

// -- Modal State
const showModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    category: 'history',
    title: '',
    description: '',
    occurred_at: new Date().toISOString().split('T')[0],
    ended_at: '',
    subject_type: '',
    subject_id: '',
    department_id: null,
    meta_data: {}
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    showModal.value = true;
};

const openEditModal = (entry) => {
    if (entry.type === 'auto') {
        alert('Dữ liệu tự động không thể chỉnh sửa.');
        return;
    }
    isEditing.value = true;
    form.id = entry.id;
    form.category = entry.category;
    form.title = entry.title;
    form.description = entry.description || '';
    form.occurred_at = entry.occurred_at;
    form.ended_at = entry.ended_at || '';
    form.subject_type = entry.subject_type || '';
    form.subject_id = entry.subject_id || '';
    form.department_id = entry.department_id || null;
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route(props.routePrefix + 'update', form.id), {
            onSuccess: () => { showModal.value = false; form.reset(); }
        });
    } else {
        form.post(route(props.routePrefix + 'store'), {
            onSuccess: () => { showModal.value = false; form.reset(); }
        });
    }
};

const deleteEntry = (entry) => {
    if (entry.type === 'auto') {
        alert('Dữ liệu tự động không thể xóa.');
        return;
    }
    if (confirm('Bạn có chắc muốn xóa lịch sử này? Thao tác này không thể hoàn tác.')) {
        router.delete(route(props.routePrefix + 'destroy', entry.id));
    }
};

// --- Timeline Visual Helpers
const getCategoryIcon = (cat) => {
    const map = {
        history: '🏛️',
        leadership: '👑',
        wedding: '💍',
        funeral: '🕊️',
        custom: '📌'
    };
    return map[cat] || '📋';
};

const getCategoryColor = (cat) => {
    const map = {
        history: 'bg-indigo-100 text-indigo-700 border-indigo-200',
        leadership: 'bg-amber-100 text-amber-700 border-amber-200',
        wedding: 'bg-pink-100 text-pink-700 border-pink-200',
        funeral: 'bg-slate-100 text-slate-700 border-slate-200',
        custom: 'bg-blue-100 text-blue-700 border-blue-200'
    };
    return map[cat] || 'bg-gray-100 text-gray-700 border-gray-200';
};
</script>

<template>
    <Head title="Sổ Tay HT (Biên Niên Sử)" />

    <component :is="isPortal ? PortalLayout : AuthenticatedLayout" :department="department" :availableDepartments="availableDepartments" :portalType="props.portalType">
        <template #header>
            Quản lý Sổ Tay HT (Biên Niên Sử)
        </template>

        <div class="max-w-7xl mx-auto pb-12">
            
            <!-- Header Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 mt-2">
                <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-2">Tổng Sự Kiện</p>
                    <div class="flex items-end justify-between">
                        <h3 class="text-3xl font-black text-slate-800">{{ stats.total }}</h3>
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg></div>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-xl shadow-sm border border-indigo-100 flex flex-col justify-between">
                    <p class="text-xs text-indigo-500 font-bold uppercase tracking-wider mb-2">Sự Kiện Lịch Sử</p>
                    <div class="flex items-end justify-between">
                        <h3 class="text-3xl font-black text-indigo-900">{{ stats.history }}</h3>
                        <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl shadow-sm border border-amber-100 flex flex-col justify-between">
                    <p class="text-xs text-amber-500 font-bold uppercase tracking-wider mb-2">Nhiệm Kỳ / Nhân Sự</p>
                    <div class="flex items-end justify-between">
                        <h3 class="text-3xl font-black text-amber-900">{{ stats.leadership }}</h3>
                        <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg></div>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl shadow-sm border border-emerald-100 flex flex-col justify-between">
                    <p class="text-xs text-emerald-500 font-bold uppercase tracking-wider mb-2">Thành Hôn, Khác</p>
                    <div class="flex items-end justify-between">
                        <h3 class="text-3xl font-black text-emerald-900">{{ stats.other }}</h3>
                        <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg></div>
                    </div>
                </div>
            </div>

            <!-- Header Controls -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Biên Niên Sử</h2>
                    <p class="text-slate-500 text-sm mt-1">Lưu trữ các cột mốc và sự kiện trọng đại của Hội Thánh theo dòng thời gian.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Filters -->
                    <select v-if="availableDepartments && availableDepartments.length > 0" v-model="searchForm.department_id" class="border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Tất cả Ban Ngành</option>
                        <option v-for="dept in availableDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                    </select>

                    <select v-model="searchForm.type" class="border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Tất cả Nguồn</option>
                        <option value="auto">Hệ thống tạo (Auto)</option>
                        <option value="manual">Nhập thủ công (Manual)</option>
                    </select>

                    <select v-model="searchForm.category" class="border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Tất cả Sự kiện</option>
                        <option value="history">Lịch sử HT</option>
                        <option value="leadership">Nhiệm kỳ Nhân sự</option>
                        <option value="wedding">Lễ Thành Hôn</option>
                        <option value="funeral">Tang Lễ</option>
                        <option value="custom">Khác</option>
                    </select>

                    <TextInput
                        class="w-64"
                        v-model="searchForm.search"
                        type="text"
                        placeholder="Tìm sự kiện..."
                    />

                    <PrimaryButton @click="openCreateModal" class="shrink-0 gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Thêm Sự Kiện
                    </PrimaryButton>
                </div>
            </div>

            <!-- Timeline Main View -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 overflow-hidden relative">
                
                <div class="absolute left-8 md:left-1/2 top-8 bottom-8 w-0.5 bg-slate-100 transform -translate-x-1/2 hidden md:block"></div>
                
                <div v-if="entries.data.length === 0" class="text-center py-12 text-slate-500">
                    Chưa có sự kiện nào được ghi nhận.
                </div>

                <div class="space-y-8 relative">
                    <div v-for="(entry, index) in entries.data" :key="entry.id" 
                         class="relative flex flex-col md:flex-row items-start"
                         :class="index % 2 === 0 ? 'md:flex-row-reverse' : ''">
                        
                        <!-- Center Dot -->
                        <div class="absolute left-0 md:left-1/2 transform -translate-x-1/2 flex items-center justify-center w-8 h-8 rounded-full border-4 border-white shadow-sm z-10"
                             :class="entry.type === 'auto' ? 'bg-indigo-500' : 'bg-emerald-500'">
                           <span class="text-white text-xs" v-if="entry.type==='auto'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                           </span>
                           <span class="text-white text-xs" v-else>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                           </span>
                        </div>

                        <!-- Spacer for alternating sides -->
                        <div class="hidden md:block w-1/2"></div>

                        <!-- Content Card -->
                        <div class="w-full md:w-[45%] pl-10 md:pl-0" :class="index % 2 === 0 ? 'md:pr-10 text-left md:text-right' : 'md:pl-10'">
                            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow group relative">
                                
                                <div class="flex items-center gap-2 mb-2" :class="index % 2 === 0 ? 'md:justify-end' : ''">
                                    <span class="text-xl">{{ getCategoryIcon(entry.category) }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border" :class="getCategoryColor(entry.category)">
                                        {{ entry.category.toUpperCase() }}
                                    </span>
                                    <span v-if="entry.department" class="px-2.5 py-0.5 rounded-full text-xs font-bold border bg-teal-50 text-teal-700 border-teal-200">
                                        {{ entry.department.name }}
                                    </span>
                                    <span class="text-sm font-bold text-slate-500 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ entry.occurred_at }}
                                        <template v-if="entry.ended_at">
                                            &rarr; {{ entry.ended_at }}
                                        </template>
                                    </span>
                                </div>

                                <h3 class="text-lg font-bold text-slate-800 leading-tight mb-2">{{ entry.title }}</h3>
                                
                                <p class="text-slate-600 text-sm whitespace-pre-wrap">{{ entry.description }}</p>

                                <div v-if="entry.type === 'manual'" class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <span class="text-xs text-slate-400">Tạo bởi: {{ entry.creator?.name || '---' }}</span>
                                    <div class="flex gap-2">
                                        <button @click="openEditModal(entry)" class="text-blue-600 hover:text-blue-800 text-sm p-1">Sửa</button>
                                        <button @click="deleteEntry(entry)" class="text-red-600 hover:text-red-800 text-sm p-1">Xóa</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
            
            <div class="mt-6 flex justify-end">
                <Pagination :links="entries.links" />
            </div>

        </div>
    </component>

    <!-- Create/Edit SlideOver -->
    <SlideOver v-model="showModal" :title="isEditing ? '📝 Chỉnh Sửa Sự Kiện Lịch Sử' : '✍️ Ghi Nhận Lịch Sử Mới'" size="md">
        <div class="px-2">
            <form id="chronicleForm" @submit.prevent="submitForm" class="space-y-5">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Phân Loại Sổ Tay" />
                        <select v-model="form.category" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="history">🏛️ Lễ Lớn / Lịch sử HT</option>
                            <option value="leadership">👑 Nhiệm kỳ/Nhân sự</option>
                            <option value="wedding">💍 Lễ Thành Hôn</option>
                            <option value="funeral">🕊️ Tang Lễ</option>
                            <option value="custom">📌 Khác</option>
                        </select>
                        <InputError :message="form.errors.category" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel value="Trạng Thái Nguồn" />
                        <div class="mt-1 flex items-center gap-2 h-10 px-3 bg-gray-50 text-gray-500 rounded-lg border border-gray-200">
                            <span>Nhập Thủ Công (Manual)</span>
                        </div>
                    </div>
                </div>

                <div>
                    <InputLabel value="Tiêu Đề / Tên Sự Kiện *" />
                    <TextInput
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        placeholder="VD: Cung hiến nhà thờ mới..."
                    />
                    <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel value="Ngày Bắt Đầu / Diễn Ra *" />
                        <TextInput
                            v-model="form.occurred_at"
                            type="date"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.occurred_at" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel value="Ngày Kết Thúc (Tùy chọn cho Nhiệm kỳ)" />
                        <TextInput
                            v-model="form.ended_at"
                            type="date"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.ended_at" class="mt-2" />
                    </div>
                </div>

                <div>
                    <InputLabel value="Mô Tả Chi Tiết" />
                    <textarea
                        v-model="form.description"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 min-h-[120px]"
                        placeholder="Ghi chú thêm nội dung, số liệu, nhân vật tham dự..."
                    ></textarea>
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <div v-if="availableDepartments && availableDepartments.length > 0 && !isPortal">
                    <InputLabel value="Quyền Sở Hữu (Ban Ngành)" />
                    <select v-model="form.department_id" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option :value="null">Chung (Toàn Hội Thánh)</option>
                        <option v-for="dept in availableDepartments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                    </select>
                    <InputError :message="form.errors.department_id" class="mt-2" />
                </div>

                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                    <h4 class="text-sm font-bold text-indigo-800 mb-2">Liên Kết Dữ Liệu (Tagging Tùy Chọn)</h4>
                    <p class="text-xs text-indigo-600 mb-3">Bạn có thể liên kết sự kiện này với một Tín hữu hoặc User cụ thể trong hệ thống để khi vào Profile của họ dòng lịch sử này sẽ xuất hiện.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Loại Đối Tượng" class="text-indigo-900" />
                            <select v-model="form.subject_type" class="mt-1 block w-full border-indigo-200 rounded-lg text-sm bg-white">
                                <option value="">--- Không liên kết ---</option>
                                <option value="App\Models\Member">Tín Hữu (Hồ Sơ)</option>
                                <option value="App\Models\User">Tài Khoản Hành Chính</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="ID Máy Chủ (Nhập số)" class="text-indigo-900" />
                            <TextInput
                                v-model="form.subject_id"
                                type="number"
                                class="mt-1 block w-full border-indigo-200"
                                placeholder="ID của User/Member..."
                            />
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <template #footer>
            <div class="flex justify-end gap-3 w-full">
                <SecondaryButton type="button" @click="showModal = false">Hủy Bỏ</SecondaryButton>
                <PrimaryButton form="chronicleForm" type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ isEditing ? 'Cập Nhật Lịch Sử' : 'Lưu Sổ Tay' }}
                </PrimaryButton>
            </div>
        </template>
    </SlideOver>
</template>
