<template>
  <Head title="HDSD: Tài Chính & Báo Cáo" />
  
  <HelpLayout>
      <div class="p-8 md:p-12 prose prose-slate max-w-none">
        
        <h1 class="text-3xl font-black mb-6 text-slate-800 border-b border-slate-100 pb-4">Tài Chính & Báo Cáo Định Kỳ</h1>
        
        <p class="text-lg text-slate-600 mb-8">
            Đây là hai công cụ phục vụ đặc thù cho vai trò Thủ Quỹ và Trưởng Ban/Thư Ký. Giúp chuẩn hóa số liệu Thu-Chi và báo cáo tự động lên Ban Trị Sự định kỳ.
        </p>

        <!-- Divider -->
        <hr class="my-8 border-slate-200">

        <!-- 1. Tài Chính -->
        <h2 class="text-2xl font-black text-slate-800 flex items-center mb-6">
          <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mr-3 shrink-0">1</span>
          Quản lý Sổ Quỹ (Finance)
        </h2>
        
        <p class="mb-6">Sổ quỹ độc lập của từng Ban Ngành, giúp theo dõi Dâng Hiến, Thu Quỹ và các khoản Chi sinh hoạt.</p>

        <div class="bg-slate-100 rounded-2xl overflow-hidden mb-8 border border-slate-200 shadow-sm relative">
             <img src="/storage/HDSD/feature_finance.png" alt="Giao diện Quản lý Tài Chính" class="w-full h-auto object-cover m-0">
        </div>

        <div class="bg-rose-50 border-l-4 border-rose-400 p-4 rounded-r-xl my-6">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-rose-500 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div class="text-sm text-rose-800 font-medium">
                    <span class="font-bold text-rose-900 block mb-1">Cảnh báo:</span>
                    Mọi thao tác Xóa (Delete) Giao dịch Tài chính đều được lưu Vet (Log) lại trên hệ thống của Church Admin. Vui lòng đối chiếu sao kê cẩn thận trước khi thay đổi.
                </div>
            </div>
        </div>

        <h4 class="font-bold text-slate-800 text-lg mt-8 mb-4">Nghiệp vụ cơ bản:</h4>
        <ul class="list-disc pl-6 space-y-3 mb-8">
            <li><strong>Thu (Income):</strong> Nhập tiền Dâng nền, Dâng hội đồng, Dâng sinh nhật...</li>
            <li><strong>Chi (Expense):</strong> Ghi chép tiền mua sách báo, hoa sinh nhật, thăm viếng ốm đau...</li>
            <li><strong>Tạo Giao Dịch Hàng Loạt:</strong> Khai báo nhanh khoản Thu/Chi bằng việc Import từ file Excel đối với các dịp Lễ lớn.</li>
            <li><strong>Thống kê:</strong> Máy chủ tự động tính Tổng Thu, Tổng Chi và Số Dư Cuối Kỳ ngay trên màn hình.</li>
        </ul>

        <!-- Divider -->
        <hr class="my-10 border-slate-200">

        <!-- 2. Báo Cáo Tháng -->
        <h2 class="text-2xl font-black text-slate-800 flex items-center mb-6">
          <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mr-3 shrink-0">2</span>
          Lập Báo Cáo Tháng (Reports)
        </h2>

        <p class="mb-6">Hệ thống đồ thị động và Bảng khai báo tổng hợp dành cho Ban Trị Sự theo định kỳ (Tuần/Tháng/Quý/Năm).</p>

        <div class="bg-slate-100 rounded-2xl overflow-hidden mb-8 border border-slate-200 shadow-sm">
             <img src="/storage/HDSD/feature_report.png" alt="Giao diện Báo Cáo" class="w-full h-auto object-cover m-0">
        </div>

        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-xl my-6">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-indigo-500 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div class="text-sm text-indigo-800 font-medium">
                    <span class="font-bold text-indigo-900 block mb-1">Quan trọng:</span>
                    Hãy đảm bảo rằng công tác Điểm Danh và Tài Chính của tháng đó ĐÃ HOÀN TẤT trước khi bạn bấm nút "Tạo Báo Cáo Tháng".
                </div>
            </div>
        </div>

        <h4 class="font-bold text-slate-800 text-lg mt-8 mb-4">Hướng dẫn Tạo Báo Cáo Mới:</h4>
        <ol class="list-decimal pl-6 space-y-3 mb-8">
            <li>Chọn khoảng thời gian (VD: Tháng 3/2026).</li>
            <li>Hệ thống sẽ <strong>Tự Động Kéo Số Liệu (Auto-fetch)</strong> từ Điểm Danh (Trung bình nhóm lại) và Tài Chính (Số dư) vào khung báo cáo.</li>
            <li>Người viết báo cáo chỉ cần điền thêm bằng văn bản vào các mục:
                <ul class="list-disc pl-5 mt-2 space-y-1 text-slate-600 font-medium">
                   <li>Tóm tắt sinh hoạt</li>
                   <li>Nan đề cầu nguyện</li>
                   <li>Kế hoạch dự kiến tháng tới</li>
                </ul>
            </li>
            <li>Nhấn <strong>"Lưu & Gửi"</strong>. Biên bản này sẽ lập tức hiện diện tại Tab "Báo Cáo Ban Ngành" của Ban Trị Sự để họ duyệt và nắm bắt.</li>
            <li>Xem lại Biểu đồ (Biểu đồ đường/Cột) thống kê xu hướng số lượng người tham dự trong suốt năm đó.</li>
        </ol>

      </div>
  </HelpLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import HelpLayout from '@/Layouts/HelpLayout.vue';
</script>
