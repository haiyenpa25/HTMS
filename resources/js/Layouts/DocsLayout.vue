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
            <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-lg text-sm">Docs</span>
            Hướng Dẫn & Cài Đặt
          </div>
        </div>
      </div>
    </header>

    <div class="flex-1 max-w-7xl mx-auto w-full flex flex-col lg:flex-row px-4 sm:px-6 lg:px-8 py-8 gap-8">
      <!-- Sidebar Nav -->
      <aside class="w-full lg:w-64 shrink-0 flex flex-col space-y-6">
        <div v-for="group in navGroups" :key="group.title">
            <h3 class="font-black text-xs text-slate-400 uppercase tracking-wider mb-2 px-3">{{ group.title }}</h3>
            <div class="space-y-1">
                <Link v-for="link in group.links" :key="link.route"
                    :href="route(link.route)"
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
import { Link } from '@inertiajs/vue3';

const navGroups = [
    {
        title: 'Bắt Đầu',
        links: [
            { name: 'Khởi tạo & Cài đặt', route: 'help.install', icon: '⚙️' },
            { name: 'Tổng quan hệ thống', route: 'help.overview', icon: '📖' },
        ]
    },
    {
        title: 'Điều Hành Thường Nhật',
        links: [
            { name: 'Quản lý Nhân sự', route: 'help.members', icon: '👥' },
            { name: 'Sự kiện & Buổi nhóm', route: 'help.meetings', icon: '🗓️' },
            { name: 'Lịch phân công', route: 'help.duty_rooster', icon: '📋' },
        ]
    },
    {
        title: 'Cổng Trực Tuyến (Portals)',
        links: [
            { name: 'Khái quát các Cổng', route: 'help.portals', icon: '🚪' },
            { name: 'Cơ Đốc Giáo Dục', route: 'help.education', icon: '🎓' },
            { name: 'Quản lý Tài chính', route: 'help.finance', icon: '💰' },
        ]
    },
    {
        title: 'Sinh Hoạt Ban Ngành',
        links: [
            { name: '1. Thành Viên', route: 'help.departments.members', icon: '👥' },
            { name: '2. Điểm Danh', route: 'help.departments.attendance', icon: '✅' },
            { name: '3. Thăm Viếng', route: 'help.departments.visitation', icon: '❤️' },
            { name: '4. Phân Công', route: 'help.departments.assignments', icon: '📋' },
            { name: '5. Tài Chính Quỹ', route: 'help.departments.finance', icon: '💵' },
            { name: '6. Báo Cáo', route: 'help.departments.reports', icon: '📊' },
        ]
    },
    {
        title: 'Lãnh Đạo & CRM',
        links: [
            { name: 'Cổng Lãnh Đạo', route: 'help.leadership', icon: '📈' },
        ]
    },
    {
        title: 'Quản Trị Hệ Thống',
        links: [
            { name: 'System Admin', route: 'help.sysadmin', icon: '🛡️' },
        ]
    }
];
</script>
