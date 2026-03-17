<template>
  <Head title="Cơ chế định tuyến Portal - Docs" />
  <DocsLayout>
    <div class="max-w-4xl mx-auto prose prose-indigo prose-slate">
      <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-6">Sơ đồ Luân Chuyển Portal</h1>
      <p class="text-lg text-slate-600 mb-8 leading-relaxed">
        Để hệ thống CMS có thể phục vụ nhiều vai trò (Tín hữu, Sinh hoạt ban ngành, Cấp lãnh đạo) trên cùng một bộ Source Code mà không bị rối, chúng tôi sử dụng cơ chế chia nhỏ thành các "Portal" độc lập.
      </p>

      <div class="grid md:grid-cols-2 gap-6 mb-10">
          <div class="bg-white border-2 border-emerald-100 rounded-2xl p-6 shadow-sm">
              <div class="text-emerald-500 text-3xl mb-3">🏠</div>
              <h3 class="font-bold text-slate-800 text-lg mb-2">Member Portal (/member)</h3>
              <p class="text-sm text-slate-600">Dành cho mọi tín hữu. Hiển thị thông tin cá nhân, sơ đồ gia đình, tin tức và theo dõi lịch trực của cá nhân đó.</p>
          </div>
          <div class="bg-white border-2 border-blue-100 rounded-2xl p-6 shadow-sm">
              <div class="text-blue-500 text-3xl mb-3">👥</div>
              <h3 class="font-bold text-slate-800 text-lg mb-2">Activities Portal (/portal)</h3>
              <p class="text-sm text-slate-600">Nơi các thành viên Ban Hướng Dẫn (Thanh Niên, Tráng Niên...) có thể kiểm tra điểm danh, lập kế hoạch chi quỹ, lên lịch sinh hoạt.</p>
          </div>
          <div class="bg-white border-2 border-purple-100 rounded-2xl p-6 shadow-sm">
              <div class="text-purple-500 text-3xl mb-3">✝️</div>
              <h3 class="font-bold text-slate-800 text-lg mb-2">Ministry Portal (/ministry)</h3>
              <p class="text-sm text-slate-600">Nơi sử dụng các công cụ diện rộng tác động lên toàn Hội Thánh: Phần mềm Thăm Viếng, Quản trị Trường Chúa Nhật.</p>
          </div>
          <div class="bg-white border-2 border-red-100 rounded-2xl p-6 shadow-sm">
              <div class="text-red-500 text-3xl mb-3">👑</div>
              <h3 class="font-bold text-slate-800 text-lg mb-2">Leadership & Admin (/deacon, /admin)</h3>
              <p class="text-sm text-slate-600">Giao diện Báo cáo cấp cao cho Mục Sự / Ban Chấp Sự và giao diện tùy chỉnh hệ thống lõi cho System Admin.</p>
          </div>
      </div>

      <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100">
          <h4 class="font-bold text-indigo-800 mb-2">Cơ chế Context Switching</h4>
          <p class="text-sm text-indigo-700 leading-relaxed mb-0">Khi một Account vừa là thành viên Thanh niên vừa thuộc Ban Truyền giáo, họ chỉ cần mở Menu góc trên bên phải để "Chuyển Đổi Không Gian Làm Việc" (Switch Context). Giao diện và các nút chức năng sẽ tự động đổi màu và ẩn/hiện theo đúng vai trò họ chọn!</p>
      </div>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';
</script>
