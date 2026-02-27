<template>
  <component :is="currentLayout">
    <template #header>
      Tổng quan Hệ thống
    </template>

    <div class="py-4 space-y-6">
      <!-- Dành cho Pastor / Admin / Dept Lead (Mock Permission) -->
      <div v-if="hasApprovalRole" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <AppCard>
          <template #header>
            <div class="flex items-center justify-between">
              <span>Hồ sơ cần duyệt</span>
              <StatusBadge status="danger">{{ $page.props.pending_approvals_count }} chờ</StatusBadge>
            </div>
          </template>
          <p class="text-gray-600 space-y-2 text-sm mb-4">
            Đang có {{ $page.props.pending_approvals_count }} đơn xin vắng mặt và yêu cầu cập nhật hồ sơ từ các thành viên trong Ban của bạn.
          </p>
          <div class="mt-4 flex gap-2">
            <PrimaryButton @click="openModal">Tiến hành Duyệt</PrimaryButton>
            <DangerButton>Từ chối tất cả</DangerButton>
          </div>
        </AppCard>
        
        <!-- Ví dụ Table cho Desktop (Bị ẩn trên Mobile nhỏ) -->
        <AppCard class="hidden md:block">
           <template #header>Thành viên mới nhất</template>
           <ul class="divide-y divide-gray-200 text-sm">
              <li class="py-2 flex justify-between"><span>Nguyễn Văn A</span> <StatusBadge status="success">Hoạt động</StatusBadge></li>
              <li class="py-2 flex justify-between"><span>Lê Thị B</span> <StatusBadge status="warning">Cách ly</StatusBadge></li>
           </ul>
        </AppCard>
      </div>

      <!-- Dành cho Member thông thường -->
      <div v-else class="grid grid-cols-1 gap-4">
        <AppCard>
           <template #header>Thông tin của bạn</template>
           <div class="flex items-center space-x-4 mb-4">
             <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xl">
               {{ $page.props.auth.user?.name.charAt(0) || 'U' }}
             </div>
             <div>
               <h3 class="text-lg font-bold">{{ $page.props.auth.user?.name || 'Người dùng' }}</h3>
               <p class="text-sm text-gray-500">{{ $page.props.auth.user?.role || 'Thành viên' }}</p>
             </div>
           </div>
           <PrimaryButton class="w-full justify-center">Cập nhật hồ sơ</PrimaryButton>
        </AppCard>
      </div>

      <!-- Vùng Modal Headless UI -->
      <Modal :show="isModalOpen" title="Duyệt Hồ Sơ" @close="closeModal">
        <p>Bạn có chắc chắn muốn duyệt đồng loạt tất cả các hồ sơ đang chờ hay không?</p>
        <template #footer>
           <button @click="closeModal" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Đóng</button>
           <PrimaryButton @click="closeModal" class="ml-2">Xác nhận Duyệt</PrimaryButton>
        </template>
      </Modal>

    </div>
  </component>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Modal from '@/Components/Modal.vue';

const page = usePage();

// Nhận diện kích thước màn hình
const windowWidth = ref(window.innerWidth);

const updateWidth = () => {
  windowWidth.value = window.innerWidth;
};

onMounted(() => {
  window.addEventListener('resize', updateWidth);
});
onUnmounted(() => {
  window.removeEventListener('resize', updateWidth);
});

// Chuyển Layout động (Responsive)
// Tailinwd md breakpoint là 768px
const currentLayout = computed(() => {
  return windowWidth.value >= 768 ? AuthenticatedLayout : MobileLayout;
});

// Giả lập quyền (Mock Role Check)
const hasApprovalRole = computed(() => {
  const role = page.props.auth.user?.role;
  return role === 'Pastor' || role === 'BTS_Admin' || role === 'Department_Lead';
});

// Modal state
const isModalOpen = ref(false);
const openModal = () => isModalOpen.value = true;
const closeModal = () => isModalOpen.value = false;
</script>
