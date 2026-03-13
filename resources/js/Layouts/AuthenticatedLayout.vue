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

      <nav class="flex-1 p-3 space-y-1 overflow-y-auto hide-scrollbar">
        <!-- 1. TỔNG QUAN -->
        <Link :href="route('dashboard')" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl font-bold transition-all group" :class="route().current('dashboard') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50'">
          <svg class="w-5 h-5 shrink-0" :class="route().current('dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Tổng Quan</span>
        </Link>

        <!-- 2. TÍN HỮU (Hierarchical) -->
        <div class="space-y-1">
          <button @click="toggleBelieversMenu" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-bold transition-all group" :class="isBelieversMenuOpen ? 'text-blue-700 bg-blue-50/30' : 'text-gray-600 hover:bg-gray-50'">
            <div class="flex items-center space-x-3">
              <svg class="w-5 h-5 shrink-0" :class="isBelieversMenuOpen ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Tín hữu</span>
            </div>
            <svg v-if="!isSidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="isBelieversMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>
          
          <div v-if="isBelieversMenuOpen && !isSidebarCollapsed" class="pl-11 space-y-1 pb-1">
            <Link :href="route('members.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('members.index') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700'">Quản lý tín hữu</Link>
            <Link :href="route('admin.visitors.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('admin.visitors.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700'">Quản lý Thân Hữu</Link>
            <Link :href="route('care.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('care.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700'">Chăm sóc & Góp ý</Link>
          </div>
        </div>

        <!-- 3. BAN NGÀNH (Hierarchical) -->
        <div class="space-y-1">
          <button @click="toggleDeptsMenu" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-bold transition-all group" :class="isDeptsMenuOpen ? 'text-indigo-700 bg-indigo-50/30' : 'text-gray-600 hover:bg-gray-50'">
            <div class="flex items-center space-x-3">
              <svg class="w-5 h-5 shrink-0" :class="isDeptsMenuOpen ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Ban Ngành</span>
            </div>
            <svg v-if="!isSidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="isDeptsMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>
          
          <div v-if="isDeptsMenuOpen && !isSidebarCollapsed" class="pl-11 space-y-1 pb-1">
            <Link :href="route('portal.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('portal.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700'">Ban Ngành Sinh Hoạt</Link>
            <Link v-if="route().has('ministry.index')" :href="route('ministry.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('ministry.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700'">Ban Ngành Mục Vụ</Link>
            <Link v-if="route().has('deacon.index')" :href="route('deacon.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('deacon.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700'">Cổng Chấp Sự</Link>
            <Link :href="route('departments.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('departments.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700'">Quản lý Ban ngành</Link>
          </div>
        </div>

        <!-- 4. SỰ KIỆN (Hierarchical) -->
        <div class="space-y-1">
          <button @click="toggleEventsMenu" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-bold transition-all group" :class="isEventsMenuOpen ? 'text-orange-700 bg-orange-50/30' : 'text-gray-600 hover:bg-gray-50'">
            <div class="flex items-center space-x-3">
              <svg class="w-5 h-5 shrink-0" :class="isEventsMenuOpen ? 'text-orange-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Sự Kiện</span>
            </div>
            <svg v-if="!isSidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="isEventsMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>
          
          <div v-if="isEventsMenuOpen && !isSidebarCollapsed" class="pl-11 space-y-1 pb-1">
            <Link :href="route('calendar.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('calendar.*') ? 'text-orange-600' : 'text-gray-500 hover:text-gray-700'">Lịch Sự Kiện</Link>
            <Link :href="route('meetings.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('meetings.*') ? 'text-orange-600' : 'text-gray-500 hover:text-gray-700'">Buổi nhóm</Link>
            <Link :href="route('duty-rooster.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('duty-rooster.*') ? 'text-orange-600' : 'text-gray-500 hover:text-gray-700'">Phân công Lịch trực</Link>
          </div>
        </div>

        <!-- 5. QUẢN TRỊ HỆ THỐNG (Hierarchical) -->
        <div class="space-y-1">
          <button @click="toggleSystemMenu" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-bold transition-all group" :class="isSystemMenuOpen ? 'text-slate-800 bg-slate-100' : 'text-gray-600 hover:bg-gray-50'">
            <div class="flex items-center space-x-3">
              <svg class="w-5 h-5 shrink-0" :class="isSystemMenuOpen ? 'text-slate-800' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Quản trị Hệ thống</span>
            </div>
            <svg v-if="!isSidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="isSystemMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>
          
          <div v-if="isSystemMenuOpen && !isSidebarCollapsed" class="pl-11 space-y-1 pb-1">
            <Link :href="route('users.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('users.*') ? 'text-slate-800' : 'text-gray-500 hover:text-gray-700'">Tài khoản</Link>
            <Link :href="route('roles.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('roles.*') ? 'text-slate-800' : 'text-gray-500 hover:text-gray-700'">Chức vụ</Link>
            <Link :href="route('admin.features.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('admin.features.*') ? 'text-slate-800' : 'text-gray-500 hover:text-gray-700'">Tính năng</Link>
            <Link :href="route('admin.users.permissions')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('admin.users.permissions*') ? 'text-slate-800' : 'text-gray-500 hover:text-gray-700'">Phân quyền</Link>
          </div>
        </div>

        <!-- 6. CÀI ĐẶT HỆ THỐNG (Hierarchical) -->
        <div class="space-y-1">
          <button @click="toggleSettingsMenu" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-bold transition-all group" :class="isSettingsMenuOpen ? 'text-slate-800 bg-slate-100' : 'text-gray-600 hover:bg-gray-50'">
            <div class="flex items-center space-x-3">
              <svg class="w-5 h-5 shrink-0" :class="isSettingsMenuOpen ? 'text-slate-800' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Cài đặt hệ thống</span>
            </div>
            <svg v-if="!isSidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="isSettingsMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>
          
          <div v-if="isSettingsMenuOpen && !isSidebarCollapsed" class="pl-11 space-y-1 pb-1">
            <Link :href="route('speakers.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('speakers.*') ? 'text-slate-800' : 'text-gray-500 hover:text-gray-700'">Diễn giả</Link>
            <Link :href="route('member.portal.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('member.portal.*') ? 'text-slate-800' : 'text-gray-500 hover:text-gray-700'">Hồ Sơ Của Tôi</Link>
            <Link :href="route('admin.activity.logs')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('admin.activity.logs*') ? 'text-slate-800' : 'text-gray-500 hover:text-gray-700'">Nhật ký hoạt động</Link>
          </div>
        </div>

        <!-- FLAT ITEMS CONTINUED -->
        <!-- 4. TRUYỀN THÔNG (Gộp nhóm thông báo) -->
        <div class="space-y-1">
          <button @click="toggleCommunicationsMenu" class="w-full flex items-center justify-between px-3 py-2.5 mt-2 rounded-xl font-bold transition-all group" :class="isCommunicationsMenuOpen ? 'text-amber-700 bg-amber-50/30' : 'text-gray-600 hover:bg-gray-50'">
            <div class="flex items-center space-x-3">
              <svg class="w-5 h-5 shrink-0" :class="isCommunicationsMenuOpen ? 'text-amber-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
              <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Truyền Thông</span>
            </div>
            <svg v-if="!isSidebarCollapsed" class="w-4 h-4 transition-transform duration-200" :class="isCommunicationsMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>
          
          <div v-if="isCommunicationsMenuOpen && !isSidebarCollapsed" class="pl-11 space-y-1 pb-1">
            <Link :href="route('notifications.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('notifications.*') ? 'text-amber-700' : 'text-gray-500 hover:text-gray-700'">Hộp thư cá nhân</Link>
            <Link :href="route('admin.announcements.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('admin.announcements.*') ? 'text-amber-700' : 'text-gray-500 hover:text-gray-700'">Đăng Bản tin nội bộ</Link>
            <Link :href="route('admin.broadcasts.index')" class="block py-2 text-sm font-medium transition-colors" :class="route().current('admin.broadcasts.*') ? 'text-amber-700' : 'text-gray-500 hover:text-gray-700'">Tin nhắn tự động (SMS)</Link>
          </div>
        </div>
        <Link :href="route('finance.index')" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl font-bold transition-all group" :class="route().current('finance.*') ? 'bg-emerald-50 text-emerald-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50'">
          <svg class="w-5 h-5 shrink-0" :class="route().current('finance.*') ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Tài chính</span>
        </Link>
        <Link :href="route('admin.assets.index')" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl font-bold transition-all group" :class="route().current('admin.assets.*') ? 'bg-emerald-50 text-emerald-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50'">
          <svg class="w-5 h-5 shrink-0" :class="route().current('admin.assets.*') ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
          <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Quản lý thiết bị</span>
        </Link>
        <Link :href="route('documents.index')" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl font-bold transition-all group" :class="route().current('documents.*') ? 'bg-emerald-50 text-emerald-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50'">
          <svg class="w-5 h-5 shrink-0" :class="route().current('documents.*') ? 'text-emerald-600' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
          <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Quản lý tài liệu Tài Liệu</span>
        </Link>
        <Link :href="route('help.install')" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl font-bold transition-all group text-emerald-600 hover:bg-emerald-50">
          <svg class="w-5 h-5 shrink-0 text-emerald-500 group-hover:text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <span v-if="!isSidebarCollapsed" class="whitespace-nowrap">Hướng dẫn / Tài liệu</span>
        </Link>
      </nav>

      <!-- User profile at bottom of sidebar (optional) -->
    </aside>

    <!-- Mobile Navigation Drawer Overlay -->
    <transition
      enter-active-class="transition-opacity ease-linear duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-linear duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-show="isMobileMenuOpen" class="fixed inset-0 z-40 flex sm:hidden">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 blur-sm transition-opacity" @click="isMobileMenuOpen = false"></div>
        <transition
          enter-active-class="transition ease-in-out duration-300 transform"
          enter-from-class="-translate-x-full"
          enter-to-class="translate-x-0"
          leave-active-class="transition ease-in-out duration-300 transform"
          leave-from-class="translate-x-0"
          leave-to-class="-translate-x-full"
        >
          <div v-show="isMobileMenuOpen" class="relative flex 1 w-full max-w-xs flex-col bg-white pt-5 pb-4 shadow-2xl">
            <div class="absolute top-0 right-0 -mr-12 pt-2">
              <button @click="isMobileMenuOpen = false" class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white bg-slate-800/50 backdrop-blur-md text-white">
                <span class="sr-only">Close sidebar</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div class="flex flex-shrink-0 items-center px-4 mb-4">
               <h2 class="text-2xl font-black text-gray-800 tracking-tight">CMS<span class="text-blue-600">HT</span></h2>
            </div>
            <!-- Duplicated Nav Menu for Mobile -->
            <div class="h-0 flex-1 overflow-y-auto hide-scrollbar px-3 space-y-1 pb-6">
               <div class="bg-gray-50/50 rounded-xl p-3 border border-gray-100/50 mb-4">
                   <p class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Điều hướng CMS</p>
               </div>
               
               <!-- 1. TRANG CHỦ -->
               <Link :href="route('dashboard')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1 bg-gray-50">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                 Tổng Quan
               </Link>

               <!-- 2. TÍN HỮU -->
               <p class="px-3 text-[10px] font-black text-gray-400 uppercase tracking-widest mt-4 mb-2">Tín Hữu</p>
               <Link :href="route('members.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                 Danh sách Tín hữu
               </Link>
               <Link :href="route('admin.visitors.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                 Thân hữu
               </Link>
               <Link :href="route('care.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                 Chăm sóc & Góp ý
               </Link>

               <!-- 3. BAN NGÀNH -->
               <p class="px-3 text-[10px] font-black text-gray-400 uppercase tracking-widest mt-4 mb-2">Ban Ngành</p>
               <Link :href="route('portal.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                 Cổng Sinh hoạt
               </Link>
               <Link v-if="route().has('ministry.index')" :href="route('ministry.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                 Cổng Mục vụ
               </Link>
               <Link v-if="route().has('deacon.index')" :href="route('deacon.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                 Cổng Chấp sự
               </Link>
               <Link :href="route('departments.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                 Quản lý Cấu hình
               </Link>

               <!-- 4. SỰ KIỆN -->
               <p class="px-3 text-[10px] font-black text-gray-400 uppercase tracking-widest mt-4 mb-2">Sự Kiện</p>
               <Link :href="route('calendar.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                 Lịch Sự Kiện
               </Link>
               <Link :href="route('meetings.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                 Buổi nhóm
               </Link>
               <Link :href="route('speakers.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                 Diễn giả
               </Link>
               <Link :href="route('admin.broadcasts.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                 CD Truyền thông
               </Link>

               <!-- 5. VẬN HÀNH -->
               <p class="px-3 text-[10px] font-black text-gray-400 uppercase tracking-widest mt-4 mb-2">Vận Hành</p>
               <Link :href="route('finance.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 Tài chính Ban Ngành
               </Link>
               <Link :href="route('admin.donations.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 Tài chính Dâng Hiến
               </Link>
               <Link :href="route('duty-rooster.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                 Phân công
               </Link>
               <Link :href="route('admin.assets.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                 Cơ sở vật chất
               </Link>
               <Link :href="route('documents.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                 Kho Tài Liệu
               </Link>
               <Link :href="route('help.install')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 Hướng dẫn / Tài liệu
               </Link>

               <!-- 6. HỆ THỐNG -->
               <p class="px-3 text-[10px] font-black text-gray-400 uppercase tracking-widest mt-4 mb-2">Hệ Thống</p>
               <Link :href="route('users.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                 Tài khoản
               </Link>
               <Link :href="route('roles.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                 Chức vụ
               </Link>
               <Link :href="route('admin.features.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                 Tính năng
               </Link>
               <Link :href="route('admin.users.permissions')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                 Phân quyền
               </Link>
               <Link :href="route('member.portal.index')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                 Hồ Sơ Của Tôi
               </Link>
               <Link :href="route('admin.activity.logs')" @click="isMobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-bold transition-all text-gray-700 hover:bg-gray-50 mb-1">
                 <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                 Nhật ký hệ thống
               </Link>
            </div>
          </div>
        </transition>
        <div class="w-14 flex-shrink-0" aria-hidden="true"></div>
      </div>
    </transition>

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col overflow-hidden relative">
      <!-- Topbar -->
      <header class="flex items-center justify-between px-6 py-3 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm z-10 sticky top-0">
        <div class="flex items-center sm:hidden">
          <button @click="isMobileMenuOpen = true" class="text-gray-500 focus:outline-none focus:text-indigo-600 hover:bg-gray-100 p-2 rounded-lg transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
          </button>
          <h2 class="ml-2 text-lg font-black text-gray-800 md:hidden">CMS<span class="text-blue-600">HT</span></h2>
        </div>
        <div class="hidden sm:block">
          <!-- Page title placeholder -->
          <h1 class="text-xl font-semibold text-gray-800">
             <slot name="header"></slot>
          </h1>
        </div>
          <div class="flex items-center space-x-4">
            <!-- Command Palette Shortcut Hint -->
            <button @click="openCommandPalette" class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-500 hover:bg-gray-100 transition-colors">
               <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
               <span>Tìm kiếm...</span>
               <span class="ml-2 text-xs font-bold text-gray-400 px-1.5 py-0.5 border border-gray-200 rounded bg-white">Ctrl K</span>
            </button>

            <NotificationDropdown />

            <!-- User Profile info -->
            <div class="text-sm font-medium text-gray-700 hidden sm:block text-right">
              <span class="block">Xin chào, {{ page.props.auth.user?.name || 'Guest' }}</span>
              <span class="block text-xs text-gray-500">{{ getRoleLabel(page.props.auth.user?.role) || '' }}</span>
            </div>
            <div class="relative group">
              <button class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center border border-gray-200 hover:bg-gray-200 transition-colors focus:outline-none overflow-hidden">
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
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 px-4 sm:px-6 lg:px-8 py-6">
        <!-- Flash message -->
        <div v-if="page.props.flash.message" class="mb-4 bg-blue-100 border border-blue-200 text-blue-700 px-4 py-3 rounded relative shadow-sm" role="alert">
          <span class="block sm:inline">{{ page.props.flash.message }}</span>
        </div>

        <slot />
      </main>
    </div>

    <!-- Command Palette Modal -->
    <CommandPalette :show="isCommandPaletteOpen" @close="isCommandPaletteOpen = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';
import CommandPalette from '@/Components/CommandPalette.vue';
import { getRoleLabel } from '@/utils/roleHelper';

const page = usePage();

const isMobileMenuOpen = ref(false);
const isCommandPaletteOpen = ref(false);
const openCommandPalette = () => { isCommandPaletteOpen.value = true; };

// Listen for Ctrl+K
const handleKeydown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        isCommandPaletteOpen.value = true;
    }
};

onMounted(() => { window.addEventListener('keydown', handleKeydown); });
onUnmounted(() => { window.removeEventListener('keydown', handleKeydown); });


const isSidebarCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');
const isDeptsMenuOpen = ref(route().current('portal.*') || route().current('ministry.*') || route().current('deacon.*') || route().current('departments.*'));
const isBelieversMenuOpen = ref(route().current('members.*') || route().current('admin.visitors.*') || route().current('care.*'));
const isEventsMenuOpen = ref(route().current('calendar.*') || route().current('meetings.*') || route().current('duty-rooster.*'));
const isSystemMenuOpen = ref(route().current('users.*') || route().current('roles.*') || route().current('admin.features.*') || route().current('admin.users.permissions*'));
const isSettingsMenuOpen = ref(route().current('speakers.*') || route().current('member.portal.*') || route().current('admin.activity.logs*'));
const isCommunicationsMenuOpen = ref(route().current('admin.announcements.*') || route().current('notifications.*') || route().current('admin.broadcasts.*'));

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    localStorage.setItem('sidebarCollapsed', isSidebarCollapsed.value);
    if (isSidebarCollapsed.value) {
        isDeptsMenuOpen.value = false;
        isBelieversMenuOpen.value = false;
        isEventsMenuOpen.value = false;
        isSettingsMenuOpen.value = false;
        isSystemMenuOpen.value = false;
        isCommunicationsMenuOpen.value = false; // Added this line
    }
};

const toggleDeptsMenu = () => {
    if (isSidebarCollapsed.value) {
        isSidebarCollapsed.value = false;
        localStorage.setItem('sidebarCollapsed', false);
    }
    isDeptsMenuOpen.value = !isDeptsMenuOpen.value;
    if (isDeptsMenuOpen.value) {
        isBelieversMenuOpen.value = false;
        isEventsMenuOpen.value = false;
        isSettingsMenuOpen.value = false;
        isSystemMenuOpen.value = false;
        isCommunicationsMenuOpen.value = false; // Added this line
    }
};

const toggleCommunicationsMenu = () => {
    if (isSidebarCollapsed.value) {
        isSidebarCollapsed.value = false;
        localStorage.setItem('sidebarCollapsed', false);
    }
    isCommunicationsMenuOpen.value = !isCommunicationsMenuOpen.value;
    if (isCommunicationsMenuOpen.value) {
        isBelieversMenuOpen.value = false;
        isDeptsMenuOpen.value = false;
        isEventsMenuOpen.value = false;
        isSystemMenuOpen.value = false;
        isSettingsMenuOpen.value = false;
    }
};

const toggleBelieversMenu = () => {
    if (isSidebarCollapsed.value) {
        isSidebarCollapsed.value = false;
        localStorage.setItem('sidebarCollapsed', false);
    }
    isBelieversMenuOpen.value = !isBelieversMenuOpen.value;
    if (isBelieversMenuOpen.value) {
        isDeptsMenuOpen.value = false;
        isEventsMenuOpen.value = false;
        isSettingsMenuOpen.value = false;
        isSystemMenuOpen.value = false;
        isCommunicationsMenuOpen.value = false;
    }
};

const toggleEventsMenu = () => {
    if (isSidebarCollapsed.value) {
        isSidebarCollapsed.value = false;
        localStorage.setItem('sidebarCollapsed', false);
    }
    isEventsMenuOpen.value = !isEventsMenuOpen.value;
    if (isEventsMenuOpen.value) {
        isBelieversMenuOpen.value = false;
        isDeptsMenuOpen.value = false;
        isSettingsMenuOpen.value = false;
        isSystemMenuOpen.value = false;
        isCommunicationsMenuOpen.value = false;
    }
};

const toggleSystemMenu = () => {
    if (isSidebarCollapsed.value) {
        isSidebarCollapsed.value = false;
        localStorage.setItem('sidebarCollapsed', false);
    }
    isSystemMenuOpen.value = !isSystemMenuOpen.value;
    if (isSystemMenuOpen.value) {
        isBelieversMenuOpen.value = false;
        isDeptsMenuOpen.value = false;
        isEventsMenuOpen.value = false;
        isSettingsMenuOpen.value = false;
        isCommunicationsMenuOpen.value = false;
    }
};

const toggleSettingsMenu = () => {
    if (isSidebarCollapsed.value) {
        isSidebarCollapsed.value = false;
        localStorage.setItem('sidebarCollapsed', false);
    }
    isSettingsMenuOpen.value = !isSettingsMenuOpen.value;
    if (isSettingsMenuOpen.value) {
        isBelieversMenuOpen.value = false;
        isDeptsMenuOpen.value = false;
        isEventsMenuOpen.value = false;
        isSystemMenuOpen.value = false;
        isCommunicationsMenuOpen.value = false;
    }
};

</script>

