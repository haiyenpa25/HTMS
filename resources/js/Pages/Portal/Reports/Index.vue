<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" @open-switcher="isSwitchOpen = true">
        <Head title="Báo cáo Ban ngành" />

        <div class="space-y-6 w-full">

            <!-- ══ HEADER ══ -->
            <!-- Print-only letterhead (hidden on screen) -->
            <div class="print-only hidden print-letterhead">
                <h1>{{ churchName || 'HỘI THÁNH TIN LÀNH' }}</h1>
                <p>BÁO CÁO TÌNH HÌNH SINH HOẠT — {{ department?.name }}</p>
                <p>Tháng {{ localMonth }}/{{ localYear }}</p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-6 no-print">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">📑 BÁO CÁO TÌNH HÌNH SINH HOẠT</h2>
                        <!-- Tooltip Helper -->
                        <div class="relative group cursor-help mt-1">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="absolute bottom-full left-4 sm:left-1/2 sm:-translate-x-1/2 mb-2 w-72 p-3 bg-gray-900 text-white text-xs font-medium rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-20 shadow-xl pointer-events-none">
                                Giao diện này hiển thị Tổng Hợp Dữ Liệu theo thời gian thực từ các hệ thống khác (Thăm viếng, Điểm danh, Tài chính...). Để nộp báo cáo cho Mục Sư, bạn cần nhấn "Lập Báo Cáo Mới" để chốt số liệu.
                                <div class="absolute top-full left-4 sm:left-1/2 sm:-translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-[15px] text-gray-500 mt-1 font-medium">{{ department?.name }} · Tháng {{ localMonth }}/{{ localYear }}</p>
                </div>
                
                <!-- Mobile Tab Scroll Bar (replaces select dropdown) -->
                <div class="sm:hidden mb-4 bg-white rounded-2xl shadow-sm border border-gray-100 flex overflow-x-auto">
                    <button
                        @click="activeTab = 'board'"
                        class="flex-1 min-w-[7rem] py-3.5 text-center font-bold text-[13px] transition-colors relative whitespace-nowrap px-4"
                        :class="activeTab === 'board' ? 'text-blue-600 bg-blue-50/50' : 'text-gray-500'"
                    >
                        Ban ĐH
                        <div v-if="activeTab === 'board'" class="absolute bottom-0 left-0 w-full h-[3px] bg-blue-600"></div>
                    </button>
                    <button
                        @click="activeTab = 'all'"
                        class="flex-1 min-w-[7rem] py-3.5 text-center font-bold text-[13px] transition-colors relative whitespace-nowrap px-4"
                        :class="activeTab === 'all' ? 'text-blue-600 bg-blue-50/50' : 'text-gray-500'"
                    >
                        Toàn Ban
                        <div v-if="activeTab === 'all'" class="absolute bottom-0 left-0 w-full h-[3px] bg-blue-600"></div>
                    </button>
                    <button
                        @click="activeTab = 'pending'"
                        class="flex-1 min-w-[7rem] py-3.5 text-center font-bold text-[13px] transition-colors relative whitespace-nowrap px-4"
                        :class="activeTab === 'pending' ? 'text-amber-600 bg-amber-50/50' : 'text-gray-500'"
                    >
                        Khách Mới
                        <span v-if="pendingCount > 0" class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full bg-amber-500 text-white text-[10px] font-black">{{ pendingCount }}</span>
                        <div v-if="activeTab === 'pending'" class="absolute bottom-0 left-0 w-full h-[3px] bg-amber-500"></div>
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-4 py-2.5 shadow-sm">
                        <select v-model="localMonth" @change="updatePeriod" class="text-[15px] font-bold text-gray-700 bg-transparent border-none focus:ring-0 p-0 cursor-pointer">
                            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
                        </select>
                        <span class="text-gray-300 font-medium">/</span>
                        <input v-model="localYear" @change="updatePeriod" type="number" class="w-16 text-[15px] bg-transparent border-none focus:ring-0 p-0 text-center font-bold cursor-pointer" min="2020" max="2099">
                    </div>
                    <span v-if="report" class="px-3.5 py-2 rounded-xl text-[13px] font-bold shadow-sm"
                        :class="report.status==='approved'?'bg-green-100 text-green-800 border-green-200':report.status==='submitted'?'bg-amber-100 text-amber-800 border-amber-200':'bg-gray-100 text-gray-600 border-gray-200'">
                        {{ statusLabel(report.status) }}
                    </span>
                    <button v-if="canApprove && report?.status==='submitted'" @click="approveReport" class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-[15px] font-bold rounded-xl transition-colors shadow-sm">✓ Duyệt Báo cáo</button>
                    <button v-if="canCreate" @click="openReportForm" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-[15px] font-bold rounded-xl shadow-sm transition-colors">
                        {{ report ? '📝 Cập nhật BC' : '✨ Lập Báo cáo mới' }}
                    </button>
                    <!-- Download PDF button (server-side dompdf) -->
                    <a
                        :href="`${route('portal.reports.export-pdf')}?month=${localMonth}&year=${localYear}`"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white text-[15px] font-bold rounded-xl hover:bg-emerald-700 transition-colors shadow-sm"
                        title="Tải xuống file PDF báo cáo tháng"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Tải PDF
                    </a>
                    <!-- Print button -->
                    <button @click="printReport" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 text-white text-[15px] font-bold rounded-xl hover:bg-gray-900 transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        In / PDF
                    </button>
                </div>

            </div>

            <!-- ══ KPI CARDS ══ -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="card in kpiCards" :key="card.label" class="rounded-2xl p-5 shadow-sm border" :class="card.bg">
                    <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider" :class="card.labelColor">{{ card.label }}</p>
                    <p class="text-3xl font-black mt-2 leading-none" :class="card.valueColor">{{ card.value }}</p>
                    <div v-if="card.change !== undefined" class="mt-3 flex items-center gap-2">
                        <span class="text-xs sm:text-[13px] font-bold px-2 py-0.5 rounded-md" :class="card.change>=0?'bg-green-100 text-green-700':'bg-red-100 text-red-700'">
                            {{ card.change >= 0 ? '▲' : '▼' }} {{ Math.abs(card.change) }}%
                        </span>
                        <span class="text-[11px] sm:text-xs font-medium" :class="card.prevColor">T.trước: {{ card.prev }}</span>
                    </div>
                    <p v-else class="text-[11px] sm:text-xs font-medium mt-3" :class="card.subColor">{{ card.sub }}</p>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════ -->
            <!-- SECTION A: BUỔI NHÓM HỘI THÁNH                   -->
            <!-- [Table LEFT | Chart RIGHT]                        -->
            <!-- ══════════════════════════════════════════════════ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-900 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-black text-white flex items-center gap-2"><span class="text-slate-400">A.</span> BUỔI NHÓM HỘI THÁNH</h3>
                        <p class="text-[11px] text-slate-300 mt-0.5">Số lượng hiện diện từng tuần trong tháng</p>
                    </div>
                    <span class="bg-white/10 text-white text-xs font-bold px-3 py-1 rounded-full">{{ church_meetings.length }} buổi · TB {{ summary.avg_church }} · <span class="text-amber-300" title="Tổng người thuộc câu gốc">📖 {{ summary.total_memory_verse_church ?? 0 }} người</span></span>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-2 divide-y xl:divide-y-0 xl:divide-x divide-gray-100">
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800">Ngày</th>
                                    <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800">Chủ đề</th>
                                    <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800 hidden md:table-cell">Diễn giả</th>
                                    <th class="px-4 py-3 text-center text-[13px] font-bold text-slate-800">Hiện Diện</th>
                                    <th class="px-4 py-3 text-center text-[13px] font-bold text-slate-800 hidden sm:table-cell">📖 Câu gốc</th>
                                    <th class="px-4 py-3 text-right text-[13px] font-bold text-slate-800 hidden sm:table-cell">Thu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template v-for="m in church_meetings" :key="m.id">
                                    <tr 
                                        class="hover:bg-slate-50/80 transition-colors cursor-pointer group"
                                        @click="toggleExpand(m.id, 'church')"
                                    >
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform duration-200" 
                                                    :class="{ 'rotate-90': expandedChurchRows.includes(m.id) }"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                <div>
                                                    <p class="text-[13px] font-black text-gray-900">{{ m.date }}</p>
                                                    <p class="text-[11px] text-gray-500 capitalize mt-0.5">{{ m.day }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-[13px] text-gray-800 max-w-[140px] truncate leading-snug">{{ m.topic || '—' }}</td>
                                        <td class="px-4 py-3 text-[13px] text-gray-600 hidden md:table-cell leading-snug">{{ m.speaker || '—' }}</td>
                                        <td class="px-4 py-3 text-center text-[15px] font-black text-amber-700">{{ m.attendance > 0 ? m.attendance : '—' }}</td>
                                        <td class="px-4 py-3 text-center text-[13px] font-bold text-indigo-700 hidden sm:table-cell">{{ m.memory_verse_count > 0 ? m.memory_verse_count : '—' }}</td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-emerald-700 hidden sm:table-cell">{{ m.income > 0 ? fmt(m.income) : '—' }}</td>
                                    </tr>
                                    <!-- Expanded Details Row -->
                                    <tr v-if="expandedChurchRows.includes(m.id)" class="bg-gray-50/50">
                                        <td colspan="5" class="px-4 py-3">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 p-3 bg-white rounded-xl border border-gray-100 shadow-sm text-[13px]">
                                                <!-- Add full details here, as columns might truncate on mobile -->
                                                <div class="md:hidden">
                                                    <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Diễn giả</span>
                                                    <span class="text-gray-900 font-medium">{{ m.speaker || '—' }}</span>
                                                </div>
                                                <div class="sm:hidden">
                                                    <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Tiền dâng</span>
                                                    <span class="text-emerald-700 font-bold">{{ m.income > 0 ? fmt(m.income) : '—' }}</span>
                                                </div>
                                                <div>
                                                    <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Kinh thánh</span>
                                                    <span class="text-gray-700">{{ m.scripture || '—' }}</span>
                                                </div>
                                                <div>
                                                    <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Câu gốc</span>
                                                    <span class="text-gray-700 italic">{{ m.memory_verse || '—' }}</span>
                                                </div>
                                                <div class="sm:col-span-2 md:col-span-1">
                                                    <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Ghi chú nhóm</span>
                                                    <span class="text-gray-700">{{ m.notes || '—' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-if="church_meetings.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-[13px] text-gray-400">Chưa có buổi nhóm HT nào trong tháng</td>
                                </tr>
                                <!-- Weekly summary rows -->
                                <tr class="bg-slate-50 border-t-2 border-slate-200">
                                    <td colspan="2" class="px-4 py-3 text-[13px] font-black text-slate-900">TỔNG / TRUNG BÌNH</td>
                                    <td class="px-4 py-3 hidden md:table-cell"></td>
                                    <td class="px-4 py-3 text-center text-[15px] font-black text-amber-700">TB: {{ summary.avg_church }}</td>
                                    <td class="px-4 py-3 text-center text-[13px] font-black text-indigo-700 hidden sm:table-cell">📖 {{ summary.total_memory_verse_church ?? 0 }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-black text-emerald-700 hidden sm:table-cell">{{ fmt(summary.church_total_income) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Chart -->
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[13px] font-bold text-gray-700">📈 Biểu Đồ Hiện Diện Theo Tuần</p>
                            <label class="flex items-center gap-1.5 cursor-pointer text-xs font-medium text-gray-500 hover:text-gray-700">
                                <input type="checkbox" v-model="compareChurch" class="rounded text-amber-600 border-gray-300 focus:ring-amber-500 w-3.5 h-3.5 transition-colors"> So sánh tháng trước
                            </label>
                        </div>
                        <div v-if="church_meetings.length > 0">
                            <apexchart type="area" height="240" :options="churchAttChartOpts" :series="churchAttSeries" />
                        </div>
                        <div v-else class="h-56 flex flex-col items-center justify-center text-gray-300 gap-2">
                            <span class="text-3xl">📭</span>
                            <p class="text-[13px]">Không có dữ liệu</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════ -->
            <!-- SECTION B: BUỔI NHÓM SINH HOẠT BAN               -->
            <!-- [Table LEFT | Chart RIGHT]                        -->
            <!-- ══════════════════════════════════════════════════ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-900 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-black text-white flex items-center gap-2"><span class="text-slate-400">B.</span> BUỔI NHÓM SINH HOẠT BAN NGÀNH</h3>
                        <p class="text-[11px] text-slate-300 mt-0.5">Số lượng hiện diện từng tuần trong tháng</p>
                    </div>
                    <span class="bg-white/10 text-white text-xs font-bold px-3 py-1 rounded-full">{{ dept_meetings.length }} buổi · TB {{ summary.avg_dept }} · <span class="text-indigo-300" title="Tổng người thuộc câu gốc">📖 {{ summary.total_memory_verse_dept ?? 0 }} người</span></span>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-2 divide-y xl:divide-y-0 xl:divide-x divide-gray-100">
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800">Ngày</th>
                                    <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800">Chủ đề</th>
                                    <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800 hidden md:table-cell">Diễn giả</th>
                                    <th class="px-4 py-3 text-center text-[13px] font-bold text-slate-800">Hiện Diện</th>
                                    <th class="px-4 py-3 text-center text-[13px] font-bold text-slate-800 hidden sm:table-cell">📖 Câu gốc</th>
                                    <th class="px-4 py-3 text-right text-[13px] font-bold text-slate-800 hidden sm:table-cell">Thu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template v-for="m in dept_meetings" :key="m.id">
                                    <tr 
                                        class="hover:bg-slate-50/80 transition-colors cursor-pointer group"
                                        @click="toggleExpand(m.id, 'dept')"
                                    >
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform duration-200" 
                                                    :class="{ 'rotate-90': expandedDeptRows.includes(m.id) }"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                <div>
                                                    <p class="text-[13px] font-black text-gray-900">{{ m.date }}</p>
                                                    <p class="text-[11px] text-gray-500 capitalize mt-0.5">{{ m.day }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-[13px] text-gray-800 max-w-[140px] truncate leading-snug">{{ m.topic || '—' }}</td>
                                        <td class="px-4 py-3 text-[13px] text-gray-600 hidden md:table-cell leading-snug">{{ m.speaker || '—' }}</td>
                                        <td class="px-4 py-3 text-center text-[15px] font-black text-amber-700">{{ m.attendance > 0 ? m.attendance : '—' }}</td>
                                        <td class="px-4 py-3 text-center text-[13px] font-bold text-indigo-700 hidden sm:table-cell">{{ m.memory_verse_count > 0 ? m.memory_verse_count : '—' }}</td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-emerald-700 hidden sm:table-cell">{{ m.income > 0 ? fmt(m.income) : '—' }}</td>
                                    </tr>
                                    <!-- Expanded Details Row -->
                                    <tr v-if="expandedDeptRows.includes(m.id)" class="bg-gray-50/50">
                                        <td colspan="5" class="px-4 py-3">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 p-3 bg-white rounded-xl border border-gray-100 shadow-sm text-[13px]">
                                                <!-- Add full details here, as columns might truncate on mobile -->
                                                <div class="md:hidden">
                                                    <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Diễn giả</span>
                                                    <span class="text-gray-900 font-medium">{{ m.speaker || '—' }}</span>
                                                </div>
                                                <div class="sm:hidden">
                                                    <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Tiền dâng</span>
                                                    <span class="text-emerald-700 font-bold">{{ m.income > 0 ? fmt(m.income) : '—' }}</span>
                                                </div>
                                                <div>
                                                    <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Kinh thánh</span>
                                                    <span class="text-gray-700">{{ m.scripture || '—' }}</span>
                                                </div>
                                                <div>
                                                    <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Câu gốc</span>
                                                    <span class="text-gray-700 italic">{{ m.memory_verse || '—' }}</span>
                                                </div>
                                                <div class="sm:col-span-2 md:col-span-1">
                                                    <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Ghi chú nhóm</span>
                                                    <span class="text-gray-700">{{ m.notes || '—' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-if="dept_meetings.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-[13px] text-gray-400">Chưa có buổi nhóm Ban nào trong tháng</td>
                                </tr>
                                <tr class="bg-slate-50 border-t-2 border-slate-200">
                                    <td colspan="2" class="px-4 py-3 text-[13px] font-black text-slate-900">TỔNG / TRUNG BÌNH</td>
                                    <td class="px-4 py-3 hidden md:table-cell"></td>
                                    <td class="px-4 py-3 text-center text-[15px] font-black text-amber-700">TB: {{ summary.avg_dept }}</td>
                                    <td class="px-4 py-3 text-center text-[13px] font-black text-indigo-700 hidden sm:table-cell">📖 {{ summary.total_memory_verse_dept ?? 0 }}</td>
                                    <td class="px-4 py-3 text-right text-sm font-black text-emerald-700 hidden sm:table-cell">{{ fmt(summary.dept_total_income) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Chart -->
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[13px] font-bold text-gray-700">📈 Biểu Đồ Hiện Diện Theo Tuần</p>
                            <label class="flex items-center gap-1.5 cursor-pointer text-xs font-medium text-gray-500 hover:text-gray-700">
                                <input type="checkbox" v-model="compareDept" class="rounded text-indigo-600 border-gray-300 focus:ring-indigo-500 w-3.5 h-3.5 transition-colors"> So sánh tháng trước
                            </label>
                        </div>
                        <div v-if="dept_meetings.length > 0">
                            <apexchart type="area" height="240" :options="deptAttChartOpts" :series="deptAttSeries" />
                        </div>
                        <div v-else class="h-56 flex flex-col items-center justify-center text-gray-300 gap-2">
                            <span class="text-3xl">📭</span>
                            <p class="text-[13px]">Không có dữ liệu</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ SECTION C: CHART CỘT SO SÁNH ══ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-[15px] font-bold text-gray-900 mb-1">📊 C. So Sánh Số Lượng Tham Dự Theo Tuần</h3>
                <p class="text-[11px] text-gray-500 mb-4">Tổng người tham dự Buổi Nhóm Hội Thánh và Buổi Nhóm Ban Ngành theo từng tuần trong tháng</p>
                <apexchart type="bar" height="220" :options="combinedBarOpts" :series="combinedBarSeries" />
            </div>

            <!-- ══ FINANCE TABLE (dept meetings only) ══ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-900 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-black text-white flex items-center gap-2"><span class="text-slate-400">D.</span> TÀI CHÍNH — TIỀN DÂNG BAN NGÀNH SINH HOẠT</h3>
                        <p class="text-[11px] text-slate-300 mt-0.5">Chỉ buổi nhóm Ban Ngành mới có tiền dâng · Tháng {{ localMonth }}/{{ localYear }}</p>
                    </div>
                    <span class="bg-white/10 text-white text-xs font-bold px-3 py-1 rounded-full">{{ dept_meetings.length }} buổi</span>
                </div>
                <!-- Per-meeting detail rows -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800 whitespace-nowrap">Ngày</th>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800">Chủ đề</th>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800 hidden lg:table-cell">Kinh thánh</th>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800 hidden xl:table-cell">Câu gốc</th>
                                <th class="px-4 py-3 text-right text-[13px] font-bold text-slate-800">Tiền Dâng</th>
                                <th class="px-4 py-3 text-right text-[13px] font-bold text-slate-800 hidden sm:table-cell">Chi</th>
                                <th class="px-4 py-3 text-center text-[13px] font-bold text-slate-800 hidden sm:table-cell">Tuần</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template v-for="m in dept_meetings" :key="m.id">
                                <tr 
                                    class="hover:bg-slate-50/80 transition-colors cursor-pointer group"
                                    @click="toggleExpand(m.id, 'finance')"
                                >
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform duration-200" 
                                                :class="{ 'rotate-90': expandedFinanceRows.includes(m.id) }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <div>
                                                <p class="text-[13px] font-black text-gray-900">{{ m.date }}</p>
                                                <p class="text-[11px] text-gray-500 capitalize mt-0.5">{{ m.day }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-[13px] font-medium text-gray-800 max-w-[160px] truncate">{{ m.topic || '—' }}</td>
                                    <td class="px-4 py-3 text-[13px] text-gray-600 hidden lg:table-cell">{{ m.scripture || '—' }}</td>
                                    <td class="px-4 py-3 text-[13px] text-gray-500 italic hidden xl:table-cell max-w-[130px] truncate">{{ m.memory_verse || '—' }}</td>
                                    <td class="px-4 py-3 text-right text-[15px] font-bold text-emerald-700">{{ m.income > 0 ? fmt(m.income) : '—' }}</td>
                                    <td class="px-4 py-3 text-right text-[15px] font-bold text-rose-700 hidden sm:table-cell">{{ m.expense > 0 ? fmt(m.expense) : '—' }}</td>
                                    <td class="px-4 py-3 text-center hidden sm:table-cell">
                                        <span class="text-[11px] font-bold bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full border border-slate-200">T{{ m.week_no }}</span>
                                    </td>
                                </tr>
                                <!-- Expanded Details Row -->
                                <tr v-if="expandedFinanceRows.includes(m.id)" class="bg-gray-50/50">
                                    <td colspan="8" class="px-4 py-3">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 p-3 bg-white rounded-xl border border-gray-100 shadow-sm text-[13px]">
                                            <div class="lg:hidden">
                                                <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Kinh thánh</span>
                                                <span class="text-gray-700">{{ m.scripture || '—' }}</span>
                                            </div>
                                            <div class="xl:hidden">
                                                <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Câu gốc</span>
                                                <span class="text-gray-700 italic">{{ m.memory_verse || '—' }}</span>
                                            </div>
                                            <div class="sm:hidden">
                                                <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Biên lai thu</span>
                                                <span class="text-gray-700">{{ m.income_receipt_no || '—' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="dept_meetings.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-[13px] text-gray-400">Chưa có buổi nhóm Ban Ngành nào trong tháng</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Ledger summary -->
                <div class="px-5 py-5 bg-gray-50 border-t grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-2.5">
                        <div class="flex justify-between text-[15px]"><span class="text-gray-600">Tồn đầu tháng:</span><span class="font-bold">{{ fmt(summary.opening_balance) }}</span></div>
                        <div class="flex justify-between text-[15px] text-emerald-700"><span>+ Tổng thu:</span><span class="font-bold">{{ fmt(summary.month_income) }}</span></div>
                        <div class="flex justify-between text-[15px] text-rose-700"><span>- Tổng chi:</span><span class="font-bold">{{ fmt(summary.month_expense) }}</span></div>
                        <div class="flex justify-between text-[17px] font-black border-t border-gray-200 pt-3 mt-1 text-blue-900"><span>TỒN CUỐI:</span><span>{{ fmt(summary.closing_balance) }}</span></div>
                    </div>
                    <div v-if="fund_balances.length > 0" class="space-y-2.5">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Theo Quỹ</p>
                        <div v-for="f in fund_balances" :key="f.id" class="flex justify-between text-[15px]">
                            <span class="text-gray-600">{{ f.name }}</span>
                            <span class="font-bold" :class="f.balance>=0?'text-emerald-700':'text-rose-700'">{{ fmt(f.balance) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══ FINANCE TREND CHART (1 chart, 3 lines) ══ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-[15px] font-bold text-gray-900 mb-1">💰 E. Xu Hướng Tài Chính 3 Tháng Gần Nhất</h3>
                <p class="text-[11px] text-gray-500 mb-4">Mỗi đường = 1 tháng · Trục ngang = Tuần 1-5 · Giá trị = Tổng thu trong tuần</p>
                <apexchart type="line" height="240" :options="finTrendOpts" :series="finTrendSeries" />
            </div>

            <!-- ══ VISITATION ══ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-900 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-[15px] font-black text-white flex items-center gap-2"><span class="text-slate-400">F.</span> CÔNG TÁC THĂM VIẾNG</h3>
                        <p class="text-[11px] text-slate-300 mt-0.5">Tháng {{ localMonth }}/{{ localYear }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-white/10 text-white text-[11px] font-bold px-2.5 py-1 rounded-full border border-white/5">{{ summary.visit_planned }} kế hoạch</span>
                        <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[11px] font-bold px-2.5 py-1 rounded-full">✓ {{ summary.visit_completed }} đã thăm</span>
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full border"
                            :class="summary.visit_pct>=80?'bg-emerald-500/20 text-emerald-300 border-emerald-500/30':summary.visit_pct>=50?'bg-amber-500/20 text-amber-300 border-amber-500/30':'bg-rose-500/20 text-rose-300 border-rose-500/30'">
                            {{ summary.visit_pct }}%
                        </span>
                    </div>
                </div>
                <!-- Progress bar + summary text -->
                <div class="px-5 py-4 bg-slate-50 border-b border-slate-100">
                    <div class="flex items-center gap-4 mb-2">
                        <div class="flex-1 bg-slate-200 rounded-full h-3">
                            <div class="h-3 rounded-full transition-all duration-700"
                                :class="summary.visit_pct>=80?'bg-emerald-500':summary.visit_pct>=50?'bg-amber-500':'bg-rose-500'"
                                :style="`width: ${summary.visit_pct}%`"></div>
                        </div>
                        <span class="text-[13px] font-black text-slate-700 w-12 text-right">{{ summary.visit_pct }}%</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Đã thực hiện <strong class="text-slate-900">{{ summary.visit_completed }}</strong> /
                        <strong>{{ summary.visit_planned }}</strong> lượt thăm viếng được lên kế hoạch.
                        <span v-if="summary.visit_pct >= 80" class="text-emerald-600 font-bold ml-1">🎉 Xuất sắc!</span>
                        <span v-else-if="summary.visit_pct >= 50" class="text-amber-600 font-medium ml-1">Cần cố gắng thêm.</span>
                        <span v-else-if="summary.visit_planned === 0" class="text-slate-400 ml-1">Chưa có kế hoạch thăm viếng.</span>
                        <span v-else class="text-rose-600 font-bold ml-1">⚠️ Cần chú trọng thăm viếng!</span>
                    </p>
                </div>
                <div v-if="visitations.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800">Ngày</th>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800">Tín hữu</th>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800 hidden md:table-cell">Lý do</th>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800 hidden lg:table-cell">Người đi thăm</th>
                                <th class="px-4 py-3 text-center text-[13px] font-bold text-slate-800">TT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template v-for="v in visitations" :key="v.id">
                                <tr 
                                    class="hover:bg-slate-50/80 transition-colors cursor-pointer group shrink-0"
                                    @click="toggleExpand(v.id, 'visit')"
                                >
                                    <td class="px-4 py-3 text-[13px] font-bold text-gray-900 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform duration-200" 
                                                :class="{ 'rotate-90': expandedVisitRows.includes(v.id) }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <span>{{ v.visit_date }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-[15px] font-medium text-gray-900">{{ v.member_name }}</td>
                                    <td class="px-4 py-3 text-[13px] text-gray-600 hidden md:table-cell">{{ v.reason || '—' }}</td>
                                    <td class="px-4 py-3 text-[13px] text-gray-500 hidden lg:table-cell">{{ v.visitors || '—' }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full border"
                                            :class="v.status==='completed'?'bg-emerald-50 text-emerald-700 border-emerald-200':'bg-amber-50 text-amber-700 border-amber-200'">
                                            {{ v.status === 'completed' ? '✓ Đã thăm' : '⏳ KH' }}
                                        </span>
                                    </td>
                                </tr>
                                <!-- Expanded Details Row -->
                                <tr v-if="expandedVisitRows.includes(v.id)" class="bg-gray-50/50">
                                    <td colspan="5" class="px-4 py-3">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-3 bg-white rounded-xl border border-gray-100 shadow-sm text-[13px]">
                                            <div class="md:hidden">
                                                <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Lý do thăm viếng</span>
                                                <span class="text-gray-700">{{ v.reason || '—' }}</span>
                                            </div>
                                            <div class="lg:hidden">
                                                <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Người đi thăm</span>
                                                <span class="text-gray-700">{{ v.visitors || '—' }}</span>
                                            </div>
                                            <div class="sm:col-span-2">
                                                <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Diễn tiến / Ghi chú thăm viếng</span>
                                                <span class="text-gray-700">{{ v.notes || '—' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-5 py-8 text-center text-[13px] text-gray-400">Không có lịch thăm viếng nào trong tháng {{ localMonth }}/{{ localYear }}.</div>
            </div>

            <!-- ══ NEXT MONTH ══ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-900 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-black text-white flex items-center gap-2"><span class="text-slate-400">G.</span> CHƯƠNG TRÌNH THÁNG TIẾP THEO</h3>
                        <p class="text-[11px] text-slate-300 mt-0.5">{{ next_month_label }}</p>
                    </div>
                    <span class="bg-white/10 text-white text-xs font-bold px-3 py-1 rounded-full">{{ next_month_meetings.length }} buổi</span>
                </div>
                <div v-if="next_month_meetings.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800">Ngày</th>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800 hidden sm:table-cell">Loại</th>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800">Chủ đề</th>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800 hidden md:table-cell">Kinh thánh</th>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800 hidden md:table-cell">Câu gốc</th>
                                <th class="px-4 py-3 text-left text-[13px] font-bold text-slate-800">Diễn giả</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template v-for="m in next_month_meetings" :key="m.id">
                                <tr 
                                    class="hover:bg-slate-50/80 transition-colors cursor-pointer group"
                                    @click="toggleExpand(m.id, 'next')"
                                >
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform duration-200" 
                                                :class="{ 'rotate-90': expandedNextRows.includes(m.id) }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <div>
                                                <p class="text-[15px] font-black text-gray-900">{{ m.date }}</p>
                                                <p class="text-[11px] text-gray-500 capitalize mt-0.5">{{ m.day }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 hidden sm:table-cell">
                                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full border border-slate-200" :class="m.is_dept?'bg-indigo-50 text-indigo-700':'bg-blue-50 text-blue-700'">
                                            {{ m.is_dept ? 'Ban Ngành' : 'Hội Thánh' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-[15px] font-medium text-gray-900">
                                        <span v-if="m.topic">{{ m.topic }}</span>
                                        <span v-else class="text-gray-400 italic text-[13px]">Chưa có</span>
                                    </td>
                                    <td class="px-4 py-3 text-[13px] text-gray-600 hidden md:table-cell">{{ m.scripture || '—' }}</td>
                                    <td class="px-4 py-3 text-[13px] text-gray-500 italic hidden md:table-cell">{{ m.memory_verse || '—' }}</td>
                                    <td class="px-4 py-3 text-[15px] text-gray-700">{{ m.preacher || '—' }}</td>
                                </tr>
                                <!-- Expanded Details Row -->
                                <tr v-if="expandedNextRows.includes(m.id)" class="bg-gray-50/50">
                                    <td colspan="6" class="px-4 py-3">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 p-3 bg-white rounded-xl border border-gray-100 shadow-sm text-[13px]">
                                            <div class="md:hidden">
                                                <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Kinh thánh</span>
                                                <span class="text-gray-700">{{ m.scripture || '—' }}</span>
                                            </div>
                                            <div class="md:hidden">
                                                <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Câu gốc</span>
                                                <span class="text-gray-700 italic">{{ m.memory_verse || '—' }}</span>
                                            </div>
                                            <div class="sm:hidden">
                                                <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Loại buổi nhóm</span>
                                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full border border-slate-200 inline-block" :class="m.is_dept?'bg-indigo-50 text-indigo-700':'bg-blue-50 text-blue-700'">
                                                    {{ m.is_dept ? 'Ban Ngành' : 'Hội Thánh' }}
                                                </span>
                                            </div>
                                            <div class="sm:col-span-2 md:col-span-3">
                                                <span class="font-bold text-gray-500 block mb-1 text-[11px] uppercase tracking-wider">Ghi chú kế hoạch</span>
                                                <span class="text-gray-700">{{ m.notes || '—' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-5 py-8 text-center text-[13px] text-gray-400">Chưa có lịch cho {{ next_month_label }}. Hãy vào <strong>Quản lý Buổi Nhóm</strong> để thêm.</div>
            </div>

            <!-- ══ AI + NARRATIVE ══ -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 no-print">
                <div class="bg-gradient-to-br from-violet-700 to-purple-800 rounded-2xl p-5 shadow-xl text-white text-[13px] leading-relaxed">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg shadow-inner">🤖</div>
                        <div>
                            <p class="text-[15px] font-black tracking-wide">AI Phân Tích Tự Động</p>
                            <p class="text-[11px] text-purple-200 mt-0.5">Dựa trên dữ liệu thực tế tháng {{ localMonth }}/{{ localYear }}</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-white/10 rounded-xl p-4 shadow-sm backdrop-blur-sm">
                            <p class="font-bold mb-2 text-[15px] text-purple-100 border-b border-purple-400/30 pb-1">📊 Sinh Hoạt</p>
                            <div class="space-y-1">
                                <p>• HT: <strong>{{ summary.church_count }} buổi</strong>, TB <strong>{{ summary.avg_church }}</strong>
                                    <span :class="summary.church_change>=0?'text-green-300':'text-rose-300 font-medium'"> ({{ summary.church_change>=0?'▲':'▼' }}{{ Math.abs(summary.church_change) }}%)</span>
                                </p>
                                <p>• Ban: <strong>{{ summary.dept_count }} buổi</strong>, TB <strong>{{ summary.avg_dept }}</strong>
                                    <span :class="summary.dept_change>=0?'text-green-300':'text-rose-300 font-medium'"> ({{ summary.dept_change>=0?'▲':'▼' }}{{ Math.abs(summary.dept_change) }}%)</span>
                                </p>
                            </div>
                            <p v-if="summary.avg_dept < summary.avg_church * 0.5" class="text-amber-300 mt-2 font-medium bg-amber-900/30 px-2 py-1 rounded inline-block">⚠️ Tỷ lệ tham dự nhóm Ban thấp bất thường.</p>
                        </div>
                        <div class="bg-white/10 rounded-xl p-4 shadow-sm backdrop-blur-sm">
                            <p class="font-bold mb-2 text-[15px] text-purple-100 border-b border-purple-400/30 pb-1">💰 Tài Chính</p>
                            <div class="space-y-1">
                                <p>• Thu: {{ fmt(summary.month_income) }} · Chi: {{ fmt(summary.month_expense) }}</p>
                                <p>• Tồn cuối: <strong class="text-[15px]">{{ fmt(summary.closing_balance) }}</strong></p>
                            </div>
                            <p v-if="summary.closing_balance < 0" class="text-rose-300 font-bold mt-2 bg-rose-900/30 px-2 py-1 rounded inline-block">🚨 Quỹ âm!</p>
                            <p v-else-if="summary.month_expense > summary.month_income * 0.85" class="text-amber-300 mt-2 font-medium bg-amber-900/30 px-2 py-1 rounded inline-block">⚠️ Chi tiêu >85% thu nhập tháng này.</p>
                            <p v-else class="text-green-300 mt-2">✅ Tài chính ổn định.</p>
                        </div>
                        <div class="bg-white/10 rounded-xl p-4 shadow-sm backdrop-blur-sm">
                            <p class="font-bold mb-2 text-[15px] text-purple-100 border-b border-purple-400/30 pb-1">🏠 Thăm Viếng</p>
                            <p v-if="summary.visit_planned === 0">Chưa lên kế hoạch thăm viếng.</p>
                            <template v-else>
                                <p>{{ summary.visit_completed }}/{{ summary.visit_planned }} lượt (<span :class="summary.visit_pct>=80?'text-green-300':'text-amber-300'">{{ summary.visit_pct }}%</span>)</p>
                                <p v-if="summary.visit_pct < 50" class="text-rose-300 mt-1">⚠️ Cần ưu tiên thăm viếng tháng tới.</p>
                                <p v-else-if="summary.visit_pct >= 80" class="text-green-300 mt-1">🎉 Xuất sắc!</p>
                            </template>
                        </div>
                        <div v-if="report?.evaluation" class="border-t border-white/20 pt-4 mt-2">
                            <p class="text-purple-300 font-bold text-[11px] uppercase tracking-wider mb-2">Nhận Xét Của BĐH HT</p>
                            <p class="italic text-[13px] leading-relaxed">"{{ report.evaluation }}"</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden relative print-narrative">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between z-10 bg-gray-50">
                        <h3 class="text-[15px] font-black text-gray-900">📋 Nhận Xét & Kế Hoạch Của Chi Phái</h3>
                        <button v-if="canCreate" @click="openReportForm" class="text-[13px] font-bold text-purple-600 hover:text-purple-800 transition-colors">{{ report ? 'Chỉnh sửa' : 'Lập báo cáo' }} →</button>
                    </div>
                    <div class="p-5 space-y-4 flex-1 relative z-10 bg-yellow-50/20">
                        <template v-if="report">
                            <p v-if="report.reporter_name" class="text-[13px] text-gray-500 font-medium flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>{{ report.reporter_name }}</p>
                            <div v-if="report.evaluation"><p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Nhận xét</p><p class="text-[15px] text-gray-700 bg-white border border-gray-200 rounded-xl p-4 shadow-sm leading-relaxed">{{ report.evaluation }}</p></div>
                            <div v-if="report.request"><p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Yêu cầu Hội thánh hỗ trợ</p><p class="text-[15px] text-gray-700 bg-amber-50 border border-amber-100 rounded-xl p-4 shadow-sm leading-relaxed">{{ report.request }}</p></div>
                            <div v-if="report.proposals"><p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Đề nghị / Kế hoạch tháng tới</p><p class="text-[15px] text-gray-700 bg-blue-50 border border-blue-100 rounded-xl p-4 shadow-sm leading-relaxed">{{ report.proposals }}</p></div>
                            <div v-if="report.activities_notes"><p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Ghi chú hoạt động khác</p><p class="text-[15px] text-gray-700 bg-purple-50 border border-purple-100 rounded-xl p-4 shadow-sm leading-relaxed">{{ report.activities_notes }}</p></div>
                        </template>
                        <div v-else class="flex-1 flex flex-col items-center justify-center py-12">
                            <p class="text-4xl mb-3">📝</p>
                            <p class="text-[15px] text-gray-400 font-medium">Chưa có báo cáo tháng {{ localMonth }}/{{ localYear }}</p>
                            <button v-if="canCreate" @click="openReportForm" class="mt-4 px-5 py-2.5 bg-purple-600 hover:bg-purple-700 transition-colors text-white text-[13px] font-bold rounded-xl shadow-sm">Lập Báo Cáo Ngay</button>
                        </div>
                    </div>
                    <!-- Watermark -->
                    <div class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-[0.03] z-0">
                        <svg viewBox="0 0 24 24" class="w-64 h-64"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Print-only: Nhận xét section -->
            <div v-if="report" class="print-only hidden bg-white border border-gray-400 rounded-xl p-6 mt-6 break-inside-avoid">
                <h3 class="font-black text-gray-900 mb-4 text-[15px] uppercase border-b border-gray-300 pb-2">H. NHẬN XÉT & KẾ HOẠCH BÁO CÁO TỪ CHI PHÁI</h3>
                <div class="grid grid-cols-2 gap-6 text-[15px]">
                    <div v-if="report.evaluation"><p class="font-bold text-gray-600 text-[10pt] uppercase tracking-wider mb-2">Nhận xét chung</p><p class="text-gray-900 leading-relaxed">{{ report.evaluation }}</p></div>
                    <div v-if="report.request"><p class="font-bold text-gray-600 text-[10pt] uppercase tracking-wider mb-2">Yêu cầu HT hỗ trợ</p><p class="text-gray-900 leading-relaxed">{{ report.request }}</p></div>
                    <div v-if="report.proposals"><p class="font-bold text-gray-600 text-[10pt] uppercase tracking-wider mb-2">Đề nghị / Kế hoạch tháng tới</p><p class="text-gray-900 leading-relaxed">{{ report.proposals }}</p></div>
                    <div v-if="report.activities_notes"><p class="font-bold text-gray-600 text-[10pt] uppercase tracking-wider mb-2">Ghi chú hoạt động khác</p><p class="text-gray-900 leading-relaxed">{{ report.activities_notes }}</p></div>
                </div>
                <div class="mt-8 flex justify-end">
                    <div class="text-center">
                        <p class="text-[13px] text-gray-500 mb-12">Người lập báo cáo</p>
                        <p class="text-[15px] font-bold text-gray-900">{{ report.reporter_name || '.......................................' }}</p>
                    </div>
                </div>
            </div>

            <!-- Print-only footer -->
            <div class="print-only hidden print-footer">
                <div class="sign-block">
                    <p class="sign-label">Người Lập Báo Cáo</p>
                    <div class="sign-line"></div>
                    <p class="mt-1 text-xs text-gray-500">{{ report?.reporter_name || '...................' }}</p>
                </div>
                <div class="sign-block">
                    <p class="sign-label">Ban Quản Nhiệm</p>
                    <div class="sign-line"></div>
                    <p class="mt-1 text-xs text-gray-500">Ký tên, đóng dấu</p>
                </div>
            </div>
        </div>

        <!-- ══ SLIDE-OVER: REPORT FORM ══ -->

        <SlideOver v-model="showReportForm" title="Lập / Cập nhật Báo cáo">
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

        <SlideOver v-model="isSwitchOpen" title="Chuyển Ban Ngành">
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
    prev_church_weekly: { type: Array, default: () => [] },
    prev_dept_weekly:   { type: Array, default: () => [] },
    combined_weekly: { type: Array, default: () => [] },
    weekly_finance:  { type: Array, default: () => [] },
    three_month_chart: { type: Array, default: () => [] },
    visitations:         { type: Array, default: () => [] },
    next_month_meetings: { type: Array, default: () => [] },
    next_month_label: String,
    summary: { type: Object, default: () => ({}) },
    fund_balances: { type: Array, default: () => [] },
    report: Object,
    churchName: { type: String, default: '' },
});

const localMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const localYear  = ref(props.filters?.year  || new Date().getFullYear());
const isSwitchOpen = ref(false);
const showReportForm = ref(false);
const formLoading = ref(false);

// Expandable Rows State
const expandedChurchRows = ref([]);
const expandedDeptRows = ref([]);
const expandedFinanceRows = ref([]);
const expandedVisitRows = ref([]);
const expandedNextRows = ref([]);

const toggleExpand = (id, type) => {
    let targetMap = {
        'church': expandedChurchRows,
        'dept': expandedDeptRows,
        'finance': expandedFinanceRows,
        'visit': expandedVisitRows,
        'next': expandedNextRows
    };
    
    let targetRef = targetMap[type];
    if (!targetRef) return;
    
    const index = targetRef.value.indexOf(id);
    if (index === -1) {
        targetRef.value.push(id);
    } else {
        targetRef.value.splice(index, 1);
    }
};

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
const compareChurch = ref(false);
const churchAttChartOpts  = computed(() => {
    let opts = areaOpts('#F59E0B', WEEK_LABELS);
    opts.colors = compareChurch.value ? ['#F59E0B', '#9CA3AF'] : ['#F59E0B'];
    opts.legend = { show: compareChurch.value, position: 'top', horizontalAlign: 'right', fontSize: '11px' };
    return opts;
});
const churchAttSeries     = computed(() => {
    let s = [{ name: 'Tháng này', data: props.church_weekly.map(m => m.attendance) }];
    if (compareChurch.value) s.push({ name: 'Tháng trước', data: props.prev_church_weekly.map(m => m.attendance) });
    return s;
});

const compareDept = ref(false);
const deptAttChartOpts  = computed(() => {
    let opts = areaOpts('#6366F1', WEEK_LABELS);
    opts.colors = compareDept.value ? ['#6366F1', '#9CA3AF'] : ['#6366F1'];
    opts.legend = { show: compareDept.value, position: 'top', horizontalAlign: 'right', fontSize: '11px' };
    return opts;
});
const deptAttSeries     = computed(() => {
    let s = [{ name: 'Tháng này', data: props.dept_weekly.map(m => m.attendance) }];
    if (compareDept.value) s.push({ name: 'Tháng trước', data: props.prev_dept_weekly.map(m => m.attendance) });
    return s;
});

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

// ── Print ─────────────────────────────────────────────────────────────────
const printReport = () => window.print();
</script>

<style>
@media print {
    body { 
        background-color: white !important; 
        -webkit-print-color-adjust: exact; 
        print-color-adjust: exact; 
    }
    
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    
    .print-letterhead {
        text-align: center;
        margin-bottom: 2rem;
        border-bottom: 2px solid #111827;
        padding-bottom: 1rem;
    }
    .print-letterhead h1 { font-size: 1.5rem; font-weight: 900; margin: 0; color: #111827 !important; }
    .print-letterhead p { font-size: 1.1rem; font-weight: 700; margin: 0.25rem 0 0; color: #374151 !important; }
    
    /* Clean up UI for print */
    .shadow-sm, .shadow-md, .shadow-lg, .shadow-xl { box-shadow: none !important; }
    .rounded-xl, .rounded-2xl { border-radius: 0 !important; }
    .border { border-color: #e5e7eb !important; }
    .bg-white { background-color: transparent !important; }
    
    /* Simplify dark headers */
    .bg-slate-900, .bg-blue-900 { 
        background-color: #f3f4f6 !important; 
    }
    .bg-slate-900 * { color: #111827 !important; }
    
    /* Table styles */
    table { width: 100% !important; border-collapse: collapse !important; border: 1px solid #d1d5db !important; }
    th, td { border: 1px solid #d1d5db !important; padding: 0.5rem !important; }
    .bg-slate-50 { background-color: #f9fafb !important; }
    
    /* Ensure tables don't get truncated */
    .overflow-x-auto, .overflow-hidden { overflow: visible !important; }
    
    /* Avoid breaking tables across pages if possible */
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; page-break-after: auto; }
    thead { display: table-header-group; }
    
    /* Hide charts on print as they lose context and rendering in standard print is bad */
    .apexcharts-canvas { display: none !important; }
    
    /* KPI grid adjust */
    .grid { display: grid !important; }
    
    /* General margins */
    @page { margin: 15mm; }
    
    /* Ensure text colors are readable */
    .text-white { color: #000 !important; }
    .text-slate-300, .text-slate-400 { color: #4b5563 !important; }
}
</style>
