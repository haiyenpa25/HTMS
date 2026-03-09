<template>
  <PortalLayout
      :department="department"
      :available-departments="availableDepartments"
      :is-global-admin="isGlobalAdmin"
      portal-type="deacon"
      @open-switcher="isSwitchOpen = true"
  >
    <div class="py-4 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Print-only letterhead -->
      <div class="print-only hidden print-letterhead">
        <h1>HỘI THÁNH TIN LÀNH THANH MỸ LỢI</h1>
        <p>BÁO CÁO THƯ KÝ HỘI THÁNH</p>
        <p>Tháng {{ localMonth }}/{{ localYear }}</p>
      </div>

      <!-- ══ HEADER ══ -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 no-print">
        <div>
          <h2 class="text-xl font-black text-gray-900">📑 BÁO CÁO HỘI THÁNH</h2>
          <p class="text-xs text-gray-500 mt-0.5">Thư Ký Hội Thánh · Tháng {{ localMonth }}/{{ localYear }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-sm">
            <select v-model="localMonth" @change="updatePeriod" class="text-sm font-medium text-gray-700 border-none focus:ring-0 p-0">
              <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
            </select>
            <input v-model="localYear" @change="updatePeriod" type="number"
              class="w-16 text-sm border-none focus:ring-0 p-0 text-center font-medium" min="2020" max="2099" />
          </div>
          <div class="flex items-center gap-2" v-if="report">
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold"
              :class="report.status==='approved'?'bg-green-100 text-green-800':report.status==='submitted'?'bg-amber-100 text-amber-800':'bg-gray-100 text-gray-600'">
              {{ statusLabel(report.status) }}
            </span>
            <span v-if="report.unlock_requested" class="px-2 py-1 bg-red-100 text-red-700 text-[10px] rounded-lg font-bold animate-pulse">🔓 Xin mở khoá</span>
          </div>

          <div class="flex items-center gap-2">
            <!-- Edit button (Thư ký nếu chưa khoá, Leader thì có thể thoải mái) -->
            <button v-if="!isLocked || isLeader" @click="openReportForm" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
              ✏️ {{ report ? 'Cập nhật BC' : 'Lập Báo cáo' }}
            </button>
            
            <!-- Submit (Thư ký) -->
            <button v-if="report && report.status === 'draft' && !isLeader" @click="updateReportStatus('submit')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
              📤 Nộp
            </button>

            <!-- Approve/Lock (Leader) -->
            <button v-if="report && report.status !== 'approved' && isLeader" @click="updateReportStatus('approve')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
              🔒 Phê Duyệt & Khoá
            </button>

            <!-- Request Unlock (Thư ký) -->
            <button v-if="report && report.status === 'approved' && !report.unlock_requested && !isLeader" @click="updateReportStatus('request_unlock')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
              🔓 Xin mở khoá
            </button>

            <!-- Approve Unlock (Leader) -->
            <button v-if="report && report.unlock_requested && isLeader" @click="updateReportStatus('approve_unlock')" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl shadow-sm transition">
              ✅ Duyệt mở khoá
            </button>

            <!-- Print button -->
            <button @click="printReport" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-800 text-white text-sm font-bold rounded-xl hover:bg-gray-900 transition-colors shadow-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
              In / PDF
            </button>
          </div>
        </div>
      </div>

      <!-- ══ KPI CARDS ══ -->

      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div v-for="card in kpiCards" :key="card.label" class="rounded-2xl p-4 shadow-sm" :class="card.bg">
          <p class="text-[10px] font-bold uppercase tracking-wider" :class="card.labelColor">{{ card.label }}</p>
          <p class="text-2xl font-black mt-1" :class="card.valueColor">{{ card.value }}</p>
          <div v-if="card.change !== undefined" class="mt-2 flex items-center gap-1.5">
            <span class="text-xs font-bold px-2 py-0.5 rounded-full"
              :class="card.change >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
              {{ card.change >= 0 ? '▲' : '▼' }} {{ Math.abs(card.change) }}%
            </span>
          </div>
          <p v-else class="text-[10px] mt-1" :class="card.subColor">{{ card.sub }}</p>
        </div>
      </div>

      <!-- ══ SECTION A: BUỔI NHÓM HỘI THÁNH ══ -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-blue-900 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-black text-white">A. BUỔI NHÓM HỘI THÁNH</h3>
            <p class="text-[10px] text-blue-300">Số lượng tham dự từng buổi trong tháng</p>
          </div>
          <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">
            {{ church_meetings.length }} buổi · TB {{ summary.avg_church ?? 0 }}
          </span>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">
          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-blue-50">
                <tr>
                  <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900">Ngày</th>
                  <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900">Chủ đề</th>
                  <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900 hidden md:table-cell">Diễn giả</th>
                  <th class="px-4 py-2.5 text-center text-xs font-bold text-blue-900">HD</th>
                  <th class="px-4 py-2.5 text-center text-xs font-bold text-blue-900 hidden sm:table-cell">Online</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="m in church_meetings" :key="m.id" class="hover:bg-blue-50/50">
                  <td class="px-4 py-2.5 whitespace-nowrap">
                    <p class="text-xs font-black text-gray-900">{{ m.date }}</p>
                    <p class="text-[10px] text-gray-400 capitalize">{{ m.day }}</p>
                  </td>
                  <td class="px-4 py-2.5 text-xs text-gray-800 max-w-[120px] truncate">{{ m.topic || '—' }}</td>
                  <td class="px-4 py-2.5 text-xs text-gray-600 hidden md:table-cell">{{ m.speaker || '—' }}</td>
                  <td class="px-4 py-2.5 text-center text-sm font-black text-amber-700">{{ m.attendance > 0 ? m.attendance : '—' }}</td>
                  <td class="px-4 py-2.5 text-center text-xs font-medium text-blue-600 hidden sm:table-cell">{{ m.online > 0 ? m.online : '—' }}</td>
                </tr>
                <tr v-if="church_meetings.length === 0">
                  <td colspan="5" class="px-4 py-8 text-center text-xs text-gray-400">Chưa có buổi nhóm HT nào trong tháng</td>
                </tr>
                <tr class="bg-blue-900/5 border-t-2 border-blue-200">
                  <td colspan="2" class="px-4 py-2.5 text-xs font-black text-blue-900">TRUNG BÌNH</td>
                  <td class="px-4 py-2.5 hidden md:table-cell"></td>
                  <td class="px-4 py-2.5 text-center text-sm font-black text-amber-700">{{ summary.avg_church ?? '—' }}</td>
                  <td class="px-4 py-2.5 hidden sm:table-cell"></td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- Chart -->
          <div class="p-5">
            <p class="text-xs font-bold text-gray-700 mb-3">📈 Biểu Đồ Hiện Diện Theo Buổi</p>
            <div v-if="church_meetings.length > 0">
              <apexchart type="area" height="230" :options="churchChartOpts" :series="churchSeries" />
            </div>
            <div v-else class="h-52 flex flex-col items-center justify-center text-gray-300 gap-2">
              <span class="text-3xl">📭</span>
              <p class="text-xs">Không có dữ liệu</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ SECTION B: YOUTUBE ══ -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-red-800 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-black text-white">B. THỐNG KÊ YOUTUBE</h3>
            <p class="text-[10px] text-red-300">Số liệu kênh YouTube Hội Thánh tháng này</p>
          </div>
          <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">Tháng {{ localMonth }}/{{ localYear }}</span>
        </div>
        <div class="p-5">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
            <div v-for="field in youtubeFields" :key="field.key">
              <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">{{ field.label }}</label>
              <input
                v-model.number="ytForm[field.key]"
                type="number" min="0"
                class="block w-full text-center text-xl font-black border-2 border-gray-200 rounded-xl py-3 focus:ring-2 focus:border-red-400 focus:ring-red-100 transition-all"
                :class="field.color"
                placeholder="0"
              />
            </div>
          </div>
          <div class="flex justify-end" v-if="!isLocked || isLeader">
            <button @click="saveYoutube" :disabled="ytSaving"
              class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl shadow-sm disabled:opacity-50 transition-colors flex items-center gap-2">
              <svg v-if="ytSaving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ ytSaving ? 'Đang lưu...' : 'Lưu số liệu YouTube' }}
            </button>
          </div>

          <!-- YT chart (line trend 3 months) -->
          <div v-if="yt_trend_series.length > 0" class="mt-4">
            <p class="text-xs font-bold text-gray-700 mb-2">📈 Xu Hướng Đăng Ký 3 Tháng Gần Nhất</p>
            <apexchart type="line" height="200" :options="ytTrendOpts" :series="yt_trend_series" />
          </div>
        </div>
      </div>

      <!-- ══ SECTION C: SO SÁNH BIỂU ĐỒ THÁNG ══ -->
      <div v-if="monthly_trend.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-bold text-gray-900 mb-1">📊 C. Xu Hướng Tham Dự 6 Tháng Gần Nhất</h3>
        <p class="text-[10px] text-gray-400 mb-4">Trung bình số người tham dự buổi nhóm Hội Thánh mỗi tháng</p>
        <apexchart type="bar" height="220" :options="trendBarOpts" :series="trendBarSeries" />
      </div>

      <!-- ══ SECTION D: SỰ CỐ ══ -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-rose-800 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-black text-white">D. SỰ CỐ & KHẮC PHỤC</h3>
            <p class="text-[10px] text-rose-300">Ghi nhận sự cố và giải pháp trong tháng</p>
          </div>
          <button v-if="!isLocked || isLeader" @click="openIncidentForm(null)"
            class="bg-white/20 hover:bg-white/30 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Thêm sự cố
          </button>
        </div>

        <!-- Incident list -->
        <div v-if="localIncidents.length > 0" class="divide-y divide-gray-100">
          <div v-for="incident in localIncidents" :key="incident.id"
            class="px-5 py-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-[10px] font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ incident.week_label }}</span>
                  <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                    :class="incident.status==='resolved' ? 'bg-green-100 text-green-700' : incident.status==='in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'">
                    {{ statusIncidentLabel(incident.status) }}
                  </span>
                </div>
                <p class="text-sm font-bold text-gray-900">{{ incident.description }}</p>
                <p v-if="incident.resolution" class="text-xs text-gray-500 mt-1">📌 Giải pháp: {{ incident.resolution }}</p>
                <p v-if="incident.direction" class="text-xs text-blue-600 mt-0.5">→ {{ incident.direction }}</p>
              </div>
              <div class="flex items-center gap-2 shrink-0" v-if="!isLocked || isLeader">
                <button @click="openIncidentForm(incident)"
                  class="w-7 h-7 rounded-full bg-gray-100 hover:bg-blue-50 hover:text-blue-600 text-gray-400 flex items-center justify-center transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                  </svg>
                </button>
                <button @click="deleteIncident(incident.id)"
                  class="w-7 h-7 rounded-full bg-gray-100 hover:bg-red-50 hover:text-red-600 text-gray-400 flex items-center justify-center transition-colors">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="px-5 py-10 text-center">
          <p class="text-3xl mb-2">✅</p>
          <p class="text-sm text-gray-400">Không có sự cố nào trong tháng {{ localMonth }}/{{ localYear }}</p>
        </div>
      </div>

      <!-- ══ AI + NARRATIVE ══ -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-gradient-to-br from-violet-700 to-purple-800 rounded-2xl p-5 shadow-xl text-white text-xs leading-relaxed">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center text-base">🤖</div>
            <div>
              <p class="text-sm font-black">AI Phân Tích Tự Động</p>
              <p class="text-[10px] text-purple-300">Dựa trên dữ liệu thực tế tháng {{ localMonth }}/{{ localYear }}</p>
            </div>
          </div>
          <div class="space-y-3">
            <div class="bg-white/10 rounded-xl p-3">
              <p class="font-bold mb-1">⛪ Sinh Hoạt Hội Thánh</p>
              <p>• <strong>{{ church_meetings.length }} buổi nhóm</strong>, TB hiện diện: <strong>{{ summary.avg_church ?? '—' }}</strong></p>
              <p v-if="summary.church_change !== undefined">
                So tháng trước:
                <span :class="summary.church_change >= 0 ? 'text-green-300' : 'text-red-300'">
                  {{ summary.church_change >= 0 ? '▲' : '▼' }} {{ Math.abs(summary.church_change) }}%
                </span>
              </p>
            </div>
            <div class="bg-white/10 rounded-xl p-3">
              <p class="font-bold mb-1">▶️ YouTube</p>
              <p>• Đăng ký: <strong>{{ ytForm.subscribers_current }}</strong> · Mới: <strong>+{{ ytForm.subscribers_new }}</strong></p>
              <p>• Lượt xem: <strong>{{ ytForm.views }}</strong> · Giờ xem: <strong>{{ ytForm.watch_hours }}h</strong></p>
            </div>
            <div class="bg-white/10 rounded-xl p-3">
              <p class="font-bold mb-1">🔧 Sự Cố</p>
              <p v-if="localIncidents.length === 0">✅ Không có sự cố trong tháng.</p>
              <template v-else>
                <p>• Tổng {{ localIncidents.length }} sự cố,
                  <span class="text-green-300">{{ localIncidents.filter(i => i.status === 'resolved').length }} đã xử lý</span>,
                  <span class="text-amber-300">{{ localIncidents.filter(i => i.status !== 'resolved').length }} đang xử lý</span>
                </p>
              </template>
            </div>
            <div v-if="report?.evaluation" class="border-t border-white/20 pt-3">
              <p class="text-purple-300 font-bold text-[10px] uppercase mb-1">Nhận Xét Thư Ký</p>
              <p class="italic">"{{ report.evaluation }}"</p>
            </div>
          </div>
        </div>

        <!-- Report notes panel -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-900">📋 Nhận Xét & Kế Hoạch</h3>
            <button v-if="!isLocked || isLeader" @click="openReportForm" class="text-xs font-bold text-purple-600 hover:text-purple-800">
              {{ report ? 'Chỉnh sửa' : 'Lập báo cáo' }} →
            </button>
          </div>
          <div class="p-4 space-y-3 flex-1">
            <template v-if="report">
              <p v-if="report.reporter_name" class="text-xs text-gray-500 font-medium">👤 {{ report.reporter_name }}</p>
              <div v-if="report.evaluation">
                <p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Nhận xét</p>
                <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-2.5">{{ report.evaluation }}</p>
              </div>
              <div v-if="report.proposals">
                <p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Đề nghị / Kế hoạch</p>
                <p class="text-sm text-gray-700 bg-blue-50 rounded-lg p-2.5">{{ report.proposals }}</p>
              </div>
              <div v-if="report.notes">
                <p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Ghi chú</p>
                <p class="text-sm text-gray-700 bg-purple-50 rounded-lg p-2.5">{{ report.notes }}</p>
              </div>
            </template>
            <div v-else class="flex-1 flex flex-col items-center justify-center py-10">
              <p class="text-3xl mb-2">📝</p>
              <p class="text-sm text-gray-400">Chưa có báo cáo tháng {{ localMonth }}/{{ localYear }}</p>
              <button @click="openReportForm" class="mt-3 px-4 py-2 bg-purple-600 text-white text-xs font-bold rounded-xl">Lập Báo Cáo</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Print-only AI summary & report notes for print -->
      <div class="print-only hidden" v-if="report || church_meetings.length > 0">
        <div class="bg-white border border-gray-200 rounded-xl p-5 mt-4">
          <h3 class="font-black text-gray-900 text-sm uppercase border-b pb-2 mb-3">TÓM TẮT TÌNH HÌNH</h3>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="font-bold text-gray-500 text-[9pt] uppercase mb-1">Buổi Nhóm Hội Thánh</p>
              <p class="text-gray-800">{{ church_meetings.length }} buổi · TB {{ summary.avg_church ?? 0 }} người</p>
            </div>
            <div>
              <p class="font-bold text-gray-500 text-[9pt] uppercase mb-1">YouTube</p>
              <p class="text-gray-800">Đăng ký: {{ ytForm.subscribers_current }} (+{{ ytForm.subscribers_new }}) · Xem: {{ ytForm.views }}’</p>
            </div>
            <div v-if="report?.evaluation">
              <p class="font-bold text-gray-500 text-[9pt] uppercase mb-1">Nhận xét</p>
              <p class="text-gray-800">{{ report.evaluation }}</p>
            </div>
            <div v-if="report?.proposals">
              <p class="font-bold text-gray-500 text-[9pt] uppercase mb-1">Đề nghị / Kế hoạch</p>
              <p class="text-gray-800">{{ report.proposals }}</p>
            </div>
          </div>
          <p v-if="report?.reporter_name" class="text-xs text-gray-400 mt-3">Người báo cáo: <strong>{{ report.reporter_name }}</strong></p>
        </div>

        <!-- Print signature footer -->
        <div class="print-footer mt-6">
          <div class="sign-block">
            <p class="sign-label">Thư Ký Hội Thánh</p>
            <div class="sign-line"></div>
            <p class="mt-1 text-xs text-gray-500">{{ report?.reporter_name || '...................' }}</p>
          </div>
          <div class="sign-block">
            <p class="sign-label">Mục Sư / Quản Nhiệm</p>
            <div class="sign-line"></div>
            <p class="mt-1 text-xs text-gray-500">Ký tên, đóng dấu</p>
          </div>
        </div>
      </div>

    </div>

    <!-- ══ SLIDE-OVER: REPORT FORM ══ -->
    <SlideOver v-model="showReportForm" title="Lập / Cập nhật Báo cáo">
      <div class="p-4 space-y-4">
        <div><label class="block text-xs font-bold text-gray-700 mb-1">Người báo cáo</label>
          <input v-model="reportForm.reporter_name" type="text" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm" placeholder="CS. Nguyễn Văn A" /></div>
        <div><label class="block text-xs font-bold text-gray-700 mb-1">Nhận xét chung</label>
          <textarea v-model="reportForm.evaluation" rows="3" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm resize-none"></textarea></div>
        <div><label class="block text-xs font-bold text-gray-700 mb-1">Đề nghị / Kế hoạch</label>
          <textarea v-model="reportForm.proposals" rows="2" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm resize-none"></textarea></div>
        <div><label class="block text-xs font-bold text-gray-700 mb-1">Ghi chú thêm</label>
          <textarea v-model="reportForm.notes" rows="2" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm resize-none"></textarea></div>
        <div class="flex gap-2 justify-end pt-2">
          <button @click="showReportForm = false" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium hover:bg-gray-50">Hủy</button>
          <button @click="submitReport" :disabled="reportSaving" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-xl disabled:opacity-50">
            {{ reportSaving ? 'Đang lưu...' : 'Lưu' }}
          </button>
        </div>
      </div>
    </SlideOver>

    <!-- ══ SLIDE-OVER: INCIDENT FORM ══ -->
    <SlideOver v-model="showIncidentForm" title="Ghi nhận Sự Cố">
      <div class="p-4 space-y-4">
        <div><label class="block text-xs font-bold text-gray-700 mb-1">Tuần</label>
          <input v-model="incidentForm.week_label" type="text" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm" placeholder="VD: Tuần 1 (01-07/01)" /></div>
        <div><label class="block text-xs font-bold text-gray-700 mb-1">Mô tả sự cố *</label>
          <textarea v-model="incidentForm.description" rows="3" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm resize-none" placeholder="Mô tả sự cố xảy ra..."></textarea></div>
        <div><label class="block text-xs font-bold text-gray-700 mb-1">Giải pháp đã thực hiện</label>
          <textarea v-model="incidentForm.resolution" rows="2" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm resize-none"></textarea></div>
        <div><label class="block text-xs font-bold text-gray-700 mb-1">Hướng xử lý tiếp theo</label>
          <textarea v-model="incidentForm.direction" rows="2" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm resize-none"></textarea></div>
        <div><label class="block text-xs font-bold text-gray-700 mb-1">Trạng thái</label>
          <select v-model="incidentForm.status" class="block w-full rounded-xl border-gray-300 shadow-sm text-sm">
            <option value="pending">⏳ Chờ xử lý</option>
            <option value="in_progress">🔄 Đang xử lý</option>
            <option value="resolved">✅ Đã xử lý</option>
          </select></div>
        <div class="flex gap-2 justify-end pt-2">
          <button @click="showIncidentForm = false" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium hover:bg-gray-50">Hủy</button>
          <button @click="submitIncident" :disabled="incidentSaving" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl disabled:opacity-50">
            {{ incidentSaving ? 'Đang lưu...' : 'Lưu' }}
          </button>
        </div>
      </div>
    </SlideOver>

    <!-- Context Switcher -->
    <SlideOver v-model="isSwitchOpen" title="Chuyển đổi Vai Trò">
      <div class="p-6 space-y-2">
        <div v-for="d in availableDepartments" :key="d.id" @click="switchDept(d.id)"
          class="p-4 rounded-xl border-2 cursor-pointer transition-all"
          :class="department?.id === d.id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 hover:border-gray-300'">
          <h4 class="text-sm font-bold" :class="department?.id === d.id ? 'text-blue-900' : 'text-gray-900'">{{ d.name }}</h4>
        </div>
      </div>
    </SlideOver>

  </PortalLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';
import VueApexCharts from 'vue3-apexcharts';
const apexchart = VueApexCharts;

const props = defineProps({
  department:           { type: Object,  default: () => ({}) },
  availableDepartments: { type: Array,   default: () => [] },
  isGlobalAdmin:        { type: Boolean, default: false },
  filters:              { type: Object,  default: () => ({}) },
  church_meetings:      { type: Array,   default: () => [] },
  yt_trend_series:      { type: Array,   default: () => [] },
  monthly_trend:        { type: Array,   default: () => [] },
  incidents:            { type: Array,   default: () => [] },
  summary:              { type: Object,  default: () => ({}) },
  report:               { type: Object,  default: null },
  youtube_stats:        { type: Object,  default: () => ({}) },
});

const isSwitchOpen   = ref(false);
const showReportForm   = ref(false);
const showIncidentForm = ref(false);
const reportSaving     = ref(false);
const ytSaving         = ref(false);
const incidentSaving   = ref(false);
const editingIncident  = ref(null);
const localIncidents   = ref([...props.incidents]);

const isLeader = computed(() => props.isGlobalAdmin);
const isLocked = computed(() => props.report?.status === 'approved');

const updateReportStatus = (action) => {
  if (!confirm('Xác nhận cập nhật trạng thái báo cáo?')) return;
  router.post(route('deacon.report.status', props.report.id), { action }, {
    preserveScroll: true
  });
};

const localMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const localYear  = ref(props.filters?.year  || new Date().getFullYear());

const updatePeriod = () => {
  router.get(route('deacon.report'), { month: localMonth.value, year: localYear.value }, {
    preserveState: true, replace: true
  });
};

const statusLabel = (s) => ({ draft: 'Bản nháp', submitted: 'Đã nộp', approved: '✓ Đã Duyệt' }[s] || s);
const statusIncidentLabel = (s) => ({ pending: '⏳ Chờ xử lý', in_progress: '🔄 Đang xử lý', resolved: '✅ Đã xử lý' }[s] || s);

// YouTube stats form
const ytForm = reactive({
  subscribers_current: props.youtube_stats?.subscribers_current ?? 0,
  subscribers_new:     props.youtube_stats?.subscribers_new     ?? 0,
  views:               props.youtube_stats?.views               ?? 0,
  watch_hours:         props.youtube_stats?.watch_hours         ?? 0,
});

const youtubeFields = [
  { key: 'subscribers_current', label: 'Đăng ký hiện tại', color: 'text-red-600' },
  { key: 'subscribers_new',     label: 'Đăng ký mới',      color: 'text-green-600' },
  { key: 'views',               label: 'Lượt xem',         color: 'text-blue-600' },
  { key: 'watch_hours',         label: 'Giờ xem',          color: 'text-purple-600' },
];

const saveYoutube = () => {
  ytSaving.value = true;
  router.post(route('deacon.report.save'), {
    report_month: localMonth.value,
    report_year:  localYear.value,
    ...ytForm,
  }, {
    preserveScroll: true,
    onFinish: () => { ytSaving.value = false; }
  });
};

// KPI cards
const kpiCards = computed(() => [
  { label: 'Số buổi nhóm HT',   value: props.church_meetings.length, bg: 'bg-white border border-gray-100', labelColor: 'text-blue-600', valueColor: 'text-gray-900', sub: `TB ${props.summary.avg_church ?? 0} người`, subColor: 'text-gray-400' },
  { label: 'TB hiện diện',      value: props.summary.avg_church ?? 0, bg: 'bg-white border border-gray-100', labelColor: 'text-amber-600', valueColor: 'text-gray-900', change: props.summary.church_change },
  { label: 'Đăng ký YT',       value: ytForm.subscribers_current, bg: 'bg-gradient-to-br from-red-500 to-red-600', labelColor: 'text-red-100', valueColor: 'text-white', sub: `+${ytForm.subscribers_new} mới`, subColor: 'text-red-200' },
  { label: 'Giờ xem',          value: `${ytForm.watch_hours}h`,    bg: 'bg-gradient-to-br from-purple-600 to-indigo-700', labelColor: 'text-purple-200', valueColor: 'text-white', sub: `${ytForm.views} lượt xem`, subColor: 'text-purple-300' },
]);

// Church attendance chart (area)
const churchChartOpts = computed(() => ({
  chart:       { toolbar: { show: false }, zoom: { enabled: false } },
  stroke:      { curve: 'smooth', width: 3 },
  fill:        { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
  colors:      ['#F59E0B'],
  xaxis:       { categories: props.church_meetings.map(m => m.date), labels: { style: { fontSize: '10px', fontWeight: 700 } } },
  yaxis:       { min: 0, labels: { style: { fontSize: '10px' } } },
  grid:        { borderColor: '#f3f4f6' },
  markers:     { size: 5, hover: { size: 7 } },
  dataLabels:  { enabled: true, style: { fontSize: '10px', fontWeight: 700 }, background: { enabled: false }, dropShadow: { enabled: false } },
  tooltip:     { y: { formatter: (v) => `${v} người` } },
  legend:      { show: false },
}));
const churchSeries = computed(() => [{ name: 'Hiện Diện', data: props.church_meetings.map(m => m.attendance) }]);

// 6-month trend bar chart
const trendBarOpts = computed(() => ({
  chart:       { toolbar: { show: false } },
  colors:      ['#3B82F6'],
  xaxis:       { categories: props.monthly_trend.map(m => m.label), labels: { style: { fontSize: '10px', fontWeight: 700 } } },
  yaxis:       { min: 0, labels: { style: { fontSize: '10px' } } },
  plotOptions: { bar: { borderRadius: 6, columnWidth: '55%', dataLabels: { position: 'top' } } },
  grid:        { borderColor: '#f3f4f6' },
  dataLabels:  { enabled: true, style: { fontSize: '10px', fontWeight: 700 }, offsetY: -18, dropShadow: { enabled: false } },
  tooltip:     { y: { formatter: (v) => `${v} người` } },
}));
const trendBarSeries = computed(() => [{ name: 'TB Hiện Diện', data: props.monthly_trend.map(m => m.avg) }]);

// YouTube trend line chart
const ytTrendOpts = computed(() => ({
  chart:  { toolbar: { show: false }, zoom: { enabled: false } },
  stroke: { curve: 'smooth', width: 2.5 },
  colors: ['#EF4444', '#22C55E', '#3B82F6'],
  xaxis:  { categories: ['3 tháng trước', '2 tháng trước', 'Tháng này'], labels: { style: { fontSize: '10px', fontWeight: 700 } } },
  yaxis:  { labels: { style: { fontSize: '10px' } } },
  legend: { position: 'top', fontSize: '11px', fontWeight: 700 },
  grid:   { borderColor: '#f3f4f6' },
  markers: { size: 5 },
  dataLabels: { enabled: false },
}));

// Report form
const reportForm = ref({ reporter_name: '', evaluation: '', proposals: '', notes: '' });
const openReportForm = () => {
  reportForm.value = {
    reporter_name: props.report?.reporter_name || '',
    evaluation:    props.report?.evaluation    || '',
    proposals:     props.report?.proposals     || '',
    notes:         props.report?.notes         || '',
  };
  showReportForm.value = true;
};
const submitReport = () => {
  reportSaving.value = true;
  router.post(route('deacon.report.save'), {
    report_month: localMonth.value,
    report_year:  localYear.value,
    ...reportForm.value,
  }, {
    preserveScroll: true,
    onSuccess: () => { showReportForm.value = false; },
    onFinish:  () => { reportSaving.value = false; }
  });
};

// Incident form
const incidentForm = reactive({ week_label: '', description: '', resolution: '', direction: '', status: 'pending' });
const openIncidentForm = (incident) => {
  editingIncident.value = incident;
  if (incident) {
    Object.assign(incidentForm, {
      week_label:  incident.week_label  || '',
      description: incident.description || '',
      resolution:  incident.resolution  || '',
      direction:   incident.direction   || '',
      status:      incident.status      || 'pending',
    });
  } else {
    Object.assign(incidentForm, { week_label: '', description: '', resolution: '', direction: '', status: 'pending' });
  }
  showIncidentForm.value = true;
};
const submitIncident = () => {
  incidentSaving.value = true;
  const isEdit = !!editingIncident.value;
  const url    = isEdit
    ? route('deacon.incident.update', editingIncident.value.id)
    : route('deacon.incident.store');
  router.post(url, {
    report_month: localMonth.value,
    report_year:  localYear.value,
    ...incidentForm,
    _method: isEdit ? 'PUT' : 'POST',
  }, {
    preserveScroll: true,
    onSuccess: (page) => {
      localIncidents.value = page.props.incidents ?? localIncidents.value;
      showIncidentForm.value = false;
    },
    onFinish: () => { incidentSaving.value = false; }
  });
};
const deleteIncident = (id) => {
  if (!confirm('Xóa sự cố này?')) return;
  router.delete(route('deacon.incident.destroy', id), {
    preserveScroll: true,
    onSuccess: (page) => {
      localIncidents.value = page.props.incidents ?? localIncidents.value.filter(i => i.id !== id);
    }
  });
};

const switchDept = (roleId) => {
  router.post(route('deacon.switch-role'), { role: roleId }, {
    preserveScroll: true,
    onSuccess: () => { isSwitchOpen.value = false; }
  });
};

// ── Print ─────────────────────────────────────────────────────────────────
const printReport = () => window.print();
</script>
