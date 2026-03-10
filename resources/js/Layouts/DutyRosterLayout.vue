<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
  title:    { type: String, default: 'Phân Công Công Tác' },
  meeting:  { type: Object, default: null },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() =>
  user.value?.roles?.some(r => ['Super_Admin','Pastor','BTS_Admin'].includes(r.name)) ?? false
);
</script>

<template>
  <div class="min-h-screen bg-[#f8f9fc] font-sans text-gray-900 flex flex-col">
    <Head :title="title">
      <!-- PWA Meta Tags -->
      <template #default>
        <link rel="manifest" href="/manifest.json" />
        <meta name="theme-color" content="#4F46E5" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
        <meta name="apple-mobile-web-app-title" content="Phân Công" />
        <link rel="apple-touch-icon" href="/icon-512.png" />
      </template>
    </Head>

    <!-- ── Top Nav ─────────────────────────────────────── -->
    <header class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-30">
      <div class="max-w-6xl mx-auto px-3 sm:px-6 flex items-center justify-between h-12 sm:h-14 gap-2">

        <!-- Logo + Title -->
        <div class="flex items-center gap-2 shrink-0">
          <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-indigo-600 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
          </div>
          <span class="font-black text-gray-900 text-xs sm:text-sm leading-tight">
            <span class="hidden xs:inline">Phân Công Công Tác</span>
            <span class="xs:hidden">Phân Công</span>
          </span>
        </div>

        <!-- Nav links — scrollable on mobile -->
        <nav class="flex items-center gap-0.5 sm:gap-1 overflow-x-auto scrollbar-none min-w-0 flex-1 justify-end">
          <Link :href="route('duty-rooster.index')"
            class="flex items-center gap-1 px-2 sm:px-3 py-1.5 text-[11px] sm:text-xs font-bold rounded-lg transition-colors whitespace-nowrap shrink-0"
            :class="route().current('duty-rooster.index') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'">
            <span>📅</span><span class="hidden sm:inline">Lịch phân công</span>
          </Link>
          <Link :href="route('duty-rooster.templates.index')"
            class="flex items-center gap-1 px-2 sm:px-3 py-1.5 text-[11px] sm:text-xs font-bold rounded-lg transition-colors whitespace-nowrap shrink-0"
            :class="route().current('duty-rooster.templates.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800'">
            <span>📋</span><span class="hidden sm:inline">Template</span>
          </Link>
          <div class="w-px h-4 bg-gray-200 mx-0.5 sm:mx-1 shrink-0 hidden sm:block"></div>
          <Link :href="route('dashboard')"
            class="flex items-center gap-1 px-2 sm:px-3 py-1.5 text-[11px] sm:text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors border border-indigo-100 whitespace-nowrap shrink-0">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
            <span class="hidden sm:inline">{{ isAdmin ? 'Quản trị' : 'Trang chủ' }}</span>
          </Link>
          <Link :href="route('logout')" method="post" as="button"
            class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0v-1m6-10V7a3 3 0 00-6 0v1"/>
            </svg>
          </Link>
        </nav>
      </div>
    </header>

    <!-- ── Page Content ──────────────────────────────────── -->
    <main class="flex-1">
      <slot />
    </main>
  </div>
</template>

<style scoped>
/* Hide scrollbar for nav overflow on mobile */
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>
