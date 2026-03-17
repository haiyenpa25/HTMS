<script setup>
const props = defineProps({
  log: Object,
  idPrefix: { type: String, default: 'props' }
});

const getEventColor = (event) => {
  const map = {
    created: 'bg-emerald-100 text-emerald-700 border-emerald-200',
    updated: 'bg-indigo-100 text-indigo-700 border-indigo-200',
    deleted: 'bg-red-100 text-red-700 border-red-200',
  };
  return map[event] || 'bg-gray-100 text-gray-700 border-gray-200';
};

const getEventIcon = (event) => {
  const map = {
    created: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
    updated: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>',
    deleted: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>',
  };
  return map[event] || '';
};

const togglePropView = (id) => {
  const el = document.getElementById(`${props.idPrefix}-${id}`);
  if (el) el.classList.toggle('hidden');
};
</script>

<template>
  <!-- Timeline Dot -->
  <div class="absolute -left-[17px] top-1.5 w-8 h-8 rounded-full border-4 border-white flex items-center justify-center shadow-sm"
       :class="getEventColor(log.event)" v-html="getEventIcon(log.event)">
  </div>

  <!-- Log Content Card -->
  <div class="bg-white border rounded-2xl shadow-sm hover:shadow-md transition-all pt-3 pb-3 px-5 mb-8"
       :class="log.event === 'deleted' ? 'border-red-100' : 'border-gray-100'">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-3 w-full">
        <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-white text-xs font-black shrink-0 shadow-sm border-2 border-white">
          {{ log.causer_name.charAt(0).toUpperCase() }}
        </div>
        
        <div class="leading-snug min-w-0">
          <span class="text-sm font-bold text-gray-900 block sm:inline">{{ log.causer_name }}</span>
          <span class="text-sm text-gray-500 sm:mx-1 block sm:inline">đã {{ log.event === 'created' ? 'tạo mới' : (log.event === 'updated' ? 'cập nhật' : 'xoá') }}</span>
          
          <span class="inline-block px-2 py-0.5 mt-1 sm:mt-0 rounded-md bg-indigo-50 text-indigo-700 text-xs font-bold whitespace-nowrap">
            {{ log.subject_label }} {{ log.subject_id ? `#${log.subject_id}` : '' }}
          </span>
        </div>
      </div>
      
      <div class="flex flex-row sm:flex-col items-center sm:items-end justify-between sm:justify-center shrink-0 w-full sm:w-auto mt-2 sm:mt-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-50">
        <span class="text-xs font-bold text-gray-500">{{ log.human_time }}</span>
        <span class="text-[10px] text-gray-400 font-mono">{{ log.created_at }}</span>
      </div>
    </div>

    <!-- Payload Expandable Viewer -->
    <div v-if="log.properties?.attributes || log.properties?.old" class="mt-4 pt-3 border-t border-gray-50">
      <button @click="togglePropView(log.id)" class="text-[11px] font-bold text-indigo-500 hover:text-indigo-700 uppercase tracking-widest flex items-center gap-1">
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        Xem lịch sử Dữ liệu (Payload) 
      </button>

      <div :id="`${idPrefix}-${log.id}`" class="hidden mt-3 max-h-48 overflow-y-auto border bg-[#0f172a] rounded-xl scrollbar-thin scrollbar-thumb-slate-600">
         <div class="flex flex-col md:flex-row w-full divide-y md:divide-y-0 md:divide-x divide-slate-800">
            <div v-if="log.properties.old" class="flex-1 p-3">
              <p class="text-[9px] uppercase font-black tracking-widest text-rose-400 mb-1.5 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Thông tin cũ - OLD</p>
              <pre class="text-xs text-slate-300 font-mono whitespace-pre-wrap break-all">{{ JSON.stringify(log.properties.old, null, 2) }}</pre>
            </div>
            <div v-if="log.properties.attributes" class="flex-1 p-3">
              <p class="text-[9px] uppercase font-black tracking-widest text-emerald-400 mb-1.5 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Thông tin mới - NEW</p>
              <pre class="text-xs text-slate-300 font-mono whitespace-pre-wrap break-all">{{ JSON.stringify(log.properties.attributes, null, 2) }}</pre>
            </div>
         </div>
      </div>
    </div>
  </div>
</template>
