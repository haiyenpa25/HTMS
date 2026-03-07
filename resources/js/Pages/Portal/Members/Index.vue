<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" :portal-type="portalType" @open-switcher="isSwitchOpen = true">
        <div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-2">
            
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-black text-gray-900 tracking-tight">Thành Viên Ban Ngành</h2>
                    <p class="text-sm text-gray-500 font-medium mt-1">Quản lý nhân sự và ban viên.</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex overflow-hidden mb-6">
                <button 
                    @click="activeTab = 'board'"
                    class="flex-1 py-4 text-center font-bold text-sm transition-colors relative"
                    :class="activeTab === 'board' ? 'text-blue-600 bg-blue-50/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                >
                    Ban Điều Hành
                    <div v-if="activeTab === 'board'" class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600"></div>
                </button>
                <button 
                    @click="activeTab = 'all'"
                    class="flex-1 py-4 text-center font-bold text-sm transition-colors relative"
                    :class="activeTab === 'all' ? 'text-blue-600 bg-blue-50/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                >
                    Toàn Ban
                    <div v-if="activeTab === 'all'" class="absolute bottom-0 left-0 w-full h-0.5 bg-blue-600"></div>
                </button>
            </div>

            <!-- Board Tab -->
            <div v-if="activeTab === 'board'" class="space-y-4 animate-fade-in">
                <div v-if="boardMembers.length === 0" class="bg-white rounded-3xl shadow-sm p-8 border border-gray-100 flex flex-col items-center justify-center text-center">
                    <p class="text-gray-500 text-sm mt-1 max-w-sm">Chưa có nhân sự Ban điều hành nào được chọn.</p>
                </div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Board Member Cards here -->
                    <div v-for="board in boardMembers" :key="board.id" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-black bg-blue-100 text-blue-600 border-2 border-white shadow-sm shrink-0">
                                    {{ board.firstName.charAt(0) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 leading-tight">{{ board.full_name }}</h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold mt-1"
                                        :class="getRoleColor(board.org_role)">
                                        {{ getRoleName(board.org_role) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-50 space-y-2">
                             <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                {{ board.phone || 'Chưa cập nhật' }}
                            </div>
                            <div v-if="board.team_name" class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                {{ board.team_name }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Members Tab -->
            <div v-if="activeTab === 'all'" class="animate-fade-in space-y-4">
                 <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 flex items-center justify-between">
                    <div class="relative w-full max-w-sm">
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            placeholder="Tìm tín hữu..." 
                            class="pl-10 block w-full border-gray-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50 py-2.5 transition-colors"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                    </div>
                 </div>

                 <!-- Members Table View Desktop -->
                 <div class="hidden sm:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
                     <!-- Bulk Actions Bar -->
                     <div v-if="selectedMemberIds.length > 0" class="bg-blue-50 border-b border-blue-100 flex items-center justify-between px-6 py-3 transition-all">
                        <div class="flex items-center space-x-4">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold text-sm">
                                {{ selectedMemberIds.length }}
                            </span>
                            <span class="text-sm font-bold text-blue-900">thành viên đang được chọn</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button @click="bulkDeleteMembers" class="px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 transition-colors shadow-sm">
                                Xóa Hàng Loạt
                            </button>
                            <button @click="openBulkAssignSlideOver" class="px-4 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                                Phân Tổ Hàng Loạt
                            </button>
                            <button @click="clearSelection" class="px-4 py-2 bg-white text-gray-500 text-sm font-bold rounded-xl hover:bg-gray-50 hover:text-gray-700 transition-colors border border-gray-200 shadow-sm">
                                Huỷ Chọn
                            </button>
                        </div>
                     </div>
                     
                     <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left w-12">
                                    <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-50">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Họ và Tên</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Số điện thoại</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Chức vụ - Tổ</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="member in members.data" :key="member.id" class="hover:bg-gray-50 transition-colors" :class="{'bg-blue-50/30': isSelected(member.id)}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" :value="member.id" v-model="selectedMemberIds" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-50">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ member.full_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ member.phone || '--' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2 w-auto">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold"
                                            :class="getRoleColor(member.org_role)">
                                            {{ getRoleName(member.org_role) }}
                                        </span>
                                        <span v-if="member.team_name" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-600">
                                            {{ member.team_name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <button @click="openMemberSlideOver(member)" class="text-blue-600 hover:text-blue-900">Chi tiết / Chức danh</button>
                                        <button @click="deleteMember(member.id)" class="text-red-500 hover:text-red-700 font-medium">Khỏi Ban</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                     </table>
                 </div>

                 <!-- Mobile Cards -->
                 <div class="sm:hidden space-y-3 relative">
                     <!-- Bulk Actions Bar Mobile -->
                     <div v-if="selectedMemberIds.length > 0" class="sticky top-0 left-0 w-full bg-blue-50/95 z-10 flex items-center justify-between p-4 rounded-xl backdrop-blur-sm border border-blue-100 shadow-sm mb-4">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-xs">
                                {{ selectedMemberIds.length }}
                            </span>
                            <span class="text-sm font-bold text-blue-900">Đã chọn</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button @click="bulkDeleteMembers" class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 transition-colors">
                                Xóa
                            </button>
                            <button @click="openBulkAssignSlideOver" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors">
                                Phân Tổ
                            </button>
                            <button @click="clearSelection" class="px-3 py-1.5 bg-white text-gray-500 text-xs font-bold rounded-lg border border-gray-200 transition-colors">
                                Huỷ
                            </button>
                        </div>
                     </div>
                     
                     <!-- Mobile Select All Toggle -->
                     <div class="flex items-center justify-between bg-gray-50 p-3 rounded-xl border border-gray-200">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-white">
                            <span class="text-sm font-bold text-gray-700">Chọn tất cả trang này</span>
                        </label>
                     </div>

                     <div v-for="member in members.data" :key="member.id" class="bg-white border rounded-xl p-4 shadow-sm relative transition-all" :class="isSelected(member.id) ? 'border-blue-300 ring-1 ring-blue-300 bg-blue-50/10' : 'border-gray-100'">
                          <!-- Mobile Checkbox Overlay -->
                          <div class="absolute top-4 left-4 z-10">
                              <input type="checkbox" :value="member.id" v-model="selectedMemberIds" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-gray-50 w-5 h-5 cursor-pointer">
                          </div>
                          
                          <div class="flex items-start justify-between pl-8" @click="toggleSelection(member.id)">
                            <!-- ... existing content wrapped in clickable div to toggle checkbox ... -->
                            <div class="flex-1 cursor-pointer">
                                <h4 class="font-bold text-gray-900 text-sm">{{ member.full_name }}</h4>
                                <p class="text-xs text-gray-500 mt-1">{{ member.phone || '--' }}</p>
                            </div>
                            <div class="flex flex-col items-end space-y-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold"
                                            :class="getRoleColor(member.org_role)">
                                    {{ getRoleName(member.org_role) }}
                                </span>
                                <span v-if="member.team_name" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">
                                    {{ member.team_name }}
                                </span>
                            </div>
                          </div>
                          
                          <!-- Mobile action button -->
                          <div class="mt-3 pt-3 border-t border-gray-50 flex justify-between items-center">
                              <button @click.stop="deleteMember(member.id)" class="text-xs font-bold text-red-500 hover:text-red-700 block">Rời Ban</button>
                              <button @click.stop="openMemberSlideOver(member)" class="text-xs font-bold text-blue-600 hover:text-blue-800">Cập nhật >></button>
                          </div>
                     </div>
                 </div>

                 <!-- Pagination -->
                  <div v-if="members.links && members.links.length > 3" class="flex justify-center mt-6 overflow-x-auto pb-2">
                    <div class="flex space-x-1 shrink-0">
                        <template v-for="(link, i) in members.links" :key="i">
                            <Link v-if="link.url" :href="link.url" class="px-3 py-1 text-sm font-medium rounded-lg transition-colors border" :class="link.active ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'" v-html="link.label"></Link>
                            <span v-else class="px-3 py-1 text-sm font-medium rounded-lg border border-transparent text-gray-400" v-html="link.label"></span>
                        </template>
                    </div>
                </div>
            </div>
            
        </div>

        <SlideOver v-model="isSwitchOpen" title="Chuyển đổi Ban Sinh Hoạt" size="md">
            <!-- Context Switcher Schema same as Attendance -->
            <template #default>
                <div class="p-6 space-y-5">
                   <div class="space-y-2">
                      <div v-for="dept in availableDepartments" :key="dept.id" @click="switchDept(dept.id)" class="w-full text-left p-4 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between group" :class="department?.id === dept.id ? 'border-blue-500 bg-blue-50' : 'border-gray-100 bg-white hover:border-gray-300 hover:bg-gray-50'">
                         <div class="flex items-center space-x-4 shrink-0">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-black" :class="department?.id === dept.id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'">{{ dept.name.charAt(0) }}</div>
                            <h4 class="text-sm font-black" :class="department?.id === dept.id ? 'text-blue-900' : 'text-gray-900'">{{ dept.name }}</h4>
                         </div>
                         <button v-if="department?.id !== dept.id" @click.stop="switchDept(dept.id)" class="px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">Chọn</button>
                      </div>
                   </div>
                </div>
            </template>
        </SlideOver>

        <SlideOver v-model="isMemberSlideOpen" :title="selectedMember ? selectedMember.full_name : 'Chi tiết'" size="md">
            <template #default>
                <div v-if="selectedMember" class="p-6 space-y-6">
                     <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Số điện thoại</label>
                        <p class="text-sm text-gray-900">{{ selectedMember.phone || 'Chưa cập nhật' }}</p>
                     </div>
                     
                     <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                        <p class="text-sm text-gray-900">{{ selectedMember.email || 'Chưa cập nhật' }}</p>
                     </div>
                     <hr class="border-gray-100">
                     
                     <!-- Update Role Form -->
                     <form @submit.prevent="updateRole" class="space-y-4">
                          <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Chức vụ trong Ban</label>
                            <select v-model="form.org_role" class="block w-full text-sm font-medium border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-3 transition-colors bg-gray-50">
                                <option value="Member">Thành viên (Member)</option>
                                <option value="TruongBan">Trưởng Ban</option>
                                <option value="PhoBan">Phó Ban</option>
                                <option value="ThuKy">Thư Ký</option>
                                <option value="ThuQuy">Thủ Quỹ</option>
                                <option value="UyVien">Ủy Viên</option>
                            </select>
                            <div v-if="form.errors.org_role" class="text-red-500 text-xs mt-1">{{ form.errors.org_role }}</div>
                          </div>
                          
                          <div v-if="teams && teams.length > 0">
                            <label class="block text-sm font-bold text-gray-900 mb-2">Phân vào Tổ (Tuỳ chọn)</label>
                            <select v-model="form.team_id" class="block w-full text-sm font-medium border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-3 transition-colors bg-gray-50">
                                <option :value="null">Không phân tổ</option>
                                <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                            </select>
                            <div v-if="form.errors.team_id" class="text-red-500 text-xs mt-1">{{ form.errors.team_id }}</div>
                          </div>
                          
                          <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-colors">
                              Lưu Thay Đổi
                          </button>
                     </form>
                </div>
            </template>
        </SlideOver>
         <SlideOver v-model="isBulkAssignSlideOpen" title="Phân Tổ Hàng Loạt" size="sm">
            <template #default>
                <div class="p-6 space-y-6">
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                        <p class="text-sm text-blue-800 font-medium">Bạn đang phân Tổ cho <strong class="font-black">{{ selectedMemberIds.length }}</strong> tín hữu.</p>
                        <p class="text-xs text-blue-600 mt-1">Lưu ý: Thao tác này sẽ ghi đè thiết lập Tổ hiện tại của các tín hữu được chọn.</p>
                    </div>

                    <form @submit.prevent="submitBulkAssign" class="space-y-4">
                        <div v-if="teams && teams.length > 0">
                        <label class="block text-sm font-bold text-gray-900 mb-2">Chọn Tổ Phân Bổ</label>
                        <select v-model="bulkForm.team_id" required class="block w-full text-sm font-medium border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 py-3 transition-colors bg-gray-50">
                            <option :value="null" disabled>-- Vui lòng chọn Tổ --</option>
                            <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                        </select>
                        <div v-if="bulkForm.errors.team_id" class="text-red-500 text-xs mt-1">{{ bulkForm.errors.team_id }}</div>
                        </div>

                        <button type="submit" :disabled="bulkForm.processing || !bulkForm.team_id" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-colors">
                            Xác Nhận Phân Tổ
                        </button>
                    </form>
                </div>
            </template>
         </SlideOver>
    </PortalLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';
import { debounce } from 'lodash';

const props = defineProps({
    department: Object,
    teams: Array,
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
    boardMembers: Array,
    members: Object,
    filters: Object,
    routePrefix: String,
    portalType: String,
});

const isSwitchOpen = ref(false);
const activeTab = ref('board');
const searchQuery = ref(props.filters?.search || '');
const isMemberSlideOpen = ref(false);
const selectedMember = ref(null);

const form = useForm({
    org_role: 'Member',
    team_id: null
});

const bulkForm = useForm({
    member_ids: [],
    team_id: null
});

const selectedMemberIds = ref([]);
const isBulkAssignSlideOpen = ref(false);

const isAllSelected = computed(() => {
    return props.members.data.length > 0 && selectedMemberIds.value.length === props.members.data.length;
});

const toggleSelectAll = (e) => {
    if (e.target.checked) {
        selectedMemberIds.value = props.members.data.map(m => m.id);
    } else {
        selectedMemberIds.value = [];
    }
};

const isSelected = (id) => selectedMemberIds.value.includes(id);

const toggleSelection = (id) => {
    if (isSelected(id)) {
        selectedMemberIds.value = selectedMemberIds.value.filter(itemId => itemId !== id);
    } else {
        selectedMemberIds.value.push(id);
    }
};

const clearSelection = () => {
    selectedMemberIds.value = [];
};

const switchDept = (deptId) => {
    const contextRoute = props.portalType === 'ministry' ? 'ministry.switch-context' : 'portal.switch-context';
    router.post(route(contextRoute), { department_id: deptId }, { preserveScroll: true, onSuccess: () => isSwitchOpen.value = false });
};

watch(searchQuery, debounce((value) => {
    router.get(route(`${props.routePrefix}.index`), {
        search: value
    }, {
        preserveState: true,
        replace: true,
    });
    if (value && activeTab.value !== 'all') {
         activeTab.value = 'all';
    }
}, 300));

const openMemberSlideOver = (member) => {
    selectedMember.value = member;
    form.org_role = member.org_role || 'Member';
    form.team_id = member.team_id || null;
    isMemberSlideOpen.value = true;
};

const updateRole = () => {
    form.post(route(`${props.routePrefix}.update`, selectedMember.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isMemberSlideOpen.value = false;
        }
    });
};

const openBulkAssignSlideOver = () => {
    bulkForm.member_ids = selectedMemberIds.value;
    bulkForm.team_id = null;
    isBulkAssignSlideOpen.value = true;
};

const submitBulkAssign = () => {
    bulkForm.post(route(`${props.routePrefix}.bulk-assign`), {
        preserveScroll: true,
        onSuccess: () => {
            isBulkAssignSlideOpen.value = false;
            clearSelection();
        }
    });
};

const deleteMember = (memberId) => {
    if (confirm('Bạn có chắc chắn muốn xóa tín hữu này khỏi Ban không? Dữ liệu điểm danh không bị mất.')) {
        router.delete(route(`${props.routePrefix}.remove`, memberId), {
            preserveScroll: true,
            onSuccess: () => {
                selectedMemberIds.value = selectedMemberIds.value.filter(id => id !== memberId);
            }
        });
    }
};

const bulkDeleteMembers = () => {
    if (confirm(`Bạn có chắc chắn muốn xóa ${selectedMemberIds.value.length} tín hữu này khỏi Ban không?`)) {
        router.delete(route(`${props.routePrefix}.bulk-remove`), {
            data: { member_ids: selectedMemberIds.value },
            preserveScroll: true,
            onSuccess: () => {
                clearSelection();
            }
        });
    }
};

const getRoleName = (roleKey) => {
    const roles = {
        'TruongBan': 'Trưởng Ban',
        'PhoBan': 'Phó Ban',
        'ThuKy': 'Thư Ký',
        'ThuQuy': 'Thủ Quỹ',
        'UyVien': 'Ủy Viên',
        'Member': 'Ban Viên',
    };
    return roles[roleKey] || 'Chưa phân công';
};

const getRoleColor = (roleKey) => {
    const colors = {
        'TruongBan': 'bg-pink-100 text-pink-700',
        'PhoBan': 'bg-purple-100 text-purple-700',
        'ThuKy': 'bg-indigo-100 text-indigo-700',
        'ThuQuy': 'bg-teal-100 text-teal-700',
        'UyVien': 'bg-orange-100 text-orange-700',
        'Member': 'bg-gray-100 text-gray-600',
    };
    return colors[roleKey] || 'bg-gray-100 text-gray-400';
};
</script>
<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
