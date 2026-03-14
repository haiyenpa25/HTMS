<template>
  <Head title="Hướng dẫn: Tài Chính & Quỹ Nội Bộ" />

  <DocsLayout>
    <div class="max-w-4xl mx-auto prose prose-indigo">
      <h1 class="text-3xl font-black text-gray-900 mb-6 tracking-tight">5. Quản Lý Tài Chính & Quỹ Phụ (Finance)</h1>
      <p class="text-lg text-gray-500 mb-8">Nghiệp vụ "Double-entry Accounting" thu gọn, đảm bảo mọi khoản Dâng hiến và Chi tiêu đều có lưu vết, minh bạch 100%.</p>
      
      <div class="my-6 border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        <img src="/images/docs/dept-finance.png" alt="Ảnh minh hoạ Giao diện Tài chính ban ngành" class="w-full object-cover">
      </div>

      <p class="text-sm text-gray-700">Tính năng này sinh ra đặc quyền cho người mang Role <strong>Thủ Quỹ</strong> của Ban ngành. Thủ quỹ Ban ngành chỉ thấy tiền của Ban mình, bị cô lập hoàn toàn với Quỹ Tổng của Hội Thánh.</p>

      <h2 class="text-xl font-bold text-gray-900 mt-10 mb-4">Giải Quyết Bài Toán Thu Chi</h2>
      
      <div class="bg-gray-50 border border-gray-100 p-6 rounded-2xl mb-8">
        <ul class="list-disc pl-5 space-y-4 text-sm text-gray-700">
          <li>
            <strong>Số Dư Quỹ (Funds/Accounts):</strong> <br/>
            Hệ thống quản lý theo cơ chế Đa Quỹ. Thủ Quỹ có thể tạo các hòm Quỹ Tách Biệt: "Quỹ Ban Thanh Niên", "Quỹ Trại Hè", "Quỹ Khuyến Học". 
          </li>
          <li>
            <strong>Tạo Phiếu Thu (Incomes):</strong> <br/>
            Thu tiền lạc quyên (Dâng hiến). Đặc biệt, bạn có thể gán nguồn tiền này lấy từ thẻ của một Tín Đồ cụ thể (Để lập bảng danh sách Tri Ân dâng hiến vinh danh cuối năm nếu cần). Và tất nhiên, mỗi phiếu bắt buộc phải chọn "Tiền chảy vào Quỹ Nào?". Doanh thu tự động cộng dồn.
          </li>
          <li>
            <strong>Tạo Phiếu Chi (Expenses):</strong> <br/>
            Chi trả tiền điện nước, photo, mua quà tân gia, ăn trưa... Ghi cực rõ ngày tháng, người thụ hưởng, và số tiền đó rút từ "Quỹ nào?". Từ đó tránh lạm chi (Chi bậy).
          </li>
          <li>
            <strong>Rút Quỹ nội bộ (Transfers):</strong> <br/>
            Di dời tiền dư từ Quỹ Ban ngành sang Quỹ Thăm Viếng nếu tháng đó Quỹ Thăm Viếng hết tiền.
          </li>
        </ul>
      </div>

      <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mt-8 rounded-r-lg">
        <p class="text-sm font-bold text-amber-800 mb-1">Cảnh Báo Tuân Thủ Kế Toán</p>
        <p class="text-sm text-amber-700 mb-0">Hệ thống áp dụng phương thức cứng nhắc: Bạn KHÔNG THỂ XÓA phiếu Thu / Phiếu Chi một khi đã lưu. Thay vì xóa, nếu nhập sai, bạn chọn tính năng "Void" (Hủy chứng từ) hoặc lập Phiếu Đảo Ngược (Bù trừ) để lại dấu vết rõ ràng.</p>
      </div>

      <div class="mt-10 flex justify-between">
        <Link :href="route('help.departments.assignments')" class="px-6 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition">
          ⬅️ 4. Phân Công
        </Link>
        <Link :href="route('help.departments.reports')" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-sm hover:bg-indigo-700 transition">
          Tiếp theo: 6. Báo Cáo ➡️
        </Link>
      </div>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
