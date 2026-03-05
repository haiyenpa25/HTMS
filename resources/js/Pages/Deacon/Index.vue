<template>
  <MobileLayout v-if="isMobile">
    <template #header>🏛️ Ban Chấp Sự</template>
    <DeaconPortalContent v-bind="$props" @switch-role="switchRole" />
  </MobileLayout>
  <AuthenticatedLayout v-else>
    <template #header>
      <div class="flex items-center gap-3">
        <span class="text-2xl">🏛️</span>
        <div>
          <h1 class="text-xl font-black text-gray-900">Ban Chấp Sự</h1>
          <p class="text-xs text-gray-500">Cổng Nội Bộ Chấp Sự</p>
        </div>
      </div>
    </template>
    <DeaconPortalContent v-bind="$props" @switch-role="switchRole" />
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import DeaconPortalContent from './DeaconPortalContent.vue';

const props = defineProps({
  activeRole:        { type: String, default: 'secretary' },
  totalMembers:      { type: Number, default: 0 },
  currentMonth:      { type: String, default: '' },
  // Secretary
  pendingAttendance: { type: Number, default: 0 },
  lastMeeting:       { type: Object, default: null },
  pendingReports:    { type: Array,  default: () => [] },
  // Treasurer
  funds:             { type: Array,  default: () => [] },
  totalIncome:       { type: Number, default: 0 },
  totalExpense:      { type: Number, default: 0 },
  pendingTx:         { type: Number, default: 0 },
});

const page = usePage();
const isMobile = computed(() => page.props.isMobile ?? false);

const switchRole = (role) => {
  router.post(route('deacon.switch-role'), { role }, { preserveScroll: true });
};
</script>
