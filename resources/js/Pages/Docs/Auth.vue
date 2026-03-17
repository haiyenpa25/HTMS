<template>
  <Head title="Xác thực & Đăng nhập - Docs" />
  <DocsLayout>
    <div class="max-w-4xl mx-auto prose prose-indigo prose-slate">
      <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-6">Đăng nhập & Xác thực Tài khoản</h1>
      
      <p class="text-lg text-slate-600 mb-8 leading-relaxed">
        Hệ thống yêu cầu tất cả người dùng phải xác thực trước khi truy cập bất kỳ Portal nghiệp vụ nào nhằm đảm bảo tính bảo mật và định tuyến chức năng chính xác theo chức vụ.
      </p>

      <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 mb-8">
        <h3 class="text-indigo-800 font-bold mb-2">Quy Trình Cấp Tài Khoản</h3>
        <p class="text-sm text-indigo-700">Hiện tại hệ thống không mở đăng ký tự do (Public Registration). Các tài khoản mới sẽ do Ban Truyền Thông hoặc Thư ký thư tín tạo trong <strong>Admin Dashboard</strong> và cung cấp mật khẩu khởi tạo cho người dùng.</p>
      </div>

      <h2 class="text-2xl font-bold text-slate-800 mt-10 mb-4 border-b pb-2">Các Bước Đăng Nhập</h2>
      <ol class="space-y-4">
          <li class="flex gap-4">
              <span class="w-8 h-8 shrink-0 bg-slate-100 rounded-full flex items-center justify-center font-bold text-slate-500">1</span>
              <div>
                  <strong class="text-slate-800 block">Truy cập trang chủ</strong>
                  <span class="text-slate-600 text-sm">Vào đường dẫn rễ của hệ thống hoặc /login.</span>
              </div>
          </li>
          <li class="flex gap-4">
              <span class="w-8 h-8 shrink-0 bg-slate-100 rounded-full flex items-center justify-center font-bold text-slate-500">2</span>
              <div>
                  <strong class="text-slate-800 block">Nhập thông tin</strong>
                  <span class="text-slate-600 text-sm">Sử dụng Email (ví dụ: ms.quantnhiem@httlthanhmyloi.com) và Mật khẩu được cấp.</span>
              </div>
          </li>
          <li class="flex gap-4">
              <span class="w-8 h-8 shrink-0 bg-indigo-100 rounded-full flex items-center justify-center font-bold text-indigo-600">3</span>
              <div>
                  <strong class="text-slate-800 block">Tự động định tuyến (Smart Routing)</strong>
                  <span class="text-slate-600 text-sm">Hệ thống sẽ tự động quét quyền hạn của bạn để quyết định chuyển bạn vào Cổng Thành Viên, Cổng Lãnh Đạo hay Cổng Quản trị mà không cần bạn chọn.</span>
              </div>
          </li>
      </ol>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
