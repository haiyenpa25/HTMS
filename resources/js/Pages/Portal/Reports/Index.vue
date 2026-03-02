<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" @open-switcher="isSwitchOpen = true">
        <Head title="Báo cáo Ban ngành" />

        <div class="py-4 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- ══ HEADER ══ -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black text-gray-900">📑 BÁO CÁO TÌNH HÌNH SINH HOẠT</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ department?.name }} · Tháng {{ localMonth }}/{{ localYear }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                        <select v-model="localMonth" @change="updatePeriod" class="text-sm font-medium text-gray-700 border-none focus:ring-0 p-0">
                            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
                        </select>
                        <input v-model="localYear" @change="updatePeriod" type="number" class="w-16 text-sm border-none focus:ring-0 p-0 text-center font-medium" min="2020" max="2099">
                    </div>
                    <span v-if="report" class="px-3 py-1.5 rounded-xl text-xs font-bold"
                        :class="report.status==='approved'?'bg-green-100 text-green-800':report.status==='submitted'?'bg-amber-100 text-amber-800':'bg-gray-100 text-gray-600'">
                        {{ statusLabel(report.status) }}
                    </span>
                    <button v-if="canApprove && report?.status==='submitted'" @click="approveReport" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-xl">✓ Duyệt</button>
                    <button v-if="canCreate" @click="openReportForm" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-xl shadow-sm">
                        ✏️ {{ report ? 'Cập nhật BC' : 'Lập Báo cáo' }}
                    </button>
                </div>
            </div>

            <!-- ══ KPI CARDS ══ -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div v-for="card in kpiCards" :key="card.label" class="rounded-2xl p-4 shadow-sm" :class="card.bg">
                    <p class="text-[10px] font-bold uppercase tracking-wider" :class="card.labelColor">{{ card.label }}</p>
                    <p class="text-2xl font-black mt-1" :class="card.valueColor">{{ card.value }}</p>
                    <div v-if="card.change !== undefined" class="mt-2 flex items-center gap-1.5">
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full" :class="card.change>=0?'bg-green-100 text-green-700':'bg-red-100 text-red-700'">
                            {{ card.change >= 0 ? '▲' : '▼' }} {{ Math.abs(card.change) }}%
                        </span>
                        <span class="text-[10px]" :class="card.prevColor">T.trước: {{ card.prev }}</span>
                    </div>
                    <p v-else class="text-[10px] mt-1" :class="card.subColor">{{ card.sub }}</p>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════ -->
            <!-- SECTION A: BUỔI NHÓM HỘI THÁNH                   -->
            <!-- [Table LEFT | Chart RIGHT]                        -->
            <!-- ══════════════════════════════════════════════════ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 bg-blue-900 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-white">A. BUỔI NHÓM HỘI THÁNH</h3>
                        <p class="text-[10px] text-blue-300">Số lượng hiện diện từng tuần trong tháng</p>
                    </div>
                    <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ church_meetings.length }} buổi · TB {{ summary.avg_church }}</span>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900">Ngày</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900">Chủ đề</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900 hidden md:table-cell">Diễn giả</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-bold text-blue-900">Hiện Diện</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-bold text-blue-900 hidden sm:table-cell">Thu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="m in church_meetings" :key="m.id" class="hover:bg-blue-50/50">
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <p class="text-xs font-black text-gray-900">{{ m.date }}</p>
                                        <p class="text-[10px] text-gray-400 capitalize">{{ m.day }}</p>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-gray-800 max-w-[140px] truncate">{{ m.topic || '—' }}</td>
                                    <td class="px-4 py-2.5 text-xs text-gray-600 hidden md:table-cell">{{ m.speaker || '—' }}</td>
                                    <td class="px-4 py-2.5 text-center text-sm font-black text-amber-700">{{ m.attendance > 0 ? m.attendance : '—' }}</td>
                                    <td class="px-4 py-2.5 text-right text-xs font-medium text-emerald-700 hidden sm:table-cell">{{ m.income > 0 ? fmt(m.income) : '—' }}</td>
                                </tr>
                                <tr v-if="church_meetings.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-xs text-gray-400">Chưa có buổi nhóm HT nào trong tháng</td>
                                </tr>
                                <!-- Weekly summary rows -->
                                <tr class="bg-blue-900/5 border-t-2 border-blue-200">
                                    <td colspan="2" class="px-4 py-2.5 text-xs font-black text-blue-900">TỔNG / TRUNG BÌNH</td>
                                    <td class="px-4 py-2.5 hidden md:table-cell"></td>
                                    <td class="px-4 py-2.5 text-center text-sm font-black text-amber-700">TB: {{ summary.avg_church }}</td>
                                    <td class="px-4 py-2.5 text-right text-xs font-bold text-emerald-700 hidden sm:table-cell">{{ fmt(summary.church_total_income) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Chart -->
                    <div class="p-5">
                        <p class="text-xs font-bold text-gray-700 mb-3">📈 Biểu Đồ Hiện Diện Theo Tuần</p>
                        <div v-if="church_meetings.length > 0">
                            <apexchart type="area" height="240" :options="churchAttChartOpts" :series="churchAttSeries" />
                        </div>
                        <div v-else class="h-56 flex flex-col items-center justify-center text-gray-300 gap-2">
                            <span class="text-3xl">📭</span>
                            <p class="text-xs">Không có dữ liệu</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════ -->
            <!-- SECTION B: BUỔI NHÓM SINH HOẠT BAN               -->
            <!-- [Table LEFT | Chart RIGHT]                        -->
            <!-- ══════════════════════════════════════════════════ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 bg-indigo-900 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-white">B. BUỔI NHÓM SINH HOẠT BAN NGÀNH</h3>
                        <p class="text-[10px] text-indigo-300">Số lượng hiện diện từng tuần trong tháng</p>
                    </div>
                    <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ dept_meetings.length }} buổi · TB {{ summary.avg_dept }}</span>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-indigo-50">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-bold text-indigo-900">Ngày</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-bold text-indigo-900">Chủ đề</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-bold text-indigo-900 hidden md:table-cell">Diễn giả</th>
                                    <th class="px-4 py-2.5 text-center text-xs font-bold text-indigo-900">Hiện Diện</th>
                                    <th class="px-4 py-2.5 text-right text-xs font-bold text-indigo-900 hidden sm:table-cell">Thu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="m in dept_meetings" :key="m.id" class="hover:bg-indigo-50/50">
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <p class="text-xs font-black text-gray-900">{{ m.date }}</p>
                                        <p class="text-[10px] text-gray-400 capitalize">{{ m.day }}</p>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-gray-800 max-w-[140px] truncate">{{ m.topic || '—' }}</td>
                                    <td class="px-4 py-2.5 text-xs text-gray-600 hidden md:table-cell">{{ m.speaker || '—' }}</td>
                                    <td class="px-4 py-2.5 text-center text-sm font-black text-amber-700">{{ m.attendance > 0 ? m.attendance : '—' }}</td>
                                    <td class="px-4 py-2.5 text-right text-xs font-medium text-emerald-700 hidden sm:table-cell">{{ m.income > 0 ? fmt(m.income) : '—' }}</td>
                                </tr>
                                <tr v-if="dept_meetings.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-xs text-gray-400">Chưa có buổi nhóm Ban nào trong tháng</td>
                                </tr>
                                <tr class="bg-indigo-900/5 border-t-2 border-indigo-200">
                                    <td colspan="2" class="px-4 py-2.5 text-xs font-black text-indigo-900">TỔNG / TRUNG BÌNH</td>
                                    <td class="px-4 py-2.5 hidden md:table-cell"></td>
                                    <td class="px-4 py-2.5 text-center text-sm font-black text-amber-700">TB: {{ summary.avg_dept }}</td>
                                    <td class="px-4 py-2.5 text-right text-xs font-bold text-emerald-700 hidden sm:table-cell">{{ fmt(summary.dept_total_income) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Chart -->
                    <div class="p-5">
                        <p class="text-xs font-bold text-gray-700 mb-3">📈 Biểu Đồ Hiện Diện Theo Tuần</p>
                        <div v-if="dept_meetings.length > 0">
                            <apexchart type="area" height="240" :options="deptAttChartOpts" :series="deptAttSeries" />
                        </div>
                        <div v-else class="h-56 flex flex-col items-center justify-center text-gray-300 gap-2">
                            <span class="text-3xl">📭</span>
                            <p class="text-xs">Không có dữ liệu</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ SECTION C: CHART CỘT SO SÁNH ══ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-bold text-gray-900 mb-1">📊 C. So Sánh Số Lượng Tham Dự Theo Tuần</h3>
                <p class="text-[10px] text-gray-400 mb-4">Tổng người tham dự Buổi Nhóm Hội Thánh và Buổi Nhóm Ban Ngành theo từng tuần trong tháng</p>
                <apexchart type="bar" height="220" :options="combinedBarOpts" :series="combinedBarSeries" />
            </div>

            <!-- ══ FINANCE TABLE (dept meetings only) ══ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 bg-green-900 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-white">D. TÀI CHÍNH — TIỀN DÂNG BAN NGÀNH SINH HOẠT</h3>
                        <p class="text-[10px] text-green-300">Chỉ buổi nhóm Ban Ngành mới có tiền dâng · Tháng {{ localMonth }}/{{ localYear }}</p>
                    </div>
                    <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ dept_meetings.length }} buổi</span>
                </div>
                <!-- Per-meeting detail rows -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-green-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-green-900 whitespace-nowrap">Ngày</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-green-900">Chủ đề</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-green-900 hidden lg:table-cell">Kinh thánh</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-green-900 hidden xl:table-cell">Câu gốc</th>
                                <th class="px-4 py-2.5 text-center text-xs font-bold text-green-900">HD</th>
                                <th class="px-4 py-2.5 text-right text-xs font-bold text-green-900">Tiền Dâng</th>
                                <th class="px-4 py-2.5 text-right text-xs font-bold text-green-900 hidden sm:table-cell">Chi</th>
                                <th class="px-4 py-2.5 text-center text-xs font-bold text-green-900 hidden sm:table-cell">Tuần</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="m in dept_meetings" :key="m.id" class="hover:bg-green-50/40">
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <p class="text-xs font-black text-gray-900">{{ m.date }}</p>
                                    <p class="text-[10px] text-gray-400 capitalize">{{ m.day }}</p>
                                </td>
                                <td class="px-4 py-2.5 text-xs font-medium text-gray-800 max-w-[160px] truncate">{{ m.topic || '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-600 hidden lg:table-cell">{{ m.scripture || '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-500 italic hidden xl:table-cell max-w-[130px] truncate">{{ m.memory_verse || '—' }}</td>
                                <td class="px-4 py-2.5 text-center text-sm font-black text-amber-700">{{ m.attendance > 0 ? m.attendance : '—' }}</td>
                                <td class="px-4 py-2.5 text-right text-sm font-bold text-emerald-700">{{ m.income > 0 ? fmt(m.income) : '—' }}</td>
                                <td class="px-4 py-2.5 text-right text-xs font-medium text-rose-700 hidden sm:table-cell">{{ m.expense > 0 ? fmt(m.expense) : '—' }}</td>
                                <td class="px-4 py-2.5 text-center hidden sm:table-cell">
                                    <span class="text-[10px] font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-full">T{{ m.week_no }}</span>
                                </td>
                            </tr>
                            <tr v-if="dept_meetings.length === 0">
                                <td colspan="8" class="px-4 py-8 text-center text-xs text-gray-400">Chưa có buổi nhóm Ban Ngành nào trong tháng</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Weekly summary subtotals -->
                <div v-if="dept_meetings.length > 0" class="overflow-x-auto border-t border-gray-100">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Theo tuần</th>
                                <th class="px-5 py-2 text-center text-[10px] font-bold text-gray-500 uppercase">Số buổi</th>
                                <th class="px-5 py-2 text-right text-[10px] font-bold text-gray-500 uppercase">Tiền dâng</th>
                                <th class="px-5 py-2 text-right text-[10px] font-bold text-gray-500 uppercase">Chi</th>
                                <th class="px-5 py-2 text-right text-[10px] font-bold text-gray-500 uppercase">Tồn lũy kế</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-gray-50/50">
                            <tr v-for="w in weekly_finance" :key="w.week" :class="w.sessions===0?'opacity-30':''">
                                <td class="px-5 py-2 text-sm font-bold text-gray-700">{{ w.week }}</td>
                                <td class="px-5 py-2 text-center text-sm text-gray-500">{{ w.sessions || '—' }}</td>
                                <td class="px-5 py-2 text-right text-sm font-bold text-emerald-700">{{ w.income > 0 ? fmt(w.income) : '—' }}</td>
                                <td class="px-5 py-2 text-right text-sm font-medium text-rose-700">{{ w.expense > 0 ? fmt(w.expense) : '—' }}</td>
                                <td class="px-5 py-2 text-right text-sm font-black" :class="w.running_balance >= 0 ? 'text-blue-800':'text-red-700'">{{ fmt(w.running_balance) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-green-900">
                            <tr>
                                <td class="px-5 py-3 text-xs font-black text-white" colspan="2">TỔNG KẾT THÁNG</td>
                                <td class="px-5 py-3 text-right text-sm font-black text-emerald-300">{{ fmt(summary.month_income) }}</td>
                                <td class="px-5 py-3 text-right text-sm font-black text-rose-300">{{ fmt(summary.month_expense) }}</td>
                                <td class="px-5 py-3 text-right text-base font-black text-white">{{ fmt(summary.closing_balance) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- Ledger summary -->
                <div class="px-5 py-4 bg-gray-50 border-t grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm"><span class="text-gray-600">Tồn đầu tháng:</span><span class="font-bold">{{ fmt(summary.opening_balance) }}</span></div>
                        <div class="flex justify-between text-sm text-emerald-700"><span>+ Tổng thu:</span><span class="font-bold">{{ fmt(summary.month_income) }}</span></div>
                        <div class="flex justify-between text-sm text-rose-700"><span>- Tổng chi:</span><span class="font-bold">{{ fmt(summary.month_expense) }}</span></div>
                        <div class="flex justify-between text-base font-black border-t pt-2 text-blue-900"><span>TỒN CUỐI:</span><span>{{ fmt(summary.closing_balance) }}</span></div>
                    </div>
                    <div v-if="fund_balances.length > 0" class="space-y-2">
                        <p class="text-[10px] font-bold text-gray-500 uppercase">Theo Quỹ</p>
                        <div v-for="f in fund_balances" :key="f.id" class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ f.name }}</span>
                            <span class="font-bold" :class="f.balance>=0?'text-emerald-700':'text-rose-700'">{{ fmt(f.balance) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ FINANCE TREND CHART (1 chart, 3 lines) ══ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-bold text-gray-900 mb-1">💰 E. Xu Hướng Tài Chính 3 Tháng Gần Nhất</h3>
                <p class="text-[10px] text-gray-400 mb-4">Mỗi đường = 1 tháng · Trục ngang = Tuần 1-5 · Giá trị = Tổng thu trong tuần</p>
                <apexchart type="line" height="240" :options="finTrendOpts" :series="finTrendSeries" />
            </div>

            <!-- ══ VISITATION ══ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 bg-purple-800 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-black text-white">F. CÔNG TÁC THĂM VIẾNG</h3>
                        <p class="text-[10px] text-purple-300">Tháng {{ localMonth }}/{{ localYear }}</p>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <span class="bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ summary.visit_planned }} kế hoạch</span>
                        <span class="bg-green-400/30 text-green-200 text-[10px] font-bold px-2 py-0.5 rounded-full">✓ {{ summary.visit_completed }} đã thăm</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                            :class="summary.visit_pct>=80?'bg-emerald-400/30 text-emerald-200':summary.visit_pct>=50?'bg-amber-400/30 text-amber-200':'bg-red-400/30 text-red-200'">
                            {{ summary.visit_pct }}%
                        </span>
                    </div>
                </div>
                <!-- Progress bar + summary text -->
                <div class="px-5 py-3 bg-purple-50 border-b border-purple-100">
                    <div class="flex items-center gap-3 mb-1.5">
                        <div class="flex-1 bg-gray-200 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full transition-all duration-700"
                                :class="summary.visit_pct>=80?'bg-emerald-500':summary.visit_pct>=50?'bg-amber-500':'bg-rose-500'"
                                :style="`width: ${summary.visit_pct}%`"></div>
                        </div>
                        <span class="text-xs font-black text-gray-700 w-10 text-right">{{ summary.visit_pct }}%</span>
                    </div>
                    <p class="text-[10px] text-gray-600">
                        Đã thực hiện <strong class="text-purple-800">{{ summary.visit_completed }}</strong> /
                        <strong>{{ summary.visit_planned }}</strong> lượt thăm viếng được lên kế hoạch.
                        <span v-if="summary.visit_pct >= 80" class="text-emerald-700 font-bold">🎉 Xuất sắc!</span>
                        <span v-else-if="summary.visit_pct >= 50" class="text-amber-700">Cần cố gắng thêm.</span>
                        <span v-else-if="summary.visit_planned === 0" class="text-gray-400">Chưa có kế hoạch thăm viếng.</span>
                        <span v-else class="text-rose-700 font-bold">⚠️ Cần chú trọng thăm viếng!</span>
                    </p>
                </div>
                <div v-if="visitations.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-600">Ngày</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-600">Tín hữu</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-600 hidden md:table-cell">Lý do</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-600 hidden lg:table-cell">Người đi thăm</th>
                                <th class="px-4 py-2.5 text-center text-xs font-bold text-gray-600">TT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="v in visitations" :key="v.id" class="hover:bg-purple-50">
                                <td class="px-4 py-2.5 text-xs font-bold text-gray-900 whitespace-nowrap">{{ v.visit_date }}</td>
                                <td class="px-4 py-2.5 text-sm font-medium text-gray-900">{{ v.member_name }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-600 hidden md:table-cell">{{ v.reason || '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-500 hidden lg:table-cell">{{ v.visitors || '—' }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                        :class="v.status==='completed'?'bg-green-100 text-green-700':'bg-amber-100 text-amber-700'">
                                        {{ v.status === 'completed' ? '✓ Đã thăm' : '⏳ KH' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-5 py-8 text-center text-sm text-gray-400">Không có lịch thăm viếng nào trong tháng {{ localMonth }}/{{ localYear }}.</div>
            </div>

            <!-- ══ NEXT MONTH ══ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 bg-amber-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-white">G. CHƯƠNG TRÌNH THÁNG TIẾP THEO</h3>
                        <p class="text-[10px] text-amber-200">{{ next_month_label }}</p>
                    </div>
                    <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ next_month_meetings.length }} buổi</span>
                </div>
                <div v-if="next_month_meetings.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-amber-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-amber-900">Ngày</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-amber-900 hidden sm:table-cell">Loại</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-amber-900">Chủ đề</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-amber-900 hidden md:table-cell">Kinh thánh</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-amber-900 hidden md:table-cell">Câu gốc</th>
                                <th class="px-4 py-2.5 text-left text-xs font-bold text-amber-900">Diễn giả</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="m in next_month_meetings" :key="m.id" class="hover:bg-amber-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <p class="text-sm font-black text-gray-900">{{ m.date }}</p>
                                    <p class="text-[10px] text-gray-500 capitalize">{{ m.day }}</p>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="m.is_dept?'bg-indigo-100 text-indigo-700':'bg-blue-100 text-blue-700'">
                                        {{ m.is_dept ? 'Ban Ngành' : 'Hội Thánh' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <span v-if="m.topic">{{ m.topic }}</span>
                                    <span v-else class="text-gray-300 italic text-xs">Chưa có</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 hidden md:table-cell">{{ m.scripture || '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500 italic hidden md:table-cell">{{ m.memory_verse || '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ m.preacher || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-5 py-8 text-center text-sm text-gray-400">Chưa có lịch cho {{ next_month_label }}. Hãy vào <strong>Quản lý Buổi Nhóm</strong> để thêm.</div>
            </div>

            <!-- ══ AI + NARRATIVE ══ -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-violet-700 to-purple-800 rounded-2xl p-5 shadow-xl text-white text-xs leading-relaxed">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-base">🤖</div>
                        <div>
                            <p class="text-sm font-black">AI Phân Tích Tự Động</p>
                            <p class="text-[10px] text-purple-300">Dựa trên dữ liệu thực tế tháng {{ localMonth }}/{{ localYear }}</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="bg-white/10 rounded-xl p-3">
                            <p class="font-bold mb-1">📊 Sinh Hoạt</p>
                            <p>• HT: <strong>{{ summary.church_count }} buổi</strong>, TB <strong>{{ summary.avg_church }}</strong>
                                <span :class="summary.church_change>=0?'text-green-300':'text-red-300'"> ({{ summary.church_change>=0?'▲':'▼' }}{{ Math.abs(summary.church_change) }}%)</span>
                            </p>
                            <p>• Ban: <strong>{{ summary.dept_count }} buổi</strong>, TB <strong>{{ summary.avg_dept }}</strong>
                                <span :class="summary.dept_change>=0?'text-green-300':'text-red-300'"> ({{ summary.dept_change>=0?'▲':'▼' }}{{ Math.abs(summary.dept_change) }}%)</span>
                            </p>
                            <p v-if="summary.avg_dept < summary.avg_church * 0.5" class="text-amber-300 mt-1">⚠️ Tỷ lệ tham dự nhóm Ban thấp bất thường.</p>
                        </div>
                        <div class="bg-white/10 rounded-xl p-3">
                            <p class="font-bold mb-1">💰 Tài Chính</p>
                            <p>• Thu: {{ fmt(summary.month_income) }} · Chi: {{ fmt(summary.month_expense) }}</p>
                            <p>• Tồn cuối: <strong>{{ fmt(summary.closing_balance) }}</strong></p>
                            <p v-if="summary.closing_balance < 0" class="text-red-300 font-bold">🚨 Quỹ âm!</p>
                            <p v-else-if="summary.month_expense > summary.month_income * 0.85" class="text-amber-300">⚠️ Chi tiêu >85% thu nhập tháng này.</p>
                            <p v-else class="text-green-300">✅ Tài chính ổn định.</p>
                        </div>
                        <div class="bg-white/10 rounded-xl p-3">
                            <p class="font-bold mb-1">🏠 Thăm Viếng</p>
                            <p v-if="summary.visit_planned === 0">Chưa lên kế hoạch thăm viếng.</p>
                            <template v-else>
                                <p>{{ summary.visit_completed }}/{{ summary.visit_planned }} lượt (<span :class="summary.visit_pct>=80?'text-green-300':'text-amber-300'">{{ summary.visit_pct }}%</span>)</p>
                                <p v-if="summary.visit_pct < 50" class="text-rose-300">⚠️ Cần ưu tiên thăm viếng tháng tới.</p>
                                <p v-else-if="summary.visit_pct >= 80" class="text-green-300">🎉 Xuất sắc!</p>
                            </template>
                        </div>
                        <div v-if="report?.evaluation" class="border-t border-white/20 pt-3">
                            <p class="text-purple-300 font-bold text-[10px] uppercase mb-1">Nhận Xét</p>
                            <p class="italic">"{{ report.evaluation }}"</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900">📋 Nhận Xét & Kế Hoạch</h3>
                        <button v-if="canCreate" @click="openReportForm" class="text-xs font-bold text-purple-600 hover:text-purple-800">{{ report ? 'Chỉnh sửa' : 'Lập báo cáo' }} →</button>
                    </div>
                    <div class="p-4 space-y-3 flex-1">
                        <template v-if="report">
                            <p v-if="report.reporter_name" class="text-xs text-gray-500 font-medium">👤 {{ report.reporter_name }}</p>
                            <div v-if="report.evaluation"><p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Nhận xét</p><p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-2.5">{{ report.evaluation }}</p></div>
                            <div v-if="report.request"><p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Yêu cầu</p><p class="text-sm text-gray-700 bg-amber-50 rounded-lg p-2.5">{{ report.request }}</p></div>
                            <div v-if="report.proposals"><p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Đề nghị</p><p class="text-sm text-gray-700 bg-blue-50 rounded-lg p-2.5">{{ report.proposals }}</p></div>
                            <div v-if="report.activities_notes"><p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Hoạt động</p><p class="text-sm text-gray-700 bg-purple-50 rounded-lg p-2.5">{{ report.activities_notes }}</p></div>
                        </template>
                        <div v-else class="flex-1 flex flex-col items-center justify-center py-10">
                            <p class="text-3xl mb-2">📝</p>
                            <p class="text-sm text-gray-400">Chưa có báo cáo tháng {{ localMonth }}/{{ localYear }}</p>
                            <button v-if="canCreate" @click="openReportForm" class="mt-3 px-4 py-2 bg-purple-600 text-white text-xs font-bold rounded-xl">Lập Báo Cáo</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ SLIDE-OVER: REPORT FORM ══ -->
        <SlideOver :show="showReportForm" @close="showReportForm = false" title="Lập / Cập nhật Báo cáo">
            <form class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-bold text-gray-700 mb-1">Tháng *</label>
                        <select v-model="reportForm.report_month" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm">
                            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
                        </select></div>
                    <div><label class="block text-xs font-bold text-gray-700 mb-1">Năm *</label>
                        <input type="number" v-model="reportForm.report_year" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm" min="2020" max="2099"></div>
                </div>
                <div><label class="block text-xs font-bold text-gray-700 mb-1">Người báo cáo</label>
                    <input type="text" v-model="reportForm.reporter_name" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm" placeholder="CS. Nguyễn Văn A"></div>
                <div><label class="block text-xs font-bold text-gray-700 mb-1">Nhận xét chung</label>
                    <textarea v-model="reportForm.evaluation" rows="3" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm"></textarea></div>
                <div><label class="block text-xs font-bold text-gray-700 mb-1">Yêu cầu</label>
                    <textarea v-model="reportForm.request" rows="2" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm"></textarea></div>
                <div><label class="block text-xs font-bold text-gray-700 mb-1">Đề nghị / Kế hoạch</label>
                    <textarea v-model="reportForm.proposals" rows="2" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm"></textarea></div>
                <div><label class="block text-xs font-bold text-gray-700 mb-1">Tình hình hoạt động</label>
                    <textarea v-model="reportForm.activities_notes" rows="2" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm"></textarea></div>
            </form>
            <template #footer>
                <div class="flex gap-2 w-full justify-end">
                    <button @click="showReportForm=false" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium hover:bg-gray-50">Hủy</button>
                    <button @click="submitReport" :disabled="formLoading" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-xl disabled:opacity-50">{{ formLoading ? 'Đang lưu...' : 'Lưu' }}</button>
                </div>
            </template>
        </SlideOver>

        <SlideOver :show="isSwitchOpen" @close="isSwitchOpen=false" title="Chuyển Ban Ngành">
            <div class="space-y-2">
                <div v-for="d in availableDepartments" :key="d.id" @click="switchDept(d.id)"
                    class="p-4 rounded-xl border-2 cursor-pointer transition-all"
                    :class="department?.id===d.id?'border-blue-500 bg-blue-50':'border-gray-100 hover:border-gray-300'">
                    <h4 class="text-sm font-bold" :class="department?.id===d.id?'text-blue-900':'text-gray-900'">{{ d.name }}</h4>
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
import VueApexCharts from 'vue3-apexcharts';
const apexchart = VueApexCharts;

const props = defineProps({
    department: Object,
    availableDepartments: { type: Array, default: () => [] },
    isGlobalAdmin: Boolean,
    canCreate: Boolean,
    canApprove: Boolean,
    filters: Object,
    church_meetings: { type: Array, default: () => [] },
    dept_meetings:   { type: Array, default: () => [] },
    church_weekly:   { type: Array, default: () => [] },
    dept_weekly:     { type: Array, default: () => [] },
    combined_weekly: { type: Array, default: () => [] },
    weekly_finance:  { type: Array, default: () => [] },
    three_month_chart: { type: Array, default: () => [] },
    visitations:         { type: Array, default: () => [] },
    next_month_meetings: { type: Array, default: () => [] },
    next_month_label: String,
    summary: { type: Object, default: () => ({}) },
    fund_balances: { type: Array, default: () => [] },
    report: Object,
});

const localMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const localYear  = ref(props.filters?.year  || new Date().getFullYear());
const isSwitchOpen = ref(false);
const showReportForm = ref(false);
const formLoading = ref(false);

const fmt = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v || 0);
const statusLabel = (s) => ({ draft: 'Bản nháp', submitted: 'Đã nộp', approved: '✓ Đã Duyệt' }[s] || s);
const updatePeriod = () => router.get(route('portal.reports.index'), { month: localMonth.value, year: localYear.value }, { preserveState: true, replace: true });

// ── KPI cards ────────────────────────────────────────────────────
const kpiCards = computed(() => [
    { label: 'TB HD Hội thánh', value: props.summary.avg_church ?? 0, bg: 'bg-white border border-gray-100', labelColor: 'text-amber-600', valueColor: 'text-gray-900', change: props.summary.church_change, prev: props.summary.prev_avg_church, prevColor: 'text-gray-400' },
    { label: 'TB HD Ban ngành', value: props.summary.avg_dept ?? 0,   bg: 'bg-white border border-gray-100', labelColor: 'text-indigo-600', valueColor: 'text-gray-900', change: props.summary.dept_change, prev: props.summary.prev_avg_dept,   prevColor: 'text-gray-400' },
    { label: 'Tổng Thu Tháng',  value: fmt(props.summary.month_income ?? 0),  bg: 'bg-gradient-to-br from-emerald-500 to-emerald-600', labelColor: 'text-emerald-100', valueColor: 'text-white', sub: `Chi: ${fmt(props.summary.month_expense ?? 0)}`, subColor: 'text-emerald-200' },
    { label: 'Tồn Cuối Kỳ',    value: fmt(props.summary.closing_balance ?? 0), bg: 'bg-gradient-to-br from-blue-600 to-indigo-700', labelColor: 'text-blue-200', valueColor: 'text-white', sub: `Đầu kỳ: ${fmt(props.summary.opening_balance ?? 0)}`, subColor: 'text-blue-300' },
]);

// ── Attendance area charts ────────────────────────────────────────
const WEEK_LABELS = ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4', 'Tuần 5'];
const areaOpts = (color, categories) => ({
    chart: { toolbar: { show: false }, zoom: { enabled: false } },
    stroke: { curve: 'smooth', width: 3 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
    colors: [color],
    xaxis: { categories, labels: { style: { fontSize: '11px', fontWeight: 700 } } },
    yaxis: { min: 0, labels: { style: { fontSize: '11px' } } },
    grid: { borderColor: '#f3f4f6' },
    markers: { size: 5, hover: { size: 7 } },
    dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 700 }, background: { enabled: false }, dropShadow: { enabled: false } },
    tooltip: { y: { formatter: (v) => `${v} người` } },
    legend: { show: false },
});

// Use actual meeting dates for chart categories, churchWeekly for weekly bars
const churchAttCategories = computed(() => props.church_meetings.map(m => m.date));
const churchAttChartOpts  = computed(() => areaOpts('#F59E0B', churchAttCategories.value));
const churchAttSeries     = computed(() => [{ name: 'Hiện Diện HT', data: props.church_meetings.map(m => m.attendance) }]);

const deptAttCategories = computed(() => props.dept_meetings.map(m => m.date));
const deptAttChartOpts  = computed(() => areaOpts('#6366F1', deptAttCategories.value));
const deptAttSeries     = computed(() => [{ name: 'Hiện Diện Ban', data: props.dept_meetings.map(m => m.attendance) }]);

// ── Combined bar chart (weekly) ────────────────────────────────
const combinedBarOpts = computed(() => ({
    chart: { toolbar: { show: false } },
    colors: ['#3B82F6', '#8B5CF6'],
    xaxis: { categories: WEEK_LABELS, labels: { style: { fontSize: '11px', fontWeight: 700 } } },
    yaxis: { min: 0, tickAmount: 5, labels: { style: { fontSize: '11px' } } },
    plotOptions: { bar: { borderRadius: 6, columnWidth: '60%', dataLabels: { position: 'top' } } },
    legend: { position: 'top', fontSize: '12px', fontWeight: 700 },
    grid: { borderColor: '#f3f4f6' },
    dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 700 }, offsetY: -18, dropShadow: { enabled: false } },
    tooltip: { y: { formatter: (v) => `${v} người` } },
}));
const combinedBarSeries = computed(() => [
    { name: 'Hội Thánh', data: props.combined_weekly.map(w => w.church) },
    { name: 'Ban Ngành', data: props.combined_weekly.map(w => w.dept)   },
]);

// ── 3-month combined finance line chart ──────────────────────────
// x-axis = Tuần 1-5, each SERIES = one month (income)
const finTrendOpts = computed(() => ({
    chart: { toolbar: { show: false }, zoom: { enabled: false } },
    stroke: { curve: 'smooth', width: 2.5 },
    colors: ['#10B981', '#F59E0B', '#6366F1'],
    xaxis: { categories: WEEK_LABELS, labels: { style: { fontSize: '11px', fontWeight: 700 } } },
    yaxis: {
        labels: {
            style: { fontSize: '10px' },
            formatter: (v) => v >= 1000000 ? `${(v/1000000).toFixed(1)}M` : v >= 1000 ? `${(v/1000).toFixed(0)}k` : String(v)
        }
    },
    legend: { position: 'top', fontSize: '12px', fontWeight: 700 },
    grid: { borderColor: '#f3f4f6' },
    markers: { size: 5 },
    dataLabels: { enabled: false },
    tooltip: { y: { formatter: (v) => new Intl.NumberFormat('vi-VN').format(v) + ' đ' } },
}));
const finTrendSeries = computed(() =>
    props.three_month_chart.map(fm => ({
        name: fm.label,
        data: fm.income,
    }))
);

// ── Report form ───────────────────────────────────────────────────
const reportForm = ref({ report_month: localMonth.value, report_year: localYear.value, reporter_name: '', evaluation: '', request: '', proposals: '', activities_notes: '' });
const openReportForm = () => {
    reportForm.value = {
        report_month: localMonth.value, report_year: localYear.value,
        reporter_name: props.report?.reporter_name || '',
        evaluation: props.report?.evaluation || '',
        request: props.report?.request || '',
        proposals: props.report?.proposals || '',
        activities_notes: props.report?.activities_notes || '',
    };
    showReportForm.value = true;
};
const submitReport = () => {
    formLoading.value = true;
    router.post(route('portal.reports.save'), reportForm.value, { preserveScroll: true, onSuccess: () => { showReportForm.value = false; }, onFinish: () => { formLoading.value = false; } });
};
const approveReport = () => {
    if (!props.report) return;
    router.post(route('portal.reports.approve', props.report.id), {}, { preserveScroll: true });
};
const switchDept = (id) => {
    router.post(route('portal.switch-context'), { department_id: id }, { preserveScroll: true, onSuccess: () => { isSwitchOpen.value = false; } });
};
</script>
