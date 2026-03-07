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
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto p-4 w-full max-w-lg mx-auto scroll-smooth">
      <div v-if="page.props.flash.message" class="mb-4 bg-blue-100 text-blue-800 text-xs px-3 py-2 rounded-md shadow-sm">
        {{ page.props.flash.message }}
      </div>
      <slot />
    </main>

    <!-- ── Sub-menus (pop-up panels above bottom nav) ────────────────────── -->

    <!-- Overlay backdrop -->
    <transition name="fade">
      <div v-if="openMenu" class="fixed inset-0 bg-black/30 z-30" @click="openMenu = null"></div>
    </transition>

    <!-- Sub-menu: Ban Ngành -->
    <transition name="slide-up">
      <div v-if="openMenu === 'department'" class="fixed bottom-16 left-0 right-0 z-40 mx-auto max-w-md px-3 pb-2">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
          <div class="px-4 py-3 bg-indigo-600">
            <p class="text-xs font-black text-indigo-200 uppercase tracking-wider">Ban Ngành</p>
          </div>
          <Link :href="route('portal.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-indigo-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Ban Ngành Sinh Hoạt</p>
              <p class="text-xs text-gray-400">Báo cáo, điểm danh, tài chính</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('ministry.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-purple-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Ban Ngành Mục Vụ</p>
              <p class="text-xs text-gray-400">Thăm viếng, giáo dục, mục vụ</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('deacon.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-amber-50 transition-colors">
            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Chấp Sự</p>
              <p class="text-xs text-gray-400">Thư Ký & Thủ Quỹ Hội Thánh</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
        </div>
      </div>
    </transition>

    <!-- Sub-menu: Cài Đặt -->
    <transition name="slide-up">
      <div v-if="openMenu === 'settings'" class="fixed bottom-16 left-0 right-0 z-40 mx-auto max-w-md px-3 pb-2">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
          <div class="px-4 py-3 bg-slate-700">
            <p class="text-xs font-black text-slate-300 uppercase tracking-wider">Cài Đặt</p>
          </div>
          <Link :href="route('users.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Quản lý User</p>
              <p class="text-xs text-gray-400">Danh sách tài khoản hệ thống</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('meetings.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Buổi Nhóm</p>
              <p class="text-xs text-gray-400">Quản lý lịch sinh hoạt Hội Thánh</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('speakers.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors">
            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Diễn Giả</p>
              <p class="text-xs text-gray-400">Quản lý danh sách diễn giả</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
        </div>
      </div>
    </transition>

    <!-- Sub-menu: Tài Khoản -->
    <transition name="slide-up">
      <div v-if="openMenu === 'account'" class="fixed bottom-16 left-0 right-0 z-40 mx-auto max-w-md px-3 pb-2">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
          <div class="px-4 py-3 bg-gray-800 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
            </div>
            <div>
              <p class="text-sm font-black text-white">{{ page.props.auth?.user?.name || 'Tài khoản' }}</p>
              <p class="text-xs text-gray-400">{{ page.props.auth?.user?.email || '' }}</p>
            </div>
          </div>
          <Link v-if="page.props.auth?.user?.home_portal" :href="page.props.auth.user.home_portal" @click="openMenu = null"
            class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-blue-50 transition-colors text-left border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-blue-600">Cổng Nội Bộ</p>
              <p class="text-xs text-gray-400">Vào khu vực quản lý</p>
            </div>
          </Link>
          <Link :href="route('logout')" method="post" as="button" @click="openMenu = null"
            class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-red-50 transition-colors text-left">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0v-1m6-10V7a3 3 0 00-6 0v1"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-red-600">Đăng Xuất</p>
              <p class="text-xs text-gray-400">Thoát khỏi tài khoản</p>
            </div>
          </Link>
        </div>
      </div>
    </transition>

    <!-- ── Bottom Navigation ────────────────────────────────────────── -->
    <nav class="bg-white border-t border-gray-200 fixed bottom-0 w-full z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] pb-safe-area-inset">
      <div class="flex justify-around items-center h-16 w-full max-w-md mx-auto">

        <!-- Tab 1: Home -->
        <Link :href="route('dashboard')"
          class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
          :class="route().current('dashboard') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900'">
          <svg class="w-6 h-6" :fill="route().current('dashboard') ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          <span class="text-[10px] font-medium">Trang chủ</span>
        </Link>

        <!-- Tab 2: Members -->
        <Link :href="route('members.index')"
          class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
          :class="route().current('members.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900'">
          <svg class="w-6 h-6" :fill="route().current('members.*') ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
          <span class="text-[10px] font-medium">Tín hữu</span>
        </Link>

        <!-- Tab 3: Ban Ngành (sub-menu toggle) -->
        <button @click="toggleMenu('department')"
          class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
          :class="(route().current('portal.*')) || openMenu === 'department' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-900'">
          <div class="relative">
            <svg class="w-6 h-6" :fill="route().current('portal.*') ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span v-if="openMenu === 'department'" class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-indigo-500"></span>
          </div>
          <span class="text-[10px] font-medium">Ban Ngành</span>
        </button>

        <!-- Tab 4: Settings (sub-menu toggle) -->
        <button @click="toggleMenu('settings')"
          class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
          :class="(route().current('meetings.*') || route().current('speakers.*')) || openMenu === 'settings' ? 'text-slate-700' : 'text-gray-500 hover:text-gray-900'">
          <div class="relative">
            <svg class="w-6 h-6" :fill="(route().current('meetings.*') || route().current('speakers.*')) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span v-if="openMenu === 'settings'" class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-slate-600"></span>
          </div>
          <span class="text-[10px] font-medium">Cài đặt</span>
        </button>

        <!-- Tab 5: Account (sub-menu toggle) -->
        <button @click="toggleMenu('account')"
          class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
          :class="openMenu === 'account' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-900'">
          <div class="relative">
            <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center border border-gray-300"
              :class="openMenu === 'account' ? 'ring-2 ring-gray-400 ring-offset-1' : ''">
              <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
            </div>
          </div>
          <span class="text-[10px] font-medium">Tài khoản</span>
        </button>

      </div>
    </nav>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

// Sub-menu state: null | 'department' | 'settings' | 'account'
const openMenu = ref(null);

const toggleMenu = (name) => {
  openMenu.value = openMenu.value === name ? null : name;
};
</script>

<style>
/* CSS cho notch Safari iOS Devices */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
  .pb-safe-area-inset {
    padding-bottom: env(safe-area-inset-bottom);
  }
}

/* Slide-up animation */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(16px);
  opacity: 0;
}

/* Fade for backdrop */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
