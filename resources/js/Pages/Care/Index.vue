<template>
  <component :is="currentLayout">
    <template #header>Chăm Sóc & Yêu Cầu Mục Vụ</template>

    <div class="py-4 space-y-4 w-full">

      <!-- Hero Banner -->
      <div class="rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 p-6 sm:p-8 text-white relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 opacity-10 pointer-events-none flex items-center justify-end pr-8">
          <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </div>
        <div class="relative z-10">
          <p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-200 mb-1">MỤC VỤ × CHĂM SÓC</p>
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Trung Tâm Chăm Sóc</h1>
          <p class="mt-2 text-sm text-rose-200">Nơi gửi gắm lời cầu thay, yêu cầu thăm viếng và góp ý xây dựng nội bộ.</p>
        </div>
        <div class="absolute top-5 right-5 sm:top-6 sm:right-6 z-10">
          <button @click="openModal" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-sm font-bold rounded-xl transition-all backdrop-blur-sm border border-white/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Gửi Yêu Cầu
          </button>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
          <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Tổng yêu cầu</p>
          <p class="text-2xl font-black text-gray-900">{{ requests.total }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-sm">
          <p class="text-xs font-bold text-amber-400 uppercase tracking-widest mb-1">Đang chờ</p>
          <p class="text-2xl font-black text-amber-700">{{ requests.data.filter(r => r.status === 'pending').length }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-sm">
          <p class="text-xs font-bold text-blue-400 uppercase tracking-widest mb-1">Đang xử lý</p>
          <p class="text-2xl font-black text-blue-700">{{ requests.data.filter(r => r.status === 'in_progress').length }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm">
          <p class="text-xs font-bold text-emerald-400 uppercase tracking-widest mb-1">Đã giải quyết</p>
          <p class="text-2xl font-black text-emerald-700">{{ requests.data.filter(r => r.status === 'resolved').length }}</p>
        </div>
      </div>


      <!-- Filters -->
      <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-wrap gap-4 items-center justify-between">
        <div class="flex gap-2 w-full md:w-auto overflow-x-auto pb-2 md:pb-0 scrollbar-hide">
          <button v-for="cat in categories" :key="cat.value"
            @click="activeCategory = cat.value"
            class="px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors border"
            :class="activeCategory === cat.value 
              ? 'bg-red-50 text-red-700 border-red-200 shadow-sm' 
              : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
            {{ cat.label }}
          </button>
        </div>

        <div v-if="isPastor" class="flex gap-2">
           <select v-model="activeStatus" class="border-gray-300 text-sm focus:border-red-500 focus:ring-red-500 rounded-lg shadow-sm py-1.5 pl-3 pr-8">
              <option value="all">Tất cả Trạng thái</option>
              <option value="pending">Mới gửi (Chờ xử lý)</option>
              <option value="in_progress">Đang theo dõi</option>
              <option value="resolved">Đã giải quyết</option>
              <option value="closed">Đã đóng</option>
           </select>
        </div>
      </div>

      <!-- Danh Sách Request (Board hoặc List) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="req in requests.data" :key="req.id" class="bg-white rounded-xl shadow-sm border p-4 flex flex-col h-full" :class="{'border-red-200 ring-1 ring-red-50': req.priority === 'urgent', 'border-gray-200': req.priority !== 'urgent'}">
          
          <div class="flex justify-between items-start mb-2">
            <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide" :class="getCategoryColor(req.category)">
              {{ getCategoryLabel(req.category) }}
            </span>
            <div class="flex items-center gap-2">
               <span v-if="req.is_private" class="flex items-center text-[10px] bg-gray-800 text-white font-bold px-2 py-0.5 rounded cursor-help" title="Thông tin này được bảo mật, chỉ Mục sư mới có thể xem">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg> MẬT
               </span>
               <!-- Dropdown Quản lý (Cho Pastor hoặc Chủ stt) -->
               <Dropdown align="right" width="48" v-if="isPastor || req.user_id === $page.props.auth.user.id">
                  <template #trigger>
                    <button class="text-gray-400 hover:text-gray-600 focus:outline-none">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                    </button>
                  </template>
                  <template #content>
                    <button v-if="isPastor" @click="openStatusModal(req)" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 font-medium">Cập nhật Trạng thái</button>
                    <!-- <button v-if="isPastor" @click="openAssignModal(req)" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 font-medium">Phân công xử lý</button> -->
                    <button @click="deleteRequest(req.id)" class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium border-t border-gray-100">Xóa Yêu cầu</button>
                  </template>
                </Dropdown>
            </div>
          </div>

          <h3 class="font-bold text-gray-900 leading-snug mb-2">{{ req.title }}</h3>
          
          <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700 mb-4 whitespace-pre-wrap italic border-l-2 border-gray-200">"{{ req.content }}"</div>

          <div class="mt-auto pt-3 border-t border-gray-100 flex flex-col gap-2">
             <div class="flex justify-between items-center text-[10px]">
                <div class="flex items-center gap-1.5 font-medium text-gray-600">
                    <div class="w-5 h-5 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600">{{ req.user?.name.substring(0,2).toUpperCase() }}</div>
                    <span>{{ req.user?.name || 'Vô danh' }}</span>
                </div>
                <span class="text-gray-400">{{ new Date(req.created_at).toLocaleDateString('vi-VN') }}</span>
             </div>
             
             <div class="flex justify-between items-center text-[10px]">
                <span class="font-bold px-2 py-0.5 rounded-full flex items-center gap-1" :class="getStatusColor(req.status)">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ getStatusLabel(req.status) }}
                </span>
                
                <span class="font-medium text-red-600 uppercase tracking-widest" v-if="req.priority === 'urgent'">Khẩn cấp</span>
             </div>
          </div>
        </div>
      </div>

      <div v-if="requests.data.length === 0" class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        <h3 class="mt-2 text-sm font-bold text-gray-900">Chưa có yêu cầu nào</h3>
        <p class="mt-1 text-xs text-gray-500">Khu vực này hiện đang trống. Cảm ơn Chúa vì mọi sự bình an!</p>
      </div>

      <Pagination :links="requests.links" />
    </div>

    <!-- M O D A L : Thêm Yêu cầu -->
    <Modal :show="isModalOpen" @close="closeModal" maxWidth="md">
      <div class="p-6">
        <h2 class="text-lg font-black text-gray-900 mb-4">Gửi Yêu cầu & Góp ý</h2>
        <form @submit.prevent="submitForm">
          <div class="space-y-4">
            <div>
              <InputLabel for="category" value="Bạn đang cần gì? *" />
              <select id="category" v-model="form.category" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-sm" required>
                <option value="prayer">Xin Ban chăm sóc cầu nguyện thay</option>
                <option value="counseling">Đăng ký tư vấn Mục vụ (Kín)</option>
                <option value="feedback">Góp ý xây dựng Hội Thánh/Hệ thống</option>
                <option value="support">Cần hỗ trợ vấn đề khác</option>
              </select>
            </div>

            <div v-if="form.category === 'counseling'" class="p-3 bg-gray-900 text-gray-300 text-xs rounded-lg flex gap-2">
                <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                Yêu cầu tư vấn Tâm linh/Mục vụ sẽ bị ĐÓNG MẬT. Chỉ có Quản trị viên cấp [Mục Sự] mới có quyền đọc nội dung này để bảo vệ sự riêng tư.
            </div>

            <div>
              <InputLabel for="title" value="Tiêu đề tóm tắt *" />
              <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full text-sm" required placeholder="VD: Xin cầu nguyện cho sức khoẻ gia đình..." />
            </div>

            <div>
              <InputLabel for="content" value="Nội dung chi tiết *" />
              <textarea id="content" v-model="form.content" rows="4" class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm text-sm" required placeholder="Trình bày chi tiết vấn đề của bạn..."></textarea>
            </div>

            <div v-show="form.category !== 'counseling'">
               <InputLabel for="priority" value="Không bắt buộc" />
               <div class="mt-2 flex items-center pt-2">
                 <input type="checkbox" id="private" v-model="form.is_private" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200">
                 <label for="private" class="ml-2 block text-sm text-gray-700 font-medium">Đặt làm Thư Mật (Chỉ gửi Riêng cho Mục sư)</label>
               </div>
               
               <div class="mt-2 flex items-center pt-2">
                 <input type="checkbox" id="urgent" v-model="form.is_urgent" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200">
                 <label for="urgent" class="ml-2 block text-sm text-red-600 font-bold">Việc rất Khẩn cấp</label>
               </div>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <SecondaryButton @click="closeModal">Hủy</SecondaryButton>
            <PrimaryButton class="bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-900" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
              Gửi Đi
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- M O D A L : Update Status (Dành cho Admin) -->
    <Modal :show="isStatusModalOpen" @close="closeStatusModal" maxWidth="sm">
        <div class="p-6">
            <h2 class="text-lg font-black text-gray-900 mb-4">Cập nhật Trạng thái Xử lý</h2>
            <form @submit.prevent="submitStatus">
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Tình trạng *" />
                        <select v-model="statusForm.status" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" required>
                            <option value="pending">Chờ xử lý / Đang tiếp nhận</option>
                            <option value="in_progress">Đang theo dõi / Chăm sóc</option>
                            <option value="resolved">Đã giải quyết tốt đẹp</option>
                            <option value="closed">Đóng lại</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeStatusModal">Hủy</SecondaryButton>
                    <PrimaryButton :disabled="statusForm.processing">Cập nhật</PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
  </component>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import Pagination from '@/Components/Pagination.vue';
import Dropdown from '@/Components/Dropdown.vue';
import debounce from 'lodash/debounce';

const currentLayout = computed(() => {
    if (typeof window === 'undefined') return AuthenticatedLayout;
    return window.innerWidth < 768 ? MobileLayout : AuthenticatedLayout;
});

const props = defineProps({
    requests: Object,
    filters: Object,
    isPastor: Boolean
});

const categories = [
    { value: 'all', label: 'Tất cả' },
    { value: 'prayer', label: '🙏 Xin cầu nguyện' },
    { value: 'counseling', label: '☕ Tư vấn Mục vụ' },
    { value: 'feedback', label: '💡 Góp ý xây dựng' },
    { value: 'support', label: '🛠 Hỗ trợ khác' }
];

const activeCategory = ref(props.filters.category || 'all');
const activeStatus = ref(props.filters.status || 'all');

watch([activeCategory, activeStatus], debounce(([newCat, newStatus]) => {
    router.get(route('care.index'), {
        category: newCat,
        status: newStatus
    }, { preserveState: true, preserveScroll: true, replace: true });
}, 300));

// Create Modal
const isModalOpen = ref(false);
const form = useForm({
    title: '',
    content: '',
    category: 'prayer',
    is_private: false,
    is_urgent: false,
    priority: 'normal'
});

const openModal = () => {
    form.reset();
    isModalOpen.value = true;
};
const closeModal = () => isModalOpen.value = false;

const submitForm = () => {
    form.priority = form.is_urgent ? 'urgent' : 'normal';
    form.post(route('care.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const deleteRequest = (id) => {
    if (confirm('Huỷ bỏ yêu cầu này?')) {
        router.delete(route('care.destroy', id), { preserveScroll: true });
    }
};

// Status Modal
const isStatusModalOpen = ref(false);
const selectedReqId = ref(null);
const statusForm = useForm({ status: '' });

const openStatusModal = (req) => {
    selectedReqId.value = req.id;
    statusForm.status = req.status;
    isStatusModalOpen.value = true;
};
const closeStatusModal = () => isStatusModalOpen.value = false;

const submitStatus = () => {
    statusForm.patch(route('care.status.update', selectedReqId.value), {
        preserveScroll: true,
        onSuccess: () => closeStatusModal()
    });
};

// UI Helpers
const getCategoryLabel = (cat) => categories.find(c => c.value === cat)?.label.replace(/[^\p{L}\p{N}\s_]/gu, '').trim() || cat;

const getCategoryColor = (cat) => {
    const map = {
        prayer: 'bg-indigo-100 text-indigo-700',
        counseling: 'bg-amber-100 text-amber-700',
        feedback: 'bg-emerald-100 text-emerald-700',
        support: 'bg-gray-200 text-gray-700'
    };
    return map[cat] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    const map = {
        pending: 'Đang chờ',
        in_progress: 'Đang theo dõi',
        resolved: 'Đã giải quyết',
        closed: 'Đã đóng'
    };
    return map[status] || status;
};

const getStatusColor = (status) => {
     const map = {
        pending: 'bg-gray-100 text-gray-500',
        in_progress: 'bg-blue-100 text-blue-600',
        resolved: 'bg-emerald-100 text-emerald-600',
        closed: 'bg-slate-200 text-slate-500'
    };
    return map[status] || 'bg-gray-100';
}
</script>