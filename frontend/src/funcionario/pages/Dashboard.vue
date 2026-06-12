<template>
  <div class="funcionario-dashboard p-4 p-md-5">
    <div class="welcome-card mb-4">
      <h2 class="mb-1">Olá, {{ authStore.user?.name }} 👋</h2>
      <p class="text-muted mb-0">Bem-vindo ao painel do funcionário. Tens acesso às seguintes áreas:</p>
    </div>

    <div class="permissions-card mb-4">
      <h6 class="mb-3"><i class="bi bi-shield-check me-2"></i>As suas permissões</h6>
      <div class="d-flex flex-wrap gap-2">
        <span v-for="p in permissionLabels" :key="p.code" class="perm-pill">
          <i class="bi bi-check2"></i> {{ p.label }}
        </span>
        <span v-if="permissionLabels.length === 0" class="text-muted small">
          Sem permissões atribuídas. Contacte o administrador.
        </span>
      </div>
    </div>

    <div class="row g-3">
      <div v-if="can('embarques.view')" class="col-md-6 col-xl-3">
        <router-link to="/funcionario/embarques" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-primary-soft"><i class="bi bi-box-seam-fill"></i></div>
            <div>
              <div class="stat-tile-label">Embarques</div>
              <div class="stat-tile-value">{{ counts.embarques }}</div>
              <div class="stat-tile-meta">{{ counts.embarques_pendente || 0 }} pendentes</div>
            </div>
          </div>
        </router-link>
      </div>
      <div v-if="can('cotacoes.view')" class="col-md-6 col-xl-3">
        <router-link to="/funcionario/cotacoes" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-success-soft"><i class="bi bi-receipt"></i></div>
            <div>
              <div class="stat-tile-label">Cotações</div>
              <div class="stat-tile-value">{{ counts.cotacoes }}</div>
              <div class="stat-tile-meta">{{ counts.cotacoes_pendente || 0 }} pendentes</div>
            </div>
          </div>
        </router-link>
      </div>
      <div v-if="can('clients.view')" class="col-md-6 col-xl-3">
        <router-link to="/funcionario/clientes" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-info-soft"><i class="bi bi-people-fill"></i></div>
            <div>
              <div class="stat-tile-label">Clientes</div>
              <div class="stat-tile-value">{{ counts.clientes }}</div>
              <div class="stat-tile-meta">{{ counts.clientes_pendente || 0 }} por aprovar</div>
            </div>
          </div>
        </router-link>
      </div>
      <div v-if="can('documentos.view')" class="col-md-6 col-xl-3">
        <router-link to="/funcionario/documentos" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-warning-soft"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div>
              <div class="stat-tile-label">Documentos</div>
              <div class="stat-tile-value">{{ counts.documentos }}</div>
              <div class="stat-tile-meta">arquivos</div>
            </div>
          </div>
        </router-link>
      </div>
    </div>

    <div class="row g-3 mt-1">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Atividade recente</h6>
          </div>
          <div class="card-body p-0">
            <ul class="recent-list">
              <li v-for="(a, i) in recentActivity" :key="i">
                <div class="activity-icon" :class="`bg-${a.color}-soft`">
                  <i :class="`bi ${a.icon}`"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-medium small">{{ a.title }}</div>
                  <small class="text-muted">{{ a.subtitle }}</small>
                </div>
                <small class="text-muted">{{ a.time }}</small>
              </li>
              <li v-if="recentActivity.length === 0" class="text-center text-muted py-4">
                Sem atividade recente.
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informação da conta</h6>
          </div>
          <div class="card-body">
            <dl class="info-list">
              <div><dt>Nome</dt><dd>{{ authStore.user?.name }}</dd></div>
              <div><dt>Email</dt><dd>{{ authStore.user?.email }}</dd></div>
              <div v-if="authStore.user?.position"><dt>Cargo</dt><dd>{{ authStore.user.position }}</dd></div>
              <div><dt>Username</dt><dd>@{{ authStore.user?.username }}</dd></div>
              <div><dt>Função</dt><dd><span class="badge bg-success">Funcionário</span></dd></div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const counts = reactive({ embarques: 0, embarques_pendente: 0, cotacoes: 0, cotacoes_pendente: 0, clientes: 0, clientes_pendente: 0, documentos: 0 })
const recentActivity = ref([])

const PERM_LABELS = {
  'dashboard.view': 'Dashboard',
  'clients.view': 'Ver clientes', 'clients.manage': 'Gerir clientes',
  'embarques.view': 'Ver embarques', 'embarques.manage': 'Gerir embarques',
  'cotacoes.view': 'Ver cotações', 'cotacoes.manage': 'Gerir cotações',
  'documentos.view': 'Ver documentos', 'documentos.manage': 'Gerir documentos',
  'contactos.view': 'Ver contactos', 'contactos.manage': 'Gerir contactos',
  'chat.view': 'Ver chat', 'chat.reply': 'Responder chat',
  'visitors.view': 'Ver visitantes',
  'content.manage': 'Gerir conteúdo do site',
}

const permissionLabels = computed(() => {
  return (authStore.permissions || []).map(code => ({ code, label: PERM_LABELS[code] || code }))
})

const can = (perm) => authStore.can(perm)

const authHeader = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } })

const safeGet = async (url) => {
  try {
    const r = await axios.get(url, authHeader())
    return r.data?.data || null
  } catch (e) { return null }
}

const load = async () => {
  const tasks = []
  if (can('embarques.view')) tasks.push(safeGet('/api/embarques').then(d => { if (d?.embarques) { counts.embarques = d.embarques.length; counts.embarques_pendente = d.embarques.filter(e => e.status === 'pendente').length } }))
  if (can('cotacoes.view')) tasks.push(safeGet('/api/cotacoes').then(d => { if (d?.cotacoes) { counts.cotacoes = d.cotacoes.length; counts.cotacoes_pendente = d.cotacoes.filter(c => c.status === 'pendente').length } }))
  if (can('documentos.view')) tasks.push(safeGet('/api/documentos').then(d => { if (d?.documentos) counts.documentos = d.documentos.length }))
  if (can('clients.view')) tasks.push(safeGet('/api/admin/users?role=cliente').then(d => { if (d?.users) { counts.clientes = d.users.length; counts.clientes_pendente = d.users.filter(u => u.approval_status === 'pending').length } }))

  await Promise.all(tasks)

  const acts = []
  if (counts.embarques_pendente > 0) acts.push({ title: `${counts.embarques_pendente} embarques pendentes`, subtitle: 'Necessitam atenção', icon: 'bi-box-seam-fill', color: 'warning', time: 'agora' })
  if (counts.cotacoes_pendente > 0) acts.push({ title: `${counts.cotacoes_pendente} cotações pendentes`, subtitle: 'Aguardam resposta', icon: 'bi-receipt', color: 'success', time: 'agora' })
  if (counts.clientes_pendente > 0) acts.push({ title: `${counts.clientes_pendente} clientes por aprovar`, subtitle: 'Aguardam aprovação do admin', icon: 'bi-person-plus-fill', color: 'info', time: 'hoje' })
  recentActivity.value = acts
}

onMounted(load)
</script>

<style scoped>
.funcionario-dashboard { background: #f5f7fa; min-height: 100vh; }

.welcome-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.permissions-card {
  background: white;
  padding: 1.25rem 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}
.permissions-card h6 { font-weight: 700; color: #0f766e; }

.perm-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #ccfbf1;
  color: #0f766e;
  font-size: 0.8rem;
  font-weight: 500;
  padding: 4px 12px;
  border-radius: 14px;
}

.stat-tile-link { text-decoration: none; color: inherit; display: block; }
.stat-tile {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  display: flex;
  align-items: center;
  gap: 1rem;
  height: 100%;
  transition: all 0.2s ease;
}
.stat-tile-link:hover .stat-tile { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,0.08); }
.stat-tile-icon {
  width: 56px; height: 56px;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}
.bg-primary-soft { background: #dbeafe; color: #1d4ed8; }
.bg-success-soft { background: #d1fae5; color: #047857; }
.bg-info-soft { background: #cffafe; color: #0e7490; }
.bg-warning-soft { background: #fef3c7; color: #b45309; }

.stat-tile-label { color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stat-tile-value { font-size: 2rem; font-weight: 700; color: #0f172a; line-height: 1.2; }
.stat-tile-meta { font-size: 0.8rem; color: #64748b; }

.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); }
.card-header { background: white; border-bottom: 1px solid #eef0f3; padding: 1rem 1.25rem; }
.card-header h6 { font-weight: 700; color: #0f172a; font-size: 0.9rem; }

.recent-list { list-style: none; padding: 0; margin: 0; }
.recent-list li {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.85rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
}
.recent-list li:last-child { border-bottom: none; }
.activity-icon {
  width: 36px; height: 36px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.95rem;
  flex-shrink: 0;
}

.info-list { margin: 0; }
.info-list > div {
  display: flex;
  justify-content: space-between;
  padding: 0.6rem 0;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.88rem;
}
.info-list > div:last-child { border-bottom: none; }
.info-list dt { color: #64748b; margin: 0; font-weight: 500; }
.info-list dd { margin: 0; color: #0f172a; font-weight: 500; }
</style>
