<template>
  <component :is="currentLayout">
    <template #header>Tổng Quan Hội Thánh</template>

    <div class="py-4 space-y-6 w-full">

      <!-- Hero Banner -->
      <div class="rounded-2xl bg-gradient-to-br from-blue-700 to-blue-900 p-6 sm:p-8 text-white relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 opacity-10 pointer-events-none flex items-center justify-end pr-8">
          <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        </div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="flex-1">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300 mb-1">DASHBOARD MỤC SƯ</p>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Tổng Quan Hội Thánh</h1>
            <p class="mt-1 text-sm text-blue-200">Tháng {{ localMonth }}/{{ localYear }} · Cập nhật lúc {{ nowTime }}</p>
          </div>
          <div class="flex items-center gap-2 bg-white/10 border border-white/20 rounded-xl px-3 py-2 backdrop-blur-sm">
            <select v-model="localMonth" @change="reload" class="text-sm font-bold border-none focus:ring-0 p-0 text-white bg-transparent">
              <option v-for="m in 12" :key="m" :value="m" class="text-gray-900">Tháng {{ m }}</option>
            </select>
            <input v-model="localYear" @change="reload" type="number" min="2020" max="2099" class="w-16 text-sm border-none focus:ring-0 p-0 text-center font-bold text-white bg-transparent">
          </div>
        </div>
      </div>

      <!-- ══ KPI CARDS ══ -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-4 text-white shadow-lg shadow-blue-100">
          <p class="text-[10px] font-bold uppercase tracking-wider text-blue-200">Tổng Tín Hữu</p>
          <p class="text-3xl font-black mt-1">{{ kpi.total_members }}</p>
          <p class="text-xs text-blue-200 mt-1">{{ kpi.active_members }} Chính thức</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-4 text-white shadow-lg shadow-emerald-100">
          <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-100">Mới Tháng Này</p>
          <p class="text-3xl font-black mt-1">{{ kpi.new_this_month }}</p>
          <p class="text-xs text-emerald-100 mt-1">thành viên gia nhập</p>
        </div>
        <div class="rounded-2xl p-4 shadow-sm bg-white border border-gray-100">
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">BC Chờ Duyệt</p>
          <p class="text-3xl font-black mt-1" :class="kpi.pending_reports > 0 ? 'text-amber-600' : 'text-gray-900'">{{ kpi.pending_reports }}</p>
          <p class="text-xs text-gray-400 mt-1">báo cáo ban ngành</p>
        </div>
        <div class="rounded-2xl p-4 shadow-sm bg-white border border-gray-100">
          <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Thăm Viếng</p>
          <p class="text-3xl font-black mt-1 text-gray-900">{{ visitation_stats.total }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ visitation_stats.completed }} hoàn thành / {{ visitation_stats.planned }} kế hoạch</p>
        </div>
      </div>

      <!-- ══ SECTION 1: BÁO CÁO CHỜ DUYỆT ══ -->
      <div v-if="pending_reports.length > 0" class="bg-amber-50 rounded-2xl border border-amber-200 overflow-hidden">
        <div class="px-5 py-3 bg-amber-500 flex items-center justify-between">
          <h3 class="text-xs font-black uppercase tracking-widest text-white">🔔 BÁO CÁO CHỜ DUYỆT</h3>
          <span class="bg-white/30 text-white text-xs font-bold px-3 py-1 rounded-full">{{ pending_reports.length }} báo cáo</span>
        </div>
        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <div v-for="r in pending_reports" :key="r.id"
            class="bg-white rounded-xl px-4 py-3 border border-amber-100 flex items-center justify-between shadow-sm">
            <div>
              <p class="text-sm font-bold text-gray-900">{{ r.dept_name }}</p>
              <p class="text-xs text-gray-500">Tháng {{ r.month }}/{{ r.year }} · Nộp: {{ r.submitted_at }}</p>
            </div>
            <a href="/portal/reports" class="text-amber-600 hover:text-amber-800 text-xs font-bold px-3 py-1 bg-amber-50 rounded-lg border border-amber-200">Duyệt</a>
          </div>
        </div>
      </div>

      <!-- ══ SECTION 2: BẢNG BUỔI NHÓM ══ -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-blue-900 flex items-center justify-between">
          <h3 class="text-xs font-black uppercase tracking-widest text-white">📅 TRUNG TÂM DỮ LIỆU: CÁC BUỔI NHÓM THÁNG {{ localMonth }}/{{ localYear }}</h3>
          <div class="flex gap-2">
            <button @click="meetingFilter = 'all'" class="text-xs font-bold px-3 py-1 rounded-full transition-colors"
              :class="meetingFilter === 'all' ? 'bg-white text-blue-900' : 'text-blue-200 hover:bg-white/10'">Tất cả</button>
            <button @click="meetingFilter = 'church'" class="text-xs font-bold px-3 py-1 rounded-full transition-colors"
              :class="meetingFilter === 'church' ? 'bg-white text-blue-900' : 'text-blue-200 hover:bg-white/10'">Hội Thánh</button>
            
            <div class="relative group">
              <button class="text-xs font-bold px-3 py-1 rounded-full transition-colors flex items-center gap-1"
                :class="isDepartmentFilter ? 'bg-white text-blue-900' : 'text-blue-200 hover:bg-white/10'">
                Ban Ngành {{ selectedDeptName ? `(${selectedDeptName})` : '' }}
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
              </button>
              <div class="absolute right-0 mt-1 pb-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 hidden group-hover:block z-50 overflow-hidden">
                <button v-for="dept in activities_departments" :key="dept.id"
                  @click="meetingFilter = 'dept_' + dept.id"
                  class="w-full text-left px-4 py-2.5 text-xs font-medium border-b border-gray-50 last:border-0 hover:bg-blue-50 transition-colors"
                  :class="meetingFilter === 'dept_' + dept.id ? 'bg-blue-50 text-blue-700 font-bold' : 'text-gray-700'">
                  {{ dept.name }}
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-slate-50 border-b border-gray-100">
              <tr>
                <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800">Ngày</th>
                <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800">Ban ngành</th>
                <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800">Chủ đề</th>
                <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800 hidden lg:table-cell">Câu gốc</th>
                <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800 hidden md:table-cell">Diễn giả</th>
                <th class="px-4 py-3.5 text-center text-[13px] font-bold text-slate-800">HD</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="m in filteredMeetings" :key="m.id" class="hover:bg-slate-50 transition-colors group">
                <td class="px-4 py-2.5 whitespace-nowrap">
                  <p class="text-xs font-black text-gray-900">{{ m.date }}</p>
                  <p class="text-[10px] text-gray-400 capitalize">{{ m.day }}</p>
                </td>
                <td class="px-4 py-2.5">
                  <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                    :class="m.type === 'church' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'">
                    {{ m.dept_name }}
                  </span>
                </td>
                <td class="px-4 py-2.5 text-xs text-gray-800 max-w-[160px] truncate">{{ m.topic || '—' }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-600 hidden lg:table-cell max-w-[130px] truncate">{{ m.memory_verse || m.scripture || '—' }}</td>
                <td class="px-4 py-2.5 text-xs text-gray-600 hidden md:table-cell">{{ m.speaker || '—' }}</td>
                <td class="px-4 py-2.5 text-center text-sm font-black text-amber-700">{{ m.attendance > 0 ? m.attendance : '—' }}</td>
              </tr>
              <tr v-if="filteredMeetings.length === 0">
                <td colspan="6" class="px-4 py-6 text-center text-xs text-gray-400">Chưa có buổi nhóm nào trong tháng này</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ══ SECTION 2B: ADVANCED ANALYTICS (PHASE 11 - REVAMP) ══ -->
      <div v-if="analytics" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Tham dự Nhóm HT (Line) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:col-span-2">
          <h3 class="text-xs font-black uppercase tracking-widest text-gray-900 mb-1">📈 SMART TRACK: TÍN HỮU DỰ NHÓM BAN NGÀNH</h3>
          <p class="text-[10px] text-gray-400 mb-4">AI phân tích lượng tín hữu điểm danh theo Ban (mỗi loại ban 1 đường phân tích)</p>
          <apexchart type="line" height="240" :options="deptMeetingLineOptsConfig" :series="deptMeetingSeries" />
        </div>
        
        <!-- Phân bố Ban ngành (Pie) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col items-center justify-center">
          <h3 class="text-xs font-black uppercase tracking-widest text-gray-900 mb-1 w-full text-center">🎯 AI DEMOGRAPHICS: PHÂN BỔ TÍN HỮU</h3>
          <p class="text-[10px] text-gray-400 mb-4 w-full text-center">Tỷ lệ tham gia nhóm Hội Thánh / Tháng</p>
          <apexchart type="donut" width="100%" height="240" :options="demoChartOpts" :series="demoSeries" />
        </div>
        
        <!-- Tài chính (Bar) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:col-span-3">
          <h3 class="text-xs font-black uppercase tracking-widest text-gray-900 mb-1">💰 CASH FLOW: DÒNG TIỀN TÀI CHÍNH (3 THÁNG)</h3>
          <p class="text-[10px] text-gray-400 mb-4">Theo dõi biến động dòng tiền ngầm theo từng block Ban ngành sinh hoạt</p>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                  <p class="text-xs font-bold text-emerald-600 mb-2 border-b pb-1">Analytics: Biểu Đồ Kênh Thu</p>
                  <apexchart type="bar" height="250" :options="financeIncomeOpts" :series="financeIncomeSeries" />
              </div>
              <div>
                  <p class="text-xs font-bold text-rose-600 mb-2 border-b pb-1">Analytics: Biểu Đồ Kênh Chi</p>
                  <apexchart type="bar" height="250" :options="financeExpenseOpts" :series="financeExpenseSeries" />
              </div>
          </div>
        </div>
      </div>

      <!-- ══ SECTION 3: BIỂU ĐỒ HIỆN DIỆN ══ -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Biểu đồ ban ngành -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <h3 class="text-xs font-black uppercase tracking-widest text-gray-900 mb-1">📊 INSIGHTS: TIẾN ĐỘ BAN NGÀNH</h3>
          <p class="text-[10px] text-gray-400 mb-4">Máy phân tích biến thiên hiện diện tuần 1-5</p>
          <apexchart v-if="dept_att_series.length > 0" type="line" height="240"
            :options="deptLineOpts" :series="dept_att_series" />
          <div v-else class="h-56 flex items-center justify-center text-gray-300">
            <p class="text-xs">Chưa có dữ liệu</p>
          </div>
        </div>
        <!-- Biểu đồ hội thánh -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <h3 class="text-xs font-black uppercase tracking-widest text-gray-900 mb-1">⛪ INSIGHTS: XU HƯỚNG HIỆN DIỆN</h3>
          <p class="text-[10px] text-gray-400 mb-4">Phân tích số lượng tham dự buổi nhóm Hội Thánh qua 5 tuần</p>
          <apexchart type="area" height="240"
            :options="churchLineOpts" :series="[church_att_line]" />
        </div>
      </div>

      <!-- ══ SECTION 4: CƠ ĐỐC GIÁO DỤC ══ -->
      <div v-if="cgdg" class="space-y-4">
        <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
          📚 <span>BAN CƠ ĐỐC GIÁO DỤC</span>
        </h3>

        <!-- Gộp TCN và KTTN + Biểu đồ Tiền dâng -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          
          <!-- Box Biểu đồ Tiền dâng -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:col-span-1">
            <h3 class="text-xs font-black uppercase tracking-widest text-gray-900 mb-1">💸 PREDICTIVE FLOW: DÂNG HIẾN HỌC VIÊN</h3>
            <p class="text-[10px] text-gray-400 mb-4">Theo dõi mạch tiền dâng lũy kế TCN & KTTN</p>
            <apexchart type="area" height="250" :options="cgdgOfferingsOpts" :series="cgdgOfferingsSeries" />
          </div>

          <!-- Box Bảng TCN và KTTN -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2 flex flex-col">
            <div class="px-5 py-3 bg-indigo-900 flex items-center justify-between">
              <h4 class="text-xs font-black uppercase tracking-widest text-white">DATA COMPONENT: TRƯỜNG CHỦ NHẬT & KTTN</h4>
            </div>
            <div class="overflow-x-auto flex-1">
              <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-slate-50 border-b border-gray-100">
                  <tr>
                    <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800 w-[15%]">Loại</th>
                    <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800">Lớp</th>
                    <th class="px-4 py-3.5 text-center text-[13px] font-bold text-slate-800">Số tuần/bài</th>
                    <th class="px-4 py-3.5 text-center text-[13px] font-bold text-slate-800">Số người</th>
                    <th class="px-4 py-3.5 text-center text-[13px] font-bold text-slate-800">Trạng thái/TB</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <!-- Sunday School Classes -->
                  <tr v-for="cls in (cgdg.sunday_school?.classes || [])" :key="'tcn_'+cls.class_id" class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-2.5">
                      <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">TCN</span>
                    </td>
                    <td class="px-4 py-2.5 text-xs font-bold text-gray-900">{{ cls.class_name }}</td>
                    <td class="px-4 py-2.5 text-center text-sm text-gray-500">{{ cls.sessions.length }}</td>
                    <td class="px-4 py-2.5 text-center text-sm font-black text-amber-700">{{ cls.total }}</td>
                    <td class="px-4 py-2.5 text-center text-xs text-gray-600">
                      <span v-if="cls.sessions.length > 0">{{ cls.sessions[cls.sessions.length - 1].date }}</span>
                      <span v-else class="text-gray-300">—</span>
                    </td>
                  </tr>
                  <!-- Bible Quiz Classes -->
                  <tr v-for="cls in (cgdg.bible_quiz?.classes || [])" :key="'bq_'+cls.class_id" class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-2.5">
                      <span class="text-[10px] font-bold bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">KTTN</span>
                    </td>
                    <td class="px-4 py-2.5 text-xs font-bold text-gray-900">{{ cls.class_name }}</td>
                    <td class="px-4 py-2.5 text-center text-sm text-gray-500">{{ cls.sessions.length }}</td>
                    <td class="px-4 py-2.5 text-center text-sm font-black text-indigo-700">{{ cls.scored_total ?? 0 }}</td>
                    <td class="px-4 py-2.5 text-center text-xs font-bold" :class="cls.avg_score_all ? 'text-emerald-700' : 'text-gray-300'">
                      {{ cls.avg_score_all != null ? 'TB: ' + cls.avg_score_all + 'đ' : 'Chưa chấm' }}
                    </td>
                  </tr>
                </tbody>
              </table>
              <div v-if="!(cgdg.sunday_school?.classes?.length) && !(cgdg.bible_quiz?.classes?.length)" class="px-5 py-6 text-center text-xs text-gray-400">
                Chưa có dữ liệu cho thánh này
              </div>
            </div>
          </div>
        </div>

        <!-- Lớp Giáo Lý (Gospel) -->
        <div v-if="cgdg.gospel?.classes?.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden w-full lg:w-2/3">
          <div class="px-5 py-3 bg-emerald-800 flex items-center justify-between">
            <h4 class="text-sm font-black text-white">📖 LỚP GIÁO LÝ (BÁP-TEM)</h4>
          </div>
          <div class="overflow-x-auto border-t border-gray-100">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-slate-50 border-b border-gray-100">
                <tr>
                  <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800">Lớp</th>
                  <th class="px-4 py-3.5 text-center text-[13px] font-bold text-slate-800">Số buổi</th>
                  <th class="px-4 py-3.5 text-center text-[13px] font-bold text-slate-800">Hiện diện</th>
                  <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800">Buổi gần nhất</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                  <tr v-for="cls in cgdg.gospel.classes" :key="cls.class_id" class="hover:bg-slate-50 transition-colors group">
                    <td class="px-4 py-2.5 text-xs font-bold text-gray-900">{{ cls.class_name }}</td>
                    <td class="px-4 py-2.5 text-center text-sm text-gray-500">{{ cls.sessions.length }}</td>
                    <td class="px-4 py-2.5 text-center text-sm font-black text-emerald-700">{{ cls.total }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-600">
                      <span v-if="cls.sessions.length > 0">
                        {{ cls.sessions[cls.sessions.length - 1].date }} — {{ cls.sessions[cls.sessions.length - 1].topic || '—' }}
                      </span>
                      <span v-else class="text-gray-300">—</span>
                    </td>
                  </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- ══ SECTION 5: SINH NHẬT ══ -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-pink-600 flex items-center justify-between">
          <h3 class="text-xs font-black uppercase tracking-widest text-white">🎂 EVENT TRIGGER: SINH NHẬT THÁNG {{ localMonth }}</h3>
          <span class="bg-white/30 text-white text-xs font-bold px-3 py-1 rounded-full">{{ birthdays.length }} sự kiện</span>
        </div>
        <div v-if="birthdays.length > 0">
          <!-- This week highlight -->
          <div v-if="birthdaysThisWeek.length > 0" class="px-5 py-3 bg-pink-50 border-b border-pink-100">
            <p class="text-xs font-bold text-pink-800 mb-2">🎉 Sinh nhật tuần này:</p>
            <div class="flex flex-wrap gap-2">
              <span v-for="b in birthdaysThisWeek" :key="b.id"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-pink-100 text-pink-900 text-xs font-bold rounded-full border border-pink-200">
                {{ b.is_today ? '🎈 ' : '' }}{{ b.full_name }} ({{ b.birth_day }} · {{ b.age }} tuổi)
              </span>
            </div>
          </div>
          <!-- Full list -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-slate-50 border-b border-gray-100">
                <tr>
                  <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800">Tên</th>
                  <th class="px-4 py-3.5 text-center text-[13px] font-bold text-slate-800">Ngày SN</th>
                  <th class="px-4 py-3.5 text-center text-[13px] font-bold text-slate-800">Tuổi</th>
                  <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800 hidden sm:table-cell">SĐT</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="b in birthdays" :key="b.id"
                  class="group transition-colors"
                  :class="b.is_today ? 'bg-pink-50 hover:bg-pink-100/50' : b.is_this_week ? 'bg-pink-50/40 hover:bg-pink-50' : 'hover:bg-slate-50'">
                  <td class="px-4 py-2.5">
                    <div class="flex items-center gap-2">
                      <span class="text-xs font-bold text-gray-900">{{ b.full_name }}</span>
                      <span v-if="b.is_today" class="text-xs bg-pink-500 text-white px-2 py-0.5 rounded-full font-bold">Hôm nay!</span>
                      <span v-else-if="b.is_this_week" class="text-xs bg-pink-100 text-pink-700 px-2 py-0.5 rounded-full font-bold">Tuần này</span>
                    </div>
                  </td>
                  <td class="px-4 py-2.5 text-center text-sm font-black text-pink-700">{{ b.birth_day }}</td>
                  <td class="px-4 py-2.5 text-center text-xs text-gray-600">{{ b.age }}</td>
                  <td class="px-4 py-2.5 text-xs text-gray-500 hidden sm:table-cell">{{ b.phone || '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else class="px-5 py-8 text-center text-xs text-gray-400">Không có sinh nhật trong tháng {{ localMonth }}</div>
      </div>

      <!-- ══ SECTION 6: THĂM VIẾNG ══ -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Bảng danh sách thăm viếng -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2 flex flex-col">
          <div class="px-5 py-3 bg-purple-800 flex items-center justify-between">
            <h3 class="text-xs font-black uppercase tracking-widest text-white">🤝 DATA COMPONENT: LỊCH TRÌNH THĂM VIẾNG THÁNG {{ localMonth }}</h3>
            <div class="flex gap-2 text-xs font-bold">
              <span class="bg-white/20 text-white px-3 py-1 rounded-full">{{ visitations.length }} sự kiện</span>
            </div>
          </div>
          <div v-if="visitations.length > 0" class="overflow-x-auto flex-1">
            <table class="min-w-full divide-y divide-gray-100 border-r border-gray-100">
              <thead class="bg-slate-50 border-b border-gray-100">
                <tr>
                  <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800">Ngày</th>
                  <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800">Tín hữu</th>
                  <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800 hidden md:table-cell">Ban ngành</th>
                  <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800 hidden lg:table-cell">Lý do</th>
                  <th class="px-4 py-3.5 text-center text-[13px] font-bold text-slate-800">Trạng thái</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="v in visitations" :key="v.id" class="hover:bg-slate-50 transition-colors group">
                  <td class="px-4 py-2.5 text-xs font-medium text-gray-700 whitespace-nowrap">{{ v.visit_date }}</td>
                  <td class="px-4 py-2.5 text-xs font-bold text-gray-900">{{ v.member_name }}</td>
                  <td class="px-4 py-2.5 text-xs text-gray-600 hidden md:table-cell">{{ v.dept_name }}</td>
                  <td class="px-4 py-2.5 text-xs text-gray-500 hidden lg:table-cell max-w-[200px] truncate">{{ v.reason }}</td>
                  <td class="px-4 py-2.5 text-center">
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                      :class="v.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'">
                      {{ v.status === 'completed' ? 'Đã thăm' : 'Kế hoạch' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="px-5 py-6 text-center text-xs text-gray-400">Chưa có hoạt động thăm viếng trong tháng này</div>
        </div>

        <!-- AI Analytics & Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
          <div class="px-5 py-3 bg-indigo-900 flex items-center justify-between">
            <h3 class="text-xs font-black uppercase tracking-widest text-white">🤖 AI: PHÂN TÍCH TIẾN ĐỘ</h3>
          </div>
          <div class="p-5 flex-1 flex flex-col justify-between">
             <div class="mb-4">
                <!-- modern progress bar & ai text -->
                <div class="flex items-end justify-between mb-1">
                   <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tỉ lệ hoàn thành (Tháng {{ localMonth }})</p>
                   <p class="text-xl font-black text-indigo-700">{{ visitation_stats.total > 0 ? Math.round(visitation_stats.completed / visitation_stats.total * 100) : 0 }}%</p>
                </div>
                <!-- Progress bar track -->
                <div class="w-full bg-gray-100 rounded-full h-2.5 mb-2 overflow-hidden shadow-inner">
                  <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-1000" :style="{ width: (visitation_stats.total > 0 ? Math.round(visitation_stats.completed / visitation_stats.total * 100) : 0) + '%' }"></div>
                </div>
                <p class="text-[11px] text-gray-500 italic">Hệ thống ghi nhận đã hoàn tất {{ visitation_stats.completed }}/{{ visitation_stats.total }} kế hoạch thăm viếng.</p>
             </div>
             
             <div class="pt-4 border-t border-gray-100">
                 <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Lịch sử 6 tháng (AI dự phóng)</p>
                 <div class="-mx-2">
                   <apexchart type="bar" height="150" :options="visitationChartOpts" :series="visitationSeries" />
                 </div>
             </div>
          </div>
        </div>
      </div>

      <!-- ══ SECTION 7, 8 & 9: TÍN HỮU MỚI, THÂN HỮU & NGÀY ĐẶC BIỆT ══ -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Tín hữu mới -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
          <div class="px-5 py-3 bg-emerald-700 flex items-center justify-between">
            <h3 class="text-xs font-black uppercase tracking-widest text-white">✝️ TÍN HỮU MỚI TIN CHÚA</h3>
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ new_members_30.length + new_members_90.length }}</span>
          </div>

          <div class="flex-1 overflow-auto">
            <!-- 30 ngày -->
            <div v-if="new_members_30.length > 0">
              <div class="px-4 py-2 bg-emerald-50 border-b border-emerald-100">
                <p class="text-xs font-black text-emerald-800">🌟 Trong 30 ngày qua ({{ new_members_30.length }} người)</p>
              </div>
              <table class="min-w-full divide-y divide-gray-100">
                <tbody>
                  <tr v-for="m in new_members_30" :key="m.id" class="hover:bg-slate-50 transition-colors group">
                    <td class="px-4 py-2.5 text-xs font-bold text-gray-900">{{ m.full_name }}</td>
                    <td class="px-4 py-2.5 text-xs text-emerald-700 font-medium text-right">{{ m.faith_date }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- 90 ngày -->
            <div v-if="new_members_90.length > 0">
              <div class="px-4 py-2 bg-gray-50 border-y border-gray-100">
                <p class="text-xs font-bold text-gray-600">30-90 ngày trước ({{ new_members_90.length }} người)</p>
              </div>
              <table class="min-w-full divide-y divide-gray-100">
                <tbody>
                  <tr v-for="m in new_members_90" :key="m.id" class="hover:bg-slate-50 transition-colors group">
                    <td class="px-4 py-2.5 text-xs font-medium text-gray-700">{{ m.full_name }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-500 text-right">{{ m.faith_date }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="new_members_30.length === 0 && new_members_90.length === 0" class="px-5 py-6 text-center text-xs text-gray-400">
              Không có tín hữu mới trong 90 ngày qua
            </div>
          </div>
        </div>

        <!-- Thân hữu truyền giảng -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
          <div class="px-5 py-3 bg-blue-700 flex items-center justify-between">
            <h3 class="text-xs font-black uppercase tracking-widest text-white">📣 THÂN HỮU TRUYỀN GIẢNG</h3>
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ evangelistic_guests }}</span>
          </div>
          <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-3">
               <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <p class="text-[32px] font-black text-blue-900 leading-none mb-1">{{ evangelistic_guests }} <span class="text-sm font-medium text-gray-500">người</span></p>
            <p class="text-[11px] text-gray-500">Tham dự các chương trình truyền giảng trong tháng {{ localMonth }}</p>
          </div>
        </div>

        <!-- Ngày đặc biệt -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
          <div class="px-5 py-3 bg-amber-600 flex items-center justify-between">
            <h3 class="text-xs font-black uppercase tracking-widest text-white">🌟 NGÀY ĐẶC BIỆT THÁNG {{ localMonth }}</h3>
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ special_dates.length }}</span>
          </div>
          <div v-if="special_dates.length > 0">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-slate-50 border-b border-gray-100">
                <tr>
                  <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800">Tên</th>
                  <th class="px-4 py-3.5 text-left text-[13px] font-bold text-slate-800">Loại</th>
                  <th class="px-4 py-3.5 text-center text-[13px] font-bold text-slate-800">Ngày</th>
                  <th class="px-4 py-3.5 text-center text-[13px] font-bold text-slate-800">Số năm</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="d in special_dates" :key="`${d.id}-${d.type}`" class="hover:bg-slate-50 transition-colors group">
                  <td class="px-4 py-2.5 text-xs font-bold text-gray-900">{{ d.full_name }}</td>
                  <td class="px-4 py-2.5">
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                      :class="d.type === 'baptism' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'">
                      {{ d.label }}
                    </span>
                  </td>
                  <td class="px-4 py-2.5 text-center text-xs font-black text-amber-700">{{ d.date }}</td>
                  <td class="px-4 py-2.5 text-center text-xs text-gray-500">{{ d.years > 0 ? d.years + ' năm' : 'Năm nay' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="px-5 py-6 text-center text-xs text-gray-400">
            Không có ngày đặc biệt trong tháng {{ localMonth }}
          </div>
        </div>
      </div>

    </div>
  </component>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import VueApexCharts from 'vue3-apexcharts';

const apexchart = VueApexCharts;

const props = defineProps({
    filters: Object,
    activities_departments: Array,
    kpi: Object,
    pending_reports: Array,
    pending_approvals_count: Number,
    meetings: Array,
    dept_att_series: Array,
    church_att_line: Object,
    cgdg: Object,
    birthdays: Array,
    visitations: Array,
    visitation_stats: Object,
    visitation_chart: Array,
    new_members_30: Array,
    new_members_90: Array,
    evangelistic_guests: Number,
    special_dates: Array,
    analytics: Object,
});

const page = usePage();
const currentLayout = computed(() => {
    if (typeof window === 'undefined') return AuthenticatedLayout;
    return window.innerWidth < 768 ? MobileLayout : AuthenticatedLayout;
});

const localMonth = ref(props.filters?.month || new Date().getMonth() + 1);
const localYear  = ref(props.filters?.year  || new Date().getFullYear());
const nowTime    = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
const reload = () => router.get('/', { month: localMonth.value, year: localYear.value }, { preserveState: true, replace: true });

// Meeting filter
const meetingFilter = ref('church');

const isDepartmentFilter = computed(() => meetingFilter.value.startsWith('dept_'));
const selectedDeptName = computed(() => {
    if (isDepartmentFilter.value) {
        const dId = parseInt(meetingFilter.value.replace('dept_', ''));
        const d = (props.activities_departments || []).find(x => x.id === dId);
        return d ? d.name : '';
    }
    return '';
});

const filteredMeetings = computed(() => {
    if (meetingFilter.value === 'all') return props.meetings || [];
    if (meetingFilter.value === 'church') return (props.meetings || []).filter(m => m.type === 'church');
    if (isDepartmentFilter.value) {
        const dId = parseInt(meetingFilter.value.replace('dept_', ''));
        // Find meetings that are type 'department' and belong to this dept
        return (props.meetings || []).filter(m => m.type === 'department' && m.dept_name === selectedDeptName.value);
    }
    return props.meetings || [];
});

// Birthday filter
const birthdaysThisWeek = computed(() => (props.birthdays || []).filter(b => b.is_this_week || b.is_today));

// ══ CHART OPTIONS ══
const WEEKS = ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4', 'Tuần 5'];

const deptLineOpts = {
    chart: { type: 'line', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    stroke: { curve: 'smooth', width: 2.5 },
    xaxis: { categories: WEEKS, labels: { style: { fontSize: '10px' } } },
    yaxis: { labels: { style: { fontSize: '10px' } }, min: 0 },
    legend: { position: 'bottom', fontSize: '10px' },
    colors: ['#6366f1', '#ec4899', '#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6'],
    tooltip: { y: { formatter: v => v + ' người' } },
    grid: { strokeDashArray: 4 },
};

const churchLineOpts = {
    chart: { type: 'area', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    stroke: { curve: 'smooth', width: 2.5 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.02 } },
    xaxis: { categories: WEEKS, labels: { style: { fontSize: '10px' } } },
    yaxis: { labels: { style: { fontSize: '10px' } }, min: 0 },
    colors: ['#2563eb'],
    tooltip: { y: { formatter: v => v + ' người' } },
    grid: { strokeDashArray: 4 },
};

// ══ ADVANCED ANALYTICS CHARTS ══
const formatCurrency = (val) => {
    if (val >= 1000000) return (val / 1000000).toFixed(1) + ' Tr';
    if (val >= 1000) return (val / 1000).toFixed(0) + ' K';
    return val;
};

// 1. Demographics (Donut)
const demoSeries = computed(() => {
    return props.analytics?.demographics?.map(d => Number(d.total)) || [];
});
const demoChartOpts = computed(() => ({
    chart: { type: 'donut', fontFamily: 'Inter, sans-serif' },
    labels: props.analytics?.demographics?.map(d => d.name) || [],
    colors: ['#3b82f6', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#6366f1', '#e5e7eb'], // Last is gray for "Chưa tham gia"
    legend: { position: 'bottom', fontSize: '10px' },
    dataLabels: { enabled: false },
    plotOptions: { pie: { donut: { size: '65%' } } }
}));

// 2. Finance (Bar 3 months split)
const financeIncomeSeries = computed(() => props.analytics?.finance_income || []);
const financeExpenseSeries = computed(() => props.analytics?.finance_expense || []);

const financeSharedOpts = {
    chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    plotOptions: { bar: { horizontal: false, columnWidth: '50%', borderRadius: 2 } },
    dataLabels: { enabled: false },
    stroke: { show: true, width: 2, colors: ['transparent'] },
    xaxis: { categories: props.analytics?.finance_categories || [], labels: { style: { fontSize: '10px' } } },
    yaxis: { labels: { formatter: formatCurrency, style: { fontSize: '10px' } } },
    grid: { strokeDashArray: 4 },
    legend: { position: 'bottom', fontSize: '10px', markers: { radius: 12 } },
    tooltip: { 
        y: { formatter: val => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val) }
    }
};

const financeIncomeOpts = computed(() => ({
    ...financeSharedOpts,
    colors: ['#10b981', '#34d399', '#6ee7b7', '#a7f3d0', '#059669', '#047857'] // Shades of Emerald
}));

const financeExpenseOpts = computed(() => ({
    ...financeSharedOpts,
    colors: ['#ef4444', '#f87171', '#fca5a5', '#fecaca', '#b91c1c', '#991b1b'] // Shades of Red
}));

// 3. Dept Meeting Lines (Replaces Church)
const deptMeetingSeries = computed(() => props.analytics?.dept_meeting_lines || []);
const deptMeetingLineOptsConfig = computed(() => ({
    chart: { height: 240, type: 'line', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    stroke: { width: 3, curve: 'smooth' },
    colors: ['#6366f1', '#ec4899', '#10b981', '#f59e0b', '#3b82f6', '#8b5cf6'],
    xaxis: { categories: WEEKS, labels: { style: { fontSize: '10px' } } },
    yaxis: { labels: { style: { fontSize: '10px' } }, min: 0 },
    grid: { strokeDashArray: 4 },
    legend: { position: 'bottom', fontSize: '10px' },
    tooltip: { y: { formatter: v => v + ' người' } },
}));

// 4. CGDG Offerings (Line)
const cgdgOfferingsSeries = computed(() => {
    if (!props.cgdg) return [];
    const series = [];
    
    // For Sunday School
    if (props.cgdg.sunday_school && props.cgdg.sunday_school.classes) {
        const numSessions = Math.max(...props.cgdg.sunday_school.classes.map(c => c.offerings_data?.length || 0), 0);
        if (numSessions > 0) {
            const data = Array(numSessions).fill(0);
            props.cgdg.sunday_school.classes.forEach(cls => {
                (cls.offerings_data || []).forEach((amt, i) => { data[i] += Number(amt || 0); });
            });
            series.push({ name: 'Trường Chủ Nhật', data });
        }
    }
    
    // For Bible Quiz
    if (props.cgdg.bible_quiz && props.cgdg.bible_quiz.classes) {
        const numSessions = Math.max(...props.cgdg.bible_quiz.classes.map(c => c.offerings_data?.length || 0), 0);
        if (numSessions > 0) {
            const data = Array(numSessions).fill(0);
            props.cgdg.bible_quiz.classes.forEach(cls => {
                (cls.offerings_data || []).forEach((amt, i) => { data[i] += Number(amt || 0); });
            });
            series.push({ name: 'Kinh Thánh TN', data });
        }
    }
    
    return series;
});

const cgdgOfferingsOpts = computed(() => ({
    chart: { type: 'area', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    stroke: { curve: 'smooth', width: 2.5 },
    colors: ['#6366f1', '#ec4899'],
    fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.05 } },
    xaxis: { 
        // Labels 1, 2, 3...
        labels: { formatter: (val) => val, style: { fontSize: '10px' } }
    },
    yaxis: { labels: { formatter: formatCurrency, style: { fontSize: '10px' } } },
    grid: { strokeDashArray: 4 },
    legend: { position: 'bottom', fontSize: '10px' },
    tooltip: { y: { formatter: val => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val) } }
}));

// 5. Visitation Mini Chart
const visitationSeries = computed(() => [
    { name: 'Đã thăm', data: props.visitation_chart?.map(d => d.count) || [] }
]);
const visitationChartOpts = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'Inter, sans-serif', sparkline: { enabled: false } },
    plotOptions: { bar: { horizontal: false, columnWidth: '60%', borderRadius: 2 } },
    colors: ['#a855f7'],
    dataLabels: { enabled: false },
    xaxis: { categories: props.visitation_chart?.map(d => d.month) || [], labels: { show: false } },
    yaxis: { labels: { show: false } },
    grid: { show: false },
    tooltip: {
        x: { formatter: function (val) { return "Tháng " + val } },
        y: { formatter: function (val) { return val + " người" } }
    }
}));

</script>