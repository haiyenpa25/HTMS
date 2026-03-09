<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" @open-switcher="isSwitchOpen = true">
        <div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-2">
            
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">Điểm danh Buổi nhóm</h2>
                    <p class="text-sm text-gray-500 font-medium mt-1">Chọn buổi nhóm để ghi nhận chuyên cần.</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Tháng -->
                    <div class="flex items-center space-x-2 shrink-0">
                        <span class="text-sm font-bold text-gray-700">Tháng:</span>
                        <select 
                            v-model="filters.month" 
                            class="border-gray-200 rounded-xl text-sm font-medium focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2 pl-3 pr-8"
                        >
                            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
                        </select>
                    </div>
                    <!-- Năm -->
                    <div class="flex items-center space-x-2 shrink-0">
                        <span class="text-sm font-bold text-gray-700">Năm:</span>
                        <select 
                            v-model="filters.year" 
                            class="border-gray-200 rounded-xl text-sm font-medium focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2 pl-3 pr-8"
                        >
                            <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                        </select>
                    </div>
                    <!-- Loại buổi nhóm -->
                    <div class="flex items-center space-x-2 shrink-0">
                        <span class="text-sm font-bold text-gray-700">Loại:</span>
                        <select 
                            v-model="filters.type"
                            class="border-gray-200 rounded-xl text-sm font-medium focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2 pl-3 pr-8"
                        >
                            <option value="">Tất cả</option>
                            <option value="church">Hội Thánh chung</option>
                            <option value="department">Ban Ngành</option>
                            <option value="holiday">Sự kiện / Lễ</option>
                        </select>
                    </div>
                    <!-- Tìm kiếm nâng cao -->
                    <div class="flex-1 min-w-[180px] relative">
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Chủ đề, câu gốc..."
                            class="w-full pl-9 border-gray-200 rounded-xl text-sm font-medium focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 py-2 pr-3"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!meetings.data || meetings.data.length === 0" class="bg-white rounded-3xl shadow-sm p-8 border border-gray-100 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 mb-4 rounded-full bg-gray-50 text-gray-400 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Chưa có buổi nhóm nào</h3>
                <p class="text-gray-500 text-sm mt-1 max-w-sm">Không tìm thấy buổi nhóm nào cho Tháng {{ filters.month }}/{{ filters.year }}. Danh sách các buổi nhóm của Ban sẽ hiển thị ở đây để bạn có thể điểm danh.</p>
            </div>

            <!-- List of Meetings -->
            <div v-else class="space-y-4">
                <div 
                    v-for="meeting in meetings.data" 
                    :key="meeting.id" 
                    class="block bg-white rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all group cursor-pointer relative"
                    @click="router.get(route('portal.attendance.show', meeting.id))"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 pr-12">
                            <!-- Date Icon -->
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex flex-col items-center justify-center shrink-0 border border-emerald-100 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                <span class="text-xs font-bold uppercase leading-none mb-0.5">{{ new Date(meeting.date).toLocaleDateString('vi-VN', { month: 'short' }) }}</span>
                                <span class="text-lg font-black leading-none">{{ new Date(meeting.date).getDate() }}</span>
                            </div>
                            
                            <div>
                                <h3 class="font-bold text-gray-900 text-base sm:text-lg group-hover:text-emerald-700 transition-colors">{{ meeting.topic || 'Buổi nhóm định kỳ' }}</h3>
                                <div class="flex items-center text-xs text-gray-500 font-medium mt-1 space-x-2 flex-wrap gap-y-1">
                                    <span class="flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ meeting.time ? meeting.time.substring(0,5) : '--:--' }}
                                    </span>
                                    <span v-if="meeting.type === 'church'" class="flex items-center text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">Hội Thánh chung</span>
                                    <span v-else-if="meeting.type === 'department'" class="flex items-center text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Ban Ngành</span>
                                    <span v-else-if="meeting.type === 'holiday'" class="flex items-center text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Sự kiện / Lễ</span>
                                    <span v-if="meeting.memory_verse" class="flex items-center text-gray-500 italic truncate max-w-[160px]">📖 {{ meeting.memory_verse }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions Container: Edit Button + Arrow -->
                        <div class="shrink-0 flex items-center space-x-2 relative z-10">
                            <!-- Edit Meeting Button (Click event must stop propagation so it doesn't trigger the Router link) -->
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
    type:   props.filters?.type   || '',
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

</script>
