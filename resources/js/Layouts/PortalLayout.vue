<template>
  <div class="h-screen bg-gray-50 flex overflow-hidden font-sans text-gray-900">

    <!-- ── Sidebar (desktop only) ─────────────────────────────────────────── -->
    <aside
      :class="['hidden lg:flex flex-col bg-white border-r border-gray-100 shadow-sm transition-all duration-300',
        sidebarCollapsed ? 'w-[68px]' : 'w-[230px]']">

      <!-- Dept header / switcher -->
      <div class="flex items-center gap-3 px-4 py-4 border-b border-gray-100 min-h-[64px] relative">
        <button @click="isSwitcherOpen = true" class="flex items-center gap-3 flex-1 min-w-0 group">
          <div :class="[
            'shrink-0 flex items-center justify-center rounded-xl font-bold text-white text-sm transition-all',
            sidebarCollapsed ? 'w-9 h-9' : 'w-10 h-10',
            portalType === 'activities' ? 'bg-blue-600' : portalType === 'ministry' ? 'bg-emerald-600' : 'bg-amber-500']">
            {{ department?.name?.charAt(0) || '?' }}
          </div>
          <div v-if="!sidebarCollapsed" class="min-w-0 flex-1 text-left">
            <p class="text-[14px] font-bold text-gray-900 truncate leading-tight">{{ department?.name || 'Cổng Ban Ngành' }}</p>
            <p class="text-[10px] font-bold uppercase tracking-wider mt-0.5"
              :class="portalType === 'activities' ? 'text-blue-500' : portalType === 'ministry' ? 'text-emerald-500' : 'text-amber-500'">
              {{ portalType === 'activities' ? 'Cổng Sinh Hoạt' : portalType === 'ministry' ? 'Cổng Mục Vụ' : 'Lãnh Đạo HT' }}
            </p>
          </div>
          <svg v-if="!sidebarCollapsed && ((availableDepartments && availableDepartments.length > 1) || isGlobalAdmin)"
            class="w-3.5 h-3.5 text-gray-400 group-hover:text-blue-500 transition-colors shrink-0"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
          </svg>
        </button>
        <button @click="sidebarCollapsed = !sidebarCollapsed"
          class="flex items-center justify-center w-6 h-6 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-all absolute -right-3 top-1/2 -translate-y-1/2 bg-white border border-gray-200 shadow-sm z-10">
          <svg :class="['w-3 h-3 transition-transform', sidebarCollapsed ? 'rotate-180' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
      </div>

      <!-- Nav items -->
      <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-1 hide-scrollbar">
        <p v-if="!sidebarCollapsed" class="px-2 pt-1 pb-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Menu</p>
        <template v-for="item in visibleNavItems" :key="item.key">
          <Link v-if="!item.disabled" :href="item.href"
            :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all',
              item.active ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
              sidebarCollapsed ? 'justify-center' : '']"
            :title="sidebarCollapsed ? item.label : ''">
            <svg class="w-5 h-5 shrink-0" :class="item.active ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
            </svg>
            <span v-if="!sidebarCollapsed">{{ item.label }}</span>
            <span v-if="item.active && !sidebarCollapsed" class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
          </Link>
          <span v-else
            :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-gray-300 cursor-not-allowed opacity-60',
              sidebarCollapsed ? 'justify-center' : '']"
            title="Bạn không có quyền truy cập">
            <svg class="w-5 h-5 shrink-0 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
            </svg>
            <span v-if="!sidebarCollapsed">{{ item.label }}</span>
          </span>
        </template>
      </nav>

      <!-- Footer -->
      <div class="border-t border-gray-100 p-3 space-y-1">
        <Link v-if="isGlobalAdmin" :href="route('dashboard')"
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-indigo-50 hover:text-indigo-700 transition-all', sidebarCollapsed ? 'justify-center' : '']"
          :title="sidebarCollapsed ? 'Quản Trị Hệ Thống' : ''">
          <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
          <span v-if="!sidebarCollapsed">Quản Trị Hệ Thống</span>
        </Link>
        <Link :href="route('member.portal.index')"
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-indigo-50 hover:text-indigo-700 transition-all w-full', sidebarCollapsed ? 'justify-center' : '']"
          :title="sidebarCollapsed ? 'Hồ Sơ Của Tôi' : ''">
          <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          <span v-if="!sidebarCollapsed">Hồ Sơ Của Tôi</span>
        </Link>
        <Link :href="route('logout')" method="post" as="button"
          :class="['flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-red-50 hover:text-red-600 transition-all w-full', sidebarCollapsed ? 'justify-center' : '']"
          :title="sidebarCollapsed ? 'Đăng Xuất' : ''">
          <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-6 0v-1m6-10V7a3 3 0 00-6 0v1"/></svg>
          <span v-if="!sidebarCollapsed">Đăng Xuất</span>
        </Link>
      </div>
    </aside>

    <!-- ── Main area ─────────────────────────────────────────────────────────── -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

      <!-- Mobile top bar -->
      <header class="lg:hidden flex items-center justify-between px-4 py-3 bg-white border-b border-gray-100 shadow-sm z-20 shrink-0">
        <button @click="isSwitcherOpen = true" class="flex items-center gap-2 min-w-0">
          <div :class="['w-8 h-8 rounded-xl flex items-center justify-center shrink-0 font-bold text-white text-sm',
            portalType === 'activities' ? 'bg-blue-600' : portalType === 'ministry' ? 'bg-emerald-600' : 'bg-amber-500']">
            {{ department?.name?.charAt(0) || '?' }}
          </div>
          <div class="min-w-0">
            <p class="text-[14px] font-bold text-gray-900 truncate leading-tight">{{ department?.name || 'Ban Ngành' }}</p>
            <p class="text-[10px] font-bold uppercase tracking-wider"
              :class="portalType === 'activities' ? 'text-blue-500' : portalType === 'ministry' ? 'text-emerald-500' : 'text-amber-500'">
              {{ portalType === 'activities' ? 'Sinh Hoạt' : portalType === 'ministry' ? 'Mục Vụ' : 'Lãnh Đạo' }}
            </p>
          </div>
          <svg v-if="(availableDepartments && availableDepartments.length > 1) || isGlobalAdmin"
            class="w-3.5 h-3.5 text-gray-400 shrink-0 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
        <Link v-if="isGlobalAdmin" :href="route('dashboard')" class="w-8 h-8 flex items-center justify-center rounded-xl text-indigo-500 hover:bg-indigo-50 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </Link>
      </header>

      <!-- Content — pb-16 on mobile to make room for bottom nav -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto pb-16 lg:pb-0">
        <div v-if="$page.props.flash?.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
          <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm font-bold rounded-r-xl shadow-sm">
            {{ $page.props.flash.error }}
          </div>
        </div>
        <!-- Backward-compat: render #header slot if provided (old-style pages) -->
        <div v-if="$slots.header" class="bg-white border-b border-gray-100 px-6 py-4 shadow-sm">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <slot name="header" />
          </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <slot />
        </div>
      </main>
    </div>

  </div>

  <!-- ── Mobile Bottom Tab Nav ────────────────────────────────────────────── -->
  <nav class="lg:hidden bg-white border-t border-gray-100 fixed bottom-0 left-0 right-0 z-20 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]" style="padding-bottom: env(safe-area-inset-bottom)">
    <div class="flex justify-around items-center h-14 max-w-lg mx-auto">

      <!-- Dynamic nav from mobileNavItems (max 6) -->
      <template v-for="item in mobileNavItems" :key="item.key">
        <Link v-if="!item.disabled" :href="item.href"
          class="flex flex-col items-center justify-center flex-1 h-full gap-0.5 transition-colors"
          :class="item.active
            ? (portalType === 'activities' ? 'text-blue-600' : portalType === 'ministry' ? 'text-emerald-600' : 'text-amber-500')
            : 'text-gray-500'">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/>
          </svg>
          <span class="text-[9px] font-bold">{{ item.shortLabel || item.label }}</span>
        </Link>
        <span v-else
          class="flex flex-col items-center justify-center flex-1 h-full gap-0.5 text-gray-300 opacity-50 cursor-not-allowed">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/>
          </svg>
          <span class="text-[9px] font-bold">{{ item.shortLabel || item.label }}</span>
        </span>
      </template>

      <!-- Dept Switcher (always last on mobile if multi-dept) -->
      <button v-if="(availableDepartments && availableDepartments.length > 1) || isGlobalAdmin"
        @click="isSwitcherOpen = true"
        class="flex flex-col items-center justify-center flex-1 h-full gap-0.5 text-gray-500 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
        </svg>
        <span class="text-[9px] font-bold">Chuyển</span>
      </button>

      <!-- Nav Settings button (only show if > 6 items available) -->
      <button v-if="visibleNavItems.length > 6"
        @click="isNavSettingsOpen = true"
        class="flex flex-col items-center justify-center flex-1 h-full gap-0.5 text-gray-400 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
        </svg>
        <span class="text-[9px] font-bold">Thêm</span>
      </button>
    </div>
  </nav>

  <!-- Mobile Nav Settings SlideOver -->
  <SlideOver v-model="isNavSettingsOpen" title="Tùy chỉnh Menu Mobile" size="sm">
    <template #default>
      <div class="p-4 space-y-2">
        <p class="text-xs text-gray-500 mb-3">Chọn tối đa 6 mục hiển thị dưới cùng. Mục "Tổng Quan" luôn hiển thị.</p>
        <div v-for="item in visibleNavItems" :key="item.key"
          class="flex items-center justify-between p-3 rounded-xl border transition-all"
          :class="item.key === 'home' ? 'bg-gray-50 border-gray-200 opacity-60' : 'border-gray-200 hover:border-indigo-200 hover:bg-indigo-50'">
          <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"/>
            </svg>
            <span class="text-sm font-bold text-gray-700">{{ item.label }}</span>
          </div>
          <div v-if="item.key === 'home'" class="text-xs text-gray-400 font-bold">Cố định</div>
          <button v-else @click="toggleNavItem(item.key)"
            :class="['relative w-11 h-6 rounded-full transition-all duration-200',
              hiddenNavKeys.has(item.key) ? 'bg-gray-200' : 'bg-indigo-600']">
            <span :class="['absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200',
              hiddenNavKeys.has(item.key) ? 'translate-x-0.5' : 'translate-x-5']"/>
          </button>
        </div>
        <p v-if="selectedNavCount >= 6" class="text-xs text-amber-600 font-bold mt-2">Đã đạt giới hạn 6 mục. Tắt 1 mục để bật mục khác.</p>
      </div>
    </template>
  </SlideOver>

  <!-- Global Context Switcher SlideOver -->
  <SlideOver v-model="isSwitcherOpen" title="Chuyển đổi Ban ngành" size="md">
    <template #default>
      <div class="p-6 space-y-2">
        <div v-for="(depts, block) in allDeptsGrouped" :key="block" class="mb-4">
          <template v-if="depts.length > 0">
            <button @click="toggleBlock(block)" class="w-full flex items-center justify-between text-left focus:outline-none mb-3 group">
              <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest" :class="blockInfo[block]?.color">
                <span>{{ blockInfo[block]?.icon }}</span> {{ blockInfo[block]?.name }}
              </h3>
              <div class="w-6 h-6 rounded flex items-center justify-center transition-colors" :class="expandedBlocks[block] ? 'bg-gray-100 text-gray-500' : 'bg-gray-50 text-gray-400 group-hover:bg-gray-100'">
                <svg :class="['w-4 h-4 transition-transform duration-200', expandedBlocks[block] ? 'rotate-180' : 'rotate-0']" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
              </div>
            </button>
            <div v-show="expandedBlocks[block]" class="space-y-2 mb-2">
              <button v-for="dept in depts" :key="dept.id"
                @click="switchDept(dept.id)"
                class="w-full text-left p-4 rounded-2xl border-2 transition-all flex items-center justify-between group"
                :class="department?.id === dept.id
                  ? 'border-blue-500 bg-blue-50 ring-4 ring-blue-50'
                  : 'border-gray-100 bg-white hover:border-blue-300 hover:bg-gray-50'">
                <div class="flex items-center space-x-4">
                  <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-colors"
                    :class="department?.id === dept.id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500 group-hover:bg-blue-100 group-hover:text-blue-600'">
                    {{ dept.name.charAt(0) }}
                  </div>
                  <div class="min-w-0">
                    <h4 class="text-sm font-bold truncate" :class="department?.id === dept.id ? 'text-blue-900' : 'text-gray-900'">{{ dept.name }}</h4>
                    <p v-if="department?.id === dept.id" class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">Đang hoạt động</p>
                    <p v-else class="text-[10px] text-gray-400 font-medium">Bấm để chuyển sang</p>
                  </div>
                </div>
                <svg v-if="department?.id === dept.id" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <svg v-else class="w-4 h-4 text-gray-300 group-hover:text-blue-400 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
              </button>
            </div>
          </template>
        </div>
        <div v-if="Object.values(allDeptsGrouped).every(d => d.length === 0)" class="text-center py-12">
          <p class="text-gray-400 font-bold">Bạn chưa được phân quyền vào Ban ngành nào.</p>
        </div>
      </div>
    </template>
  </SlideOver>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import SlideOver from '@/Components/SlideOver.vue';
import { usePermissions } from '@/Composables/usePermissions';

// Allow child pages to trigger the dept switcher via @open-switcher
const emit = defineEmits(['openSwitcher']);

const page = usePage();
// Single source of truth: auth.allowed_features (computed by PortalService in HandleInertiaRequests)
const { can, isSuperAdmin: isAdmin } = usePermissions();

const props = defineProps({
  department:           Object,
  availableDepartments: Array,
  isGlobalAdmin:        Boolean,
  userPermissions:      { type: Object, default: () => ({}) }, // kept for backward compat, not used for nav
  portalType:           { type: String, default: 'activities' },
  hideNav:              { type: Boolean, default: false },
  title:                { type: String, default: null },
});

// Sidebar / switcher state
const sidebarCollapsed = ref(false);
const isSwitcherOpen   = ref(false);
const allDeptsGrouped  = computed(() => page.props.allAvailableDepartments || {});

const blockInfo = {
  activities: { name: 'Ban Ngành Sinh Hoạt',        icon: '🎯', color: 'text-blue-600' },
  ministry:   { name: 'Ban Ngành Mục Vụ',            icon: '⛪', color: 'text-emerald-600' },
  leadership: { name: 'Ban Chấp Sự / Lãnh Đạo',     icon: '🛡', color: 'text-amber-600' },
};

const activeBlockForSwitcher = props.portalType === 'deacon' ? 'leadership' : props.portalType;
const expandedBlocks = ref({
  activities: activeBlockForSwitcher === 'activities',
  ministry: activeBlockForSwitcher === 'ministry',
  leadership: activeBlockForSwitcher === 'leadership'
});

const toggleBlock = (block) => {
  expandedBlocks.value[block] = !expandedBlocks.value[block];
};

const switchDept = (deptId) => {
  if (deptId === 'secretary' || deptId === 'treasurer') {
    router.post(route('deacon.switch-role'), { role: deptId }, {
      preserveScroll: true,
      onSuccess: () => { isSwitcherOpen.value = false; },
    });
  } else if (props.portalType === 'ministry') {
    // Ministry portal: update ministry session key
    router.post(route('ministry.switch-context'), { department_id: deptId }, {
      preserveScroll: true,
      onSuccess: () => { isSwitcherOpen.value = false; },
    });
  } else {
    // Activities portal (default)
    router.post(route('portal.switch-context'), { department_id: deptId }, {
      preserveScroll: true,
      onSuccess: () => { isSwitcherOpen.value = false; },
    });
  }
};

// ── Nav item definitions per portal type ─────────────────────────────────────
// Icons are SVG `d` path strings from Heroicons
const ICONS = {
  dashboard:  'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  attendance: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
  visitation: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
  members:    'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
  reports:    'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
  finance:    'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  assignment: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
  education:  'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
  logs:       'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  documents:  'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
  care:       'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};

/**
 * visibleNavItems — Nguồn sự thật duy nhất: can(slug) từ usePermissions
 * can(slug) đọc từ auth.allowed_features (PortalService::getAllowedFeaturesForDept)
 * Quy tắc: can(slug) = true → hiện và enable; false → ẩn hoàn toàn
 */
const visibleNavItems = computed(() => {
  const type = props.portalType;

  const always = [
    { key: 'home', label: 'Tổng Quan', icon: ICONS.dashboard,
      href: type === 'activities' ? route('portal.index') : type === 'ministry' ? route('ministry.index') : route('deacon.index'),
      active: false, disabled: false },
  ];

  if (type === 'activities') {
    return [
      ...always,
      can('attendance')   && { key: 'attendance',   label: 'Điểm Danh',  icon: ICONS.attendance, href: route('portal.attendance.index'),     active: route().current('portal.attendance.*'),     disabled: false },
      can('visitation')   && { key: 'visitation',   label: 'Thăm Viếng', icon: ICONS.visitation, href: route('portal.visitation.index'),     active: route().current('portal.visitation.*'),     disabled: false },
      can('care')         && { key: 'care',         label: 'Chăm Sóc',   icon: ICONS.care,       href: route('portal.care.index'),           active: route().current('portal.care.*'),           disabled: false },
      can('members')      && { key: 'members',      label: 'Thành Viên', icon: ICONS.members,    href: route('portal.members.index'),        active: route().current('portal.members.*'),        disabled: false },
      can('reports')      && { key: 'reports',      label: 'Báo Cáo',    icon: ICONS.reports,    href: route('portal.reports.index'),        active: route().current('portal.reports.*'),        disabled: false },
      can('finance')      && { key: 'finance',      label: 'Tài Chính',  icon: ICONS.finance,    href: route('portal.finance.index'),        active: route().current('portal.finance.*'),        disabled: false },
      can('documents')    && { key: 'documents',    label: 'Tài Liệu',   icon: ICONS.documents,  href: route('portal.documents.index'),      active: route().current('portal.documents.*'),      disabled: false },
      can('chronicles')   && { key: 'chronicles',   label: 'Sổ Tay HT',  icon: ICONS.logs,       href: route('portal.chronicles.index'),     active: route().current('portal.chronicles.*'),     disabled: false },
      can('assignments')  && { key: 'assignments',  label: 'Phân Công',  icon: ICONS.assignment, href: route('portal.duty-rooster.index'),   active: route().current('portal.duty-rooster.*'),  disabled: false },
      can('activity-logs')&& { key: 'logs',         label: 'Nhật Ký',    icon: ICONS.logs,       href: route('portal.logs'),                active: route().current('portal.logs'),             disabled: false },
    ].filter(Boolean);
  }

  if (type === 'ministry') {
    return [
      ...always,
      can('members')              && { key: 'members',   label: 'Thành Viên',    icon: ICONS.members,    href: route('ministry.members.index'),          active: route().current('ministry.members.*'),          disabled: false },
      can('visitation')           && { key: 'visitation', label: 'Thăm Viếng',    icon: ICONS.visitation, href: route('ministry.visitation.index'),        active: route().current('ministry.visitation.*'),        disabled: false },
      can('care')                 && { key: 'care',       label: 'Chăm Sóc',      icon: ICONS.care,       href: route('ministry.care.index'),             active: route().current('ministry.care.*'),             disabled: false },
      can('education-classes')    && { key: 'classes',   label: 'Lớp Học',        icon: ICONS.education,  href: route('ministry.education.classes'),      active: route().current('ministry.education.classes'),  disabled: false },
      can('education-report')     && { key: 'edu-rep',   label: 'BC Giáo Dục',    icon: ICONS.reports,    href: route('ministry.education.report'),       active: route().current('ministry.education.report'),   disabled: false },
      can('documents')            && { key: 'documents', label: 'Tài Liệu',       icon: ICONS.documents,  href: route('ministry.documents.index'),        active: route().current('ministry.documents.*'),        disabled: false },
      can('chronicles')           && { key: 'chronicles', label: 'Sổ Tay HT',     icon: ICONS.logs,       href: route('ministry.chronicles.index'),       active: route().current('ministry.chronicles.*'),       disabled: false },
      can('assignments')          && { key: 'assignments', label: 'Phân Công',    icon: ICONS.assignment, href: route('ministry.duty-rooster.index'),     active: route().current('ministry.duty-rooster.*'),     disabled: false },
      can('activity-logs')        && { key: 'logs',       label: 'Nhật Ký',        icon: ICONS.logs,       href: route('ministry.logs'),                  active: route().current('ministry.logs'),               disabled: false },
    ].filter(Boolean);
  }

  // Deacon portal
  const deaconRole = page.props.activeDeaconRole;
  return [
    ...always,
    deaconRole === 'secretary' && can('attendance') && { key: 'attendance', label: 'Điểm Danh',   icon: ICONS.attendance, href: route('deacon.attendance'),         active: route().current('deacon.attendance.*'), disabled: false },
    deaconRole === 'secretary' && can('reports')    && { key: 'reports',    label: 'Báo Cáo',     icon: ICONS.reports,    href: route('deacon.report'),             active: route().current('deacon.report.*'),    disabled: false },
    can('members')                                  && { key: 'members',    label: 'Thành Viên',  icon: ICONS.members,    href: route('deacon.members.index'),      active: route().current('deacon.members.*'),   disabled: false },
    deaconRole === 'treasurer' && can('finance')    && { key: 'finance',    label: 'Quản Lý Quỹ', icon: ICONS.finance,    href: route('finance.index'),             active: route().current('finance.*'),          disabled: false },
    can('assignments')                              && { key: 'assignments', label: 'Phân Công',   icon: ICONS.assignment, href: route('deacon.duty-rooster.index'), active: route().current('deacon.duty-rooster.*'), disabled: false },
  ].filter(Boolean);
});

// Mobile nav: max 6, user-configurable via localStorage
const MOBILE_NAV_MAX = 6;
const isNavSettingsOpen = ref(false);

// Load user hidden nav preferences from localStorage (per portal type)
const navPrefKey = computed(() => `portal_nav_hidden_${props.portalType}`);
const hiddenNavKeys = ref(new Set(
  JSON.parse(localStorage.getItem(`portal_nav_hidden_${props.portalType}`) || '[]')
));

const toggleNavItem = (key) => {
  const hidden = new Set(hiddenNavKeys.value);
  if (hidden.has(key)) {
    hidden.delete(key); // show it
  } else if (selectedNavCount.value >= MOBILE_NAV_MAX) {
    return; // already at max, can't add more
  } else {
    hidden.add(key); // hide it
  }
  hiddenNavKeys.value = hidden;
  localStorage.setItem(navPrefKey.value, JSON.stringify([...hidden]));
};

// How many items are currently visible (not hidden, not counting 'home' which is always visible)
const selectedNavCount = computed(() =>
  visibleNavItems.value.filter(i => i.key !== 'home' && !hiddenNavKeys.value.has(i.key)).length + 1 // +1 for home
);

// Mobile shows up to MOBILE_NAV_MAX items, filtering out hidden ones
const mobileNavItems = computed(() => {
  const ordered = visibleNavItems.value.filter(
    i => i.key === 'home' || !hiddenNavKeys.value.has(i.key)
  );
  return ordered.slice(0, MOBILE_NAV_MAX);
});

</script>

<style scoped>
@supports (padding-bottom: env(safe-area-inset-bottom)) {
  .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
}
</style>
