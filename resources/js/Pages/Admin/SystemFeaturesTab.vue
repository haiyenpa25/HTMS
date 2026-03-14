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
    const global = configs.find(c => c.scope === 'global' || (c.block_type === null && c.department_id === null));
    if (global) return { scope: 'global', block_type: null, is_active: global.is_active, department_ids: [] };
    
    const blockConfigs = configs.filter(c => c.department_id === null && c.block_type !== null);
    if (blockConfigs.length > 0) {
        return { scope: 'block', block_type: blockConfigs[0].block_type, is_active: blockConfigs[0].is_active, department_ids: [] };
    }

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
    { id: 'activities', name: 'Ban Sinh Hoạt', icon: '🎯', desc: 'Điểm danh, báo cáo, thành viên v.v.', color: 'blue' },
    { id: 'ministry',   name: 'Ban Mục Vụ',   icon: '⛪', desc: 'Giáo dục, lớp học, tiền dâng v.v.', color: 'emerald' },
    { id: 'leadership', name: 'Ban Chấp Sự',  icon: '🛡', desc: 'Quản trị hội đồng chấp sự.', color: 'amber' },
];

const blockColorMap = {
    blue: { active: 'bg-blue-600 border-blue-600 text-white shadow-blue-200', inactive: 'bg-white border-gray-200 text-gray-600 hover:border-blue-300 hover:bg-blue-50' },
    emerald: { active: 'bg-emerald-600 border-emerald-600 text-white shadow-emerald-200', inactive: 'bg-white border-gray-200 text-gray-600 hover:border-emerald-300 hover:bg-emerald-50' },
    amber: { active: 'bg-amber-500 border-amber-500 text-white shadow-amber-200', inactive: 'bg-white border-gray-200 text-gray-600 hover:border-amber-300 hover:bg-amber-50' },
};

// ── Feature icon map (slug → SVG path) ─────────────────────────────────────────────
const featureIconMap = {
    'attendance':       { path: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', color: 'text-blue-600 bg-blue-50' },
    'visitation':       { path: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', color: 'text-rose-600 bg-rose-50' },
    'members':          { path: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', color: 'text-violet-600 bg-violet-50' },
    'thanh-vien':       { path: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', color: 'text-violet-600 bg-violet-50' },
    'reports':          { path: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', color: 'text-indigo-600 bg-indigo-50' },
    'finance':          { path: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', color: 'text-emerald-600 bg-emerald-50' },
    'assignments':      { path: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', color: 'text-amber-600 bg-amber-50' },
    'education-classes':{ path: 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z', color: 'text-cyan-600 bg-cyan-50' },
    'education-report': { path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', color: 'text-cyan-600 bg-cyan-50' },
    'default':          { path: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z', color: 'text-gray-600 bg-gray-100' },
};

const getFeatureIcon = (slug) => featureIconMap[slug] || featureIconMap['default'];

const availableDepts = computed(() =>
    props.departments.filter(d => d.block === form.value.block_type)
);

const selectFeature = (id) => {
    isCreating.value = false;
    activeFeatureId.value = id;
    const config = getCurrentConfig(id);
    form.value = { ...config };
};

if (activeFeatureId.value) selectFeature(activeFeatureId.value);

// ── Toast ──────────────────────────────────────────────────────────────────────
const toastMsg = ref('');
const toastError = ref(false);
const showToast = (msg, err = false) => {
    toastMsg.value = msg; toastError.value = err;
    setTimeout(() => toastMsg.value = '', 3500);
};

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

const scopeLabel = (scope) => ({ global: 'Toàn Bộ', block: 'Theo Ban', specific: 'Cụ Thể' })[scope] ?? scope;
const scopeBadgeColor = (scope) => ({
    global: 'bg-purple-100 text-purple-700',
    block:  'bg-blue-100 text-blue-700',
    specific: 'bg-emerald-100 text-emerald-700',
})[scope] ?? 'bg-gray-100 text-gray-500';

// ── Matrix View State ───────────────────────────────────────────────────────
const viewMode = ref('detail');
const matrixAssignments = computed(() => {
    const map = {};
    (props.systemConfig || []).forEach(c => {
        const key = `${c.feature_id}-${c.department_id || c.block_type || 'global'}`;
        map[key] = c.is_active;
    });
    return map;
});

const deptsByBlock = computed(() => {
    const groups = { activities: [], ministry: [], leadership: [] };
    props.departments.forEach(d => {
        if (groups[d.block]) groups[d.block].push(d);
    });
    return groups;
});

// ── Matrix Block Filter ─────────────────────────────────────────────────────
const selectedMatrixBlock = ref('activities'); // 'activities' | 'ministry' | 'leadership' | 'all'

const visibleBlocksInMatrix = computed(() => {
    if (selectedMatrixBlock.value === 'all') return blockTypes;
    return blockTypes.filter(b => b.id === selectedMatrixBlock.value);
});

const isTogglingMatrix = ref(null);
const toggleMatrix = async (featureId, deptId, block, scope, currentVal) => {
    const key = `${featureId}-${deptId || block || 'global'}`;
    isTogglingMatrix.value = key;
    try {
        await axios.post(route('admin.features.matrix.toggle'), {
            feature_id: featureId,
            department_id: deptId,
            block_type: block,
            scope: scope,
            is_active: !currentVal
        });
        router.reload({ only: ['systemConfig'] });
    } catch (err) {
        showToast('❌ Lỗi: ' + (err?.response?.data?.message || err.message), true);
    } finally {
        isTogglingMatrix.value = null;
    }
};

const getMatrixStatus = (featureId, deptId, block, scope) => {
    const key = `${featureId}-${deptId || block || 'global'}`;
    const explicitVal = matrixAssignments.value[key];
    
    if (explicitVal !== undefined) {
        return { is_explicit: true, is_active: explicitVal };
    }
    
    if (scope === 'specific') {
        const blockVal = matrixAssignments.value[`${featureId}-${block}`];
        if (blockVal !== undefined) return { is_explicit: false, is_active: blockVal };
        const globalVal = matrixAssignments.value[`${featureId}-global`];
        return { is_explicit: false, is_active: globalVal ?? true };
    } else if (scope === 'block') {
        const globalVal = matrixAssignments.value[`${featureId}-global`];
        return { is_explicit: false, is_active: globalVal ?? true };
    }
    
    return { is_explicit: false, is_active: true };
};

// KPI stats
const configuredCount = computed(() => {
    const configured = new Set((props.systemConfig || []).map(c => c.feature_id));
    return configured.size;
});

const globalCount = computed(() => (props.systemConfig || []).filter(c => c.scope === 'global').length);
</script>

<template>
<div class="flex flex-col gap-5 w-full">

    <!-- ── Toast ──────────────────────────────────────── -->
    <transition name="slide-up">
        <div v-if="toastMsg"
            :class="toastError ? 'bg-red-500' : 'bg-emerald-500'"
            class="fixed bottom-6 right-6 text-white px-5 py-3.5 rounded-2xl shadow-2xl z-50 font-bold text-sm flex items-center gap-2 border border-white/20">
            {{ toastMsg }}
        </div>
    </transition>

    <!-- ── View Switcher ──────────────────────────────── -->
    <div class="flex items-center justify-between">
        <!-- Pill Tabs -->
        <div class="flex gap-1 p-1.5 bg-gray-100/80 rounded-2xl shadow-inner border border-gray-200/60">
            <button @click="viewMode = 'detail'"
                :class="['flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black transition-all duration-200',
                    viewMode === 'detail' ? 'bg-white text-indigo-700 shadow-md border border-indigo-100' : 'text-gray-500 hover:text-gray-700 hover:bg-white/50']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Cấu Hình Chi Tiết
            </button>
            <button @click="viewMode = 'matrix'"
                :class="['flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black transition-all duration-200',
                    viewMode === 'matrix' ? 'bg-white text-indigo-700 shadow-md border border-indigo-100' : 'text-gray-500 hover:text-gray-700 hover:bg-white/50']">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M3 14h18M10 3v18M14 3v18M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6z"/>
                </svg>
                Bảng Ma Trận
            </button>
        </div>
        <!-- Add Feature button (detail mode only) -->
        <button v-if="viewMode === 'detail'" @click="isCreating = true"
            class="hidden sm:flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-all shadow-sm active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Thêm Module
        </button>
    </div>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- DETAIL VIEW                                           -->
    <!-- ══════════════════════════════════════════════════════ -->
    <div v-if="viewMode === 'detail'" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col md:flex-row min-h-[620px]">

        <!-- Sidebar -->
        <div class="w-full md:w-[280px] lg:w-[300px] border-r border-gray-100 bg-gray-50/50 flex flex-col shrink-0">
            <div class="px-4 py-3.5 bg-white border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Danh Mục Module</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ features.length }} tính năng hệ thống</p>
                </div>
                <!-- Mobile add button -->
                <button @click="isCreating = true"
                    class="sm:hidden w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
            <div class="overflow-y-auto flex-1 p-2.5 space-y-1">
                <button v-for="f in features" :key="f.id" @click="selectFeature(f.id)"
                    :class="['w-full text-left px-3.5 py-3 rounded-xl flex items-center gap-3 transition-all duration-150 group border-2',
                        activeFeatureId === f.id && !isCreating
                            ? 'bg-white border-indigo-300 shadow-sm'
                            : 'bg-transparent border-transparent hover:bg-white hover:border-gray-200 hover:shadow-sm']">
                    <!-- SVG Icon -->
                    <div :class="['w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors',
                        activeFeatureId === f.id && !isCreating
                            ? getFeatureIcon(f.slug).color
                            : 'bg-gray-100 group-hover:' + getFeatureIcon(f.slug).color.split(' ')[1]]">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="getFeatureIcon(f.slug).path"/>
                        </svg>
                    </div>
                    <!-- Text -->
                    <div class="flex-1 min-w-0">
                        <p :class="['font-bold text-sm truncate transition-colors',
                            activeFeatureId === f.id && !isCreating ? 'text-indigo-700' : 'text-gray-700']">
                            {{ f.name }}
                        </p>
                        <p class="text-[10px] text-gray-400 font-mono truncate">{{ f.slug }}</p>
                    </div>
                    <!-- Scope badge -->
                    <span v-if="activeFeatureId === f.id && !isCreating"
                        :class="['text-[9px] font-black px-2 py-0.5 rounded-full shrink-0 uppercase tracking-wider', scopeBadgeColor(form.scope)]">
                        {{ scopeLabel(form.scope) }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Main Panel -->
        <div class="flex-1 flex flex-col min-w-0 relative">

            <!-- New Feature Form -->
            <div v-if="isCreating" class="flex-1 overflow-y-auto p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-7">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-gray-900">Thêm Module Tính Năng</h2>
                        <p class="text-xs text-gray-500">Tạo một module mới trong hệ thống</p>
                    </div>
                </div>
                <form @submit.prevent="submitNewFeature" class="space-y-5 max-w-lg">
                    <div>
                        <label class="block text-sm font-black text-gray-900 mb-1.5">Tên Tính Năng <span class="text-red-500">*</span></label>
                        <input type="text" v-model="newFeatureForm.name" required placeholder="VD: Quản lý Quỹ..."
                            class="w-full border-gray-200 rounded-xl bg-gray-50 px-4 py-3 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-black text-gray-900 mb-1.5">Mã Slug <span class="text-red-500">*</span></label>
                        <input type="text" v-model="newFeatureForm.slug" required placeholder="VD: finance, attendance-pro..."
                            class="w-full border-gray-200 rounded-xl bg-gray-50 px-4 py-3 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition font-mono text-indigo-600">
                        <p v-if="newFeatureForm.errors.slug" class="text-red-500 text-xs mt-1">{{ newFeatureForm.errors.slug }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-black text-gray-900 mb-1.5">Icon (Emoji)</label>
                            <input type="text" v-model="newFeatureForm.icon" placeholder="📦" class="w-full border-gray-200 rounded-xl bg-gray-50 px-4 py-3 text-sm focus:ring-indigo-500 text-center text-2xl">
                        </div>
                        <div>
                            <label class="block text-sm font-black text-gray-900 mb-1.5">Portal Mặc Định</label>
                            <select v-model="newFeatureForm.portal_type" class="w-full border-gray-200 rounded-xl bg-gray-50 px-4 py-3 text-sm focus:ring-indigo-500">
                                <option value="activities">🎯 Sinh Hoạt</option>
                                <option value="ministry">⛪ Mục Vụ</option>
                                <option value="leadership">🛡 Chấp Sự</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-black text-gray-900 mb-1.5">Mô tả</label>
                        <textarea v-model="newFeatureForm.description" rows="2" class="w-full border-gray-200 rounded-xl bg-gray-50 px-4 py-3 text-sm" placeholder="Mô tả công dụng..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="isCreating = false"
                            class="px-5 py-2.5 rounded-xl font-bold text-gray-600 hover:bg-gray-100 text-sm transition">Hủy</button>
                        <button type="submit" :disabled="newFeatureForm.processing"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-sm disabled:opacity-50 transition active:scale-95">
                            Thêm Vào Hệ Thống
                        </button>
                    </div>
                </form>
            </div>

            <!-- Feature Config -->
            <div v-else-if="activeFeature" class="flex-1 flex flex-col overflow-hidden">
                <!-- Feature header -->
                <div class="px-6 sm:px-8 py-5 border-b border-gray-100 bg-white flex items-center gap-4">
                    <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center shrink-0', getFeatureIcon(activeFeature.slug).color]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="getFeatureIcon(activeFeature.slug).path"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-black text-gray-900">{{ activeFeature.name }}</h2>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-lg text-xs font-mono font-bold">{{ activeFeature.slug }}</span>
                            <span class="text-xs text-gray-400">{{ activeFeature.description || 'Chưa có mô tả' }}</span>
                        </div>
                    </div>
                    <span :class="['text-xs font-black px-3 py-1 rounded-full border', scopeBadgeColor(form.scope)]">
                        {{ scopeLabel(form.scope) }}
                    </span>
                </div>

                <!-- Config body -->
                <div class="flex-1 overflow-y-auto px-6 sm:px-8 py-6 pb-28">

                    <!-- Step indicator -->
                    <div class="flex items-center gap-2 mb-7">
                        <div :class="['w-7 h-7 rounded-full flex items-center justify-center text-xs font-black transition-colors', 'bg-indigo-600 text-white']">1</div>
                        <div class="flex-1 h-0.5 bg-gray-200 rounded"></div>
                        <div :class="['w-7 h-7 rounded-full flex items-center justify-center text-xs font-black transition-colors', form.scope !== 'global' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-400']">2</div>
                        <div class="flex-1 h-0.5 bg-gray-200 rounded"></div>
                        <div :class="['w-7 h-7 rounded-full flex items-center justify-center text-xs font-black transition-colors', form.scope === 'specific' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-400']">3</div>
                    </div>

                    <!-- STEP 1: Scope -->
                    <div class="mb-7">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Bước 1 — Phạm Vi Áp Dụng</p>
                        <div class="space-y-2.5">

                            <label :class="['flex items-start gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all',
                                form.scope === 'global' ? 'border-purple-400 bg-purple-50/60 shadow-sm' : 'border-gray-200 bg-white hover:border-purple-200 hover:bg-purple-50/20']">
                                <input type="radio" v-model="form.scope" value="global" class="w-4 h-4 text-purple-600 mt-1 shrink-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">🌐</span>
                                        <p class="font-black text-gray-900 text-sm">Tất Cả Ban Ngành</p>
                                        <span class="text-[10px] font-black bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full uppercase">Global</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Tính năng hiện trên portal của <strong>mọi ban ngành</strong> trong hệ thống.</p>
                                </div>
                            </label>

                            <label :class="['flex items-start gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all',
                                form.scope === 'block' ? 'border-blue-400 bg-blue-50/60 shadow-sm' : 'border-gray-200 bg-white hover:border-blue-200 hover:bg-blue-50/20']">
                                <input type="radio" v-model="form.scope" value="block" class="w-4 h-4 text-blue-600 mt-1 shrink-0">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">📋</span>
                                        <p class="font-black text-gray-900 text-sm">Theo Loại Ban Ngành</p>
                                        <span class="text-[10px] font-black bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full uppercase">Block</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Hiển thị mặc định cho <strong>tất cả ban trong một loại</strong> (VD: Mọi ban Sinh Hoạt).</p>
                                </div>
                            </label>

                            <label :class="['flex items-start gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all',
                                form.scope === 'specific' ? 'border-emerald-400 bg-emerald-50/60 shadow-sm' : 'border-gray-200 bg-white hover:border-emerald-200 hover:bg-emerald-50/20']">
                                <input type="radio" v-model="form.scope" value="specific" class="w-4 h-4 text-emerald-600 mt-1 shrink-0">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">🎯</span>
                                        <p class="font-black text-gray-900 text-sm">Ban Ngành Cụ Thể</p>
                                        <span class="text-[10px] font-black bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full uppercase">Specific</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Chỉ hiển thị cho <strong>những ban được chọn</strong>. Các ban khác không thấy.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- STEP 2: Block selector -->
                    <div v-if="form.scope !== 'global'" class="mb-7">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Bước 2 — Chọn Loại Ban Ngành</p>
                        <div class="flex flex-wrap gap-2.5">
                            <button v-for="b in blockTypes" :key="b.id" type="button"
                                @click="form.block_type = b.id; form.department_ids = []"
                                :class="['flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-black border-2 transition-all shadow-sm',
                                    form.block_type === b.id
                                        ? blockColorMap[b.color].active + ' shadow-md'
                                        : blockColorMap[b.color].inactive]">
                                <span>{{ b.icon }}</span>
                                {{ b.name }}
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3a: Active toggle (global/block) -->
                    <div v-if="form.scope === 'global' || form.scope === 'block'" class="mb-7">
                        <p class="text-xs font-black text-gray-500 uppercase tracking-widest mb-3">Bước {{ form.scope === 'global' ? '2' : '3' }} — Trạng Thái Kích Hoạt</p>
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-200 w-max">
                            <span class="text-sm font-bold text-gray-700">Trạng thái:</span>
                            <button type="button" @click="form.is_active = !form.is_active"
                                :class="form.is_active
                                    ? 'bg-emerald-500 text-white ring-4 ring-emerald-100 shadow-sm'
                                    : 'bg-gray-200 text-gray-500 hover:bg-gray-300'"
                                class="px-6 py-2 rounded-full text-sm font-black transition-all active:scale-95">
                                {{ form.is_active ? '✅ ĐANG BẬT' : '❌ ĐANG TẮT' }}
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3b: Department list (specific) -->
                    <div v-if="form.scope === 'specific'" class="mb-7">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Bước 3 — Chọn Ban Ngành Cụ Thể</p>
                            <span class="bg-emerald-100 text-emerald-700 px-2.5 py-0.5 rounded-full text-[10px] font-black">{{ form.department_ids.length }} đã chọn</span>
                        </div>
                        <div v-if="availableDepts.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-56 overflow-y-auto p-1">
                            <label v-for="d in availableDepts" :key="d.id"
                                class="flex items-center gap-2.5 p-3 rounded-xl cursor-pointer border-2 transition-all"
                                :class="form.department_ids.includes(d.id) ? 'border-emerald-400 bg-emerald-50 shadow-sm' : 'border-gray-200 bg-white hover:border-emerald-200 hover:bg-emerald-50/30'">
                                <input type="checkbox" :value="d.id" v-model="form.department_ids" class="w-4 h-4 text-emerald-600 rounded">
                                <span class="text-sm font-bold text-gray-800 truncate leading-tight">{{ d.name }}</span>
                            </label>
                        </div>
                        <p v-else class="text-sm text-gray-400 italic p-4 bg-gray-50 rounded-xl">Chưa có ban ngành. Hãy chọn loại ban ở bước 2.</p>
                    </div>
                </div>

                <!-- Save bar -->
                <div class="absolute bottom-0 left-0 right-0 px-6 sm:px-8 py-4 bg-white/95 backdrop-blur border-t border-gray-100 flex items-center justify-between z-20 shadow-[0_-4px_16px_rgba(0,0,0,0.05)]">
                    <div class="text-xs text-gray-400 hidden sm:block">
                        <span class="font-bold text-gray-600">{{ activeFeature?.name }}</span> →
                        <span :class="scopeBadgeColor(form.scope)" class="font-bold px-2 py-0.5 rounded-full ml-1">{{ scopeLabel(form.scope) }}</span>
                    </div>
                    <button @click="saveConfig" :disabled="isSaving"
                        class="ml-auto flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-7 py-2.5 rounded-xl font-black text-sm shadow-sm transition-all disabled:opacity-50 active:scale-95">
                        <svg v-if="isSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ isSaving ? 'Đang lưu...' : 'Lưu Cấu Hình' }}
                    </button>
                </div>
            </div>

            <!-- Empty (no feature selected) -->
            <div v-else class="flex-1 flex flex-col items-center justify-center text-center p-8 gap-4">
                <div class="w-20 h-20 rounded-3xl bg-gray-100 flex items-center justify-center text-4xl">⚙️</div>
                <p class="font-black text-gray-600 text-base">Chọn module bên trái để cấu hình</p>
                <p class="text-sm text-gray-400 max-w-xs">Mỗi module có thể được phân quyền theo phạm vi Global, Block, hoặc ban ngành cụ thể.</p>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════ -->
    <!-- MATRIX VIEW                                           -->
    <!-- ══════════════════════════════════════════════════════ -->
    <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">

        <!-- Matrix block selector + legend -->
        <div class="px-5 py-4 border-b border-gray-100 bg-white space-y-3">
            <!-- Block filter pills -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-black text-gray-500 uppercase tracking-widest shrink-0">Lọc theo khối:</span>
                <div class="flex gap-1.5 flex-wrap">
                    <button @click="selectedMatrixBlock = 'activities'"
                        :class="['flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-black border-2 transition-all',
                            selectedMatrixBlock === 'activities'
                                ? 'bg-blue-600 border-blue-600 text-white shadow-sm shadow-blue-200'
                                : 'bg-white border-gray-200 text-gray-600 hover:border-blue-300 hover:bg-blue-50']">
                        <span>🎯</span> Ban Sinh Hoạt
                        <span class="opacity-60 font-normal">({{ deptsByBlock.activities.length }})</span>
                    </button>
                    <button @click="selectedMatrixBlock = 'ministry'"
                        :class="['flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-black border-2 transition-all',
                            selectedMatrixBlock === 'ministry'
                                ? 'bg-emerald-600 border-emerald-600 text-white shadow-sm shadow-emerald-200'
                                : 'bg-white border-gray-200 text-gray-600 hover:border-emerald-300 hover:bg-emerald-50']">
                        <span>⛪</span> Ban Mục Vụ
                        <span class="opacity-60 font-normal">({{ deptsByBlock.ministry.length }})</span>
                    </button>
                    <button @click="selectedMatrixBlock = 'leadership'"
                        :class="['flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-black border-2 transition-all',
                            selectedMatrixBlock === 'leadership'
                                ? 'bg-amber-500 border-amber-500 text-white shadow-sm shadow-amber-200'
                                : 'bg-white border-gray-200 text-gray-600 hover:border-amber-300 hover:bg-amber-50']">
                        <span>🛡</span> Ban Chấp Sự
                        <span class="opacity-60 font-normal">({{ deptsByBlock.leadership.length }})</span>
                    </button>
                    <button @click="selectedMatrixBlock = 'all'"
                        :class="['flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-black border-2 transition-all',
                            selectedMatrixBlock === 'all'
                                ? 'bg-gray-700 border-gray-700 text-white shadow-sm'
                                : 'bg-white border-gray-200 text-gray-500 hover:border-gray-400 hover:bg-gray-50']">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        Tất Cả
                    </button>
                </div>
            </div>
            <!-- Legend -->
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-1.5"><div class="w-3.5 h-3.5 rounded-md bg-purple-600"></div><span class="text-[10px] font-bold text-purple-700">Global</span></div>
                <div class="flex items-center gap-1.5"><div class="w-3.5 h-3.5 rounded-md bg-blue-400"></div><span class="text-[10px] font-bold text-blue-600">Block (tất cả)</span></div>
                <div class="flex items-center gap-1.5"><div class="w-3.5 h-3.5 rounded-md bg-emerald-500"></div><span class="text-[10px] font-bold text-emerald-700">Ban cụ thể</span></div>
                <div class="flex items-center gap-1.5"><div class="w-3.5 h-3.5 rounded-md bg-gray-100 border-2 border-gray-300 flex items-center justify-center"><svg class="w-2 h-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></div><span class="text-[10px] font-bold text-gray-500">Từ chối rõ</span></div>
                <div class="flex items-center gap-1.5"><div class="w-3.5 h-3.5 rounded-md bg-emerald-200 border border-emerald-200"></div><span class="text-[10px] font-bold text-gray-400">Kế thừa</span></div>
                <p class="ml-auto text-[10px] text-gray-400 italic hidden lg:block">Nhấn ô để bật/tắt tính năng.</p>
            </div>
        </div>

        <!-- Table wrapper -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <!-- Row 1: Block group headers -->
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-4 text-xs font-black text-gray-500 uppercase tracking-widest sticky left-0 bg-gray-50 z-20 shadow-[2px_0_8px_rgba(0,0,0,0.06)] min-w-[180px] w-[180px]">
                            Module / Tính Năng
                        </th>
                        <th class="p-3 text-[11px] font-black text-purple-700 uppercase tracking-wider text-center border-l border-gray-200 bg-purple-50/60 w-[80px]">
                            <div class="flex flex-col items-center gap-0.5">
                                <span>🌐</span>
                                <span>Toàn HT</span>
                            </div>
                        </th>
                        <template v-for="b in visibleBlocksInMatrix" :key="b.id">
                            <th :colspan="deptsByBlock[b.id].length + 1"
                                class="p-3 text-[11px] font-black uppercase tracking-wider text-center border-l border-gray-200"
                                :class="b.id === 'activities' ? 'text-blue-700 bg-blue-50/50' : b.id === 'ministry' ? 'text-emerald-700 bg-emerald-50/50' : 'text-amber-700 bg-amber-50/50'">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span>{{ b.icon }}</span>
                                    <span>{{ b.name }}</span>
                                    <span class="text-[9px] font-normal opacity-60 normal-case">({{ deptsByBlock[b.id].length }} ban)</span>
                                </div>
                            </th>
                        </template>
                    </tr>
                    <!-- Row 2: Dept column labels -->
                    <tr class="bg-white border-b border-gray-100">
                        <th class="sticky left-0 bg-white z-20 shadow-[2px_0_8px_rgba(0,0,0,0.06)] p-2 w-[180px]"></th>
                        <th class="p-2 text-center border-l border-gray-100 text-purple-400 italic font-bold text-[10px] w-[80px]">Global</th>
                        <template v-for="b in visibleBlocksInMatrix" :key="b.id">
                            <!-- Block "Tất cả" column -->
                            <th class="p-2 text-center border-l border-gray-200 italic font-black text-[10px] w-[72px]"
                                :class="b.id === 'activities' ? 'text-blue-500 bg-blue-50/30' : b.id === 'ministry' ? 'text-emerald-500 bg-emerald-50/30' : 'text-amber-500 bg-amber-50/30'">
                                Tất cả
                            </th>
                            <!-- Individual dept columns -->
                            <th v-for="d in deptsByBlock[b.id]" :key="d.id"
                                class="p-2 text-center border-l border-gray-50 font-bold text-[10px] text-gray-600 min-w-[88px] max-w-[120px]">
                                <div class="px-1 leading-tight" :title="d.name">{{ d.name }}</div>
                            </th>
                        </template>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="f in features" :key="f.id"
                        class="hover:bg-indigo-50/20 transition-colors group">
                        <!-- Feature name -->
                        <td class="p-3 sticky left-0 bg-white group-hover:bg-indigo-50/20 z-10 shadow-[2px_0_8px_rgba(0,0,0,0.04)] border-r border-gray-100 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <div :class="['w-8 h-8 rounded-xl flex items-center justify-center text-base flex-shrink-0 transition-colors', getFeatureIcon(f.slug).color]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" :d="getFeatureIcon(f.slug).path"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-xs text-gray-800 leading-tight">{{ f.name }}</p>
                                    <p class="text-[9px] font-mono text-gray-400">{{ f.slug }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Global checkbox -->
                        <td class="p-3 text-center border-l border-gray-100">
                            <button @click="toggleMatrix(f.id, null, null, 'global', getMatrixStatus(f.id, null, null, 'global').is_active)"
                                :disabled="isTogglingMatrix === `${f.id}-global`"
                                :title="getMatrixStatus(f.id, null, null, 'global').is_active ? 'Đang bật - Nhấn để tắt' : 'Đang tắt - Nhấn để bật'"
                                :class="['w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all mx-auto hover:scale-110 active:scale-95',
                                    getMatrixStatus(f.id, null, null, 'global').is_active
                                        ? 'bg-purple-600 border-purple-600 text-white shadow-md shadow-purple-200'
                                        : 'border-gray-200 hover:border-purple-300 bg-white']">
                                <svg v-if="isTogglingMatrix === `${f.id}-global`" class="animate-spin w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <svg v-else-if="getMatrixStatus(f.id, null, null, 'global').is_active" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </td>

                        <!-- Block & Dept checkboxes (filtered) -->
                        <template v-for="b in visibleBlocksInMatrix" :key="b.id">
                            <!-- Block toggle -->
                            <td class="p-3 text-center border-l border-gray-200">
                                <button @click="toggleMatrix(f.id, null, b.id, 'block', getMatrixStatus(f.id, null, b.id, 'block').is_active)"
                                    :disabled="isTogglingMatrix === `${f.id}-${b.id}`"
                                    :title="b.name + ' - ' + (getMatrixStatus(f.id, null, b.id, 'block').is_active ? 'Đang bật' : 'Đang tắt')"
                                    :class="['w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all mx-auto hover:scale-110 active:scale-95',
                                        getMatrixStatus(f.id, null, b.id, 'block').is_active
                                            ? (getMatrixStatus(f.id, null, b.id, 'block').is_explicit
                                                ? (b.id === 'activities' ? 'bg-blue-600 border-blue-600' : b.id === 'ministry' ? 'bg-emerald-600 border-emerald-600' : 'bg-amber-500 border-amber-500') + ' text-white shadow-md'
                                                : (b.id === 'activities' ? 'bg-blue-200 border-blue-200' : b.id === 'ministry' ? 'bg-emerald-200 border-emerald-200' : 'bg-amber-200 border-amber-200') + ' text-white')
                                            : (getMatrixStatus(f.id, null, b.id, 'block').is_explicit
                                                ? 'bg-gray-100 border-gray-300'
                                                : 'border-gray-200 bg-white hover:border-indigo-300')]">
                                    <svg v-if="isTogglingMatrix === `${f.id}-${b.id}`" class="animate-spin w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    <template v-else>
                                        <svg v-if="getMatrixStatus(f.id, null, b.id, 'block').is_active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        <svg v-else-if="getMatrixStatus(f.id, null, b.id, 'block').is_explicit" class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </template>
                                </button>
                            </td>
                            <!-- Dept toggles -->
                            <td v-for="d in deptsByBlock[b.id]" :key="d.id" class="p-3 text-center border-l border-gray-50">
                                <button @click="toggleMatrix(f.id, d.id, b.id, 'specific', getMatrixStatus(f.id, d.id, b.id, 'specific').is_active)"
                                    :disabled="isTogglingMatrix === `${f.id}-${d.id}`"
                                    :title="d.name + ': ' + (getMatrixStatus(f.id, d.id, b.id, 'specific').is_active ? 'Đang bật' : 'Đang tắt')"
                                    :class="['w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all mx-auto hover:scale-110 active:scale-95',
                                        getMatrixStatus(f.id, d.id, b.id, 'specific').is_active
                                            ? (getMatrixStatus(f.id, d.id, b.id, 'specific').is_explicit
                                                ? 'bg-emerald-500 border-emerald-500 text-white shadow-md shadow-emerald-100'
                                                : 'bg-emerald-200 border-emerald-200 text-white')
                                            : (getMatrixStatus(f.id, d.id, b.id, 'specific').is_explicit
                                                ? 'bg-gray-100 border-gray-300'
                                                : 'border-gray-200 bg-white hover:border-emerald-300')]">
                                    <svg v-if="isTogglingMatrix === `${f.id}-${d.id}`" class="animate-spin w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    <template v-else>
                                        <svg v-if="getMatrixStatus(f.id, d.id, b.id, 'specific').is_active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        <svg v-else-if="getMatrixStatus(f.id, d.id, b.id, 'specific').is_explicit" class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </template>
                                </button>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
</template>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.slide-up-enter-from, .slide-up-leave-to {
    opacity: 0;
    transform: translateY(16px) scale(0.95);
}
</style>