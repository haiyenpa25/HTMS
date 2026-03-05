<template>
  <MobileLayout v-if="isMobile">
    <template #header>🏛️ Ban Chấp Sự</template>
    <DeaconContent
      :activeRole="activeRole"
      :stats="stats"
      :pendingReports="pendingReports"
      :attendanceSummary="attendanceSummary"
      :currentMonth="currentMonth"
      @switch-role="switchRole"
    />
  </MobileLayout>

  <AuthenticatedLayout v-else>
    <template #header>
      <div class="flex items-center gap-3">
        <span class="text-2xl">🏛️</span>
        <div>
          <h1 class="text-xl font-black text-gray-900">Ban Chấp Sự</h1>
          <p class="text-xs text-gray-500">Thư Ký & Thủ Quỹ Hội Thánh</p>
        </div>
      </div>
    </template>
    <DeaconContent
      :activeRole="activeRole"
      :stats="stats"
      :pendingReports="pendingReports"
      :attendanceSummary="attendanceSummary"
      :currentMonth="currentMonth"
      @switch-role="switchRole"
    />
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import DeaconContent from './DeaconContent.vue';

const props = defineProps({
  activeRole:        { type: String, default: 'secretary' },
  stats:             Object,
  pendingReports:    Array,
  attendanceSummary: Array,
  currentMonth:      String,
});

const page = usePage();
const isMobile = computed(() => page.props.isMobile ?? false);

const switchRole = (role) => {
  router.post(route('deacon.switch-role'), { role }, {
    preserveScroll: true,
  });
};
</script>
