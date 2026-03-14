<template>
  <Head title="Hướng Dẫn Chiến Dịch Email" />
  
  <HelpLayout>
      <div class="p-8 md:p-12 prose prose-slate max-w-none">
        
        <div class="flex items-center space-x-4 mb-6 border-b border-slate-100 pb-6">
            <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-800 m-0">Chiến Dịch Email (Broadcasts)</h1>
                <p class="text-slate-500 m-0 mt-1">Gửi thông báo hàng loạt xuyên suốt hệ thống bằng Email</p>
            </div>
        </div>

        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-2xl mb-8">
            <p class="text-indigo-900 m-0 text-sm">
                <strong>Tổng quan:</strong> Tin Tự Động được thiết kế đặc biệt để Quản trị gửi thư tới nhóm nhân sự số lượng lớn, xử lí dạng Background Job chống đứng web khi số tiền kết xuất thông điệp lớn hơn vài trăm records.
            </p>
        </div>

        <!-- Section 1 -->
        <h2 class="text-2xl font-black text-slate-800 flex items-center mt-10">
            <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mr-3 shrink-0 text-sm">1</span>
            Quản Lý Lịch Sử Chiến Dịch
        </h2>
        <p>Bảng hiển thị trực quan liệt kê toàn bộ tiến độ của các lá Thư, phân loại rõ ràng qua Màu Sắc Trạng Thái:</p>
        <ul class="space-y-2 mb-6">
            <li><strong class="text-slate-600 bg-slate-100 px-2 rounded">Bản Nháp</strong>: Lá thư chưa phát hành, có thể được nhấn "Gửi ngay" bất kì lúc nào.</li>
            <li><strong class="text-blue-700 bg-blue-100 px-2 rounded">Đang Gửi</strong>: Tiến trình phát hành email đang chạy. (Kèm theo vạch Progress Bar bên cạnh trạng thái Tiến Độ).</li>
            <li><strong class="text-emerald-800 bg-emerald-100 px-2 rounded">Hoàn Tất</strong> / <strong class="text-red-800 bg-red-100 px-2 rounded">Lỗi</strong>: Đã đóng hàng đợi Queue. Có thống kê chính xác số mail Failed và Success.</li>
        </ul>

        <!-- Section 2 -->
        <h2 class="text-2xl font-black text-slate-800 flex items-center mt-12 border-t border-slate-100 pt-8">
            <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mr-3 shrink-0 text-sm">2</span>
            Thiết Lập Thư Trong Slide-Over
        </h2>
        <p>Giao diện khởi tạo được làm dạng Khung Trượt để giúp bạn theo tác dễ dàng, gọn nhẹ, lưu trữ thay vì tải lại toàn bộ Browser.</p>

        <figure class="my-8 rounded-2xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50 p-2">
            <img src="/images/docs/broadcast_slide_over.png" alt="Giao diện Khởi tạo Chiến dịch" class="rounded-xl w-full object-cover">
            <figcaption class="text-center text-sm text-slate-500 mt-3 font-medium">Hình 1: Soạn tin Broadcast và Chọn bộ Lọc thông minh</figcaption>
        </figure>

        <h3 class="text-lg font-bold text-slate-700">Các tính năng tùy chỉnh:</h3>
        <ol class="space-y-3 mb-8">
            <li>Nhập Tiêu đề và nội dung Thư. Nội dung hỗ trợ các tag thẻ Markdown cơ bản của Email (`#`, `*`, v.v...).</li>
            <li>
                <strong>Cấu hình Bộ lọc Đối tượng Nhận thông minh:</strong>
                <ul class="mt-2 list-disc list-inside text-slate-600">
                    <li>Bạn có thể tích hợp nhiều Role + nhiều phòng Ban khác nhau. Hệ thống sẽ tự lọc, những user sở hữu 1 trong các Role hoặc Ban ngành được chọn đều sẽ nhận Mail.</li>
                    <li>Sẽ tự loại bỏ các User bị thiết lập sai / thiếu Email.</li>
                    <li><strong>Bỏ tích toàn bộ:</strong> Hiểu rằng bạn đang gửi Đại trà Tổng thể.</li>
                </ul>
            </li>
            <li>Hai tùy chọn chức năng hành động: <strong>Lưu Nháp (Draft)</strong> hoặc <strong>Gửi Ngay Bằng Background (Send/Queue)</strong>.</li>
        </ol>

        <div class="bg-red-50 p-4 rounded-xl text-sm border border-red-200 mb-8 mt-4">
            <h4 class="font-bold flex items-center text-red-700 m-0 mb-2">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                Tránh spam Thư liên tiếp:
            </h4>
            <ul class="m-0 pl-6 space-y-1 text-red-600">
                Lưu ý quan trọng: Một chiến dịch ĐANG GỬI tuyệt đối <strong>Không Thể Bị Xoá</strong> để đảm bảo Database Job không bị gián đoạn gây crash Server. Vui lòng cân nhắc kiểm tra bản Nháp cẩn thận trước khi Gửi hàng nghìn Email.
            </ul>
        </div>

      </div>
  </HelpLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import HelpLayout from '@/Layouts/HelpLayout.vue';
</script>
