<template>
  <component :is="currentLayout">
    <template #header>
      Tổng quan Hệ thống
    </template>

    <div class="py-4 space-y-6">

      <!-- ── Flash success message ── -->
      <div v-if="$page.props.flash?.success"
        class="bg-green-50 border border-green-200 text-green-800 text-sm font-medium px-4 py-3 rounded-xl flex items-center gap-2">
        <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ $page.props.flash.success }}
      </div>

      <!-- ── Admin / Pastor cards ── -->
      <div v-if="hasApprovalRole" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <!-- Card: Hồ sơ cần duyệt -->
        <AppCard>
          <template #header>
            <div class="flex items-center justify-between">
              <span class="font-bold">Hồ sơ cần duyệt</span>
              <StatusBadge status="danger">{{ $page.props.pending_approvals_count }} chờ</StatusBadge>
            </div>
          </template>
          <p class="text-gray-600 text-sm mb-4">
            Đang có <strong>{{ $page.props.pending_approvals_count }}</strong> đơn xin vắng mặt và yêu cầu cập nhật hồ sơ từ các thành viên.
          </p>
          <div class="flex gap-2">
            <PrimaryButton @click="openModal">Tiến hành Duyệt</PrimaryButton>
            <DangerButton>Từ chối tất cả</DangerButton>
          </div>
        </AppCard>

        <!-- Card: Báo cáo chờ duyệt (NEW) -->
        <AppCard v-if="$page.props.pending_reports_count > 0">
          <template #header>
            <div class="flex items-center justify-between">
              <span class="font-bold">📑 Báo cáo chờ duyệt</span>
              <StatusBadge status="warning">{{ $page.props.pending_reports_count }} báo cáo</StatusBadge>
            </div>
          </template>
          <p class="text-gray-600 text-sm mb-4">
            Có <strong>{{ $page.props.pending_reports_count }}</strong> báo cáo ban ngành đang chờ bạn duyệt (trạng thái "Đã nộp").
          </p>
          <div class="flex flex-wrap gap-2">
            <a href="/portal/reports"
              class="inline-flex items-center gap-1.5 px-4 py-2 bg-purple-600 text-white text-sm font-bold rounded-xl hover:bg-purple-700 transition-colors">
              📋 Báo cáo Ban Ngành
            </a>
            <a href="/education/report"
              class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 transition-colors">
              📚 BC Cơ Đốc Giáo Dục
            </a>
          </div>
        </AppCard>

        <!-- Card: Thành viên mới nhất -->
        <AppCard class="hidden md:block">
          <template #header>Thành viên mới nhất</template>
          <ul class="divide-y divide-gray-200 text-sm">
            <li class="py-2 flex justify-between"><span>Nguyễn Văn A</span> <StatusBadge status="success">Hoạt động</StatusBadge></li>
            <li class="py-2 flex justify-between"><span>Lê Thị B</span> <StatusBadge status="warning">Cách ly</StatusBadge></li>
          </ul>
        </AppCard>

      </div>

      <!-- ── No pending reports banner (still show to admin when 0) ── -->
      <div v-if="hasApprovalRole && $page.props.pending_reports_count === 0"
        class="text-center py-3 text-xs text-gray-400 bg-gray-50 rounded-xl border border-gray-100">
        ✓ Không có báo cáo nào đang chờ duyệt
      </div>

      <!-- ── Member thông thường ── -->
      <div v-if="!hasApprovalRole" class="grid grid-cols-1 gap-4">
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

      <!-- Modal duyệt hồ sơ -->
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
const windowWidth = ref(window.innerWidth);
const updateWidth = () => { windowWidth.value = window.innerWidth; };
onMounted(() => window.addEventListener('resize', updateWidth));
onUnmounted(() => window.removeEventListener('resize', updateWidth));

const currentLayout = computed(() => windowWidth.value >= 768 ? AuthenticatedLayout : MobileLayout);

const hasApprovalRole = computed(() => {
  const role = page.props.auth.user?.role;
  return role === 'Pastor' || role === 'Super_Admin' || role === 'BTS_Admin' || role === 'Department_Lead';
});

const isModalOpen = ref(false);
const openModal = () => isModalOpen.value = true;
const closeModal = () => isModalOpen.value = false;
</script>
