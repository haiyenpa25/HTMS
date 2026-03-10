<template>
    <PortalLayout :department="department" :available-departments="availableDepartments" :is-global-admin="isGlobalAdmin" portal-type="education">
        <div class="py-6 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                    <Link :href="route(routePrefix + '.index')" class="p-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </Link>
                    <div>
                        <h1 class="text-xl font-black text-gray-900">Qu?n L� L?p H?c</h1>
                        <p class="text-sm text-gray-500">{{ classes.length }} l?p dang ho?t d?ng</p>
                    </div>
                    </div>
                <button v-if="isAdmin" @click="openCreateForm" class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white rounded-xl shadow-sm hover:from-indigo-700 hover:to-indigo-600 transition-all font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    T?o L?p M?i
                </button>
            </div>

            <!-- Empty state -->
            <div v-if="classes.length === 0" class="py-20 flex flex-col items-center text-center bg-white rounded-2xl border border-gray-100 shadow-sm">
                <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-700 mb-1">Chua c� l?p h?c n�o</h3>
                <p class="text-sm text-gray-400 mb-4">Nh? qu?n tr? vi�n t?o l?p h?c d? b?t d?u.</p>
                <button v-if="isAdmin" @click="openCreateForm" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-colors">T?o L?p �?u Ti�n</button>
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
                                <div class="text-[10px] text-blue-500 font-medium">H?c vi�n</div>
                            </div>
                            <div class="text-center bg-purple-50 rounded-xl py-2">
                                <div class="text-base font-black text-purple-700">{{ cls.teachers.length }}</div>
                                <div class="text-[10px] text-purple-500 font-medium">Gi�o vi�n</div>
                            </div>
                            <div class="text-center bg-indigo-50 rounded-xl py-2">
                                <div class="text-base font-black text-indigo-700">{{ cls.session_count }}</div>
                                <div class="text-[10px] text-indigo-500 font-medium">Bu?i h?c</div>
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
                        <Link :href="route(routePrefix + '.sessions', cls.id)"
                            class="flex-1 text-center text-sm font-black py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-600 transition-all shadow-sm flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Qu?n l� bu?i h?c
                        </Link>
                        <button v-if="isAdmin" @click="openMembersPanel(cls)" class="px-3 py-2 border border-indigo-200 text-indigo-600 rounded-xl hover:bg-indigo-50 text-sm font-bold transition-colors" title="Qu?n l� th�nh vi�n">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </button>
                        <button v-if="isAdmin" @click="openEditForm(cls)" class="px-3 py-2 border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 text-sm font-bold transition-colors" title="S?a l?p">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button v-if="isAdmin" @click="deleteClass(cls)" class="px-3 py-2 border border-red-200 text-red-500 rounded-xl hover:bg-red-50 text-sm font-bold transition-colors" title="X�a l?p">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Class SlideOver -->
        <SlideOver v-model="isFormOpen" :title="editingClass ? 'S?a L?p H?c' : 'T?o L?p H?c M?i'">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">T�n l?p <span class="text-red-500">*</span></label>
                    <input v-model="form.name" type="text" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="VD: L?p Trung L�o 2025">
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Lo?i l?p <span class="text-red-500">*</span></label>
                    <select v-model="form.class_type" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="sunday_school">?? L?p Tru?ng Ch�a Nh?t (di?m danh + ti?n d�ng)</option>
                        <option value="gospel">?? L?p Gi�o L� / Phu?c �m (ch? di?m danh)</option>
                        <option value="bible_quiz">?? Tr?c Nghi?m Kinh Th�nh (di?m danh + ch?m di?m)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">M� t?</label>
                    <textarea v-model="form.description" rows="3" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="M� t? l?p h?c..."></textarea>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3 w-full">
                    <button @click="isFormOpen = false" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50">H?y</button>
                    <button @click="submitForm" :disabled="form.processing" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white rounded-xl text-sm font-bold hover:from-indigo-700 hover:to-indigo-600 disabled:opacity-50">
                        {{ form.processing ? '�ang luu...' : (editingClass ? 'C?p nh?t' : 'T?o l?p') }}
                    </button>
                </div>
            </template>
        </SlideOver>

        <!-- Members Management SlideOver -->
        <SlideOver v-model="isMembersOpen" :title="'Th�nh vi�n: ' + (managingClass?.name ?? '')">
            <div class="space-y-4">

                <!-- Tab switcher: T�m ki?m / Th�m h�ng lo?t -->
                <div class="flex bg-gray-100 rounded-xl p-1">
                    <button @click="memberTab = 'search'"
                        :class="memberTab === 'search' ? 'bg-white shadow text-indigo-700 font-black' : 'text-gray-500 hover:text-gray-700'"
                        class="flex-1 text-sm py-1.5 rounded-lg transition-all font-bold">?? T�m ki?m</button>
                    <button @click="memberTab = 'bulk'"
                        :class="memberTab === 'bulk' ? 'bg-white shadow text-indigo-700 font-black' : 'text-gray-500 hover:text-gray-700'"
                        class="flex-1 text-sm py-1.5 rounded-lg transition-all font-bold">?? Th�m h�ng lo?t</button>
                </div>

                <!-- TAB: T�M KI?M 1 NGU?I -->
                <div v-if="memberTab === 'search'">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Th�m th�nh vi�n</label>
                    <div class="relative">
                        <input v-model="memberSearch" @input="searchMembers" type="text"
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="T�m t�n t�n h?u...">
                        <div v-if="memberSearchResults.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                            <div v-for="m in memberSearchResults" :key="m.id" @click="selectMember(m)" class="px-3 py-2.5 hover:bg-indigo-50 cursor-pointer text-sm font-medium border-b border-gray-100 last:border-0 flex items-center gap-2">
                                <span class="flex-1">{{ m.full_name }}</span>
                                <span class="text-gray-400 text-xs">{{ m.phone ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="selectedMember" class="mt-2 flex items-center gap-2 p-2.5 bg-indigo-50 rounded-xl">
                        <span class="flex-1 text-sm font-bold text-indigo-800">{{ selectedMember.full_name }}</span>
                        <select v-model="newMemberRole" class="text-xs border-indigo-200 rounded-lg font-bold">
                            <option value="student">H?c vi�n</option>
                            <option value="teacher">Gi�o vi�n</option>
                        </select>
                        <button @click="addMember" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-black rounded-lg hover:bg-indigo-700 transition-colors">Th�m</button>
                    </div>
                </div>

                <!-- TAB: TH�M H�NG LO?T -->
                <div v-if="memberTab === 'bulk'" class="space-y-3">
                    <!-- B? l?c -->
                    <div class="space-y-2">
                        <input v-model="bulkSearch" type="text" placeholder="?? L?c theo t�n..."
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <div class="grid grid-cols-2 gap-2">
                            <select v-model="bulkFilterDept"
                                class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">T?t c? ban ng�nh</option>
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                            <select v-model="bulkFilterType"
                                class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">T?t c? lo?i t�n h?u</option>
                                <option value="chinh_thuc">Ch�nh th?c</option>
                                <option value="chua_chinh_thuc">Chua ch�nh th?c</option>
                                <option value="than_huu">Th�n h?u</option>
                                <option value="tin_huu_ht_khac">T�n h?u HT kh�c</option>
                            </select>
                        </div>
                    </div>

                    <!-- Ch?n role -->
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-gray-500 shrink-0">Vai tr�:</label>
                        <div class="flex bg-gray-100 rounded-lg p-0.5 flex-1">
                            <button @click="bulkRole = 'student'"
                                :class="bulkRole === 'student' ? 'bg-white shadow text-indigo-700' : 'text-gray-500'"
                                class="flex-1 text-xs py-1 rounded-md font-bold transition-all">H?c vi�n</button>
                            <button @click="bulkRole = 'teacher'"
                                :class="bulkRole === 'teacher' ? 'bg-white shadow text-indigo-700' : 'text-gray-500'"
                                class="flex-1 text-xs py-1 rounded-md font-bold transition-all">Gi�o vi�n</button>
                        </div>
                    </div>

                    <!-- S? lu?ng & ch?n t?t c? -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">{{ filteredForBulk.length }} t�n h?u � �ang ch?n: <strong class="text-indigo-700">{{ bulkSelected.size }}</strong></span>
                        <div class="flex gap-2">
                            <button @click="selectAllBulk" class="text-xs text-indigo-600 font-bold hover:underline">Ch?n t?t c?</button>
                            <button @click="clearBulk" class="text-xs text-gray-400 hover:text-gray-600 font-bold">B? ch?n</button>
                        </div>
                    </div>

                    <!-- Danh s�ch t�n h?u (checkboxes) -->
                    <div class="border border-gray-200 rounded-xl overflow-hidden max-h-64 overflow-y-auto">
                        <div v-if="filteredForBulk.length === 0" class="py-6 text-center text-gray-400 text-sm">Kh�ng t�m th?y t�n h?u.</div>
                        <div v-for="m in filteredForBulk" :key="m.id"
                            @click="toggleBulkSelect(m.id)"
                            class="flex items-center gap-3 px-3 py-2.5 border-b border-gray-100 last:border-0 cursor-pointer hover:bg-indigo-50/50 transition-colors"
                            :class="bulkSelected.has(m.id) ? 'bg-indigo-50' : ''">
                            <input type="checkbox" :checked="bulkSelected.has(m.id)" class="w-4 h-4 text-indigo-600 rounded border-gray-300 pointer-events-none">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-gray-900">{{ m.full_name }}</div>
                                <div class="text-xs text-gray-400">{{ memberTypeLabel(m.member_type) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- N�t th�m h�ng lo?t -->
                    <button @click="bulkAddMembers" :disabled="bulkSelected.size === 0 || bulkLoading"
                        class="w-full py-3 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white font-black rounded-xl hover:from-indigo-700 hover:to-indigo-600 transition-all disabled:opacity-50 flex items-center justify-center gap-2 text-sm">
                        <svg v-if="bulkLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ bulkLoading ? '�ang th�m...' : `+ Th�m ${bulkSelected.size} th�nh vi�n` }}
                    </button>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <!-- Gi�o vi�n -->
                    <div v-if="managingClass?.teachers?.length > 0" class="mb-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Gi�o vi�n ({{ managingClass.teachers.length }})</label>
                        <div class="space-y-1.5">
                            <div v-for="t in managingClass.teachers" :key="t.id" class="flex items-center gap-2 p-2.5 bg-purple-50 rounded-xl">
                                <span class="flex-1 text-sm font-bold text-purple-900">{{ t.full_name }}</span>
                                <span class="text-[11px] bg-purple-200 text-purple-700 px-2 py-0.5 rounded-full font-bold">Gi�o vi�n</span>
                                <button @click="removeMember(t.id)" class="text-red-400 hover:text-red-600 p-1 rounded-lg hover:bg-red-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- H?c vi�n -->
                    <div v-if="managingClass?.students_list?.length > 0">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">H?c vi�n ({{ managingClass.students_list.length }})</label>
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
                        L?p chua c� th�nh vi�n n�o.
                    </div>
                </div>
            </div>
        </SlideOver>

    </PortalLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import PortalLayout from '@/Layouts/PortalLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';

const props = defineProps({
    classes: Array,
    department: Object,
    isAdmin: Boolean,
    portalType: String,
    routePrefix: { type: String, default: 'education' },
    availableDepartments: Array,
    isGlobalAdmin: Boolean,
    allMembers: Array,
    departments: { type: Array, default: () => [] },
});


// -- Class Create/Edit ------------------------------------------------
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
    const labels = { sunday_school: 'L?p Tru?ng CN', gospel: 'Gi�o L�', bible_quiz: 'Tr?c Nghi?m' };
    return labels[type] ?? type;
};

const memberTypeLabel = (type) => {
    const labels = {
        chinh_thuc: 'Ch�nh th?c',
        chua_chinh_thuc: 'Chua ch�nh th?c',
        than_huu: 'Th�n h?u',
        tin_huu_ht_khac: 'T�n h?u HT kh�c',
    };
    return labels[type] ?? (type || '�');
};

const submitForm = () => {
    if (editingClass.value) {
        form.put(route(props.routePrefix + '.update', editingClass.value.id), {

            preserveScroll: true,
            onSuccess: () => { isFormOpen.value = false; },
        });
    } else {
        form.post(route(props.routePrefix + '.store'), {

            preserveScroll: true,
            onSuccess: () => { isFormOpen.value = false; form.reset(); },
        });
    }
};

// -- Members Management -----------------------------------------------
const isMembersOpen = ref(false);
const managingClass = ref(null);
const memberSearch = ref('');
const memberSearchResults = ref([]);
const selectedMember = ref(null);
const newMemberRole = ref('student');
const memberTab = ref('search'); // 'search' | 'bulk'

// -- Bulk add state ----------------------------------------------------
const bulkSearch = ref('');
const bulkFilterDept = ref('');
const bulkFilterType = ref('');
const bulkRole = ref('student');
const bulkSelected = ref(new Set());
const bulkLoading = ref(false);

// Danh s�ch t�n h?u d� l?c cho bulk (lo?i ra nh?ng ngu?i d� trong l?p)
const filteredForBulk = computed(() => {
    const existingIds = new Set([
        ...(managingClass.value?.teachers || []).map(t => t.id),
        ...(managingClass.value?.students_list || []).map(s => s.id),
    ]);
    const deptFilter = bulkFilterDept.value ? Number(bulkFilterDept.value) : null;
    return (props.allMembers || []).filter(m => {
        if (existingIds.has(m.id)) return false;
        if (bulkSearch.value && !m.full_name.toLowerCase().includes(bulkSearch.value.toLowerCase())) return false;
        if (deptFilter && !(m.department_ids || []).includes(deptFilter)) return false;
        if (bulkFilterType.value && m.member_type !== bulkFilterType.value) return false;
        return true;
    });
});

const toggleBulkSelect = (id) => {
    const next = new Set(bulkSelected.value);
    if (next.has(id)) next.delete(id);
    else next.add(id);
    bulkSelected.value = next;
};

const selectAllBulk = () => {
    bulkSelected.value = new Set(filteredForBulk.value.map(m => m.id));
};

const clearBulk = () => {
    bulkSelected.value = new Set();
};

const bulkAddMembers = () => {
    if (bulkSelected.value.size === 0) return;
    bulkLoading.value = true;
    router.post(route(props.routePrefix + '.members.bulk-store', managingClass.value.id), {

        member_ids: Array.from(bulkSelected.value),
        role: bulkRole.value,
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            const updated = page.props.classes?.find(c => c.id === managingClass.value.id);
            if (updated) managingClass.value = { ...updated };
            bulkSelected.value = new Set();
        },
        onFinish: () => { bulkLoading.value = false; },
    });
};

const openMembersPanel = (cls) => {
    managingClass.value = { ...cls };
    memberSearch.value = '';
    selectedMember.value = null;
    memberTab.value = 'search';
    bulkSearch.value = '';
    bulkFilterDept.value = '';
    bulkFilterType.value = '';
    bulkSelected.value = new Set();
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
    router.post(route(props.routePrefix + '.members.store', managingClass.value.id), {

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
    if (!confirm('X�a th�nh vi�n n�y kh?i l?p?')) return;
    router.delete(route(props.routePrefix + '.members.destroy', [managingClass.value.id, memberId]), {

        preserveScroll: true,
        onSuccess: (page) => {
            const updated = page.props.classes?.find(c => c.id === managingClass.value.id);
            if (updated) managingClass.value = { ...updated };
        },
    });
};

const deleteClass = (cls) => {
    if (!confirm(`B?n c� ch?c mu?n x�a l?p "${cls.name}"?\n\nLuu �: T?t c? b�i h?c, di?m danh v� th�nh vi�n trong l?p n�y s? b? x�a vinh vi?n.`)) return;
    router.delete(route(props.routePrefix + '.destroy', cls.id), {

        preserveScroll: true,
    });
};
</script>
