<template>
  <component :is="currentLayout">
    <template #header>
      Hồ sơ Tín hữu
    </template>

    <div class="py-4 space-y-6 max-w-5xl mx-auto">
      <!-- Nút quay lại & Thao tác -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <Link :href="route('members.index')" class="flex items-center text-sm text-gray-500 hover:text-blue-600 transition-colors group w-fit">
          <svg class="w-5 h-5 mr-1 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Quay lại danh sách
        </Link>
        
        <div class="flex items-center space-x-2">
          <button class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-bold hover:bg-gray-50 shadow-sm transition-all">
            In hồ sơ
          </button>
          <button class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 shadow-md shadow-blue-100 transition-all">
            Chỉnh sửa
          </button>
        </div>
      </div>

      <!-- Member Profile Header -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="h-40 bg-gradient-to-br from-indigo-500 via-blue-600 to-blue-700 relative">
          <div class="absolute inset-0 bg-grid-white/[0.1] bg-[size:16px_16px]"></div>
          <div class="absolute bottom-4 right-6 flex space-x-2">
             <StatusBadge :status="member.status === 'active' ? 'success' : 'gray'">
                {{ member.status === 'active' ? 'Đang sinh hoạt' : member.status }}
             </StatusBadge>
             <span class="px-3 py-1 bg-white/20 backdrop-blur-md text-white text-[10px] uppercase font-black rounded-full border border-white/20">
                {{ member.member_code }}
             </span>
          </div>
        </div>
        
        <div class="px-8 pb-8 relative">
          <div class="flex flex-col md:flex-row md:items-end -mt-16 md:space-x-8">
            <div class="w-32 h-32 rounded-3xl bg-white p-1.5 shadow-xl">
              <div class="w-full h-full rounded-2xl bg-gradient-to-tr from-blue-100 to-indigo-100 text-blue-700 flex items-center justify-center text-5xl font-black">
                {{ (member.full_name || 'T').charAt(0) }}
              </div>
            </div>
            <div class="mt-6 md:mt-0 flex-1 pb-1">
              <h1 class="text-3xl font-black text-gray-900 leading-none mb-2">{{ member.full_name }}</h1>
              <div class="flex flex-wrap items-center gap-y-2 gap-x-6 text-sm text-gray-500">
                <div class="flex items-center">
                  <div class="w-2 h-2 rounded-full bg-blue-500 mr-2"></div>
                  {{ member.member_type || 'Tín hữu chính thức' }}
                </div>
                <div v-if="member.phone" class="flex items-center">
                  <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                  {{ member.phone }}
                </div>
                <div v-if="member.email" class="flex items-center">
                  <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                  {{ member.email }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex items-center space-x-1 bg-gray-100 p-1.5 rounded-2xl w-full sm:w-fit overflow-x-auto no-scrollbar">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'px-6 py-2.5 rounded-xl text-sm font-black transition-all whitespace-nowrap',
            activeTab === tab.id 
              ? 'bg-white text-blue-700 shadow-sm' 
              : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200'
          ]"
        >
          <div class="flex items-center justify-center">
             <component :is="tab.icon" class="w-4 h-4 mr-2" />
             {{ tab.name }}
          </div>
        </button>
      </div>

      <!-- Tab Content Area -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content (Left/Center) -->
        <div class="lg:col-span-2 space-y-8">
          
          <!-- TAB 1: THÔNG TIN CHUNG -->
          <div v-if="activeTab === 'general'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <AppCard title="Hồ sơ cơ bản" icon="IdentificationIcon">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8 py-2">
                <div>
                   <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block">Địa chỉ hiện tại</label>
                   <p class="text-sm text-gray-900 font-bold leading-relaxed">{{ member.address || 'Chưa cập nhật' }}</p>
                   <a v-if="member.visit_location" :href="member.visit_location" target="_blank" class="mt-2 flex items-center text-xs text-blue-600 font-bold hover:underline">
                      <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                      Vị trí trên bản đồ
                   </a>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1 block">Ngày sinh</label>
                    <p class="text-sm text-gray-900 font-black">{{ member.date_of_birth || 'Chưa rõ' }}</p>
                  </div>
                  <div>
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1 block">Giới tính</label>
                    <p class="text-sm text-gray-900 font-black uppercase">{{ member.gender === 'male' ? 'Nam' : 'Nữ' }}</p>
                  </div>
                </div>
              </div>

              <div class="mt-8 pt-8 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <div class="flex items-center justify-between bg-blue-50/50 p-3 rounded-2xl border border-blue-100">
                    <span class="text-xs font-bold text-gray-600">Ngày tin Chúa</span>
                    <span class="text-sm font-black text-blue-700">{{ member.faith_date || 'N/A' }}</span>
                  </div>
                  <div class="flex items-center justify-between bg-purple-50/50 p-3 rounded-2xl border border-purple-100">
                    <span class="text-xs font-bold text-gray-600">Ngày Báp-tem</span>
                    <span class="text-sm font-black text-purple-700">{{ member.baptism_date || 'Chưa' }}</span>
                  </div>
                </div>
                <div>
                   <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 block">Ghi chú chung</label>
                   <p class="text-xs text-gray-600 italic leading-relaxed">
                     {{ member.general_notes || 'Không có ghi chú nào khác.' }}
                   </p>
                </div>
              </div>
            </AppCard>

            <AppCard title="Ân tứ & Phục vụ" icon="SparklesIcon">
               <div v-if="member.talents.length" class="flex flex-wrap gap-2">
                  <span v-for="t in member.talents" :key="t.id" class="px-4 py-2 bg-gradient-to-tr from-yellow-50 to-orange-50 text-orange-700 border border-yellow-200 rounded-2xl text-xs font-black shadow-sm">
                    {{ t.name }}
                  </span>
               </div>
               <div v-else class="py-4 text-center text-sm text-gray-400 italic">
                  Chưa khai báo ân tứ phục vụ.
               </div>
               
               <div class="mt-8 pt-6 border-t border-gray-100">
                  <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4 block">Khóa học đã tham gia</label>
                  <div v-if="member.courses.length" class="space-y-3">
                     <div v-for="c in member.courses" :key="c.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl">
                        <span class="text-sm font-bold text-gray-700">{{ c.name }}</span>
                        <span class="px-3 py-1 bg-green-100 text-green-700 text-[10px] font-black rounded-lg uppercase">Completed</span>
                     </div>
                  </div>
                  <div v-else class="text-xs text-gray-400 italic">Chưa ghi nhận khóa học.</div>
               </div>
            </AppCard>
          </div>

          <!-- TAB 2: LỊCH SỬ CHĂM SÓC -->
          <div v-if="activeTab === 'history'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
             <div class="flex items-center justify-between mb-2 px-2">
                <h2 class="text-xl font-black text-gray-900 leading-none">Dòng thời gian Chăm sóc</h2>
                <button class="text-xs font-black text-blue-600 hover:text-blue-700 uppercase tracking-widest">+ Thêm nhật ký</button>
             </div>
             
             <div class="relative pl-12 space-y-12 before:absolute before:left-5 before:top-2 before:bottom-0 before:w-1 before:bg-blue-100 before:rounded-full">
                <div v-for="(log, idx) in member.care_logs" :key="idx" class="relative group">
                   <!-- Timeline Dot -->
                   <div class="absolute -left-[35px] top-1.5 w-7 h-7 bg-white border-4 border-blue-500 rounded-full shadow-lg shadow-blue-100 z-10 transition-transform group-hover:scale-125"></div>
                   
                   <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-sm transition-all hover:shadow-md hover:border-blue-100">
                      <div class="flex items-center justify-between mb-4">
                         <div class="text-sm font-black text-gray-900">{{ formatDate(log.visit_date) }}</div>
                         <div v-if="log.is_sensitive" class="px-2 py-0.5 bg-red-50 text-red-600 text-[10px] font-black rounded-lg uppercase border border-red-100">Bảo mật</div>
                      </div>
                      <h3 class="text-lg font-bold text-gray-800 mb-2">{{ log.summary }}</h3>
                      <p class="text-sm text-gray-500 leading-relaxed mb-6">{{ log.notes }}</p>
                      
                      <div class="flex items-center justify-between pt-4 border-t border-gray-50 mt-auto">
                         <div class="flex items-center text-xs text-gray-400">
                            <span class="mr-2 italic">Người thăm:</span>
                            <span class="font-bold text-gray-600">{{ log.caregiver?.full_name || 'Hệ thống' }}</span>
                         </div>
                         <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center cursor-pointer hover:bg-blue-500 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                         </div>
                      </div>
                   </div>
                </div>

                <div v-if="!member.care_logs.length" class="bg-white rounded-3xl p-20 text-center border-2 border-dashed border-gray-100">
                   <div class="text-gray-300 mb-2">Chưa có nhật ký thăm viếng.</div>
                   <button class="text-xs font-black text-blue-500 uppercase tracking-widest text-center hover:underline">Khởi tạo lần đầu</button>
                </div>
             </div>
          </div>

          <!-- TAB 3: GIA ĐÌNH & QUAN HỆ -->
          <div v-if="activeTab === 'family'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <AppCard title="Hộ gia đình" icon="HomeIcon">
               <div v-if="member.household" class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                  <div>
                    <h3 class="text-2xl font-black text-gray-900 mb-1">{{ member.household.name }}</h3>
                    <div class="flex items-center text-sm text-gray-500 italic">
                      <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                      {{ member.household.address || member.address }}
                    </div>
                  </div>
                  <Link :href="'#'" class="px-6 py-2.5 bg-gray-900 text-white rounded-2xl text-xs font-black hover:bg-black transition-all shadow-lg shadow-gray-200">
                    Xem hộ gia đình
                  </Link>
               </div>
               <div v-else class="text-center py-8">
                  <p class="text-sm text-gray-400 italic mb-4">Chưa được gán vào hộ gia đình nào.</p>
                  <button class="px-6 py-2 border border-blue-200 text-blue-600 rounded-2xl text-xs font-bold hover:bg-blue-50">Tạo hộ mới</button>
               </div>
            </AppCard>

            <AppCard title="Sơ đồ Gia phả (3 đời)" icon="ShareIcon">
               <div class="p-4 bg-gray-50 rounded-2xl min-h-[300px] flex flex-col items-center justify-center space-y-8">
                  <!-- Parent Row -->
                  <div class="flex space-x-4">
                     <div v-for="p in parents" :key="p.id" class="w-24 h-24 rounded-2xl bg-white shadow-sm flex flex-col items-center justify-center border-2 border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mb-2 font-black italic">P</div>
                        <span class="text-[10px] font-bold text-center px-2 line-clamp-1">{{ p.full_name }}</span>
                     </div>
                     <div v-if="!parents.length" class="w-24 h-24 border-2 border-dashed border-gray-200 rounded-2xl flex items-center justify-center text-[10px] text-gray-300 font-bold">Thêm Cha/Mẹ</div>
                  </div>

                  <!-- Connector -->
                  <div class="w-0.5 h-8 bg-blue-200 relative before:w-4 before:h-0.5 before:bg-blue-200 before:absolute before:-top-0.5 before:-left-2 after:w-4 after:h-0.5 after:bg-blue-200 after:absolute after:-top-0.5 after:left-1"></div>

                  <!-- Main Person -->
                  <div class="w-32 h-32 rounded-3xl bg-blue-600 shadow-xl border-4 border-white flex flex-col items-center justify-center text-white ring-8 ring-blue-50">
                     <div class="text-2xl font-black italic mb-1 uppercase tracking-tighter">{{ (member.full_name || 'T').split(' ').pop() }}</div>
                     <span class="text-[8px] opacity-70 font-black uppercase tracking-widest text-center">Bản thân</span>
                  </div>

                  <!-- Connector -->
                  <div class="w-0.5 h-8 bg-blue-200"></div>

                  <!-- Children Row -->
                  <div class="flex space-x-4">
                     <div v-for="c in children" :key="c.id" class="w-24 h-24 rounded-2xl bg-white shadow-sm flex flex-col items-center justify-center border-2 border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 mb-2 font-black italic">C</div>
                         <span class="text-[10px] font-bold text-center px-2 line-clamp-1">{{ c.full_name }}</span>
                     </div>
                     <div v-if="!children.length" class="w-24 h-24 border-2 border-dashed border-gray-200 rounded-2xl flex items-center justify-center text-[10px] text-gray-300 font-bold">Thêm Con</div>
                  </div>
               </div>
               <div class="mt-6 text-center">
                  <span class="text-[10px] text-gray-400 font-black italic uppercase">* Hệ thống tự động liên kết dữ liệu theo quan hệ Cha-Mẹ-Con *</span>
               </div>
            </AppCard>
          </div>

          <!-- TAB 4: MỤC VỤ (PASTOR ONLY) -->
          <div v-if="activeTab === 'pastoral'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
             <div class="bg-red-50/30 border border-red-100 rounded-3xl p-8">
                <div class="flex items-center mb-8">
                   <div class="w-12 h-12 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center mr-4">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                   </div>
                   <div>
                      <h2 class="text-2xl font-black text-gray-900">Thông tin Mục vụ</h2>
                      <p class="text-xs text-red-600 font-bold uppercase tracking-widest mt-1">Dữ liệu tuyệt mật dành riêng cho Quản nhiệm</p>
                   </div>
                </div>

                <div class="grid grid-cols-1 gap-8">
                   <div class="space-y-6">
                      <div class="bg-white rounded-2xl p-6 border border-red-50 shadow-sm">
                         <label class="text-[10px] font-black uppercase tracking-widest text-red-400 mb-3 block">Nan đề & Cầu thay</label>
                         <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-700 italic border border-dashed border-gray-200">
                            {{ member.sensitive_info?.prayer_concerns || 'Chưa có ghi nhận nan đề.' }}
                         </div>
                      </div>
                      
                      <div class="bg-white rounded-2xl p-6 border border-red-50 shadow-sm">
                         <label class="text-[10px] font-black uppercase tracking-widest text-red-400 mb-3 block">Ghi chú mục vụ riêng</label>
                         <p class="text-sm text-gray-600 leading-relaxed font-bold">
                            {{ member.sensitive_info?.pastoral_notes || 'Không có ghi chú riêng cho tín hữu này.' }}
                         </p>
                      </div>
                   </div>

                   <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                      <div class="bg-white rounded-2xl p-6 border border-red-50 shadow-sm">
                         <label class="text-[10px] font-black uppercase tracking-widest text-red-400 mb-2 block">Nghề nghiệp chi tiết</label>
                         <p class="text-lg font-black text-gray-900">{{ member.sensitive_info?.occupation || '-' }}</p>
                      </div>
                      <div class="bg-white rounded-2xl p-6 border border-red-50 shadow-sm">
                         <label class="text-[10px] font-black uppercase tracking-widest text-red-400 mb-2 block">Tình trạng Hôn nhân</label>
                         <p class="text-lg font-black text-gray-900">{{ member.sensitive_info?.marital_status || '-' }}</p>
                      </div>
                   </div>
                </div>
             </div>
          </div>

        </div>

        <!-- Sidebar (Right) -->
        <div class="space-y-8">
           <AppCard title="Vai trò Ban ngành" class="!bg-gray-50/50">
              <div v-if="member.memberships.length" class="space-y-4">
                 <div v-for="m in member.memberships" :key="m.id" class="p-4 bg-white rounded-2xl shadow-sm border border-gray-100 group">
                    <div class="text-[10px] font-black uppercase tracking-widest text-blue-500 mb-1">
                       {{ m.model?.name }}
                    </div>
                    <div class="text-lg font-black text-gray-900 group-hover:text-blue-700 transition-colors">
                       {{ m.org_role?.name }}
                    </div>
                 </div>
              </div>
              <div v-else class="text-sm text-gray-400 italic py-4 text-center">
                 Chưa đảm nhiệm vai trò nào.
              </div>
           </AppCard>

           <AppCard title="Hoạt động">
               <div class="space-y-4">
                  <div class="flex items-center justify-between p-3 bg-white rounded-2xl border border-gray-100 shadow-sm">
                     <span class="text-xs font-bold text-gray-500">Điểm danh cuối</span>
                     <span class="text-xs font-black text-gray-900">22/02/2026</span>
                  </div>
                  <div class="flex items-center justify-between p-3 bg-white rounded-2xl border border-gray-100 shadow-sm text-center">
                     <div>
                        <div class="text-[10px] font-black uppercase text-gray-400 mb-0.5">Tỉ lệ tham gia</div>
                        <div class="text-xl font-black text-blue-600">85%</div>
                     </div>
                     <div class="w-px h-8 bg-gray-100"></div>
                     <div>
                        <div class="text-[10px] font-black uppercase text-gray-400 mb-0.5">Thăm viếng</div>
                        <div class="text-xl font-black text-indigo-600">{{ member.care_logs.length }}</div>
                     </div>
                  </div>
               </div>
           </AppCard>
        </div>
      </div>
    </div>
  </component>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import AppCard from '@/Components/AppCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

// HeroIcons Shorthand (giả lập để code gọn)
const IdentificationIcon = () => import('lucide-vue-next').then(m => m.User);
const SparklesIcon = () => import('lucide-vue-next').then(m => m.Sparkles);
const HomeIcon = () => import('lucide-vue-next').then(m => m.Home);
const ShareIcon = () => import('lucide-vue-next').then(m => m.Share2);
const HistoryIcon = () => import('lucide-vue-next').then(m => m.History);
const ShieldCheckIcon = () => import('lucide-vue-next').then(m => m.ShieldCheck);

const props = defineProps({
  member: Object,
  auth_roles: Array
});

const activeTab = ref('general');

const isPastor = computed(() => {
  return props.auth_roles.includes('Pastor');
});

const tabs = computed(() => {
  const base = [
    { id: 'general', name: 'Thông tin chung', icon: IdentificationIcon },
    { id: 'family', name: 'Gia đình & Quan hệ', icon: HomeIcon },
    { id: 'history', name: 'Nhật ký Chăm sóc', icon: HistoryIcon },
  ];
  
  if (isPastor.value) {
    base.push({ id: 'pastoral', name: 'Mục vụ', icon: ShieldCheckIcon });
  }
  
  return base;
});

// Helper xử lý định dạng ngày
const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit' });
};

// Phân lọc quan hệ gia phả đơn giản cho demo
const parents = computed(() => props.member.related_from ? props.member.related_from.filter(r => r.pivot.type === 'parent') : []);
const children = computed(() => props.member.related_to ? props.member.related_to.filter(r => r.pivot.type === 'child') : []);

// Nhận diện kích thước màn hình
const windowWidth = ref(window.innerWidth);
const updateWidth = () => windowWidth.value = window.innerWidth;
onMounted(() => window.addEventListener('resize', updateWidth));
onUnmounted(() => window.removeEventListener('resize', updateWidth));

const currentLayout = computed(() => {
  return windowWidth.value >= 768 ? AuthenticatedLayout : MobileLayout;
});
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
