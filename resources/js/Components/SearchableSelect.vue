<template>
  <div class="relative" v-click-outside="close">
    <div
      @click="toggle"
      class="flex items-center justify-between w-full px-4 py-2 text-sm bg-white border border-gray-300 rounded-xl cursor-pointer focus-within:ring-2 focus-within:ring-amber-500 focus-within:border-amber-500 shadow-sm"
      :class="{ 'opacity-50 cursor-not-allowed': disabled }"
    >
      <div class="flex-1 truncate">
        <template v-if="selectedOption">
          <slot name="selected" :option="selectedOption">
            {{ selectedOption[labelKey] }}
          </slot>
        </template>
        <template v-else>
          <span class="text-gray-400 font-medium">{{ placeholder }}</span>
        </template>
      </div>
      <svg
        class="w-5 h-5 text-gray-400 transition-transform duration-200"
        :class="{ 'rotate-180': isOpen }"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>

    <transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden"
      >
        <div class="p-2 border-b border-gray-100">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              ref="searchInput"
              v-model="searchQuery"
              type="text"
              class="block w-full pl-10 pr-3 py-2 text-sm border-gray-200 rounded-lg focus:ring-amber-500 focus:border-amber-500"
              :placeholder="searchPlaceholder"
              @keydown.esc="close"
            />
          </div>
        </div>

        <ul class="max-h-60 overflow-y-auto py-1 custom-scrollbar">
          <li
            v-for="option in filteredOptions"
            :key="option[valueKey]"
            @click="select(option)"
            class="px-4 py-2.5 text-sm cursor-pointer hover:bg-amber-50 transition-colors flex items-center justify-between"
            :class="{ 'bg-amber-50 text-amber-700 font-bold': modelValue === option[valueKey] }"
          >
            <slot name="option" :option="option">
              {{ option[labelKey] }}
            </slot>
            <svg
              v-if="modelValue === option[valueKey]"
              class="w-4 h-4 text-amber-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
          </li>
          <li v-if="filteredOptions.length === 0" class="px-4 py-8 text-center text-gray-400 text-sm">
            <svg class="w-8 h-8 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ noResultsText }}
          </li>
        </ul>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';

const props = defineProps({
  modelValue: [String, Number],
  options: {
    type: Array,
    default: () => []
  },
  labelKey: {
    type: String,
    default: 'label'
  },
  valueKey: {
    type: String,
    default: 'value'
  },
  placeholder: {
    type: String,
    default: 'Chọn một mục...'
  },
  searchPlaceholder: {
    type: String,
    default: 'Tìm kiếm...'
  },
  noResultsText: {
    type: String,
    default: 'Không tìm thấy kết quả'
  },
  disabled: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const searchInput = ref(null);

const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options;
  const q = searchQuery.value.toLowerCase();
  return props.options.filter(option => 
    String(option[props.labelKey]).toLowerCase().includes(q)
  );
});

const selectedOption = computed(() => {
  return props.options.find(opt => opt[props.valueKey] === props.modelValue);
});

const toggle = () => {
  if (props.disabled) return;
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = '';
    nextTick(() => {
      searchInput.value?.focus();
    });
  }
};

const select = (option) => {
  emit('update:modelValue', option[props.valueKey]);
  emit('change', option);
  isOpen.value = false;
};

const close = () => {
  isOpen.value = false;
};

// Custom directive for clicking outside
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event);
      }
    };
    document.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent);
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(209, 213, 219, 0.8);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.8);
}
</style>
