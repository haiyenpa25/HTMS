<template>
  <DocsLayout>
    <Head title="Docs - Tổng Quan" />
    <article class="prose prose-slate max-w-none">
      <h1 class="text-3xl font-black text-gray-900 mb-2">Tổng Quan Hệ Thống</h1>
      <p class="text-lg text-gray-500 mb-8">Hiểu về nguyên lý hoạt động, cấu trúc Cổng (Portal) và Phân quyền (MAC) trong hệ thống CMS quản trị Hội Thánh.</p>
      
      <!-- TOC -->
      <div class="bg-indigo-50/50 rounded-2xl p-6 mb-10 border border-indigo-100">
        <h3 class="font-bold text-indigo-900 mb-3 text-sm uppercase tracking-wider">Nội dung chính</h3>
        <ul class="space-y-2 text-sm text-indigo-700">
          <li><a href="#kien-truc-portal" class="hover:underline">1. Kiến trúc Cổng không gian (Portals)</a></li>
          <li><a href="#matrix-access-control" class="hover:underline">2. Hệ thống Phân Quyền Ma Trận (MAC)</a></li>
        </ul>
      </div>

      <!-- Section 1 -->
      <h2 id="kien-truc-portal" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">1</span> 
        Kiến trúc Cổng không gian (Portals)
      </h2>
      <p>Hệ thống được thiết kế dựa trên nguyên lý <strong>"Phân tán không gian làm việc"</strong>. Tương ứng với cấu trúc tổ chức của Hội Thánh, dữ liệu và màn hình làm việc được chia thành 3 nhóm cổng (Portals) chính:</p>
      
      <div class="grid md:grid-cols-3 gap-4 my-6">
        <div class="border border-blue-200 bg-blue-50/50 rounded-xl p-5">
          <h3 class="text-blue-900 font-bold mb-2">1. Lãnh Đạo (Leadership)</h3>
          <p class="text-sm text-blue-800">Dành cho Mục sư và Ban Chấp Sự. Cho phép xem báo cáo tổng hợp, dữ liệu tài chính cấp Hội Thánh, và ra quyết định chiến lược toàn cục.</p>
        </div>
        <div class="border border-emerald-200 bg-emerald-50/50 rounded-xl p-5">
          <h3 class="text-emerald-900 font-bold mb-2">2. Sinh Hoạt (Activities)</h3>
          <p class="text-sm text-emerald-800">Dành cho các ban đặc thù theo lứa tuổi: <strong>Thanh Tráng, Thanh Niên, Lão Niên</strong>. Tập trung vào việc quản lý nhân sự thành viên, điểm danh, và quỹ nội bộ.</p>
        </div>
        <div class="border border-purple-200 bg-purple-50/50 rounded-xl p-5">
          <h3 class="text-purple-900 font-bold mb-2">3. Mục Vụ (Ministry)</h3>
          <p class="text-sm text-purple-800">Dành cho các ban chuyên đề trực tiếp phục vụ chương trình lễ: <strong>Âm Nhạc, Cầu Nguyện, Tiếp Tân</strong>. Tập trung vào phân công lịch trực và sự kiện.</p>
        </div>
      </div>

      <p><em>Lưu ý: Bạn chỉ có thể nhìn thấy menu của Ban ngành nào nếu bạn được phân công làm việc (Là Trưởng ban, thư ký, v.v.) vào Ban ngành đó.</em></p>

      <!-- Section 2 -->
      <h2 id="matrix-access-control" class="text-xl font-bold text-gray-900 pb-2 border-b border-gray-100 mt-10 mb-4 flex items-center gap-2">
        <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm">2</span> 
        Hệ thống Phân Quyền Ma Trận (MAC)
      </h2>

      <p>Hệ thống không sử dụng các Role cố định kiểu cũ (Admin, User) mà sử dụng hệ thống bảo mật <strong>Matrix Access Control (Điều khiển Truy cập Ma trận)</strong>. Bao gồm 2 lớp (Tier) linh hoạt:</p>

      <div class="bg-gray-50 border border-gray-200 p-6 rounded-2xl mb-6">
        <h4 class="font-bold text-gray-900 text-lg mb-2">Tier 1: Vùng kiểm soát (Department Level)</h4>
        <p class="text-sm text-gray-600 mb-4">Đầu tiên, hệ thống xác nhận bạn thuộc Ban ngành nào. Ví dụ, tài khoản `tb.thanhtrang` được gán làm Trưởng Ban Thanh Tráng. Do đó, tài khoản này sẽ vĩnh viễn không thể xem dữ liệu (tài chính, quỹ, điểm danh) của Ban Thanh Niên, dù cố tình gõ đường dẫn URL.</p>
        
        <h4 class="font-bold text-gray-900 text-lg mb-2 pt-4 border-t border-gray-200">Tier 2: Tính năng chi tiết (Feature Whitelist)</h4>
        <p class="text-sm text-gray-600">Bên trong nhóm Ban Thanh tráng, tài khoản sẽ được cấp phép sử dụng <strong>từng tính năng cụ thể (Features)</strong>. Một Quản trị viên Tối cao (SuperAdmin) có thể bật/tắt quyền truy cập của bất kỳ ai vào các tính năng như:</p>
        <ul class="list-disc pl-5 mt-2 text-sm text-gray-600 space-y-1">
          <li><code>Attendance</code> (Được phép điểm danh)</li>
          <li><code>Finance</code> (Được phép lập phiếu thu quỹ, chi quỹ)</li>
          <li><code>Reports</code> (Được phép tổng hợp báo cáo)</li>
        </ul>
      </div>

      <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl">
        <p class="text-sm text-emerald-800 m-0"><strong>Chốt lại:</strong> Nhờ MAC, Hội Thánh có thể cấp quyền siêu linh hoạt. Bạn có thể cho phép một người chỉ là thành viên bình thường nhưng vẫn vào được chức năng <em>"Điểm danh Thanh Tráng"</em> để phụ giúp Thư ký, mà không sợ người đó dòm ngó vào <em>"Quỹ Thanh Tráng"</em>.</p>
      </div>

    </article>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
