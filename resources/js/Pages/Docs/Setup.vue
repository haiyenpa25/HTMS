<template>
  <DocsLayout>
    <div>
      <h1 class="text-2xl font-black text-gray-900 mb-6">Khởi tạo Dữ liệu ban đầu</h1>
      <p class="text-sm text-gray-600 mb-8 max-w-2xl">
        Hệ thống được thiết kế với tính năng tự động khởi tạo dữ liệu mẫu 
        (Seed Data) giúp bạn nhanh chóng thiết lập hệ thống từ rỗng sang đầy đủ cấu trúc ban ngành, quyền hạn và danh sách tài khoản cơ bản.
      </p>

      <div class="space-y-10">
          <!-- Step 1 -->
          <div>
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-2">
              <span class="bg-orange-100 text-orange-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span> 
              Chạy lệnh Seed Dữ Liệu
            </h3>
            <p class="mt-3 text-sm text-gray-600 leading-relaxed">
              Dữ liệu của các Ban Ngành, Tính Năng và các tài khoản mẫu đã được cấu hình sẵn trong mã nguồn. Để tạo lập lại toàn bộ cấu trúc gốc (bao gồm Ban Thanh Tráng), hãy chạy lệnh sau trong Terminal (thư mục gốc dự án):
            </p>
            <div class="mt-3 bg-gray-900 rounded-xl p-4 flex items-center justify-between group">
              <code class="text-green-400 font-mono text-sm">php artisan migrate:fresh --seed</code>
              <button @click="copyCommand" class="text-gray-400 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10" title="Copy">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
              </button>
            </div>
            <p class="mt-2 text-xs text-red-500 font-medium font-bold">⚠️ Lưu ý: Lệnh này sẽ xoá toàn bộ dữ liệu hiện tại và tạo lại từ đầu.</p>
          </div>

          <!-- Step 2 -->
          <div>
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-2">
              <span class="bg-orange-100 text-orange-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span> 
              Danh Sách Tài Khoản Mẫu
            </h3>
            <p class="mt-3 text-sm text-gray-600 mb-4">Sau khi seed, bạn có thể đăng nhập bằng các tài khoản sau (Mật khẩu chung: <code class="bg-gray-100 text-orange-600 px-2 py-0.5 rounded font-bold">Abc.1234</code>):</p>
            
            <div class="grid gap-4 md:grid-cols-2">
              <div class="border border-gray-200 rounded-2xl p-4 hover:border-orange-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center font-black">SA</div>
                  <div>
                    <h4 class="font-bold text-gray-900">Quản Trị Tối Cao</h4>
                    <p class="text-xs text-gray-500">Toàn quyền hệ thống</p>
                  </div>
                </div>
                <div class="bg-gray-50 p-2 rounded-lg text-sm text-gray-700 font-mono mt-3">superadmin@httlthanhmyloi.com</div>
              </div>

              <div class="border border-gray-200 rounded-2xl p-4 hover:border-orange-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-black">TB</div>
                  <div>
                    <h4 class="font-bold text-gray-900">Trưởng Ban (Thanh Tráng)</h4>
                    <p class="text-xs text-gray-500">Quyền quản lý ban ngành</p>
                  </div>
                </div>
                <div class="bg-gray-50 p-2 rounded-lg text-sm text-gray-700 font-mono mt-3">tb.thanhtrang@httlthanhmyloi.com</div>
              </div>

              <div class="border border-gray-200 rounded-2xl p-4 hover:border-orange-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center font-black">TK</div>
                  <div>
                    <h4 class="font-bold text-gray-900">Thư Ký (Thanh Tráng)</h4>
                    <p class="text-xs text-gray-500">Quyền điểm danh, báo cáo</p>
                  </div>
                </div>
                <div class="bg-gray-50 p-2 rounded-lg text-sm text-gray-700 font-mono mt-3">tk.thanhtrang@httlthanhmyloi.com</div>
              </div>
            </div>
          </div>

          <!-- Step 3 -->
          <div>
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-2">
              <span class="bg-orange-100 text-orange-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">3</span> 
              Cấu trúc Ban Ngành được khởi tạo
            </h3>
            <div class="mt-4 bg-gray-50 rounded-2xl p-5 border border-gray-100 text-sm text-gray-700">
              <ul class="space-y-4">
                <li class="flex items-start gap-3">
                  <span class="text-blue-500 mt-0.5">🔹</span>
                  <div>
                    <p class="font-bold text-gray-900">Khối Lãnh Đạo</p>
                    <p class="text-gray-500 text-xs mt-1">Ban Chấp Sự, Toàn Hội Thánh</p>
                  </div>
                </li>
                <li class="flex items-start gap-3">
                  <span class="text-emerald-500 mt-0.5">🔹</span>
                  <div>
                    <p class="font-bold text-gray-900">Khối Sinh Hoạt</p>
                    <p class="text-gray-500 text-xs mt-1">Ban Lão niên, Trung tráng niên, <strong>Ban Thanh Tráng</strong>, Ban Thanh niên...</p>
                  </div>
                </li>
                <li class="flex items-start gap-3">
                  <span class="text-purple-500 mt-0.5">🔹</span>
                  <div>
                    <p class="font-bold text-gray-900">Khối Mục Vụ (Nội Bộ)</p>
                    <p class="text-gray-500 text-xs mt-1">Ban Chứng Đạo, Ban Âm Nhạc, Ban Cầu Nguyện...</p>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <div class="pt-6">
            <a href="/login" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-black text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
              Đến Trang Đăng Nhập
            </a>
          </div>
      </div>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';

const copyCommand = () => {
    navigator.clipboard.writeText('php artisan migrate:fresh --seed');
    alert('Đã copy lệnh!');
};
</script>
