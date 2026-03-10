<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" portal-type="education">
        <!-- Full-width indigo header -->
        <div class="bg-gradient-to-r from-indigo-800 via-indigo-700 to-indigo-600 text-white">
            <div class="w-full py-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-indigo-300 text-xs font-bold uppercase tracking-wider mb-1">Ban Cơ Đốc Giáo Dục</p>
                        <h1 class="text-2xl font-black leading-tight">Báo Cáo {{ monthLabel }}</h1>
                        <p class="text-indigo-200 text-xs mt-0.5">{{ sundays.length }} Chủ Nhật · {{ classData.length }} lớp học</p>
                    </div>
                    <div class="flex items-center gap-3 flex-wrap">
                        <button @click="prevMonth" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <input type="month" v-model="selectedMonth" @change="loadReport"
                            class="bg-white/10 border border-white/20 text-white rounded-xl px-4 py-2 text-sm font-bold focus:ring-2 focus:ring-white/30 focus:outline-none" />
                        <button @click="nextMonth" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <button @click="printReport" class="flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-sm font-bold transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            In báo cáo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full py-6 space-y-8">

            <!-- ── No data state ─────────────────────────────── -->
            <div v-if="classData.length === 0" class="py-24 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
                <div class="w-20 h-20 bg-indigo-50 rounded-3xl flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="font-black text-gray-700 text-xl mb-2">Chưa có dữ liệu {{ monthLabel }}</h3>
                <p class="text-gray-400 text-sm">Vào lớp học và điểm danh để tạo dữ liệu báo cáo.</p>
            </div>

            <div v-else id="report-content" class="space-y-8">

                <!-- ── KPI Summary Cards ─────────────────────────────── -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-1">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tổng học viên</div>
                        <div class="text-3xl font-black text-indigo-700">{{ totalStudents }}</div>
                        <div class="text-xs text-gray-400">{{ classData.length }} lớp học</div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-1">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">TB Có Mặt / CN</div>
                        <div class="text-3xl font-black text-green-600">{{ overallAvgPresent }}</div>
                        <div class="text-xs text-gray-400">{{ sundays.length }} Chủ Nhật</div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-1">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tổng Tiền Dâng</div>
                        <div class="text-3xl font-black text-emerald-600">{{ formatMoneyShort(totalIncome) }}</div>
                        <div class="text-xs text-gray-400">Thu trong tháng</div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col gap-1">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tồn Quỹ</div>
                        <div class="text-3xl font-black" :class="(totalIncome - totalExpense) >= 0 ? 'text-indigo-700' : 'text-red-600'">
                            {{ formatMoneyShort(totalIncome - totalExpense) }}
                        </div>
                        <div class="text-xs text-gray-400">Chi: {{ formatMoneyShort(totalExpense) }}</div>
                    </div>
                </div>

                <!-- ── Attendance chart + Class breakdown (side by side) ─── -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Bar chart: Điểm danh theo CN -->
                    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-black text-gray-900 text-sm uppercase tracking-wider mb-1">Biểu Đồ Điểm Danh</h2>
                        <p class="text-xs text-gray-400 mb-4">Số học viên có mặt theo từng Chủ Nhật</p>
                        <div class="relative" style="height: 240px">
                            <canvas ref="attendanceChartRef"></canvas>
                        </div>
                    </div>

                    <!-- Donut chart: Phân bổ lớp -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-black text-gray-900 text-sm uppercase tracking-wider mb-1">Phân Bổ Học Viên</h2>
                        <p class="text-xs text-gray-400 mb-4">Tỷ lệ học viên theo lớp</p>
                        <div class="relative" style="height: 180px">
                            <canvas ref="donutChartRef"></canvas>
                        </div>
                        <!-- Legend -->
                        <div class="mt-3 space-y-1.5">
                            <div v-for="(cls, i) in classData" :key="cls.id" class="flex items-center gap-2 text-xs">
                                <div class="w-3 h-3 rounded-sm shrink-0" :style="{ background: chartColors[i % chartColors.length] }"></div>
                                <span class="font-medium text-gray-700 truncate">{{ cls.name }}</span>
                                <span class="ml-auto font-bold text-gray-500">{{ cls.total_students }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Per-class attendance trend (small sparklines) ──── -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4" v-if="sundays.length > 0">
                    <div v-for="(cls, i) in classData" :key="cls.id"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full mr-1"
                                    :class="classTypeBadge(cls.class_type).class">{{ classTypeBadge(cls.class_type).label }}</span>
                                <p class="font-black text-gray-900 text-sm mt-1">{{ cls.name }}</p>
                                <p v-if="cls.teachers" class="text-xs text-gray-400">{{ cls.teachers }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-2xl font-black text-indigo-700">{{ cls.avg_present }}</div>
                                <div class="text-[10px] text-gray-400">TB / buổi</div>
                            </div>
                        </div>
                        <!-- Mini sparkline -->
                        <div class="relative" style="height: 60px">
                            <canvas :ref="el => sparkRefs[i] = el"></canvas>
                        </div>
                        <!-- Sundays row -->
                        <div class="flex gap-1 mt-2">
                            <div v-for="sunday in sundays" :key="sunday"
                                class="flex-1 text-center text-[9px] font-bold"
                                :class="cls.sessions[sunday]?.has_data ? 'text-indigo-600' : 'text-gray-300'">
                                {{ shortDay(sunday) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── BẢNG TỔNG HỢP ĐIỂM DANH (Sunday School + Gospel) ─── -->
                <div v-if="mainClasses.length > 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-indigo-700 to-indigo-600 text-white flex items-center justify-between">
                        <div>
                            <h2 class="font-black text-base">Bảng Điểm Danh Tổng Hợp</h2>
                            <p class="text-indigo-200 text-xs mt-0.5">Trường Chủ Nhật + Lớp Giáo Lý</p>
                        </div>
                        <div class="text-xs font-bold bg-white/20 text-white px-3 py-1.5 rounded-xl">{{ sundays.length }} Chủ Nhật</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-indigo-50 border-b border-indigo-100">
                                    <th class="px-4 py-3 text-left font-bold text-indigo-900 whitespace-nowrap w-36 sticky left-0 bg-indigo-50">Chủ Nhật</th>
                                    <template v-for="cls in mainClasses" :key="cls.id">
                                        <th class="px-3 py-3 text-center font-bold text-indigo-900 whitespace-nowrap border-l border-indigo-100"
                                            :colspan="cls.class_type !== 'gospel' ? 2 : 1">
                                            <div>{{ cls.name }}</div>
                                            <div v-if="cls.teachers" class="text-[10px] font-normal text-indigo-400">({{ cls.teachers }})</div>
                                        </th>
                                    </template>
                                </tr>
                                <tr class="bg-indigo-50/60 border-b border-indigo-100 text-xs text-indigo-600">
                                    <td class="px-4 py-1.5 sticky left-0 bg-indigo-50/60"></td>
                                    <template v-for="cls in mainClasses" :key="cls.id">
                                        <td class="px-3 py-1.5 text-center font-bold border-l border-indigo-100">Hiện diện</td>
                                        <td v-if="cls.class_type !== 'gospel'" class="px-3 py-1.5 text-center font-bold border-l border-indigo-50">Tiền dâng</td>
                                    </template>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="(sunday, idx) in sundays" :key="sunday"
                                    class="hover:bg-indigo-50/30 transition-colors"
                                    :class="idx % 2 === 0 ? 'bg-white' : 'bg-gray-50/40'">
                                    <td class="px-4 py-3 font-bold text-gray-700 whitespace-nowrap sticky left-0 bg-inherit">{{ formatSunday(sunday) }}</td>
                                    <template v-for="cls in mainClasses" :key="cls.id">
                                        <td class="px-3 py-3 text-center border-l border-gray-100">
                                            <span v-if="cls.sessions[sunday]?.has_data"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg font-black text-sm"
                                                :class="presentClass(cls.sessions[sunday]?.present, cls.avg_present)">
                                                {{ cls.sessions[sunday]?.present || 0 }}
                                            </span>
                                            <span v-else class="text-gray-200 text-lg">—</span>
                                        </td>
                                        <td v-if="cls.class_type !== 'gospel'" class="px-3 py-3 text-center border-l border-gray-50">
                                            <span v-if="cls.sessions[sunday]?.has_data && cls.sessions[sunday]?.income > 0"
                                                class="text-xs font-bold text-green-700 bg-green-50 px-2 py-0.5 rounded-full">
                                                {{ formatMoney(cls.sessions[sunday]?.income || 0) }}
                                            </span>
                                            <span v-else-if="cls.sessions[sunday]?.has_data" class="text-gray-300 text-xs">0 đ</span>
                                            <span v-else class="text-gray-200">—</span>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="bg-amber-50 font-black border-t-2 border-amber-200">
                                    <td class="px-4 py-3 text-amber-900 whitespace-nowrap sticky left-0 bg-amber-50">Trung bình / Tổng</td>
                                    <template v-for="cls in mainClasses" :key="cls.id">
                                        <td class="px-3 py-3 text-center border-l border-amber-200">
                                            <span class="text-amber-900">{{ cls.avg_present }}</span>
                                        </td>
                                        <td v-if="cls.class_type !== 'gospel'" class="px-3 py-3 text-center border-l border-amber-100">
                                            <span class="text-green-800">{{ formatMoney(cls.total_income) }}</span>
                                        </td>
                                    </template>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- ── LỊCH GIÁO VIÊN PHỤ TRÁCH ─── -->
                <div v-if="hasTeacherData" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-amber-600 to-orange-600 text-white flex items-center justify-between">
                        <div>
                            <h2 class="font-black text-base">👨‍🏫 Lịch Giáo Viên Phụ Trách</h2>
                            <p class="text-amber-100 text-xs mt-0.5">Giáo viên phụ trách từng Chủ Nhật theo lớp</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-amber-50 border-b border-amber-100">
                                    <th class="px-4 py-3 text-left font-bold text-amber-900 whitespace-nowrap w-32 sticky left-0 bg-amber-50">Chủ Nhật</th>
                                    <th v-for="cls in mainClasses" :key="cls.id"
                                        class="px-3 py-3 text-center font-bold text-amber-900 whitespace-nowrap border-l border-amber-100">
                                        {{ cls.name }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="(sunday, idx) in sundays" :key="sunday"
                                    class="hover:bg-amber-50/30 transition-colors"
                                    :class="idx % 2 === 0 ? 'bg-white' : 'bg-amber-50/20'">
                                    <td class="px-4 py-3 font-bold text-gray-700 whitespace-nowrap sticky left-0 bg-inherit">{{ formatSunday(sunday) }}</td>
                                    <td v-for="cls in mainClasses" :key="cls.id"
                                        class="px-3 py-2.5 text-center border-l border-gray-100">
                                        <div v-if="cls.sessions[sunday]?.has_data">
                                            <div v-if="cls.sessions[sunday]?.teacher_name"
                                                class="text-xs font-black text-amber-800 bg-amber-100 rounded-lg px-2 py-1 inline-block">
                                                {{ cls.sessions[sunday].teacher_name }}
                                            </div>
                                            <span v-else class="text-gray-300 text-xs">—</span>
                                            <div v-if="cls.sessions[sunday]?.lesson_number" class="text-[10px] text-gray-400 mt-0.5">
                                                Bài {{ cls.sessions[sunday].lesson_number }}
                                                <span v-if="cls.sessions[sunday]?.topic"> · {{ cls.sessions[sunday].topic }}</span>
                                            </div>
                                        </div>
                                        <span v-else class="text-gray-200">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ── BẢNG TRẮC NGHIỆM KINH THÁNH ─── -->
                <div v-if="bibleQuizClasses.length > 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 bg-gradient-to-r from-emerald-700 to-emerald-600 text-white">
                        <h2 class="font-black text-base">Bảng Kinh Thánh Trắc Nghiệm</h2>
                        <p class="text-emerald-200 text-xs mt-0.5">Điểm danh + điểm số học viên</p>
                    </div>
                    <template v-for="cls in bibleQuizClasses" :key="cls.id">
                        <!-- Stats row -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-0 border-b border-gray-100">
                            <div class="px-5 py-4 border-r border-gray-100">
                                <div class="text-xs text-gray-400 font-bold uppercase">Lớp</div>
                                <div class="font-black text-gray-900 text-sm mt-0.5">{{ cls.name }}</div>
                                <div class="text-xs text-gray-400">{{ cls.teachers || 'Chưa có GV' }}</div>
                            </div>
                            <div class="px-5 py-4 border-r border-gray-100 text-center">
                                <div class="text-xs text-gray-400 font-bold uppercase">Tham gia</div>
                                <div class="text-2xl font-black text-emerald-700">{{ cls.quiz_data.length }}</div>
                                <div class="text-xs text-gray-400">học viên</div>
                            </div>
                            <div class="px-5 py-4 border-r border-gray-100 text-center">
                                <div class="text-xs text-gray-400 font-bold uppercase">Trung tín</div>
                                <div class="text-2xl font-black text-blue-700">{{ cls.quiz_data.filter(q => q.is_faithful).length }}</div>
                                <div class="text-xs text-gray-400">người</div>
                            </div>
                            <div class="px-5 py-4 text-center">
                                <div class="text-xs text-gray-400 font-bold uppercase">Tỷ lệ TT</div>
                                <div class="text-2xl font-black text-amber-600">
                                    {{ cls.quiz_data.length > 0 ? Math.round(cls.quiz_data.filter(q => q.is_faithful).length / cls.quiz_data.length * 100) : 0 }}%
                                </div>
                                <div class="text-xs text-gray-400">trung tín</div>
                            </div>
                        </div>
                        <!-- Student table -->
                        <div v-if="cls.quiz_data.length > 0" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-bold text-gray-600 w-10">#</th>
                                        <th class="px-4 py-3 text-left font-bold text-gray-700">Họ tên</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700">Buổi có mặt</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700">Điểm TB</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700">Tình trạng</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="(student, idx) in cls.quiz_data" :key="idx"
                                        class="hover:bg-gray-50 transition-colors"
                                        :class="student.is_faithful ? 'bg-green-50/40' : ''">
                                        <td class="px-4 py-3 text-gray-400 text-center text-xs font-bold">{{ idx + 1 }}</td>
                                        <td class="px-4 py-3 font-bold text-gray-900">{{ student.name }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-black text-gray-700">{{ student.attended }}</span>
                                            <span class="text-gray-400">/{{ student.total_sessions }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span v-if="student.avg_score !== null"
                                                class="font-black text-indigo-700 bg-indigo-100 px-2.5 py-1 rounded-lg text-xs">
                                                {{ student.avg_score }}đ
                                            </span>
                                            <span v-else class="text-gray-300 text-xs">—</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span v-if="student.is_faithful"
                                                class="inline-flex items-center gap-1 text-green-700 bg-green-100 px-2.5 py-1 rounded-full text-xs font-black">
                                                ✓ Trung tín
                                            </span>
                                            <span v-else class="text-gray-300 text-xs">—</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>

                <!-- ── TÀI CHÍNH ─── -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Finance table (2/3) -->
                    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 bg-gradient-to-r from-green-700 to-emerald-600 text-white">
                            <h2 class="font-black text-base">Tài Chính Tháng</h2>
                            <p class="text-green-200 text-xs mt-0.5">Thu chi theo từng lớp học</p>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-green-50/60 border-b border-green-100">
                                <tr>
                                    <th class="px-5 py-3 text-left font-bold text-green-900">Lớp</th>
                                    <th class="px-5 py-3 text-right font-bold text-green-900">Tổng Thu</th>
                                    <th class="px-5 py-3 text-right font-bold text-green-900">Tổng Chi</th>
                                    <th class="px-5 py-3 text-right font-bold text-green-900">Tồn</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="cls in classesWithFinance" :key="cls.id" class="hover:bg-gray-50">
                                    <td class="px-5 py-3 font-bold text-gray-900">{{ cls.name }}</td>
                                    <td class="px-5 py-3 text-right text-green-700 font-bold">{{ formatMoney(cls.total_income) }}</td>
                                    <td class="px-5 py-3 text-right text-red-500 font-bold">{{ formatMoney(cls.total_expense) }}</td>
                                    <td class="px-5 py-3 text-right font-black" :class="(cls.total_income - cls.total_expense) >= 0 ? 'text-indigo-700' : 'text-red-700'">
                                        {{ formatMoney(cls.total_income - cls.total_expense) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="border-t-2 border-green-200 bg-green-50 font-black">
                                <tr>
                                    <td class="px-5 py-3 text-green-900">Tổng cộng</td>
                                    <td class="px-5 py-3 text-right text-green-800">{{ formatMoney(totalIncome) }}</td>
                                    <td class="px-5 py-3 text-right text-red-700">{{ formatMoney(totalExpense) }}</td>
                                    <td class="px-5 py-3 text-right" :class="(totalIncome - totalExpense) >= 0 ? 'text-indigo-800' : 'text-red-700'">
                                        {{ formatMoney(totalIncome - totalExpense) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- Finance bar chart (1/3) -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                        <h2 class="font-black text-gray-900 text-sm uppercase tracking-wider mb-1">Biểu Đồ Thu Chi</h2>
                        <p class="text-xs text-gray-400 mb-4">So sánh thu / chi từng lớp</p>
                        <div class="relative" style="height: 220px">
                            <canvas ref="financeChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <!-- ── LẬP BÁO CÁO NHẬN XÉT ─── -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="px-5 py-4 bg-gradient-to-r from-violet-700 to-purple-700 text-white flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-black text-base">Báo Cáo Nhận Xét Tháng</h2>
                            <p class="text-violet-200 text-xs mt-0.5">Đánh giá · Kế hoạch · Đề nghị</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <!-- Status badge -->
                            <span v-if="eduReport" class="px-3 py-1.5 rounded-xl text-xs font-black"
                                :class="{
                                    'bg-green-400/20 text-green-200': eduReport.status === 'approved',
                                    'bg-amber-400/20 text-amber-200': eduReport.status === 'submitted',
                                    'bg-white/15 text-white': eduReport.status === 'draft',
                                }">
                                {{ { draft: '📝 Bản nháp', submitted: '📤 Đã nộp', approved: '✓ Đã duyệt' }[eduReport.status] }}
                            </span>
                            <!-- Approve button -->
                            <button v-if="canApproveReport && eduReport?.status === 'submitted'"
                                @click="doApprove"
                                class="px-3 py-1.5 bg-green-400/30 hover:bg-green-400/50 text-green-200 text-xs font-black rounded-xl border border-green-400/30 transition-colors">
                                ✓ Duyệt báo cáo
                            </button>
                            <!-- Create / Edit -->
                            <button v-if="canManageReport" @click="openReportForm"
                                class="flex items-center gap-1.5 px-4 py-2 bg-white text-violet-700 font-black text-sm rounded-xl hover:bg-violet-50 transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                {{ eduReport ? 'Cập nhật' : 'Lập báo cáo' }}
                            </button>
                        </div>
                    </div>

                    <!-- Report content -->
                    <div v-if="eduReport" class="divide-y divide-gray-100">
                        <!-- Reporter info -->
                        <div v-if="eduReport.reporter_name" class="px-5 py-3 bg-violet-50/50 flex items-center gap-2">
                            <svg class="w-4 h-4 text-violet-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-sm font-bold text-violet-800">{{ eduReport.reporter_name }}</span>
                            <span class="text-xs text-gray-400">— Người báo cáo</span>
                        </div>
                        <!-- Sections grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-0 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                            <div class="p-5 space-y-4">
                                <div v-if="eduReport.evaluation">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span>Nhận xét chung
                                    </p>
                                    <p class="text-sm text-gray-700 bg-indigo-50 rounded-xl p-3 leading-relaxed">{{ eduReport.evaluation }}</p>
                                </div>
                                <div v-if="eduReport.highlights">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>Điểm nổi bật / Thành tựu
                                    </p>
                                    <p class="text-sm text-gray-700 bg-green-50 rounded-xl p-3 leading-relaxed">{{ eduReport.highlights }}</p>
                                </div>
                                <div v-if="eduReport.challenges">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>Khó khăn / Thách thức
                                    </p>
                                    <p class="text-sm text-gray-700 bg-amber-50 rounded-xl p-3 leading-relaxed">{{ eduReport.challenges }}</p>
                                </div>
                            </div>
                            <div class="p-5 space-y-4">
                                <div v-if="eduReport.request">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>Yêu cầu lên Ban Quản Nhiệm
                                    </p>
                                    <p class="text-sm text-gray-700 bg-red-50 rounded-xl p-3 leading-relaxed">{{ eduReport.request }}</p>
                                </div>
                                <div v-if="eduReport.proposals">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>Đề nghị / Kế hoạch tháng tới
                                    </p>
                                    <p class="text-sm text-gray-700 bg-blue-50 rounded-xl p-3 leading-relaxed">{{ eduReport.proposals }}</p>
                                </div>
                                <div v-if="eduReport.activities_notes">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-purple-500 inline-block"></span>Ghi chú hoạt động khác
                                    </p>
                                    <p class="text-sm text-gray-700 bg-purple-50 rounded-xl p-3 leading-relaxed">{{ eduReport.activities_notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="py-14 text-center">
                        <div class="w-16 h-16 bg-violet-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-violet-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="font-black text-gray-700 mb-1">Chưa có báo cáo nhận xét</h3>
                        <p class="text-gray-400 text-sm mb-4">Lập báo cáo để ghi nhận đánh giá tháng {{ monthLabel }}</p>
                        <button v-if="canManageReport" @click="openReportForm"
                            class="px-6 py-2 bg-violet-600 text-white font-bold text-sm rounded-xl hover:bg-violet-700 transition-colors">
                            ✏️ Lập báo cáo
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- SlideOver: Lập Báo Cáo Nhận Xét -->
        <SlideOver v-model="showReportForm" title="Lập Báo Cáo Nhận Xét" description="Nhận xét · Đánh giá · Kế hoạch Ban CĐGD">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1">Người báo cáo</label>
                    <input v-model="reportForm.reporter_name" type="text" placeholder="CS. Nguyễn Văn A"
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1">Trạng thái</label>
                        <select v-model="reportForm.status"
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm">
                            <option value="draft">📝 Bản nháp</option>
                            <option value="submitted">📤 Nộp báo cáo</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1">Nhận xét chung</label>
                    <textarea v-model="reportForm.evaluation" rows="3"
                        placeholder="Đánh giá tình hình sinh hoạt trong tháng..."
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 text-green-600">Điểm nổi bật / Thành tựu</label>
                    <textarea v-model="reportForm.highlights" rows="2"
                        placeholder="Những điểm tích cực, thành tựu đạt được..."
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 text-amber-600">Khó khăn / Thách thức</label>
                    <textarea v-model="reportForm.challenges" rows="2"
                        placeholder="Những khó khăn gặp phải trong tháng..."
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 text-red-500">Yêu cầu lên Ban Quản Nhiệm</label>
                    <textarea v-model="reportForm.request" rows="2"
                        placeholder="Các yêu cầu, hỗ trợ cần thiết..."
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 text-blue-600">Đề nghị / Kế hoạch tháng tới</label>
                    <textarea v-model="reportForm.proposals" rows="2"
                        placeholder="Kế hoạch dự kiến tháng tiếp theo..."
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-1 text-purple-600">Ghi chú hoạt động khác</label>
                    <textarea v-model="reportForm.activities_notes" rows="2"
                        placeholder="Hoạt động khác, kết nạp thành viên mới, ..."
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 text-sm"></textarea>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 w-full">
                    <button @click="showReportForm = false" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="submitReport" :disabled="reportFormLoading"
                        class="px-6 py-2 bg-violet-600 text-white rounded-xl text-sm font-bold hover:bg-violet-700 disabled:opacity-50 transition-colors">
                        {{ reportFormLoading ? 'Đang lưu...' : 'Lưu báo cáo' }}
                    </button>
                </div>
            </template>
        </SlideOver>

    </PortalLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';

Chart.register(...registerables);

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps({
    department: Object,
    availableDepartments: { type: Array, default: () => [] },
    isGlobalAdmin: Boolean,
    canManageReport: { type: Boolean, default: false },
    canApproveReport: { type: Boolean, default: false },
    month: String,
    sundays: { type: Array, default: () => [] },
    classData: { type: Array, default: () => [] },
    eduReport: { type: Object, default: null },
});

// ── State ──────────────────────────────────────────────────────────
const selectedMonth = ref(props.month);
const attendanceChartRef = ref(null);
const donutChartRef      = ref(null);
const financeChartRef    = ref(null);
const sparkRefs          = ref([]);
const showReportForm     = ref(false);
const reportFormLoading  = ref(false);

const reportForm = ref({
    report_month: '',
    report_year: '',
    reporter_name: '',
    status: 'draft',
    evaluation: '',
    highlights: '',
    challenges: '',
    request: '',
    proposals: '',
    activities_notes: '',
});

const openReportForm = () => {
    const [y, m] = (props.month || '').split('-');
    reportForm.value = {
        report_month: parseInt(m) || '',
        report_year: parseInt(y) || '',
        reporter_name: props.eduReport?.reporter_name || '',
        status: props.eduReport?.status === 'approved' ? 'submitted' : (props.eduReport?.status || 'draft'),
        evaluation: props.eduReport?.evaluation || '',
        highlights: props.eduReport?.highlights || '',
        challenges: props.eduReport?.challenges || '',
        request: props.eduReport?.request || '',
        proposals: props.eduReport?.proposals || '',
        activities_notes: props.eduReport?.activities_notes || '',
    };
    showReportForm.value = true;
};

const submitReport = () => {
    reportFormLoading.value = true;
    router.post(route('education.report.save'), reportForm.value, {
        preserveScroll: true,
        onSuccess: () => { showReportForm.value = false; },
        onFinish: () => { reportFormLoading.value = false; },
    });
};

const doApprove = () => {
    if (!props.eduReport) return;
    router.post(route('education.report.approve', props.eduReport.id), {}, { preserveScroll: true });
};

let attendanceChart = null;
let donutChart      = null;
let financeChart    = null;
const sparkCharts   = [];

// ── Chart palette ─────────────────────────────────────────────────
const chartColors = [
    '#6366f1', '#22c55e', '#f59e0b', '#ec4899', '#14b8a6',
    '#8b5cf6', '#ef4444', '#3b82f6', '#84cc16', '#f97316',
];

// ── Computed ───────────────────────────────────────────────────────
const mainClasses       = computed(() => props.classData.filter(c => c.class_type === 'sunday_school' || c.class_type === 'gospel'));
const bibleQuizClasses  = computed(() => props.classData.filter(c => c.class_type === 'bible_quiz'));
const classesWithFinance = computed(() => props.classData.filter(c => c.class_type !== 'gospel'));

// Chỉ hiển thị bảng GV nếu có ít nhất 1 session với teacher_name
const hasTeacherData = computed(() =>
    mainClasses.value.some(cls =>
        props.sundays.some(s => cls.sessions?.[s]?.teacher_name)
    )
);

const totalIncome  = computed(() => props.classData.reduce((s, c) => s + (c.total_income  || 0), 0));
const totalExpense = computed(() => props.classData.reduce((s, c) => s + (c.total_expense || 0), 0));

const totalStudents = computed(() => props.classData.reduce((s, c) => s + (c.total_students || 0), 0));

const overallAvgPresent = computed(() => {
    const classes = props.classData.filter(c => c.avg_present > 0);
    if (classes.length === 0) return 0;
    const sum = classes.reduce((s, c) => s + (parseFloat(c.avg_present) || 0), 0);
    return Math.round(sum);
});

const monthLabel = computed(() => {
    const [y, m] = (props.month || '').split('-');
    return `Tháng ${m}/${y}`;
});

// ── Navigation ────────────────────────────────────────────────────
const prevMonth = () => {
    const [y, m] = selectedMonth.value.split('-').map(Number);
    const d = new Date(y, m - 2, 1);
    selectedMonth.value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
    loadReport();
};
const nextMonth = () => {
    const [y, m] = selectedMonth.value.split('-').map(Number);
    const d = new Date(y, m, 1);
    selectedMonth.value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
    loadReport();
};
const loadReport = () => router.get(route('education.report'), { month: selectedMonth.value }, { preserveScroll: true });

// ── Charts ────────────────────────────────────────────────────────
const buildAttendanceChart = () => {
    if (!attendanceChartRef.value || props.sundays.length === 0) return;
    if (attendanceChart) attendanceChart.destroy();

    const labels = props.sundays.map(d =>
        new Intl.DateTimeFormat('vi-VN', { day: '2-digit', month: '2-digit' }).format(new Date(d + 'T00:00:00'))
    );

    const datasets = mainClasses.value.map((cls, i) => ({
        label: cls.name,
        data: props.sundays.map(s => cls.sessions[s]?.has_data ? (cls.sessions[s]?.present || 0) : null),
        backgroundColor: chartColors[i % chartColors.length] + '99',
        borderColor: chartColors[i % chartColors.length],
        borderWidth: 2,
        borderRadius: 6,
        spanGaps: true,
    }));

    attendanceChart = new Chart(attendanceChartRef.value, {
        type: 'bar',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 5, font: { size: 11 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 11 } }, grid: { display: false } },
            },
        },
    });
};

const buildDonutChart = () => {
    if (!donutChartRef.value || props.classData.length === 0) return;
    if (donutChart) donutChart.destroy();

    donutChart = new Chart(donutChartRef.value, {
        type: 'doughnut',
        data: {
            labels: props.classData.map(c => c.name),
            datasets: [{
                data: props.classData.map(c => c.total_students || 0),
                backgroundColor: chartColors.slice(0, props.classData.length),
                borderWidth: 2,
                borderColor: '#fff',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '66%',
            plugins: { legend: { display: false } },
        },
    });
};

const buildFinanceChart = () => {
    if (!financeChartRef.value || classesWithFinance.value.length === 0) return;
    if (financeChart) financeChart.destroy();

    financeChart = new Chart(financeChartRef.value, {
        type: 'bar',
        data: {
            labels: classesWithFinance.value.map(c => c.name.split(' ').slice(-2).join(' ')),
            datasets: [
                {
                    label: 'Thu',
                    data: classesWithFinance.value.map(c => c.total_income || 0),
                    backgroundColor: '#22c55e99',
                    borderColor: '#22c55e',
                    borderWidth: 2,
                    borderRadius: 4,
                },
                {
                    label: 'Chi',
                    data: classesWithFinance.value.map(c => c.total_expense || 0),
                    backgroundColor: '#ef444499',
                    borderColor: '#ef4444',
                    borderWidth: 2,
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => v >= 1000 ? (v/1000)+'K' : v, font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } },
            },
        },
    });
};

const buildSparklines = () => {
    sparkCharts.forEach(c => c?.destroy());
    sparkCharts.length = 0;

    props.classData.forEach((cls, i) => {
        const el = sparkRefs.value[i];
        if (!el) return;
        const data = props.sundays.map(s => cls.sessions[s]?.has_data ? (cls.sessions[s]?.present || 0) : null);
        const c = new Chart(el, {
            type: 'line',
            data: {
                labels: props.sundays.map(d =>
                    new Intl.DateTimeFormat('vi-VN', { day: '2-digit', month: '2-digit' }).format(new Date(d + 'T00:00:00'))
                ),
                datasets: [{
                    data,
                    borderColor: chartColors[i % chartColors.length],
                    backgroundColor: chartColors[i % chartColors.length] + '22',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: chartColors[i % chartColors.length],
                    fill: true,
                    tension: 0.3,
                    spanGaps: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => `${ctx.raw} người` } } },
                scales: {
                    y: { display: false, beginAtZero: true },
                    x: { display: false },
                },
            },
        });
        sparkCharts.push(c);
    });
};

const buildAllCharts = () => {
    nextTick(() => {
        buildAttendanceChart();
        buildDonutChart();
        buildFinanceChart();
        buildSparklines();
    });
};

onMounted(buildAllCharts);
watch(() => props.classData, buildAllCharts, { deep: true });

// ── Helpers ────────────────────────────────────────────────────────
const formatSunday = (d) => d
    ? new Intl.DateTimeFormat('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(d + 'T00:00:00'))
    : '';

const shortDay = (d) => d
    ? new Intl.DateTimeFormat('vi-VN', { day: '2-digit', month: '2-digit' }).format(new Date(d + 'T00:00:00'))
    : '';

const formatMoney = (val) => {
    if (!val && val !== 0) return '—';
    if (val === 0) return '0 đ';
    return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
};

const formatMoneyShort = (val) => {
    if (!val && val !== 0) return '0';
    if (Math.abs(val) >= 1_000_000) return (val / 1_000_000).toFixed(1) + 'M';
    if (Math.abs(val) >= 1_000) return (val / 1_000).toFixed(0) + 'K';
    return val + 'đ';
};

const classTypeBadge = (type) => ({
    sunday_school: { label: 'Trường CN', class: 'bg-indigo-100 text-indigo-700' },
    gospel:        { label: 'Giáo Lý',   class: 'bg-purple-100 text-purple-700' },
    bible_quiz:    { label: 'Trắc Nghiệm', class: 'bg-emerald-100 text-emerald-700' },
}[type] || { label: type, class: 'bg-gray-100 text-gray-700' });

const presentClass = (val, avg) => {
    if (val === null || val === undefined) return 'bg-gray-50 text-gray-300';
    const n = parseFloat(avg) || 0;
    if (val >= n) return 'bg-green-100 text-green-800';
    if (val >= n * 0.8) return 'bg-yellow-100 text-yellow-800';
    return 'bg-red-100 text-red-700';
};

const printReport = () => window.print();
</script>

<style scoped>
@media print {
    header, nav, .no-print { display: none !important; }
    #report-content { padding: 0; }
}
</style>