<template>
   <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
      <AppCard class="p-0 overflow-hidden border border-gray-100">
         <div class="px-4 sm:px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between bg-white gap-4">
            <div>
               <h3 class="text-lg font-black text-gray-900">Ban viên & Chức vụ</h3>
               <p class="text-xs text-gray-500 font-medium mt-1">Quản lý thành viên tham gia khối ban ngành</p>
            </div>
            <button @click="openAssignSlideOver" class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-colors flex justify-center items-center shadow-md shadow-blue-100">
               <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
               Thêm Ban viên
            </button>
         </div>

         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Họ và Tên</th>
                  <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider hidden sm:table-cell">Liên hệ</th>
                  <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Chức vụ (Role)</th>
                  <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-black text-gray-500 uppercase tracking-wider">Thao tác</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <tr v-for="member in members" :key="member.id" class="hover:bg-gray-50 transition-colors group">
                  <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                     <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-black text-xs mr-3 shrink-0">
                           {{ member.full_name?.charAt(0) || 'U' }}
                        </div>
                        <div>
                           <div class="text-sm font-black text-gray-900">{{ member.full_name }}</div>
                           <div class="text-xs text-gray-500 sm:hidden mt-0.5">{{ member.phone || 'Chưa có SĐT' }}</div>
                        </div>
                     </div>
                  </td>
                  <td class="px-4 sm:px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                     <div class="text-xs text-gray-500 font-medium">{{ member.phone || 'Chưa có SĐT' }}</div>
                     <div class="text-[10px] text-gray-400">{{ member.email }}</div>
                  </td>
                  <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                     <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-100">
                       {{ member.role || 'Thành viên' }}
                     </span>
                  </td>
                  <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                     <button @click="removeMember(member.id)" class="text-red-500 hover:text-red-700 font-bold p-1 hover:bg-red-50 rounded">Tháo gỡ</button>
                  </td>
                </tr>
                <tr v-if="members.length === 0">
                   <td colspan="4" class="px-6 py-8 text-center text-gray-500 text-sm">
                      Chưa có thành viên nào.
                   </td>
                </tr>
              </tbody>
            </table>
         </div>
      </AppCard>

      <SlideOver 
         v-model="isSlideOverOpen" 
         title="Thêm Ban viên (Nhiều người)"
         description="Tìm kiếm, lọc và phân bổ tín hữu vào ban ngành"
         size="xl"
      >
         <form @submit.prevent="submit" class="flex flex-col h-full bg-white -m-6 sm:-m-8">
            <div class="flex-1 overflow-y-auto flex flex-col md:flex-row">
               
               <!-- Left sidebar: Filters & Role assignment -->
               <div class="w-full md:w-1/3 bg-gray-50 border-r border-gray-100 p-6 space-y-6 shrink-0 order-2 md:order-1 border-t md:border-t-0 mt-6 md:mt-0">
                  <h4 class="text-sm font-black text-gray-900 border-b border-gray-200 pb-2">1. Định danh Chức vụ</h4>
                  <div class="space-y-4">
                     <div class="space-y-2">
                       <label class="text-xs font-bold text-gray-700 uppercase tracking-wide">Vai trò trong Ban <span class="text-red-500">*</span></label>
                       <select v-model="form.org_role_id" required class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 bg-white">
                          <option v-for="role in availableRoles" :key="role.id" :value="role.id">
                             {{ role.name }}
                          </option>
                       </select>
                     </div>

                     <div v-if="teams && teams.length > 0" class="space-y-2">
                       <label class="text-xs font-bold text-gray-700 uppercase tracking-wide">Trực thuộc Tổ (Tùy chọn)</label>
                       <select v-model="form.team_id" class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 bg-white">
                          <option :value="null">Không phân vào tổ nào</option>
                          <option v-for="team in teams" :key="team.id" :value="team.id">
                             {{ team.name }}
                          </option>
                       </select>
                     </div>
                  </div>

                  <h4 class="text-sm font-black text-gray-900 border-b border-gray-200 pb-2 pt-4">2. Bộ lọc Tín hữu</h4>
                  <div class="space-y-4">
                     <div class="space-y-2">
                       <label class="text-xs font-bold text-gray-700 uppercase tracking-wide">Tìm kiếm (Tên/SĐT)</label>
                       <input v-model="searchQuery" @input="fetchMembers" type="text" placeholder="Nhập từ khóa..." class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 bg-white" />
                     </div>
                     <div class="space-y-2">
                       <label class="text-xs font-bold text-gray-700 uppercase tracking-wide">Lọc Độ Tuổi</label>
                       <select v-model="filterAge" @change="fetchMembers" class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 bg-white">
                          <option value="">Tất cả</option>
                          <option value="18">Từ 18 tuổi trở lên</option>
                          <option value="30">Từ 30 tuổi trở lên</option>
                          <option value="50">Từ 50 tuổi trở lên</option>
                          <option value="65">Từ 65 tuổi trở lên</option>
                       </select>
                     </div>
                     <div class="space-y-2">
                       <label class="text-xs font-bold text-gray-700 uppercase tracking-wide">Tình trạng Hôn nhân</label>
                       <select v-model="filterMarital" @change="fetchMembers" class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2.5 bg-white">
                          <option value="">Tất cả</option>
                          <option value="Độc thân">Độc thân</option>
                          <option value="Đã kết hôn">Đã kết hôn</option>
                          <option value="Góa">Góa</option>
                       </select>
                     </div>
                  </div>
               </div>

               <!-- Right side: Members Checklist -->
               <div class="w-full md:w-2/3 p-6 flex flex-col order-1 md:order-2 h-96 md:h-auto">
                  <div class="flex items-center justify-between mb-4 shrink-0">
                     <h4 class="text-sm font-black text-gray-900">3. Chọn Thành Viên ({{ form.member_ids.length }} đang chọn)</h4>
                     <button type="button" @click="selectAll" class="text-xs font-bold text-blue-600 hover:text-blue-800">Chọn tất cả trang này</button>
                  </div>

                  <div class="flex-1 overflow-y-auto border border-gray-200 rounded-xl bg-gray-50">
                     <div v-if="isLoading" class="p-8 text-center text-sm text-gray-500 font-bold flex flex-col items-center">
                        <svg class="animate-spin h-6 w-6 text-blue-600 mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Đang lấy danh sách...
                     </div>
                     <div v-else-if="availableMembers.length === 0" class="p-8 text-center text-sm text-gray-500">
                        Không tìm thấy tín hữu phù hợp.
                     </div>
                     <div v-else class="divide-y divide-gray-100">
                        <label v-for="member in availableMembers" :key="member.id" class="flex items-center px-4 py-3 hover:bg-white cursor-pointer transition-colors">
                           <input type="checkbox" :value="member.id" v-model="form.member_ids" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 shrink-0">
                           <div class="ml-3 flex-1">
                              <div class="text-sm font-black text-gray-900">{{ member.full_name }}</div>
                              <div class="text-xs text-gray-500 mt-0.5 flex space-x-2">
                                 <span>{{ member.phone || 'No phone' }}</span>
                                 <span>•</span>
                                 <span>{{ member.marital_status }}</span>
                              </div>
                           </div>
                        </label>
                     </div>
                  </div>
                  <div class="mt-2 text-[10px] text-gray-400 font-medium italic text-right shrink-0">
                     * Đang hiển thị tối đa 100 kết quả gần nhất. Hãy sử dụng bộ lọc nếu chưa thấy người cần tìm.
                  </div>
               </div>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-gray-200 shrink-0 bg-white flex items-center justify-between">
               <div class="text-sm font-bold text-gray-700">
                  <span v-if="form.errors.member_ids" class="text-red-500">{{ form.errors.member_ids }}</span>
                  <span v-else>Đã chọn: <span class="text-blue-600 font-black">{{ form.member_ids.length }}</span> người</span>
               </div>
               <div class="flex space-x-3">
                  <button type="button" @click="isSlideOverOpen = false" class="px-5 py-2.5 border border-gray-300 rounded-xl font-bold text-sm text-gray-700 hover:bg-gray-50 transition-all">Hủy</button>
                  <button type="submit" :disabled="form.processing || form.member_ids.length === 0" class="px-5 py-2.5 bg-blue-600 rounded-xl font-bold text-sm text-white shadow-md hover:bg-blue-700 disabled:opacity-50 transition-all">Gán vào Ban</button>
               </div>
            </div>
         </form>
      </SlideOver>
   </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import axios from 'axios';
import AppCard from '@/Components/AppCard.vue';
import SlideOver from '@/Components/SlideOver.vue';

const props = defineProps({
   department: Object,
   teams: Array,
   members: Array,
   availableRoles: Array,
});

const emit = defineEmits(['refresh']);

const isSlideOverOpen = ref(false);
const isLoading = ref(false);

const availableMembers = ref([]);
const searchQuery = ref('');
const filterAge = ref('');
const filterMarital = ref('');

const form = useForm({
  member_ids: [],
  org_role_id: props.availableRoles?.length > 0 ? props.availableRoles[0].id : '',
  team_id: null,
});

const fetchMembers = debounce(async () => {
   isLoading.value = true;
   try {
      const { data } = await axios.get('/api/members', {
         params: {
            search: searchQuery.value,
            age_from: filterAge.value,
            marital_status: filterMarital.value
         }
      });
      availableMembers.value = data;
   } catch (error) {
      console.error("Lỗi lấy danh sách thành viên", error);
   } finally {
      isLoading.value = false;
   }
}, 300);

const openAssignSlideOver = () => {
   form.reset();
   searchQuery.value = '';
   filterAge.value = '';
   filterMarital.value = '';
   form.member_ids = [];
   isSlideOverOpen.value = true;
   fetchMembers();
};

const selectAll = () => {
   const ids = availableMembers.value.map(m => m.id);
   // Add only unique
   const newIds = [...new Set([...form.member_ids, ...ids])];
   form.member_ids = newIds;
};

const submit = () => {
   form.post(route('departments.members.assign', props.department.id), {
      onSuccess: () => {
         isSlideOverOpen.value = false;
         emit('refresh');
      }
   });
};

const removeMember = (memberId) => {
   if(confirm('Chắc chắn muốn tháo gỡ thành viên khỏi ban này?')) {
      router.delete(route('departments.members.remove', { department: props.department.id, member: memberId }), {
         onSuccess: () => {
            emit('refresh');
         }
      })
   }
}
</script>