<template>
  <div class="admin-page p-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <h2 class="mb-0">{{ t('admin.users_title') }}</h2>
      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-sm" :class="filter === 'all' ? 'btn-primary' : 'btn-outline-primary'" @click="filter = 'all'">{{ t('admin.users_all') }}</button>
        <button class="btn btn-sm" :class="filter === 'pending' ? 'btn-warning' : 'btn-outline-warning'" @click="filter = 'pending'">
          {{ t('admin.users_pending') }} <span v-if="pendingCount > 0" class="badge bg-light text-dark ms-1">{{ pendingCount }}</span>
        </button>
        <button class="btn btn-sm" :class="filter === 'approved' ? 'btn-success' : 'btn-outline-success'" @click="filter = 'approved'">{{ t('admin.users_approved') }}</button>
        <button class="btn btn-sm" :class="filter === 'rejected' ? 'btn-danger' : 'btn-outline-danger'" @click="filter = 'rejected'">{{ t('admin.users_rejected') }}</button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">{{ t('common.loading') }}</div>
    <div v-else-if="filteredUsers.length === 0" class="text-center py-5 text-muted">
      {{ t('admin.users_empty') }}
    </div>
    <div v-else class="card">
      <div class="card-body">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>{{ t('admin.user_col') }}</th>
              <th>Email</th>
              <th>{{ t('admin.users_phone') }}</th>
              <th>{{ t('admin.approval_col') }}</th>
              <th>{{ t('admin.users_status') }}</th>
              <th>{{ t('admin.last_login') }}</th>
              <th>{{ t('admin.users_password') }}</th>
              <th class="text-end">{{ t('admin.actions_col') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in filteredUsers" :key="user.id">
              <td>
                <strong>{{ user.name }}</strong>
                <small class="d-block text-muted">@{{ user.username }}</small>
              </td>
              <td>{{ user.email }}</td>
              <td>{{ user.phone || '—' }}</td>
              <td>
                <span :class="getStatusBadge(user).class">{{ getStatusBadge(user).text }}</span>
              </td>
              <td>
                <span :class="getLockStatus(user).class">{{ getLockStatus(user).text }}</span>
              </td>
              <td><small class="text-muted">{{ formatDate(user.last_login) }}</small></td>
              <td>
                <span v-if="user.password_must_change" class="badge bg-warning text-dark">{{ t('admin.users_change_password') }}</span>
                <span v-else-if="user.password_changed_at" class="badge bg-info text-dark">{{ t('admin.users_password_changed') }} {{ formatDateOnly(user.password_changed_at) }}</span>
                <span v-else class="badge bg-secondary">{{ t('admin.users_password_not_changed') }}</span>
              </td>
              <td class="text-end">
                <div class="btn-group btn-group-sm" v-if="user.approval_status === 'pending'">
                  <button class="btn btn-success" @click="approve(user)">{{ t('admin.approve') }}</button>
                  <button class="btn btn-outline-danger" @click="reject(user)">{{ t('admin.reject') }}</button>
                </div>
                <div class="btn-group btn-group-sm" v-else-if="user.approval_status === 'rejected'">
                  <button class="btn btn-success" @click="approve(user)">{{ t('admin.users_reactivate') }}</button>
                  <button class="btn btn-outline-danger" @click="destroy(user)">{{ t('common.delete') }}</button>
                </div>
                <div class="btn-group btn-group-sm" v-else>
                  <button class="btn btn-outline-primary" @click="openEdit(user)"><i class="bi bi-pencil"></i></button>
                  <button class="btn btn-outline-info" @click="openDetail(user)"><i class="bi bi-eye"></i></button>
                  <button class="btn btn-outline-danger" @click="destroy(user)">{{ t('common.delete') }}</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal" v-if="showRejectModal" @click.self="showRejectModal = false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ t('admin.reject') }}</h5>
            <button type="button" class="btn-close" @click="showRejectModal = false"></button>
          </div>
          <div class="modal-body">
            <p>{{ t('admin.users_confirm_reject_text') }} <strong>{{ rejectingUser?.name }}</strong>?</p>
            <div class="mb-2">
              <label class="form-label">{{ t('admin.users_reject_reason') }}</label>
              <textarea class="form-control" rows="3" v-model="rejectReason"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showRejectModal = false">{{ t('common.cancel') }}</button>
            <button class="btn btn-danger" @click="confirmReject">{{ t('admin.users_confirm_reject') }}</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" v-if="showDetailModal" @click.self="showDetailModal = false">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ t('admin.users_title') }}</h5>
            <button type="button" class="btn-close" @click="showDetailModal = false"></button>
          </div>
          <div class="modal-body" v-if="!detailLoading">
            <div class="row">
              <div class="col-md-6">
                <h6>{{ t('admin.users_user_data') }}</h6>
                <table class="table table-sm table-borderless">
                  <tr><th>{{ t('admin.users_full_name') }}</th><td>{{ detailUser?.name }}</td></tr>
                  <tr><th>Username</th><td>@{{ detailUser?.username }}</td></tr>
                  <tr><th>Email</th><td>{{ detailUser?.email }}</td></tr>
                  <tr><th>{{ t('admin.users_phone') }}</th><td>{{ detailUser?.phone || '—' }}</td></tr>
                  <tr><th>Role</th><td><span class="badge bg-info text-dark">{{ detailUser?.role }}</span></td></tr>
                  <tr><th>{{ t('admin.users_approval') }}</th><td><span :class="getStatusBadge(detailUser).class">{{ getStatusBadge(detailUser).text }}</span></td></tr>
                  <tr><th>{{ t('admin.users_status') }}</th><td><span :class="getLockStatus(detailUser).class">{{ getLockStatus(detailUser).text }}</span></td></tr>
                  <tr><th>{{ t('admin.users_last_login') }}</th><td>{{ formatDate(detailUser?.last_login) }}</td></tr>
                  <tr><th>{{ t('admin.users_password') }}</th><td>
                    <span v-if="detailUser?.password_must_change" class="badge bg-warning text-dark">{{ t('admin.users_change_password') }}</span>
                    <span v-else-if="detailUser?.password_changed_at" class="badge bg-info text-dark">{{ t('admin.users_password_changed') }} {{ formatDateOnly(detailUser.password_changed_at) }}</span>
                    <span v-else class="badge bg-secondary">{{ t('admin.users_password_not_changed') }}</span>
                  </td></tr>
                  <tr><th>{{ t('admin.lock_title') }}</th><td v-if="detailUser?.locked_at">Até {{ formatDate(detailUser.locked_at) }} - {{ detailUser?.locked_reason || t('admin.users_no_reason') }}</td><td v-else>—</td></tr>
                  <tr><th>{{ t('admin.users_registered_at') }}</th><td>{{ formatDate(detailUser?.created_at) }}</td></tr>
                </table>
              </div>
              <div class="col-md-6">
                <h6>{{ t('admin.users_company_data') }}</h6>
                <table class="table table-sm table-borderless" v-if="detailCompany">
                  <tr><th>{{ t('admin.users_company_name') }}</th><td>{{ detailCompany?.company_name }}</td></tr>
                  <tr><th>NIF</th><td>{{ detailCompany?.nif || '—' }}</td></tr>
                  <tr><th>{{ t('admin.users_address') }}</th><td>{{ detailCompany?.address }}</td></tr>
                  <tr><th>{{ t('admin.users_phone') }}</th><td>{{ detailCompany?.phone }}</td></tr>
                  <tr><th>Email</th><td>{{ detailCompany?.email }}</td></tr>
                  <tr><th>{{ t('admin.users_service') }}</th><td>{{ detailCompany?.service }}</td></tr>
                  <tr><th>{{ t('admin.users_case_description') }}</th><td>{{ detailCompany?.case_description }}</td></tr>
                  <tr><th>Logo</th><td v-if="detailCompany?.logo"><img :src="detailCompany.logo" alt="Logo" style="max-height: 60px;"></td><td v-else>—</td></tr>
                  <tr><th>{{ t('admin.users_published') }}</th><td><span :class="detailCompany?.is_published ? 'badge bg-success' : 'badge bg-secondary'">{{ detailCompany?.is_published ? t('admin.users_yes') : t('admin.users_no') }}</span></td></tr>
                </table>
                <div v-if="!detailCompany" class="text-muted">{{ t('admin.users_no_company') }}</div>
              </div>
            </div>
          </div>
          <div class="modal-body text-center" v-if="detailLoading">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ t('common.loading') }}</span></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showDetailModal = false">{{ t('common.close') }}</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" v-if="showEditModal" @click.self="showEditModal = false">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ t('admin.users_title') }}</h5>
            <button type="button" class="btn-close" @click="showEditModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="row">
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
                  <label class="form-label">{{ t('admin.users_full_name') }} *</label>
                  <input type="text" class="form-control" v-model="editForm.name" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" :value="editForm.email" disabled>
                </div>
                <div class="mb-3">
                  <label class="form-label">{{ t('admin.users_phone') }}</label>
                  <input type="tel" class="form-control" v-model="editForm.phone">
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">{{ t('admin.users_account_status') }}</label>
                    <select class="form-select" v-model="editForm.approval_status">
                      <option value="approved">{{ t('admin.users_approved') }}</option>
                      <option value="pending">{{ t('admin.users_pending') }}</option>
                      <option value="rejected">{{ t('admin.users_rejected') }}</option>
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">{{ t('admin.users_status') }}</label>
                    <select class="form-select" v-model="editForm.status">
                      <option :value="1">{{ t('admin.services_active') }}</option>
                      <option :value="0">{{ t('admin.services_inactive') }}</option>
                    </select>
                  </div>
                </div>
                <div v-if="editError" class="alert alert-danger py-2">{{ editError }}</div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showEditModal = false">{{ t('common.cancel') }}</button>
            <button class="btn btn-primary" :disabled="editSaving" @click="saveEdit">
              <span v-if="editSaving" class="spinner-border spinner-border-sm me-1"></span>
              {{ editSaving ? t('admin.settings_save') : t('admin.settings_save') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { supabase } from '@/lib/supabase'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()

const users = ref([])
const loading = ref(false)
const filter = ref('pending')
const pendingCount = ref(0)
const showRejectModal = ref(false)
const rejectingUser = ref(null)
const rejectReason = ref('')
const showDetailModal = ref(false)
const detailUser = ref(null)
const detailCompany = ref(null)
const detailLoading = ref(false)
const showEditModal = ref(false)
const editingUser = ref(null)
const editForm = ref({ name: '', email: '', phone: '', approval_status: 'approved', status: 1, photo: '' })
const editSaving = ref(false)
const editError = ref('')
const editPhotoMsg = ref('')
const editPhotoError = ref(false)

const filteredUsers = computed(() => {
  if (filter.value === 'all') return users.value
  return users.value.filter(u => u.approval_status === filter.value)
})

const loadUsers = async () => {
  loading.value = true
  try {
    const { data, error } = await supabase.from('users').select('id, auth_id, created_at, updated_at, name, email, username, phone, role, position, permissions, approval_status, status, photo, password_must_change, password_changed_at, locked_at, locked_reason').order('created_at', { ascending: false })
    if (!error) users.value = data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const loadPendingCount = async () => {
  try {
    const { count, error } = await supabase.from('users').select('*', { count: 'exact', head: true }).eq('approval_status', 'pending')
    if (!error) pendingCount.value = count || 0
  } catch (error) {
    pendingCount.value = 0
  }
}

const openDetail = async (user) => {
  detailLoading.value = true
  try {
    detailUser.value = user
    const { data: company, error } = await supabase.from('companies').select('*').eq('user_id', user.id).single()
    if (!error) detailCompany.value = company
    showDetailModal.value = true
  } catch (error) {
    alert(t('admin.users_error_loading'))
  } finally {
    detailLoading.value = false
  }
}

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('pt-PT', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const formatDateOnly = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('pt-PT', { year: 'numeric', month: 'short', day: 'numeric' })
}

const getStatusBadge = (user) => {
  if (user.approval_status === 'pending') return { class: 'badge bg-warning text-dark', text: t('admin.users_pending') }
  if (user.approval_status === 'approved') return { class: 'badge bg-success', text: t('admin.users_approved') }
  return { class: 'badge bg-danger', text: t('admin.users_rejected') }
}

const getLockStatus = (user) => {
  if (user.locked_at) return { class: 'badge bg-danger', text: t('admin.lock_title') }
  if (user.status === 0) return { class: 'badge bg-secondary', text: t('admin.services_inactive') }
  return { class: 'badge bg-success', text: t('admin.services_active') }
}

const approve = async (user) => {
  if (!confirm(`${t('admin.users_confirm_approve')} ${user.name}?`)) return
  try {
    const { error } = await supabase.from('users').update({ approval_status: 'approved', approved_at: new Date() }).eq('id', user.id)
    if (!error) {
      await loadUsers()
      await loadPendingCount()
    }
  } catch (error) {
    alert(error.message || t('admin.error_approve'))
  }
}

const reject = (user) => {
  rejectingUser.value = user
  rejectReason.value = ''
  showRejectModal.value = true
}

const confirmReject = async () => {
  if (!rejectingUser.value) return
  try {
    const { error } = await supabase.from('users').update({ approval_status: 'rejected', rejection_reason: rejectReason.value }).eq('id', rejectingUser.value.id)
    if (!error) {
      showRejectModal.value = false
      await loadUsers()
      await loadPendingCount()
    }
  } catch (error) {
    alert(error.message || t('admin.error_reject'))
  }
}

const destroy = async (user) => {
  if (!confirm(`${t('admin.users_confirm_delete')} ${user.name}?`)) return
  try {
    const { error } = await supabase.from('users').delete().eq('id', user.id)
    if (!error) {
      await loadUsers()
    }
  } catch (error) {
    alert(error.message || t('admin.error_delete'))
  }
}

const openEdit = (user) => {
  editingUser.value = user
  editForm.value = {
    name: user.name || '',
    email: user.email || '',
    phone: user.phone || '',
    approval_status: user.approval_status || 'approved',
    status: user.status ?? 1,
    photo: user.photo || ''
  }
  editError.value = ''
  editPhotoMsg.value = ''
  editPhotoError.value = false
  showEditModal.value = true
}

const saveEdit = async () => {
  if (!editingUser.value) return
  editSaving.value = true
  editError.value = ''
  try {
    const { error } = await supabase.from('users').update({
      name: editForm.value.name,
      phone: editForm.value.phone,
      approval_status: editForm.value.approval_status,
      status: editForm.value.status
    }).eq('id', editingUser.value.id)
    if (!error) {
      showEditModal.value = false
      await loadUsers()
    }
  } catch (error) {
    editError.value = error.message || t('admin.error_save')
  } finally {
    editSaving.value = false
  }
}

const onEditPhotoChange = async (e) => {
  const file = e.target.files?.[0]
  if (!file || !editingUser.value) return
  editPhotoMsg.value = ''
  editPhotoError.value = false
  const fileExt = file.name.split('.').pop()
  const fileName = `${editingUser.value.id}.${fileExt}`
  const { data, error: uploadError } = await supabase.storage.from('photos').upload(fileName, file, { upsert: true })
  if (!uploadError && data) {
    const { data: urlData } = supabase.storage.from('photos').getPublicUrl(fileName)
    editForm.value.photo = urlData.publicUrl
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

onMounted(async () => {
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

let channel = null

onBeforeUnmount(() => {
  if (channel) supabase.removeChannel(channel)
})
</script>

<style scoped>
.admin-page {
  background: #f8f9fa;
  min-height: 100vh;
}

.modal {
  display: block;
  background: rgba(0, 0, 0, 0.5);
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1050;
}

.card {
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
}

.edit-photo-preview {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: #f1f5f9;
  border: 3px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.edit-photo-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.edit-photo-preview .photo-placeholder {
  color: #94a3b8;
  font-size: 2.5rem;
}
</style>
