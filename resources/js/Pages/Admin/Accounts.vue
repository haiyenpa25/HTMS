<template>
  <component :is="currentLayout">
    <template #header>Quản Lý Tài Khoản</template>

    <div class="accounts-page">
      <!-- Header Banner -->
      <div class="page-banner">
        <div>
          <p class="banner-label">QUẢN TRỊ HỆ THỐNG</p>
          <h1>👤 Quản Lý Tài Khoản</h1>
          <p class="banner-sub">Tạo · Gắn tín hữu · Phân quyền · Reset mật khẩu</p>
        </div>
        <button @click="showCreateModal = true" class="btn-primary">
          + Tạo Tài Khoản Mới
        </button>
      </div>

      <!-- Stats Row -->
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-n">{{ stats.total_users }}</div>
          <div class="stat-l">Tổng tài khoản</div>
        </div>
        <div class="stat-card stat-green">
          <div class="stat-n">{{ stats.linked_users }}</div>
          <div class="stat-l">Đã gắn tín hữu</div>
        </div>
        <div class="stat-card stat-amber">
          <div class="stat-n">{{ stats.unlinked_users }}</div>
          <div class="stat-l">Chưa gắn tín hữu</div>
        </div>
        <div class="stat-card stat-red">
          <div class="stat-n">{{ stats.superadmins }}</div>
          <div class="stat-l">Super Admin</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filter-bar">
        <input
          v-model="searchText"
          @keyup.enter="applySearch"
          type="text"
          placeholder="Tìm theo tên, email..."
          class="search-input"
          id="account-search"
        />
        <div class="filter-pills">
          <button
            v-for="f in filterOptions" :key="f.value"
            @click="applyFilter(f.value)"
            class="filter-pill"
            :class="{ active: filters.filter === f.value }"
          >{{ f.label }}</button>
        </div>
      </div>

      <!-- Flash messages -->
      <div v-if="$page.props.flash?.success" class="flash-success">
        ✅ {{ $page.props.flash.success }}
      </div>

      <!-- Table -->
      <div class="table-card">
        <table class="accounts-table">
          <thead>
            <tr>
              <th>Tên / Email</th>
              <th>Tín Hữu Gắn Kết</th>
              <th>Roles</th>
              <th>SuperAdmin</th>
              <th>Ngày tạo</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in users.data" :key="u.id" class="account-row">
              <td>
                <div class="user-cell">
                  <div class="user-avatar">{{ u.name.charAt(0).toUpperCase() }}</div>
                  <div>
                    <div class="user-name">{{ u.name }}</div>
                    <div class="user-email">{{ u.email }}</div>
                  </div>
                </div>
              </td>
              <td>
                <div v-if="u.member" class="member-link">
                  <span class="member-name">{{ u.member.full_name }}</span>
                  <span class="member-code">{{ u.member.member_code }}</span>
                  <span class="member-status" :class="'status-' + u.member.status?.toLowerCase().replace(' ','_')">
                    {{ u.member.status }}
                  </span>
                </div>
                <div v-else class="no-member">
                  <span class="no-link-badge">Chưa gắn</span>
                  <button @click="openLinkModal(u)" class="btn-xs btn-indigo">Gắn ngay</button>
                </div>
              </td>
              <td>
                <span v-for="r in u.roles" :key="r" class="role-badge">{{ r }}</span>
                <span v-if="!u.roles?.length" class="no-role">—</span>
              </td>
              <td class="text-center">
                <button
                  @click="toggleSuperAdmin(u)"
                  class="superadmin-toggle"
                  :class="{ 'is-sa': u.is_superadmin }"
                  :title="u.is_superadmin ? 'Thu hồi SuperAdmin' : 'Cấp SuperAdmin'"
                >
                  {{ u.is_superadmin ? '⭐ Super' : '○' }}
                </button>
              </td>
              <td class="text-muted text-sm">{{ u.created_at }}</td>
              <td>
                <div class="action-btns">
                  <button @click="openLinkModal(u)" class="btn-xs btn-blue" title="Gắn/đổi tín hữu">🔗</button>
                  <button @click="openResetModal(u)" class="btn-xs btn-amber" title="Reset mật khẩu">🔑</button>
                  <button @click="openOrgRoles(u)" class="btn-xs btn-purple" title="Xem chức danh">📋</button>
                  <button
                    v-if="!u.is_superadmin && u.id !== currentUserId"
                    @click="confirmDelete(u)"
                    class="btn-xs btn-red" title="Xóa tài khoản"
                  >🗑</button>
                </div>
              </td>
            </tr>
            <tr v-if="!users.data?.length">
              <td colspan="6" class="empty-row">Không tìm thấy tài khoản nào</td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="pagination">
          <button
            v-for="p in users.last_page" :key="p"
            @click="goPage(p)"
            class="page-btn"
            :class="{ active: users.current_page === p }"
          >{{ p }}</button>
        </div>
      </div>

      <!-- ══ MODAL: Tạo tài khoản mới từ member ══ -->
      <div v-if="showCreateModal" class="modal-overlay" @click.self="showCreateModal = false">
        <div class="modal-box">
          <h2 class="modal-title">+ Tạo Tài Khoản Từ Tín Hữu</h2>
          <form @submit.prevent="submitCreate">
            <div class="form-group">
              <label>Tín Hữu</label>
              <select v-model="createForm.member_id" required class="form-select" id="create-member-select">
                <option value="">— Chọn tín hữu chưa có tài khoản —</option>
                <option v-for="m in unlinked_members" :key="m.id" :value="m.id">
                  {{ m.full_name }} ({{ m.member_code }}) — {{ m.status }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Email đăng nhập</label>
              <input v-model="createForm.email" type="email" required class="form-input" placeholder="email@example.com" id="create-email" />
            </div>
            <div class="form-group">
              <label>Mật khẩu tạm <span class="hint">(để trống = tự tạo ngẫu nhiên)</span></label>
              <input v-model="createForm.password" type="text" class="form-input" placeholder="Tùy chọn..." id="create-password" />
            </div>
            <div class="modal-actions">
              <button type="button" @click="showCreateModal = false" class="btn-ghost">Hủy</button>
              <button type="submit" class="btn-primary" :disabled="creating">
                {{ creating ? 'Đang tạo...' : 'Tạo Tài Khoản' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- ══ MODAL: Gắn tín hữu ══ -->
      <div v-if="linkModal.show" class="modal-overlay" @click.self="linkModal.show = false">
        <div class="modal-box">
          <h2 class="modal-title">🔗 Gắn Tín Hữu — {{ linkModal.user?.name }}</h2>
          <p class="modal-sub">Chọn tín hữu để gắn vào tài khoản này. Nếu TK đang gắn với tín hữu khác, sẽ tự tháo ra.</p>
          <form @submit.prevent="submitLink">
            <div class="form-group">
              <label>Tín Hữu (chưa có tài khoản)</label>
              <select v-model="linkForm.member_id" required class="form-select" id="link-member-select">
                <option value="">— Chọn —</option>
                <option v-for="m in unlinked_members" :key="m.id" :value="m.id">
                  {{ m.full_name }} ({{ m.member_code }})
                </option>
              </select>
            </div>
            <div class="modal-actions">
              <button type="button" @click="linkModal.show = false" class="btn-ghost">Hủy</button>
              <button type="submit" class="btn-primary">Gắn</button>
            </div>
          </form>
          <div v-if="linkModal.user?.member" class="unlink-section">
            <p class="unlink-label">Hiện đang gắn với: <strong>{{ linkModal.user.member.full_name }}</strong></p>
            <button @click="submitUnlink" class="btn-xs btn-red">Tháo gắn kết</button>
          </div>
        </div>
      </div>

      <!-- ══ MODAL: Reset mật khẩu ══ -->
      <div v-if="resetModal.show" class="modal-overlay" @click.self="resetModal.show = false">
        <div class="modal-box">
          <h2 class="modal-title">🔑 Reset Mật Khẩu — {{ resetModal.user?.name }}</h2>
          <form @submit.prevent="submitReset">
            <div class="form-group">
              <label>Mật khẩu mới</label>
              <input v-model="resetForm.password" type="text" required class="form-input" placeholder="Nhập mật khẩu mới..." id="reset-password-input" />
            </div>
            <div class="modal-actions">
              <button type="button" @click="resetModal.show = false" class="btn-ghost">Hủy</button>
              <button type="submit" class="btn-amber-solid">Reset</button>
            </div>
          </form>
        </div>
      </div>

      <!-- ══ DRAWER: Chức danh org ══ -->
      <div v-if="orgRolesDrawer.show" class="modal-overlay" @click.self="orgRolesDrawer.show = false">
        <div class="modal-box">
          <h2 class="modal-title">📋 Chức Danh — {{ orgRolesDrawer.user?.name }}</h2>
          <div v-if="orgRolesDrawer.loading" class="loading-msg">Đang tải...</div>
          <div v-else-if="!orgRolesDrawer.data?.roles?.length" class="empty-msg">
            Tín hữu chưa có chức danh nào trong hệ thống.
          </div>
          <div v-else>
            <div v-for="r in orgRolesDrawer.data.roles" :key="r.role_code + r.model_id" class="role-row">
              <span class="role-code-badge">{{ r.role_code }}</span>
              <span class="role-name">{{ r.role_name }}</span>
              <span class="dept-name">{{ r.dept_name }}</span>
              <span class="join-date">{{ r.join_date }}</span>
            </div>
          </div>
          <div class="modal-actions">
            <button @click="orgRolesDrawer.show = false" class="btn-ghost">Đóng</button>
          </div>
        </div>
      </div>
    </div>
  </component>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import MobileLayout from '@/Layouts/MobileLayout.vue'

const props = defineProps({
  users:            { type: Object, default: () => ({}) },
  stats:            { type: Object, default: () => ({}) },
  unlinked_members: { type: Array, default: () => [] },
  filters:          { type: Object, default: () => ({}) },
})

const page = usePage()
const currentUserId = computed(() => page.props.auth?.user?.id)
const currentLayout = computed(() =>
  typeof window !== 'undefined' && window.innerWidth < 768 ? MobileLayout : AuthenticatedLayout
)

// ── Filters ────────────────────────────────────────
const searchText = ref(props.filters?.search ?? '')
const filterOptions = [
  { value: '', label: 'Tất cả' },
  { value: 'has_member', label: '🔗 Đã gắn' },
  { value: 'no_member', label: '⚠️ Chưa gắn' },
  { value: 'superadmin', label: '⭐ SuperAdmin' },
]

function applySearch() {
  router.get(route('admin.accounts.index'), { search: searchText.value, filter: props.filters?.filter }, { preserveState: true })
}
function applyFilter(val) {
  router.get(route('admin.accounts.index'), { search: searchText.value, filter: val }, { preserveState: true })
}
function goPage(p) {
  router.get(route('admin.accounts.index'), { ...props.filters, page: p }, { preserveState: true })
}

// ── Tạo tài khoản mới ──────────────────────────────
const showCreateModal = ref(false)
const creating = ref(false)
const createForm = ref({ member_id: '', email: '', password: '' })

function submitCreate() {
  creating.value = true
  router.post(route('admin.accounts.create-from-member'), createForm.value, {
    onFinish: () => { creating.value = false; showCreateModal.value = false; createForm.value = { member_id: '', email: '', password: '' } },
  })
}

// ── Gắn tín hữu ────────────────────────────────────
const linkModal = ref({ show: false, user: null })
const linkForm = ref({ member_id: '' })

function openLinkModal(u) {
  linkModal.value = { show: true, user: u }
  linkForm.value.member_id = ''
}
function submitLink() {
  router.post(route('admin.accounts.link-member', linkModal.value.user.id), linkForm.value, {
    onFinish: () => { linkModal.value.show = false }
  })
}
function submitUnlink() {
  router.post(route('admin.accounts.unlink-member', linkModal.value.user.id), {}, {
    onFinish: () => { linkModal.value.show = false }
  })
}

// ── Reset mật khẩu ─────────────────────────────────
const resetModal = ref({ show: false, user: null })
const resetForm = ref({ password: '' })

function openResetModal(u) {
  resetModal.value = { show: true, user: u }
  resetForm.value.password = ''
}
function submitReset() {
  router.post(route('admin.accounts.reset-password', resetModal.value.user.id), resetForm.value, {
    onFinish: () => { resetModal.value.show = false }
  })
}

// ── Toggle SuperAdmin ───────────────────────────────
function toggleSuperAdmin(u) {
  if (!confirm(`${u.is_superadmin ? 'Thu hồi' : 'Cấp'} quyền Super Admin cho ${u.name}?`)) return
  router.post(route('admin.accounts.toggle-superadmin', u.id))
}

// ── Xóa tài khoản ──────────────────────────────────
function confirmDelete(u) {
  if (!confirm(`Xóa tài khoản "${u.name}"? Hồ sơ tín hữu sẽ không bị xóa.`)) return
  router.delete(route('admin.accounts.destroy', u.id))
}

// ── Org Roles drawer ────────────────────────────────
const orgRolesDrawer = ref({ show: false, user: null, loading: false, data: null })

async function openOrgRoles(u) {
  orgRolesDrawer.value = { show: true, user: u, loading: true, data: null }
  try {
    const res = await fetch(route('admin.accounts.org-roles', u.id))
    orgRolesDrawer.value.data = await res.json()
  } finally {
    orgRolesDrawer.value.loading = false
  }
}
</script>

<style scoped>
.accounts-page { max-width: 1200px; margin: 0 auto; padding: 24px 16px; font-family: 'Inter', sans-serif; }

/* Banner */
.page-banner {
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;
  background: linear-gradient(135deg, #1e3a5f, #1e40af);
  color: white; border-radius: 16px; padding: 24px 28px; margin-bottom: 20px;
}
.banner-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; opacity: 0.7; margin: 0; }
.page-banner h1 { margin: 4px 0; font-size: 1.5rem; font-weight: 800; }
.banner-sub { font-size: 0.82rem; opacity: 0.75; margin: 2px 0 0; }

/* Stats */
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px; }
.stat-card {
  background: white; border: 1px solid #e5e7eb; border-radius: 12px;
  padding: 16px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.stat-n { font-size: 2rem; font-weight: 800; color: #1f2937; line-height: 1; }
.stat-l { font-size: 0.72rem; color: #6b7280; margin-top: 4px; }
.stat-green .stat-n { color: #065f46; }
.stat-amber .stat-n { color: #92400e; }
.stat-red   .stat-n { color: #991b1b; }

/* Filter bar */
.filter-bar { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 16px; }
.search-input {
  flex: 1; min-width: 220px; padding: 9px 14px; border: 1.5px solid #e5e7eb;
  border-radius: 10px; font-size: 0.88rem; outline: none;
  transition: border-color 0.15s;
}
.search-input:focus { border-color: #6366f1; }
.filter-pills { display: flex; gap: 6px; flex-wrap: wrap; }
.filter-pill {
  padding: 6px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 500;
  border: 1.5px solid #e5e7eb; background: white; cursor: pointer;
  transition: all 0.15s; color: #374151;
}
.filter-pill.active { background: #4f46e5; color: white; border-color: #4f46e5; }
.filter-pill:hover:not(.active) { border-color: #a5b4fc; }

/* Flash */
.flash-success {
  background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46;
  padding: 12px 16px; border-radius: 10px; font-size: 0.88rem; margin-bottom: 14px;
}

/* Table */
.table-card { background: white; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
.accounts-table { width: 100%; border-collapse: collapse; }
.accounts-table th {
  background: #f8fafc; padding: 12px 16px; text-align: left;
  font-size: 0.78rem; font-weight: 700; color: #374151; border-bottom: 1.5px solid #e5e7eb;
}
.account-row td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.account-row:last-child td { border-bottom: none; }
.account-row:hover td { background: #fafafa; }

.user-cell { display: flex; align-items: center; gap: 10px; }
.user-avatar {
  width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white; font-weight: 700; display: flex; align-items: center; justify-content: center;
  font-size: 0.9rem; flex-shrink: 0;
}
.user-name { font-size: 0.88rem; font-weight: 600; color: #1f2937; }
.user-email { font-size: 0.75rem; color: #6b7280; }

.member-link { display: flex; flex-direction: column; gap: 2px; }
.member-name { font-size: 0.85rem; font-weight: 600; color: #065f46; }
.member-code { font-size: 0.7rem; color: #6b7280; }
.member-status { font-size: 0.68rem; font-weight: 700; padding: 1px 6px; border-radius: 8px; background: #d1fae5; color: #065f46; display: inline-block; }

.no-member { display: flex; align-items: center; gap: 6px; }
.no-link-badge { font-size: 0.72rem; color: #d97706; background: #fef3c7; padding: 2px 8px; border-radius: 8px; font-weight: 600; }

.role-badge { font-size: 0.7rem; background: #e0e7ff; color: #3730a3; padding: 2px 7px; border-radius: 8px; margin-right: 4px; font-weight: 600; }
.no-role { color: #d1d5db; font-size: 0.85rem; }

.superadmin-toggle {
  font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 10px;
  border: 1.5px solid #e5e7eb; background: white; cursor: pointer; transition: all 0.15s;
  color: #6b7280;
}
.superadmin-toggle.is-sa { background: #fef9c3; border-color: #fbbf24; color: #92400e; }
.superadmin-toggle:hover { border-color: #6366f1; }

.action-btns { display: flex; gap: 4px; flex-wrap: wrap; }
.btn-xs {
  font-size: 0.75rem; padding: 4px 8px; border-radius: 7px; border: 1px solid;
  cursor: pointer; font-weight: 600; transition: all 0.12s; background: white;
}
.btn-xs:hover { opacity: 0.8; }
.btn-indigo { border-color: #c7d2fe; color: #4338ca; }
.btn-blue   { border-color: #bfdbfe; color: #1e40af; }
.btn-amber  { border-color: #fde68a; color: #92400e; }
.btn-purple { border-color: #ddd6fe; color: #5b21b6; }
.btn-red    { border-color: #fca5a5; color: #991b1b; }

.text-center { text-align: center; }
.text-muted { color: #9ca3af; }
.text-sm { font-size: 0.8rem; }
.empty-row { text-align: center; padding: 32px; color: #9ca3af; font-size: 0.9rem; }

/* Pagination */
.pagination { display: flex; gap: 4px; justify-content: center; padding: 16px; }
.page-btn {
  padding: 6px 12px; border-radius: 8px; border: 1px solid #e5e7eb;
  cursor: pointer; font-size: 0.82rem; font-weight: 600; background: white;
  transition: all 0.12s; color: #374151;
}
.page-btn.active { background: #4f46e5; color: white; border-color: #4f46e5; }

/* Buttons */
.btn-primary {
  background: linear-gradient(135deg, #6366f1, #4f46e5); color: white;
  padding: 10px 20px; border-radius: 10px; border: none; font-weight: 600;
  font-size: 0.9rem; cursor: pointer; transition: opacity 0.15s;
}
.btn-primary:hover { opacity: 0.9; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-ghost {
  background: white; border: 1.5px solid #e5e7eb; color: #374151;
  padding: 10px 20px; border-radius: 10px; font-weight: 600; font-size: 0.9rem;
  cursor: pointer; transition: border-color 0.15s;
}
.btn-ghost:hover { border-color: #6366f1; }
.btn-amber-solid {
  background: #d97706; color: white; padding: 10px 20px; border-radius: 10px;
  border: none; font-weight: 600; font-size: 0.9rem; cursor: pointer;
}

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center; z-index: 50; padding: 16px;
}
.modal-box {
  background: white; border-radius: 16px; padding: 28px; width: 100%; max-width: 480px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;
}
.modal-title { font-size: 1.1rem; font-weight: 700; color: #1f2937; margin: 0 0 6px; }
.modal-sub { font-size: 0.82rem; color: #6b7280; margin: 0 0 18px; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }

.form-group { margin-bottom: 14px; }
.form-group label { display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 5px; }
.hint { font-weight: 400; color: #9ca3af; }
.form-input, .form-select {
  width: 100%; padding: 9px 12px; border: 1.5px solid #e5e7eb; border-radius: 9px;
  font-size: 0.88rem; outline: none; transition: border-color 0.15s;
}
.form-input:focus, .form-select:focus { border-color: #6366f1; }

.unlink-section {
  margin-top: 18px; padding-top: 14px; border-top: 1px solid #f3f4f6;
  display: flex; align-items: center; gap: 10px;
}
.unlink-label { font-size: 0.82rem; color: #374151; flex: 1; }

.role-row {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 0; border-bottom: 1px solid #f3f4f6;
}
.role-row:last-child { border-bottom: none; }
.role-code-badge { font-size: 0.68rem; font-weight: 800; background: #e0e7ff; color: #3730a3; padding: 2px 7px; border-radius: 8px; }
.role-name { font-size: 0.85rem; font-weight: 600; color: #1f2937; flex: 1; }
.dept-name { font-size: 0.78rem; color: #6b7280; }
.join-date { font-size: 0.72rem; color: #9ca3af; }

.loading-msg, .empty-msg { text-align: center; color: #9ca3af; padding: 24px; font-size: 0.88rem; }

@media (max-width: 768px) {
  .stats-row { grid-template-columns: 1fr 1fr; }
  .filter-bar { flex-direction: column; align-items: stretch; }
}
</style>
