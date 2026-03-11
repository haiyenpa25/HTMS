<template>
  <component :is="currentLayout">
    <template #header>Thư Viện Tài Liệu</template>

    <div class="py-4 space-y-4 w-full">

      <!-- Hero Banner -->
      <div class="rounded-2xl bg-gradient-to-br from-blue-600 to-sky-700 p-6 sm:p-8 text-white relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 opacity-10 pointer-events-none flex items-center justify-end pr-8">
          <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div class="relative z-10">
          <p class="text-xs font-bold uppercase tracking-[0.2em] text-sky-200 mb-1">HỆ THỐNG × TÀI LIỆU</p>
          <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Kho Tài Liệu Số</h1>
          <p class="mt-2 text-sm text-sky-200">Lưu trữ và chia sẻ quy định, thư tín, biểu mẫu, biên bản họp nội bộ.</p>
        </div>
        <div class="absolute top-5 right-5 sm:top-6 sm:right-6 z-10" v-if="$page.props.auth.user.roles.includes('Super_Admin') || $page.props.auth.user.roles.includes('Pastor') || $page.props.auth.user.roles.includes('Department_Leader')">
          <button @click="openUploadModal" class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-sm font-bold rounded-xl transition-all backdrop-blur-sm border border-white/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Tải Tài Liệu Lên
          </button>
        </div>
      </div>


      <!-- Bộ lọc và Tìm kiếm -->
      <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="w-full md:w-1/3">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input 
              v-model="search" 
              type="text" 
              placeholder="Tìm tên tài liệu, mô tả..." 
              class="w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-shadow"
            />
          </div>
        </div>

        <div class="flex gap-2 overflow-x-auto w-full md:w-auto pb-2 md:pb-0 scrollbar-hide">
          <button v-for="cat in categories" :key="cat.value"
            @click="activeCategory = cat.value"
            class="px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors border"
            :class="activeCategory === cat.value 
              ? 'bg-blue-50 text-blue-700 border-blue-200 shadow-sm' 
              : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
            {{ cat.label }}
          </button>
        </div>
      </div>

      <!-- Danh sách File -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="doc in documents.data" :key="doc.id" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow group flex flex-col h-full">
          <div class="flex justify-between items-start mb-3">
            <div class="flex items-center gap-3 w-4/5">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" :class="getFileColor(doc.file_type)">
                <svg v-if="['pdf'].includes(doc.file_type)" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <svg v-else-if="['doc','docx'].includes(doc.file_type)" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <svg v-else-if="['xls','xlsx'].includes(doc.file_type)" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
              </div>
              <div>
                <h3 class="font-bold text-sm text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-1 truncate" :title="doc.title">{{ doc.title }}</h3>
                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider mt-0.5">{{ doc.file_size }} · {{ doc.file_type }}</p>
              </div>
            </div>
            
            <Dropdown align="right" width="48">
              <template #trigger>
                <button class="text-gray-400 hover:text-gray-600 focus:outline-none bg-gray-50 hover:bg-gray-100 rounded p-1">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                </button>
              </template>
              <template #content>
                <DropdownLink :href="doc.download_url" as="a" class="flex items-center text-blue-600 font-medium">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg> Tải xuống
                </DropdownLink>
                <button v-if="doc.can_delete" @click="confirmDelete(doc.id)" class="w-full text-left block px-4 py-2 text-sm leading-5 text-red-600 hover:bg-red-50 focus:outline-none transition duration-150 ease-in-out font-medium flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Xóa tệp
                </button>
              </template>
            </Dropdown>
          </div>

          <p class="text-xs text-gray-600 mb-4 line-clamp-2 mt-2 flex-grow">{{ doc.description || 'Không có mô tả chi tiết.' }}</p>

          <div class="mt-auto pt-3 border-t border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
              <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-[10px] font-bold">
                {{ doc.uploader.substring(0,2).toUpperCase() }}
              </div>
              <span class="text-[10px] text-gray-500 font-medium">{{ doc.created_at }}</span>
            </div>
            
            <div class="flex gap-1">
              <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider" :class="getVisibilityColor(doc.visibility)">
                {{ getVisibilityLabel(doc.visibility) }}
              </span>
              <span class="text-[9px] font-bold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded uppercase tracking-wider" v-if="doc.department">
                {{ doc.department }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Trống -->
      <div v-if="documents.data.length === 0" class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        <h3 class="mt-2 text-sm font-bold text-gray-900">Không tìm thấy tài liệu</h3>
        <p class="mt-1 text-xs text-gray-500">Chưa có file nào khớp với bộ lọc hoặc bạn chưa tải lên phân loại này.</p>
      </div>

      <!-- Phân trang -->
      <Pagination :links="documents.links" class="mt-4" />
    </div>

    <!-- S L I D E  O V E R : Upload Tài liệu -->
    <SlideOver v-model="isUploadModalOpen" title="Tải lên Tài liệu Mới" size="md">
      <form id="uploadForm" @submit.prevent="submitUpload">
        <div class="space-y-4">
            <div>
              <InputLabel for="title" value="Tên tài liệu *" />
              <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full text-sm" required placeholder="VD: Nội quy sinh hoạt thanh niên..." />
              <InputError :message="form.errors.title" class="mt-2" />
            </div>

            <div>
              <InputLabel for="description" value="Mô tả tóm tắt" />
              <textarea id="description" v-model="form.description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" placeholder="(Tuỳ chọn) Ghi chú thêm về file này..."></textarea>
              <InputError :message="form.errors.description" class="mt-2" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <InputLabel for="category" value="Phân loại *" />
                <select id="category" v-model="form.category" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" required>
                  <option v-for="cat in Object.values(categories).filter(c=>c.value!=='all')" :key="cat.value" :value="cat.value">{{ cat.label }}</option>
                </select>
                <InputError :message="form.errors.category" class="mt-2" />
              </div>
              <div>
                <InputLabel for="visibility" value="Quyền Truy Cập *" />
                <select id="visibility" v-model="form.visibility" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" required>
                  <option value="public">Công khai (Mọi người)</option>
                  <option value="internal">Nội bộ Tín Hữu</option>
                  <option value="leadership">Chỉ Ban Chấp Sự/Mục Sư</option>
                  <option value="private">Riêng tư (Chỉ mình tôi)</option>
                </select>
                <InputError :message="form.errors.visibility" class="mt-2" />
              </div>
            </div>

            <div>
              <InputLabel for="file" value="Chọn File (Bắt buộc, Tối đa 20MB) *" />
              <input type="file" id="file" @change="e => form.file = e.target.files[0]" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip" />
              <InputError :message="form.errors.file" class="mt-2" />
            </div>
          </div>
      </form>
      <template #footer>
        <div class="flex justify-end gap-3 w-full">
          <SecondaryButton type="button" @click="closeUploadModal">Hủy</SecondaryButton>
          <PrimaryButton form="uploadForm" type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
            Tải lên Hệ Thống
          </PrimaryButton>
        </div>
      </template>
    </SlideOver>

  </component>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Pagination from '@/Components/Pagination.vue';
import SlideOver from '@/Components/SlideOver.vue';
import debounce from 'lodash/debounce';

const currentLayout = computed(() => {
    if (typeof window === 'undefined') return AuthenticatedLayout;
    return window.innerWidth < 768 ? MobileLayout : AuthenticatedLayout;
});

const props = defineProps({
    documents: Object,
    filters: Object,
});

const categories = [
    { value: 'all', label: 'Tất cả Tài liệu' },
    { value: 'policy', label: 'Nội quy & Chính sách' },
    { value: 'meeting_minute', label: 'Biên bản & Nghị quyết' },
    { value: 'manual', label: 'Hướng dẫn sử dụng' },
    { value: 'form', label: 'Biểu mẫu & Tờ khai' },
    { value: 'general', label: 'Khác' },
];

const search = ref(props.filters.search || '');
const activeCategory = ref(props.filters.category || 'all');

watch([search, activeCategory], debounce(([newSearch, newCat]) => {
    router.get(route('documents.index'), {
        search: newSearch,
        category: newCat
    }, { preserveState: true, preserveScroll: true, replace: true });
}, 300));

// UPLOAD LOGIC
const isUploadModalOpen = ref(false);
const form = useForm({
    title: '',
    description: '',
    category: 'general',
    visibility: 'public',
    file: null,
});

const openUploadModal = () => {
    form.reset();
    isUploadModalOpen.value = true;
};
const closeUploadModal = () => { isUploadModalOpen.value = false; };

const submitUpload = () => {
    form.post(route('documents.store'), {
        preserveScroll: true,
        onSuccess: () => closeUploadModal(),
    });
};

// DELETE LOGIC
const confirmDelete = (id) => {
    if (confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tài liệu này khỏi Thư viện không?')) {
        router.delete(route('documents.destroy', id), {
            preserveScroll: true,
        });
    }
};

// UI HELPERS
const getFileColor = (ext) => {
    if (!ext) return 'bg-gray-400';
    ext = ext.toLowerCase();
    if (['pdf'].includes(ext)) return 'bg-red-500';
    if (['doc', 'docx'].includes(ext)) return 'bg-blue-600';
    if (['xls', 'xlsx', 'csv'].includes(ext)) return 'bg-emerald-600';
    if (['ppt', 'pptx'].includes(ext)) return 'bg-orange-500';
    if (['jpg', 'jpeg', 'png'].includes(ext)) return 'bg-purple-500';
    if (['zip', 'rar'].includes(ext)) return 'bg-amber-600';
    return 'bg-gray-500';
};

const getVisibilityLabel = (vis) => {
    const map = {
        public: 'Công khai',
        internal: 'Nội bộ',
        leadership: 'BT Sự',
        private: 'Riêng tư'
    };
    return map[vis] || vis;
};

const getVisibilityColor = (vis) => {
    const map = {
        public: 'bg-green-100 text-green-700',
        internal: 'bg-blue-100 text-blue-700',
        leadership: 'bg-purple-100 text-purple-700',
        private: 'bg-gray-200 text-gray-700'
    };
    return map[vis] || 'bg-gray-100 text-gray-600';
};
</script>