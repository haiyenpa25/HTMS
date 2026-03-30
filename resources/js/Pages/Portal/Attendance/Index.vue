<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" @open-switcher="isSwitchOpen = true">
        <div class="space-y-6 w-full">
            
            <div class="flex items-start justify-between gap-3 mb-2">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-2xl font-black text-gray-900 tracking-tight truncate">Điểm danh Buổi nhóm</h2>
                        <!-- Tooltip Helper -->
                        <div class="relative group cursor-help mt-1 shrink-0" tabindex="0">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="absolute sm:top-full top-auto bottom-full sm:bottom-auto mb-2 sm:mb-0 left-0 sm:mt-2 w-72 p-3 bg-gray-900 text-white text-[11px] font-medium rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible group-focus:opacity-100 group-focus:visible transition-all z-50 shadow-xl pointer-events-none">
                                Chọn một buổi nhóm để ghi điểm danh thủ công. Hoặc bạn có thể xuất Template Excel của buổi nhóm đó, điền vào và dùng nút "Import Excel" để cập nhật nhanh chóng.
                                <div class="absolute sm:bottom-full sm:top-auto top-full left-4 border-4 border-transparent sm:border-b-gray-900 border-t-gray-900"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm font-medium mt-1 text-gray-500">Chọn buổi nhóm để ghi nhận chuyên cần.</p>
                </div>
                <!-- Import Excel Button -->
                <button
                    @click="showImportModal = true"
                    title="Cập nhật điểm danh bằng file Excel"
                    class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm whitespace-nowrap"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span class="hidden sm:inline">Import</span> Excel
                </button>
            </div>

            <!-- Import Modal -->
            <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-black text-gray-900">Import Điểm Danh Excel</h3>
                        <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitImport" enctype="multipart/form-data" class="space-y-5">
                        <!-- Meeting ID -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">Buổi nhóm *</label>
                            <select v-model="importForm.meeting_id" class="w-full border-gray-200 rounded-xl text-sm focus:ring-emerald-500 focus:border-emerald-500 py-2.5" required>
                                <option value="">-- Chọn buổi nhóm --</option>
                                <option v-for="m in meetings.data" :key="m.id" :value="m.id">
                                    [#{{ m.id }}] {{ m.date }} – {{ m.topic || 'Buổi nhóm' }}
                                </option>
                            </select>
                            <p class="text-sm text-gray-500 mt-1.5">💡 Lọc tháng/năm bên trên để tìm buổi nhóm</p>
                        </div>
                        <!-- File -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">File Excel (.xlsx) *</label>
                            <input
                                type="file"
                                accept=".xlsx,.xls"
                                @change="e => importForm.file = e.target.files[0]"
                                class="w-full text-sm text-gray-600 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer"
                                required
                            />
                            <p class="text-sm text-gray-500 mt-1.5">Dùng file template tải xuống từ 📥 trong buổi nhóm</p>
                        </div>
                        <!-- Error -->
                        <div v-if="importError" class="bg-red-50 border border-red-200 text-red-700 text-sm font-medium rounded-xl px-4 py-3">{{ importError }}</div>
                        <!-- Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-4">
                            <button type="button" @click="showImportModal = false" class="px-5 py-2.5 text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-colors">Hủy</button>
                            <button type="submit" :disabled="importLoading" class="px-6 py-2.5 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm disabled:opacity-60 flex items-center justify-center min-w-[120px]">
                                <svg v-if="importLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ importLoading ? 'Đang tải...' : 'Import' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            <!-- Filters -->
            <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-5 border border-gray-100">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Tháng -->
                    <div class="flex items-center space-x-2 shrink-0">
                        <span class="text-sm font-bold text-gray-700">Tháng:</span>
                        <select 
                            v-model="filters.month" 
                            class="border-gray-200 rounded-xl text-sm font-medium focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2.5 pl-3 pr-8"
                        >
                            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
                        </select>
                    </div>
                    <!-- Năm -->
                    <div class="flex items-center space-x-2 shrink-0">
                        <span class="text-sm font-bold text-gray-700">Năm:</span>
                        <select 
                            v-model="filters.year" 
                            class="border-gray-200 rounded-xl text-sm font-medium focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2.5 pl-3 pr-8"
                        >
                            <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>
                    <!-- Loại buổi nhóm -->
                    <div class="flex items-center space-x-2 shrink-0">
                        <span class="text-sm font-bold text-gray-700">Loại:</span>
                        <select 
                            v-model="filters.type"
                            class="border-gray-200 rounded-xl text-sm font-medium focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2.5 pl-3 pr-8"
                        >
                            <option value="">Tất cả</option>
                            <option value="church">Hội Thánh chung</option>
                            <option value="department">Ban Ngành</option>
                            <option value="holiday">Sự kiện / Lễ</option>
                        </select>
                    </div>
                    <!-- Tìm kiếm nâng cao -->
                    <div class="flex-1 min-w-[200px] relative">
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Chủ đề, câu gốc..."
                            class="w-full pl-10 border-gray-200 rounded-xl text-sm font-medium focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2.5 pr-4"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!meetings.data || meetings.data.length === 0" class="bg-white rounded-[1.5rem] shadow-sm p-8 sm:p-12 border border-gray-100 flex flex-col items-center justify-center text-center">
                <div class="w-20 h-20 mb-5 rounded-full bg-gray-50 text-gray-400 flex items-center justify-center border border-gray-100 shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
                <h3 class="text-xl font-black text-gray-900 mb-1.5">Chưa có buổi nhóm</h3>
                <p class="text-gray-500 text-base max-w-sm leading-relaxed">Không tìm thấy buổi nhóm nào cho Tháng {{ filters.month }}/{{ filters.year }}. Danh sách các buổi nhóm của Ban sẽ hiển thị ở đây.</p>
            </div>

            <!-- List of Meetings -->
            <div v-else class="space-y-4">
                <div 
                    v-for="meeting in meetings.data" 
                    :key="meeting.id" 
                    class="block bg-white rounded-2xl p-5 sm:p-6 border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all duration-200 group cursor-pointer relative"
                    @click="router.get(route('portal.attendance.show', meeting.id))"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4 sm:gap-5 pr-12 min-w-0">
                            <!-- Date Icon -->
                            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex flex-col items-center justify-center shrink-0 border border-emerald-100 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                                <span class="text-[11px] font-black uppercase tracking-wider leading-none mb-1 opacity-80">{{ new Date(meeting.date).toLocaleDateString('vi-VN', { month: 'short' }) }}</span>
                                <span class="text-xl font-black leading-none">{{ new Date(meeting.date).getDate() }}</span>
                            </div>
                            
                            <div class="min-w-0">
                                <h3 class="font-bold text-gray-900 text-base sm:text-lg group-hover:text-emerald-700 transition-colors truncate">{{ meeting.topic || 'Buổi nhóm định kỳ' }}</h3>
                                <div class="flex items-center text-sm text-gray-500 font-medium mt-1.5 space-x-2.5 flex-wrap gap-y-2">
                                    <span class="flex items-center shrink-0">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ meeting.time ? meeting.time.substring(0,5) : '--:--' }}
                                    </span>
                                    <span v-if="meeting.type === 'church'" class="flex items-center text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md font-bold text-[13px] shrink-0 border border-indigo-100">Hội Thánh chung</span>
                                    <span v-else-if="meeting.type === 'department'" class="flex items-center text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-md font-bold text-[13px] shrink-0 border border-emerald-100">Ban Ngành</span>
                                    <span v-else-if="meeting.type === 'holiday'" class="flex items-center text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-md font-bold text-[13px] shrink-0 border border-amber-100">Sự kiện / Lễ</span>
                                    <span v-if="meeting.memory_verse" class="flex items-center text-gray-500 italic truncate max-w-[180px] sm:max-w-xs shrink-0"><span class="mr-1">📖</span> {{ meeting.memory_verse }}</span>
                                    <!-- Badge buổi nghỉ — ưu tiên hiển thị nhất -->
                                    <span
                                        v-if="meeting.is_cancelled"
                                        class="flex items-center gap-1 text-red-700 bg-red-50 px-2.5 py-0.5 rounded-md font-bold text-[13px] shrink-0 border border-red-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636L5.636 18.364M5.636 5.636l12.728 12.728"/></svg>
                                        Buổi Nghỉ
                                    </span>
                                    <template v-else>
                                        <!-- Badge trạng thái điểm danh -->
                                        <span
                                            v-if="meeting.attendance_summary && meeting.attendance_summary.manual_count > 0"
                                            class="flex items-center gap-1 text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-md font-bold text-[13px] shrink-0 border border-emerald-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            {{ meeting.attendance_summary.manual_count }} người
                                        </span>
                                        <span
                                            v-else
                                            class="flex items-center gap-1 text-orange-600 bg-orange-50 px-2.5 py-0.5 rounded-md font-bold text-[13px] shrink-0 border border-orange-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Chưa ghi
                                        </span>
                                    </template>
                                </div>
                            </div>

                        </div>
                        
                        <!-- Actions Container: Edit Button + Export + Arrow -->
                        <div class="shrink-0 flex items-center space-x-2 relative z-10">
                            <!-- Export Template Button -->
                            <a
                                :href="route('portal.attendance.export', meeting.id)"
                                @click.stop
                                class="w-8 h-8 rounded-full bg-white text-gray-400 hover:bg-emerald-50 hover:text-emerald-600 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100 focus:opacity-100 shadow-sm border border-gray-100"
                                title="Xuất template điểm danh Excel"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </a>
                            <!-- Edit Meeting Button -->
                            <button 
                                @click.stop="openEditMeeting(meeting)" 
                                class="w-8 h-8 rounded-full bg-white text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100 focus:opacity-100 shadow-sm border border-gray-100"
                                title="Cập nhật hoặc xóa buổi nhóm"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            
                            <!-- Action Arrow -->
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 text-gray-400 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-colors">
                                <svg class="w-4 h-4" transform="rotate(-45)" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination (simple) -->
                <div v-if="meetings.links && meetings.links.length > 3" class="flex justify-center mt-6 overflow-x-auto pb-2">
                    <div class="flex space-x-1 shrink-0">
                        <template v-for="(link, i) in meetings.links" :key="i">
                            <Link v-if="link.url" :href="link.url" class="px-3 py-1 text-sm font-medium rounded-lg transition-colors border" :class="link.active ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'" v-html="link.label"></Link>
                            <span v-else class="px-3 py-1 text-sm font-medium rounded-lg border border-transparent text-gray-400" v-html="link.label"></span>
                        </template>
                    </div>
                </div>
            </div>
            
        </div>

        <SlideOver v-model="isSwitchOpen" title="Chuyển đổi Ban Sinh Hoạt" size="md">
            <!-- Same Switcher Template -->
            <template #default>
                <div class="p-6 space-y-5">
                   <div class="space-y-2">
                      <div v-for="dept in availableDepartments" :key="dept.id" @click="switchDept(dept.id)" class="w-full text-left p-4 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between group" :class="department?.id === dept.id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-white hover:border-gray-300 hover:bg-gray-50'">
                         <div class="flex items-center space-x-4 shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-black" :class="department?.id === dept.id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'">{{ dept.name.charAt(0) }}</div>
                            <h4 class="text-sm font-black" :class="department?.id === dept.id ? 'text-blue-900' : 'text-gray-900'">{{ dept.name }}</h4>
                         </div>
                         <button v-if="department?.id !== dept.id" @click.stop="switchDept(dept.id)" class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">Chọn</button>
                      </div>
                   </div>
                </div>
            </template>
        </SlideOver>

        <!-- Edit Meeting SlideOver -->
        <SlideOver 
            v-model="isMeetingFormOpen" 
            title="Sửa Buổi Nhóm" 
            description="Cập nhật hoặc xóa thông tin buổi nhóm hiện tại."
            size="md"
        >
            <MeetingForm 
                v-if="activeMeeting"
                :meeting="activeMeeting" 
                @close="isMeetingFormOpen = false"
                @success="handleMeetingSuccess"
            />
        </SlideOver>

    </PortalLayout>
</template>

<script setup>
import { ref, watch, reactive, provide } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';
import MeetingForm from '@/Pages/Meetings/Partials/MeetingForm.vue';
import { debounce } from 'lodash';

const props = defineProps({
    department: Object,
    departments: Array, // Expected for MeetingForm
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
    meetings: Object,
    filters: Object,
});

// Provide departments for MeetingForm which uses usePage().props.departments
provide('departments', props.departments);

const isSwitchOpen = ref(false);

const switchDept = (deptId) => {
    router.post(route('portal.switch-context'), { department_id: deptId }, { preserveScroll: true, onSuccess: () => isSwitchOpen.value = false });
};

// Edit Meeting Logic
const isMeetingFormOpen = ref(false);
const activeMeeting = ref(null);

const openEditMeeting = (meeting) => {
    activeMeeting.value = meeting;
    isMeetingFormOpen.value = true;
};

const handleMeetingSuccess = () => {
    isMeetingFormOpen.value = false;
    activeMeeting.value = null;
    router.reload({ only: ['meetings'] });
};

// Filter Logic
const filters = reactive({
    month:  props.filters?.month  || new Date().getMonth() + 1,
    year:   props.filters?.year   || new Date().getFullYear(),
    type:   props.filters?.type !== undefined ? props.filters.type : 'church',
    search: props.filters?.search || '',
});

const currentYear = new Date().getFullYear();
const availableYears = [currentYear - 2, currentYear - 1, currentYear, currentYear + 1, currentYear + 2];

watch(filters, debounce(() => {
    router.get(route('portal.attendance.index'), {
        month:  filters.month,
        year:   filters.year,
        type:   filters.type,
        search: filters.search,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300));

// Import Modal Logic
const showImportModal = ref(false);
const importLoading = ref(false);
const importError = ref('');
const importForm = ref({ meeting_id: '', file: null });

const submitImport = () => {
    if (!importForm.value.meeting_id || !importForm.value.file) {
        importError.value = 'Vui lòng chọn buổi nhóm và file Excel.';
        return;
    }
    importError.value = '';
    importLoading.value = true;

    const data = new FormData();
    data.append('meeting_id', importForm.value.meeting_id);
    data.append('file', importForm.value.file);

    router.post(route('portal.attendance.import'), data, {
        forceFormData: true,
        onSuccess: () => {
            showImportModal.value = false;
            importForm.value = { meeting_id: '', file: null };
            router.reload({ only: ['meetings'] });
        },
        onError: (errors) => {
            importError.value = errors.file || 'Có lỗi xảy ra khi import.';
        },
        onFinish: () => { importLoading.value = false; },
    });
};

</script>