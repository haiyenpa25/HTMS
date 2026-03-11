<template>
  <AuthenticatedLayout>
    <template #header>Quản lý Tài chính: Dâng hiến & Thập phân</template>

    <div class="py-4 space-y-6 w-full">
      
      <!-- Thống kê Tài chính nhanh -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
         <!-- Card 1: Tổng Thu Tháng Này -->
         <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-2xl shadow-lg border border-indigo-700 p-6 relative overflow-hidden text-white">
            <div class="absolute right-0 top-0 opacity-10 translate-x-4 -translate-y-4">
               <svg class="h-32 w-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            </div>
            <div class="text-indigo-200 text-xs font-bold uppercase tracking-wider mb-2 flex justify-between">
               <span>Tổng Dâng Hiến (Tháng {{ new Date().getMonth() + 1 }})</span>
            </div>
            <div class="text-4xl font-black mb-1 truncate">{{ new Intl.NumberFormat('vi-VN').format(stats.current_month_total || 0) }} ₫</div>
            <div class="text-sm text-indigo-200">Bao gồm tất cả các Quỹ</div>
         </div>

         <!-- Card 2: Tổng Thập Phân Tháng Này -->
         <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-2xl shadow-lg border border-emerald-700 p-6 relative overflow-hidden text-white">
            <div class="absolute right-0 top-0 opacity-10 translate-x-4 -translate-y-4">
               <svg class="h-32 w-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="text-emerald-200 text-xs font-bold uppercase tracking-wider mb-2 flex justify-between">
               <span>Tiền Thập Phân (1/10)</span>
            </div>
            <div class="text-4xl font-black mb-1 truncate">{{ new Intl.NumberFormat('vi-VN').format(stats.tithe_month_total || 0) }} ₫</div>
            <div class="text-sm text-emerald-200">Phần Mười của Hội Chúng</div>
         </div>

         <!-- Card 3: Nút Chức năng Nhanh -->
         <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-center">
            <Link :href="route('admin.donations.batch')" class="group flex items-center justify-between p-3 bg-indigo-50 rounded-xl hover:bg-indigo-600 transition-colors mb-3">
               <div>
                  <div class="text-sm font-bold text-indigo-700 group-hover:text-white transition-colors">Nhập Liệu Dâng Hiến (Batch Entry)</div>
                  <div class="text-xs text-indigo-500 group-hover:text-indigo-200 transition-colors">Dành cho Thủ Quỹ nhập Lô hàng tuần</div>
               </div>
               <div class="bg-indigo-200 group-hover:bg-indigo-500 rounded-full p-2 transition-colors">
                  <svg class="h-5 w-5 text-indigo-700 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
               </div>
            </Link>

            <button @click="openFundModal()" class="group flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors text-left border border-gray-100">
               <div>
                  <div class="text-sm font-bold text-gray-700">Quản lý Sổ Quỹ (Fund Setup)</div>
                  <div class="text-xs text-gray-500">Thêm mới Quỹ Kiến Thiết, Truyền Giáo...</div>
               </div>
               <div class="bg-gray-200 rounded-full p-2">
                  <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
               </div>
            </button>
         </div>
      </div>

      <!-- Khối Bảng Danh sách Donation -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex flex-col md:flex-row gap-4 items-center justify-between bg-gray-50/50">
          <div class="flex flex-1 w-full gap-2 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input v-model="search" type="text" placeholder="Tìm tên Tín hữu, mã Code..." class="w-full md:w-64 pl-9 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" />
            
            <select v-model="filterFund" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm hidden md:block max-w-[200px]">
               <option value="">-- Tất cả Quỹ --</option>
               <option v-for="f in funds" :key="f.id" :value="f.id">{{ f.name }}</option>
            </select>

            <select v-model="filterType" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm hidden md:block">
               <option value="">-- Tất cả Hình thức --</option>
               <option value="tithe">Thập Phân (1/10)</option>
               <option value="offering">Lạc Quyên</option>
               <option value="thanksgiving">Cảm Tạ</option>
               <option value="pledge">Lời Hứa Dâng</option>
               <option value="special">Đặc Biệt / Khác</option>
            </select>
          </div>
          <div class="shrink-0 flex items-center gap-2">
            <span class="text-xs text-gray-500 font-medium">Lưu ý: Biên lai lỗi cần báo cho Admin DB xử lý xoá mềm để chống gian lận.</span>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-[11px] text-gray-500 uppercase font-black tracking-wider">
              <tr>
                <th class="px-4 py-3 text-left w-10">Mã</th>
                <th class="px-4 py-3 text-left">Người Dâng/Thành Viên</th>
                <th class="px-4 py-3 text-right">Số Tiền (VNĐ)</th>
                <th class="px-4 py-3 text-left">Hạng Mục</th>
                <th class="px-4 py-3 text-left">Ngày Ghi Nhận</th>
                <th class="px-4 py-3 text-left">Thủ Quỹ (Người lưu)</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr v-for="item in donations.data" :key="item.id" class="hover:bg-indigo-50/30 transition-colors">
                <td class="px-4 py-4 whitespace-nowrap text-xs font-mono text-gray-400">
                   #{{ String(item.id).padStart(5, '0') }}
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex-shrink-0 flex items-center justify-center text-indigo-700 font-black text-xs">
                       {{ item.user ? item.user.name.charAt(0).toUpperCase() : '?' }}
                    </div>
                    <div class="ml-3">
                      <div class="font-bold text-sm text-gray-900">{{ item.user ? item.user.name : 'Khách vãng lai / Ẩn danh' }}</div>
                      <div v-if="item.user" class="text-[11px] text-gray-500 mt-0.5">Mã TH: <span class="font-medium text-gray-700">{{ item.user.member_code || 'N/A' }}</span></div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-right">
                   <div class="text-sm font-black text-emerald-600">{{ new Intl.NumberFormat('vi-VN').format(item.amount) }}</div>
                   <div class="text-[10px] text-gray-500 uppercase font-bold mt-1 tracking-wider">{{ getPaymentMethodLabel(item.payment_method) }}</div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                   <div class="mb-1"><span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full" :class="getTypeColor(item.type)">{{ getTypeLabel(item.type) }}</span></div>
                   <div class="text-[11px] font-medium text-gray-700">{{ item.fund?.name || 'Vô danh' }}</div>
                   <div v-if="item.notes" class="text-[11px] text-gray-400 italic mt-0.5 truncate max-w-[200px]" :title="item.notes">"{{ item.notes }}"</div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                   {{ new Date(item.donation_date).toLocaleDateString('vi-VN') }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-xs text-gray-500">
                   {{ item.recorder ? item.recorder.name : 'System' }}
                </td>
              </tr>
              <tr v-if="donations.data.length === 0">
                <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500">
                  <div class="flex flex-col items-center justify-center">
                     <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                     Không tìm thấy giao dịch nào.
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-end">
           <Pagination :links="donations.links" />
        </div>
      </div>
      
      <!-- DANH SÁCH QUỸ CỦA HỘI THÁNH -->
      <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden mt-8">
         <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h3 class="text-[13px] font-black text-gray-900 uppercase tracking-wider">Danh mục Sổ Quỹ Kế toán</h3>
         </div>
         <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
             <div v-for="fund in funds" :key="fund.id" class="border border-gray-100 rounded-lg p-4 hover:border-indigo-300 hover:shadow-md transition-all cursor-pointer relative" @click="openFundModal(fund)">
                 <div class="absolute top-3 right-3 w-2 h-2 rounded-full" :class="fund.is_active ? 'bg-emerald-500' : 'bg-red-500'"></div>
                 <h4 class="font-bold text-gray-900 text-sm truncate pr-4" :title="fund.name">{{ fund.name }}</h4>
                 <div class="text-[10px] text-gray-400 font-mono uppercase mt-0.5">{{ fund.code }} • {{ fund.donations_count }} giao dịch</div>
                 <div class="mt-3 text-lg font-black text-indigo-700">{{ new Intl.NumberFormat('vi-VN').format(fund.balance) }} ₫</div>
             </div>
         </div>
      </div>
    </div>

    <!-- S L I D E  O V E R : TẠO / SỬA QUỸ -->
    <SlideOver v-model="isFundModalOpen" :title="isEditingFund ? 'Chỉnh Sửa Quỹ' : 'Thêm Quỹ Mới (Sổ cái)'" size="sm">
      <p class="text-xs text-gray-500 mb-6">Định nghĩa một túi tiền để điều tiết các dòng dâng hiến.</p>
      <form id="fundForm" @submit.prevent="submitFundForm">
         <div class="space-y-4">
              <div>
                <InputLabel value="Tên Quỹ *" />
                <TextInput v-model="fundForm.name" type="text" class="mt-1 block w-full text-sm font-bold" required placeholder="VD: Quỹ Xây Dựng Đền Thờ Mới" />
              </div>
              <div class="grid grid-cols-2 gap-4">
                  <div>
                    <InputLabel value="Mã định danh (Code)*" />
                    <TextInput v-model="fundForm.code" type="text" class="mt-1 block w-full text-xs font-mono uppercase" required placeholder="XAYDUNG" :disabled="isEditingFund" />
                    <div class="text-[9px] text-gray-400 mt-1">Viết liền không dấu, không đổi sau khi tạo.</div>
                  </div>
                  <div>
                    <InputLabel value="Quy mô / Dạng *" />
                    <select v-model="fundForm.type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                       <option value="general">Quỹ Chung (General)</option>
                       <option value="building">Xây Dựng (Building)</option>
                       <option value="mission">Truyền Giáo (Mission)</option>
                       <option value="charity">Từ Thiện (Charity)</option>
                       <option value="other">Khác</option>
                    </select>
                  </div>
              </div>
              <div>
                 <InputLabel value="Mô tả mục đích" />
                 <textarea v-model="fundForm.description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="Mô tả ngắn..."></textarea>
              </div>
              <div class="flex items-center mt-2">
                 <input type="checkbox" id="is_active" v-model="fundForm.is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 text-sm">
                 <label for="is_active" class="ml-2 block text-sm text-gray-900 font-medium">Còn hoạt động (Cho phép thu)</label>
              </div>
           </div>
        </form>
      <template #footer>
        <div class="flex justify-end gap-3 w-full">
            <SecondaryButton type="button" @click="closeFundModal">Hủy</SecondaryButton>
            <PrimaryButton form="fundForm" type="submit" :disabled="fundForm.processing" class="bg-indigo-600 hover:bg-indigo-700">Lưu Quỹ</PrimaryButton>
        </div>
      </template>
    </SlideOver>

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SlideOver from '@/Components/SlideOver.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps(['donations', 'funds', 'filters', 'stats']);

const search = ref(props.filters.search || '');
const filterFund = ref(props.filters.fund_id || '');
const filterType = ref(props.filters.type || '');

watch([search, filterFund, filterType], debounce(([val1, val2, val3]) => {
    router.get(route('admin.donations.index'), { search: val1, fund_id: val2, type: val3 }, { preserveState: true, replace: true });
}, 300));

// Helpers Giao diện
const getPaymentMethodLabel = (v) => {
    const map = { cash: 'Tiền mặt', transfer: 'Chuyển khoản', card: 'Quẹt Thẻ' };
    return map[v] || v;
};
const getTypeLabel = (v) => {
    const map = { tithe: '1/10', offering: 'Lạc Quyên', thanksgiving: 'Cảm Tạ', pledge: 'Hứa Dâng', special: 'Đặc Biệt' };
    return map[v] || v;
};
const getTypeColor = (v) => {
    const map = { 
        tithe: 'bg-emerald-100 text-emerald-800', 
        offering: 'bg-blue-100 text-blue-800', 
        thanksgiving: 'bg-amber-100 text-amber-800',
        pledge: 'bg-indigo-100 text-indigo-800',
        special: 'bg-purple-100 text-purple-800'
    };
    return map[v] || 'bg-gray-100 text-gray-800';
};

// --- QUẢN LÝ QUỸ MODAL ---
const isFundModalOpen = ref(false);
const isEditingFund = ref(false);
const activeFundId = ref(null);

const fundForm = useForm({
    name: '', code: '', description: '', type: 'general', is_active: true
});

const openFundModal = (fund = null) => {
    fundForm.clearErrors();
    if(fund) {
        isEditingFund.value = true;
        activeFundId.value = fund.id;
        fundForm.name = fund.name; fundForm.code = fund.code; fundForm.description = fund.description;
        fundForm.type = fund.type; fundForm.is_active = fund.is_active;
    } else {
        isEditingFund.value = false;
        activeFundId.value = null;
        fundForm.reset();
    }
    isFundModalOpen.value = true;
};

const closeFundModal = () => isFundModalOpen.value = false;

const submitFundForm = () => {
    if(isEditingFund.value) {
        fundForm.put(route('admin.funds.update', activeFundId.value), { onSuccess: () => closeFundModal() });
    } else {
        fundForm.post(route('admin.funds.store'), { onSuccess: () => closeFundModal() });
    }
};

</script>