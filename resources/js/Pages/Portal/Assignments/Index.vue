<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" @open-switcher="isSwitchOpen = true">
        <div class="py-4 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100 flex flex-col items-center justify-center min-h-[50vh] text-center">
                <div class="w-20 h-20 mb-4 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"></path></svg>
                </div>
                <h2 class="text-xl font-black text-gray-900 mb-2">Phân công Nhân sự</h2>
                <p class="text-gray-500 text-sm max-w-md">Lịch trình phân công mục vụ theo tháng cho Tín hữu.</p>
            </div>
        </div>

        <SlideOver v-model="isSwitchOpen" title="Chuyển đổi Ban Sinh Hoạt" size="md">
            <template #default>
                <div class="p-6 space-y-5">
                   <div class="space-y-2">
                      <div v-for="dept in availableDepartments" :key="dept.id" @click="switchDept(dept.id)" class="w-full text-left p-4 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between group" :class="department?.id === dept.id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-white'">
                         <div class="flex items-center space-x-4 shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-black" :class="department?.id === dept.id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'">{{ dept.name.charAt(0) }}</div>
                            <h4 class="text-sm font-black" :class="department?.id === dept.id ? 'text-blue-900' : 'text-gray-900'">{{ dept.name }}</h4>
                         </div>
                         <button v-if="department?.id !== dept.id" @click.stop="switchDept(dept.id)" class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">Chọn</button>
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
