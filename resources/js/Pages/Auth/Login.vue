<template>
  <div class="min-h-screen bg-[#f8fafc] flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans relative overflow-hidden">
    <!-- Premium Gradient Background elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
      <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-indigo-400/20 blur-[120px] mix-blend-multiply"></div>
      <div class="absolute top-[20%] -right-[10%] w-[40%] h-[40%] rounded-full bg-emerald-400/20 blur-[100px] mix-blend-multiply"></div>
      <div class="absolute -bottom-[10%] left-[20%] w-[60%] h-[40%] rounded-full bg-purple-400/20 blur-[120px] mix-blend-multiply"></div>
      <!-- Subtle grid pattern -->
      <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#94a3b8 1px, transparent 1px); background-size: 24px 24px;"></div>
    </div>
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 transition-all">
      <div class="text-center group">
        <div class="relative inline-block">
          <div class="absolute inset-0 bg-indigo-500 blur-xl opacity-20 rounded-full group-hover:opacity-40 transition-opacity duration-500"></div>
          <img src="/LOGO.png" alt="CMS Logo" class="relative mx-auto h-24 sm:h-28 object-contain drop-shadow-xl group-hover:scale-105 transition-transform duration-500" />
        </div>
        <h1 class="mt-6 text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
          CMS <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent font-black">Hội Thánh</span>
        </h1>
        <p class="mt-3 text-sm font-medium text-slate-500">
          Nền tảng Quản trị & Điều hành Nội bộ
        </p>
      </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0 relative z-10">
      <!-- Premium Glassmorphism Card -->
      <div class="bg-white/80 py-8 px-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] sm:rounded-3xl border border-white/60 backdrop-blur-xl relative overflow-hidden">
        <!-- Shine effect -->
        <div class="absolute top-0 -inset-full h-full w-1/2 z-0 block transform -skew-x-12 bg-gradient-to-r from-transparent to-white opacity-20 animate-shine"></div>
        
        <form @submit.prevent="submit" class="space-y-6 relative z-10">
          <div>
            <label for="email" class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">
              Tên đăng nhập
            </label>
            <div class="mt-1 flex shadow-sm rounded-xl overflow-hidden border border-slate-200/80 bg-white/50 focus-within:ring-2 focus-within:ring-indigo-500/50 focus-within:border-indigo-500 transition-all duration-300 group">
              <input
                id="email"
                v-model="form.email"
                type="text"
                autocomplete="email"
                required
                placeholder="Ví dụ: superadmin"
                class="appearance-none block w-full px-4 py-3 border-0 bg-transparent focus:ring-0 placeholder-slate-400 text-slate-900 font-medium"
                :class="{ 'bg-red-50/50': form.errors.email }"
              />
              <div class="flex items-center px-4 bg-slate-100/50 border-l border-slate-200/80 text-slate-500 font-semibold text-sm select-none group-focus-within:bg-indigo-50/50 group-focus-within:text-indigo-600 group-focus-within:border-indigo-200 transition-colors">
                @{{ systemDomain }}
              </div>
            </div>
            <p v-if="form.errors.email" class="mt-2 text-xs font-bold text-red-500">
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <label for="password" class="block text-xs font-black uppercase tracking-wider text-slate-600 mb-2">
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
                class="appearance-none block w-full px-4 py-3 bg-white/50 border border-slate-200/80 rounded-xl shadow-sm font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all duration-300"
                :class="{ 'border-red-500 bg-red-50/50': form.errors.password }"
              />
            </div>
            <p v-if="form.errors.password" class="mt-2 text-xs font-bold text-red-500">
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
              <Link :href="route('password.request')" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                Quên mật khẩu?
              </Link>
            </div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-xl shadow-[0_4px_14px_0_rgba(79,70,229,0.39)] text-sm font-black text-white bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
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
              <span>{{ form.processing ? 'ĐANG XỬ LÝ...' : 'ĐĂNG NHẬP' }}</span>
              <svg v-if="!form.processing" class="w-5 h-5 ml-2 -mr-1 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
          </div>
        </form>

        <div class="mt-8 relative z-10">
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

    <!-- Installation Guide Link -->
    <div class="fixed bottom-4 right-4 z-20">
      <Link :href="route('help.install', { mode: 'theo-chuc-nang' })" class="flex items-center space-x-2 px-3 py-1.5 bg-slate-200/50 hover:bg-slate-200 text-slate-600 rounded-lg transition-colors text-xs font-bold backdrop-blur-sm border border-slate-300/30">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>Hướng dẫn cài đặt</span>
      </Link>
    </div>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
  systemDomain: String,
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
  domain: props.systemDomain,
});

const submit = () => {
  form.post(route('login.authenticate'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<style scoped>
/* Nạp font Inter nếu chưa có (Tùy chọn) */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.font-sans {
  font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
}

@keyframes shine {
  100% {
    transform: translateX(250%) skewX(-12deg);
  }
}
.animate-shine {
  animation: shine 4s infinite 2s;
}
</style>