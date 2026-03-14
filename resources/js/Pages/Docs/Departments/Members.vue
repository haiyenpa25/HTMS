<template>
  <Head title="Hướng dẫn: Thành Viên Ban Ngành" />

  <DocsLayout>
    <div class="max-w-4xl mx-auto prose prose-indigo">
      <h1 class="text-3xl font-black text-gray-900 mb-6 tracking-tight">1. Quản lý Thành Viên Ban Ngành (Members)</h1>
      <p class="text-lg text-gray-500 mb-8">Tính năng cốt lõi giúp Ban Điều Hành nắm rõ số lượng, phân bổ nhân sự và trao quyền quản trị linh hoạt.</p>
      
      <div class="bg-blue-50 border border-blue-200 p-6 rounded-2xl mb-10 shadow-sm">
        <p class="text-sm font-bold text-gray-900 mb-2">💡 Tips</p>
        <p class="text-sm text-gray-600 mb-0">Hệ thống hỗ trợ nạp tự động tín hữu từ dữ liệu Hội Thánh (do System Admin quản lý). Bạn không cần phải tốn công gõ lại dữ liệu từng người từ đầu.</p>
      </div>

      <div class="my-6 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <img src="/images/docs/dept-members.png" alt="Ảnh minh hoạ Giao diện Thành viên Ban ngành" class="w-full object-cover">
      </div>

      <h2 class="text-xl font-bold text-gray-900 mt-10 mb-4">Các Chức năng chính</h2>
      
      <ul class="list-disc pl-5 mt-4 space-y-4 text-sm text-gray-700">
        <li>
          <strong>Nạp Thành Viên:</strong> <br/>
          Thay vì tạo mới, Thư ký nhấn nút "Thêm/Nạp Thành Viên", hệ thống sẽ hiển thị một danh sách tín hữu đang sinh hoạt tại Hội Thánh chưa có trong Ban ngành. Chỉ cần "tick" chọn và nạp vào. Bạn cũng có thể thiết lập trạng thái `Chính Thức` hay `Khách Mời` lúc nạp.
        </li>
        <li>
          <strong>Chia Tổ/Nhóm (Teams/Groups):</strong> <br/>
          Rất tiện lợi cho các Ban ngành có số lượng đông. Bằng cách chọn "Sửa", bạn có thể gán các tín hữu vào Tổ 1, Tổ 2, hoặc các nhóm nhỏ như Ban Hát, Tổ Nam... Tính năng này kết hợp trực tiếp với Phân hệ Điểm Danh để điểm danh theo tổ.
        </li>
        <li>
          <strong>Cấp quyền Đặc Quyền (Thư Ký / Thủ Quỹ):</strong> <br/>
          Ngay tại danh sách, cột "Chức Vụ" cho phép Trưởng Ban "Bật/Tắt" quyền Thư Ký hoặc Thủ Quỹ cho một thành viên. Lập tức, thành viên đó sẽ được cấp quyền truy cập hệ thống để phụ giúp Ban Điều Hành.
        </li>
        <li>
          <strong>Trạng Thái (Status):</strong> <br/>
          Khi một thành viên chuyển sinh hoạt hoặc đi xa, bạn có thể chuyển trạng thái (Active -> Inactive). Họ sẽ bị ẩn khỏi danh sách điểm danh nhưng vẫn giữ nguyên lịch sử hoạt động.
        </li>
      </ul>

      <div class="mt-10 flex justify-end">
        <Link :href="route('help.departments.attendance')" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-sm hover:bg-indigo-700 transition">
          Tiếp theo: 2. Điểm Danh ➡️
        </Link>
      </div>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
