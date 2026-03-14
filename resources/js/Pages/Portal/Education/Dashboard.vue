<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" :portal-type="portalType">
        <div class="w-full p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto space-y-6">

            <!-- Hero banner -->
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-3xl p-6 text-white shadow-lg">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-black">Ban Cơ Đốc Giáo Dục</h1>
                        <p class="text-indigo-200 text-sm mt-0.5">{{ stats.total_classes }} lớp học · {{ stats.total_students }} học viên · {{ stats.total_sessions }} buổi đã học</p>
                    </div>
                </div>
            </div>

            <!-- Feature Cards -->
            <div>
                <h2 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-3">Tính năng</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">

                    <!-- Quản lý lớp học -->
                    <Link :href="route('education.classes')" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col items-center text-center group hover:shadow-md hover:border-indigo-200 transition-all active:scale-95 duration-150">
                        <div class="w-14 h-14 mb-3 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-sm leading-tight">Quản lý lớp<br><span class="text-xs font-medium text-indigo-600">Danh sách &amp; Thiết lập</span></h3>
                    </Link>

                    <!-- Điểm danh (truy cập nhanh qua sessions list) -->
                    <Link :href="route('education.classes')" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col items-center text-center group hover:shadow-md hover:border-green-200 transition-all active:scale-95 duration-150">
                        <div class="w-14 h-14 mb-3 rounded-2xl bg-green-50 text-green-500 flex items-center justify-center group-hover:bg-green-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-sm leading-tight">Điểm danh<br><span class="text-xs font-medium text-green-600">Vào lớp → Chọn buổi</span></h3>
                    </Link>

                    <!-- Tiền dâng -->
                    <Link :href="route('education.classes')" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col items-center text-center group hover:shadow-md hover:border-emerald-200 transition-all active:scale-95 duration-150">
                        <div class="w-14 h-14 mb-3 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-sm leading-tight">Tiền Dâng<br><span class="text-xs font-medium text-emerald-600">Thu chi theo lớp</span></h3>
                    </Link>

                    <!-- Báo cáo tháng -->
                    <Link :href="route('education.report')" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex flex-col items-center text-center group hover:shadow-md hover:border-blue-200 transition-all active:scale-95 duration-150">
                        <div class="w-14 h-14 mb-3 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-sm leading-tight">Báo Cáo<br><span class="text-xs font-medium text-blue-600">Thống kê theo tháng</span></h3>
                    </Link>

                </div>
            </div>

            <!-- Recent classes quick access -->
            <div v-if="classes.length > 0">
                <h2 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-3">Lớp học — Vào buổi học hôm nay</h2>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="divide-y divide-gray-100">
                        <div v-for="cls in classes" :key="cls.id" class="flex items-center gap-3 px-4 py-3 hover:bg-indigo-50/50 transition-colors">
                            <!-- Icon -->
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-black text-base shrink-0">
                                {{ cls.name.charAt(0) }}
                            </div>
                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 text-sm truncate">{{ cls.name }}</p>
                                <p class="text-xs text-gray-500">{{ cls.students_count }} học viên · {{ cls.session_count }} buổi đã học</p>
                            </div>
                            <!-- Teachers -->
                            <div class="hidden sm:flex items-center gap-1 shrink-0">
                                <span v-for="t in cls.teachers.slice(0,2)" :key="t.id" class="text-[11px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium">{{ t.full_name.split(' ').pop() }}</span>
                            </div>
                            <!-- CTA -->
                            <Link :href="route('education.sessions', cls.id)"
                                class="shrink-0 flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white text-xs font-black rounded-xl hover:bg-indigo-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Vào lớp
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty classes -->
            <div v-else class="bg-indigo-50/50 border border-indigo-100 rounded-2xl py-10 text-center">
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <p class="font-bold text-gray-700 text-sm">Chưa có lớp học nào</p>
                <p class="text-xs text-gray-400 mt-1">Vào <strong>Quản lý lớp</strong> để tạo lớp đầu tiên.</p>
            </div>

        </div>
    </PortalLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

defineProps({
    classes: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ total_classes: 0, total_students: 0, total_sessions: 0 }) },
    department: Object,
    availableDepartments: { type: Array, default: () => [] },
    isGlobalAdmin: Boolean,
    isAdmin: Boolean,
    portalType: { type: String, default: 'ministry' },
});
</script>