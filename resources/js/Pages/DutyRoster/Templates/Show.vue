<script setup>
import { ref, computed, reactive, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DutyRosterLayout from '@/Layouts/DutyRosterLayout.vue';
import axios from 'axios';

const props = defineProps({
  template:             Object,
  departments:          Array,
  participatingDeptIds: Array,
});

// ── Template state ─────────────────────────────────────────
const templateName   = ref(props.template.name);
const saving         = ref(false);
const savingMsg      = ref(null);

// ── Role IDs already in this template ─────────────────────
const templateRoleIds = ref(new Set(props.template.roles?.map(r => r.department_role_id) || []));

// Roles in this template (live set)
const inTemplate = (id) => templateRoleIds.value.has(id);

// ── Section I: get all roles with section='Chương Trình Lễ' ────
const sectionIRoles = computed(() => {
  const roles = [];
  props.departments.forEach(dept => {
    (dept.duty_roles || []).forEach(role => {
      if (role.section === 'Chương Trình Lễ') {
        roles.push({ ...role, department: dept });
      }
    });
  });
  return roles.sort((a, b) => a.sort_order - b.sort_order);
});

// ── Section II: depts with roles NOT in 'Chương Trình Lễ' ─
const supportDepts = computed(() =>
  props.departments.filter(d =>
    (d.duty_roles || []).some(r => r.section !== 'Chương Trình Lễ')
  )
);
const supportRoles = (dept) =>
  (dept.duty_roles || []).filter(r => r.section !== 'Chương Trình Lễ');

// ── Selected support depts (initially from participatingDeptIds) ────
const selectedSupportDeptIds = ref([...props.participatingDeptIds]);
const isDeptSelected = (id) => selectedSupportDeptIds.value.includes(id);
const toggleDept = (id) => {
  if (isDeptSelected(id)) {
    selectedSupportDeptIds.value = selectedSupportDeptIds.value.filter(x => x !== id);
  } else {
    selectedSupportDeptIds.value.push(id);
  }
};
const activeSupportDepts = computed(() =>
  supportDepts.value.filter(d => selectedSupportDeptIds.value.includes(d.id))
);

// Accordion state for support depts
const collapsed = ref({});
const toggleCollapse = (id) => { collapsed.value[id] = !collapsed.value[id]; };

// ── Add role inline state ──────────────────────────────────
// For section I: add role to Ban Chấp Sự
const newSectionIRole = reactive({ name: '', max_count: 1, show: false });

// For section II depts
const newDeptRole = reactive({}); // deptId -> { name, max_count, show }
const initDeptRole = (id) => {
  if (!newDeptRole[id]) newDeptRole[id] = { name: '', max_count: 1, show: false };
};

// ── Toggle role in template ────────────────────────────────
const toggleRole = async (roleId) => {
  saving.value = true;
  try {
    if (inTemplate(roleId)) {
      await axios.delete(route('duty-rooster.templates.roles.remove', {
        template: props.template.id, role: roleId,
      }));
      templateRoleIds.value.delete(roleId);
    } else {
      await axios.post(route('duty-rooster.templates.roles.add', props.template.id), {
        role_id: roleId,
      });
      templateRoleIds.value.add(roleId);
    }
    // Force reactivity
    templateRoleIds.value = new Set(templateRoleIds.value);
  } catch (e) { /* noop */ }
  saving.value = false;
};

// ── Add new role to Ban Chấp Sự (Section I) ───────────────
const chapSuDept = computed(() =>
  props.departments.find(d => d.name.includes('Chấp'))
);

const addSectionIRole = async () => {
  if (!newSectionIRole.name.trim() || !chapSuDept.value) return;
  saving.value = true;
  try {
    const res = await axios.post(route('duty-rooster.roles.store', chapSuDept.value.id), {
      name: newSectionIRole.name.trim(),
      section: 'Chương Trình Lễ',
      max_count: newSectionIRole.max_count,
    });
    // Also add to template
    await axios.post(route('duty-rooster.templates.roles.add', props.template.id), {
      role_id: res.data.id,
    });
    newSectionIRole.name = ''; newSectionIRole.max_count = 1; newSectionIRole.show = false;
    router.reload({ only: ['template', 'departments'] });
  } catch (e) { /* noop */ }
  saving.value = false;
};

// ── Add new role to a support department ──────────────────
const addSupportRole = async (dept) => {
  const d = newDeptRole[dept.id];
  if (!d?.name?.trim()) return;
  saving.value = true;
  try {
    const res = await axios.post(route('duty-rooster.roles.store', dept.id), {
      name: d.name.trim(),
      section: dept.name,
      max_count: d.max_count || 1,
    });
    await axios.post(route('duty-rooster.templates.roles.add', props.template.id), { role_id: res.data.id });
    d.name = ''; d.max_count = 1; d.show = false;
    router.reload({ only: ['template', 'departments'] });
  } catch (e) { /* noop */ }
  saving.value = false;
};

// ── Save template name ─────────────────────────────────────
const saveName = async () => {
  if (!templateName.value.trim()) return;
  await axios.put(route('duty-rooster.templates.update', props.template.id), {
    name: templateName.value,
  });
  savingMsg.value = '✓ Đã lưu'; setTimeout(() => savingMsg.value = null, 2000);
};
</script>

<template>
  <DutyRosterLayout title="Mẫu Phân Công">
    <Head :title="`Mẫu: ${template.name}`" />

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 pb-16">

      <!-- ── Breadcrumb ─────────────────────────────── -->
      <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-4">
        <Link :href="route('duty-rooster.templates.index')" class="hover:text-indigo-600">Templates</Link>
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        <span class="text-gray-600">Chi tiết</span>
      </div>

      <!-- ── Title + Actions ────────────────────────── -->
      <div class="flex items-start justify-between gap-4 mb-8">
        <div class="flex-1">
          <input v-model="templateName"
            class="text-3xl font-black text-gray-900 border-0 border-b-2 border-transparent focus:border-indigo-400 focus:ring-0 bg-transparent p-0 w-full leading-tight"
            @blur="saveName" @keyup.enter="saveName" placeholder="Tên mẫu phân công..." />
          <div class="flex items-center gap-2 mt-1.5">
            <p class="text-xs text-gray-400">Thiết lập các vị trí phục vụ cố định cho buổi lễ.</p>
            <transition name="fade">
              <span v-if="savingMsg" class="text-[10px] text-emerald-600 font-bold">{{ savingMsg }}</span>
            </transition>
          </div>
        </div>
        <div class="flex gap-2 shrink-0">
          <Link :href="route('duty-rooster.index')"
            class="px-4 py-2.5 text-sm font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition-all">
            Áp dụng cho buổi nhóm →
          </Link>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════
           SECTION I — CHƯƠNG TRÌNH LỄ
           ══════════════════════════════════════════════ -->
      <div class="mb-8">
        <!-- Section header -->
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-2xl bg-orange-500 text-white flex items-center justify-center text-sm font-black shadow-sm">I</div>
            <div>
              <h2 class="text-base font-black text-gray-900 uppercase tracking-wide">Chương Trình Lễ</h2>
              <p class="text-[11px] text-gray-400">Các vị trí trong chương trình thờ phượng chính · Click để thêm/bỏ vào mẫu</p>
            </div>
          </div>
          <span class="text-xs text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full font-bold">
            {{ sectionIRoles.filter(r => inTemplate(r.id)).length }}/{{ sectionIRoles.length }} trong mẫu
          </span>
        </div>

        <!-- Roles grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-3">
          <div v-for="role in sectionIRoles" :key="role.id"
            @click="toggleRole(role.id)"
            class="group relative flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all select-none"
            :class="inTemplate(role.id)
              ? 'bg-orange-50 border-orange-300 shadow-sm'
              : 'bg-white border-gray-200 hover:border-orange-200 hover:bg-orange-50/30'">

            <!-- Checkbox circle -->
            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5 transition-all"
              :class="inTemplate(role.id) ? 'bg-orange-500' : 'bg-gray-200 group-hover:bg-orange-200'">
              <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path v-if="inTemplate(role.id)" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
              </svg>
            </div>

            <!-- Role info -->
            <div class="flex-1 min-w-0">
              <p class="font-bold text-sm leading-tight" :class="inTemplate(role.id) ? 'text-orange-900' : 'text-gray-700'">
                {{ role.name }}
              </p>
              <div class="flex items-center gap-1.5 mt-1">
                <span v-if="role.max_count > 1"
                  class="text-[10px] font-black px-1.5 py-0.5 rounded-full"
                  :class="inTemplate(role.id) ? 'bg-orange-200 text-orange-700' : 'bg-gray-100 text-gray-500'">
                  × {{ role.max_count }} người
                </span>
                <span v-if="role.section" class="text-[10px] text-gray-400">{{ role.section }}</span>
              </div>
            </div>

            <!-- In template badge -->
            <div v-if="inTemplate(role.id)" class="shrink-0">
              <span class="text-[10px] font-black text-orange-600 bg-orange-100 px-2 py-0.5 rounded-full">Đã thêm</span>
            </div>
          </div>

          <!-- Add new Section I role card -->
          <div class="border-2 border-dashed border-gray-200 rounded-2xl p-4 transition-all"
            :class="newSectionIRole.show ? 'border-orange-300 bg-orange-50/30' : 'hover:border-orange-200'">

            <div v-if="!newSectionIRole.show">
              <button @click="newSectionIRole.show = true"
                class="w-full flex items-center justify-center gap-2 text-sm text-gray-400 hover:text-orange-500 font-bold transition-colors py-3">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Thêm vai trò mới
              </button>
            </div>

            <div v-else class="space-y-2.5">
              <p class="text-[10px] font-black text-orange-600 uppercase tracking-wider">Thêm vai trò Chương Trình Lễ</p>
              <input v-model="newSectionIRole.name" type="text" placeholder="VD: Đọc kinh thánh, Cầu nguyện..."
                class="w-full text-sm rounded-xl border-gray-200 focus:ring-orange-400 focus:border-orange-400" />
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-600 shrink-0">Số người:</span>
                <div class="flex items-center gap-1">
                  <button @click="newSectionIRole.max_count = Math.max(1, newSectionIRole.max_count-1)"
                    class="w-6 h-6 rounded-lg bg-gray-100 text-gray-600 font-black text-sm flex items-center justify-center hover:bg-gray-200">−</button>
                  <span class="text-sm font-bold text-gray-800 w-4 text-center">{{ newSectionIRole.max_count }}</span>
                  <button @click="newSectionIRole.max_count = Math.min(10, newSectionIRole.max_count+1)"
                    class="w-6 h-6 rounded-lg bg-gray-100 text-gray-600 font-black text-sm flex items-center justify-center hover:bg-gray-200">+</button>
                </div>
              </div>
              <div class="flex gap-2">
                <button @click="newSectionIRole.show=false; newSectionIRole.name=''"
                  class="flex-1 py-2 text-xs font-bold text-gray-500 bg-gray-100 rounded-xl hover:bg-gray-200">Hủy</button>
                <button @click="addSectionIRole" :disabled="!newSectionIRole.name.trim() || saving"
                  class="flex-1 py-2 text-xs font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-xl disabled:opacity-40">+ Thêm vào mẫu</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════
           SECTION II — BAN HỖ TRỢ
           ══════════════════════════════════════════════ -->
      <div>
        <div class="flex items-center gap-3 mb-4">
          <div class="w-9 h-9 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-sm font-black shadow-sm">II</div>
          <div>
            <h2 class="text-base font-black text-gray-900 uppercase tracking-wide">Ban Hỗ Trợ</h2>
            <p class="text-[11px] text-gray-400">Chọn các ban tham gia → Tick vai trò cần có trong mẫu</p>
          </div>
        </div>

        <!-- Dept chip selector -->
        <div class="bg-gray-50 rounded-2xl border border-gray-100 p-4 mb-4">
          <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-3">Chọn ban tham gia trong chương trình lễ này:</p>
          <div class="flex flex-wrap gap-2">
            <button v-for="dept in supportDepts" :key="dept.id"
              @click="toggleDept(dept.id); initDeptRole(dept.id)"
              class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition-all border"
              :class="isDeptSelected(dept.id)
                ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300 hover:text-indigo-600'">
              <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path v-if="isDeptSelected(dept.id)" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
              </svg>
              {{ dept.name }}
            </button>
          </div>
        </div>

        <!-- Empty state -->
        <div v-if="activeSupportDepts.length === 0"
          class="text-center py-10 bg-white rounded-2xl border-2 border-dashed border-gray-200 text-gray-400 text-sm">
          Chưa chọn ban nào — tick vào các ban ở trên để thiết lập vai trò
        </div>

        <!-- Accordion per selected dept -->
        <div v-else class="space-y-2">
          <div v-for="dept in activeSupportDepts" :key="dept.id"
            class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-visible">

            <!-- Dept accordion header -->
            <button @click="toggleCollapse(dept.id)"
              class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-gray-50/60 transition-colors">
              <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200"
                :class="!collapsed[dept.id] ? 'rotate-90 text-indigo-500' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
              </svg>
              <span class="font-black text-gray-800 text-sm flex-1">{{ dept.name }}</span>
              <!-- Role tag pills -->
              <div class="hidden sm:flex gap-1 flex-wrap max-w-xs">
                <span v-for="role in supportRoles(dept).slice(0,4)" :key="role.id"
                  class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-full"
                  :class="inTemplate(role.id) ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400'">
                  {{ role.name.length > 8 ? role.name.slice(0,8)+'…' : role.name }}
                </span>
              </div>
              <span class="font-black text-xs ml-2"
                :class="supportRoles(dept).filter(r=>inTemplate(r.id)).length > 0 ? 'text-indigo-600' : 'text-gray-400'">
                {{ supportRoles(dept).filter(r=>inTemplate(r.id)).length }} vai trò
              </span>
            </button>

            <!-- Dept roles (when expanded) -->
            <div v-if="!collapsed[dept.id]" class="border-t border-gray-100 px-5 pb-5 pt-4 bg-gray-50/30 rounded-b-2xl">
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 mb-3">
                <div v-for="role in supportRoles(dept)" :key="role.id"
                  @click="toggleRole(role.id)"
                  class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition-all select-none"
                  :class="inTemplate(role.id)
                    ? 'bg-indigo-50 border-indigo-200 shadow-sm'
                    : 'bg-white border-gray-200 hover:border-indigo-200 hover:bg-indigo-50/30'">
                  <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 transition-all"
                    :class="inTemplate(role.id) ? 'bg-indigo-500' : 'bg-gray-200'">
                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                      <path v-if="inTemplate(role.id)" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      <path v-else stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold truncate" :class="inTemplate(role.id) ? 'text-indigo-800' : 'text-gray-700'">
                      {{ role.name }}
                    </p>
                    <p v-if="role.max_count > 1" class="text-[9px] text-gray-400">×{{ role.max_count }} người</p>
                  </div>
                </div>
              </div>

              <!-- Add new role to this dept -->
              <div class="mt-3" @click.stop>
                <div v-if="!newDeptRole[dept.id]?.show">
                  <button @click="initDeptRole(dept.id); newDeptRole[dept.id].show = true"
                    class="flex items-center gap-1.5 text-xs font-bold text-gray-400 hover:text-indigo-500 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Thêm vai trò mới cho {{ dept.name }}
                  </button>
                </div>
                <div v-else class="flex items-end gap-2 p-3 bg-white rounded-xl border border-indigo-200">
                  <div class="flex-1">
                    <input v-model="newDeptRole[dept.id].name" type="text" placeholder="Tên vai trò..."
                      class="w-full text-xs rounded-lg border-gray-200 focus:ring-indigo-400 focus:border-indigo-400 mb-1.5"
                      @keyup.enter="addSupportRole(dept)" />
                    <div class="flex items-center gap-1.5">
                      <span class="text-[10px] text-gray-500">Số người:</span>
                      <button @click="newDeptRole[dept.id].max_count = Math.max(1,(newDeptRole[dept.id].max_count||1)-1)"
                        class="w-5 h-5 rounded bg-gray-100 text-xs font-black flex items-center justify-center">−</button>
                      <span class="text-xs font-bold w-3 text-center">{{ newDeptRole[dept.id]?.max_count||1 }}</span>
                      <button @click="newDeptRole[dept.id].max_count = Math.min(10,(newDeptRole[dept.id].max_count||1)+1)"
                        class="w-5 h-5 rounded bg-gray-100 text-xs font-black flex items-center justify-center">+</button>
                    </div>
                  </div>
                  <div class="flex gap-1.5 shrink-0">
                    <button @click="newDeptRole[dept.id].show=false; newDeptRole[dept.id].name=''"
                      class="px-2.5 py-1.5 text-xs font-bold text-gray-500 bg-gray-100 rounded-lg">Hủy</button>
                    <button @click="addSupportRole(dept)"
                      :disabled="!newDeptRole[dept.id]?.name?.trim() || saving"
                      class="px-2.5 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg disabled:opacity-40">
                      + Thêm
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DutyRosterLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity .3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
