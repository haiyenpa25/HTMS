<template>
  <div class="min-h-screen bg-slate-50 font-sans flex flex-col">
    <!-- Top Nav -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center gap-3">
            <Link :href="route('dashboard')" class="text-orange-600 hover:text-orange-700 transition flex items-center gap-2 font-bold text-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Trở về Hệ Thống
            </Link>
          </div>
          <div class="font-black text-slate-800 text-lg flex items-center gap-2">
            <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-lg text-sm">Docs Hub</span>
            Hướng Dẫn & Cẩm Nang
          </div>
        </div>
      </div>
      
      <!-- Tab Switcher -->
      <div class="bg-slate-100 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8 overflow-x-auto hide-scrollbar">
                <Link v-for="tab in modes" :key="tab.id" 
                    :href="route(route().current() || 'help.install', { mode: tab.id })"
                    class="py-4 px-1 border-b-2 font-bold text-sm transition-colors whitespace-nowrap"
                    :class="currentMode === tab.id ? 'border-orange-500 text-orange-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'">
                    {{ tab.name }}
                </Link>
            </div>
        </div>
      </div>
    </header>

    <div class="flex-1 max-w-7xl mx-auto w-full flex flex-col lg:flex-row px-4 sm:px-6 lg:px-8 py-8 gap-8">
      <!-- Sidebar Nav -->
      <aside class="w-full lg:w-64 shrink-0 flex flex-col space-y-6">
        <div v-for="group in activeNavGroups" :key="group.title">
            <h3 class="font-black text-xs text-slate-400 uppercase tracking-wider mb-2 px-3">{{ group.title }}</h3>
            <div class="space-y-1">
                <Link v-for="link in group.links" :key="link.route"
                    :href="route(link.route, { mode: currentMode })"
                    class="px-3 py-2.5 rounded-xl font-bold text-sm transition-all flex items-center gap-2"
                    :class="route().current(link.route) ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-700'">
                    <span class="text-lg">{{ link.icon }}</span>
                    {{ link.name }}
                </Link>
            </div>
        </div>
      </aside>

      <!-- Main Content Area -->
      <main class="flex-1 bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 sm:p-10">
            <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currentMode = computed(() => page.props.mode || route().params.mode || 'theo-chuc-nang');

const modes = [
    { id: 'theo-chuc-nang', name: '📚 Đọc Theo Theo Chức Năng' },
    { id: 'theo-nguoi-dung', name: '👥 Cẩm Nang Theo Vai Trò' },
    { id: 'theo-portal', name: '🚪 Tổ Chức Theo Apps (Portals)' },
];

const chucNangNav = [
    {
        title: 'Khởi đầu & Kiến trúc',
        links: [
            { name: 'Đăng nhập & Xác thực', route: 'help.auth', icon: '🔑' },
            { name: 'Khởi tạo & Cài đặt', route: 'help.install', icon: '⚙️' },
            { name: 'Tổng quan hệ thống', route: 'help.overview', icon: '📖' },
        ]
    },
    {
        title: 'Tính năng Nghiệp vụ',
        links: [
            { name: 'Ban Ngành Cơ Bản', route: 'help.departments.intro', icon: '🏢' },
            { name: 'Thành Viên', route: 'help.departments.members', icon: '👥' },
            { name: 'Tổ Chức Điểm Danh', route: 'help.departments.attendance', icon: '✅' },
            { name: 'CRM Thăm Viếng', route: 'help.departments.visitation', icon: '❤️' },
            { name: 'Lịch phân công', route: 'help.departments.assignments', icon: '📋' },
            { name: 'Tài Chính Quỹ', route: 'help.departments.finance', icon: '💵' },
            { name: 'Báo Cáo Tổng Hợp', route: 'help.departments.reports', icon: '📊' },
            { name: 'Nhật ký Buổi Nhóm', route: 'help.meetings', icon: '🗓️' },
        ]
    },
    {
        title: 'Quản Trị Hệ Thống',
        links: [
            { name: 'Cài đặt Chung', route: 'help.sysadmin', icon: '🛡️' },
            { name: 'Quản lý Người Dùng', route: 'help.admin.users', icon: '👥' },
            { name: 'Quản lý Tính Năng', route: 'help.admin.features', icon: '🧩' },
            { name: 'Phân Quyền (MAC V2)', route: 'help.admin.permissions', icon: '🔑' }
        ]
    }
];

const nguoiDungNav = [
    {
        title: 'Dành Cho Mọi Tín Hữu',
        links: [
            { name: 'Đăng nhập', route: 'help.auth', icon: '🔑' },
            { name: 'Cổng Thành Viên', route: 'help.members', icon: '👤' },
        ]
    },
    {
        title: 'Cấp Ban Ngành (Thư ký, BHD)',
        links: [
            { name: 'Hiểu về Portal Ban Ngành', route: 'help.departments.intro', icon: '🏢' },
            { name: 'Quản lý Nhân sự', route: 'help.departments.members', icon: '👥' },
            { name: 'Tiến hành Điểm Danh', route: 'help.departments.attendance', icon: '✅' },
            { name: 'Giao Việc, Phân Công', route: 'help.departments.assignments', icon: '📋' },
            { name: 'Thu chi Quỹ cá nhân', route: 'help.departments.finance', icon: '💵' },
        ]
    },
    {
        title: 'Cấp Lãnh Đạo (Mục Sư, Chấp Sự)',
        links: [
            { name: 'Tổng quan Bối cảnh', route: 'help.overview', icon: '📖' },
            { name: 'Cổng Lãnh Đạo', route: 'help.leadership', icon: '📈' },
            { name: 'Thống kê Báo cáo', route: 'help.departments.reports', icon: '📊' },
        ]
    },
    {
        title: 'Cấp Quản Trị Hệ Thống (SA)',
        links: [
            { name: 'Quản lý Danh mục User', route: 'help.admin.users', icon: '👥' },
            { name: 'Bật/Tắt Module', route: 'help.admin.features', icon: '🧩' },
            { name: 'Ma Trận Quyền Hạn', route: 'help.admin.permissions', icon: '🔑' },
            { name: 'Cấu hình Lõi', route: 'help.sysadmin', icon: '⚙️' },
        ]
    }
];

const portalNav = [
    {
        title: 'Trọng Tâm & Cơ Bản',
        links: [
            { name: 'Sơ đồ luân chuyển Portal', route: 'help.portals.intro', icon: '🗺️' },
            { name: 'Trang Xác Thực', route: 'help.auth', icon: '🔑' },
        ]
    },
    {
        title: 'Portal Sinh Hoạt (/portal)',
        links: [
            { name: 'Dữ Liệu Thành Viên', route: 'help.departments.members', icon: '👥' },
            { name: 'Tổ chức Điểm danh', route: 'help.departments.attendance', icon: '✅' },
            { name: 'Hệ thống Quỹ Nội Bộ', route: 'help.departments.finance', icon: '💵' },
            { name: 'Điều hướng Công việc', route: 'help.departments.assignments', icon: '📋' },
        ]
    },
    {
        title: 'Portal Mục Vụ (/ministry)',
        links: [
            { name: 'Danh sách Đối tượng', route: 'help.departments.visitation', icon: '❤️' },
            { name: 'Lớp Cơ Đốc Giáo Dục', route: 'help.education', icon: '🎓' },
        ]
    },
    {
        title: 'Admin Dashboard (/admin)',
        links: [
            { name: 'Phân tích User', route: 'help.admin.users', icon: '👥' },
            { name: 'Kiến trúc Permissions', route: 'help.admin.permissions', icon: '🔑' },
        ]
    }
];

const activeNavGroups = computed(() => {
    switch (currentMode.value) {
        case 'theo-nguoi-dung': return nguoiDungNav;
        case 'theo-portal': return portalNav;
        case 'theo-chuc-nang':
        default: return chucNangNav;
    }
});
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
