<template>
  <div class="admin-page p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h1 class="page-title"><i class="bi bi-person-badge-fill me-2"></i>Funcionários</h1>
        <p class="text-muted mb-0">Gere as contas dos teus funcionários e define as suas permissões.</p>
      </div>
      <button class="btn btn-primary" @click="openForm()">
        <i class="bi bi-plus-lg me-1"></i> Novo funcionário
      </button>
    </div>

    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
    <div v-else-if="items.length === 0" class="card empty-card">
      <div class="card-body text-center py-5">
        <i class="bi bi-person-badge" style="font-size: 3rem; color: #0f766e;"></i>
        <h5 class="mt-3">Sem funcionários</h5>
        <p class="text-muted mb-3">Cria a primeira conta de funcionário para começar.</p>
        <button class="btn btn-primary" @click="openForm()">
          <i class="bi bi-plus-lg me-1"></i> Criar funcionário
        </button>
      </div>
    </div>
    <div v-else class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead>
              <tr>
                <th>Funcionário</th>
                <th>Cargo</th>
                <th>Email</th>
                <th>Estado</th>
                <th>Permissões</th>
                <th>Último login</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
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
                <td><span class="badge bg-info">{{ f.position || '—' }}</span></td>
                <td>{{ f.email }}</td>
                <td>
                  <span v-if="isLocked(f)" class="badge bg-danger" :title="lockTooltip(f)">
                    <i class="bi bi-lock-fill me-1"></i>Bloqueado
                  </span>
                  <span v-else-if="f.status === 0" class="badge bg-secondary">
                    <i class="bi bi-slash-circle me-1"></i>Desativado
                  </span>
                  <span v-else-if="f.password_must_change" class="badge bg-warning text-dark">
                    <i class="bi bi-key me-1"></i>Alterar senha
                  </span>
                  <span v-else class="badge bg-success">
                    <i class="bi bi-check-circle me-1"></i>Ativo
                  </span>
                </td>
                <td>
                  <span class="text-muted small">{{ (f.permissions || []).length }} de {{ allPermissions.length }}</span>
                </td>
                <td><small class="text-muted">{{ f.last_login ? formatDate(f.last_login) : 'Nunca' }}</small></td>
                <td class="text-end">
                  <div class="action-buttons">
                    <button v-if="isLocked(f)" class="btn btn-sm btn-outline-success" @click="unlockUser(f)" title="Desbloquear">
                      <i class="bi bi-unlock-fill"></i>
                    </button>
                    <button v-else class="btn btn-sm btn-outline-warning" @click="openLockModal(f)" title="Bloquear">
                      <i class="bi bi-lock-fill"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" @click="openForm(f)" title="Editar">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(f)" title="Eliminar">
                      <i class="bi bi-trash"></i>
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
              <i class="bi bi-person-badge-fill me-2"></i>{{ editing ? 'Editar funcionário' : 'Novo funcionário' }}
            </h5>
            <button type="button" class="btn-close" @click="closeForm"></button>
          </div>
          <form @submit.prevent="handleSubmit" novalidate>
            <div class="modal-body">
              <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Nome completo *</label>
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
                  <label class="form-label">Telefone</label>
                  <input v-model="form.phone" type="text" class="form-control">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Cargo / posição</label>
                  <input v-model="form.position" type="text" class="form-control" placeholder="Ex: Operador de Embarques">
                </div>
                <div class="col-md-6">
                  <label class="form-label">
                    {{ editing ? 'Nova senha (deixar vazio para manter)' : 'Senha *' }}
                  </label>
                  <input v-model="form.password" type="password" class="form-control" :required="!editing" minlength="6">
                </div>
                <div class="col-12">
                  <label class="form-label fw-bold">Permissões de acesso</label>
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
              <button type="button" class="btn btn-outline-secondary" @click="closeForm">Cancelar</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                {{ editing ? 'Atualizar' : 'Criar funcionário' }}
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
              <i class="bi bi-lock-fill me-2 text-warning"></i>Bloquear funcionário
            </h5>
            <button type="button" class="btn-close" @click="closeLockModal"></button>
          </div>
          <form @submit.prevent="submitLock">
            <div class="modal-body">
              <div v-if="lockError" class="alert alert-danger">{{ lockError }}</div>
              <p class="text-muted small">
                Bloquear <strong>{{ lockTarget.name }}</strong> ({{ lockTarget.email }}) impedirá o
                acesso ao painel durante o período definido.
              </p>
              <div class="mb-3">
                <label class="form-label">Duração do bloqueio</label>
                <select v-model.number="lockForm.duration_hours" class="form-select">
                  <option :value="1">1 hora</option>
                  <option :value="6">6 horas</option>
                  <option :value="12">12 horas</option>
                  <option :value="24">24 horas (padrão)</option>
                  <option :value="48">2 dias</option>
                  <option :value="168">7 dias</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Motivo</label>
                <textarea v-model="lockForm.reason" class="form-control" rows="3" required placeholder="Ex: Atividade suspeita, solicitação do utilizador, etc."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" @click="closeLockModal">Cancelar</button>
              <button type="submit" class="btn btn-warning" :disabled="locking">
                <span v-if="locking" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-lock-fill me-1"></i>
                Bloquear
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

const items = ref([])
const allPermissions = ref([])
const loading = ref(false)
const saving = ref(false)
const showForm = ref(false)
const editing = ref(null)
const errorMessage = ref('')
const form = reactive({ name: '', username: '', email: '', phone: '', position: '', password: '', permissions: [] })
const lockTarget = ref(null)
const lockForm = reactive({ duration_hours: 24, reason: '' })
const locking = ref(false)
const lockError = ref('')

const PERM_LABELS = {
  'dashboard.view': 'Dashboard',
  'clients.view': 'Ver clientes', 'clients.manage': 'Gerir clientes',
  'embarques.view': 'Ver embarques', 'embarques.manage': 'Gerir embarques',
  'cotacoes.view': 'Ver cotações', 'cotacoes.manage': 'Gerir cotações',
  'documentos.view': 'Ver documentos', 'documentos.manage': 'Gerir documentos',
  'contactos.view': 'Ver contactos', 'contactos.manage': 'Gerir contactos',
  'chat.view': 'Ver chat', 'chat.reply': 'Responder chat',
  'visitors.view': 'Ver visitantes',
  'content.manage': 'Gerir conteúdo',
}

const permissionGroups = [
  { label: 'Clientes', icon: 'bi-people-fill', perms: ['clients.view', 'clients.manage'] },
  { label: 'Embarques', icon: 'bi-box-seam-fill', perms: ['embarques.view', 'embarques.manage'] },
  { label: 'Cotações', icon: 'bi-receipt', perms: ['cotacoes.view', 'cotacoes.manage'] },
  { label: 'Documentos', icon: 'bi-file-earmark-text-fill', perms: ['documentos.view', 'documentos.manage'] },
  { label: 'Contactos', icon: 'bi-person-rolodex', perms: ['contactos.view', 'contactos.manage'] },
  { label: 'Chat', icon: 'bi-chat-dots-fill', perms: ['chat.view', 'chat.reply'] },
  { label: 'Outros', icon: 'bi-three-dots', perms: ['dashboard.view', 'visitors.view', 'content.manage'] },
]

const permLabel = (code) => PERM_LABELS[code] || code
const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT', { day: '2-digit', month: 'short', year: 'numeric' }) : 'Nunca'

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
    lockError.value = 'Indique um motivo para o bloqueio.'
    return
  }
  locking.value = true
  lockError.value = ''
  const lockUntil = new Date(Date.now() + lockForm.duration_hours * 3600 * 1000).toISOString()
  const { error } = await supabase.from('users').update({ locked_at: lockUntil, locked_reason: lockForm.reason.trim() }).eq('id', lockTarget.value.id)
  locking.value = false
  if (error) {
    lockError.value = error.message || 'Erro ao bloquear.'
    return
  }
  closeLockModal()
  await fetchList()
}
const unlockUser = async (u) => {
  if (!confirm(`Desbloquear o funcionário "${u.name}"?`)) return
  const { error } = await supabase.from('users').update({ locked_at: null, locked_reason: null }).eq('id', u.id)
  if (error) {
    alert(error.message || 'Erro ao desbloquear.')
    return
  }
  await fetchList()
}

const fetchList = async () => {
  loading.value = true
  try {
    const { data, error } = await supabase.from('users').select('*').eq('role', 'funcionario').order('created_at', { ascending: false })
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
    form.password = ''
    form.permissions = Array.isArray(item.permissions) ? [...item.permissions] : []
  } else {
    form.name = ''
    form.username = ''
    form.email = ''
    form.phone = ''
    form.position = ''
    form.password = ''
    form.permissions = []
  }
  errorMessage.value = ''
  showForm.value = true
}

const closeForm = () => { showForm.value = false; editing.value = null }

const handleSubmit = async () => {
  errorMessage.value = ''
  if (!editing.value && form.permissions.length === 0) {
    errorMessage.value = 'Selecione pelo menos uma permissão.'
    return
  }
  saving.value = true
  try {
    const payload = {
      name: form.name,
      username: form.username,
      email: form.email,
      phone: form.phone,
      position: form.position,
      permissions: form.permissions,
      role: 'funcionario'
    }
    if (editing.value) {
      if (form.password) payload.password = form.password
      const { error } = await supabase.from('users').update(payload).eq('id', editing.value.id)
      if (error) throw error
    } else {
      payload.status = 1
      payload.approval_status = 'approved'
      const { error } = await supabase.from('users').insert(payload)
      if (error) throw error
    }
    closeForm()
    await fetchList()
  } catch (e) {
    errorMessage.value = e.message || 'Erro ao guardar.'
  } finally { saving.value = false }
}

const confirmDelete = async (item) => {
  if (!confirm(`Eliminar o funcionário "${item.name}"? Esta acção é irreversível.`)) return
  try {
    const { error } = await supabase.from('users').delete().eq('id', item.id)
    if (error) throw error
    await fetchList()
  } catch (e) {
    alert(e.message || 'Erro ao eliminar')
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
