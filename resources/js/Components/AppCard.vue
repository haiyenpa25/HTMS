<template>
  <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div v-if="$slots.header" class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-800">
      <slot name="header"></slot>
    </div>
    <div class="p-6 text-gray-900">
      <slot></slot>
    </div>
    <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-end">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
</script>
