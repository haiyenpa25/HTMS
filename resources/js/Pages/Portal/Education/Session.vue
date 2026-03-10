<template>
    <PortalLayout :department="null" :available-departments="[]" :is-global-admin="false" :portalType="portalType">
        <div class="min-h-screen bg-gray-50">
            <!-- Focus Mode Header -->
            <div class="bg-gradient-to-r from-indigo-700 to-indigo-600 text-white">
                <!-- Row 1: back + class name + CN navigation -->
                <div class="max-w-5xl mx-auto px-4 pt-3 pb-2 flex items-center gap-3">
                    <Link :href="route('education.sessions', eduClass.id)" class="p-1.5 rounded-lg hover:bg-white/10 transition-colors shrink-0" title="Danh sách buổi học">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </Link>
                    <div class="flex-1 min-w-0">
                        <h1 class="font-black text-base leading-tight truncate">{{ eduClass.name }}</h1>
                        <p class="text-indigo-200 text-xs">{{  eduClass.class_type === 'gospel' ? 'Lớp Giáo Lý (linh hoạt)' : 'Buổi nhóm CĐGD — Cố định Chủ Nhật' }}</p>
                    </div>
                    <!-- CN navigation -->
                    <div class="flex items-center gap-1 shrink-0">
                        <button @click="goToSunday(navigation.prev_sunday)"
                            class="p-1.5 rounded-lg hover:bg-white/20 transition-colors"
                            title="Chủ Nhật trước">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <span class="text-xs font-black bg-white/15 px-3 py-1.5 rounded-lg min-w-[120px] text-center">
                            {{ formatShortDate(navigation.current_date) }}
                        </span>
                        <button @click="goToSunday(navigation.next_sunday)"
                            :disabled="!navigation.can_go_next"
                            :class="navigation.can_go_next ? 'hover:bg-white/20' : 'opacity-30 cursor-not-allowed'"
                            class="p-1.5 rounded-lg transition-colors"
                            title="Chủ Nhật sau">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Row 2: Lesson Info Card -->
                <div class="max-w-5xl mx-auto px-4 pb-2">
                    <!-- Has lesson info -->
                    <div v-if="session.topic || session.lesson_number || session.lesson_series"
                        class="bg-white/10 backdrop-blur rounded-xl px-4 py-3 flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span v-if="session.lesson_number" class="bg-white text-indigo-700 text-xs font-black px-2 py-0.5 rounded-full shrink-0">
                                    Bài {{ session.lesson_number }}
                                </span>
                                <span v-if="session.lesson_series" class="text-indigo-200 text-xs font-bold truncate">
                                    {{ session.lesson_series }}
                                </span>
                                <!-- Teacher badge -->
                                <span v-if="session.teacher_name" class="bg-amber-400/25 text-amber-200 text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    GV: {{ session.teacher_name }}
                                </span>
                            </div>
                            <p v-if="session.topic" class="font-black text-white text-base mt-1 leading-snug truncate">
                                &ldquo;{{ session.topic }}&rdquo;
                            </p>
                            <p v-if="session.scripture" class="text-indigo-200 text-xs mt-0.5">
                                📖 {{ session.scripture }}
                            </p>
                        </div>
                        <button v-if="canMarkAttendance" @click="isSessionInfoOpen = true"
                            class="shrink-0 p-1.5 bg-white/10 hover:bg-white/25 rounded-lg transition-colors" title="Sửa thông tin bài học">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                    </div>
                    <!-- No lesson info yet -->
                    <button v-else-if="canMarkAttendance" @click="isSessionInfoOpen = true"
                        class="w-full bg-white/10 hover:bg-white/20 border border-dashed border-white/30 rounded-xl px-4 py-3 text-left transition-colors flex items-center gap-3">
                        <svg class="w-5 h-5 text-indigo-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <div>
                            <p class="text-white text-sm font-bold">✕ Chưa có thông tin bài học</p>
                            <p class="text-indigo-300 text-xs">Nhấn để nhập Bài số, Tên bài, Loạt bài, Câu gốc</p>
                        </div>
                    </button>
                </div>

                <!-- Tabs -->
                <div class="max-w-5xl mx-auto px-4 flex border-t border-white/10">
                    <!-- bible_quiz: chỉ hiện 2 tab riêng -->
                    <template v-if="eduClass.class_type === 'bible_quiz'">
                        <button @click="activeTab = 'scores'" :class="activeTab === 'scores' ? 'border-b-2 border-white text-white' : 'text-indigo-300 hover:text-white'" class="px-4 py-2.5 text-sm font-bold transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Chấm Điểm
                            <span v-if="scoredCount > 0" class="bg-amber-400/30 text-amber-100 text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ scoredCount }}/{{ localRecords.length }}</span>
                        </button>
                        <button @click="activeTab = 'ranking'" :class="activeTab === 'ranking' ? 'border-b-2 border-white text-white' : 'text-indigo-300 hover:text-white'" class="px-4 py-2.5 text-sm font-bold transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Xếp Hạng
                        </button>
                    </template>
                    <!-- Các loại lớp khác -->
                    <template v-else>
                        <button @click="activeTab = 'attendance'" :class="activeTab === 'attendance' ? 'border-b-2 border-white text-white' : 'text-indigo-300 hover:text-white'" class="px-4 py-2.5 text-sm font-bold transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            Điểm Danh
                            <span class="bg-white/20 text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ presentCount }}/{{ localRecords.length }}</span>
                        </button>
                        <button v-if="eduClass.class_type !== 'gospel'" @click="activeTab = 'finance'" :class="activeTab === 'finance' ? 'border-b-2 border-white text-white' : 'text-indigo-300 hover:text-white'" class="px-4 py-2.5 text-sm font-bold transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Tiền Dâng
                            <span v-if="totalSessionIncome > 0" class="bg-green-400/30 text-green-100 text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ formatMoney(totalSessionIncome) }}</span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Flash message -->
            <div v-if="page.props.flash?.success" class="bg-green-50 border-b border-green-200 px-4 py-2.5 text-sm text-green-700 font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ page.props.flash.success }}
            </div>

            <div class="max-w-5xl mx-auto px-4 py-5">

                <!-- ── TAB: ĐIỂM DANH ────────────────────────────────────── -->
                <div v-if="activeTab === 'attendance'">

                    <!-- Mode Toggle -->
                    <div class="flex items-center gap-1 mb-4 bg-gray-100 p-1 rounded-xl w-fit">
                        <button @click="attendanceMode = 'quick'"
                            :class="attendanceMode === 'quick' ? 'bg-white text-indigo-700 shadow-sm font-black' : 'text-gray-500 font-medium hover:text-gray-700'"
                            class="px-4 py-1.5 rounded-lg text-sm transition-all">
                            ⚡ Nhập nhanh
                        </button>
                        <button @click="attendanceMode = 'checkin'"
                            :class="attendanceMode === 'checkin' ? 'bg-white text-indigo-700 shadow-sm font-black' : 'text-gray-500 font-medium hover:text-gray-700'"
                            class="px-4 py-1.5 rounded-lg text-sm transition-all">
                            ✓ Điểm danh tên
                        </button>
                    </div>

                    <!-- ── QUICK MODE ── -->
                    <div v-if="attendanceMode === 'quick'">
                        <div class="bg-white rounded-2xl border border-indigo-100 shadow-sm p-6 space-y-5">
                            <div class="flex items-center gap-2 text-indigo-700 mb-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span class="font-black text-sm">Nhập số nhanh — không cần tích tên từng người</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">✅ Có mặt</label>
                                    <input v-model.number="quickPresent" type="number" min="0"
                                        class="w-full rounded-xl border-green-300 focus:border-green-500 focus:ring-green-500 text-center text-2xl font-black text-green-700 py-3 shadow-sm"
                                        placeholder="0" />
                                </div>
                                <div>
                                    <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">❌ Vắng</label>
                                    <input v-model.number="quickAbsent" type="number" min="0"
                                        class="w-full rounded-xl border-red-300 focus:border-red-500 focus:ring-red-500 text-center text-2xl font-black text-red-500 py-3 shadow-sm"
                                        placeholder="0" />
                                </div>
                            </div>
                            <!-- Live preview -->
                            <div class="flex items-center justify-center gap-6 bg-indigo-50 rounded-xl py-3 text-sm">
                                <span class="text-gray-600">Tổng ghi nhận: <strong class="text-gray-900">{{ (quickPresent || 0) + (quickAbsent || 0) }}</strong> người</span>
                                <span class="text-green-700 font-bold">✅ {{ quickPresent || 0 }} có mặt</span>
                                <span class="text-red-600 font-bold">❌ {{ quickAbsent || 0 }} vắng</span>
                            </div>
                            <button v-if="canMarkAttendance" @click="saveQuick" :disabled="attendanceLoading"
                                class="w-full py-3 bg-gradient-to-r from-green-600 to-green-500 text-white font-black rounded-xl hover:from-green-700 hover:to-green-600 transition-all shadow-sm disabled:opacity-50 flex items-center justify-center gap-2">
                                <svg v-if="attendanceLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ attendanceLoading ? 'Đang lưu...' : '⚡ Lưu Nhanh' }}
                            </button>
                            <!-- Show last saved data -->
                            <div v-if="session.attendance_mode === 'quick' && session.total_present !== null"
                                class="text-center text-xs text-gray-400 italic">
                                Lần lưu trước: {{ session.total_present }} có mặt · {{ session.total_absent ?? 0 }} vắng
                            </div>
                        </div>
                    </div>

                    <!-- ── CHECK-IN MODE ── -->
                    <div v-else>
                    <!-- Quick stats -->
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="bg-white rounded-xl border border-gray-100 py-3 text-center shadow-sm">
                            <div class="text-2xl font-black text-green-600">{{ presentCount }}</div>
                            <div class="text-[11px] text-gray-500 font-medium">Có mặt</div>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-100 py-3 text-center shadow-sm">
                            <div class="text-2xl font-black text-red-500">{{ absentCount }}</div>
                            <div class="text-[11px] text-gray-500 font-medium">Vắng</div>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-100 py-3 text-center shadow-sm">
                            <div class="text-2xl font-black text-indigo-600">{{ memorizedCount }}</div>
                            <div class="text-[11px] text-gray-500 font-medium">Thuộc câu gốc</div>
                        </div>
                    </div>

                    <!-- Bulk actions -->
                    <div v-if="canMarkAttendance" class="flex gap-2 mb-4">
                        <button @click="markAll('present')" class="flex-1 py-2 text-sm font-bold bg-green-50 text-green-700 border border-green-200 rounded-xl hover:bg-green-100 transition-colors">✅ Tất cả có mặt</button>
                        <button @click="markAll('absent')" class="flex-1 py-2 text-sm font-bold bg-gray-50 text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-100 transition-colors">❌ Tất cả vắng</button>
                    </div>

                    <!-- Empty state -->
                    <div v-if="localRecords.length === 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm py-12 text-center">
                        <div class="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h3 class="font-bold text-gray-600 text-sm mb-1">Lớp chưa có học viên</h3>
                        <p class="text-xs text-gray-400">Nhờ admin thêm học viên vào lớp.</p>
                    </div>

                    <!-- Student list -->
                    <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="divide-y divide-gray-100">
                            <div v-for="rec in localRecords" :key="rec.member_id" class="px-4 py-3 flex items-center gap-3 hover:bg-gray-50/50 transition-colors">
                                <!-- Avatar -->
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-black shrink-0 transition-colors"
                                    :class="rec.attendance === 'present' ? 'bg-green-100 text-green-800' : rec.attendance === 'excused' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-700'">
                                    {{ rec.full_name?.charAt(0) }}
                                </div>
                                <!-- Name -->
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-sm text-gray-900 truncate">{{ rec.full_name }}</div>
                                    <div v-if="rec.phone" class="text-xs text-gray-400">{{ rec.phone }}</div>
                                </div>
                                <!-- Edit controls -->
                                <div v-if="canMarkAttendance" class="flex items-center gap-2 shrink-0">
                                    <!-- Attendance toggle -->
                                    <div class="flex rounded-lg border border-gray-200 overflow-hidden text-xs">
                                        <button @click="rec.attendance = 'present'" :class="rec.attendance === 'present' ? 'bg-green-500 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'" class="px-2 py-1.5 font-bold transition-colors border-r border-gray-200">✓</button>
                                        <button @click="rec.attendance = 'excused'" :class="rec.attendance === 'excused' ? 'bg-yellow-400 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'" class="px-2 py-1.5 font-bold transition-colors border-r border-gray-200">P</button>
                                        <button @click="rec.attendance = 'absent'" :class="rec.attendance === 'absent' ? 'bg-red-500 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'" class="px-2 py-1.5 font-bold transition-colors">✗</button>
                                    </div>
                                    <!-- Memorize toggle -->
                                    <button @click="rec.memorized_verse = !rec.memorized_verse"
                                        :class="rec.memorized_verse ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-white text-gray-300 border-gray-200'"
                                        class="px-2 py-1.5 border rounded-lg text-xs font-bold transition-colors" title="Thuộc câu gốc">
                                        📖
                                    </button>
                                    <!-- Quiz score -->
                                    <input v-model.number="rec.quiz_score" type="number" min="0" max="100" placeholder="Đ"
                                        class="w-14 text-xs border border-gray-200 rounded-lg py-1.5 px-2 text-center focus:ring-indigo-500 focus:border-indigo-500" />
                                </div>
                                <!-- Read-only -->
                                <div v-else class="flex items-center gap-2 shrink-0">
                                    <span :class="rec.attendance === 'present' ? 'bg-green-100 text-green-700' : rec.attendance === 'excused' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'" class="text-xs font-bold px-2 py-0.5 rounded-full">
                                        {{ rec.attendance === 'present' ? 'Có mặt' : rec.attendance === 'excused' ? 'Có phép' : 'Vắng' }}
                                    </span>
                                    <span v-if="rec.memorized_verse" class="text-lg" title="Thuộc câu gốc">📖</span>
                                    <span v-if="rec.quiz_score !== null" class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full">{{ rec.quiz_score }}đ</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save button -->
                    <div v-if="canMarkAttendance && localRecords.length > 0" class="mt-4">
                        <button @click="saveAttendance" :disabled="attendanceLoading" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white font-black rounded-xl hover:from-indigo-700 hover:to-indigo-600 transition-all shadow-sm disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg v-if="attendanceLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ attendanceLoading ? 'Đang lưu...' : '✅ Lưu Điểm Danh' }}
                        </button>
                    </div>
                    </div><!-- end checkin mode -->
                </div>


                <!-- ── TAB: CHẤM ĐIỂM (bible_quiz) ────────────────────────────── -->
                <div v-if="activeTab === 'scores' && eduClass.class_type === 'bible_quiz'" class="space-y-4">

                    <!-- Header thông tin bài thi -->
                    <div class="bg-amber-50 rounded-2xl border border-amber-100 px-5 py-3 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-3 flex-wrap">
                                <span v-if="session.book" class="bg-amber-200 text-amber-900 text-xs font-black px-2.5 py-0.5 rounded-full">Sách: {{ session.book }}</span>
                                <span v-if="session.total_questions" class="bg-indigo-100 text-indigo-800 text-xs font-black px-2.5 py-0.5 rounded-full">ℓ {{ session.total_questions }} câu</span>
                                <span v-if="session.grader_name" class="bg-gray-100 text-gray-700 text-xs font-bold px-2.5 py-0.5 rounded-full">👤 {{ session.grader_name }}</span>
                                <span v-if="!session.book && !session.total_questions" class="text-amber-600 text-xs italic">Chưa nhập thông tin bài thi</span>
                            </div>
                            <div class="flex items-center gap-4 mt-1.5 text-xs text-gray-500">
                                <span class="text-green-600 font-bold">✔ {{ scoredCount }}/{{ localRecords.length }} đã chấm</span>
                                <span v-if="avgScore !== '—'" class="text-amber-700 font-bold">ĐTB: {{ avgScore }}/{{ session.total_questions || 100 }}</span>
                            </div>
                        </div>
                        <button v-if="canMarkAttendance" @click="isQuizInfoOpen = true"
                            class="shrink-0 text-xs font-bold text-amber-700 px-3 py-1.5 bg-amber-100 hover:bg-amber-200 rounded-xl transition-colors">
                            ✏️ Thông tin
                        </button>
                    </div>

                    <!-- Ảnh bài thi -->
                    <div v-if="session.photo_path" class="bg-white rounded-xl border border-gray-100 p-3">
                        <a :href="session.photo_path" target="_blank">
                            <img :src="session.photo_path" class="max-h-48 mx-auto rounded-xl border border-gray-200 object-contain" alt="Ảnh bài KT">
                        </a>
                        <p class="text-center text-xs text-gray-400 mt-1">📸 Nhấn để xem to</p>
                    </div>

                    <!-- Danh sách học viên — click để nhập điểm -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 bg-indigo-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="text-sm font-black text-white">Chấm Điểm Học Viên</span>
                            <span class="text-xs text-indigo-300 ml-auto">Nhấn tên để nhập điểm</span>
                        </div>

                        <div v-if="localRecords.length === 0" class="py-10 text-center text-gray-400 text-sm">
                            Lớp chưa có học viên.
                        </div>

                        <div v-else class="divide-y divide-gray-100">
                            <div v-for="(rec, idx) in localRecords" :key="rec.member_id">
                                <!-- Row: click để expand -->
                                <div @click="canMarkAttendance && toggleExpand(rec.member_id)"
                                    class="flex items-center gap-3 px-4 py-3 transition-colors"
                                    :class="[
                                        expandedMember === rec.member_id ? 'bg-amber-50' : 'hover:bg-gray-50/60',
                                        canMarkAttendance ? 'cursor-pointer' : ''
                                    ]">
                                    <!-- Avatar / rank badge -->
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-black shrink-0"
                                        :class="rec.quiz_score !== null && rec.quiz_score !== undefined ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-500'">
                                        {{ rec.full_name?.charAt(0) }}
                                    </div>
                                    <!-- Name -->
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-sm text-gray-900">{{ rec.full_name }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            <span v-if="rec.quiz_score !== null && rec.quiz_score !== undefined" class="text-amber-700 font-bold">
                                                Điểm: <span class="text-base">{{ rec.quiz_score }}</span>/{{ session.total_questions || 100 }}
                                                <span class="text-gray-400 font-normal ml-1">({{ Math.round(rec.quiz_score / (session.total_questions || 100) * 100) }}%)</span>
                                            </span>
                                            <span v-else class="text-gray-300 italic">Chưa chấm</span>
                                        </div>
                                    </div>
                                    <!-- Status badge -->
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                                            :class="rec.quiz_score !== null && rec.quiz_score !== undefined ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                            {{ rec.quiz_score !== null && rec.quiz_score !== undefined ? '✔ Đã chấm' : '◦ Chưa chấm' }}
                                        </span>
                                        <svg v-if="canMarkAttendance" class="w-4 h-4 text-gray-400 transition-transform"
                                            :class="expandedMember === rec.member_id ? 'rotate-180' : ''"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>

                                <!-- Expanded: nhập điểm accordion -->
                                <div v-if="expandedMember === rec.member_id && canMarkAttendance"
                                    class="px-4 pb-4 bg-amber-50/60 border-t border-amber-100">
                                    <div class="flex items-center gap-3 pt-3">
                                        <div class="flex-1">
                                            <label class="block text-xs font-black text-gray-600 mb-1 uppercase tracking-wider">Điểm số (tối đa {{ session.total_questions || 100 }})</label>
                                            <div class="flex items-center gap-3">
                                                <input v-model.number="rec.quiz_score"
                                                    type="number" :min="0" :max="session.total_questions || 100"
                                                    class="w-24 text-center text-xl font-black rounded-xl border-amber-300 focus:border-amber-500 focus:ring-amber-500 py-2 shadow-sm"
                                                    placeholder="0"
                                                    @keyup.enter="saveAttendanceAndClose">
                                                <span class="text-sm text-gray-400">/ {{ session.total_questions || 100 }}</span>
                                                <span v-if="rec.quiz_score !== null && rec.quiz_score !== undefined"
                                                    class="text-lg font-black"
                                                    :class="rec.quiz_score / (session.total_questions || 100) >= 0.8 ? 'text-green-600' : rec.quiz_score / (session.total_questions || 100) >= 0.5 ? 'text-amber-600' : 'text-red-600'">
                                                    {{ Math.round(rec.quiz_score / (session.total_questions || 100) * 100) }}%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex gap-2 shrink-0">
                                            <button @click="rec.quiz_score = null; rec.attendance = 'absent'; toggleExpand(rec.member_id)"
                                                class="px-3 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition-colors">
                                                Xóa
                                            </button>
                                            <button @click="saveAttendanceAndClose"
                                                :disabled="attendanceLoading"
                                                class="px-4 py-2 bg-amber-600 text-white text-xs font-black rounded-xl hover:bg-amber-700 transition-colors disabled:opacity-50">
                                                {{ attendanceLoading ? '...' : 'Lưu' }}
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-2">ℹ nhập điểm và bấm Lưu — điểm số > 0 sẽ được ghi nhận là có mặt</p>
                                </div>
                            </div>
                        </div>

                        <!-- Save all button -->
                        <div v-if="canMarkAttendance && localRecords.length > 0" class="px-5 py-4 bg-gray-50 border-t border-gray-100">
                            <button @click="saveAttendance" :disabled="attendanceLoading"
                                class="w-full py-3 bg-gradient-to-r from-amber-600 to-amber-500 text-white font-black rounded-xl hover:from-amber-700 hover:to-amber-600 transition-all shadow-sm disabled:opacity-50 flex items-center justify-center gap-2">
                                <svg v-if="attendanceLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ attendanceLoading ? 'Đang lưu...' : '💾 Lưu Tất Cả Điểm' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ── TAB: XẼP HẠNG (bible_quiz) ───────────────────────── -->
                <div v-if="activeTab === 'ranking' && eduClass.class_type === 'bible_quiz'" class="space-y-5">

                    <!-- Stats summary -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-4 text-center">
                            <div class="text-2xl font-black text-amber-600">{{ avgScore }}</div>
                            <div class="text-[11px] text-gray-500 mt-1">Điểm TB bài này</div>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-4 text-center">
                            <div class="text-2xl font-black text-green-600">{{ scoredCount }}</div>
                            <div class="text-[11px] text-gray-500 mt-1">Đã nộp bài</div>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-4 text-center">
                            <div class="text-2xl font-black text-red-500">{{ localRecords.length - scoredCount }}</div>
                            <div class="text-[11px] text-gray-500 mt-1">Chưa nộp</div>
                        </div>
                    </div>

                    <!-- Bảng xếp hạng bài này -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 bg-gradient-to-r from-amber-600 to-amber-500 flex items-center justify-between">
                            <h3 class="text-sm font-black text-white">🏆 Xếp Hạng Bài Này</h3>
                            <span class="text-xs text-amber-100">{{ session.total_questions || 100 }} câu</span>
                        </div>
                        <div v-if="ranking.length === 0" class="py-10 text-center text-gray-400 text-sm">
                            Chưa có điểm nào được ghi nhận.
                        </div>
                        <div v-else class="divide-y divide-gray-100">
                            <div v-for="(r, i) in ranking" :key="r.member_id"
                                class="flex items-center gap-3 px-4 py-3"
                                :class="i === 0 ? 'bg-yellow-50' : i === 1 ? 'bg-gray-50/80' : i === 2 ? 'bg-orange-50/50' : ''">
                                <div class="w-9 text-center">
                                    <span v-if="i < 3" class="text-xl">{{ i === 0 ? '🥇' : i === 1 ? '🥈' : '🥉' }}</span>
                                    <span v-else class="text-sm font-black text-gray-400">{{ i + 1 }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-sm text-gray-900">{{ r.full_name }}</div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <div class="w-24 bg-gray-200 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full transition-all"
                                                :style="`width: ${r.percent}%`"
                                                :class="r.percent >= 80 ? 'bg-green-500' : r.percent >= 50 ? 'bg-amber-500' : 'bg-red-500'"></div>
                                        </div>
                                        <span class="text-xs font-bold" :class="r.percent >= 80 ? 'text-green-600' : r.percent >= 50 ? 'text-amber-600' : 'text-red-500'">{{ r.percent }}%</span>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span class="text-xl font-black"
                                        :class="r.percent >= 80 ? 'text-green-700' : r.percent >= 50 ? 'text-amber-700' : 'text-red-600'">
                                        {{ r.score }}
                                    </span>
                                    <span class="text-xs text-gray-400">/{{ session.total_questions || 100 }}</span>
                                </div>
                            </div>
                            <!-- Học viên chưa nộp -->
                            <div v-for="rec in notSubmitted" :key="'ns-' + rec.member_id"
                                class="flex items-center gap-3 px-4 py-3 opacity-50">
                                <div class="w-9 text-center text-sm text-gray-400">—</div>
                                <div class="flex-1">
                                    <div class="font-bold text-sm text-gray-500">{{ rec.full_name }}</div>
                                    <div class="text-xs text-gray-400">Chưa nộp bài</div>
                                </div>
                                <span class="text-sm font-black text-gray-300">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── TAB: TIỀN DÂNG ───────────────────────────────────── -->
                <div v-if="activeTab === 'finance'">
                    <div v-if="funds.length === 0" class="py-12 text-center text-gray-400 text-sm">Lớp chưa có quỹ nào.</div>
                    <div v-for="fund in funds" :key="fund.id" class="mb-5">
                        <!-- Fund header -->
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="font-black text-gray-900">{{ fund.name }}</h3>
                                <p class="text-xs text-gray-500">Tổng quỹ: <span class="font-bold text-green-600">{{ formatMoney(fund.balance) }}</span></p>
                            </div>
                            <button v-if="canRecordOffering" @click="openOfferingForm(fund)" class="flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 border border-green-200 rounded-xl text-sm font-bold hover:bg-green-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Ghi nhận
                            </button>
                        </div>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                            <div v-if="fund.transactions.length === 0" class="py-8 text-center text-gray-400 text-sm italic">Chưa có giao dịch trong buổi học này.</div>
                            <div v-else class="divide-y divide-gray-100">
                                <div v-for="tx in fund.transactions" :key="tx.id" class="px-4 py-3 flex items-center gap-3">
                                    <div :class="tx.type === 'income' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'" class="w-8 h-8 rounded-full flex items-center justify-center text-base shrink-0 font-black">
                                        {{ tx.type === 'income' ? '↑' : '↓' }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-bold text-sm text-gray-900">{{ formatMoney(tx.amount) }}</div>
                                        <div class="text-xs text-gray-400">{{ tx.category || 'Không phân loại' }}</div>
                                        <div v-if="tx.description" class="text-xs text-gray-400 italic">{{ tx.description }}</div>
                                    </div>
                                    <button v-if="canRecordOffering" @click="deleteOffering(tx.id)" class="text-red-400 hover:text-red-600 p-1 rounded-lg hover:bg-red-50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Session total -->
                        <div v-if="fund.transactions.length > 0" class="mt-2 flex justify-end gap-4 text-sm">
                            <span class="text-green-600 font-bold">Thu: {{ formatMoney(fund.transactions.filter(t => t.type === 'income').reduce((s, t) => s + t.amount, 0)) }}</span>
                            <span class="text-red-500 font-bold">Chi: {{ formatMoney(fund.transactions.filter(t => t.type === 'expense').reduce((s, t) => s + t.amount, 0)) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Session Info SlideOver -->
        <SlideOver v-model="isSessionInfoOpen" title="Thông Tin Bài Học" description="Nhập thông tin bài học cho buổi Chủ Nhật này">
            <div class="space-y-4">
                <!-- Giáo viên phụ trách buổi này -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">👨‍🏫 Giáo viên phụ trách buổi này</label>
                    <select v-model="sessionForm.teacher_id"
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option :value="null">— Không chỉ định —</option>
                        <option v-for="t in teachers" :key="t.id" :value="t.id">
                            {{ t.full_name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Bài số</label>
                    <input v-model.number="sessionForm.lesson_number" type="number" min="1" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="VD: 5">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Loạt bài</label>
                    <input v-model="sessionForm.lesson_series" type="text" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="VD: Thư Hê-bơ-rơ, Sáng Thế Ký...">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tên bài học</label>
                    <input v-model="sessionForm.topic" type="text" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Tiêu đề bài học...">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Câu gốc</label>
                    <input v-model="sessionForm.scripture" type="text" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="VD: Giăng 3:16">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Ghi chú</label>
                    <textarea v-model="sessionForm.notes" rows="3" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 w-full">
                    <button @click="isSessionInfoOpen = false" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="saveSessionInfo" :disabled="sessionForm.processing" class="px-6 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 disabled:opacity-50">
                        {{ sessionForm.processing ? 'Lưu...' : 'Lưu thông tin' }}
                    </button>
                </div>
            </template>
        </SlideOver>

        <!-- Offering SlideOver -->
        <SlideOver v-model="isOfferingOpen" title="Ghi Nhận Tiền Dâng">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Loại giao dịch</label>
                    <div class="flex gap-2">
                        <button @click="offeringForm.type = 'income'" :class="offeringForm.type === 'income' ? 'bg-green-500 text-white border-green-500' : 'bg-white text-gray-600 border-gray-200'" class="flex-1 py-2.5 border rounded-xl text-sm font-bold transition-colors">↑ Thu (Tiền dâng)</button>
                        <button @click="offeringForm.type = 'expense'" :class="offeringForm.type === 'expense' ? 'bg-red-500 text-white border-red-500' : 'bg-white text-gray-600 border-gray-200'" class="flex-1 py-2.5 border rounded-xl text-sm font-bold transition-colors">↓ Chi</button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Số Tiền (VNĐ) <span class="text-red-500">*</span></label>
                    <input v-model.number="offeringForm.amount" type="number" min="0" step="1000"
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-right font-bold text-lg" placeholder="0">
                    <p v-if="offeringForm.amount" class="text-sm text-right mt-1 font-bold text-green-600">{{ formatMoney(offeringForm.amount) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Phân loại</label>
                    <input v-model="offeringForm.category" type="text" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="VD: Tiền dâng tuần, Chi hoạt động...">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Ghi chú</label>
                    <textarea v-model="offeringForm.description" rows="2" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 w-full">
                    <button @click="isOfferingOpen = false" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="saveOffering" :disabled="offeringForm.processing" class="px-6 py-2 bg-green-600 text-white rounded-xl text-sm font-bold hover:bg-green-700 disabled:opacity-50">
                        {{ offeringForm.processing ? 'Lưu...' : 'Ghi nhận' }}
                    </button>
                </div>
            </template>
        </SlideOver>

        <!-- ══ QUIZ INFO SLIDEOVER (bible_quiz only) ══ -->
        <SlideOver :show="isQuizInfoOpen" @close="isQuizInfoOpen = false" title="Thông Tin Bài Kiểm Tra">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Sách Kinh Thánh</label>
                    <input v-model="quizBook" type="text" placeholder="VD: Giăng, Ma-thi-ơ, Rô-ma..."
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Số câu hỏi</label>
                    <input v-model.number="quizTotalQ" type="number" min="1" max="200" placeholder="VD: 30"
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                    <p class="text-xs text-gray-400 mt-1">Dùng để tính % đạt khi xếp hạng</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Người chấm bài</label>
                    <select v-model="quizGraderId"
                        class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                        <option :value="null">(Không có)</option>
                        <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.full_name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">📸 Ảnh bài kiểm tra</label>
                    <input type="file" accept="image/*" @change="onPhotoChange"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                    <p class="text-xs text-gray-400 mt-1">Tối đa 5MB · JPG, PNG</p>
                    <img v-if="photoPreview" :src="photoPreview" class="mt-2 max-h-40 rounded-xl border border-gray-200 object-contain" alt="Preview">
                    <img v-else-if="session.photo_path" :src="session.photo_path" class="mt-2 max-h-40 rounded-xl border border-gray-200 object-contain" alt="Ảnh hiện tại">
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 w-full">
                    <button @click="isQuizInfoOpen = false" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="saveQuizInfo" :disabled="quizSaving"
                        class="px-6 py-2 bg-amber-600 text-white rounded-xl text-sm font-bold hover:bg-amber-700 disabled:opacity-50 flex items-center gap-2">
                        <svg v-if="quizSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ quizSaving ? 'Đang lưu...' : 'Lưu thông tin' }}
                    </button>
                </div>
            </template>
        </SlideOver>

    </PortalLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link, usePage } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';

const page = usePage();

const props = defineProps({
    eduClass: Object,
    session: Object,
    students: Array,
    teachers: { type: Array, default: () => [] },
    funds: Array,
    navigation: { type: Object, default: () => ({}) },
    canMarkAttendance: Boolean,
    canRecordOffering: Boolean,
    portalType: String,
});

// ── State ────────────────────────────────────────────────────────
// bible_quiz mặc định tab 'scores', các loại khác 'attendance'
const activeTab = ref(props.eduClass?.class_type === 'bible_quiz' ? 'scores' : 'attendance');
const isSessionInfoOpen = ref(false);
const isOfferingOpen = ref(false);
const isQuizInfoOpen = ref(false);
const attendanceLoading = ref(false);
const expandedMember = ref(null);  // accordion for bible_quiz scoring

const toggleExpand = (memberId) => {
    expandedMember.value = expandedMember.value === memberId ? null : memberId;
};

// Sau khi lưu, tự đầu nhập có điểm → đánh dấu attendance='present'
const saveAttendanceAndClose = () => {
    // auto-mark as present if score > 0
    localRecords.value.forEach(r => {
        if (r.quiz_score !== null && r.quiz_score !== undefined && r.quiz_score >= 0) {
            r.attendance = 'present';
        }
    });
    attendanceLoading.value = true;
    router.post(route('education.attendance.save', [props.eduClass.id, props.session.id]), {
        mode: 'checkin',
        records: localRecords.value.map(r => ({
            member_id: r.member_id,
            attendance: r.attendance,
            memorized_verse: r.memorized_verse,
            quiz_score: r.quiz_score ?? null,
        }))
    }, {
        preserveScroll: true,
        onFinish: () => {
            attendanceLoading.value = false;
            expandedMember.value = null;
        },
    });
};

// Quiz info state
const quizBook     = ref(props.session?.book || '');
const quizTotalQ   = ref(props.session?.total_questions || null);
const quizGraderId = ref(props.session?.grader_id || null);
const quizSaving   = ref(false);
const photoFile    = ref(null);
const photoPreview = ref(null);

const onPhotoChange = (e) => {
    photoFile.value = e.target.files[0] || null;
    if (photoFile.value) {
        const reader = new FileReader();
        reader.onload = (ev) => photoPreview.value = ev.target.result;
        reader.readAsDataURL(photoFile.value);
    }
};

const saveQuizInfo = () => {
    quizSaving.value = true;
    const formData = new FormData();
    formData.append('_method', 'PUT');
    if (quizBook.value)     formData.append('book', quizBook.value);
    if (quizTotalQ.value)   formData.append('total_questions', quizTotalQ.value);
    if (quizGraderId.value) formData.append('grader_id', quizGraderId.value);
    if (photoFile.value)    formData.append('photo', photoFile.value);
    router.post(route('education.session.update', [props.eduClass.id, props.session.id]), formData, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => { isQuizInfoOpen.value = false; photoFile.value = null; photoPreview.value = null; },
        onFinish: () => quizSaving.value = false,
    });
};

// Local copy of records for live editing
const localRecords = ref(props.students.map(s => ({ ...s })));

// ── Computed ─────────────────────────────────────────────────────
const presentCount  = computed(() => localRecords.value.filter(r => r.attendance === 'present').length);
const absentCount   = computed(() => localRecords.value.filter(r => r.attendance === 'absent').length);
const memorizedCount = computed(() => localRecords.value.filter(r => r.memorized_verse).length);
const scoredCount   = computed(() => localRecords.value.filter(r => r.quiz_score !== null && r.quiz_score !== undefined).length);

// Ranking: sort by quiz_score desc (all who have a score)
const ranking = computed(() => {
    const maxScore = props.session?.total_questions || 100;
    return localRecords.value
        .filter(r => r.quiz_score !== null && r.quiz_score !== undefined)
        .sort((a, b) => (b.quiz_score ?? 0) - (a.quiz_score ?? 0))
        .map(r => ({
            member_id: r.member_id,
            full_name: r.full_name,
            score: r.quiz_score,
            percent: Math.round((r.quiz_score / maxScore) * 100),
        }));
});

// Học viên chưa nộp bài (không có điểm)
const notSubmitted = computed(() =>
    localRecords.value.filter(r => r.quiz_score === null || r.quiz_score === undefined)
);

const avgScore = computed(() => {
    if (ranking.value.length === 0) return '—';
    const total = ranking.value.reduce((s, r) => s + r.score, 0);
    return (total / ranking.value.length).toFixed(1);
});
const totalSessionIncome = computed(() =>
    props.funds.flatMap(f => f.transactions.filter(t => t.type === 'income')).reduce((s, t) => s + t.amount, 0)
);

// ── Session navigation ───────────────────────────────────────────
// ── Session navigation (Sunday-based) ────────────────────────────────
const goToSunday = (dateStr) => {
    router.get(route('education.session', props.eduClass.id), { date: dateStr }, { preserveScroll: false });
};

// ── Attendance mode ──────────────────────────────────────────────────
// Init mode from saved session data, fallback to 'checkin'
const attendanceMode = ref(props.session?.attendance_mode === 'quick' ? 'quick' : 'checkin');

// Quick mode state — pre-fill from saved data if available
const quickPresent = ref(props.session?.total_present ?? '');
const quickAbsent  = ref(props.session?.total_absent ?? '');

const saveQuick = () => {
    attendanceLoading.value = true;
    router.post(route('education.attendance.save', [props.eduClass.id, props.session.id]), {
        mode: 'quick',
        total_present: quickPresent.value || 0,
        total_absent:  quickAbsent.value || 0,
    }, {
        preserveScroll: true,
        onFinish: () => attendanceLoading.value = false,
    });
};

const markAll = (status) => localRecords.value.forEach(r => r.attendance = status);

const saveAttendance = () => {
    attendanceLoading.value = true;
    router.post(route('education.attendance.save', [props.eduClass.id, props.session.id]), {
        mode: 'checkin',
        records: localRecords.value.map(r => ({
            member_id: r.member_id,
            attendance: r.attendance,
            memorized_verse: r.memorized_verse,
            quiz_score: r.quiz_score ?? null,
        }))
    }, {
        preserveScroll: true,
        onFinish: () => attendanceLoading.value = false,
    });
};

// ── Session Info Form ────────────────────────────────────────────
const sessionForm = useForm({
    lesson_number:  props.session.lesson_number,
    lesson_series:  props.session.lesson_series || '',
    topic:          props.session.topic || '',
    scripture:      props.session.scripture || '',
    notes:          props.session.notes || '',
    teacher_id:     props.session.teacher_id ?? null,
});

const saveSessionInfo = () => {
    sessionForm.put(route('education.session.update', [props.eduClass.id, props.session.id]), {
        preserveScroll: true,
        onSuccess: () => isSessionInfoOpen.value = false,
    });
};

// ── Offerings ────────────────────────────────────────────────────
const offeringForm = useForm({
    edu_class_fund_id: null,
    type: 'income',
    amount: null,
    category: '',
    description: '',
});

const openOfferingForm = (fund) => {
    offeringForm.reset();
    offeringForm.edu_class_fund_id = fund.id;
    offeringForm.type = 'income';
    isOfferingOpen.value = true;
};

const saveOffering = () => {
    offeringForm.post(route('education.offering.store', [props.eduClass.id, props.session.id]), {
        preserveScroll: true,
        onSuccess: () => { isOfferingOpen.value = false; offeringForm.reset(); },
    });
};

const deleteOffering = (txId) => {
    if (!confirm('Xóa giao dịch này?')) return;
    router.delete(route('education.offering.destroy', [props.eduClass.id, props.session.id, txId]), {
        preserveScroll: true,
    });
};

// ── Helpers ──────────────────────────────────────────────────────
const formatShortDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr + 'T00:00:00');
    return new Intl.DateTimeFormat('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(d);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return new Intl.DateTimeFormat('vi-VN', { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric' }).format(new Date(dateStr + 'T00:00:00'));
};

const formatMoney = (val) => {
    if (!val) return '0 ₫';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
};
</script>