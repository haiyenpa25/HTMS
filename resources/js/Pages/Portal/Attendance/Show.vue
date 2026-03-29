<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" :hide-nav="true">
        <!-- Sticky Header for Back Navigation -->
        <div class="bg-white border-b border-gray-100 sticky top-0 z-10 px-4 py-3 sm:px-6 lg:px-8 w-full flex items-center justify-between gap-3">
            <button @click="goBack" class="flex items-center text-gray-500 hover:text-gray-900 font-bold text-sm transition-colors shrink-0">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Quay lại
            </button>
            <div class="text-right flex-1 min-w-0">
                <h2 class="text-sm font-black text-gray-900 truncate">{{ meeting.topic || 'Buổi nhóm định kỳ' }}</h2>
                <p class="text-[10px] sm:text-xs text-gray-500 font-medium">{{ formattedDate }}</p>
            </div>
            <!-- Nhánh thêm khách mới nhanh -->
            <button @click="isAddPendingOpen = true"
                title="Thêm khách mới chờ duyệt"
                class="shrink-0 w-9 h-9 flex items-center justify-center rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 border border-amber-200 active:scale-95 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </button>
        </div>

        <div class="pb-24 w-full mt-4 px-0 sm:px-4 lg:px-8">
            <div class="bg-white sm:rounded-[2rem] shadow-sm border-x-0 sm:border border-gray-100 overflow-hidden">
                
                <!-- Tab Headers (Only show if it's a department meeting OR if we want to toggle. For church meetings, only manual count is needed but we can hide the tabs and just show the manual count form) -->
                <div v-if="meeting.type === 'department' || meeting.type === 'holiday'" class="flex border-b border-gray-100">
                    <button 
                        @click="activeTab = 'manual'"
                        class="flex-1 py-4 text-center font-bold text-sm transition-colors relative"
                        :class="activeTab === 'manual' ? 'text-emerald-600' : 'text-gray-500 hover:text-gray-700'"
                    >
                        Nhập số lượng
                        <div v-if="activeTab === 'manual'" class="absolute bottom-0 left-0 w-full h-0.5 bg-emerald-600"></div>
                    </button>
                    <button 
                        @click="activeTab = 'named'"
                        class="flex-1 py-4 text-center font-bold text-sm transition-colors relative"
                        :class="activeTab === 'named' ? 'text-emerald-600' : 'text-gray-500 hover:text-gray-700'"
                    >
                        Check-in Tên
                        <div v-if="activeTab === 'named'" class="absolute bottom-0 left-0 w-full h-0.5 bg-emerald-600"></div>
                    </button>
                </div>
                <!-- Optional title for Church Meetings -->
                <div v-else class="bg-gray-50 border-b border-gray-100 px-6 py-4">
                    <h3 class="font-bold text-gray-900 text-sm">Điểm danh tổng (Báo cáo số lượng)</h3>
                </div>

                <!-- Form Area -->
                <div class="p-4 sm:p-8">
                    
                    <!-- Progress / Comparison Badge (Only for Department Meetings) -->
                    <div v-if="(meeting.type === 'department' || meeting.type === 'holiday')" class="mb-6 flex items-center justify-between bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <div>
                            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Đối chiếu</p>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-600">Nhập tay: <strong class="text-gray-900 border-b border-dashed border-gray-300">{{ form.manual_count || 0 }}</strong></span>
                                <span class="text-gray-300">|</span>
                                <span class="text-sm font-medium text-gray-600">Check-in: <strong class="text-emerald-600 border-b border-dashed border-emerald-200">{{ checkedInCount }}</strong> / {{ form.attendances.length }}</span>
                            </div>
                        </div>
                        <div v-if="parseInt(form.manual_count || 0) < checkedInCount" class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0" title="Số đếm tay nhỏ hơn số người check-in. Báo cáo chung sẽ lấy số thao tác check-in.">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div v-else class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </div>

                    <!-- Tab 1: Manual Count -->
                    <div v-show="activeTab === 'manual' || meeting.type === 'church'" class="space-y-6 animate-fade-in">
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Số lượng người tham dự thực tế</label>
                            <p class="text-xs text-gray-500 mb-4">Nhập tổng số người bạn đếm được trong buổi nhóm. (Sẽ dùng làm báo cáo chính lên Hội thánh nếu lớn hơn số người check-in đích danh).</p>
                            
                            <div class="relative max-w-sm mx-auto sm:mx-0">
                                <input 
                                    type="number" 
                                    v-model="form.manual_count" 
                                    min="0"
                                    class="block w-full text-center text-4xl sm:text-5xl font-black text-emerald-600 border-2 border-gray-200 rounded-[2rem] py-6 focus:ring-4 focus:ring-emerald-100 focus:border-emerald-500 transition-all placeholder:text-gray-300"
                                    placeholder="0"
                                >
                                <div class="absolute inset-y-0 right-6 flex items-center pointer-events-none">
                                    <span class="text-gray-400 font-bold">Người</span>
                                </div>
                            </div>
                            <div v-if="form.errors.manual_count" class="text-red-500 text-xs mt-2 font-medium">{{ form.errors.manual_count }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Ghi chú (Tùy chọn)</label>
                            <textarea 
                                v-model="form.notes"
                                rows="3"
                                class="block w-full border-gray-200 rounded-2xl shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm resize-none transition-colors"
                                placeholder="Ghi chú về buổi nhóm (vd: Có nhiều khách mời...)"
                            ></textarea>
                            <div v-if="form.errors.notes" class="text-red-500 text-xs mt-2 font-medium">{{ form.errors.notes }}</div>
                        </div>
                    </div>

                    <div v-if="(meeting.type === 'department' || meeting.type === 'holiday')" v-show="activeTab === 'named'" class="space-y-4 animate-fade-in">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-2 px-1 gap-2">
                            <p class="text-xs text-gray-500 font-medium">Đánh dấu thành viên có mặt. Các thành viên không được chọn sẽ tự động tính là Vắng mặt.</p>
                        </div>
                        <!-- Advanced Filter Row -->
                        <div class="flex flex-wrap gap-2 mb-3">
                            <!-- Tìm tên -->
                            <div class="relative flex-1 min-w-[140px]">
                                <input
                                    v-model="nameSearch"
                                    type="text"
                                    placeholder="Tìm tên..."
                                    class="w-full pl-8 border-gray-200 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2 pr-3"
                                >
                                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                            </div>
                            <!-- Tìm SĐT -->
                            <div class="relative min-w-[120px]">
                                <input
                                    v-model="phoneSearch"
                                    type="text"
                                    placeholder="SĐT..."
                                    class="w-full pl-8 border-gray-200 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2 pr-3"
                                >
                                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                            </div>
                            <!-- Lọc Tổ -->
                            <div v-if="teams && teams.length > 0" class="min-w-[140px]">
                                <select 
                                    v-model="selectedTeamId"
                                    class="w-full border-gray-200 rounded-xl text-sm font-medium focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2 pl-3 pr-8"
                                >
                                    <option :value="null">Tất cả các Tổ</option>
                                    <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- List of Members -->
                        <div 
                            v-for="member in filteredMembers" 
                            :key="member.id"
                            class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl border transition-all gap-4"
                            :class="member.status === 'present' ? 'bg-emerald-50/50 border-emerald-200' : 'bg-white border-gray-100 hover:border-gray-200'"
                        >
                            <div class="flex items-center justify-between sm:justify-start w-full sm:w-1/3 cursor-pointer" @click="toggleStatus(member.id)">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 transition-colors"
                                        :class="member.status === 'present' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-500'"
                                    >
                                        {{ member.full_name.split(' ').pop().charAt(0) }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm transition-colors" :class="member.status === 'present' ? 'text-emerald-900' : 'text-gray-900'">{{ member.full_name }}</h4>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ member.phone || 'Không có SĐT' }}</p>
                                    </div>
                                </div>
                                
                                <!-- Toggle Button / Action (Mobile View) -->
                                <div class="shrink-0 group flex space-x-1 sm:hidden">
                                     <button 
                                        @click.stop="setStatus(member.id, 'absent')"
                                        type="button" 
                                        class="p-2 rounded-lg text-xs font-bold transition-colors"
                                        :class="member.status === 'absent' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-white text-gray-400 border border-transparent hover:text-gray-600'"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                    <button 
                                        @click.stop="setStatus(member.id, 'present')"
                                        type="button" 
                                        class="p-2 rounded-lg text-xs font-bold transition-colors"
                                        :class="member.status === 'present' ? 'bg-emerald-500 text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Additional Metrics: Verse & Quiz (Only show if present) -->
                            <div v-if="member.status === 'present'" class="flex items-center space-x-4 w-full sm:w-auto border-t sm:border-t-0 border-emerald-100/50 pt-3 sm:pt-0 animate-fade-in">
                                <!-- Memorized Verse Checkbox -->
                                <label class="flex items-center space-x-2 cursor-pointer group">
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" v-model="member.memorized_verse" class="peer sr-only">
                                        <div class="w-5 h-5 rounded border-2 border-emerald-200 peer-checked:bg-emerald-500 peer-checked:border-emerald-500 transition-colors flex items-center justify-center">
                                            <svg class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    </div>
                                    <span class="text-xs font-medium text-emerald-800 group-hover:text-emerald-900 transition-colors">Thuộc câu gốc</span>
                                </label>

                                <!-- Bible Quiz Score -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-xs font-medium text-emerald-800">Đố KT:</label>
                                    <input 
                                        type="number" 
                                        v-model="member.quiz_score" 
                                        min="0"
                                        placeholder="0"
                                        class="w-16 text-center text-sm font-bold text-emerald-900 border-emerald-200 rounded-lg py-1 px-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:opacity-50 transition-colors"
                                    >
                                </div>
                            </div>
                            
                            <!-- Desktop Toggle Buttons -->
                            <div class="hidden sm:flex shrink-0 group space-x-1 pl-4 border-l border-emerald-100/50 ml-auto items-center">
                                <button 
                                    @click.stop="setStatus(member.id, 'present')"
                                    type="button" 
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                                    :class="member.status === 'present' ? 'bg-emerald-500 text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                >
                                    Có mặt
                                </button>
                                <button 
                                    @click.stop="setStatus(member.id, 'absent')"
                                    type="button" 
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors"
                                    :class="member.status === 'absent' ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-white text-gray-400 border border-transparent hover:text-gray-600'"
                                >
                                    Vắng
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Submit Button -->
        <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/80 backdrop-blur-md border-t border-gray-100 z-20 mb-16 sm:mb-0" style="padding-bottom: calc(1rem + env(safe-area-inset-bottom));">
            <div class="w-full flex items-center justify-between">
                <div class="hidden sm:block">
                    <p class="text-sm font-bold text-gray-900">Ban {{ department.name }}</p>
                    <p class="text-xs text-gray-500">Đã check-in: {{ checkedInCount }}</p>
                </div>
                <button 
                    @click="submit" 
                    :disabled="form.processing"
                    class="w-full sm:w-auto px-8 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-black rounded-xl shadow-lg shadow-emerald-600/20 active:scale-95 transition-all flex items-center justify-center disabled:opacity-70 disabled:cursor-not-allowed"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Lưu Báo Cáo Điểm Danh
                </button>
            </div>
        </div>
        <!-- SlideOver: Thêm Khách Mới (Nhanh) -->
        <SlideOver v-model="isAddPendingOpen" title="Thêm Khách Mới" size="sm">
            <template #default>
                <div class="p-6 space-y-5">
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-3">
                        <p class="text-sm text-amber-800 font-medium">Ghi nhận khách mới gặp buổi này. Thông tin sẽ được gửi cho Mục Sư / Trưởng Ban duyệt sau.</p>
                    </div>
                    <form @submit.prevent="submitPending" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-1.5">Họ và tên <span class="text-red-500">*</span></label>
                            <input v-model="pendingForm.full_name" type="text" required placeholder="Nguyễn Văn A"
                                class="block w-full border-gray-200 rounded-xl text-[15px] font-medium focus:ring-amber-500 focus:border-amber-500 py-3 bg-gray-50 transition-colors"
                                :class="{ 'border-red-300': pendingForm.errors.full_name }">
                            <p v-if="pendingForm.errors.full_name" class="text-red-500 text-sm mt-1">{{ pendingForm.errors.full_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-1.5">Số điện thoại</label>
                            <input v-model="pendingForm.phone" type="tel" placeholder="0901 234 567"
                                class="block w-full border-gray-200 rounded-xl text-[15px] font-medium focus:ring-amber-500 focus:border-amber-500 py-3 bg-gray-50 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-1.5">Ghi chú</label>
                            <textarea v-model="pendingForm.general_notes" rows="3" placeholder="Người quen của ai, được mời lần đầu..."
                                class="block w-full border-gray-200 rounded-xl text-[15px] font-medium focus:ring-amber-500 focus:border-amber-500 py-2.5 bg-gray-50 transition-colors resize-none">
                            </textarea>
                        </div>
                        <div class="pt-2 flex gap-3">
                            <button type="button" @click="isAddPendingOpen = false"
                                class="flex-1 py-3 border border-gray-200 rounded-xl text-[15px] font-bold text-gray-600 hover:bg-gray-50 transition-colors">
                                Hủy
                            </button>
                            <button type="submit" :disabled="pendingForm.processing || !pendingForm.full_name"
                                class="flex-1 py-3 bg-amber-500 text-white rounded-xl text-[15px] font-bold hover:bg-amber-600 disabled:opacity-50 transition-colors flex items-center justify-center gap-2">
                                <svg v-if="pendingForm.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Gửi lên duyệt
                            </button>
                        </div>
                    </form>
                </div>
            </template>
        </SlideOver>
    </PortalLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';

const props = defineProps({
    department: Object,
    teams: Array,
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
    meeting: Object,
    summary: Object,
    members: Array,
});

const activeTab = ref('manual');
const isAddPendingOpen = ref(false);

const pendingForm = useForm({
    full_name: '',
    phone: '',
    general_notes: '',
});

const submitPending = () => {
    pendingForm.post(route('portal.members.store-pending'), {
        preserveScroll: true,
        onSuccess: () => {
            isAddPendingOpen.value = false;
            pendingForm.reset();
        },
    });
};

const goBack = () => {
    if (window.history.length > 2) {
        window.history.back();
    } else {
        router.get(route('portal.attendance.index'));
    }
};

// Initialize form
const form = useForm({
    manual_count: props.summary.manual_count ?? '',
    notes: props.summary.notes || '',
    attendances: JSON.parse(JSON.stringify(props.members)) // deep copy
});

// Watch for changes in props.members (e.g. after a successful submission refresh)
watch(() => props.members, (newMembers) => {
    form.attendances = JSON.parse(JSON.stringify(newMembers));
}, { deep: true });

// Watch for summary updates (e.g. after successful save)
watch(() => props.summary, (newSummary) => {
    form.manual_count = newSummary.manual_count ?? '';
    form.notes = newSummary.notes || '';
}, { deep: true });

const selectedTeamId = ref(null);
const nameSearch = ref('');
const phoneSearch = ref('');

const filteredMembers = computed(() => {
    let list = [...form.attendances];
    // Sort ABC
    list.sort((a, b) => a.full_name.localeCompare(b.full_name, 'vi'));
    // Filter by team
    if (selectedTeamId.value) {
        list = list.filter(m => m.team_id === selectedTeamId.value);
    }
    // Filter by name
    if (nameSearch.value.trim()) {
        const q = nameSearch.value.toLowerCase();
        list = list.filter(m => m.full_name.toLowerCase().includes(q));
    }
    // Filter by phone
    if (phoneSearch.value.trim()) {
        list = list.filter(m => m.phone && m.phone.includes(phoneSearch.value.trim()));
    }
    return list;
});

// Computed Check-in count
const checkedInCount = computed(() => {
    return form.attendances.filter(m => m.status === 'present').length;
});

// Set specific status
const setStatus = (memberId, status) => {
    const idx = form.attendances.findIndex(m => m.id === memberId);
    if (idx !== -1) form.attendances[idx].status = status;
};

// Toggle logic: present -> absent -> present
const toggleStatus = (memberId) => {
    const idx = form.attendances.findIndex(m => m.id === memberId);
    if (idx !== -1) {
        form.attendances[idx].status = form.attendances[idx].status === 'present' ? 'absent' : 'present';
    }
};

const formattedDate = computed(() => {
    if (!props.meeting.date) return '';
    const d = new Date(props.meeting.date);
    return d.toLocaleDateString('vi-VN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});

const submit = () => {
    const finalManualCount = parseInt(form.manual_count);
    // Chỉ tự fill nếu không nhập gì (trống hoàn toàn)
    if (form.manual_count === '' || form.manual_count === null) {
        form.manual_count = checkedInCount.value;
    }
    form.post(route('portal.attendance.store', props.meeting.id), {
        preserveScroll: true,
        preserveState: false, // reload để lấy summary mới từ server
    });
};
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>