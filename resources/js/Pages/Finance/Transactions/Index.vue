<template>
    <PortalLayout :department="department" :availableDepartments="[]" :isGlobalAdmin="isGlobalAdmin" portalType="finance" @open-switcher="">
        <Head title="Sổ Cầm Quỹ" />
        
        <div class="px-4 py-6 sm:px-6 lg:px-8 w-full space-y-6">
            <!-- Header & Toolbar -->
            <div class="flex flex-col md:flex-row md:items-end justify-between space-y-4 md:space-y-0">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Sổ Cầm Quỹ</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        <span v-if="department">Xem thu chi của: <strong>{{ department.name }}</strong></span>
                        <span v-else>Quản lý đa quỹ hội thánh</span>
                    </p>
                </div>
                
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Period Filter -->
                    <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
                        <select v-model="localFilters.month" @change="updateFilters" class="text-sm font-medium text-gray-700 border-none focus:ring-0 p-0 pr-1">
                            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
                        </select>
                        <input v-model="localFilters.year" @change="updateFilters" type="number" class="w-20 text-sm font-medium text-gray-700 border-none focus:ring-0 p-0 text-center" min="2020" max="2099">
                    </div>
                    <!-- Fund filter -->
                    <select v-model="localFilters.fund_id" @change="updateFilters" class="text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tất cả Quỹ</option>
                        <option v-for="f in funds" :key="f.id" :value="f.id">{{ f.name }}</option>
                    </select>

                    <button v-if="canManage" @click="openTransferForm" class="shrink-0 inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-bold text-indigo-700 shadow-sm hover:bg-indigo-100 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        Chuyển Quỹ
                    </button>
                    <button @click="openForm('income')" class="shrink-0 inline-flex items-center justify-center rounded-xl border border-transparent bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Phiếu Thu
                    </button>
                    <button @click="openForm('expense')" class="shrink-0 inline-flex items-center justify-center rounded-xl border border-transparent bg-rose-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-rose-700 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        Phiếu Chi
                    </button>
                </div>
            </div>

            <!-- Summary Bar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-100">
                    <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Tổng Thu (Kì này)</p>
                    <p class="text-xl font-black text-emerald-600 mt-1">{{ formatCurrency(summary.periodIncome) }}</p>
                </div>
                <div class="bg-rose-50 rounded-2xl p-4 border border-rose-100">
                    <p class="text-xs font-bold text-rose-800 uppercase tracking-wider">Tổng Chi (Kì này)</p>
                    <p class="text-xl font-black text-rose-600 mt-1">{{ formatCurrency(summary.periodExpense) }}</p>
                </div>
                <div class="bg-blue-50 rounded-2xl p-4 border border-blue-100">
                    <p class="text-xs font-bold text-blue-800 uppercase tracking-wider">Tồn Quỹ Hiện Tại</p>
                    <p class="text-xl font-black text-blue-900 mt-1">{{ formatCurrency(summary.currentBalance) }}</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200">
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wider">Chênh lệch kì này</p>
                    <p class="text-xl font-black mt-1" :class="(summary.periodIncome - summary.periodExpense) >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                        {{ formatCurrency(summary.periodIncome - summary.periodExpense) }}
                    </p>
                </div>
            </div>

            <!-- Fund Balances -->
            <div v-if="funds.length > 1" class="flex flex-wrap gap-3">
                <div v-for="fund in funds" :key="fund.id" class="flex items-center bg-white border border-gray-100 rounded-xl px-4 py-2 shadow-sm gap-3">
                    <div class="w-2.5 h-2.5 rounded-full" :class="fund.balance >= 0 ? 'bg-emerald-400' : 'bg-rose-400'"></div>
                    <div>
                        <span class="text-xs font-bold text-gray-700">{{ fund.name }}</span>
                        <span class="ml-2 text-sm font-black" :class="fund.balance >= 0 ? 'text-emerald-700' : 'text-rose-700'">{{ formatCurrency(fund.balance) }}</span>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-28">Ngày</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nội dung</th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Hiện diện</th>
                                <th class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thu (+)</th>
                                <th class="px-5 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Chi (-)</th>
                                <th class="px-5 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-5 py-3.5 w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <template v-for="tx in transactions.data" :key="tx.id">
                                <tr class="hover:bg-gray-50 transition-colors group" :class="{'bg-amber-50/30': tx.status === 'pending'}">
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                        {{ formatDate(tx.transaction_date) }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ tx.description || 'Không có mô tả' }}</div>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            <span v-if="tx.category" class="text-xs font-medium px-2 py-0.5 rounded-full" :class="tx.type === 'income' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">{{ tx.category }}</span>
                                            <span v-if="tx.fund" class="text-xs text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full font-medium">{{ tx.fund.name }}</span>
                                        </div>
                                        <!-- Tithe breakdown if exists -->
                                        <div v-if="tx.contributions && tx.contributions.length > 0" class="mt-1.5 flex flex-wrap gap-1">
                                            <span v-for="c in tx.contributions" :key="c.id" class="text-[10px] bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded font-bold">
                                                {{ c.member_group }}: {{ c.people_count }} người
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap hidden lg:table-cell">
                                        <span v-if="tx.session_metric" class="inline-flex items-center space-x-1 bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full text-xs font-bold">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                            <span>{{ tx.session_metric.attendance_count }}</span>
                                        </span>
                                        <span v-else class="text-gray-300 text-xs">—</span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right font-bold text-sm">
                                        <span v-if="tx.type === 'income'" class="text-emerald-600">+ {{ formatCurrency(tx.amount) }}</span>
                                        <span v-else class="text-gray-300">—</span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right font-bold text-sm">
                                        <span v-if="tx.type === 'expense'" class="text-rose-600">- {{ formatCurrency(tx.amount) }}</span>
                                        <span v-else class="text-gray-300">—</span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-center">
                                        <button v-if="tx.status === 'pending' && canApprove" @click="approveTransaction(tx)" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 hover:bg-amber-200 transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Duyệt?
                                        </button>
                                        <span v-else-if="tx.status === 'pending'" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Chờ duyệt</span>
                                        <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Đã duyệt
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        <button @click="openForm(tx.type, tx)" class="text-gray-400 hover:text-indigo-600 opacity-0 group-hover:opacity-100 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            
                            <!-- Fund Transfers Section -->
                            <tr v-if="transfers.length > 0" class="bg-indigo-50/50">
                                <td colspan="7" class="px-5 py-2">
                                    <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Lệnh Chuyển Quỹ Trong Kì</span>
                                </td>
                            </tr>
                            <tr v-for="tr in transfers" :key="'tr-' + tr.id" class="bg-indigo-50/30 hover:bg-indigo-50 transition-colors">
                                <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-600 font-medium">{{ formatDate(tr.transfer_date) }}</td>
                                <td class="px-5 py-3" colspan="2">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                                        <span class="text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded font-bold text-xs">{{ tr.from_fund?.name }}</span>
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                        <span class="text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded font-bold text-xs">{{ tr.to_fund?.name }}</span>
                                    </div>
                                    <div v-if="tr.note" class="text-xs text-gray-500 mt-1">{{ tr.note }}</div>
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-sm text-indigo-600" colspan="2">{{ formatCurrency(tr.amount) }}</td>
                                <td class="px-5 py-3 text-center">
                                    <span v-if="tr.status === 'approved'" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Đã duyệt</span>
                                    <span v-else class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Chờ duyệt</span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button v-if="tr.status === 'pending' && canManage" @click="deleteTransfer(tr)" class="text-red-400 hover:text-red-600 text-xs font-medium">Xóa</button>
                                </td>
                            </tr>

                            <tr v-if="transactions.data.length === 0 && transfers.length === 0">
                                <td colspan="7" class="px-6 py-14 text-center">
                                    <div class="w-14 h-14 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-sm text-gray-500 font-medium">Không có giao dịch nào trong khoảng thời gian này.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="md:hidden divide-y divide-gray-100">
                    <div v-for="tx in transactions.data" :key="tx.id" class="p-4 hover:bg-gray-50 transition-colors" @click="openForm(tx.type, tx)" :class="{'bg-amber-50/30': tx.status === 'pending'}">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900">{{ tx.description || 'Không mô tả' }}</span>
                                <span class="text-xs text-gray-500 mt-0.5">{{ formatDate(tx.transaction_date) }} · {{ tx.category }}</span>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                <span class="text-base font-black" :class="tx.type === 'income' ? 'text-emerald-600' : 'text-rose-600'">
                                    {{ tx.type === 'income' ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                                </span>
                                <span v-if="tx.status === 'approved'" class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-800">Đã duyệt</span>
                                <span v-else class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-800">Chờ duyệt</span>
                            </div>
                        </div>
                        <div v-if="tx.contributions && tx.contributions.length > 0" class="mt-1 flex flex-wrap gap-1">
                            <span v-for="c in tx.contributions" :key="c.id" class="text-[10px] bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded font-bold">{{ c.member_group }}: {{ c.people_count }}ng</span>
                        </div>
                    </div>
                    <div v-if="transactions.data.length === 0" class="p-8 text-center text-sm text-gray-500">Không có giao dịch nào.</div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="transactions.links && transactions.data.length > 0" class="flex justify-between items-center">
                <p class="text-xs text-gray-500">Trang {{ transactions.current_page }} / {{ transactions.last_page }}</p>
                <div class="flex gap-1">
                    <template v-for="(link, k) in transactions.links" :key="k">
                        <Link v-if="link.url" :href="link.url" class="px-3 py-1.5 border rounded-lg text-xs font-medium transition-colors" :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'" v-html="link.label"></Link>
                        <span v-else class="px-3 py-1.5 rounded-lg text-xs text-gray-400 cursor-not-allowed" v-html="link.label"></span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Transaction Form SlideOver -->
        <SlideOver :show="showForm" @close="closeForm" :title="formTitle" :wide="true">
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Type toggle -->
                <div class="flex gap-2 p-1 bg-gray-100 rounded-xl">
                    <button type="button" @click="form.type = 'income'" class="flex-1 py-2 text-sm font-bold rounded-lg transition-colors" :class="form.type === 'income' ? 'bg-emerald-600 text-white shadow' : 'text-gray-500 hover:text-gray-700'">
                        <span>📥</span> Thu Tiền
                    </button>
                    <button type="button" @click="form.type = 'expense'" class="flex-1 py-2 text-sm font-bold rounded-lg transition-colors" :class="form.type === 'expense' ? 'bg-rose-600 text-white shadow' : 'text-gray-500 hover:text-gray-700'">
                        <span>📤</span> Chi Tiền
                    </button>
                </div>

                <!-- Date & Fund -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Ngày giao dịch <span class="text-red-500">*</span></label>
                        <input type="date" v-model="form.transaction_date" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 sm:text-sm" required>
                        <p v-if="form.errors.transaction_date" class="text-xs text-red-600 mt-1">{{ form.errors.transaction_date }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Quỹ <span class="text-red-500">*</span></label>
                        <select v-model="form.fund_id" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 sm:text-sm" required :disabled="isEditing">
                            <option value="" disabled>Chọn Quỹ</option>
                            <option v-for="f in funds" :key="f.id" :value="f.id">{{ f.name }}</option>
                        </select>
                        <p v-if="form.errors.fund_id" class="text-xs text-red-600 mt-1">{{ form.errors.fund_id }}</p>
                    </div>
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Số tiền (VNĐ) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <span class="font-bold text-lg" :class="form.type === 'income' ? 'text-emerald-600' : 'text-rose-600'">{{ form.type === 'income' ? '+' : '-' }}</span>
                        </div>
                        <input type="number" v-model="form.amount" class="block w-full rounded-xl border-gray-300 pl-8 pr-12 sm:text-sm font-bold text-lg focus:ring-2 focus:ring-blue-500" placeholder="0" min="0" required>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-gray-400 sm:text-sm">VND</span>
                        </div>
                    </div>
                    <p v-if="form.errors.amount" class="text-xs text-red-600 mt-1">{{ form.errors.amount }}</p>
                </div>

                <!-- Description & Category -->
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nội dung / Mô tả</label>
                        <input type="text" v-model="form.description" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 sm:text-sm" placeholder="VD: Dâng hiến Chủ nhật 02/03">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Phân loại</label>
                        <select v-model="form.category" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 sm:text-sm">
                            <option value="">-- Chọn phân loại --</option>
                            <template v-if="form.type === 'income'">
                                <option>Tiền hộp</option>
                                <option>Tiền phần mười (1/10)</option>
                                <option>Tiền lạc quyên</option>
                                <option>Dâng mua sắm</option>
                                <option>Tiền dâng đặc biệt</option>
                            </template>
                            <template v-else>
                                <option>Chi thường kỳ</option>
                                <option>Chi bất thường</option>
                                <option>Chi sinh hoạt</option>
                                <option>Chi mua sắm</option>
                                <option>Chi công tác xã hội</option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Tithe Breakdown (only when category = tiền phần mười) -->
                <div v-if="form.type === 'income' && form.category === 'Tiền phần mười (1/10)'" class="bg-indigo-50 rounded-2xl p-4 border border-indigo-100 space-y-3">
                    <h4 class="text-sm font-bold text-indigo-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Chi tiết Phần Mười theo Ban ngành
                    </h4>
                    <div v-for="(contrib, idx) in form.contributions" :key="idx" class="grid grid-cols-3 gap-2 items-center">
                        <span class="text-xs font-bold text-indigo-800">{{ contrib.member_group }}</span>
                        <div class="relative">
                            <input type="number" v-model="contrib.people_count" min="0" placeholder="Số người" class="block w-full text-sm rounded-lg border-indigo-200 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 px-2">
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">người</span>
                        </div>
                        <div class="relative">
                            <input type="number" v-model="contrib.amount" min="0" placeholder="Số tiền" class="block w-full text-sm rounded-lg border-indigo-200 focus:ring-indigo-500 focus:border-indigo-500 py-1.5 px-2">
                        </div>
                    </div>
                    <p class="text-xs text-indigo-600">💡 Nhập số người và số tiền dâng phần mười theo từng nhóm ban ngành.</p>
                </div>

                <!-- Session Metrics -->
                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200 space-y-3">
                    <h4 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Sỉ số buổi nhóm
                        <span class="text-[10px] font-normal text-gray-500 bg-gray-200 px-2 py-0.5 rounded-full">(Tùy chọn)</span>
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Hiện diện</label>
                            <input type="number" v-model="form.attendance_count" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="0" min="0">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Người dâng 1/10</label>
                            <input type="number" v-model="form.tithe_count" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="0" min="0">
                        </div>
                    </div>
                </div>
            </form>

            <template #footer>
                <div class="flex justify-between items-center w-full">
                    <div>
                        <button v-if="isEditing" type="button" @click="confirmDelete" class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Xóa phiếu
                        </button>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="closeForm" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Hủy</button>
                        <button type="button" @click="submit" :disabled="form.processing" class="px-6 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white transition-all disabled:opacity-50" :class="form.type === 'income' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'">
                            {{ form.processing ? 'Đang lưu...' : (isEditing ? 'Cập nhật' : `Lập ${form.type === 'income' ? 'Phiếu Thu' : 'Phiếu Chi'}`) }}
                        </button>
                    </div>
                </div>
            </template>
        </SlideOver>

        <!-- Fund Transfer SlideOver -->
        <SlideOver :show="showTransferForm" @close="showTransferForm = false" title="Lệnh Chuyển Quỹ">
            <form @submit.prevent="submitTransfer" class="space-y-5">
                <p class="text-sm text-gray-500">Chuyển tiền giữa các quỹ nội bộ. Lệnh chuyển sẽ được lưu và cần duyệt.</p>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Quỹ nguồn (Chuyển từ) <span class="text-red-500">*</span></label>
                    <select v-model="transferForm.from_fund_id" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 sm:text-sm" required>
                        <option value="" disabled>Chọn Quỹ</option>
                        <option v-for="f in funds" :key="f.id" :value="f.id">{{ f.name }} (Tồn: {{ formatCurrency(f.balance) }})</option>
                    </select>
                    <p v-if="transferForm.errors.from_fund_id" class="text-xs text-red-600 mt-1">{{ transferForm.errors.from_fund_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Quỹ đích (Chuyển đến) <span class="text-red-500">*</span></label>
                    <select v-model="transferForm.to_fund_id" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 sm:text-sm" required>
                        <option value="" disabled>Chọn Quỹ</option>
                        <option v-for="f in funds" :key="f.id" :value="f.id" :disabled="f.id === transferForm.from_fund_id">{{ f.name }}</option>
                    </select>
                    <p v-if="transferForm.errors.to_fund_id" class="text-xs text-red-600 mt-1">{{ transferForm.errors.to_fund_id }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Ngày chuyển <span class="text-red-500">*</span></label>
                        <input type="date" v-model="transferForm.transfer_date" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Số tiền (VNĐ) <span class="text-red-500">*</span></label>
                        <input type="number" v-model="transferForm.amount" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 sm:text-sm font-bold" placeholder="0" min="1" required>
                        <p v-if="transferForm.errors.amount" class="text-xs text-red-600 mt-1">{{ transferForm.errors.amount }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Ghi chú</label>
                    <textarea v-model="transferForm.note" rows="2" class="block w-full rounded-xl border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 sm:text-sm" placeholder="Lý do chuyển quỹ..."></textarea>
                </div>
            </form>

            <template #footer>
                <div class="flex gap-3 w-full justify-end">
                    <button type="button" @click="showTransferForm = false" class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button type="button" @click="submitTransfer" :disabled="transferForm.processing" class="px-6 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
                        {{ transferForm.processing ? 'Đang lưu...' : 'Tạo Lệnh Chuyển' }}
                    </button>
                </div>
            </template>
        </SlideOver>

    </PortalLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';

const MEMBER_GROUPS = ['Ban Chấp sự', 'Trung lão', 'Thanh tráng', 'Thanh niên', 'Thiếu nhi'];

const props = defineProps({
    transactions: Object,
    transfers: Array,
    funds: Array,
    filters: Object,
    summary: Object,
    department: Object,
    isGlobalAdmin: Boolean,
    canManage: Boolean,
    canApprove: Boolean,
});

const localFilters = ref({
    month: props.filters.month || new Date().getMonth() + 1,
    year: props.filters.year || new Date().getFullYear(),
    fund_id: props.filters.fund_id || '',
});

const updateFilters = () => {
    router.get(route('finance.transactions.index'), localFilters.value, {
        preserveState: true, preserveScroll: true, replace: true,
    });
};

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
const formatDate = (d) => d ? new Date(d).toLocaleDateString('vi-VN') : '';

// Transaction Form
const showForm = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const makeContributions = () => MEMBER_GROUPS.map(g => ({ member_group: g, people_count: 0, amount: 0 }));

const form = useForm({
    type: 'income',
    fund_id: props.funds.length > 0 ? props.funds[0].id : '',
    amount: null,
    transaction_date: new Date().toISOString().split('T')[0],
    category: '',
    description: '',
    attendance_count: null,
    tithe_count: null,
    metrics_notes: '',
    contributions: makeContributions(),
});

// Reset contributions when category changes
watch(() => form.category, (newCat) => {
    if (newCat !== 'Tiền phần mười (1/10)') {
        form.contributions = makeContributions();
    }
});

const formTitle = computed(() => {
    const typeLabel = form.type === 'income' ? 'Phiếu Thu' : 'Phiếu Chi';
    return `${isEditing.value ? 'Cập nhật' : 'Lập'} ${typeLabel}`;
});

const openForm = (type, editingTx = null) => {
    form.reset();
    form.clearErrors();
    form.type = type;
    form.contributions = makeContributions();

    if (editingTx) {
        isEditing.value = true;
        editingId.value = editingTx.id;
        form.fund_id = editingTx.fund_id;
        form.amount = editingTx.amount;
        form.transaction_date = editingTx.transaction_date?.split('T')[0] || editingTx.transaction_date;
        form.category = editingTx.category || '';
        form.description = editingTx.description || '';
        if (editingTx.session_metric) {
            form.attendance_count = editingTx.session_metric.attendance_count;
            form.tithe_count = editingTx.session_metric.tithe_count;
        }
        if (editingTx.contributions && editingTx.contributions.length > 0) {
            // Merge existing contributions into form
            form.contributions = MEMBER_GROUPS.map(g => {
                const existing = editingTx.contributions.find(c => c.member_group === g);
                return { member_group: g, people_count: existing?.people_count || 0, amount: existing?.amount || 0 };
            });
        }
    } else {
        isEditing.value = false;
        editingId.value = null;
        form.fund_id = props.funds.length > 0 ? props.funds[0].id : '';
        form.transaction_date = new Date().toISOString().split('T')[0];
    }
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    setTimeout(() => { form.reset(); form.contributions = makeContributions(); }, 300);
};

const submit = () => {
    const options = { preserveScroll: true, onSuccess: () => closeForm() };
    if (isEditing.value) {
        form.put(route('finance.transactions.update', editingId.value), options);
    } else {
        form.post(route('finance.transactions.store'), options);
    }
};

const confirmDelete = () => {
    if (confirm('Bạn có chắc chắn muốn xóa giao dịch này?')) {
        router.delete(route('finance.transactions.destroy', editingId.value), {
            preserveScroll: true,
            onSuccess: () => closeForm(),
        });
    }
};

const approveTransaction = (tx) => {
    router.post(route('finance.transactions.approve', tx.id), { status: 'approved' }, { preserveScroll: true });
};

// Fund Transfer Form
const showTransferForm = ref(false);
const transferForm = useForm({
    from_fund_id: '',
    to_fund_id: '',
    amount: null,
    transfer_date: new Date().toISOString().split('T')[0],
    note: '',
});

const openTransferForm = () => {
    transferForm.reset();
    showTransferForm.value = true;
};

const submitTransfer = () => {
    transferForm.post(route('finance.transfers.store'), {
        preserveScroll: true,
        onSuccess: () => { showTransferForm.value = false; },
    });
};

const deleteTransfer = (tr) => {
    if (confirm('Xóa lệnh chuyển quỹ này?')) {
        router.delete(route('finance.transfers.destroy', tr.id), { preserveScroll: true });
    }
};
</script>