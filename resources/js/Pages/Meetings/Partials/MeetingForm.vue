<template>
  <form @submit.prevent="submit" class="flex flex-col h-full bg-white relative">
    
    <!-- Progress Bar (Only show when Creating) -->
    <div v-if="!isEditing" class="h-1.5 w-full bg-gray-100 overflow-hidden shrink-0">
       <div class="h-full bg-blue-600 transition-all duration-300" :style="`width: ${(currentStep / 3) * 100}%`"></div>
    </div>

    <!-- Body -->
    <div class="flex-1 overflow-y-auto p-6 relative">
      <div class="space-y-6">
        
        <!-- STEP 1: Loại hình -->
        <div v-show="currentStep === 1" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
          <div class="space-y-3">
            <label class="text-sm font-black text-gray-900">1. Loại hình tổ chức <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-1 gap-3">
               <label class="cursor-pointer group">
                  <input type="radio" v-model="form.type" value="church" class="peer sr-only" @change="autoSuggestDate('church')">
                  <div class="p-4 border-2 border-gray-100 rounded-2xl peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:bg-gray-50 transition-all flex items-center space-x-4">
                     <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                     </div>
                     <div>
                        <span class="block text-base font-black text-gray-900">Hội Thánh chung</span>
                        <span class="block text-xs text-gray-500 font-medium mt-0.5">Thờ phượng vào Chúa Nhật</span>
                     </div>
                  </div>
               </label>
               <label class="cursor-pointer group">
                  <input type="radio" v-model="form.type" value="department" class="peer sr-only" @change="autoSuggestDate('department')">
                  <div class="p-4 border-2 border-gray-100 rounded-2xl peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:bg-gray-50 transition-all flex items-center space-x-4">
                     <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                     </div>
                     <div>
                        <span class="block text-base font-black text-gray-900">Ban Ngành</span>
                        <span class="block text-xs text-gray-500 font-medium mt-0.5">Sinh hoạt, học kinh thánh ban</span>
                     </div>
                  </div>
               </label>
               <label class="cursor-pointer group">
                  <input type="radio" v-model="form.type" value="holiday" class="peer sr-only">
                  <div class="p-4 border-2 border-gray-100 rounded-2xl peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:bg-gray-50 transition-all flex items-center space-x-4">
                     <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                     </div>
                     <div>
                        <span class="block text-base font-black text-gray-900">Sự kiện / Lễ</span>
                        <span class="block text-xs text-gray-500 font-medium mt-0.5">Giáng sinh, Phục sinh, ...</span>
                     </div>
                  </div>
               </label>
            </div>
            <div v-if="form.errors.type" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.type }}</div>
          </div>

          <!-- Select Department if type === department -->
          <div v-if="form.type === 'department'" class="space-y-2 animate-in fade-in slide-in-from-top-2 duration-300">
            <label class="text-sm font-black text-gray-900">Chọn Ban Ngành <span class="text-red-500">*</span></label>
            <select 
              v-model="form.department_id" 
              class="w-full text-base border-gray-300 rounded-xl shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 bg-gray-50"
              :required="form.type === 'department'"
            >
              <option value="" disabled>-- Hãy chọn một Ban --</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
            </select>
            <div v-if="form.errors.department_id" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.department_id }}</div>
          </div>
        </div>

        <!-- STEP 2: Thời gian & Tạo Hàng Loạt -->
        <div v-show="currentStep === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-300">
           <div class="grid grid-cols-2 gap-4">
              <!-- Date -->
              <div class="space-y-2">
                <label class="text-sm font-black text-gray-900">Ngày nhóm <span class="text-red-500">*</span></label>
                <input 
                  v-model="form.date" 
                  type="date" 
                  required
                  class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 bg-gray-50 font-bold"
                />
                <div v-if="form.errors.date" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.date }}</div>
              </div>

              <!-- Time -->
              <div class="space-y-2">
                <label class="text-sm font-black text-gray-900">Giờ nhóm <span class="text-red-500">*</span></label>
                <input 
                  v-model="form.time" 
                  type="time" 
                  required
                  class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 bg-gray-50 font-bold"
                />
                <div v-if="form.errors.time" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.time }}</div>
              </div>
           </div>

           <!-- Bulk Creation (Tạo hàng loạt) - Hide if Editing -->
           <div v-if="!isEditing" class="p-5 bg-blue-50 border border-blue-100 rounded-2xl space-y-4">
              <div class="flex items-start justify-between">
                 <div>
                    <h4 class="text-sm font-black text-blue-900">Tạo Lịch Hàng Loạt (Tuần)</h4>
                    <p class="text-xs text-blue-700 mt-1">Hữu ích khi tạo lịch cố định cho nhiều tuần kế tiếp.</p>
                 </div>
                 <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="isBulkEnabled" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                 </label>
              </div>

              <div v-if="isBulkEnabled" class="animate-in fade-in slide-in-from-top-2 duration-200">
                 <label class="text-xs font-bold text-gray-700 uppercase tracking-wide">Số lượng tuần cần tạo</label>
                 <div class="flex items-center mt-2 space-x-4">
                    <input 
                      v-model.number="form.bulk_weeks" 
                      type="range" 
                      min="2" max="12" 
                      class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600"
                    />
                    <span class="text-lg font-black text-blue-600 w-12 text-center">{{ form.bulk_weeks }}</span>
                 </div>
                 <p class="text-[11px] text-gray-500 font-medium mt-2">Hệ thống sẽ tự động nhân bản lịch này vào <b>{{ form.bulk_weeks - 1 }} tuần kế tiếp</b> với cùng thiết lập diễn giả, nội dung (có thể sửa chi tiết sau).</p>
              </div>
           </div>
        </div>

        <!-- STEP 3: Chi tiết Nội dung -->
        <div v-show="currentStep === 3" class="space-y-5 animate-in fade-in slide-in-from-right-4 duration-300">
          <!-- Topic -->
          <div class="space-y-2">
            <label class="text-sm font-black text-gray-900">Chủ đề buổi nhóm</label>
            <input 
              v-model="form.topic" 
              type="text" 
              class="w-full text-base border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 placeholder:text-gray-400 bg-gray-50"
              placeholder="VD: Cảm tạ Chúa mùa gặt..."
            />
            <div v-if="form.errors.topic" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.topic }}</div>
          </div>

          <!-- Scripture -->
          <div class="space-y-2">
            <label class="text-sm font-black text-gray-900">Kinh Thánh nền tảng</label>
            <input 
              v-model="form.scripture" 
              type="text" 
              class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 placeholder:text-gray-400 bg-gray-50"
              placeholder="VD: Thi thiên 23:1-6"
            />
            <div v-if="form.errors.scripture" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.scripture }}</div>
          </div>

          <!-- Memory Verse -->
          <div class="space-y-2">
            <label class="text-sm font-black text-gray-900">Câu gốc</label>
            <input 
              v-model="form.memory_verse" 
              type="text" 
              class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 placeholder:text-gray-400 bg-gray-50"
              placeholder="VD: Thi thiên 23:1"
            />
            <div v-if="form.errors.memory_verse" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.memory_verse }}</div>
          </div>

          <!-- Speaker (Diễn giả) -->
          <div class="space-y-2 relative">
            <label class="text-sm font-black text-gray-900">Diễn giả / Người hướng dẫn</label>
            <div class="relative">
              <input 
                v-model="speakerSearch" 
                @focus="isSpeakerDropdownOpen = true"
                @blur="handleSpeakerBlur"
                @input="fetchSpeakers"
                type="text" 
                class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3 placeholder:text-gray-400 bg-gray-50 pr-10"
                placeholder="Tìm kiếm diễn giả..."
              />
              <div v-if="form.speaker_id" class="absolute inset-y-0 right-0 flex items-center pr-3">
                 <button type="button" @click="clearSpeaker" class="text-gray-400 hover:text-red-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                 </button>
              </div>
            </div>
            
            <!-- Floating Dropdown -->
            <div v-if="isSpeakerDropdownOpen && speakersList.length > 0" class="absolute z-50 w-full mt-1 bg-white border border-gray-100 rounded-xl shadow-lg max-h-60 overflow-y-auto hidden-scrollbar">
               <ul class="py-1">
                 <li 
                   v-for="s in speakersList" 
                   :key="s.id"
                   @mousedown.prevent="selectSpeaker(s)"
                   class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer flex items-center justify-between group"
                 >
                   <div>
                      <div class="text-sm font-bold text-gray-900 group-hover:text-blue-700">{{ s.title ? s.title + ' ' : '' }}{{ s.full_name }}</div>
                      <div class="text-xs text-gray-500" v-if="s.phone">{{ s.phone }}</div>
                   </div>
                   <span v-if="s.is_external" class="text-[10px] font-bold px-1.5 py-0.5 bg-purple-100 text-purple-700 rounded">Khách mời</span>
                   <span v-else class="text-[10px] font-bold px-1.5 py-0.5 bg-emerald-100 text-emerald-700 rounded">Nội bộ</span>
                 </li>
               </ul>
            </div>
            <div v-if="form.errors.speaker_id" class="text-xs text-red-500 font-medium mt-1">{{ form.errors.speaker_id }}</div>
            
            <!-- Quick Add Link (Optional enhancements below input) -->
            <div class="flex justify-end mt-1">
               <span class="text-[11px] text-gray-500">Chưa có trong danh sách? <a :href="route('speakers.index')" target="_blank" class="text-blue-600 font-bold hover:underline">Thêm diễn giả mới</a></span>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Footer Action Buttons -->
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0 space-y-3">
      <div class="flex items-center justify-between w-full">
         
         <button 
           v-if="currentStep > 1"
           type="button" 
           @click="previousStep"
           class="px-5 py-3 text-gray-500 font-bold hover:bg-gray-200 rounded-xl transition-colors text-sm"
         >
           Quay lại
         </button>
         <button 
           v-else
           type="button" 
           @click="$emit('close')"
           class="px-5 py-3 text-gray-500 font-bold hover:bg-gray-200 rounded-xl transition-colors text-sm"
         >
           Hủy bỏ
         </button>

         <button 
           v-if="currentStep < 3"
           type="button" 
           @click="nextStep"
           class="px-6 py-3 bg-gray-900 text-white rounded-xl hover:bg-gray-800 text-sm font-bold shadow-md transition-all flex items-center"
         >
           Tiếp tục
           <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
         </button>

         <button 
           v-else
           type="submit" 
           :disabled="form.processing"
           class="px-8 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-black shadow-lg shadow-blue-200 transition-all flex items-center justify-center disabled:opacity-50 min-w-[140px]"
         >
           <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
             <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
             <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
           </svg>
           {{ isEditing ? 'Lưu cập nhật' : 'Hoàn tất & Tạo' }}
         </button>

      </div>
      
      <!-- Delete Action inside Edit mode only -->
      <div v-if="isEditing" class="pt-4 mt-2 border-t border-red-100 flex justify-center w-full">
         <button 
           type="button"
           @click="confirmDelete"
           class="text-xs font-bold text-red-600 hover:text-red-800 hover:underline px-4 py-2 rounded-lg hover:bg-red-50 transition-colors"
         >
           Xóa vĩnh viễn Buổi nhóm này
         </button>
      </div>
    </div>
  </form>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
  meeting: {
    type: Object,
    default: null
  }
});

const page = usePage();
const departments = computed(() => page.props.departments || []);

const emit = defineEmits(['close', 'success']);

const isEditing = computed(() => !!props.meeting);
const currentStep = ref(isEditing.value ? 3 : 1); // If editing, jump to end
const isBulkEnabled = ref(false);

const form = useForm({
  type: 'church',
  department_id: '',
  date: '',
  time: '08:30',
  topic: '',
  memory_verse: '',
  scripture: '',
  speaker_id: null,
  bulk_weeks: 4,
});

// Speaker Autocomplete Logic
const speakerSearch = ref('');
const isSpeakerDropdownOpen = ref(false);
const speakersList = ref([]);

const fetchSpeakers = async () => {
    try {
        const response = await fetch(route('api.speakers.index', { search: speakerSearch.value }));
        const data = await response.json();
        speakersList.value = data;
    } catch (error) {
        console.error("Error fetching speakers", error);
    }
};

const selectSpeaker = (speaker) => {
    form.speaker_id = speaker.id;
    speakerSearch.value = (speaker.title ? speaker.title + ' ' : '') + speaker.full_name;
    isSpeakerDropdownOpen.value = false;
};

const clearSpeaker = () => {
    form.speaker_id = null;
    speakerSearch.value = '';
    speakersList.value = [];
    isSpeakerDropdownOpen.value = false;
};

const handleSpeakerBlur = () => {
    setTimeout(() => {
        isSpeakerDropdownOpen.value = false;
    }, 200); // delay to allow mousedown to trigger
};

// Auto-suggest next Sunday for Church
const autoSuggestDate = (typeStr) => {
   if (typeStr === 'church' && !isEditing.value) {
      const d = new Date();
      d.setDate(d.getDate() + (7 - d.getDay()) % 7); // Next Sunday
      form.date = d.toISOString().split('T')[0];
      form.time = '08:30';
   } else if (typeStr === 'department' && !isEditing.value) {
      form.date = new Date().toISOString().split('T')[0];
      form.time = '19:30';
   }
};

// Watch input meeting to fill the form in Edit mode
watch(() => props.meeting, (newVal) => {
  if (newVal) {
    form.type = newVal.type || 'church';
    form.department_id = newVal.department_id || '';
    form.date = newVal.date || new Date().toISOString().split('T')[0];
    form.time = newVal.time ? newVal.time.substring(0,5) : '08:30';
    form.topic = newVal.topic || '';
    form.memory_verse = newVal.memory_verse || '';
    form.scripture = newVal.scripture || '';
    form.speaker_id = newVal.speaker_id || null;
    if (newVal.speaker) {
       speakerSearch.value = (newVal.speaker.title ? newVal.speaker.title + ' ' : '') + newVal.speaker.full_name;
    } else if (newVal.preacher) { // Fallback for old records
       speakerSearch.value = newVal.preacher;
    } else {
       speakerSearch.value = '';
    }
  } else {
    form.reset();
    form.clearErrors();
    speakerSearch.value = '';
    speakersList.value = [];
    form.type = 'church';
    form.bulk_weeks = 4;
    autoSuggestDate('church');
  }
}, { immediate: true });

// Clear department_id if changing type back to church
watch(() => form.type, (newVal) => {
    if(newVal === 'church') {
        form.department_id = '';
    }
});

onMounted(() => {
   if (!isEditing.value && !form.date) autoSuggestDate('church');
});

const nextStep = () => {
   if (currentStep.value === 1) {
      if (form.type === 'department' && !form.department_id) {
         form.setError('department_id', 'Vui lòng chọn Ban ngành phụ trách.');
         return;
      }
      form.clearErrors();
   }
   if (currentStep.value === 2) {
      if (!form.date || !form.time) return;
   }
   if (currentStep.value < 3) currentStep.value++;
};

const previousStep = () => {
   if (currentStep.value > 1) currentStep.value--;
};

const submit = () => {
  // If bulk is not enabled, ensure it's not passed
  if (!isBulkEnabled.value) {
     form.bulk_weeks = 1;
  }

  if (isEditing.value) {
    form.put(route('meetings.update', props.meeting.id), {
      onSuccess: () => {
        emit('success');
        emit('close');
      }
    });
  } else {
    form.post(route('meetings.store'), {
      onSuccess: () => {
        emit('success');
        emit('close');
      }
    });
  }
};

const confirmDelete = () => {
  if (confirm(`Bạn có chắc chắn muốn xóa buổi nhóm này? Hành động này không thể hoàn tác!`)) {
    router.delete(route('meetings.destroy', props.meeting.id), {
      onSuccess: () => {
        emit('success');
        emit('close');
      }
    });
  }
}
</script>
