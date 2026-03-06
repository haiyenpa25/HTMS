<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AdminPortalLayout from '@/Layouts/AdminPortalLayout.vue';
import axios from 'axios';

const props = defineProps({
  users: Object,
  departments: Array,
  orgRoles: Array,
  filters: Object,
});

// ─── Search ──────────────────────────────────────────────────────────────────
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

// ─── Active User State ────────────────────────────────────────────────────────
const activeUser = ref(null);
const isLoading = ref(false);
const memberInfo = ref(null);
const isSaving = ref(false);
const savedMsg = ref('');

const form = ref({
  global_roles: [],
  memberships: [],
});

// Active Chip = which dept accordion is expanded
const activeChipId = ref(null);

// Feature permissions labels with icons
const featurePerms = [
  { key: 'manage_members',    label: 'Quản lý nhân sự',    icon: '👥' },
  { key: 'manage_attendance', label: 'Điểm danh',           icon: '📅' },
  { key: 'manage_funds',      label: 'Quản lý Quỹ',         icon: '💵' },
  { key: 'manage_reports',    label: 'Báo cáo & Thống kê',  icon: '📊' },
];

const DEFAULT_PERMISSIONS = {
  manage_members: true,
  manage_attendance: true,
  manage_funds: false,
  manage_reports: false,
};

// ─── Load user permissions ────────────────────────────────────────────────────
const selectUser = async (user) => {
  activeUser.value = user;
  isLoading.value = true;
  activeChipId.value = null;
  try {
    const res = await axios.get(route('admin.users.permissions.show', user.id));
    form.value.global_roles = res.data.global_roles || [];
    form.value.memberships = (res.data.memberships || []).map(m => ({
      model_type: m.model_type,
      model_id: m.model_id,
      org_role_id: m.org_role_id,
      permissions: m.permissions ?? { ...DEFAULT_PERMISSIONS },
    }));
    memberInfo.value = res.data.member || null;
    // Auto-expand first chip
    if (form.value.memberships.length > 0) {
      activeChipId.value = form.value.memberships[0].model_id;
    }
  } catch (e) {
    console.error('Failed to load permissions', e);
  } finally {
    isLoading.value = false;
  }
};

// ─── Global Roles ─────────────────────────────────────────────────────────────
const globalRoleOptions = [
  { id: 'Super_Admin', label: 'Super Admin', desc: 'Toàn quyền hệ thống', color: 'from-rose-600 to-red-700' },
  { id: 'Pastor',      label: 'Mục Sư',      desc: 'Duyệt báo cáo, quản trị toàn cục', color: 'from-purple-600 to-violet-700' },
];

const hasGlobalRole = (id) => form.value.global_roles.includes(id);
const toggleGlobalRole = (id) => {
  if (hasGlobalRole(id)) {
    form.value.global_roles = form.value.global_roles.filter(r => r !== id);
  } else {
    form.value.global_roles.push(id);
  }
};

// ─── Department Memberships ───────────────────────────────────────────────────
const getDeptMembership = (deptId) =>
  form.value.memberships.find(m => m.model_type === 'App\\Models\\Department' && m.model_id === deptId);

const hasDept = (deptId) => !!getDeptMembership(deptId);

const toggleDept = (deptId) => {
  if (hasDept(deptId)) {
    form.value.memberships = form.value.memberships.filter(
      m => !(m.model_type === 'App\\Models\\Department' && m.model_id === deptId)
    );
    if (activeChipId.value === deptId) {
      activeChipId.value = form.value.memberships[0]?.model_id ?? null;
    }
  } else {
    const defaultRole = props.orgRoles.find(r => r.code === 'team_member') ?? props.orgRoles[props.orgRoles.length - 1];
    form.value.memberships.push({
      model_type: 'App\\Models\\Department',
      model_id: deptId,
      org_role_id: defaultRole?.id ?? 1,
      permissions: { ...DEFAULT_PERMISSIONS },
    });
    activeChipId.value = deptId;
  }
};

const updateDeptRole = (deptId, roleId) => {
  const m = getDeptMembership(deptId);
  if (m) m.org_role_id = parseInt(roleId);
};

const togglePermission = (deptId, key) => {
  const m = getDeptMembership(deptId);
  if (m) {
    if (!m.permissions) m.permissions = { ...DEFAULT_PERMISSIONS };
    m.permissions[key] = !m.permissions[key];
  }
};

// Active chips = depts where user has a membership
const activeChips = computed(() =>
  form.value.memberships
    .filter(m => m.model_type === 'App\\Models\\Department')
    .map(m => {
      const dept = props.departments.find(d => d.id === m.model_id);
      return dept ? { ...dept, membership: m } : null;
    })
    .filter(Boolean)
);

// Accordion = currently selected chip
const activeChipDept = computed(() =>
  activeChips.value.find(c => c.id === activeChipId.value) ?? null
);

// ─── Church Membership (Ban Chấp Sự) ─────────────────────────────────────────
const churchMembership = computed(() =>
  form.value.memberships.find(m => m.model_type === 'App\\Models\\Church')
);
const hasChurch = computed(() => !!churchMembership.value);
const toggleChurch = () => {
  if (hasChurch.value) {
    form.value.memberships = form.value.memberships.filter(m => m.model_type !== 'App\\Models\\Church');
  } else {
    const dRole = props.orgRoles.find(r => r.code === 'deacon_secretary') ?? props.orgRoles[0];
    form.value.memberships.push({
      model_type: 'App\\Models\\Church',
      model_id: 1,
      org_role_id: dRole?.id ?? 1,
      permissions: { ...DEFAULT_PERMISSIONS },
    });
  }
};
const updateChurchRole = (roleId) => {
  if (churchMembership.value) churchMembership.value.org_role_id = parseInt(roleId);
};
const churchRoles = computed(() => props.orgRoles.filter(r => r.code?.startsWith('deacon_')));
const deptRoles = computed(() => props.orgRoles.filter(r => !r.code?.startsWith('deacon_') && !['pastor', 'bts_admin'].includes(r.code)));

// ─── Save ─────────────────────────────────────────────────────────────────────
const save = async () => {
  isSaving.value = true;
  savedMsg.value = '';
  try {
    await axios.post(route('admin.users.permissions.update', activeUser.value.id), {
      _method: 'POST',
      global_roles: form.value.global_roles,
      memberships: form.value.memberships,
    });
    savedMsg.value = 'Đã lưu thành công!';
    setTimeout(() => savedMsg.value = '', 3000);
  } catch (e) {
    savedMsg.value = 'Lỗi khi lưu!';
  } finally {
    isSaving.value = false;
  }
};

// Avatar letter
const avatarLetter = computed(() => {
  if (!activeUser.value) return '?';
  return (activeUser.value.name || 'U').charAt(0).toUpperCase();
});
</script>

<template>
  <Head title="Phân Quyền Chi Tiết" />
  <AdminPortalLayout title="Cấp Quyền Người Dùng" active-tab="permissions">
    <div class="min-h-screen bg-gray-50 text-gray-900">
      <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

        <!-- ── Main Layout: Sidebar + Detail ── -->
        <div class="flex gap-5 h-[calc(100vh-180px)] min-h-[600px]">

          <!-- ── LEFT: User List ── -->
          <div class="w-76 flex-shrink-0 flex flex-col bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <!-- Search -->
            <div class="p-4 border-b border-gray-100">
              <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input v-model="searchInput" @input="handleSearch" type="text"
                  placeholder="Tìm tên, email..."
                  class="w-full pl-9 pr-3 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition" />
              </div>
            </div>

            <!-- Users List -->
            <div class="flex-1 overflow-y-auto p-2 space-y-1">
              <button v-for="user in users.data" :key="user.id"
                @click="selectUser(user)"
                class="w-full text-left px-4 py-3 rounded-xl transition-all duration-150 flex items-center gap-3"
                :class="activeUser?.id === user.id ? 'bg-indigo-600 shadow-md' : 'hover:bg-gray-50'">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-sm flex-shrink-0"
                  :class="activeUser?.id === user.id ? 'bg-indigo-400 text-white' : 'bg-indigo-100 text-indigo-600'">
                  {{ (user.name || 'U').charAt(0).toUpperCase() }}
                </div>
                <div class="overflow-hidden">
                  <div class="font-bold text-sm truncate" :class="activeUser?.id === user.id ? 'text-white' : 'text-gray-900'">{{ user.name }}</div>
                  <div class="text-xs truncate" :class="activeUser?.id === user.id ? 'text-indigo-200' : 'text-gray-500'">{{ user.email }}</div>
                </div>
              </button>
              <div v-if="!users.data.length" class="py-12 text-center text-gray-400 text-sm">Không tìm thấy người dùng.</div>
            </div>
          </div>

          <!-- ── RIGHT: Permission Detail Panel ── -->
          <div class="flex-1 flex flex-col bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

            <!-- Empty state -->
            <div v-if="!activeUser" class="flex-1 flex flex-col items-center justify-center text-gray-400">
              <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-3xl mb-4">👈</div>
              <p class="font-bold text-gray-600">Chọn một tài khoản</p>
              <p class="text-sm mt-1 text-gray-400">để thiết lập quyền chi tiết</p>
            </div>

            <!-- Loading -->
            <div v-else-if="isLoading" class="flex-1 flex items-center justify-center">
              <div class="w-10 h-10 rounded-full border-2 border-indigo-500 border-t-transparent animate-spin"></div>
            </div>

            <!-- Content -->
            <template v-else>
              <!-- Header: Profile + Save -->
              <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white sticky top-0 z-10">
                <div class="flex items-center gap-4">
                  <div class="relative">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-xl font-black text-white shadow-md">
                      {{ avatarLetter }}
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-green-500 border-2 border-white"></div>
                  </div>
                  <div>
                    <h2 class="font-black text-gray-900 text-base">{{ activeUser.name }}</h2>
                    <p class="text-xs text-gray-500">Phân quyền chi tiết tính năng</p>
                    <p v-if="memberInfo?.code" class="text-xs text-indigo-500 font-mono mt-0.5">{{ memberInfo.code }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-3">
                  <span v-if="savedMsg" class="text-sm font-bold" :class="savedMsg.includes('Lỗi') ? 'text-red-500' : 'text-green-600'">{{ savedMsg }}</span>
                  <button @click="save" :disabled="isSaving"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-bold text-sm rounded-xl shadow-sm transition-all flex items-center gap-2">
                    <svg v-if="isSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Lưu
                  </button>
                </div>
              </div>

              <!-- Body: Scrollable -->
              <div class="flex-1 overflow-y-auto p-6 space-y-5">

                <!-- ─ 1. Global Roles ─ -->
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                  <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 rounded bg-rose-100 text-rose-500 flex items-center justify-center">🌍</span>
                    Quyền Hệ Thống Toàn Cục
                  </h3>
                  <div class="grid grid-cols-2 gap-3">
                    <label v-for="gr in globalRoleOptions" :key="gr.id"
                      @click="toggleGlobalRole(gr.id)"
                      class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                      :class="hasGlobalRole(gr.id) ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'">
                      <div class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 transition-all"
                        :class="hasGlobalRole(gr.id) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300'">
                        <svg v-if="hasGlobalRole(gr.id)" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                      </div>
                      <div>
                        <div class="font-bold text-sm text-gray-900">{{ gr.label }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ gr.desc }}</div>
                      </div>
                    </label>
                  </div>
                </div>

                <!-- ─ 2. Ban Chấp Sự ─ -->
                <div class="bg-amber-50 rounded-2xl p-5 border border-amber-100">
                  <h3 class="text-xs font-black text-amber-700 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <span>🏛️</span> Ban Chấp Sự
                  </h3>
                  <label @click="toggleChurch"
                    class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                    :class="hasChurch ? 'border-amber-400 bg-amber-100' : 'border-amber-200 hover:bg-amber-100'">
                    <div class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 transition-all"
                      :class="hasChurch ? 'bg-amber-500 border-amber-500' : 'border-amber-300'">
                      <svg v-if="hasChurch" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      </svg>
                    </div>
                    <span class="font-bold text-sm text-amber-900">Có quyền Ban Chấp Sự (Thư ký / Thủ quỹ)</span>
                  </label>
                  <div v-if="hasChurch" class="mt-3 ml-8 flex items-center gap-3">
                    <span class="text-xs text-gray-500">Chức vụ:</span>
                    <select :value="churchMembership?.org_role_id"
                      @change="(e) => updateChurchRole(e.target.value)"
                      class="text-sm bg-white border border-amber-200 text-gray-700 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-amber-400 outline-none">
                      <option v-for="r in churchRoles" :key="r.id" :value="r.id">{{ r.name }}</option>
                    </select>
                  </div>
                </div>

                <!-- ─ 3. Ban Ngành ─ -->
                <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                  <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <span>🏃</span> Quyền Ban Ngành
                  </h3>

                  <!-- Active Department Chips -->
                  <div class="mb-4">
                    <p class="text-xs text-gray-500 mb-3">Đã cấp quyền:</p>
                    <div class="flex flex-wrap gap-2">
                      <button v-for="chip in activeChips" :key="chip.id"
                        @click="activeChipId = chip.id"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold transition-all border"
                        :class="activeChipId === chip.id ? 'bg-indigo-600 border-indigo-500 text-white shadow-md' : 'bg-white border-gray-200 text-gray-700 hover:border-indigo-300'">
                        {{ chip.block === 'activities' ? '🏃' : '🙏' }}
                        {{ chip.name }}
                        <svg @click.stop="toggleDept(chip.id)" class="w-3 h-3 ml-1 opacity-60 hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                      </button>
                      <span v-if="activeChips.length === 0" class="text-xs text-gray-400 italic">Chưa có ban ngành nào</span>
                    </div>
                  </div>

                  <!-- Accordion: Active chip permissions -->
                  <div v-if="activeChipDept" class="mb-4 rounded-xl bg-white border border-indigo-200 overflow-hidden shadow-sm">
                    <div class="flex items-center justify-between px-4 py-3 bg-indigo-50 border-b border-indigo-100">
                      <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="font-black text-indigo-900 text-sm">{{ activeChipDept.name }}</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-600 font-bold">Đang chỉnh sửa</span>
                      </div>
                      <select :value="activeChipDept.membership.org_role_id"
                        @change="(e) => updateDeptRole(activeChipDept.id, e.target.value)"
                        class="text-xs bg-white border border-indigo-200 text-gray-700 rounded-lg px-2 py-1 focus:ring-2 focus:ring-indigo-400 outline-none">
                        <option v-for="r in deptRoles" :key="r.id" :value="r.id">{{ r.name }}</option>
                      </select>
                    </div>
                    <!-- Feature Permissions -->
                    <div class="divide-y divide-gray-100">
                      <label v-for="perm in featurePerms" :key="perm.key"
                        @click="togglePermission(activeChipDept.id, perm.key)"
                        class="flex items-center justify-between px-5 py-3.5 hover:bg-indigo-50/50 cursor-pointer transition-colors">
                        <div class="flex items-center gap-3">
                          <span class="text-lg w-7">{{ perm.icon }}</span>
                          <span class="text-sm font-medium text-gray-700">{{ perm.label }}</span>
                        </div>
                        <div class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 transition-all"
                          :class="activeChipDept.membership.permissions?.[perm.key] ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300'">
                          <svg v-if="activeChipDept.membership.permissions?.[perm.key]"
                            class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                          </svg>
                        </div>
                      </label>
                    </div>
                  </div>

                  <!-- Other chips (collapsed) -->
                  <div v-for="chip in activeChips.filter(c => c.id !== activeChipId)" :key="'o' + chip.id"
                    class="mb-2 rounded-xl border border-gray-200 overflow-hidden">
                    <button @click="activeChipId = chip.id"
                      class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
                      <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="font-bold text-gray-700 text-sm">{{ chip.name }}</span>
                      </div>
                      <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                      </svg>
                    </button>
                  </div>

                  <!-- Add Departments -->
                  <div class="mt-4 border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-500 mb-3">Thêm ban ngành:</p>
                    <div class="mb-3">
                      <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-2">🏃 Ban Sinh Hoạt</p>
                      <div class="flex flex-wrap gap-2">
                        <button v-for="dept in departments.filter(d => d.block === 'activities')" :key="dept.id"
                          @click="toggleDept(dept.id)"
                          class="px-3 py-1.5 rounded-full text-xs font-bold border transition-all"
                          :class="hasDept(dept.id) ? 'bg-emerald-100 border-emerald-400 text-emerald-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                          {{ hasDept(dept.id) ? '✓ ' : '+ ' }}{{ dept.name }}
                        </button>
                      </div>
                    </div>
                    <div>
                      <p class="text-[10px] font-bold text-purple-600 uppercase tracking-wider mb-2">🙏 Ban Mục Vụ</p>
                      <div class="flex flex-wrap gap-2">
                        <button v-for="dept in departments.filter(d => d.block === 'ministry')" :key="dept.id"
                          @click="toggleDept(dept.id)"
                          class="px-3 py-1.5 rounded-full text-xs font-bold border transition-all"
                          :class="hasDept(dept.id) ? 'bg-purple-100 border-purple-400 text-purple-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                          {{ hasDept(dept.id) ? '✓ ' : '+ ' }}{{ dept.name }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </AdminPortalLayout>
</template>
