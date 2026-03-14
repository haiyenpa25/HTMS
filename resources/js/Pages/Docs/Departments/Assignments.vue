<template>
  <Head title="Hướng dẫn: Phân Công Ban Ngành" />

  <DocsLayout>
    <div class="max-w-4xl mx-auto prose prose-indigo">
      <h1 class="text-3xl font-black text-gray-900 mb-6 tracking-tight">4. Lên Lịch & Phân Công Phục Vụ (Duty Roster)</h1>
      <p class="text-lg text-gray-500 mb-8">Tranh được tình trạng tin nhắn trôi trong nhóm chat dẫn đến sót việc. Hệ thống thông báo rõ ràng.</p>
      
      <div class="my-6 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <img src="/images/docs/dept-assignments.png" alt="Ảnh minh hoạ Giao diện Phân công" class="w-full object-cover">
      </div>

      <p class="text-sm text-gray-700">Mỗi Ban Ngành, dù lớn hay nhỏ đều có những chức vụ sinh hoạt định kỳ. Ví dụ Ban Thanh Niên cần Hướng dẫn Nhóm, Đánh đàn, Máy chiếu, Âm thanh... Hệ thống Duty Roster (Phân công) được tạo ra để Trưởng Ban phân rõ ai làm việc gì vào Chủ Nhật tới.</p>

      <h2 class="text-xl font-bold text-gray-900 mt-10 mb-4">Làm Chủ Bảng Phân Công</h2>
      
      <ul class="list-disc pl-5 mt-4 space-y-4 text-sm text-gray-700">
        <li>
          <strong>Tạo Chức năng (Roles):</strong> <br/>
          Không giống các hệ thống rập khuôn, ứng dụng này cho phép mỗi Ban TỰ ĐỊNH NGHĨA các nhóm vai trò riêng biệt. Bạn nhấn vào nút "Thêm Vai Trò" và lần lượt điền "Trưởng Ban Nhạc", "Thư Ký Ghi Biên Bản", "Hát Lĩnh Xướng". Các Role này sẽ cố định ở Ban của bạn.
        </li>
        <li>
          <strong>Tạo Lịch Phục Vụ Hàng Tuần:</strong> <br/>
          Mỗi tuần hoặc mỗi sự kiện, bạn bấm "Thêm Nhiệm vụ", hệ thống hiện lên 1 lưới chọn (Select Grid). Ở Cột Vai Trò sẽ xổ xuống các Role đã định nghĩa, và Cột Tín Hữu sẽ lấy danh sách từ Quản Lý Thành Viên. Thao tác siêu tốc.
        </li>
        <li>
          <strong>Sao Chép Tuần Cũ:</strong> <br/>
          Đây là tính năng tiết kiệm thời gian nhất. Nếu tuần sau Ban hát vẫn những nhân sự đó (chỉ đổi 1-2 vị trí), bạn bấm "Auto Assign (Sao Chép)" để lặp lại khung của tuần trước, rồi sửa nhẹ lại. Xong!
        </li>
        <li>
          <strong>Phân Quyền Theo Ban:</strong> <br/>
          Đặc biệt, lịch phục vụ của Ban bạn SẼ HIỆN trực tiếp vào <em>Hồ Sơ Của Tôi (Dashboard cá nhân)</em> của tín hữu đó khi đăng nhập cổng Hội Thánh, nhắc nhở họ hoàn thành bổn phận. Tuyệt vời chứ?
        </li>
      </ul>

      <div class="mt-10 flex justify-between">
        <Link :href="route('help.departments.visitation')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
          ⬅️ 3. Thăm Viếng
        </Link>
        <Link :href="route('help.departments.finance')" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-sm hover:bg-indigo-700 transition">
          Tiếp theo: 5. Tài Chính Mở Rộng ➡️
        </Link>
      </div>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
