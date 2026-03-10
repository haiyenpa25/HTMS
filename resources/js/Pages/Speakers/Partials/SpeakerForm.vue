<template>
  <form @submit.prevent="submit" class="flex flex-col h-full bg-white relative">
    <div class="flex-1 overflow-y-auto p-6 relative">
      <div class="space-y-6">
        
        <!-- Toggle: Is External -->
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100/50">
          <div>
            <h3 class="text-sm font-bold text-gray-900">Khách mời bên ngoài</h3>
            <p class="text-xs text-gray-500 mt-0.5">Bật nếu diễn giả không thuộc Hội thánh</p>
          </div>
          <button 
            type="button"
            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2"
            :class="form.is_external ? 'bg-blue-600' : 'bg-gray-200'"
            @click="form.is_external = !form.is_external"
          >
            <span class="sr-only">Toggle Khách mời</span>
            <span 
              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
              :class="form.is_external ? 'translate-x-5' : 'translate-x-0'"
            ></span>
          </button>
        </div>

        <!-- Title -->
        <div class="space-y-2">
          <label class="text-sm font-black text-gray-900">Chức danh</label>
          <input 
            v-model="form.title" 
            type="text" 
            class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 placeholder:text-gray-400 bg-gray-50"
            placeholder="VD: Mục sư, Truyền đạo, Chấp sự..."
          />
          <div v-if="form.errors.title" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.title }}</div>
        </div>

        <!-- Full Name -->
        <div class="space-y-2">
          <label class="text-sm font-black text-gray-900">Họ và tên diễn giả <span class="text-red-500">*</span></label>
          <input 
            v-model="form.full_name" 
            type="text" 
            class="w-full text-base border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 placeholder:text-gray-400 font-bold"
            placeholder="Nhập họ tên đầy đủ"
            required
          />
          <div v-if="form.errors.full_name" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.full_name }}</div>
        </div>

        <!-- Phone -->
        <div class="space-y-2">
          <label class="text-sm font-black text-gray-900">Số điện thoại liên hệ</label>
          <input 
            v-model="form.phone" 
            type="tel" 
            class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 placeholder:text-gray-400 bg-gray-50"
            placeholder="VD: 0912345678"
          />
          <div v-if="form.errors.phone" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.phone }}</div>
        </div>

        <!-- Birth Year -->
        <div class="space-y-2">
          <label class="text-sm font-black text-gray-900">Năm sinh</label>
          <input 
            v-model="form.birth_year" 
            type="number" 
            min="1900"
            :max="new Date().getFullYear()"
            class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 placeholder:text-gray-400 bg-gray-50"
            placeholder="VD: 1980"
          />
          <div v-if="form.errors.birth_year" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.birth_year }}</div>
        </div>

        <!-- Managed Church (Only if External) -->
        <div v-if="form.is_external" class="space-y-2 animate-in fade-in slide-in-from-top-2">
          <label class="text-sm font-black text-gray-900">Hội Thánh / Tổ chức trực thuộc</label>
          <input 
            v-model="form.managed_church" 
            type="text" 
            class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 placeholder:text-gray-400 bg-gray-50"
            placeholder="VD: Hội Thánh Tin Lành abc"
          />
          <div v-if="form.errors.managed_church" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.managed_church }}</div>
        </div>
        
      </div>
    </div>

    <!-- Footer Action Buttons -->
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0 space-y-3">
      <div class="flex items-center justify-between w-full">
         <button 
           type="button" 
           @click="$emit('close')"
           class="px-5 py-3 text-gray-500 font-bold hover:bg-gray-200 rounded-xl transition-colors text-sm"
         >
           Hủy bỏ
         </button>

         <button 
           type="submit" 
           :disabled="form.processing"
           class="px-8 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-black shadow-lg shadow-blue-200 transition-all flex items-center justify-center disabled:opacity-50 min-w-[140px]"
         >
           <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
             <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
             <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
           </svg>
           {{ isEditing ? 'Lưu thay đổi' : 'Hoàn tất & Thêm' }}
         </button>
      </div>
      
      <!-- Delete Action inside Edit mode only -->
      <div v-if="isEditing" class="pt-4 mt-2 border-t border-red-100 flex justify-center w-full">
         <button 
           type="button"
           @click="confirmDelete"
           class="text-xs font-bold text-red-600 hover:text-red-800 hover:underline px-4 py-2 rounded-lg hover:bg-red-50 transition-colors"
         >
           Xóa diễn giả này
         </button>
      </div>
    </div>
  </form>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
  speaker: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'success']);

const isEditing = computed(() => !!props.speaker);

const form = useForm({
  title: '',
  full_name: '',
  phone: '',
  birth_year: '',
  managed_church: '',
  is_external: false,
  member_id: null
});

// Watch input speaker to fill the form in Edit mode
watch(() => props.speaker, (newVal) => {
  if (newVal) {
    form.title = newVal.title || '';
    form.full_name = newVal.full_name || '';
    form.phone = newVal.phone || '';
    form.birth_year = newVal.birth_year || '';
    form.managed_church = newVal.managed_church || '';
    form.is_external = newVal.is_external || false;
    form.member_id = newVal.member_id || null;
  } else {
    form.reset();
    form.clearErrors();
  }
}, { immediate: true });

const submit = () => {
  if (isEditing.value) {
    form.put(route('speakers.update', props.speaker.id), {
      onSuccess: () => {
        emit('success');
        emit('close');
      }
    });
  } else {
    form.post(route('speakers.store'), {
      onSuccess: () => {
        emit('success');
        emit('close');
      }
    });
  }
};

const confirmDelete = () => {
  if (confirm(`Bạn có chắc chắn muốn xóa diễn giả này? Hành động này không thể hoàn tác!`)) {
    router.delete(route('speakers.destroy', props.speaker.id), {
      onSuccess: () => {
        emit('success');
        emit('close');
      }
    });
  }
}
</script>