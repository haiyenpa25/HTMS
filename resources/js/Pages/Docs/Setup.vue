<template>
  <DocsLayout>
    <div>
      <h1 class="text-2xl font-black text-gray-900 mb-6">Khởi tạo Dữ liệu ban đầu</h1>
      <p class="text-sm text-gray-600 mb-8 max-w-2xl">
        Hệ thống được thiết kế với tính năng tự động khởi tạo dữ liệu mẫu 
        (Seed Data) giúp bạn nhanh chóng thiết lập hệ thống từ rỗng sang đầy đủ cấu trúc ban ngành, quyền hạn và danh sách tài khoản cơ bản.
      </p>

      <div class="space-y-10">
          <!-- Step 1 -->
          <div>
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-2">
              <span class="bg-orange-100 text-orange-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span> 
              Chạy lệnh Seed Dữ Liệu
            </h3>
            <p class="mt-3 text-sm text-gray-600 leading-relaxed">
              Dữ liệu của các Ban Ngành, Tính Năng và các tài khoản mẫu đã được cấu hình sẵn trong mã nguồn. Để tạo lập lại toàn bộ cấu trúc gốc (bao gồm Ban Thanh Tráng), hãy chạy lệnh sau trong Terminal (thư mục gốc dự án):
            </p>
            <div class="mt-3 bg-gray-900 rounded-xl p-4 flex items-center justify-between group">
              <code class="text-green-400 font-mono text-sm">php artisan migrate:fresh --seed</code>
              <button @click="copyCommand" class="text-gray-400 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10" title="Copy">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
              </button>
            </div>
            <p class="mt-2 text-xs text-red-500 font-medium font-bold">⚠️ Lưu ý: Lệnh này sẽ xoá toàn bộ dữ liệu hiện tại và tạo lại từ đầu.</p>
          </div>

          <!-- Step 2 -->
          <div>
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-2">
              <span class="bg-orange-100 text-orange-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span> 
              Danh Sách Tài Khoản Mẫu
            </h3>
            <p class="mt-3 text-sm text-gray-600 mb-4">Sau khi seed, bạn có thể đăng nhập bằng các tài khoản sau (Mật khẩu chung: <code class="bg-gray-100 text-orange-600 px-2 py-0.5 rounded font-bold">Abc.1234</code>):</p>
            
            <div class="grid gap-4 md:grid-cols-2">
              <div class="border border-gray-200 rounded-2xl p-4 hover:border-orange-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center font-black">SA</div>
                  <div>
                    <h4 class="font-bold text-gray-900">Quản Trị Tối Cao</h4>
                    <p class="text-xs text-gray-500">Toàn quyền hệ thống</p>
                  </div>
                </div>
                <div class="bg-gray-50 p-2 rounded-lg text-sm text-gray-700 font-mono mt-3">superadmin@httlthanhmyloi.com</div>
              </div>

              <div class="border border-gray-200 rounded-2xl p-4 hover:border-orange-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center font-black">TB</div>
                  <div>
                    <h4 class="font-bold text-gray-900">Trưởng Ban (Thanh Tráng)</h4>
                    <p class="text-xs text-gray-500">Quyền quản lý ban ngành</p>
                  </div>
                </div>
                <div class="bg-gray-50 p-2 rounded-lg text-sm text-gray-700 font-mono mt-3">tb.thanhtrang@httlthanhmyloi.com</div>
              </div>

              <div class="border border-gray-200 rounded-2xl p-4 hover:border-orange-300 hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center font-black">TK</div>
                  <div>
                    <h4 class="font-bold text-gray-900">Thư Ký (Thanh Tráng)</h4>
                    <p class="text-xs text-gray-500">Quyền điểm danh, báo cáo</p>
                  </div>
                </div>
                <div class="bg-gray-50 p-2 rounded-lg text-sm text-gray-700 font-mono mt-3">tk.thanhtrang@httlthanhmyloi.com</div>
              </div>
            </div>
          </div>

          <!-- Step 3 -->
          <div>
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-2">
              <span class="bg-orange-100 text-orange-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">3</span> 
              Cấu trúc Ban Ngành được khởi tạo
            </h3>
            <div class="mt-4 bg-gray-50 rounded-2xl p-5 border border-gray-100 text-sm text-gray-700">
              <ul class="space-y-4">
                <li class="flex items-start gap-3">
                  <span class="text-blue-500 mt-0.5">🔹</span>
                  <div>
                    <p class="font-bold text-gray-900">Khối Lãnh Đạo</p>
                    <p class="text-gray-500 text-xs mt-1">Ban Chấp Sự, Toàn Hội Thánh</p>
                  </div>
                </li>
                <li class="flex items-start gap-3">
                  <span class="text-emerald-500 mt-0.5">🔹</span>
                  <div>
                    <p class="font-bold text-gray-900">Khối Sinh Hoạt</p>
                    <p class="text-gray-500 text-xs mt-1">Ban Lão niên, Trung tráng niên, <strong>Ban Thanh Tráng</strong>, Ban Thanh niên...</p>
                  </div>
                </li>
                <li class="flex items-start gap-3">
                  <span class="text-purple-500 mt-0.5">🔹</span>
                  <div>
                    <p class="font-bold text-gray-900">Khối Mục Vụ (Nội Bộ)</p>
                    <p class="text-gray-500 text-xs mt-1">Ban Chứng Đạo, Ban Âm Nhạc, Ban Cầu Nguyện...</p>
                  </div>
                </li>
              </ul>
            </div>
          </div>

          <!-- Step 4: MAC V2 Permissions Guide -->
          <div>
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-2">
              <span class="bg-orange-100 text-orange-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">4</span> 
              Kiến trúc Phân quyền Đa tầng (MAC V2)
            </h3>
            <p class="mt-3 text-sm text-gray-600 leading-relaxed mb-4">
              Hệ thống sử dụng cơ chế <strong class="text-indigo-600">Multi-Tier Matrix Access Control V2</strong> để quản lý linh hoạt từng module tính năng. Quản trị viên chỉ cần thiết lập thông qua giao diện ma trận mà không cần can thiệp vào Source Code.
            </p>

            <div class="grid lg:grid-cols-2 gap-6 mb-6">
                <!-- Concept 1 -->
                <div class="bg-white border-2 border-purple-100 rounded-2xl p-5 shadow-sm">
                    <h4 class="font-black text-purple-800 text-base mb-2 flex items-center gap-2">
                        <span>1️⃣</span> Lớp Gán Chức Năng (Assignment)
                    </h4>
                    <p class="text-xs text-gray-600 mb-3">Quyết định xem <strong>Ban nào được phép nhìn thấy tính năng nào</strong> trên thanh Menu Portal.</p>
                    <ul class="text-xs space-y-2 text-gray-700">
                        <li class="flex gap-2">
                            <span class="text-purple-500">🌐</span> 
                            <div><strong>Global (Tất cả Ban Ngành):</strong> Bật tính năng cho toàn bộ hệ thống (VD: Hồ sơ cá nhân).</div>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-purple-500">📋</span> 
                            <div><strong>Block (Theo Loại Ban):</strong> Bật tự động cho một khối (VD: Tính năng Điểm danh cho mọi ban Sinh Hoạt).</div>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-purple-500">🎯</span> 
                            <div><strong>Specific (Ban Cụ Thể):</strong> Chỉ gán riêng lẻ (VD: Quản lý Ca đoàn chỉ cho Ban Âm nhạc).</div>
                        </li>
                    </ul>
                </div>

                <!-- Concept 2 -->
                <div class="bg-white border-2 border-indigo-100 rounded-2xl p-5 shadow-sm">
                    <h4 class="font-black text-indigo-800 text-base mb-2 flex items-center gap-2">
                        <span>2️⃣</span> Lớp Tầm Nhìn Dữ Liệu (Data Scope)
                    </h4>
                    <p class="text-xs text-gray-600 mb-3">Quyết định xem <strong>sau khi truy cập tính năng, người dùng được xem những dữ liệu gì</strong>.</p>
                    <ul class="text-xs space-y-2 text-gray-700">
                        <li class="flex gap-2">
                            <span class="text-indigo-500">🌐</span> 
                            <div><strong>Global:</strong> Xem dữ liệu của toàn Hội Thánh (Dành cho Lãnh Đạo).</div>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-indigo-500">🏢</span> 
                            <div><strong>Department:</strong> Chỉ được xem dữ liệu thuộc về Ban đang truy cập.</div>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-indigo-500">👥</span> 
                            <div><strong>Group:</strong> Bị giới hạn ở khu vực hoặc tổ nhỏ mình phụ trách.</div>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-indigo-500">👤</span> 
                            <div><strong>Self:</strong> Bị giới hạn hoàn toàn, chỉ thấy dữ liệu cá nhân mình tạo.</div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Workflow Guide -->
            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                <h4 class="font-black text-slate-800 text-base mb-4">Các bước Cấu hình trên hệ thống</h4>
                <div class="space-y-4">
                    <div class="flex gap-4 items-start">
                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 font-bold flex items-center justify-center shrink-0">1</div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Đăng nhập tài khoản Quản trị <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded text-xs ml-1">SA</span></p>
                            <p class="text-xs text-slate-500 mt-1">Truy cập vào Admin Portal.</p>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 font-bold flex items-center justify-center shrink-0">2</div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Trình đơn Cài Đặt Hệ Thống > Tính Năng</p>
                            <p class="text-xs text-slate-500 mt-1">Chọn một tính năng từ cột bên trái (Ví dụ: Thăm Viếng).</p>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 font-bold flex items-center justify-center shrink-0">3</div>
                        <div class="w-full">
                            <p class="text-sm font-bold text-slate-800 mb-2">Tiến hành cấu hình 3 bước như hình bên dưới</p>
                            <img src="/images/docs/data_scope_section.png" alt="Admin UI config for Data Scope" class="rounded-xl border border-slate-200 shadow-sm w-full object-cover">
                            <p class="text-xs text-slate-400 font-italic mt-2 text-center">Giao diện cấu hình phạm vi và tầm nhìn tính năng trong Admin Portal.</p>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 font-bold flex items-center justify-center shrink-0">4</div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Cấu hình Nâng cao trong "Bảng Ma Trận"</p>
                            <p class="text-xs text-slate-500 mt-1">Nếu có một Ban ngọai lệ cần bật/tắt chức năng dù Global/Block đã cài đặt, hãy sang tab Bảng Ma Trận để toggle On/Off bằng tay.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Case Studies -->
            <div class="mt-8">
                <h4 class="font-black text-slate-800 text-base mb-4 border-b border-slate-200 pb-2">Ví dụ Áp Dụng Thực Tế (Case Studies)</h4>
                <div class="grid md:grid-cols-3 gap-4">
                    <!-- Case 1 -->
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">1</span>
                            <h5 class="font-bold text-slate-800 text-sm">Thăm Viếng Toàn Nhà Thờ</h5>
                        </div>
                        <p class="text-xs text-slate-600 mb-3"><strong>Bài toán:</strong> Cấp quyền cho Ban Thăm Viếng xem toàn bộ hồ sơ đi thăm của cả Hội Thánh.</p>
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-500">Phạm vi gán:</span>
                                <span class="font-bold text-slate-800">Ban Thăm Viếng (🎯 Specific)</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500">Tầm nhìn dữ liệu:</span>
                                <span class="font-bold text-indigo-600 flex items-center gap-1">🌐 Global</span>
                            </div>
                        </div>
                    </div>

                    <!-- Case 2 -->
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">2</span>
                            <h5 class="font-bold text-slate-800 text-sm">Điểm Danh Nội Bộ Ban</h5>
                        </div>
                        <p class="text-xs text-slate-600 mb-3"><strong>Bài toán:</strong> Ban sinh hoạt (Thanh Niên, Tráng Niên...) tự điểm danh mà không thấy dữ liệu của ban khác.</p>
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-500">Phạm vi gán:</span>
                                <span class="font-bold text-slate-800">Mọi ban sinh hoạt (📋 Block)</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500">Tầm nhìn dữ liệu:</span>
                                <span class="font-bold text-indigo-600 flex items-center gap-1">🏢 Department</span>
                            </div>
                        </div>
                    </div>

                    <!-- Case 3 -->
                    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold">3</span>
                            <h5 class="font-bold text-slate-800 text-sm">Quản Lý Nhóm Nhỏ / Khu Vực</h5>
                        </div>
                        <p class="text-xs text-slate-600 mb-3"><strong>Bài toán:</strong> Trưởng nhóm nhỏ chỉ được quyền xem thành viên trong khu vực tài phán của mình.</p>
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-500">Phạm vi gán:</span>
                                <span class="font-bold text-slate-800">Đặc quyền cá nhân (🎯 Specific)</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500">Tầm nhìn dữ liệu:</span>
                                <span class="font-bold text-indigo-600 flex items-center gap-1">👥 Group</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

          </div>

          <div class="pt-6 border-t border-gray-100">
            <a href="/login" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-black text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors">
              Đến Trang Đăng Nhập
            </a>
          </div>
      </div>
    </div>
  </DocsLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DocsLayout from '@/Layouts/DocsLayout.vue';

const copyCommand = () => {
    navigator.clipboard.writeText('php artisan migrate:fresh --seed');
    alert('Đã copy lệnh!');
};
</script>
