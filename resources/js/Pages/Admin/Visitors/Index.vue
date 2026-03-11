<template>
  <component :is="currentLayout">
    <template #header>CRM - Quản lý Chăm sóc Thân Hữu</template>

    <div class="py-4 space-y-6 w-full">
      
      <!-- Phễu Chăm sóc (Funnel) -->
      <div class="bg-indigo-900 rounded-2xl shadow-lg border border-indigo-800 p-6 overflow-hidden relative">
         <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
         <h3 class="text-white font-black tracking-widest uppercase text-xs mb-6 opacity-80 flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg> Phễu Chuyển Đổi Sinh Linh Rước (Funnel)</h3>
         
         <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8 relative z-10">
            <!-- Giai đoạn 1 -->
            <div class="flex flex-col">
               <div class="text-indigo-200 text-xs font-bold uppercase tracking-wider mb-2 flex items-center justify-between">
                  <span>1. Mới đến</span>
                  <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
               </div>
               <div class="bg-white/10 border border-white/20 rounded-xl p-4 backdrop-blur-sm">
                  <div class="text-4xl font-black text-white">{{ funnel.new }}</div>
                  <div class="text-sm text-indigo-200 mt-1 font-medium">Chưa liên hệ</div>
               </div>
            </div>
            
            <!-- Giai đoạn 2 -->
            <div class="flex flex-col">
               <div class="text-blue-200 text-xs font-bold uppercase tracking-wider mb-2 flex items-center justify-between">
                  <span>2. Đang Chăm Sóc</span>
                  <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
               </div>
               <div class="bg-blue-500/20 border border-blue-400/30 rounded-xl p-4 backdrop-blur-sm">
                  <div class="text-4xl font-black text-white">{{ funnel.contacted }}</div>
                  <div class="text-sm text-blue-200 mt-1 font-medium">Đã gọi điện/Thăm nhà</div>
               </div>
            </div>

            <!-- Giai đoạn 3 -->
            <div class="flex flex-col">
               <div class="text-amber-200 text-xs font-bold uppercase tracking-wider mb-2 flex items-center justify-between">
                  <span>3. Học Giáo Lý</span>
                  <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
               </div>
               <div class="bg-amber-500/20 border border-amber-400/30 rounded-xl p-4 backdrop-blur-sm">
                  <div class="text-4xl font-black text-white">{{ funnel.studying }}</div>
                  <div class="text-sm text-amber-200 mt-1 font-medium">Chuẩn bị Báp-tem</div>
               </div>
            </div>

            <!-- Giai đoạn 4 -->
            <div class="flex flex-col">
               <div class="text-emerald-200 text-xs font-bold uppercase tracking-wider mb-2 flex items-center justify-between">
                  <span>4. Trở Thành Tín Hữu</span>
                  <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
               </div>
               <div class="bg-emerald-500/20 border border-emerald-400/30 rounded-xl p-4 backdrop-blur-sm relative overflow-hidden">
                  <div class="absolute right-0 bottom-0 opacity-10 translate-x-4 translate-y-4 text-white"><svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg></div>
                  <div class="text-4xl font-black text-white">{{ funnel.baptized }}</div>
                  <div class="text-sm text-emerald-200 mt-1 font-medium">Đã Báp-tem</div>
               </div>
            </div>
         </div>
      </div>

      <!-- Controls & Table -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex flex-col md:flex-row gap-4 items-center justify-between bg-gray-50/50">
          <div class="flex flex-1 w-full gap-2 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input v-model="search" type="text" placeholder="Tìm tên, SĐT, Email..." class="w-full md:w-80 pl-9 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" />
            
            <select v-model="filterStatus" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm hidden md:block">
               <option value="all">Tất cả Trạng thái</option>
               <option v-for="st in statusList" :key="st.value" :value="st.value">{{ st.label }}</option>
            </select>
          </div>
          <PrimaryButton @click="openModal(null)" class="shrink-0 bg-indigo-600 hover:bg-indigo-700">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Thêm Thân Hữu Mới
          </PrimaryButton>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-[11px] text-gray-500 uppercase font-black tracking-wider">
              <tr>
                <th class="px-4 py-3 text-left w-10">Mới</th>
                <th class="px-4 py-3 text-left">Hồ sơ Thân hữu</th>
                <th class="px-4 py-3 text-left">Thông tin liên lạc</th>
                <th class="px-4 py-3 text-left">Giai đoạn & Người chăm sóc</th>
                <th class="px-4 py-3 text-left">Lần tương tác cuối</th>
                <th class="px-4 py-3 text-right">Tác vụ</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr v-for="item in visitors.data" :key="item.id" class="hover:bg-indigo-50/30 transition-colors group">
                <td class="px-4 py-4 whitespace-nowrap">
                   <span v-if="isNew(item.created_at)" class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                   </span>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex-shrink-0 flex items-center justify-center text-indigo-700 font-black text-lg">
                       {{ item.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="ml-3">
                      <div class="font-bold text-sm text-gray-900">{{ item.name }}</div>
                      <div class="text-[11px] text-gray-500 mt-0.5">Mời bởi: <span class="font-medium text-gray-700">{{ item.invited_by || 'Tự đến' }}</span></div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-4">
                   <div class="text-sm font-medium text-gray-800 flex items-center"><svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg> {{ item.phone || 'Chưa cung cấp' }}</div>
                   <div v-if="item.email" class="text-[11px] text-gray-500 mt-1 flex items-center"><svg class="w-3h h-3.5 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> {{ item.email }}</div>
                   <div class="text-[11px] text-gray-500 mt-1 truncate max-w-[200px]" :title="item.address"><span v-if="item.address" class="font-medium text-gray-700">{{ item.address }}</span></div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <div class="mb-2">
                     <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full border" :class="getStatusColor(item.status)">
                       {{ getStatusLabel(item.status) }}
                     </span>
                  </div>
                  <div class="text-xs flex items-center" :class="item.assigned_to ? 'text-indigo-700 font-bold' : 'text-amber-600 font-medium'">
                     <svg class="w-3.5 h-3.5 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                     {{ item.assigned_to ? item.assigned_to.name : 'Chưa phân người theo dõi' }}
                  </div>
                </td>
                <td class="px-4 py-4 text-sm text-gray-500">
                   <div v-if="item.followups?.length > 0">
                      <div class="font-bold text-gray-900 border-b border-gray-100 pb-1 mb-1">{{ new Date(item.followups[0].contact_date).toLocaleDateString('vi-VN') }} <span class="text-[10px] font-normal px-1 bg-gray-100 rounded text-gray-600 uppercase">{{ getFollowupTypeLabel(item.followups[0].type) }}</span></div>
                      <div class="text-[11px] leading-tight line-clamp-2 italic text-gray-600">"{{ item.followups[0].notes }}"</div>
                   </div>
                   <div v-else class="text-xs italic text-gray-400">Chưa có lịch sử chăm sóc</div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <!-- Nút Ghi nhận Chăm sóc (Follow-up) -->
                    <button @click="openFollowupModal(item)" class="text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center shadow-sm">
                       <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Ghi nhận
                    </button>

                    <button @click="openModal(item)" class="text-gray-400 hover:text-blue-600 p-1.5 bg-gray-50 rounded-md">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    <!-- <button @click="deleteItem(item.id)" class="text-gray-400 hover:text-red-600 p-1">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button> -->
                  </div>
                </td>
              </tr>
              <tr v-if="visitors.data.length === 0">
                <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">
                  <div class="flex flex-col items-center justify-center">
                     <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                     Không có dữ liệu thân hữu phù hợp bộ lọc.
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-end">
           <Pagination :links="visitors.links" />
        </div>
      </div>
    </div>

    <!-- S L I D E  O V E R : Thêm / Sửa Thân Hữu -->
    <SlideOver v-model="isModalOpen" :title="isEditing ? 'Chỉnh Sửa Hồ Sơ Thân Hữu' : 'Thêm Thân Hữu Mới'" size="md">
      <p class="text-sm text-gray-500 mb-6">Điền thông tin cơ bản về khách mời lần đầu tham dự để đưa vào phễu chăm sóc.</p>
      <form id="visitorForm" @submit.prevent="submitForm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <InputLabel value="Họ và Tên *" />
              <TextInput v-model="form.name" type="text" class="mt-1 block w-full text-base font-bold" required placeholder="VD: Nguyễn Văn A" />
            </div>
            
            <div>
              <InputLabel value="Số điện thoại" />
              <TextInput v-model="form.phone" type="text" class="mt-1 block w-full text-sm font-mono" placeholder="09xxxx..." />
            </div>

            <div>
              <InputLabel value="Email" />
              <TextInput v-model="form.email" type="email" class="mt-1 block w-full text-sm" placeholder="email@example.com" />
            </div>

            <div class="md:col-span-2">
              <InputLabel value="Địa chỉ nơi ở" />
              <TextInput v-model="form.address" type="text" class="mt-1 block w-full text-sm" placeholder="Số nhà, đường, phường, quận" />
            </div>

            <div>
              <InputLabel value="Mời bởi (Do ai dắt tới?)" />
              <TextInput v-model="form.invited_by" type="text" class="mt-1 block w-full text-sm" placeholder="Tên Tín hữu mời" />
            </div>
            
            <div>
              <InputLabel value="Ngày tới Hội thánh lần đầu" />
              <TextInput v-model="form.first_visit_date" type="date" class="mt-1 block w-full text-sm" />
            </div>

            <div class="md:col-span-2 border-t border-gray-100 pt-3 mt-1 relative"><span class="absolute -top-3 left-3 bg-white px-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Phễu & Chăm Sóc</span></div>

            <div>
              <InputLabel value="Giai đoạn trong Phễu *" />
              <select v-model="form.status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                 <option v-for="st in statusList" :key="st.value" :value="st.value">{{ st.label }}</option>
              </select>
            </div>

            <div>
              <InputLabel value="Phân công Người chăm sóc" />
              <select v-model="form.assigned_to" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                 <option :value="null">-- Chưa phân công --</option>
                 <option v-for="u in assignableUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
              </select>
            </div>
            
            <div class="md:col-span-2">
              <InputLabel value="Nhu cầu Cầu nguyện / Lời làm chứng" />
              <textarea v-model="form.prayer_requests" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm bg-indigo-50/30" placeholder="Họ cầu nguyện cho gia đình, công việc, hay sức khoẻ?"></textarea>
            </div>
          </div>
        </form>
      <template #footer>
        <div class="flex justify-end gap-3 w-full">
          <SecondaryButton type="button" @click="closeModal">Hủy</SecondaryButton>
          <PrimaryButton form="visitorForm" class="bg-indigo-600 hover:bg-indigo-700" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
             Lưu Hồ Sơ
          </PrimaryButton>
        </div>
      </template>
    </SlideOver>

    <!-- S L I D E  O V E R : TẠO FOLLOW UPs -->
    <SlideOver v-model="isFollowupModalOpen" title="Ghi nhận Chăm sóc Thân hữu" size="lg">
        <div class="flex items-center gap-3 mb-6">
           <p class="text-sm font-bold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full"><svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> {{ activeVisitor?.name }}</p>
           <span class="px-3 py-1 text-[10px] font-bold uppercase rounded-full border bg-white" :class="getStatusColor(activeVisitor?.status)">Hệ thống: {{ getStatusLabel(activeVisitor?.status) }}</span>
        </div>

          <!-- Lịch sử Followup gần đây -->
          <div class="mb-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
             <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b border-gray-200 pb-2 mb-3">Nhật ký Tiếp xúc / Chăm sóc</h3>
             <div v-if="followupsLoading" class="text-center py-4 text-sm text-gray-500 animate-pulse">Đang tải lịch sử...</div>
             <div v-else-if="followupHistory.length === 0" class="text-sm text-gray-500 italic text-center py-2">Chưa ai từng liên hệ thân hữu này. Hãy là người đầu tiên!</div>
             <div v-else class="space-y-3 max-h-[300px] overflow-y-auto pr-2">
                <div v-for="f in followupHistory" :key="f.id" class="bg-white p-3 rounded-lg border border-gray-100 shadow-sm relative">
                   <div class="absolute left-0 top-0 w-1 h-full rounded-l-lg" :class="getOutcomeColor(f.outcome)"></div>
                   <div class="flex justify-between items-start mb-1 pl-2">
                      <div class="font-bold text-gray-800 text-sm flex items-center">
                         {{ getFollowupTypeLabel(f.type) }}
                         <span class="text-xs font-normal text-gray-400 ml-2">{{ new Date(f.contact_date).toLocaleDateString('vi-VN') }}</span>
                      </div>
                      <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded" :class="getOutcomeColor(f.outcome)">
                         {{ getOutcomeLabel(f.outcome) }}
                      </span>
                   </div>
                   <div class="pl-2 text-[13px] text-gray-600 italic">"{{ f.notes }}"</div>
                   <div class="pl-2 mt-2 pt-2 border-t border-gray-50 text-[10px] text-gray-400 font-bold uppercase tracking-wider">Thực hiện bởi: {{ f.user?.name }}</div>
                </div>
             </div>
          </div>

          <!-- FORM TẠO MỚI FOLLOW-UP -->
          <div class="bg-white border text-left p-0 rounded-xl mt-4">
              <h3 class="text-sm font-black text-gray-800 mb-4 px-1 flex items-center">📝 Tạo Phiếu Chăm Sóc Mới</h3>
              <form id="followupForm" @submit.prevent="submitFollowup" class="space-y-4">
                 <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="md:col-span-1">
                       <InputLabel value="Ngày tương tác *" />
                       <TextInput v-model="followupForm.contact_date" type="date" class="mt-1 block w-full text-sm font-mono" required />
                    </div>
                    <div class="md:col-span-1">
                       <InputLabel value="Hình thức *" />
                       <select v-model="followupForm.type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                          <option value="call">📞 Gọi điện thoại</option>
                          <option value="message">💬 Nhắn tin (Zalo/FB)</option>
                          <option value="visit">🏠 Tới chẩn nhà thăm</option>
                          <option value="meeting">⛪ Gặp gỡ tại Hội Thánh</option>
                       </select>
                    </div>
                    <div class="md:col-span-2">
                       <InputLabel value="Kết quả đánh giá *" />
                       <select v-model="followupForm.outcome" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm font-bold" required>
                          <option value="positive" class="text-emerald-700">Tích cực (Khao khát, hẹn gặp lại)</option>
                          <option value="neutral">Bình thường (Đã chuyển thông điệp)</option>
                          <option value="no_answer" class="text-amber-600">Không liên lạc được (Gọi k nghe)</option>
                          <option value="negative" class="text-red-600">Tiêu cực (Từ chối, không quan tâm)</option>
                       </select>
                    </div>
                 </div>

                 <div>
                    <InputLabel value="Ghi chú chi tiết kết quả buổi nói chuyện *" />
                    <textarea v-model="followupForm.notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required placeholder="Thân hữu nói gì? Trở ngại của họ là gì? Cần giúp đỡ thêm điều gì?"></textarea>
                 </div>

                 <!-- TỰ ĐỘNG CHUYỂN PHẾU -->
                 <div class="bg-amber-50 p-3 rounded-lg border border-amber-200 mt-4">
                     <span class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-2 block">Dịch chuyển Phễu Thân Hữu?</span>
                     <label class="flex items-center text-sm font-medium text-gray-700">
                        <select v-model="followupForm.update_status" class="block w-full border-amber-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm bg-white">
                           <option value="">Giữ nguyên Trạng thái hiện tại ({{ getStatusLabel(activeVisitor?.status) }})</option>
                           <option v-for="st in statusList.filter(s => s.value !== activeVisitor?.status)" :key="st.value" :value="st.value">
                              Đổi sang giai đoạn: {{ st.label }}
                           </option>
                        </select>
                     </label>
                 </div>

              </form>
          </div>
      <template #footer>
        <div class="flex justify-end w-full gap-3">
            <SecondaryButton type="button" @click="closeFollowupModal">Huỷ, đóng lại</SecondaryButton>
            <PrimaryButton form="followupForm" :disabled="followupForm.processing" class="bg-indigo-600 hover:bg-indigo-700 focus:bg-indigo-700">
               Lưu Ghi Nhận
            </PrimaryButton>
        </div>
      </template>
    </SlideOver>
  </component>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import debounce from 'lodash/debounce';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const currentLayout = computed(() => {
    if (typeof window === 'undefined') return AuthenticatedLayout;
    return window.innerWidth < 768 ? MobileLayout : AuthenticatedLayout;
});
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SlideOver from '@/Components/SlideOver.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps(['visitors', 'filters', 'funnel', 'assignableUsers']);

const search = ref(props.filters.search || '');
const filterStatus = ref(props.filters.status || 'all');

watch([search, filterStatus], debounce(([val1, val2]) => {
    router.get(route('admin.visitors.index'), { search: val1, status: val2 }, { preserveState: true, replace: true });
}, 300));

const isNew = (createdAt) => {
    const diffTime = Math.abs(new Date() - new Date(createdAt));
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
    return diffDays <= 7;
};

// --- Helpers Giao diện ---
const statusList = [
    { value: 'new', label: '1. Thân hữu Mới (Chưa LH)' },
    { value: 'contacted', label: '2. Đang Chăm Sóc' },
    { value: 'studying', label: '3. Đang học Giáo Lý' },
    { value: 'baptized', label: '4. Đã Báp-tem (Đủ ĐK)' },
    { value: 'lost', label: 'Mất liên lạc / Tạm nghỉ' }
];

const getStatusLabel = v => statusList.find(c => c.value === v)?.label.replace(/^\d+\.\s*/, '') || v;
const getStatusColor = v => {
    const map = {
        new: 'border-gray-200 text-gray-500 bg-white',
        contacted: 'border-blue-300 text-blue-700 bg-blue-50',
        studying: 'border-amber-300 text-amber-700 bg-amber-50',
        baptized: 'border-emerald-500 text-white bg-emerald-500',
        lost: 'border-gray-300 text-gray-500 bg-gray-100 line-through'
    };
    return map[v] || 'bg-gray-100';
};

const getFollowupTypeLabel = v => {
    const map = { call: 'Gọi điện thoại', visit: 'Thăm viếng nhà', message: 'Nhắn tin', meeting: 'Gặp gỡ TT' };
    return map[v] || v;
};

const getOutcomeLabel = v => {
    const map = { positive: 'Tích cực', neutral: 'Bình thường', negative: 'Từ chối', no_answer: 'Không LL được' };
    return map[v] || v;
}
const getOutcomeColor = v => {
    const map = { positive: 'bg-emerald-500 text-white', neutral: 'bg-gray-200 text-gray-700', negative: 'bg-red-500 text-white', no_answer: 'bg-amber-400 text-white' };
    return map[v] || 'bg-gray-200';
}

// --- CRUD MODAL ---
const isModalOpen = ref(false);
const isEditing = ref(false);
const activeId = ref(null);

const form = useForm({
    name: '', phone: '', email: '', birth_date: '', address: '',
    first_visit_date: new Date().toISOString().slice(0, 10),
    invited_by: '', prayer_requests: '', status: 'new', assigned_to: null
});

const openModal = (item) => {
    form.clearErrors();
    if(item) {
        isEditing.value = true;
        activeId.value = item.id;
        form.name = item.name; form.phone = item.phone; form.email = item.email;
        form.birth_date = item.birth_date; form.address = item.address; form.first_visit_date = item.first_visit_date;
        form.invited_by = item.invited_by; form.prayer_requests = item.prayer_requests; form.status = item.status; form.assigned_to = item.assigned_to?.id || null;
    } else {
        isEditing.value = false;
        activeId.value = null;
        form.reset();
        form.status = 'new';
    }
    isModalOpen.value = true;
};
const closeModal = () => isModalOpen.value = false;

const submitForm = () => {
    if(isEditing.value) {
        form.put(route('admin.visitors.update', activeId.value), { onSuccess: () => closeModal() });
    } else {
        form.post(route('admin.visitors.store'), { onSuccess: () => {
            closeModal();
            router.reload({ only: ['funnel'] }); // Update number
        }});
    }
};

// --- CHĂM SÓC (FOLLOW UP) MODAL ---
const isFollowupModalOpen = ref(false);
const activeVisitor = ref(null);
const followupHistory = ref([]);
const followupsLoading = ref(false);

const followupForm = useForm({
    type: 'call',
    contact_date: new Date().toISOString().slice(0, 10),
    notes: '',
    outcome: 'neutral',
    update_status: ''
});

const openFollowupModal = async (visitor) => {
    activeVisitor.value = visitor;
    isFollowupModalOpen.value = true;
    followupForm.reset();
    followupForm.update_status = ''; // Dọn biến chuyển trạng thái cũ đi
    await fetchFollowups();
};

const closeFollowupModal = () => isFollowupModalOpen.value = false;

const fetchFollowups = async () => {
    followupsLoading.value = true;
    try {
        const { data } = await axios.get(route('admin.visitors.followups.index', activeVisitor.value.id));
        followupHistory.value = data;
    } catch(e) { console.error('Fetch failed', e); }
    finally { followupsLoading.value = false; }
};

const submitFollowup = () => {
    followupForm.post(route('admin.visitors.followups.store', activeVisitor.value.id), {
        onSuccess: () => {
            followupForm.reset();
            fetchFollowups(); // Refresh API
            // Refresh Dashboard table AND funnel under the hood
            router.reload({ only: ['visitors', 'funnel'] });
            
            // Nếu có chuyển đổi trạng thái cục bộ trên UI (Hack cho mượt)
            if(followupForm.update_status) {
                activeVisitor.value.status = followupForm.update_status;
            }
        }
    });
};
</script>