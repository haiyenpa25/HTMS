<template>
  <Head title="Bật/Tắt Module - Docs" />
  <DocsLayout>
    <div class="max-w-4xl mx-auto prose prose-indigo prose-slate">
      <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-6">Quản lý Bật/Tắt Tính Năng (Features)</h1>
      <p class="text-lg text-slate-600 mb-8 leading-relaxed">
        Hệ thống được thiết kế theo kiến trúc Module lõi rời. Quản trị viên có thể bật hoặc tắt từng module (ví dụ: Tài chính, Điểm danh) để chúng xuất hiện hoặc biến mất khỏi hệ thống ngay lập tức mà không cần code lại.
      </p>

      <div class="bg-orange-50 p-6 rounded-2xl border border-orange-100 mb-8">
          <h4 class="font-bold text-orange-800 mb-2">Cảnh báo</h4>
          <p class="text-sm text-orange-700 leading-relaxed mb-0">Việc Disable một Feature trong mục "Cài Đặt Hệ Thống > Tính Năng" sẽ ảnh hưởng đến toàn bộ các ban đang sử dụng tính năng đó. Hãy cẩn thận khi cấu hình Global Off!</p>
      </div>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
