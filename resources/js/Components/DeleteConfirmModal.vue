<template>
  <div v-show="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex flex-col items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      
      <!-- Background overlay -->
      <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-show="show" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" @click="$emit('close')"></div>
      </transition>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <!-- Modal panel -->
      <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to-class="opacity-100 translate-y-0 sm:scale-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100 translate-y-0 sm:scale-100" leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        <div v-show="show" class="inline-block relative overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-md w-full">
           <div class="px-6 py-6 pb-0">
             <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
             </div>
             <div class="text-center">
                <h3 class="text-xl font-black text-gray-900 leading-tight mb-2" id="modal-title">
                   {{ title }}
                </h3>
                <p class="text-sm text-gray-500">
                   {{ message }}
                </p>
             </div>
           </div>
           
           <div class="px-6 py-5 mt-6 bg-gray-50 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 rounded-b-2xl">
              <button type="button" @click="$emit('close')" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-bold hover:bg-gray-100 focus:outline-none transition-colors">
                 Hủy bỏ
              </button>
              <button type="button" @click="$emit('confirm')" class="w-full sm:w-auto px-5 py-2.5 bg-red-600 border border-transparent rounded-xl text-white font-bold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm transition-colors">
                 Tiếp tục xóa
              </button>
           </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  title: String,
  message: String,
});

defineEmits(['close', 'confirm']);
</script>
