<template>
    <PortalLayout title="Thăm Viếng & Chăm Sóc">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-black text-xl text-gray-900 leading-tight flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        Công Tác Thăm Viếng
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Ghi nhận và theo dõi công tác chăm sóc tín hữu.</p>
                </div>
                <div v-if="canManage" class="flex space-x-3">
                    <button @click="openForm()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                        + Ghi nhận Thăm viếng
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Tabs -->
            <div class="mb-6 border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'history'" :class="[activeTab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700', 'whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm']">
                        Lịch sử Thăm Viếng
                    </button>
                    <button v-if="suggestions" @click="activeTab = 'suggestions'" :class="[activeTab === 'suggestions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700', 'whitespace-nowrap flex py-4 px-1 border-b-2 font-medium text-sm']">
                        Đề xuất Kế hoạch
                        <span v-if="suggestions.length > 0" class="ml-2 bg-blue-100 text-blue-600 py-0.5 px-2.5 rounded-full text-xs font-medium inline-block">{{ suggestions.length }}</span>
                    </button>
                </nav>
            </div>

            <div v-if="activeTab === 'history'">
                <!-- Data Toolbar -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <!-- Reason Filter -->
                    <select v-model="filterForm.reason" class="block w-full sm:w-48 border-gray-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 py-2.5 transition-colors">
                        <option value="">-- Tất cả lý do --</option>
                        <option v-for="r in reasons" :key="r" :value="r">{{ r }}</option>
                    </select>
                    
                    <!-- Month Filter -->
                     <input type="month" v-model="selectedMonthYear" class="block w-full sm:w-48 border-gray-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 py-2.5 transition-colors" />
                </div>
                
                <div class="flex items-center gap-2">
                     <button @click="applyFilters" class="px-4 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-bold hover:bg-gray-800 transition-colors shadow-sm w-full sm:w-auto text-center">
                        Lọc dữ liệu
                    </button>
                    <button @click="resetFilters" v-if="hasActiveFilter" class="px-4 py-2.5 bg-gray-100 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-200 transition-colors w-full sm:w-auto text-center">
                        Xoá lọc
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="visitations.data.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-bold text-gray-900">Chưa có dữ liệu</h3>
                <p class="mt-1 text-sm text-gray-500">Chưa có lịch sử thăm viếng nào được ghi nhận.</p>
                <div class="mt-6" v-if="canManage">
                    <button @click="openForm()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        + Tạo mới
                    </button>
                </div>
            </div>

            <!-- List View -->
            <div v-else class="space-y-4">
                <div v-for="visitation in visitations.data" :key="visitation.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <!-- Main Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-bold text-gray-900">{{ visitation.member?.full_name || 'N/A' }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="getReasonColor(visitation.reason)">
                                        {{ visitation.reason }}
                                    </span>
                                    <span v-if="visitation.visitation_type === 'church'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Hội Thánh
                                    </span>
                                    <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ visitation.department?.name || 'Ban Ngành' }}
                                    </span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500 gap-4 mb-4">
                                     <div class="flex items-center">
                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ formatDate(visitation.visit_date) }}
                                     </div>
                                     <div class="flex items-center" v-if="visitation.member?.phone">
                                        <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        {{ visitation.member.phone }}
                                     </div>
                                </div>

                                <!-- Sensitive Content details -->
                                <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                                    <div v-if="visitation.content">
                                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Nội dung chi tiết / Tâm vấn</h4>
                                        <p class="text-sm text-gray-600 whitespace-pre-line" :class="{'text-red-500 font-semibold italic': visitation.content.includes('***')}">
                                            {{ visitation.content }}
                                        </p>
                                    </div>
                                    <div v-if="visitation.prayer_points">
                                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Vấn đề cầu nguyện</h4>
                                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ visitation.prayer_points }}</p>
                                    </div>
                                    <div v-if="visitation.gifts">
                                         <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Quà tặng</h4>
                                        <p class="text-sm text-gray-600">{{ visitation.gifts }}</p>
                                    </div>
                                </div>
                                
                                <!-- Team -->
                                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <span class="text-xs font-medium text-gray-500 mr-2">Đoàn đi thăm:</span>
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="visitor in visitation.visitors" :key="visitor.id" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                                {{ visitor.full_name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center sm:flex-col gap-2" v-if="canManage">
                                <button @click="openForm(visitation)" class="px-3 py-1.5 bg-gray-50 text-gray-700 text-xs font-bold rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors w-full sm:w-auto text-center">
                                    Chỉnh sửa
                                </button>
                                <button @click="confirmDelete(visitation)" class="px-3 py-1.5 bg-red-50 text-red-700 text-xs font-bold rounded-lg border border-red-100 hover:bg-red-100 transition-colors w-full sm:w-auto text-center">
                                    Xoá
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination Placeholder -->
                <div v-if="visitations.links && visitations.data.length > 0" class="mt-6 flex justify-center">
                    <!-- Basic pagination, should use a proper component if available -->
                    <div class="flex space-x-1">
                        <template v-for="(link, k) in visitations.links" :key="k">
                            <Link v-if="link.url" :href="link.url" class="px-3 py-1 border rounded-lg text-sm transition-colors" :class="link.active ? 'bg-blue-600 text-white font-bold border-blue-600' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200'" v-html="link.label"></Link>
                            <span v-else class="px-3 py-1 border border-gray-100 rounded-lg text-sm text-gray-400 cursor-not-allowed" v-html="link.label"></span>
                        </template>
                    </div>
                </div>
            </div>
            </div> <!-- End History Tab -->

            <!-- Suggestions Tab -->
            <div v-else-if="activeTab === 'suggestions'">
                <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 mb-6 flex items-start">
                    <svg class="h-5 w-5 text-blue-500 mt-0.5 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-bold text-blue-900">Tính năng Đề xuất Thông minh</h4>
                        <p class="text-xs text-blue-700 mt-1">Hệ thống gợi ý các tín hữu cần được quan tâm, bao gồm những người vắng mặt liên tục trên 3 tuần hoặc chưa được thăm viếng trong vòng 6 tháng qua.</p>
                    </div>
                </div>

                <div v-if="suggestions.length === 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                    <h3 class="text-sm font-bold text-gray-900">Tuyệt vời!</h3>
                    <p class="mt-1 text-sm text-gray-500">Tất cả tín hữu đều đã được thăm viếng hoặc chăm sóc chu đáo gần đây.</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="member in suggestions" :key="member.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex items-center justify-between p-5">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold mr-4">
                                {{ member.full_name?.charAt(0) || 'N' }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">{{ member.full_name }}</h3>
                                <p class="text-xs text-gray-500">{{ member.phone || 'Chưa cập nhật SĐT' }}</p>
                            </div>
                            <span class="ml-4 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                Chưa thăm / vắng lâu
                            </span>
                        </div>
                        <button @click="openFormForSuggestion(member)" class="px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg border border-blue-100 hover:bg-blue-100 transition-colors">
                            Lên Kế Hoạch
                        </button>
                    </div>
                </div>
            </div> <!-- End Suggestions Tab -->
        </div>

        <!-- SlideOver Form -->
        <SlideOver v-model="isFormOpen" :title="isEditing ? 'Cập nhật Thăm Viếng' : 'Ghi nhận Thăm Viếng'" size="md">
            <template #default>
                <form @submit.prevent="submitForm" class="p-6 space-y-6">
                    
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 mb-6" v-if="!isGlobalAdmin && form.visitation_type === 'church'">
                        <p class="text-sm text-blue-800"><strong class="font-bold">Lưu ý:</strong> Yêu cầu thăm viếng này sẽ được chuyển lên Hội Thánh. Bạn sẽ không thể xem nội dung chi tiết sau khi gửi trừ khi bạn nằm trong đoàn đi thăm.</p>
                    </div>

                    <!-- Người được thăm -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Tín hữu được thăm <span class="text-red-500">*</span></label>
                        <select v-model="form.member_id" required class="block w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm py-2.5 bg-gray-50">
                            <option :value="null" disabled>Chọn tín hữu</option>
                            <option v-for="member in members" :key="member.id" :value="member.id">{{ member.full_name }} ({{ member.phone || 'Không có SĐT' }})</option>
                        </select>
                        <div v-if="form.errors.member_id" class="text-red-500 text-xs mt-1">{{ form.errors.member_id }}</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Cấp độ -->
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Loại hình thăm <span class="text-red-500">*</span></label>
                            <select v-model="form.visitation_type" required class="block w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm py-2.5 bg-gray-50">
                                <option value="department" v-if="department">Ban Ngành ({{ department.name }})</option>
                                <option value="church">Hội Thánh</option>
                            </select>
                            <div v-if="form.errors.visitation_type" class="text-red-500 text-xs mt-1">{{ form.errors.visitation_type }}</div>
                        </div>
                        
                        <!-- Ngày thăm -->
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Ngày thăm <span class="text-red-500">*</span></label>
                            <input type="date" v-model="form.visit_date" required class="block w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm py-2.5 bg-gray-50">
                            <div v-if="form.errors.visit_date" class="text-red-500 text-xs mt-1">{{ form.errors.visit_date }}</div>
                        </div>
                    </div>

                    <!-- Lý do -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Lý do chính <span class="text-red-500">*</span></label>
                        <select v-model="form.reason" required class="block w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm py-2.5 bg-gray-50">
                            <option :value="null" disabled>Chọn lý do</option>
                            <option v-for="r in reasons" :key="r" :value="r">{{ r }}</option>
                        </select>
                        <div v-if="form.errors.reason" class="text-red-500 text-xs mt-1">{{ form.errors.reason }}</div>
                    </div>

                    <!-- Đoàn đi thăm (Multiple) -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Đoàn đi thăm <span class="text-red-500">*</span></label>
                        <select v-model="form.visitor_ids" multiple required class="block w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm py-2.5 min-h-[120px] bg-gray-50">
                            <option v-for="member in members" :key="member.id" :value="member.id">{{ member.full_name }}</option>
                        </select>
                        <p class="text-[10px] text-gray-500 mt-1">Gợi ý: Giữ phím Ctrl (hoặc Cmd) để chọn nhiều người.</p>
                        <div v-if="form.errors.visitor_ids" class="text-red-500 text-xs mt-1">{{ form.errors.visitor_ids }}</div>
                    </div>

                    <!-- Nội dung nhạy cảm -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">
                            Nội dung chi tiết / Tâm vấn
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">🔒 Nhạy cảm</span>
                        </label>
                        <p class="text-xs text-gray-500 mb-2">Chỉ Mục sư và người nằm trong Đoàn đi thăm mới được xem nội dung này.</p>
                        <textarea v-model="form.content" rows="4" class="block w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm py-2.5 bg-gray-50" placeholder="Biên bản tâm vấn, nội dung trò chuyện chính..."></textarea>
                        <div v-if="form.errors.content" class="text-red-500 text-xs mt-1">{{ form.errors.content }}</div>
                    </div>

                    <!-- Vấn đề cầu nguyện -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Vấn đề cầu nguyện</label>
                        <textarea v-model="form.prayer_points" rows="3" class="block w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm py-2.5 bg-gray-50" placeholder="Các mục cần Ban Cầu nguyện hoặc HT cầu thay..."></textarea>
                        <div v-if="form.errors.prayer_points" class="text-red-500 text-xs mt-1">{{ form.errors.prayer_points }}</div>
                    </div>

                    <!-- Quà tặng -->
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-2">Quà tặng / Trợ cấp (Tùy chọn)</label>
                        <input type="text" v-model="form.gifts" class="block w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm py-2.5 bg-gray-50" placeholder="VD: 1 phần quà, 500.000đ...">
                        <div v-if="form.errors.gifts" class="text-red-500 text-xs mt-1">{{ form.errors.gifts }}</div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 border-t border-gray-100">
                        <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-colors">
                            {{ form.processing ? 'Đang lưu...' : (isEditing ? 'Lưu Cập Nhật' : 'Tạo Hồ Sơ Thăm Viếng') }}
                        </button>
                    </div>
                </form>
            </template>
        </SlideOver>
        
    </PortalLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';

const props = defineProps({
    visitations: Object,
    members: Array,
    suggestions: Array,
    filters: Object,
    canManage: Boolean,
    visitationTypes: Object,
    reasons: Array,
    department: Object,
    isGlobalAdmin: Boolean
});

const activeTab = ref('history');

// Filtering
const filterForm = useForm({
    reason: props.filters.reason || '',
    month: props.filters.month || '',
    year: props.filters.year || '',
});

const selectedMonthYear = ref(
    props.filters.year && props.filters.month 
        ? `${props.filters.year}-${String(props.filters.month).padStart(2, '0')}` 
        : ''
);

watch(selectedMonthYear, (val) => {
    if (val) {
        const [year, month] = val.split('-');
        filterForm.year = year;
        filterForm.month = month;
    } else {
        filterForm.year = '';
        filterForm.month = '';
    }
});

const hasActiveFilter = computed(() => {
    return filterForm.reason !== '' || filterForm.month !== '';
});

const applyFilters = () => {
    filterForm.get(route('ministry.visitation.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filterForm.reason = '';
    filterForm.month = '';
    filterForm.year = '';
    selectedMonthYear.value = '';
    applyFilters();
};

// Form Management
const isFormOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    member_id: null,
    visitation_type: props.department ? 'department' : 'church',
    visit_date: new Date().toISOString().split('T')[0],
    reason: null,
    content: '',
    prayer_points: '',
    gifts: '',
    visitor_ids: []
});

const openForm = (visitation = null) => {
    if (visitation) {
        isEditing.value = true;
        editingId.value = visitation.id;
        form.member_id = visitation.member_id;
        form.visitation_type = visitation.visitation_type;
        form.visit_date = visitation.visit_date;
        form.reason = visitation.reason;
        
        // Hide content if it's masked
        form.content = visitation.content === '*** (Chỉ Mục sư & Người thăm viếng được xem) ***' ? '' : visitation.content;
        
        form.prayer_points = visitation.prayer_points;
        form.gifts = visitation.gifts;
        form.visitor_ids = visitation.visitors ? visitation.visitors.map(v => v.id) : [];
    } else {
        isEditing.value = false;
        editingId.value = null;
        form.reset();
        form.visitation_type = props.department ? 'department' : 'church';
        form.visit_date = new Date().toISOString().split('T')[0];
    }
    isFormOpen.value = true;
};

const openFormForSuggestion = (member) => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.visitation_type = props.department ? 'department' : 'church';
    form.visit_date = new Date().toISOString().split('T')[0];
    form.member_id = member.id;
    isFormOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('ministry.visitation.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('ministry.visitation.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            }
        });
    }
};

const confirmDelete = (visitation) => {
    if (confirm('Bạn có chắc chắn muốn xoá lịch sử thăm viếng này?')) {
        router.delete(route('ministry.visitation.destroy', visitation.id), {
            preserveScroll: true
        });
    }
};

// Helpers
const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(date);
};

const getReasonColor = (reason) => {
    const map = {
        'ốm đau': 'bg-red-100 text-red-800',
        'mới tin Chúa': 'bg-green-100 text-green-800',
        'khích lệ': 'bg-teal-100 text-teal-800',
        'khác': 'bg-gray-100 text-gray-800'
    };
    return map[reason] || 'bg-gray-100 text-gray-800';
};
</script>
