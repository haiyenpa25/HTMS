<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans relative overflow-hidden">
    <!-- Background pattern -->
    <div class="absolute inset-0 z-0 opacity-40 mix-blend-multiply" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px;"></div>
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
      <div class="text-center">
        <img src="/LOGO.png" alt="CMS Logo" class="mx-auto h-24 sm:h-28 object-contain drop-shadow-md hover:scale-105 transition-transform duration-300" />
        <h2 class="mt-6 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Quên mật khẩu</h2>
        <p class="mt-2 text-sm text-slate-600">Nhập email của bạn để nhận liên kết đặt lại mật khẩu</p>
      </div>

      <div class="mt-8 bg-white/90 py-8 px-6 shadow-2xl shadow-slate-200/50 rounded-2xl border border-white backdrop-blur-md">
        
        <div v-if="status" class="mb-4 font-medium text-sm text-emerald-600 p-4 bg-emerald-50 rounded-xl border border-emerald-100 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700">Email đã đăng ký</label>
            <div class="mt-1 flex shadow-sm rounded-xl overflow-hidden border border-slate-200 focus-within:ring-2 focus-within:ring-blue-500 transition-all duration-200">
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                class="appearance-none block w-full px-4 py-3 border-0 focus:ring-0 placeholder-slate-400 text-slate-900"
                :class="{'bg-red-50': form.errors.email}"
              />
            </div>
            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>

          <div class="flex items-center justify-between mt-4">
            <Link :href="route('login')" class="text-sm font-bold text-slate-500 hover:text-blue-600 transition-colors">
              &larr; Quay lại đăng nhập
            </Link>

            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex justify-center py-2 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all disabled:opacity-50"
            >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              Gửi liên kết
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
.font-sans {
  font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
}
</style>