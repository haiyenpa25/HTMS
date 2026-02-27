<template>
  <component :is="currentLayout">
    <template #header>
      Chi tiết Ban ngành
    </template>

    <div class="py-4 space-y-6 max-w-5xl mx-auto">
      <!-- Back Button -->
      <div class="flex items-center">
        <Link :href="route('departments.index')" class="flex items-center text-sm text-gray-500 hover:text-blue-600 transition-colors group">
          <svg class="w-5 h-5 mr-1 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Quay lại danh sách
        </Link>
      </div>

      <!-- Department Header -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="flex items-center space-x-4">
            <div class="p-4 rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
            <div>
              <div class="flex items-center space-x-2">
                <h1 class="text-3xl font-extrabold text-gray-900">{{ department.name }}</h1>
                <StatusBadge :status="department.is_active ? 'success' : 'gray'">
                  {{ department.is_active ? 'Đang hoạt động' : 'Tạm ngưng' }}
                </StatusBadge>
              </div>
              <p class="text-sm font-mono text-gray-400 mt-1 uppercase tracking-widest">{{ department.code }}</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
             <button class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold transition-all transform hover:scale-[1.02]">
                Chỉnh sửa
             </button>
             <PrimaryButton class="px-6 py-2.5">
                Báo cáo Ban
             </PrimaryButton>
          </div>
        </div>
        
        <div class="mt-8 pt-8 border-t border-gray-50">
          <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Giới thiệu ban ngành</h3>
          <p class="text-gray-600 leading-relaxed">
            {{ department.description || 'Ban ngành này chưa có thông tin mô tả chi tiết từ quản trị viên.' }}
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Teams & Stats -->
        <div class="lg:col-span-1 space-y-6">
          <AppCard title="Số liệu thống kê">
            <div class="grid grid-cols-2 gap-4">
               <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                 <span class="block text-2xl font-black text-blue-600">{{ department.teams.length }}</span>
                 <span class="text-[10px] font-bold text-slate-400 uppercase">Tổ nhóm</span>
               </div>
               <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                 <span class="block text-2xl font-black text-indigo-600">{{ department.members.length }}</span>
                 <span class="text-[10px] font-bold text-slate-400 uppercase">Thành viên</span>
               </div>
            </div>
          </AppCard>

          <AppCard title="Các Tổ / Nhóm trực thuộc">
            <div class="space-y-3">
              <div v-for="team in department.teams" :key="team.id" class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50 transition-all cursor-pointer group">
                <div class="flex items-center space-x-3">
                  <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                  <span class="text-sm font-bold text-gray-700 group-hover:text-blue-700">{{ team.name }}</span>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </div>
              <div v-if="department.teams.length === 0" class="text-center py-6 text-gray-400 text-sm italic">
                Ban này chưa chia tổ.
              </div>
            </div>
          </AppCard>
          
          <AppCard title="Ban điều hành (Giám sát)">
             <div class="space-y-4">
               <div v-for="supervisor in department.supervisors" :key="supervisor.id" class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                    {{ (supervisor.full_name || 'S').charAt(0) }}
                  </div>
                  <div>
                    <p class="text-sm font-bold text-gray-900 leading-none">{{ supervisor.full_name }}</p>
                    <p class="text-[10px] text-gray-400 mt-1 uppercase">Người giám sát</p>
                  </div>
               </div>
               <div v-if="department.supervisors.length === 0" class="text-center py-4 text-gray-400 text-sm italic">
                 Chưa chỉ định người giám sát.
               </div>
             </div>
          </AppCard>
        </div>

        <!-- Right Column: Members List -->
        <div class="lg:col-span-2">
          <AppCard title="Danh sách Thành viên trong Ban">
            <div class="overflow-x-auto">
               <table class="min-w-full divide-y divide-gray-100">
                 <thead>
                   <tr>
                     <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Thành viên</th>
                     <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Vai trò / Tổ</th>
                     <th class="px-4 py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Thao tác</th>
                   </tr>
                 </thead>
                 <tbody class="divide-y divide-gray-50">
                   <tr v-for="member in department.members" :key="member.id" class="hover:bg-slate-50/50 transition-colors">
                     <td class="px-4 py-4 whitespace-nowrap">
                       <div class="flex items-center space-x-3">
                         <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center text-xs font-black">
                            {{ (member.full_name || 'T').charAt(0) }}
                         </div>
                         <div class="text-sm font-bold text-gray-900">{{ member.full_name }}</div>
                       </div>
                     </td>
                     <td class="px-4 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">
                          Thành viên
                        </span>
                     </td>
                     <td class="px-4 py-4 whitespace-nowrap text-right">
                        <Link :href="route('members.show', member.id)" class="text-xs font-bold text-blue-600 hover:text-blue-800 underline">
                          Hồ sơ
                        </Link>
                     </td>
                   </tr>
                   <tr v-if="department.members.length === 0">
                     <td colspan="3" class="px-4 py-12 text-center text-gray-400 italic">
                        Chưa có thành viên nào trong ban này.
                     </td>
                   </tr>
                 </tbody>
               </table>
            </div>
          </AppCard>
        </div>
      </div>
    </div>
  </component>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
  department: Object
});

// Nhận diện kích thước màn hình
const windowWidth = ref(window.innerWidth);
const updateWidth = () => windowWidth.value = window.innerWidth;
onMounted(() => window.addEventListener('resize', updateWidth));
onUnmounted(() => window.removeEventListener('resize', updateWidth));

const currentLayout = computed(() => {
  return windowWidth.value >= 768 ? AuthenticatedLayout : MobileLayout;
});
</script>
