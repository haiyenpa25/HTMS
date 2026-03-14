<template>
  <DocsLayout>
    <Head title="Docs - Cổng Lãnh Đạo" />
    <article class="prose prose-slate max-w-none">
      <h1 class="text-3xl font-black text-gray-900 mb-2">Hướng Dẫn: Cổng Lãnh Đạo (Leadership CRM)</h1>
      <p class="text-lg text-gray-500 mb-8">Nơi cung cấp cho Mục sư, Ban trị sự, Ban thư ký bức tranh toàn cảnh và các công cụ chăm sóc tín hữu chuyên sâu.</p>
      
      <!-- TOC -->
      <div class="bg-indigo-50/50 rounded-2xl p-6 mb-10 border border-indigo-100">
        <h3 class="font-bold text-indigo-900 mb-3 text-sm uppercase tracking-wider">Nội dung chính</h3>
        <ul class="space-y-2 text-sm text-indigo-700">
          <li><a href="#chamsoc" class="hover:underline">1. Chăm sóc Hệ Thống & Phiếu Yêu Cầu (Care Tickets)</a></li>
          <li><a href="#than-huu" class="hover:underline">2. Quản lý Thân Hữu (Visitors CRM)</a></li>
          <li><a href="#tai-san" class="hover:underline">3. Kho Quản lý Tài Sản & Mượn Trả</a></li>
        </ul>
      </div>

      <!-- Section 1 -->
      <h2 id="chamsoc" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">1</span> 
        Chăm sóc & Phiếu Yêu Cầu (Care Tickets)
      </h2>
      <p>Khi các tín hữu ở trên app điện thoại, họ có nút <strong>"Gửi Tờ trình Cầu Nguyện/Thăm Viếng"</strong>. Các tờ trình này sẽ bay thẳng vào màn hình Cổng Lãnh Đạo (Menu <code>Chăm Sóc & Hỗ Trợ</code>).</p>
      
      <div class="my-6 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <img src="/images/docs/leadership-care.png" alt="Ảnh minh hoạ Giao diện Care Tickets" class="w-full object-cover" onerror="this.onerror=null; this.outerHTML='<div class=\'aspect-video bg-gray-50 flex flex-col items-center justify-center p-6 text-center\'><svg class=\'w-12 h-12 text-gray-300 mb-2\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z\'></path></svg><span class=\'text-sm text-gray-500\'>Vui lòng chụp ảnh màn hình /care và lưu vào /public/images/docs/leadership-care.png</span></div>'">
      </div>

      <ul class="list-disc pl-5 mt-2 space-y-2 text-sm text-gray-700">
        <li><strong>Pending (Chờ xử lý):</strong> Ban Lãnh Đạo sẽ nhìn thấy nó đầu tiên.</li>
        <li><strong>Assigned (Phân công):</strong> Mục sư có thể chỉ định "Chấp sự A" hoặc "Trưởng ban B" đi lo việc thăm viếng gia đình đó. Trạng thái chuyển sang <code>In Progress</code>.</li>
        <li><strong>Resolved:</strong> Hoàn tất công việc chăm sóc.</li>
      </ul>

      <!-- Section 2 -->
      <h2 id="than-huu" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">2</span> 
        Quản lý Thân Hữu (Visitors CRM)
      </h2>
      <p>Một Thân hữu đến nhà thờ lần đầu sẽ được Ban Tiếp Tân hoặc Truyền Giáo ghi nhận thông tin (Tên, Số ĐT). Tính năng này hoạt động như một hệ thống CRM chuyên nghiệp:</p>
      
      <div class="my-6 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <img src="/images/docs/leadership-visitors.png" alt="Ảnh minh hoạ Giao diện CRM Thân Hữu" class="w-full object-cover" onerror="this.onerror=null; this.outerHTML='<div class=\'aspect-video bg-gray-50 flex flex-col items-center justify-center p-6 text-center\'><svg class=\'w-12 h-12 text-gray-300 mb-2\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z\'></path></svg><span class=\'text-sm text-gray-500\'>Vui lòng chụp ảnh màn hình /visitors và lưu vào /public/images/docs/leadership-visitors.png</span></div>'">
      </div>

      <div class="bg-gray-50 border border-t-[3px] border-t-amber-500 p-6 rounded-b-2xl mb-6">
        <p class="text-sm font-bold text-gray-900 mb-2">Quy Trình Nhật Ký Follow-up</p>
        <p class="text-sm text-gray-600 mb-4">Bạn không chỉ ghi tên người ta bỏ đó. Hệ thống bắt bạn phải <em>Lưu Lịch sử Liên Hệ</em>.</p>
        <ul class="list-decimal pl-5 text-sm text-gray-600 space-y-1">
          <li><strong>Lần 1:</strong> Ngày 02/03 - Gọi điện mời nhóm - Thân hữu bảo bận.</li>
          <li><strong>Lần 2:</strong> Ngày 09/03 - Nhắn tin Zalo - Thân hữu hứa sẽ đến.</li>
          <li><strong>Cột mốc mới:</strong> Chuyển status thân hữu thành <code>Believed (Đã Tin Chúa)</code> -> Lập tức tạo tài khoản Thành Viên (Member) bằng 1 cú click.</li>
        </ul>
      </div>

      <!-- Section 3 -->
      <h2 id="tai-san" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">3</span> 
        Kho Quản lý Tài Sản Cố Định (Assets)
      </h2>
      <p>Bộ phận Quản lý Cơ sở vật chất sử dụng Cổng lãnh đạo (Quyền SuperAdmin/AssetManager) để lưu kho Bàn ghế, Loa đài, Máy chiếu.</p>
      
      <div class="grid md:grid-cols-2 gap-4 mt-4">
        <div class="border border-emerald-200 rounded-xl p-4 shadow-sm bg-emerald-50/20">
          <h3 class="font-bold text-emerald-900 mb-2">Xuất Mượn Chuyên Xuyên</h3>
          <p class="text-sm text-gray-600">Khi Ban Thanh Niên cần mượn Loa Kéo đi dã ngoại, Thủ kho sẽ chọn <code>Loa kéo JBl</code> -> <strong>Tạo Phiếu Xuất Mượn</strong> -> Chọn Tên Trưởng Ban TN. Trạng thái loa lập tức thành <code>In_use</code>.</p>
        </div>
        <div class="border border-indigo-200 rounded-xl p-4 shadow-sm bg-indigo-50/20">
          <h3 class="font-bold text-indigo-900 mb-2">Trả Kho (Return/Lost)</h3>
          <p class="text-sm text-gray-600">Khi làm mất hoặc trả kho, thao tác được đánh dấu 1 click và cập nhật lịch sử ai là người mượn cuối cùng, ngày trả có khớp hạn không.</p>
        </div>
      </div>

    </article>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
