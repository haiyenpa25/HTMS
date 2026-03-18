<template>
  <div class="space-y-6">
    <!-- Block 1: Hộ Gia Đình -->
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-blue-100 bg-blue-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="font-bold text-blue-900 text-sm flex items-center">
          <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
          Thành viên Cùng Hộ Gia Đình
        </h3>
        <div class="flex items-center gap-2">
          <span v-if="member.household" class="text-xs font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-md truncate max-w-[200px]">
            Hộ: {{ member.household.name || 'Gia đình vô danh' }}
          </span>
          <button v-if="isPastor && member.household" @click="showAddMemberModal = true" class="px-2.5 py-1.5 text-xs font-bold bg-white text-blue-600 border border-blue-200 hover:bg-blue-50 rounded-lg transition-colors whitespace-nowrap shadow-sm">
            + Ghép thành viên
          </button>
        </div>
      </div>
      
      <div v-if="member.household && member.household.members" class="p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="m in member.household.members" :key="'hh-'+m.id" 
            class="flex items-center justify-between p-3 rounded-xl border transition-colors"
            :class="m.id === member.household.head_member_id ? 'bg-amber-50 border-amber-200' : 'bg-gray-50 border-gray-100 hover:border-blue-200'">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-sm shrink-0 shadow-sm"
                :class="m.id === member.household.head_member_id ? 'bg-gradient-to-tr from-amber-400 to-yellow-300 text-yellow-900' : 'bg-white text-gray-700 border border-gray-200'">
                {{ (m.full_name || 'U').charAt(0) }}
              </div>
              <div class="min-w-0 truncate">
                <a :href="route('members.show', m.id)" class="text-sm font-bold text-gray-900 hover:text-blue-600 truncate block transition-colors">
                  {{ m.full_name }}
                  <span v-if="m.id === member.id" class="text-xs text-blue-500 ml-1">(Đang xem)</span>
                </a>
                <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-2">
                  <span>{{ m.member_code }}</span>
                  <span v-if="m.id === member.household.head_member_id" class="px-1.5 py-0.5 bg-amber-200 text-amber-800 rounded font-black uppercase tracking-widest text-[9px]">Chủ Hộ</span>
                </div>
              </div>
            </div>
            
            <div class="flex items-center gap-1">
              <button v-if="isPastor && m.id !== member.household.head_member_id" 
                @click="setHead(m.id)"
                class="px-2.5 py-1.5 text-[11px] font-bold text-amber-700 bg-white border border-amber-200 hover:bg-amber-100 rounded-lg transition-colors whitespace-nowrap shadow-sm">
                Làm Chủ hộ
              </button>
              <button v-if="isPastor" @click="removeFromHousehold(m.id)"
                class="p-1.5 text-red-500 hover:bg-red-50 hover:text-red-600 rounded-lg transition-colors" title="Bỏ khỏi Hộ">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="p-8 pb-10 flex flex-col items-center justify-center">
        <div class="text-sm text-gray-400 italic mb-4">Thành viên này chưa được xếp vào Hộ Gia Đình nào.</div>
        <button v-if="isPastor" @click="createHousehold" class="px-5 py-2.5 text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-md shadow-blue-200 transition-all flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Khởi tạo Hộ gia đình mới
        </button>
      </div>
    </div>

    <!-- Block 2: Quan hệ Tín hữu -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
        <h3 class="font-bold text-gray-900 text-sm flex items-center">
          <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Sơ đồ Gia Phả / Huyết Thống
        </h3>
        <button v-if="isPastor" @click="showRelModal = true" class="px-2.5 py-1.5 text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg transition-colors">
          + Thêm quan hệ
        </button>
      </div>

      <div class="p-5">
        <div v-if="hasRelationships" class="py-2">
          
          <!-- Tree Container -->
          <div class="flex flex-col items-center gap-6">
            
            <!-- Gen -2: Ông Bà -->
            <div v-if="genMinus2.length" class="flex flex-col items-center gap-3">
              <div class="flex flex-wrap justify-center gap-4">
                <div v-for="rel in genMinus2" :key="rel.id" class="relative group">
                  <div class="w-28 h-auto p-2 border-2 border-slate-200 bg-white rounded-xl shadow-sm text-center hover:border-slate-400 transition-colors">
                    <button v-if="isPastor" @click="deleteRel(rel.id)" class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    <div class="text-[10px] font-black uppercase text-slate-600 mb-1 tracking-widest">{{ rel.resolved_type }}</div>
                    <a :href="route('members.show', rel.id)" class="text-xs font-bold text-gray-900 leading-tight block truncate" :title="rel.full_name">{{ rel.full_name }}</a>
                  </div>
                </div>
              </div>
              <div class="w-px h-6 bg-slate-200"></div>
            </div>

            <!-- Gen -1: Cha Mẹ -->
            <div v-if="genMinus1.length" class="flex flex-col items-center gap-3">
              <div class="flex flex-wrap justify-center gap-4">
                <div v-for="rel in genMinus1" :key="rel.id" class="relative group">
                  <div class="w-32 h-auto p-2.5 border-2 border-indigo-200 bg-white rounded-xl shadow-sm text-center hover:border-indigo-400 transition-colors">
                    <button v-if="isPastor" @click="deleteRel(rel.id)" class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    <div class="text-[10px] font-black uppercase text-indigo-600 mb-1 tracking-widest">{{ rel.resolved_type }}</div>
                    <a :href="route('members.show', rel.id)" class="text-xs font-bold text-gray-900 leading-tight block truncate" :title="rel.full_name">{{ rel.full_name }}</a>
                  </div>
                </div>
              </div>
              <div class="w-px h-6 bg-indigo-200"></div>
            </div>

            <!-- Gen 0: Bản thân + Vợ chồng + Anh chị em -->
            <div class="flex flex-col items-center gap-3 w-full max-w-2xl px-2">
              <div class="flex flex-wrap justify-center items-center gap-4 p-4 rounded-3xl bg-indigo-50 border border-indigo-100 w-full relative">
                
                <!-- Siblings (Anh/Chị) -->
                <div v-for="rel in genZeroOld" :key="rel.id" class="relative group">
                  <div class="w-28 h-auto p-2 border border-blue-200 bg-white rounded-xl shadow-sm text-center">
                    <button v-if="isPastor" @click="deleteRel(rel.id)" class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 opacity-0 group-hover:opacity-100"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    <div class="text-[9px] font-black uppercase text-blue-500 mb-0.5">{{ rel.resolved_type }}</div>
                    <a :href="route('members.show', rel.id)" class="text-xs font-bold text-gray-800 leading-tight block truncate" :title="rel.full_name">{{ rel.full_name }}</a>
                  </div>
                </div>

                <!-- Self -->
                <div class="w-36 h-auto p-3 border-2 border-blue-500 bg-white rounded-2xl shadow-md text-center transform scale-105 z-10 flex-shrink-0">
                  <div class="text-[10px] font-black uppercase text-blue-600 mb-1 tracking-widest">Bản thân</div>
                  <div class="text-sm font-black text-gray-900 leading-tight truncate" :title="member.full_name">{{ member.full_name }}</div>
                </div>

                <!-- Spouse -->
                <div v-for="rel in genZeroSpouse" :key="rel.id" class="relative group">
                  <div class="w-32 h-auto p-2 border-2 border-pink-300 bg-pink-50 rounded-2xl shadow-sm text-center relative ml-3">
                    <div class="absolute -left-6 top-1/2 -mt-2.5 text-pink-400 bg-white rounded-full p-0.5"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></div>
                    <button v-if="isPastor" @click="deleteRel(rel.id)" class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 opacity-0 group-hover:opacity-100"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    <div class="text-[10px] font-black uppercase text-pink-600 mb-1 tracking-widest">{{ rel.resolved_type }}</div>
                    <a :href="route('members.show', rel.id)" class="text-xs font-bold text-gray-900 leading-tight block truncate" :title="rel.full_name">{{ rel.full_name }}</a>
                  </div>
                </div>

                <!-- Siblings (Em) -->
                <div v-for="rel in genZeroYoung" :key="rel.id" class="relative group">
                  <div class="w-28 h-auto p-2 border border-blue-200 bg-white rounded-xl shadow-sm text-center">
                    <button v-if="isPastor" @click="deleteRel(rel.id)" class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 opacity-0 group-hover:opacity-100"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    <div class="text-[9px] font-black uppercase text-blue-500 mb-0.5">{{ rel.resolved_type }}</div>
                    <a :href="route('members.show', rel.id)" class="text-xs font-bold text-gray-800 leading-tight block truncate" :title="rel.full_name">{{ rel.full_name }}</a>
                  </div>
                </div>
              </div>
              
              <div v-if="genPlus1.length || genPlus2.length" class="w-px h-6 bg-indigo-200"></div>
            </div>

            <!-- Gen +1: Các Con -->
            <div v-if="genPlus1.length" class="flex flex-col items-center gap-3">
              <div class="flex flex-wrap justify-center gap-4">
                <div v-for="rel in genPlus1" :key="rel.id" class="relative group">
                  <div class="w-28 h-auto p-2 border-2 border-green-200 bg-white rounded-xl shadow-sm text-center hover:border-green-400 transition-colors">
                    <button v-if="isPastor" @click="deleteRel(rel.id)" class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    <div class="text-[10px] font-black uppercase text-green-600 mb-1 tracking-widest">{{ rel.resolved_type }}</div>
                    <a :href="route('members.show', rel.id)" class="text-xs font-bold text-gray-900 leading-tight block truncate" :title="rel.full_name">{{ rel.full_name }}</a>
                  </div>
                </div>
              </div>
              <div v-if="genPlus2.length" class="w-px h-6 bg-green-200"></div>
            </div>

            <!-- Gen +2: Cháu -->
            <div v-if="genPlus2.length" class="flex flex-col items-center gap-3">
              <div class="flex flex-wrap justify-center gap-4">
                <div v-for="rel in genPlus2" :key="rel.id" class="relative group">
                  <div class="w-24 h-auto p-2 border border-green-300 bg-green-50 rounded-xl shadow-sm text-center">
                    <button v-if="isPastor" @click="deleteRel(rel.id)" class="absolute -top-2 -right-2 bg-red-100 text-red-600 rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    <div class="text-[9px] font-black uppercase text-green-700 mb-0.5 tracking-widest">{{ rel.resolved_type }}</div>
                    <a :href="route('members.show', rel.id)" class="text-[11px] font-bold text-gray-800 leading-tight block truncate" :title="rel.full_name">{{ rel.full_name }}</a>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Other relations -->
             <div v-if="genOther.length" class="flex flex-wrap justify-center gap-3 mt-4 pt-4 border-t border-gray-100 w-full">
                <div v-for="rel in genOther" :key="rel.id" class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 border text-xs relative group pr-6">
                    <span class="font-black text-gray-500">{{ rel.resolved_type }}:</span>
                    <a :href="route('members.show', rel.id)" class="font-bold text-gray-800 hover:text-blue-600 truncate max-w-[120px]">{{ rel.full_name }}</a>
                    <button v-if="isPastor" @click="deleteRel(rel.id)" class="absolute top-1/2 -mt-3 right-1 bg-red-100 text-red-600 rounded-md p-1 opacity-0 group-hover:opacity-100"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
             </div>

          </div>
        </div>
        <div v-else class="text-center py-8 text-sm text-gray-400 italic">
          Chưa khai báo dòng tộc / gia phả. Sơ đồ cây sẽ hiện ở đây.
        </div>
      </div>
    </div>

    <!-- Modal Thêm Quan Hệ -->
    <div v-if="showRelModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="font-bold text-gray-900 text-lg">Thiết lập quan hệ</h3>
          <button @click="showRelModal = false" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <form @submit.prevent="submitRel" class="p-6">
          <div class="space-y-4">
            
            <!-- Tìm kiếm tín hữu -->
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1.5">Mã Tín hữu hoặc SĐT người liên quan</label>
              <div class="flex gap-2">
                <input type="text" v-model="searchQuery" placeholder="Nhập để tìm..." 
                  class="flex-1 text-sm border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-indigo-500" @keyup.enter.prevent="searchMembers">
                <button type="button" @click="searchMembers" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition-colors">Tìm</button>
              </div>
              <div v-if="searchResults.length" class="mt-2 border rounded-xl divide-y overflow-hidden max-h-40 overflow-y-auto">
                <div v-for="res in searchResults" :key="res.id" @click="selectMember(res)"
                  class="p-2 text-sm hover:bg-indigo-50 cursor-pointer flex justify-between items-center transition-colors"
                  :class="formRel.related_member_id === res.id ? 'bg-indigo-100 border-l-2 border-indigo-500' : 'bg-white'">
                  <span class="font-bold text-gray-900">{{ res.full_name }}</span>
                  <span class="text-xs text-gray-500">{{ res.member_code }}</span>
                </div>
              </div>
             </div>

            <div v-if="formRel.related_member_id" class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Là (...) của tín hữu này</label>
                <select v-model="formRel.type" required class="w-full text-sm border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                  <option value="Cha">Cha</option>
                  <option value="Mẹ">Mẹ</option>
                  <option value="Vợ">Vợ</option>
                  <option value="Chồng">Chồng</option>
                  <option value="Con trai">Con trai</option>
                  <option value="Con gái">Con gái</option>
                  <option value="Anh trai">Anh trai</option>
                  <option value="Chị gái">Chị gái</option>
                  <option value="Em trai">Em trai</option>
                  <option value="Em gái">Em gái</option>
                  <option value="Ông">Ông</option>
                  <option value="Bà">Bà</option>
                  <option value="Cháu">Cháu</option>
                  <option value="Khác">Khác</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5 text-center px-2 py-0.5 rounded bg-amber-100 text-amber-800">
                  Phản xạ tự động
                </label>
                <select v-model="formRel.inverse_type" class="w-full text-sm border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500" title="Tự động gán chiều ngược lại cho người kia">
                  <option value="">-- Không cần --</option>
                  <option value="Con trai">Con trai</option>
                  <option value="Con gái">Con gái</option>
                  <option value="Cha">Cha</option>
                  <option value="Mẹ">Mẹ</option>
                  <option value="Vợ">Vợ</option>
                  <option value="Chồng">Chồng</option>
                  <option value="Anh trai">Anh trai</option>
                  <option value="Chị gái">Chị gái</option>
                  <option value="Em trai">Em trai</option>
                  <option value="Em gái">Em gái</option>
                </select>
              </div>
              <div class="col-span-2 pt-3 border-t border-gray-100 mt-1">
                <label class="flex items-center gap-2.5 cursor-pointer group">
                  <input type="checkbox" v-model="formRel.sync_household" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 transition-all">
                  <span class="text-xs font-bold text-gray-700 group-hover:text-indigo-700 transition-colors">Tự động mang người này vào cùng Hộ Gia Đình hiện tại</span>
                </label>
              </div>
            </div>
          </div>

          <div class="mt-6 pt-5 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" @click="showRelModal = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
              Hủy
            </button>
            <button type="submit" :disabled="formRel.processing || !formRel.related_member_id" class="px-5 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-200 transition-all disabled:opacity-50">
              Lưu Quan Hệ
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Ghép Thành Viên Vào Hộ -->
    <div v-if="showAddMemberModal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-blue-50/50">
          <h3 class="font-bold text-blue-900 text-lg">Ghép thành viên vào Hộ</h3>
          <button @click="showAddMemberModal = false" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <form @submit.prevent="submitAddMember" class="p-6">
          <div class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1.5">Trưởng nhóm Tín hữu cần ghép (Mã HOẶC Tên)</label>
              <div class="flex gap-2">
                <input type="text" v-model="searchHHQuery" placeholder="Nhập để tìm..." 
                  class="flex-1 text-sm border-gray-300 rounded-xl focus:border-blue-500 focus:ring-blue-500" @keyup.enter.prevent="searchHHMembers">
                <button type="button" @click="searchHHMembers" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition-colors">Tìm</button>
              </div>
              <div v-if="searchHHResults.length" class="mt-2 border rounded-xl divide-y overflow-hidden max-h-40 overflow-y-auto">
                <div v-for="res in searchHHResults" :key="'hh-'+res.id" @click="selectHHMember(res)"
                  class="p-2 text-sm hover:bg-blue-50 cursor-pointer flex justify-between items-center transition-colors"
                  :class="formHH.member_id === res.id ? 'bg-blue-100 border-l-2 border-blue-500' : 'bg-white'">
                  <span class="font-bold text-gray-900">{{ res.full_name }}</span>
                  <span class="text-xs text-gray-500">{{ res.member_code }}</span>
                </div>
              </div>
             </div>
             
             <div v-if="formHH.member_id" class="p-3 bg-amber-50 rounded-xl border border-amber-100 text-xs text-amber-800 flex items-start gap-2">
               <svg class="w-4 h-4 shrink-0 mt-0.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
               <p>Việc này sẽ đưa thành viên trên vào chung Hộ Gia Đình hiện tại. Thao tác này chỉ quản lý danh sách sống chung, KHÔNG GẮN KẾT phả hệ/huyết thống.</p>
             </div>
          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="showAddMemberModal = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
              Hủy
            </button>
            <button type="submit" :disabled="formHH.processing || !formHH.member_id" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md shadow-blue-200 transition-all disabled:opacity-50">
              Ghép Vào Hộ
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  member: Object,
  isPastor: Boolean
});

const hasRelationships = computed(() => {
  const to = props.member.related_to || [];
  const from = props.member.related_from || [];
  return to.length > 0 || from.length > 0;
});

const getInverseType = (whatIAm, theirGender) => {
  const isFemale = theirGender === 'Nữ';
  switch (whatIAm) {
    case 'Cha':
    case 'Mẹ':
      return isFemale ? 'Con gái' : 'Con trai';
    case 'Con trai':
    case 'Con gái':
      return isFemale ? 'Mẹ' : 'Cha';
    case 'Ông':
    case 'Bà':
      return 'Cháu';
    case 'Cháu':
      return isFemale ? 'Bà' : 'Ông';
    case 'Vợ':
      return 'Chồng';
    case 'Chồng':
      return 'Vợ';
    case 'Anh trai':
    case 'Chị gái':
      return isFemale ? 'Em gái' : 'Em trai';
    case 'Em trai':
    case 'Em gái':
      return isFemale ? 'Chị gái' : 'Anh trai';
    default:
      return 'Khác';
  }
};

const allRelations = computed(() => {
  const result = [];
  
  (props.member.related_to || []).forEach(rel => {
     result.push({ ...rel, resolved_type: rel.pivot.type });
  });
  
  (props.member.related_from || []).forEach(rel => {
     if (!result.find(r => r.id === rel.id)) {
        result.push({ ...rel, resolved_type: getInverseType(rel.pivot.type, rel.gender) });
     }
  });

  return result;
});

const genMinus2 = computed(() => allRelations.value.filter(r => ['Ông', 'Bà'].includes(r.resolved_type)));
const genMinus1 = computed(() => allRelations.value.filter(r => ['Cha', 'Mẹ'].includes(r.resolved_type)));
const genZero = computed(() => allRelations.value.filter(r => ['Vợ', 'Chồng', 'Anh trai', 'Chị gái', 'Em trai', 'Em gái'].includes(r.resolved_type)));
const genZeroSpouse = computed(() => genZero.value.filter(r => ['Vợ', 'Chồng'].includes(r.resolved_type)));
const genZeroOld = computed(() => genZero.value.filter(r => ['Anh trai', 'Chị gái'].includes(r.resolved_type)));
const genZeroYoung = computed(() => genZero.value.filter(r => ['Em trai', 'Em gái'].includes(r.resolved_type)));
const genPlus1 = computed(() => allRelations.value.filter(r => ['Con trai', 'Con gái'].includes(r.resolved_type)));
const genPlus2 = computed(() => allRelations.value.filter(r => ['Cháu'].includes(r.resolved_type)));
const genOther = computed(() => allRelations.value.filter(r => !['Ông', 'Bà', 'Cha', 'Mẹ', 'Vợ', 'Chồng', 'Anh trai', 'Chị gái', 'Em trai', 'Em gái', 'Con trai', 'Con gái', 'Cháu'].includes(r.resolved_type)));

// Set Chủ Hộ
const setHead = (headId) => {
  if (!confirm('Đặt người này làm Chủ Hộ cho cả Gia đình?')) return;
  router.put(route('households.set-head', props.member.household.id), {
    head_member_id: headId
  }, { preserveScroll: true });
};

// Relation Modal
const showRelModal = ref(false);
const searchQuery = ref('');
const searchResults = ref([]);

const formRel = useForm({
  related_member_id: '',
  type: 'Cha',
  inverse_type: '',
  sync_household: true
});

const searchMembers = async () => {
  if (!searchQuery.value) return;
  try {
    const res = await axios.get(route('api.members.index', { search: searchQuery.value }));
    searchResults.value = res.data.filter(i => i.id !== props.member.id);
  } catch (error) {
    console.error(error);
  }
};

const selectMember = (memberResult) => {
  formRel.related_member_id = memberResult.id;
  // Gợi ý tự động
  if (['Nam'].includes(memberResult.gender)) formRel.type = 'Cha';
  if (['Nữ'].includes(memberResult.gender)) formRel.type = 'Mẹ';
};

const submitRel = () => {
  formRel.post(route('members.relationships.store', props.member.id), {
    preserveScroll: true,
    onSuccess: () => {
      showRelModal.value = false;
      formRel.reset();
      searchQuery.value = '';
      searchResults.value = [];
    }
  });
};

const deleteRel = (relatedId) => {
  if (confirm('Xóa liên kết này khỏi cây gia phả?')) {
    router.delete(route('members.relationships.destroy', [props.member.id, relatedId]), { preserveScroll: true });
  }
};

// --- HOUSEHOLD ACTIONS ---
const createHousehold = () => {
  if (confirm('Khởi tạo Hộ gia đình với tín hữu này làm Chủ hộ?')) {
    router.post(route('households.store', props.member.id), {}, { preserveScroll: true });
  }
};

const removeFromHousehold = (memberId) => {
  if (confirm('Xác nhận gỡ thành viên này khỏi Hộ gia đình hiện tại?')) {
    router.delete(route('households.remove_member', [props.member.household.id, memberId]), { preserveScroll: true });
  }
};

const showAddMemberModal = ref(false);
const searchHHQuery = ref('');
const searchHHResults = ref([]);
const formHH = useForm({
  member_id: ''
});

const searchHHMembers = async () => {
  if (!searchHHQuery.value) return;
  try {
    const res = await axios.get(route('api.members.index', { search: searchHHQuery.value }));
    // Filter out people who are already in the current household
    searchHHResults.value = res.data.filter(i => 
       !(props.member.household?.members || []).map(m => m.id).includes(i.id)
    );
  } catch (error) {
    console.error(error);
  }
};

const selectHHMember = (res) => {
  formHH.member_id = res.id;
};

const submitAddMember = () => {
  formHH.post(route('households.add_member', props.member.household.id), {
    preserveScroll: true,
    onSuccess: () => {
      showAddMemberModal.value = false;
      formHH.reset();
      searchHHQuery.value = '';
      searchHHResults.value = [];
    }
  });
};
</script>
