<template>
  <AuthenticatedLayout>
    <template #header>Quản lý Cơ sở Vật chất</template>

    <div class="py-4 space-y-6 w-full">
      
      <!-- Thống kê Header -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
           <div>
              <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">Tổng Số Thiết Bị</p>
              <h3 class="text-2xl font-black text-gray-800">{{ stats.total }}</h3>
           </div>
           <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
           <div>
              <p class="text-xs text-green-600 font-bold uppercase tracking-wider mb-1">Đang Sử Dụng Tốt</p>
              <h3 class="text-2xl font-black text-gray-800">{{ stats.in_use }}</h3>
           </div>
           <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
           <div>
              <p class="text-xs text-amber-500 font-bold uppercase tracking-wider mb-1">Đang Bảo Trì/Hỏng</p>
              <h3 class="text-2xl font-black text-gray-800">{{ stats.maintenance }}</h3>
           </div>
           <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
           <div>
              <p class="text-xs text-red-500 font-bold uppercase tracking-wider mb-1">Thất Lạc</p>
              <h3 class="text-2xl font-black text-gray-800">{{ stats.lost }}</h3>
           </div>
           <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
        </div>
      </div>

      <!-- Controls & Table -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
        
        <div class="p-4 border-b border-gray-100 flex flex-col md:flex-row gap-4 items-center justify-between bg-gray-50/50">
          <div class="flex flex-1 w-full gap-2 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input v-model="search" type="text" placeholder="Tìm tên, mã code, brand..." class="w-full md:w-80 pl-9 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" />
            
            <select v-model="filterCategory" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm hidden md:block">
               <option value="all">Tất cả Phân loại</option>
               <option v-for="cat in categoriesList" :key="cat.value" :value="cat.value">{{ cat.label }}</option>
            </select>
            
            <select v-model="filterStatus" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm hidden md:block">
               <option value="all">Tất cả Tình trạng</option>
               <option v-for="st in statusList" :key="st.value" :value="st.value">{{ st.label }}</option>
            </select>
          </div>
          <PrimaryButton @click="openModal(null)" class="shrink-0">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Thêm Tài Sản Mới
          </PrimaryButton>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase font-black tracking-wider">
              <tr>
                <th class="px-4 py-3 text-left">Mã TS</th>
                <th class="px-4 py-3 text-left">Tên Tài Sản</th>
                <th class="px-4 py-3 text-left">Phân loại / Brand</th>
                <th class="px-4 py-3 text-left">Trạng thái</th>
                <th class="px-4 py-3 text-left">Giữ bởi</th>
                <th class="px-4 py-3 text-right">Thao tác</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in assets.data" :key="item.id" class="hover:bg-gray-50/50 transition-colors group">
                <td class="px-4 py-4 whitespace-nowrap text-sm font-mono font-bold text-gray-900 border-l-2 border-transparent">
                  {{ item.code }}
                </td>
                <td class="px-4 py-4">
                  <div class="font-bold text-sm text-gray-900">{{ item.name }}</div>
                  <div class="text-[11px] text-gray-500 line-clamp-1 max-w-[200px]" :title="item.notes">{{ item.notes || 'Không có ghi chú' }}</div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                   <div class="text-xs font-medium text-gray-700">{{ getCategoryLabel(item.category) }}</div>
                   <div class="text-[10px] text-gray-500 font-bold uppercase">{{ item.brand || 'N/A' }} {{ item.model ? '- '+item.model : '' }}</div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full" :class="getStatusColor(item.status)">
                    {{ getStatusLabel(item.status) }}
                  </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span v-if="item.department" class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded">{{ item.department.name }}</span>
                  <span v-else class="text-xs text-gray-400 italic">Kho chung HT</span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <!-- Nút Mượn (Chỉ hiện khi tài sản đang trống ở kho) -->
                    <button @click="openLoanModal(item)" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-2 py-1.5 rounded text-xs font-bold flex items-center">
                       <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg> Mượn/Trả
                    </button>

                    <button @click="openModal(item)" class="text-gray-400 hover:text-blue-600 p-1">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    <button @click="deleteItem(item.id)" class="text-gray-400 hover:text-red-600 p-1">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="assets.data.length === 0">
                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                  Không có dữ liệu phù hợp
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-end">
           <Pagination :links="assets.links" />
        </div>
      </div>
    </div>

    <!-- M O D A L : Thêm / Sửa Tài Sản -->
    <Modal :show="isModalOpen" @close="closeModal" maxWidth="2xl">
      <div class="p-6">
        <h2 class="text-lg font-black text-gray-900 mb-4">{{ isEditing ? 'Chỉnh sửa Tài sản' : 'Thêm Tài sản Mới' }}</h2>
        <form @submit.prevent="submitForm">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <InputLabel value="Tên gọi / Mô tả Tài sản *" />
              <TextInput v-model="form.name" type="text" class="mt-1 block w-full text-sm font-bold" required placeholder="VD: Bàn Mixer X32 / Đàn Piano Yamaha..." />
            </div>
            
            <div>
              <InputLabel value="Mã định danh (Số sơ thẩm) *" />
              <TextInput v-model="form.code" type="text" class="mt-1 block w-full text-sm font-mono uppercase" required placeholder="TS-AM-001" />
            </div>

            <div>
              <InputLabel value="Loại thiết bị *" />
              <select v-model="form.category" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                 <option v-for="cat in categoriesList" :key="cat.value" :value="cat.value">{{ cat.label }}</option>
              </select>
            </div>

            <div>
              <InputLabel value="Hãng / Thương hiệu" />
              <TextInput v-model="form.brand" type="text" class="mt-1 block w-full text-sm" />
            </div>

            <div>
              <InputLabel value="Model" />
              <TextInput v-model="form.model" type="text" class="mt-1 block w-full text-sm" />
            </div>
            
            <div class="md:col-span-2">
              <InputLabel value="Serial Number (S/N)" />
              <TextInput v-model="form.serial_number" type="text" class="mt-1 block w-full text-sm font-mono" />
            </div>

            <div class="md:col-span-2 border-t border-gray-100 pt-3 mt-1 relative"><span class="absolute -top-3 left-3 bg-white px-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tài chính & Quản lý</span></div>

            <div>
              <InputLabel value="Tình trạng hiện tại *" />
              <select v-model="form.status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                 <option v-for="st in statusList" :key="st.value" :value="st.value">{{ st.label }}</option>
              </select>
            </div>

            <div>
              <InputLabel value="Bàn giao cố định cho (Ban Ngành)" />
              <select v-model="form.department_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                 <option :value="null">-- Kho Tổng Hệ Thống --</option>
                 <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
              </select>
            </div>

            <div>
              <InputLabel value="Ngày Mua" />
              <TextInput v-model="form.purchase_date" type="date" class="mt-1 block w-full text-sm" />
            </div>

            <div>
              <InputLabel value="Nguyên giá lúc mua" />
              <TextInput v-model="form.purchase_price" type="number" step="1000" min="0" class="mt-1 block w-full text-sm font-mono" />
            </div>
            
            <div class="md:col-span-2">
              <InputLabel value="Ghi chú thêm" />
              <textarea v-model="form.notes" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"></textarea>
            </div>
            
          </div>

          <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-100">
            <SecondaryButton @click="closeModal">Hủy</SecondaryButton>
            <PrimaryButton class="ms-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
               Lưu Dữ Liệu
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- M O D A L : Mượn / Trả (Check-out / Check-in) -->
    <Modal :show="isLoanModalOpen" @close="closeLoanModal" maxWidth="3xl">
       <div class="p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex justify-between items-start mb-6">
             <div>
                <h2 class="text-xl font-black text-gray-900">Quản lý Mượn/Trả: <span class="text-indigo-600">{{ activeAsset?.code }}</span></h2>
                <p class="text-sm font-bold text-gray-600 mt-1">{{ activeAsset?.name }}</p>
             </div>
             <span class="px-3 py-1 text-xs font-bold uppercase rounded-lg" :class="getStatusColor(activeAsset?.status)">{{ getStatusLabel(activeAsset?.status) }}</span>
          </div>

          <!-- Lịch sử giao dịch mượn/trả -->
          <div class="mb-6">
             <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2 mb-3">Lịch sử Chuyển giao</h3>
             <div v-if="loansLoading" class="text-center py-4 text-sm text-gray-500">Đang tải dữ liệu...</div>
             <div v-else-if="loanHistory.length === 0" class="text-sm text-gray-500 italic">Chưa từng có ai mượn thiết bị này.</div>
             <div v-else class="space-y-3">
                <div v-for="l in loanHistory" :key="l.id" class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-sm">
                   <div class="flex justify-between items-start mb-2">
                      <div class="font-bold text-gray-800">
                         {{ l.borrower?.name }} 
                         <span class="text-xs text-gray-500 font-normal block">{{ l.department ? '('+l.department.name+')' : '' }}</span>
                      </div>
                      <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded" :class="l.status === 'borrowing' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700'">
                         {{ l.status === 'borrowing' ? 'ĐANG MƯỢN' : 'ĐÃ TRẢ' }}
                      </span>
                   </div>
                   <div class="grid grid-cols-2 gap-2 text-[11px] text-gray-600">
                      <div><span class="font-medium">Mượn lúc:</span> {{ new Date(l.borrowed_at).toLocaleString('vi-VN') }}</div>
                      <div><span class="font-medium">Người xuất:</span> {{ l.issuer?.name }}</div>
                      <div v-if="l.returned_at"><span class="font-medium text-emerald-600">Trả lúc:</span> {{ new Date(l.returned_at).toLocaleString('vi-VN') }}</div>
                      <div v-if="l.received_by"><span class="font-medium">Người nhận:</span> {{ l.receiver?.name }}</div>
                   </div>
                   <!-- Button Trả nếu đang mượn -->
                   <div v-if="l.status === 'borrowing'" class="mt-3 pt-3 border-t border-gray-200">
                      <button @click="showReturnForm(l)" class="w-full py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs rounded transition-colors uppercase tracking-wider">
                         Ghi Nhận Đã Thu Hồi Về Kho
                      </button>
                   </div>
                </div>
             </div>
          </div>

          <!-- FORM CHO MƯỢN (CHỈ HIỆN KHI KHÔNG CÓ AI ĐANG MƯỢN) -->
          <div v-if="!currentActiveLoan && !isReturning" class="bg-white border-2 border-dashed border-indigo-200 p-4 rounded-xl">
              <h3 class="text-sm font-black text-indigo-900 mb-4 flex items-center"><svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Tạo Phiếu Giao Thiết Bị (Check-out)</h3>
              
              <div v-if="['lost','broken','liquidated'].includes(activeAsset?.status)" class="p-3 bg-red-50 text-red-600 text-sm font-medium rounded-lg mb-4">
                 Không thể cho mượn tài sản đang hỏng hoặc thất lạc.
              </div>
              <form v-else @submit.prevent="submitLoan">
                 <div class="space-y-4">
                    <div class="relative">
                       <InputLabel value="Tìm Sinh viên/Tín hữu mượn *" />
                       <!-- Simple auto-complete simulation -->
                       <TextInput v-model="borrowerSearchQuery" @input="searchBorrower" @focus="showUserDropdown=true" type="text" class="mt-1 block w-full text-sm" placeholder="Gõ tên hoặc email..." />
                       <ul v-if="showUserDropdown && searchedUsers.length > 0" class="absolute z-10 top-full left-0 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 max-h-40 overflow-auto">
                          <li v-for="u in searchedUsers" :key="u.id" @click="selectBorrower(u)" class="px-3 py-2 text-sm hover:bg-indigo-50 cursor-pointer border-b border-gray-50 last:border-0 font-medium text-gray-800">
                             {{ u.name }} <span class="text-xs text-gray-500 ml-1 font-normal">{{ u.email }}</span>
                          </li>
                       </ul>
                       <div v-if="loanForm.borrower_id" class="mt-2 text-xs text-indigo-700 font-bold flex items-center">
                          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Đã chọn: {{ selectedBorrowerName }}
                       </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                       <div>
                          <InputLabel value="Mượn phục vụ Ban ngành (Tuỳ chọn)" />
                          <select v-model="loanForm.department_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                             <option :value="null">-- Mượn cá nhân --</option>
                             <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                          </select>
                       </div>
                       <div>
                          <InputLabel value="Ngày Hẹn Trả *" />
                          <TextInput v-model="loanForm.expected_return_date" type="datetime-local" class="mt-1 block w-full text-sm" required />
                       </div>
                    </div>

                    <div>
                       <InputLabel value="Tình trạng bàn giao (Ghi chú)" />
                       <TextInput v-model="loanForm.borrow_notes" type="text" class="mt-1 block w-full text-sm" placeholder="VD: Pin còn 50%, có dán tem..." />
                    </div>

                    <div class="flex justify-end pt-2">
                       <PrimaryButton :disabled="loanForm.processing || !loanForm.borrower_id">Xác Nhận Xuất Kho</PrimaryButton>
                    </div>
                 </div>
              </form>
          </div>

          <!-- FORM TRẢ LẠI (CHECK-IN) -->
          <div v-if="isReturning" class="bg-emerald-50 border-2 border-emerald-200 p-4 rounded-xl">
             <h3 class="text-sm font-black text-emerald-900 mb-4 flex items-center"><svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Xác Nhận Thu Hồi Thiết Bị</h3>
             <form @submit.prevent="submitReturn">
                <div class="space-y-4">
                   <div>
                       <InputLabel value="Đánh giá tình trạng Thu về *" />
                       <select v-model="returnForm.asset_status" class="mt-1 block w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm text-sm font-bold" required>
                          <option value="in_use">Bình thường (In use)</option>
                          <option value="maintenance">Cần bảo trì / Vệ sinh</option>
                          <option value="broken">Bị hỏng hóc sau khi dùng</option>
                          <option value="lost">Xác nhận Đã Báo Mất</option>
                       </select>
                    </div>

                    <div v-if="returnForm.asset_status === 'lost'" class="text-red-600 bg-red-50 p-2 text-xs font-bold rounded border border-red-200">Lưu ý: Báo mất sẽ khoá tài sản này và đưa vào diện Thất lạc.</div>

                    <div>
                       <InputLabel value="Ghi chú khi nhận lại" />
                       <TextInput v-model="returnForm.return_notes" type="text" class="mt-1 block w-full text-sm" placeholder="VD: Bị trầy xước nhẹ..." />
                    </div>

                    <div class="flex justify-end pt-2 gap-2">
                       <button type="button" @click="isReturning=false" class="px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-200 rounded">Huỷ thao tác</button>
                       <PrimaryButton class="bg-emerald-600 hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900" :disabled="returnForm.processing">Hoàn Thành Nhận Trả</PrimaryButton>
                    </div>
                </div>
             </form>
          </div>
       </div>
    </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import debounce from 'lodash/debounce';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps(['assets', 'filters', 'departments', 'stats']);

const search = ref(props.filters.search || '');
const filterCategory = ref(props.filters.category || 'all');
const filterStatus = ref(props.filters.status || 'all');

watch([search, filterCategory, filterStatus], debounce(([val1, val2, val3]) => {
    router.get(route('admin.assets.index'), { search: val1, category: val2, status: val3 }, { preserveState: true, replace: true });
}, 300));

// Helpers
const categoriesList = [
    { value: 'electronics', label: 'Điện tử & Khuếch đại' },
    { value: 'furniture', label: 'Bàn ghế & Nội thất' },
    { value: 'musical', label: 'Nhạc cụ' },
    { value: 'books', label: 'Sách & Kinh Thánh' },
    { value: 'vehicle', label: 'Phương tiện chở' },
    { value: 'other', label: 'Khác' }
];

const statusList = [
    { value: 'new', label: 'Mới 100%' },
    { value: 'in_use', label: 'Đang dùng' },
    { value: 'maintenance', label: 'Đang sửa chữa' },
    { value: 'broken', label: 'Hỏng hóc' },
    { value: 'lost', label: 'Thất lạc' },
    { value: 'liquidated', label: 'Thanh lý' }
];

const getCategoryLabel = v => categoriesList.find(c => c.value === v)?.label || v;
const getStatusLabel = v => statusList.find(s => s.value === v)?.label || v;
const getStatusColor = v => {
    const map = {
        new: 'bg-emerald-100 text-emerald-800',
        in_use: 'bg-indigo-100 text-indigo-800',
        maintenance: 'bg-amber-100 text-amber-800 border border-amber-300',
        broken: 'bg-red-100 text-red-800 border border-red-300',
        lost: 'bg-gray-800 text-white',
        liquidated: 'bg-gray-200 text-gray-500 line-through'
    };
    return map[v] || 'bg-gray-100';
};

// --- CRUD MODAL ---
const isModalOpen = ref(false);
const isEditing = ref(false);
const activeId = ref(null);

const form = useForm({
    name: '', code: '', category: 'electronics', brand: '', model: '', serial_number: '',
    purchase_date: '', purchase_price: null, status: 'in_use', department_id: null, notes: ''
});

const openModal = (item) => {
    form.clearErrors();
    if(item) {
        isEditing.value = true;
        activeId.value = item.id;
        form.name = item.name; form.code = item.code; form.category = item.category;
        form.brand = item.brand; form.model = item.model; form.serial_number = item.serial_number;
        form.purchase_date = item.purchase_date; form.purchase_price = item.purchase_price;
        form.status = item.status; form.department_id = item.department_id; form.notes = item.notes;
    } else {
        isEditing.value = false;
        activeId.value = null;
        form.reset();
        form.code = 'TS-' + Math.floor(Math.random()*90000 + 10000); // Random mã Code nhanh
    }
    isModalOpen.value = true;
};
const closeModal = () => isModalOpen.value = false;

const submitForm = () => {
    if(isEditing.value) {
        form.put(route('admin.assets.update', activeId.value), { onSuccess: () => closeModal() });
    } else {
        form.post(route('admin.assets.store'), { onSuccess: () => closeModal() });
    }
};

const deleteItem = (id) => {
    if(confirm('Bạn có chắc xoá vĩnh viễn tài sản này khỏi kho?')) {
        router.delete(route('admin.assets.destroy', id), { preserveScroll: true });
    }
};

// --- LOAN MODAL (Check-in / out) ---
const isLoanModalOpen = ref(false);
const activeAsset = ref(null);
const loanHistory = ref([]);
const loansLoading = ref(false);
const currentActiveLoan = ref(false);

const openLoanModal = async (asset) => {
    activeAsset.value = asset;
    isLoanModalOpen.value = true;
    isReturning.value = false;
    currentActiveLoan.value = false;
    await fetchLoans();
};
const closeLoanModal = () => isLoanModalOpen.value = false;

const fetchLoans = async () => {
    loansLoading.value = true;
    try {
        const { data } = await axios.get(route('admin.assets.loans', activeAsset.value.id));
        loanHistory.value = data;
        currentActiveLoan.value = data.some(l => l.status === 'borrowing');
    } catch(e) { console.error('Fetch loans failed', e); }
    finally { loansLoading.value = false; }
};

// Checkout Form
const loanForm = useForm({
    borrower_id: null, department_id: null, borrowed_at: new Date().toISOString().slice(0, 16),
    expected_return_date: '', borrow_notes: ''
});
const borrowerSearchQuery = ref('');
const showUserDropdown = ref(false);
const searchedUsers = ref([]);
const selectedBorrowerName = ref('');

const searchBorrower = debounce(async () => {
    if(borrowerSearchQuery.value.length < 2) { searchedUsers.value = []; return; }
    const { data } = await axios.get(route('admin.assets.api.search-users', { q: borrowerSearchQuery.value }));
    searchedUsers.value = data;
}, 300);

const selectBorrower = (u) => {
    loanForm.borrower_id = u.id;
    selectedBorrowerName.value = u.name;
    borrowerSearchQuery.value = u.name;
    showUserDropdown.value = false;
};

const submitLoan = () => {
    loanForm.post(route('admin.assets.loan.store', activeAsset.value.id), {
        onSuccess: () => {
            loanForm.reset();
            borrowerSearchQuery.value = '';
            fetchLoans(); // Refresh history
        }
    });
};

// Check-in Form
const isReturning = ref(false);
const returnForm = useForm({
    returned_at: new Date().toISOString().slice(0, 16),
    status: 'returned',
    return_notes: '',
    asset_status: 'in_use' // Default assumed it comes back ok
});
const returningLoanId = ref(null);

const showReturnForm = (loan) => {
    isReturning.value = true;
    returningLoanId.value = loan.id;
    returnForm.asset_status = activeAsset.value.status === 'new' ? 'in_use' : activeAsset.value.status;
};

const submitReturn = () => {
    if (returnForm.asset_status === 'lost') returnForm.status = 'lost';
    returnForm.patch(route('admin.assets.loan.return', returningLoanId.value), {
        onSuccess: () => {
            isReturning.value = false;
            fetchLoans();
            // Optional trigger inertia visit to update asset layout list
            router.reload({ only: ['assets', 'stats'] });
        }
    });
};
</script>