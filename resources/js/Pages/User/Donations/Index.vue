<template>
    <AuthenticatedLayout>
        <template #header>Lịch sử phần dâng hiến vinh hiển Chúa</template>

        <div class="py-4 space-y-6 max-w-5xl mx-auto">
            <!-- Hero Statement -->
            <div class="bg-gradient-to-br from-gray-900 to-indigo-900 rounded-3xl p-8 text-white shadow-2xl relative overflow-hidden border border-indigo-800">
                <div class="absolute right-0 top-0 opacity-10 translate-x-12 -translate-y-12">
                   <svg class="h-64 w-64" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-start md:items-end relative z-10">
                    <div>
                        <div class="text-indigo-200 text-sm font-bold uppercase tracking-widest mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            Tài Liệu Bảo Mật Tuyệt Đối
                        </div>
                        <h2 class="text-3xl font-black mb-1">Thống Kê Dâng Hiến {{ currentYear }}</h2>
                        <p class="text-indigo-300 text-sm max-w-lg mb-4">"Mỗi người hãy quyên tùy theo lòng mình đã định, không miễn cưỡng, không ép uổng; vì Đức Chúa Trời yêu kẻ dâng hiến cách vui lòng." - 2 Cô-rinh-tô 9:7</p>
                    </div>
                    <div class="text-right mt-4 md:mt-0">
                        <div class="text-[11px] text-indigo-200 uppercase font-black tracking-widest mb-1">Tổng dâng ({{ currentYear }})</div>
                        <div class="text-4xl font-black text-emerald-400">{{ new Intl.NumberFormat('vi-VN').format(yearlyTotal) }} ₫</div>
                    </div>
                </div>
            </div>

            <!-- Lịch sử Giao dịch -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h3 class="font-black text-gray-800 text-sm uppercase tracking-wider flex items-center">
                       <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                       Sao Kê Của Bạn
                    </h3>
                    <div class="flex items-center gap-2">
                         <select v-model="filterYear" @change="fetchByYear" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm py-1.5 font-bold text-gray-700">
                             <option v-for="y in availableYears" :key="y" :value="y">Năm {{ y }}</option>
                         </select>
                         <button onclick="window.print()" class="text-xs bg-white border border-gray-200 hover:bg-gray-50 text-gray-600 font-bold py-1.5 px-3 rounded-md flex items-center shadow-sm transition-colors">
                             <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg> In Giấy
                         </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto print:p-0">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 text-[11px] text-gray-500 uppercase font-black tracking-wider">
                      <tr>
                        <th class="px-6 py-4 text-left">Mã Số & Ngày</th>
                        <th class="px-6 py-4 text-left">Mục Đích Dâng</th>
                        <th class="px-6 py-4 text-left">Hạng Mục</th>
                        <th class="px-6 py-4 text-right">Số Tiền (VNĐ)</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                      <tr v-for="item in donations.data" :key="item.id" class="hover:bg-indigo-50/20 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-gray-900 border-b border-gray-100 pb-1 mb-1 inline-block">{{ new Date(item.donation_date).toLocaleDateString('vi-VN') }}</div>
                            <div class="text-[10px] text-gray-400 font-mono">ID: #{{ String(item.id).padStart(5, '0') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-lg border shadow-sm" :class="getTypeColor(item.type)">
                                {{ getTypeLabel(item.type) }}
                            </span>
                            <div v-if="item.notes" class="text-xs text-gray-500 mt-2 bg-gray-50 p-2 rounded-lg italic border border-gray-100">"{{ item.notes }}"</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-800">{{ item.fund?.name || 'Vô danh' }}</div>
                            <div class="text-xs text-gray-400 mt-1 uppercase">{{ getPaymentMethodLabel(item.payment_method) }} <span v-if="item.reference_number" class="text-[10px] px-1 bg-gray-100 border rounded font-mono text-gray-500">{{ item.reference_number }}</span></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-lg font-black text-emerald-600">{{ new Intl.NumberFormat('vi-VN').format(item.amount) }}</div>
                        </td>
                      </tr>
                      <tr v-if="donations.data.length === 0">
                        <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">
                          <div class="flex flex-col items-center justify-center">
                             <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                             Không có bất kỳ ghi nhận dâng hiến nào trong năm {{ filterYear }}.
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Footer Pagination -->
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-end print:hidden">
                   <Pagination :links="donations.links" />
                </div>
            </div>
            
            <div class="text-center text-xs text-gray-400 mt-8 mb-4">
                <p>Biên lai dâng hiến này được tạo máy bởi {{ $page.props.auth.user.name }}.<br/>Mọi thắc mắc số liệu xin vui lòng liên hệ Thủ Quỹ Hội Thánh hoặc Ban Chấp Sự.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps(['donations', 'yearlyTotal', 'availableYears', 'currentYear']);
const filterYear = ref(props.currentYear);

const fetchByYear = () => {
    router.get(route('user.donations.index'), { year: filterYear.value }, { preserveState: true, replace: true });
};

// Helpers Giao diện
const getPaymentMethodLabel = (v) => {
    const map = { cash: 'Tiền mặt', transfer: 'Chuyể khoản', card: 'Thẻ' };
    return map[v] || v;
};
const getTypeLabel = (v) => {
    const map = { tithe: '1/10', offering: 'Lạc Quyên', thanksgiving: 'Cảm Tạ', pledge: 'Hứa Dâng', special: 'Đặc Biệt' };
    return map[v] || v;
};
const getTypeColor = (v) => {
    const map = { 
        tithe: 'bg-emerald-100 text-emerald-800 border-emerald-200', 
        offering: 'bg-blue-100 text-blue-800 border-blue-200', 
        thanksgiving: 'bg-amber-100 text-amber-800 border-amber-200',
        pledge: 'bg-indigo-100 text-indigo-800 border-indigo-200',
        special: 'bg-purple-100 text-purple-800 border-purple-200'
    };
    return map[v] || 'bg-gray-100 text-gray-800 border-gray-200';
};
</script>

<style>
@media print {
    body { background-color: white !important; }
    .print\:hidden { display: none !important; }
    .print\:p-0 { padding: 0 !important; }
    nav, header { display: none !important; }
}
</style>