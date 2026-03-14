<template>
  <DocsLayout>
    <Head title="Docs - Quản lý Tài chính" />
    <article class="prose prose-slate max-w-none">
      <h1 class="text-3xl font-black text-gray-900 mb-2">Hướng Dẫn: Quản Lý Quỹ & Tài Chính</h1>
      <p class="text-lg text-gray-500 mb-8">Một phân hệ độc lập dành cho Ban Tài chính, Chấp sự và Thủ quỹ các Ban ngành nhằm minh bạch dòng tiền Hệ thống.</p>
      
      <!-- TOC -->
      <div class="bg-indigo-50/50 rounded-2xl p-6 mb-10 border border-indigo-100">
        <h3 class="font-bold text-indigo-900 mb-3 text-sm uppercase tracking-wider">Nội dung chính</h3>
        <ul class="space-y-2 text-sm text-indigo-700">
          <li><a href="#tong-quan-quy" class="hover:underline">1. Tổng Quan Quỹ & Phân Quyền</a></li>
          <li><a href="#phieu-thu-chi" class="hover:underline">2. Lập Phiếu Thu / Phiếu Chi</a></li>
          <li><a href="#bao-cao-tai-chinh" class="hover:underline">3. Báo Cáo Thống Kê & Bảng Điện Tử</a></li>
        </ul>
      </div>

      <!-- Section 1 -->
      <h2 id="tong-quan-quy" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">1</span> 
        Tổng Quan Quỹ & Phân Quyền
      </h2>
      <p>Cũng tương tự như Cổng Điểm Danh, Cổng Tài Chính (Finance Portal) bị ràng buộc gắt gao bởi hệ thống <strong>Matrix Access Control</strong>. Điều này có nghĩa là:</p>
      <ul class="list-disc pl-5 my-4">
        <li><strong>SuperAdmin / Thủ quỹ Hội Thánh:</strong> Cầm chìa khóa vào **Tất cả các Quỹ** hiển thị trên một biểu đồ cực lớn (Toàn cảnh Hội Thánh). Nhìn lướt là biết Quỹ Thanh Tráng đang có bao nhiêu, Quỹ Xây Dựng, Quỹ Truyền Giáo đang dư hay thiếu.</li>
        <li><strong>Ban Ngành:</strong> Tuyệt đối chỉ thao tác được với Quỹ của riêng Ban mình. Ví dụ, `tb.thanhtrang` (Trưởng ban Thanh tráng) chỉ xem được thông số dòng tiền Thanh tráng, không thể tọc mạch sang quỹ Truyền Giáo.</li>
      </ul>

      <!-- Section 2 -->
      <h2 id="phieu-thu-chi" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">2</span> 
        Lập Phiếu Thu / Phiếu Chi
      </h2>
      <p>Mỗi tác vụ thay đổi số dư quỹ đều yêu cầu bạn phải điền MỘT chứng từ (Giao dịch) cụ thể. Giao diện này đã được thiết kế dưới dạng <strong>Dashboard tương tác</strong> rất trực quan.</p>
      
      <div class="bg-gray-50 border border-gray-200 p-6 rounded-2xl mb-6 flex flex-col gap-4">
        <div class="bg-white p-4 border-l-4 border-emerald-500 rounded-r-xl shadow-sm">
          <h4 class="font-bold text-gray-900 text-sm mb-1 uppercase tracking-wider">Lập Phiếu Thu (Nhập Tiền)</h4>
          <p class="text-xs text-gray-600">Nhấn nút <strong>"Thêm Giao dịch Mới"</strong>. Chọn loại (Type) là Thu. Các danh mục thường gặp có thể là: Tiền dâng phần mười (Tithes), Tiền dâng tự nguyện (Offerings), hoặc Tiền đóng quỹ sinh hoạt (Activities). Chọn Quỹ Đích đến (Nếu là Superadmin/Chấp sự). Bạn ghi nhận số tiền và Ghi chú người nộp.</p>
        </div>
        
        <div class="bg-white p-4 border-l-4 border-rose-500 rounded-r-xl shadow-sm">
          <h4 class="font-bold text-gray-900 text-sm mb-1 uppercase tracking-wider">Lập Phiếu Chi (Xuất Tiền)</h4>
          <p class="text-xs text-gray-600">Làm y hệt bước phía trên. Nhưng Chọn loại Giao dịch là <strong>Chi (Expense)</strong>. Hệ thống sẽ tự trừ đi TỔNG SỐ DƯ của Quỹ được hiển thị ở bảng điều khiển trung tâm lập tức (Real-time update).</p>
        </div>
      </div>

      <!-- Section 3 -->
      <h2 id="bao-cao-tai-chinh" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">3</span> 
        Báo Cáo Thống Kê & Bảng Điện Tử
      </h2>
      <p>Tính năng vượt trội của hệ thống là Trực quan hóa dữ liệu (Data Visualization). Bảng điều khiển (Dashboard) tự động vẽ 2 biểu đồ:</p>
      
      <ul class="list-none space-y-3 my-4">
        <li class="flex items-start gap-2">
          <span class="text-indigo-600 font-bold">1. Biểu Đồ Tròn (Phân Tán Cấu Trúc Quỹ):</span> Nhìn vào sẽ thấy bao nhiêu phần trăm là Dâng Phần Mười, bao nhiêu phần trăm là Quỹ Truyền giao...
        </li>
        <li class="flex items-start gap-2">
          <span class="text-emerald-600 font-bold">2. Biểu Đồ Cột (Chênh Lệch Thu Chi Tháng):</span> Đánh giá được cán cân tài chính, trong tháng này thu vào bao nhiêu và chi ra bao nhiêu rất rành mạch.
        </li>
      </ul>

      <div class="bg-amber-50 p-4 border-l-4 border-amber-500 text-sm text-amber-900 mt-2">
        <p class="m-0">Lưu ý: Quản lý Quỹ chưa bao giờ dễ hơn! Nếu cần, bạn vẫn có thể in chứng từ giấy ra như bình thường nhờ tính năng <strong>[Xuất/In PDF]</strong> để trình ký Chấp Sự.</p>
      </div>

    </article>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
