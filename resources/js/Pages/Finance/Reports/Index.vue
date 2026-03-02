<template>
    <PortalLayout :department="department" :availableDepartments="[]" :isGlobalAdmin="isGlobalAdmin" portalType="finance">
        <Head title="Báo Cáo Tổng Hợp" />
        
        <div class="px-4 py-6 sm:px-6 lg:px-8 max-w-[1400px] mx-auto space-y-6">
            <!-- Header & Toolbar -->
            <div class="flex flex-col md:flex-row md:items-end justify-between space-y-4 md:space-y-0">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Báo Cáo Hoạt Động Tháng</h2>
                    <p class="text-sm text-gray-500 mt-1">Tổng hợp thu chi và số lượng tham gia theo từng kỳ.</p>
                </div>
                
                <div class="flex items-center w-full md:w-auto">
                     <DataToolbar 
                        :filters="localFilters"
                        :showPeriodFilter="true"
                        @update:filters="updateFilters"
                        class="w-full"
                    />
                </div>
            </div>

            <!-- Dashboard Summary Cards (MoM Comparisons) -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex flex-col justify-center">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tổng Thu</p>
                    <p class="text-xl font-bold text-emerald-600">{{ formatCurrency(currentTotals.income) }}</p>
                    <div class="mt-2 text-xs font-medium flex items-center">
                        <span :class="comparisons.income >= 0 ? 'text-emerald-500 bg-emerald-50' : 'text-rose-500 bg-rose-50'" class="px-1.5 py-0.5 rounded-md flex items-center">
                            <svg v-if="comparisons.income >= 0" class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            <svg v-else class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                            {{ Math.abs(comparisons.income) }}%
                        </span>
                        <span class="text-gray-400 ml-1.5">vs tháng trước</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex flex-col justify-center">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tổng Chi</p>
                    <p class="text-xl font-bold text-rose-600">{{ formatCurrency(currentTotals.expense) }}</p>
                    <div class="mt-2 text-xs font-medium flex items-center">
                        <span :class="comparisons.expense <= 0 ? 'text-emerald-500 bg-emerald-50' : 'text-rose-500 bg-rose-50'" class="px-1.5 py-0.5 rounded-md flex items-center">
                            <svg v-if="comparisons.expense > 0" class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            <svg v-else class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                            {{ Math.abs(comparisons.expense) }}%
                        </span>
                        <span class="text-gray-400 ml-1.5">vs tháng trước</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm flex flex-col justify-center">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tồn Cuối Kỳ</p>
                    <p class="text-xl font-bold text-blue-700">{{ formatCurrency(currentTotals.balance) }}</p>
                    <div class="mt-2 text-xs font-medium flex items-center">
                        <span :class="comparisons.balance >= 0 ? 'text-emerald-500 bg-emerald-50' : 'text-rose-500 bg-rose-50'" class="px-1.5 py-0.5 rounded-md flex items-center">
                            <svg v-if="comparisons.balance >= 0" class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            <svg v-else class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                            {{ Math.abs(comparisons.balance) }}%
                        </span>
                    </div>
                </div>

                <!-- Metrics Columns -->
                <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-100 shadow-sm flex flex-col justify-center">
                    <p class="text-xs font-semibold text-indigo-800 uppercase tracking-wider mb-1">Hiện Diện</p>
                    <p class="text-xl font-bold text-indigo-900">{{ currentTotals.attendance || 0 }} <span class="text-xs font-medium text-indigo-600">người</span></p>
                    <div class="mt-2 text-xs font-medium flex items-center">
                        <span :class="comparisons.attendance >= 0 ? 'text-emerald-600' : 'text-rose-600'" class="flex items-center font-bold">
                            {{ comparisons.attendance >= 0 ? '+' : '' }}{{ comparisons.attendance }}%
                        </span>
                        <span class="text-indigo-400 ml-1.5">vs tháng trước</span>
                    </div>
                </div>

                <div class="bg-violet-50 rounded-xl p-4 border border-violet-100 shadow-sm flex flex-col justify-center">
                    <p class="text-xs font-semibold text-violet-800 uppercase tracking-wider mb-1">Dâng Hiến</p>
                    <p class="text-xl font-bold text-violet-900">{{ currentTotals.tithes || 0 }} <span class="text-xs font-medium text-violet-600">người</span></p>
                    <div class="mt-2 text-xs font-medium flex items-center">
                        <span :class="comparisons.tithes >= 0 ? 'text-emerald-600' : 'text-rose-600'" class="flex items-center font-bold">
                            {{ comparisons.tithes >= 0 ? '+' : '' }}{{ comparisons.tithes }}%
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Data Table (Excel-like) -->
                <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800">Chi Tiết Sổ Quỹ</h3>
                        <button class="text-sm text-blue-600 font-medium hover:text-blue-800 flex items-center">
                           <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                           Xuất Excel (Tạm đóng)
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border-collapse">
                            <thead class="bg-gray-100 border-b-2 border-gray-300">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-200 w-24">Ngày</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-200">Diễn giải</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-bold text-emerald-700 uppercase tracking-wider border-r border-gray-200">Thu</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-bold text-rose-700 uppercase tracking-wider border-r border-gray-200">Chi</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-200 hidden sm:table-cell">Hiện Diện</th>
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider hidden sm:table-cell">Dâng Hiến</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="tx in transactions" :key="tx.id" class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-4 py-2.5 whitespace-nowrap text-sm text-gray-600 border-r border-gray-100">
                                        {{ formatDate(tx.transaction_date) }}
                                    </td>
                                    <td class="px-4 py-2.5 text-sm font-medium text-gray-900 border-r border-gray-100">
                                        {{ tx.description || 'Không có mô tả' }}
                                        <div class="text-[11px] text-gray-500 font-normal mt-0.5" v-if="tx.category">{{ tx.category }}</div>
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-right font-bold text-sm text-emerald-600 bg-emerald-50/20 border-r border-gray-100">
                                        {{ tx.type === 'income' ? formatCurrency(tx.amount) : '' }}
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-right font-bold text-sm text-rose-600 bg-rose-50/20 border-r border-gray-100">
                                        {{ tx.type === 'expense' ? formatCurrency(tx.amount) : '' }}
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-center text-sm font-medium text-gray-600 border-r border-gray-100 hidden sm:table-cell">
                                        {{ tx.session_metric?.attendance_count ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap text-center text-sm font-medium text-gray-600 hidden sm:table-cell">
                                        {{ tx.session_metric?.tithe_count ?? '-' }}
                                    </td>
                                </tr>
                                <tr v-if="transactions.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 bg-gray-50/50">
                                        Chưa có bản ghi thu chi nào trong tháng.
                                    </td>
                                </tr>
                                <!-- Total Row -->
                                <tr class="bg-gray-100 border-t-2 border-gray-300 font-bold" v-if="transactions.length > 0">
                                    <td colspan="2" class="px-4 py-3 text-right text-sm text-gray-800 border-r border-gray-200 uppercase">
                                        Tổng Cộng Kỳ Này
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-emerald-700 border-r border-gray-200">
                                        {{ formatCurrency(currentTotals.income) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-rose-700 border-r border-gray-200">
                                        {{ formatCurrency(currentTotals.expense) }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-800 border-r border-gray-200 hidden sm:table-cell">
                                        {{ currentTotals.attendance }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-800 hidden sm:table-cell">
                                        {{ currentTotals.tithes }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Upcoming Activities Sidebar -->
                <div class="lg:col-span-1 space-y-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Hoạt Động Kế Tiếp
                        </h3>
                        
                        <div v-if="activities.length > 0" class="space-y-4 relative before:absolute before:inset-0 before:ml-2.5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-200 before:to-transparent">
                             <div v-for="item in activities" :key="item.id" class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                <div class="flex items-center justify-center w-5 h-5 rounded-full border-2 border-white bg-blue-500 text-white shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10"></div>
                                <div class="w-[calc(100%-2rem)] md:w-[calc(50%-1.5rem)] bg-white p-3 rounded-lg border border-gray-100 shadow-sm">
                                    <time class="text-[10px] font-bold text-blue-500 uppercase">{{ formatDate(item.date) }}</time>
                                    <p class="text-xs text-gray-800 font-medium mt-1 leading-snug">{{ item.title }}</p>
                                </div>
                             </div>
                        </div>
                        <div v-else class="text-sm text-gray-500 text-center py-6 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                            Không có sự kiện/cuộc gọi nào được lên lịch trong tháng.
                        </div>
                    </div>

                    <!-- Note Widget -->
                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-100 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 text-amber-500/10">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                        </div>
                        <h4 class="text-sm font-bold text-amber-800 relative z-10 mb-2">Lưu ý Dữ liệu</h4>
                        <p class="text-xs text-amber-700/80 relative z-10">
                            Tỉ lệ phần trăm được tính toán dựa trên tổng ngân sách chốt vào cuối tháng trước. Dữ liệu chỉ mang tính chất tham khảo nội bộ.
                        </p>
                    </div>
                </div>
            </div>
            
        </div>
    </PortalLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import DataToolbar from '@/Components/DataToolbar.vue';

const props = defineProps({
    currentTotals: Object,
    prevTotals: Object,
    comparisons: Object,
    transactions: Array,
    activities: Array,
    funds: Array,
    filters: Object,
    department: Object,
    isGlobalAdmin: Boolean,
});

const localFilters = ref({
    month: props.filters.month || new Date().getMonth() + 1,
    year: props.filters.year || new Date().getFullYear(),
});

const updateFilters = (newFilters) => {
    localFilters.value = { ...localFilters.value, ...newFilters };
    router.get(route('finance.reports.index'), localFilters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>
