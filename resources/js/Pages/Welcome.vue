<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white">

    <!-- === HEADER / NAVBAR === -->
    <header class="border-b border-white/10 backdrop-blur-sm bg-white/5">
      <div class="max-w-5xl mx-auto px-6 py-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-500/30">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a10 10 0 11-4.13 19.14M12 2v4m0 0a6 6 0 00-6 6"/></svg>
          </div>
          <div>
            <div class="font-black text-lg text-white leading-none tracking-tight">HTMS</div>
            <div class="text-xs text-white/50 font-medium">Hệ Thống Quản Lý Hội Thánh</div>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <span class="text-sm text-white/60 hidden sm:block">{{ $page.props.auth.user.name }}</span>
          <Link :href="homePortal"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            {{ portalLabel }}
          </Link>
          <Link :href="route('logout')" method="post" as="button"
            class="p-2 text-white/40 hover:text-white/80 hover:bg-white/10 rounded-xl transition-all" title="Đăng xuất">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          </Link>
        </div>
      </div>
    </header>


    <!-- === HERO: Lời Chúa === -->
    <section class="relative px-6 py-20 md:py-28 text-center overflow-hidden">
      <!-- Nền hiệu ứng -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-[-100px] left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-indigo-600/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-64 h-64 bg-violet-600/10 rounded-full blur-3xl"></div>
      </div>

      <div class="relative max-w-3xl mx-auto">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-amber-400/10 border border-amber-400/20 rounded-full text-amber-300 text-xs font-bold uppercase tracking-widest mb-8">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          Lời Chúa Hôm Nay · Kinh Thánh 1925
        </div>

        <blockquote class="text-2xl md:text-3xl font-light leading-relaxed text-white/90 italic mb-6 tracking-wide">
          "{{ verse.text }}"
        </blockquote>
        <cite class="text-indigo-300 font-bold text-base not-italic">— {{ verse.ref }}</cite>
      </div>
    </section>


    <!-- === THÔNG ĐIỆP HỘI THÁNH === -->
    <section class="px-6 pb-12 max-w-5xl mx-auto">
      <div class="bg-white/5 border border-white/10 rounded-3xl p-8 md:p-12 backdrop-blur-sm flex flex-col md:flex-row gap-8 items-center">
        <div class="shrink-0">
          <div class="w-24 h-24 md:w-32 md:h-32 rounded-3xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-2xl shadow-indigo-500/30">
            <svg class="w-14 h-14 md:w-20 md:h-20 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
          </div>
        </div>
        <div>
          <div class="text-xs font-black uppercase tracking-widest text-indigo-400 mb-2">Chào Mừng Đến Với</div>
          <h1 class="text-2xl md:text-3xl font-black text-white mb-3 leading-tight">Hội Thánh Tin Lành Thành Mỹ Lợi</h1>
          <p class="text-white/60 text-base leading-relaxed">
            Đây là không gian làm việc nội bộ của Hội Thánh. Hệ thống giúp các đầy tớ Chúa phục vụ hiệu quả hơn — từ quản lý tín hữu, sắp xếp lịch phụng vụ, đến theo dõi chăm sóc và ghi chép hành trình thuộc linh.
          </p>
        </div>
      </div>
    </section>


    <!-- === CƠ CẤU BAN NGÀNH (DYNAMIC) === -->
    <section class="px-6 pb-20 max-w-5xl mx-auto" v-if="departments.length">
      <div class="text-center mb-10">
        <h2 class="text-xl font-black text-white mb-2">Cơ Cấu Tổ Chức</h2>
        <p class="text-white/40 text-sm">Các ban ngành và cơ quan trong Hội Thánh</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="dept in departments" :key="dept.id"
          class="group bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-2xl p-6 transition-all duration-300 cursor-default">
          <div class="flex items-start gap-4">
            <div :class="`dept-icon-${dept.color}`"
              class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-110">
              <!-- Shield for leadership -->
              <svg v-if="dept.icon === 'shield'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              <!-- Users for ministry -->
              <svg v-else-if="dept.icon === 'users'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <!-- Calendar for activities -->
              <svg v-else-if="dept.icon === 'calendar'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              <!-- Dollar for finance -->
              <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0">
              <h3 class="font-bold text-white text-sm leading-tight mb-1 truncate">{{ dept.name }}</h3>
              <p v-if="dept.description" class="text-white/40 text-xs leading-relaxed line-clamp-2">{{ dept.description }}</p>
              <span class="inline-block mt-2 text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full"
                :class="blockBadge(dept.block)">
                {{ blockLabel(dept.block) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>


    <!-- === FOOTER === -->
    <footer class="border-t border-white/10 px-6 py-6 text-center text-xs text-white/30">
      HTMS · Hệ Thống Quản Lý Hội Thánh &nbsp;·&nbsp; {{ new Date().getFullYear() }}
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const $page = usePage();

const props = defineProps({
  verse: Object,
  departments: Array,
});

// home_portal từ backend (HandleInertiaRequests)
const homePortal = computed(() => $page.props.auth?.user?.home_portal ?? '/member');

// Label hiển thị phù hợp với từng loại portal
const portalLabel = computed(() => ({
  '/dashboard': 'Bảng Quản Trị',
  '/portal':    'Vào Portal Ban',
  '/ministry':  'Portal Mục Vụ',
  '/deacon':    'Portal Chấp Sự',
  '/member':    'Trang Cá Nhân',
}[homePortal.value] ?? 'Vào Hệ Thống'));

const blockLabel = (block) => ({
  leadership: 'Ban Chấp Sự',
  ministry: 'Ban Ngành',
  activities: 'Sinh Hoạt',
  finance: 'Tài Chính',
}[block] ?? block);

const blockBadge = (block) => ({
  leadership: 'bg-indigo-500/20 text-indigo-300',
  ministry: 'bg-emerald-500/20 text-emerald-300',
  activities: 'bg-amber-500/20 text-amber-300',
  finance: 'bg-blue-500/20 text-blue-300',
}[block] ?? 'bg-white/10 text-white/40');
</script>

<style scoped>
.dept-icon-indigo { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
.dept-icon-emerald { background: linear-gradient(135deg, #059669, #10b981); }
.dept-icon-amber { background: linear-gradient(135deg, #d97706, #f59e0b); }
.dept-icon-blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }
.dept-icon-gray { background: linear-gradient(135deg, #374151, #6b7280); }
</style>
