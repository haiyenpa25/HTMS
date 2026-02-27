<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden font-sans text-gray-900">
    <!-- Sidebar / Desktop -->
    <aside class="hidden w-64 overflow-y-auto bg-white border-r border-gray-200 md:block flex-shrink-0 relative">
      <div class="py-4 text-center border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800 tracking-tight">CMS<span class="text-blue-600">HT</span></h2>
      </div>
      <nav class="p-4 space-y-1">
        <Link :href="route('dashboard')" class="flex items-center space-x-2 px-3 py-2 rounded-md font-medium transition-colors" :class="route().current('dashboard') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100'">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          <span>Tổng quan</span>
        </Link>
        <Link :href="route('members.index')" class="flex items-center space-x-2 px-3 py-2 rounded-md font-medium transition-colors" :class="route().current('members.*') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100'">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
          <span>Tín hữu</span>
        </Link>
        <Link :href="route('departments.index')" class="flex items-center space-x-2 px-3 py-2 rounded-md font-medium transition-colors" :class="route().current('departments.*') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100'">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
          <span>Ban ngành</span>
        </Link>
        <a href="#" class="flex items-center space-x-2 px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-md font-medium transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          <span>Hồ sơ & Đơn</span>
          <span v-if="page.props.pending_approvals_count > 0" class="ml-auto inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-800">{{ page.props.pending_approvals_count }}</span>
        </a>

        <!-- Menu Hệ thống (Chỉ hiện khi có quyền quản trị, nhưng để demo cứ hiện trước) -->
        <div class="pt-4 mt-4 border-t border-gray-100">
          <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Cài đặt hệ thống</p>
          <div class="space-y-1">
            <Link :href="route('users.index')" class="flex items-center space-x-2 px-3 py-2 rounded-md font-medium transition-colors" :class="route().current('users.*') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
              <span>Quản lý Người dùng</span>
            </Link>
            <Link :href="route('roles.index')" class="flex items-center space-x-2 px-3 py-2 rounded-md font-medium transition-colors" :class="route().current('roles.*') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-100'">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
              <span>Phân quyền</span>
            </Link>
          </div>
        </div>
      </nav>
    </aside>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Topbar -->
      <header class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-100 shadow-sm z-10">
        <div class="flex items-center sm:hidden">
          <button class="text-gray-500 focus:outline-none focus:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
          </button>
          <h2 class="ml-3 text-lg font-bold text-gray-800 md:hidden">CMS<span class="text-blue-600">HT</span></h2>
        </div>
        <div class="hidden sm:block">
          <!-- Page title placeholder -->
          <h1 class="text-xl font-semibold text-gray-800">
             <slot name="header"></slot>
          </h1>
        </div>
        <div class="flex items-center space-x-4">
          <!-- User Profile info -->
          <div class="text-sm font-medium text-gray-700 hidden sm:block text-right">
            <span class="block">Xin chào, {{ page.props.auth.user?.name || 'Guest' }}</span>
            <span class="block text-xs text-gray-500">{{ page.props.auth.user?.role || '' }}</span>
          </div>
          <div class="relative group">
            <button class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center border border-gray-200 hover:bg-gray-200 transition-colors focus:outline-none">
              <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
            </button>
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
              <Link :href="route('logout')" method="post" as="button" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors">
                Đăng xuất
              </Link>
            </div>
          </div>
        </div>
      </header>
      
      <!-- Content Area -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
        <!-- Flash message -->
        <div v-if="page.props.flash.message" class="mb-4 bg-blue-100 border border-blue-200 text-blue-700 px-4 py-3 rounded relative shadow-sm" role="alert">
          <span class="block sm:inline">{{ page.props.flash.message }}</span>
        </div>

        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
const page = usePage();
</script>
