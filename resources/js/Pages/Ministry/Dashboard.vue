<template>
  <PortalLayout 
      :department="activeDepartment" 
      :available-departments="availableDepartments"
      :is-global-admin="isGlobalAdmin"
      portal-type="ministry"
  >
       <!-- Empty State -->
       <div v-if="!activeDepartment" class="h-full flex flex-col items-center justify-center p-6 text-center min-h-[60vh]">
           <div class="w-24 h-24 mb-6 rounded-3xl bg-blue-100 flex items-center justify-center text-blue-500 shadow-inner">
               <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
           </div>
           <h2 class="text-xl font-black text-gray-900 mb-2">Chào mừng đến Cổng Ban Mục Vụ</h2>
           <p class="text-sm text-gray-500 max-w-xs mb-8">
              {{ isGlobalAdmin ? 'Bạn có quyền quản lý toàn hệ thống. Hãy chọn một Ban Mục Vụ để làm việc.' : 'Bạn chưa được phân bổ vào Ban Mục Vụ nào.' }}
           </p>
           <button v-if="isGlobalAdmin" 
              class="px-6 py-3 bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-200 font-bold hover:bg-blue-700 transition-all text-sm flex items-center">
             <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
             Chọn Ban Mục Vụ
           </button>
       </div>

       <!-- Dashboard Content -->
       <div v-else class="w-full p-4 sm:p-6 lg:p-8 space-y-6 max-w-5xl mx-auto">
           
           <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
             
             <!-- Render cards động từ deptFeatures (Level 1: block/specific config) -->
             <template v-for="card in visibleFeatureCards" :key="card.key">
               
               <!-- UNLOCKED: User có quyền Level 2 -->
               <Link v-if="can(card.key)"
                 :href="card.href ? card.href : '#'"
                 class="bg-white rounded-[1.5rem] p-5 shadow-sm border flex flex-col items-center justify-center text-center group transition-all active:scale-95"
                 :class="`border-gray-100 hover:shadow-md ${card.hoverBorder}`">
                 <div class="w-16 h-16 mb-3 rounded-2xl flex items-center justify-center transition-colors"
                   :class="`${card.bg} ${card.text} ${card.hoverBg} group-hover:text-white`">
                   <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                     <g v-html="card.icon"></g>
                   </svg>
                 </div>
                 <h3 class="font-bold text-gray-900 leading-tight">{{ card.label }}<br>
                   <span class="text-xs font-medium" :class="card.sub">{{ card.subtitle }}</span>
                 </h3>
               </Link>

               <!-- LOCKED: User không có quyền Level 2 -->
               <div v-else
                 class="bg-gray-50 rounded-[1.5rem] p-5 border border-dashed border-gray-200 flex flex-col items-center justify-center text-center opacity-60 cursor-not-allowed relative overflow-hidden">
                 <div class="absolute top-2 right-2">
                   <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                   </svg>
                 </div>
                 <div class="w-16 h-16 mb-3 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400">
                   <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                     <g v-html="card.icon"></g>
                   </svg>
                 </div>
                 <h3 class="font-bold text-gray-400 leading-tight">{{ card.label }}<br>
                   <span class="text-xs font-medium text-gray-400">Chưa được cấp quyền</span>
                 </h3>
               </div>

             </template>

             <!-- Khi không có tính năng nào -->
             <template v-if="visibleFeatureCards.length === 0">
                 <div class="col-span-2 lg:col-span-3 bg-gray-50/40 border border-gray-200 rounded-2xl p-6 text-center text-sm text-gray-500 font-medium">
                     <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                     Ban này hiện chưa có tính năng nào được cấu hình cho portal.
                 </div>
             </template>

           </div>
           
           <div class="mt-4 bg-blue-50/50 rounded-3xl p-5 border border-blue-50 shadow-sm">
              <h3 class="text-sm font-black text-blue-900 tracking-wider mb-1 flex items-center">
                  <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  Thông tin Ban Mục Vụ
              </h3>
              <p class="text-blue-800 text-sm">Đang xem chức năng của <strong>{{ activeDepartment.name }}</strong>. Mọi dữ liệu được lưu riêng cho ban này.</p>
           </div>
       </div>


  </PortalLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';

const page = usePage();
const deptFeatures = computed(() => page.props.departmentFeatures || {});
const authPermissions = computed(() => page.props.userPermissions || {});

const props = defineProps({
    activeDepartment: Object,
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
    userPermissions: { type: Object, default: () => ({}) },
});

// Check quyền user Level 2
const can = (key) => {
    if (props.isGlobalAdmin) return true;
    return authPermissions.value?.[key] === true
        || (key === 'members' && authPermissions.value?.['thanh-vien'] === true);
};

// Redundant switcher state removed (handled in PortalLayout)


// ── Feature Card Definitions (tất cả tính năng có trong hệ thống) ──────────
// Mỗi entry map slug → route + UI. Chỉ hiển thị card nếu deptFeatures[slug] === true
// ── Feature Card Definitions (tất cả tính năng có trong hệ thống Dashboard) ──────────
// Ma trận Level 1 (deptFeatures) sẽ quyết định card nào hiện ra.
const allFeatureCards = [
    {
        key: 'visitation',
        label: 'Thăm Viếng',
        subtitle: 'Lịch trình & Báo cáo',
        href: route('ministry.visitation.index'),
        bg: 'bg-amber-50', text: 'text-amber-500', hoverBg: 'group-hover:bg-amber-500',
        hoverBorder: 'hover:border-amber-200', sub: 'text-amber-600',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>`,
    },
    {
        key: 'members',
        label: 'Thành Viên',
        subtitle: 'Thông tin ban viên',
        href: route('ministry.members.index'),
        bg: 'bg-blue-50', text: 'text-blue-500', hoverBg: 'group-hover:bg-blue-600',
        hoverBorder: 'hover:border-blue-200', sub: 'text-blue-600',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>`,
    },
    {
        key: 'attendance',
        label: 'Điểm Danh',
        subtitle: 'Nhóm & Sinh hoạt',
        href: route('portal.attendance.index'), // Ministry reuses activity attendance usually
        bg: 'bg-emerald-50', text: 'text-emerald-500', hoverBg: 'group-hover:bg-emerald-500',
        hoverBorder: 'hover:border-emerald-200', sub: 'text-emerald-600',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75"/>`,
    },
    {
        key: 'education-classes',
        label: 'Lớp Học',
        subtitle: 'Danh sách & Thiết lập',
        href: route('ministry.education.classes'),
        bg: 'bg-indigo-50', text: 'text-indigo-500', hoverBg: 'group-hover:bg-indigo-600',
        hoverBorder: 'hover:border-indigo-200', sub: 'text-indigo-600',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>`,
    },
    {
        key: 'education-report',
        label: 'Báo Cáo GD',
        subtitle: 'Thống kê giáo dục',
        href: route('ministry.education.report'),
        bg: 'bg-blue-50', text: 'text-blue-500', hoverBg: 'group-hover:bg-blue-600',
        hoverBorder: 'hover:border-blue-200', sub: 'text-blue-600',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>`,
    },
    {
        key: 'finance',
        label: 'Tài chính',
        subtitle: 'Thu chi & Quỹ',
        href: route('portal.finance.index'), 
        bg: 'bg-rose-50', text: 'text-rose-500', hoverBg: 'group-hover:bg-rose-600',
        hoverBorder: 'hover:border-rose-200', sub: 'text-rose-600',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>`,
    },
    {
        key: 'reports',
        label: 'Báo cáo',
        subtitle: 'Thống kê hoạt động',
        href: route('portal.reports.index'),
        bg: 'bg-purple-50', text: 'text-purple-500', hoverBg: 'group-hover:bg-purple-600',
        hoverBorder: 'hover:border-purple-200', sub: 'text-purple-600',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>`,
    },
    {
        key: 'duty-rooster',
        label: 'Lịch Sinh Hoạt',
        subtitle: 'Bảng phân công',
        href: route('duty-rooster.index'),
        bg: 'bg-amber-50', text: 'text-amber-500', hoverBg: 'group-hover:bg-amber-500',
        hoverBorder: 'hover:border-amber-200', sub: 'text-amber-600',
        icon: `<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>`,
    },
];

// Chỉ hiện cards mà ban này có trong deptFeatures (Level 1)
const visibleFeatureCards = computed(() => {
    const features = deptFeatures.value;
    if (!features) return [];
    return allFeatureCards.filter(card => {
        // Kiểm tra deptFeatures[slug] = true
        if (features[card.key] === true) return true;
        // Alias: 'members' có thể được lưu là 'thanh-vien'
        if (card.key === 'members' && features['thanh-vien'] === true) return true;
        return false;
    });
});
</script>