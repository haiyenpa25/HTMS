<template>
  <div class="min-h-screen bg-gray-50 font-sans">

    <!-- ══════════════ TOP NAVBAR ══════════════ -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-30 shadow-sm">
      <div class="flex items-center gap-3 px-4 sm:px-6 h-14">
        <!-- Logo -->
        <div class="flex items-center gap-2 shrink-0">
          <div class="w-8 h-8 bg-orange-500 rounded-xl flex items-center justify-center text-white font-black text-sm">✝</div>
          <span class="font-black text-gray-900 hidden sm:block text-sm tracking-tight">CMS Hội Thánh</span>
        </div>

        <!-- Search -->
        <div class="flex-1 max-w-md mx-auto">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="text" placeholder="Tìm kiếm tài liệu, sự kiện..." class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-200 focus:border-orange-300 transition-all"/>
          </div>
        </div>

        <!-- Right: Bell + User -->
        <div class="flex items-center gap-2 shrink-0">
          <button @click="showNotifPanel = !showNotifPanel"
            class="relative w-9 h-9 bg-gray-50 hover:bg-orange-50 border border-gray-200 rounded-xl flex items-center justify-center transition-colors">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span v-if="notifications.length > 0"
              class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[9px] font-black rounded-full flex items-center justify-center">
              {{ notifications.length }}
            </span>
          </button>

          <!-- Avatar -->
          <div class="flex items-center gap-2">
            <div class="w-9 h-9 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center text-white font-black text-sm shadow-sm">
              {{ displayName.charAt(0).toUpperCase() }}
            </div>
            <div class="hidden sm:block">
              <p class="text-xs font-bold text-gray-900 leading-none">{{ displayName }}</p>
              <p class="text-[10px] text-gray-400 mt-0.5">{{ memberStatus }}</p>
            </div>
          </div>

          <!-- Dashboard (Admin Only) -->
          <Link v-if="hasPortalAccess || isAdmin" :href="route('dashboard')"
            class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-xl text-xs font-bold transition-colors border border-indigo-100" title="Quản trị Hệ thống">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.426-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
            <span>Quản trị</span>
          </Link>

          <!-- Logout -->
          <Link :href="route('logout')" method="post" as="button" title="Đăng xuất"
            class="w-9 h-9 bg-gray-50 hover:bg-red-50 border border-gray-200 hover:border-red-200 rounded-xl flex items-center justify-center transition-colors group">
            <svg class="w-4 h-4 text-gray-400 group-hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </Link>
        </div>
      </div>
    </header>

    <!-- ══════════════ MAIN LAYOUT ══════════════ -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 pb-24 lg:pb-8 space-y-6">

      <!-- Hero Card -->
      <div v-if="member" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Banner -->
        <div class="h-32 sm:h-40 bg-gradient-to-br from-indigo-600 via-blue-600 to-sky-500 relative">
          <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
          <div class="absolute bottom-4 right-5 flex gap-2">
            <span class="px-3 py-1 bg-white/20 backdrop-blur text-white text-[10px] uppercase font-black rounded-full border border-white/30">{{ member.member_code || 'Tín Hữu' }}</span>
            <span v-if="member.household?.head_member_id === member.id" class="px-3 py-1 bg-amber-500/80 backdrop-blur text-white text-[10px] uppercase font-black rounded-full border border-white/30 shadow-md shadow-amber-500/20">🥇 Chủ Hộ</span>
            <span class="px-3 py-1 rounded-full text-xs font-bold border border-white/30 backdrop-blur bg-green-500/80 text-white">
              {{ memberStatus }}
            </span>
          </div>
        </div>

        <div class="px-6 pb-6 sm:px-8 sm:pb-8 relative">
          <div class="flex flex-col sm:flex-row sm:items-end sm:gap-6">
            <!-- Avatar -->
            <div class="-mt-12 sm:-mt-16 w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-white p-1.5 shadow-xl shrink-0">
              <div class="w-full h-full rounded-2xl bg-gradient-to-tr from-orange-400 to-orange-600 text-white flex items-center justify-center text-4xl sm:text-5xl font-black">
                {{ (member.full_name || 'T').charAt(0) }}
              </div>
            </div>
            <!-- Info -->
            <div class="mt-4 sm:mt-0 sm:pb-1 flex-1">
              <h1 class="text-2xl sm:text-3xl font-black text-gray-900 leading-tight mb-2">{{ member.full_name }}</h1>
              <div class="flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-gray-500">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                  {{ member.phone || 'Chưa cập nhật SĐT' }}
                </div>
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  {{ member.email || 'Chưa cập nhật Email' }}
                </div>
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  Gia nhập: {{ member.joined_date ? formatDate(member.joined_date) : '—' }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs (Desktop/Tablet) -->
      <div class="hidden md:flex items-center gap-1 bg-gray-100/80 p-1.5 rounded-2xl w-fit max-w-full overflow-x-auto no-scrollbar">
        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
          :class="['px-4 py-2 text-sm font-bold transition-all whitespace-nowrap flex items-center gap-1.5 rounded-xl',
            activeTab === tab.id ? 'bg-white text-orange-600 shadow-sm' : 'text-gray-500 hover:text-gray-700']">
          <component :is="tab.icon" class="w-4 h-4"/>
          {{ tab.name }}
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- ─── LEFT COLUMN ─── -->
        <div class="lg:col-span-2 min-w-0 space-y-6">

        <!-- TAB: TRANG CHỦ (Home) -->
        <div v-show="activeTab === 'home'" class="space-y-6">
          <div v-if="showHero"
            class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white p-6 sm:p-8 shadow-lg">
          <!-- Nền mờ decoration -->
          <div class="absolute inset-0 opacity-20">
            <div class="absolute top-0 right-0 w-48 h-48 bg-orange-500 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 w-32 h-32 bg-blue-500 rounded-full translate-y-1/2 blur-3xl"></div>
          </div>
          <div class="relative z-10 flex flex-col sm:flex-row gap-6">
            <div class="flex-1">
              <!-- Badge -->
              <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-500 text-white text-[10px] font-black uppercase tracking-widest rounded-full mb-4">
                📖 LỜI CHÚA MỖI NGÀY
              </span>
              <!-- Greeting -->
              <h2 class="text-2xl sm:text-3xl font-black tracking-tight mb-3">
                Xin chào, {{ member ? member.full_name.split(' ').pop() : displayName.split(' ').pop() }}! 👋
              </h2>
              <!-- Bible verse -->
              <blockquote class="text-gray-200 text-sm leading-relaxed italic mb-2 max-w-md">
                "{{ todayVerse.text }}"
              </blockquote>
              <cite class="text-orange-400 text-xs font-bold not-italic">— {{ todayVerse.ref }}</cite>
            </div>
            <!-- CTA Buttons -->
            <div class="flex sm:flex-col gap-2 sm:justify-end shrink-0">
              <button @click="activeTab = 'care'"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white/15 hover:bg-white/25 text-white text-xs font-bold rounded-xl transition-all border border-white/20 active:scale-95">
                💌 Gửi cầu nguyện
              </button>
            </div>
          </div>
          <!-- Dismiss -->
          <button @click="showHero = false"
            class="absolute top-3 right-3 text-gray-400 hover:text-white transition-colors text-xs font-medium">
            ✕ Bỏ phần này
          </button>
          </div>

          <!-- Dashboard Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
             <!-- Tin tức nổi bật -->
             <div class="space-y-3">
                <h3 class="font-black text-gray-900 text-sm flex items-center gap-2">
                  <span class="text-red-500">📰</span> Tin Tức Mới Nhất
                </h3>
                <div v-if="announcements.length > 0" class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:border-red-200 transition-all cursor-pointer h-[120px] flex flex-col justify-center" @click="openNews(announcements[0])">
                  <h4 class="font-bold text-gray-900 text-sm mb-1 leading-snug">{{ announcements[0].title }}</h4>
                  <div class="text-[11px] text-gray-500 line-clamp-2" v-html="announcements[0].content"></div>
                </div>
                <div v-else class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm h-[120px] flex items-center justify-center text-xs text-gray-400">
                  Chưa có bản tin.
                </div>
             </div>

             <!-- Nhiệm vụ sắp tới -->
             <div class="space-y-3">
                <h3 class="font-black text-gray-900 text-sm flex items-center gap-2">
                  <span class="text-orange-500">🎯</span> Lịch Phục Vụ Của Ban
                </h3>
                <div v-if="upcomingEvents.length > 0" class="bg-orange-50 rounded-xl border border-orange-100 p-4 shadow-sm h-[120px] flex flex-col justify-center">
                   <h4 class="font-bold text-orange-900 text-sm mb-1 line-clamp-1 truncate">{{ upcomingEvents[0].title }}</h4>
                   <p class="text-[10px] text-orange-600 flex items-center gap-1 mb-2 font-black tracking-widest uppercase">
                     {{ formatDate(upcomingEvents[0].meeting_date) }}
                   </p>
                   <button @click="activeTab = 'calendar'" class="w-full text-[10px] font-bold text-white bg-orange-500 hover:bg-orange-600 py-2 rounded-lg transition-colors shadow-sm">Xem toàn bộ lịch trình</button>
                </div>
                <div v-else class="bg-gray-50 rounded-xl border border-gray-100 p-4 shadow-sm h-[120px] flex items-center justify-center text-xs text-gray-400">
                  Tuần này bạn chưa có lịch phục vụ.
                </div>
             </div>
          </div>

          <!-- Thống kê nhanh ngang -->
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center text-center">
                 <span class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center mb-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg></span>
                 <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-0.5">Đi nhóm</p>
                 <p class="text-xl font-black text-indigo-700">{{ member?.attendances?.length || 0 }}</p>
              </div>
              <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center text-center">
                 <span class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mb-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg></span>
                 <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-0.5">Thăm viếng</p>
                 <p class="text-xl font-black text-emerald-700">{{ member?.visitations?.length || 0 }}</p>
              </div>
              <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center text-center">
                 <span class="w-8 h-8 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mb-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg></span>
                 <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-0.5">Yêu cầu CQ</p>
                 <p class="text-xl font-black text-rose-700">{{ careRequests?.length || 0 }}</p>
              </div>
              <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col items-center text-center">
                 <span class="w-8 h-8 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mb-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></span>
                 <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-0.5">Lịch phục vụ</p>
                 <p class="text-xl font-black text-amber-700">{{ upcomingEvents?.length || 0 }}</p>
              </div>
          </div>
        </div>

        <!-- TAB: DƯỠNG LINH (Spiritual) -->
        <div v-show="activeTab === 'spiritual'" class="space-y-6">
          <FaithJourneyTimeline v-if="member" :member="member" :isPastor="false" />
        </div>

        <!-- TAB: LỊCH TRÌNH (Calendar) -->
        <div v-show="activeTab === 'calendar'" class="space-y-6">
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-black uppercase text-gray-900 tracking-widest flex items-center gap-2">
              <span class="text-orange-500">📅</span> Lịch trình & Nhiệm vụ
            </h3>
            <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1">
              <button @click="calView = 'week'"
                :class="calView === 'week' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'"
                class="px-3 py-1 text-xs font-bold rounded-lg transition-all">Tuần</button>
              <button @click="calView = 'month'"
                :class="calView === 'month' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'"
                class="px-3 py-1 text-xs font-bold rounded-lg transition-all">Tháng</button>
            </div>
          </div>

          <!-- Week View Strip -->
          <div v-show="calView === 'week'" class="px-4 py-3 border-b border-gray-50">
            <div class="flex items-stretch justify-center gap-1.5 overflow-x-auto pb-1">
              <button v-for="day in weekDays" :key="day.date"
                @click="selectedDay = day.date"
                :class="[
                  'flex flex-col items-center min-w-[48px] px-2 py-2 rounded-xl text-center transition-all shrink-0',
                  selectedDay === day.date
                    ? 'bg-orange-500 text-white shadow-md shadow-orange-200'
                    : 'hover:bg-gray-50 text-gray-600',
                  day.isToday && selectedDay !== day.date ? 'ring-2 ring-orange-200' : ''
                ]">
                <span class="text-[9px] font-bold uppercase tracking-widest opacity-70">{{ day.label }}</span>
                <span class="text-base font-black leading-none mt-0.5">{{ day.num }}</span>
                <span v-if="hasEventOnDate(day.date)"
                  class="w-1 h-1 rounded-full mt-1"
                  :class="selectedDay === day.date ? 'bg-white' : 'bg-orange-500'"></span>
                <span v-else class="w-1 h-1 mt-1"></span>
              </button>
            </div>
          </div>

          <!-- Month View Grid -->
          <div v-show="calView === 'month'" class="px-4 py-3 border-b border-gray-50">
             <div class="flex items-center justify-between mb-3 px-2">
                 <button @click="prevMonth" class="p-1 hover:bg-gray-100 rounded-lg text-gray-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                 </button>
                 <span class="font-bold text-sm text-gray-800">Tháng {{ currentMonth.getMonth() + 1 }}, {{ currentMonth.getFullYear() }}</span>
                 <button @click="nextMonth" class="p-1 hover:bg-gray-100 rounded-lg text-gray-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                 </button>
             </div>
             <div class="grid grid-cols-7 gap-1 sm:gap-2 text-center">
                 <span v-for="d in ['CN','T2','T3','T4','T5','T6','T7']" :key="d" class="text-[9px] sm:text-[11px] font-black text-gray-400 py-1">{{d}}</span>
                 
                 <div v-for="(day, idx) in monthDays" :key="idx" 
                      class="aspect-square flex flex-col items-center justify-center rounded-lg sm:rounded-xl cursor-pointer transition-all border border-transparent"
                      :class="[
                         day.empty ? 'invisible' : (selectedDay === day.date ? 'bg-orange-500 text-white shadow-md border-orange-600' : 'bg-gray-50/50 hover:bg-orange-50 text-gray-700 border-gray-100'),
                         day.isToday && selectedDay !== day.date ? 'ring-2 ring-orange-200 bg-orange-50/30' : ''
                      ]"
                      @click="!day.empty && (selectedDay = day.date)">
                      <span v-if="!day.empty" class="text-[10px] sm:text-xs font-bold">{{ day.num }}</span>
                      <span v-if="!day.empty && hasEventOnDate(day.date)" class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full mt-0.5 sm:mt-1" :class="selectedDay === day.date ? 'bg-white' : 'bg-orange-500'"></span>
                 </div>
             </div>
          </div>

          <!-- Schedule of selected day -->
          <div class="px-5 py-3">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3">LỊCH TRÌNH HÔM NAY</p>

            <div v-if="todayEvents.length > 0" class="space-y-2">
              <div v-for="event in todayEvents" :key="event.id"
                class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors group">
                <!-- Time -->
                <div class="shrink-0 text-center min-w-[40px]">
                  <p class="text-[10px] font-bold text-gray-400 uppercase">{{ formatTime(event.meeting_date) }}</p>
                </div>
                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                    <span class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full"
                      :class="getEventBadge(event.type)">
                      {{ getEventLabel(event.type) }}
                    </span>
                    <span v-if="event.location" class="text-[10px] text-gray-400">{{ event.location }}</span>
                  </div>
                  <p class="font-bold text-gray-900 text-sm">{{ event.title }}</p>
                </div>
                <button v-if="event.scope_type === 'personal'" @click.stop="deleteEvent(event.raw_id)"
                  class="shrink-0 opacity-0 group-hover:opacity-100 w-6 h-6 rounded-full hover:bg-red-50 text-gray-400 hover:text-red-500 flex items-center justify-center transition-all">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
                <button v-else class="shrink-0 opacity-0 group-hover:opacity-100 w-6 h-6 rounded-full hover:bg-gray-200 flex items-center justify-center transition-all">
                  <svg class="w-3 h-3 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                  </svg>
                </button>
              </div>
            </div>
            <div v-else class="py-8 text-center">
              <div class="w-12 h-12 bg-gray-50 rounded-full mx-auto mb-3 flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
              <p class="text-sm text-gray-400">Ngày này chưa có lịch</p>
            </div>

            <!-- Việc của bạn (Care Requests) -->
            <div v-if="careRequests.length > 0">
              <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3 mt-4">VIỆC CỦA BẠN</p>
              <div v-for="req in careRequests.slice(0, 3)" :key="req.id"
                class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-500 flex items-center justify-center shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-bold text-sm text-gray-900 truncate">{{ req.title }}</p>
                  <p class="text-xs text-gray-500">{{ careCategories[req.category] || req.category }}</p>
                </div>
                <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full"
                  :class="{
                    'bg-amber-100 text-amber-700': req.status === 'pending',
                    'bg-blue-100 text-blue-700': req.status === 'in_progress',
                    'bg-emerald-100 text-emerald-700': req.status === 'resolved',
                    'bg-gray-100 text-gray-500': req.status === 'closed',
                  }">{{ statusLabel(req.status) }}</span>
              </div>
            </div>
          </div>
        </div>
        </div>

        <!-- TAB: TIN TỨC (News) -->
        <div v-show="activeTab === 'news'" class="space-y-4">
          <div class="flex items-center gap-3">
            <div class="bg-white border text-gray-500 font-bold border-gray-200 rounded-xl flex items-center gap-2 p-1 overflow-hidden shadow-sm">
              <button class="bg-green-50 text-green-700 px-4 py-2 rounded-lg text-sm font-black transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H14"/></svg>
                Bản Tin
              </button>
              <button class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Dưỡng Linh
              </button>
            </div>
            <div class="flex-1 border-b border-gray-200"></div>
          </div>

          <div v-if="announcements.length > 0" class="space-y-4">
            <!-- Featured News: First Item -->
            <div @click="openNews(announcements[0])" class="relative rounded-2xl overflow-hidden bg-gray-900 text-white shadow-md group cursor-pointer aspect-[21/9] sm:aspect-[21/8]">
              <!-- Mock Image Background -->
              <img src="https://images.unsplash.com/photo-1507434965515-61970f2714be?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Cover" class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:scale-105 transition-transform duration-700" />
              <!-- Gradient Overlay -->
              <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
              
              <div class="absolute inset-0 p-5 sm:p-8 flex flex-col justify-end">
                <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black tracking-widest uppercase self-start mb-3">
                  THÔNG BÁO
                </span>
                <h3 class="text-xl sm:text-2xl font-black mb-2 leading-tight drop-shadow-md group-hover:-translate-y-1 transition-transform">
                  {{ announcements[0].title }}
                </h3>
                <div class="text-gray-200 text-xs sm:text-sm line-clamp-2 md:w-3/4 opacity-90 mb-4" v-html="announcements[0].content"></div>
                
                <div class="flex items-center gap-4 text-xs font-bold opacity-80 uppercase tracking-wider">
                  <span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ announcements[0].time }}</span>
                  <span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> {{ announcements[0].author }}</span>
                  <div class="flex-1"></div>
                  <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg> 0</span>
                </div>
              </div>
            </div>

            <!-- Other News: Grid -->
            <div v-if="announcements.length > 1" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div v-for="news in announcements.slice(1)" :key="news.id" @click="openNews(news)"
                class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md hover:border-red-100 transition-all cursor-pointer group flex flex-col h-full">
                
                <span class="inline-block px-3 py-1 bg-red-50 text-red-500 rounded-full text-[10px] font-black tracking-widest uppercase self-start mb-3">
                  THÔNG BÁO
                </span>
                
                <h4 class="font-black text-gray-900 text-lg mb-2 leading-snug group-hover:text-red-500 transition-colors">
                  {{ news.title }}
                </h4>
                
                <div class="text-sm text-gray-500 mb-6 line-clamp-2" v-html="news.content"></div>
                
                <div class="mt-auto flex items-center justify-between text-[11px] font-bold text-gray-400">
                  <span class="flex items-center gap-1.5 uppercase tracking-wide">
                     <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                     {{ news.time }}
                  </span>
                  <div class="flex items-center gap-1.5 text-green-600 group-hover:underline">
                    Xem chi tiết <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="text-center pt-2">
               <button class="text-xs font-black uppercase text-gray-400 hover:text-green-600 transition-colors bg-white px-5 py-2.5 rounded-xl border border-gray-200 shadow-sm">
                 Tất cả thông báo cá nhân
               </button>
            </div>
          </div>
          
          <div v-else class="py-12 bg-white rounded-2xl border border-gray-100 text-center shadow-sm">
             <div class="w-16 h-16 bg-gray-50 rounded-full mx-auto mb-4 flex items-center justify-center">
               <span class="text-2xl opacity-50">📰</span>
             </div>
             <p class="font-bold text-gray-900 mb-1">Chưa có bản tin</p>
             <p class="text-sm text-gray-500">Giữ liên lạc, cập nhật sẽ xuất hiện tại đây.</p>
          </div>
        </div>

        <!-- TAB: THÔNG TIN CHUNG (General) -->
        <div v-show="activeTab === 'general'" class="space-y-6">
           <!-- Placeholder for general info -->
           <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
             <h3 class="font-black mb-4">Thông tin cá nhân</h3>
             <p class="text-sm text-gray-500">Đang hiển thị thông tin chung của tín hữu.</p>
           </div>
        </div>

        <!-- TAB: GIA ĐÌNH (Family) -->
        <div v-show="activeTab === 'family'" class="space-y-6">
           <FamilyTreeCard v-if="member" :member="member" :isPastor="false" />
        </div>

        <!-- TAB: LỊCH SỬ THĂM VIẾNG (History) -->
        <div v-show="activeTab === 'history'" class="space-y-6">
           <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
             <h3 class="font-black mb-4">Lịch sử chăm sóc & Thăm viếng</h3>
             <ul v-if="member?.visitations?.length" class="space-y-4">
               <li v-for="visit in member.visitations" :key="visit.id" class="border-l-2 border-orange-200 pl-4 py-1">
                 <p class="text-xs text-orange-600 font-bold mb-1">{{ formatDate(visit.visit_date) }}</p>
                 <p class="text-sm font-medium text-gray-800">{{ visit.purpose }}</p>
                 <p class="text-xs text-gray-500 mt-1">Người thăm: {{ visit.visitors?.map(v => v.full_name).join(', ') || 'Hội Thánh' }}</p>
               </li>
             </ul>
             <p v-else class="text-sm text-gray-500">Chưa có 기록 thăm viếng nào.</p>
           </div>
        </div>

      </div>

      <!-- ─── RIGHT SIDEBAR ─── -->
      <div class="w-full lg:w-72 xl:w-80 shrink-0 space-y-4">

        <!-- THAO TÁC NHANH -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
            <span class="text-orange-500 text-sm">⚡</span>
            <h3 class="font-black text-sm text-gray-900">Thao tác nhanh</h3>
          </div>
          <div class="p-3 space-y-2">
            <!-- Primary CTA -->
            <button @click="activeTab = 'care'"
              class="w-full flex items-center gap-3 px-4 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold text-sm transition-all active:scale-95 shadow-md shadow-orange-200">
              <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
              Gửi yêu cầu cầu nguyện
            </button>
            <!-- Secondary CTAs -->

            <Link :href="route('documents.index')"
              class="w-full flex items-center gap-3 px-4 py-3 bg-gray-50 hover:bg-orange-50 text-gray-700 hover:text-orange-600 rounded-xl font-bold text-sm transition-all border border-gray-100 hover:border-orange-200">
              <svg class="w-5 h-5 shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Tài liệu & Tài nguyên
            </Link>
          </div>
        </div>



        <!-- HỒ SƠ TÍN HỮU (compact) -->
        <div v-if="member" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-4 py-3 flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center text-white font-black">
              {{ member.full_name.charAt(0) }}
            </div>
            <div>
              <p class="font-black text-white text-sm leading-none">{{ member.full_name }}</p>
              <p class="text-gray-400 text-xs mt-0.5">{{ member.member_code ? '#' + member.member_code : 'Tín hữu' }}</p>
            </div>
          </div>
          <div class="divide-y divide-gray-50 text-sm">
            <div class="px-4 py-2.5 flex justify-between">
              <span class="text-xs text-gray-500">Trạng thái</span>
              <span class="text-xs font-bold text-emerald-600">{{ memberStatus }}</span>
            </div>
            <div class="px-4 py-2.5 flex justify-between">
              <span class="text-xs text-gray-500">Báp-têm</span>
              <span class="text-xs font-bold" :class="member.is_baptized ? 'text-blue-600' : 'text-gray-400'">
                {{ member.is_baptized ? '✝ Đã báp-têm' : 'Chưa' }}
              </span>
            </div>
            <div v-if="member.departments?.length > 0" class="px-4 py-2.5">
              <p class="text-xs text-gray-500 mb-1.5">Ban ngành</p>
              <div class="flex flex-wrap gap-1">
                <span v-for="dept in member.departments" :key="dept.id"
                  class="text-[10px] font-bold text-orange-700 bg-orange-50 px-2 py-0.5 rounded-full">
                  {{ dept.name }}
                </span>
              </div>
            </div>
            <!-- Leaflet Map Integration -->
            <div class="px-4 py-3 bg-gray-50" v-if="member.latitude && member.longitude">
               <p class="text-[10px] font-black uppercase text-gray-400 mb-2">Vị trí địa lý (Nhà riêng)</p>
               <div class="h-[160px] rounded-xl overflow-hidden border border-gray-200 z-0">
                  <l-map :zoom="14" :center="[member.latitude, member.longitude]" :useGlobalLeaflet="false" class="z-0 relative">
                      <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" attribution="&copy; OpenStreetMap"></l-tile-layer>
                      <l-marker :lat-lng="[member.latitude, member.longitude]"></l-marker>
                  </l-map>
               </div>
            </div>
            <div class="px-4 py-3 bg-gray-50" v-else>
               <p class="text-[10px] font-black uppercase text-gray-400 mb-2">Vị trí địa lý (Nhà riêng)</p>
               <div class="h-[100px] rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center bg-white">
                  <p class="text-[10px] text-gray-400 font-medium">Chưa cập nhật tọa độ GPS</p>
               </div>
            </div>
          </div>
        </div>

        <!-- TỔNG QUAN -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
           <h3 class="font-black text-gray-900 mb-4 flex items-center gap-2">
             <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg> Tổng quan
           </h3>
           <div class="grid grid-cols-2 gap-3">
              <div class="bg-indigo-50/50 p-3 rounded-xl border border-indigo-100">
                 <p class="text-[10px] font-black uppercase tracking-widest text-indigo-500 mb-1">Đi nhóm</p>
                 <p class="text-xl font-black text-indigo-700">{{ member?.attendances?.length || 0 }} <span class="text-[10px] font-bold text-indigo-400 ml-1">LẦN</span></p>
              </div>
              <div class="bg-emerald-50/50 p-3 rounded-xl border border-emerald-100">
                 <p class="text-[10px] font-black uppercase tracking-widest text-emerald-500 mb-1">Thăm viếng</p>
                 <p class="text-xl font-black text-emerald-700">{{ member?.visitations?.length || 0 }} <span class="text-[10px] font-bold text-emerald-400 ml-1">LẦN</span></p>
              </div>
           </div>
        </div>

        <!-- LỊCH PHỤC VỤ SẮP TỚI -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-black text-gray-900 text-sm flex items-center gap-2">
              <span class="text-orange-500">🎯</span> Lịch phục vụ
            </h3>
          </div>
          <div v-if="upcomingEvents.length > 0" class="p-3 grid grid-cols-1 gap-3">
            <div v-for="event in upcomingEvents" :key="event.id"
              class="border border-gray-100 rounded-xl p-3 hover:border-orange-200 hover:shadow-sm transition-all bg-gray-50/50">
              <h4 class="font-bold text-gray-900 text-xs mb-1">{{ event.title }}</h4>
              <p class="text-[10px] text-gray-500 flex items-center gap-1 mb-0.5 font-medium">
                <svg class="w-3 h-3 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ formatDate(event.meeting_date) }}
              </p>
              <div class="mt-2 text-center">
                 <button v-if="event.status === 'pending'" @click.prevent="respondDuty(event.raw_id, 'accepted')" class="w-full py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-lg transition-all active:scale-95 shadow-sm shadow-emerald-200/50">
                   Xác nhận gọi
                 </button>
                 <button v-else-if="event.status === 'accepted'" class="w-full py-1.5 bg-emerald-50 text-emerald-600 border border-emerald-200 text-[10px] font-black uppercase tracking-widest rounded-lg cursor-default">
                   ✨ Đã nhận việc
                 </button>
                 <button v-else-if="event.status === 'declined'" class="w-full py-1.5 bg-red-50 text-red-600 border border-red-200 text-[10px] font-black uppercase tracking-widest rounded-lg cursor-default" :title="event.reason">
                   🚫 Đã từ chối
                 </button>
              </div>
            </div>
            <div class="text-center pt-1">
               <Link :href="route('calendar.index')" class="text-[10px] font-bold text-gray-400 hover:text-orange-500 uppercase tracking-widest transition-colors">>> Đi đến Lịch trình chi tiết</Link>
            </div>
          </div>
          <div v-else class="py-6 text-center">
            <p class="text-[11px] font-medium text-gray-400">Không có lịch phục vụ sắp tới.</p>
          </div>
        </div>

      </div>
    </div>
    </div>

    <!-- ══════════════ CARE MODAL ══════════════ -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="activeTab === 'care'" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4">
          <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="activeTab = 'home'"></div>
          <div class="relative bg-white w-full sm:max-w-lg sm:rounded-2xl rounded-t-2xl shadow-2xl max-h-[90vh] overflow-y-auto animate-slideup">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 px-5 py-4 text-white sm:rounded-t-2xl rounded-t-2xl">
              <div class="flex items-center justify-between">
                <div>
                  <h2 class="text-lg font-black">💌 Gửi Yêu Cầu</h2>
                  <p class="text-orange-100 text-xs mt-0.5">Mục sư và Hội Thánh sẽ tiếp nhận</p>
                </div>
                <button @click="activeTab = 'home'" class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </div>
            </div>
            <form @submit.prevent="submitCareRequest" class="p-5 space-y-4">
              <!-- Flash success -->
              <div v-if="page.props.flash?.success"
                class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-3 py-2 rounded-xl text-sm font-medium">
                ✅ {{ page.props.flash.success }}
              </div>
              <!-- Category -->
              <div>
                <label class="text-xs font-black text-gray-700 uppercase tracking-widest mb-2 block">Loại yêu cầu *</label>
                <div class="grid grid-cols-3 gap-2">
                  <button v-for="(label, key) in careCategories" :key="key" type="button"
                    @click="careForm.category = key"
                    :class="careForm.category === key ? 'bg-orange-500 text-white border-orange-500 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-orange-200'"
                    class="p-2 rounded-xl border text-xs font-bold transition-all active:scale-95">
                    {{ label }}
                  </button>
                </div>
              </div>
              <!-- Title -->
              <div>
                <label class="text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5 block">Tiêu đề *</label>
                <input v-model="careForm.title" type="text" required placeholder="Tiêu đề ngắn gọn..."
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none transition-all"/>
              </div>
              <!-- Content -->
              <div>
                <label class="text-xs font-black text-gray-700 uppercase tracking-widest mb-1.5 block">Nội dung *</label>
                <textarea v-model="careForm.content" rows="4" required placeholder="Chia sẻ chi tiết..."
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:ring-2 focus:ring-orange-200 focus:border-orange-400 outline-none transition-all resize-none"></textarea>
              </div>
              <!-- Options -->
              <div class="flex flex-col sm:flex-row gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" v-model="careForm.is_urgent" class="w-4 h-4 rounded border-gray-300 text-red-500"/>
                  <span class="text-sm font-bold text-red-600">⚡ Khẩn cấp</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" v-model="careForm.is_private" class="w-4 h-4 rounded border-gray-300"/>
                  <span class="text-sm text-gray-600 font-medium">🔒 Thư mật cho Mục sư</span>
                </label>
              </div>
              <!-- Submit -->
              <button type="submit" :disabled="careLoading"
                class="w-full py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white font-black rounded-xl shadow-lg shadow-orange-200 hover:from-orange-600 hover:to-red-600 active:scale-[0.98] transition-all disabled:opacity-50">
                {{ careLoading ? 'Đang gửi...' : '💌 Gửi Yêu Cầu' }}
              </button>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- S L I D E  O V E R : Tạo lịch cá nhân -->
    <SlideOver v-model="isEventModalOpen" title="Tạo Lịch Cá Nhân" size="sm">
      <form id="personalEventForm" @submit.prevent="submitEvent" class="space-y-4">
        <div>
          <InputLabel for="title" value="Chủ đề / Tên sự kiện *" />
          <TextInput id="title" v-model="eventForm.title" type="text" class="mt-1 block w-full text-sm" required autofocus />
          <InputError class="mt-2" :message="eventForm.errors.title" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <InputLabel for="start_time" value="Bắt đầu *" />
            <TextInput id="start_time" v-model="eventForm.start_time" type="datetime-local" class="mt-1 block w-full text-sm" required />
            <InputError class="mt-2" :message="eventForm.errors.start_time" />
          </div>
          <div :class="{ 'opacity-50 pointer-events-none': eventForm.is_all_day }">
            <InputLabel for="end_time" value="Kết thúc" />
            <TextInput id="end_time" v-model="eventForm.end_time" type="datetime-local" class="mt-1 block w-full text-sm" :required="!eventForm.is_all_day"/>
            <InputError class="mt-2" :message="eventForm.errors.end_time" />
          </div>
        </div>

        <div>
          <label class="flex items-center gap-2 mt-2 cursor-pointer">
            <input type="checkbox" v-model="eventForm.is_all_day" class="rounded border-gray-300 text-orange-600 shadow-sm focus:ring-orange-500" />
            <span class="text-sm font-bold text-gray-700">Cả ngày (All day)</span>
          </label>
        </div>

        <div>
          <InputLabel for="description" value="Ghi chú chi tiết" />
          <textarea id="description" v-model="eventForm.description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-md shadow-sm text-sm" placeholder="Nhập ghi chú cá nhân..."></textarea>
        </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3 w-full">
          <SecondaryButton type="button" @click="isEventModalOpen = false">Hủy</SecondaryButton>
          <PrimaryButton form="personalEventForm" type="submit" :class="{ 'opacity-25': eventForm.processing }" :disabled="eventForm.processing">
            Lưu sự kiện cá nhân
          </PrimaryButton>
        </div>
      </template>
    </SlideOver>

    <!-- News SlideOver Component -->
    <SlideOver :show="isNewsModalOpen" @close="isNewsModalOpen = false" title="Chi tiết Bản Tin">
      <div v-if="selectedNews" class="p-6">
        <h2 class="text-2xl font-black text-gray-900 mb-2">{{ selectedNews.title }}</h2>
        <div class="flex flex-wrap items-center gap-4 text-xs font-bold text-gray-500 uppercase tracking-wider mb-6 pb-6 border-b border-gray-100">
           <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> {{ selectedNews.time }}</span>
           <span class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> {{ selectedNews.author }}</span>
        </div>
        
        <div class="prose prose-sm prose-orange max-w-none text-gray-700 leading-relaxed font-medium" v-html="selectedNews.content"></div>
      </div>
      <template #footer>
        <div class="flex justify-end w-full">
          <SecondaryButton type="button" @click="isNewsModalOpen = false">Đóng</SecondaryButton>
        </div>
      </template>
    </SlideOver>

  </div>

  <!-- ─── MOBILE BOTTOM NAV ─── -->
  <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white/90 backdrop-blur-md border-t border-gray-200 z-40 px-2 py-1 flex justify-around items-center shadow-[0_-4px_20px_rgba(0,0,0,0.05)] pb-safe">
    <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
      class="flex flex-col items-center justify-center w-full py-2 transition-colors relative"
      :class="activeTab === tab.id ? 'text-orange-600' : 'text-gray-400 hover:text-gray-600'">
      <component :is="tab.icon" class="w-5 h-5 mb-1 transition-transform" :class="{'scale-110': activeTab === tab.id}" />
      <span class="text-[9px] font-bold">{{ tab.name }}</span>
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage, router, useForm } from '@inertiajs/vue3';
import SlideOver from '@/Components/SlideOver.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

import { Home as HomeIcon, User as IdentificationIcon, History as HistoryIcon, Users as FamilyIcon, Star as FaithIcon, CalendarIcon, NewspaperIcon } from 'lucide-vue-next';
import FamilyTreeCard from '@/Components/Member/FamilyTreeCard.vue';
import FaithJourneyTimeline from '@/Components/Member/FaithJourneyTimeline.vue';
import 'leaflet/dist/leaflet.css';
import { LMap, LTileLayer, LMarker } from '@vue-leaflet/vue-leaflet';
import axios from 'axios';

const page = usePage();

const isAdmin = computed(() =>
  page.props.auth?.user?.roles?.some(r => ['Super_Admin', 'Pastor', 'BTS_Admin'].includes(r.name)) ?? false
);

const props = defineProps({
  member:         Object,
  careRequests:   { type: Array, default: () => [] },
  notifications:  { type: Array, default: () => [] },
  announcements:  { type: Array, default: () => [] },
  upcomingEvents: { type: Array, default: () => [] },
  monthEventsData:{ type: Array, default: () => [] },
  careCategories: { type: Object, default: () => ({}) },
  hasPortalAccess:{ type: Boolean, default: false },
});

// ── State ──
const activeTab      = ref('home'); // 'home', 'general', 'family', 'history', 'calendar', 'news', 'spiritual'
const activeCareTab  = ref(false); // separate flag for the care request modal
const showHero       = ref(true);
const calView        = ref('week');
const showNotifPanel = ref(false);

const isNewsModalOpen = ref(false);
const selectedNews = ref(null);

const openNews = (news) => {
    selectedNews.value = news;
    isNewsModalOpen.value = true;
};

const tabs = computed(() => {
  return [
    { id: 'home', name: 'Trang chủ', icon: HomeIcon },
    { id: 'general', name: 'Thông tin', icon: IdentificationIcon },
    { id: 'family', name: 'Gia đình', icon: FamilyIcon },
    { id: 'history', name: 'Thăm viếng', icon: HistoryIcon },
    { id: 'calendar', name: 'Lịch trình', icon: CalendarIcon },
    { id: 'news', name: 'Tin tức', icon: NewspaperIcon },
    { id: 'spiritual', name: 'Dưỡng linh', icon: FaithIcon },
  ];
});

const today = new Date();
const selectedDay = ref(today.toDateString());

const careLoading = ref(false);
const careForm    = ref({
  category:   'prayer',
  title:      '',
  content:    '',
  is_urgent:  false,
  is_private: false,
});

// Personal Events Modal
const isEventModalOpen = ref(false);
const eventForm = useForm({
  title: '',
  description: '',
  start_time: '',
  end_time: '',
  is_all_day: false,
  scope_type: 'personal',
  scope_id: null,
  color: '#8b5cf6', // Mặc định màu tím cho lịch cá nhân
  type: 'other' // Type
});

const submitEvent = () => {
  eventForm.post(route('calendar.events.store'), {
      preserveScroll: true,
      onSuccess: () => {
          isEventModalOpen.value = false;
          eventForm.reset();
          // Tải lại components
          router.reload({ only: ['upcomingEvents', 'monthEventsData'] });
      }
  });
};

const deleteEvent = (raw_id) => {
    if(confirm('Bạn có chắc xoá lịch cá nhân này?')) {
        router.delete(route('calendar.events.destroy', raw_id), {
            preserveScroll: true,
            onFinish: () => router.reload({ only: ['upcomingEvents', 'monthEventsData'] })
        });
    }
};

const respondDuty = (duty_raw_id, action) => {
    let reason = null;
    if (action === 'declined') {
        reason = prompt('Vui lòng cho biết lý do từ chối (bắt buộc):');
        if (reason === null || reason.trim() === '') {
            return;
        }
    }
    router.patch(route('member.duty.update-status', duty_raw_id), {
        status: action,
        reason: reason
    }, {
        preserveScroll: true,
        onFinish: () => router.reload({ only: ['upcomingEvents'] })
    });
};

const monthEvents = computed(() => props.monthEventsData || []);

onMounted(() => {
    // monthEvents now uses props.monthEventsData, no Axios call needed.
});

// Helper check event by date
const hasEventOnDate = (dateString) => {
    return monthEvents.value.some(e => new Date(e.start || e.meeting_date).toDateString() === dateString);
};

// ── Computed ──
const displayName = computed(() =>
  props.member?.full_name || page.props.auth?.user?.name || 'Tín hữu'
);

const memberStatus = computed(() => {
  if (!props.member) return 'Khách';
  const map = {
    official:  'Chính thức',
    candidate: 'Dự bị',
    learning:  'Đang hiểu đạo',
    visitor:   'Thân hữu',
  };
  return map[props.member.attendance_status] || map[props.member.member_type] || 'Tín hữu';
});

// Week days strip
const weekDays = computed(() => {
  const days = [];
  const labels = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
  const start = new Date(today);
  start.setDate(today.getDate() - today.getDay()); // Sunday
  for (let i = 0; i < 7; i++) {
    const d = new Date(start);
    d.setDate(start.getDate() + i);
    days.push({
      label:   labels[i],
      num:     d.getDate(),
      date:    d.toDateString(),
      isToday: d.toDateString() === today.toDateString()
    });
  }
  return days;
});

// Month Grid UI state
const currentMonth = ref(new Date(today.getFullYear(), today.getMonth(), 1));

const monthDays = computed(() => {
  const year = currentMonth.value.getFullYear();
  const month = currentMonth.value.getMonth();
  
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  
  const days = [];
  
  const startDayOfWeek = firstDay.getDay(); 
  for (let i = 0; i < startDayOfWeek; i++) {
    days.push({ empty: true });
  }
  
  for (let i = 1; i <= lastDay.getDate(); i++) {
    const d = new Date(year, month, i);
    days.push({
      num: i,
      date: d.toDateString(),
      isToday: d.toDateString() === today.toDateString()
    });
  }
  return days;
});

const nextMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1);
};
const prevMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1);
};

// Events on selected day
const todayEvents = computed(() => {
   // Ưu tiên hiển thị event từ API calendar nếu đã load, nếu chưa thì fallback xài props.upcomingEvents
   const eventsPool = monthEvents.value.length > 0 ? monthEvents.value : props.upcomingEvents;
   
   return eventsPool.filter(e => {
       const evDate = e.start || e.meeting_date;
       return new Date(evDate).toDateString() === selectedDay.value;
   }).map(e => ({
       id: e.id,
       title: e.title,
       meeting_date: e.start || e.meeting_date,
       location: e.location || e.extendedProps?.location,
       type: e.type || e.extendedProps?.type || 'other'
   }));
});

// Bible verses rotation
const verses = [
  { text: 'Mọi việc anh em làm, hãy tận tâm mà làm, như làm cho Chúa, chứ không phải cho người ta.', ref: 'Cô-lô-se 3:23' },
  { text: 'Hãy hết lòng tin cậy Đức Giê-hô-va, chớ nương cậy vào sự thông sáng của con.', ref: 'Châm Ngôn 3:5' },
  { text: 'Tôi có thể làm được mọi sự nhờ Đấng ban thêm sức cho tôi.', ref: 'Phi-líp 4:13' },
  { text: 'Hãy vui mừng trong sự trông cậy; nhịn nhục trong sự hoạn nạn; bền lòng mà cầu nguyện.', ref: 'Rô-ma 12:12' },
  { text: 'Chúa là ánh sáng và sự cứu rỗi của tôi; tôi sẽ sợ ai?', ref: 'Thi Thiên 27:1' },
  { text: 'Đức Giê-hô-va chăn giữ tôi, tôi sẽ chẳng thiếu thốn gì.', ref: 'Thi Thiên 23:1' },
  { text: 'Vì Đức Chúa Trời yêu thương thế gian đến nỗi đã ban Con một của Ngài.', ref: 'Giăng 3:16' },
];

const todayVerse = computed(() => {
  const idx = today.getDate() % verses.length;
  return verses[idx];
});

// ── Helpers ──
const statusLabel = (s) => ({
  pending:     'Chờ xử lý',
  in_progress: 'Đang xử lý',
  resolved:    'Đã giải quyết',
  closed:      'Đã đóng',
}[s] || s);

const formatDate = (dateStr) => {
  const d = new Date(dateStr);
  return d.toLocaleDateString('vi-VN', { weekday: 'short', day: 'numeric', month: 'short' });
};

const formatTime = (dateStr) => {
  const d = new Date(dateStr);
  return d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
};

const getEventLabel = (type) => ({
  worship:   'LỄ THỜ PHƯỢNG',
  meeting:   'HỌP BAN',
  training:  'LỊCH TRỰC',
  other:     'SỰ KIỆN',
}[type] || 'SỰ KIỆN');

const getEventBadge = (type) => ({
  worship:  'bg-blue-100 text-blue-700',
  meeting:  'bg-purple-100 text-purple-700',
  training: 'bg-emerald-100 text-emerald-700',
  other:    'bg-orange-100 text-orange-700',
}[type] || 'bg-gray-100 text-gray-600');

// ── Actions ──
const submitCareRequest = () => {
  if (!careForm.value.title || !careForm.value.content) return;
  careLoading.value = true;
  router.post(route('member.portal.care.submit'), careForm.value, {
    onSuccess: () => {
      careForm.value = { category: 'prayer', title: '', content: '', is_urgent: false, is_private: false };
      activeCareTab.value = false;
      careLoading.value = false;
    },
    onError: () => { careLoading.value = false; },
  });
};
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .relative, .modal-leave-to .relative { transform: translateY(40px); }

@keyframes slideup {
  from { transform: translateY(100%); }
  to   { transform: translateY(0); }
}
.animate-slideup { animation: slideup 0.3s cubic-bezier(0.32, 0.72, 0, 1); }

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-clamp: 2; /* fix linting */
}
</style>
