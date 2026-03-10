<script setup>
import { Link } from '@inertiajs/vue3';
import AdminPortalLayout from '@/Layouts/AdminPortalLayout.vue';

defineProps({ roles: Array });

const getRoleDescription = (name) => {
  const descs = {
    'Super_Admin':    'Quyền tối cao — Bypass toàn bộ kiểm tra. Không nên cấp rộng rãi.',
    'Pastor':         'Mục Sư / Quản nhiệm. Duyệt báo cáo, xem toàn cục nhưng không xoá hệ thống.',
    'Administrator':  'Quản trị nội bộ. Giới hạn tuỳ biến các cài đặt hệ thống lõi.',
  };
  return descs[name] || `Vai trò nghiệp vụ "${name}". Quyền hạn được gán tuỳ theo yêu cầu tổ chức.`;
};

const roleIconColor = (name) => {
  if (name === 'Super_Admin') return 'bg-red-100 text-red-600';
  if (name === 'Pastor')      return 'bg-purple-100 text-purple-600';
  return 'bg-indigo-100 text-indigo-600';
};
</script>

<template>
  <AdminPortalLayout title="Quản lý Chức vụ" active-tab="roles">

    <!-- Intro Banner -->
    <div class="bg-gradient-to-br from-indigo-700 to-blue-800 rounded-3xl p-6 sm:p-8 text-white shadow-lg mb-6 relative overflow-hidden">
      <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_60%_50%,white,transparent_70%)]"></div>
      <div class="relative z-10">
        <h1 class="text-2xl sm:text-3xl font-black mb-2">Chức vụ Toàn Cục</h1>
        <p class="text-blue-100 text-sm max-w-xl">Quản lý các Vai trò cấp toàn hệ thống (Super Admin, Mục Sư). Đây là tầng quyền hạn nền, không phụ thuộc vào Ban Ngành.</p>
      </div>
    </div>

    <!-- Roles Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
      <div v-for="role in roles" :key="role.id"
        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-indigo-200 transition-all group flex flex-col">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-lg" :class="roleIconColor(role.name)">
            {{ role.name.charAt(0) }}
          </div>
          <div class="px-2 py-0.5 bg-gray-50 rounded-lg text-[10px] font-bold text-gray-600 border border-gray-100 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            {{ role.users_count || 0 }}
          </div>
        </div>
        <h3 class="text-sm font-black text-gray-900 mb-1">{{ role.name }}</h3>
        <p class="text-xs text-gray-500 mb-4 flex-1 line-clamp-2 leading-relaxed">{{ getRoleDescription(role.name) }}</p>
        <div class="pt-3 border-t border-gray-50 flex items-center justify-between mt-auto">
          <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ role.permissions_count }} Quyền</span>
          <Link :href="route('roles.show', role.id)" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center">
            Xem
            <svg class="w-3.5 h-3.5 ml-0.5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
          </Link>
        </div>
      </div>
    </div>

    <!-- Warning -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex gap-3">
      <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
      <p class="text-sm text-amber-800">
        <strong>Chú ý:</strong> Vai trò <code class="bg-amber-100 px-1 rounded">Super_Admin</code> có quyền bypass toàn bộ kiểm tra. Không thu hồi hoặc đổi tên vai trò này.
      </p>
    </div>

  </AdminPortalLayout>
</template>