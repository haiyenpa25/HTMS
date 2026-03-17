<template>
  <DocsLayout>
    <Head title="Docs - Quản Trị Hệ Thống" />
    <article class="prose prose-slate max-w-none">
      <h1 class="text-3xl font-black text-gray-900 mb-2">Hướng Dẫn: Quản Trị Hệ Thống (SuperAdmin)</h1>
      <p class="text-lg text-gray-500 mb-8">Dành riêng cho Quản trị viên Tối cao: Nơi bạn nắm giữ chìa khóa sinh mệnh của toàn bộ ứng dụng CMS.</p>
      
      <!-- TOC -->
      <div class="bg-indigo-50/50 rounded-2xl p-6 mb-10 border border-indigo-100">
        <h3 class="font-bold text-indigo-900 mb-3 text-sm uppercase tracking-wider">Nội dung chính</h3>
        <ul class="space-y-2 text-sm text-indigo-700">
          <li><a href="#quan-ly-tai-khoan" class="hover:underline">1. Giám sát & Quản lý Tài khoản (Users)</a></li>
          <li><a href="#tinh-nang-he-thong" class="hover:underline">2. Giao diện Cấp quyền ngang (Feature Assignments)</a></li>
          <li><a href="#truyen-thong" class="hover:underline">3. Gửi Thông Báo Hàng Loạt (Broadcasts) & Bản Tin</a></li>
          <li><a href="#co-cau" class="hover:underline">4. Cấu trúc Ban Ngành & Tổ Nhóm</a></li>
          <li><a href="#so-tay" class="hover:underline">5. Sổ Tay Hội Thánh (Biên Niên Sử)</a></li>
        </ul>
      </div>

      <!-- Section 1 -->
      <h2 id="quan-ly-tai-khoan" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">1</span> 
        Giám sát & Quản lý Tài khoản
      </h2>
      <p>Bằng cách truy cập vào Menu <code>Tài khoản & Phân quyền</code> (Dưới góc trái Màn hình Admin), bạn sẽ mở ra danh sách toàn bộ Accounts đang hoạt động.</p>
      
      <div class="my-6 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <img src="/images/docs/sysadmin-users.png" alt="Ảnh minh hoạ Giao diện Quản lý Tài khoản" class="w-full object-cover" onerror="this.onerror=null; this.outerHTML='<div class=\'aspect-video bg-gray-50 flex flex-col items-center justify-center p-6 text-center\'><svg class=\'w-12 h-12 text-gray-300 mb-2\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'></path></svg><span class=\'text-sm text-gray-500\'>Vui lòng chụp ảnh màn hình /users và lưu vào /public/images/docs/sysadmin-users.png</span></div>'">
      </div>

      <ul class="list-disc pl-5 mt-2 space-y-2">
        <li><strong>Liên kết Hồ sơ Tín hữu:</strong> Một tài khoản có thể (hoặc không) liên kết với 1 đối tượng `Member` trong CSDL.</li>
        <li><strong>Vai trò Spatie (Roles):</strong> Bạn có thể cấp các roles như `Pastor`, `Department_Lead`, v.v.</li>
        <li><strong>Sửa Mật Khẩu (Force Reset):</strong> Trong trường hợp người dùng quên mật khẩu, SuperAdmin có quyền chủ động đặt mật khẩu mới cho họ.</li>
      </ul>

      <!-- Section 2 -->
      <h2 id="tinh-nang-he-thong" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">2</span> 
        Giao diện Cấp quyền ngang (Matrix Access Control)
      </h2>
      <p>Khi bạn nhấp vào <kbd>Tính Năng</kbd> từ trang Tài khoản một người, một Bảng điều khiển (Matrix) đầy màu sắc sẽ bật lên bên phải màn hình. Đây là **Tuyệt Chiêu** của CMS này:</p>
      
      <div class="my-6 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <img src="/images/docs/sysadmin-mac.png" alt="Ảnh minh hoạ Giao diện Matrix Access Control" class="w-full object-cover" onerror="this.onerror=null; this.outerHTML='<div class=\'aspect-video bg-gray-50 flex flex-col items-center justify-center p-6 text-center\'><svg class=\'w-12 h-12 text-gray-300 mb-2\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\'></path></svg><span class=\'text-sm text-gray-500\'>Vui lòng chụp ảnh màn hình Giao diện Cấp quyền ngang và lưu vào /public/images/docs/sysadmin-mac.png</span></div>'">
      </div>

      <div class="bg-gray-50 border border-gray-200 p-6 rounded-2xl mb-6">
        <p class="text-sm text-gray-700 leading-relaxed mb-4">
          Hãy tưởng tượng tín hữu A thuộc <strong>Ban Lão Niên</strong>. Theo mặc định, do không có chức danh gì, A chỉ nhìn thấy giao diện Sinh hoạt cơ bản. Nhưng nếu Ban Lão Niên đang thiếu người làm Thủ quỹ, Mục sư nhờ A phụ giúp tính toán sổ sách.
        </p>
        <p class="text-sm text-emerald-700 font-bold">
           -> Giải pháp: Super Admin không cần gán cho A chức danh Thủ quỹ! Gán chức danh rất nguy hiểm. Super Admin chỉ cần mở thẻ Tính năng của A lên, và gạt công tắc: <strong>[Bật] - Lập báo cáo thu chi - Ban Ngành Lão Niên</strong>.
        </p>
        <p class="text-sm text-gray-700 mt-4 italic">
          Bùm! Trong tài khoản của A lập tức xuất hiện nút Menu "Quản lý Tài Chính", nhưng chức năng này cũng bị giới hạn 100% chỉ thao tác trên tiền Lão Niên.
        </p>
      </div>

      <!-- Section 3 -->
      <h2 id="truyen-thong" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">3</span> 
        Gửi Thông Báo Hàng Loạt (Broadcasts) & Bản Tin
      </h2>
      
      <div class="grid md:grid-cols-2 gap-4 mt-4">
        <div class="border border-blue-200 rounded-xl p-4 shadow-sm bg-blue-50/20">
          <h3 class="font-bold text-blue-900 mb-2">Bản Tin Hội Thánh</h3>
          <p class="text-sm text-gray-600">Những tin tức chung (VD: Chương trình Mùa Chay, Lịch dọn dẹp nhà thờ) sẽ được đăng ở mục <strong>Bản Tin</strong>. Nội dung này sẽ đập vào mắt mọi người ngay khi họ vừa đăng nhập vào Cổng Lãnh Đạo hoặc Cổng Tín Hữu.</p>
        </div>
        <div class="border border-purple-200 rounded-xl p-4 shadow-sm bg-purple-50/20">
          <h3 class="font-bold text-purple-900 mb-2">Gửi Email/Thông báo (Broadcast)</h3>
          <p class="text-sm text-gray-600">Hàng tuần, hệ thống có thể tạo các luồng Broadcast. Tính năng này giống như gửi tin nhắn Zalo hàng loạt đến tất cả các hộp thư Email đã đăng ký của tín hữu.</p>
        </div>
      </div>

      <!-- Section 4 -->
      <h2 id="co-cau" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">4</span> 
        Cấu trúc Ban Ngành & Tổ Nhóm
      </h2>
      <p>Từ mục Menu Cấu Hình Tổ Chức, bạn có thể thiết lập hàng loạt các Ban. Trong một Ban Ngành đặc thù như Thanh Niên, bạn lại có thể chia nhỏ ra thành các Tổ (Team) như Tổ 1, Tổ 2, Toán kỹ thuật, Toán hát dẫn.</p>
      <p>Điều này giúp các Trưởng ban sau này dễ dàng giao việc và lập lịch Phân công Duty Roster hơn so với việc gọi tên từng cá nhân.</p>

      <!-- Section 5 -->
      <h2 id="so-tay" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">5</span> 
        Sổ Tay Hội Thánh (Biên Niên Sử)
      </h2>
      <p>Hệ thống tự động lưu giữ **Biên Niên Sử** cho các sự kiện quan trọng để các thế hệ Chấp Sự và Quản nhiệm sau này có thể tra cứu nhanh.</p>
      
      <div class="my-6 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <img src="/images/docs/sysadmin-chronicles.png" alt="Ảnh minh hoạ Giao diện Sổ Tay Hội Thánh" class="w-full object-cover" onerror="this.onerror=null; this.outerHTML='<div class=\'aspect-video bg-gray-50 flex flex-col items-center justify-center p-6 text-center\'><svg class=\'w-12 h-12 text-gray-300 mb-2\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\'></path></svg><span class=\'text-sm text-gray-500\'>Vui lòng chụp ảnh màn hình giao diện /admin/chronicles và lưu vào /public/images/docs/sysadmin-chronicles.png</span></div>'">
      </div>

      <div class="grid md:grid-cols-2 gap-4 mt-4">
        <div class="border border-indigo-200 rounded-xl p-4 shadow-sm bg-indigo-50/20">
          <h3 class="font-bold text-indigo-900 mb-2">Tự động Lưu Nội Bộ (Auto)</h3>
          <p class="text-sm text-gray-600">Khi một thành viên kết thúc nhiệm kỳ Trưởng Ban/Thủ quỹ, hệ thống Observer tự đóng mốc và ghi nhận thời gian phục vụ của họ vào Sổ Tay.</p>
        </div>
        <div class="border border-emerald-200 rounded-xl p-4 shadow-sm bg-emerald-50/20">
          <h3 class="font-bold text-emerald-900 mb-2">Ghi Nhận Thủ Công (Manual)</h3>
          <p class="text-sm text-gray-600">Admin có thể tùy chọn Thêm Sự Kiện để nhập hình ảnh lễ Khánh thành nhà thờ, Lễ thành hôn, Lễ tang, v.v. và Tag mã định danh của Tín Hữu vào hồ sơ.</p>
        </div>
      </div>

    </article>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
