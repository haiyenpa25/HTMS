<template>
  <Head title="HDSD: Điểm Danh & Thăm Viếng" />
  
  <HelpLayout>
      <div class="p-8 md:p-12 prose prose-slate max-w-none">
        
        <h1 class="text-3xl font-black mb-6 text-slate-800 border-b border-slate-100 pb-4">Điểm Danh & Ghi Chép Thăm Viếng</h1>
        
        <p class="text-lg text-slate-600 mb-8">
            Hai chức năng cốt lõi giúp Ban Điều Hành nắm được tình hình chuyên cần của tín hữu và ghi nhận công tác chăm sóc, thăm viếng định kỳ.
        </p>

        <!-- Divider -->
        <hr class="my-8 border-slate-200">

        <!-- 1. Điểm Danh -->
        <h2 class="text-2xl font-black text-slate-800 flex items-center mb-6">
          <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3 shrink-0">1</span>
          Điểm Danh Nhóm Lại (Attendance)
        </h2>
        
        <p class="mb-6">Nơi ghi nhận sự hiện diện của Thành viên trong các Buổi Nhóm hàng tuần.</p>

        <div class="bg-slate-100 rounded-2xl overflow-hidden mb-8 border border-slate-200 shadow-sm relative">
             <img src="/storage/HDSD/feature_attendance.png" alt="Giao diện Điểm danh" class="w-full h-auto object-cover m-0">
        </div>

        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-xl my-6">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-amber-500 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                <div class="text-sm text-amber-800 font-medium">
                    <span class="font-bold text-amber-900 block mb-1">Thao tác nhanh:</span>
                    Bạn có thể Điểm danh nhanh thông qua ứng dụng Mobile hoặc dùng Thanh Tìm Kiếm để gõ tên người mới đến.
                </div>
            </div>
        </div>

        <h4 class="font-bold text-slate-800 text-lg mt-8 mb-4">Quy trình Điểm Danh:</h4>
        <ol class="list-decimal pl-6 space-y-3 mb-8">
            <li>Chọn Buổi nhóm cần điểm danh ở thanh điều hướng trên cùng.</li>
            <li>Danh sách tất cả thành viên của Ban sẽ hiện ra.</li>
            <li>Click vào Checkbox bên cạnh tên để đánh dấu <strong>"Có Mặt"</strong>.</li>
            <li>Các tùy chọn mở rộng:
               <ul class="list-disc pl-5 mt-2 space-y-1 text-slate-600">
                  <li>Đánh dấu <strong>"Vắng (Có phép)"</strong> bằng cách chọn biểu tượng Giấy xin phép.</li>
                  <li>Thêm Thân Hữu hoặc Khách tham dự không có trong danh sách.</li>
               </ul>
            </li>
        </ol>

        <p class="italic text-slate-500">Hệ thống sẽ tự động lưu lại Trạng Thái (Live Auto-save) mà không cần bạn phải bấm nút Lưu.</p>

        <!-- Divider -->
        <hr class="my-10 border-slate-200">

        <!-- 2. Thăm viếng -->
        <h2 class="text-2xl font-black text-slate-800 flex items-center mb-6">
          <span class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center mr-3 shrink-0">2</span>
          Quản Lý Thăm Viếng (Visitation)
        </h2>

        <p class="mb-6">Ban thăm viếng hoặc Ban Điều hành có thể cập nhật kết quả các biên bản thăm viếng cá nhân.</p>

        <div class="bg-slate-100 rounded-2xl overflow-hidden mb-8 border border-slate-200 shadow-sm">
             <img src="/storage/HDSD/feature_visitation.png" alt="Giao diện Thăm viếng" class="w-full h-auto object-cover m-0">
        </div>

        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-xl my-6">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-red-500 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <div class="text-sm text-red-800 font-medium">
                    <span class="font-bold text-red-900 block mb-1">Bảo mật thông tin:</span>
                    Những nội dung thăm viếng được phân loại là "Kín đáo/Gửi Mục sư" sẽ bị ẩn và chỉ có Quản trị hệ thống hoặc Mục Sư Quản Nhiệm mới có thể đọc chi tiết.
                </div>
            </div>
        </div>

        <h4 class="font-bold text-slate-800 text-lg mt-8 mb-4">Tính năng nổi bật:</h4>
        <ul class="list-disc pl-6 space-y-3 mb-8">
            <li><strong>Thêm phiếu Thăm Viếng:</strong> Nhập thời gian, Đối tượng được thăm, Người thực hiện và Tóm tắt nội dung/Nan đề cầu nguyện.</li>
            <li><strong>Phân loại Kết Quả:</strong> Đánh giá tình trạng (Tích cực, Đang theo dõi, Cần Mục sư hỗ trợ).</li>
            <li><strong>Lịch sử Theo dõi:</strong> Cuộn dọc để xem Toàn bộ nhật ký chăm sóc của Ban trong suốt một năm qua.</li>
        </ul>

        <p class="italic text-slate-500">Mẹo: Sử dụng những dữ liệu Thăm viếng này để tổng hợp đưa vào Báo cáo Tháng.</p>

      </div>
  </HelpLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import HelpLayout from '@/Layouts/HelpLayout.vue';
</script>
