<template>
  <component :is="currentLayout">
    <template #header>
      Phân quyền Hệ thống
    </template>

    <div class="py-4 space-y-6 max-w-7xl mx-auto">
      <!-- Intro Section -->
      <div class="bg-gradient-to-br from-indigo-700 to-blue-800 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute inset-0 bg-grid-white/[0.1] bg-[size:16px_16px]"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
           <div>
              <h1 class="text-3xl font-black mb-2 leading-none">Nhóm Vai trò (Roles)</h1>
              <p class="text-blue-100 max-w-xl">
                 Hệ thống phân quyền được kiểm soát chặt chẽ thông qua các Nhóm Vai trò. Bạn có thể xem và điều chỉnh quyền hạn chi tiết cho từng nhóm này.
              </p>
           </div>
           <!-- Role Action (Tương lai có thể thêm Role Create form) -->
           <div class="flex-shrink-0">
              <button class="px-6 py-3 bg-white text-blue-700 rounded-2xl font-black hover:bg-blue-50 transition-colors shadow-sm">
                 + Thêm Vai trò mới
              </button>
           </div>
        </div>
      </div>

      <!-- Roles Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        <div v-for="role in roles" :key="role.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 hover:shadow-md hover:border-blue-200 transition-all group flex flex-col">
           <div class="flex items-start justify-between mb-3">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-lg" 
                   :class="{
                     'bg-red-100 text-red-600': role.name === 'Super Admin',
                     'bg-purple-100 text-purple-600': role.name === 'Pastor',
                     'bg-blue-100 text-blue-600': !['Super Admin', 'Pastor'].includes(role.name)
                   }">
                 {{ role.name.charAt(0) }}
              </div>
              
              <!-- Users count badge -->
              <div class="px-2 py-0.5 bg-gray-50 rounded-lg text-[10px] font-bold text-gray-600 border border-gray-100 flex items-center" title="Số lượng tài khoản">
                 <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                 {{ role.users_count || 0 }}
              </div>
           </div>
           
           <h3 class="text-base font-black text-gray-900 mb-1 leading-tight">{{ role.name }}</h3>
           
           <div class="flex-1 mt-1">
              <p class="text-xs text-gray-500 mb-4 line-clamp-2 leading-relaxed">
                 {{ getRoleDescription(role.name) }}
              </p>
           </div>
           
           <div class="pt-3 mt-auto border-t border-gray-50 flex items-center justify-between">
              <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ role.permissions_count }} Quyền hạn</span>
              <Link :href="route('roles.show', role.id)" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center group-hover:underline">
                 Mở
                 <svg class="w-3.5 h-3.5 ml-0.5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
              </Link>
           </div>
        </div>
      </div>
      
      <!-- Warning Alert -->
      <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex mt-8">
         <div class="flex-shrink-0 mt-0.5">
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
         </div>
         <div class="ml-3 text-sm text-amber-800">
            <strong>Chú ý an toàn:</strong> Vai trò <code>Super Admin</code> là nhóm phân quyền tối cao, có thể can thiệp toàn bộ hệ thống bỏ qua kiểm tra Permission. Bạn không nên thu hồi hay đổi tên vai trò này.
         </div>
      </div>
    </div>
  </component>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';

defineProps({
  roles: Array,
});

const getRoleDescription = (name) => {
   const descs = {
      'Super Admin': 'Quản trị viên cấp cao nhất. Có toàn quyền quản lý hệ thống, phân quyền và điều chỉnh kỹ thuật.',
      'Pastor': 'Mục sư Quản nhiệm. Được cấp quyền truy cập vào thông tin tín hữu lớp 3 (Nhạy cảm/Mục vụ).',
      'Ban Lãnh Đạo': 'Ban Trị sự, Chấp sự. Quản lý toàn bộ tín hữu, lên kế hoạch cho các Khối.',
      'Trưởng Ban': 'Quản lý một ban ngành cụ thể, xem danh sách tín hữu trong ban.',
      'Thư ký Hội Thánh': 'Hỗ trợ quản lý dữ liệu, báo cáo, cập nhật hồ sơ tín hữu.',
   };
   return descs[name] || `Vai trò nghiệp vụ ${name}. Được cấp phát các quyền tương ứng bởi quản trị viên.`;
};

// Layout Manager
const windowWidth = ref(window.innerWidth);
const updateWidth = () => windowWidth.value = window.innerWidth;
onMounted(() => window.addEventListener('resize', updateWidth));
onUnmounted(() => window.removeEventListener('resize', updateWidth));

const currentLayout = computed(() => {
  return windowWidth.value >= 768 ? AuthenticatedLayout : MobileLayout;
});
</script>
