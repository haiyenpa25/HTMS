<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">
          CMS<span class="text-blue-600">HT</span>
        </h1>
        <h2 class="mt-6 text-2xl font-bold text-slate-800">
          Đăng nhập vào hệ thống
        </h2>
        <p class="mt-2 text-sm text-slate-500">
          Hệ thống quản lý dữ liệu Hội Thánh
        </p>
      </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
      <div class="bg-white py-8 px-6 shadow-xl rounded-2xl border border-slate-100 backdrop-blur-sm">
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700">
              Email
            </label>
            <div class="mt-1">
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                placeholder="email@example.com"
                class="appearance-none block w-full px-4 py-3 border border-slate-200 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                :class="{ 'border-red-500 bg-red-50': form.errors.email }"
              />
            </div>
            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <label for="password" class="block text-sm font-semibold text-slate-700">
              Mật khẩu
            </label>
            <div class="mt-1">
              <input
                id="password"
                v-model="form.password"
                type="password"
                autocomplete="current-password"
                required
                placeholder="••••••••"
                class="appearance-none block w-full px-4 py-3 border border-slate-200 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                :class="{ 'border-red-500 bg-red-50': form.errors.password }"
              />
            </div>
            <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
              {{ form.errors.password }}
            </p>
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember_me"
                v-model="form.remember"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded transition-all cursor-pointer"
              />
              <label for="remember_me" class="ml-2 block text-sm text-slate-600 cursor-pointer select-none">
                Ghi nhớ đăng nhập
              </label>
            </div>

            <div class="text-sm">
              <a href="#" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                Quên mật khẩu?
              </a>
            </div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg 
                v-if="form.processing" 
                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" 
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ form.processing ? 'Đang xử lý...' : 'Đăng nhập' }}
            </button>
          </div>
        </form>

        <div class="mt-8">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-slate-100"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-slate-400">
                Gặp khó khăn khi truy cập?
              </span>
            </div>
          </div>
          <p class="mt-4 text-center text-sm text-slate-500">
            Vui lòng liên hệ Thư ký hoặc Ban kỹ thuật để được hỗ trợ.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login.authenticate'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<style scoped>
/* Nạp font Inter nếu chưa có (Tùy chọn) */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.font-sans {
  font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
}
</style>
