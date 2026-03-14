<template>
  <DocsLayout>
    <Head title="Docs - Quản lý Nhân sự" />
    <article class="prose prose-slate max-w-none">
      <h1 class="text-3xl font-black text-gray-900 mb-2">Hướng Dẫn: Quản Lý Nhân Sự</h1>
      <p class="text-lg text-gray-500 mb-8">Trang được thiết kế cho Mục Sư, Chấp Sự, và Trưởng Ban để truy cập toàn bộ hồ sơ Tín Hữu trong Hội Thánh.</p>
      
      <!-- TOC -->
      <div class="bg-indigo-50/50 rounded-2xl p-6 mb-10 border border-indigo-100">
        <h3 class="font-bold text-indigo-900 mb-3 text-sm uppercase tracking-wider">Nội dung chính</h3>
        <ul class="space-y-2 text-sm text-indigo-700">
          <li><a href="#them-tin-huu" class="hover:underline">1. Thêm Tín Hữu và Tạo Tài Khoản (Dành cho Quản trị viên)</a></li>
          <li><a href="#ho-gia-dinh" class="hover:underline">2. Quản lý Hộ Gia Đình (Household)</a></li>
          <li><a href="#xuat-danh-sach" class="hover:underline">3. Tìm kiếm & Xuất Excel </a></li>
        </ul>
      </div>

      <!-- Section 1 -->
      <h2 id="them-tin-huu" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">1</span> 
        Thêm Tín Hữu và Tạo Tài Khoản
      </h2>
      <p>Từ bảng điều khiển (Dashboard), bạn có thể thêm một tín hữu mới bằng cách bấm vào <strong>+ Thêm tín hữu</strong> ở menu <strong>Quản lý Thành Viên</strong>.</p>
      <ul class="list-disc pl-5 mt-2 space-y-2">
        <li><strong>Thông tin cơ bản:</strong> Điền tên, ngày sinh, giới tính, trạng thái thành viên (Khách, Hướng Đạo, Báp-têm...).</li>
        <li><strong>Tự động tạo tài khoản:</strong> Khi bạn nhập email vào ô "Email" (ví dụ: `nguyenvana@gmail.com`), hệ thống tự động sinh một tài khoản đăng nhập tương ứng cho tín hữu này. 
          <br><span class="text-sm bg-gray-100 px-2 py-1 rounded">Cảnh báo: Mật khẩu mặc định là <code>Abc.1234</code>. Hãy nhắc tín hữu đổi mật khẩu khi đăng nhập lần đầu!</span>
        </li>
      </ul>

      <!-- Section 2 -->
      <h2 id="ho-gia-dinh" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">2</span> 
        Quản lý Hộ Gia Đình (Household)
      </h2>
      <p>Hệ thống cho phép liên kết những tín hữu có quan hệ gia đình thành một cấu trúc gọi là <strong>Hộ Gia Đình</strong>. Điều này có lợi cho việc In Danh Sách Thăm Viếng theo từng gia đình.</p>
      
      <div class="grid md:grid-cols-2 gap-4 mt-4">
        <div class="border border-gray-200 rounded-xl p-4 shadow-sm bg-white">
          <h3 class="font-bold text-gray-900 mb-2 text-sm uppercase tracking-widest text-emerald-600">Cách 1: Thiết lập Chủ Hộ</h3>
          <p class="text-sm text-gray-600">Truy cập hồ sơ của Người Chồng/Người Cha (hoặc người đứng tên gia đình). Phía trên bên phải mục <strong>Gia Đình</strong>, tick chọn <em>"Phân công làm Chủ Hộ"</em>.</p>
        </div>
        <div class="border border-gray-200 rounded-xl p-4 shadow-sm bg-white">
          <h3 class="font-bold text-gray-900 mb-2 text-sm uppercase tracking-widest text-indigo-600">Cách 2: Gắn thành viên</h3>
          <p class="text-sm text-gray-600">Sau khi đã có người làm Chủ Hộ. Bạn bấm vào nút <strong>+ Thêm quan hệ</strong> và tìm tên người mẹ/người con, sau đó chọn loại quan hệ (Vợ, Con Trai, Con Gái...). Cấu trúc gia đình sẽ tự động hình thành.</p>
        </div>
      </div>

      <!-- Section 3 -->
      <h2 id="xuat-danh-sach" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">3</span> 
        Tìm kiếm & Xuất Excel
      </h2>
      
      <p>Một tính năng cốt lõi là Bộ Lọc tìm kiếm tín hữu.</p>
      <div class="bg-gray-50 border border-gray-200 p-6 rounded-2xl my-4">
        <p class="text-sm text-gray-600 mb-4">Bạn có thể lọc tất cả danh sách thành viên đang thuộc <em>(Ban Thanh Tiếu Niên)</em> hoặc đã nhận phép Báp-tem ở ô tìm kiếm dọc theo thanh công cụ. Sau khi đã chắt lọc danh sách như ý muốn, bạn có thể thực hiện xuất Excel.</p>
        <p class="text-sm text-gray-600 mb-0 font-bold">Nút "Lưu file CSV (Excel)" nằm ở phía trên bên phải của Bảng Dữ Liệu.</p>
      </div>

    </article>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
