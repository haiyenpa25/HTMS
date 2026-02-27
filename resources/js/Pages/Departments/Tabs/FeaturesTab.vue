<template>
   <div class="animate-in fade-in slide-in-from-bottom-4 duration-500 max-w-4xl">
      <AppCard class="p-8 border border-gray-100 shadow-sm relative overflow-hidden">
         <!-- Decorative background piece -->
         <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-bl-[100px] -z-10 opacity-50"></div>

         <div class="flex items-center mb-10">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mr-5 shadow-inner">
               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <div>
               <h3 class="text-2xl font-black text-gray-900 tracking-tight">Cấu hình Hệ thống (Feature Flags)</h3>
               <p class="text-sm text-gray-500 font-medium mt-1">Bật tắt các module và tính năng được phép hoạt động trên ban này.</p>
            </div>
         </div>

         <form @submit.prevent="submit" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div v-for="feature in availableFeatures" :key="feature.id" 
                    :class="[
                       form.feature_keys.includes(feature.id) ? 'bg-blue-50/50 border-blue-200' : 'bg-gray-50/50 border-gray-200 hover:border-gray-300',
                       'p-5 rounded-2xl border transition-all cursor-pointer relative overflow-hidden group'
                    ]"
                    @click="toggleFeature(feature.id)"
               >
                  <div class="flex items-start justify-between">
                     <div class="flex-1 pr-4">
                        <label class="text-sm font-black text-gray-900 cursor-pointer group-hover:text-blue-700 transition-colors">{{ feature.name }}</label>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed font-medium">{{ feature.description }}</p>
                     </div>
                     <div class="relative shrink-0 ml-4 mt-1">
                        <input type="checkbox" :value="feature.id" v-model="form.feature_keys" class="sr-only peer" @click.stop>
                        <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600 transition-colors flex items-center shadow-inner"></div>
                        <div :class="[
                           form.feature_keys.includes(feature.id) ? 'translate-x-full border-blue-600' : 'translate-x-0 border-gray-300',
                           'absolute top-[2px] left-[2px] bg-white border rounded-full h-5 w-5 transition-transform shadow-sm'
                        ]"></div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="pt-8 border-t border-gray-100 flex items-center justify-between">
               <p class="text-xs text-red-500 font-bold max-w-lg">
                  Lưu ý: Chức năng này sẽ quét quyền ở tầng Backend và ẩn hiện các menu ở tầng Frontend tương ứng.
               </p>
               <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-gray-900 text-white rounded-xl font-black text-sm hover:bg-black shadow-lg shadow-gray-200 transition-all flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                  Lưu Cấu hình
               </button>
            </div>
         </form>
      </AppCard>
   </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AppCard from '@/Components/AppCard.vue';

const props = defineProps({
   department: Object,
});

const emit = defineEmits(['refresh']);

const availableFeatures = [
  { id: 'attendance', name: 'Điểm danh (Attendance)', description: 'Cho phép tổ chức buổi nhóm riêng và điểm danh thành viên hoặc thân hữu.' },
  { id: 'visits', name: 'Thăm viếng (Care & Visit)', description: 'Quản lý lịch trình chăm sóc, phân công nhân sự thăm viếng tín hữu.' },
  { id: 'reports', name: 'Báo cáo / Thống kê', description: 'Cung cấp biểu đồ và danh sách dữ liệu riêng của ban ngành này.' },
  { id: 'meetings', name: 'Buổi học / Buổi nhóm', description: 'Tích hợp module bài học và chi tiết tiết học trong Ban ngành.' },
  { id: 'notifications', name: 'Thông báo Nội bộ', description: 'Gửi tin nhắn hoặc thông báo đẩy cho các thành viên trong ban.' },
];

const form = useForm({
  feature_keys: props.department.feature_keys || [],
});

const toggleFeature = (id) => {
   const index = form.feature_keys.indexOf(id);
   if (index === -1) {
      form.feature_keys.push(id);
   } else {
      form.feature_keys.splice(index, 1);
   }
};

const submit = () => {
   form.put(route('departments.features.update', props.department.id), {
      preserveScroll: true,
      onSuccess: () => {
         emit('refresh');
         alert('Đã cập nhật tính năng thành công!');
      }
   });
};
</script>
