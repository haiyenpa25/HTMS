<template>
   <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
      <AppCard class="p-0 overflow-hidden border border-gray-100">
         <div class="px-4 sm:px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-white">
            <div>
               <h3 class="text-lg font-black text-gray-900">Danh sách Tổ (Teams)</h3>
               <p class="text-xs text-gray-500 font-medium mt-1">Phân chia nhóm nhỏ trực thuộc ban</p>
            </div>
            <button @click="openCreateSlideOver" class="px-3 sm:px-4 py-2 bg-blue-50 text-blue-700 rounded-xl text-sm font-bold hover:bg-blue-100 transition-colors flex items-center shrink-0">
               <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
               Thêm Tổ
            </button>
         </div>

         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Tên Tổ</th>
                  <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider hidden sm:table-cell">Mã Tổ</th>
                  <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Trạng thái</th>
                  <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-black text-gray-500 uppercase tracking-wider">Thao tác</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <tr v-for="team in teams" :key="team.id" class="hover:bg-gray-50 transition-colors group">
                  <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                     <div class="text-sm font-black text-gray-900">{{ team.name }}</div>
                     <div class="text-xs text-gray-500 font-bold mt-1 sm:hidden">Mã: {{ team.code || '-' }}</div>
                  </td>
                  <td class="px-4 sm:px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                     <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-800">
                       {{ team.code || '-' }}
                     </span>
                  </td>
                  <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-center">
                     <span v-if="team.is_active" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-50 text-green-700" title="Đang hoạt động">Hoạt động</span>
                     <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700" title="Tạm ngưng">Tạm ngưng</span>
                  </td>
                  <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                     <button @click="openEditSlideOver(team)" class="text-blue-600 hover:text-blue-900 font-bold p-1 hover:bg-blue-50 rounded">Sửa</button>
                  </td>
                </tr>
                <tr v-if="teams.length === 0">
                   <td colspan="4" class="px-6 py-8 text-center text-gray-500 text-sm">
                      Chưa có tổ nào được tạo.
                   </td>
                </tr>
              </tbody>
            </table>
         </div>
      </AppCard>

      <SlideOver 
         v-model="isSlideOverOpen" 
         :title="selectedTeam ? 'Sửa Tổ' : 'Thêm Tổ mới'"
      >
         <form @submit.prevent="submit" class="flex flex-col h-full">
            <div class="flex-1 space-y-6">
               <div class="space-y-2">
                 <label class="text-xs font-bold text-gray-700 uppercase">Tên Tổ <span class="text-red-500">*</span></label>
                 <input v-model="form.name" required type="text" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                 <div v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</div>
               </div>
               
               <div class="space-y-2">
                 <label class="text-xs font-bold text-gray-700 uppercase">Mã Tổ</label>
                 <input v-model="form.code" type="text" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" />
               </div>

               <div class="space-y-2">
                 <label class="text-xs font-bold text-gray-700 uppercase">Mô tả</label>
                 <textarea v-model="form.description" rows="3" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
               </div>

               <label class="flex items-center space-x-2">
                  <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                  <span class="text-sm font-bold text-gray-700">Đang hoạt động</span>
               </label>
            </div>

            <div class="mt-8 flex justify-end space-x-3 pt-4 border-t border-gray-100">
               <button type="button" @click="isSlideOverOpen = false" class="px-4 py-2 border border-gray-300 rounded-lg font-bold text-sm text-gray-700 hover:bg-gray-50">Hủy</button>
               <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 rounded-lg font-bold text-sm text-white hover:bg-blue-700">Lưu thay đổi</button>
            </div>
            
            <div v-if="selectedTeam" class="mt-4 pt-4 border-t border-red-100 flex justify-center">
               <button type="button" @click="confirmDelete" class="text-xs font-bold text-red-600 hover:underline">Xóa Tổ này</button>
            </div>
         </form>
      </SlideOver>
   </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppCard from '@/Components/AppCard.vue';
import SlideOver from '@/Components/SlideOver.vue';

const props = defineProps({
   department: Object,
   teams: Array,
});

const emit = defineEmits(['refresh']);

const isSlideOverOpen = ref(false);
const selectedTeam = ref(null);

const form = useForm({
  name: '',
  code: '',
  description: '',
  is_active: true,
});

const openCreateSlideOver = () => {
   selectedTeam.value = null;
   form.reset();
   isSlideOverOpen.value = true;
};

const openEditSlideOver = (team) => {
   selectedTeam.value = team;
   form.name = team.name;
   form.code = team.code || '';
   form.description = team.description || '';
   form.is_active = team.is_active;
   isSlideOverOpen.value = true;
};

const submit = () => {
   if (selectedTeam.value) {
      form.put(route('departments.teams.update', { department: props.department.id, team: selectedTeam.value.id }), {
         onSuccess: () => {
            isSlideOverOpen.value = false;
            emit('refresh');
         }
      });
   } else {
      form.post(route('departments.teams.store', props.department.id), {
         onSuccess: () => {
            isSlideOverOpen.value = false;
            emit('refresh');
         }
      });
   }
};

const confirmDelete = () => {
   if(confirm('Chắc chắn muốn xóa tổ này?')) {
      router.delete(route('departments.teams.destroy', { department: props.department.id, team: selectedTeam.value.id }), {
         onSuccess: () => {
            isSlideOverOpen.value = false;
            emit('refresh');
         }
      })
   }
}
</script>