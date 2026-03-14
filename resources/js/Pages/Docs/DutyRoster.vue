<template>
  <DocsLayout>
    <Head title="Docs - Lịch Phân Công" />
    <article class="prose prose-slate max-w-none">
      <h1 class="text-3xl font-black text-gray-900 mb-2">Hướng Dẫn: Lịch Phân Công</h1>
      <p class="text-lg text-gray-500 mb-8">Tính năng Lịch Phân Công giúp quản lý và tự động hóa việc xếp lịch nhân sự cho các buổi lễ của Hội Thánh cũng như các buổi nhóm Ban Ngành.</p>

      <!-- TOC -->
      <div class="bg-indigo-50/50 rounded-2xl p-6 mb-10 border border-indigo-100">
        <h3 class="font-bold text-indigo-900 mb-3 text-sm uppercase tracking-wider">Nội dung chính</h3>
        <ul class="space-y-2 text-sm text-indigo-700">
          <li><a href="#tong-quan" class="hover:underline">1. Tổng quan giao diện chính</a></li>
          <li><a href="#quan-ly-mau" class="hover:underline">2. Quản lý Mẫu Phân Công (Template)</a></li>
          <li><a href="#tao-su-kien" class="hover:underline">3. Tạo Buổi Nhóm / Sự Kiện Mới</a></li>
          <li><a href="#phan-cong" class="hover:underline">4. Thực hiện Phân Công Nhân Sự</a></li>
        </ul>
      </div>

      <!-- Section 1 -->
      <h2 id="tong-quan" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">1</span> 
        Tổng quan giao diện chính
      </h2>
      <p>Khi truy cập vào menu <strong>Lịch phân công</strong> từ thanh menu bên trái, bạn sẽ thấy màn hình Dashboard gồm các phần chính:</p>
      <ul class="list-disc pl-5 mt-2 space-y-2">
        <li><strong>Lượt nhóm sắp tới:</strong> Hiển thị sự kiện gần nhất cùng tiến độ xếp lịch (%).</li>
        <li><strong>Danh sách Lịch:</strong> Liệt kê tất cả các buổi nhóm trong tháng. Bạn có thể thay đổi tháng bằng các nút điều hướng.</li>
        <li><strong>Bộ lọc (Bên phải):</strong> Giúp bạn lọc nhanh các buổi lễ theo Trạng thái (Đã xong/Chưa xong), Loại buổi nhóm (Hội thánh/Ban ngành).</li>
        <li><strong>Thao tác nhanh:</strong> Các nút để tạo Lịch mới, Quản lý Mẫu (Templates), hoặc xuất định dạng Excel tổng hợp.</li>
      </ul>

      <!-- Section 2 -->
      <h2 id="quan-ly-mau" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">2</span> 
        Quản lý Mẫu Phân Công (Template)
      </h2>
      <div class="bg-amber-50 border-l-4 border-amber-500 p-4 my-4 rounded-r-xl">
        <p class="text-sm text-amber-800 m-0"><strong>Mẹo:</strong> Khuyến khích bạn nên thiết lập <em>Mẫu phân công (Templates)</em> trước khi tạo các Lịch sự kiện. Việc này giúp tiết kiệm 90% thời gian khi phân công các buổi lễ cố định (như Lễ Thờ Phượng Chúa Nhật).</p>
      </div>

      <h3 class="text-lg font-bold text-gray-800 mt-6 mb-2">2.1 Tạo Mẫu mới</h3>
      <p>Vào mục <strong>Quản lý Mẫu</strong> từ cột phải màn hình chính, bấm chọn <strong>+ Tạo Mẫu Mới</strong>. Nhập tên mẫu (VD: "Chúa Nhật Tuần 1") và chọn loại mẫu:</p>
      <ul class="list-disc pl-5 mt-2">
        <li><strong>Hội Thánh:</strong> Áp dụng cho các chương trình chung toàn hội thánh.</li>
        <li><strong>Ban Ngành:</strong> Áp dụng riêng cho các buổi sinh hoạt nội bộ ban ngành (chỉ Trưởng/Phó ban mới nhìn thấy).</li>
      </ul>

      <h3 class="text-lg font-bold text-gray-800 mt-6 mb-2">2.2 Thiết lập cấu trúc (Sections)</h3>
      <p>Sau khi tạo, giao diện chi tiết Mẫu sẽ chia làm 2 phần (Section):</p>
      
      <div class="grid md:grid-cols-2 gap-4 mt-4">
        <div class="border border-gray-200 rounded-xl p-4 shadow-sm bg-white">
          <div class="flex items-center gap-2 mb-2">
            <span class="w-6 h-6 bg-orange-500 text-white rounded-lg flex items-center justify-center text-xs font-bold">I</span>
            <strong class="text-gray-900">Chương Trình Lễ</strong>
          </div>
          <p class="text-sm text-gray-600">Đại diện cho các vị trí điều hành chính trên bục giảng (Hướng dẫn, Cầu nguyện, Đọc Kinh Thánh...). Bạn click vào ô vị trí để thêm vào Mẫu, hoặc bấm "+ Thêm vai trò mới" nếu chưa có.</p>
        </div>
        <div class="border border-gray-200 rounded-xl p-4 shadow-sm bg-white">
          <div class="flex items-center gap-2 mb-2">
            <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center text-xs font-bold">II</span>
            <strong class="text-gray-900">Ban Hỗ Trợ</strong>
          </div>
          <p class="text-sm text-gray-600">Dành cho các ban (VD: Ban Âm Nhạc, Ban Tiếp Tân). Đầu tiên, click <strong>chọn Ban tham gia</strong> ở trên cùng, sau đó mở rộng từng ban bên dưới và tick chọn vị trí cần thiết.</p>
        </div>
      </div>

      <!-- Section 3 -->
      <h2 id="tao-su-kien" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">3</span> 
        Tạo Buổi Nhóm / Sự Kiện Mới
      </h2>
      <ol class="list-decimal pl-5 space-y-2 mt-2">
        <li>Từ trang chủ Lịch Phân Công, nhấn <strong>+ Tạo phân công</strong>.</li>
        <li>Điền thông tin sự kiện: <em>Ngày diễn ra, giờ, chủ đề, diễn giả</em>.</li>
        <li>Nhấn <strong>Tạo & Phân Lịch</strong> để hoàn tất tạo sự kiện trống.</li>
      </ol>

      <!-- Section 4 -->
      <h2 id="phan-cong" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">4</span> 
        Thực hiện Phân Công Nhân Sự
      </h2>
      <p>Sau khi tạo (hoặc khi click vào nút "Chi tiết" của một buổi nhóm đang trống), bạn sẽ vào giao diện Xếp lịch chính thức. Giao diện này cũng chia làm 2 phần y hệt như lúc làm Mẫu (Chương trình lễ & Ban hỗ trợ).</p>
      
      <div class="bg-indigo-50 p-4 rounded-xl my-4">
        <h4 class="font-bold text-indigo-900 mb-2">📋 Cách 1: Phân công nhanh gọn bằng cách Áp Dụng Mẫu (Template)</h4>
        <p class="text-sm text-indigo-800">Trên góc phải của màn hình chi tiết, bấm nút <strong>Áp dụng mẫu</strong>. Chọn Mẫu mà bạn đã tạo ở bước 2. Hệ thống sẽ tự động ghép dán tất cả các "Vị trí trống" vào giao diện. Bạn lúc này chỉ việc bấm chọn tên người vào từng lỗ trống.</p>
      </div>

      <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
        <h4 class="font-bold text-gray-800 mb-2">✍️ Cách 2: Phân công thủ công (Cho sự kiện đột xuất không có mẫu)</h4>
        <p class="text-sm text-gray-600">Với các Ban Hỗ trợ, bạn nhấn mở tên Ban ra, bấm nút "+ Thêm vị trí...". Gõ tên công việc, xong. Sau đó bấm vào ô công việc vừa tạo và chọn tên người đảm nhiệm là hoàn tất.</p>
      </div>

    </article>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
