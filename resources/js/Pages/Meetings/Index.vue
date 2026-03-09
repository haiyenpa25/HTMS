<template>
  <component :is="currentLayout">
    <template #header>
      Tổ chức Buổi Nhóm
    </template>

    <div class="py-4 space-y-6">
      <!-- Toolbar -->
      <DataToolbar 
        v-model:search="filterForm.search"
        v-model:viewMode="viewMode"
        storageKey="meetings_view_mode"
        placeholder="Tìm theo chủ đề, người hướng dẫn..."
      >
        <template #filters>
           <button type="button" @click="showFilters = !showFilters" class="ml-2 flex flex-col md:flex-row md:items-center justify-center space-x-2 px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-100 transition-colors">
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
              <span>Lọc nâng cao</span>
              <span v-if="activeFilterCount > 0" class="ml-1 bg-blue-100 text-blue-700 py-0.5 px-2 rounded-full text-[10px]">{{ activeFilterCount }}</span>
           </button>
        </template>
        <template #actions>
          <a :href="exportUrl" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-sm font-bold hover:bg-emerald-100 transition-colors mr-2" title="Xuất danh sách buổi nhóm ra Excel">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Xuất Excel
          </a>
          <button @click="showMeetingImportModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-sm font-bold hover:bg-amber-100 transition-colors mr-2" title="Import cập nhật buổi nhóm từ Excel">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import Excel
          </button>
          <a :href="route('education.index')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-sm font-bold hover:bg-indigo-100 transition-colors mr-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Buổi CĐGD
          </a>
          <PrimaryButton @click="openCreateSlideOver">
            + Lên lịch Buổi nhóm
          </PrimaryButton>
        </template>
      </DataToolbar>

      <!-- Panel Bộ Lọc -->
      <div v-show="showFilters" class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm animate-in slide-in-from-top-4 duration-200">
         <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Tiêu chí Lọc</h3>
            <button @click="resetFilters" class="text-xs font-bold text-red-600 hover:text-red-800 hover:underline">Xóa tất cả lọc</button>
         </div>
         <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Phân loại -->
            <div class="space-y-1">
               <label class="text-xs font-bold text-gray-500">Loại buổi nhóm</label>
               <select v-model="filterForm.type" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50">
                  <option value="">Tất cả</option>
                  <option value="church">Hội Thánh</option>
                  <option value="department">Ban Ngành</option>
               </select>
            </div>
            
            <!-- Từ ngày -->
            <div class="space-y-1">
               <label class="text-xs font-bold text-gray-500">Từ ngày</label>
               <input type="date" v-model="filterForm.date_from" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50" />
            </div>

            <!-- Đến ngày -->
            <div class="space-y-1">
               <label class="text-xs font-bold text-gray-500">Đến ngày</label>
               <input type="date" v-model="filterForm.date_to" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50" />
            </div>
         </div>
      </div>

      <!-- Selection Action Bar -->
      <div
        v-if="selectedIds.length > 0"
        class="flex items-center justify-between bg-indigo-600 text-white rounded-2xl px-5 py-3 shadow-md"
      >
        <div class="flex items-center gap-3">
          <button @click="selectedIds = []" class="text-indigo-200 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
          <span class="text-sm font-bold">Đã chọn <span class="font-black bg-white/20 px-2 py-0.5 rounded-full">{{ selectedIds.length }}</span> buổi nhóm</span>
        </div>
        <a
          :href="exportUrl"
          class="inline-flex items-center gap-2 px-4 py-1.5 bg-white text-indigo-700 text-sm font-bold rounded-lg hover:bg-indigo-50 transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Xuất {{ selectedIds.length }} buổi nhóm
        </a>
      </div>

      <!-- Data List -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        
        <!-- Table View (Desktop & Grid mixed) -->
        <div v-show="viewMode === 'list'" class="overflow-x-auto min-h-[500px]">
          <table class="min-w-full divide-y divide-gray-200 hidden md:table">
            <thead class="bg-gray-50/50">
              <tr>
                <th scope="col" class="pl-6 pr-2 py-4 w-10">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    :indeterminate="isIndeterminate"
                    @change="toggleSelectAll"
                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                  />
                </th>
                <th scope="col" class="px-6 py-4 text-left text-[11px] font-black text-gray-500 uppercase tracking-widest">Ngày / Giờ</th>
                <th scope="col" class="px-6 py-4 text-left text-[11px] font-black text-gray-500 uppercase tracking-widest">Loại / Chủ đề</th>
                <th scope="col" class="px-6 py-4 text-left text-[11px] font-black text-gray-500 uppercase tracking-widest">Diễn giả</th>
                <th scope="col" class="px-6 py-4 text-left text-[11px] font-black text-gray-500 uppercase tracking-widest">Ban ngành</th>
                <th scope="col" class="px-6 py-4 text-right text-[11px] font-black text-gray-500 uppercase tracking-widest">Hành động</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr v-for="meeting in meetings" :key="meeting.id"
                class="hover:bg-blue-50/50 transition-colors group cursor-pointer"
                :class="{ 'bg-indigo-50/50': selectedIds.includes(meeting.id) }"
                @click="goToMeeting(meeting.id)"
              >
                <td class="pl-6 pr-2 py-5" @click.stop>
                  <input
                    type="checkbox"
                    :value="meeting.id"
                    v-model="selectedIds"
                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                  />
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900 border border-gray-200 px-3 py-1 rounded w-max bg-gray-50">{{ formatDate(meeting.date) }}</div>
                  <div class="text-xs text-gray-500 font-medium mt-1">{{ meeting.time }}</div>
                </td>
                <td class="px-6 py-5">
                  <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider" :class="meeting.type === 'church' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'">
                      {{ meeting.type === 'church' ? 'Hội Thánh' : 'Ban Ngành' }}
                    </span>
                    <span class="text-sm font-black text-gray-900 line-clamp-1 truncate block max-w-xs">{{ meeting.topic || '(Chưa có chủ đề)' }}</span>
                  </div>
                  <div class="text-xs text-gray-500 mt-1 line-clamp-1 max-w-sm truncate" v-if="meeting.scripture">
                    KT: {{ meeting.scripture }}
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span class="text-sm text-gray-700 font-medium">{{ meeting.preacher || '-' }}</span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span v-if="meeting.department" class="text-xs bg-gray-100 text-gray-700 font-bold px-2 py-1 rounded">{{ meeting.department.name }}</span>
                  <span v-else class="text-xs text-gray-400 font-medium">-</span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-right">
                   <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button @click.stop="openEditSlideOver(meeting)" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" title="Chỉnh sửa">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                      </button>
                   </div>
                </td>
              </tr>
              <tr v-if="meetings.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  <div class="flex flex-col items-center justify-center space-y-3">
                     <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                     <p class="text-sm font-medium">Không tìm thấy buổi nhóm nào.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Mobile Cards View for List Mode -->
          <div class="md:hidden divide-y divide-gray-100">
             <div v-for="meeting in meetings" :key="'mob-'+meeting.id" class="p-5 flex items-start space-x-4 relative group hover:bg-gray-50 transition-colors cursor-pointer" @click="goToMeeting(meeting.id)">
               <div class="flex-1 min-w-0 flex flex-col justify-between py-1">
                 <div>
                    <div class="flex items-center space-x-2 mb-1">
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider" :class="meeting.type === 'church' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'">
                        {{ meeting.type === 'church' ? 'HT' : 'BN' }}
                      </span>
                      <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ formatDate(meeting.date) }} • {{ meeting.time }}</p>
                    </div>
                    <h4 class="text-base font-black text-gray-900 truncate leading-tight mt-1">{{ meeting.topic || '(Chưa có chủ đề)' }}</h4>
                    <p class="text-xs text-gray-500 line-clamp-1 mt-1 font-medium" v-if="meeting.scripture">KT: {{ meeting.scripture }}</p>
                 </div>
                 <div class="mt-3 flex items-center gap-2 flex-wrap">
                    <span v-if="meeting.preacher" class="inline-flex items-center text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded-md">
                      Mục sư: {{ meeting.preacher }}
                    </span>
                 </div>
               </div>
               
               <!-- Quick Actions menu trigger (mobile) -->
               <button @click.stop="openEditSlideOver(meeting)" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-colors relative z-10">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
               </button>
             </div>
             <div v-if="meetings.length === 0" class="p-10 text-center">
                <p class="text-sm font-medium text-gray-500">Chưa có dữ liệu.</p>
             </div>
          </div>
        </div>

        <!-- Grid View -->
        <div v-show="viewMode === 'grid'" class="p-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="meeting in meetings" :key="'grid-'+meeting.id" class="bg-white border border-gray-200 rounded-2xl p-5 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative group cursor-pointer" @click="goToMeeting(meeting.id)">
               <div class="flex justify-between items-start mb-4">
                  <div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider mb-2" :class="meeting.type === 'church' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'">
                      {{ meeting.type === 'church' ? 'Hội Thánh' : 'Ban Ngành' }}
                    </span>
                    <h3 class="text-lg font-black text-gray-900 leading-tight line-clamp-2" :title="meeting.topic">{{ meeting.topic || '(Chưa có chủ đề)' }}</h3>
                  </div>
                  <button @click.stop="openEditSlideOver(meeting)" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg opacity-0 group-hover:opacity-100 transition-all z-10">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                  </button>
               </div>
               
               <div class="space-y-2 mt-4 text-sm font-medium">
                  <div class="flex items-center text-gray-600">
                     <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                     {{ formatDate(meeting.date) }} • {{ meeting.time }}
                  </div>
                  <div class="flex items-center text-gray-600 line-clamp-1" v-if="meeting.scripture">
                     <svg class="w-4 h-4 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                     {{ meeting.scripture }}
                  </div>
                  <div class="flex items-center text-gray-600" v-if="meeting.department">
                     <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                     {{ meeting.department.name }}
                  </div>
               </div>
            </div>
          </div>
          <div v-if="meetings.length === 0" class="text-center py-12">
             <p class="text-gray-500 font-medium">Không tìm thấy dữ liệu góc nhìn Lưới.</p>
          </div>
        </div>

      </div>
    </div>

    <!-- Import Modal -->
    <div v-if="showMeetingImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6 space-y-5">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-black text-gray-900">Import cập nhật Buổi nhóm</h3>
            <p class="text-xs text-gray-500 mt-0.5">Cập nhật hàng loạt từ file Excel</p>
          </div>
          <button @click="showMeetingImportModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Info Box -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800 space-y-1">
          <p class="font-bold">Hướng dẫn:</p>
          <ol class="list-decimal ml-4 space-y-1 text-xs">
            <li>Xuất danh sách buổi nhóm ra Excel (nút <strong>Xuất Excel</strong>)</li>
            <li>Sửa các cột: <strong>Chủ đề, Câu gốc, Phân đoạn, Diễn giả</strong>...</li>
            <li>Giữ nguyên cột <strong>ID Buổi Nhóm</strong> (cột A), không xóa</li>
            <li>Để trống ô = giữ nguyên giá trị cũ | Gõ <code class="bg-amber-100 px-1 rounded">-</code> = xóa nội dung</li>
            <li>Import lại file ở đây</li>
          </ol>
        </div>

        <!-- File Input -->
        <div>
          <label class="block text-sm font-bold text-gray-700 mb-1.5">Chọn file Excel (.xlsx)</label>
          <input
            type="file"
            accept=".xlsx,.xls"
            @change="e => meetingImportFile = e.target.files[0]"
            class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100"
          />
        </div>

        <!-- Import Errors from last import -->
        <div v-if="$page.props.flash?.import_errors?.length" class="bg-red-50 border border-red-200 rounded-xl p-4">
          <p class="text-sm font-bold text-red-700 mb-2">Các dòng bỏ qua:</p>
          <ul class="text-xs text-red-600 space-y-0.5 list-disc ml-4">
            <li v-for="(e, i) in $page.props.flash.import_errors" :key="i">{{ e }}</li>
          </ul>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-1">
          <button type="button" @click="showMeetingImportModal = false" class="px-4 py-2 text-sm font-bold text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-xl transition-colors">Hủy</button>
          <button
            @click="submitMeetingsImport"
            :disabled="!meetingImportFile || meetingImportLoading"
            class="px-5 py-2 bg-amber-600 text-white text-sm font-bold rounded-xl hover:bg-amber-700 transition-colors disabled:opacity-50"
          >
            {{ meetingImportLoading ? 'Đang import...' : '🚀 Import và cập nhật' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Khung tạo/sửa trượt từ phải (SlideOver) -->
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
import { router, Link } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import DataToolbar from '@/Components/DataToolbar.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SlideOver from '@/Components/SlideOver.vue';
import MeetingForm from './Partials/MeetingForm.vue';

// Desktop/Mobile layout switching
const isMobile = ref(window.innerWidth < 768);
const currentLayout = computed(() => isMobile.value ? markRaw(MobileLayout) : markRaw(AuthenticatedLayout));

window.addEventListener('resize', debounce(() => {
  isMobile.value = window.innerWidth < 768;
}, 250));

const props = defineProps({
  meetings: Array,
  filters: Object,
});

const viewMode = ref('list');
const showFilters = ref(false);

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
  return count;
});

// Checkbox selection
const selectedIds = ref([]);

const isAllSelected = computed(() =>
  props.meetings.length > 0 && selectedIds.value.length === props.meetings.length
);
const isIndeterminate = computed(() =>
  selectedIds.value.length > 0 && selectedIds.value.length < props.meetings.length
);
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedIds.value = [];
  } else {
    selectedIds.value = props.meetings.map(m => m.id);
  }
};

// Reset selection when meetings list changes
watch(() => props.meetings, () => { selectedIds.value = []; });

// Export URL reacts to selection + filters
const exportUrl = computed(() => {
  const params = new URLSearchParams();
  if (selectedIds.value.length > 0) {
    // Export only selected
    params.set('ids', selectedIds.value.join(','));
  } else {
    // Export all with current filters
    if (filterForm.value.type) params.set('type', filterForm.value.type);
    if (filterForm.value.date_from) params.set('date_from', filterForm.value.date_from);
    if (filterForm.value.date_to) params.set('date_to', filterForm.value.date_to);
  }
  const qs = params.toString();
  return route('meetings.export') + (qs ? '?' + qs : '');
});

const resetFilters = () => {
  filterForm.value.type = '';
  filterForm.value.date_from = '';
  filterForm.value.date_to = '';
};

watch(filterForm, debounce((newVal) => {
  router.get(route('meetings.index'), newVal, { preserveState: true, replace: true });
}, 300), { deep: true });

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

// SlideOver State
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
  setTimeout(() => {
    selectedMeeting.value = null;
  }, 300);
};

const handleSuccess = () => {
  router.reload({ only: ['meetings'] });
};

const goToMeeting = (id) => {
  router.get(route('meetings.show', id));
};

// ─── Meeting Import ───────────────────────────────────────────────────────────
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
