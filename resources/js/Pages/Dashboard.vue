<template>
  <component :is="currentLayout">
    <template #header>Dashboard Mục Sư</template>

    <div class="py-4 space-y-6 w-full">

      <!-- Hero Banner -->
      <div class="rounded-2xl bg-gradient-to-br from-blue-700 to-blue-900 p-6 sm:p-8 text-white relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 opacity-10 pointer-events-none flex items-center justify-end pr-8">
          <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        </div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center gap-4">
          <div class="flex-1">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-300 mb-1">TỔNG QUAN × HỘI THÁNH</p>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Dashboard Mục Sư</h1>
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
          <h3 class="text-sm font-black text-white">🔔 BÁO CÁO CHỜ DUYỆT</h3>
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
          <h3 class="text-sm font-black text-white">📅 CÁC BUỔI NHÓM THÁNG {{ localMonth }}/{{ localYear }}</h3>
          <div class="flex gap-2">
            <button @click="meetingFilter = 'all'" class="text-xs font-bold px-3 py-1 rounded-full transition-colors"
              :class="meetingFilter === 'all' ? 'bg-white text-blue-900' : 'text-blue-200 hover:bg-white/10'">Tất cả</button>
            <button @click="meetingFilter = 'church'" class="text-xs font-bold px-3 py-1 rounded-full transition-colors"
              :class="meetingFilter === 'church' ? 'bg-white text-blue-900' : 'text-blue-200 hover:bg-white/10'">Hội Thánh</button>
            <button @click="meetingFilter = 'department'" class="text-xs font-bold px-3 py-1 rounded-full transition-colors"
              :class="meetingFilter === 'department' ? 'bg-white text-blue-900' : 'text-blue-200 hover:bg-white/10'">Ban Ngành</button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-blue-50">
              <tr>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900">Ngày</th>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900">Ban ngành</th>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900">Chủ đề</th>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900 hidden lg:table-cell">Câu gốc</th>
                <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-900 hidden md:table-cell">Diễn giả</th>
                <th class="px-4 py-2.5 text-center text-xs font-bold text-blue-900">HD</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="m in filteredMeetings" :key="m.id" class="hover:bg-blue-50/40">
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

      <!-- ══ SECTION 2B: ADVANCED ANALYTICS (PHASE 11) ══ -->
      <div v-if="analytics" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Tăng trưởng tín hữu (Line) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:col-span-2">
          <h3 class="text-sm font-bold text-gray-900 mb-1">📈 Tốc độ tăng trưởng Tín hữu (6 tháng)</h3>
          <p class="text-[10px] text-gray-400 mb-4">Lũy kế tổng tín hữu và số người mới gia nhập mỗi tháng</p>
          <apexchart type="line" height="240" :options="growthChartOpts" :series="growthSeries" />
        </div>
        
        <!-- Phân bố Ban ngành (Pie) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col items-center justify-center">
          <h3 class="text-sm font-bold text-gray-900 mb-1 w-full text-center">🎯 Phân số Tín hữu theo Ban</h3>
          <p class="text-[10px] text-gray-400 mb-4 w-full text-center">Tỷ lệ thành viên tham gia sinh hoạt</p>
          <apexchart type="donut" width="100%" height="240" :options="demoChartOpts" :series="demoSeries" />
        </div>
        
        <!-- Tài chính (Bar) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:col-span-3">
          <h3 class="text-sm font-bold text-gray-900 mb-1">💰 Tổng quan Tài chính (6 tháng)</h3>
          <p class="text-[10px] text-gray-400 mb-4">So sánh thu - chi toàn khoản của hệ thống</p>
          <apexchart type="bar" height="280" :options="financeChartOpts" :series="financeSeries" />
        </div>
      </div>

      <!-- ══ SECTION 3: BIỂU ĐỒ HIỆN DIỆN ══ -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Biểu đồ ban ngành -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <h3 class="text-sm font-bold text-gray-900 mb-1">📊 Hiện Diện Ban Ngành Theo Tuần</h3>
          <p class="text-[10px] text-gray-400 mb-4">Mỗi đường = 1 ban ngành · Tuần 1-5 trong tháng</p>
          <apexchart v-if="dept_att_series.length > 0" type="line" height="240"
            :options="deptLineOpts" :series="dept_att_series" />
          <div v-else class="h-56 flex items-center justify-center text-gray-300">
            <p class="text-xs">Chưa có dữ liệu</p>
          </div>
        </div>
        <!-- Biểu đồ hội thánh -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
          <h3 class="text-sm font-bold text-gray-900 mb-1">⛪ Hiện Diện Hội Thánh Theo Tuần</h3>
          <p class="text-[10px] text-gray-400 mb-4">Số lượng tham dự buổi nhóm hội thánh mỗi tuần</p>
          <apexchart type="area" height="240"
            :options="churchLineOpts" :series="[church_att_line]" />
        </div>
      </div>

      <!-- ══ SECTION 4: CƠ ĐỐC GIÁO DỤC ══ -->
      <div class="space-y-4">
        <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
          📚 <span>BAN CƠ ĐỐC GIÁO DỤC</span>
        </h3>

        <!-- 3 loại lớp CGDG -->
        <div v-for="(cgdgGroup, key) in cgdg" :key="key" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-5 py-3 bg-indigo-900 flex items-center justify-between">
            <h4 class="text-sm font-black text-white">{{ cgdgGroup.label }}</h4>
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ cgdgGroup.classes.length }} lớp</span>
          </div>
          <div v-if="cgdgGroup.classes.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-indigo-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-bold text-indigo-900">Lớp</th>
                  <!-- bible_quiz: Số bài | Số người tham gia | Điểm TB | Bài gần nhất -->
                  <template v-if="key === 'bible_quiz'">
                    <th class="px-4 py-2 text-center text-xs font-bold text-indigo-900">Số bài</th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-indigo-900">Số người tham gia</th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-indigo-900">Điểm Trung Bình</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-indigo-900 hidden md:table-cell">Bài gần nhất</th>
                  </template>
                  <!-- Các loại lớp khác: Số buổi | Tổng HD | Buổi gần nhất -->
                  <template v-else>
                    <th class="px-4 py-2 text-center text-xs font-bold text-indigo-900">Số buổi</th>
                    <th class="px-4 py-2 text-center text-xs font-bold text-indigo-900">Tổng HD</th>
                    <th class="px-4 py-2 text-left text-xs font-bold text-indigo-900 hidden md:table-cell">Buổi gần nhất</th>
                  </template>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="cls in cgdgGroup.classes" :key="cls.class_id" class="hover:bg-indigo-50/40">
                  <td class="px-4 py-2.5 text-xs font-bold text-gray-900">{{ cls.class_name }}</td>
                  <!-- bible_quiz row -->
                  <template v-if="key === 'bible_quiz'">
                    <td class="px-4 py-2.5 text-center text-sm text-gray-500">{{ cls.sessions.length }}</td>
                    <td class="px-4 py-2.5 text-center text-sm font-black text-indigo-700">{{ cls.scored_total ?? 0 }}</td>
                    <td class="px-4 py-2.5 text-center text-sm font-black" :class="cls.avg_score_all ? 'text-emerald-700' : 'text-gray-300'">
                      {{ cls.avg_score_all != null ? cls.avg_score_all + 'đ' : '—' }}
                    </td>
                    <td class="px-4 py-2.5 text-xs text-gray-600 hidden md:table-cell">
                      <span v-if="cls.sessions.length > 0">
                        {{ cls.sessions[cls.sessions.length - 1].date }}
                        <span v-if="cls.sessions[cls.sessions.length - 1].avg_score != null" class="text-emerald-600 font-bold ml-1">
                          · TB: {{ cls.sessions[cls.sessions.length - 1].avg_score }}đ
                        </span>
                        <span v-else class="text-gray-400 ml-1">· Chưa chấm</span>
                      </span>
                      <span v-else class="text-gray-300">—</span>
                    </td>
                  </template>
                  <!-- Other class types row -->
                  <template v-else>
                    <td class="px-4 py-2.5 text-center text-sm text-gray-500">{{ cls.sessions.length }}</td>
                    <td class="px-4 py-2.5 text-center text-sm font-black text-amber-700">{{ cls.total }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-600 hidden md:table-cell">
                      <span v-if="cls.sessions.length > 0">
                        {{ cls.sessions[cls.sessions.length - 1].date }} — {{ cls.sessions[cls.sessions.length - 1].topic || '—' }}
                      </span>
                      <span v-else class="text-gray-300">—</span>
                    </td>
                  </template>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="px-5 py-6 text-center text-xs text-gray-400">Chưa có dữ liệu cho tháng này</div>
        </div>
      </div>

      <!-- ══ SECTION 5: SINH NHẬT ══ -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-pink-600 flex items-center justify-between">
          <h3 class="text-sm font-black text-white">🎂 SINH NHẬT THÁNG {{ localMonth }}</h3>
          <span class="bg-white/30 text-white text-xs font-bold px-3 py-1 rounded-full">{{ birthdays.length }} người</span>
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
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-bold text-gray-600">Tên</th>
                  <th class="px-4 py-2 text-center text-xs font-bold text-gray-600">Ngày SN</th>
                  <th class="px-4 py-2 text-center text-xs font-bold text-gray-600">Tuổi</th>
                  <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 hidden sm:table-cell">SĐT</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="b in birthdays" :key="b.id"
                  :class="b.is_today ? 'bg-pink-50' : b.is_this_week ? 'bg-pink-50/40' : 'hover:bg-gray-50/40'">
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
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-purple-800 flex items-center justify-between">
          <h3 class="text-sm font-black text-white">🤝 THĂM VIẾNG THÁNG {{ localMonth }}</h3>
          <div class="flex gap-2 text-xs font-bold">
            <span class="bg-white/20 text-white px-3 py-1 rounded-full">{{ visitation_stats.total }} lượt</span>
            <span class="bg-green-400/30 text-green-200 px-3 py-1 rounded-full">✓ {{ visitation_stats.completed }}</span>
          </div>
        </div>
        <div v-if="visitations.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600">Ngày</th>
                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600">Tín hữu</th>
                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 hidden md:table-cell">Ban ngành</th>
                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 hidden lg:table-cell">Lý do</th>
                <th class="px-4 py-2 text-center text-xs font-bold text-gray-600">Trạng thái</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="v in visitations" :key="v.id" class="hover:bg-purple-50/30">
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

      <!-- ══ SECTION 7 + 8: TÍN HỮU MỚI & NGÀY ĐẶC BIỆT ══ -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Tín hữu mới -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-5 py-3 bg-emerald-700 flex items-center justify-between">
            <h3 class="text-sm font-black text-white">✝️ TÍN HỮU MỚI TIN CHÚA</h3>
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ new_members_30.length + new_members_90.length }}</span>
          </div>

          <!-- 30 ngày -->
          <div v-if="new_members_30.length > 0">
            <div class="px-4 py-2 bg-emerald-50 border-b border-emerald-100">
              <p class="text-xs font-black text-emerald-800">🌟 Trong 30 ngày qua ({{ new_members_30.length }} người)</p>
            </div>
            <table class="min-w-full divide-y divide-gray-100">
              <tbody>
                <tr v-for="m in new_members_30" :key="m.id" class="hover:bg-gray-50/40">
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
                <tr v-for="m in new_members_90" :key="m.id" class="hover:bg-gray-50/40">
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

        <!-- Ngày đặc biệt -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-5 py-3 bg-amber-600 flex items-center justify-between">
            <h3 class="text-sm font-black text-white">🌟 NGÀY ĐẶC BIỆT THÁNG {{ localMonth }}</h3>
            <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">{{ special_dates.length }}</span>
          </div>
          <div v-if="special_dates.length > 0">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-amber-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-bold text-amber-900">Tên</th>
                  <th class="px-4 py-2 text-left text-xs font-bold text-amber-900">Loại</th>
                  <th class="px-4 py-2 text-center text-xs font-bold text-amber-900">Ngày</th>
                  <th class="px-4 py-2 text-center text-xs font-bold text-amber-900">Số năm</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="d in special_dates" :key="`${d.id}-${d.type}`" class="hover:bg-amber-50/30">
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
    new_members_30: Array,
    new_members_90: Array,
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
const meetingFilter = ref('all');
const filteredMeetings = computed(() => {
    if (meetingFilter.value === 'all') return props.meetings || [];
    return (props.meetings || []).filter(m => m.type === meetingFilter.value);
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
    colors: ['#3b82f6', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#6366f1'],
    legend: { position: 'bottom', fontSize: '10px' },
    dataLabels: { enabled: false },
    plotOptions: { pie: { donut: { size: '70%' } } }
}));

// 2. Finance (Bar)
const financeSeries = computed(() => [
    { name: 'Thu', data: props.analytics?.finance_chart?.map(d => d.income) || [] },
    { name: 'Chi', data: props.analytics?.finance_chart?.map(d => d.expense) || [] }
]);
const financeChartOpts = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    plotOptions: { bar: { horizontal: false, columnWidth: '40%', borderRadius: 4 } },
    dataLabels: { enabled: false },
    stroke: { show: true, width: 2, colors: ['transparent'] },
    xaxis: { categories: props.analytics?.finance_chart?.map(d => d.month) || [], labels: { style: { fontSize: '11px' } } },
    yaxis: { labels: { formatter: formatCurrency, style: { fontSize: '11px' } } },
    colors: ['#10b981', '#ef4444'], // Thu xanh, Chi đỏ
    fill: { opacity: 1 },
    grid: { strokeDashArray: 4 },
    tooltip: { 
        y: { formatter: val => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val) }
    }
}));

// 3. Growth (Mixed Line & Column)
const growthSeries = computed(() => [
    { name: 'Tổng Tín hữu', type: 'line', data: props.analytics?.growth_chart?.map(d => d.total) || [] },
    { name: 'Người mới', type: 'column', data: props.analytics?.growth_chart?.map(d => d.new) || [] }
]);
const growthChartOpts = computed(() => ({
    chart: { height: 240, type: 'line', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    stroke: { width: [3, 0], curve: 'smooth' },
    plotOptions: { bar: { columnWidth: '20%', borderRadius: 4 } },
    colors: ['#3b82f6', '#f59e0b'],
    xaxis: { categories: props.analytics?.growth_chart?.map(d => d.month) || [], labels: { style: { fontSize: '11px' } } },
    yaxis: [{
        title: { text: 'Tổng số' },
        labels: { style: { fontSize: '10px' } }
    }, {
        opposite: true,
        title: { text: 'Người mới' },
        labels: { style: { fontSize: '10px' } },
        min: 0
    }],
    grid: { strokeDashArray: 4 },
}));

</script>