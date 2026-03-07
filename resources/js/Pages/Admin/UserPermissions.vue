<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AdminPortalLayout from '@/Layouts/AdminPortalLayout.vue';
import SystemFeaturesTab from '@/Pages/Admin/SystemFeaturesTab.vue';
import axios from 'axios';

const props = defineProps({
  users:         Object,  // paginated
  departments:   Array,   // all departments
  features:      Array,   // 10 MAC features
  filters:       Object,
  preselectUser: Object,
  systemConfig:  Array,   // System-level Feature Config (Level 1)
});

const activeTab = ref('users'); // 'users' or 'system'

// ── Search ────────────────────────────────────────────────────────────────────
const searchInput = ref(props.filters?.search || '');
let searchTimeout;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(route('admin.users.permissions'), { search: searchInput.value }, {
      preserveState: true, replace: true,
    });
  }, 400);
};

// ── Active User ───────────────────────────────────────────────────────────────
const activeUser    = ref(null);
const showUserList  = ref(!props.preselectUser);
const isLoading     = ref(false);
const isSaving      = ref(false);
const isSuperAdmin  = ref(false);
const globalRoles   = ref([]);
// Map: `${dept_id}-${feature_id}` → { is_enabled, access_level }
const macMatrix     = ref({});
const toastMsg      = ref('');
const toastError    = ref(false);

// Departments user currently has any permission in (for chips display)
const grantedDeptIds = computed(() => {
  const ids = new Set();
  for (const key of Object.keys(macMatrix.value)) {
    const [deptId] = key.split('-');
    if (macMatrix.value[key].is_enabled) ids.add(Number(deptId));
  }
  return ids;
});

// Active dept chip (expanded)
const activeDeptId = ref(null);

const activeDept = computed(() =>
  props.departments?.find(d => d.id === activeDeptId.value) ?? null
);

// Dept groups for "Thêm ban ngành" section
const deptGroups = computed(() => {
  const groups = {};
  for (const d of (props.departments || [])) {
    const block = d.block || 'activities';
    if (!groups[block]) groups[block] = [];
    groups[block].push(d);
  }
  return groups;
});

const blockLabel = (b) => ({
  activities: 'Ban Sinh Hoạt',
  ministry:   'Ban Mục Vụ',
  leadership: 'Ban Chấp Sự',
})[b] ?? b;

// ── Load user ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  if (props.preselectUser) await selectUser(props.preselectUser);
});

const selectUser = async (user) => {
  activeUser.value  = user;
  isLoading.value   = true;
  macMatrix.value   = {};
  globalRoles.value = [];
  isSuperAdmin.value = false;
  activeDeptId.value = null;
  showUserList.value = false;

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

    // Auto-expand first enabled dept
    if (grantedDeptIds.value.size > 0) {
      activeDeptId.value = [...grantedDeptIds.value][0];
    }
  } catch (e) {
    showToast('Lỗi khi tải phân quyền.', true);
  } finally {
    isLoading.value = false;
  }
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const macKey = (dId, fId) => `${dId}-${fId}`;
const isEnabled   = (dId, fId) => macMatrix.value[macKey(dId, fId)]?.is_enabled   ?? false;
const accessLevel = (dId, fId) => macMatrix.value[macKey(dId, fId)]?.access_level ?? 'view';

// ── Toggle feature ────────────────────────────────────────────────────────────
const toggleFeature = async (deptId, featureId, newVal) => {
  if (!activeUser.value) return;
  const key  = macKey(deptId, featureId);
  const prev = macMatrix.value[key];

  // Optimistic
  macMatrix.value = { ...macMatrix.value, [key]: { is_enabled: newVal, access_level: prev?.access_level ?? 'view' } };

  try {
    await axios.post(route('admin.users.permissions.toggle', activeUser.value.id), {
      department_id: deptId,
      feature_id:    featureId,
      is_enabled:    newVal,
      access_level:  prev?.access_level ?? 'view',
    });
    showToast(newVal ? 'Đã bật tính năng ✓' : 'Đã tắt tính năng');
  } catch {
    // Revert
    macMatrix.value = { ...macMatrix.value, [key]: prev ?? { is_enabled: !newVal, access_level: 'view' } };
    showToast('Lỗi khi lưu quyền!', true);
  }
};

// ── Toggle access level ───────────────────────────────────────────────────────
const setAccessLevel = async (deptId, featureId, level) => {
  if (!activeUser.value) return;
  const key = macKey(deptId, featureId);
  const prev = macMatrix.value[key];
  macMatrix.value = { ...macMatrix.value, [key]: { ...prev, access_level: level } };

  try {
    await axios.post(route('admin.users.permissions.toggle', activeUser.value.id), {
      department_id: deptId, feature_id: featureId,
      is_enabled:    prev?.is_enabled ?? false, access_level: level,
    });
  } catch {
    macMatrix.value = { ...macMatrix.value, [key]: prev };
    showToast('Lỗi!', true);
  }
};

// ── Grant/Revoke All for a dept ───────────────────────────────────────────────
const isGrantingAll = ref(false);
const grantAllForDept = async (dept) => {
  isGrantingAll.value = true;
  const allEnabled = props.features.every(f => isEnabled(dept.id, f.id));
  // Toggle all to opposite
  const newVal = !allEnabled;
  for (const feature of props.features) {
    await toggleFeature(dept.id, feature.id, newVal);
  }
  showToast(newVal ? `Đã cấp toàn bộ quyền cho ${dept.name}` : `Đã thu hồi toàn bộ quyền`);
  isGrantingAll.value = false;
};

// ── Add dept to user (enable first feature to grant initial access) ────────────
const addDept = (dept) => {
  activeDeptId.value = dept.id;
  if (!grantedDeptIds.value.has(dept.id)) {
    // Scroll to dept
  }
};

// ── Global Roles ──────────────────────────────────────────────────────────────
const roleOptions = [
  { id: 'Super_Admin', label: 'Super Admin',  desc: 'Toàn quyền hệ thống' },
  { id: 'Pastor',      label: 'Mục Sư',       desc: 'Duyệt báo cáo, quản trị toàn cục' },
];

const hasRole = (id) => globalRoles.value.includes(id);
const toggleRole = async (id) => {
  const newRoles = hasRole(id)
    ? globalRoles.value.filter(r => r !== id)
    : [...globalRoles.value, id];
  globalRoles.value = newRoles;
  try {
    await axios.post(route('admin.users.permissions.roles', activeUser.value.id), { roles: newRoles });
    showToast('Đã cập nhật vai trò toàn cục ✓');
  } catch {
    showToast('Lỗi khi cập nhật vai trò!', true);
  }
};

// ── Grant Full ────────────────────────────────────────────────────────────────
const isGrantingFull = ref(false);
const grantFull = async () => {
  if (!activeUser.value || isGrantingFull.value) return;
  if (!confirm(`Cấp TOÀN QUYỀN tất cả tính năng→mọi ban ngành cho ${activeUser.value.name}?`)) return;
  isGrantingFull.value = true;
  try {
    const res = await axios.post(route('admin.users.permissions.grant-full', activeUser.value.id));
    await selectUser(activeUser.value);
    showToast(res.data.message || 'Đã cấp toàn quyền!');
  } catch {
    showToast('Lỗi!', true);
  } finally {
    isGrantingFull.value = false;
  }
};

// ── Toast ─────────────────────────────────────────────────────────────────────
let toastTimer;
const showToast = (msg, isError = false) => {
  toastMsg.value   = msg;
  toastError.value = isError;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toastMsg.value = '', 2500);
};

// Icon map for features
const featureIcon = (slug) => ({
  'attendance':          '📋',
  'visitation':          '🏠',
  'members':             '👥',
  'assignments':         '🔧',
  'reports':             '📊',
  'finance':             '💰',
  'education-classes':   '🏫',
  'education-attendance':'📝',
  'education-offering':  '💵',
  'education-report':    '📈',
})[slug] ?? '⚙️';
</script>

<template>
  <Head title="Quản Lý Tính Năng & Phân Quyền" />
  <AdminPortalLayout>
    <div class="max-w-4xl mx-auto px-4 py-8">
      
      <!-- Tabs Navigation -->
      <div class="flex space-x-6 border-b border-gray-200 mb-6">
        <button @click="activeTab = 'users'" 
          :class="['py-3 px-1 border-b-2 font-black text-sm transition-all outline-none', activeTab === 'users' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300']">
          Phân Quyền Cá Nhân (User Matrix)
        </button>
        <button @click="activeTab = 'system'" 
          :class="['py-3 px-1 border-b-2 font-black text-sm transition-all outline-none flex items-center gap-2', activeTab === 'system' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300']">
          Cấu Hình Tính Năng (Hệ Thống)
          <span class="bg-indigo-100 text-indigo-700 py-0.5 px-2 rounded-full text-[10px] hidden sm:inline-block">MỚI</span>
        </button>
      </div>

      <!-- Tab 1: User Matrix -->
      <div v-show="activeTab === 'users'">
        <!-- ══ User Picker / Header ══════════════════════════════════════════ -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-5 overflow-hidden">
        <!-- Search bar -->
        <div class="px-4 py-3 flex items-center gap-3 border-b border-gray-100">
          <div class="relative flex-1">
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input v-model="searchInput" @input="handleSearch" type="text"
              class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
              placeholder="Tìm tên hoặc email tài khoản...">
          </div>
          <button v-if="activeUser && !showUserList" @click="showUserList = !showUserList"
            class="text-xs px-3 py-2 border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50 whitespace-nowrap">
            ← Danh sách user
          </button>
        </div>

        <!-- User list dropdown -->
        <div v-if="showUserList" class="divide-y divide-gray-50 max-h-56 overflow-y-auto">
          <button v-for="u in users.data" :key="u.id" @click="selectUser(u)"
            class="w-full text-left px-4 py-3 hover:bg-indigo-50 active:bg-indigo-100 transition-colors flex items-center gap-3 group"
            :class="activeUser?.id === u.id ? 'bg-indigo-50' : ''">
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-black shrink-0">
              {{ u.name.charAt(0).toUpperCase() }}
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-bold text-gray-900 truncate">{{ u.name }}</p>
              <p class="text-xs text-gray-400 truncate">{{ u.email }}</p>
            </div>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
          <p v-if="users.data.length === 0" class="py-8 text-center text-gray-400 text-sm">Không tìm thấy.</p>
        </div>

        <!-- Selected user header -->
        <div v-if="activeUser && !showUserList" class="px-4 py-3 flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black shrink-0">
            {{ activeUser.name.charAt(0).toUpperCase() }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-black text-gray-900 text-sm">{{ activeUser.name }}</p>
            <p class="text-xs text-gray-400 truncate">{{ activeUser.email }}</p>
          </div>
          <!-- God Mode Badge -->
          <span v-if="isSuperAdmin" class="text-[10px] px-2 py-1 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg font-black">
            ⚡ GOD MODE
          </span>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="py-20 flex flex-col items-center text-gray-400">
        <svg class="animate-spin w-10 h-10 text-indigo-300 mb-3" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <p class="text-sm">Đang tải...</p>
      </div>

      <!-- Empty state -->
      <div v-else-if="!activeUser && !showUserList"
        class="py-20 flex flex-col items-center text-gray-400 bg-white rounded-2xl border border-gray-100">
        <svg class="w-12 h-12 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <p class="font-bold text-sm">Chọn người dùng để phân quyền</p>
      </div>

      <!-- ══ Permission Panel ═══════════════════════════════════════════════ -->
      <div v-else-if="activeUser && !isLoading && !showUserList" class="space-y-4">

        <!-- 1. Quyền Hệ Thống Toàn Cục -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
            <span>🔐</span>
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">Quyền Hệ Thống Toàn Cục</h3>
          </div>
          <div class="p-4 grid grid-cols-2 gap-3">
            <label v-for="role in roleOptions" :key="role.id"
              class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all"
              :class="hasRole(role.id)
                ? 'border-indigo-500 bg-indigo-50'
                : 'border-gray-200 hover:border-gray-300'">
              <input type="checkbox" :checked="hasRole(role.id)" @change="toggleRole(role.id)"
                class="mt-0.5 w-4 h-4 text-indigo-600 rounded border-gray-300">
              <div class="min-w-0">
                <p class="text-sm font-black text-gray-900">{{ role.label }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ role.desc }}</p>
              </div>
            </label>
          </div>
          <!-- Grant Full button -->
          <div class="px-4 pb-4">
            <button @click="grantFull" :disabled="isGrantingFull"
              class="w-full py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-sm font-black rounded-xl hover:from-amber-600 hover:to-orange-600 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              {{ isGrantingFull ? 'Đang cấp...' : '⚡ Cấp Toàn Quyền Tất Cả' }}
            </button>
          </div>
        </div>

        <!-- 2. Quyền Ban Ngành / MAC Matrix -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">
              👥 Quyền Ban Ngành
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Đã cấp quyền:</p>
          </div>

          <!-- Dept chips — currently enabled depts -->
          <div class="px-4 py-3 flex flex-wrap gap-2">
            <template v-if="grantedDeptIds.size > 0">
              <button v-for="dept in departments.filter(d => grantedDeptIds.has(d.id))" :key="dept.id"
                @click="activeDeptId = activeDeptId === dept.id ? null : dept.id"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-all"
                :class="activeDeptId === dept.id
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100'">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ dept.name }}
              </button>
            </template>
            <p v-else class="text-xs text-gray-400 italic py-1">Chưa cấp quyền bất kỳ ban ngành nào.</p>
          </div>

          <!-- Expanded dept → feature list -->
          <div v-if="activeDept" class="border-t border-gray-100 mx-4 mb-4 rounded-xl border border-gray-200 overflow-hidden">
            <!-- Dept card header -->
            <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-200">
              <div class="flex items-center gap-2">
                <span class="text-base">🏢</span>
                <span class="font-black text-gray-900 text-sm">{{ activeDept.name }}</span>
                <span class="text-[10px] px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full font-bold">
                  {{ features.filter(f => isEnabled(activeDept.id, f.id)).length }}/{{ features.length }} tính năng
                </span>
              </div>
              <!-- Access level per dept (top-level) -->
              <select
                class="text-xs border border-gray-200 rounded-lg font-bold bg-white focus:ring-1 focus:ring-indigo-400 py-1 px-2 text-gray-600"
                @change="features.forEach(f => isEnabled(activeDept.id, f.id) && setAccessLevel(activeDept.id, f.id, $event.target.value))">
                <option value="view">Lý Lịch (xem)</option>
                <option value="manage">Quản lý</option>
              </select>
            </div>

            <!-- Feature rows -->
            <div class="divide-y divide-gray-100">
              <label v-for="feature in features" :key="feature.id"
                class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors">
                <div class="flex items-center gap-3">
                  <span class="text-lg w-6 text-center">{{ featureIcon(feature.slug) }}</span>
                  <span class="text-sm font-medium text-gray-800">{{ feature.name }}</span>
                </div>
                <input type="checkbox"
                  :checked="isEnabled(activeDept.id, feature.id)"
                  @change="toggleFeature(activeDept.id, feature.id, $event.target.checked)"
                  class="w-4 h-4 text-indigo-600 rounded border-gray-300 cursor-pointer focus:ring-indigo-400">
              </label>
            </div>
          </div>
        </div>

        <!-- 3. Thêm ban ngành -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-black text-gray-700">➕ Thêm ban ngành</h3>
          </div>
          <div class="p-4 space-y-4">
            <div v-for="(depts, block) in deptGroups" :key="block">
              <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-1">
                <span>{{ block === 'activities' ? '🎯' : block === 'ministry' ? '⛪' : '🛡' }}</span>
                {{ blockLabel(block) }}
              </p>
              <div class="flex flex-wrap gap-2">
                <button v-for="dept in depts" :key="dept.id"
                  @click="addDept(dept)"
                  class="flex items-center gap-1 text-xs px-3 py-1.5 rounded-full border transition-all font-medium"
                  :class="grantedDeptIds.has(dept.id)
                    ? 'bg-green-50 border-green-300 text-green-700'
                    : 'bg-white border-gray-300 text-gray-600 hover:border-indigo-300 hover:text-indigo-600'">
                  <span v-if="grantedDeptIds.has(dept.id)" class="text-green-500">✓</span>
                  <span v-else>+</span>
                  {{ dept.name }}
                </button>
              </div>
            </div>
          </div>
        </div>

      </div><!-- end permission panel -->
      </div><!-- end v-show -->

      <!-- Tab 2: System Features Config -->
      <div v-if="activeTab === 'system'" class="mt-4">
        <SystemFeaturesTab 
            :features="features" 
            :departments="departments" 
            :systemConfig="systemConfig" 
        />
      </div>

    </div>

    <!-- Toast -->
    <Transition enter-from-class="opacity-0 translate-y-2" leave-to-class="opacity-0 translate-y-2"
      enter-active-class="transition duration-200" leave-active-class="transition duration-200">
      <div v-if="toastMsg"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-2 px-4 py-3 rounded-xl shadow-xl text-sm font-bold"
        :class="toastError ? 'bg-red-600 text-white' : 'bg-green-600 text-white'">
        {{ toastMsg }}
      </div>
    </Transition>

  </AdminPortalLayout>
</template>
