<template>
  <div class="admin-page p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h1 class="page-title"><i class="bi bi-person-badge-fill me-2"></i>{{ t('admin.employees_title') }}</h1>
        <p class="text-muted mb-0">{{ t('admin.employees_description') }}</p>
      </div>
      <button class="btn btn-primary" @click="openForm()">
        <i class="bi bi-plus-lg me-1"></i> {{ t('admin.employees_new') }}
      </button>
    </div>

    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
    <div v-else class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead>
              <tr>
                <th>{{ t('admin.employees_col_employee') }}</th>
                <th>BI</th>
                <th>Departamento</th>
                <th>{{ t('admin.employees_position') }}</th>
                <th>Email</th>
                <th>{{ t('admin.services_status') }}</th>
                <th>{{ t('admin.employees_permissions') }}</th>
                <th>{{ t('admin.last_login') }}</th>
                <th class="text-end">{{ t('admin.employees_col_actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="items.length === 0">
                <td colspan="9" class="text-center py-4 text-muted">
                  <i class="bi bi-person-badge me-2" style="font-size: 1.5rem; opacity: 0.4;"></i>Nenhum funcionário registado
                </td>
              </tr>
              <tr v-for="f in items" :key="f.id">
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar-sm">
                      <img v-if="f.photo" :src="f.photo" :alt="f.name">
                      <span v-else>{{ initials(f.name) }}</span>
                    </div>
                    <div>
                      <strong>{{ f.name }}</strong>
                      <small class="d-block text-muted">@{{ f.username }}</small>
                    </div>
                  </div>
                </td>
                <td><code class="tracking-code">{{ f.bi || '' }}</code></td>
                <td><span class="badge bg-info">{{ f.position || '' }}</span></td>
                <td><span class="badge bg-secondary"><i class="bi bi-building me-1"></i>{{ deptLabels[f.departamento] || '' }}</span></td>
                <td>{{ f.email }}</td>
                <td>
                  <span v-if="isLocked(f)" class="badge bg-danger" :title="lockTooltip(f)">
                    <i class="bi bi-lock-fill me-1"></i>{{ t('admin.employees_status_locked') }}
                  </span>
                  <span v-else-if="f.status === 0" class="badge bg-secondary">
                    <i class="bi bi-slash-circle me-1"></i>{{ t('admin.employees_status_disabled') }}
                  </span>
                  <span v-else-if="f.password_must_change" class="badge bg-warning text-dark">
                    <i class="bi bi-key me-1"></i>{{ t('admin.employees_status_password_change') }}
                  </span>
                  <span v-else class="badge bg-success">
                    <i class="bi bi-check-circle me-1"></i>{{ t('admin.employees_status_active') }}
                  </span>
                </td>
                <td>
                  <span class="text-muted small">{{ (f.permissions || []).length }} de {{ allPermissions.length }}</span>
                </td>
                <td><small class="text-muted">{{ f.last_login ? formatDate(f.last_login) : t('admin.employees_never') }}</small></td>
                <td class="text-end">
                  <div class="action-buttons">
                    <button v-if="isLocked(f)" class="btn btn-sm btn-outline-success" @click="unlockUser(f)" :title="t('admin.employees_action_unlock')">
                      <i class="bi bi-unlock-fill"></i>
                    </button>
                    <button v-else class="btn btn-sm btn-outline-warning" @click="openLockModal(f)" :title="t('admin.employees_action_lock')">
                      <i class="bi bi-lock-fill"></i>
                    </button>
                    <button class="btn-icon btn-edit" @click="openForm(f)" :title="t('admin.employees_action_edit')">
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn-icon btn-delete" @click="confirmDelete(f)" :title="t('admin.employees_action_delete')">
                      <i class="bi bi-trash3"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="modal fade show d-block" v-if="showForm" @click.self="closeForm" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-person-badge-fill me-2"></i>{{ editing ? t('admin.employees_edit_title') : t('admin.employees_new_title') }}
            </h5>
            <button type="button" class="btn-close" @click="closeForm"></button>
          </div>
          <form @submit.prevent="handleSubmit" novalidate>
            <div class="modal-body">
              <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
              <div class="row g-3">
                <div class="col-md-6">
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
                  <label class="form-label">{{ t('admin.employees_position_label') }}</label>
                  <input v-model="form.position" type="text" class="form-control" :placeholder="t('admin.employees_position_placeholder')">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold">Departamento *</label>
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
                  <label class="form-label">
                    {{ editing ? t('admin.employees_new_password') : t('admin.employees_password_label') }}
                  </label>
                  <input v-model="form.password" type="password" class="form-control" :required="!editing" minlength="6">
                </div>
                <div class="col-12">
                  <label class="form-label fw-bold">{{ t('admin.employees_access_permissions') }}</label>
                  <div class="perm-dept-info mb-2" v-if="form.departamento">
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Permissões atribuídas automaticamente pelo departamento: <strong>{{ deptLabels[form.departamento] }}</strong></small>
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
            <div class="modal-footer">
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
    <div class="modal-backdrop fade show" v-if="showForm"></div>

    <div class="modal fade show d-block" v-if="lockTarget" @click.self="closeLockModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-lock-fill me-2 text-warning"></i>{{ t('admin.employees_lock_title') }}
            </h5>
            <button type="button" class="btn-close" @click="closeLockModal"></button>
          </div>
          <form @submit.prevent="submitLock">
            <div class="modal-body">
              <div v-if="lockError" class="alert alert-danger">{{ lockError }}</div>
              <p class="text-muted small">
                {{ t('admin.lock_title') }} <strong>{{ lockTarget.name }}</strong> ({{ lockTarget.email }}) {{ t('admin.lock_will_prevent') }}
              </p>
              <div class="mb-3">
                <label class="form-label">{{ t('admin.lock_duration') }}</label>
                <select v-model.number="lockForm.duration_hours" class="form-select">
                  <option :value="1">{{ t('admin.employees_lock_1h') }}</option>
                  <option :value="6">{{ t('admin.employees_lock_6h') }}</option>
                  <option :value="12">{{ t('admin.employees_lock_12h') }}</option>
                  <option :value="24">{{ t('admin.employees_lock_24h_default') }}</option>
                  <option :value="48">{{ t('admin.employees_lock_2d') }}</option>
                  <option :value="168">{{ t('admin.employees_lock_7d') }}</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">{{ t('admin.employees_lock_reason') }}</label>
                <textarea v-model="lockForm.reason" class="form-control" rows="3" :placeholder="t('admin.lock_reason_placeholder')"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" @click="closeLockModal">{{ t('common.cancel') }}</button>
              <button type="submit" class="btn btn-warning" :disabled="locking">
                <span v-if="locking" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-lock-fill me-1"></i>
                {{ t('admin.lock_title') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-if="lockTarget"></div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useI18n } from '@/composables/useI18n'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'

const { t } = useI18n()
const toast = useToast()
const { confirm } = useConfirm()

const items = ref([])
const allPermissions = ref([])
const loading = ref(false)
const saving = ref(false)
const showForm = ref(false)
const editing = ref(null)
const errorMessage = ref('')
const form = reactive({ name: '', username: '', email: '', phone: '', position: '', departamento: '', password: '', permissions: [], bi: '' })

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
const lockTarget = ref(null)
const lockForm = reactive({ duration_hours: 24, reason: '' })
const locking = ref(false)
const lockError = ref('')

const PERM_LABELS = {
  'dashboard.view': 'Dashboard',
  'clients.view': t('admin.perm_view_clients'), 'clients.manage': t('admin.perm_manage_clients'),
  'embarques.view': t('admin.perm_view_shipments'), 'embarques.manage': t('admin.perm_manage_shipments'),
  'cotacoes.view': t('admin.perm_view_quotes'), 'cotacoes.manage': t('admin.perm_manage_quotes'),
  'documentos.view': t('admin.perm_view_documents'), 'documentos.manage': t('admin.perm_manage_documents'),
  'contactos.view': t('admin.perm_view_contacts'), 'contactos.manage': t('admin.perm_manage_contacts'),
  'chat.view': t('admin.perm_view_chat'), 'chat.reply': t('admin.perm_reply_chat'),
  'licenciamentos.view': 'Ver Licenciamentos', 'licenciamentos.manage': 'Gerir Licenciamentos',
  'visitors.view': t('admin.perm_view_visitors'),
  'content.manage': t('admin.perm_manage_content'),
}

const permissionGroups = [
  { label: 'Certificação', icon: 'bi-patch-check-fill', perms: ['embarques.view', 'embarques.manage'] },
  { label: 'Documentação', icon: 'bi-file-earmark-text-fill', perms: ['documentos.view', 'documentos.manage'] },
  { label: 'Licenciamentos', icon: 'bi-sticky-fill', perms: ['licenciamentos.view', 'licenciamentos.manage'] },
  { label: 'Facturação', icon: 'bi-receipt', perms: ['cotacoes.view', 'cotacoes.manage'] },
  { label: t('admin.perm_group_clients'), icon: 'bi-people-fill', perms: ['clients.view', 'clients.manage'] },
  { label: t('admin.perm_group_contacts'), icon: 'bi-person-rolodex', perms: ['contactos.view', 'contactos.manage'] },
  { label: t('admin.perm_group_chat'), icon: 'bi-chat-dots-fill', perms: ['chat.view', 'chat.reply'] },
  { label: t('admin.perm_group_others'), icon: 'bi-three-dots', perms: ['dashboard.view', 'visitors.view', 'content.manage'] },
]

const permLabel = (code) => PERM_LABELS[code] || code
const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT', { day: '2-digit', month: 'short', year: 'numeric' }) : t('admin.employees_never')

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

const isLocked = (u) => {
  if (!u.locked_at) return false
  const lockedTs = new Date(u.locked_at).getTime()
  return (Date.now() - lockedTs) < 24 * 3600 * 1000
}
const lockTooltip = (u) => {
  if (!u.locked_at) return ''
  const remaining = (new Date(u.locked_at).getTime() + 24 * 3600 * 1000) - Date.now()
  if (remaining <= 0) return 'Bloqueio expirado (aguarde nova tentativa)'
  const hours = Math.floor(remaining / 3600000)
  const minutes = Math.floor((remaining % 3600000) / 60000)
  return `Bloqueado · expira em ${hours}h ${minutes}min${u.locked_reason ? ' · ' + u.locked_reason : ''}`
}

const openLockModal = (u) => {
  lockTarget.value = u
  lockForm.duration_hours = 24
  lockForm.reason = ''
  lockError.value = ''
}
const closeLockModal = () => {
  lockTarget.value = null
  lockError.value = ''
}
const submitLock = async () => {
  if (!lockForm.reason.trim()) {
    lockError.value = t('admin.employees_lock_reason_required')
    return
  }
  locking.value = true
  lockError.value = ''
  const lockUntil = new Date(Date.now() + lockForm.duration_hours * 3600 * 1000).toISOString()
  const { error } = await supabase.from('users').update({ locked_at: lockUntil, locked_reason: lockForm.reason.trim() }).eq('id', lockTarget.value.id)
  locking.value = false
  if (error) {
    lockError.value = error.message || t('admin.lock_error')
    return
  }
  closeLockModal()
  await fetchList()
}
const unlockUser = async (u) => {
  if (!await confirm({ title: 'Desbloquear funcionário', message: `${t('admin.employees_lock_confirm')} "${u.name}"?`, type: 'info', confirmText: 'Desbloquear', cancelText: 'Cancelar' })) return
  const { error } = await supabase.from('users').update({ locked_at: null, locked_reason: null }).eq('id', u.id)
  if (error) {
    toast.error(error.message || t('admin.unlock_error'))
    return
  }
  await fetchList()
}

const fetchList = async () => {
  loading.value = true
  try {
    const { data, error } = await supabase.from('users').select('id, auth_id, created_at, updated_at, name, email, username, phone, bi, role, position, departamento, permissions, approval_status, status, photo, password_must_change, password_changed_at, locked_at, locked_reason').eq('role', 'funcionario').order('created_at', { ascending: false })
    if (!error) items.value = data
  } finally { loading.value = false }
}

const fetchPerms = async () => {
  allPermissions.value = Object.keys(PERM_LABELS)
}

const openForm = (item = null) => {
  editing.value = item
  if (item) {
    form.name = item.name
    form.username = item.username
    form.email = item.email
    form.phone = item.phone || ''
    form.position = item.position || ''
    form.departamento = item.departamento || ''
    form.password = ''
    form.permissions = Array.isArray(item.permissions) ? [...item.permissions] : []
    form.bi = item.bi || ''
  } else {
    form.name = ''
    form.username = ''
    form.email = ''
    form.phone = ''
    form.position = ''
    form.departamento = ''
    form.password = ''
    form.permissions = []
    form.bi = ''
  }
  errorMessage.value = ''
  biLookupStatus.value = ''
  biLookupMessage.value = ''
  showForm.value = true
}

const closeForm = () => { showForm.value = false; editing.value = null }

const handleSubmit = async () => {
  errorMessage.value = ''
  if (!form.name.trim()) {
    errorMessage.value = 'O nome completo é obrigatório.'
    return
  }
  if (!form.username.trim()) {
    errorMessage.value = 'O username é obrigatório.'
    return
  }
  if (!form.email.trim()) {
    errorMessage.value = 'O email é obrigatório.'
    return
  }
  if (!form.departamento) {
    errorMessage.value = 'Selecione o departamento.'
    return
  }
  if (!editing.value && !form.password) {
    errorMessage.value = t('admin.employees_password_required')
    return
  }
  if (!editing.value && form.password && form.password.length < 12) {
    errorMessage.value = 'A senha deve ter pelo menos 12 caracteres.'
    return
  }
  if (form.bi && !isValidBiFormat(form.bi)) {
    errorMessage.value = 'Formato de BI inválido. Use 14 caracteres (ex: 006151112LA041).'
    return
  }
  if (!editing.value && form.permissions.length === 0) {
    errorMessage.value = t('admin.employees_select_permissions')
    return
  }
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
            console.warn('Senha atualizada apenas em public.users. Use "Redefinir senha" no Auth se necessário.')
          }
        } catch (e) {
          console.warn('Admin API indisponível para atualizar senha no Auth.')
        }
      }
    } else {
      const { data: existing } = await supabase.from('users').select('id, email').eq('email', form.email).maybeSingle()
      if (existing) {
        throw new Error(t('admin.employees_email_exists'))
      }

      const { data: existingUser } = await supabase.from('users').select('id, username').eq('username', form.username).maybeSingle()
      if (existingUser) {
        throw new Error(t('admin.employees_username_exists'))
      }

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
          must_change_password: true,
        },
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
              must_change_password: true,
            },
          },
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
        })
        if (dbError) throw dbError
      }
    }
    closeForm()
    await fetchList()
  } catch (e) {
    errorMessage.value = e.message || t('admin.employees_create_error')
  } finally { saving.value = false }
}

const confirmDelete = async (item) => {
  if (!await confirm({ title: 'Eliminar funcionário', message: `${t('admin.employees_delete_confirm')} "${item.name}"? ${t('admin.employees_delete_warning')}`, type: 'danger', confirmText: 'Eliminar', cancelText: 'Cancelar' })) return
  try {
    const { error } = await supabase.from('users').delete().eq('id', item.id)
    if (error) throw error
    await fetchList()
  } catch (e) {
    toast.error(e.message || t('admin.error_delete'))
  }
}

onMounted(async () => {
  await fetchPerms()
  await fetchList()
})
</script>

<style scoped>
.admin-page { background: #f8f9fa; min-height: 100vh; }
.page-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }
.tracking-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }
.input-group .btn { z-index: 0; }

.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); }
.empty-card { border: none; }

.avatar-sm {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0f766e, #134e4a);
  color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.78rem; font-weight: 600;
  flex-shrink: 0;
  overflow: hidden;
}
.avatar-sm img { width: 100%; height: 100%; object-fit: cover; }

.action-buttons { display: inline-flex; gap: 0.4rem; }

.perm-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 1rem;
  max-height: 360px;
  overflow-y: auto;
}
.perm-group {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.75rem;
}
.perm-group-title {
  font-weight: 600;
  color: #0f766e;
  font-size: 0.85rem;
  margin-bottom: 0.5rem;
  display: flex; align-items: center; gap: 6px;
}
.perm-items { display: flex; flex-direction: column; gap: 4px; }
.perm-item {
  display: flex; align-items: center; gap: 8px;
  font-size: 0.82rem;
  color: #475569;
  cursor: pointer;
  padding: 3px 4px;
  border-radius: 4px;
  transition: background 0.15s;
}
.perm-item:hover { background: #f1f5f9; }
.perm-item input { margin: 0; }

.modal-backdrop { z-index: 1040; }
.modal { z-index: 1050; }

@media (max-width: 768px) {
  .perm-grid { grid-template-columns: 1fr; }
}
</style>
