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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-5 flex flex-col lg:flex-row gap-5">

      <!-- ─── LEFT COLUMN ─── -->
      <div class="flex-1 min-w-0 space-y-5">

        <!-- HERO BANNER: Lời Chúa mỗi ngày -->
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
              <Link :href="route('calendar.index')"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-black rounded-xl transition-all hover:shadow-lg hover:shadow-orange-500/30 active:scale-95">
                📅 Xem lịch tuần
              </Link>
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

        <!-- LỊCH TRÌNH & NHIỆM VỤ -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-black text-gray-900 flex items-center gap-2">
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

          <!-- Day Strip -->
          <div class="px-4 py-3 border-b border-gray-50">
            <div class="flex items-stretch gap-1.5 overflow-x-auto pb-1">
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
                <span v-if="day.hasEvent"
                  class="w-1 h-1 rounded-full mt-1"
                  :class="selectedDay === day.date ? 'bg-white' : 'bg-orange-500'"></span>
                <span v-else class="w-1 h-1 mt-1"></span>
              </button>
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
                <button class="shrink-0 opacity-0 group-hover:opacity-100 w-6 h-6 rounded-full hover:bg-gray-200 flex items-center justify-center transition-all">
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

        <!-- LỊCH PHỤC VỤ SẮP TỚI -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-black text-gray-900 flex items-center gap-2">
              <span class="text-orange-500">🎯</span> Lịch phục vụ sắp tới
            </h3>
            <Link :href="route('calendar.index')" class="text-xs font-bold text-orange-500 hover:underline">Xem tất cả</Link>
          </div>
          <div v-if="upcomingEvents.length > 0" class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div v-for="event in upcomingEvents" :key="event.id"
              class="border border-gray-100 rounded-xl p-4 hover:border-orange-200 hover:shadow-sm transition-all">
              <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">CHỜ XÁC NHẬN</span>
              </div>
              <h4 class="font-bold text-gray-900 text-sm mb-1">{{ event.title }}</h4>
              <p class="text-xs text-gray-500 flex items-center gap-1 mb-0.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ formatDate(event.meeting_date) }}
              </p>
              <p class="text-xs text-gray-500 flex items-center gap-1 mb-3">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ event.location || 'Hội Thánh' }}
              </p>
              <div class="flex gap-2">
                <button class="flex-1 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-black rounded-lg transition-all active:scale-95">
                  Xác nhận
                </button>
                <button class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold rounded-lg transition-all active:scale-95">
                  Từ chối
                </button>
              </div>
            </div>
          </div>
          <div v-else class="py-10 text-center">
            <p class="text-sm text-gray-400">Không có lịch phục vụ sắp tới</p>
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
            <Link :href="route('calendar.index')"
              class="w-full flex items-center gap-3 px-4 py-3 bg-gray-50 hover:bg-orange-50 text-gray-700 hover:text-orange-600 rounded-xl font-bold text-sm transition-all border border-gray-100 hover:border-orange-200">
              <svg class="w-5 h-5 shrink-0 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              Lịch sinh hoạt Hội Thánh
            </Link>
            <Link :href="route('documents.index')"
              class="w-full flex items-center gap-3 px-4 py-3 bg-gray-50 hover:bg-orange-50 text-gray-700 hover:text-orange-600 rounded-xl font-bold text-sm transition-all border border-gray-100 hover:border-orange-200">
              <svg class="w-5 h-5 shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Tài liệu & Tài nguyên
            </Link>
          </div>
        </div>

        <!-- THÔNG BÁO MỚI -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
            <span class="text-orange-500 text-sm">📣</span>
            <h3 class="font-black text-sm text-gray-900">Thông báo mới</h3>
          </div>
          <div v-if="notifications.length > 0" class="divide-y divide-gray-50">
            <div v-for="notif in notifications" :key="notif.id"
              class="px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer">
              <div class="flex items-start gap-2 mb-1">
                <span v-if="notif.urgent" class="shrink-0 text-[9px] font-black bg-red-500 text-white px-1.5 py-0.5 rounded-full uppercase">KHẨN</span>
              </div>
              <p class="font-bold text-gray-900 text-sm leading-snug">{{ notif.title }}</p>
              <p v-if="notif.message" class="text-xs text-gray-500 mt-0.5 leading-relaxed line-clamp-2">{{ notif.message }}</p>
              <p class="text-[10px] text-gray-400 mt-1.5 font-medium uppercase tracking-wide">{{ notif.time }}</p>
            </div>
          </div>
          <div v-else class="px-4 py-8 text-center">
            <p class="text-sm text-gray-400">Không có thông báo mới</p>
          </div>
          <div class="px-4 py-3 border-t border-gray-50 text-center">
            <button class="text-xs font-bold text-orange-500 hover:underline">Tất cả thông báo</button>
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

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
  member:         Object,
  careRequests:   { type: Array, default: () => [] },
  notifications:  { type: Array, default: () => [] },
  upcomingEvents: { type: Array, default: () => [] },
  careCategories: { type: Object, default: () => ({}) },
});

// ── State ──
const activeTab      = ref('home');   // 'home' | 'care'
const showHero       = ref(true);
const calView        = ref('week');
const showNotifPanel = ref(false);

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
    const hasEvent = props.upcomingEvents.some(e => {
      const ed = new Date(e.meeting_date);
      return ed.toDateString() === d.toDateString();
    });
    days.push({
      label:   labels[i],
      num:     d.getDate(),
      date:    d.toDateString(),
      isToday: d.toDateString() === today.toDateString(),
      hasEvent,
    });
  }
  return days;
});

// Events on selected day
const todayEvents = computed(() =>
  props.upcomingEvents.filter(e => new Date(e.meeting_date).toDateString() === selectedDay.value)
);

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
      activeTab.value = 'home';
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
}
</style>
