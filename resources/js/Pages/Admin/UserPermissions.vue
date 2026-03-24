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

// -- Search --------------------------------------------------------------------
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

// -- Active User ---------------------------------------------------------------
const activeUser    = ref(null);
const showUserList  = ref(!props.preselectUser);
const isLoading     = ref(false);
const isSaving      = ref(false);
const isSuperAdmin  = ref(false);
const globalRoles   = ref([]);
// Map: `${dept_id}-${feature_id}` ? { is_enabled, access_level }
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
// Block dropdown for feature list (defaults to dept's own block)
const selectedBlock = ref('activities');

const activeDept = computed(() =>
  props.departments?.find(d => d.id === activeDeptId.value) ?? null
);

// When dept changes, auto-set the dropdown to that dept's block
watch(activeDeptId, (newId) => {
  const dept = props.departments?.find(d => d.id === newId);
  selectedBlock.value = dept?.block || 'activities';
});

// Features for the selected block type (controlled by dropdown)
const activeDeptFeatures = computed(() =>
  props.features.filter(f => f.portal_type === selectedBlock.value)
);

const blockOptions = [
  { value: 'activities', label: '?? Sinh Ho?t' },
  { value: 'ministry',   label: '? M?c V?' },
  { value: 'leadership', label: '?? Ch?p S?' },
];

// Dept groups for "Th�m ban ng�nh" section
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
  activities: 'Ban Sinh Ho?t',
  ministry:   'Ban M?c V?',
  leadership: 'Ban Ch?p S?',
})[b] ?? b;

// -- Load user -----------------------------------------------------------------
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
    showToast('L?i khi t?i ph�n quy?n.', true);
  } finally {
    isLoading.value = false;
  }
};

// -- Helpers -------------------------------------------------------------------
const macKey = (dId, fId) => `${dId}-${fId}`;
const isEnabled   = (dId, fId) => macMatrix.value[macKey(dId, fId)]?.is_enabled   ?? false;
const accessLevel = (dId, fId) => macMatrix.value[macKey(dId, fId)]?.access_level ?? 'view';

// -- Toggle feature ------------------------------------------------------------
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
    showToast(newVal ? '�� b?t t�nh nang ?' : '�� t?t t�nh nang');
  } catch {
    // Revert
    macMatrix.value = { ...macMatrix.value, [key]: prev ?? { is_enabled: !newVal, access_level: 'view' } };
    showToast('L?i khi luu quy?n!', true);
  }
};

// -- Toggle access level -------------------------------------------------------
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
    showToast('L?i!', true);
  }
};

// -- Grant/Revoke All for a dept -----------------------------------------------
const isGrantingAll = ref(false);
const grantAllForDept = async (dept) => {
  isGrantingAll.value = true;
  const validFeatures = props.features.filter(f => f.portal_type === selectedBlock.value);
  
  const allEnabled = validFeatures.every(f => isEnabled(dept.id, f.id));
  // Toggle all to opposite
  const newVal = !allEnabled;
  for (const feature of validFeatures) {
    await toggleFeature(dept.id, feature.id, newVal);
  }
  showToast(newVal ? `�� c?p to�n b? quy?n cho ${dept.name}` : `�� thu h?i to�n b? quy?n`);
  isGrantingAll.value = false;
};

// -- Add dept to user: grants initial feature access & opens dept panel -------
const addDept = async (dept) => {
  if (!activeUser.value) return;

  // Set active block to dept's block so features load correctly
  selectedBlock.value = dept.block || 'activities';
  activeDeptId.value = dept.id;

  // If dept is NOT yet in grantedDeptIds, enable the first feature to bootstrap it
  if (!grantedDeptIds.value.has(dept.id)) {
    const firstFeature = props.features.find(f => f.portal_type === dept.block);
    if (firstFeature) {
      await toggleFeature(dept.id, firstFeature.id, true);
    }
  }

  // Scroll the feature panel into view after short delay
  setTimeout(() => {
    document.querySelector('[data-dept-panel]')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }, 100);
};


// -- Global Roles --------------------------------------------------------------
const roleOptions = [
  { id: 'Super_Admin', label: 'Super Admin',  desc: 'To�n quy?n h? th?ng' },
  { id: 'Pastor',      label: 'M?c Su',       desc: 'Duy?t b�o c�o, qu?n tr? to�n c?c' },
];

const hasRole = (id) => globalRoles.value.includes(id);
const toggleRole = async (id) => {
  const newRoles = hasRole(id)
    ? globalRoles.value.filter(r => r !== id)
    : [...globalRoles.value, id];
  globalRoles.value = newRoles;
  try {
    await axios.post(route('admin.users.permissions.roles', activeUser.value.id), { roles: newRoles });
    showToast('�� c?p nh?t vai tr� to�n c?c ?');
  } catch {
    showToast('L?i khi c?p nh?t vai tr�!', true);
  }
};

// -- Grant Full ----------------------------------------------------------------
const isGrantingFull = ref(false);
const grantFull = async () => {
  if (!activeUser.value || isGrantingFull.value) return;
  if (!confirm(`C?p TO�N QUY?N t?t c? t�nh nang?m?i ban ng�nh cho ${activeUser.value.name}?`)) return;
  isGrantingFull.value = true;
  try {
    const res = await axios.post(route('admin.users.permissions.grant-full', activeUser.value.id));
    await selectUser(activeUser.value);
    showToast(res.data.message || '�� c?p to�n quy?n!');
  } catch {
    showToast('L?i!', true);
  } finally {
    isGrantingFull.value = false;
  }
};

// -- Toast ---------------------------------------------------------------------
let toastTimer;
const showToast = (msg, isError = false) => {
  toastMsg.value   = msg;
  toastError.value = isError;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toastMsg.value = '', 2500);
};

// Icon map for features
const featureIcon = (slug) => ({
  'attendance':          '📝',
  'visitation':          '🏥',
  'members':             '👥',
  'assignments':         '📋',
  'reports':             '📊',
  'finance':             '💰',
  'education-classes':   '🎓',
  'education-attendance':'📅',
  'education-offering':  '💵',
  'chronicles':          '📔',
  'module_chronicles':   '📔',
  'education-report':    '📈',
})[slug] ?? '📦';
</script>

<template>
  <Head title="Qu?n L� T�nh Nang & Ph�n Quy?n" />
  <AdminPortalLayout>
    <div class="max-w-4xl mx-auto px-4 py-8">
      
      <!-- Tabs Navigation -->
      <div class="flex space-x-6 border-b border-gray-200 mb-6">
        <button @click="activeTab = 'users'" 
          :class="['py-3 px-1 border-b-2 font-black text-sm transition-all outline-none', activeTab === 'users' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300']">
          Ph�n Quy?n C� Nh�n (User Matrix)
        </button>
        <button @click="activeTab = 'system'" 
          :class="['py-3 px-1 border-b-2 font-black text-sm transition-all outline-none flex items-center gap-2', activeTab === 'system' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-400 hover:text-gray-700 hover:border-gray-300']">
          C?u H�nh T�nh Nang (H? Th?ng)
          <span class="bg-indigo-100 text-indigo-700 py-0.5 px-2 rounded-full text-[10px] hidden sm:inline-block">M?I</span>
        </button>
      </div>

      <!-- Tab 1: User Matrix -->
      <div v-show="activeTab === 'users'">
        <!-- -- User Picker / Header ------------------------------------------ -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-5 overflow-hidden">
        <!-- Search bar -->
        <div class="px-4 py-3 flex items-center gap-3 border-b border-gray-100">
          <div class="relative flex-1">
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input v-model="searchInput" @input="handleSearch" type="text"
              class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
              placeholder="T�m t�n ho?c email t�i kho?n...">
          </div>
          <button v-if="activeUser && !showUserList" @click="showUserList = !showUserList"
            class="text-xs px-3 py-2 border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50 whitespace-nowrap">
            ? Danh s�ch user
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
          <p v-if="users.data.length === 0" class="py-8 text-center text-gray-400 text-sm">Kh�ng t�m th?y.</p>
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
            ? GOD MODE
          </span>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="py-20 flex flex-col items-center text-gray-400">
        <svg class="animate-spin w-10 h-10 text-indigo-300 mb-3" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <p class="text-sm">�ang t?i...</p>
      </div>

      <!-- Empty state -->
      <div v-else-if="!activeUser && !showUserList"
        class="py-20 flex flex-col items-center text-gray-400 bg-white rounded-2xl border border-gray-100">
        <svg class="w-12 h-12 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <p class="font-bold text-sm">Ch?n ngu?i d�ng d? ph�n quy?n</p>
      </div>

      <!-- -- Permission Panel ----------------------------------------------- -->
      <div v-else-if="activeUser && !isLoading && !showUserList" class="space-y-4">

        <!-- 1. Quy?n H? Th?ng To�n C?c -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
            <span>??</span>
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">Quy?n H? Th?ng To�n C?c</h3>
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
              {{ isGrantingFull ? '�ang c?p...' : '? C?p To�n Quy?n T?t C?' }}
            </button>
          </div>
        </div>

        <!-- 2. Quy?n Ban Ng�nh / MAC Matrix -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">
              ?? Quy?n Ban Ng�nh
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">�� c?p quy?n:</p>
          </div>

          <!-- Dept chips � currently enabled depts -->
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
            <p v-else class="text-xs text-gray-400 italic py-1">Chua c?p quy?n b?t k? ban ng�nh n�o.</p>
          </div>

          <!-- Expanded dept → feature list -->
          <div v-if="activeDept" data-dept-panel class="border-t border-gray-100 mx-4 mb-4 rounded-xl border border-gray-200 overflow-hidden">

            <!-- Dept card header -->
            <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-200 flex-wrap gap-2">
              <div class="flex items-center gap-2">
                <span class="text-base">??</span>
                <span class="font-black text-gray-900 text-sm">{{ activeDept.name }}</span>
                <span class="text-[10px] px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full font-bold">
                  {{ activeDeptFeatures.filter(f => isEnabled(activeDept.id, f.id)).length }}/{{ activeDeptFeatures.length }} t�nh nang
                </span>
              </div>
              <div class="flex items-center gap-2 flex-wrap">
                <!-- Block type dropdown -->
                <select v-model="selectedBlock"
                  class="text-xs border border-indigo-200 rounded-lg font-bold bg-indigo-50 text-indigo-700 focus:ring-1 focus:ring-indigo-400 py-1.5 px-2.5">
                  <option v-for="b in blockOptions" :key="b.value" :value="b.value">{{ b.label }}</option>
                </select>
                <!-- Access level select -->
                <select
                  class="text-xs border border-gray-200 rounded-lg font-bold bg-white focus:ring-1 focus:ring-indigo-400 py-1.5 px-2 text-gray-600"
                  @change="activeDeptFeatures.forEach(f => isEnabled(activeDept.id, f.id) && setAccessLevel(activeDept.id, f.id, $event.target.value))">
                  <option value="view">Ch? xem</option>
                  <option value="manage">Qu?n l�</option>
                </select>
              </div>
            </div>

            <!-- Feature rows -->
            <div class="divide-y divide-gray-100">
              <label v-for="feature in activeDeptFeatures" :key="feature.id"
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

        <!-- 3. Th�m ban ng�nh -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-black text-gray-700">? Th�m ban ng�nh</h3>
          </div>
          <div class="p-4 space-y-4">
            <div v-for="(depts, block) in deptGroups" :key="block">
              <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-1">
                <span>{{ block === 'activities' ? '??' : block === 'ministry' ? '?' : '??' }}</span>
                {{ blockLabel(block) }}
              </p>
              <div class="flex flex-wrap gap-2">
                <button v-for="dept in depts" :key="dept.id"
                  @click="addDept(dept)"
                  class="flex items-center gap-1 text-xs px-3 py-1.5 rounded-full border transition-all font-medium"
                  :class="grantedDeptIds.has(dept.id)
                    ? 'bg-green-50 border-green-300 text-green-700'
                    : 'bg-white border-gray-300 text-gray-600 hover:border-indigo-300 hover:text-indigo-600'">
                  <span v-if="grantedDeptIds.has(dept.id)" class="text-green-500">?</span>
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