<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminPortalLayout from '@/Layouts/AdminPortalLayout.vue';
import axios from 'axios';

const props = defineProps({
  users:         Object,
  departments:   Array,
  features:      Array, // 10 MAC features from Feature table
  filters:       Object,
  preselectUser: Object,
});

// ── Search ────────────────────────────────────────────────────────────────────
const searchInput = ref(props.filters?.search || '');
let searchTimeout;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(route('admin.users.permissions'), { search: searchInput.value }, {
      preserveState: true, replace: true,
    });
  }, 500);
};

// ── Active User & MAC state ────────────────────────────────────────────────────
const activeUser    = ref(null);
const isLoading     = ref(false);
const isSuperAdmin  = ref(false);
const globalRoles   = ref([]);
// mac: Map<`${dept_id}-${feature_id}`, { is_enabled, access_level }>
const macMatrix     = ref({});
const toastMsg      = ref('');
const toastType     = ref('success'); // success | error
const isGranting    = ref(false);

// Preselect on mount
onMounted(async () => {
  if (props.preselectUser) {
    await selectUser(props.preselectUser);
  }
});

const hideSidebar = computed(() => !!props.preselectUser);

// ── Load user MAC matrix (AJAX) ───────────────────────────────────────────────
const selectUser = async (user) => {
  activeUser.value  = user;
  isLoading.value   = true;
  macMatrix.value   = {};
  globalRoles.value = [];
  isSuperAdmin.value = false;
  try {
    const res = await axios.get(route('admin.users.permissions.show', user.id));
    globalRoles.value  = res.data.global_roles || [];
    isSuperAdmin.value = res.data.is_super_admin || false;

    // Build MAC matrix map from array
    const map = {};
    for (const row of (res.data.permissions || [])) {
      const key = `${row.department_id}-${row.feature_id}`;
      map[key]  = { is_enabled: row.is_enabled, access_level: row.access_level || 'view' };
    }
    macMatrix.value = map;
  } catch (e) {
    showToast('Lỗi khi tải dữ liệu phân quyền.', 'error');
  } finally {
    isLoading.value = false;
  }
};

// ── MAC Matrix helpers ────────────────────────────────────────────────────────
const macKey = (deptId, featureId) => `${deptId}-${featureId}`;

const isEnabled = (deptId, featureId) => {
  return macMatrix.value[macKey(deptId, featureId)]?.is_enabled ?? false;
};

const accessLevel = (deptId, featureId) => {
  return macMatrix.value[macKey(deptId, featureId)]?.access_level ?? 'view';
};

// ── Toggle 1 feature for current user ─────────────────────────────────────────
const toggleFeature = async (dept, feature, newVal) => {
  if (!activeUser.value) return;

  const key = macKey(dept.id, feature.id);
  // Optimistic update
  macMatrix.value = {
    ...macMatrix.value,
    [key]: {
      is_enabled:   newVal,
      access_level: macMatrix.value[key]?.access_level ?? 'view',
    },
  };

  try {
    await axios.post(
      route('admin.users.permissions.toggle', activeUser.value.id),
      { department_id: dept.id, feature_id: feature.id, is_enabled: newVal }
    );
    showToast(newVal ? `Đã bật: ${feature.name} — ${dept.name}` : `Đã tắt: ${feature.name}`, 'success');
  } catch {
    // Revert
    macMatrix.value = {
      ...macMatrix.value,
      [key]: { is_enabled: !newVal, access_level: macMatrix.value[key]?.access_level ?? 'view' },
    };
    showToast('Lỗi khi cập nhật quyền.', 'error');
  }
};

// ── Toggle access level ────────────────────────────────────────────────────────
const toggleAccess = async (dept, feature, level) => {
  if (!activeUser.value) return;
  const key = macKey(dept.id, feature.id);
  const prev = macMatrix.value[key]?.access_level ?? 'view';

  macMatrix.value = {
    ...macMatrix.value,
    [key]: { ...macMatrix.value[key], access_level: level },
  };

  try {
    await axios.post(
      route('admin.users.permissions.toggle', activeUser.value.id),
      {
        department_id: dept.id,
        feature_id:    feature.id,
        is_enabled:    macMatrix.value[key]?.is_enabled ?? false,
        access_level:  level,
      }
    );
  } catch {
    macMatrix.value = {
      ...macMatrix.value,
      [key]: { ...macMatrix.value[key], access_level: prev },
    };
    showToast('Lỗi khi cập nhật.', 'error');
  }
};

// ── Grant Full Access ──────────────────────────────────────────────────────────
const grantFull = async () => {
  if (!activeUser.value || isGranting.value) return;
  if (!confirm(`Cấp toàn quyền cho ${activeUser.value.name}? Thao tác này bật tất cả tính năng cho mọi ban ngành.`)) return;
  isGranting.value = true;
  try {
    const res = await axios.post(route('admin.users.permissions.grant-full', activeUser.value.id));
    showToast(res.data.message || 'Đã cấp toàn quyền!', 'success');
    // Reload matrix
    await selectUser(activeUser.value);
  } catch {
    showToast('Lỗi khi cấp toàn quyền.', 'error');
  } finally {
    isGranting.value = false;
  }
};

// ── Feature groups by portal_type ─────────────────────────────────────────────
const featureGroups = computed(() => {
  const groups = {};
  for (const f of (props.features || [])) {
    if (!groups[f.portal_type]) groups[f.portal_type] = [];
    groups[f.portal_type].push(f);
  }
  return groups;
});

const portalTypeLabel = (type) => {
  const labels = {
    activities: '🎯 Ban Ngành Sinh Hoạt',
    ministry:   '⛪ Ban Ngành Mục Vụ (Cơ Đốc Giáo Dục)',
    deacon:     '🛡 Ban Chấp Sự',
  };
  return labels[type] ?? type;
};

// ── Departments by block ───────────────────────────────────────────────────────
const deptsByBlock = computed(() => {
  const groups = {};
  for (const d of (props.departments || [])) {
    const b = d.block || 'activities';
    if (!groups[b]) groups[b] = [];
    groups[b].push(d);
  }
  return groups;
});

// Active tab block
const activeBlock = ref('activities');
const blocks = [
  { key: 'activities', label: '🎯 Sinh Hoạt' },
  { key: 'ministry',   label: '⛪ Mục Vụ' },
  { key: 'leadership', label: '🛡 Lãnh Đạo' },
];

// ── Toast ──────────────────────────────────────────────────────────────────────
let toastTimer;
const showToast = (msg, type = 'success') => {
  toastMsg.value  = msg;
  toastType.value = type;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { toastMsg.value = ''; }, 3000);
};

// Re-select user when changing tab block
watch(activeBlock, () => {
  // nothing special, UI recalculates via computed
});
</script>

<template>
  <Head title="Phân Quyền Người Dùng (MAC)" />
  <AdminPortalLayout>
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-black text-gray-900">🔐 Ma Trận Phân Quyền</h1>
          <p class="text-sm text-gray-500 mt-0.5">Chọn người dùng → bật/tắt tính năng theo từng ban ngành</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- ══ LEFT: User Picker ══════════════════════════════════════════════ -->
        <div v-if="!hideSidebar" class="lg:col-span-1">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Search -->
            <div class="px-4 py-3 border-b border-gray-100">
              <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input v-model="searchInput" @input="handleSearch" type="text"
                  class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  placeholder="Tìm email hoặc tên...">
              </div>
            </div>

            <!-- User list -->
            <div class="divide-y divide-gray-50 max-h-[70vh] overflow-y-auto">
              <button v-for="u in users.data" :key="u.id" @click="selectUser(u)"
                class="w-full text-left px-4 py-3 hover:bg-indigo-50 transition-colors flex items-center gap-3"
                :class="activeUser?.id === u.id ? 'bg-indigo-50 border-l-4 border-indigo-500' : ''">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-black shrink-0">
                  {{ u.name.charAt(0).toUpperCase() }}
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-bold text-gray-900 truncate">{{ u.name }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ u.email }}</p>
                </div>
                <span v-if="u.roles?.length" class="text-[10px] px-2 py-0.5 bg-rose-100 text-rose-700 rounded-full font-bold shrink-0">
                  {{ u.roles[0] }}
                </span>
              </button>
              <div v-if="users.data.length === 0" class="py-12 text-center text-gray-400 text-sm">
                Không tìm thấy người dùng.
              </div>
            </div>
          </div>
        </div>

        <!-- ══ RIGHT: MAC Matrix Panel ════════════════════════════════════════ -->
        <div :class="hideSidebar ? 'lg:col-span-3' : 'lg:col-span-2'">

          <!-- Empty state -->
          <div v-if="!activeUser" class="bg-white rounded-2xl shadow-sm border border-gray-100 py-20 flex flex-col items-center text-gray-400">
            <svg class="w-14 h-14 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <p class="font-bold">Chọn người dùng để phân quyền</p>
          </div>

          <!-- Loading -->
          <div v-else-if="isLoading" class="bg-white rounded-2xl shadow-sm border border-gray-100 py-20 flex flex-col items-center text-gray-400">
            <svg class="animate-spin w-10 h-10 text-indigo-400 mb-3" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <p class="text-sm">Đang tải phân quyền...</p>
          </div>

          <!-- Matrix Panel -->
          <div v-else class="space-y-4">
            <!-- User header bar -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center justify-between flex-wrap gap-3">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-lg">
                  {{ activeUser.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <h2 class="font-black text-gray-900 text-base">{{ activeUser.name }}</h2>
                  <p class="text-xs text-gray-400">{{ activeUser.email }}</p>
                  <div class="flex gap-1 mt-1 flex-wrap">
                    <span v-for="r in globalRoles" :key="r"
                      class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                      :class="r === 'Super_Admin' ? 'bg-rose-100 text-rose-700' : 'bg-purple-100 text-purple-700'">
                      {{ r }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="flex gap-2 flex-wrap">
                <!-- God Mode badge -->
                <div v-if="isSuperAdmin" class="flex items-center gap-1 px-3 py-1.5 bg-rose-50 border border-rose-200 rounded-xl">
                  <span class="text-rose-600 text-xs font-black">⚡ GOD MODE</span>
                  <span class="text-red-400 text-[10px]">— Bypass tất cả</span>
                </div>
                <!-- Grant Full button -->
                <button @click="grantFull" :disabled="isGranting"
                  class="flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl text-sm font-black hover:from-amber-600 hover:to-orange-600 transition-all disabled:opacity-50 shadow-sm">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                  {{ isGranting ? 'Đang cấp...' : 'Cấp Toàn Quyền' }}
                </button>
              </div>
            </div>

            <!-- Block tabs -->
            <div class="flex gap-2 flex-wrap">
              <button v-for="b in blocks" :key="b.key" @click="activeBlock = b.key"
                class="px-4 py-2 rounded-xl text-sm font-bold transition-all"
                :class="activeBlock === b.key
                  ? 'bg-indigo-600 text-white shadow-sm'
                  : 'bg-white text-gray-600 border border-gray-200 hover:border-indigo-300 hover:text-indigo-600'">
                {{ b.label }}
              </button>
            </div>

            <!-- Department × Feature matrix (active block) -->
            <div v-for="dept in (deptsByBlock[activeBlock] || [])" :key="dept.id"
              class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

              <!-- Dept header -->
              <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-black">
                  {{ dept.name.charAt(0) }}
                </div>
                <h3 class="font-black text-gray-900 text-sm">{{ dept.name }}</h3>
                <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full font-bold">{{ dept.code }}</span>
              </div>

              <!-- Features grid -->
              <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div v-for="feature in features" :key="feature.id"
                  class="flex items-center justify-between gap-3 p-3 rounded-xl border transition-all"
                  :class="isEnabled(dept.id, feature.id)
                    ? 'bg-green-50 border-green-200'
                    : 'bg-gray-50 border-gray-200'">

                  <!-- Feature info -->
                  <div class="flex items-center gap-2 min-w-0">
                    <span class="text-lg shrink-0">{{ feature.icon }}</span>
                    <div class="min-w-0">
                      <p class="text-sm font-bold text-gray-900 truncate">{{ feature.name }}</p>
                      <p class="text-[10px] text-gray-400 truncate">{{ feature.slug }}</p>
                    </div>
                  </div>

                  <!-- Toggle + Access level -->
                  <div class="flex items-center gap-2 shrink-0">
                    <!-- Access level selector (hiện khi enabled) -->
                    <select v-if="isEnabled(dept.id, feature.id)"
                      :value="accessLevel(dept.id, feature.id)"
                      @change="toggleAccess(dept, feature, $event.target.value)"
                      class="text-[10px] border-green-200 rounded-lg font-bold bg-white focus:ring-1 focus:ring-green-500 py-0.5 px-1">
                      <option value="view">Xem</option>
                      <option value="manage">Quản lý</option>
                    </select>
                    <!-- Toggle switch -->
                    <button @click="toggleFeature(dept, feature, !isEnabled(dept.id, feature.id))"
                      class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                      :class="isEnabled(dept.id, feature.id) ? 'bg-green-500' : 'bg-gray-300'">
                      <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
                        :class="isEnabled(dept.id, feature.id) ? 'translate-x-6' : 'translate-x-1'"/>
                    </button>
                  </div>
                </div>

                <div v-if="features.length === 0" class="col-span-2 text-center py-8 text-gray-400 text-sm">
                  Chưa có tính năng nào.
                </div>
              </div>
            </div>

            <!-- Empty departments -->
            <div v-if="!deptsByBlock[activeBlock]?.length"
              class="bg-white rounded-2xl shadow-sm border border-gray-100 py-12 text-center text-gray-400 text-sm">
              Không có ban ngành nào trong nhóm này.
            </div>
          </div>
        </div>

      </div><!-- end grid -->
    </div>

    <!-- Toast notification -->
    <Transition enter-from-class="opacity-0 translate-y-2" leave-to-class="opacity-0 translate-y-2"
      enter-active-class="transition duration-200" leave-active-class="transition duration-200">
      <div v-if="toastMsg" class="fixed bottom-6 right-6 z-50 flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg text-sm font-bold"
        :class="toastType === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path v-if="toastType === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        {{ toastMsg }}
      </div>
    </Transition>

  </AdminPortalLayout>
</template>
