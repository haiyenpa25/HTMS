<template>
    <PortalLayout :department="eduClass.department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" portal-type="ministry">
        <div class="min-h-screen bg-gray-50">

            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-700 to-indigo-600 text-white">
                <div class="max-w-5xl mx-auto px-4 py-4 flex items-center gap-3">
                    <Link :href="route(props.routePrefix + '.classes')" class="p-1.5 rounded-lg hover:bg-white/10 transition-colors shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </Link>
                    <div class="flex-1 min-w-0">
                        <h1 class="font-bold text-lg leading-tight">{{ eduClass.name }}</h1>
                        <p class="text-indigo-200 text-xs">
                            {{ eduClass.class_type === 'bible_quiz' ? 'Quản lý bài kiểm tra' : 'Quản lý buổi học' }}
                            · {{ sessions.length }} {{ eduClass.class_type === 'bible_quiz' ? 'bài đã ghi nhận' : 'buổi đã ghi nhận' }}
                        </p>
                    </div>
                    <!-- Bulk delete indicator -->
                    <div v-if="selectedIds.size > 0" class="flex items-center gap-2">
                        <span class="text-sm font-bold bg-white/20 text-white px-3 py-1.5 rounded-xl">
                            Đã chọn {{ selectedIds.size }}
                        </span>
                        <!-- Step 1: request confirm -->
                        <div v-if="!bulkConfirming" class="flex items-center gap-2">
                            <button @click="bulkConfirming = true"
                                class="flex items-center gap-1.5 px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-bold text-sm rounded-xl transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Xóa {{ selectedIds.size }} {{ eduClass.class_type === 'bible_quiz' ? 'bài' : 'buổi' }}
                            </button>
                        </div>
                        <!-- Step 2: confirm panel -->
                        <div v-else class="flex items-center gap-2 bg-red-500 rounded-xl px-3 py-1.5">
                            <span class="text-white text-xs font-bold">Xác nhận xóa {{ selectedIds.size }} buổi?</span>
                            <button @click="bulkDelete"
                                class="px-3 py-1 bg-white text-red-600 text-xs font-bold rounded-lg hover:bg-red-50 transition-colors">
                                Xóa!
                            </button>
                            <button @click="bulkConfirming = false"
                                class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 transition-colors">
                                Hủy
                            </button>
                        </div>
                        <button @click="clearSelection" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 transition-colors text-xs font-bold">Bỏ chọn</button>
                    </div>
                    <!-- Create button -->
                    <button v-if="canManage" @click="showCreateForm = true"
                        class="flex items-center gap-2 px-4 py-2 bg-white text-indigo-700 font-bold text-sm rounded-xl hover:bg-indigo-50 transition-colors shadow-sm shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ eduClass.class_type === 'bible_quiz' ? '+ Thêm bài KT' : 'Tạo buổi học' }}
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="max-w-5xl mx-auto px-4 py-6 space-y-4">

                <!-- Flash message -->
                <div v-if="page.props.flash?.success"
                    class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-green-700 text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ page.props.flash.success }}
                </div>

                <!-- Session list -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-3 border-b border-gray-100 flex items-center gap-3">
                        <!-- Select All -->
                        <div v-if="canManage && sessions.length > 0" class="flex items-center gap-2 shrink-0">
                            <input type="checkbox" id="select-all"
                                :checked="selectedIds.size === sessions.length && sessions.length > 0"
                                :indeterminate="selectedIds.size > 0 && selectedIds.size < sessions.length"
                                @change="toggleAll"
                                class="w-4 h-4 text-indigo-600 rounded border-gray-300 cursor-pointer">
                            <label for="select-all" class="text-xs font-bold text-gray-500 cursor-pointer select-none">Chọn tất cả</label>
                        </div>
                        <div class="flex-1 flex items-center justify-between">
                            <h2 class="font-bold text-gray-900 text-sm uppercase tracking-wider">
                                {{ eduClass.class_type === 'bible_quiz' ? 'Danh sách bài học' : 'Danh sách buổi học' }}
                            </h2>
                            <span class="text-xs text-gray-400">{{ eduClass.class_type === 'bible_quiz' ? 'Nhấn để chấm điểm' : 'Nhấn vào buổi để điểm danh' }}</span>
                        </div>
                    </div>

                    <!-- Empty -->
                    <div v-if="sessions.length === 0" class="py-16 text-center">
                        <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-700 text-base mb-1">Chưa có buổi học nào</h3>
                        <p class="text-gray-400 text-sm mb-4">Tạo buổi học đầu tiên để bắt đầu điểm danh</p>
                        <button v-if="canManage" @click="showCreateForm = true"
                            class="px-6 py-2 bg-indigo-600 text-white font-bold text-sm rounded-xl hover:bg-indigo-700 transition-colors">
                            + Tạo buổi học
                        </button>
                    </div>

                    <!-- Session rows -->
                    <div v-else class="divide-y divide-gray-100">
                        <div v-for="s in sessions" :key="s.id"
                            class="flex items-center gap-3 px-4 py-4 hover:bg-indigo-50/40 transition-colors"
                            :class="selectedIds.has(s.id) ? 'bg-indigo-50/60' : ''">

                            <!-- Checkbox -->
                            <div v-if="canManage" class="shrink-0" @click.stop>
                                <input type="checkbox"
                                    :checked="selectedIds.has(s.id)"
                                    @change="toggleSelect(s.id)"
                                    class="w-4 h-4 text-indigo-600 rounded border-gray-300 cursor-pointer">
                            </div>

                            <!-- Date badge — clickable to enter session -->
                            <div class="shrink-0 w-14 text-center cursor-pointer" @click="goToSession(s.id)">
                                <div class="text-lg font-bold text-indigo-700 leading-none">{{ dayNum(s.session_date) }}</div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase mt-0.5">{{ monthShort(s.session_date) }}</div>
                                <div class="text-[10px] text-gray-300">{{ year(s.session_date) }}</div>
                            </div>

                            <!-- Info — clickable to enter session -->
                            <div class="flex-1 min-w-0 cursor-pointer" @click="goToSession(s.id)">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span v-if="s.lesson_number" class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                        Bài {{ s.lesson_number }}
                                    </span>
                                    <span v-if="s.lesson_series" class="text-gray-400 text-xs font-medium truncate max-w-[140px]">{{ s.lesson_series }}</span>
                                    <span v-if="isSunday(s.session_date)" class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full">CN</span>
                                </div>
                                <p class="font-bold text-gray-900 text-sm mt-0.5 truncate">
                                    {{ s.topic || '(Chưa có tên bài)' }}
                                </p>
                                <div class="flex items-center gap-3 mt-0.5 text-xs text-gray-400">
                                    <span class="text-green-600 font-bold">
                                        {{ eduClass.class_type === 'bible_quiz' ? '✔ ' + s.present_count + ' làm bài' : '✔ ' + s.present_count + ' có mặt' }}
                                    </span>
                                    <span v-if="s.avg_score !== null && s.avg_score !== undefined" class="text-amber-600 font-bold">TB: {{ s.avg_score }}đ</span>
                                    <span v-if="s.total_income > 0" class="text-indigo-600 font-bold">{{ formatMoney(s.total_income) }}</span>
                                </div>
                            </div>

                            <!-- Always-visible actions -->
                            <div class="flex items-center gap-2 shrink-0">
                                <button @click="goToSession(s.id)"
                                    class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition-colors">
                                    {{ eduClass.class_type === 'bible_quiz' ? 'Vào lớp' : 'Vào buổi' }}
                                </button>
                                <button v-if="canManage" @click="deleteSingle(s)"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100" title="Xóa buổi này">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Session SlideOver -->
        <SlideOver v-model="showCreateForm" title="Tạo Buổi Học Mới" description="Chọn ngày Chủ Nhật cho buổi học">
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Chọn nhanh (Chủ Nhật)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button v-for="sun in upcomingSundays" :key="sun.value"
                            @click="createForm.session_date = sun.value"
                            :class="createForm.session_date === sun.value
                                ? 'bg-indigo-600 text-white border-indigo-600'
                                : 'bg-white text-gray-700 border-gray-200 hover:border-indigo-300'"
                            class="border rounded-xl py-2.5 px-3 text-sm font-bold transition-colors text-center">
                            {{ sun.label }}
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Hoặc chọn ngày tùy chỉnh</label>
                    <input v-model="createForm.session_date" type="date"
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-400">Lớp Giáo Lý có thể chọn ngày bất kỳ</p>
                </div>
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Thông tin bài học (tuỳ chọn)</p>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Bài số</label>
                                <input v-model.number="createForm.lesson_number" type="number" min="1"
                                    class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="5">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Câu gốc</label>
                                <input v-model="createForm.scripture" type="text"
                                    class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Giăng 3:16">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Loạt bài</label>
                            <input v-model="createForm.lesson_series" type="text"
                                class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="VD: Sáng Thế Ký, Hê-bơ-rơ...">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Tên bài học</label>
                            <input v-model="createForm.topic" type="text"
                                class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Tiêu đề bài học...">
                        </div>
                    </div>
                </div>
                <p v-if="createForm.errors.session_date" class="text-xs text-red-500">{{ createForm.errors.session_date }}</p>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 w-full">
                    <button @click="showCreateForm = false" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="submitCreate" :disabled="createForm.processing || !createForm.session_date"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 disabled:opacity-50 transition-colors">
                        {{ createForm.processing ? 'Đang tạo...' : 'Tạo buổi học' }}
                    </button>
                </div>
            </template>
        </SlideOver>

    </PortalLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';

const page = usePage();

const props = defineProps({
    eduClass: Object,
    sessions: Array,
    canManage: Boolean,
    portalType: String,
    routePrefix: { type: String, default: 'ministry.education' },
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
});


// ── Create form ────────────────────────────────────────────────────
const showCreateForm = ref(false);
const createForm = useForm({
    session_date: '',
    lesson_number: null,
    lesson_series: '',
    topic: '',
    scripture: '',
});

// ── Selection state ────────────────────────────────────────────────
const selectedIds = ref(new Set());

const toggleSelect = (id) => {
    const next = new Set(selectedIds.value);
    if (next.has(id)) next.delete(id);
    else next.add(id);
    selectedIds.value = next;
};

const toggleAll = (e) => {
    if (e.target.checked) {
        selectedIds.value = new Set(props.sessions.map(s => s.id));
    } else {
        selectedIds.value = new Set();
    }
};

// ── Confirm state for bulk ─────────────────────────────────────────
const bulkConfirming = ref(false);

const clearSelection = () => {
    selectedIds.value = new Set();
    bulkConfirming.value = false;
};

// ── Actions ────────────────────────────────────────────────────────
const goToSession = (sessionId) => {
    router.get(route(props.routePrefix + '.session.view', [props.eduClass.id, sessionId]));
};


const deleteSingle = (session) => {
    // Direct delete without confirm dialog - just a trash icon click
    router.delete(route(props.routePrefix + '.sessions.destroy', [props.eduClass.id, session.id]), {

        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value.delete(session.id);
        },
    });
};

const bulkDelete = () => {
    router.delete(route(props.routePrefix + '.sessions.bulk-destroy', props.eduClass.id), {

        data: { ids: Array.from(selectedIds.value) },
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = new Set();
            bulkConfirming.value = false;
        },
    });
};

// ── Create session ─────────────────────────────────────────────────
const upcomingSundays = computed(() => {
    const result = [];
    const now = new Date();
    let d = new Date(now);
    while (d.getDay() !== 0) d.setDate(d.getDate() - 1);
    for (let i = 3; i >= 0; i--) {
        const s = new Date(d);
        s.setDate(d.getDate() - i * 7);
        result.push({
            value: s.toISOString().split('T')[0],
            label: new Intl.DateTimeFormat('vi-VN', { day: '2-digit', month: '2-digit' }).format(s)
                + (i === 0 ? ' (CN này)' : ''),
        });
    }
    const next = new Date(d);
    next.setDate(d.getDate() + 7);
    result.push({
        value: next.toISOString().split('T')[0],
        label: new Intl.DateTimeFormat('vi-VN', { day: '2-digit', month: '2-digit' }).format(next) + ' (CN sau)',
    });
    return result;
});

const submitCreate = () => {
    createForm.post(route(props.routePrefix + '.sessions.store', props.eduClass.id), {

        preserveScroll: true,
        onSuccess: () => { showCreateForm.value = false; createForm.reset(); },
    });
};

// ── Date helpers ───────────────────────────────────────────────────
const dayNum    = (d) => d ? new Date(d + 'T00:00:00').getDate() : '';
const monthShort = (d) => d ? new Intl.DateTimeFormat('vi-VN', { month: 'short' }).format(new Date(d + 'T00:00:00')) : '';
const year      = (d) => d ? new Date(d + 'T00:00:00').getFullYear() : '';
const isSunday  = (d) => d ? new Date(d + 'T00:00:00').getDay() === 0 : false;
const formatMoney = (val) => {
    if (!val) return '';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(val);
};
</script>