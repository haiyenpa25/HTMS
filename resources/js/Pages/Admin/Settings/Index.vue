<template>
  <component :is="currentLayout">
    <template #header>Cấu Hình Hội Thánh</template>

    <div class="py-4 w-full max-w-4xl mx-auto space-y-6">

      <!-- Header -->
      <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-8 -top-8 opacity-5">
          <svg class="h-64 w-64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 4.3c1.77 0 3.2 1.43 3.2 3.2S13.77 11.7 12 11.7c-1.77 0-3.2-1.43-3.2-3.2S10.23 5.3 12 5.3zM12 13c2.13 0 6.4 1.07 6.4 3.2V17.4H5.6v-1.2c0-2.13 4.27-3.2 6.4-3.2z"/></svg>
        </div>
        <div class="relative z-10">
          <h2 class="text-3xl font-black mb-2">⚙️ Cấu Hình Hội Thánh</h2>
          <p class="text-slate-300 text-sm max-w-xl">Quản lý thông tin cơ bản, nhiệm kỳ, tính năng hệ thống và cấu hình email Hội Thánh.</p>
        </div>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-green-800 font-semibold text-sm">{{ $page.props.flash.success }}</p>
      </div>
      <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-red-800 font-semibold text-sm">{{ $page.props.flash.error }}</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="save">
        <div v-for="group in schema" :key="group.group" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
          <!-- Group Header -->
          <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center gap-3">
            <span class="text-xl">{{ group.icon }}</span>
            <h3 class="font-black text-gray-800 text-sm uppercase tracking-wider">{{ group.group }}</h3>
          </div>

          <!-- Settings list -->
          <div class="divide-y divide-gray-50">
            <div v-for="setting in group.settings" :key="setting.key" class="px-6 py-5 flex flex-col sm:flex-row sm:items-start gap-3">
              <!-- Label & description -->
              <div class="flex-1 min-w-0">
                <label :for="'setting-' + setting.key" class="block text-sm font-bold text-gray-800 mb-0.5">
                  {{ setting.label }}
                </label>
                <p class="text-xs text-gray-500">{{ setting.description }}</p>
              </div>

              <!-- Input -->
              <div class="sm:w-72 flex-shrink-0">
                <!-- Boolean toggle -->
                <template v-if="setting.type === 'boolean'">
                  <button type="button"
                    @click="form.settings[setting.key] = form.settings[setting.key] === '1' ? '0' : '1'"
                    class="relative inline-flex h-7 w-14 items-center rounded-full transition-colors"
                    :class="form.settings[setting.key] === '1' ? 'bg-indigo-600' : 'bg-gray-200'"
                  >
                    <span class="sr-only">{{ setting.label }}</span>
                    <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform"
                      :class="form.settings[setting.key] === '1' ? 'translate-x-8' : 'translate-x-1'"
                    ></span>
                  </button>
                  <span class="ml-2 text-xs font-medium" :class="form.settings[setting.key] === '1' ? 'text-indigo-600' : 'text-gray-400'">
                    {{ form.settings[setting.key] === '1' ? 'Bật' : 'Tắt' }}
                  </span>
                </template>

                <!-- Textarea -->
                <textarea v-else-if="setting.type === 'textarea'"
                  :id="'setting-' + setting.key"
                  v-model="form.settings[setting.key]"
                  rows="3"
                  class="block w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                  :placeholder="'Nhập ' + setting.label + '...'"
                ></textarea>

                <!-- Integer -->
                <input v-else-if="setting.type === 'integer'"
                  :id="'setting-' + setting.key"
                  v-model.number="form.settings[setting.key]"
                  type="number"
                  class="block w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                  :placeholder="'Nhập ' + setting.label + '...'"
                >

                <!-- Text (default) -->
                <input v-else
                  :id="'setting-' + setting.key"
                  v-model="form.settings[setting.key]"
                  type="text"
                  class="block w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-sm"
                  :placeholder="'Nhập ' + setting.label + '...'"
                >
              </div>
            </div>
          </div>
        </div>

        <!-- Save button -->
        <div class="flex justify-end pb-6">
          <button type="submit" :disabled="processing"
            class="inline-flex items-center gap-2 px-8 py-3 bg-slate-800 hover:bg-slate-900 text-white font-black rounded-xl transition-colors shadow-lg disabled:opacity-50">
            <svg v-if="processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ processing ? 'Đang lưu...' : 'Lưu Cấu Hình' }}</span>
          </button>
        </div>
      </form>
    </div>
  </component>
</template>

<script setup>
import { computed, ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const currentLayout = computed(() => {
  if (typeof window === 'undefined') return AuthenticatedLayout;
  return window.innerWidth < 768 ? MobileLayout : AuthenticatedLayout;
});

const props = defineProps({
  schema: { type: Array, default: () => [] },
});

const processing = ref(false);

// Build reactive form.settings from schema
const initialSettings = {};
props.schema.forEach(group => {
  group.settings.forEach(s => {
    // Convert booleans to string '1'/'0' for toggle
    if (s.type === 'boolean') {
      initialSettings[s.key] = s.value == '1' || s.value === true ? '1' : '0';
    } else {
      initialSettings[s.key] = s.value ?? '';
    }
  });
});

const form = reactive({ settings: { ...initialSettings } });

function save() {
  processing.value = true;
  router.post(route('admin.church-settings.update'), { settings: form.settings }, {
    preserveScroll: true,
    onFinish: () => processing.value = false,
  });
}
</script>
