<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import { router, Link, Head, useForm } from '@inertiajs/vue3';
import AdminPortalLayout from '@/Layouts/AdminPortalLayout.vue';
import SystemFeaturesTab from '@/Pages/Admin/SystemFeaturesTab.vue';
import UserFormModal from './FormModal.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import axios from 'axios';

const props = defineProps({
  users:        Object,
  roles:        Array,
  departments:  Array,
  blockLabels:  Object,
  filters:      Object,
  features:     Array,
  systemConfig: Array,
  preselectUser: Object,
});

// ── Tab State ────────────────────────────────────────────────────────────────
const activeTab = ref('dashboard');

onMounted(() => {
  if (props.preselectUser) {
    activeTab.value = 'permissions';
    selectPermUser(props.preselectUser);
  }
});

// ── Users Tab: Filters ────────────────────────────────────────────────────────
const search = ref(props.filters?.search || '');
const selectedBlock = ref(props.filters?.block || '');
const selectedDept  = ref(props.filters?.department_id ? Number(props.filters.department_id) : null);

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

const debouncedSearch = ref(null);
watch(search, () => {
  clearTimeout(debouncedSearch.value);
  debouncedSearch.value = setTimeout(applyFilters, 350);
});
const onBlockChange = () => { selectedDept.value = null; applyFilters(); };
const onDeptChange = () => applyFilters();
const clearFilters = () => { search.value = ''; selectedBlock.value = ''; selectedDept.value = null; applyFilters(); };

// ── User CRUD Modal ───────────────────────────────────────────────────────────
const showModal = ref(false);
const selectedUser = ref(null);
const openCreateModal = () => { selectedUser.value = null; showModal.value = true; };
const openEditModal = (user) => { selectedUser.value = user; showModal.value = true; };
const closeModal = () => { showModal.value = false; selectedUser.value = null; };

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

// ── Dashboard Tab ─────────────────────────────────────────────────────────────
const featuresByPortal = computed(() => {
  const groups = {};
  for (const f of (props.features || [])) {
    const t = f.portal_type || 'activities';
    if (!groups[t]) groups[t] = [];
    groups[t].push(f);
  }
  return groups;
});
const portalMeta = (type) => ({
  activities: { name: 'Sinh Hoạt', icon: '🎯', color: 'emerald' },
  ministry:   { name: 'Mục Vụ',   icon: '⛪', color: 'blue' },
  deacon:     { name: 'Chấp Sự',  icon: '🛡', color: 'amber' },
})[type] || { name: type, icon: '📦', color: 'indigo' };

const cardBorder = (color) => ({
  emerald: 'border-emerald-200 hover:border-emerald-400',
  blue:    'border-blue-200 hover:border-blue-400',
  amber:   'border-amber-200 hover:border-amber-400',
  indigo:  'border-indigo-200 hover:border-indigo-400',
})[color] || 'border-gray-200';

const textAccent = (color) => ({
  emerald: 'text-emerald-600',
  blue:    'text-blue-600',
  amber:   'text-amber-600',
  indigo:  'text-indigo-600',
})[color] || 'text-gray-600';

// ── Permissions Tab: MAC Matrix ───────────────────────────────────────────────
const permUser        = ref(null);
const isLoadingPerm   = ref(false);
const isSuperAdmin    = ref(false);
const globalRoles     = ref([]);
const macMatrix       = ref({});
const isGrantingFull  = ref(false);
const permToastMsg    = ref('');
const permToastError  = ref(false);
const showPermList    = ref(true);
const activeDeptId    = ref(null);
const permSearch      = ref('');
const permSelectedBlock = ref('activities');
let permSearchTimeout;

watch(activeDeptId, (newId) => {
  const dept = props.departments?.find(d => d.id === newId);
  if (dept) permSelectedBlock.value = dept.block || 'activities';
});

const permDeptFeatures = computed(() =>
  (props.features || []).filter(f => f.portal_type === permSelectedBlock.value)
);

const blockOptions = [
  { value: 'activities', label: '🎯 Sinh Hoạt' },
  { value: 'ministry',   label: '⛪ Mục Vụ' },
  { value: 'leadership', label: '🛡 Chấp Sự' },
];

const grantedDeptIds = computed(() => {
  const ids = new Set();
  for (const key of Object.keys(macMatrix.value)) {
    const [deptId] = key.split('-');
    if (macMatrix.value[key].is_enabled) ids.add(Number(deptId));
  }
  return ids;
});

const activeDept = computed(() => props.departments?.find(d => d.id === activeDeptId.value) ?? null);

const deptGroups = computed(() => {
  const groups = {};
  for (const d of (props.departments || [])) {
    const block = d.block || 'activities';
    if (!groups[block]) groups[block] = [];
    groups[block].push(d);
  }
  return groups;
});

const blockLabel = (b) => ({ activities: 'Ban Sinh Hoạt', ministry: 'Ban Mục Vụ', leadership: 'Ban Chấp Sự' })[b] ?? b;

const handlePermSearch = () => {
  clearTimeout(permSearchTimeout);
  permSearchTimeout = setTimeout(() => {
    router.get(route('users.index'), { _perm: 1, search: permSearch.value }, { preserveState: true, replace: true });
  }, 400);
};

const macKey = (dId, fId) => `${dId}-${fId}`;
const isEnabled = (dId, fId) => macMatrix.value[macKey(dId, fId)]?.is_enabled ?? false;
const accessLevel = (dId, fId) => macMatrix.value[macKey(dId, fId)]?.access_level ?? 'view';

let permToastTimer;
const showPermToast = (msg, isError = false) => {
  permToastMsg.value = msg;
  permToastError.value = isError;
  clearTimeout(permToastTimer);
  permToastTimer = setTimeout(() => permToastMsg.value = '', 2500);
};

const selectPermUser = async (user) => {
  permUser.value     = user;
  isLoadingPerm.value = true;
  macMatrix.value    = {};
  globalRoles.value  = [];
  isSuperAdmin.value = false;
  activeDeptId.value = null;
  showPermList.value = false;

  try {
    const res = await axios.get(route('admin.users.permissions.show', user.id));
    globalRoles.value  = res.data.global_roles   || [];
    isSuperAdmin.value = res.data.is_super_admin || false;
    const map = {};
    for (const row of (res.data.permissions || [])) {
      map[`${row.department_id}-${row.feature_id}`] = {
        is_enabled:   row.is_enabled,
        access_level: row.access_level || 'view',
      };
    }
    macMatrix.value = map;
    if (grantedDeptIds.value.size > 0) {
      activeDeptId.value = [...grantedDeptIds.value][0];
    }
  } catch (e) {
    showPermToast('Lỗi khi tải phân quyền.', true);
  } finally {
    isLoadingPerm.value = false;
  }
};

const toggleFeature = async (deptId, featureId, newVal) => {
  if (!permUser.value) return;
  const key  = macKey(deptId, featureId);
  const prev = macMatrix.value[key];
  macMatrix.value = { ...macMatrix.value, [key]: { is_enabled: newVal, access_level: prev?.access_level ?? 'view' } };
  try {
    await axios.post(route('admin.users.permissions.toggle', permUser.value.id), {
      department_id: deptId, feature_id: featureId, is_enabled: newVal, access_level: prev?.access_level ?? 'view',
    });
    showPermToast(newVal ? 'Đã bật tính năng ✓' : 'Đã tắt tính năng');
  } catch {
    macMatrix.value = { ...macMatrix.value, [key]: prev ?? { is_enabled: !newVal, access_level: 'view' } };
    showPermToast('Lỗi khi lưu quyền!', true);
  }
};

const setAccessLevel = async (deptId, featureId, level) => {
  if (!permUser.value) return;
  const key = macKey(deptId, featureId);
  const prev = macMatrix.value[key];
  macMatrix.value = { ...macMatrix.value, [key]: { ...prev, access_level: level } };
  try {
    await axios.post(route('admin.users.permissions.toggle', permUser.value.id), {
      department_id: deptId, feature_id: featureId, is_enabled: prev?.is_enabled ?? false, access_level: level,
    });
  } catch {
    macMatrix.value = { ...macMatrix.value, [key]: prev };
    showPermToast('Lỗi!', true);
  }
};

const roleOptions = [
  { id: 'Super_Admin', label: 'Super Admin',  desc: 'Toàn quyền hệ thống' },
  { id: 'Pastor',      label: 'Mục Sư',       desc: 'Duyệt báo cáo, quản trị toàn cục' },
];
const hasRole = (id) => globalRoles.value.includes(id);
const toggleRole = async (id) => {
  const newRoles = hasRole(id) ? globalRoles.value.filter(r => r !== id) : [...globalRoles.value, id];
  globalRoles.value = newRoles;
  try {
    await axios.post(route('admin.users.permissions.roles', permUser.value.id), { roles: newRoles });
    showPermToast('Đã cập nhật vai trò toàn cục ✓');
  } catch {
    showPermToast('Lỗi khi cập nhật vai trò!', true);
  }
};

const grantFull = async () => {
  if (!permUser.value || isGrantingFull.value) return;
  if (!confirm(`Cấp TOÀN QUYỀN tất cả tính năng→mọi ban ngành cho ${permUser.value.name}?`)) return;
  isGrantingFull.value = true;
  try {
    const res = await axios.post(route('admin.users.permissions.grant-full', permUser.value.id));
    await selectPermUser(permUser.value);
    showPermToast(res.data.message || 'Đã cấp toàn quyền!');
  } catch {
    showPermToast('Lỗi!', true);
  } finally {
    isGrantingFull.value = false;
  }
};

const featureIcon = (slug) => ({
  'attendance':           '📋', 'visitation':           '🏠',
  'members':              '👥', 'assignments':           '🔧',
  'reports':              '📊', 'finance':               '💰',
  'education-classes':    '🏫', 'education-report':      '📈',
})[slug] ?? '⚙️';

const addDept = (dept) => { activeDeptId.value = dept.id; };
</script>

<template>
  <Head title="Quản Lý Hệ Thống" />
  <AdminPortalLayout title="Quản Lý Hệ Thống" :hide-tabs="true">

    <!-- ══ Slot: Tab bar ══ -->
    <template #tabs>
      <button v-for="tab in [
          { id: 'dashboard',   label: '🏠 Tổng Quan' },
          { id: 'users',       label: '👤 Người Dùng' },
          { id: 'config',      label: '🧩 Tính Năng' },
          { id: 'permissions', label: '🔐 Phân Quyền' },
        ]" :key="tab.id"
        @click="activeTab = tab.id"
        :class="['px-4 py-3 text-xs sm:text-sm font-bold whitespace-nowrap transition-colors border-b-2 cursor-pointer outline-none',
                 activeTab === tab.id ? 'border-white text-white' : 'border-transparent text-indigo-200 hover:text-white hover:border-indigo-300']">
        {{ tab.label }}
      </button>
    </template>

    <!-- ══ TAB 1: TỔNG QUAN ════════════════════════════════════════════════ -->
    <div v-if="activeTab === 'dashboard'" class="animate-fade space-y-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-black text-gray-900">Kho Tính Năng Hệ Thống</h1>
          <p class="text-sm text-gray-500 mt-1">Quản lý và giám sát các modules trong hệ thống CMS</p>
        </div>
        <button @click="activeTab = 'config'"
          class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-sm transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 7a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
          Cấu Hình
        </button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Tổng Tính Năng</p>
          <p class="text-3xl font-black text-indigo-600">{{ features?.length || 0 }}</p>
          <p class="text-xs text-gray-400 mt-1">modules đăng ký</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Tổng Portal</p>
          <p class="text-3xl font-black text-emerald-600">3</p>
          <p class="text-xs text-gray-400 mt-1">Sinh Hoạt · Mục Vụ · Chấp Sự</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Tài Khoản</p>
          <p class="text-3xl font-black text-amber-600">{{ users?.total || users?.data?.length || 0 }}</p>
          <p class="text-xs text-gray-400 mt-1">người dùng trong hệ thống</p>
        </div>
      </div>

      <!-- Feature cards by portal -->
      <div v-for="(feats, portalType) in featuresByPortal" :key="portalType">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xl"
            :class="{
              'bg-emerald-50': portalType === 'activities',
              'bg-blue-50': portalType === 'ministry',
              'bg-amber-50': portalType === 'leadership' || portalType === 'deacon',
              'bg-indigo-50': !['activities','ministry','leadership','deacon'].includes(portalType),
            }">
            {{ portalMeta(portalType).icon }}
          </div>
          <div>
            <h2 class="text-base font-black text-gray-800">Ban {{ portalMeta(portalType).name }}</h2>
            <p class="text-xs text-gray-400">{{ feats.length }} tính năng được định nghĩa</p>
          </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
          <div v-for="feat in feats" :key="feat.id"
            class="bg-white rounded-2xl p-4 border-2 shadow-sm hover:shadow-md transition-all cursor-default"
            :class="cardBorder(portalMeta(portalType).color)">
            <div class="text-2xl mb-3">{{ featureIcon(feat.slug) }}</div>
            <h3 class="font-black text-gray-900 text-sm leading-snug">{{ feat.name }}</h3>
            <p :class="['text-[10px] mt-1 font-mono', textAccent(portalMeta(portalType).color)]">{{ feat.slug }}</p>
            <p class="text-[11px] text-gray-400 mt-2 line-clamp-2">{{ feat.description || 'Chưa có mô tả' }}</p>
          </div>
        </div>
      </div>

      <div v-if="!features?.length" class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-16 text-center">
        <div class="text-5xl mb-4">🧩</div>
        <h2 class="font-black text-gray-700 text-xl mb-2">Chưa có tính năng nào</h2>
        <button @click="activeTab = 'config'" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold shadow-sm hover:bg-indigo-700 mt-2">+ Thêm Tính Năng →</button>
      </div>
    </div>

    <!-- ══ TAB 2: NGƯỜI DÙNG ═══════════════════════════════════════════════ -->
    <div v-else-if="activeTab === 'users'" class="animate-fade space-y-4">
      <!-- Filter bar -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center">
          <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input v-model="search" type="text" placeholder="Tìm theo tên, email..."
              class="w-full pl-9 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none"/>
          </div>
          <select v-model="selectedBlock" @change="onBlockChange"
            class="py-2.5 px-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none bg-white min-w-[160px]">
            <option value="">📋 Tất cả loại ban</option>
            <option v-for="(label, key) in blockLabels" :key="key" :value="key">{{ label }}</option>
          </select>
          <select v-if="selectedBlock" v-model="selectedDept" @change="onDeptChange"
            class="py-2.5 px-3 border border-indigo-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none bg-indigo-50 text-indigo-700 font-medium min-w-[200px]">
            <option :value="null">— Tất cả ban trong loại</option>
            <option v-for="d in filteredDepts" :key="d.id" :value="d.id">{{ d.name }}</option>
          </select>
          <button v-if="selectedBlock || search" @click="clearFilters"
            class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-500 hover:bg-gray-50 whitespace-nowrap shrink-0">✕ Xóa lọc</button>
          <button @click="openCreateModal"
            class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-sm transition-all shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tạo Tài Khoản
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/80">
              <tr>
                <th class="px-6 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Tài Khoản</th>
                <th class="px-6 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Liên hệ</th>
                <th class="px-6 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Vai trò</th>
                <th class="px-6 py-3.5 text-left text-xs font-black text-gray-400 uppercase tracking-wider">Ban Ngành</th>
                <th class="px-6 py-3.5 text-right text-xs font-black text-gray-400 uppercase tracking-wider">Thao tác</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-50">
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-indigo-50/20 transition-colors group">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-sm">{{ (user.name || 'U').charAt(0).toUpperCase() }}</div>
                    <div>
                      <p class="text-sm font-bold text-gray-900">{{ user.name }}</p>
                      <p class="text-xs text-gray-400">{{ user.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ user.phone || '—' }}</td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold" :class="roleColor(user.role)">{{ user.role || 'Chưa phân' }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  <span v-if="user.departments !== 'Chưa tham gia'" class="text-gray-700">{{ user.departments }}</span>
                  <span v-else class="italic text-gray-300">Chưa tham gia</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-1.5 opacity-80 group-hover:opacity-100 transition-opacity">
                    <button @click="selectPermUser(user); activeTab = 'permissions'" class="text-xs font-bold text-teal-600 bg-teal-50 hover:bg-teal-100 px-3 py-1.5 rounded-lg transition-colors">🔐 Quyền</button>
                    <button @click="openEditModal(user)" class="text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">Sửa</button>
                    <button @click="confirmDelete(user)" class="text-xs font-bold text-red-500 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">Xóa</button>
                  </div>
                </td>
              </tr>
              <tr v-if="!users.data.length"><td colspan="5" class="px-6 py-16 text-center text-gray-400 italic text-sm">Không tìm thấy tài khoản nào.</td></tr>
            </tbody>
          </table>
        </div>
        <!-- Mobile cards -->
        <div class="md:hidden divide-y divide-gray-100">
          <div v-for="user in users.data" :key="user.id" class="p-4">
            <div class="flex items-start justify-between mb-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-black text-sm">{{ (user.name || 'U').charAt(0).toUpperCase() }}</div>
                <div>
                  <p class="text-sm font-bold text-gray-900">{{ user.name }}</p>
                  <p class="text-xs text-gray-400">{{ user.email }}</p>
                </div>
              </div>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold" :class="roleColor(user.role)">{{ user.role || '—' }}</span>
            </div>
            <p class="text-xs text-gray-400 mb-3">{{ user.departments !== 'Chưa tham gia' ? user.departments : '—' }}</p>
            <div class="flex gap-2 pt-3 border-t border-gray-50">
              <button @click="selectPermUser(user); activeTab = 'permissions'" class="flex-1 text-center text-teal-700 bg-teal-50 py-2 rounded-xl font-bold text-xs">🔐 Quyền</button>
              <button @click="openEditModal(user)" class="flex-1 text-center text-indigo-700 bg-indigo-50 py-2 rounded-xl font-bold text-xs">Sửa</button>
              <button @click="confirmDelete(user)" class="flex-1 text-center text-red-600 bg-red-50 py-2 rounded-xl font-bold text-xs">Xóa</button>
            </div>
          </div>
          <div v-if="!users.data.length" class="p-10 text-center text-gray-400 italic text-sm">Không tìm thấy người dùng.</div>
        </div>
      </div>
      <!-- Pagination -->
      <div v-if="users.links?.length > 3" class="flex justify-center">
        <nav class="flex gap-1">
          <template v-for="(link, k) in users.links" :key="k">
            <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-2 rounded-lg text-sm border transition-colors"
              :class="link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'" />
            <span v-else v-html="link.label" class="px-3 py-2 rounded-lg text-sm border border-gray-200 bg-white text-gray-300" />
          </template>
        </nav>
      </div>
    </div>

    <!-- ══ TAB 3: TÍNH NĂNG ════════════════════════════════════════════════ -->
    <div v-else-if="activeTab === 'config'" class="animate-fade">
      <SystemFeaturesTab :features="features" :departments="departments" :systemConfig="systemConfig" />
    </div>

    <!-- ══ TAB 4: PHÂN QUYỀN ══════════════════════════════════════════════ -->
    <div v-else-if="activeTab === 'permissions'" class="animate-fade max-w-3xl mx-auto space-y-4">

      <!-- Search & Select -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-3">
          <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input v-model="permSearch" @input="handlePermSearch" type="text"
              class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
              placeholder="Tìm tên hoặc email tài khoản cần phân quyền...">
          </div>
          <button v-if="permUser" @click="permUser = null; macMatrix = {}; activeDeptId = null"
            class="text-xs px-3 py-2 border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50 whitespace-nowrap shrink-0">✕ Đổi</button>
        </div>
        <div v-if="permUser" class="px-4 py-3 bg-indigo-50 border-b border-indigo-100 flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-black shrink-0">{{ permUser.name.charAt(0).toUpperCase() }}</div>
          <div class="flex-1 min-w-0">
            <p class="font-black text-gray-900 text-sm truncate">{{ permUser.name }}</p>
            <p class="text-xs text-gray-400 truncate">{{ permUser.email }}</p>
          </div>
          <span v-if="isSuperAdmin" class="text-[10px] px-2 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg font-black">⚡ GOD MODE</span>
          <span class="text-[10px] px-2 py-1 bg-green-50 text-green-700 border border-green-200 rounded-lg font-bold">✓ Đang chỉnh</span>
        </div>
        <div v-if="!permUser" class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
          <button v-for="u in users.data" :key="u.id" @click="selectPermUser(u)"
            class="w-full text-left px-4 py-3 hover:bg-indigo-50/50 transition-colors flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-sm font-black shrink-0">{{ u.name.charAt(0).toUpperCase() }}</div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-bold text-gray-900 truncate">{{ u.name }}</p>
              <p class="text-xs text-gray-400 truncate">{{ u.email }}</p>
            </div>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
          <p v-if="users.data.length === 0" class="py-8 text-center text-gray-400 text-sm italic">Không tìm thấy tài khoản.</p>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="!permUser && !isLoadingPerm" class="bg-white rounded-2xl border border-dashed border-gray-200 shadow-sm p-12 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-indigo-50 flex items-center justify-center text-3xl">🔐</div>
        <p class="font-black text-gray-700 text-base">Chọn người dùng để phân quyền</p>
        <p class="text-gray-400 text-sm mt-1">Tìm kiếm và bấm chọn tài khoản cần cấu hình quyền</p>
      </div>

      <!-- Loading -->
      <div v-else-if="isLoadingPerm" class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 flex flex-col items-center text-gray-400">
        <svg class="animate-spin w-10 h-10 text-indigo-300 mb-3" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <p class="text-sm">Đang tải quyền...</p>
      </div>

      <!-- Permission panels -->
      <div v-else-if="permUser" class="space-y-4">

        <!-- Global Roles -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">Quyền Hệ Thống Toàn Cục</h3>
          </div>
          <div class="p-5 grid grid-cols-2 gap-3">
            <label v-for="role in roleOptions" :key="role.id"
              class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all"
              :class="hasRole(role.id) ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'">
              <input type="checkbox" :checked="hasRole(role.id)" @change="toggleRole(role.id)" class="mt-0.5 w-4 h-4 text-indigo-600 rounded border-gray-300">
              <div>
                <p class="text-sm font-black text-gray-900">{{ role.label }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ role.desc }}</p>
              </div>
            </label>
          </div>
          <div class="px-5 pb-5">
            <button @click="grantFull" :disabled="isGrantingFull"
              class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-sm font-black rounded-xl hover:from-amber-600 hover:to-orange-600 transition-all disabled:opacity-50 flex items-center justify-center gap-2 shadow-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              {{ isGrantingFull ? 'Đang cấp...' : '⚡ Cấp Toàn Quyền Tất Cả Ban Ngành' }}
            </button>
          </div>
        </div>

        <!-- Department permissions -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">Quyền Ban Ngành</h3>
            </div>
            <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">{{ grantedDeptIds.size }} ban</span>
          </div>
          <div class="px-5 py-4 flex flex-wrap gap-2" :class="grantedDeptIds.size > 0 ? 'border-b border-gray-100' : ''">
            <template v-if="grantedDeptIds.size > 0">
              <button v-for="dept in departments.filter(d => grantedDeptIds.has(d.id))" :key="dept.id"
                @click="activeDeptId = activeDeptId === dept.id ? null : dept.id"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-all border"
                :class="activeDeptId === dept.id ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100'">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ dept.name }}
              </button>
            </template>
            <p v-else class="text-xs text-gray-400 italic py-1">Chưa cấp quyền ban ngành nào. Dùng nút ➕ bên dưới.</p>
          </div>
          <div v-if="activeDept" class="px-5 pb-5 pt-4">
            <div class="rounded-xl border border-gray-200 overflow-hidden">
              <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center gap-2">
                  <span class="font-black text-gray-900 text-sm">{{ activeDept.name }}</span>
                  <span class="text-[10px] px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full font-bold">
                    {{ permDeptFeatures.filter(f => isEnabled(activeDept.id, f.id)).length }}/{{ permDeptFeatures.length }}
                  </span>
                </div>
                <div class="flex items-center gap-2">
                  <select v-model="permSelectedBlock" class="text-xs border border-indigo-200 bg-indigo-50 text-indigo-700 rounded-lg font-bold py-1.5 px-2.5">
                    <option v-for="b in blockOptions" :key="b.value" :value="b.value">{{ b.label }}</option>
                  </select>
                  <button @click="activeDeptId = null" class="text-xs text-gray-400 hover:text-gray-600 px-2 py-1">✕</button>
                </div>
              </div>
              <div class="divide-y divide-gray-50 bg-white">
                <label v-for="feature in permDeptFeatures" :key="feature.id"
                  class="flex items-center justify-between gap-3 px-4 py-3.5 hover:bg-gray-50 cursor-pointer transition-colors">
                  <div class="flex items-center gap-3">
                    <span class="text-xl w-7 text-center">{{ featureIcon(feature.slug) }}</span>
                    <div>
                      <span class="text-sm font-bold text-gray-800">{{ feature.name }}</span>
                      <p class="text-xs text-gray-400 font-mono">{{ feature.slug }}</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" :checked="isEnabled(activeDept.id, feature.id)"
                      @change="toggleFeature(activeDept.id, feature.id, $event.target.checked)" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-400 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                  </label>
                </label>
                <p v-if="permDeptFeatures.length === 0" class="px-4 py-6 text-xs text-gray-400 italic text-center">Không có tính năng nào cho loại ban này.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Add department -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="text-sm font-black text-gray-700">Thêm / Quản Lý Ban Ngành</h3>
          </div>
          <div class="p-5 space-y-5">
            <div v-for="(depts, block) in deptGroups" :key="block">
              <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2.5 flex items-center gap-1.5">
                <span>{{ block === 'activities' ? '🎯' : block === 'ministry' ? '⛪' : '🛡' }}</span>
                {{ blockLabel(block) }}
              </p>
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                <button v-for="dept in depts" :key="dept.id" @click="addDept(dept)"
                  class="flex items-center gap-1.5 text-xs px-3 py-2.5 rounded-xl border transition-all font-medium text-left"
                  :class="grantedDeptIds.has(dept.id)
                    ? 'bg-green-50 border-green-200 text-green-700 hover:bg-green-100'
                    : 'bg-white border-gray-200 text-gray-600 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50'">
                  <span v-if="grantedDeptIds.has(dept.id)" class="text-green-500">✓</span>
                  <span v-else class="text-gray-300">+</span>
                  <span class="truncate">{{ dept.name }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Toast -->
      <Transition enter-from-class="opacity-0 translate-y-2" leave-to-class="opacity-0 translate-y-2"
        enter-active-class="transition duration-200" leave-active-class="transition duration-200">
        <div v-if="permToastMsg" class="fixed bottom-6 right-6 z-50 flex items-center gap-2 px-4 py-3 rounded-xl shadow-xl text-sm font-bold"
          :class="permToastError ? 'bg-red-600 text-white' : 'bg-green-600 text-white'">
          {{ permToastMsg }}
        </div>
      </Transition>
    </div>

    <!-- Modals -->
    <UserFormModal v-if="showModal" :show="showModal" :roles="roles" :editingUser="selectedUser" @close="closeModal" />
    <DeleteConfirmModal v-if="showDeleteModal" :show="showDeleteModal" title="Xóa Tài Khoản"
      :message="'Xóa tài khoản ' + userToDelete?.name + '? Hành động này không thể hoàn tác.'"
      @close="showDeleteModal = false" @confirm="deleteUser" />

  </AdminPortalLayout>
</template>

<style scoped>
.animate-fade { animation: fadein 0.18s ease; }
@keyframes fadein { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
</style>
