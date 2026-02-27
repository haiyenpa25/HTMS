<template>
  <component :is="currentLayout">
    <template #header>
      Chi tiết Nhóm Vai trò
    </template>

    <div class="py-4 space-y-6 max-w-5xl mx-auto">
      <!-- Back Button -->
      <div class="flex items-center">
        <Link :href="route('roles.index')" class="flex items-center text-sm text-gray-500 hover:text-blue-600 transition-colors group">
          <svg class="w-5 h-5 mr-1 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Quay lại danh sách
        </Link>
      </div>

      <!-- Header -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
         <div class="flex items-center space-x-6">
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center font-black text-4xl shadow-md"
                 :class="{
                   'bg-red-100 text-red-600 shadow-red-100': role.name === 'Super Admin',
                   'bg-purple-100 text-purple-600 shadow-purple-100': role.name === 'Pastor',
                   'bg-blue-100 text-blue-600 shadow-blue-100': !['Super Admin', 'Pastor'].includes(role.name)
                 }">
               {{ role.name.charAt(0) }}
            </div>
            <div>
               <h1 class="text-3xl font-black text-gray-900 mb-1">{{ role.name }}</h1>
               <div class="flex items-center space-x-4 text-sm text-gray-500 font-bold">
                  <span>{{ role.users_count }} Tài khoản</span>
                  <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                  <span>{{ role.permissions.length }} Quyền hạn</span>
               </div>
            </div>
         </div>
         
         <div v-show="form.isDirty" class="flex-shrink-0 flex items-center space-x-3 bg-yellow-50 p-3 rounded-2xl border border-yellow-100">
            <span class="text-xs font-bold text-yellow-800">Cần lưu thay đổi</span>
            <button @click="submit" :disabled="form.processing" class="px-6 py-2 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 transition-colors disabled:opacity-50 shadow-sm">
               {{ form.processing ? 'Đang lưu...' : 'Lưu quyền hạn' }}
            </button>
         </div>
      </div>

      <div v-if="role.name === 'Super Admin'" class="bg-red-50 border border-red-200 rounded-2xl p-6 text-red-800">
         <div class="flex items-center font-black text-lg mb-2">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            Tài khoản Super Admin
         </div>
         <p>Vai trò này luôn có toàn quyền điều khiển hệ thống, bất kể danh sách các quyền chi tiết (Permissions) có được chọn hay không. Cài đặt bên dưới chỉ mang tính chất minh họa.</p>
      </div>

      <!-- Permissions Matrix -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
         <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between">
            <h2 class="text-xl font-black text-gray-900">Ma trận Quyền hạn (Permissions)</h2>
            <div class="text-sm font-bold text-gray-500 mt-2 sm:mt-0">
               Trượt để Bật / Tắt quyền hệ thống
            </div>
         </div>
         
         <div class="p-6 space-y-8">
            <div v-if="Object.keys(groupedPermissions).length === 0" class="py-12 text-center text-gray-400 italic">
               Hệ thống chưa đăng ký quyền hạn nào.
            </div>

            <!-- Grouped by Module -->
            <div v-for="(perms, groupName) in groupedPermissions" :key="groupName" class="border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                <!-- Group Header -->
                <div class="bg-gray-50/80 px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                   <div class="flex items-center space-x-3">
                      <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                         </svg>
                      </div>
                      <h3 class="text-base font-black text-gray-800 tracking-wide uppercase">
                         Module: {{ formatGroupName(groupName) }}
                      </h3>
                   </div>
                   
                   <!-- Check All Toggle cho nhóm -->
                   <button v-if="role.name !== 'Super Admin'" type="button" @click="toggleGroup(perms)" class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                      Bật/Tắt toàn bộ
                   </button>
                </div>

                <!-- Group Permissions Items -->
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                   <div v-for="perm in perms" :key="perm.id" class="flex items-center justify-between p-3 rounded-xl border transition-colors cursor-pointer group"
                        :class="form.permissions.includes(perm.name) ? 'bg-blue-50/50 border-blue-200' : 'bg-white border-gray-200 hover:border-blue-100'"
                        @click="togglePermission(perm.name)">
                      
                      <div class="flex-1 pr-3">
                         <div class="text-sm font-bold transition-colors" :class="form.permissions.includes(perm.name) ? 'text-blue-900' : 'text-gray-700'">{{ formatActionName(perm.name) }}</div>
                      </div>
                      
                      <!-- Custom Switch -->
                      <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full transition-colors shrink-0 outline outline-1 outline-gray-300/50 shadow-inner"
                           :class="{ 'bg-blue-600 outline-blue-700': form.permissions.includes(perm.name) }">
                         <div class="w-4 h-4 bg-white rounded-full transition-transform transform translate-x-0.5 translate-y-0.5 shadow-sm"
                              :class="{ 'translate-x-[16px]': form.permissions.includes(perm.name) }"></div>
                      </div>
                   </div>
                </div>
            </div>

         </div>
      </div>
    </div>
  </component>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const props = defineProps({
  role: Object,
  groupedPermissions: Object, // Đã thay đổi từ Array sang Object gom nhóm
});

// Format tên Group Module
const formatGroupName = (name) => {
   const resourceMap = {
      'users': 'Người dùng', 
      'roles': 'Phân quyền', 
      'members': 'Tín hữu', 
      'departments': 'Ban ngành', 
      'sensitive_info': 'Thông tin Nhạy cảm'
   };
   return resourceMap[name] || name;
};

// Format hành động (VD: view users -> Xem)
const formatActionName = (name) => {
   const action = name.split(' ')[0];
   const actionMap = {
      'view': 'Xem', 'create': 'Thêm mới', 'edit': 'Chỉnh sửa', 'delete': 'Xóa', 'manage': 'Quản lý'
   };
   
   return actionMap[action] || action;
};

const form = useForm({
  permissions: props.role.permissions.map(p => p.name),
});

const togglePermission = (name) => {
   if (props.role.name === 'Super Admin') return; // Ngăn chặn tương tác nếu là SuperAdmin
   
   const index = form.permissions.indexOf(name);
   if (index === -1) {
      form.permissions.push(name);
   } else {
      form.permissions.splice(index, 1);
   }
};

const toggleGroup = (perms) => {
   if (props.role.name === 'Super Admin') return;
   
   const permNames = perms.map(p => p.name);
   // Kiểm tra xem nhóm này đã được toggle hết chưa
   const allSelected = permNames.every(name => form.permissions.includes(name));
   
   if (allSelected) {
       // Bỏ chọn tất cả
       form.permissions = form.permissions.filter(name => !permNames.includes(name));
   } else {
       // Chọn tất cả (những cái chưa chọn)
       permNames.forEach(name => {
           if (!form.permissions.includes(name)) {
               form.permissions.push(name);
           }
       });
   }
};

const submit = () => {
   form.put(route('roles.update', props.role.id), {
      preserveScroll: true,
      onSuccess: () => {
         // Optionally show a toast here, flash is handled by layout
      }
   });
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
