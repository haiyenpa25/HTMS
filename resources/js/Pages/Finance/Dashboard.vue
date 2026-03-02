<template>
    <PortalLayout :department="activeDepartment" :availableDepartments="availableDepartments" :isGlobalAdmin="isGlobalAdmin" portalType="finance" @open-switcher="showSwitcher = true">
        <Head title="Tài Chính Hội Thánh" />

        <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Tổng quan Tài chính</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ activeDepartment ? activeDepartment.name : 'Quỹ Hội Thánh Tổng hợp' }} · Tháng {{ filters.month }}/{{ filters.year }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                        <select v-model="localMonth" @change="updatePeriod" class="text-sm font-medium text-gray-700 border-none focus:ring-0 p-0 pr-1">
                            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
                        </select>
                        <input v-model="localYear" @change="updatePeriod" type="number" class="w-16 text-sm font-medium text-gray-700 border-none focus:ring-0 p-0 text-center" min="2020" max="2099">
                    </div>
                    <Link :href="route('finance.transactions.index')" class="px-4 py-2 text-sm font-bold bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                        Sổ Cầm Quỹ
                    </Link>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-200">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-100">Tổng Thu</p>
                    <p class="text-2xl font-black mt-1">{{ formatCurrency(currentTotals.income) }}</p>
                    <div class="flex items-center mt-3 text-xs font-bold" :class="comparisons.income >= 0 ? 'text-emerald-100' : 'text-red-200'">
                        <span>{{ comparisons.income >= 0 ? '▲' : '▼' }} {{ Math.abs(comparisons.income) }}%</span>
                        <span class="ml-1 text-emerald-200 font-normal">so với tháng trước</span>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-2xl p-5 text-white shadow-lg shadow-rose-200">
                    <p class="text-xs font-bold uppercase tracking-wider text-rose-100">Tổng Chi</p>
                    <p class="text-2xl font-black mt-1">{{ formatCurrency(currentTotals.expense) }}</p>
                    <div class="flex items-center mt-3 text-xs font-bold" :class="comparisons.expense <= 0 ? 'text-rose-100' : 'text-red-200'">
                        <span>{{ comparisons.expense >= 0 ? '▲' : '▼' }} {{ Math.abs(comparisons.expense) }}%</span>
                        <span class="ml-1 text-rose-200 font-normal">so với tháng trước</span>
                    </div>
                </div>
                <div class="col-span-2 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-5 text-white shadow-lg shadow-blue-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-blue-200">Tồn Quỹ Tích Lũy</p>
                            <p class="text-3xl font-black mt-1">{{ formatCurrency(currentTotals.balance) }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-4 text-xs font-bold">
                        <div>
                            <span class="text-blue-200">Chênh lệch:</span>
                            <span class="ml-1" :class="(currentTotals.income - currentTotals.expense) >= 0 ? 'text-emerald-300' : 'text-red-300'">
                                {{ formatCurrency(currentTotals.income - currentTotals.expense) }}
                            </span>
                        </div>
                        <div class="flex items-center" :class="comparisons.balance >= 0 ? 'text-emerald-300' : 'text-red-300'">
                            <span>{{ comparisons.balance >= 0 ? '▲' : '▼' }} {{ Math.abs(comparisons.balance) }}% tháng trước</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fund Breakdown (if multiple funds) -->
            <div v-if="funds && funds.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-900">Số Dư Từng Quỹ</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    <div v-for="fund in funds" :key="fund.id" class="px-5 py-3 flex justify-between items-center hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-2.5 h-2.5 rounded-full" :class="fund.balance >= 0 ? 'bg-emerald-400' : 'bg-rose-400'"></div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ fund.name }}</p>
                                <p v-if="fund.description" class="text-xs text-gray-500">{{ fund.description }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-black" :class="fund.balance >= 0 ? 'text-emerald-700' : 'text-rose-700'">{{ formatCurrency(fund.balance) }}</span>
                    </div>
                </div>
            </div>

            <!-- Tithes & Contributions by Group -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-900">Báo Cáo Dâng Hiến Theo Ban Ngành</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Số liệu tháng {{ filters.month }}/{{ filters.year }}</p>
                </div>
                <div class="p-5">
                    <!-- Attendance & Tithe Overview -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
                        <div class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                            <p class="text-xs font-bold text-blue-700">Sỉ Số Trung Bình</p>
                            <p class="text-xl font-black text-blue-900 mt-1">{{ currentTotals.attendance || 0 }}</p>
                            <p class="text-xs font-medium mt-1" :class="comparisons.attendance >= 0 ? 'text-emerald-600' : 'text-red-500'">
                                {{ comparisons.attendance >= 0 ? '▲' : '▼' }} {{ Math.abs(comparisons.attendance) }}%
                            </p>
                        </div>
                        <div class="bg-indigo-50 rounded-xl p-3 border border-indigo-100">
                            <p class="text-xs font-bold text-indigo-700">Người Dâng 1/10</p>
                            <p class="text-xl font-black text-indigo-900 mt-1">{{ currentTotals.tithes || 0 }}</p>
                            <p class="text-xs font-medium mt-1" :class="comparisons.tithes >= 0 ? 'text-emerald-600' : 'text-red-500'">
                                {{ comparisons.tithes >= 0 ? '▲' : '▼' }} {{ Math.abs(comparisons.tithes) }}%
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-200 col-span-2">
                            <p class="text-xs font-bold text-gray-700">Tháng trước so sánh</p>
                            <div class="flex gap-4 mt-1">
                                <div>
                                    <span class="text-xs text-gray-500">Thu:</span>
                                    <span class="text-sm font-bold text-gray-900 ml-1">{{ formatCurrency(prevTotals.income) }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Chi:</span>
                                    <span class="text-sm font-bold text-gray-900 ml-1">{{ formatCurrency(prevTotals.expense) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contribution per group table -->
                    <div v-if="contributionByGroup && contributionByGroup.length > 0">
                        <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3">Chi tiết dâng 1/10 theo ban ngành</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-50 rounded-xl">
                                        <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-500 uppercase">Ban ngành</th>
                                        <th class="px-4 py-2.5 text-center text-xs font-bold text-gray-500 uppercase">Số người</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-bold text-gray-500 uppercase">Số tiền</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="item in contributionByGroup" :key="item.member_group" class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-bold text-gray-900">{{ item.member_group }}</td>
                                        <td class="px-4 py-3 text-sm text-center font-medium text-gray-700">{{ item.total_people }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-bold text-indigo-700">{{ formatCurrency(item.total_amount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 text-sm text-gray-400">
                        <p>Chưa có dữ liệu dâng hiến theo ban ngành trong tháng này.</p>
                        <p class="text-xs mt-1">Hãy nhập giao dịch "Tiền phần mười (1/10)" với bảng phân tổ để thấy báo cáo tại đây.</p>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <Link :href="route('finance.transactions.index')" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all text-center group">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm">Sổ Cầm Quỹ</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Thu chi chi tiết</p>
                </Link>
                <Link :href="route('finance.reports.index')" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all text-center group">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm">Báo Cáo Tháng</h3>
                    <p class="text-xs text-gray-500 mt-0.5">So sánh & Thống kê</p>
                </Link>
                <Link :href="route('finance.funds.index')" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:border-violet-200 transition-all text-center group">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-violet-50 text-violet-500 flex items-center justify-center group-hover:bg-violet-500 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm">Quản Lý Quỹ</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Tạo và chỉnh sửa quỹ</p>
                </Link>
            </div>
        </div>

        <!-- Switcher SlideOver -->
        <SlideOver :show="showSwitcher" @close="showSwitcher = false" title="Chuyển Ban Ngành / Context">
            <div class="space-y-4">
                <p class="text-sm text-gray-500">Chọn ban ngành bạn muốn xem quản lý tài chính.</p>
                <div class="space-y-2">
                    <div v-for="dept in availableDepartments" :key="dept.id" @click="switchContext(dept.id)"
                        class="w-full text-left p-4 rounded-xl border-2 transition-all cursor-pointer"
                        :class="activeDepartment?.id === dept.id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-white hover:border-gray-300'">
                        <h4 class="text-sm font-bold text-gray-900">{{ dept.name }}</h4>
                        <span v-if="activeDepartment?.id === dept.id" class="text-xs text-blue-600 font-bold">Đang hoạt động</span>
                    </div>
                </div>
            </div>
        </SlideOver>
    </PortalLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';

const props = defineProps({
    activeDepartment: Object,
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
    currentTotals: Object,
    prevTotals: Object,
    comparisons: Object,
    funds: Array,
    contributionByGroup: Array,
    filters: Object,
});

const showSwitcher = ref(false);
const localMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const localYear = ref(props.filters?.year || new Date().getFullYear());

const updatePeriod = () => {
    router.get(route('finance.index'), { month: localMonth.value, year: localYear.value }, {
        preserveState: true, replace: true,
    });
};

const switchContext = (deptId) => {
    router.post(route('finance.switch-context'), { department_id: deptId }, {
        preserveScroll: true,
        onSuccess: () => { showSwitcher.value = false; }
    });
};

const formatCurrency = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v || 0);
</script>
