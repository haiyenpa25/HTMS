<template>
  <component :is="currentLayout">
    <template #header>Soạn Bản Tin Mới</template>

    <div class="py-4 w-full max-w-4xl mx-auto space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-br from-indigo-900 to-purple-900 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10 translate-x-12 -translate-y-12">
          <svg class="h-64 w-64" fill="currentColor" viewBox="0 0 24 24"><path d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0l-8 5-8-5h16zm0 12H4V8l8 5 8-5v10z"/></svg>
        </div>
        <div class="relative z-10">
          <div class="flex items-center gap-3 mb-3">
            <Link :href="route('admin.broadcasts.index')" class="text-indigo-300 hover:text-white text-sm transition-colors">← Danh sách</Link>
            <span class="text-indigo-500">/</span>
            <span class="text-white text-sm font-medium">Soạn Bản Tin Mới</span>
          </div>
          <h2 class="text-3xl font-black mb-2">✉️ Soạn Bản Tin Email</h2>
          <p class="text-indigo-200 text-sm max-w-xl">Soạn thảo và gửi Email hàng loạt tới tất cả Tín hữu hoặc theo từng Ban ngành cụ thể.</p>
        </div>
      </div>

      <!-- Flash Success -->
      <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-green-800 font-medium text-sm">{{ $page.props.flash.success }}</p>
      </div>

      <!-- Main Form -->
      <form @submit.prevent="submitSend" class="space-y-6">
        <!-- Subject -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <label class="block text-sm font-black text-gray-700 uppercase tracking-wider mb-3">📌 Chủ Đề Tiêu Đề *</label>
          <input
            v-model="form.subject"
            type="text"
            class="block w-full border-gray-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg font-bold"
            placeholder="Ví dụ: Thư mời Lễ Kỷ Niệm 10 Năm Thành Lập..."
            required
          >
          <p v-if="form.errors.subject" class="text-sm text-red-600 mt-2">{{ form.errors.subject }}</p>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <div class="flex items-center justify-between mb-3">
            <label class="text-sm font-black text-gray-700 uppercase tracking-wider">📝 Nội Dung Email *</label>
            <span class="text-xs text-gray-400 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1 font-mono">Hỗ trợ Markdown</span>
          </div>
          <textarea
            v-model="form.content"
            rows="14"
            class="block w-full border-gray-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm leading-relaxed"
            required
            placeholder="# Tiêu đề lớn&#10;&#10;Xin chào anh chị em,&#10;&#10;Trân trọng kính mời..."
          ></textarea>
          <p v-if="form.errors.content" class="text-sm text-red-600 mt-2">{{ form.errors.content }}</p>
          <!-- Live char count -->
          <p class="text-right text-xs text-gray-400 mt-2">{{ form.content.length }} ký tự</p>
        </div>

        <!-- Target Audience -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
          <h3 class="text-sm font-black text-gray-700 uppercase tracking-wider mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            🎯 Đối Tượng Nhận Email
          </h3>

          <!-- Broadcast to all badge -->
          <div v-if="!form.target_roles.length && !form.target_departments.length" class="mb-4 bg-indigo-50 border border-indigo-200 rounded-xl p-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-indigo-700 font-medium">Bỏ trống = gửi đến <strong>TẤT CẢ</strong> tín hữu có email.</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Theo Role -->
            <div>
              <label class="block text-xs font-bold text-gray-500 mb-2 uppercase">Theo Vai Trò</label>
              <div class="max-h-44 overflow-y-auto bg-gray-50 border border-gray-200 rounded-xl p-2 space-y-1">
                <label v-for="role in roles" :key="role.id" class="flex items-center p-2 hover:bg-white rounded-lg cursor-pointer transition-colors">
                  <input type="checkbox" :value="role.id" v-model="form.target_roles" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                  <span class="ml-2 text-sm font-medium text-gray-700">{{ role.name }}</span>
                </label>
              </div>
              <p v-if="form.target_roles.length" class="text-xs text-indigo-600 font-medium mt-1">{{ form.target_roles.length }} vai trò được chọn</p>
            </div>

            <!-- Theo Ban ngành -->
            <div>
              <label class="block text-xs font-bold text-gray-500 mb-2 uppercase">Theo Ban Ngành</label>
              <div class="max-h-44 overflow-y-auto bg-gray-50 border border-gray-200 rounded-xl p-2 space-y-1">
                <label v-for="dept in departments" :key="dept.id" class="flex items-center p-2 hover:bg-white rounded-lg cursor-pointer transition-colors">
                  <input type="checkbox" :value="dept.id" v-model="form.target_departments" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                  <span class="ml-2 text-sm font-medium text-gray-700">{{ dept.name }}</span>
                </label>
              </div>
              <p v-if="form.target_departments.length" class="text-xs text-indigo-600 font-medium mt-1">{{ form.target_departments.length }} ban ngành được chọn</p>
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-4 italic bg-yellow-50 border border-yellow-200 rounded-xl p-3">
            ⚠️ Hệ thống chỉ gửi tới những Tín hữu (thỏa mãn Cả Vai trò <strong>HOẶC</strong> Cả Ban ngành) có email hợp lệ.
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-between items-center pb-6">
          <Link :href="route('admin.broadcasts.index')" class="w-full sm:w-auto order-3 sm:order-1 text-center py-3 px-6 border border-gray-300 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors">
            Hủy Bỏ
          </Link>
          <div class="flex gap-3 w-full sm:w-auto order-1 sm:order-2">
            <button type="button" @click="submitDraft" :disabled="form.processing"
              class="flex-1 sm:flex-none py-3 px-6 bg-white border border-gray-300 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors disabled:opacity-50 shadow-sm">
              <span v-if="form.processing && form.action === 'save'">Đang lưu...</span>
              <span v-else>💾 Lưu Nháp</span>
            </button>
            <button type="submit" :disabled="form.processing"
              class="flex-1 sm:flex-none py-3 px-8 bg-indigo-600 hover:bg-indigo-700 rounded-xl text-sm font-black text-white transition-colors disabled:opacity-50 shadow-lg shadow-indigo-200">
              <span v-if="form.processing && form.action === 'send'">Đang xử lý...</span>
              <span v-else>🚀 Gửi Ngay</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </component>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const currentLayout = computed(() => {
  if (typeof window === 'undefined') return AuthenticatedLayout;
  return window.innerWidth < 768 ? MobileLayout : AuthenticatedLayout;
});

const props = defineProps({
  roles:       { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
});

const form = useForm({
  subject:            '',
  content:            '',
  target_roles:       [],
  target_departments: [],
  action:             'send',
});

function submitDraft() {
  form.action = 'save';
  form.post(route('admin.broadcasts.store'));
}

function submitSend() {
  if (!confirm('Bạn có chắc chắn muốn phát hành Thư này ngay? Hệ thống sẽ quét Data và đưa vào Hàng đợi máy chủ. Lưu ý: Thao tác này KHÔNG THỂ THU HỒI.')) return;
  form.action = 'send';
  form.post(route('admin.broadcasts.store'));
}
</script>
