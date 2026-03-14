<template>
  <component :is="currentLayout">
    <template #header>Hồ sơ Tín hữu</template>

    <div class="py-4 max-w-6xl mx-auto px-4 sm:px-6 space-y-6">

      <!-- Back + Actions -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <Link :href="route('members.index')" class="flex items-center text-sm text-gray-500 hover:text-blue-600 transition-colors group w-fit">
          <svg class="w-4 h-4 mr-1.5 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          Danh sách Tín hữu
        </Link>
        <div class="flex items-center gap-2">
          <button v-if="!isEditing" @click="startEditing" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 shadow-md shadow-blue-100 transition-all flex items-center">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Chỉnh sửa
          </button>
          <template v-else>
            <button @click="cancelEditing" class="px-4 py-2 bg-white text-gray-600 border border-gray-200 rounded-xl text-sm font-bold hover:bg-gray-50 transition-all">Hủy</button>
            <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-bold hover:bg-green-700 shadow-md shadow-green-100 transition-all">
              {{ form.processing ? 'Đang lưu...' : 'Lưu thay đổi' }}
            </button>
          </template>
        </div>
      </div>

      <!-- Hero Card -->
      <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Banner -->
        <div class="h-32 sm:h-40 bg-gradient-to-br from-indigo-600 via-blue-600 to-sky-500 relative">
          <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%221%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
          <div class="absolute bottom-4 right-5 flex gap-2">
            <span class="px-3 py-1 bg-white/20 backdrop-blur text-white text-[10px] uppercase font-black rounded-full border border-white/30">{{ member.member_code }}</span>
            <span v-if="member.household?.head_member_id === member.id" class="px-3 py-1 bg-amber-500/80 backdrop-blur text-white text-[10px] uppercase font-black rounded-full border border-white/30 shadow-md shadow-amber-500/20">🥇 Chủ Hộ</span>
            <span class="px-3 py-1 rounded-full text-xs font-bold border border-white/30 backdrop-blur"
              :class="member.status === 'Chính thức' ? 'bg-green-500/80 text-white' : 'bg-white/20 text-white'">
              {{ member.status }}
            </span>
          </div>
        </div>

        <div class="px-6 pb-6 sm:px-8 sm:pb-8 relative">
          <div class="flex flex-col sm:flex-row sm:items-end sm:gap-6">
            <!-- Avatar -->
            <div class="-mt-12 sm:-mt-16 w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-white p-1.5 shadow-xl shrink-0">
              <div class="w-full h-full rounded-2xl bg-gradient-to-tr from-blue-100 to-indigo-200 text-blue-700 flex items-center justify-center text-4xl sm:text-5xl font-black">
                {{ (member.full_name || 'T').charAt(0) }}
              </div>
            </div>
            <!-- Info -->
            <div class="mt-4 sm:mt-0 sm:pb-1 flex-1">
              <h1 v-if="!isEditing" class="text-2xl sm:text-3xl font-black text-gray-900 leading-tight mb-2">{{ member.full_name }}</h1>
              <input v-else type="text" v-model="form.full_name" class="w-full text-2xl font-black text-gray-900 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 mb-2 py-1" placeholder="Họ và Tên"/>
              <div class="flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-gray-500">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                  <input v-if="isEditing" type="tel" v-model="form.phone" class="w-32 py-1 text-sm border-gray-300 rounded-md focus:border-blue-500" placeholder="SĐT"/>
                  <template v-else>{{ member.phone || 'Chưa có SĐT' }}</template>
                </div>
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                  <input v-if="isEditing" type="email" v-model="form.email" class="w-48 py-1 text-sm border-gray-300 rounded-md focus:border-blue-500" placeholder="Email"/>
                  <template v-else>{{ member.email || 'Chưa có email' }}</template>
                </div>
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  Gia nhập: {{ member.joined_date ? formatDate(member.joined_date) : '—' }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex items-center gap-1 bg-gray-100/80 p-1.5 rounded-2xl w-fit max-w-full overflow-x-auto no-scrollbar">
        <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
          :class="['px-4 py-2 rounded-xl text-sm font-bold transition-all whitespace-nowrap flex items-center gap-1.5',
            activeTab === tab.id ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-500 hover:text-gray-700']">
          <component :is="tab.icon" class="w-4 h-4"/>
          {{ tab.name }}
        </button>
      </div>

      <!-- Tab Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Main Left -->
        <div class="lg:col-span-2 space-y-6">

          <!-- TAB 1: THÔNG TIN CHUNG -->
          <div v-if="activeTab === 'general'" class="space-y-5">
            <!-- Basic Info Card -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-gray-900 flex items-center text-sm">
                  <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                  Hồ sơ cơ bản
                </h3>
              </div>
              <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Address + Map -->
                <div class="sm:col-span-2">
                  <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Địa chỉ</label>
                  <textarea v-if="isEditing" v-model="form.address" rows="2" class="w-full text-sm font-medium border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Địa chỉ..."></textarea>
                  <p v-else class="text-sm text-gray-900 font-bold">{{ member.address || 'Chưa cập nhật' }}</p>
                </div>

                <!-- Geolocation -->
                <div class="sm:col-span-2 bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-blue-800 flex items-center">
                      <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                      Tọa độ GPS
                    </span>
                    <div class="flex items-center gap-2">
                      <a v-if="member.latitude && member.longitude"
                        :href="`https://www.google.com/maps?q=${member.latitude},${member.longitude}`"
                        target="_blank"
                        class="text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-lg flex items-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        Xem trên Google Maps
                      </a>
                      <a v-if="member.latitude && member.longitude"
                        :href="`https://www.google.com/maps/dir/?api=1&destination=${member.latitude},${member.longitude}`"
                        target="_blank"
                        class="text-xs font-bold text-blue-700 bg-white border border-blue-200 hover:bg-blue-50 px-3 py-1.5 rounded-lg flex items-center gap-1 transition-colors">
                        🗺️ Dẫn đường
                      </a>
                    </div>
                  </div>
                  <div v-if="member.latitude && member.longitude" class="text-xs text-blue-700 font-medium">
                    Lat: <span class="font-black">{{ member.latitude }}</span> &nbsp;|&nbsp; Lng: <span class="font-black">{{ member.longitude }}</span>
                  </div>
                  <div v-else class="text-xs text-gray-400 italic">Chưa có tọa độ GPS. Có thể cập nhật từ phiếu thăm viếng.</div>
                </div>

                <div>
                  <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Ngày sinh</label>
                  <input v-if="isEditing" type="date" v-model="form.date_of_birth" class="w-full text-sm font-medium border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"/>
                  <p v-else class="text-sm font-black text-gray-900">{{ member.date_of_birth ? formatDate(member.date_of_birth) : 'Chưa rõ' }}</p>
                </div>

                <div>
                  <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Giới tính</label>
                  <select v-if="isEditing" v-model="form.gender" class="w-full text-sm font-medium border-gray-300 rounded-lg shadow-sm">
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                  </select>
                  <p v-else class="text-sm font-black text-gray-900">{{ member.gender || '—' }}</p>
                </div>

                <div>
                  <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Ngày tin Chúa</label>
                  <input v-if="isEditing" type="date" v-model="form.faith_date" class="w-full text-sm font-medium border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"/>
                  <p v-else class="text-sm font-black text-blue-700">{{ member.faith_date ? formatDate(member.faith_date) : '—' }}</p>
                </div>

                <div>
                  <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Ngày Báp-têm</label>
                  <input v-if="isEditing" type="date" v-model="form.baptism_date" class="w-full text-sm font-medium border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"/>
                  <p v-else class="text-sm font-black text-purple-700">{{ member.baptism_date ? formatDate(member.baptism_date) : (member.is_baptized ? 'Đã báp-têm' : 'Chưa Báp-têm') }}</p>
                </div>

                <div class="sm:col-span-2">
                  <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1.5 block">Ghi chú chung</label>
                  <textarea v-if="isEditing" v-model="form.general_notes" rows="3" placeholder="Ghi chú về tín hữu..." class="w-full text-sm font-medium border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 resize-none"></textarea>
                  <p v-else class="text-sm text-gray-500 italic">{{ member.general_notes || 'Không có ghi chú.' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 2: NHẬT KÝ CHĂM SÓC (Thăm Viếng) -->
          <div v-if="activeTab === 'history'" class="space-y-4">
            <div class="flex items-center justify-between">
              <h2 class="text-base font-bold text-gray-800">Lịch sử Thăm Viếng ({{ (member.visitations || []).length }})</h2>
            </div>

            <div v-if="!member.visitations || !member.visitations.length"
              class="bg-white rounded-2xl border-2 border-dashed border-gray-200 p-14 text-center">
              <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              <p class="text-sm text-gray-400 font-medium">Chưa có lần thăm viếng nào được ghi nhận.</p>
            </div>

            <!-- Timeline -->
            <div class="relative pl-10 space-y-4 before:absolute before:left-3.5 before:top-2 before:bottom-0 before:w-0.5 before:bg-gray-200">
              <div v-for="(v, idx) in (member.visitations || [])" :key="idx" class="relative group">
                <!-- Dot -->
                <div class="absolute -left-[26px] top-3 w-4 h-4 rounded-full border-2 shadow-sm z-10 transition-transform group-hover:scale-125"
                  :class="{
                    'bg-green-400 border-green-500': v.status === 'completed',
                    'bg-red-400 border-red-500': v.priority === 'high',
                    'bg-blue-400 border-blue-500': v.status === 'planned',
                    'bg-gray-300 border-gray-400': v.status === 'cancelled',
                  }"></div>

                <div class="bg-white rounded-2xl border shadow-sm transition-all hover:shadow-md p-5"
                  :class="{
                    'border-green-100 bg-green-50/30': v.status === 'completed',
                    'border-red-100 bg-red-50/30': v.priority === 'high' && v.status !== 'completed',
                    'border-gray-100': v.status !== 'completed' && v.priority !== 'high',
                  }">
                  <div class="flex items-start justify-between mb-3">
                    <div>
                      <div class="font-bold text-gray-900 text-sm">{{ formatDate(v.visit_date) }}</div>
                      <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold capitalize"
                          :class="v.priority === 'high' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800'">
                          {{ v.reason }}
                          <span v-if="v.priority === 'high'"> ⚠️</span>
                        </span>
                        <span v-if="v.status === 'completed'" class="text-xs px-2 py-0.5 rounded-full font-bold bg-green-100 text-green-800">✓ Hoàn thành</span>
                        <span v-else-if="v.status === 'planned'" class="text-xs px-2 py-0.5 rounded-full font-bold bg-blue-100 text-blue-800">Kế hoạch</span>
                        <span v-else class="text-xs px-2 py-0.5 rounded-full font-bold bg-gray-100 text-gray-600">Đã hủy</span>
                      </div>
                    </div>
                  </div>
                  <div v-if="v.content" class="text-sm text-gray-600 leading-relaxed mb-3">{{ v.content }}</div>
                  <div v-if="v.prayer_points" class="text-xs text-purple-700 bg-purple-50 rounded-lg p-2.5 mb-3 italic">
                    🙏 {{ v.prayer_points }}
                  </div>
                  <div v-if="v.visitors && v.visitors.length" class="flex items-center gap-2 pt-3 border-t border-gray-100">
                    <div class="flex -space-x-1.5">
                      <div v-for="vis in v.visitors.slice(0,5)" :key="vis.id"
                        class="w-6 h-6 rounded-full bg-blue-100 border-2 border-white flex items-center justify-center text-[9px] font-black text-blue-800"
                        :title="vis.full_name">{{ vis.full_name?.charAt(0) }}</div>
                    </div>
                    <span class="text-xs text-gray-500">{{ v.visitors.map(vi => vi.full_name).join(', ') }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 4: GIA ĐÌNH & NGƯỜI THÂN -->
          <div v-if="activeTab === 'family'" class="space-y-4">
             <FamilyTreeCard :member="member" :isPastor="isPastor" />
          </div>

          <!-- TAB 5: HÀNH TRÌNH ĐỨC TIN -->
          <div v-if="activeTab === 'faith'" class="space-y-4">
             <FaithJourneyTimeline :member="member" :isPastor="isPastor" />
          </div>

          <!-- TAB 3: MỤC VỤ -->
          <div v-if="activeTab === 'ministry'" class="space-y-5">

            <!-- Department Memberships -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-gray-900 text-sm flex items-center">
                  <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  Chức danh & Ban ngành
                </h3>
              </div>
              <div class="p-5">
                <div v-if="member.memberships && member.memberships.length" class="space-y-3">
                  <div v-for="m in member.memberships" :key="m.id"
                    class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:border-blue-100 hover:bg-blue-50/30 transition-colors">
                    <div>
                      <div class="text-xs font-bold text-gray-500 mb-0.5">{{ m.model?.name || 'Ban ngành' }}</div>
                      <div class="font-bold text-gray-900">{{ m.role?.name || m.org_role?.name || 'Thành viên' }}</div>
                      <div v-if="m.join_date" class="text-xs text-gray-400 mt-0.5">Từ: {{ formatDate(m.join_date) }}</div>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2 py-1 rounded-lg"
                      :class="m.role?.code === 'tb' || m.org_role?.code === 'tb' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700'">
                      {{ m.role?.code?.toUpperCase() || m.org_role?.code?.toUpperCase() || 'BV' }}
                    </span>
                  </div>
                </div>
                <div v-else class="text-sm text-gray-400 italic text-center py-8">Chưa đảm nhiệm vai trò nào.</div>
              </div>
            </div>

            <!-- Talents -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
              <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-bold text-gray-900 text-sm">✨ Ân tứ & Phục vụ</h3>
              </div>
              <div class="p-5">
                <div v-if="member.talents && member.talents.length" class="flex flex-wrap gap-2">
                  <span v-for="t in member.talents" :key="t.id"
                    class="px-4 py-2 bg-gradient-to-tr from-yellow-50 to-orange-50 text-orange-700 border border-yellow-200 rounded-xl text-xs font-bold shadow-sm">
                    {{ t.name }}
                  </span>
                </div>
                <p v-else class="text-sm text-gray-400 italic text-center py-6">Chưa khai báo ân tứ.</p>
              </div>
            </div>

            <!-- Pastoral Info (Pastor only) -->
            <div v-if="isPastor" class="bg-red-50 rounded-2xl border border-red-100 shadow-sm overflow-hidden">
              <div class="px-6 py-4 border-b border-red-100">
                <h3 class="font-bold text-red-900 text-sm flex items-center">
                  <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                  Thông tin Mục vụ (Tuyệt mật)
                </h3>
              </div>
              <div class="p-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="bg-white rounded-xl p-4 border border-red-50">
                    <label class="text-[10px] font-black uppercase text-red-400 block mb-1">Nghề nghiệp</label>
                    <input v-if="isEditing" type="text" v-model="form.occupation" class="w-full text-sm font-bold border-red-200 rounded-lg focus:border-red-400" placeholder="Nghề nghiệp"/>
                    <p v-else class="text-sm font-bold text-gray-900">{{ member.sensitive_info?.occupation || '—' }}</p>
                  </div>
                  <div class="bg-white rounded-xl p-4 border border-red-50">
                    <label class="text-[10px] font-black uppercase text-red-400 block mb-1">Hôn nhân</label>
                    <select v-if="isEditing" v-model="form.marital_status" class="w-full text-sm font-bold border-red-200 rounded-lg">
                      <option>Độc thân</option><option>Đã kết hôn</option><option>Góa</option><option>Khác</option>
                    </select>
                    <p v-else class="text-sm font-bold text-gray-900">{{ member.sensitive_info?.marital_status || '—' }}</p>
                  </div>
                </div>
                <div class="bg-white rounded-xl p-4 border border-red-50">
                  <label class="text-[10px] font-black uppercase text-red-400 block mb-2">Nan đề & Cầu thay</label>
                  <textarea v-if="isEditing" v-model="form.prayer_concerns" rows="3" class="w-full text-sm font-medium border-red-200 rounded-lg" placeholder="Nan đề..."></textarea>
                  <p v-else class="text-sm text-gray-700 italic leading-relaxed">{{ member.sensitive_info?.prayer_concerns || 'Chưa có ghi nhận.' }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 border border-red-50">
                  <label class="text-[10px] font-black uppercase text-red-400 block mb-2">Ghi chú Mục vụ</label>
                  <textarea v-if="isEditing" v-model="form.pastoral_notes" rows="3" class="w-full text-sm font-medium border-red-200 rounded-lg" placeholder="Ghi chú mục vụ..."></textarea>
                  <p v-else class="text-sm text-gray-700 leading-relaxed">{{ member.sensitive_info?.pastoral_notes || 'Không có ghi chú.' }}</p>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Sidebar -->
        <div class="space-y-5">

          <!-- Quick Stats -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
            <h4 class="text-xs font-black uppercase tracking-widest text-gray-400">Tổng quan</h4>

            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl">
              <span class="text-xs font-bold text-blue-800">Điểm danh cuối</span>
              <span class="text-xs font-black text-blue-900">
                {{ lastAttendanceDate }}
              </span>
            </div>

            <div class="flex items-center justify-between p-3 bg-indigo-50 rounded-xl">
              <span class="text-xs font-bold text-indigo-800">Thăm viếng</span>
              <span class="text-xl font-black text-indigo-700">{{ (member.visitations || []).length }}</span>
            </div>

            <div class="flex items-center justify-between p-3 bg-green-50 rounded-xl">
              <span class="text-xs font-bold text-green-800">Thăm hoàn thành</span>
              <span class="text-xl font-black text-green-700">{{ (member.visitations || []).filter(v => v.status === 'completed').length }}</span>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl"
              :class="member.is_baptized ? 'bg-purple-50' : 'bg-gray-50'">
              <span class="text-xs font-bold" :class="member.is_baptized ? 'text-purple-800' : 'text-gray-500'">Báp-têm</span>
              <span class="text-xs font-black px-2 py-1 rounded-full"
                :class="member.is_baptized ? 'bg-purple-200 text-purple-800' : 'bg-gray-200 text-gray-600'">
                {{ member.is_baptized ? '✓ Đã làm' : 'Chưa' }}
              </span>
            </div>
          </div>

          <!-- Departments -->
          <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50/50">
              <h4 class="text-xs font-black uppercase tracking-widest text-gray-400">Ban ngành tham gia</h4>
            </div>
            <div class="p-4">
              <div v-if="member.memberships && member.memberships.length" class="space-y-2">
                <div v-for="m in member.memberships" :key="'side-'+m.id" class="flex items-center gap-2 p-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                  <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-100 to-blue-100 flex items-center justify-center text-indigo-700 font-black text-xs shrink-0">
                    {{ (m.model?.name || 'B').charAt(0) }}
                  </div>
                  <div class="min-w-0">
                    <div class="text-xs font-bold text-gray-800 truncate">{{ m.model?.name }}</div>
                    <div class="text-[10px] text-gray-500">{{ m.role?.name || m.org_role?.name }}</div>
                  </div>
                </div>
              </div>
              <p v-else class="text-xs text-gray-400 italic text-center py-4">Chưa tham gia ban ngành nào.</p>
            </div>
          </div>

          <!-- Last Visitation -->
          <div v-if="member.visitations && member.visitations.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50/50">
              <h4 class="text-xs font-black uppercase tracking-widest text-gray-400">Thăm viếng gần nhất</h4>
            </div>
            <div class="p-4">
              <div class="text-sm font-bold text-gray-900">{{ formatDate(member.visitations[0].visit_date) }}</div>
              <div class="text-xs text-gray-500 mt-0.5">{{ member.visitations[0].reason }}</div>
              <div class="mt-2">
                <span class="text-xs px-2 py-0.5 rounded-full font-bold"
                  :class="member.visitations[0].status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'">
                  {{ member.visitations[0].status === 'completed' ? '✓ Hoàn thành' : 'Kế hoạch' }}
                </span>
              </div>
              <div v-if="member.visitations[0].visitors && member.visitations[0].visitors.length" class="mt-2 text-xs text-gray-500">
                👥 {{ member.visitations[0].visitors.map(v => v.full_name).join(', ') }}
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </component>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MobileLayout from '@/Layouts/MobileLayout.vue';
import { User as IdentificationIcon, History as HistoryIcon, BookOpen as MinistryIcon, Users as FamilyIcon, Star as FaithIcon } from 'lucide-vue-next';
import FamilyTreeCard from '@/Components/Member/FamilyTreeCard.vue';
import FaithJourneyTimeline from '@/Components/Member/FaithJourneyTimeline.vue';

const props = defineProps({
  member: Object,
  auth_roles: Array,
  isPastor: Boolean,
});

const isEditing = ref(false);

const form = useForm({
  full_name: props.member?.full_name || '',
  email: props.member?.email || '',
  phone: props.member?.phone || '',
  date_of_birth: props.member?.date_of_birth ? String(props.member.date_of_birth).split('T')[0] : '',
  gender: props.member?.gender || '',
  address: props.member?.address || '',
  status: props.member?.status || 'Chưa rõ',
  is_baptized: props.member?.is_baptized || false,
  faith_date: props.member?.faith_date ? String(props.member.faith_date).split('T')[0] : '',
  baptism_date: props.member?.baptism_date ? String(props.member.baptism_date).split('T')[0] : '',
  general_notes: props.member?.general_notes || '',
  marital_status: props.member?.sensitive_info?.marital_status || 'Độc thân',
  prayer_concerns: props.member?.sensitive_info?.prayer_concerns || '',
  pastoral_notes: props.member?.sensitive_info?.pastoral_notes || '',
  occupation: props.member?.sensitive_info?.occupation || '',
});

const startEditing = () => { isEditing.value = true; };
const cancelEditing = () => { form.reset(); isEditing.value = false; };
const submit = () => {
  if (!props.member?.id) return;
  form.put(route('members.update', props.member.id), {
    preserveScroll: true,
    onSuccess: () => { isEditing.value = false; },
  });
};

const activeTab = ref('general');

const tabs = computed(() => {
  const base = [
    { id: 'general', name: 'Thông tin', icon: IdentificationIcon },
    { id: 'family', name: 'Gia phả', icon: FamilyIcon },
    { id: 'faith', name: 'Hành trình', icon: FaithIcon },
    { id: 'history', name: 'Thăm Viếng', icon: HistoryIcon },
    { id: 'ministry', name: 'Mục vụ', icon: MinistryIcon },
  ];
  return base;
});

const formatDate = (dateString) => {
  if (!dateString) return '—';
  const dateStr = String(dateString).split('T')[0];
  const parts = dateStr.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return dateString;
};

const lastAttendanceDate = computed(() => {
  const atts = props.member.attendances;
  if (!atts || !atts.length) return 'Chưa có';
  const last = atts[0];
  if (last.meeting?.date) return formatDate(last.meeting.date);
  return formatDate(last.created_at);
});

// Layout detection
const windowWidth = ref(window.innerWidth);
const updateWidth = () => windowWidth.value = window.innerWidth;
onMounted(() => window.addEventListener('resize', updateWidth));
onUnmounted(() => window.removeEventListener('resize', updateWidth));
const currentLayout = computed(() => windowWidth.value >= 768 ? AuthenticatedLayout : MobileLayout);
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>