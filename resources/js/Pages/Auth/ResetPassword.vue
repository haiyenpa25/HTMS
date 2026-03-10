<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans relative overflow-hidden">
    <div class="absolute inset-0 z-0 opacity-40 mix-blend-multiply" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px;"></div>
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
      <div class="text-center">
        <h2 class="mt-6 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Đặt lại mật khẩu mới</h2>
      </div>

      <div class="mt-8 bg-white/90 py-8 px-6 shadow-2xl shadow-slate-200/50 rounded-2xl border border-white backdrop-blur-md">
        <form @submit.prevent="submit" class="space-y-6">
          
          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700">Email</label>
            <div class="mt-1">
              <input id="email" v-model="form.email" type="email" required readonly class="appearance-none block w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-500 cursor-not-allowed"/>
            </div>
            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
          </div>

          <div>
            <label for="password" class="block text-sm font-semibold text-slate-700">Mật khẩu mới</label>
            <div class="mt-1">
              <input id="password" v-model="form.password" type="password" required class="appearance-none block w-full px-4 py-3 border border-slate-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-transparent"/>
            </div>
            <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Xác nhận mật khẩu mới</label>
            <div class="mt-1">
              <input id="password_confirmation" v-model="form.password_confirmation" type="password" required class="appearance-none block w-full px-4 py-3 border border-slate-200 rounded-xl shadow-sm focus:ring-blue-500 focus:border-transparent"/>
            </div>
            <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
          </div>

          <div class="mt-4">
            <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-all">
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              Đặt lại mật khẩu
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
.font-sans {
  font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
}
</style>