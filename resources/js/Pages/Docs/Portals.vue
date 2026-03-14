<template>
  <DocsLayout>
    <Head title="Docs - Hệ Sinh Thái Cổng" />
    <article class="prose prose-slate max-w-none">
      <h1 class="text-3xl font-black text-gray-900 mb-2">Hướng Dẫn: Sinh Thái Cổng Hệ Thống (Portals)</h1>
      <p class="text-lg text-gray-500 mb-8">Tìm hiểu cách 4 cổng thông tin khác nhau (Lãnh đạo, Mục vụ, Ban ngành, Tín hữu) tương tác liên hoàn.</p>
      
      <!-- Section 1 -->
      <h2 id="tong-hop" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">1</span> 
        Menu "Chuyển Đổi Cổng" (Switch Portals)
      </h2>
      <p>Hệ thống có một Drop-down góc phải trên cùng màn hình gọi là `Portal Switcher`. Khi click vào, nếu bạn có đủ Quyền (Roles/Functions), bạn sẽ được chuyển đến Cổng tương ứng.</p>

      <div class="my-6 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <img src="/images/docs/portals-overview.png" alt="Ảnh minh hoạ Giao diện Cổng Portal" class="w-full object-cover" onerror="this.onerror=null; this.outerHTML='<div class=\'aspect-video bg-gray-50 flex flex-col items-center justify-center p-6 text-center\'><svg class=\'w-12 h-12 text-gray-300 mb-2\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10\'></path></svg><span class=\'text-sm text-gray-500\'>Vui lòng chụp ảnh màn hình /member-portal và lưu vào /public/images/docs/portals-overview.png</span></div>'">
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <!-- Card 1 -->
        <div class="border border-blue-200 rounded-2xl p-6 bg-blue-50/10">
          <h3 class="font-bold text-blue-900 text-lg mb-2">1. Cổng Mục Vụ (Ministry Portal)</h3>
          <p class="text-sm text-gray-700">Dành cho các ban chuyên biệt hỗ trợ Hội Thánh (Ban Âm Nhạc, Cầu Nguyện, CĐGD). <strong>Không bao giờ</strong> cung cấp tính năng Kế toán/Quỹ cho các ban này, vì họ nhận ngân sách ấn định từ Hội Thánh.</p>
        </div>
        <!-- Card 2 -->
        <div class="border border-emerald-200 rounded-2xl p-6 bg-emerald-50/10">
          <h3 class="font-bold text-emerald-900 text-lg mb-2">2. Cổng Sinh Hoạt Ban Ngành (Activities Portal)</h3>
          <p class="text-sm text-gray-700">Đây là các ban vận hành như "Vương quốc nhỏ" (Thanh Niên, Tráng Niên). Họ <strong>Cần có Module Tài Chính (Quỹ)</strong> riêng biệt, Điểm danh riêng biệt. Do vậy tính năng ở Cổng này là lớn nhất.</p>
        </div>
        <!-- Card 3 -->
        <div class="border border-indigo-200 rounded-2xl p-6 bg-indigo-50/10">
          <h3 class="font-bold text-indigo-900 text-lg mb-2">3. Cổng Thư Ký / Chấp Sự (Deacon Portal)</h3>
          <p class="text-sm text-gray-700">Tuy không phải là Lãnh đạo tối cao (Mục sư), Ban Chuyên Trách <strong>Chấp Sự</strong> có Cổng riêng. Tại đây họ lập <em>Biên bản Báo Cáo Chấp Sự Hàng Tháng</em>, và nộp các báo cáo <em>Sự Cố (Incidents/Cơ sở vật chất)</em>.</p>
        </div>
        <!-- Card 4 -->
        <div class="border border-rose-200 rounded-2xl p-6 bg-rose-50/10">
          <h3 class="font-bold text-rose-900 text-lg mb-2">4. Cổng Tín Hữu (Member App Mobile)</h3>
          <p class="text-sm text-gray-700">Tín hữu bình thường (Thành viên không mang chức danh) nhìn thấy một giao diện cực kỳ tối giản thiết kế theo Mobile-first. (Bấm vào Avatar -> Hồ sơ của tôi).</p>
          <p class="text-sm font-bold text-rose-600 mt-2 cursor-pointer"><em>Ứng dụng cho phép: Xem lịch sử Dâng phần mười cá nhân bí mật, Xem Lịch phân công của cá nhân, Nộp đơn cầu nguyện ẩn danh.</em></p>
        </div>
      </div>

    </article>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
