<template>
    <PortalLayout :department="department" :available-departments="[department]" :is-global-admin="isGlobalAdmin" :portalType="portalType || 'activities'">
        <template #header>
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-xl text-gray-800 leading-tight">Thăm Viếng {{ department?.name || 'Hội Thánh' }}</h2>
                    <!-- Tooltip Helper -->
                    <div class="relative group cursor-help focus:outline-none" tabindex="0">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-amber-500 group-focus:text-amber-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="absolute top-full left-0 sm:left-1/2 sm:-translate-x-1/2 mt-2 w-64 p-3 bg-gray-900 text-white text-[11px] font-medium rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible group-focus:opacity-100 group-focus:visible transition-all z-20 shadow-xl pointer-events-none">
                            Nhấn "Lên Kế Hoạch" để lên lịch đi thăm tín hữu. Dùng "Khẩn Cấp" cho các trường hợp đặc biệt không thể báo trước. Ban điều hành sẽ xem "Đề Xuất" do phần mềm gợi ý dựa vào dữ liệu vắng nhóm.
                            <div class="absolute bottom-full left-4 sm:left-1/2 sm:-translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-500">Quản lý và cập nhật báo cáo các chuyến đi thăm viếng chăm sóc tín hữu.</p>
            </div>
        </template>

        <div class="w-full space-y-6">
            <!-- Data Toolbar -->
            <DataToolbar
                v-model:search="search"
                :viewMode="viewMode"
                @update:viewMode="viewMode = $event"
                placeholder="Tìm tín hữu được thăm..."
                storageKey="visitation_view_mode"
            >
                <template #filters>
                    <div class="flex flex-wrap gap-2 items-center">
                        <!-- Department Filter (Ministry only) -->
                        <select v-if="portalType === 'ministry' && activityDepartments?.length" v-model="filtersForm.filter_dept" @change="updateFilters" class="rounded-lg border-gray-200 text-sm font-medium py-1.5 pl-3 pr-8 text-gray-700 bg-white hover:bg-gray-50 focus:ring-amber-500 focus:border-amber-500 transition-colors shadow-sm">
                            <option value="">Tất cả Ban Sinh Hoạt</option>
                            <option v-for="d in activityDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            <option value="other">Khác (không thuộc ban)</option>
                        </select>
                        <div v-if="portalType === 'ministry'" class="w-px h-6 bg-gray-200 mx-1"></div>
                        <!-- Reason Filter -->
                        <select v-model="filtersForm.reason" @change="updateFilters" class="rounded-lg border-gray-200 text-sm font-medium py-1.5 pl-3 pr-8 text-gray-700 bg-white hover:bg-gray-50 focus:ring-amber-500 focus:border-amber-500 transition-colors shadow-sm capitalize">
                            <option value="">Tất cả lý do</option>
                            <option v-for="r in reasons" :key="r" :value="r">{{ r }}</option>
                        </select>
                        <div class="w-px h-6 bg-gray-200 mx-1"></div>
                        <!-- Period Filters dropdown -->
                        <select v-model="filtersForm.period" @change="updateFilters" class="rounded-lg border-gray-200 text-sm font-medium py-1.5 pl-3 pr-8 text-gray-700 bg-white hover:bg-gray-50 focus:ring-amber-500 focus:border-amber-500 transition-colors shadow-sm">
                            <option value="">Tất cả thời gian</option>
                            <option value="1m">1 tháng gần đây</option>
                            <option value="3m">3 tháng gần đây</option>
                            <option value="6m">6 tháng gần đây</option>
                            <option value="1y">1 năm gần đây</option>
                        </select>
                        <button v-if="filtersForm.period || filtersForm.reason || filtersForm.filter_dept || search" @change="updateFilters" @click="clearFilters" class="px-3 py-1.5 text-gray-400 hover:text-red-600 text-sm font-medium transition-colors">Xóa lọc</button>
                    </div>
                </template>

                <template #actions>
                    <!-- Desktop only buttons -->
                    <div class="hidden sm:flex items-center gap-2">
                        <!-- Suggestions Button -->
                        <button v-if="isGlobalAdmin || portalType === 'ministry'" @click="isSuggOpen = true"
                            class="relative font-bold px-3 py-2 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl shadow-sm hover:bg-amber-100 transition-colors flex items-center text-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Đề Xuất Thăm Viếng
                            <span v-if="suggCount > 0" class="absolute -top-1.5 -right-1.5 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-black leading-none text-white bg-red-500 rounded-full min-w-[18px]">{{ suggCount }}</span>
                        </button>
                        <button v-if="canManage" @click="openEmergencyForm" class="font-bold px-3 py-2 bg-red-50 text-red-600 border border-red-200 rounded-xl shadow-sm hover:bg-red-100 transition-colors flex items-center text-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Khẩn Cấp
                        </button>
                        <button v-if="canManage" @click="openForm" class="font-bold px-4 py-2 bg-gradient-to-r from-amber-600 to-amber-500 text-white rounded-xl shadow-sm hover:from-amber-700 hover:to-amber-600 transition-all flex items-center text-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Lên Kế Hoạch
                        </button>
                    </div>
                </template>
            </DataToolbar>

            <!-- Suggestions inline section removed, now in slide-over -->

            <!-- Main list -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div v-if="visitations.data.length === 0" class="py-16 text-center">
                    <div class="w-16 h-16 mx-auto bg-amber-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-700 mb-1">Chưa có dữ liệu</h3>
                    <p class="text-sm text-gray-400">Chưa có chường thăm viếng nào được báo cáo.</p>
                </div>

                <!-- Desktop Table -->
                <div v-else-if="viewMode === 'list'" class="hidden md:block overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-50 border-b border-gray-100">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 text-left text-[13px] font-bold text-slate-800">Ngày</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-[13px] font-bold text-slate-800">Tín hữu ĐT</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-[13px] font-bold text-slate-800">Lý do</th>
                                <th scope="col" class="px-5 py-3.5 text-left text-[13px] font-bold text-slate-800">Đoàn đi thăm</th>
                                <th scope="col" class="px-5 py-3.5 text-center text-[13px] font-bold text-slate-800">Trạng thái</th>
                                <th scope="col" class="px-5 py-3.5 text-center text-[13px] font-bold text-slate-800">Tác vụ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="visitation in visitations.data" :key="visitation.id"
                                :class="{
                                    'bg-green-50 hover:bg-green-100/60': visitation.status === 'completed',
                                    'bg-red-50 hover:bg-red-100/60': visitation.priority === 'high' && visitation.status !== 'completed',
                                    'bg-gray-50/50 hover:bg-gray-100/60': visitation.status === 'cancelled',
                                    'bg-white hover:bg-amber-50/40': visitation.status === 'planned' && visitation.priority !== 'high',
                                }"
                                class="transition-colors group">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-bold text-gray-800 text-[15px]">{{ formatDate(visitation.visit_date) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-gray-900 text-[15px]">{{ visitation.member?.full_name }}</div>
                                    <div class="text-[13px] text-gray-500 mt-0.5">{{ visitation.member?.phone || '—' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[13px] font-bold capitalize"
                                        :class="{
                                            'bg-red-100 text-red-800': visitation.priority === 'high',
                                            'bg-amber-100 text-amber-800': visitation.priority !== 'high'
                                        }">
                                        {{ visitation.reason }}
                                    </span>
                                    <span v-if="visitation.priority === 'high'" class="ml-1 text-[11px] font-bold text-red-600">⚠️ Khẩn</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex -space-x-2">
                                        <div v-for="visitor in visitation.visitors.slice(0, 4)" :key="visitor.id"
                                            class="h-8 w-8 rounded-full ring-2 ring-white flex items-center justify-center text-xs font-bold"
                                            :class="visitation.status === 'completed' ? 'bg-green-200 text-green-900' : 'bg-blue-100 text-blue-900'"
                                            :title="visitor.full_name">
                                            {{ visitor.full_name?.charAt(0) }}
                                        </div>
                                        <div v-if="visitation.visitors.length > 4"
                                            class="h-8 w-8 rounded-full ring-2 ring-white bg-gray-200 flex items-center justify-center text-[11px] font-bold text-gray-700">
                                            +{{ visitation.visitors.length - 4 }}
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1.5 leading-tight max-w-[200px] truncate">{{ visitation.visitors.map(v => v.full_name).join(', ') }}</div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span v-if="visitation.status === 'completed'" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[13px] font-bold bg-green-200 text-green-900">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        Hoàn thành
                                    </span>
                                    <span v-else-if="visitation.status === 'cancelled'" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[13px] font-bold bg-gray-200 text-gray-700">
                                        Đã hủy
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-[13px] font-bold bg-blue-100 text-blue-800">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Kế hoạch
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex justify-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button v-if="canManage" @click="editForm(visitation)" class="text-xs font-bold px-3 py-1.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg shadow-sm transition-colors">
                                            Sửa
                                        </button>
                                        <button v-if="canManage || isGlobalAdmin" @click="confirmDelete(visitation)" class="text-xs font-bold px-3 py-1.5 bg-white border border-red-200 text-red-600 hover:bg-red-50 rounded-lg shadow-sm transition-colors">
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Grid Cards (Desktop) -->
                <div v-else-if="visitations.data.length > 0 && viewMode === 'grid'" class="hidden md:grid grid-cols-2 lg:grid-cols-3 gap-5 p-5">
                    <div v-for="visitation in visitations.data" :key="'grid-'+visitation.id"
                        :class="{
                            'border-green-200 bg-green-50': visitation.status === 'completed',
                            'border-red-200 bg-red-50': visitation.priority === 'high' && visitation.status !== 'completed',
                            'border-gray-200 bg-gray-50': visitation.status === 'cancelled',
                            'border-gray-200 bg-white': visitation.status === 'planned' && visitation.priority !== 'high',
                        }"
                        class="p-5 border rounded-2xl hover:shadow-md transition-all relative flex flex-col group">
                        <!-- Status bar -->
                        <div class="absolute top-0 left-0 w-1.5 h-full rounded-l-2xl"
                            :class="{
                                'bg-green-400': visitation.status === 'completed',
                                'bg-red-400': visitation.priority === 'high' && visitation.status !== 'completed',
                                'bg-gray-300': visitation.status === 'cancelled',
                                'bg-blue-400': visitation.status === 'planned' && visitation.priority !== 'high',
                            }"></div>
                        <div class="ml-3">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-bold text-gray-900 text-[15px] group-hover:text-amber-700 transition-colors">{{ visitation.member?.full_name }}</h3>
                                    <p class="text-[13px] text-gray-500 mt-0.5">{{ formatDate(visitation.visit_date) }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-100 text-amber-800 capitalize">{{ visitation.reason }}</span>
                            </div>
                            <p class="text-sm text-gray-500 mb-5 flex-1 leading-relaxed"><span class="font-medium text-gray-700">Người thăm:</span> {{ visitation.visitors.map(v => v.full_name).join(', ') }}</p>
                            <div class="flex gap-3 pt-4 border-t border-gray-100 mt-auto opacity-0 group-hover:opacity-100 transition-opacity">
                                <button v-if="canManage" @click="editForm(visitation)" class="flex-1 text-[13px] font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 py-2 rounded-xl border border-blue-100 transition-colors">Cập nhật</button>
                                <button v-if="canManage || isGlobalAdmin" @click="confirmDelete(visitation)" class="flex-1 text-[13px] font-bold text-red-700 bg-red-50 hover:bg-red-100 py-2 rounded-xl border border-red-100 transition-colors">Xóa</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Cards -->
                <div v-if="visitations.data.length > 0" class="md:hidden divide-y divide-gray-100 bg-white">
                    <div v-for="visitation in visitations.data" :key="'mob-'+visitation.id"
                        :class="{
                            'bg-green-50/50 border-l-4 border-l-green-400': visitation.status === 'completed',
                            'bg-red-50/50 border-l-4 border-l-red-400': visitation.priority === 'high' && visitation.status !== 'completed',
                            'bg-gray-50/50 border-l-4 border-l-gray-300': visitation.status === 'cancelled',
                            'bg-white border-l-4 border-l-blue-400': visitation.status === 'planned' && visitation.priority !== 'high',
                        }"
                        class="px-5 py-4 transition-colors">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-bold text-gray-900 text-[15px]">{{ visitation.member?.full_name }}</h3>
                                <p class="text-[13px] text-gray-500 mt-0.5">{{ formatDate(visitation.visit_date) }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800 capitalize">{{ visitation.reason }}</span>
                                <span v-if="visitation.status === 'completed'" class="text-[11px] font-bold text-green-700">✅ Hoàn thành</span>
                                <span v-else-if="visitation.priority === 'high'" class="text-[11px] font-bold text-red-600">⚠️ Khẩn cấp</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-3.5 leading-relaxed"><span class="font-medium text-gray-800">Thăm:</span> {{ visitation.visitors.map(v => v.full_name).join(', ') }}</p>
                        <div class="flex gap-2">
                            <button v-if="canManage" @click="editForm(visitation)" class="text-[13px] font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-xl border border-blue-100 flex-1 transition-colors">Cập nhật</button>
                            <button v-if="canManage || isGlobalAdmin" @click="confirmDelete(visitation)" class="text-[13px] font-bold text-red-600 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-xl border border-red-100 transition-colors">Xóa</button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="visitations.links && visitations.data.length > 0" class="px-5 py-4 border-t border-gray-100 flex justify-between items-center bg-gray-50">
                    <p class="text-sm text-gray-500">Trang <span class="font-medium text-gray-900">{{ visitations.current_page }}</span> / {{ visitations.last_page }}</p>
                    <div class="flex gap-1.5">
                        <template v-for="(link, k) in visitations.links" :key="k">
                            <Link v-if="link.url" :href="link.url"
                                class="px-3.5 py-1.5 border rounded-lg text-sm font-medium transition-colors"
                                :class="link.active ? 'bg-amber-600 text-white border-amber-600' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200'"
                                v-html="link.label">
                            </Link>
                            <span v-else class="px-3.5 py-1.5 rounded-lg text-sm text-gray-400 cursor-not-allowed bg-gray-50 border border-gray-100" v-html="link.label"></span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Mobile FAB -->
            <div v-if="canManage || isGlobalAdmin || portalType === 'ministry'" class="fixed bottom-20 right-4 flex flex-col gap-3 md:hidden z-40">
                <!-- Suggestion Button -->
                <button v-if="isGlobalAdmin || portalType === 'ministry'" @click="isSuggOpen = true"
                    class="relative w-12 h-12 flex items-center justify-center rounded-full shadow-lg bg-white border border-amber-200 text-amber-600 hover:bg-amber-50 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span v-if="suggCount > 0" class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow-sm ring-2 ring-white">
                        {{ suggCount }}
                    </span>
                </button>
                <template v-if="canManage">
                    <button @click="openEmergencyForm"
                        class="w-12 h-12 flex items-center justify-center rounded-full shadow-lg bg-red-500 text-white hover:bg-red-600 transition-all active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </button>
                    <button @click="openForm"
                        class="w-14 h-14 flex items-center justify-center rounded-full shadow-xl bg-gradient-to-br from-amber-500 to-amber-600 text-white hover:from-amber-600 hover:to-amber-700 transition-all active:scale-95">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </template>
            </div>
        </div>

        <!-- SlideOver Form -->
        <!-- SlideOver Form -->
        <SlideOver v-model="isFormOpen" :title="isEditing ? 'Cập nhật Chuyến Thăm' : 'Lập Kế hoạch Thăm Viếng'">
            <div class="space-y-6">
                <!-- For department localized visitation, visitation_type is implicitly 'department' -->
                
                <!-- Date -->
                <div class="flex space-x-5">
                    <div class="flex-1">
                        <label class="block text-[15px] font-bold text-gray-700 mb-1.5">Ngày thăm (dự kiến) <span class="text-red-500">*</span></label>
                        <input v-model="form.visit_date" type="date" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-[15px] py-2">
                        <p v-if="form.errors.visit_date" class="mt-1.5 text-sm text-red-500">{{ form.errors.visit_date }}</p>
                    </div>
                    
                    <div class="w-1/3">
                        <label class="block text-[15px] font-bold text-gray-700 mb-1.5">Độ ưu tiên</label>
                        <select v-model="form.priority" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-[15px] py-2 font-bold" :class="{'text-red-600': form.priority === 'high'}">
                            <option value="normal" class="text-gray-900 text-[15px]">Bình thường</option>
                            <option value="medium" class="text-amber-600 text-[15px]">Trung bình</option>
                            <option value="high" class="text-red-600 text-[15px]">Khẩn Cấp (Cao)</option>
                        </select>
                    </div>
                </div>

                <!-- Visited Member (Searchable) -->
                <div>
                    <label class="block text-[15px] font-bold text-gray-700 mb-1.5">Tín hữu được thăm <span class="text-red-500">*</span></label>
                    <template v-if="!isEditing">
                        <div class="relative mb-3">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" v-model="visitedSearch" placeholder="Tìm người được thăm..." class="block w-full pl-10 pr-3 py-2 text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500 italic">
                        </div>
                        
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 max-h-56 overflow-y-auto mb-3 custom-scrollbar">
                            <div class="flex flex-col space-y-2">
                                <label v-for="m in filteredVisitedMembers" :key="'vis-'+m.id" class="inline-flex items-center cursor-pointer p-2 hover:bg-amber-50 rounded-lg transition-colors">
                                    <input type="checkbox" v-model="form.member_ids" :value="m.id" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 hidden peer">
                                    <div class="w-5 h-5 border-2 border-gray-300 rounded flex items-center justify-center peer-checked:bg-amber-500 peer-checked:border-amber-500 mr-3 transition-colors">
                                        <svg class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <span class="text-[15px] font-medium text-gray-700 peer-checked:text-amber-800 flex-1">{{ m.full_name }}</span>
                                    <span class="text-xs text-gray-500 font-medium ml-2">{{ m.phone || 'K có SĐT' }}</span>
                                </label>
                                <div v-if="filteredVisitedMembers.length === 0" class="text-center py-5 text-sm text-gray-400 italic">Không tìm thấy ai</div>
                            </div>
                        </div>
                        
                        <div v-if="form.member_ids.length > 0" class="flex flex-wrap gap-2">
                            <span v-for="vId in form.member_ids" :key="vId" class="inline-flex items-center px-2.5 py-1 rounded bg-amber-100 text-amber-800 text-[13px] font-bold">
                                {{ members.find(m => m.id === vId)?.full_name || 'Unknown' }}
                                <button @click.prevent="form.member_ids = form.member_ids.filter(id => id !== vId)" class="ml-1.5 text-amber-600 hover:text-amber-900 bg-amber-200/50 rounded-full p-0.5"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                            </span>
                        </div>
                        <p v-if="form.errors.member_ids" class="mt-1 text-sm text-red-500">{{ form.errors.member_ids }}</p>
                    </template>
                    <template v-else>
                        <SearchableSelect
                            v-model="form.member_id"
                            :options="members.map(m => ({ value: m.id, label: `${m.full_name} (${m.phone || 'K có SĐT'})` }))"
                            placeholder="-- Gõ tên để tìm nhanh --"
                            searchPlaceholder="Tìm theo tên..."
                            noResultsText="Không tìm thấy thành viên này"
                        />
                        <p v-if="form.errors.member_id" class="mt-1 text-sm text-red-500">{{ form.errors.member_id }}</p>
                    </template>
                    
                    <!-- Location / Geolocation Row (Only visible if member selected) -->
                    <div v-if="isEditing ? form.member_id : (form.member_ids && form.member_ids.length === 1)" class="mt-4 bg-gray-50 border border-gray-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[13px] font-bold text-gray-700">Tọa độ Bản đồ (GPS):</span>
                            <div class="space-x-2 flex">
                                <button type="button" @click="fetchLocation" :disabled="isFetchingLocation" class="text-[13px] font-medium px-3 py-1.5 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 transition-colors">
                                    {{ isFetchingLocation ? 'Đang lấy GPS...' : '📍 Lấy Tọa Độ' }}
                                </button>
                                <a v-if="form.latitude && form.longitude" :href="`https://www.google.com/maps/dir/?api=1&destination=${form.latitude},${form.longitude}`" target="_blank" class="text-[13px] px-3 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg hover:bg-blue-100 font-bold inline-flex items-center transition-colors">
                                    🗺️ Dẫn đường
                                </a>
                            </div>
                        </div>
                        <div class="flex space-x-3">
                            <input v-model="form.latitude" type="text" placeholder="Vĩ độ (Latitude)" class="w-1/2 text-sm border-gray-300 rounded-lg py-2 pl-3 pr-2" :class="{'bg-gray-100': !form.latitude}">
                            <input v-model="form.longitude" type="text" placeholder="Kinh độ (Longitude)" class="w-1/2 text-sm border-gray-300 rounded-lg py-2 pl-3 pr-2" :class="{'bg-gray-100': !form.longitude}">
                        </div>
                    </div>
                </div>

                <!-- Custom Visitors Selector (Only from this department) -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-[15px] font-bold text-gray-700">Đoàn đi thăm <span class="text-red-500">*</span></label>
                        <div class="flex space-x-2 items-center">
                            <button type="button" @click="selectExecutiveTeam" class="text-[13px] text-amber-600 font-bold hover:text-amber-800 hover:underline transition-colors">Ban điều hành</button>
                            <span class="text-gray-300">|</span>
                            <button type="button" @click="form.visitors = []" class="text-[13px] text-gray-500 hover:text-gray-800 hover:underline transition-colors">Xóa chọn</button>
                        </div>
                    </div>
                    
                    <div class="relative mb-3">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" v-model="visitorSearch" placeholder="Tìm người đi thăm..." class="block w-full pl-10 pr-3 py-2 text-sm border-gray-200 rounded-xl focus:ring-amber-500 focus:border-amber-500 italic">
                    </div>
                    
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 max-h-56 overflow-y-auto mb-3 custom-scrollbar">
                        <div class="flex flex-col space-y-2">
                            <label v-for="m in filteredVisitors" :key="m.id" class="inline-flex items-center cursor-pointer p-2 hover:bg-amber-50 rounded-lg transition-colors">
                                <input type="checkbox" v-model="form.visitors" :value="m.id" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 hidden peer">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded flex items-center justify-center peer-checked:bg-amber-500 peer-checked:border-amber-500 mr-3 transition-colors">
                                    <svg class="w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-[15px] font-medium text-gray-700 peer-checked:text-amber-800 flex-1">{{ m.full_name }}</span>
                                <span v-if="isExecutive(m)" class="text-[11px] bg-amber-100 text-amber-800 px-2 py-0.5 rounded font-bold ml-2">BĐH</span>
                                <span v-else class="text-[11px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded font-medium ml-2">Ban viên</span>
                            </label>
                            <div v-if="filteredVisitors.length === 0" class="text-center py-5 text-sm text-gray-400 italic">Không tìm thấy ai</div>
                        </div>
                    </div>
                    
                    <div v-if="form.visitors.length > 0" class="flex flex-wrap gap-2">
                        <span v-for="vId in form.visitors" :key="vId" class="inline-flex items-center px-2.5 py-1 rounded bg-amber-100 text-amber-800 text-[13px] font-bold">
                            {{ members.find(m => m.id === vId)?.full_name || 'Unknown' }}
                            <button @click.prevent="form.visitors = form.visitors.filter(id => id !== vId)" class="ml-1.5 text-amber-600 hover:text-amber-900 bg-amber-200/50 rounded-full p-0.5"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </span>
                    </div>

                    <p v-if="form.errors.visitors" class="mt-1.5 text-sm text-red-500">{{ form.errors.visitors }}</p>
                </div>

                <!-- Status & Reason -->
                <div class="flex space-x-5">
                    <div class="w-1/3">
                        <label class="block text-[15px] font-bold text-gray-700 mb-1.5">Trạng thái <span class="text-red-500">*</span></label>
                        <select v-model="form.status" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-[15px] py-2 font-bold">
                            <option value="planned" class="text-blue-600">Kế hoạch</option>
                            <option value="completed" class="text-green-600">Hoàn Thành</option>
                            <option value="cancelled" class="text-gray-500">Đã Hủy</option>
                        </select>
                    </div>

                    <div class="flex-1">
                        <div class="flex justify-between items-center mb-1.5">
                            <label class="block text-[15px] font-bold text-gray-700">Lý do thăm viếng <span class="text-red-500">*</span></label>
                            <button type="button" @click="addReason" class="text-[13px] text-amber-600 font-bold hover:text-amber-800 transition-colors bg-amber-50 px-2 py-0.5 rounded shadow-sm border border-amber-100">+ Thêm lý do</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <select v-model="form.reason" class="flex-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-[15px] py-2 capitalize">
                                <option value="">-- Chọn lý do --</option>
                                <option v-for="r in reasons" :key="r" :value="r">{{ r }}</option>
                            </select>
                            <button type="button" v-if="form.reason && canDeleteReason(form.reason)" @click="deleteReason(form.reason)" title="Xóa lý do này khỏi bộ nhớ" class="p-2 text-red-500 hover:bg-red-50 rounded-lg shrink-0 border border-transparent hover:border-red-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                        <p v-if="form.errors.reason" class="mt-1.5 text-sm text-red-500">{{ form.errors.reason }}</p>
                    </div>
                </div>

                <!-- Prayer Points -->
                <div>
                    <label class="block text-[15px] font-bold text-gray-700 mb-1.5">Vấn đề cầu nguyện</label>
                    <textarea v-model="form.prayer_points" rows="2" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-[15px]"></textarea>
                    <p class="mt-1.5 text-xs text-gray-500">Ghi chú lại những vấn đề cần HT/Ban ngành cầu nguyện thêm.</p>
                </div>

                <!-- Content -->
                <div>
                    <label class="block text-[15px] font-bold text-gray-700 mb-1.5">Nội dung chi tiết</label>
                    <textarea v-model="form.content" rows="4" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-[15px]"></textarea>
                    <p class="mt-1.5 text-xs text-gray-500 font-bold text-amber-600">⚠ Dữ liệu nhạy cảm. Chỉ ban quản lý mới được xem đầy đủ.</p>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end space-x-3 w-full">
                    <button @click="isFormOpen = false" type="button" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-[15px] font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-colors">
                        Hủy
                    </button>
                    <button @click="submitForm" :disabled="form.processing" type="button" class="px-6 py-2.5 border border-transparent rounded-xl shadow-sm text-[15px] font-bold text-white bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Đang lưu...' : 'Lưu báo cáo' }}
                    </button>
                </div>
            </template>
        </SlideOver>

        <!-- Suggestions Slide-Over -->
        <SlideOver v-model="isSuggOpen" title="Đề Xuất Thăm Viếng">
            <div class="space-y-5">
                <!-- Dept Filter inside suggestions -->
                <div v-if="portalType === 'ministry' && activityDepartments?.length" class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                    <label class="block text-sm font-bold text-amber-800 mb-2">Lọc theo Ban Sinh Hoạt</label>
                    <select v-model="suggDept" @change="fetchSuggestions" class="block w-full rounded-xl border-amber-200 text-[15px] font-medium py-2.5 pl-3.5 pr-8 text-gray-700 bg-white focus:ring-amber-500 focus:border-amber-500 shadow-sm">
                        <option value="">Toàn bộ Hội Thánh</option>
                        <option v-for="d in activityDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
                        <option value="other">Khác (không thuộc ban sinh hoạt nào)</option>
                    </select>
                </div>

                <!-- Summary badge -->
                <div v-if="localSuggestions.length > 0" class="flex items-center justify-between mb-2">
                    <span class="text-[15px] font-bold text-gray-700">{{ filteredSuggestions.length }} tín hữu cần thăm</span>
                    <div class="flex gap-2.5">
                        <span class="text-xs font-bold px-2.5 py-1 bg-red-100 text-red-700 rounded-lg">{{ filteredSuggestions.filter(s => s.priority === 'high').length }} Khẩn</span>
                        <span class="text-xs font-bold px-2.5 py-1 bg-amber-100 text-amber-700 rounded-lg">{{ filteredSuggestions.filter(s => s.priority === 'medium').length }} Trung bình</span>
                    </div>
                </div>

                <div v-if="localSuggestions.length > 0" class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors group-focus-within:text-amber-500">
                        <svg class="h-4 w-4 text-gray-400 group-focus-within:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" v-model="suggSearch" placeholder="Lọc nhanh bằng Tên hoặc SĐT..." class="block w-full pl-10 pr-4 py-2.5 text-[15px] border-amber-200 rounded-xl focus:ring-amber-500 focus:border-amber-500 transition-all shadow-sm bg-white hover:bg-amber-50/30 mb-5">
                </div>

                <div v-if="filteredSuggestions.length === 0" class="py-14 flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mb-4 text-green-500 shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-[15px] font-bold text-gray-800 mb-1.5">Tốt lắm!</p>
                    <p class="text-[13px] text-gray-500">Không có tín hữu nào cần ưu tiên thăm viếng trong lúc này.</p>
                </div>

                <div class="space-y-3">
                    <div v-for="s in filteredSuggestions" :key="'sugg-'+s.id"
                        class="p-5 rounded-2xl border transition-colors shadow-sm hover:shadow"
                        :class="s.priority === 'high' ? 'bg-red-50 border-red-200' : 'bg-amber-50 border-amber-200'">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2.5 mb-2">
                                    <div class="w-3 h-3 rounded-full shrink-0" :class="s.priority === 'high' ? 'bg-red-500' : 'bg-amber-400'"></div>
                                    <h4 class="font-bold text-[15px] truncate leading-tight" :class="s.priority === 'high' ? 'text-red-950' : 'text-gray-950'">{{ s.full_name }}</h4>
                                </div>
                                <p v-if="s.phone" class="text-[13px] text-gray-600 ml-5.5 flex items-center gap-1.5"><span class="text-gray-400">📞</span> {{ s.phone }}</p>
                                <p v-if="s.dept_name" class="text-[13px] text-blue-700 font-medium ml-5.5 mt-1 flex items-center gap-1.5"><span class="text-blue-400">🏠</span> {{ s.dept_name }}</p>
                                <div class="flex flex-wrap gap-1.5 mt-3 ml-5.5">
                                    <span v-for="r in s.reasons" :key="r"
                                        :class="s.priority === 'high' ? 'bg-red-100/80 text-red-800 border-red-200' : 'bg-amber-100/80 text-amber-800 border-amber-200'"
                                        class="text-[11px] font-bold px-2.5 py-0.5 rounded-md border">{{ r }}</span>
                                </div>
                            </div>
                            <button v-if="canManage" @click="createFromSuggestion(s); isSuggOpen = false"
                                class="shrink-0 text-[13px] font-bold px-4 py-2 rounded-xl transition-all shadow-sm active:scale-95"
                                :class="s.priority === 'high' ? 'bg-red-600 text-white hover:bg-red-700 hover:shadow-red-200' : 'bg-amber-500 text-white hover:bg-amber-600 hover:shadow-amber-200'">
                                Lập kế hoạch
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </SlideOver>

    </PortalLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';
import DataToolbar from '@/Components/DataToolbar.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    visitations: Object,
    members: Array,
    suggestions: [Array, Object],
    filters: Object,
    canManage: Boolean,
    visitationTypes: Object,
    reasons: Array,
    dbReasons: Array,
    department: Object,
    isGlobalAdmin: Boolean,
    routePrefix: String,
    portalType: String,
    activityDepartments: Array,
});

const getRoutePrefix = () => props.routePrefix || 'portal.visitation';

// DataToolbar State
const search = ref(props.filters.search || '');
const viewMode = ref('list');
const filtersForm = ref({
    period: props.filters.period || '',
    reason: props.filters.reason || '',
    filter_dept: props.filters.filter_dept || '',
});

// Suggestions slide-over state
const isSuggOpen = ref(false);
const suggDept = ref(props.filters.sugg_dept || '');
const suggSearch = ref('');
const localSuggestions = ref(Array.isArray(props.suggestions) ? props.suggestions : Object.values(props.suggestions || {}));

const filteredSuggestions = computed(() => {
    let list = localSuggestions.value;
    if (suggSearch.value) {
        let q = suggSearch.value.toLowerCase();
        list = list.filter(s => (s.full_name && s.full_name.toLowerCase().includes(q)) || (s.phone && s.phone.includes(q)));
    }
    return list;
});

const suggCount = computed(() => localSuggestions.value.length);

const fetchSuggestions = () => {
    router.get(route(getRoutePrefix() + '.index'), {
        ...filtersForm.value,
        search: search.value,
        sugg_dept: suggDept.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
            localSuggestions.value = Array.isArray(props.suggestions) ? props.suggestions : Object.values(props.suggestions || {});
        }
    });
};

watch(() => props.suggestions, (val) => {
    localSuggestions.value = Array.isArray(val) ? val : Object.values(val || {});
}, { immediate: true });

const updateFilters = () => {
    router.get(route(getRoutePrefix() + '.index'), { 
        search: search.value,
        period: filtersForm.value.period, 
        reason: filtersForm.value.reason,
        filter_dept: filtersForm.value.filter_dept,
        sugg_dept: suggDept.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

watch(search, debounce(() => {
    updateFilters();
}, 300));

const setPeriod = (periodValue) => {
    filtersForm.value.period = periodValue;
    updateFilters();
};

const clearFilters = () => {
    search.value = '';
    filtersForm.value = {
        period: '',
        reason: '',
        filter_dept: '',
    };
    suggDept.value = '';
    updateFilters();
};

const addReason = () => {
    const reasonName = prompt('Nhập tên lý do thăm viếng mới:');
    if (reasonName && reasonName.trim()) {
        router.post(route('visitation-reasons.store'), { name: reasonName.trim() }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                form.reason = reasonName.trim().toLowerCase();
            }
        });
    }
};

const canDeleteReason = (name) => {
    if (!props.dbReasons) return false;
    const dbR = props.dbReasons.find(r => r.name === name);
    return dbR && (dbR.department_id === props.department?.id || props.isGlobalAdmin);
};

const deleteReason = (name) => {
    const dbR = props.dbReasons.find(r => r.name === name);
    if (!dbR) return;
    if (confirm(`Bạn có chắc muốn xóa lý do: ${name}?`)) {
        router.delete(route('visitation-reasons.destroy', dbR.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                form.reason = '';
            }
        });
    }
};

// Visitors selection logic
const visitorSearch = ref('');
const filteredVisitors = computed(() => {
    if (!visitorSearch.value) return props.members;
    const q = visitorSearch.value.toLowerCase();
    return props.members.filter(m => m.full_name.toLowerCase().includes(q));
});

const isExecutive = (member) => {
    return member.memberships?.some(m => m.role?.level >= 30);
};

const selectExecutiveTeam = () => {
    form.visitors = props.members
        .filter(isExecutive)
        .map(m => m.id);
};

// Form Management
const isFormOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    visitation_type: 'department', // Default, but overridden
    member_ids: [],
    member_id: '',
    visit_date: new Date().toISOString().split('T')[0],
    reason: '',
    prayer_points: '',
    content: '',
    gifts: '',
    visitors: [],
    // newly added fields
    status: 'planned',
    priority: 'normal',
    latitude: '',
    longitude: '',
    // Support pastoral array map
    visitor_ids: []
});

const visitedSearch = ref('');
const filteredVisitedMembers = computed(() => {
    if (!visitedSearch.value) return props.members;
    const q = visitedSearch.value.toLowerCase();
    return props.members.filter(m => m.full_name.toLowerCase().includes(q) || (m.phone && m.phone.includes(q)));
});

watch(() => form.visitors, (newVal) => {
    form.visitor_ids = newVal; // Keep both synced in case pastoral uses id
}, { deep: true });

const openForm = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.member_ids = [];
    form.visitation_type = props.portalType === 'ministry' ? 'department' : 'department'; // Keep as is initially
    
    // Fix: Get local date properly
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    form.visit_date = `${yyyy}-${mm}-${dd}`;
    
    isFormOpen.value = true;
};

const openEmergencyForm = () => {
    openForm();
    form.priority = 'high';
    form.reason = 'ốm đau'; // pre-fill a reason
};

const createFromSuggestion = (suggestion) => {
    openForm();
    form.member_ids = [suggestion.id];
    form.priority = suggestion.priority;
    form.latitude = suggestion.latitude || '';
    form.longitude = suggestion.longitude || '';
};

const editForm = (visitation) => {
    isEditing.value = true;
    editingId.value = visitation.id;
    form.visitation_type = visitation.visitation_type;
    form.member_id = visitation.member_id;
    
    // Fix: Properly parse Laravel's datetime output safely
    form.visit_date = visitation.visit_date ? String(visitation.visit_date).split('T')[0].split(' ')[0] : '';
    
    form.reason = visitation.reason;
    form.status = visitation.status || 'planned';
    form.priority = visitation.priority || 'normal';
    form.prayer_points = visitation.prayer_points;
    form.content = visitation.content && visitation.content.includes('***') ? '' : visitation.content;
    form.gifts = visitation.gifts;
    form.visitors = visitation.visitors ? visitation.visitors.map(v => v.id) : [];
    // Populate coordinates if member has it
    form.latitude = visitation.member?.latitude || '';
    form.longitude = visitation.member?.longitude || '';
    
    isFormOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route(getRoutePrefix() + '.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => isFormOpen.value = false,
        });
    } else {
        form.post(route(getRoutePrefix() + '.store'), {
            preserveScroll: true,
            onSuccess: () => isFormOpen.value = false,
        });
    }
};

const confirmDelete = (visitation) => {
    if (confirm(`Bạn có chắc muốn xóa báo cáo thăm viếng này?`)) {
        router.delete(route(getRoutePrefix() + '.destroy', visitation.id), {
            preserveScroll: true,
        });
    }
};

// Geolocation Logic
const isFetchingLocation = ref(false);
const fetchLocation = () => {
    if (!navigator.geolocation) {
        alert("Trình duyệt không hỗ trợ Geolocation.");
        return;
    }
    
    isFetchingLocation.value = true;
    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;
            isFetchingLocation.value = false;
        },
        (error) => {
            alert(`Lỗi khi lấy vị trí GPS: ${error.message}`);
            isFetchingLocation.value = false;
        },
        { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
    );
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(date);
};
</script>
