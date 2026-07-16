<template>
  <div class="admin-list-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h4 class="mb-1 fw-bold"><i class="bi bi-people me-2"></i>{{ t('admin.users_title') }}</h4>
        <small class="text-muted">{{ t('admin.users_description') }}</small>
      </div>
      <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" @click="openCreate">
        <i class="bi bi-plus-lg"></i><span>{{ t('admin.users_new') }}</span>
      </button>
    </div>

    <div class="filter-bar d-flex gap-2 flex-wrap mb-3">
      <button class="btn btn-sm" :class="filter === 'all' ? 'btn-primary' : 'btn-outline-primary'" @click="filter = 'all'">{{ t('admin.users_all') }}</button>
      <button class="btn btn-sm" :class="filter === 'pending' ? 'btn-warning text-dark' : 'btn-outline-warning'" @click="filter = 'pending'">
        {{ t('admin.users_pending') }} <span v-if="pendingCount > 0" class="badge bg-light text-dark ms-1">{{ pendingCount }}</span>
      </button>
      <button class="btn btn-sm" :class="filter === 'approved' ? 'btn-success' : 'btn-outline-success'" @click="filter = 'approved'">{{ t('admin.users_approved') }}</button>
      <button class="btn btn-sm" :class="filter === 'rejected' ? 'btn-danger' : 'btn-outline-danger'" @click="filter = 'rejected'">{{ t('admin.users_rejected') }}</button>
    </div>

    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary" style="width:2rem;height:2rem"></div></div>

    <div v-else-if="filteredUsers.length === 0" class="empty-state">
      <i class="bi bi-people"></i><p>{{ t('admin.users_empty') }}</p>
    </div>

    <div v-else class="section-group">
      <div class="section-body">
        <div class="list-row" v-for="user in filteredUsers" :key="user.id">
          <div class="list-row-left">
            <div class="user-avatar">
              <img v-if="user.photo" :src="user.photo" :alt="user.name || t('admin.users_photo')">
              <i v-else class="bi bi-person-fill"></i>
            </div>
            <div class="list-info">
              <div class="list-title">{{ user.name }}</div>
              <div class="list-sub">{{ user.email }}</div>
            </div>
          </div>

          <div class="list-row-center">
            <div class="list-meta">
              <span class="meta-item"><i class="bi bi-telephone me-1 text-muted"></i>{{ user.phone || '—' }}</span>
              <span class="meta-item"><i class="bi bi-clock me-1 text-muted"></i>{{ formatDate(user.created_at) }}</span>
            </div>
          </div>

          <div class="list-row-right">
            <span :class="getApprovalBadge(user).class">{{ getApprovalBadge(user).text }}</span>
            <span class="status-dot" :class="user.status === 1 ? 'active' : 'inactive'"></span>
            <div class="action-btns">
              <button class="action-btn reset-pwd" title="Repor Senha" @click="openResetPassword(user)"><i class="bi bi-key"></i></button>
              <button class="action-btn edit" :title="t('common.edit')" @click="openEdit(user)"><i class="bi bi-pencil"></i></button>
              <button class="action-btn delete" :title="t('common.delete')" @click="openDelete(user)"><i class="bi bi-trash3"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create / Edit Modal -->
    <div class="modal fade" ref="formModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold">{{ editingUser ? t('common.edit') + ' ' + t('admin.users_title') : t('admin.users_new') }}</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body pt-2">
            <div class="row g-3">
              <div class="col-md-4 text-center mb-3">
                <div class="edit-photo-preview mx-auto mb-2">
                  <img v-if="editForm.photo" :src="editForm.photo" :alt="t('admin.users_photo')">
                  <div v-else class="photo-placeholder"><i class="bi bi-person-fill"></i></div>
                </div>
                <label class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-camera-fill"></i> {{ t('admin.profile_change_photo') }}
                  <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" @change="onEditPhotoChange" hidden>
                </label>
                <div v-if="editPhotoMsg" class="small mt-1" :class="editPhotoError ? 'text-danger' : 'text-success'">{{ editPhotoMsg }}</div>
              </div>
              <div class="col-md-8">
                <div class="mb-3">
                  <label class="form-label fw-medium small">{{ t('admin.users_full_name') }} *</label>
                  <input type="text" class="form-control form-control-sm" v-model="editForm.name" required>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-medium small">Email</label>
                  <input type="email" class="form-control form-control-sm" :value="editForm.email" disabled>
                </div>
                <div class="mb-3">
                  <label class="form-label fw-medium small">{{ t('admin.users_phone') }}</label>
                  <input type="tel" class="form-control form-control-sm" v-model="editForm.phone">
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium small">{{ t('admin.users_account_status') }}</label>
                    <select class="form-select form-select-sm" v-model="editForm.approval_status">
                      <option value="approved">{{ t('admin.users_approved') }}</option>
                      <option value="pending">{{ t('admin.users_pending') }}</option>
                      <option value="rejected">{{ t('admin.users_rejected') }}</option>
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-medium small">{{ t('admin.users_status') }}</label>
                    <select class="form-select form-select-sm" v-model="editForm.status">
                      <option :value="1">{{ t('admin.services_active') }}</option>
                      <option :value="0">{{ t('admin.services_inactive') }}</option>
                    </select>
                  </div>
                </div>
                <div v-if="editError" class="alert alert-danger py-2 mb-0">{{ editError }}</div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ t('common.cancel') }}</button>
            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2" :disabled="editSaving" @click="saveEdit">
              <span v-if="editSaving" class="spinner-border spinner-border-sm"></span>
              <i v-else class="bi bi-check-lg"></i>{{ t('admin.settings_save') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" ref="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-body text-center py-4">
            <div class="delete-icon mb-3"><i class="bi bi-trash3"></i></div>
            <h6 class="fw-bold mb-2">{{ t('admin.users_confirm_delete') }}</h6>
            <p class="text-muted small mb-0">{{ deleteTarget?.name }}</p>
          </div>
          <div class="modal-footer border-0 justify-content-center pt-0 pb-3">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ t('common.cancel') }}</button>
            <button type="button" class="btn btn-danger btn-sm d-flex align-items-center gap-2" :disabled="deleting" @click="confirmDelete">
              <span v-if="deleting" class="spinner-border spinner-border-sm"></span>
              <i v-else class="bi bi-trash3"></i>{{ t('common.delete') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" ref="rejectModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold">{{ t('admin.reject') }}</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body pt-2">
            <p class="mb-2">{{ t('admin.users_confirm_reject_text') }} <strong>{{ rejectingUser?.name }}</strong>?</p>
            <div class="mb-0">
              <label class="form-label fw-medium small">{{ t('admin.users_reject_reason') }}</label>
              <textarea class="form-control form-control-sm" rows="3" v-model="rejectReason"></textarea>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ t('common.cancel') }}</button>
            <button type="button" class="btn btn-danger btn-sm" @click="confirmReject">{{ t('admin.users_confirm_reject') }}</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" ref="resetPwdModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold"><i class="bi bi-key-fill me-2"></i>Repor Senha</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body pt-2">
            <p v-if="!resetPwdResult">Vai gerar uma nova senha para <strong>{{ resetPwdUser?.name }}</strong>. O cliente receberá a nova senha por email.</p>

            <div v-if="!resetPwdResult && !resetPwdLoading" class="text-center py-3">
              <button class="btn btn-warning btn-lg" @click="confirmResetPassword">
                <i class="bi bi-key-fill me-2"></i>Gerar Nova Senha
              </button>
            </div>

            <div v-if="resetPwdLoading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted">A gerar senha e enviar email...</p>
            </div>

            <div v-if="resetPwdResult" class="alert alert-success mb-0">
              <h6 class="alert-heading mb-2"><i class="bi bi-check-circle-fill me-1"></i>Senha gerada e enviada!</h6>
              <p class="mb-2">A nova senha foi enviada para <strong>{{ resetPwdUser?.email }}</strong></p>
              <div class="d-flex align-items-center gap-2 p-2 bg-light rounded">
                <code class="fs-5 flex-grow-1 text-center" style="letter-spacing:2px;font-family:monospace;">{{ resetPwdResult }}</code>
                <button class="btn btn-sm btn-outline-primary" @click="copyPassword" title="Copiar">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
              <small class="text-muted d-block mt-2">O cliente deverá alterar a senha após iniciar sessão.</small>
            </div>

            <div v-if="resetPwdError" class="alert alert-danger py-2 mb-0">{{ resetPwdError }}</div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ resetPwdResult ? 'Fechar' : t('common.cancel') }}</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" ref="detailModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold">{{ t('admin.users_title') }}</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body pt-2" v-if="!detailLoading">
            <div class="row">
              <div class="col-md-6">
                <h6 class="fw-bold text-muted small text-uppercase mb-3">{{ t('admin.users_user_data') }}</h6>
                <table class="table table-sm table-borderless">
                  <tr><th class="text-muted">{{ t('admin.users_full_name') }}</th><td>{{ detailUser?.name }}</td></tr>
                  <tr><th class="text-muted">Username</th><td>@{{ detailUser?.username }}</td></tr>
                  <tr><th class="text-muted">Email</th><td>{{ detailUser?.email }}</td></tr>
                  <tr><th class="text-muted">{{ t('admin.users_phone') }}</th><td>{{ detailUser?.phone || '' }}</td></tr>
                  <tr><th class="text-muted">Role</th><td><span class="badge bg-info text-dark">{{ detailUser?.role }}</span></td></tr>
                  <tr><th class="text-muted">{{ t('admin.users_approval') }}</th><td><span :class="getApprovalBadge(detailUser).class">{{ getApprovalBadge(detailUser).text }}</span></td></tr>
                  <tr><th class="text-muted">{{ t('admin.users_status') }}</th><td><span class="status-dot" :class="detailUser?.status === 1 ? 'active' : 'inactive'" style="display:inline-block;vertical-align:middle"></span> {{ detailUser?.status === 1 ? t('admin.services_active') : t('admin.services_inactive') }}</td></tr>
                  <tr><th class="text-muted">{{ t('admin.users_last_login') }}</th><td>{{ formatDate(detailUser?.last_login) }}</td></tr>
                  <tr><th class="text-muted">{{ t('admin.users_password') }}</th><td>
                    <span v-if="detailUser?.password_must_change" class="badge bg-warning text-dark">{{ t('admin.users_change_password') }}</span>
                    <span v-else-if="detailUser?.password_changed_at" class="badge bg-info text-dark">{{ t('admin.users_password_changed') }} {{ formatDateOnly(detailUser.password_changed_at) }}</span>
                    <span v-else class="badge bg-secondary">{{ t('admin.users_password_not_changed') }}</span>
                  </td></tr>
                  <tr><th class="text-muted">{{ t('admin.users_registered_at') }}</th><td>{{ formatDate(detailUser?.created_at) }}</td></tr>
                </table>
              </div>
              <div class="col-md-6">
                <h6 class="fw-bold text-muted small text-uppercase mb-3">{{ t('admin.users_company_data') }}</h6>
                <div v-if="detailCompany">
                  <table class="table table-sm table-borderless">
                    <tr><th class="text-muted">{{ t('admin.users_company_name') }}</th><td>{{ detailCompany?.company_name }}</td></tr>
                    <tr><th class="text-muted">NIF</th><td>{{ detailCompany?.nif || '' }}</td></tr>
                    <tr><th class="text-muted">{{ t('admin.users_address') }}</th><td>{{ detailCompany?.address }}</td></tr>
                    <tr><th class="text-muted">{{ t('admin.users_phone') }}</th><td>{{ detailCompany?.phone }}</td></tr>
                    <tr><th class="text-muted">Email</th><td>{{ detailCompany?.email }}</td></tr>
                    <tr><th class="text-muted">{{ t('admin.users_service') }}</th><td>{{ detailCompany?.service }}</td></tr>
                    <tr><th class="text-muted">{{ t('admin.users_case_description') }}</th><td>{{ detailCompany?.case_description }}</td></tr>
                    <tr><th class="text-muted">Logo</th><td v-if="detailCompany?.logo"><img :src="detailCompany.logo" alt="Logo" style="max-height: 60px;"></td><td v-else></td></tr>
                    <tr><th class="text-muted">{{ t('admin.users_published') }}</th><td><span :class="detailCompany?.is_published ? 'badge bg-success' : 'badge bg-secondary'">{{ detailCompany?.is_published ? t('admin.users_yes') : t('admin.users_no') }}</span></td></tr>
                  </table>
                </div>
                <div v-else class="text-muted small">{{ t('admin.users_no_company') }}</div>
              </div>
            </div>
          </div>
          <div class="modal-body text-center py-4" v-if="detailLoading">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ t('common.loading') }}</span></div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">{{ t('common.close') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { supabase } from '@/lib/supabase'
import { supabaseAdmin } from '@/lib/supabaseAdmin'
import { Modal } from 'bootstrap'
import { useI18n } from '@/composables/useI18n'
import { useAuthStore } from '@/stores/authStore'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'

const { t } = useI18n()
const toast = useToast()
const { confirm } = useConfirm()
const authStore = useAuthStore()
const API_URL = import.meta.env.VITE_API_URL || ''

const users = ref([])
const loading = ref(false)
const filter = ref('pending')
const pendingCount = ref(0)

const editingUser = ref(null)
const editForm = reactive({ name: '', email: '', phone: '', approval_status: 'approved', status: 1, photo: '' })
const editSaving = ref(false)
const editError = ref('')
const editPhotoMsg = ref('')
const editPhotoError = ref(false)

const deleteTarget = ref(null)
const deleting = ref(false)

const rejectingUser = ref(null)
const rejectReason = ref('')

const detailUser = ref(null)
const detailCompany = ref(null)
const detailLoading = ref(false)

const resetPwdUser = ref(null)
const resetPwdLoading = ref(false)
const resetPwdResult = ref(null)
const resetPwdError = ref('')

const formModal = ref(null)
const deleteModal = ref(null)
const rejectModal = ref(null)
const resetPwdModal = ref(null)
const detailModal = ref(null)

let formInst = null
let delInst = null
let rejectInst = null
let resetPwdInst = null
let detailInst = null
let channel = null

const filteredUsers = computed(() => {
  if (filter.value === 'all') return users.value
  return users.value.filter(u => u.approval_status === filter.value)
})

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('pt-PT', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatDateOnly = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('pt-PT', { year: 'numeric', month: 'short', day: 'numeric' })
}

const getApprovalBadge = (user) => {
  if (!user) return { class: '', text: '' }
  if (user.approval_status === 'pending') return { class: 'approval-badge pending', text: t('admin.users_pending') }
  if (user.approval_status === 'approved') return { class: 'approval-badge approved', text: t('admin.users_approved') }
  return { class: 'approval-badge rejected', text: t('admin.users_rejected') }
}

async function loadUsers() {
  loading.value = true
  try {
    const { data, error } = await supabase.from('users').select('id, auth_id, created_at, updated_at, name, email, username, phone, role, position, permissions, approval_status, status, photo, password_must_change, password_changed_at, locked_at, locked_reason').eq('role', 'cliente').order('created_at', { ascending: false })
    if (!error) users.value = data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

async function loadPendingCount() {
  try {
    const { count, error } = await supabase.from('users').select('*', { count: 'exact', head: true }).eq('role', 'cliente').eq('approval_status', 'pending')
    if (!error) pendingCount.value = count || 0
  } catch { pendingCount.value = 0 }
}

function openCreate() {
  editingUser.value = null
  Object.assign(editForm, { name: '', email: '', phone: '', approval_status: 'approved', status: 1, photo: '' })
  editError.value = ''
  editPhotoMsg.value = ''
  editPhotoError.value = false
  formInst.show()
}

function openEdit(user) {
  editingUser.value = user
  Object.assign(editForm, {
    name: user.name || '',
    email: user.email || '',
    phone: user.phone || '',
    approval_status: user.approval_status || 'approved',
    status: user.status ?? 1,
    photo: user.photo || ''
  })
  editError.value = ''
  editPhotoMsg.value = ''
  editPhotoError.value = false
  formInst.show()
}

async function saveEdit() {
  if (!editingUser.value) return
  editSaving.value = true
  editError.value = ''
  try {
    const { error } = await supabase.from('users').update({
      name: editForm.name,
      phone: editForm.phone,
      approval_status: editForm.approval_status,
      status: editForm.status
    }).eq('id', editingUser.value.id)
    if (!error) {
      formInst.hide()
      await loadUsers()
      await loadPendingCount()
    }
  } catch (err) {
    editError.value = err.message || t('admin.error_save')
  } finally {
    editSaving.value = false
  }
}

async function onEditPhotoChange(e) {
  const file = e.target.files?.[0]
  if (!file || !editingUser.value) return
  editPhotoMsg.value = ''
  editPhotoError.value = false
  const fileExt = file.name.split('.').pop()
  const fileName = `${editingUser.value.id}.${fileExt}`
  const { data, error: uploadError } = await supabase.storage.from('photos').upload(fileName, file, { upsert: true })
  if (!uploadError && data) {
    const { data: urlData } = supabase.storage.from('photos').getPublicUrl(fileName)
    editForm.photo = urlData.publicUrl
    const { error: updateError } = await supabase.from('users').update({ photo: urlData.publicUrl }).eq('id', editingUser.value.id)
    if (!updateError) {
      editPhotoError.value = false
      editPhotoMsg.value = t('admin.users_photo_updated')
      await loadUsers()
    }
  } else {
    editPhotoError.value = true
    editPhotoMsg.value = uploadError?.message || t('admin.error_upload_photo')
  }
  e.target.value = ''
}

function openDelete(user) {
  deleteTarget.value = user
  delInst.show()
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    const { error } = await supabase.from('users').delete().eq('id', deleteTarget.value.id)
    if (!error) {
      delInst.hide()
      toast.success(t('admin.users_deleted'))
      await loadUsers()
      await loadPendingCount()
    }
  } catch (err) {
    toast.error(err.message || t('admin.error_delete'))
  } finally {
    deleting.value = false
  }
}

async function approve(user) {
  if (!await confirm({ title: 'Aprovar utilizador', message: `${t('admin.users_confirm_approve')} ${user.name}?`, type: 'info', confirmText: 'Aprovar', cancelText: 'Cancelar' })) return
  try {
    const { error } = await supabase.from('users').update({ approval_status: 'approved', approved_at: new Date() }).eq('id', user.id)
    if (!error) {
      await supabase.from('companies').update({ is_published: true }).eq('user_id', user.id)
      if (API_URL) {
        fetch(`${API_URL}/admin/users/${user.id}/send-email`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ type: 'approval' })
        }).catch(() => {})
      }
      await loadUsers()
      await loadPendingCount()
    }
  } catch (err) {
    toast.error(err.message || t('admin.error_approve'))
  }
}

function rejectUser(user) {
  rejectingUser.value = user
  rejectReason.value = ''
  rejectInst.show()
}

async function confirmReject() {
  if (!rejectingUser.value) return
  try {
    const { error } = await supabase.from('users').update({ approval_status: 'rejected', rejection_reason: rejectReason.value }).eq('id', rejectingUser.value.id)
    if (!error) {
      if (API_URL) {
        fetch(`${API_URL}/admin/users/${rejectingUser.value.id}/send-email`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ type: 'rejection', reason: rejectReason.value })
        }).catch(() => {})
      }
      rejectInst.hide()
      await loadUsers()
      await loadPendingCount()
    }
  } catch (err) {
    toast.error(err.message || t('admin.error_reject'))
  }
}

async function openDetail(user) {
  detailLoading.value = true
  try {
    detailUser.value = user
    const { data: company, error } = await supabase.from('companies').select('*').eq('user_id', user.id).single()
    if (!error) detailCompany.value = company
    detailInst.show()
  } catch (err) {
    toast.error(t('admin.users_error_loading'))
  } finally {
    detailLoading.value = false
  }
}

function openResetPassword(user) {
  resetPwdUser.value = user
  resetPwdResult.value = null
  resetPwdError.value = ''
  resetPwdInst.show()
}

async function confirmResetPassword() {
  if (!resetPwdUser.value) return
  resetPwdLoading.value = true
  resetPwdError.value = ''
  resetPwdResult.value = null

  const chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'
  let newPassword = ''
  for (let i = 0; i < 12; i++) {
    newPassword += chars.charAt(Math.floor(Math.random() * chars.length))
  }

  try {
    if (resetPwdUser.value.auth_id) {
      const { error: authError } = await supabaseAdmin.auth.admin.updateUserById(
        resetPwdUser.value.auth_id,
        { password: newPassword }
      )
      if (authError) throw new Error('Auth: ' + authError.message)
    }

    const { error: dbError } = await supabase.from('users').update({
      password_must_change: true,
      password_changed_at: null
    }).eq('id', resetPwdUser.value.id)
    if (dbError) throw dbError

    resetPwdResult.value = newPassword
    await loadUsers()
  } catch (err) {
    resetPwdError.value = err.message || 'Erro ao repor senha'
  } finally {
    resetPwdLoading.value = false
  }
}

function copyPassword() {
  if (resetPwdResult.value) navigator.clipboard.writeText(resetPwdResult.value)
}

onMounted(async () => {
  formInst = new Modal(formModal.value)
  delInst = new Modal(deleteModal.value)
  rejectInst = new Modal(rejectModal.value)
  resetPwdInst = new Modal(resetPwdModal.value)
  detailInst = new Modal(detailModal.value)

  await loadUsers()
  await loadPendingCount()

  channel = supabase
    .channel('admin-users-list')
    .on('postgres_changes', { event: '*', schema: 'public', table: 'users' }, () => {
      loadUsers()
      loadPendingCount()
    })
    .subscribe()
})

onBeforeUnmount(() => {
  formInst?.dispose()
  delInst?.dispose()
  rejectInst?.dispose()
  resetPwdInst?.dispose()
  detailInst?.dispose()
  if (channel) supabase.removeChannel(channel)
})
</script>

<style scoped>
.admin-list-page { padding: 1.5rem; }
.section-group { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.section-body { padding: 0.25rem 0; }
.list-row { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1.25rem; transition: background 0.15s; gap: 1rem; }
.list-row:hover { background: #f8fafc; }
.list-row-left { display: flex; align-items: center; gap: 0.875rem; min-width: 0; flex: 1; }
.user-avatar { width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; border: 2px solid #e2e8f0; }
.user-avatar img { width: 100%; height: 100%; object-fit: cover; }
.user-avatar i { font-size: 1.25rem; color: #94a3b8; }
.list-info { min-width: 0; }
.list-title { font-size: 0.875rem; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.list-sub { font-size: 0.75rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 280px; }
.list-row-center { flex-shrink: 0; }
.list-meta { display: flex; gap: 1rem; }
.meta-item { font-size: 0.75rem; color: #64748b; white-space: nowrap; }
.list-row-right { display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; }
.approval-badge { font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 6px; }
.approval-badge.approved { background: #dcfce7; color: #15803d; }
.approval-badge.pending { background: #fef9c3; color: #a16207; }
.approval-badge.rejected { background: #fee2e2; color: #dc2626; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.status-dot.active { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.15); }
.status-dot.inactive { background: #cbd5e1; }
.action-btns { display: flex; gap: 0.25rem; }
.action-btn { width: 32px; height: 32px; border: none; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; background: transparent; font-size: 0.85rem; }
.action-btn.edit { color: #3b82f6; }
.action-btn.edit:hover { background: #eff6ff; }
.action-btn.delete { color: #ef4444; }
.action-btn.delete:hover { background: #fef2f2; }
.action-btn.reset-pwd { color: #f59e0b; }
.action-btn.reset-pwd:hover { background: #fef3c7; }
.delete-icon { width: 56px; height: 56px; border-radius: 50%; background: #fef2f2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto; }
.empty-state { text-align: center; padding: 4rem 2rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
.edit-photo-preview { width: 100px; height: 100px; border-radius: 50%; background: #f1f5f9; border: 3px solid #e2e8f0; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.edit-photo-preview img { width: 100%; height: 100%; object-fit: cover; }
.edit-photo-preview .photo-placeholder { color: #94a3b8; font-size: 2.5rem; }
.filter-bar { margin-bottom: 0.75rem; }
</style>
