<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" @open-switcher="isSwitchOpen = true">
        <Head title="Tài chính Ban ngành" />

        <div class="space-y-6 w-full">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-2xl font-black text-gray-900">Tài chính Ban ngành</h2>
                        <!-- Tooltip Helper -->
                        <div class="relative group cursor-help mt-1">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-emerald-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="absolute bottom-full left-4 sm:left-1/2 sm:-translate-x-1/2 mb-2 w-64 p-3 bg-gray-900 text-white text-xs font-medium rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-20 shadow-xl pointer-events-none">
                                Chỉ có Quỹ thu được từ việc dâng hiến trong Buổi Nhóm hàng tuần của Ban ngành mới được quản lý ở đây. Bấm vào từng buổi nhóm trong bảng để bắt đầu ghi Nhận/Chi tiền.
                                <div class="absolute top-full left-4 sm:left-1/2 sm:-translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-[13px] text-gray-500 mt-1">{{ department?.name }} · Tháng {{ localMonth }}/{{ localYear }} · Bấm vào buổi nhóm để ghi tiền</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Period Picker -->
                    <div class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-xl px-4 py-2.5 shadow-sm">
                        <select v-model="localMonth" @change="updatePeriod" class="text-[15px] font-medium text-gray-700 border-none focus:ring-0 p-0 pr-1 cursor-pointer">
                            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
                        </select>
                        <input v-model="localYear" @change="updatePeriod" type="number" class="w-16 text-[15px] border-none focus:ring-0 p-0 text-center font-medium" min="2020" max="2099">
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-100/50">
                    <p class="text-xs font-bold uppercase tracking-widest text-emerald-100">Tổng Thu</p>
                    <p class="text-2xl font-black mt-2">{{ formatCurrency(summary.month_income) }}</p>
                </div>
                <div class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-2xl p-5 text-white shadow-lg shadow-rose-100/50">
                    <p class="text-xs font-bold uppercase tracking-widest text-rose-100">Tổng Chi</p>
                    <p class="text-2xl font-black mt-2">{{ formatCurrency(summary.month_expense) }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-widest text-gray-500">Tồn Tháng Trước</p>
                    <p class="text-2xl font-black text-gray-900 mt-2">{{ formatCurrency(summary.opening_balance) }}</p>
                </div>
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-5 text-white shadow-lg shadow-blue-100/50">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-widest text-blue-200">Tổng Tồn Hiện Tại</p>
                    <p class="text-2xl font-black mt-2">{{ formatCurrency(summary.closing_balance) }}</p>
                </div>
            </div>

            <!-- Meetings Table — click vào để ghi tiền -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-green-900 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] sm:text-base font-black text-white">D. TÀI CHÍNH — TIỀN DÂNG BAN NGÀNH SINH HOẠT</h3>
                        <p class="text-[11px] sm:text-xs text-green-300 mt-0.5">Chỉ buổi nhóm Ban Ngành mới có tiền dâng · Tháng {{ localMonth }}/{{ localYear }}</p>
                    </div>
                    <span class="bg-white/20 text-white text-[13px] font-bold px-3 py-1 rounded-full">{{ meetings.length }} buổi</span>
                </div>

                <!-- Desktop Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-slate-50 border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-[13px] font-bold text-slate-800 whitespace-nowrap">Ngày</th>
                                <th class="px-5 py-3.5 text-left text-[13px] font-bold text-slate-800">Chủ đề</th>
                                <th class="px-5 py-3.5 text-left text-[13px] font-bold text-slate-800 hidden lg:table-cell">Kinh thánh</th>
                                <th class="px-5 py-3.5 text-left text-[13px] font-bold text-slate-800 hidden xl:table-cell">Câu gốc</th>
                                <th class="px-5 py-3.5 text-center text-[13px] font-bold text-slate-800">HD</th>
                                <th class="px-5 py-3.5 text-right text-[13px] font-bold text-slate-800">Tiền Dâng</th>
                                <th class="px-5 py-3.5 text-right text-[13px] font-bold text-slate-800 hidden sm:table-cell">Chi</th>
                                <th class="px-5 py-3.5 w-8" v-if="canManage"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="m in meetings"
                                :key="m.id"
                                class="hover:bg-slate-50/80 transition-colors"
                                :class="canManage ? 'cursor-pointer group' : ''"
                                @click="canManage && openFinanceForm(m)"
                            >
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="text-[13px] sm:text-sm font-black text-gray-900">{{ formatDate(m.meeting_date) }}</p>
                                    <p class="text-xs text-gray-500 capitalize mt-0.5">{{ formatDayOfWeek(m.meeting_date) }}</p>
                                </td>
                                <td class="px-5 py-4 text-[13px] sm:text-[15px] font-medium text-gray-800 max-w-[200px] truncate">{{ m.topic || '—' }}</td>
                                <td class="px-5 py-4 text-[13px] sm:text-sm text-gray-600 hidden lg:table-cell">{{ m.scripture || '—' }}</td>
                                <td class="px-5 py-4 text-[13px] sm:text-sm text-gray-500 italic hidden xl:table-cell max-w-[150px] truncate">{{ m.memory_verse || '—' }}</td>
                                <td class="px-5 py-4 text-center text-[15px] font-black text-amber-700">{{ m.attendance > 0 ? m.attendance : '—' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <span v-if="m.session_income > 0" class="text-[15px] font-bold text-emerald-700">{{ formatCurrency(m.session_income) }}</span>
                                    <span v-else class="text-gray-300 text-[15px]">—</span>
                                </td>
                                <td class="px-5 py-4 text-right hidden sm:table-cell">
                                    <span v-if="m.session_expense > 0" class="text-sm font-medium text-rose-700">{{ formatCurrency(m.session_expense) }}</span>
                                    <span v-else class="text-gray-300 text-[15px]">—</span>
                                </td>
                                <td class="px-5 py-4 text-right" v-if="canManage">
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </td>
                            </tr>

                            <!-- Empty state -->
                            <tr v-if="meetings.length === 0">
                                <td :colspan="canManage ? 8 : 7" class="px-5 py-12 text-center">
                                    <div class="w-14 h-14 mx-auto mb-4 bg-gray-50 rounded-full flex items-center justify-center border border-gray-100">
                                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <p class="text-[15px] text-gray-600 font-bold mb-1">Chưa có buổi nhóm nào trong tháng {{ localMonth }}/{{ localYear }}</p>
                                    <p class="text-sm text-gray-500">Hãy tạo buổi nhóm trong phần Quản lý Buổi Nhóm Ban Ngành</p>
                                </td>
                            </tr>

                            <!-- Totals row -->
                            <tr v-if="meetings.length > 0" class="bg-green-900 border-t border-green-800">
                                <td class="px-5 py-3.5 text-sm font-black text-white" colspan="2">TỔNG KẾT THÁNG</td>
                                <td class="px-5 py-3.5 hidden lg:table-cell"></td>
                                <td class="px-5 py-3.5 hidden xl:table-cell"></td>
                                <td class="px-5 py-3.5 text-center text-sm text-green-300 font-bold">{{ meetings.length }} buổi</td>
                                <td class="px-5 py-3.5 text-right text-[15px] font-black text-emerald-300">{{ formatCurrency(summary.month_income) }}</td>
                                <td class="px-5 py-3.5 text-right text-[15px] font-black text-rose-300 hidden sm:table-cell">{{ formatCurrency(summary.month_expense) }}</td>
                                <td class="px-5 py-3.5" v-if="canManage"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Ledger Summary -->
                <div class="px-6 py-5 bg-gray-50 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2.5">
                        <div class="flex justify-between text-[15px]"><span class="text-gray-600">Tồn đầu tháng:</span><span class="font-bold">{{ formatCurrency(summary.opening_balance) }}</span></div>
                        <div class="flex justify-between text-[15px] text-emerald-700"><span>+ Tổng thu:</span><span class="font-bold">{{ formatCurrency(summary.month_income) }}</span></div>
                        <div class="flex justify-between text-[15px] text-rose-700"><span>- Tổng chi:</span><span class="font-bold">{{ formatCurrency(summary.month_expense) }}</span></div>
                        <div class="flex justify-between text-[17px] font-black border-t border-gray-200 mt-2 pt-3 text-blue-900"><span>TỒN CUỐI:</span><span>{{ formatCurrency(summary.closing_balance) }}</span></div>
                    </div>
                    <div v-if="funds.length > 0" class="space-y-2.5 md:border-l md:border-gray-200 md:pl-6">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Theo Quỹ</p>
                        <div v-for="f in funds" :key="f.id" class="flex justify-between text-[15px]">
                            <span class="text-gray-700 font-medium">{{ f.name }}</span>
                            <span class="font-bold" :class="f.balance >= 0 ? 'text-emerald-700' : 'text-rose-700'">{{ formatCurrency(f.balance) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- === Finance SlideOver (ghi tiền dâng / chi cho 1 buổi nhóm) === -->
        <SlideOver v-model="showFinanceForm"
            :title="selectedMeeting ? 'Ghi Tiền — ' + formatDate(selectedMeeting.meeting_date) : 'Ghi Tiền'"
            :wide="true">
            <div v-if="selectedMeeting" class="space-y-5">
                <!-- Meeting info -->
                <div class="bg-green-50 rounded-xl px-5 py-4 border border-green-100">
                    <p class="text-[15px] font-bold text-green-900">{{ selectedMeeting.topic || '(Chưa có chủ đề)' }}</p>
                    <p class="text-[13px] text-green-700 mt-1">{{ formatDate(selectedMeeting.meeting_date) }} · HD: {{ selectedMeeting.attendance > 0 ? selectedMeeting.attendance + ' người' : '—' }}</p>
                </div>

                <!-- Finance rows -->
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <h4 class="text-[15px] font-bold text-gray-900">Thu / Chi</h4>
                        <button type="button" @click="addRow" class="inline-flex items-center gap-1.5 text-[13px] font-bold text-blue-600 hover:text-blue-800 px-4 py-2 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Thêm dòng
                        </button>
                    </div>

                    <div v-for="(row, idx) in financeRows" :key="idx" class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5 tracking-wide">LOẠI</label>
                                <div class="flex gap-2">
                                    <button type="button" @click="row.type = 'thu'" class="flex-1 py-2 text-[13px] font-bold rounded-xl transition-colors" :class="row.type === 'thu' ? 'bg-emerald-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'">Thu</button>
                                    <button type="button" @click="row.type = 'chi'" class="flex-1 py-2 text-[13px] font-bold rounded-xl transition-colors" :class="row.type === 'chi' ? 'bg-rose-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'">Chi</button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1.5 tracking-wide">PHÂN LOẠI</label>
                                <select v-model="row.category" class="block w-full rounded-xl border-gray-300 text-[13px] font-medium focus:ring-blue-500 py-2">
                                    <option value="">-- Chọn --</option>
                                    <option v-if="row.type === 'thu'" value="Tiền hộp tuần">Tiền hộp tuần</option>
                                    <option v-if="row.type === 'thu'" value="Tiền dâng lạc quyên">Tiền dâng lạc quyên</option>
                                    <option v-if="row.type === 'thu'" value="Tiền phần mười (1/10)">Tiền phần mười (1/10)</option>
                                    <option v-if="row.type === 'chi'" value="Chi hoạt động">Chi hoạt động</option>
                                    <option v-if="row.type === 'chi'" value="Thăm viếng">Thăm viếng</option>
                                    <option v-if="row.type === 'chi'" value="Chi sinh hoạt">Chi sinh hoạt</option>
                                    <option v-if="row.type === 'chi'" value="Chi bất thường">Chi bất thường</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <label class="block text-xs font-bold text-gray-500 mb-1.5 tracking-wide">SỐ TIỀN (VNĐ)</label>
                                <input type="number" v-model="row.amount" min="0" class="block w-full rounded-xl border-gray-300 text-[15px] font-bold focus:ring-blue-500 py-2" placeholder="0">
                            </div>
                            <button type="button" @click="removeRow(idx)" class="shrink-0 mt-6 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors p-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <div v-if="financeRows.length === 0" class="text-center py-6 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50">
                        <p class="text-sm text-gray-500 font-medium">Chưa có dòng nào. Bấm "+ Thêm dòng" để ghi tiền.</p>
                        <button type="button" @click="addRow" class="mt-2 text-[13px] font-bold text-blue-600 hover:underline">+ Thêm dòng ghi tiền</button>
                    </div>

                    <!-- Live total -->
                    <div v-if="financeRows.length > 0" class="bg-blue-50 rounded-2xl px-5 py-4 border border-blue-100 space-y-2 mt-4">
                        <div class="flex justify-between text-[15px]">
                            <span class="font-bold text-blue-900">Tổng Thu:</span>
                            <span class="font-black text-emerald-700">+ {{ formatCurrency(liveTotalThu) }}</span>
                        </div>
                        <div class="flex justify-between text-[15px]">
                            <span class="font-bold text-blue-900">Tổng Chi:</span>
                            <span class="font-black text-rose-700">- {{ formatCurrency(liveTotalChi) }}</span>
                        </div>
                        <div class="flex justify-between text-[17px] border-t border-blue-200/60 pt-3 mt-2">
                            <span class="font-black text-blue-900">Tồn buổi nhóm:</span>
                            <span class="font-black" :class="liveTotalThu - liveTotalChi >= 0 ? 'text-emerald-700' : 'text-rose-700'">
                                {{ formatCurrency(liveTotalThu - liveTotalChi) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-between items-center w-full">
                    <button v-if="selectedMeeting && (selectedMeeting.session_income > 0 || selectedMeeting.session_expense > 0)"
                        type="button" @click="clearFinance"
                        class="text-red-600 text-[13px] sm:text-sm font-bold flex items-center gap-1.5 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span class="hidden sm:inline">Xóa tiền buổi này</span>
                        <span class="sm:hidden">Xóa</span>
                    </button>
                    <div v-else></div>
                    <div class="flex gap-2.5">
                        <button type="button" @click="closeFinanceForm" class="px-5 py-2.5 border border-gray-200 rounded-xl text-[15px] font-bold text-gray-700 hover:bg-gray-50 transition-colors">Hủy</button>
                        <button type="button" @click="submitFinance" :disabled="formLoading || financeRows.length === 0" class="px-6 py-2.5 bg-green-700 hover:bg-green-800 text-white text-[15px] font-bold rounded-xl disabled:opacity-50 transition-colors shadow-sm">
                            {{ formLoading ? 'Đang lưu...' : 'Lưu Tài Chính' }}
                        </button>
                    </div>
                </div>
            </template>
        </SlideOver>

        <!-- Dept Switcher SlideOver -->
        <SlideOver v-model="isSwitchOpen" title="Chuyển Ban Ngành">
            <div class="space-y-3">
                <div v-for="dept in availableDepartments" :key="dept.id" @click="switchDept(dept.id)"
                    class="w-full text-left p-5 rounded-xl border-2 transition-all cursor-pointer shadow-sm hover:shadow"
                    :class="department?.id === dept.id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-white hover:border-gray-300'">
                    <h4 class="text-[15px] font-bold" :class="department?.id === dept.id ? 'text-blue-900' : 'text-gray-900'">{{ dept.name }}</h4>
                    <span v-if="department?.id === dept.id" class="text-[13px] text-blue-600 font-bold mt-1 block">Đang hoạt động</span>
                </div>
            </div>
        </SlideOver>
    </PortalLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';

const props = defineProps({
    department: Object,
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
    canManage: Boolean,
    meetings: Array,
    funds: Array,
    filters: Object,
    summary: Object,
});

// Period filter
const localMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const localYear  = ref(props.filters?.year  || new Date().getFullYear());
const updatePeriod = () => router.get(route('portal.finance.index'), { month: localMonth.value, year: localYear.value }, { preserveState: true, replace: true });

const formatCurrency = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v || 0);
const formatDate = (d) => d ? new Date(d + 'T00:00:00').toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '';
const formatDayOfWeek = (d) => d ? new Date(d + 'T00:00:00').toLocaleDateString('vi-VN', { weekday: 'long' }) : '';

// ============== FINANCE FORM for a selected meeting ==============
const showFinanceForm = ref(false);
const selectedMeeting = ref(null);
const financeRows     = ref([]);
const formLoading     = ref(false);

const makeBlankRow = () => ({ type: 'thu', amount: 0, category: '' });

const openFinanceForm = (meeting) => {
    selectedMeeting.value = meeting;
    // Pre-populate from existing finances
    if (meeting.finances && meeting.finances.length > 0) {
        financeRows.value = meeting.finances.map(f => ({
            type: f.type,
            amount: f.amount,
            category: f.category || '',
        }));
    } else {
        financeRows.value = [makeBlankRow()];
    }
    showFinanceForm.value = true;
};

const closeFinanceForm = () => {
    showFinanceForm.value = false;
    selectedMeeting.value = null;
    financeRows.value = [];
};

const addRow    = () => financeRows.value.push(makeBlankRow());
const removeRow = (idx) => financeRows.value.splice(idx, 1);

const liveTotalThu = computed(() =>
    financeRows.value.reduce((s, r) => r.type === 'thu' ? s + (parseFloat(r.amount) || 0) : s, 0)
);
const liveTotalChi = computed(() =>
    financeRows.value.reduce((s, r) => r.type === 'chi' ? s + (parseFloat(r.amount) || 0) : s, 0)
);

const submitFinance = () => {
    if (!selectedMeeting.value) return;
    formLoading.value = true;
    router.post(route('portal.finance.store', selectedMeeting.value.id), {
        finances: financeRows.value,
    }, {
        preserveScroll: true,
        onSuccess: () => closeFinanceForm(),
        onFinish: () => formLoading.value = false,
    });
};

const clearFinance = () => {
    if (!selectedMeeting.value) return;
    if (confirm('Bạn có chắc muốn xóa toàn bộ tiền của buổi nhóm này?')) {
        formLoading.value = true;
        router.delete(route('portal.finance.delete', selectedMeeting.value.id), {
            preserveScroll: true,
            onSuccess: () => closeFinanceForm(),
            onFinish: () => formLoading.value = false,
        });
    }
};

// ============== DEPT SWITCHER ==============
const isSwitchOpen = ref(false);
const switchDept = (deptId) => {
    router.post(route('portal.switch-context'), { department_id: deptId }, {
        preserveScroll: true,
        onSuccess: () => isSwitchOpen.value = false,
    });
};
</script>