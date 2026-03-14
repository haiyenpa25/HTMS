<template>
  <Head title="Hướng dẫn: Điểm Danh Ban Ngành" />

  <DocsLayout>
    <div class="max-w-4xl mx-auto prose prose-indigo">
      <h1 class="text-3xl font-black text-gray-900 mb-6 tracking-tight">2. Điểm Danh Định Kỳ (Attendance)</h1>
      <p class="text-lg text-gray-500 mb-8">Công cụ tuyệt vời giúp Thủ quỹ và Thư ký nắm bắt sự chuyên cần của Ban ngành trong nháy mắt.</p>
      
      <div class="my-6 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <img src="/images/docs/dept-attendance.png" alt="Ảnh minh hoạ Giao diện Điểm danh" class="w-full object-cover">
      </div>

      <h2 class="text-xl font-bold text-gray-900 mt-10 mb-4">Cách thức hoạt động</h2>
      
      <p class="text-sm text-gray-700">Điểm danh trong hệ thống không phải là một danh sách rời rạc. Mỗi "Cột điểm danh" được gắn chặt với một <strong>Buổi Nhóm/Sự Kiện</strong> đã được tạo trên Lịch Hội Thánh (hoặc Lịch Ban Ngành).</p>

      <ul class="list-disc pl-5 mt-4 space-y-4 text-sm text-gray-700">
        <li>
          <strong>Tạo Buổi Điểm Danh:</strong> <br/>
          Tại giao diện chính, chọn "Thêm Buổi Điểm Danh", hệ thống sẽ gợi ý các sự kiện diễn ra trong tuần này hoặc tuần lưu trữ. Chọn đúng Buổi nhóm của bạn để bắt đầu.
        </li>
        <li>
          <strong>Điểm Danh Dạng Lưới (Grid Matrix):</strong> <br/>
          Danh sách Tín hữu sẽ xuất hiện theo từng hàng. Bạn chỉ việc thao tác cực nhanh bằng cách nhấp vào ô trạng thái: <br/>
          ✅ Có mặt <br/>
          ❌ Vắng mặt <br/>
          ❓ Có phép (Kèm theo ghi chú nếu bị ốm đau)
        </li>
        <li>
          <strong>Lọc theo Tổ:</strong> <br/>
          Kết hợp với tính năng <em>Thành Viên</em>, bạn có thể lọc ra "Tổ 1" để gọi tên và điểm danh liền mạch không sợ sót.
        </li>
        <li>
          <strong>Quét Mã QR:</strong> <br/>
          Ngoài điểm danh tay, hệ thống cung cấp mã QR sinh ra cho từng Sự kiện. Thành viên dùng cổng thông tin Nội Bộ (Hồ Sơ Của Tôi) trên điện thoại quét mã QR để tự đánh dấu "Có mặt".
        </li>
      </ul>

      <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 mt-8 rounded-r-lg">
        <p class="text-sm font-bold text-indigo-800 mb-1">Thống Kê Tự Động</p>
        <p class="text-sm text-indigo-700 mb-0">Phần trăm chuyên cần của một tín đồ (Tổng số đi lễ / Tổng số buổi) sẽ xuất hiện cạnh tên gọi của họ. Nắm bắt ai đi nhóm trễ, ai lâu không đi nhóm dễ dàng nhận biết bằng màu sắc Đỏ/Xanh.</p>
      </div>

      <div class="mt-10 flex justify-between">
        <Link :href="route('help.departments.members')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
          ⬅️ 1. Thành Viên
        </Link>
        <Link :href="route('help.departments.visitation')" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-sm hover:bg-indigo-700 transition">
          Tiếp theo: 3. Thăm Viếng ➡️
        </Link>
      </div>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
