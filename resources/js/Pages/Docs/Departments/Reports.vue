<template>
  <Head title="Hướng dẫn: Báo Cáo Gộp Hàng Tháng" />

  <DocsLayout>
    <div class="max-w-4xl mx-auto prose prose-indigo">
      <h1 class="text-3xl font-black text-gray-900 mb-6 tracking-tight">6. Báo Cáo Hoạt Động (Reports)</h1>
      <p class="text-lg text-gray-500 mb-8">Một cú vung tay kỳ diệu gom 100% dữ liệu phát sinh trong tháng của tất cả tính năng vào một File trình duyệt và gửi lên Thượng Tầng Lãnh Đạo.</p>
      
      <div class="my-6 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <img src="/images/docs/dept-reports.png" alt="Ảnh minh hoạ Giao diện Báo cáo" class="w-full object-cover">
      </div>

      <p class="text-sm text-gray-700">Mọi thao tác Quản Lý Thành Viên, Thêm bớt Quỹ, Chăm sóc (Thăm viếng) hay Điểm Danh mà bạn khổ công chăm chút... đều sẽ trở nên vô giá ở mục Báo Cáo Cuối Tháng.</p>

      <h2 class="text-xl font-bold text-gray-900 mt-10 mb-4">Các Bước Xuất Báo Cáo Nhanh</h2>
      
      <ul class="list-decimal pl-5 mt-4 space-y-4 text-sm text-gray-700">
        <li>
          <strong>Cách Chọn Kỳ Kế Toán:</strong> <br/>
          Báo cáo chạy hệ đếm theo Tháng (Ví dụ: Tháng 01 / 2026). Việc đầu tiên của Thư Ký là nhấn `Tạo Báo Cáo Mới` và nhập Tháng/Năm.
        </li>
        <li>
          <strong>Tự Động Kéo Số Liệu (Auto-fetch Engine):</strong> <br/>
          Khác biệt của Cổng nội bộ CMS này là tính năng <code>Compile</code>. Ứng dụng sẽ lục lại từ ngày 1 đến ngày 31 của tháng bạn chọn để thống kê tự động (Không tính sai dù 1 chữ số). Nó sẽ rà soát:
          <ul class="list-disc pl-5 mt-2 text-gray-600 mb-2">
            <li>Có bao nhiêu người được thêm mới/rời đi? Đưa ra Biến Số Nhóm (Nhóm Tăng/Nhóm Giảm).</li>
            <li>Khoản Thu/Chi trong kì là gì? Số dư Quỹ ngày đầu tháng và Ngày cuối tháng (Pằng máy tính).</li>
            <li>Sĩ số Điểm danh Bình quân: Hệ thống sẽ chia trung bình cộng tỷ lệ chuyên cần các Sự Kiện diễn ra đúng trong Tháng đó để chốt "Trung Bình Số Người Đi Nhóm".</li>
          </ul>
        </li>
        <li>
          <strong>Can Thiệp Bằng Tay (Manual Override):</strong> <br/>
          Dù máy móc làm rất chuẩn mực, hệ thống vẫn cho phép cấp Quản Trị Viên (Thư ký lập hoặc Trưởng Ban) gõ chữ trực tiếp vào Form Báo Cáo để thêm Định Hướng Tuần Tới, Kế Hoạch Tổ Chức Hội Đồng... 
        </li>
        <li>
          <strong>Submit & Archive (Khóa Lưu Trữ):</strong> <br/>
          Khi Trưởng Ban kiểm tra và đồng ý, báo cáo sẽ được BẤM CHỐT "Submit". Ngay lập tức, file Báo cáo này bị "Khép lại (Đóng băng)" bảo mật không ai được sửa chữa. Đồng thời, Cổng Lãnh Đạo Hội Thánh (Mục sư) sẽ "Nhảy Thông báo" thấy được Báo cáo này để nhận xét (Comment).
        </li>
      </ul>

      <div class="mt-10 flex justify-between">
        <Link :href="route('help.departments.finance')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
          ⬅️ 5. Tài Chính Mở Rộng
        </Link>
        <div class="px-6 py-3 bg-green-50 text-green-700 font-bold border border-green-200 rounded-xl shadow-sm text-center">
          🎉 Ban Đã Thành Thạo Cổng Ban Ngành!
        </div>
      </div>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
