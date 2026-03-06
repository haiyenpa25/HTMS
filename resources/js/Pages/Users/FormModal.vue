<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  show:        { type: Boolean, default: false },
  roles:       { type: Array,   default: () => [] },
  editingUser: { type: Object,  default: null },
});

const emit = defineEmits(['close']);

const isEditing     = computed(() => !!props.editingUser);
const showPassword  = ref(false);
const activeSection = ref('info'); // 'info' | 'password'

const form = useForm({
  name:     '',
  email:    '',
  phone:    '',
  role:     '',
  password: '',
});

// Populate when editing user changes
watch(() => props.editingUser, (user) => {
  activeSection.value = 'info';
  showPassword.value  = false;
  if (user) {
    form.name     = user.name  ?? '';
    form.email    = user.email ?? '';
    form.phone    = user.phone ?? '';
    form.role     = (user.role === 'Guest' || !user.role) ? '' : user.role;
    form.password = '';
  } else {
    form.reset();
  }
  form.clearErrors();
}, { immediate: true });

watch(() => props.show, (v) => {
  if (v) activeSection.value = 'info';
});

const close = () => {
  form.reset();
  form.clearErrors();
  emit('close');
};

const submit = () => {
  if (isEditing.value) {
    form.put(route('users.update', props.editingUser.id), { onSuccess: close });
  } else {
    form.post(route('users.store'), { onSuccess: close });
  }
};

const roleColors = {
  Super_Admin: 'bg-red-50 text-red-700 border-red-200',
  Pastor:      'bg-purple-50 text-purple-700 border-purple-200',
};
</script>

<template>
  <!-- Overlay + Slide-Over Panel -->
  <teleport to="body">
    <transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0">
      <div v-if="show" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center px-4 py-6 sm:p-0" @click.self="close">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close"></div>

        <!-- Panel -->
        <transition
          enter-active-class="transition-all duration-300"
          enter-from-class="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
          enter-to-class="opacity-100 translate-y-0 sm:scale-100"
          leave-active-class="transition-all duration-200"
          leave-from-class="opacity-100 translate-y-0 sm:scale-100"
          leave-to-class="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95">
          <div v-if="show" class="relative z-10 w-full sm:max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">

            <!-- ── Header ── -->
            <div class="bg-indigo-700 px-6 py-5 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      :d="isEditing
                        ? 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'
                        : 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'" />
                  </svg>
                </div>
                <div>
                  <h2 class="text-white font-black text-lg leading-tight">
                    {{ isEditing ? 'Cập nhật Tài khoản' : 'Tạo Tài khoản Mới' }}
                  </h2>
                  <p class="text-indigo-200 text-xs">
                    {{ isEditing ? editingUser?.email : 'Điền đầy đủ thông tin để tạo mới' }}
                  </p>
                </div>
              </div>
              <button @click="close" class="text-indigo-200 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <!-- ── Tab Selector (Edit only) ── -->
            <div v-if="isEditing" class="flex border-b border-gray-100 bg-gray-50/80">
              <button @click="activeSection = 'info'"
                class="flex-1 py-3 text-sm font-bold transition-colors flex items-center justify-center gap-2"
                :class="activeSection === 'info' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-white' : 'text-gray-500 hover:text-gray-700'">
                👤 Thông tin
              </button>
              <button @click="activeSection = 'password'"
                class="flex-1 py-3 text-sm font-bold transition-colors flex items-center justify-center gap-2"
                :class="activeSection === 'password' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-white' : 'text-gray-500 hover:text-gray-700'">
                🔑 Đổi mật khẩu
              </button>
            </div>

            <!-- ── Form Body ── -->
            <form @submit.prevent="submit" class="p-6 space-y-4">

              <!-- SECTION: Info -->
              <div v-if="!isEditing || activeSection === 'info'" class="space-y-4">

                <!-- Name -->
                <div>
                  <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                    Họ và Tên <span class="text-red-500">*</span>
                  </label>
                  <input v-model="form.name" type="text" required
                    placeholder="Nguyễn Văn A"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all"
                    :class="{ 'border-red-400 ring-1 ring-red-400': form.errors.name }" />
                  <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                    Địa chỉ Email <span class="text-red-500">*</span>
                  </label>
                  <input v-model="form.email" type="email" required
                    placeholder="ten@httlthanhmyloi.com"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all"
                    :class="{ 'border-red-400 ring-1 ring-red-400': form.errors.email }" />
                  <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                </div>

                <!-- Phone -->
                <div>
                  <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Số điện thoại</label>
                  <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">📱</span>
                    <input v-model="form.phone" type="tel" placeholder="09xxxxxxxx"
                      class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" />
                  </div>
                  <p v-if="form.errors.phone" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
                </div>

                <!-- Role -->
                <div>
                  <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Chức vụ Toàn Cục</label>
                  <select v-model="form.role"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                    <option value="">— Chưa phân chức vụ (Guest) —</option>
                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                  </select>
                  <p class="mt-1.5 text-[11px] text-gray-400">
                    💡 Phân quyền chi tiết được cấu hình riêng trong tab Phân Quyền.
                  </p>
                </div>

                <!-- Password (Create only) -->
                <div v-if="!isEditing">
                  <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                    Mật khẩu <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                      required placeholder="Tối thiểu 8 ký tự"
                      class="w-full pl-4 pr-11 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all"
                      :class="{ 'border-red-400': form.errors.password }" />
                    <button type="button" @click="showPassword = !showPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                      <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                      </svg>
                    </button>
                  </div>
                  <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                </div>
              </div>

              <!-- SECTION: Change Password (Edit only) -->
              <div v-if="isEditing && activeSection === 'password'" class="space-y-4">
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex gap-2 text-xs text-amber-700">
                  <span>⚠️</span>
                  <p>Để trống nếu <strong>không muốn đổi mật khẩu</strong>. Mật khẩu mới phải từ 8 ký tự trở lên.</p>
                </div>
                <div>
                  <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Mật khẩu mới</label>
                  <div class="relative">
                    <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                      placeholder="Nhập mật khẩu mới..."
                      class="w-full pl-4 pr-11 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all"
                      :class="{ 'border-red-400': form.errors.password }" />
                    <button type="button" @click="showPassword = !showPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                      <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                      </svg>
                    </button>
                  </div>
                  <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                </div>
                <!-- Password strength indicator -->
                <div v-if="form.password.length > 0" class="space-y-1">
                  <div class="flex gap-1">
                    <div v-for="i in 4" :key="i" class="h-1 flex-1 rounded-full transition-colors"
                      :class="[
                        form.password.length >= i*3
                          ? (form.password.length >= 12 ? 'bg-emerald-500' : form.password.length >= 8 ? 'bg-amber-400' : 'bg-red-400')
                          : 'bg-gray-200'
                      ]"></div>
                  </div>
                  <p class="text-[11px]"
                    :class="form.password.length >= 12 ? 'text-emerald-600' : form.password.length >= 8 ? 'text-amber-600' : 'text-red-500'">
                    {{ form.password.length >= 12 ? '✅ Mạnh' : form.password.length >= 8 ? '⚡ Đủ điều kiện' : '⚠️ Quá ngắn' }}
                  </p>
                </div>
              </div>

              <!-- ── Actions ── -->
              <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" @click="close"
                  class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold text-sm hover:bg-gray-50 transition-colors">
                  Hủy
                </button>
                <button type="submit" :disabled="form.processing"
                  class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-200 transition-all disabled:opacity-60 flex items-center gap-2">
                  <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <span>{{ form.processing ? 'Đang lưu...' : (isEditing ? 'Lưu thay đổi' : 'Tạo tài khoản') }}</span>
                </button>
              </div>

            </form>
          </div>
        </transition>
      </div>
    </transition>
  </teleport>
</template>
