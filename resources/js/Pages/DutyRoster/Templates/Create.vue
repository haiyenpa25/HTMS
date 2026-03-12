<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DutyRosterLayout from '@/Layouts/DutyRosterLayout.vue';

const props = defineProps({ departments: Array, defaultType: String, defaultDeptId: Number });

const name = ref('');
const type = ref(props.defaultType || 'church');
const department_id = ref(props.defaultDeptId || null);
const saving = ref(false);

const submit = () => {
  if (!name.value.trim()) return;
  router.post(route('duty-rooster.templates.store'), { 
    name: name.value,
    type: type.value,
    department_id: department_id.value
  });
};
</script>

<template>
  <DutyRosterLayout title="Tạo Mẫu Mới">
    <Head title="Tạo Mẫu Mới" />
    <div class="max-w-lg mx-auto px-6 py-16">
      <Link :href="route('duty-rooster.templates.index')" class="text-xs text-gray-400 hover:text-gray-700 flex items-center gap-1 mb-6">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Quay lại
      </Link>

      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center mb-6">
          <svg class="w-7 h-7 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
        </div>
        <h1 class="text-2xl font-black text-gray-900 mb-2">Tạo Mẫu Mới</h1>
        <p class="text-sm text-gray-400 mb-8">Sau khi tạo, bạn sẽ chọn các ban và thêm vai trò cho từng ban.</p>

        <div class="mb-4">
          <label class="block text-xs font-black text-gray-600 uppercase tracking-wider mb-2">Tên mẫu phân công</label>
          <input v-model="name" type="text"
            placeholder="VD: Lễ Thờ Phượng Chúa Nhật, Nhóm Ban Ngành..."
            class="w-full text-sm rounded-xl border-gray-200 focus:ring-orange-400 focus:border-orange-400"
            @keyup.enter="submit" />
        </div>

        <div class="mb-6 grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-black text-gray-600 uppercase tracking-wider mb-2">Loại mẫu</label>
                <select v-model="type" class="w-full text-sm rounded-xl border-gray-200 focus:ring-orange-400 focus:border-orange-400" :disabled="props.defaultDeptId !== null">
                    <option value="church">Hội Thánh</option>
                    <option value="department">Ban Ngành</option>
                </select>
            </div>
            <div v-if="type === 'department'">
                <label class="block text-xs font-black text-gray-600 uppercase tracking-wider mb-2">Ban Ngành</label>
                <select v-model="department_id" class="w-full text-sm rounded-xl border-gray-200 focus:ring-orange-400 focus:border-orange-400" :disabled="props.defaultDeptId !== null">
                    <option :value="null">-- Chọn Ban --</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
            </div>
        </div>

        <button @click="submit" :disabled="!name.trim() || (type === 'department' && !department_id)"
          class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-black text-sm rounded-xl transition-all shadow-sm disabled:opacity-40">
          Tạo &amp; Thiết lập vị trí →
        </button>
      </div>
    </div>
  </DutyRosterLayout>
</template>