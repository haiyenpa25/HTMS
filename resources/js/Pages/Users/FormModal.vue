<template>
  <div v-show="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      
      <!-- Background overlay -->
      <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-show="show" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" @click="close"></div>
      </transition>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <!-- Modal panel -->
      <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to-class="opacity-100 translate-y-0 sm:scale-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100 translate-y-0 sm:scale-100" leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        <div v-show="show" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="flex items-center justify-between mb-5 pb-4 border-b border-gray-100">
             <h3 class="text-xl font-black text-gray-900 leading-6" id="modal-title">
               {{ isEditing ? 'Cập nhật Người dùng' : 'Tạo mới Tài khoản' }}
             </h3>
             <button @click="close" class="text-gray-400 hover:text-gray-500 focus:outline-none">
               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
             </button>
          </div>
          
          <form @submit.prevent="submit">
            <div class="space-y-5">
              
              <!-- Tên hiển thị -->
              <div>
                <label for="name" class="block text-sm font-bold text-gray-700">Tên hiển thị <span class="text-red-500">*</span></label>
                <div class="mt-1">
                  <input type="text" id="name" v-model="form.name" required class="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Nguyễn Văn A">
                </div>
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
              </div>

              <!-- Email truy cập -->
              <div>
                <label for="email" class="block text-sm font-bold text-gray-700">Địa chỉ Email <span class="text-red-500">*</span></label>
                <div class="mt-1">
                  <input type="email" id="email" v-model="form.email" required class="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="email@example.com">
                </div>
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
              </div>

              <!-- Điện thoại -->
              <div>
                <label for="phone" class="block text-sm font-bold text-gray-700">Số Điện thoại</label>
                <div class="mt-1">
                  <input type="text" id="phone" v-model="form.phone" class="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="09xxxxxxxx">
                </div>
                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
              </div>

              <!-- Phân quyền (Role) -->
              <div>
                <label for="role" class="block text-sm font-bold text-gray-700">Vai trò chung Toàn Cục (Role)</label>
                <div class="mt-1">
                  <select id="role" v-model="form.role" class="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                    <option value="">-- Guest (Không phần quyền) --</option>
                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                  </select>
                </div>
                <p class="mt-1.5 text-xs text-gray-500">Người dùng sẽ cần được thiết lập phân quyền mở rộng sâu hơn thông qua trang Cài đặt Phân Quyền.</p>
                <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
              </div>

              <!-- Mật khẩu -->
              <div>
                <label for="password" class="block text-sm font-bold text-gray-700">Mật khẩu <span v-if="!isEditing" class="text-red-500">*</span></label>
                <div class="mt-1 relative rounded-md shadow-sm">
                  <input :type="showPassword ? 'text' : 'password'" id="password" v-model="form.password" :required="!isEditing" class="block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" :placeholder="isEditing ? 'Để trống nếu không muốn đổi mk' : 'Mật khẩu > 8 ký tự'">
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <button type="button" @click="showPassword = !showPassword" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                      <svg v-if="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                      <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                    </button>
                  </div>
                </div>
                <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
              </div>

            </div>
            
            <div class="mt-8 flex justify-end space-x-3">
              <button type="button" @click="close" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-bold hover:bg-gray-50 focus:outline-none transition-colors">
                Hủy bỏ
              </button>
              <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-blue-600 border border-transparent rounded-xl text-white font-bold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-colors">
                {{ form.processing ? 'Đang lưu...' : (isEditing ? 'Lưu thay đổi' : 'Tạo mới') }}
              </button>
            </div>
          </form>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  show: Boolean,
  roles: Array,
  editingUser: Object,
});

const emit = defineEmits(['close']);

const showPassword = ref(false);

const isEditing = computed(() => !!props.editingUser);

const form = useForm({
  name: '',
  email: '',
  phone: '',
  role: '',
  password: '',
});

// Watch for changes in editingUser to populate the form
watch(() => props.editingUser, (user) => {
  if (user) {
    form.name = user.name;
    form.email = user.email;
    form.phone = user.phone || '';
    form.role = user.role === 'Guest' ? '' : user.role;
    form.password = '';
  } else {
    form.reset();
  }
  form.clearErrors();
}, { immediate: true });

const close = () => {
  form.reset();
  form.clearErrors();
  emit('close');
};

const submit = () => {
  if (isEditing.value) {
    form.put(route('users.update', props.editingUser.id), {
      onSuccess: () => close(),
    });
  } else {
    form.post(route('users.store'), {
      onSuccess: () => close(),
    });
  }
};
</script>
