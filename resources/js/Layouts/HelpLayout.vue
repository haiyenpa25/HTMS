<template>
  <div class="min-h-screen bg-slate-50 font-sans text-slate-900 pb-20 flex flex-col">
    <!-- Top Header -->
    <header class="bg-indigo-700 text-white shadow-lg relative overflow-hidden shrink-0">
      <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-indigo-900/30 rounded-full blur-2xl"></div>
      
      <div class="max-w-7xl mx-auto px-6 py-8 relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <Link href="/" class="inline-flex items-center text-indigo-100 hover:text-white mb-2 transition-colors text-sm font-bold uppercase tracking-wider">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            CMSHT V2.0
          </Link>
          <h1 class="text-3xl md:text-4xl font-black tracking-tight drop-shadow-sm">Trung Tâm Hướng Dẫn</h1>
          <p class="text-indigo-100 mt-2 max-w-xl text-sm leading-relaxed">
            Tài liệu chính thức dành cho Quản trị viên và Tín hữu thuộc nền tảng CMS Hội Thánh Tin Lành.
          </p>
        </div>
        
        <div class="hidden md:block">
           <svg class="w-24 h-24 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
        </div>
      </div>
    </header>

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 mt-8 flex flex-col lg:flex-row gap-8 relative z-10">
      
      <!-- Sidebar / Menu Dọc -->
      <aside class="w-full lg:w-72 shrink-0">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-8">
            <div class="p-4 bg-slate-50 border-b border-slate-100">
                <h3 class="font-black text-slate-800 uppercase tracking-widest text-xs">Mục Lục Tài Liệu</h3>
            </div>
            <nav class="p-3 space-y-1">
                <Link :href="route('help.install')" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors" :class="route().current('help.install') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                    <span class="w-2 h-2 rounded-full mr-3" :class="route().current('help.install') ? 'bg-indigo-600' : 'bg-slate-300'"></span>
                    Cài Đặt Hệ Thống (Mới)
                </Link>
                
                <div class="pt-4 pb-2 px-4">
                    <h4 class="text-[10px] font-black text-slate-400 text-left uppercase tracking-widest">Truyền Thông</h4>
                </div>
                
                <Link :href="route('help.notifications')" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors" :class="route().current('help.notifications') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                    <span class="w-2 h-2 rounded-full mr-3" :class="route().current('help.notifications') ? 'bg-indigo-600' : 'bg-slate-300'"></span>
                    Hộp Thư - Thông Báo
                </Link>

                <Link :href="route('help.announcements')" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors" :class="route().current('help.announcements') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                    <span class="w-2 h-2 rounded-full mr-3" :class="route().current('help.announcements') ? 'bg-indigo-600' : 'bg-slate-300'"></span>
                    Đăng Bản Tin Mới (Announcements)
                </Link>

                <Link :href="route('help.broadcasts')" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors" :class="route().current('help.broadcasts') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                    <span class="w-2 h-2 rounded-full mr-3" :class="route().current('help.broadcasts') ? 'bg-indigo-600' : 'bg-slate-300'"></span>
                    Gửi Tự Động Hàng Loạt (Broadcasts)
                </Link>

                <div class="pt-4 pb-2 px-4 border-t border-slate-100 mt-2">
                    <h4 class="text-[10px] font-black text-slate-400 text-left uppercase tracking-widest">Cầu Nguyện & Ban Ngành</h4>
                </div>

                <Link :href="route('help.portal.members')" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors" :class="route().current('help.portal.members') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                    <span class="w-2 h-2 rounded-full mr-3" :class="route().current('help.portal.members') ? 'bg-indigo-600' : 'bg-slate-300'"></span>
                    Thành Viên & Phân Công
                </Link>

                <Link :href="route('help.portal.attendance')" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors" :class="route().current('help.portal.attendance') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                    <span class="w-2 h-2 rounded-full mr-3" :class="route().current('help.portal.attendance') ? 'bg-indigo-600' : 'bg-slate-300'"></span>
                    Điểm Danh & Thăm Viếng
                </Link>

                <Link :href="route('help.portal.finance')" class="flex items-center px-4 py-3 text-sm font-bold rounded-xl transition-colors" :class="route().current('help.portal.finance') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'">
                    <span class="w-2 h-2 rounded-full mr-3" :class="route().current('help.portal.finance') ? 'bg-indigo-600' : 'bg-slate-300'"></span>
                    Tài Chính & Báo Cáo
                </Link>
            </nav>
        </div>
      </aside>

      <!-- Nội Dung Bài Viết -->
      <div class="flex-1 min-w-0 bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
        <slot></slot>
      </div>
    </main>

    <!-- Footer -->
    <div class="max-w-7xl mx-auto w-full px-6 mt-16 mb-8 text-center text-slate-500 text-sm">
        <p>Bản Quyền &copy; 2026 CMSHT by Antigravity AI.</p>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
