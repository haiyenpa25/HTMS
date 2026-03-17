<template>
  <Head title="Quản lý Người Dùng - Docs" />
  <DocsLayout>
    <div class="max-w-4xl mx-auto prose prose-indigo prose-slate">
      <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-6">Quản lý Dữ liệu Người Dùng</h1>
      <p class="text-lg text-slate-600 mb-8 leading-relaxed">
        Module Người dùng (Users Management) nằm trong khu vực System Admin là nơi Quản trị viên cấp cao chịu trách nhiệm quản trị vòng đời tài khoản toàn hệ thống.
      </p>

      <div class="grid md:grid-cols-2 gap-6 mb-10">
          <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
              <h3 class="font-bold text-slate-800 text-lg mb-2">Tạo mới Tài khoản</h3>
              <p class="text-sm text-slate-600">Thêm mới thông tin cá nhân của người dùng, cấp Username/Email và cấp quyền đăng nhập (Access Portals).</p>
          </div>
          <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
              <h3 class="font-bold text-slate-800 text-lg mb-2">Đăng nhập mạo danh</h3>
              <p class="text-sm text-slate-600">Với quyền `SuperAdmin`, bạn có thể bấm nút "Impersonate" để đăng nhập dưới góc nhìn của một User bất kỳ để Troubleshoot xử lý sự cố.</p>
          </div>
      </div>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
