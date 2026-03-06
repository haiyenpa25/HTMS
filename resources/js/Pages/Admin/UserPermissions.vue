<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
  users: Object,
  departments: Array,
  orgRoles: Array,
  filters: Object,
});

const searchInput = ref(props.filters?.search || '');

// Tìm kiếm User (Debounced)
let searchTimeout;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(route('admin.users.permissions'), { search: searchInput.value }, {
      preserveState: true,
      replace: true,
    });
  }, 500);
};

// State View Giữa Bảng
const activeUser = ref(null);
const isLoadingPermissions = ref(false);

const form = useForm({
  global_roles: [],
  memberships: [],
});

const selectUser = async (user) => {
  activeUser.value = user;
  isLoadingPermissions.value = true;
  
  try {
    const res = await axios.get(route('admin.users.permissions.show', user.id));
    form.global_roles = res.data.global_roles || [];
    
    // Transform fetched memberships back to tree structure state
    form.memberships = (res.data.memberships || []).map(m => ({
      model_type: m.model_type,
      model_id: m.model_id,
      org_role_id: m.org_role_id
    }));
  } catch (err) {
    console.error('Failed to load permissions', err);
  } finally {
    isLoadingPermissions.value = false;
  }
};

const submitPermissions = () => {
  form.post(route('admin.users.permissions.update', activeUser.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Optional: show a toast
    }
  });
};

/* ---------------- Tree View Logic ---------------- */
const spatieGlobalRoles = [
  { id: 'Super_Admin', name: 'Super Admin (Toàn Quyền Toàn Hệ Thống)' },
  { id: 'Pastor', name: 'Mục Sư (Duyệt Báo cáo, Quản trị Toàn Cục)' },
];

const hasGlobalRole = (role) => form.global_roles.includes(role);
const toggleGlobalRole = (role) => {
  if (hasGlobalRole(role)) {
    form.global_roles = form.global_roles.filter(r => r !== role);
  } else {
    form.global_roles.push(role);
  }
};

const getDeptMembership = (deptId) => {
  return form.memberships.find(m => m.model_type === 'App\\Models\\Department' && m.model_id === deptId);
};

const hasDepartment = (deptId) => !!getDeptMembership(deptId);

const toggleDepartment = (deptId) => {
  if (hasDepartment(deptId)) {
    // Remove
    form.memberships = form.memberships.filter(
      m => !(m.model_type === 'App\\Models\\Department' && m.model_id === deptId)
    );
  } else {
    // Default add as Member level 10
    const defaultRole = props.orgRoles.find(r => r.code === 'team_member') || props.orgRoles[props.orgRoles.length - 1];
    form.memberships.push({
      model_type: 'App\\Models\\Department',
      model_id: deptId,
      org_role_id: defaultRole.id,
    });
  }
};

const updateDepartmentRole = (deptId, newRoleId) => {
  const mem = getDeptMembership(deptId);
  if (mem) {
    mem.org_role_id = newRoleId;
  }
};

// Phân tách Nhóm Ban Sinh Họat & Mục vụ theo Keyword (Tạm tính)
const isMinistry = (name) => {
  const kws = ['mục vụ', 'truyền thông', 'âm nhạc', 'y tế', 'thăm viếng', 'chăm sóc', 'cầu nguyện'];
  return kws.some(kw => name.toLowerCase().includes(kw));
};

const minDeptList = computed(() => props.departments.filter(d => isMinistry(d.name)));
const actDeptList = computed(() => props.departments.filter(d => !isMinistry(d.name)));

/* ---------------- Church Level (Ban Chấp Sự / Thư Ký Giáo Hội) ---------------- */
const getChurchMembership = () => {
  return form.memberships.find(m => m.model_type === 'App\\Models\\Church' && m.model_id === 1);
};
const hasChurchRole = computed(() => !!getChurchMembership());
const toggleChurchRole = () => {
  if (hasChurchRole.value) {
    form.memberships = form.memberships.filter(m => !(m.model_type === 'App\\Models\\Church'));
  } else {
    // Default Thư ký Chấp sự
    const dRole = props.orgRoles.find(r => r.code === 'deacon_secretary') || props.orgRoles[0];
    form.memberships.push({
      model_type: 'App\\Models\\Church',
      model_id: 1,
      org_role_id: dRole?.id || 1
    });
  }
};
const updateChurchRole = (newRoleId) => {
  const mem = getChurchMembership();
  if (mem) mem.org_role_id = newRoleId;
};

// Filter roles for UI
const churchRolesOptions = computed(() => props.orgRoles.filter(r => r.code.startsWith('deacon_')));
const deptRolesOptions = computed(() => props.orgRoles.filter(r => !r.code.startsWith('deacon_') && !['pastor', 'bts_admin'].includes(r.code)));

</script>

<template>
  <Head title="Cấp Quyền Hệ Thống" />

  <AuthenticatedLayout>
    <div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-2xl font-black tracking-tight text-gray-900 sm:text-3xl">🔐 Quản Trị Phân Quyền</h1>
        <p class="mt-2 text-sm text-gray-500">Cấp quyền đa tầng (RBAC) cho Thành viên: Cấp Hệ Thống - Cấp Chấp Sự - Cấp Ban Ngành.</p>
      </div>

      <div class="flex flex-col lg:flex-row gap-6 h-[800px]">
        
        <!-- SIDEBAR: USERS LIST -->
        <div class="w-full lg:w-1/3 flex flex-col bg-white rounded-3xl shadow-xl shadow-blue-900/5 overflow-hidden border border-gray-100">
          <div class="p-4 border-b border-gray-100 bg-gray-50/50">
            <input 
              v-model="searchInput"
              @input="handleSearch"
              type="text" 
              placeholder="🔍 Tìm tên, email..." 
              class="w-full px-4 py-2.5 text-sm bg-white border-0 ring-1 ring-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 transition-shadow"
            />
          </div>
          
          <div class="flex-1 overflow-y-auto p-2 space-y-1">
            <button 
              v-for="user in users.data" 
              :key="user.id"
              @click="selectUser(user)"
              class="w-full text-left px-4 py-3 rounded-2xl transition-all duration-200 group"
              :class="activeUser?.id === user.id ? 'bg-blue-600 shadow-md shadow-blue-600/20' : 'hover:bg-gray-50'"
            >
              <div class="font-bold text-sm" :class="activeUser?.id === user.id ? 'text-white' : 'text-gray-900'">{{ user.name }}</div>
              <div class="text-xs mt-0.5 truncate" :class="activeUser?.id === user.id ? 'text-blue-200' : 'text-gray-500'">{{ user.email }}</div>
            </button>
            <div v-if="!users.data.length" class="p-8 text-center text-gray-400 text-sm">Không tìm thấy ai.</div>
          </div>
        </div>

        <!-- MAIN AREA: TREE VIEW PERMISSIONS -->
        <div class="w-full lg:w-2/3 flex flex-col bg-white rounded-3xl shadow-xl shadow-blue-900/5 overflow-hidden border border-gray-100 relative">
          
          <div v-if="!activeUser" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-50/50">
            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4 text-3xl">👈</div>
            <h3 class="text-lg font-bold text-gray-900">Chọn một nhân sự</h3>
            <p class="text-sm text-gray-500 mt-1">Bấm vào tên một người ở cột bên trái để bắt đầu phân quyền.</p>
          </div>

          <template v-else>
            <!-- Header Phân Quyền -->
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-white z-10 sticky top-0">
              <div>
                <h2 class="text-lg font-black text-gray-900">Phân quyền: <span class="text-blue-600">{{ activeUser.name }}</span></h2>
                <div class="text-xs text-gray-500 font-medium">{{ activeUser.email }}</div>
              </div>
              <button 
                @click="submitPermissions"
                :disabled="form.processing"
                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center gap-2"
              >
                <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Lưu Thay Đổi
              </button>
            </div>

            <div class="flex-1 overflow-y-auto bg-gray-50/30 p-6">
              
              <div v-if="isLoadingPermissions" class="flex justify-center py-12">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              </div>

              <div v-else class="space-y-8">
                <!-- 1. System Level -->
                <div class="bg-white p-5 rounded-2xl border border-gray-200">
                  <h3 class="font-black text-sm text-rose-600 mb-4 uppercase tracking-wider flex items-center gap-2">
                    🌍 Cấp Độ Hệ Thống (Global Roles)
                  </h3>
                  <div class="space-y-3">
                    <label v-for="gRole in spatieGlobalRoles" :key="gRole.id" class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 cursor-pointer transition">
                      <input type="checkbox" :checked="hasGlobalRole(gRole.id)" @change="toggleGlobalRole(gRole.id)" class="mt-1 w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                      <div>
                        <div class="font-bold text-gray-900 text-sm">{{ gRole.name }}</div>
                      </div>
                    </label>
                  </div>
                </div>

                <!-- 2. Deacon Level -->
                <div class="bg-white p-5 rounded-2xl border border-gray-200 border-l-4 border-l-blue-600">
                  <h3 class="font-black text-sm text-blue-800 mb-4 uppercase tracking-wider flex items-center gap-2">
                    🕍 Ban Chấp Sự (Hội Thánh)
                  </h3>
                  
                  <label class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100 cursor-pointer mb-2 transition">
                    <input type="checkbox" :checked="hasChurchRole" @change="toggleChurchRole" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="font-bold text-gray-900 text-sm">Giao Quyền Ban Chấp Sự</span>
                  </label>

                  <div v-if="hasChurchRole" class="ml-8 mt-2 pl-4 border-l-2 border-gray-200">
                    <div class="text-xs text-gray-500 font-bold mb-1">Chức vụ phụ trách:</div>
                    <select 
                      :value="getChurchMembership()?.org_role_id"
                      @change="(e) => updateChurchRole(parseInt(e.target.value))"
                      class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 font-bold bg-white"
                    >
                      <option v-for="r in churchRolesOptions" :key="r.id" :value="r.id">{{ r.name }}</option>
                    </select>
                  </div>
                </div>

                <!-- 3. Activities Level -->
                <div class="bg-white p-5 rounded-2xl border border-gray-200 border-l-4 border-l-emerald-500">
                  <h3 class="font-black text-sm text-emerald-700 mb-4 uppercase tracking-wider flex items-center gap-2">
                    🏃 Ban Ngành Sinh Hoạt
                  </h3>
                  <div class="space-y-4">
                    <div v-for="dept in actDeptList" :key="dept.id">
                      <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" :checked="hasDepartment(dept.id)" @change="toggleDepartment(dept.id)" class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <span class="font-bold text-gray-900 text-sm">{{ dept.name }}</span>
                      </label>
                      <div v-if="hasDepartment(dept.id)" class="ml-8 mt-2 pl-4 border-l-2 border-gray-200">
                        <select 
                          :value="getDeptMembership(dept.id)?.org_role_id"
                          @change="(e) => updateDepartmentRole(dept.id, parseInt(e.target.value))"
                          class="text-sm border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 font-bold"
                        >
                          <option v-for="r in deptRolesOptions" :key="r.id" :value="r.id">{{ r.name }}</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 4. Ministry Level -->
                <div class="bg-white p-5 rounded-2xl border border-gray-200 border-l-4 border-l-purple-500">
                  <h3 class="font-black text-sm text-purple-700 mb-4 uppercase tracking-wider flex items-center gap-2">
                    🙏 Ban Ngành Mục Vụ
                  </h3>
                  <div class="space-y-4">
                    <div v-for="dept in minDeptList" :key="dept.id">
                      <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" :checked="hasDepartment(dept.id)" @change="toggleDepartment(dept.id)" class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <span class="font-bold text-gray-900 text-sm">{{ dept.name }}</span>
                      </label>
                      <div v-if="hasDepartment(dept.id)" class="ml-8 mt-2 pl-4 border-l-2 border-gray-200">
                        <select 
                          :value="getDeptMembership(dept.id)?.org_role_id"
                          @change="(e) => updateDepartmentRole(dept.id, parseInt(e.target.value))"
                          class="text-sm border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 font-bold"
                        >
                          <option v-for="r in deptRolesOptions" :key="r.id" :value="r.id">{{ r.name }}</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
            
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
