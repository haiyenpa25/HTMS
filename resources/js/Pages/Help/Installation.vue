<template>
  <Head title="Hướng Dẫn Cài Đặt" />
  
  <HelpLayout>
      <div class="p-8 md:p-12 prose prose-slate max-w-none">
        <h1 class="text-3xl font-black mb-6 text-slate-800 border-b border-slate-100 pb-4">Hướng Dẫn Cài Đặt Hệ Thống</h1>
        
        <!-- Navigation Tabs -->
        <div class="flex border-b border-slate-100 bg-slate-50/50 overflow-x-auto no-scrollbar mb-8 rounded-xl">
          <button v-for="tab in tabs" :key="tab.id" 
            @click="activeTab = tab.id"
            :class="[
              'px-6 py-4 text-sm font-bold whitespace-nowrap transition-all border-b-2',
              activeTab === tab.id ? 'border-indigo-600 text-indigo-600 bg-white' : 'border-transparent text-slate-500 hover:text-indigo-500'
            ]"
          >
            {{ tab.name }}
          </button>
        </div>

        <!-- Tab 1: Yêu Cầu -->
        <div v-if="activeTab === 'prerequisites'" class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-500">
          <h2 class="text-2xl font-black text-slate-800 flex items-center">
            <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mr-3 shrink-0">1</span>
            Yêu Cầu Hệ Thống
          </h2>
          <p>Để vận hành mã nguồn này ổn định, máy chủ hoặc máy tính cá nhân của bạn cần có:</p>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
              <h4 class="font-bold text-slate-700 mb-2">Backend & Server</h4>
              <ul class="text-sm space-y-2 list-none p-0">
                <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> PHP >= 8.2 (Bắt buộc)</li>
                <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> MySQL 8.0 / MariaDB</li>
                <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> Composer</li>
              </ul>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
              <h4 class="font-bold text-slate-700 mb-2">Frontend & Tools</h4>
              <ul class="text-sm space-y-2 list-none p-0">
                <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> Node.js >= 18</li>
                <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> Git</li>
                <li class="flex items-center"><svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> VS Code / Cursor AI</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Tab 2: Quy trình cài đặt -->
        <div v-if="activeTab === 'setup'" class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500 mt-6">
          <h2 class="text-2xl font-black text-slate-800 flex items-center">
            <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3 shrink-0">2</span>
            Quy Trình Triển Khai
          </h2>
          
          <section class="space-y-4">
            <h3 class="font-bold text-lg text-slate-700">Bước 1: Clone & Cài đặt thư viện</h3>
            <div class="bg-indigo-950 rounded-2xl p-6 text-indigo-50 font-mono text-sm shadow-inner relative group">
              <button @click="copyCode('clone')" class="absolute top-4 right-4 p-2 bg-white/10 rounded-lg hover:bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
              </button>
              <code class="block whitespace-pre">git clone [URL_GITHUB] cms_new
cd cms_new
composer install
npm install</code>
            </div>
          </section>

          <section class="space-y-4">
            <h3 class="font-bold text-lg text-slate-700">Bước 2: Cấu hình ENV (Quan trọng nhất)</h3>
            <p>Mở file <code class="bg-slate-100 px-1 rounded">.env</code> và điều chỉnh các thông số sau để hệ thống tự động sinh dữ liệu đúng domain:</p>
            <div class="bg-indigo-950 rounded-2xl p-6 text-indigo-50 font-mono text-sm shadow-inner overflow-x-auto">
              <code class="block whitespace-pre text-emerald-400"># --- THÔNG TIN HỘI THÁNH MỚI ---
CHURCH_NAME="Hội Thánh Tin Lành [Tên]"
SYSTEM_DOMAIN="tenmienmoi.com"
CHURCH_EMAIL="contact@tenmienmoi.com"
CHURCH_ADDRESS="Địa chỉ Hội Thánh"
CHURCH_PHONE="0123456789"</code>
            </div>
          </section>
        </div>

        <!-- Tab 3: Khởi tạo DB -->
        <div v-if="activeTab === 'database'" class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-500 mt-6">
          <h2 class="text-2xl font-black text-slate-800 flex items-center">
            <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mr-3 shrink-0">3</span>
            Khởi Tạo Dữ Liệu
          </h2>
          <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-2xl mb-6">
            <p class="text-sm text-blue-800 font-medium">
              <strong>Mẹo:</strong> Tôi đã thiết kế một Seeder duy nhất để làm sạch DB và dựng khung sườn hoàn chỉnh.
            </p>
          </div>
          
          <section class="space-y-4">
            <h3 class="font-bold text-slate-700">1. Tạo bảng Database</h3>
            <div class="bg-slate-900 rounded-xl p-4 text-slate-100 font-mono text-xs">php artisan migrate</div>
          </section>

          <section class="space-y-4">
            <h3 class="font-bold text-slate-700">2. Dựng "Khung sườn" (Skeleton)</h3>
            <div class="bg-slate-900 rounded-xl p-4 text-slate-100 font-mono text-xs mb-2">php artisan db:seed --class=SystemSkeletonSeeder</div>
            <p class="text-sm text-slate-500 italic">Lệnh này tạo: Ban ngành, User SuperAdmin (password: Abc.1234), Phân quyền feature...</p>
          </section>
        </div>

        <!-- Tab 4: Logo -->
        <div v-if="activeTab === 'logo'" class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-500 mt-6">
          <h2 class="text-2xl font-black text-slate-800 flex items-center">
            <span class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center mr-3 shrink-0">4</span>
            Thay Đổi Logo
          </h2>
          <p>Để thay đổi logo của Hội Thánh, bạn thực hiện 2 bước đơn giản:</p>
          <ol class="space-y-6">
            <li class="flex gap-4">
              <div class="w-6 h-6 rounded-full bg-rose-500 text-white flex-shrink-0 flex items-center justify-center text-xs font-bold mt-1">1</div>
              <div>
                <h4 class="font-bold text-slate-700">Tải ảnh lên thư mục public</h4>
                <p class="text-sm text-slate-500">Chép file logo (JPG/PNG) vào thư mục <code class="bg-slate-100 px-1 rounded">public/images/logo.png</code></p>
              </div>
            </li>
            <li class="gap-4 flex">
              <div class="w-6 h-6 rounded-full bg-rose-500 text-white flex-shrink-0 flex items-center justify-center text-xs font-bold mt-1">2</div>
              <div>
                <h4 class="font-bold text-slate-700">Chỉnh sửa PortalLayout.vue</h4>
                <p class="text-sm text-slate-500 mb-2">Mở file <code class="bg-slate-100 px-1 rounded">resources/js/Layouts/PortalLayout.vue</code> và tìm đoạn dòng 12-14. Thay thế bằng:</p>
                <div class="bg-slate-900 rounded-xl p-4 text-slate-100 font-mono text-xs">
                  &lt;img src="/images/logo.png" class="w-10 h-10 object-contain" alt="Logo"&gt;
                </div>
              </div>
            </li>
          </ol>
        </div>

      </div>
  </HelpLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import HelpLayout from '@/Layouts/HelpLayout.vue';

const activeTab = ref('prerequisites');

const tabs = [
  { id: 'prerequisites', name: 'Yêu cầu' },
  { id: 'setup', name: 'Triển khai' },
  { id: 'database', name: 'Dữ liệu (Seeder)' },
  { id: 'logo', name: 'Logo' },
];

const copyCode = (type) => {
  alert('Đã chọn đoạn mã. Hãy chép vào Terminal của bạn!');
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>