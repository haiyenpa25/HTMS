<template>
  <TransitionRoot as="template" :show="modelValue">
    <Dialog as="div" class="relative z-50" @close="$emit('update:modelValue', false)">
      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="ease-in-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in-out duration-300"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
            <!-- Panel -->
            <TransitionChild
              as="template"
              enter="transform transition ease-in-out duration-300 sm:duration-400"
              enter-from="translate-x-full"
              enter-to="translate-x-0"
              leave="transform transition ease-in-out duration-300 sm:duration-400"
              leave-from="translate-x-0"
              leave-to="translate-x-full"
            >
              <DialogPanel
                class="pointer-events-auto w-screen w-full"
                :class="sizeClass"
              >
                <div class="flex h-full flex-col bg-white shadow-2xl">
                  <!-- Header -->
                  <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                      <DialogTitle class="text-xl font-black text-gray-900 font-sans tracking-tight">
                        {{ title }}
                      </DialogTitle>
                      <p v-if="description" class="mt-1 text-sm text-gray-500 font-medium">
                        {{ description }}
                      </p>
                    </div>
                    <div class="ml-3 flex h-7 items-center">
                      <button
                        type="button"
                        class="relative rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500"
                        @click="$emit('update:modelValue', false)"
                      >
                        <span class="absolute -inset-2.5" />
                        <span class="sr-only">Đóng panel</span>
                        <XIcon class="h-5 w-5" aria-hidden="true" />
                      </button>
                    </div>
                  </div>
                  
                  <!-- Body -->
                  <div class="relative flex-1 px-6 py-6 sm:px-8 overflow-y-auto w-full">
                    <slot></slot>
                  </div>
                  
                  <!-- Footer Actions (Optional) -->
                  <div v-if="$slots.footer" class="border-t border-gray-100 px-6 py-4 bg-gray-50">
                     <slot name="footer"></slot>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { X as XIcon } from 'lucide-vue-next';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg, xl, 2xl
    validator(value) {
      return ['sm', 'md', 'lg', 'xl', '2xl'].includes(value)
    }
  }
});

defineEmits(['update:modelValue']);

const sizeClass = computed(() => {
  const sizes = {
    'sm': 'max-w-md',
    'md': 'max-w-xl',
    'lg': 'max-w-2xl',
    'xl': 'max-w-4xl',
    '2xl': 'max-w-6xl',
  };
  return sizes[props.size] || sizes.md;
});
</script>
