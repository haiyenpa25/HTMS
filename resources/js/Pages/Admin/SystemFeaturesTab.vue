<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    features: Array,
    departments: Array,
    systemConfig: Array,
});

// Group configs by feature_id
const configMap = computed(() => {
    const map = {};
    for (const c of (props.systemConfig || [])) {
        if (!map[c.feature_id]) map[c.feature_id] = [];
        map[c.feature_id].push(c);
    }
    return map;
});

const activeFeatureId = ref(props.features[0]?.id || null);

const activeFeature = computed(() => 
    props.features.find(f => f.id === activeFeatureId.value)
);

const isCreating = ref(false);

const newFeatureForm = useForm({
    name: '',
    slug: '',
    icon: '📦',
    portal_type: 'activities',
    description: '',
});

// Form for editing config
const form = useForm({
    feature_id: null,
    block_type: 'activities', // default
    is_active: true,
    target_mode: 'all', // 'all' or 'specific'
    specific_departments: [], // Array of department IDs
});

const blockTypes = [
    { id: 'activities', name: 'Sinh Hoạt', icon: '🎯' },
    { id: 'ministry', name: 'Mục Vụ', icon: '⛪' },
    { id: 'leadership', name: 'Chấp Sự', icon: '🛡' },
];

const availableDepartmentsForBlock = computed(() => {
    return props.departments.filter(d => d.block === form.block_type);
});

// Load existing config for the selected feature & block
const loadConfig = () => {
    const configs = configMap.value[activeFeatureId.value] || [];
    const blockConfigs = configs.filter(c => c.block_type === form.block_type);
    
    if (blockConfigs.length === 0) {
        form.target_mode = 'all';
        form.is_active = false;
        form.specific_departments = [];
        return;
    }

    const allConfig = blockConfigs.find(c => c.department_id === null);
    if (allConfig) {
        form.target_mode = 'all';
        form.is_active = allConfig.is_active;
        form.specific_departments = [];
    } else {
        form.target_mode = 'specific';
        form.is_active = true;
        form.specific_departments = blockConfigs.filter(c => c.is_active).map(c => c.department_id);
    }
};

const selectFeature = (id) => {
    isCreating.value = false;
    activeFeatureId.value = id;
    form.feature_id = id;
    loadConfig();
};

const changeBlock = (block) => {
    form.block_type = block;
    loadConfig();
};

const toastMsg = ref('');
const toastError = ref(false);
const showToast = (msg, err = false) => {
    toastMsg.value = msg;
    toastError.value = err;
    setTimeout(() => toastMsg.value = '', 3000);
};

const saveConfig = () => {
    form.feature_id = activeFeatureId.value;
    
    const payload = {
        feature_id: form.feature_id,
        block_type: form.block_type,
        target_mode: form.target_mode,
        is_active: form.is_active,
        department_ids: form.target_mode === 'specific' ? form.specific_departments : [],
    };

    axios.post(route('admin.features.assign'), payload)
        .then(() => showToast('Đã lưu cấu hình phân bổ tính năng!'))
        .catch(() => showToast('Có lỗi xảy ra', true));
};

const submitNewFeature = () => {
    newFeatureForm.post(route('admin.features.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isCreating.value = false;
            showToast('Đã tạo tính năng mới thành công!');
            newFeatureForm.reset();
            // Auto select newest feature if possible (Inertia reloads the props.features array)
            if (props.features.length) selectFeature(props.features[props.features.length - 1].id);
        },
        onError: () => {
            showToast('Lỗi! Vui lòng kiểm tra lại Form', true);
        }
    });
};

// Auto-init
if (activeFeatureId.value && !isCreating.value) selectFeature(activeFeatureId.value);

</script>

<template>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col md:flex-row min-h-[600px] w-full">
    
    <!-- Toast -->
    <div v-if="toastMsg" :class="toastError ? 'bg-red-500' : 'bg-emerald-500'" class="fixed bottom-4 right-4 text-white px-6 py-3 rounded-xl shadow-lg z-50 transition-all font-bold">
        {{ toastMsg }}
    </div>

    <!-- Sidebar: Danh sách Tính năng -->
    <div class="w-full md:w-1/3 border-r border-gray-100 bg-gray-50 flex flex-col">
        <div class="px-4 py-4 bg-white border-b border-gray-100 flex items-center justify-between shadow-sm z-10">
            <span class="font-black text-gray-800">Các Tính Năng (Modules)</span>
            <button @click="isCreating = true" class="text-[11px] bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white px-3 py-1.5 rounded-full font-black uppercase tracking-wider transition-colors shadow-sm">
                + Thêm
            </button>
        </div>
        
        <div class="overflow-y-auto flex-1 p-3 space-y-1.5">
            <button v-for="f in features" :key="f.id" @click="selectFeature(f.id)"
                :class="['w-full text-left px-4 py-3 rounded-[1rem] flex items-center gap-4 transition-all', 
                         activeFeatureId === f.id && !isCreating 
                            ? 'bg-white border-2 border-indigo-400 text-indigo-700 shadow-md ring-4 ring-indigo-50 transform scale-[1.02]' 
                            : 'bg-white border-2 border-transparent text-gray-600 shadow-sm hover:shadow hover:border-gray-200']">
                <span class="text-2xl bg-gray-50 rounded-full w-10 h-10 flex items-center justify-center shrink-0" v-html="f.icon || '📦'"></span>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm truncate">{{ f.name }}</div>
                    <div class="text-xs text-gray-400 truncate mt-0.5">Mã: {{ f.slug }}</div>
                </div>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full md:w-2/3 flex flex-col bg-white">
        
        <!-- SECTION: THÊM TÍNH NĂNG MỚI -->
        <div v-if="isCreating" class="flex-1 p-6 md:p-8 overflow-y-auto animate-fade-in">
            <h2 class="text-2xl font-black text-gray-900 mb-2 flex items-center gap-3">
                <span>✨</span> Khởi tạo Tính Năng Mới
            </h2>
            <p class="text-sm text-gray-500 mb-8 pb-6 border-b border-gray-100">Đăng ký một module mới vào hệ thống trước khi có thể phân bổ cho các ban ngành.</p>

            <form @submit.prevent="submitNewFeature" class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tên Tính Năng (Hiển thị UI)</label>
                    <input type="text" v-model="newFeatureForm.name" required placeholder="VD: Quản lý Quỹ..." 
                           class="w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                    <div v-if="newFeatureForm.errors.name" class="text-red-500 text-xs mt-1">{{ newFeatureForm.errors.name }}</div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Mã (Slug - Không được trùng)</label>
                    <input type="text" v-model="newFeatureForm.slug" required placeholder="VD: finance, attendance_pro..." 
                           class="w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm text-indigo-600">
                    <div v-if="newFeatureForm.errors.slug" class="text-red-500 text-xs mt-1">{{ newFeatureForm.errors.slug }}</div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Icon (Emoji/SVG)</label>
                        <input type="text" v-model="newFeatureForm.icon" placeholder="📦" 
                               class="w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Portal Mặc Định</label>
                        <select v-model="newFeatureForm.portal_type" 
                                class="w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="activities">Sinh Hoạt</option>
                            <option value="ministry">Mục Vụ</option>
                            <option value="deacon">Chấp Sự</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Mô tả (Tùy chọn)</label>
                    <textarea v-model="newFeatureForm.description" rows="2" 
                              class="w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                              placeholder="Mô tả công dụng của module..."></textarea>
                </div>

                <div class="pt-6 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" @click="isCreating = false" class="px-5 py-2.5 rounded-xl font-bold text-gray-600 hover:bg-gray-100 transition-colors">
                        Hủy
                    </button>
                    <button type="submit" :disabled="newFeatureForm.processing" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-sm transition-all focus:ring-2 focus:ring-indigo-500 flex items-center gap-2 disabled:opacity-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Thêm Vào Hệ Thống
                    </button>
                </div>
            </form>
        </div>

        <!-- SECTION: CẤU HÌNH TÍNH NĂNG (Gán Ban Ngành) -->
        <div v-else-if="activeFeature" class="flex-1 flex flex-col animate-fade-in relative h-full">
            <div class="flex-1 overflow-y-auto p-6 md:p-8 pb-32">
                <h2 class="text-3xl font-black text-gray-900 mb-2 flex items-center gap-3">
                    <span v-html="activeFeature.icon" class="text-4xl drop-shadow-sm"></span>
                    {{ activeFeature.name }}
                </h2>
                <div class="flex items-center gap-2 mb-8 pb-6 border-b border-gray-100">
                    <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md text-xs font-mono font-bold">{{ activeFeature.slug }}</span>
                    <span class="text-sm text-gray-500">{{ activeFeature.description || 'Chưa có mô tả' }}</span>
                </div>

                <!-- Chọn Cổng (Block) -->
                <div class="mb-8">
                    <label class="block text-sm font-black text-gray-800 mb-3 uppercase tracking-wider">1. Thuộc Khu Vực (Portal Block)</label>
                    <div class="flex flex-wrap gap-3">
                        <button v-for="b in blockTypes" :key="b.id" @click="changeBlock(b.id)"
                            :class="['px-5 py-3 rounded-xl text-sm font-black border transition-all flex items-center gap-2', 
                                    form.block_type === b.id ? 'bg-indigo-600 border-indigo-600 text-white shadow-md transform scale-[1.02]' : 'bg-white border-gray-200 text-gray-600 hover:border-indigo-300 hover:bg-indigo-50']">
                            <span>{{ b.icon }}</span>
                            {{ b.name }}
                        </button>
                    </div>
                </div>

                <!-- Chế độ áp dụng -->
                <div class="mb-4">
                    <label class="block text-sm font-black text-gray-800 mb-3 uppercase tracking-wider">2. Cấu Hình Phân Bổ Nhanh</label>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Option: All -->
                        <label class="flex items-start gap-4 p-5 bg-white rounded-2xl border-2 cursor-pointer transition-all" 
                               :class="form.target_mode === 'all' ? 'border-indigo-500 shadow-md bg-indigo-50/30' : 'border-gray-100 hover:border-indigo-200'">
                            <input type="radio" v-model="form.target_mode" value="all" class="mt-1 w-5 h-5 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <div class="font-black text-base text-gray-900">Cho Phép Toàn Bộ Khối Mặc Định</div>
                                <div class="text-sm text-gray-500 mt-1">Tính năng sẽ tự động kích hoạt cho tất cả ban ngành nằm trong khối <b>{{ blockTypes.find(b=>b.id===form.block_type)?.name }}</b>. Phù hợp cho tính năng cốt lõi.</div>
                                
                                <Transition enter-from-class="opacity-0 scale-95" enter-active-class="transition duration-150">
                                <div v-if="form.target_mode === 'all'" class="mt-4 flex items-center gap-3 bg-white p-3 rounded-xl border border-indigo-100 w-max shadow-sm">
                                    <span class="text-sm font-bold text-gray-700">Trạng thái phát hành:</span>
                                    <button type="button" @click="form.is_active = !form.is_active" 
                                            :class="form.is_active ? 'bg-emerald-500 text-white ring-4 ring-emerald-100' : 'bg-gray-100 text-gray-400 border border-gray-200'" 
                                            class="px-4 py-1.5 rounded-full text-xs font-black transition-all">
                                        {{ form.is_active ? '✅ ĐANG BẬT' : '❌ ĐANG TẮT' }}
                                    </button>
                                </div>
                                </Transition>
                            </div>
                        </label>

                        <!-- Option: Specific -->
                        <label class="flex items-start gap-4 p-5 bg-white rounded-2xl border-2 cursor-pointer transition-all"
                               :class="form.target_mode === 'specific' ? 'border-indigo-500 shadow-md bg-indigo-50/30' : 'border-gray-100 hover:border-indigo-200'">
                            <input type="radio" v-model="form.target_mode" value="specific" class="mt-1 w-5 h-5 text-indigo-600 focus:ring-indigo-500">
                            <div class="flex-1 w-full">
                                <div class="font-black text-base text-gray-900">Phân Bổ Riêng Lẻ (Tùy Chọn Khóa)</div>
                                <div class="text-sm text-gray-500 mt-1">Chỉ những Ban ngành được tích chọn dưới đây mới thấy card tính năng này ở Portal Dashboard.</div>
                                
                                <Transition enter-from-class="opacity-0 translate-y-2" enter-active-class="transition duration-200">
                                <div v-if="form.target_mode === 'specific'" class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 max-h-60 overflow-y-auto">
                                    <label v-for="d in availableDepartmentsForBlock" :key="d.id" 
                                           class="flex items-center gap-3 p-3 bg-white hover:bg-indigo-50 rounded-lg cursor-pointer border transition-colors shadow-sm" 
                                           :class="form.specific_departments.includes(d.id) ? 'border-indigo-400 ring-2 ring-indigo-100' : 'border-gray-100'">
                                        <input type="checkbox" :value="d.id" v-model="form.specific_departments" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm font-bold text-gray-800">{{ d.name }}</span>
                                    </label>
                                    <div v-if="availableDepartmentsForBlock.length === 0" class="text-sm text-gray-400 italic font-medium col-span-2 p-4 text-center">Không có tổ chức ban ngành nào trong khu vực này.</div>
                                </div>
                                </Transition>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Absolute Bottom Bar -->
            <div class="absolute bottom-0 left-0 right-0 p-6 bg-white/90 backdrop-blur-md border-t border-gray-100 flex justify-end z-20">
                <button @click="saveConfig" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3.5 rounded-2xl font-black shadow-lg shadow-indigo-200 transition-transform active:scale-95 focus:ring-4 focus:ring-indigo-500 focus:ring-offset-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    LƯU THIẾT LẬP MODULE
                </button>
            </div>
        </div>
        
        <!-- Empty State -->
        <div v-else class="flex-1 flex flex-col items-center justify-center text-gray-400 p-8 text-center bg-gray-50/50">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-3xl">🧩</div>
            <h3 class="font-black text-gray-600 text-lg mb-1">Chưa chọn Module</h3>
            <p class="text-sm text-gray-400">Chọn một cấu hình tính năng bên trái<br>để điều chỉnh cấu trúc phân bổ.</p>
        </div>
    </div>
</div>
</template>
