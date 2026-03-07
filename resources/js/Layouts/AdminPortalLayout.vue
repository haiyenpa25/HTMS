<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    title:     { type: String, default: 'Quản trị Hệ thống' },
    activeTab: { type: String, default: 'users' }, // used when no slot override
    hideTabs:  { type: Boolean, default: false },   // hide default tab bar
});

// Default tabs shown when no #tabs slot is provided
const defaultTabs = [
    { key: 'users',       label: '👥 Tài khoản',  route: 'users.index' },
    { key: 'roles',       label: '🛡️ Quyền',       route: 'roles.index' },
    { key: 'permissions', label: '🔐 Phân Quyền', route: 'admin.users.permissions' },
];
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col font-sans text-gray-900">
    <Head :title="title" />

    <!-- ── Header ── -->
    <header class="bg-indigo-700 text-white shadow-md shrink-0 sticky top-0 z-30">
      <div class="px-4 py-3 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full flex items-center justify-between gap-4">
        <!-- Left: Icon + Title -->
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <div>
            <h1 class="text-sm sm:text-base font-black leading-tight">{{ title }}</h1>
            <p class="text-[10px] sm:text-xs text-indigo-200 font-medium">Cổng Quản Trị Hệ Thống</p>
          </div>
        </div>

        <!-- Right: Dashboard + Logout -->
        <div class="flex items-center gap-2">
          <Link :href="route('dashboard')"
            class="text-xs font-bold bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg flex items-center transition-colors">
            Bảng điều khiển
            <svg class="w-3.5 h-3.5 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
          </Link>
          <Link :href="route('logout')" method="post" as="button"
            class="text-white bg-white/10 hover:bg-red-500/40 transition-colors p-2 rounded-full" title="Đăng xuất">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0v-1m6-10V7a3 3 0 00-6 0v1"/>
            </svg>
          </Link>
        </div>
      </div>

      <!-- Tab Navigation: Use slot if provided, otherwise default tabs -->
      <div class="px-2 pb-0 flex overflow-x-auto no-scrollbar border-t border-white/10 max-w-7xl mx-auto w-full">
        <!-- Slot override (e.g., Users/Index.vue overrides with its own tabs) -->
        <slot name="tabs">
          <!-- Default tabs (Tài khoản | Quyền | Phân Quyền) -->
          <Link v-if="!hideTabs"
            v-for="tab in defaultTabs" :key="tab.key"
            :href="route(tab.route)"
            class="px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2"
            :class="activeTab === tab.key
              ? 'border-white text-white'
              : 'border-transparent text-indigo-200 hover:text-white hover:border-indigo-300'">
            {{ tab.label }}
          </Link>
        </slot>
      </div>
    </header>

    <!-- ── Main Content ── -->
    <main class="flex-1 overflow-y-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <slot />
      </div>
    </main>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
