<template>
  <div class="admin-page p-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <h2 class="mb-0">Clientes</h2>
      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-sm" :class="filter === 'all' ? 'btn-primary' : 'btn-outline-primary'" @click="filter = 'all'">Todos</button>
        <button class="btn btn-sm" :class="filter === 'pending' ? 'btn-warning' : 'btn-outline-warning'" @click="filter = 'pending'">
          Pendentes <span v-if="pendingCount > 0" class="badge bg-light text-dark ms-1">{{ pendingCount }}</span>
        </button>
        <button class="btn btn-sm" :class="filter === 'approved' ? 'btn-success' : 'btn-outline-success'" @click="filter = 'approved'">Aprovados</button>
        <button class="btn btn-sm" :class="filter === 'rejected' ? 'btn-danger' : 'btn-outline-danger'" @click="filter = 'rejected'">Rejeitados</button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">A carregar...</div>
    <div v-else-if="filteredUsers.length === 0" class="text-center py-5 text-muted">
      Nenhum cliente encontrado.
    </div>
    <div v-else class="card">
      <div class="card-body">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Utilizador</th>
              <th>Email</th>
              <th>Telefone</th>
              <th>Aprovação</th>
              <th>Status</th>
              <th>Último Login</th>
              <th>Senha</th>
              <th class="text-end">Ações</th>
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
                <span v-if="user.password_must_change" class="badge bg-warning text-dark">Trocar senha</span>
                <span v-else-if="user.password_changed_at" class="badge bg-info text-dark">Alterada {{ formatDateOnly(user.password_changed_at) }}</span>
                <span v-else class="badge bg-secondary">Não alterada</span>
              </td>
              <td class="text-end">
                <div class="btn-group btn-group-sm" v-if="user.approval_status === 'pending'">
                  <button class="btn btn-success" @click="approve(user)">Aprovar</button>
                  <button class="btn btn-outline-danger" @click="reject(user)">Rejeitar</button>
                </div>
                <div class="btn-group btn-group-sm" v-else-if="user.approval_status === 'rejected'">
                  <button class="btn btn-success" @click="approve(user)">Reativar</button>
                  <button class="btn btn-outline-danger" @click="destroy(user)">Eliminar</button>
                </div>
                <div class="btn-group btn-group-sm" v-else>
                  <button class="btn btn-outline-primary" @click="openEdit(user)"><i class="bi bi-pencil"></i></button>
                  <button class="btn btn-outline-info" @click="openDetail(user)"><i class="bi bi-eye"></i></button>
                  <button class="btn btn-outline-danger" @click="destroy(user)">Eliminar</button>
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
            <h5 class="modal-title">Rejeitar cliente</h5>
            <button type="button" class="btn-close" @click="showRejectModal = false"></button>
          </div>
          <div class="modal-body">
            <p>Tem a certeza que pretende rejeitar <strong>{{ rejectingUser?.name }}</strong>?</p>
            <div class="mb-2">
              <label class="form-label">Motivo (opcional)</label>
              <textarea class="form-control" rows="3" v-model="rejectReason"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showRejectModal = false">Cancelar</button>
            <button class="btn btn-danger" @click="confirmReject">Confirmar rejeição</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" v-if="showDetailModal" @click.self="showDetailModal = false">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Detalhes do Cliente</h5>
            <button type="button" class="btn-close" @click="showDetailModal = false"></button>
          </div>
          <div class="modal-body" v-if="!detailLoading">
            <div class="row">
              <div class="col-md-6">
                <h6>Dados do Utilizador</h6>
                <table class="table table-sm table-borderless">
                  <tr><th>Nome</th><td>{{ detailUser?.name }}</td></tr>
                  <tr><th>Username</th><td>@{{ detailUser?.username }}</td></tr>
                  <tr><th>Email</th><td>{{ detailUser?.email }}</td></tr>
                  <tr><th>Telefone</th><td>{{ detailUser?.phone || '—' }}</td></tr>
                  <tr><th>Role</th><td><span class="badge bg-info text-dark">{{ detailUser?.role }}</span></td></tr>
                  <tr><th>Aprovação</th><td><span :class="getStatusBadge(detailUser).class">{{ getStatusBadge(detailUser).text }}</span></td></tr>
                  <tr><th>Status</th><td><span :class="getLockStatus(detailUser).class">{{ getLockStatus(detailUser).text }}</span></td></tr>
                  <tr><th>Último Login</th><td>{{ formatDate(detailUser?.last_login) }}</td></tr>
                  <tr><th>Senha</th><td>
                    <span v-if="detailUser?.password_must_change" class="badge bg-warning text-dark">Trocar senha</span>
                    <span v-else-if="detailUser?.password_changed_at" class="badge bg-info text-dark">Alterada {{ formatDateOnly(detailUser.password_changed_at) }}</span>
                    <span v-else class="badge bg-secondary">Não alterada</span>
                  </td></tr>
                  <tr><th>Bloqueio</th><td v-if="detailUser?.locked_at">Até {{ formatDate(detailUser.locked_at) }} - {{ detailUser?.locked_reason || 'Sem motivo' }}</td><td v-else>—</td></tr>
                  <tr><th>Registado em</th><td>{{ formatDate(detailUser?.created_at) }}</td></tr>
                </table>
              </div>
              <div class="col-md-6">
                <h6>Dados da Empresa</h6>
                <table class="table table-sm table-borderless" v-if="detailCompany">
                  <tr><th>Nome da Empresa</th><td>{{ detailCompany?.company_name }}</td></tr>
                  <tr><th>NIF</th><td>{{ detailCompany?.nif || '—' }}</td></tr>
                  <tr><th>Endereço</th><td>{{ detailCompany?.address }}</td></tr>
                  <tr><th>Telefone</th><td>{{ detailCompany?.phone }}</td></tr>
                  <tr><th>Email</th><td>{{ detailCompany?.email }}</td></tr>
                  <tr><th>Serviço</th><td>{{ detailCompany?.service }}</td></tr>
                  <tr><th>Descrição do Caso</th><td>{{ detailCompany?.case_description }}</td></tr>
                  <tr><th>Logo</th><td v-if="detailCompany?.logo"><img :src="detailCompany.logo" alt="Logo" style="max-height: 60px;"></td><td v-else>—</td></tr>
                  <tr><th>Publicado no site</th><td><span :class="detailCompany?.is_published ? 'badge bg-success' : 'badge bg-secondary'">{{ detailCompany?.is_published ? 'Sim' : 'Não' }}</span></td></tr>
                </table>
                <div v-if="!detailCompany" class="text-muted">Sem dados de empresa configurados</div>
              </div>
            </div>
          </div>
          <div class="modal-body text-center" v-if="detailLoading">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">A carregar...</span></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showDetailModal = false">Fechar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal" v-if="showEditModal" @click.self="showEditModal = false">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Editar Cliente</h5>
            <button type="button" class="btn-close" @click="showEditModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-4 text-center mb-3">
                <div class="edit-photo-preview mx-auto mb-2">
                  <img v-if="editForm.photo" :src="editForm.photo" alt="Foto">
                  <div v-else class="photo-placeholder"><i class="bi bi-person-fill"></i></div>
                </div>
                <label class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-camera-fill"></i> Alterar foto
                  <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" @change="onEditPhotoChange" hidden>
                </label>
                <div v-if="editPhotoMsg" class="small mt-1" :class="editPhotoError ? 'text-danger' : 'text-success'">{{ editPhotoMsg }}</div>
              </div>
              <div class="col-md-8">
                <div class="mb-3">
                  <label class="form-label">Nome completo *</label>
                  <input type="text" class="form-control" v-model="editForm.name" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" :value="editForm.email" disabled>
                </div>
                <div class="mb-3">
                  <label class="form-label">Telefone</label>
                  <input type="tel" class="form-control" v-model="editForm.phone">
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Estado da conta</label>
                    <select class="form-select" v-model="editForm.approval_status">
                      <option value="approved">Aprovado</option>
                      <option value="pending">Pendente</option>
                      <option value="rejected">Rejeitado</option>
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" v-model="editForm.status">
                      <option :value="1">Ativo</option>
                      <option :value="0">Inativo</option>
                    </select>
                  </div>
                </div>
                <div v-if="editError" class="alert alert-danger py-2">{{ editError }}</div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showEditModal = false">Cancelar</button>
            <button class="btn btn-primary" :disabled="editSaving" @click="saveEdit">
              <span v-if="editSaving" class="spinner-border spinner-border-sm me-1"></span>
              {{ editSaving ? 'A guardar...' : 'Guardar alterações' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
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

const authHeader = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } })

const loadUsers = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/admin/users', authHeader())
    if (response.data.success) {
      users.value = response.data.data.users
    }
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const loadPendingCount = async () => {
  try {
    const response = await axios.get('/api/admin/users/pending/count', authHeader())
    if (response.data.success) {
      pendingCount.value = response.data.data.count
    }
  } catch (error) {
    pendingCount.value = 0
  }
}

const openDetail = async (user) => {
  detailLoading.value = true
  try {
    const response = await axios.get(`/api/admin/users/${user.id}`, authHeader())
    if (response.data.success) {
      detailUser.value = response.data.data.user
      detailCompany.value = response.data.data.company
      showDetailModal.value = true
    }
  } catch (error) {
    alert('Erro ao carregar detalhes')
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
  if (user.approval_status === 'pending') return { class: 'badge bg-warning text-dark', text: 'Pendente' }
  if (user.approval_status === 'approved') return { class: 'badge bg-success', text: 'Aprovado' }
  return { class: 'badge bg-danger', text: 'Rejeitado' }
}

const getLockStatus = (user) => {
  if (user.locked_at) return { class: 'badge bg-danger', text: 'Bloqueado' }
  if (user.status === 0) return { class: 'badge bg-secondary', text: 'Inativo' }
  return { class: 'badge bg-success', text: 'Ativo' }
}

const approve = async (user) => {
  if (!confirm(`Aprovar a conta de ${user.name}?`)) return
  try {
    const response = await axios.post(`/api/admin/users/${user.id}/approve`, {}, authHeader())
    if (response.data.success) {
      await loadUsers()
      await loadPendingCount()
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Erro ao aprovar')
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
    const response = await axios.post(`/api/admin/users/${rejectingUser.value.id}/reject`, {
      reason: rejectReason.value
    }, authHeader())
    if (response.data.success) {
      showRejectModal.value = false
      await loadUsers()
      await loadPendingCount()
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Erro ao rejeitar')
  }
}

const destroy = async (user) => {
  if (!confirm(`Eliminar definitivamente ${user.name}?`)) return
  try {
    const response = await axios.delete(`/api/admin/users/${user.id}`, authHeader())
    if (response.data.success) {
      await loadUsers()
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Erro ao eliminar')
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
    const response = await axios.put(`/api/admin/users/${editingUser.value.id}`, {
      name: editForm.value.name,
      phone: editForm.value.phone,
      approval_status: editForm.value.approval_status,
      status: editForm.value.status
    }, authHeader())
    if (response.data.success) {
      showEditModal.value = false
      await loadUsers()
    }
  } catch (error) {
    editError.value = error.response?.data?.message || 'Erro ao guardar'
  } finally {
    editSaving.value = false
  }
}

const onEditPhotoChange = async (e) => {
  const file = e.target.files?.[0]
  if (!file || !editingUser.value) return
  editPhotoMsg.value = ''
  editPhotoError.value = false
  const formData = new FormData()
  formData.append('photo', file)
  try {
    const response = await axios.post('/api/auth/upload-photo', formData, {
      headers: { Authorization: `Bearer ${authStore.token}`, 'Content-Type': 'multipart/form-data' }
    })
    if (response.data.success) {
      editForm.value.photo = response.data.data.photo
      editPhotoError.value = false
      editPhotoMsg.value = 'Foto atualizada.'
      await loadUsers()
    }
  } catch (error) {
    editPhotoError.value = true
    editPhotoMsg.value = error.response?.data?.message || 'Erro ao carregar foto'
  }
  e.target.value = ''
}

onMounted(async () => {
  await loadUsers()
  await loadPendingCount()
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
