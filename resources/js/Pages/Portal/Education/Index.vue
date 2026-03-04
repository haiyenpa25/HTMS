<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" portal-type="education">
        <div class="py-6 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                    <Link :href="route('education.index')" class="p-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </Link>
                    <div>
                        <h1 class="text-xl font-black text-gray-900">Quản Lý Lớp Học</h1>
                        <p class="text-sm text-gray-500">{{ classes.length }} lớp đang hoạt động</p>
                    </div>
                    </div>
                <button v-if="isAdmin" @click="openCreateForm" class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white rounded-xl shadow-sm hover:from-indigo-700 hover:to-indigo-600 transition-all font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tạo Lớp Mới
                </button>
            </div>

            <!-- Empty state -->
            <div v-if="classes.length === 0" class="py-20 flex flex-col items-center text-center bg-white rounded-2xl border border-gray-100 shadow-sm">
                <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-700 mb-1">Chưa có lớp học nào</h3>
                <p class="text-sm text-gray-400 mb-4">Nhờ quản trị viên tạo lớp học để bắt đầu.</p>
                <button v-if="isAdmin" @click="openCreateForm" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors">Tạo Lớp Đầu Tiên</button>
            </div>

            <!-- Class Cards Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="cls in classes" :key="cls.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all overflow-hidden flex flex-col">
                    <!-- Card header -->
                    <div class="p-5 flex-1">
                        <div class="flex items-start gap-2 mb-3">
                            <div class="w-10 h-10 bg-indigo-100 text-indigo-700 rounded-xl flex items-center justify-center font-black text-base shrink-0">
                                {{ cls.name.charAt(0) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-black text-gray-900 leading-tight truncate">{{ cls.name }}</h3>
                                <p v-if="cls.description" class="text-xs text-gray-400 truncate mt-0.5">{{ cls.description }}</p>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold mt-1 inline-block" :class="{
                                    'bg-indigo-100 text-indigo-700': cls.class_type === 'sunday_school',
                                    'bg-purple-100 text-purple-700': cls.class_type === 'gospel',
                                    'bg-emerald-100 text-emerald-700': cls.class_type === 'bible_quiz',
                                }">{{ classTypeLabel(cls.class_type) }}</span>
                            </div>
                        </div>
                        <!-- Stats row -->
                        <div class="grid grid-cols-3 gap-2 mt-3">
                            <div class="text-center bg-blue-50 rounded-xl py-2">
                                <div class="text-base font-black text-blue-700">{{ cls.students_count }}</div>
                                <div class="text-[10px] text-blue-500 font-medium">Học viên</div>
                            </div>
                            <div class="text-center bg-purple-50 rounded-xl py-2">
                                <div class="text-base font-black text-purple-700">{{ cls.teachers.length }}</div>
                                <div class="text-[10px] text-purple-500 font-medium">Giáo viên</div>
                            </div>
                            <div class="text-center bg-indigo-50 rounded-xl py-2">
                                <div class="text-base font-black text-indigo-700">{{ cls.session_count }}</div>
                                <div class="text-[10px] text-indigo-500 font-medium">Buổi học</div>
                            </div>
                        </div>
                        <!-- Teachers -->
                        <div v-if="cls.teachers.length > 0" class="mt-3 flex flex-wrap gap-1">
                            <span v-for="t in cls.teachers.slice(0, 3)" :key="t.id" class="inline-flex items-center px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[11px] font-bold rounded-full">
                                {{ t.full_name }}
                            </span>
                        </div>
                    </div>
                    <!-- Actions -->
                    <div class="px-5 py-3 border-t border-gray-100 flex gap-2">
                        <Link :href="route('education.sessions', cls.id)"
                            class="flex-1 text-center text-sm font-black py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-600 transition-all shadow-sm flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Quản lý buổi học
                        </Link>
                        <button v-if="isAdmin" @click="openMembersPanel(cls)" class="px-3 py-2 border border-indigo-200 text-indigo-600 rounded-xl hover:bg-indigo-50 text-sm font-bold transition-colors" title="Quản lý thành viên">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </button>
                        <button v-if="isAdmin" @click="openEditForm(cls)" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 text-sm font-bold transition-colors" title="Sửa lớp">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Class SlideOver -->
        <SlideOver v-model="isFormOpen" :title="editingClass ? 'Sửa Lớp Học' : 'Tạo Lớp Học Mới'">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Tên lớp <span class="text-red-500">*</span></label>
                    <input v-model="form.name" type="text" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="VD: Lớp Trung Lão 2025">
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Loại lớp <span class="text-red-500">*</span></label>
                    <select v-model="form.class_type" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="sunday_school">📖 Lớp Trường Chúa Nhật (điểm danh + tiền dâng)</option>
                        <option value="gospel">✝️ Lớp Giáo Lý / Phước Âm (chỉ điểm danh)</option>
                        <option value="bible_quiz">📝 Trắc Nghiệm Kinh Thánh (điểm danh + chấm điểm)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Mô tả</label>
                    <textarea v-model="form.description" rows="3" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Mô tả lớp học..."></textarea>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 w-full">
                    <button @click="isFormOpen = false" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button @click="submitForm" :disabled="form.processing" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white rounded-xl text-sm font-bold hover:from-indigo-700 hover:to-indigo-600 disabled:opacity-50">
                        {{ form.processing ? 'Đang lưu...' : (editingClass ? 'Cập nhật' : 'Tạo lớp') }}
                    </button>
                </div>
            </template>
        </SlideOver>

        <!-- Members Management SlideOver -->
        <SlideOver v-model="isMembersOpen" :title="'Thành viên: ' + (managingClass?.name ?? '')">
            <div class="space-y-5">
                <!-- Search & Add -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Thêm thành viên</label>
                    <div class="relative">
                        <input v-model="memberSearch" @input="searchMembers" type="text"
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Tìm tên tín hữu...">
                        <div v-if="memberSearchResults.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                            <div v-for="m in memberSearchResults" :key="m.id" @click="selectMember(m)" class="px-3 py-2.5 hover:bg-indigo-50 cursor-pointer text-sm font-medium border-b border-gray-100 last:border-0">
                                {{ m.full_name }} <span class="text-gray-400 text-xs ml-1">{{ m.phone ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="selectedMember" class="mt-2 flex items-center gap-2 p-2.5 bg-indigo-50 rounded-xl">
                        <span class="flex-1 text-sm font-bold text-indigo-800">{{ selectedMember.full_name }}</span>
                        <select v-model="newMemberRole" class="text-xs border-indigo-200 rounded-lg font-bold">
                            <option value="student">Học viên</option>
                            <option value="teacher">Giáo viên</option>
                        </select>
                        <button @click="addMember" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-black rounded-lg hover:bg-indigo-700 transition-colors">Thêm</button>
                    </div>
                </div>

                <!-- Teachers -->
                <div v-if="managingClass?.teachers?.length > 0">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Giáo viên ({{ managingClass.teachers.length }})</label>
                    <div class="space-y-1.5">
                        <div v-for="t in managingClass.teachers" :key="t.id" class="flex items-center gap-2 p-2.5 bg-purple-50 rounded-xl">
                            <span class="flex-1 text-sm font-bold text-purple-900">{{ t.full_name }}</span>
                            <span class="text-[11px] bg-purple-200 text-purple-700 px-2 py-0.5 rounded-full font-bold">Giáo viên</span>
                            <button @click="removeMember(t.id)" class="text-red-400 hover:text-red-600 p-1 rounded-lg hover:bg-red-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Students -->
                <div v-if="managingClass?.students_list?.length > 0">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Học viên ({{ managingClass.students_list.length }})</label>
                    <div class="space-y-1.5 max-h-72 overflow-y-auto">
                        <div v-for="s in managingClass.students_list" :key="s.id" class="flex items-center gap-2 p-2.5 bg-blue-50 rounded-xl">
                            <span class="flex-1 text-sm font-medium text-blue-900">{{ s.full_name }}</span>
                            <button @click="removeMember(s.id)" class="text-red-400 hover:text-red-600 p-1 rounded-lg hover:bg-red-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="!managingClass?.teachers?.length && !managingClass?.students_list?.length" class="py-6 text-center text-gray-400 text-sm italic">
                    Lớp chưa có thành viên nào.
                </div>
            </div>
        </SlideOver>

    </PortalLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';

const props = defineProps({
    classes: Array,
    department: Object,
    isAdmin: Boolean,
    portalType: String,
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
    allMembers: Array,
});

// ── Class Create/Edit ────────────────────────────────────────────────
const isFormOpen = ref(false);
const editingClass = ref(null);
const form = useForm({ name: '', description: '', class_type: 'sunday_school' });

const openCreateForm = () => {
    editingClass.value = null;
    form.reset();
    isFormOpen.value = true;
};

const openEditForm = (cls) => {
    editingClass.value = cls;
    form.name = cls.name;
    form.description = cls.description || '';
    form.class_type = cls.class_type || 'sunday_school';
    isFormOpen.value = true;
};

// Helper: translate class_type to Vietnamese label
const classTypeLabel = (type) => {
    const labels = { sunday_school: 'Lớp Trường CN', gospel: 'Giáo Lý', bible_quiz: 'Trắc Nghiệm' };
    return labels[type] ?? type;
};

const submitForm = () => {
    if (editingClass.value) {
        form.put(route('education.update', editingClass.value.id), {
            preserveScroll: true,
            onSuccess: () => { isFormOpen.value = false; },
        });
    } else {
        form.post(route('education.store'), {
            preserveScroll: true,
            onSuccess: () => { isFormOpen.value = false; form.reset(); },
        });
    }
};

// ── Members Management ───────────────────────────────────────────────
const isMembersOpen = ref(false);
const managingClass = ref(null);
const memberSearch = ref('');
const memberSearchResults = ref([]);
const selectedMember = ref(null);
const newMemberRole = ref('student');

const openMembersPanel = (cls) => {
    managingClass.value = { ...cls };
    memberSearch.value = '';
    selectedMember.value = null;
    isMembersOpen.value = true;
};

const searchMembers = () => {
    if (!memberSearch.value || memberSearch.value.length < 2) {
        memberSearchResults.value = [];
        return;
    }
    const q = memberSearch.value.toLowerCase();
    const existingIds = [
        ...(managingClass.value?.teachers || []).map(t => t.id),
        ...(managingClass.value?.students_list || []).map(s => s.id),
    ];
    memberSearchResults.value = (props.allMembers || [])
        .filter(m => m.full_name.toLowerCase().includes(q) && !existingIds.includes(m.id))
        .slice(0, 8);
};

const selectMember = (m) => {
    selectedMember.value = m;
    memberSearch.value = m.full_name;
    memberSearchResults.value = [];
};

const addMember = () => {
    if (!selectedMember.value) return;
    router.post(route('education.members.store', managingClass.value.id), {
        member_id: selectedMember.value.id,
        role: newMemberRole.value,
        joined_at: new Date().toISOString().split('T')[0],
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            const updated = page.props.classes?.find(c => c.id === managingClass.value.id);
            if (updated) managingClass.value = { ...updated };
            selectedMember.value = null;
            memberSearch.value = '';
        },
    });
};

const removeMember = (memberId) => {
    if (!confirm('Xóa thành viên này khỏi lớp?')) return;
    router.delete(route('education.members.destroy', [managingClass.value.id, memberId]), {
        preserveScroll: true,
        onSuccess: (page) => {
            const updated = page.props.classes?.find(c => c.id === managingClass.value.id);
            if (updated) managingClass.value = { ...updated };
        },
    });
};
</script>
