<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
    :class="colorClasses"
  >
    <slot></slot>
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    default: 'info', // success, warning, danger, info
  },
});

const colorClasses = computed(() => {
  switch (props.status) {
    case 'success':
      return 'bg-green-100 text-green-800';
    case 'warning':
      return 'bg-yellow-100 text-yellow-800';
    case 'danger':
      return 'bg-red-100 text-red-800';
    case 'info':
    default:
      return 'bg-blue-100 text-blue-800';
  }
});
</script>
