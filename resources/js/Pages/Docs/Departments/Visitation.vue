<template>
  <Head title="Hướng dẫn: Thăm Viếng Chăm Sóc" />

  <DocsLayout>
    <div class="max-w-4xl mx-auto prose prose-indigo">
      <h1 class="text-3xl font-black text-gray-900 mb-6 tracking-tight">3. Nhật Ký Thăm Viếng Chăm Sóc (Visitation)</h1>
      <p class="text-lg text-gray-500 mb-8">Một Ban ngành mạnh khỏe là một Hội Thánh mạnh khỏe. Chức năng lưu giữ tình yêu thương giữa các tín đồ.</p>
      
      <div class="my-6 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <img src="/images/docs/dept-visitation.png" alt="Ảnh minh hoạ Giao diện Thăm viếng" class="w-full object-cover">
      </div>

      <p class="text-sm text-gray-700">Khi một tín hữu đau ốm, hay vắng nhóm dài ngày (có thể xem qua chức năng <em>Điểm Danh</em>), Ban Điều Hành tổ chức các buổi Thăm Viếng. Tuy nhiên, nếu thiếu đi công cụ ghi chép thì những lời cầu nguyện hay thực trạng của tín đồ sẽ bị trôi vào quên lãng.</p>

      <h2 class="text-xl font-bold text-gray-900 mt-10 mb-4">Các Bước Ghi Nhật Ký Thăm Viếng</h2>
      
      <ol class="list-decimal pl-5 mt-4 space-y-4 text-sm text-gray-700">
        <li>
          <strong>Tạo Chuyến Đi:</strong> <br/>
          Chọn nút "Ghi nhận Thăm viếng Mới". Chọn Ngày đi, Đối tượng được thăm (Có thể chọn nhiều Tín hữu cùng lúc nếu thăm nguyên gia đình).
        </li>
        <li>
          <strong>Người Thực Hiện (Người đi thăm):</strong> <br/>
          Hệ thống sẽ cho bạn lưu lại ai là trưởng đoàn và các nhân sự nào đã cùng đi thăm để quản lý công tác hiệu quả.
        </li>
        <li>
          <strong>Mục Đích Thăm Viếng:</strong> <br/>
          Phân loại mục đích rõ ràng: Thăm vì đau ốm/Sinh đẻ, Thăm viếng tân tín hữu, Ủi an tang quyến, ... Hệ thống có tích hợp sẵn các Tag phân loại màu sắc rực rỡ, giúp Mục Sư / Trưởng Ban trích lọc báo cáo cực nhanh chóng.
        </li>
        <li>
          <strong>Ghi Chép Diễn Biến & Lời Cầu Nguyện:</strong> <br/>
          Đây là ô Text mở rộng, để bạn hoặc Thư ký đoàn lưu y chang những chia sẻ của tín hữu, các vướng mắc thuộc linh, kèm theo Vấn Đề Cầu Nguyện chung của gia đình. Dữ liệu này sẽ làm cơ sở để theo dõi lịch sử sinh hoạt của họ trong những năm tháng tới.
        </li>
      </ol>

      <div class="mt-10 flex justify-between">
        <Link :href="route('help.departments.attendance')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
          ⬅️ 2. Điểm Danh
        </Link>
        <Link :href="route('help.departments.assignments')" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-sm hover:bg-indigo-700 transition">
          Tiếp theo: 4. Phân Công ➡️
        </Link>
      </div>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
