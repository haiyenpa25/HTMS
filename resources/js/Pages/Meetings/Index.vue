<template>
  <component :is="currentLayout">
    <template #header>
      Tổ chức Buổi Nhóm
    </template>

    <div class="py-4 space-y-5">

      <!-- ── Toolbar ─────────────────────────────────────────────────────── -->
      <div class="flex flex-wrap items-center gap-2">
        <!-- Search -->
        <div class="relative flex-1 min-w-[200px]">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input v-model="filterForm.search" type="text" placeholder="Tìm theo chủ đề, diễn giả..." class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all outline-none"/>
        </div>

        <!-- Filter: Loại -->
        <select v-model="filterForm.type" class="text-sm border border-gray-200 rounded-xl bg-gray-50 px-3 py-2.5 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none font-medium text-gray-700">
          <option value="">Tất cả loại</option>
          <option value="church">Hội Thánh</option>
          <option value="department">Ban Ngành</option>
        </select>

        <!-- Filter: Từ ngày -->
        <input type="date" v-model="filterForm.date_from" class="text-sm border border-gray-200 rounded-xl bg-gray-50 px-3 py-2.5 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none text-gray-700"/>
        <input type="date" v-model="filterForm.date_to" class="text-sm border border-gray-200 rounded-xl bg-gray-50 px-3 py-2.5 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none text-gray-700"/>

        <!-- Clear filter -->
        <button v-if="activeFilterCount > 0" @click="resetFilters" class="text-sm font-bold text-red-500 hover:text-red-700 px-3 py-2.5 hover:bg-red-50 rounded-xl transition-colors">
          ✕ Xóa lọc <span class="ml-1 bg-red-100 text-red-600 text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ activeFilterCount }}</span>
        </button>

        <div class="flex-1"/>

        <!-- Actions -->
        <a :href="exportUrl" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-sm font-bold hover:bg-emerald-100 transition-colors" title="Xuất Excel">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <span class="hidden sm:inline">Xuất Excel</span>
        </a>
        <button @click="showMeetingImportModal = true" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl text-sm font-bold hover:bg-amber-100 transition-colors" title="Import Excel">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
          <span class="hidden sm:inline">Import</span>
        </button>
        <a :href="route('education.index')" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-xl text-sm font-bold hover:bg-indigo-100 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          <span class="hidden sm:inline">CĐGD</span>
        </a>
        <button @click="openCreateSlideOver" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 active:scale-95 transition-all shadow-md shadow-indigo-200">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Lên lịch
        </button>
      </div>

      <!-- ── Selection bar ─────────────────────────────────────────────── -->
      <div v-if="selectedIds.length > 0" class="flex items-center justify-between bg-indigo-600 text-white rounded-2xl px-5 py-3 shadow-lg shadow-indigo-200">
        <div class="flex items-center gap-3">
          <button @click="selectedIds = []" class="text-indigo-200 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
          <span class="text-sm font-bold">Đã chọn <span class="bg-white/20 px-2 py-0.5 rounded-full font-black">{{ selectedIds.length }}</span> buổi nhóm</span>
        </div>
        <a :href="exportUrl" class="inline-flex items-center gap-2 px-4 py-1.5 bg-white text-indigo-700 text-sm font-bold rounded-lg hover:bg-indigo-50 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Xuất {{ selectedIds.length }} buổi nhóm
        </a>
      </div>

      <!-- ── Stats row ─────────────────────────────────────────────────── -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
          <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Tổng buổi nhóm</p>
          <p class="text-2xl font-black text-gray-900">{{ meetings.length }}</p>
        </div>
        <div class="bg-white border border-indigo-100 rounded-2xl p-4 shadow-sm">
          <p class="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-1">Hội Thánh</p>
          <p class="text-2xl font-black text-indigo-700">{{ meetings.filter(m => m.type === 'church').length }}</p>
        </div>
        <div class="bg-white border border-emerald-100 rounded-2xl p-4 shadow-sm">
          <p class="text-xs font-bold text-emerald-400 uppercase tracking-widest mb-1">Ban Ngành</p>
          <p class="text-2xl font-black text-emerald-700">{{ meetings.filter(m => m.type === 'department').length }}</p>
        </div>
        <div class="bg-white border border-amber-100 rounded-2xl p-4 shadow-sm">
          <p class="text-xs font-bold text-amber-400 uppercase tracking-widest mb-1">Đã chọn</p>
          <p class="text-2xl font-black text-amber-700">{{ selectedIds.length }}</p>
        </div>
      </div>

      <!-- ── Main Table ─────────────────────────────────────────────────── -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Table (Desktop) -->
        <div class="overflow-x-auto">
          <table class="min-w-full hidden md:table">
            <thead>
              <tr class="bg-gray-50/80 border-b border-gray-100">
                <th class="pl-5 pr-3 py-3.5 w-10">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    :indeterminate="isIndeterminate"
                    @change="toggleSelectAll"
                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                  />
                </th>
                <th class="px-4 py-3.5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Ngày / Giờ</th>
                <th class="px-4 py-3.5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Loại</th>
                <th class="px-4 py-3.5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Chủ đề / Kinh Thánh</th>
                <th class="px-4 py-3.5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Diễn giả</th>
                <th class="px-4 py-3.5 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Ban ngành</th>
                <th class="px-4 py-3.5 text-center text-[11px] font-black text-gray-400 uppercase tracking-widest">Thao tác</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <template v-for="meeting in meetings" :key="meeting.id">
              <tr
                class="hover:bg-indigo-50/30 transition-colors cursor-pointer"
                :class="{ 'bg-indigo-50/50': selectedIds.includes(meeting.id) }"
                @click="goToMeeting(meeting.id)"
              >
                <!-- Checkbox -->
                <td class="pl-5 pr-3 py-4" @click.stop>
                  <input
                    type="checkbox"
                    :value="meeting.id"
                    v-model="selectedIds"
                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                  />
                </td>

                <!-- Date -->
                <td class="px-4 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex flex-col items-center justify-center text-center shrink-0"
                      :class="meeting.type === 'church' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'"
                    >
                      <span class="text-[9px] font-bold uppercase leading-none">{{ formatMonthShort(meeting.date) }}</span>
                      <span class="text-base font-black leading-tight">{{ formatDay(meeting.date) }}</span>
                    </div>
                    <div>
                      <div class="text-sm font-bold text-gray-800">{{ formatDate(meeting.date) }}</div>
                      <div class="text-xs text-gray-400 font-medium">{{ meeting.time?.substring(0, 5) }}</div>
                    </div>
                  </div>
                </td>

                <!-- Type badge -->
                <td class="px-4 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider"
                    :class="meeting.type === 'church' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'"
                  >
                    {{ meeting.type === 'church' ? 'Hội Thánh' : 'Ban Ngành' }}
                  </span>
                </td>

                <!-- Topic — truncated with expand toggle -->
                <td class="px-4 py-4 max-w-xs" @click.stop>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <div class="text-sm font-bold text-gray-900" :class="expandedRows.has(meeting.id) ? '' : 'truncate'">{{ meeting.topic || '—' }}</div>
                      <div class="text-xs text-gray-400 font-medium mt-0.5" :class="expandedRows.has(meeting.id) ? '' : 'truncate'" v-if="meeting.scripture">📖 {{ meeting.scripture }}</div>
                      <div class="text-xs text-indigo-400 font-medium mt-0.5" v-if="expandedRows.has(meeting.id) && meeting.memory_verse">✨ {{ meeting.memory_verse }}</div>
                    </div>
                    <button
                      @click="toggleRowExpand(meeting.id)"
                      class="shrink-0 w-5 h-5 rounded text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors flex items-center justify-center"
                      :title="expandedRows.has(meeting.id) ? 'Thu gọn' : 'Xem đầy đủ'"
                    >
                      <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expandedRows.has(meeting.id) ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                  </div>
                </td>

                <!-- Preacher -->
                <td class="px-4 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-700 font-medium">{{ meeting.preacher || '—' }}</span>
                </td>

                <!-- Department -->
                <td class="px-4 py-4 whitespace-nowrap">
                  <span v-if="meeting.department" class="text-xs bg-gray-100 text-gray-700 font-bold px-2 py-1 rounded-lg">{{ meeting.department.name }}</span>
                  <span v-else class="text-xs text-gray-300">—</span>
                </td>

                <!-- Actions — ALWAYS VISIBLE on desktop -->
                <td class="px-4 py-4 whitespace-nowrap" @click.stop>
                  <div class="flex items-center justify-center gap-2">
                    <button
                      @click="openEditSlideOver(meeting)"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 rounded-lg hover:bg-indigo-100 hover:border-indigo-200 transition-colors"
                      title="Chỉnh sửa"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                      Sửa
                    </button>
                    <button
                      @click="goToMeeting(meeting.id)"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-600 bg-gray-50 border border-gray-100 rounded-lg hover:bg-gray-100 transition-colors"
                      title="Xem chi tiết"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                      Xem
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Expanded detail row -->
              <tr v-if="expandedRows.has(meeting.id)" class="bg-indigo-50/20">
                <td colspan="7" class="px-8 py-3 border-b border-indigo-100">
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div v-if="meeting.preacher"><span class="font-bold text-gray-500 block mb-0.5">Diễn Giả</span><span class="text-gray-800">{{ meeting.preacher }}</span></div>
                    <div v-if="meeting.scripture"><span class="font-bold text-gray-500 block mb-0.5">Phân Đoạn KT</span><span class="text-gray-800">📖 {{ meeting.scripture }}</span></div>
                    <div v-if="meeting.memory_verse"><span class="font-bold text-gray-500 block mb-0.5">Câu Gốc</span><span class="text-gray-800">✨ {{ meeting.memory_verse }}</span></div>
                    <div v-if="meeting.topic"><span class="font-bold text-gray-500 block mb-0.5">Chủ Đề Đầy Đủ</span><span class="text-gray-800">{{ meeting.topic }}</span></div>
                  </div>
                </td>
              </tr>
              </template>

              <!-- Empty State -->
              <tr v-if="meetings.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center space-y-3">
                    <div class="w-16 h-16 rounded-2xl bg-gray-50 flex items-center justify-center">
                      <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-sm font-bold text-gray-500">Không tìm thấy buổi nhóm nào.</p>
                    <p class="text-xs text-gray-400">Thử thay đổi bộ lọc hoặc tạo buổi nhóm mới.</p>
                    <button @click="openCreateSlideOver" class="mt-2 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-colors">+ Lên lịch buổi nhóm</button>
                  </div>
                </td>
              </tr>
            </tbody>

          </table>

          <!-- Mobile Card List -->
          <div class="md:hidden divide-y divide-gray-50">
            <div
              v-for="meeting in meetings"
              :key="'mob-'+meeting.id"
              class="p-4 flex items-start gap-3 cursor-pointer hover:bg-gray-50 transition-colors"
              @click="goToMeeting(meeting.id)"
            >
              <!-- Date icon -->
              <div class="w-11 h-11 rounded-xl flex flex-col items-center justify-center shrink-0"
                :class="meeting.type === 'church' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'"
              >
                <span class="text-[8px] font-bold uppercase leading-none">{{ formatMonthShort(meeting.date) }}</span>
                <span class="text-lg font-black leading-tight">{{ formatDay(meeting.date) }}</span>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded"
                    :class="meeting.type === 'church' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'"
                  >{{ meeting.type === 'church' ? 'HT' : 'BN' }}</span>
                  <span class="text-[10px] font-bold text-gray-400">{{ meeting.time?.substring(0, 5) }}</span>
                </div>
                <h4 class="text-sm font-black text-gray-900 truncate mt-0.5">{{ meeting.topic || '(Chưa có chủ đề)' }}</h4>
                <p class="text-xs text-gray-400 mt-0.5 truncate" v-if="meeting.scripture">📖 {{ meeting.scripture }}</p>
              </div>

              <!-- Mobile actions -->
              <button @click.stop="openEditSlideOver(meeting)" class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </button>
            </div>

            <div v-if="meetings.length === 0" class="p-10 text-center">
              <p class="text-sm font-medium text-gray-400">Chưa có dữ liệu.</p>
            </div>
          </div>
        </div>

        <!-- Table footer info -->
        <div v-if="meetings.length > 0" class="px-5 py-3 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
          <p class="text-xs text-gray-400 font-medium">Hiển thị {{ meetings.length }} buổi nhóm</p>
          <p v-if="activeFilterCount > 0" class="text-xs text-indigo-500 font-bold">Đang lọc với {{ activeFilterCount }} tiêu chí</p>
        </div>
      </div>

    </div>

    <!-- ── Import Modal ──────────────────────────────────────────────────── -->
    <div v-if="showMeetingImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6 space-y-5">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-black text-gray-900">Import cập nhật Buổi nhóm</h3>
            <p class="text-xs text-gray-500 mt-0.5">Cập nhật hàng loạt từ file Excel</p>
          </div>
          <button @click="showMeetingImportModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-amber-800 space-y-1">
          <p class="text-sm font-bold">Hướng dẫn:</p>
          <ol class="list-decimal ml-4 space-y-1 text-xs">
            <li>Xuất danh sách buổi nhóm ra Excel (nút <strong>Xuất Excel</strong>)</li>
            <li>Sửa các cột: <strong>Chủ đề, Câu gốc, Phân đoạn, Diễn giả</strong>...</li>
            <li>Giữ nguyên cột <strong>ID Buổi Nhóm</strong> (cột A), không xóa</li>
            <li>Để trống ô = giữ nguyên | Gõ <code class="bg-amber-100 px-1 rounded">-</code> = xóa nội dung</li>
          </ol>
        </div>
        <div>
          <label class="block text-sm font-bold text-gray-700 mb-1.5">Chọn file Excel (.xlsx)</label>
          <input type="file" accept=".xlsx,.xls" @change="e => meetingImportFile = e.target.files[0]"
            class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100"/>
        </div>
        <div class="flex items-center justify-end gap-3">
          <button @click="showMeetingImportModal = false" class="px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">Hủy</button>
          <button @click="submitMeetingsImport" :disabled="!meetingImportFile || meetingImportLoading"
            class="px-5 py-2 bg-amber-600 text-white text-sm font-bold rounded-xl hover:bg-amber-700 transition-colors disabled:opacity-50">
            {{ meetingImportLoading ? 'Đang import...' : '🚀 Import và cập nhật' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── SlideOver Form ────────────────────────────────────────────────── -->
    <SlideOver
      v-model="isSlideOverOpen"
      :title="selectedMeeting ? 'Chỉnh sửa Buổi nhóm' : 'Lên lịch Buổi nhóm'"
      :description="selectedMeeting ? 'Cập nhật lại thông tin buổi nhóm' : 'Tạo buổi nhóm mới cho Hội Thánh hoặc Ban Ngành'"
    >
      <MeetingForm
        v-if="isSlideOverOpen"
        :meeting="selectedMeeting"
        @close="closeSlideOver"
        @success="handleSuccess"
      />
    </SlideOver>

  </component>
</template>

<script setup>
import { ref, computed, watch, markRaw } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';
import MeetingForm from './Partials/MeetingForm.vue';

// ── Layout ───────────────────────────────────────────────────────────────────
const isMobile = ref(window.innerWidth < 768);
const currentLayout = computed(() => isMobile.value ? markRaw(MobileLayout) : markRaw(AuthenticatedLayout));
window.addEventListener('resize', debounce(() => { isMobile.value = window.innerWidth < 768; }, 250));

// ── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
  meetings: Array,
  departments: Array,
  filters: Object,
});

// ── Filters ──────────────────────────────────────────────────────────────────
const filterForm = ref({
  search: props.filters?.search || '',
  type: props.filters?.type || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
});

const activeFilterCount = computed(() => {
  let count = 0;
  if (filterForm.value.type) count++;
  if (filterForm.value.date_from) count++;
  if (filterForm.value.date_to) count++;
  if (filterForm.value.search) count++;
  return count;
});

const resetFilters = () => {
  filterForm.value = { search: '', type: '', date_from: '', date_to: '' };
};

watch(filterForm, debounce((v) => {
  router.get(route('meetings.index'), v, { preserveState: true, replace: true });
}, 300), { deep: true });

// ── Date helpers ─────────────────────────────────────────────────────────────
const formatDate = (d) => {
  if (!d) return '';
  return new Date(d).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
const formatDay = (d) => {
  if (!d) return '';
  return new Date(d).getDate();
};
const formatMonthShort = (d) => {
  if (!d) return '';
  return new Date(d).toLocaleDateString('vi-VN', { month: 'short' }).replace('.', '');
};

// ── Checkbox selection ────────────────────────────────────────────────────────
const selectedIds = ref([]);

// ── Row expand ────────────────────────────────────────────────────────────────
const expandedRows = ref(new Set());
const toggleRowExpand = (id) => {
  const s = new Set(expandedRows.value);
  if (s.has(id)) { s.delete(id); } else { s.add(id); }
  expandedRows.value = s;
};

const isAllSelected = computed(() =>
  props.meetings.length > 0 && selectedIds.value.length === props.meetings.length
);
const isIndeterminate = computed(() =>
  selectedIds.value.length > 0 && selectedIds.value.length < props.meetings.length
);
const toggleSelectAll = () => {
  selectedIds.value = isAllSelected.value ? [] : props.meetings.map(m => m.id);
};

watch(() => props.meetings, () => { selectedIds.value = []; });

// ── Export URL ────────────────────────────────────────────────────────────────
const exportUrl = computed(() => {
  const params = new URLSearchParams();
  if (selectedIds.value.length > 0) {
    params.set('ids', selectedIds.value.join(','));
  } else {
    if (filterForm.value.type) params.set('type', filterForm.value.type);
    if (filterForm.value.date_from) params.set('date_from', filterForm.value.date_from);
    if (filterForm.value.date_to) params.set('date_to', filterForm.value.date_to);
  }
  const qs = params.toString();
  return route('meetings.export') + (qs ? '?' + qs : '');
});

// ── SlideOver ─────────────────────────────────────────────────────────────────
const isSlideOverOpen = ref(false);
const selectedMeeting = ref(null);

const openCreateSlideOver = () => {
  selectedMeeting.value = null;
  isSlideOverOpen.value = true;
};
const openEditSlideOver = (meeting) => {
  selectedMeeting.value = meeting;
  isSlideOverOpen.value = true;
};
const closeSlideOver = () => {
  isSlideOverOpen.value = false;
  setTimeout(() => { selectedMeeting.value = null; }, 300);
};
const handleSuccess = () => {
  router.reload({ only: ['meetings'] });
};

const goToMeeting = (id) => {
  router.get(route('meetings.show', id));
};

// ── Meeting Import ─────────────────────────────────────────────────────────────
const showMeetingImportModal = ref(false);
const meetingImportFile      = ref(null);
const meetingImportLoading   = ref(false);

const submitMeetingsImport = () => {
  if (!meetingImportFile.value) return;
  meetingImportLoading.value = true;
  const data = new FormData();
  data.append('file', meetingImportFile.value);
  router.post(route('meetings.import'), data, {
    forceFormData: true,
    onSuccess: () => {
      showMeetingImportModal.value = false;
      meetingImportFile.value = null;
      router.reload({ only: ['meetings'] });
    },
    onError: () => {},
    onFinish: () => { meetingImportLoading.value = false; },
  });
};
</script>