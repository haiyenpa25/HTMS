<template>
  <Head title="Cơ cấu Ban Ngành - Docs" />
  <DocsLayout>
    <div class="max-w-4xl mx-auto prose prose-indigo prose-slate">
      <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-6">Ban Ngành Cơ Bản</h1>
      <p class="text-lg text-slate-600 mb-8 leading-relaxed">
        Hội Thánh có nhiều ban và tổ chức. Để hệ thống có thể cấp phát tính năng (Features) đúng cho từng ban, chúng tôi chia ban ngành thành 3 khối (Blocks) chính yếu.
      </p>

      <ul class="space-y-6 list-none pl-0">
          <li class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
              <h3 class="flex items-center gap-2 text-xl font-bold text-slate-800 mt-0 mb-3">
                  <span class="bg-blue-100 text-blue-600 p-2 rounded-lg text-sm">Activities Block</span>
                  Sinh Hoạt Độ Tuổi (Cộng đồng)
              </h3>
              <p class="text-slate-600 text-sm mb-4">Các ban được tổ chức theo lứa tuổi. Mục đích chính là sinh hoạt nội bộ, bồi linh và thu chi quỹ sự kiện.</p>
              <div class="flex flex-wrap gap-2">
                  <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-medium text-slate-700">Thiếu Nhi</span>
                  <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-medium text-slate-700">Thanh Thiếu Niên</span>
                  <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-medium text-slate-700">Thanh Niên</span>
                  <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-medium text-slate-700">Tráng Niên</span>
                  <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-medium text-slate-700">Phụ Nữ</span>
              </div>
          </li>

          <li class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
              <h3 class="flex items-center gap-2 text-xl font-bold text-slate-800 mt-0 mb-3">
                  <span class="bg-purple-100 text-purple-600 p-2 rounded-lg text-sm">Ministry Block</span>
                  Phục Vụ / Chuyên Trách (Mục vụ)
              </h3>
              <p class="text-slate-600 text-sm mb-4">Các ban tổ chức theo kỹ năng âm nhạc, chăm sóc, y tế, phục vụ hệ sinh thái chung.</p>
              <div class="flex flex-wrap gap-2">
                  <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-medium text-slate-700">Ban Thăm Viếng</span>
                  <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-medium text-slate-700">Ban Cầu Nguyện</span>
                  <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-medium text-slate-700">Các Ca Đoành</span>
                  <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-medium text-slate-700">Ban Âm Nhạc / Kỹ Thuật</span>
              </div>
          </li>

          <li class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
              <h3 class="flex items-center gap-2 text-xl font-bold text-slate-800 mt-0 mb-3">
                  <span class="bg-red-100 text-red-600 p-2 rounded-lg text-sm">Leadership Block</span>
                  Lãnh Đạo / Quản Trị
              </h3>
              <p class="text-slate-600 text-sm mb-4">Các vị trí có thẩm quyền trên toàn bộ dữ liệu Hội Thánh phục vụ điều hành.</p>
              <div class="flex flex-wrap gap-2">
                  <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-medium text-slate-700">Mục Sư Quản Nhiệm</span>
                  <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-medium text-slate-700">Thư Ký & Tài Phủ</span>
                  <span class="bg-slate-100 px-3 py-1 rounded-full text-xs font-medium text-slate-700">Ban Chấp Sự</span>
              </div>
          </li>
      </ul>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
