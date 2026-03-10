<template>
  <AuthenticatedLayout>
    <template #header>Nhập Liệu Dâng Hiến (Batch Entry)</template>

    <div class="py-4 space-y-6 w-full">
        <div class="bg-indigo-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
           <div class="absolute right-0 top-0 opacity-10 translate-x-12 -translate-y-12">
               <svg class="h-64 w-64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
           </div>
           
           <h2 class="text-2xl font-black mb-2 relative flex items-center">
              <Link :href="route('admin.donations.index')" class="text-indigo-200 hover:text-white mr-4 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
              </Link>
              Biên lai Hàng loạt
           </h2>
           <p class="text-indigo-200 text-sm max-w-2xl">Nhập nhanh nhiều bản ghi dâng hiến cùng một lúc vào buổi nhóm ngày Chúa Nhật. Tiết kiệm thời gian cho Thủ Quỹ. Nhấn Nút "+" để thêm dòng mới.</p>
        </div>

        <form @submit.prevent="submitBatch" class="space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50 text-[10px] text-gray-500 uppercase font-black tracking-wider">
                         <tr>
                            <th class="px-4 py-3 text-center w-10">STT</th>
                            <th class="px-4 py-3 text-left w-64">Ngày & Quỹ</th>
                            <th class="px-4 py-3 text-left min-w-[300px]">Thành Viên Dâng (Search / Auto-complete)</th>
                            <th class="px-4 py-3 text-left w-48">Hình Thức & Loại</th>
                            <th class="px-4 py-3 text-left w-48">Số Tiền (VNĐ)</th>
                            <th class="px-4 py-3 text-left">Ghi chú / MGD</th>
                            <th class="px-4 py-3 text-center w-16">Xoá</th>
                         </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-100">
                         <tr v-for="(entry, idx) in form.entries" :key="idx" class="hover:bg-gray-50 transition-colors">
                             <td class="px-4 py-3 text-center font-black text-gray-300">{{ idx + 1 }}</td>
                             
                             <td class="px-4 py-3">
                                 <input v-model="entry.donation_date" type="date" class="w-full text-xs border-0 border-b border-gray-200 focus:ring-0 focus:border-indigo-500 bg-transparent p-0 pb-1 mb-2 font-mono" required />
                                 <select v-model="entry.fund_id" class="w-full text-xs font-bold text-indigo-700 border-0 border-b border-indigo-200 focus:ring-0 focus:border-indigo-500 bg-transparent p-0 pb-1" required>
                                    <option v-for="f in funds" :key="f.id" :value="f.id">{{ f.name }}</option>
                                 </select>
                             </td>

                             <td class="px-4 py-3 relative">
                                <div class="flex flex-col gap-1">
                                   <!-- The search input -->
                                   <div class="relative">
                                     <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                        <svg class="h-3 w-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                     </div>
                                     <input 
                                         type="text" 
                                         :value="entry.searchTerm" 
                                         @input="onSearchInput(idx, $event.target.value)" 
                                         @focus="entry.showDropdown = true"
                                         @blur="hideDropdownDelayed(idx)"
                                         placeholder="Phím gõ 3 chữ cái tìm tên/mã tín hữu..." 
                                         class="w-full text-sm pl-7 border border-gray-200 rounded-md focus:ring-1 focus:ring-indigo-500 py-1.5" 
                                     />
                                     <button type="button" v-if="entry.user_id" @click="clearUser(idx)" class="absolute inset-y-0 right-0 pr-2 flex items-center text-gray-400 hover:text-red-500">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                     </button>
                                   </div>
                                   
                                   <div v-if="entry.user_id" class="text-[10px] text-emerald-600 font-bold bg-emerald-50 py-1 px-2 rounded flex items-center">
                                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                      {{ entry.selectedUser?.name }} ({{ entry.selectedUser?.member_code }})
                                   </div>
                                   <div v-else class="text-[10px] text-gray-400 font-medium italic">Ghi nhận: Vô danh / Khách vãng lai</div>
                                   
                                   <!-- Autocomplete Dropdown -->
                                   <div v-if="entry.showDropdown && entry.searchResults.length > 0" class="absolute z-50 mt-10 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto left-0">
                                      <div v-for="u in entry.searchResults" :key="u.id" @click="selectUser(idx, u)" class="px-3 py-2 text-sm cursor-pointer hover:bg-indigo-50 border-b border-gray-50 flex justify-between items-center group">
                                         <div>
                                             <div class="font-bold text-gray-800 group-hover:text-indigo-700">{{ u.name }}</div>
                                             <div class="text-[10px] text-gray-400">{{ u.email || u.phone }}</div>
                                         </div>
                                         <div class="text-[10px] font-mono text-gray-500 border border-gray-200 px-1 rounded">{{ u.member_code }}</div>
                                      </div>
                                   </div>
                                </div>
                             </td>

                             <td class="px-4 py-3">
                                 <select v-model="entry.type" class="w-full text-xs border border-gray-200 rounded-md focus:ring-1 focus:ring-indigo-500 mb-2 py-1.5" required>
                                    <option value="tithe">Thập Phân (1/10)</option>
                                    <option value="offering">Lạc Quyên bình thường</option>
                                    <option value="thanksgiving">Cảm Tạ</option>
                                    <option value="pledge">Hoàn thành Hứa dâng</option>
                                    <option value="special">Đặc Biệt</option>
                                 </select>
                                 <select v-model="entry.payment_method" class="w-full text-xs font-bold text-gray-700 border border-gray-200 bg-gray-50 rounded-md focus:ring-1 focus:ring-indigo-500 py-1.5" required>
                                    <option value="cash">Tiền mặt (Phong bì)</option>
                                    <option value="transfer">Chuyển khoản</option>
                                    <option value="card">Quẹt Thẻ</option>
                                 </select>
                             </td>

                             <td class="px-4 py-3">
                                <div class="relative">
                                  <input v-model="entry.amount" type="number" min="1000" class="w-full text-sm font-black text-right pr-8 border border-gray-200 rounded-md focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 text-emerald-700 py-1.5" required />
                                  <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-xs font-bold text-gray-400">₫</div>
                                </div>
                                <div class="text-[10px] text-right text-gray-400 mt-1 uppercase tracking-widest font-bold">
                                   {{ new Intl.NumberFormat('vi-VN').format(entry.amount || 0) }} VNĐ
                                </div>
                             </td>

                             <td class="px-4 py-3">
                                <input v-if="entry.payment_method === 'transfer'" v-model="entry.reference_number" type="text" class="w-full text-xs font-mono uppercase border border-gray-200 rounded-md focus:ring-1 focus:ring-indigo-500 py-1.5 mb-1 bg-blue-50/50" placeholder="Mã giao dịch / UNC..." />
                                <textarea v-model="entry.notes" rows="1" class="w-full text-xs border border-gray-200 rounded-md focus:ring-1 focus:ring-indigo-500 py-1 placeholder-gray-300" placeholder="Lời cầu nguyện trên phong bì..."></textarea>
                             </td>

                             <td class="px-4 py-3 text-center">
                                <button type="button" @click="removeEntry(idx)" class="text-gray-300 hover:text-red-500 transition-colors p-2" title="Xoá dòng" :disabled="form.entries.length === 1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                             </td>
                         </tr>
                      </tbody>
                  </table>
                </div>
                
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center text-sm">
                   <button type="button" @click="addEntry" class="flex items-center font-bold text-indigo-600 hover:text-indigo-800 transition-colors px-4 py-2 bg-indigo-100 rounded-lg">
                      <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg> Thêm (Add Row)
                   </button>
                </div>
            </div>

            <!-- Total và Submit -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
               <div>
                  <div class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-1">Tổng cộng Lô nhập</div>
                  <div class="text-3xl font-black text-indigo-700">{{ new Intl.NumberFormat('vi-VN').format(totalBatchAmount) }} VNĐ</div>
                  <div class="text-[11px] font-medium text-gray-400 mt-1">Đã nhập {{ form.entries.length }} bản ghi</div>
               </div>
               <div class="flex items-center gap-3">
                  <span class="text-xs text-amber-600 font-bold bg-amber-50 px-3 py-2 rounded-lg mr-2 hidden md:inline-block"><svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Kiểm tra kỹ trước khi Nạp kho</span>
                  <SecondaryButton type="button" @click="router.get(route('admin.donations.index'))">Huỷ / Quay Lại</SecondaryButton>
                  <PrimaryButton class="bg-indigo-600 px-8 py-3 hover:bg-indigo-700" :disabled="form.processing">
                     Lưu Lô Dữ Liệu
                  </PrimaryButton>
               </div>
            </div>
        </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import debounce from 'lodash/debounce';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps(['funds']);

// Khởi tạo dòng nhập liệu trắng
const createEmptyEntry = () => ({
    donation_date: new Date().toISOString().slice(0, 10),
    fund_id: props.funds.length > 0 ? props.funds[0].id : '',
    user_id: null,
    type: 'tithe',
    amount: '',
    payment_method: 'cash',
    reference_number: '',
    notes: '',
    
    // UI Helpers state cho từng row
    searchTerm: '',
    searchResults: [],
    showDropdown: false,
    selectedUser: null
});

const form = useForm({
    entries: [
        createEmptyEntry()
    ]
});

// Tính tổng tiền realtime
const totalBatchAmount = computed(() => {
    return form.entries.reduce((sum, item) => sum + (Number(item.amount) || 0), 0);
});

const addEntry = () => {
    // Clone thông tin quỹ và ngày từ dòng cuối cùng để tiết kiệm thời gian gõ
    const lastEntry = form.entries[form.entries.length - 1];
    const newEntry = createEmptyEntry();
    newEntry.donation_date = lastEntry.donation_date;
    newEntry.fund_id = lastEntry.fund_id;
    newEntry.type = lastEntry.type;
    
    form.entries.push(newEntry);
};

const removeEntry = (idx) => {
    if (form.entries.length > 1) {
        form.entries.splice(idx, 1);
    }
};

// Autocomplete logic for EACH ROW independent
const performSearch = debounce(async (idx, term) => {
    if(term.length < 2) {
        form.entries[idx].searchResults = [];
        return;
    }
    try {
        const { data } = await axios.get(route('admin.donations.api.search-users'), { params: { q: term }});
        form.entries[idx].searchResults = data;
        form.entries[idx].showDropdown = true;
    } catch(e) { console.error('Lỗi search user', e); }
}, 300);

const onSearchInput = (idx, value) => {
    form.entries[idx].searchTerm = value;
    performSearch(idx, value);
};

const selectUser = (idx, user) => {
    form.entries[idx].user_id = user.id;
    form.entries[idx].selectedUser = user;
    form.entries[idx].searchTerm = user.name;
    form.entries[idx].showDropdown = false;
};

const clearUser = (idx) => {
    form.entries[idx].user_id = null;
    form.entries[idx].selectedUser = null;
    form.entries[idx].searchTerm = '';
};

const hideDropdownDelayed = (idx) => {
    setTimeout(() => {
        if(form.entries[idx]) {
            form.entries[idx].showDropdown = false;
        }
    }, 200);
};

// Submit API
const submitBatch = () => {
    form.post(route('admin.donations.store-batch'));
};
</script>