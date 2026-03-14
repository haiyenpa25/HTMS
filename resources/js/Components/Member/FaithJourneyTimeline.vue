<template>
  <div class="space-y-5">
    <!-- Header & Add Button -->
    <div class="flex items-center justify-between">
      <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        Hành trình Đức tin ({{ (member.faith_journeys || []).length }})
      </h2>
      <button v-if="isPastor" @click="openAddModal" class="px-3 py-1.5 text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg flex items-center transition-colors">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Thêm mốc kiện
      </button>
    </div>

    <!-- Empty State -->
    <div v-if="!member.faith_journeys || !member.faith_journeys.length"
      class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-14 text-center">
      <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      <p class="text-sm text-gray-400 font-medium">Chưa có mốc thời gian nào được ghi lại.</p>
    </div>

    <!-- Timeline Layout -->
    <div v-else class="relative pl-10 space-y-4 before:absolute before:left-3.5 before:top-2 before:bottom-0 before:w-0.5 before:bg-indigo-100">
      <div v-for="fj in member.faith_journeys" :key="fj.id" class="relative group">
        <!-- Dot -->
        <div class="absolute -left-[26px] top-3 w-4 h-4 rounded-full border-2 shadow-sm z-10 transition-transform group-hover:scale-125"
          :class="getIconColorClass(fj.event_type)"></div>

        <!-- Card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm transition-all hover:shadow-md p-5 group-hover:border-indigo-100 relative">
          
          <!-- Delete Button -->
          <div v-if="isPastor" class="absolute top-4 right-4 flex gap-1">
            <button @click="editItem(fj)" class="text-gray-400 hover:text-blue-500 transition-colors p-1 rounded-lg hover:bg-blue-50" title="Chỉnh sửa">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
            <button @click="deleteItem(fj)" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-lg hover:bg-red-50" title="Xóa">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>

          <div class="flex items-start justify-between mb-2">
            <div>
              <div class="font-black text-gray-900 text-sm flex items-center gap-2">
                {{ formatDate(fj.event_date) }}
                <span class="text-[10px] uppercase font-bold tracking-widest px-2 py-0.5 rounded-md" :class="getBadgeClass(fj.event_type)">
                  {{ getEventLabel(fj.event_type) }}
                </span>
              </div>
            </div>
          </div>
          
          <div v-if="fj.description" class="text-sm text-gray-600 leading-relaxed mb-3">
            {{ fj.description }}
          </div>
          
          <div v-if="fj.related_person_or_church" class="flex items-center gap-1.5 pt-3 border-t border-gray-50 text-xs text-gray-500 font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
            Cá nhân/Tổ chức liên quan: <span class="text-gray-900 font-bold ml-1">{{ fj.related_person_or_church }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-bold text-gray-900 text-lg">{{ editingJourney ? 'Cập nhật mốc sự kiện' : 'Thêm mốc sự kiện' }}</h3>
          <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <form @submit.prevent="submit" class="p-6 overflow-y-auto w-full">
          <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Loại sự kiện <span class="text-red-500">*</span></label>
                <select v-model="form.event_type" required class="w-full text-sm border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="tin_chua">Tin Chúa</option>
                  <option value="bap_tem">Báp-têm</option>
                  <option value="bat_tay">Bắt tay chức viên mới</option>
                  <option value="nhan_chuc">Nhận chức</option>
                  <option value="thuyen_chuyen">Thuyên chuyển</option>
                  <option value="qua_doi">Qua đời / Về nước Chúa</option>
                  <option value="ky_luat">Kỷ luật</option>
                  <option value="khac">Khác</option>
                </select>
                <div v-if="form.errors.event_type" class="text-red-500 text-xs mt-1">{{ form.errors.event_type }}</div>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Ngày diễn ra <span class="text-red-500">*</span></label>
                <input type="date" v-model="form.event_date" required class="w-full text-sm border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-indigo-500">
                <div v-if="form.errors.event_date" class="text-red-500 text-xs mt-1">{{ form.errors.event_date }}</div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1.5">Người thi hành / Nơi tổ chức (Tùy chọn)</label>
              <input type="text" v-model="form.related_person_or_church" placeholder="VD: MS quản nhiệm Nguyễn Văn A, HTTL ABC..." class="w-full text-sm border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1.5">Chi tiết nội dung</label>
              <textarea v-model="form.description" rows="3" placeholder="Mô tả sự kiện (tùy chọn)..." class="w-full text-sm border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>
          </div>

          <div class="mt-6 pt-5 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" @click="showAddModal = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
              Hủy
            </button>
            <button type="submit" :disabled="form.processing" class="px-5 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-200 transition-all flex items-center">
              <span v-if="form.processing">Đang lưu...</span>
              <span v-else>Lưu sự kiện</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
  member: Object,
  isPastor: Boolean
});

const showAddModal = ref(false);
const editingJourney = ref(null);

const form = useForm({
  member_id: props.member.id,
  event_type: 'tin_chua',
  event_date: new Date().toISOString().split('T')[0],
  description: '',
  related_person_or_church: ''
});

const openAddModal = () => {
  editingJourney.value = null;
  form.reset();
  form.clearErrors();
  form.member_id = props.member.id;
  form.event_type = 'tin_chua';
  form.event_date = new Date().toISOString().split('T')[0];
  showAddModal.value = true;
};

const editItem = (journey) => {
  editingJourney.value = journey;
  form.clearErrors();
  form.member_id = props.member.id;
  form.event_type = journey.event_type;
  form.event_date = journey.event_date ? journey.event_date.split('T')[0] : '';
  form.description = journey.description || '';
  form.related_person_or_church = journey.related_person_or_church || '';
  showAddModal.value = true;
};

const submit = () => {
  if (editingJourney.value) {
    form.put(route('faith-journeys.update', editingJourney.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        showAddModal.value = false;
        form.reset();
      }
    });
  } else {
    form.post(route('faith-journeys.store'), {
      preserveScroll: true,
      onSuccess: () => {
        showAddModal.value = false;
        form.reset();
      }
    });
  }
};

const deleteItem = (journey) => {
  if (confirm('Bạn có chắc chắn muốn xóa mốc sự kiện này?')) {
    router.delete(route('faith-journeys.destroy', journey.id), {
      preserveScroll: true
    });
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '—';
  const dateStr = String(dateString).split('T')[0];
  const parts = dateStr.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return dateString;
};

const getEventLabel = (type) => {
  const map = {
    'tin_chua': 'Tin Chúa',
    'bap_tem': 'Báp-têm',
    'bat_tay': 'Gia nhập HT',
    'nhan_chuc': 'Nhận chức',
    'thuyen_chuyen': 'Thuyên chuyển',
    'qua_doi': 'Về nước Chúa',
    'ky_luat': 'Kỷ luật',
    'khac': 'Khác'
  };
  return map[type] || type;
};

const getIconColorClass = (type) => {
  switch (type) {
    case 'tin_chua': return 'bg-yellow-400 border-yellow-500';
    case 'bap_tem': return 'bg-cyan-400 border-cyan-500';
    case 'bat_tay': return 'bg-blue-400 border-blue-500';
    case 'nhan_chuc': return 'bg-purple-400 border-purple-500';
    case 'ky_luat': return 'bg-red-400 border-red-500';
    case 'qua_doi': return 'bg-gray-700 border-gray-900';
    default: return 'bg-gray-300 border-gray-400';
  }
};

const getBadgeClass = (type) => {
  switch (type) {
    case 'tin_chua': return 'bg-yellow-100 text-yellow-800';
    case 'bap_tem': return 'bg-cyan-100 text-cyan-800';
    case 'bat_tay': return 'bg-blue-100 text-blue-800';
    case 'nhan_chuc': return 'bg-purple-100 text-purple-800';
    case 'ky_luat': return 'bg-red-100 text-red-800';
    case 'qua_doi': return 'bg-gray-100 text-gray-800';
    default: return 'bg-gray-100 text-gray-600';
  }
};
</script>
