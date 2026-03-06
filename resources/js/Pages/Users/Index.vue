<script setup>
import { ref, watch, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminPortalLayout from '@/Layouts/AdminPortalLayout.vue';
import UserFormModal from './FormModal.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';

const props = defineProps({
  users: Object,
  roles: Array,
  departments: Array,  // [{id, name, block}]
  blockLabels: Object, // {'activities': '🏃 Ban Sinh Hoạt', ...}
  filters: Object,
});

const search = ref(props.filters?.search || '');
const selectedBlock = ref(props.filters?.block || '');
const selectedDept  = ref(props.filters?.department_id ? Number(props.filters.department_id) : null);

// Departments filtered by selected block
const filteredDepts = computed(() => {
  if (!selectedBlock.value) return [];
  return props.departments.filter(d => d.block === selectedBlock.value);
});

const applyFilters = () => {
  router.get(route('users.index'), {
    search: search.value || undefined,
    block: selectedBlock.value || undefined,
    department_id: selectedDept.value || undefined,
  }, { preserveState: true, replace: true });
};

watch(search, (v) => {
  applyFilters();
}, { debounce: 300 });

const onBlockChange = () => {
  selectedDept.value = null;
  applyFilters();
};

const onDeptChange = () => applyFilters();

const clearFilters = () => {
  search.value = '';
  selectedBlock.value = '';
  selectedDept.value = null;
  applyFilters();
};

// Modal
const showModal = ref(false);
const selectedUser = ref(null);
const openCreateModal = () => { selectedUser.value = null; showModal.value = true; };
const openEditModal = (user) => { selectedUser.value = user; showModal.value = true; };
const closeModal = () => { showModal.value = false; selectedUser.value = null; };

// Delete
const showDeleteModal = ref(false);
const userToDelete = ref(null);
const confirmDelete = (user) => { userToDelete.value = user; showDeleteModal.value = true; };
const deleteUser = () => {
  router.delete(route('users.destroy', userToDelete.value.id), {
    preserveScroll: true,
    onSuccess: () => { showDeleteModal.value = false; userToDelete.value = null; }
  });
};

const roleColor = (role) => {
  if (role === 'Super_Admin') return 'bg-red-100 text-red-800';
  if (role === 'Pastor')      return 'bg-purple-100 text-purple-800';
  if (role === 'Guest')       return 'bg-gray-100 text-gray-700';
  return 'bg-indigo-100 text-indigo-800';
};

const blockBadge = (block) => {
  const map = {
    activities: 'bg-emerald-100 text-emerald-700',
    ministry: 'bg-purple-100 text-purple-700',
    leadership: 'bg-amber-100 text-amber-700',
  };
  return map[block] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
  <AdminPortalLayout title="Quản lý Tài khoản" active-tab="users">

    <!-- ── Filter Panel ── -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5">
      <div class="flex flex-col md:flex-row gap-3">

        <!-- Search -->
        <div class="relative flex-1">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="search" type="text" placeholder="Tìm theo tên, email..."
            class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none"/>
        </div>

        <!-- Block Filter (Loại Ban Ngành) -->
        <select v-model="selectedBlock" @change="onBlockChange"
          class="py-2.5 px-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white min-w-[160px]">
          <option value="">📋 Tất cả loại ban</option>
          <option v-for="(label, key) in blockLabels" :key="key" :value="key">{{ label }}</option>
        </select>

        <!-- Department Filter (dependent on block) -->
        <select v-if="selectedBlock" v-model="selectedDept" @change="onDeptChange"
          class="py-2.5 px-3 border border-indigo-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white min-w-[200px]">
          <option :value="null">— Tất cả ban trong loại này</option>
          <option v-for="d in filteredDepts" :key="d.id" :value="d.id">{{ d.name }}</option>
        </select>

        <!-- Clear + Create -->
        <button v-if="selectedBlock || search" @click="clearFilters"
          class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-500 hover:bg-gray-50 transition-colors">
          ✕ Xóa lọc
        </button>
        <button @click="openCreateModal"
          class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-sm transition-all shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
          Tạo Tài Khoản
        </button>
      </div>

      <!-- Active Filter Summary -->
      <div v-if="selectedBlock || selectedDept" class="mt-3 flex items-center gap-2 text-xs text-indigo-600">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        <span>Đang lọc: <strong>{{ blockLabels[selectedBlock] }}</strong>
          <span v-if="selectedDept"> → <strong>{{ filteredDepts.find(d => d.id === selectedDept)?.name }}</strong></span>
          — <strong class="underline cursor-pointer" @click="clearFilters">bỏ lọc</strong>
        </span>
      </div>
    </div>

    <!-- ── Data Table ── -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

      <!-- Desktop Table -->
      <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
          <thead class="bg-gray-50/80">
            <tr>
              <th class="px-6 py-3.5 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Tài Khoản</th>
              <th class="px-6 py-3.5 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Liên hệ</th>
              <th class="px-6 py-3.5 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Chức vụ</th>
              <th class="px-6 py-3.5 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Ban Ngành</th>
              <th class="px-6 py-3.5 text-right text-xs font-black text-gray-500 uppercase tracking-wider">Thao tác</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-50">
            <tr v-for="user in users.data" :key="user.id" class="hover:bg-indigo-50/30 transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-sm shrink-0">
                    {{ (user.name || 'U').charAt(0) }}
                  </div>
                  <div>
                    <p class="text-sm font-bold text-gray-900">{{ user.name }}</p>
                    <p class="text-xs text-gray-500">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600">{{ user.phone || '—' }}</td>
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold" :class="roleColor(user.role)">
                  {{ user.role || 'Chưa phân' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                <span v-if="user.departments !== 'Chưa tham gia'" class="text-gray-700">{{ user.departments }}</span>
                <span v-else class="italic text-gray-400">Chưa tham gia</span>
              </td>
              <td class="px-6 py-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-2">
                  <Link :href="route('admin.users.permissions') + '?user_id=' + user.id"
                    class="text-xs font-bold text-teal-600 bg-teal-50 hover:bg-teal-100 px-3 py-1.5 rounded-lg transition-colors">
                    🔐 Quyền
                  </Link>
                  <button @click="openEditModal(user)" class="text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">Sửa</button>
                  <button @click="confirmDelete(user)" class="text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">Xóa</button>
                </div>
              </td>
            </tr>
            <tr v-if="!users.data.length">
              <td colspan="5" class="px-6 py-16 text-center text-gray-400 italic">Không tìm thấy tài khoản nào.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card View -->
      <div class="md:hidden divide-y divide-gray-100">
        <div v-for="user in users.data" :key="user.id" class="p-4">
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-sm">
                {{ (user.name || 'U').charAt(0) }}
              </div>
              <div>
                <p class="text-sm font-bold text-gray-900">{{ user.name }}</p>
                <p class="text-xs text-gray-500">{{ user.email }}</p>
              </div>
            </div>
            <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold" :class="roleColor(user.role)">
              {{ user.role || '—' }}
            </span>
          </div>
          <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 mb-4">
            <div><span class="font-semibold text-gray-700">SĐT:</span> {{ user.phone || '—' }}</div>
            <div><span class="font-semibold text-gray-700">Ban:</span> {{ user.departments }}</div>
          </div>
          <div class="flex gap-2 pt-3 border-t border-gray-50">
            <Link :href="route('admin.users.permissions') + '?search=' + encodeURIComponent(user.email)"
              class="flex-1 text-center text-teal-700 bg-teal-50 py-2 rounded-xl font-bold text-xs">🔐 Phân Quyền</Link>
            <button @click="openEditModal(user)" class="flex-1 text-center text-indigo-700 bg-indigo-50 py-2 rounded-xl font-bold text-xs">Sửa</button>
            <button @click="confirmDelete(user)" class="flex-1 text-center text-red-600 bg-red-50 py-2 rounded-xl font-bold text-xs">Xóa</button>
          </div>
        </div>
        <div v-if="!users.data.length" class="p-10 text-center text-gray-400 italic text-sm">Không tìm thấy người dùng.</div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="users.links?.length > 3" class="flex justify-center mt-6">
      <nav class="flex gap-1">
        <template v-for="(link, k) in users.links" :key="k">
          <Link v-if="link.url" :href="link.url" v-html="link.label"
            class="px-3 py-2 rounded-lg text-sm border transition-colors"
            :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'" />
          <span v-else v-html="link.label" class="px-3 py-2 rounded-lg text-sm border border-gray-200 bg-white text-gray-400" />
        </template>
      </nav>
    </div>

    <!-- Modals -->
    <UserFormModal v-if="showModal" :show="showModal" :roles="roles" :editingUser="selectedUser" @close="closeModal" />
    <DeleteConfirmModal v-if="showDeleteModal" :show="showDeleteModal" title="Xóa Tài Khoản"
      :message="'Xóa tài khoản ' + userToDelete?.name + '? Hành động này không thể hoàn tác.'"
      @close="showDeleteModal = false" @confirm="deleteUser" />

  </AdminPortalLayout>
</template>
