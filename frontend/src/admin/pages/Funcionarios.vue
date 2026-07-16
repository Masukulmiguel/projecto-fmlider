<template>
  <div class="admin-page p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h1 class="page-title">{{ t('admin.employees_title') }}</h1>
        <p class="page-subtitle mb-0">{{ t('admin.employees_description') }}</p>
      </div>
      <button class="btn btn-primary" @click="openForm()">
        <i class="bi bi-plus-lg me-1"></i> {{ t('admin.employees_new') }}
      </button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else-if="items.length === 0" class="empty-state">
      <div class="empty-icon"><i class="bi bi-person-badge"></i></div>
      <p>{{ t('admin.employees_add_first') }}</p>
      <button class="btn btn-primary btn-sm" @click="openForm()">
        <i class="bi bi-plus-lg me-1"></i> {{ t('admin.employees_new') }}
      </button>
    </div>

    <div v-else class="section-group">
      <div v-for="f in items" :key="f.id" class="employee-row">
        <div class="employee-info">
          <div class="employee-avatar">
            <img v-if="f.photo" :src="f.photo" :alt="f.name">
            <span v-else class="avatar-initials">{{ initials(f.name) }}</span>
          </div>
          <div class="employee-details">
            <div class="employee-name">{{ f.name }}</div>
            <div class="employee-email">{{ f.email }}</div>
          </div>
        </div>

        <div class="employee-meta">
          <span class="meta-dept">
            <i class="bi bi-building"></i> {{ deptLabels[f.departamento] || f.departamento || '—' }}
          </span>
          <span class="meta-role">{{ f.position || '—' }}</span>
        </div>

        <div class="employee-status">
          <span class="status-dot" :class="{ active: isActive(f) }"></span>
          <span class="status-label">{{ statusLabel(f) }}</span>
        </div>

        <div class="employee-login">
          <small>{{ f.last_login ? formatDate(f.last_login) : t('admin.employees_never') }}</small>
        </div>

        <div class="employee-actions">
          <button class="btn-icon btn-reset-pwd" @click="openResetPassword(f)" :title="'Repor Senha'">
            <i class="bi bi-key"></i>
          </button>
          <button class="btn-icon btn-edit" @click="openForm(f)" :title="t('admin.employees_action_edit')">
            <i class="bi bi-pencil-square"></i>
          </button>
          <button class="btn-icon btn-delete" @click="openDeleteModal(f)" :title="t('admin.employees_action_delete')">
            <i class="bi bi-trash3"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showForm" class="modal-backdrop-custom" @click.self="closeForm">
      <div class="modal-dialog-centered-custom">
        <div class="modal-box">
          <div class="modal-box-header">
            <h5>
              <i class="bi bi-person-badge-fill me-2"></i>
              {{ editing ? t('admin.employees_edit_title') : t('admin.employees_new_title') }}
            </h5>
            <button type="button" class="btn-close-modal" @click="closeForm">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <form @submit.prevent="handleSubmit" novalidate>
            <div class="modal-box-body">
              <div v-if="errorMessage" class="alert alert-danger alert-sm">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ errorMessage }}
              </div>

              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">{{ t('admin.employees_full_name') }}</label>
                  <input v-model="form.name" type="text" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Username *</label>
                  <input v-model="form.username" type="text" class="form-control" :disabled="!!editing" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email *</label>
                  <input v-model="form.email" type="email" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ t('admin.employees_phone') }}</label>
                  <input v-model="form.phone" type="text" class="form-control">
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ t('admin.employees_position_label') }}</label>
                  <input v-model="form.position" type="text" class="form-control" :placeholder="t('admin.employees_position_placeholder')">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Bilhete de Identidade</label>
                  <div class="input-group">
                    <input v-model="form.bi" type="text" class="form-control" placeholder="006151112LA041" maxlength="14" :class="{'is-invalid': form.bi && !isValidBiFormat(form.bi)}" @blur="onBiBlur">
                    <button class="btn btn-outline-primary" type="button" @click="consultarBI" :disabled="consultingBi || !form.bi">
                      <span v-if="consultingBi" class="spinner-border spinner-border-sm"></span>
                      <i v-else class="bi bi-search"></i>
                    </button>
                  </div>
                  <div v-if="form.bi && !isValidBiFormat(form.bi)" class="invalid-feedback d-block">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>BI deve ter 14 caracteres (ex: 006151112LA041)
                  </div>
                  <div v-if="biLookupStatus" class="small mt-1" :class="biLookupStatus === 'error' ? 'text-danger' : 'text-success'">
                    <i :class="biLookupStatus === 'error' ? 'bi bi-x-circle-fill' : 'bi bi-check-circle-fill'" class="me-1"></i>
                    {{ biLookupMessage }}
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Departamento *</label>
                  <select v-model="form.departamento" class="form-select" @change="onDepartamentoChange" required>
                    <option value="">Selecione o departamento</option>
                    <option value="certificacao">Certificação</option>
                    <option value="documentacao">Documentação</option>
                    <option value="licenciamentos">Licenciamentos</option>
                    <option value="facturacao">Facturação</option>
                    <option value="logistica">Logística</option>
                    <option value="administracao">Administração</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ editing ? t('admin.employees_new_password') : t('admin.employees_password_label') }}</label>
                  <input v-model="form.password" type="password" class="form-control" :required="!editing" minlength="6">
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ t('admin.employees_position') }}</label>
                  <input v-model="form.role_label" type="text" class="form-control" placeholder="Ex: Gerente">
                </div>

                <div class="col-12">
                  <label class="form-label fw-bold mb-2">Foto do funcionário</label>
                  <div class="photo-upload-area" @click="triggerFileInput" @dragover.prevent @drop.prevent="onDrop">
                    <input ref="fileInput" type="file" accept="image/*" class="d-none" @change="onFileChange">
                    <template v-if="form.photo">
                      <img :src="form.photo" class="photo-preview" alt="Pré-visualização">
                      <button type="button" class="photo-remove-btn" @click.stop="removePhoto" title="Remover foto">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </template>
                    <template v-else>
                      <div class="photo-placeholder">
                        <i class="bi bi-camera"></i>
                        <span>Clique ou arraste uma foto</span>
                        <small>Max. 2 MB</small>
                      </div>
                    </template>
                  </div>
                  <div v-if="photoError" class="text-danger small mt-1">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ photoError }}
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label fw-bold">{{ t('admin.employees_access_permissions') }}</label>
                  <div v-if="form.departamento" class="perm-dept-info mb-2">
                    <small class="text-muted">
                      <i class="bi bi-info-circle me-1"></i>
                      Permissões do departamento: <strong>{{ deptLabels[form.departamento] }}</strong>
                    </small>
                  </div>
                  <div class="perm-grid">
                    <label v-for="group in permissionGroups" :key="group.label" class="perm-group">
                      <div class="perm-group-title">
                        <i :class="group.icon"></i> {{ group.label }}
                      </div>
                      <div class="perm-items">
                        <label v-for="p in group.perms" :key="p" class="perm-item">
                          <input type="checkbox" :value="p" v-model="form.permissions">
                          <span>{{ permLabel(p) }}</span>
                        </label>
                      </div>
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-box-footer">
              <button type="button" class="btn btn-outline-secondary" @click="closeForm">{{ t('common.cancel') }}</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                {{ editing ? t('common.edit') : t('admin.employees_new') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-backdrop-custom" @click.self="closeDeleteModal">
      <div class="modal-dialog-centered-custom">
        <div class="modal-box modal-sm">
          <div class="delete-modal-body">
            <div class="delete-icon-circle">
              <i class="bi bi-trash3"></i>
            </div>
            <h5>{{ t('admin.employees_delete_confirm') }}</h5>
            <p class="text-muted mb-1">
              <strong>{{ deleteTarget?.name }}</strong>
            </p>
            <p class="text-muted small mb-0">{{ t('admin.employees_delete_warning') }}</p>
          </div>
          <div class="modal-box-footer">
            <button type="button" class="btn btn-outline-secondary" @click="closeDeleteModal">{{ t('common.cancel') }}</button>
            <button type="button" class="btn btn-danger" @click="handleDelete" :disabled="deleting">
              <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
              <i class="bi bi-trash3 me-1"></i> Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reset Password Modal -->
    <div v-if="showResetPwdModal" class="modal-backdrop-custom" @click.self="closeResetPasswordModal">
      <div class="modal-dialog-centered-custom">
        <div class="modal-box modal-sm">
          <div class="modal-box-header">
            <h5>
              <i class="bi bi-key-fill me-2"></i>Repor Senha
            </h5>
            <button type="button" class="btn-close-modal" @click="closeResetPasswordModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <div class="modal-box-body">
            <p v-if="!resetPwdResult" class="text-muted mb-3">
              Vai gerar uma nova senha para <strong>{{ resetPwdTarget?.name }}</strong>.
            </p>

            <div v-if="!resetPwdResult && !resetPwdLoading" class="text-center py-3">
              <button class="btn btn-warning btn-lg" @click="confirmResetPassword">
                <i class="bi bi-key-fill me-2"></i>Gerar Nova Senha
              </button>
            </div>

            <div v-if="resetPwdLoading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted">A gerar nova senha...</p>
            </div>

            <div v-if="resetPwdResult" class="alert alert-success mb-0">
              <h6 class="alert-heading mb-2"><i class="bi bi-check-circle-fill me-1"></i>Senha gerada com sucesso!</h6>
              <p class="mb-2">Nova senha para <strong>{{ resetPwdTarget?.name }}</strong>:</p>
              <div class="d-flex align-items-center gap-2 p-2 bg-light rounded">
                <code class="fs-5 flex-grow-1 text-center" style="letter-spacing:2px;font-family:monospace;">{{ resetPwdResult }}</code>
                <button class="btn btn-sm btn-outline-primary" @click="copyResetPassword" title="Copiar">
                  <i class="bi bi-clipboard"></i>
                </button>
              </div>
              <small class="text-muted d-block mt-2">O funcionário deverá alterar a senha após iniciar sessão.</small>
            </div>

            <div v-if="resetPwdError" class="alert alert-danger py-2 mb-0">{{ resetPwdError }}</div>
          </div>
          <div class="modal-box-footer">
            <button type="button" class="btn btn-outline-secondary" @click="closeResetPasswordModal">{{ resetPwdResult ? 'Fechar' : t('common.cancel') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Modal } from 'bootstrap'
import { supabase } from '@/lib/supabase'
import { useI18n } from '@/composables/useI18n'
import { useToast } from '@/composables/useToast'
import { useAuthStore } from '@/stores/authStore'

const { t } = useI18n()
const toast = useToast()
const authStore = useAuthStore()
const API_URL = import.meta.env.VITE_API_URL || ''

const items = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const showForm = ref(false)
const editing = ref(null)
const errorMessage = ref('')

const form = reactive({
  name: '',
  username: '',
  email: '',
  phone: '',
  position: '',
  role_label: '',
  departamento: '',
  password: '',
  permissions: [],
  bi: '',
  photo: ''
})

const deptLabels = {
  certificacao: 'Certificação',
  documentacao: 'Documentação',
  licenciamentos: 'Licenciamentos',
  facturacao: 'Facturação',
  logistica: 'Logística',
  administracao: 'Administração'
}

const deptPermissions = {
  certificacao: ['dashboard.view', 'clients.view', 'contactos.view', 'contactos.manage', 'chat.view', 'chat.reply'],
  documentacao: ['dashboard.view', 'documentos.view', 'documentos.manage', 'clients.view', 'contactos.view', 'chat.view'],
  licenciamentos: ['dashboard.view', 'licenciamentos.view', 'licenciamentos.manage', 'clients.view', 'contactos.view', 'chat.view'],
  facturacao: ['dashboard.view', 'clients.view', 'clients.manage', 'contactos.view', 'chat.view'],
  logistica: ['dashboard.view', 'logistica.view', 'logistica.manage', 'motoristas.view', 'motoristas.manage', 'camioes.view', 'camioes.manage', 'entregas.view', 'entregas.manage', 'clients.view', 'contactos.view', 'chat.view'],
  administracao: ['dashboard.view', 'clients.view', 'clients.manage', 'documentos.view', 'documentos.manage', 'contactos.view', 'contactos.manage', 'chat.view', 'chat.reply', 'licenciamentos.view', 'licenciamentos.manage', 'logistica.view', 'logistica.manage', 'motoristas.view', 'motoristas.manage', 'camioes.view', 'camioes.manage', 'entregas.view', 'entregas.manage', 'visitors.view', 'content.manage']
}

const onDepartamentoChange = () => {
  if (form.departamento && deptPermissions[form.departamento]) {
    form.permissions = [...deptPermissions[form.departamento]]
  }
}

const PERM_LABELS = {
  'dashboard.view': 'Dashboard',
  'clients.view': t('admin.perm_view_clients'),
  'clients.manage': t('admin.perm_manage_clients'),
  'embarques.view': t('admin.perm_view_shipments'),
  'embarques.manage': t('admin.perm_manage_shipments'),
  'cotacoes.view': t('admin.perm_view_quotes'),
  'cotacoes.manage': t('admin.perm_manage_quotes'),
  'documentos.view': t('admin.perm_view_documents'),
  'documentos.manage': t('admin.perm_manage_documents'),
  'contactos.view': t('admin.perm_view_contacts'),
  'contactos.manage': t('admin.perm_manage_contacts'),
  'chat.view': t('admin.perm_view_chat'),
  'chat.reply': t('admin.perm_reply_chat'),
  'licenciamentos.view': 'Ver Licenciamentos',
  'licenciamentos.manage': 'Gerir Licenciamentos',
  'visitors.view': t('admin.perm_view_visitors'),
  'content.manage': t('admin.perm_manage_content')
}

const permissionGroups = [
  { label: 'Certificação', icon: 'bi-patch-check-fill', perms: ['embarques.view', 'embarques.manage'] },
  { label: 'Documentação', icon: 'bi-file-earmark-text-fill', perms: ['documentos.view', 'documentos.manage'] },
  { label: 'Licenciamentos', icon: 'bi-sticky-fill', perms: ['licenciamentos.view', 'licenciamentos.manage'] },
  { label: 'Facturação', icon: 'bi-receipt', perms: ['cotacoes.view', 'cotacoes.manage'] },
  { label: t('admin.perm_group_clients'), icon: 'bi-people-fill', perms: ['clients.view', 'clients.manage'] },
  { label: t('admin.perm_group_contacts'), icon: 'bi-person-rolodex', perms: ['contactos.view', 'contactos.manage'] },
  { label: t('admin.perm_group_chat'), icon: 'bi-chat-dots-fill', perms: ['chat.view', 'chat.reply'] },
  { label: t('admin.perm_group_others'), icon: 'bi-three-dots', perms: ['dashboard.view', 'visitors.view', 'content.manage'] }
]

const permLabel = (code) => PERM_LABELS[code] || code
const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT', { day: '2-digit', month: 'short', year: 'numeric' }) : t('admin.employees_never')
const isActive = (f) => f.status === 1 && !f.locked_at
const statusLabel = (f) => {
  if (f.locked_at) return t('admin.employees_status_locked')
  if (f.status === 0) return t('admin.employees_status_disabled')
  return t('admin.employees_status_active')
}

const isValidBiFormat = (bi) => /^\d{9}[A-Z]{2}\d{3}$/.test(bi.toUpperCase())
const consultingBi = ref(false)
const biLookupStatus = ref('')
const biLookupMessage = ref('')

const onBiBlur = () => {
  if (form.bi && isValidBiFormat(form.bi)) {
    consultarBI()
  }
}

const consultarBI = async () => {
  if (!form.bi || !isValidBiFormat(form.bi)) {
    biLookupStatus.value = 'error'
    biLookupMessage.value = 'Formato de BI inválido'
    return
  }
  consultingBi.value = true
  biLookupStatus.value = ''
  biLookupMessage.value = ''
  const bi = form.bi.toUpperCase()
  try {
    const res = await fetch(`/api/bi-lookup/${bi}`, { signal: AbortSignal.timeout(15000) })
    const data = await res.json()
    if (data.success && data.data && data.data.nome) {
      biLookupStatus.value = 'success'
      biLookupMessage.value = `Titular: ${data.data.nome} (${data.data.fonte})`
      if (!form.name || !editing.value) {
        form.name = data.data.nome
      }
    } else if (data.success && data.data && data.data.validFormat) {
      biLookupStatus.value = 'success'
      biLookupMessage.value = `BI com formato válido (fonte: ${data.data.fonte})`
    } else {
      biLookupStatus.value = 'error'
      biLookupMessage.value = data.message || 'BI não encontrado'
    }
  } catch (e) {
    biLookupStatus.value = 'error'
    biLookupMessage.value = 'Erro ao consultar BI. Tente novamente.'
    console.error(e)
  } finally {
    consultingBi.value = false
  }
}

const fileToBase64 = (file) => new Promise((resolve, reject) => {
  const reader = new FileReader()
  reader.readAsDataURL(file)
  reader.onload = () => resolve(reader.result)
  reader.onerror = (err) => reject(err)
})

const fileInput = ref(null)
const photoError = ref('')

const triggerFileInput = () => {
  fileInput.value?.click()
}

const onFileChange = async (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  await processPhoto(file)
  if (fileInput.value) fileInput.value.value = ''
}

const onDrop = async (e) => {
  const file = e.dataTransfer.files?.[0]
  if (!file || !file.type.startsWith('image/')) return
  await processPhoto(file)
}

const processPhoto = async (file) => {
  photoError.value = ''
  if (file.size > 2 * 1024 * 1024) {
    photoError.value = 'A imagem deve ter no máximo 2 MB.'
    return
  }
  if (!file.type.startsWith('image/')) {
    photoError.value = 'Apenas ficheiros de imagem são aceites.'
    return
  }
  try {
    const base64 = await fileToBase64(file)
    form.photo = base64
  } catch {
    photoError.value = 'Erro ao processar a imagem.'
  }
}

const removePhoto = () => {
  form.photo = ''
  photoError.value = ''
}

const fetchList = async () => {
  loading.value = true
  try {
    const { data, error } = await supabase
      .from('users')
      .select('id, auth_id, created_at, updated_at, name, email, username, phone, bi, role, position, departamento, permissions, approval_status, status, photo, password_must_change, password_changed_at, locked_at, locked_reason')
      .eq('role', 'funcionario')
      .order('created_at', { ascending: false })
    if (!error) items.value = data
  } finally {
    loading.value = false
  }
}

const openForm = (item = null) => {
  editing.value = item
  errorMessage.value = ''
  biLookupStatus.value = ''
  biLookupMessage.value = ''
  photoError.value = ''

  if (item) {
    form.name = item.name
    form.username = item.username
    form.email = item.email
    form.phone = item.phone || ''
    form.position = item.position || ''
    form.role_label = ''
    form.departamento = item.departamento || ''
    form.password = ''
    form.permissions = Array.isArray(item.permissions) ? [...item.permissions] : []
    form.bi = item.bi || ''
    form.photo = item.photo || ''
  } else {
    form.name = ''
    form.username = ''
    form.email = ''
    form.phone = ''
    form.position = ''
    form.role_label = ''
    form.departamento = ''
    form.password = ''
    form.permissions = []
    form.bi = ''
    form.photo = ''
  }

  showForm.value = true
  document.body.style.overflow = 'hidden'
}

const closeForm = () => {
  showForm.value = false
  editing.value = null
  document.body.style.overflow = ''
}

const handleSubmit = async () => {
  errorMessage.value = ''
  if (!form.name.trim()) { errorMessage.value = 'O nome completo é obrigatório.'; return }
  if (!form.username.trim()) { errorMessage.value = 'O username é obrigatório.'; return }
  if (!form.email.trim()) { errorMessage.value = 'O email é obrigatório.'; return }
  if (!form.departamento) { errorMessage.value = 'Selecione o departamento.'; return }
  if (!editing.value && !form.password) { errorMessage.value = t('admin.employees_password_required'); return }
  if (!editing.value && form.password && form.password.length < 12) { errorMessage.value = 'A senha deve ter pelo menos 12 caracteres.'; return }
  if (form.bi && !isValidBiFormat(form.bi)) { errorMessage.value = 'Formato de BI inválido. Use 14 caracteres (ex: 006151112LA041).'; return }
  if (!editing.value && form.permissions.length === 0) { errorMessage.value = t('admin.employees_select_permissions'); return }

  saving.value = true
  try {
    if (form.bi) {
      const biUpper = form.bi.toUpperCase()
      const { data: existingBi } = await supabase.from('users').select('id, name').eq('bi', biUpper).maybeSingle()
      if (existingBi && (!editing.value || existingBi.id !== editing.value.id)) {
        throw new Error(`Este BI já está registado para "${existingBi.name}".`)
      }
    }

    if (editing.value) {
      const payload = {
        name: form.name,
        username: form.username,
        phone: form.phone,
        position: form.position,
        departamento: form.departamento,
        permissions: form.permissions,
        bi: form.bi ? form.bi.toUpperCase() : null,
        photo: form.photo || null
      }
      const { error } = await supabase.from('users').update(payload).eq('id', editing.value.id)
      if (error) throw error

      if (form.password && editing.value.auth_id) {
        try {
          const { error: authError } = await supabase.auth.admin.updateUserById(
            editing.value.auth_id,
            { password: form.password }
          )
          if (authError) {
            console.warn('Senha atualizada apenas em public.users.')
          }
        } catch (e) {
          console.warn('Admin API indisponível para atualizar senha no Auth.')
        }
      }

      toast.success('Funcionário atualizado com sucesso!')
    } else {
      const { data: existing } = await supabase.from('users').select('id, email').eq('email', form.email).maybeSingle()
      if (existing) throw new Error(t('admin.employees_email_exists'))

      const { data: existingUser } = await supabase.from('users').select('id, username').eq('username', form.username).maybeSingle()
      if (existingUser) throw new Error(t('admin.employees_username_exists'))

      let authUserId = null
      const { data: authData, error: authError } = await supabase.auth.admin.createUser({
        email: form.email,
        password: form.password,
        email_confirm: true,
        user_metadata: {
          username: form.username,
          name: form.name,
          phone: form.phone || '',
          position: form.position || '',
          departamento: form.departamento || '',
          role: 'funcionario',
          approval_status: 'approved',
          permissions: form.permissions,
          company_completed: true,
          must_change_password: true
        }
      })

      if (authError) {
        const { data: authData2, error: authError2 } = await supabase.auth.signUp({
          email: form.email,
          password: form.password,
          options: {
            data: {
              username: form.username,
              name: form.name,
              phone: form.phone || '',
              position: form.position || '',
              departamento: form.departamento || '',
              role: 'funcionario',
              approval_status: 'approved',
              permissions: form.permissions,
              company_completed: true,
              must_change_password: true
            }
          }
        })
        if (authError2) throw authError2
        authUserId = authData2.user?.id
      } else {
        authUserId = authData.user?.id
      }

      if (authUserId) {
        const { error: dbError } = await supabase.from('users').insert({
          auth_id: authUserId,
          username: form.username,
          name: form.name,
          email: form.email,
          phone: form.phone || '',
          position: form.position || '',
          departamento: form.departamento || '',
          bi: form.bi ? form.bi.toUpperCase() : null,
          role: 'funcionario',
          approval_status: 'approved',
          status: 1,
          permissions: JSON.stringify(form.permissions),
          password_must_change: true,
          password: 'supabase_auth_managed',
          photo: form.photo || null
        })
        if (dbError) throw dbError
      }

      toast.success('Funcionário criado com sucesso!')
    }

    closeForm()
    await fetchList()
  } catch (e) {
    errorMessage.value = e.message || t('admin.employees_create_error')
  } finally {
    saving.value = false
  }
}

const showDeleteModal = ref(false)
const deleteTarget = ref(null)

const showResetPwdModal = ref(false)
const resetPwdTarget = ref(null)
const resetPwdLoading = ref(false)
const resetPwdResult = ref(null)
const resetPwdError = ref('')

const openDeleteModal = (f) => {
  deleteTarget.value = f
  showDeleteModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  deleteTarget.value = null
  document.body.style.overflow = ''
}

const handleDelete = async () => {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    const { error } = await supabase.from('users').delete().eq('id', deleteTarget.value.id)
    if (error) throw error
    toast.success('Funcionário eliminado com sucesso!')
    closeDeleteModal()
    await fetchList()
  } catch (e) {
    toast.error(e.message || t('admin.error_delete'))
  } finally {
    deleting.value = false
  }
}

const openResetPassword = (f) => {
  resetPwdTarget.value = f
  resetPwdResult.value = null
  resetPwdError.value = ''
  showResetPwdModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeResetPasswordModal = () => {
  showResetPwdModal.value = false
  resetPwdTarget.value = null
  resetPwdResult.value = null
  resetPwdError.value = ''
  document.body.style.overflow = ''
}

const confirmResetPassword = async () => {
  if (!resetPwdTarget.value) return
  resetPwdLoading.value = true
  resetPwdError.value = ''
  resetPwdResult.value = null

  try {
    const resetUrl = API_URL ? `${API_URL}/admin/users/${resetPwdTarget.value.id}/reset-password` : `/api/admin/users/${resetPwdTarget.value.id}/reset-password`
    const res = await fetch(resetUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      }
    })
    const data = await res.json()
    if (res.ok && data.success) {
      resetPwdResult.value = data.data.password
      await fetchList()
    } else {
      resetPwdError.value = data.message || 'Erro ao repor senha'
    }
  } catch (e) {
    resetPwdError.value = e.message || 'Erro de conexão'
  } finally {
    resetPwdLoading.value = false
  }
}

const copyResetPassword = () => {
  if (resetPwdResult.value) navigator.clipboard.writeText(resetPwdResult.value)
}

onMounted(() => {
  fetchList()
})
</script>

<style scoped>
.admin-page {
  background: #f8f9fa;
  min-height: 100vh;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.25rem;
}

.page-subtitle {
  font-size: 0.875rem;
  color: #64748b;
}

.empty-state {
  text-align: center;
  padding: 4rem 1rem;
  color: #94a3b8;
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.4;
}

.empty-state p {
  margin-bottom: 1.25rem;
  font-size: 0.95rem;
}

.section-group {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.employee-row {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 0.75rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.15s;
}

.employee-row:last-child {
  border-bottom: none;
}

.employee-row:hover {
  background: #f8fafc;
}

.employee-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
  flex: 1.4;
}

.employee-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #f1f5f9;
  overflow: hidden;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.employee-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-initials {
  font-size: 0.8rem;
  font-weight: 600;
  color: #475569;
}

.employee-details {
  min-width: 0;
}

.employee-name {
  font-weight: 600;
  font-size: 0.9rem;
  color: #0f172a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.employee-email {
  font-size: 0.8rem;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.employee-meta {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 180px;
}

.meta-dept {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: #eff6ff;
  color: #1e40af;
  padding: 0.2rem 0.6rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 500;
  white-space: nowrap;
}

.meta-dept i {
  font-size: 0.7rem;
}

.meta-role {
  font-size: 0.8rem;
  color: #475569;
  white-space: nowrap;
}

.employee-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  min-width: 100px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #cbd5e1;
  flex-shrink: 0;
}

.status-dot.active {
  background: #22c55e;
}

.status-label {
  font-size: 0.8rem;
  color: #475569;
  white-space: nowrap;
}

.employee-login {
  min-width: 100px;
  text-align: right;
  color: #94a3b8;
  font-size: 0.8rem;
}

.employee-actions {
  display: flex;
  gap: 0.35rem;
  flex-shrink: 0;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
  font-size: 0.85rem;
}

.btn-edit {
  background: #eff6ff;
  color: #3b82f6;
}

.btn-edit:hover {
  background: #dbeafe;
  color: #2563eb;
}

.btn-delete {
  background: #fef2f2;
  color: #ef4444;
}

.btn-delete:hover {
  background: #fee2e2;
  color: #dc2626;
}

.btn-reset-pwd {
  background: #fef3c7;
  color: #d97706;
}

.btn-reset-pwd:hover {
  background: #fde68a;
  color: #b45309;
}

/* Modals */
.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 1040;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  overflow-y: auto;
}

.modal-dialog-centered-custom {
  width: 100%;
  max-width: 640px;
  max-height: 90vh;
  margin: auto;
  display: flex;
  flex-direction: column;
}

.modal-box {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  flex: 1;
  min-height: 0;
}

.modal-box.modal-sm {
  max-width: 400px;
}

.modal-box-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e2e8f0;
  flex-shrink: 0;
  gap: 0.75rem;
}

.modal-box-header h5 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 600;
  color: #0f172a;
  min-width: 0;
  word-break: break-word;
  flex: 1;
}

.btn-close-modal {
  width: 32px;
  height: 32px;
  min-width: 32px;
  border: none;
  border-radius: 8px;
  background: #f1f5f9;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
  flex-shrink: 0;
}

.btn-close-modal:hover {
  background: #e2e8f0;
  color: #0f172a;
}

.modal-box form {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

.modal-box-body {
  padding: 1.25rem;
  overflow-y: auto;
  flex: 1;
  min-height: 0;
}

.modal-box-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border-top: 1px solid #e2e8f0;
  flex-shrink: 0;
  flex-wrap: wrap;
}

.delete-modal-body {
  padding: 2rem 1.5rem 1rem;
  text-align: center;
}

.delete-icon-circle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #fef2f2;
  color: #ef4444;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  margin: 0 auto 1rem;
}

.delete-modal-body h5 {
  font-weight: 600;
  color: #0f172a;
  margin-bottom: 0.5rem;
}

/* Photo Upload */
.photo-upload-area {
  width: 140px;
  height: 140px;
  border: 2px dashed #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  overflow: hidden;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.photo-upload-area:hover {
  border-color: #3b82f6;
  background: #f8fafc;
}

.photo-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.35rem;
  color: #94a3b8;
  font-size: 0.8rem;
}

.photo-placeholder i {
  font-size: 1.5rem;
  opacity: 0.5;
}

.photo-placeholder small {
  font-size: 0.7rem;
  opacity: 0.6;
}

.photo-remove-btn {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: none;
  background: rgba(0, 0, 0, 0.6);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.65rem;
  cursor: pointer;
  transition: background 0.15s;
}

.photo-remove-btn:hover {
  background: #ef4444;
}

/* Permissions */
.perm-dept-info {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  padding: 0.5rem 0.75rem;
}

.perm-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 1rem;
  max-height: 320px;
  overflow-y: auto;
}

.perm-group {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.75rem;
}

.perm-group-title {
  font-weight: 600;
  color: #0f766e;
  font-size: 0.85rem;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 6px;
}

.perm-items {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.perm-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.82rem;
  color: #475569;
  cursor: pointer;
  padding: 3px 4px;
  border-radius: 4px;
  transition: background 0.15s;
}

.perm-item:hover {
  background: #f1f5f9;
}

.perm-item input {
  margin: 0;
}

.alert-sm {
  font-size: 0.85rem;
  padding: 0.6rem 0.85rem;
}

/* Responsive */
@media (max-width: 1024px) {
  .employee-row {
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  .employee-meta,
  .employee-status,
  .employee-login {
    min-width: 0;
  }

  .employee-login {
    text-align: left;
  }
}

@media (max-width: 768px) {
  .employee-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
  }

  .employee-info {
    width: 100%;
  }

  .employee-meta {
    width: 100%;
  }

  .employee-status {
    width: 100%;
  }

  .employee-login {
    width: 100%;
    text-align: left;
  }

  .employee-actions {
    width: 100%;
    justify-content: flex-end;
  }

  .perm-grid {
    grid-template-columns: 1fr;
  }

  .modal-backdrop-custom {
    padding: 0.5rem;
  }

  .modal-dialog-centered-custom {
    max-width: 100%;
    margin: 0;
  }

  .modal-box {
    border-radius: 12px;
  }

  .modal-box-header {
    padding: 0.85rem 1rem;
  }

  .modal-box-header h5 {
    font-size: 0.95rem;
  }

  .modal-box-body {
    padding: 1rem;
  }

  .modal-box-footer {
    padding: 0.75rem 1rem;
    gap: 0.4rem;
  }

  .modal-box-footer .btn {
    flex: 1;
    min-width: 0;
  }
}

@media (max-width: 480px) {
  .admin-page { padding: 0.75rem !important; }
  .employee-row { padding: 0.75rem 1rem !important; }
  .employee-avatar { width: 36px !important; height: 36px !important; }
  .page-title { font-size: 1rem !important; }
  .modal-backdrop-custom { padding: 0.35rem; }
  .modal-box-header { padding: 0.7rem 0.85rem; }
  .modal-box-header h5 { font-size: 0.9rem; }
  .modal-box-body { padding: 0.75rem; }
  .modal-box-footer { padding: 0.6rem 0.85rem; }
}
</style>
