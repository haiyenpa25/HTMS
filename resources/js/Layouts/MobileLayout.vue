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

    <!-- Sub-menu: Tín Hữu -->
    <transition name="slide-up">
      <div v-if="openMenu === 'members'" class="fixed bottom-16 left-0 right-0 z-40 mx-auto max-w-md px-3 pb-2">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
          <div class="px-4 py-3 bg-blue-600">
            <p class="text-xs font-black text-blue-200 uppercase tracking-wider">Tín Hữu</p>
          </div>
          <Link :href="route('members.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-blue-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Hồ sơ Tín hữu</p>
              <p class="text-xs text-gray-400">Danh sách thành viên, Báp-tem</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('admin.visitors.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-emerald-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Thân Hữu <span class="text-[10px] bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded ml-1 font-bold"></span></p>
              <p class="text-xs text-gray-400">Quản lý người mới đến</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('care.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-rose-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-rose-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Chăm sóc <span class="text-[10px] bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded ml-1 font-bold"></span></p>
              <p class="text-xs text-gray-400">Theo dõi vòng đời tín hữu</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <a href="#" @click.prevent="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-amber-50 transition-colors">
            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Phiếu Yêu Cầu <span class="text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded ml-1 font-bold">MỚI</span></p>
              <p class="text-xs text-gray-400">Cầu nguyện, xin thăm viếng (Ticketing)</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </a>
        </div>
      </div>
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
              <p class="text-xs text-gray-400">Điểm danh, Báo cáo, Tài chính Ban</p>
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
              <p class="text-xs text-gray-400">Thăm viếng, Giáo dục (TCN)</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('deacon.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-amber-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Chấp Sự</p>
              <p class="text-xs text-gray-400">Thư Ký & Thủ Quỹ Hội Thánh</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <a href="#" @click.prevent="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-green-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Tổ / Nhóm <span class="text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded ml-1 font-bold">MỚI</span></p>
              <p class="text-xs text-gray-400">Quản lý hệ thống Cell Group</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </a>
        </div>
      </div>
    </transition>

    <!-- Sub-menu: Sự Kiện (TAB MỚI) -->
    <transition name="slide-up">
      <div v-if="openMenu === 'events'" class="fixed bottom-16 left-0 right-0 z-40 mx-auto max-w-md px-3 pb-2">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
          <div class="px-4 py-3 bg-fuchsia-600">
            <p class="text-xs font-black text-fuchsia-200 uppercase tracking-wider">Sự Kiện</p>
          </div>
          <Link :href="route('calendar.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-fuchsia-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-fuchsia-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-fuchsia-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Lịch Hội Thánh <span class="text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded ml-1 font-bold"></span></p>
              <p class="text-xs text-gray-400">Lịch tổng quát (Unified Calendar)</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('meetings.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-rose-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-rose-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Buổi Nhóm</p>
              <p class="text-xs text-gray-400">Quản lý chương trình thờ phượng</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('duty-rooster.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-orange-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-orange-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Phân Công (Roster)</p>
              <p class="text-xs text-gray-400">Xếp lịch hướng dẫn, phục vụ</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('speakers.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-teal-50 transition-colors">
            <div class="w-9 h-9 rounded-xl bg-teal-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Diễn Giả</p>
              <p class="text-xs text-gray-400">Tra cứu thông tin Mục sư/Diễn giả</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
        </div>
      </div>
    </transition>

    <!-- Sub-menu: Vận Hành (Cài Đặt cũ) -->
    <transition name="slide-up">
      <div v-if="openMenu === 'settings'" class="fixed bottom-16 left-0 right-0 z-40 mx-auto max-w-md px-3 pb-2">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
          <div class="px-4 py-3 bg-slate-700">
            <p class="text-xs font-black text-slate-300 uppercase tracking-wider">Vận Hành</p>
          </div>
          <Link :href="route('users.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Quản lý User</p>
              <p class="text-xs text-gray-400">Danh sách tài khoản & Phân quyền MAC</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('finance.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-emerald-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Tài Chính</p>
              <p class="text-xs text-gray-400">Sổ quỹ, Dâng hiến & Nhập sổ hàng loạt</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('admin.assets.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-stone-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-stone-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Thiết bị</p>
              <p class="text-xs text-gray-400">Quản lý thiết bị & Mượn/Trả</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('documents.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-cyan-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-cyan-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Tài Liệu</p>
              <p class="text-xs text-gray-400">Lưu trữ văn bản, file đám mây</p>
            </div>
            <svg class="w-4 h-4 text-gray-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </Link>
          <Link :href="route('admin.broadcasts.index')" @click="openMenu = null"
            class="flex items-center gap-3 px-4 py-3.5 hover:bg-violet-50 transition-colors border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-violet-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Truyền Thông <span class="text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded ml-1 font-bold"></span></p>
              <p class="text-xs text-gray-400">Gửi Email Broadcasting hàng loạt</p>
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
          <Link :href="route('member.portal.index')" @click="openMenu = null"
            class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-gray-50 transition-colors text-left border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Hồ sơ cá nhân</p>
              <p class="text-xs text-gray-400">Chỉnh avatar, mật khẩu, thông tin</p>
            </div>
          </Link>
          <Link v-if="isAdmin" :href="route('dashboard')" @click="openMenu = null"
            class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors text-left border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">Quản trị Hệ thống</p>
              <p class="text-xs text-gray-400">Trở về bảng điều khiển</p>
            </div>
          </Link>
          <Link v-if="page.props.auth?.user?.home_portal" :href="page.props.auth.user.home_portal" @click="openMenu = null"
            class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-blue-50 transition-colors text-left border-b border-gray-100">
            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-blue-600">Cổng Nội Bộ</p>
              <p class="text-xs text-gray-400">Chuyển đổi portal quản trị</p>
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

        <!-- Tab 2: Members (sub-menu toggle) -->
        <button @click="toggleMenu('members')"
          class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
          :class="route().current('members.*') || openMenu === 'members' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900'">
          <div class="relative">
            <svg class="w-6 h-6" :fill="route().current('members.*') ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span v-if="openMenu === 'members'" class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-blue-500"></span>
          </div>
          <span class="text-[10px] font-medium">Tín Hữu</span>
        </button>

        <!-- Tab 3: Ban Ngành (sub-menu toggle) -->
        <button @click="toggleMenu('department')"
          class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
          :class="(route().current('portal.*') || route().current('ministry.*') || route().current('deacon.*')) || openMenu === 'department' ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-900'">
          <div class="relative">
            <svg class="w-6 h-6" :fill="(route().current('portal.*') || route().current('ministry.*') || route().current('deacon.*')) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span v-if="openMenu === 'department'" class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-indigo-500"></span>
          </div>
          <span class="text-[10px] font-medium">Ban Ngành</span>
        </button>
        
        <!-- Tab 4: Sự Kiện (sub-menu toggle) -->
        <button @click="toggleMenu('events')"
          class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
          :class="openMenu === 'events' ? 'text-fuchsia-600' : 'text-gray-500 hover:text-gray-900'">
          <div class="relative">
            <svg class="w-6 h-6" :fill="openMenu === 'events' ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span v-if="openMenu === 'events'" class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-fuchsia-500"></span>
          </div>
          <span class="text-[10px] font-medium">Sự Kiện</span>
        </button>

        <!-- Tab 5: Settings (sub-menu toggle) -->
        <button @click="toggleMenu('settings')"
          class="flex flex-col items-center justify-center w-full h-full space-y-1 transition-colors"
          :class="openMenu === 'settings' || route().current('users.*') ? 'text-slate-700' : 'text-gray-500 hover:text-gray-900'">
          <div class="relative">
            <svg class="w-6 h-6" :fill="route().current('users.*') ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span v-if="openMenu === 'settings'" class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-slate-600"></span>
          </div>
          <span class="text-[10px] font-medium">Vận Hành</span>
        </button>

        <!-- Tab 6: Account (sub-menu toggle) -->
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
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const isAdmin = computed(() =>
  page.props.auth?.user?.roles?.some(r => ['Super_Admin', 'Pastor', 'BTS_Admin'].includes(r.name)) ?? false
);

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
