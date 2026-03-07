<template>
  <div class="flex h-screen bg-gray-50 overflow-hidden font-sans text-gray-900">
    <aside 
      class="hidden overflow-y-auto bg-white border-r border-gray-200 md:flex flex-col flex-shrink-0 relative transition-all duration-300"
      :class="isSidebarCollapsed ? 'w-20' : 'w-64'"
    >
      <div class="py-4 flex items-center justify-between px-4 border-b border-gray-200 shrink-0">
        <h2 v-if="!isSidebarCollapsed" class="text-xl font-black text-gray-800 tracking-tight whitespace-nowrap overflow-hidden">CMS<span class="text-blue-600">HT</span></h2>
        <h2 v-else class="text-xl font-black text-blue-600 tracking-tight mx-auto">C<span class="text-gray-800">H</span></h2>
        
        <button @click="toggleSidebar" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors" :class="isSidebarCollapsed ? 'mx-auto' : ''">
          <svg v-if="!isSidebarCollapsed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </button>
      </div>

      <nav class="flex-1 p-3 space-y-1.5 overflow-y-auto hide-scrollbar">
        <Link :href="route('dashboard')" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl font-bold transition-all group" :class="route().current('dashboard') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50'">
          <svg class="w-5 h-5 shrink-0" :class="route().current('dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Tổng quan</span>
        </Link>
        <Link :href="route('members.index')" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl font-bold transition-all group" :class="route().current('members.*') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50'">
          <svg class="w-5 h-5 shrink-0" :class="route().current('members.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
          <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Tín hữu</span>
        </Link>
        <!-- Ban ngành Accordion -->
        <div class="space-y-1">
          <button @click="toggleDeptsMenu" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-bold transition-all group" :class="(route().current('portal.*') || route().current('ministry.*') || route().current('deacon.*')) ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50'">
             <div class="flex items-center space-x-3">
               <svg class="w-5 h-5 shrink-0" :class="(route().current('portal.*') || route().current('ministry.*')) ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
               <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Ban ngành</span>
             </div>
             <svg v-if="!isSidebarCollapsed" class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="isDeptsMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>
          
          <div v-show="!isSidebarCollapsed && isDeptsMenuOpen" class="pl-11 pr-3 py-1.5 space-y-1">
             <Link :href="route('portal.index')" class="flex items-center px-3 py-2 text-sm font-bold rounded-lg transition-colors" :class="route().current('portal.*') ? 'text-blue-700 bg-blue-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'">
               Sinh hoạt
             </Link>
             <Link v-if="route().has('ministry.index')" :href="route('ministry.index')" class="flex items-center px-3 py-2 text-sm font-bold rounded-lg transition-colors" :class="route().current('ministry.*') ? 'text-blue-700 bg-blue-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'">
               Mục vụ
             </Link>
              <Link v-if="route().has('deacon.index')" :href="route('deacon.index')" class="flex items-center px-3 py-2 text-sm font-bold rounded-lg transition-colors" :class="route().current('deacon.*') ? 'text-amber-700 bg-amber-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'">
                Chấp sự
              </Link>
          </div>
        </div>

        <Link :href="route('finance.index')" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl font-bold transition-all group" :class="route().current('finance.*') ? 'bg-emerald-50 text-emerald-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50'">
          <svg class="w-5 h-5 shrink-0" :class="route().current('finance.*') ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Tài chính</span>
        </Link>

        <!-- Menu Hệ thống -->
        <div class="pt-4 mt-4 border-t border-gray-100">
          <p v-if="!isSidebarCollapsed" class="px-3 text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Cài đặt hệ thống</p>
          <div class="space-y-1">
            <button @click="toggleSettingsMenu" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-bold transition-all group" :class="(route().current('users.*') || route().current('roles.*') || route().current('meetings.*') || route().current('admin.users.*')) ? 'bg-indigo-50 text-indigo-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50'">
               <div class="flex items-center space-x-3">
                 <svg class="w-5 h-5 shrink-0" :class="(route().current('users.*') || route().current('roles.*') || route().current('meetings.*') || route().current('admin.users.*')) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                 <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Hệ thống</span>
               </div>
               <svg v-if="!isSidebarCollapsed" class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="isSettingsMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            
            <!-- Submenu Items -->
            <div v-show="!isSidebarCollapsed && isSettingsMenuOpen" class="pl-11 pr-3 py-1.5 space-y-1">
               <Link :href="route('users.index')" class="flex items-center px-3 py-2 text-sm font-bold rounded-lg transition-colors" :class="(route().current('users.*') || route().current('roles.*') || route().current('admin.users.permissions*')) ? 'text-indigo-700 bg-indigo-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'">
                 Quản trị Tài khoản
               </Link>
               <Link :href="route('speakers.index')" class="flex items-center px-3 py-2 text-sm font-bold rounded-lg transition-colors" :class="route().current('speakers.*') ? 'text-indigo-700 bg-indigo-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'">
                 Diễn giả
               </Link>
               <Link :href="route('departments.index')" class="flex items-center px-3 py-2 text-sm font-bold rounded-lg transition-colors" :class="route().current('departments.*') ? 'text-indigo-700 bg-indigo-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'">
                 Quản lý Ban ngành
               </Link>
               <Link :href="route('meetings.index')" class="flex items-center px-3 py-2 text-sm font-bold rounded-lg transition-colors" :class="route().current('meetings.*') ? 'text-indigo-700 bg-indigo-50/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50'">
                 Buổi nhóm
               </Link>
            </div>
          </div>
        </div>
      </nav>
      
      <!-- User profile at bottom of sidebar (optional) -->
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
              <Link v-if="page.props.auth?.user?.home_portal" :href="page.props.auth.user.home_portal" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors border-b border-gray-100">
                Vào Cổng Nội Bộ
              </Link>
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
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
const page = usePage();

const isSidebarCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');
const isSettingsMenuOpen = ref(route().current('users.*') || route().current('roles.*') || route().current('meetings.*') || route().current('departments.*') || route().current('admin.users.*'));
const isDeptsMenuOpen = ref(route().current('portal.*') || route().current('ministry.*') || route().current('deacon.*') || false);

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    localStorage.setItem('sidebarCollapsed', isSidebarCollapsed.value);
    if (isSidebarCollapsed.value) {
        isSettingsMenuOpen.value = false;
        isDeptsMenuOpen.value = false;
    }
};

const toggleDeptsMenu = () => {
    if (isSidebarCollapsed.value) {
        isSidebarCollapsed.value = false;
        localStorage.setItem('sidebarCollapsed', false);
    }
    isDeptsMenuOpen.value = !isDeptsMenuOpen.value;
};

const toggleSettingsMenu = () => {
    if (isSidebarCollapsed.value) {
        isSidebarCollapsed.value = false;
        localStorage.setItem('sidebarCollapsed', false);
    }
    isSettingsMenuOpen.value = !isSettingsMenuOpen.value;
};

</script>

