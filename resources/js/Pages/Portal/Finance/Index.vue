<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" @open-switcher="isSwitchOpen = true">
        <Head title="Tài chính Ban ngành" />

        <div class="py-4 space-y-5 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black text-gray-900">Tài chính Ban ngành</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ department?.name }} · Tháng {{ localMonth }}/{{ localYear }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Period Picker -->
                    <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                        <select v-model="localMonth" @change="updatePeriod" class="text-sm font-medium text-gray-700 border-none focus:ring-0 p-0 pr-1">
                            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
                        </select>
                        <input v-model="localYear" @change="updatePeriod" type="number" class="w-16 text-sm border-none focus:ring-0 p-0 text-center font-medium" min="2020" max="2099">
                    </div>
                    <button v-if="canManage && funds.length > 0" @click="openMeetingForm()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 shadow-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Thêm Buổi Nhóm
                    </button>
                    <button v-if="canManage" @click="showFundForm = true" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 shadow-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Quỹ
                    </button>
                </div>
            </div>

            <!-- No funds warning -->
            <div v-if="funds.length === 0 && canManage" class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <h4 class="text-sm font-bold text-amber-900">Chưa có quỹ nào</h4>
                    <p class="text-xs text-amber-700 mt-0.5">Hãy tạo ít nhất một quỹ trước khi nhập buổi nhóm.</p>
                    <button @click="showFundForm = true" class="mt-2 text-xs font-bold text-amber-900 underline">Tạo Quỹ ngay →</button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-4 text-white shadow-lg shadow-emerald-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-100">Tổng Thu</p>
                    <p class="text-xl font-black mt-1">{{ formatCurrency(summary.month_income) }}</p>
                </div>
                <div class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-2xl p-4 text-white shadow-lg shadow-rose-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-rose-100">Tổng Chi</p>
                    <p class="text-xl font-black mt-1">{{ formatCurrency(summary.month_expense) }}</p>
                </div>
                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Tồn Tháng Trước</p>
                    <p class="text-xl font-black text-gray-900 mt-1">{{ formatCurrency(summary.opening_balance) }}</p>
                </div>
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-4 text-white shadow-lg shadow-blue-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-200">Tổng Tồn Hiện Tại</p>
                    <p class="text-xl font-black mt-1">{{ formatCurrency(summary.closing_balance) }}</p>
                </div>
            </div>

            <!-- Fund Balances -->
            <div v-if="funds.length > 0" class="flex flex-wrap gap-2">
                <div v-for="fund in funds" :key="fund.id" class="flex items-center gap-2 bg-white border border-gray-100 rounded-xl px-4 py-2.5 shadow-sm">
                    <div class="w-2 h-2 rounded-full" :class="fund.balance >= 0 ? 'bg-emerald-400' : 'bg-rose-400'"></div>
                    <span class="text-xs font-bold text-gray-700">{{ fund.name }}</span>
                    <span class="text-sm font-black" :class="fund.balance >= 0 ? 'text-emerald-700' : 'text-rose-700'">{{ formatCurrency(fund.balance) }}</span>
                </div>
            </div>

            <!-- Meetings List — Click vào để mở ghi tiền -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-900">📅 Danh sách Buổi Nhóm — Bấm vào buổi nhóm để ghi thu/chi</h3>
                    <span class="text-xs text-gray-500">{{ meetings.length }} buổi · Tháng {{ localMonth }}/{{ localYear }}</span>
                </div>

                <!-- Meetings as Clickable Cards -->
                <div class="divide-y divide-gray-50">
                    <div
                        v-for="m in meetings"
                        :key="m.id"
                        @click="canManage && openMeetingForm(m)"
                        class="flex items-center gap-4 px-5 py-4 hover:bg-blue-50/60 cursor-pointer transition-colors group"
                        :class="canManage ? 'cursor-pointer' : 'cursor-default'"
                    >
                        <!-- Date Badge -->
                        <div class="shrink-0 w-14 text-center">
                            <div class="text-[10px] font-bold text-gray-400 uppercase">{{ formatDayOfWeek(m.meeting_date) }}</div>
                            <div class="text-xl font-black text-gray-900 leading-none">{{ formatDay(m.meeting_date) }}</div>
                            <div class="text-[10px] text-gray-500 mt-0.5">Th{{ formatMonth(m.meeting_date) }}</div>
                        </div>

                        <!-- Attendance -->
                        <div class="shrink-0 flex flex-col gap-1">
                            <span class="inline-flex items-center gap-1 text-xs text-gray-600">
                                <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3"/></svg>
                                <span class="font-bold">{{ m.attendance_morning }}</span> sáng
                            </span>
                            <span class="inline-flex items-center gap-1 text-xs text-gray-600">
                                <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21"/></svg>
                                <span class="font-bold">{{ m.attendance_afternoon }}</span> chiều
                            </span>
                        </div>

                        <!-- Note -->
                        <div class="flex-1 min-w-0">
                            <p v-if="m.note" class="text-xs text-gray-500 truncate">{{ m.note }}</p>
                            <p v-else class="text-xs text-gray-300 italic">Không có ghi chú</p>
                            <!-- Transaction preview -->
                            <div v-if="m.transactions && m.transactions.length > 0" class="flex gap-2 mt-1">
                                <span class="text-[10px] bg-emerald-50 text-emerald-700 font-bold px-1.5 py-0.5 rounded">{{ m.transactions.filter(t => t.type==='income').length }} khoản thu</span>
                                <span class="text-[10px] bg-rose-50 text-rose-700 font-bold px-1.5 py-0.5 rounded">{{ m.transactions.filter(t => t.type==='expense').length }} khoản chi</span>
                            </div>
                            <div v-else class="mt-1">
                                <span class="text-[10px] text-gray-300 italic">Chưa ghi tiền</span>
                            </div>
                        </div>

                        <!-- Finance Numbers -->
                        <div class="shrink-0 text-right space-y-0.5">
                            <p v-if="m.session_income > 0" class="text-sm font-bold text-emerald-600">+{{ formatCurrency(m.session_income) }}</p>
                            <p v-if="m.session_expense > 0" class="text-sm font-bold text-rose-600">-{{ formatCurrency(m.session_expense) }}</p>
                            <p v-if="m.session_income === 0 && m.session_expense === 0" class="text-xs text-gray-300">—</p>
                            <p class="text-xs font-black" :class="m.session_balance >= 0 ? 'text-blue-700' : 'text-rose-700'">
                                Tồn: {{ formatCurrency(m.session_balance) }}
                            </p>
                        </div>

                        <!-- Edit Icon -->
                        <div v-if="canManage" class="shrink-0 text-gray-300 group-hover:text-blue-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </div>
                    </div>

                    <!-- Totals Row -->
                    <div v-if="meetings.length > 0" class="flex items-center gap-4 px-5 py-3.5 bg-blue-50 border-t border-blue-100">
                        <div class="shrink-0 w-14">
                            <span class="text-xs font-black text-blue-900">Tổng</span>
                        </div>
                        <div class="flex-1"></div>
                        <div class="shrink-0 text-right">
                            <p class="text-sm font-black text-emerald-700">+{{ formatCurrency(summary.month_income) }}</p>
                            <p class="text-sm font-black text-rose-700">-{{ formatCurrency(summary.month_expense) }}</p>
                        </div>
                        <div class="shrink-0 w-4"></div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="meetings.length === 0" class="px-6 py-12 text-center">
                        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-sm text-gray-500 font-medium">Chưa có buổi nhóm nào trong tháng {{ localMonth }}/{{ localYear }}</p>
                        <button v-if="canManage && funds.length > 0" @click="openMeetingForm()" class="mt-3 text-sm font-bold text-blue-600 hover:underline">+ Thêm buổi nhóm đầu tiên</button>
                    </div>
                </div>
            </div>

            <!-- Monthly Summary Report -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="text-sm font-bold text-blue-900">📊 Báo cáo Tài chính Tháng {{ localMonth }}/{{ localYear }}</h3>
                </div>
                <div class="p-5 space-y-4">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="py-2.5 text-gray-600 font-medium">Tồn quỹ tháng trước chuyển sang</td>
                                <td class="py-2.5 text-right font-bold text-gray-900">{{ formatCurrency(summary.opening_balance) }}</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 text-emerald-700 font-medium">Tổng thu trong tháng ({{ meetings.filter(m => m.session_income > 0).length }} buổi có thu)</td>
                                <td class="py-2.5 text-right font-bold text-emerald-700">+ {{ formatCurrency(summary.month_income) }}</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 text-rose-700 font-medium">Tổng chi trong tháng ({{ meetings.filter(m => m.session_expense > 0).length }} buổi có chi)</td>
                                <td class="py-2.5 text-right font-bold text-rose-700">- {{ formatCurrency(summary.month_expense) }}</td>
                            </tr>
                            <tr class="font-black text-base border-t-2 border-blue-200">
                                <td class="pt-3 pb-1 text-blue-900">Tổng tồn hiện tại (cuối tháng)</td>
                                <td class="pt-3 pb-1 text-right text-blue-900">{{ formatCurrency(summary.closing_balance) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Per-fund balances -->
                    <div v-if="funds.length > 0" class="border-t border-gray-100 pt-4">
                        <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3">Số dư từng quỹ</h4>
                        <div class="space-y-2">
                            <div v-for="fund in funds" :key="fund.id" class="flex justify-between items-center py-1.5">
                                <span class="text-sm text-gray-700 font-medium">{{ fund.name }}</span>
                                <span class="text-sm font-black" :class="fund.balance >= 0 ? 'text-emerald-700' : 'text-rose-700'">{{ formatCurrency(fund.balance) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Per-meeting breakdown -->
                    <div v-if="meetings.length > 0" class="border-t border-gray-100 pt-4">
                        <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3">Chi tiết từng buổi nhóm</h4>
                        <div class="space-y-1">
                            <div v-for="m in meetings" :key="m.id" class="flex items-center justify-between py-1.5 text-xs">
                                <span class="text-gray-600 font-medium">{{ formatDate(m.meeting_date) }}</span>
                                <div class="flex gap-4 text-right">
                                    <span v-if="m.session_income > 0" class="text-emerald-600 font-bold">+{{ formatCurrency(m.session_income) }}</span>
                                    <span v-if="m.session_expense > 0" class="text-rose-600 font-bold">-{{ formatCurrency(m.session_expense) }}</span>
                                    <span v-if="m.session_income === 0 && m.session_expense === 0" class="text-gray-300">Không có giao dịch</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance summary -->
                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3">Thống kê Hiện Diện</h4>
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500">Số buổi</p>
                                <p class="text-lg font-black text-gray-900">{{ summary.meeting_count }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs text-gray-500">TB Hiện Diện</p>
                                <p class="text-lg font-black text-gray-900">{{ summary.avg_attendance }}</p>
                            </div>
                            <div class="rounded-xl p-3" :class="summary.attendance_change >= 0 ? 'bg-emerald-50' : 'bg-rose-50'">
                                <p class="text-xs text-gray-500">So tháng trước</p>
                                <p class="text-lg font-black" :class="summary.attendance_change >= 0 ? 'text-emerald-700' : 'text-rose-700'">
                                    {{ summary.attendance_change >= 0 ? '+' : '' }}{{ summary.attendance_change }}%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- === Meeting SlideOver === -->
        <SlideOver :show="showMeetingForm" @close="closeMeetingForm" :title="meetingFormEditing ? 'Cập nhật Buổi Nhóm · ' + formatDate(meetingFormEditing.meeting_date) : 'Thêm Buổi Nhóm Mới'" :wide="true">
            <form @submit.prevent="submitMeeting" class="space-y-5">
                <!-- Date & Attendance -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-3 sm:col-span-1">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Ngày nhóm <span class="text-red-500">*</span></label>
                        <input type="date" v-model="meetingForm.meeting_date" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">☀ HD Sáng</label>
                        <input type="number" v-model="meetingForm.attendance_morning" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 sm:text-sm" min="0" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">🌙 HD Chiều</label>
                        <input type="number" v-model="meetingForm.attendance_afternoon" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 sm:text-sm" min="0" placeholder="0">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Ghi chú buổi nhóm</label>
                    <textarea v-model="meetingForm.note" rows="2" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 sm:text-sm" placeholder="Vd: Học Lời Chúa, Thông công..."></textarea>
                </div>

                <!-- Transaction Section -->
                <div class="border-t border-gray-100 pt-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <h4 class="text-sm font-bold text-gray-900">Tài chính buổi nhóm</h4>
                        <button type="button" @click="addTransaction" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800 px-3 py-1.5 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Thêm dòng
                        </button>
                    </div>

                    <div v-for="(tx, idx) in meetingForm.transactions" :key="idx" class="bg-gray-50 rounded-xl p-3 space-y-2 border border-gray-100">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Loại</label>
                                <div class="flex gap-1.5">
                                    <button type="button" @click="tx.type = 'income'" class="flex-1 py-1.5 text-xs font-bold rounded-lg transition-colors" :class="tx.type === 'income' ? 'bg-emerald-600 text-white' : 'bg-white border border-gray-200 text-gray-600'">Thu</button>
                                    <button type="button" @click="tx.type = 'expense'" class="flex-1 py-1.5 text-xs font-bold rounded-lg transition-colors" :class="tx.type === 'expense' ? 'bg-rose-600 text-white' : 'bg-white border border-gray-200 text-gray-600'">Chi</button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Quỹ</label>
                                <select v-model="tx.department_fund_id" class="block w-full rounded-lg border-gray-300 text-xs focus:ring-blue-500 py-1.5" required>
                                    <option value="" disabled>Chọn Quỹ</option>
                                    <option v-for="f in funds" :key="f.id" :value="f.id">{{ f.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Số tiền (VNĐ)</label>
                                <input type="number" v-model="tx.amount" min="0" class="block w-full rounded-lg border-gray-300 text-sm font-bold focus:ring-blue-500 py-1.5" placeholder="0">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Phân loại</label>
                                <select v-model="tx.category" class="block w-full rounded-lg border-gray-300 text-xs focus:ring-blue-500 py-1.5">
                                    <option value="">-- Chọn --</option>
                                    <option v-if="tx.type === 'income'" value="Tiền hộp tuần">Tiền hộp tuần</option>
                                    <option v-if="tx.type === 'income'" value="Tiền dâng lạc quyên">Tiền dâng lạc quyên</option>
                                    <option v-if="tx.type === 'income'" value="Tiền phần mười (1/10)">Tiền phần mười (1/10)</option>
                                    <option v-if="tx.type === 'expense'" value="Chi hoạt động">Chi hoạt động</option>
                                    <option v-if="tx.type === 'expense'" value="Thăm viếng">Thăm viếng</option>
                                    <option v-if="tx.type === 'expense'" value="Chi sinh hoạt">Chi sinh hoạt</option>
                                    <option v-if="tx.type === 'expense'" value="Chi bất thường">Chi bất thường</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" v-model="tx.description" class="block flex-1 rounded-lg border-gray-300 text-xs focus:ring-blue-500 py-1.5" placeholder="Ghi chú thêm...">
                            <button type="button" @click="removeTransaction(idx)" class="shrink-0 text-red-400 hover:text-red-600 transition-colors p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <div v-if="meetingForm.transactions.length === 0" class="text-center py-4 border-2 border-dashed border-gray-200 rounded-xl">
                        <p class="text-xs text-gray-400">Chưa có dòng giao dịch nào.</p>
                        <button type="button" @click="addTransaction" class="mt-2 text-xs font-bold text-blue-600 hover:underline">+ Thêm dòng ghi tiền</button>
                    </div>

                    <!-- Live total preview -->
                    <div v-if="meetingForm.transactions.length > 0" class="bg-blue-50 rounded-xl px-4 py-3 border border-blue-100">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-bold text-blue-900">Tổng Thu buổi này:</span>
                            <span class="font-black text-emerald-700">+ {{ formatCurrency(liveTotalIncome) }}</span>
                        </div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-bold text-blue-900">Tổng Chi buổi này:</span>
                            <span class="font-black text-rose-700">- {{ formatCurrency(liveTotalExpense) }}</span>
                        </div>
                        <div class="flex justify-between text-sm border-t border-blue-200 pt-2 mt-2">
                            <span class="font-black text-blue-900">Tồn buổi nhóm:</span>
                            <span class="font-black" :class="liveBalance >= 0 ? 'text-emerald-700' : 'text-rose-700'">
                                {{ formatCurrency(liveBalance) }}
                            </span>
                        </div>
                    </div>
                </div>
            </form>

            <template #footer>
                <div class="flex justify-between items-center w-full">
                    <button v-if="meetingFormEditing" type="button" @click="deleteMeeting" class="text-red-600 text-sm font-medium flex items-center gap-1 hover:text-red-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Xóa Buổi Nhóm
                    </button>
                    <div v-else></div>
                    <div class="flex gap-2">
                        <button type="button" @click="closeMeetingForm" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Hủy</button>
                        <button type="button" @click="submitMeeting" :disabled="meetingFormLoading" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl disabled:opacity-50 transition-colors">
                            {{ meetingFormLoading ? 'Đang lưu...' : (meetingFormEditing ? 'Cập nhật' : 'Lưu Buổi Nhóm') }}
                        </button>
                    </div>
                </div>
            </template>
        </SlideOver>

        <!-- === Fund Form SlideOver === -->
        <SlideOver :show="showFundForm" @close="showFundForm = false" title="Quản lý Quỹ">
            <div class="space-y-5">
                <!-- Existing funds list -->
                <div v-if="funds.length > 0" class="space-y-2">
                    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Quỹ hiện có</h4>
                    <div v-for="fund in funds" :key="fund.id" class="flex justify-between items-center py-2.5 px-3 bg-gray-50 rounded-xl border border-gray-100">
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ fund.name }}</p>
                            <p class="text-xs text-gray-500">Số dư: {{ formatCurrency(fund.balance) }}</p>
                        </div>
                        <button @click="deleteFund(fund)" class="text-xs text-red-500 hover:text-red-700 font-bold">Xóa</button>
                    </div>
                </div>

                <!-- New fund form -->
                <div class="border-t border-gray-100 pt-4 space-y-3">
                    <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wider">Tạo Quỹ Mới</h4>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Tên Quỹ <span class="text-red-500">*</span></label>
                        <input type="text" v-model="fundForm.name" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 sm:text-sm" placeholder="VD: Quỹ thường xuyên">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Mô tả (tùy chọn)</label>
                        <input type="text" v-model="fundForm.description" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-blue-500 sm:text-sm" placeholder="VD: Quỹ sinh hoạt hàng tuần">
                    </div>
                    <button type="button" @click="submitFund" :disabled="!fundForm.name" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl disabled:opacity-50">Tạo Quỹ</button>
                </div>
            </div>
        </SlideOver>

        <!-- Dept Switcher SlideOver -->
        <SlideOver :show="isSwitchOpen" @close="isSwitchOpen = false" title="Chuyển Ban Ngành">
            <div class="space-y-2">
                <div v-for="dept in availableDepartments" :key="dept.id" @click="switchDept(dept.id)"
                    class="w-full text-left p-4 rounded-xl border-2 transition-all cursor-pointer"
                    :class="department?.id === dept.id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-white hover:border-gray-300'">
                    <h4 class="text-sm font-bold" :class="department?.id === dept.id ? 'text-blue-900' : 'text-gray-900'">{{ dept.name }}</h4>
                    <span v-if="department?.id === dept.id" class="text-xs text-blue-600 font-bold">Đang hoạt động</span>
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
const formatDate = (d) => d ? new Date(d + 'T00:00:00').toLocaleDateString('vi-VN', { weekday: 'short', day: '2-digit', month: '2-digit' }) : '';
const formatDayOfWeek = (d) => d ? new Date(d + 'T00:00:00').toLocaleDateString('vi-VN', { weekday: 'short' }) : '';
const formatDay = (d) => d ? new Date(d + 'T00:00:00').getDate() : '';
const formatMonth = (d) => d ? (new Date(d + 'T00:00:00').getMonth() + 1) : '';

// ============== MEETING FORM ==============
const showMeetingForm   = ref(false);
const meetingFormEditing = ref(null);
const meetingFormLoading = ref(false);

const makeBlankTx = () => ({
    department_fund_id: props.funds.length > 0 ? props.funds[0].id : '',
    type: 'income', amount: 0, category: '', description: '',
});

const meetingForm = ref({
    meeting_date: new Date().toISOString().split('T')[0],
    attendance_morning: 0,
    attendance_afternoon: 0,
    note: '',
    transactions: [],
});

const liveTotalIncome = computed(() =>
    meetingForm.value.transactions.reduce((acc, tx) => tx.type === 'income' ? acc + (parseInt(tx.amount) || 0) : acc, 0)
);
const liveTotalExpense = computed(() =>
    meetingForm.value.transactions.reduce((acc, tx) => tx.type === 'expense' ? acc + (parseInt(tx.amount) || 0) : acc, 0)
);
const liveBalance = computed(() => liveTotalIncome.value - liveTotalExpense.value);

const openMeetingForm = (meeting = null) => {
    if (meeting) {
        meetingFormEditing.value = meeting;
        meetingForm.value = {
            meeting_date: meeting.meeting_date,
            attendance_morning: meeting.attendance_morning,
            attendance_afternoon: meeting.attendance_afternoon,
            note: meeting.note || '',
            transactions: (meeting.transactions || []).map(tx => ({
                department_fund_id: tx.department_fund_id,
                type: tx.type,
                amount: tx.amount,
                category: tx.category || '',
                description: tx.description || '',
            })),
        };
    } else {
        meetingFormEditing.value = null;
        meetingForm.value = {
            meeting_date: new Date().toISOString().split('T')[0],
            attendance_morning: 0,
            attendance_afternoon: 0,
            note: '',
            transactions: props.funds.length > 0 ? [makeBlankTx()] : [],
        };
    }
    showMeetingForm.value = true;
};

const closeMeetingForm = () => {
    showMeetingForm.value = false;
    meetingFormEditing.value = null;
};

const addTransaction    = () => meetingForm.value.transactions.push(makeBlankTx());
const removeTransaction = (idx) => meetingForm.value.transactions.splice(idx, 1);

const submitMeeting = () => {
    meetingFormLoading.value = true;
    const payload = meetingForm.value;
    if (meetingFormEditing.value) {
        router.put(route('portal.finance.meetings.update', meetingFormEditing.value.id), payload, {
            preserveScroll: true,
            onSuccess: () => closeMeetingForm(),
            onFinish: () => meetingFormLoading.value = false,
        });
    } else {
        router.post(route('portal.finance.meetings.store'), payload, {
            preserveScroll: true,
            onSuccess: () => closeMeetingForm(),
            onFinish: () => meetingFormLoading.value = false,
        });
    }
};

const deleteMeeting = () => {
    if (!meetingFormEditing.value) return;
    if (confirm('Bạn có chắc muốn xóa buổi nhóm này? Tất cả giao dịch liên kết cũng sẽ bị xóa.')) {
        router.delete(route('portal.finance.meetings.destroy', meetingFormEditing.value.id), {
            preserveScroll: true,
            onSuccess: () => closeMeetingForm(),
        });
    }
};

// ============== FUND FORM ==============
const showFundForm = ref(false);
const fundForm = ref({ name: '', description: '' });

const submitFund = () => {
    if (!fundForm.value.name) return;
    router.post(route('portal.finance.funds.store'), fundForm.value, {
        preserveScroll: true,
        onSuccess: () => { fundForm.value = { name: '', description: '' }; },
    });
};

const deleteFund = (fund) => {
    if (fund.balance !== 0) { alert('Không thể xóa quỹ còn số dư. Vui lòng chuyển hết số dư trước.'); return; }
    if (confirm(`Xóa quỹ "${fund.name}"?`)) {
        router.delete(route('portal.finance.funds.destroy', fund.id), { preserveScroll: true });
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
