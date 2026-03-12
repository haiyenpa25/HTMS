<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" @open-switcher="isSwitchOpen = true">
        <div class="space-y-6 w-full">
            <div class="bg-white rounded-3xl shadow-sm p-8 border border-gray-100 flex flex-col items-center justify-center min-h-[50vh] text-center">
                <div class="w-24 h-24 mb-5 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center shadow-inner">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"></path></svg>
                </div>
                <div class="flex items-center justify-center gap-2 mb-2">
                    <h2 class="text-2xl font-black text-gray-900">Phân công Nhân sự</h2>
                    <!-- Tooltip Helper -->
                    <div class="relative group cursor-help mt-1 shrink-0">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-amber-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-64 p-3 bg-gray-900 text-white text-[11px] font-medium rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-20 shadow-xl pointer-events-none">
                            Hiển thị lịch phân công mục vụ (hướng dẫn chương trình, cầu nguyện, âm thanh...) của Ban trong tháng. Module này đang trong thời gian thử nghiệm.
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
                        </div>
                    </div>
                </div>
                <p class="text-gray-500 text-[15px] max-w-md">Lịch trình phân công mục vụ theo tháng cho Tín hữu.</p>
            </div>
        </div>

        <SlideOver v-model="isSwitchOpen" title="Chuyển đổi Ban Sinh Hoạt" size="md">
            <template #default>
                <div class="p-6 space-y-5">
                   <div class="space-y-3">
                      <div v-for="dept in availableDepartments" :key="dept.id" @click="switchDept(dept.id)" class="w-full text-left p-5 rounded-2xl border-2 transition-all cursor-pointer flex items-center justify-between group shadow-sm hover:shadow" :class="department?.id === dept.id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-white'">
                         <div class="flex items-center space-x-4 shrink-0">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center font-black text-[15px]" :class="department?.id === dept.id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'">{{ dept.name.charAt(0) }}</div>
                            <h4 class="text-[15px] font-black" :class="department?.id === dept.id ? 'text-blue-900' : 'text-gray-900'">{{ dept.name }}</h4>
                         </div>
                         <button v-if="department?.id !== dept.id" @click.stop="switchDept(dept.id)" class="px-4 py-2 text-[13px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors">Chọn</button>
                      </div>
                   </div>
                </div>
            </template>
        </SlideOver>
    </PortalLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';

const props = defineProps({
    department: Object,
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
});

const isSwitchOpen = ref(false);

const switchDept = (deptId) => {
    router.post(route('portal.switch-context'), { department_id: deptId }, { preserveScroll: true, onSuccess: () => isSwitchOpen.value = false });
};
</script>