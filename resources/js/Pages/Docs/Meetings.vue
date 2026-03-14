<template>
  <DocsLayout>
    <Head title="Docs - Sự Kiện & Lịch Trình" />
    <article class="prose prose-slate max-w-none">
      <h1 class="text-3xl font-black text-gray-900 mb-2">Hướng Dẫn: Sự Kiện & Buổi Nhóm</h1>
      <p class="text-lg text-gray-500 mb-8">Nơi cung cấp cái nhìn tổng quan về tất cả các sinh hoạt của Hội Thánh theo thời gian.</p>
      
      <!-- TOC -->
      <div class="bg-indigo-50/50 rounded-2xl p-6 mb-10 border border-indigo-100">
        <h3 class="font-bold text-indigo-900 mb-3 text-sm uppercase tracking-wider">Nội dung chính</h3>
        <ul class="space-y-2 text-sm text-indigo-700">
          <li><a href="#lich-nam" class="hover:underline">1. Lịch Sự Kiện Hệ Thống</a></li>
          <li><a href="#tao-su-kien" class="hover:underline">2. Tạo Lịch Sinh Hoạt Mới</a></li>
          <li><a href="#lien-ket-phan-cong" class="hover:underline">3. Liên kết với Phân Công Công Việc</a></li>
        </ul>
      </div>

      <!-- Section 1 -->
      <h2 id="lich-nam" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">1</span> 
        Lịch Sự Kiện Hệ Thống (Master Calendar)
      </h2>
      <p>Khi truy cập <strong>Quản lý Lịch Nhóm</strong> (hoặc Dashboard Calendar ban đầu), bạn sẽ thấy Lịch tổng quan hiển thị giao diện tháng/tuần tiện lợi. Các sự kiện được mã hóa màu sắc để dễ phân biệt:</p>
      
      <ul class="list-none space-y-3 my-6 text-sm text-gray-700">
        <li><span class="inline-block w-4 h-4 bg-orange-500 rounded-sm mr-2 align-middle border border-orange-600"></span> <strong>Hội Thánh:</strong> Buổi nhóm chung (TD: Thờ Phượng Chúa Nhật). Mọi người đều có thể thấy chữ này.</li>
        <li><span class="inline-block w-4 h-4 bg-blue-500 rounded-sm mr-2 align-middle border border-blue-600"></span> <strong>Ban Tráng Niên / Thanh niên:</strong> Sự kiện nội bộ của từng ban. Chỉ hiển thị trong tài khoản người thuộc ban đó.</li>
        <li><span class="inline-block w-4 h-4 bg-gray-400 rounded-sm mr-2 align-middle border border-gray-500"></span> <strong>Công tác cá nhân:</strong> Ngày hẹn cá nhân.</li>
      </ul>

      <!-- Section 2 -->
      <h2 id="tao-su-kien" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">2</span> 
        Tạo Lịch Sinh Hoạt Mới
      </h2>
      
      <div class="grid md:grid-cols-2 gap-6 items-start mt-6">
        <div>
          <p class="text-sm">Để tạo lịch:</p>
          <ol class="list-decimal pl-5 space-y-2 text-sm text-gray-600">
            <li>Bấm vào ngày tương ứng ngay trên giao diện Lịch lớn.</li>
            <li><strong>Nhập Tiêu Đề:</strong> Ví dụ <code>Lễ Thương Khó</code>.</li>
            <li><strong>Chọn Loại:</strong> Rất quan trọng! Nếu chọn Nội bộ ban ngành, thì người ban khác không thể nhìn thấy sự kiện để đến.</li>
            <li><strong>Khung thời gian cụ thể:</strong> Nếu diễn ra cả ngày thì check vào ô vuông All Day.</li>
            <li>Nhấn <strong>Lưu mới</strong>. Lịch sẽ lập tức xuất hiện trong Cổng (Portal) của các quản trị viên để vào điểm danh.</li>
          </ol>
        </div>
        <div class="bg-blue-50 border border-blue-200 p-5 rounded-2xl">
          <h4 class="text-indigo-800 font-bold mb-2">Quyền Sở Hữu Lịch & Chỉnh Sửa</h4>
          <p class="text-xs text-indigo-900 leading-relaxed">Nếu bạn là <code>superadmin</code>, bạn có quyền <strong>Sửa/Xóa</strong> tất cả các events xuất hiện trên hệ thống. Tuy nhiên nếu bạn chỉ là <code>tk.thanhtrang</code> (Thư ký Thanh Tráng), bạn chỉ nhìn thấy (View-only) sự kiện của toàn Hội thánh, mà không được phép kéo thả, xóa chúng.</p>
        </div>
      </div>

      <!-- Section 3 -->
      <h2 id="lien-ket-phan-cong" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">3</span> 
        Liên kết Lịch với Phân Công Công Việc
      </h2>
      <p>Bất kỳ một sự kiện nào được tạo ra từ <em>Lịch Hệ Thống</em> đều sẽ xuất hiện trên màn hình quản lý trạng thái của <strong>Lịch Phân Công (Duty Roster)</strong>, ở ngay mục <em>Lượt Nhóm Sắp Tới</em>.</p>
      
      <div class="bg-orange-50 p-4 border-l-4 border-orange-500 text-sm italic text-orange-900 mt-2">
        <p class="m-0">Lưu ý: Mọi sự kiện dù là Ban ngành hay Toàn Hội thánh đều chia sẻ cùng cấu trúc dữ liệu tên là bảng <code>meetings</code>. Do vậy thay vì điều hướng lộn xộn, bạn vẫn thiết lập phân công trên cùng 1 tính năng "Duty Roster" - không cần tạo tính năng phân công lịch lềnh khềnh lại từ đầu trong từng Cổng Ban Ngành nhỏ.</p>
      </div>

    </article>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
