<template>
  <div class="admin-deacon-assignments">
    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>⚖ Phân Công Chấp Sự</h1>
        <p class="subtitle">Quản lý phân công theo nhiệm kỳ · Giữ nguyên lịch sử khi bầu lại</p>
      </div>
    </div>

    <!-- Term Controls -->
    <div class="term-controls">
      <div class="term-selector">
        <label>Xem nhiệm kỳ:</label>
        <div class="term-buttons">
          <button
            v-for="term in term_years"
            :key="term.from + '-' + term.to"
            class="term-btn"
            :class="{ active: viewing_term_from === term.from }"
            @click="switchTerm(term.from)"
          >
            {{ term.label }}
          </button>
          <button class="term-btn current-badge" v-if="!term_years.length">
            Chưa có nhiệm kỳ
          </button>
        </div>
      </div>

      <!-- Tạo nhiệm kỳ mới -->
      <button class="btn-new-term" @click="showNewTermModal = true">
        ✨ Tạo Nhiệm Kỳ Mới
      </button>
    </div>

    <!-- Department Assignments Table -->
    <div class="assignments-panel">
      <h2 class="panel-title">
        Phân công {{ viewing_term_from ? `Nhiệm Kỳ ${viewing_term_from}` : 'Hiện Tại' }}
        <span class="current-indicator" v-if="viewing_term_from === current_term_year">✓ Đang Hoạt Động</span>
      </h2>

      <div class="table-wrap">
        <table class="assignment-table">
          <thead>
            <tr>
              <th>Ban Ngành</th>
              <th>Khối</th>
              <th>Chấp Sự Phụ Trách</th>
              <th>Ghi Chú</th>
              <th class="actions-col">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in dept_assignments" :key="row.dept_id" class="assignment-row">
              <td class="dept-name-cell">{{ row.dept_name }}</td>
              <td><span class="block-badge" :class="'block-' + row.dept_block">{{ blockLabel(row.dept_block) }}</span></td>
              <td>
                <div v-if="row.assignment" class="deacon-assigned">
                  <span class="deacon-name">{{ row.assignment.deacon_name }}</span>
                  <span class="term-range">{{ row.assignment.term_from }}–{{ row.assignment.term_to }}</span>
                </div>
                <span v-else class="unassigned">— Chưa phân công —</span>
              </td>
              <td class="notes-cell">{{ row.assignment?.notes || '—' }}</td>
              <td>
                <button class="btn-edit" @click="openEdit(row)">
                  {{ row.assignment ? '✏ Sửa' : '+ Gán' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Edit/Assign Modal -->
    <div v-if="editModal.show" class="modal-overlay" @click.self="editModal.show = false">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ editModal.assignment ? 'Sửa Phân Công' : 'Gán Chấp Sự' }}</h3>
          <button class="modal-close" @click="editModal.show = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="modal-dept-name">Ban: <strong>{{ editModal.deptName }}</strong></div>

          <div class="field-group">
            <label>Nhiệm kỳ từ năm</label>
            <input type="number" v-model.number="editForm.term_from" class="form-input" min="2000" max="2100" />
          </div>
          <div class="field-group">
            <label>Đến năm</label>
            <input type="number" v-model.number="editForm.term_to" class="form-input" min="2000" max="2100" />
          </div>
          <div class="field-group">
            <label>Chấp Sự Phụ Trách</label>
            <select v-model="editForm.deacon_id" class="form-select">
              <option value="">-- Chọn Chấp Sự --</option>
              <option v-for="d in deacons" :key="d.id" :value="d.id">
                {{ d.full_name }} {{ d.phone ? `(${d.phone})` : '' }}
              </option>
            </select>
          </div>
          <div class="field-group">
            <label>Ghi Chú</label>
            <input type="text" v-model="editForm.notes" class="form-input" placeholder="Ghi chú (tùy chọn)" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="editModal.show = false">Hủy</button>
          <button class="btn-save" @click="saveAssignment" :disabled="!editForm.deacon_id">
            💾 Lưu Phân Công
          </button>
        </div>
      </div>
    </div>

    <!-- New Term Modal -->
    <div v-if="showNewTermModal" class="modal-overlay" @click.self="showNewTermModal = false">
      <div class="modal">
        <div class="modal-header">
          <h3>✨ Tạo Nhiệm Kỳ Mới</h3>
          <button class="modal-close" @click="showNewTermModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="field-group">
            <label>Từ năm</label>
            <input type="number" v-model.number="newTermForm.term_from" class="form-input" />
          </div>
          <div class="field-group">
            <label>Đến năm</label>
            <input type="number" v-model.number="newTermForm.term_to" class="form-input" />
          </div>
          <div class="field-group">
            <label>Copy phân công từ nhiệm kỳ</label>
            <select v-model="newTermForm.copy_from_year" class="form-select">
              <option value="">-- Không copy, tạo trống --</option>
              <option v-for="t in term_years" :key="t.from" :value="t.from">{{ t.label }}</option>
            </select>
            <p class="field-hint">Nếu copy, danh sách người phụ trách sẽ giống kỳ trước — bạn chỉ cần thay ai cần thay đổi.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="showNewTermModal = false">Hủy</button>
          <button class="btn-save" @click="initNewTerm">🚀 Tạo Nhiệm Kỳ</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  dept_assignments:   { type: Array, default: () => [] },
  deacons:            { type: Array, default: () => [] },
  term_years:         { type: Array, default: () => [] },
  current_term_year:  { type: Number, default: 0 },
  viewing_term_from:  { type: Number, default: 0 },
})

const showNewTermModal = ref(false)
const editModal = reactive({ show: false, deptId: null, deptName: '', assignment: null })
const editForm = reactive({ deacon_id: '', term_from: 2024, term_to: 2026, notes: '' })
const newTermForm = reactive({ term_from: new Date().getFullYear(), term_to: new Date().getFullYear() + 2, copy_from_year: '' })

function blockLabel(block) {
  return { activities: 'Sinh Hoạt', ministry: 'Mục Vụ', finance: 'Tài Chính', global: 'Toàn HT' }[block] || block
}

function switchTerm(termFrom) {
  router.get(route('admin.deacon-assignments.index'), { term_from: termFrom }, { preserveState: true })
}

function openEdit(row) {
  editModal.show     = true
  editModal.deptId   = row.dept_id
  editModal.deptName = row.dept_name
  editModal.assignment = row.assignment

  editForm.deacon_id = row.assignment?.deacon_id || ''
  editForm.term_from = props.viewing_term_from || props.current_term_year
  editForm.term_to   = row.assignment?.term_to || props.viewing_term_from + 2
  editForm.notes     = row.assignment?.notes || ''
}

function saveAssignment() {
  router.post(route('admin.deacon-assignments.assign'), {
    department_id: editModal.deptId,
    deacon_id:     editForm.deacon_id,
    term_from:     editForm.term_from,
    term_to:       editForm.term_to,
    notes:         editForm.notes,
  }, {
    onSuccess: () => { editModal.show = false },
  })
}

function initNewTerm() {
  router.post(route('admin.deacon-assignments.init-term'), {
    term_from:      newTermForm.term_from,
    term_to:        newTermForm.term_to,
    copy_from_year: newTermForm.copy_from_year || null,
  }, {
    onSuccess: () => { showNewTermModal.value = false },
  })
}
</script>

<style scoped>
.admin-deacon-assignments {
  max-width: 1100px;
  margin: 0 auto;
  padding: 24px 16px;
  font-family: 'Inter', sans-serif;
}
.page-header {
  background: linear-gradient(135deg, #1e293b, #1e3a5f);
  color: white;
  border-radius: 14px;
  padding: 24px 28px;
  margin-bottom: 24px;
}
.page-header h1 { margin: 0; font-size: 1.5rem; }
.subtitle { margin: 4px 0 0; opacity: 0.75; font-size: 0.88rem; }

/* Term Controls */
.term-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 12px;
}
.term-selector { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.term-selector label { font-size: 0.88rem; font-weight: 500; color: #374151; }
.term-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
.term-btn {
  padding: 6px 14px;
  border: 1.5px solid #d1d5db;
  border-radius: 20px;
  background: white;
  font-size: 0.82rem;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
}
.term-btn.active { background: #1e3a5f; color: white; border-color: #1e3a5f; }
.btn-new-term {
  padding: 8px 18px;
  background: #059669;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  font-family: inherit;
}

/* Table */
.assignments-panel {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 20px;
}
.panel-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1e3a5f;
  margin: 0 0 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.current-indicator {
  font-size: 0.75rem;
  background: #d1fae5;
  color: #065f46;
  padding: 3px 10px;
  border-radius: 20px;
  font-weight: 500;
}
.table-wrap { overflow-x: auto; }
.assignment-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.assignment-table th {
  text-align: left;
  padding: 10px 12px;
  border-bottom: 2px solid #e5e7eb;
  color: #6b7280;
  font-size: 0.78rem;
  font-weight: 600;
  text-transform: uppercase;
}
.assignment-row td {
  padding: 12px;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: middle;
}
.assignment-row:hover { background: #f9fafb; }
.dept-name-cell { font-weight: 500; color: #1f2937; }
.block-badge { font-size: 0.73rem; padding: 3px 8px; border-radius: 10px; font-weight: 500; }
.block-activities { background: #d1fae5; color: #065f46; }
.block-ministry   { background: #dbeafe; color: #1e40af; }
.block-finance    { background: #fef9c3; color: #713f12; }
.block-global     { background: #e0e7ff; color: #3730a3; }
.deacon-assigned { display: flex; align-items: center; gap: 8px; }
.deacon-name { font-weight: 500; color: #1f2937; }
.term-range { font-size: 0.75rem; color: #9ca3af; background: #f3f4f6; padding: 2px 6px; border-radius: 4px; }
.unassigned { color: #9ca3af; font-style: italic; font-size: 0.88rem; }
.notes-cell { font-size: 0.83rem; color: #6b7280; max-width: 160px; }
.btn-edit {
  padding: 5px 12px;
  background: #1e3a5f;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  cursor: pointer;
  font-family: inherit;
}

/* Modal */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.45);
  display: flex; align-items: center; justify-content: center;
  z-index: 100;
}
.modal {
  background: white;
  border-radius: 16px;
  width: 440px;
  max-width: 95vw;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 18px 20px;
  border-bottom: 1px solid #f3f4f6;
}
.modal-header h3 { margin: 0; font-size: 1rem; color: #1f2937; }
.modal-close { background: none; border: none; font-size: 1.1rem; cursor: pointer; color: #6b7280; }
.modal-body { padding: 18px 20px; }
.modal-dept-name { font-size: 0.88rem; color: #374151; margin-bottom: 14px; }
.field-group { margin-bottom: 14px; }
.field-group label { display: block; font-size: 0.82rem; font-weight: 500; color: #374151; margin-bottom: 5px; }
.form-input, .form-select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9rem;
  font-family: inherit;
  box-sizing: border-box;
}
.field-hint { font-size: 0.78rem; color: #9ca3af; margin: 4px 0 0; }
.modal-footer {
  display: flex; justify-content: flex-end; gap: 10px;
  padding: 14px 20px;
  border-top: 1px solid #f3f4f6;
}
.btn-cancel { padding: 8px 16px; background: #f3f4f6; border: none; border-radius: 8px; cursor: pointer; font-family: inherit; }
.btn-save {
  padding: 8px 18px;
  background: #1e3a5f;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  font-family: inherit;
}
.btn-save:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
