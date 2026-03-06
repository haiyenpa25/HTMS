<template>
  <component :is="currentLayout">
    <template #header>
      Quản lý Người dùng
    </template>

    <div class="py-4 space-y-6">
      <!-- Search & Actions -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="relative w-full md:w-96">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <input
            v-model="search"
            type="text"
            placeholder="Tìm theo tên, email..."
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm shadow-sm"
          />
        </div>
        
        <PrimaryButton @click="openCreateModal" class="w-full md:w-auto flex justify-center items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          Tạo Tài Khoản
        </PrimaryButton>
      </div>

      <!-- Data View -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Desktop Table View (Hidden on mobile) -->
        <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
               <tr>
                 <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Họ tên & Email</th>
                 <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Liên hệ</th>
                 <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Phân quyền gốc (Role)</th>
                 <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ban Ngành</th>
                 <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
               </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
               <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 transition-colors group">
                 <td class="px-6 py-4 whitespace-nowrap">
                   <div class="flex items-center">
                     <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                       {{ (user.name || 'U').charAt(0) }}
                     </div>
                     <div class="ml-4">
                       <div class="text-sm font-bold text-gray-900">{{ user.name }}</div>
                       <div class="text-sm text-gray-500">{{ user.email }}</div>
                     </div>
                   </div>
                 </td>
                 <td class="px-6 py-4 whitespace-nowrap">
                     <div class="text-sm text-gray-900">{{ user.phone || 'Chưa cập nhật' }}</div>
                 </td>
                 <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                          :class="{
                            'bg-red-100 text-red-800': user.role === 'Super_Admin',
                            'bg-purple-100 text-purple-800': user.role === 'Pastor',
                            'bg-blue-100 text-blue-800': !['Super_Admin', 'Pastor', 'Guest'].includes(user.role),
                            'bg-gray-100 text-gray-800': user.role === 'Guest'
                          }">
                      {{ user.role }}
                    </span>
                 </td>
                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 w-48 truncate">
                   <div class="truncate max-w-xs" :title="user.departments">{{ user.departments }}</div>
                 </td>
                 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                   <button @click="openEditModal(user)" class="text-indigo-600 hover:text-indigo-900 font-bold bg-indigo-50 px-3 py-1 rounded-lg mr-2 transition-colors">Sửa</button>
                   <button @click="confirmDelete(user)" class="text-red-600 hover:text-red-900 font-bold bg-red-50 px-3 py-1 rounded-lg transition-colors border border-red-100">Xóa</button>
                 </td>
               </tr>
               <tr v-if="users.data.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                     Không tìm thấy tài khoản người dùng nào.
                  </td>
               </tr>
            </tbody>
          </table>
        </div><!-- End Desktop Table View -->

        <!-- Mobile Card View (Hidden on desktop) -->
        <div class="md:hidden divide-y divide-gray-100">
           <div v-for="user in users.data" :key="user.id" class="p-4 bg-white hover:bg-gray-50 transition-colors">
              <div class="flex items-start justify-between">
                 <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                       {{ (user.name || 'U').charAt(0) }}
                    </div>
                    <div class="ml-3">
                       <h3 class="text-sm font-bold text-gray-900">{{ user.name }}</h3>
                       <p class="text-xs text-gray-500">{{ user.email }}</p>
                    </div>
                 </div>
                 <div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold"
                          :class="{
                            'bg-red-100 text-red-800': user.role === 'Super_Admin',
                            'bg-purple-100 text-purple-800': user.role === 'Pastor',
                            'bg-blue-100 text-blue-800': !['Super_Admin', 'Pastor', 'Guest'].includes(user.role),
                            'bg-gray-100 text-gray-800': user.role === 'Guest'
                          }">
                      {{ user.role }}
                    </span>
                 </div>
              </div>
              <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-gray-600">
                 <div>
                    <span class="font-semibold text-gray-800">SĐT:</span> {{ user.phone || 'Chưa cập nhật' }}
                 </div>
                 <div class="truncate">
                    <span class="font-semibold text-gray-800">Ban:</span> {{ user.departments }}
                 </div>
              </div>
              <div class="mt-4 flex justify-end gap-2 border-t border-gray-50 pt-3">
                 <button @click="openEditModal(user)" class="flex-1 text-center text-indigo-700 bg-indigo-50 py-2 rounded-lg font-bold text-sm">Sửa thông tin</button>
                 <button @click="confirmDelete(user)" class="flex-1 text-center text-red-600 bg-red-50 py-2 rounded-lg font-bold text-sm shadow-sm border border-red-100">Xóa</button>
              </div>
           </div>
           <div v-if="users.data.length === 0" class="p-8 text-center text-gray-500 italic">
               Không tìm thấy người dùng.
           </div>
        </div><!-- End Mobile Card View -->

      </div>
      
      <!-- Pagination -->
      <div v-if="users.links.length > 3" class="flex justify-center mt-6">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
          <template v-for="(link, k) in users.links" :key="k">
             <Link
               v-if="link.url"
               :href="link.url"
               v-html="link.label"
               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
               :class="{ 'bg-blue-50 text-blue-600 border-blue-500 z-10': link.active }"
             />
             <span
               v-else
               v-html="link.label"
               class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-400"
             />
          </template>
        </nav>
      </div>

    </div>
    
    <!-- Include User Form Modal Component -->
    <UserFormModal 
        v-if="showModal" 
        :show="showModal" 
        :roles="roles"
        :editingUser="selectedUser"
        @close="closeModal" 
    />
    
    <!-- Include Delete Confirmation Modal -->
    <DeleteConfirmModal
        v-if="showDeleteModal"
        :show="showDeleteModal"
        :title="'Xóa Tài Khoản'"
        :message="'Bạn có chắc chắn muốn xóa tài khoản ' + userToDelete?.name + '? Tất cả dữ liệu đăng nhập sẽ bị xóa vĩnh viễn.'"
        @close="showDeleteModal = false"
        @confirm="deleteUser"
    />
  </component>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import UserFormModal from './FormModal.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue'; // We assume this exists or we create it

const props = defineProps({
  users: Object,
  roles: Array,
  filters: Object,
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
  router.get(route('users.index'), { search: value }, {
    preserveState: true,
    replace: true,
  });
});

// Modal Logic
const showModal = ref(false);
const selectedUser = ref(null);

const openCreateModal = () => {
    selectedUser.value = null;
    showModal.value = true;
};

const openEditModal = (user) => {
    selectedUser.value = user;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedUser.value = null;
};

// Delete Logic
const showDeleteModal = ref(false);
const userToDelete = ref(null);

const confirmDelete = (user) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const deleteUser = () => {
    router.delete(route('users.destroy', userToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            userToDelete.value = null;
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
