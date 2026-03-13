<template>
  <Head title="Hướng Dẫn Sử Dụng Hộp Thư & Bản Tin" />
  
  <HelpLayout>
      <div class="p-8 md:p-12 prose prose-slate max-w-none">
        
        <div class="flex items-center space-x-4 mb-6 border-b border-slate-100 pb-6">
            <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-800 m-0">Hệ Thống Thông Báo (Notifications)</h1>
                <p class="text-slate-500 m-0 mt-1">Hướng dẫn chi tiết quản lý Hộp thư & Đăng Bản tin nội bộ Hội Thánh</p>
            </div>
        </div>

        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-2xl mb-8">
            <p class="text-indigo-900 m-0 text-sm">
                <strong>Tổng quan:</strong> Hệ thống cung cấp thông tin cập nhật theo thời gian thực mỗi khi có thông báo tự động từ hệ thống (duyệt báo cáo, thay đổi lịch) hoặc có Bản tin do Mục sư/Ban trị sự phát động.
            </p>
        </div>

        <!-- Section 1 -->
        <h2 class="text-2xl font-black text-slate-800 flex items-center mt-10">
            <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mr-3 shrink-0 text-sm">1</span>
            Biểu tượng Chuông Thông Báo
        </h2>
        <p>Ở bất kì trang nào trong hệ thống, bạn đều sẽ nhìn thấy <strong>Icon Chuông Thông Báo</strong> ở trên cùng góc phải (phía trước Tên tài khoản của bạn).</p>
        <ul class="space-y-2">
            <li>Hệ thống <strong>tự động cập nhật số lượng thông báo</strong> chưa đọc mà không cần tải lại trang. Chữ số màu đỏ trên góc chuông (ví dụ <code>1</code> hoặc <code>9+</code>) cho biết số lượng tin mới.</li>
            <li>Nhấp vào biểu tượng Chuông để mở thanh Menu Dropdown xem nhanh <strong>Top 5 thông báo mới nhất</strong>.</li>
        </ul>

        <figure class="my-8 rounded-2xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50 p-2">
            <img src="/images/docs/notification_dropdown.png" alt="Biểu tượng chuông báo và Menu thả xuống" class="rounded-xl w-full object-cover">
            <figcaption class="text-center text-sm text-slate-500 mt-3 font-medium">Hình 1: Biểu tượng chuông báo và Menu Dropdown xem nhanh</figcaption>
        </figure>

        <div class="bg-slate-50 p-4 rounded-xl text-sm border border-slate-200 mb-8">
            <h4 class="font-bold flex items-center text-slate-700 m-0 mb-2">
                <svg class="w-4 h-4 text-amber-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                Mẹo sử dụng (Tips):
            </h4>
            <ul class="m-0 pl-6 space-y-1 text-slate-600">
                <li>Nhấp vào một thông báo trong danh sách để truy cập ngay liên kết liên quan. Hệ thống sẽ ngay lập tức đánh dấu thông báo này là <strong>Đã đọc</strong>.</li>
                <li>Bạn có thể click nút chữ nhỏ <strong>Đánh dấu tất cả đã đọc</strong> trên đầu khối Dropdown để dọn dẹp số chuông báo nhanh chóng.</li>
            </ul>
        </div>


        <!-- Section 2 -->
        <h2 class="text-2xl font-black text-slate-800 flex items-center mt-12 border-t border-slate-100 pt-8">
            <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mr-3 shrink-0 text-sm">2</span>
            Hộp Thư Cá Nhân (User Inbox)
        </h2>
        <p>Để xem toàn bộ lịch sử thông báo và Bản tin chi tiết hơn, hãy truy cập vào trang <strong>Hộp thư cá nhân</strong> bằng cách click vào đường link dưới cùng của menu Dropdown hoặc truy cập từ menu chính.</p>

        <figure class="my-8 rounded-2xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50 p-2">
            <img src="/images/docs/user_inbox.png" alt="Giao diện Hộp Thư Cá Nhân" class="rounded-xl w-full object-cover">
            <figcaption class="text-center text-sm text-slate-500 mt-3 font-medium">Hình 2: Giao diện Hộp Thư Cá Nhân hiển thị tất cả bản tin/thông báo</figcaption>
        </figure>

        <h3 class="text-lg font-bold text-slate-700">Các tính năng chính:</h3>
        <ul class="space-y-2 mb-8">
            <li>Liệt kê toàn bộ các <strong>Bản tin</strong> (Announcements) và <strong>Thông báo Hệ thống</strong> (System Notifications). Mới nhất luôn được xếp trên cùng.</li>
            <li>Mỗi thông báo có Icon màu sắc giúp nhận diện tức thì loại cảnh báo. (Ví dụ: màu Tím với icon Loa là Bản tin đăng tải từ Admin).</li>
            <li>Nội dung bản tin được hiển thị đầy đủ ngay trên luồng (không bị cắt bớt).</li>
            <li><strong>Màu nền nhạt</strong> kèm theo icon chấm tròn nhỏ chỉ báo rằng thông báo đó đang trong trạng thái <strong>CHƯA ĐỌC</strong>.</li>
            <li>Nhấp vào trên màn hình để xem để chuyến sang trạng thái Đã Đọc.</li>
        </ul>


        <!-- Section 3 -->
        <h2 class="text-2xl font-black text-slate-800 flex items-center mt-12 border-t border-slate-100 pt-8">
            <span class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center mr-3 shrink-0 text-sm">3</span>
            Gửi Bản Tin (Dành cho Quản Trị Viên)
        </h2>
        <p>Nhóm người dùng có cấp bậc <strong>Super Admin, Mục sư, Trưởng ban</strong> sẽ có thêm công cụ Đăng Bản tin tại Sidebar bên tay trái: Khu vực <code>Truyền Thông -> Bản Tin</code>.</p>
        <p>Khác với các tin nhắn tự động cảnh báo lỗi hay phê duyệt, Module Bản Tin cho phép bạn trực tiếp biên soạn nội dung thông báo gửi xuống hàng loạt Tín hữu.</p>

        <figure class="my-8 rounded-2xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50 p-2">
            <img src="/images/docs/create_announcement.png" alt="Giao diện Tạo mới Bản Tin" class="rounded-xl w-full object-cover">
            <figcaption class="text-center text-sm text-slate-500 mt-3 font-medium">Hình 3: Giao diện soạn thảo và chọn nhóm đối tượng nhận Bản Tin</figcaption>
        </figure>

        <h3 class="text-lg font-bold text-slate-700">Cách tạo mới Bản tin:</h3>
        <ol class="space-y-3 mb-8">
            <li>Nhấn nút <strong>Đăng bản tin mới</strong> trên trang Quản lý Bản tin.</li>
            <li>Điền Tiêu đề và Nội dung chi tiết cần thông báo vào form.</li>
            <li>
                Cấu hình <strong>Phạm vi gửi</strong> (Scope):
                <ul class="mt-2 list-disc list-inside text-slate-600">
                    <li><strong>Toàn Hệ thống (Global)</strong>: Bản tin sẽ gửi tới chuông báo của toàn bộ tài khoản tại Hội Thánh.</li>
                    <li><strong>Riêng theo Ban ngành (Department)</strong>: Chỉ gửi riêng cho Danh sách tín hữu phân định vào Ban ngành đó. Rất thuận tiện để thông báo sinh hoạt nội bộ.</li>
                </ul>
            </li>
            <li>Click <strong>Gửi Bản Tin</strong>. Tất cả User có quyền nhận sẽ ngay lập tức thấy thông báo nhảy trên Dropdown của họ.</li>
        </ol>

      </div>
  </HelpLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import HelpLayout from '@/Layouts/HelpLayout.vue';
</script>
