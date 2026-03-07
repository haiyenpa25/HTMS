<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    features: Array,
    departments: Array,
    systemConfig: Array,
});

// ── Active Feature ─────────────────────────────────────────────────────────────
const activeFeatureId = ref(props.features[0]?.id || null);
const activeFeature = computed(() =>
    props.features.find(f => f.id === activeFeatureId.value)
);

const isCreating = ref(false);

// ── Current config for active feature (parsed) ────────────────────────────────
const getCurrentConfig = (featureId) => {
    const configs = (props.systemConfig || []).filter(c => c.feature_id === featureId);
    // Check global first
    const global = configs.find(c => c.scope === 'global' || (c.block_type === null && c.department_id === null));
    if (global) return { scope: 'global', block_type: null, is_active: global.is_active, department_ids: [] };
    
    // Check block level (null dept, has block_type)
    const blockConfigs = configs.filter(c => c.department_id === null && c.block_type !== null);
    if (blockConfigs.length > 0) {
        return { scope: 'block', block_type: blockConfigs[0].block_type, is_active: blockConfigs[0].is_active, department_ids: [] };
    }

    // Check specific
    const specific = configs.filter(c => c.department_id !== null);
    if (specific.length > 0) {
        return {
            scope: 'specific',
            block_type: specific[0].block_type,
            is_active: true,
            department_ids: specific.map(c => c.department_id),
        };
    }

    return { scope: 'block', block_type: 'activities', is_active: true, department_ids: [] };
};

// ── Form State ─────────────────────────────────────────────────────────────────
const form = ref({ scope: 'block', block_type: 'activities', is_active: true, department_ids: [] });

const blockTypes = [
    { id: 'activities', name: 'Ban Sinh Hoạt', icon: '🎯', desc: 'Điểm danh, báo cáo, thành viên v.v.' },
    { id: 'ministry',   name: 'Ban Mục Vụ',   icon: '⛪', desc: 'Giáo dục, lớp học, tiền dâng v.v.' },
    { id: 'leadership', name: 'Ban Chấp Sự',  icon: '🛡', desc: 'Quản trị hội đồng chấp sự.' },
];

const availableDepts = computed(() =>
    props.departments.filter(d => d.block === form.value.block_type)
);

const selectFeature = (id) => {
    isCreating.value = false;
    activeFeatureId.value = id;
    const config = getCurrentConfig(id);
    form.value = { ...config };
};

// Auto-init
if (activeFeatureId.value) selectFeature(activeFeatureId.value);

// ── Toast ──────────────────────────────────────────────────────────────────────
const toastMsg = ref('');
const toastError = ref(false);
const showToast = (msg, err = false) => {
    toastMsg.value = msg; toastError.value = err;
    setTimeout(() => toastMsg.value = '', 3000);
};

// ── Watch for prop updates (from router.reload) ───────────────────────────────
watch(() => props.systemConfig, () => {
    if (activeFeatureId.value && !isCreating.value) {
        selectFeature(activeFeatureId.value);
    }
}, { deep: true });

// ── Save ───────────────────────────────────────────────────────────────────────
const isSaving = ref(false);
const saveConfig = async () => {
    if (!activeFeatureId.value) return;
    isSaving.value = true;
    try {
        await axios.post(route('admin.features.assign'), {
            feature_id:     activeFeatureId.value,
            scope:          form.value.scope,
            block_type:     form.value.scope === 'global' ? null : form.value.block_type,
            is_active:      form.value.is_active,
            department_ids: form.value.scope === 'specific' ? form.value.department_ids : [],
        });
        // Reload Inertia page props so systemConfig reflects new save
        router.reload({ only: ['systemConfig'], onSuccess: () => {
            showToast('✅ Đã lưu thành công!');
        }});
    } catch (err) {
        showToast('❌ Lỗi khi lưu: ' + (err?.response?.data?.message || err.message), true);
    } finally {
        isSaving.value = false;
    }
};

// ── New Feature Form ───────────────────────────────────────────────────────────
const newFeatureForm = useForm({
    name: '', slug: '', icon: '📦', portal_type: 'activities', description: '',
});
const submitNewFeature = () => {
    newFeatureForm.post(route('admin.features.store'), {
        preserveScroll: true,
        onSuccess: () => { isCreating.value = false; showToast('✅ Đã tạo tính năng mới!'); newFeatureForm.reset(); },
        onError: () => showToast('❌ Lỗi! Kiểm tra lại Form', true)
    });
};

const scopeLabel = (scope) => ({ global: '🌐 Toàn Bộ', block: '📋 Theo Loại Ban', specific: '🎯 Ban Cụ Thể' })[scope] ?? scope;
</script>

<template>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col md:flex-row min-h-[600px] w-full">

    <!-- Toast -->
    <div v-if="toastMsg" :class="toastError ? 'bg-red-500' : 'bg-emerald-500'"
        class="fixed bottom-4 right-4 text-white px-6 py-3 rounded-xl shadow-lg z-50 font-bold">
        {{ toastMsg }}
    </div>

    <!-- Sidebar -->
    <div class="w-full md:w-1/3 border-r border-gray-100 bg-gray-50 flex flex-col">
        <div class="px-4 py-4 bg-white border-b border-gray-100 flex items-center justify-between shadow-sm">
            <span class="font-black text-gray-800">Các Tính Năng (Modules)</span>
            <button @click="isCreating = true"
                class="text-[11px] bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white px-3 py-1.5 rounded-full font-black uppercase tracking-wider transition-colors">
                + Thêm
            </button>
        </div>
        <div class="overflow-y-auto flex-1 p-3 space-y-1.5">
            <button v-for="f in features" :key="f.id" @click="selectFeature(f.id)"
                :class="['w-full text-left px-4 py-3 rounded-xl flex items-center gap-3 transition-all border-2',
                    activeFeatureId === f.id && !isCreating
                        ? 'bg-white border-indigo-400 text-indigo-700 shadow-md'
                        : 'bg-white border-transparent text-gray-600 shadow-sm hover:border-gray-200']">
                <span class="text-2xl w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center shrink-0" v-html="f.icon || '📦'"></span>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm truncate">{{ f.name }}</div>
                    <div class="text-xs text-gray-400 font-mono truncate">{{ f.slug }}</div>
                    <!-- show current scope badge -->
                    <span v-if="activeFeatureId === f.id && !isCreating"
                        :class="['inline-flex mt-1 items-center text-[10px] font-black px-2 py-0.5 rounded-full',
                            form.scope === 'global' ? 'bg-purple-100 text-purple-700' :
                            form.scope === 'block'  ? 'bg-blue-100 text-blue-700' :
                                                       'bg-green-100 text-green-700']">
                        {{ scopeLabel(form.scope) }}
                    </span>
                </div>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full md:w-2/3 flex flex-col bg-white">

        <!-- New Feature Form -->
        <div v-if="isCreating" class="flex-1 p-6 md:p-8 overflow-y-auto">
            <h2 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">✨ Thêm Tính Năng Mới</h2>
            <form @submit.prevent="submitNewFeature" class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tên Tính Năng</label>
                    <input type="text" v-model="newFeatureForm.name" required placeholder="VD: Quản lý Quỹ..."
                        class="w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Mã (Slug)</label>
                    <input type="text" v-model="newFeatureForm.slug" required placeholder="VD: finance, attendance_pro..."
                        class="w-full border-gray-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm text-indigo-600">
                    <div v-if="newFeatureForm.errors.slug" class="text-red-500 text-xs mt-1">{{ newFeatureForm.errors.slug }}</div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Icon (Emoji)</label>
                        <input type="text" v-model="newFeatureForm.icon" placeholder="📦" class="w-full border-gray-200 rounded-xl focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Portal Mặc Định</label>
                        <select v-model="newFeatureForm.portal_type" class="w-full border-gray-200 rounded-xl focus:ring-indigo-500">
                            <option value="activities">🎯 Sinh Hoạt</option>
                            <option value="ministry">⛪ Mục Vụ</option>
                            <option value="leadership">🛡 Chấp Sự</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Mô tả</label>
                    <textarea v-model="newFeatureForm.description" rows="2" class="w-full border-gray-200 rounded-xl text-sm" placeholder="Mô tả công dụng..."></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="isCreating = false" class="px-5 py-2.5 rounded-xl font-bold text-gray-600 hover:bg-gray-100">Hủy</button>
                    <button type="submit" :disabled="newFeatureForm.processing"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-sm disabled:opacity-50">
                        Thêm Vào Hệ Thống
                    </button>
                </div>
            </form>
        </div>

        <!-- Feature Config -->
        <div v-else-if="activeFeature" class="flex-1 flex flex-col relative">
            <div class="flex-1 overflow-y-auto p-6 md:p-8 pb-28">

                <!-- Feature Header -->
                <div class="mb-8">
                    <h2 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                        <span v-html="activeFeature.icon" class="text-3xl"></span>
                        {{ activeFeature.name }}
                    </h2>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md text-xs font-mono font-bold">{{ activeFeature.slug }}</span>
                        <span class="text-sm text-gray-400">{{ activeFeature.description || 'Chưa có mô tả' }}</span>
                    </div>
                </div>

                <!-- STEP 1: Choose Scope -->
                <div class="mb-8">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">Bước 1 — Phạm Vi Áp Dụng</p>
                    <div class="grid grid-cols-1 gap-3">

                        <!-- Global -->
                        <label class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                            :class="form.scope === 'global' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-200'">
                            <input type="radio" v-model="form.scope" value="global" class="w-5 h-5 text-purple-600">
                            <div>
                                <p class="font-black text-gray-900">🌐 Tất Cả Ban Ngành</p>
                                <p class="text-xs text-gray-500 mt-0.5">Tính năng hiển thị trên portal của <strong>mọi ban ngành</strong> (Sinh Hoạt, Mục Vụ, Chấp Sự).</p>
                            </div>
                        </label>

                        <!-- Block -->
                        <label class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                            :class="form.scope === 'block' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-200'">
                            <input type="radio" v-model="form.scope" value="block" class="w-5 h-5 text-blue-600">
                            <div>
                                <p class="font-black text-gray-900">📋 Theo Loại Ban Ngành</p>
                                <p class="text-xs text-gray-500 mt-0.5">Tính năng hiển thị mặc định cho <strong>tất cả ban trong một loại</strong> (VD: Tất cả ban Sinh Hoạt).</p>
                            </div>
                        </label>

                        <!-- Specific -->
                        <label class="flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                            :class="form.scope === 'specific' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-200'">
                            <input type="radio" v-model="form.scope" value="specific" class="w-5 h-5 text-green-600">
                            <div>
                                <p class="font-black text-gray-900">🎯 Ban Ngành Cụ Thể</p>
                                <p class="text-xs text-gray-500 mt-0.5">Chỉ hiển thị cho <strong>những ban được chọn bên dưới</strong>. Các ban khác không thấy tính năng này.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- STEP 2: Block selector (for block or specific scope) -->
                <div v-if="form.scope !== 'global'" class="mb-8">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">Bước 2 — Chọn Loại Ban Ngành</p>
                    <div class="flex flex-wrap gap-3">
                        <button v-for="b in blockTypes" :key="b.id" @click="form.block_type = b.id; form.department_ids = []"
                            :class="['flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-black border-2 transition-all',
                                form.block_type === b.id ? 'bg-indigo-600 border-indigo-600 text-white shadow-md' : 'bg-white border-gray-200 text-gray-600 hover:border-indigo-300']">
                            <span>{{ b.icon }}</span>
                            <div class="text-left">
                                <p>{{ b.name }}</p>
                                <p v-if="form.block_type === b.id" class="text-[10px] opacity-75 font-normal">{{ b.desc }}</p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Active toggle (for global or block) -->
                <div v-if="form.scope === 'global' || form.scope === 'block'" class="mb-8">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">Bước {{ form.scope === 'global' ? '2' : '3' }} — Trạng Thái Kích Hoạt</p>
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-200 w-max">
                        <span class="text-sm font-bold text-gray-700">Trạng thái:</span>
                        <button type="button" @click="form.is_active = !form.is_active"
                            :class="form.is_active ? 'bg-emerald-500 text-white ring-4 ring-emerald-100' : 'bg-gray-200 text-gray-500'"
                            class="px-5 py-2 rounded-full text-sm font-black transition-all">
                            {{ form.is_active ? '✅ ĐANG BẬT' : '❌ ĐANG TẮT' }}
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Department list (for specific scope) -->
                <div v-if="form.scope === 'specific'" class="mb-8">
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4">
                        Bước 3 — Chọn Ban Ngành Cụ Thể
                        <span class="ml-2 bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-[10px] font-black">{{ form.department_ids.length }} đã chọn</span>
                    </p>
                    <div v-if="availableDepts.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-56 overflow-y-auto p-1">
                        <label v-for="d in availableDepts" :key="d.id"
                            class="flex items-center gap-2 p-3 rounded-xl cursor-pointer border-2 transition-all"
                            :class="form.department_ids.includes(d.id) ? 'border-green-400 bg-green-50' : 'border-gray-200 bg-white hover:border-green-300'">
                            <input type="checkbox" :value="d.id" v-model="form.department_ids" class="w-4 h-4 text-green-600 rounded">
                            <span class="text-sm font-bold text-gray-800 truncate">{{ d.name }}</span>
                        </label>
                    </div>
                    <p v-else class="text-sm text-gray-400 italic p-4 bg-gray-50 rounded-xl">Chưa có ban ngành trong loại này. Hãy chọn loại ban ở bước 2.</p>
                </div>
            </div>

            <!-- Save Bar -->
            <div class="absolute bottom-0 left-0 right-0 p-5 bg-white/95 backdrop-blur border-t border-gray-100 flex justify-end z-20">
                <button @click="saveConfig" :disabled="isSaving"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-2xl font-black shadow-lg transition-all disabled:opacity-50 flex items-center gap-2">
                    <svg v-if="isSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ isSaving ? 'Đang lưu...' : 'LƯU CẤU HÌNH' }}
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex-1 flex flex-col items-center justify-center text-gray-400 p-8 text-center">
            <div class="text-5xl mb-3">🧩</div>
            <p class="font-black text-gray-600 text-lg">Chọn tính năng bên trái để cấu hình</p>
        </div>
    </div>
</div>
</template>
