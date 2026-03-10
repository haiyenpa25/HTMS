<template>
  <Head title="Hướng Dẫn Cài Đặt" />
  
  <div class="min-h-screen bg-slate-50 font-sans text-slate-900 pb-20">
    <!-- Top Header -->
    <header class="bg-indigo-700 text-white py-12 px-6 shadow-lg relative overflow-hidden">
      <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-indigo-900/30 rounded-full blur-2xl"></div>
      
      <div class="max-w-4xl mx-auto relative z-10">
        <Link href="/" class="inline-flex items-center text-indigo-100 hover:text-white mb-6 transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
          Quay lại Trang chủ
        </Link>
        <h1 class="text-4xl md:text-5xl font-black tracking-tight mb-4">Hướng Dẫn Cài Đặt Hệ Thống</h1>
        <p class="text-xl text-indigo-100 max-w-2xl leading-relaxed">
          Tài liệu chi tiết giúp bạn triển khai mã nguồn này cho một Hội Thánh mới chỉ trong vài phút.
        </p>
      </div>
    </header>

    <main class="max-w-4xl mx-auto -mt-10 px-6 relative z-10">
      <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
        <!-- Navigation Tabs -->
        <div class="flex border-b border-slate-100 bg-slate-50/50 overflow-x-auto no-scrollbar">
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

        <!-- Content Area -->
        <div class="p-8 md:p-12 prose prose-slate max-w-none">
          
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
          <div v-if="activeTab === 'setup'" class="space-y-8 animate-in fade-in slide-in-from-bottom-2 duration-500">
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
          <div v-if="activeTab === 'database'" class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-500">
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
          <div v-if="activeTab === 'logo'" class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-500">
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

        <!-- Footer -->
        <div class="bg-slate-50 border-t border-slate-100 p-8 flex flex-col md:flex-row items-center justify-between gap-4">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-black">HT</div>
            <div>
              <p class="text-sm font-bold text-slate-800">CMS Hội Thánh - Version 2.0</p>
              <p class="text-xs text-slate-500">Hệ thống quản lý nội bộ chuyên nghiệp</p>
            </div>
          </div>
          <a href="/login" class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
            Vào Trang Đăng Nhập
          </a>
        </div>
      </div>
    </main>

    <!-- Floating Help Button -->
    <div class="fixed bottom-8 right-8">
      <div class="bg-white p-4 rounded-2xl shadow-2xl border border-slate-100 flex items-center space-x-3 max-w-xs animate-bounce cursor-help hover:animate-none">
        <div class="w-10 h-10 bg-emerald-500 text-white rounded-xl flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.443 1.103m-3.13 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM12 18h.01"></path></svg> 
        </div>
        <p class="text-xs font-medium text-slate-600">Bạn cần hỗ trợ thêm? <br/><span class="font-bold text-indigo-600">Liên hệ Antigravity AI</span></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const activeTab = ref('prerequisites');

const tabs = [
  { id: 'prerequisites', name: '1. Yêu cầu' },
  { id: 'setup', name: '2. Triển khai' },
  { id: 'database', name: '3. Dữ liệu (Seeder)' },
  { id: 'logo', name: '4. Logo & TK' },
];

const copyCode = (type) => {
  // Logic to copy to clipboard if needed
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