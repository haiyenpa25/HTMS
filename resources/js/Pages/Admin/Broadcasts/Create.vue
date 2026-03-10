<template>
  <AuthenticatedLayout>
    <template #header>Soạn Thảo Email Thông Báo</template>

    <div class="py-4 space-y-6 max-w-4xl mx-auto">
        <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
           
           <div class="p-6 md:p-8 space-y-6 border-b border-gray-100">
               <div>
                  <InputLabel value="Chủ Đề Tiêu Đề (Subject) *" />
                  <TextInput v-model="form.subject" type="text" class="mt-1 block w-full font-bold text-lg" required placeholder="Ví dụ: Thư mời Lễ Kỷ Niệm 10 Năm Thành Lập..." />
                  <div v-if="form.errors.subject" class="text-sm text-red-600 mt-1">{{ form.errors.subject }}</div>
               </div>

               <div>
                  <div class="flex items-center justify-between mb-1">
                     <InputLabel value="Nội Dung Gửi (Trình Soạn Thảo Markdown) *" />
                     <span class="text-[10px] text-gray-400 font-mono">Hỗ trợ các thẻ Markdown cơ bản</span>
                  </div>
                  <textarea v-model="form.content" rows="12" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-mono text-sm leading-relaxed" required placeholder="# Tiêu đề lớn&#10;&#10;Xin chào anh chị em,&#10;&#10;Trân trọng kính mời..."></textarea>
                  <div v-if="form.errors.content" class="text-sm text-red-600 mt-1">{{ form.errors.content }}</div>
               </div>

               <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 shadow-sm">
                  <h4 class="font-black text-sm text-gray-800 mb-4 flex items-center">
                     <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                     Lọc Đối Tượng Nhận Email (Bỏ trống để gửi cho TẤT CẢ)
                  </h4>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <!-- Lọc Theo Role -->
                      <div>
                          <InputLabel value="Theo Vai Trò (Roles)" class="mb-2" />
                          <div class="max-h-40 overflow-y-auto bg-white border border-gray-200 rounded-lg p-2 space-y-1">
                              <label v-for="role in roles" :key="role.id" class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                  <input type="checkbox" :value="role.id" v-model="form.target_roles" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 text-xs">
                                  <span class="ml-2 text-xs font-bold text-gray-700">{{ role.name }}</span>
                              </label>
                          </div>
                      </div>

                      <!-- Lọc Theo Ban Ngành -->
                      <div>
                          <InputLabel value="Theo Ban Ngành (Departments)" class="mb-2" />
                          <div class="max-h-40 overflow-y-auto bg-white border border-gray-200 rounded-lg p-2 space-y-1">
                              <label v-for="dept in departments" :key="dept.id" class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                  <input type="checkbox" :value="dept.id" v-model="form.target_departments" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 text-xs">
                                  <span class="ml-2 text-xs font-bold text-gray-700">{{ dept.name }}</span>
                              </label>
                          </div>
                      </div>
                  </div>
                  <p class="text-[11px] text-gray-500 mt-3 italic">Hệ thống sẽ chỉ gửi tới những Tín hữu (thỏa mãn Cả Vai trò HOẶC Cả Ban ngành) có cung cấp Email hợp lệ. Nếu bỏ checkbox mọi dòng tức là gửi Đại trà Tổng thể.</p>
               </div>
           </div>

           <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
              <SecondaryButton type="button" @click="router.get(route('admin.broadcasts.index'))">
                 Huỷ Bỏ
              </SecondaryButton>
              <div class="space-x-3 flex">
                 <button type="button" @click="saveDraft" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors text-sm" :disabled="form.processing">
                    Lưu Bản Nháp
                 </button>
                 <PrimaryButton type="submit" class="bg-indigo-600 hover:bg-indigo-700 py-2.5 px-6 font-bold" :disabled="form.processing">
                    Lấy Danh Sách & Gửi Ngay
                 </PrimaryButton>
              </div>
           </div>
        </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps(['roles', 'departments']);

const form = useForm({
    subject: '',
    content: '',
    target_roles: [],
    target_departments: [],
    action: 'send'
});

const saveDraft = () => {
    form.action = 'save';
    form.post(route('admin.broadcasts.store'));
};

const submit = () => {
    if(confirm('Bạn có chắc chắn muốn phát hành Thư này ngay? Hệ thống sẽ quét Data và đưa vào Hàng đợi máy chủ. Lưu ý: Thao tác này KHÔNG THỂ THU HỒI.')) {
        form.action = 'send';
        form.post(route('admin.broadcasts.store'));
    }
};
</script>