<template>
  <form @submit.prevent="submit" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <!-- Thông tin cơ bản -->
      <div class="space-y-6 md:col-span-2 bg-gray-50 p-5 rounded-2xl border border-gray-100 placeholder:text-gray-400">
         <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Hồ sơ Cầm tay</h3>
         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="full_name" class="block text-sm font-bold text-gray-700">Họ và Tên <span class="text-red-500">*</span></label>
              <input type="text" id="full_name" v-model="form.full_name" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-medium" placeholder="VD: Nguyễn Văn A" required />
              <div v-if="form.errors.full_name" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.full_name }}</div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
               <div>
                 <label for="gender" class="block text-sm font-bold text-gray-700">Giới tính</label>
                 <select id="gender" v-model="form.gender" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-medium">
                   <option value="Nam">Nam</option>
                   <option value="Nữ">Nữ</option>
                 </select>
                 <div v-if="form.errors.gender" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.gender }}</div>
               </div>
               <div>
                 <label for="date_of_birth" class="block text-sm font-bold text-gray-700">Ngày sinh</label>
                 <input type="date" id="date_of_birth" v-model="form.date_of_birth" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-medium" />
                 <div v-if="form.errors.date_of_birth" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.date_of_birth }}</div>
               </div>
            </div>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="phone" class="block text-sm font-bold text-gray-700">Số điện thoại</label>
              <input type="tel" id="phone" v-model="form.phone" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-medium" placeholder="09xxxx..." />
              <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.phone }}</div>
            </div>
            
            <div>
              <label for="email" class="block text-sm font-bold text-gray-700">Email</label>
              <input type="email" id="email" v-model="form.email" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-medium" placeholder="email@example.com" />
              <div v-if="form.errors.email" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.email }}</div>
            </div>
         </div>
         
         <div>
            <label for="address" class="block text-sm font-bold text-gray-700">Địa chỉ hiện tại</label>
            <textarea id="address" v-model="form.address" rows="2" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-medium resize-none" placeholder="Số nhà, đường, phường/xã..."></textarea>
            <div v-if="form.errors.address" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.address }}</div>
         </div>
      </div>

      <!-- Tình trạng thuộc linh -->
      <div class="space-y-6 md:col-span-2">
         <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 pb-2">Tâm linh & Hội thánh</h3>
         <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
               <label for="status" class="block text-sm font-bold text-gray-700">Trạng thái Tín hữu</label>
               <select id="status" v-model="form.status" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-bold text-blue-700 bg-blue-50">
                 <option value="Chính thức">Chính thức</option>
                 <option value="Thân hữu">Thân hữu</option>
                 <option value="Hội viên liên kết">Hội viên liên kết</option>
                 <option value="Chuyển đi">Chuyển đi</option>
               </select>
               <div v-if="form.errors.status" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.status }}</div>
            </div>

            <div>
               <label for="marital_status" class="block text-sm font-bold text-gray-700">Hôn nhân</label>
               <select id="marital_status" v-model="form.marital_status" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-medium">
                 <option value="Độc thân">Độc thân</option>
                 <option value="Đã kết hôn">Đã kết hôn</option>
                 <option value="Góa">Góa</option>
                 <option value="Khác">Khác/Chưa rõ</option>
               </select>
               <div v-if="form.errors.marital_status" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.marital_status }}</div>
            </div>

            <div class="flex flex-col justify-end pb-1 text-sm font-bold text-gray-700">
               <label class="flex items-center space-x-3 cursor-pointer group">
                  <input type="checkbox" v-model="form.is_baptized" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 w-5 h-5 transition-transform group-hover:scale-110" />
                  <span class="group-hover:text-blue-600 transition-colors">Đã nhận Báp-têm</span>
               </label>
               <div v-if="form.errors.is_baptized" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.is_baptized }}</div>
            </div>
         </div>
      </div>
    </div>

    <!-- Nút Submit -->
    <div class="pt-6 border-t border-gray-100 flex justify-end space-x-3">
      <button 
         type="button" 
         @click="$emit('cancel')" 
         class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-all"
      >
        Hủy bỏ
      </button>
      <button 
         type="submit" 
         :disabled="form.processing"
         class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-md shadow-blue-100 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
      >
        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        Lưu Tín hữu
      </button>
    </div>
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['success', 'cancel']);

const form = useForm({
  full_name: '',
  gender: 'Nam',
  date_of_birth: '',
  phone: '',
  email: '',
  address: '',
  status: 'Chính thức',
  marital_status: 'Độc thân',
  is_baptized: false,
});

const submit = () => {
  form.post(route('members.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      emit('success');
    },
  });
};
</script>