<template>
  <Head title="Hướng Dẫn Quản Lý Bản Tin" />
  
  <HelpLayout>
      <div class="p-8 md:p-12 prose prose-slate max-w-none">
        
        <div class="flex items-center space-x-4 mb-6 border-b border-slate-100 pb-6">
            <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-800 m-0">Bản Tin Nội Bộ (Announcements)</h1>
                <p class="text-slate-500 m-0 mt-1">Cách Quản trị viên sử dụng tính năng thông báo trực tiếp</p>
            </div>
        </div>

        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-2xl mb-8">
            <p class="text-indigo-900 m-0 text-sm">
                <strong>Tổng quan:</strong> Tính năng Bản Tin hiển thị các thông báo quan trọng đến toàn hệ thống hoặc từng Ban ngành trực tiếp trên giao diện sử dụng của Tín Hữu, thay thế cho Email bằng Notification nổi bật trên web/app.
            </p>
        </div>

        <!-- Section 1 -->
        <h2 class="text-2xl font-black text-slate-800 flex items-center mt-10">
            <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mr-3 shrink-0 text-sm">1</span>
            Xem Danh sách Bản Tin
        </h2>
        <p>Truy cập thông qua Sidebar: <strong>Truyền Thông</strong> > <strong>Bản Tin</strong>.</p>
        <ul class="space-y-2">
            <li>Người dùng quản trị sẽ xem được toàn bộ danh sách Bản tin đã phát hành từ hệ thống.</li>
            <li>Theo dõi được người tạo, nội dung tóm tắt, nhóm đối tượng sẽ nhìn thấy, và ngày đăng.</li>
        </ul>

        <!-- Section 2 -->
        <h2 class="text-2xl font-black text-slate-800 flex items-center mt-12 border-t border-slate-100 pt-8">
            <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mr-3 shrink-0 text-sm">2</span>
            Tạo Mới Bằng Khung Trượt (Slide Over)
        </h2>
        <p>Hệ thống có tiện ích tạo mới dạng Slider Panel để giúp thao tác nhanh và không bị mất định hướng trên bảng biểu gốc.</p>

        <figure class="my-8 rounded-2xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50 p-2">
            <img src="/images/docs/announcement_slide_over.png" alt="Giao diện Tạo mới Bản Tin" class="rounded-xl w-full object-cover">
            <figcaption class="text-center text-sm text-slate-500 mt-3 font-medium">Hình 1: Panel thêm mới trực quan ở lề phải</figcaption>
        </figure>

        <h3 class="text-lg font-bold text-slate-700">Các bước thực hiện:</h3>
        <ol class="space-y-3 mb-8">
            <li>Tại góc trên cùng bên phải, nhấp vào nút <strong>"Đăng bản tin mới"</strong>. Bảng điền thông tin sẽ trượt ra.</li>
            <li>Nhập <strong>Tiêu đề Bản tin</strong> ngắn gọn.</li>
            <li>Soạn nội dung chi tiết. Tính năng hỗ trợ một số cấu trúc Markdown hoặc văn bản cơ bản (xuống dòng, gạch ngang).</li>
            <li>
                Cấu hình <strong>Phạm vi gửi</strong> (Scope):
                <ul class="mt-2 list-disc list-inside text-slate-600">
                    <li><strong>Toàn Hệ thống (Global)</strong>: Gửi tới 100% tài khoản.</li>
                    <li><strong>Riêng theo Ban ngành</strong>: Admin chọn đúng Ban ngành chỉ định để bảo mật thông tin nội bộ.</li>
                </ul>
            </li>
            <li>Click <strong>Gửi Bản Tin</strong>. Trạng thái sẽ được reset và hệ thống cảnh báo (Chuông) của các members được chọn sẽ cập nhật lập tức bản ghi này.</li>
        </ol>

        <!-- Section 3 -->
        <h2 class="text-2xl font-black text-slate-800 flex items-center mt-12 border-t border-slate-100 pt-8">
            <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mr-3 shrink-0 text-sm">3</span>
            Lưu Ý Về Xóa Thông Tin
        </h2>
        <div class="bg-amber-50 p-4 rounded-xl text-sm border border-amber-200 mb-8 mt-4">
            <p class="m-0 text-slate-700">Khi một Bản tin lỗi thời, bạn cần tự nhấn nút <strong>Xóa thùng rác</strong> đỏ ở cuối dòng trên Danh sách để thu hồi. Hệ thống chưa hỗ trợ thiết lập Hạn (Expired Date) cho bản tin, nên thao tác này cần làm định kì thủ công để tránh giao diện của Users quá lộn xộn nội dung cũ.</p>
        </div>

      </div>
  </HelpLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import HelpLayout from '@/Layouts/HelpLayout.vue';
</script>
