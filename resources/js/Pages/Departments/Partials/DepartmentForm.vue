<template>
  <form @submit.prevent="submit" class="flex flex-col h-full bg-white relative">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-10 shrink-0">
      <div>
        <h2 class="text-xl font-black text-gray-900">{{ isEditing ? 'Chỉnh sửa Ban Ngành' : 'Thêm Ban Ngành Mới' }}</h2>
        <p class="text-xs text-gray-500 font-medium mt-1">
          {{ isEditing ? 'Cập nhật thông tin chi tiết của ban' : 'Điền thông tin cơ bản để tạo ban ngành' }}
        </p>
      </div>
      <button type="button" @click="$emit('close')" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-full transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>

    <!-- Body -->
    <div class="flex-1 overflow-y-auto p-6 space-y-6">
      
      <!-- Name -->
      <div class="space-y-2">
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wide">Tên ban ngành <span class="text-red-500">*</span></label>
        <input 
          v-model="form.name" 
          type="text" 
          required
          class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 placeholder:text-gray-400"
          placeholder="VD: Ban Thanh Niên"
        />
        <div v-if="form.errors.name" class="text-xs text-red-500 font-medium flex items-center mt-1">
            <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ form.errors.name }}
        </div>
      </div>

      <!-- Code -->
      <div class="space-y-2">
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wide">Mã ban (Tùy chọn)</label>
        <input 
          v-model="form.code" 
          type="text" 
          class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 placeholder:text-gray-400"
          placeholder="VD: BTN"
        />
        <div v-if="form.errors.code" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.code }}</div>
      </div>

      <!-- Block/Type -->
      <div class="space-y-2">
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wide">Phân loại <span class="text-red-500">*</span></label>
        <select 
          v-model="form.block" 
          required
          class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3"
        >
          <option value="leadership">Lãnh đạo</option>
          <option value="ministry">Mục vụ</option>
          <option value="activities">Sinh hoạt</option>
        </select>
        <div v-if="form.errors.block" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.block }}</div>
      </div>

      <!-- Active Toggle -->
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
        <div>
           <label class="text-sm font-bold text-gray-900">Trạng thái Hoạt động</label>
           <p class="text-xs text-gray-500 mt-0.5">Ban đang sinh hoạt hoặc tạm ngưng</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer">
          <input type="checkbox" v-model="form.is_active" class="sr-only peer">
          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
        </label>
      </div>

      <!-- Description -->
      <div class="space-y-2">
        <label class="text-xs font-bold text-gray-700 uppercase tracking-wide">Mô tả thêm</label>
        <textarea 
          v-model="form.description" 
          rows="3" 
          class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 placeholder:text-gray-400"
          placeholder="Giới thiệu ban..."
        ></textarea>
        <div v-if="form.errors.description" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.description }}</div>
      </div>
      
    </div>

    <!-- Footer -->
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0 space-y-3">
      <div class="flex justify-end space-x-3 w-full">
         <button 
           type="button" 
           @click="$emit('close')"
           class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 text-sm font-bold transition-all"
         >
           Hủy bỏ
         </button>
         <button 
           type="submit" 
           :disabled="form.processing"
           class="px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-bold shadow-md shadow-blue-200 transition-all flex items-center justify-center disabled:opacity-50 min-w-[120px]"
         >
           <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
             <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
             <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
           </svg>
           {{ isEditing ? 'Lưu cập nhật' : 'Tạo mới' }}
         </button>
      </div>
      
      <!-- Delete Action inside Edit mode only -->
      <div v-if="isEditing" class="pt-4 mt-2 border-t border-red-100 flex justify-center w-full">
         <button 
           type="button"
           @click="confirmDelete"
           class="text-xs font-bold text-red-600 hover:text-red-800 hover:underline px-4 py-2 rounded-lg hover:bg-red-50 transition-colors"
         >
           Xóa vĩnh viễn Ban Ngành này
         </button>
      </div>
    </div>
  </form>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
  department: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'success']);

const isEditing = computed(() => !!props.department);

const form = useForm({
  name: '',
  code: '',
  block: 'ministry',
  description: '',
  is_active: true,
});

// Watch input department to fill the form in Edit mode
watch(() => props.department, (newVal) => {
  if (newVal) {
    form.name = newVal.name || '';
    form.code = newVal.code || '';
    form.block = newVal.block || 'ministry';
    form.description = newVal.description || '';
    form.is_active = newVal.is_active ?? true;
  } else {
    form.reset();
  }
}, { immediate: true });

const submit = () => {
  if (isEditing.value) {
    form.put(route('departments.update', props.department.id), {
      onSuccess: () => {
        emit('success');
        emit('close');
      }
    });
  } else {
    form.post(route('departments.store'), {
      onSuccess: () => {
        emit('success');
        emit('close');
      }
    });
  }
};

const confirmDelete = () => {
  if (confirm(`Bạn có chắc chắn muốn xóa ban: ${props.department?.name}? Hành động này không thể hoàn tác!`)) {
    router.delete(route('departments.destroy', props.department.id), {
      onSuccess: () => {
        emit('success');
        emit('close');
      }
    });
  }
}
</script>
