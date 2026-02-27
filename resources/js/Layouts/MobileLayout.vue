<template>
  <div class="flex flex-col h-screen bg-gray-50 font-sans text-gray-900 overflow-hidden relative pb-16">
    <!-- Top Header -->
    <header class="bg-white shadow-sm border-b border-gray-100 px-4 py-3 flex items-center justify-between z-10 sticky top-0">
      <h1 class="text-lg font-bold text-gray-800 truncate">
        <slot name="header">CMS<span class="text-blue-600">HT</span></slot>
      </h1>
      <div class="flex items-center space-x-3 text-sm">
        <div class="relative">
          <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
          <span v-if="page.props.pending_approvals_count > 0" class="absolute top-0 right-0 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
        </div>
        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center border border-gray-300">
           <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto p-4 w-full max-w-lg mx-auto scroll-smooth">
      <div v-if="page.props.flash.message" class="mb-4 bg-blue-100 text-blue-800 text-xs px-3 py-2 rounded-md shadow-sm">
        {{ page.props.flash.message }}
      </div>
      <slot />
    </main>

    <!-- Bottom Navigation (Mobile Focus) -->
    <nav class="bg-white border-t border-gray-200 fixed bottom-0 w-full z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] pb-safe-area-inset">
      <div class="flex justify-around items-center h-16 w-full max-w-md mx-auto">
        
        <!-- Tab 1: Home -->
        <Link :href="route('dashboard')" class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors" :class="route().current('dashboard') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900'">
          <svg class="w-6 h-6" :fill="route().current('dashboard') ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          <span class="text-[10px] font-medium">Trang chủ</span>
        </Link>

        <!-- Tab 2: Profile/My Info -->
        <Link :href="route('members.index')" class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors" :class="route().current('members.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900'">
          <svg class="w-6 h-6" :fill="route().current('members.*') ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
          </svg>
          <span class="text-[10px] font-medium">Tín hữu</span>
        </Link>

        <!-- Tab 2: Departments -->
        <Link :href="route('departments.index')" class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors" :class="route().current('departments.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900'">
          <svg class="w-6 h-6" :fill="route().current('departments.*') ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
          <span class="text-[10px] font-medium">Ban ngành</span>
        </Link>

        <!-- Tab 3: Action/Forms -->
        <a href="#" class="flex flex-col items-center justify-center w-full h-full space-y-1 relative text-gray-500 hover:text-gray-900 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <span class="text-[10px] font-medium">Đơn từ</span>
          <span v-if="page.props.pending_approvals_count > 0" class="absolute top-1 right-2 inline-flex items-center justify-center px-1.5 py-0.5 text-[8px] font-bold text-white bg-red-500 rounded-full">{{ page.props.pending_approvals_count }}</span>
        </a>

        <!-- Tab 4: Menu / Logout -->
        <Link :href="route('logout')" method="post" as="button" class="flex flex-col items-center justify-center w-full h-full space-y-1 text-gray-500 hover:text-red-600 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0v-1m6-10V7a3 3 0 00-6 0v1"></path>
          </svg>
          <span class="text-[10px] font-medium">Đăng xuất</span>
        </Link>

      </div>
    </nav>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
const page = usePage();
</script>

<style>
/* CSS cho notch Safari Ios Devices */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
  .pb-safe-area-inset {
    padding-bottom: env(safe-area-inset-bottom);
  }
}
</style>
