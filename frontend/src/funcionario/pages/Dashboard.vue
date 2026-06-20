<template>
  <div class="funcionario-dashboard p-4 p-md-5">
    <!-- Welcome Banner -->
    <div class="welcome-banner fml-fade-up">
      <div class="welcome-content">
        <div class="welcome-text">
          <h1>{{ greeting }}, {{ authStore.user?.name }} 👋</h1>
          <p>{{ t('dashboard.subtitle') }}</p>
        </div>
        <div class="welcome-avatar">
          <img v-if="authStore.user?.photo" :src="authStore.user.photo" :alt="authStore.user.name">
          <span v-else>{{ initials(authStore.user?.name) }}</span>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
      <div v-if="can('embarques.view')" class="col-md-6 col-xl-3">
        <router-link to="/funcionario/embarques" class="stat-card-link">
          <div class="stat-card fml-fade-up stagger-1 hover-lift">
            <div class="stat-icon stat-blue">
              <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="stat-info">
              <span class="stat-label">{{ t('dashboard.shipments') }}</span>
              <span class="stat-value">{{ counts.embarques }}</span>
              <span class="stat-meta">{{ counts.embarques_pendente || 0 }} {{ t('dashboard.pending') }}</span>
            </div>
          </div>
        </router-link>
      </div>
      <div v-if="can('cotacoes.view')" class="col-md-6 col-xl-3">
        <router-link to="/funcionario/cotacoes" class="stat-card-link">
          <div class="stat-card fml-fade-up stagger-2 hover-lift">
            <div class="stat-icon stat-green">
              <i class="bi bi-receipt"></i>
            </div>
            <div class="stat-info">
              <span class="stat-label">{{ t('dashboard.quotes') }}</span>
              <span class="stat-value">{{ counts.cotacoes }}</span>
              <span class="stat-meta">{{ counts.cotacoes_pendente || 0 }} {{ t('dashboard.pending') }}</span>
            </div>
          </div>
        </router-link>
      </div>
      <div v-if="can('clients.view')" class="col-md-6 col-xl-3">
        <router-link to="/funcionario/clientes" class="stat-card-link">
          <div class="stat-card fml-fade-up stagger-3 hover-lift">
            <div class="stat-icon stat-cyan">
              <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-info">
              <span class="stat-label">{{ t('dashboard.clients') }}</span>
              <span class="stat-value">{{ counts.clientes }}</span>
              <span class="stat-meta">{{ counts.clientes_pendente || 0 }} {{ t('dashboard.pending_approval') }}</span>
            </div>
          </div>
        </router-link>
      </div>
      <div v-if="can('documentos.view')" class="col-md-6 col-xl-3">
        <router-link to="/funcionario/documentos" class="stat-card-link">
          <div class="stat-card fml-fade-up stagger-4 hover-lift">
            <div class="stat-icon stat-amber">
              <i class="bi bi-file-earmark-text-fill"></i>
            </div>
            <div class="stat-info">
              <span class="stat-label">{{ t('dashboard.documents') }}</span>
              <span class="stat-value">{{ counts.documentos }}</span>
              <span class="stat-meta">{{ t('dashboard.files') }}</span>
            </div>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Activity + Quick Actions -->
    <div class="row g-3 mb-4">
      <div class="col-lg-8">
        <div class="content-card fml-fade-up stagger-3">
          <div class="card-header-custom">
            <h6><i class="bi bi-clock-history me-2"></i>{{ t('dashboard.recent_activity') }}</h6>
          </div>
          <div class="activity-list">
            <div v-for="(a, i) in recentActivity" :key="i" class="activity-item">
              <div class="activity-icon" :class="`stat-${a.color}`">
                <i :class="['bi', a.icon]"></i>
              </div>
              <div class="activity-content">
                <span class="activity-title">{{ a.title }}</span>
                <span class="activity-subtitle">{{ a.subtitle }}</span>
              </div>
              <span class="activity-time">{{ a.time }}</span>
            </div>
            <div v-if="recentActivity.length === 0" class="empty-state">
              <i class="bi bi-inbox"></i>
              <p>{{ t('dashboard.no_activity') }}</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="content-card fml-fade-up stagger-4">
          <div class="card-header-custom">
            <h6><i class="bi bi-lightning-charge-fill me-2"></i>{{ t('dashboard.quick_actions') }}</h6>
          </div>
          <div class="quick-actions">
            <router-link v-if="can('embarques.manage')" to="/funcionario/embarques" class="action-btn">
              <div class="action-icon stat-blue"><i class="bi bi-plus-circle"></i></div>
              <span>{{ t('dashboard.new_shipment') }}</span>
            </router-link>
            <router-link v-if="can('cotacoes.manage')" to="/funcionario/cotacoes" class="action-btn">
              <div class="action-icon stat-green"><i class="bi bi-plus-square"></i></div>
              <span>{{ t('dashboard.new_quote') }}</span>
            </router-link>
            <router-link v-if="can('clients.view')" to="/funcionario/clientes" class="action-btn">
              <div class="action-icon stat-cyan"><i class="bi bi-people"></i></div>
              <span>{{ t('dashboard.view_clients') }}</span>
            </router-link>
            <router-link v-if="can('documentos.view')" to="/funcionario/documentos" class="action-btn">
              <div class="action-icon stat-amber"><i class="bi bi-folder2-open"></i></div>
              <span>{{ t('dashboard.view_documents') }}</span>
            </router-link>
            <router-link v-if="can('chat.view')" to="/funcionario/mensagens" class="action-btn">
              <div class="action-icon stat-purple"><i class="bi bi-chat-dots"></i></div>
              <span>{{ t('dashboard.view_messages') }}</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Permissions + Account Info -->
    <div class="row g-3">
      <div class="col-lg-6">
        <div class="content-card fml-fade-up stagger-5">
          <div class="card-header-custom">
            <h6><i class="bi bi-shield-check me-2"></i>{{ t('dashboard.permissions') }}</h6>
          </div>
          <div class="permissions-grid">
            <span v-for="p in permissionLabels" :key="p.code" class="perm-tag">
              <i class="bi bi-check2"></i> {{ p.label }}
            </span>
            <span v-if="permissionLabels.length === 0" class="empty-text">
              {{ t('dashboard.no_permissions') }}
            </span>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="content-card fml-fade-up stagger-6">
          <div class="card-header-custom">
            <h6><i class="bi bi-person-circle me-2"></i>{{ t('dashboard.account_info') }}</h6>
          </div>
          <div class="info-grid">
            <div class="info-row">
              <span class="info-label">{{ t('dashboard.name') }}</span>
              <span class="info-value">{{ authStore.user?.name }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">{{ t('dashboard.email') }}</span>
              <span class="info-value">{{ authStore.user?.email }}</span>
            </div>
            <div v-if="authStore.user?.position" class="info-row">
              <span class="info-label">{{ t('dashboard.position') }}</span>
              <span class="info-value">{{ authStore.user.position }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">{{ t('dashboard.username') }}</span>
              <span class="info-value">@{{ authStore.user?.username }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">{{ t('dashboard.role') }}</span>
              <span class="info-value"><span class="role-badge">{{ t('dashboard.employee') }}</span></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'
import { useI18n } from '@/composables/useI18n.js'

const authStore = useAuthStore()
const { t } = useI18n()
const counts = reactive({ embarques: 0, embarques_pendente: 0, cotacoes: 0, cotacoes_pendente: 0, clientes: 0, clientes_pendente: 0, documentos: 0 })
const recentActivity = ref([])

const greeting = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return t('dashboard.welcome')
  if (h < 18) return t('dashboard.welcome_afternoon')
  return t('dashboard.welcome_evening')
})

const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()

const PERM_LABELS = computed(() => ({
  'dashboard.view': t('funcionario.perm_dashboard'),
  'clients.view': t('funcionario.perm_view_clients'), 'clients.manage': t('funcionario.perm_manage_clients'),
  'embarques.view': t('funcionario.perm_view_shipments'), 'embarques.manage': t('funcionario.perm_manage_shipments'),
  'cotacoes.view': t('funcionario.perm_view_quotes'), 'cotacoes.manage': t('funcionario.perm_manage_quotes'),
  'documentos.view': t('funcionario.perm_view_documents'), 'documentos.manage': t('funcionario.perm_manage_documents'),
  'contactos.view': t('funcionario.perm_view_contacts'), 'contactos.manage': t('funcionario.perm_manage_contacts'),
  'chat.view': t('funcionario.perm_view_chat'), 'chat.reply': t('funcionario.perm_reply_chat'),
  'visitors.view': t('funcionario.perm_view_visitors'),
  'content.manage': t('funcionario.perm_manage_content'),
}))

const permissionLabels = computed(() => {
  return (authStore.permissions || []).map(code => ({ code, label: PERM_LABELS.value[code] || code }))
})

const can = (perm) => authStore.can(perm)

const load = async () => {
  const tasks = []
  if (can('embarques.view')) tasks.push(
    supabase.from('embarques').select('*').then(({ data, error }) => {
      if (!error && data) { counts.embarques = data.length; counts.embarques_pendente = data.filter(e => e.status === 'pendente').length }
    })
  )
  if (can('cotacoes.view')) tasks.push(
    supabase.from('cotacoes').select('*').then(({ data, error }) => {
      if (!error && data) { counts.cotacoes = data.length; counts.cotacoes_pendente = data.filter(c => c.status === 'pendente').length }
    })
  )
  if (can('documentos.view')) tasks.push(
    supabase.from('documentos').select('*').then(({ data, error }) => {
      if (!error && data) counts.documentos = data.length
    })
  )
  if (can('clients.view')) tasks.push(
    supabase.from('users').select('*').eq('role', 'cliente').then(({ data, error }) => {
      if (!error && data) { counts.clientes = data.length; counts.clientes_pendente = data.filter(u => u.approval_status === 'pending').length }
    })
  )

  await Promise.all(tasks)

  const acts = []
  if (counts.embarques_pendente > 0) acts.push({ title: `${counts.embarques_pendente} ${t('dashboard.shipments').toLowerCase()} ${t('dashboard.pending')}`, subtitle: t('dashboard.needs_attention'), icon: 'bi-box-seam-fill', color: 'blue', time: t('dashboard.now') })
  if (counts.cotacoes_pendente > 0) acts.push({ title: `${counts.cotacoes_pendente} ${t('dashboard.quotes').toLowerCase()} ${t('dashboard.pending')}`, subtitle: t('dashboard.awaits_response'), icon: 'bi-receipt', color: 'green', time: t('dashboard.now') })
  if (counts.clientes_pendente > 0) acts.push({ title: `${counts.clientes_pendente} ${t('dashboard.clients').toLowerCase()} ${t('dashboard.pending_approval')}`, subtitle: t('dashboard.awaits_admin'), icon: 'bi-person-plus-fill', color: 'cyan', time: t('dashboard.today') })
  recentActivity.value = acts
}

onMounted(load)
</script>

<style scoped>
.funcionario-dashboard { background: var(--content-bg); min-height: 100vh; }

/* Welcome Banner */
.welcome-banner {
  background: var(--welcome-gradient);
  border-radius: 20px;
  padding: 2rem 2.5rem;
  margin-bottom: 1.5rem;
  position: relative;
  overflow: hidden;
}
.welcome-banner::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -20%;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
  border-radius: 50%;
}
.welcome-content { display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1; }
.welcome-text h1 { color: #ffffff; font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; }
.welcome-text p { color: rgba(255,255,255,0.8); margin: 0; font-size: 0.95rem; }
.welcome-avatar {
  width: 70px; height: 70px;
  border-radius: 50%;
  background: rgba(255,255,255,0.2);
  backdrop-filter: blur(10px);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; font-weight: 700; color: #ffffff;
  border: 3px solid rgba(255,255,255,0.3);
  overflow: hidden;
}
.welcome-avatar img { width: 100%; height: 100%; object-fit: cover; }

/* Stat Cards */
.stat-card-link { text-decoration: none; color: inherit; display: block; }
.stat-card {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: var(--card-radius);
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.25s var(--fml-ease);
}
.stat-card-link:hover .stat-card {
  transform: translateY(-4px);
  box-shadow: var(--card-shadow-hover);
}
.stat-icon {
  width: 56px; height: 56px;
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}
.stat-blue { background: var(--stat-blue-bg); color: var(--stat-blue-text); }
.stat-green { background: var(--stat-green-bg); color: var(--stat-green-text); }
.stat-cyan { background: var(--stat-cyan-bg); color: var(--stat-cyan-text); }
.stat-amber { background: var(--stat-amber-bg); color: var(--stat-amber-text); }
.stat-purple { background: var(--stat-purple-bg); color: var(--stat-purple-text); }

.stat-info { display: flex; flex-direction: column; }
.stat-label { color: var(--text-secondary); font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stat-value { font-size: 2rem; font-weight: 700; color: var(--text-primary); line-height: 1.2; }
.stat-meta { font-size: 0.8rem; color: var(--text-muted); }

/* Content Cards */
.content-card {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: var(--card-radius);
  overflow: hidden;
  transition: background 0.3s ease, border-color 0.3s ease;
}
.card-header-custom {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--divider);
}
.card-header-custom h6 {
  margin: 0;
  font-weight: 700;
  color: var(--text-primary);
  font-size: 0.95rem;
}

/* Activity List */
.activity-list { padding: 0.5rem 0; }
.activity-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1.5rem;
  transition: background 0.15s ease;
  cursor: default;
}
.activity-item:hover { background: var(--hover-bg); }
.activity-icon {
  width: 40px; height: 40px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem;
  flex-shrink: 0;
}
.activity-content { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.activity-title { font-weight: 600; font-size: 0.9rem; color: var(--text-primary); }
.activity-subtitle { font-size: 0.8rem; color: var(--text-muted); }
.activity-time { font-size: 0.78rem; color: var(--text-muted); white-space: nowrap; }

.empty-state {
  text-align: center;
  padding: 3rem 1.5rem;
  color: var(--text-muted);
}
.empty-state i { font-size: 2.5rem; margin-bottom: 0.5rem; display: block; }
.empty-state p { margin: 0; }

/* Quick Actions */
.quick-actions { padding: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem; }
.action-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  border-radius: 12px;
  text-decoration: none;
  color: var(--text-primary);
  font-weight: 500;
  font-size: 0.9rem;
  transition: all 0.2s ease;
}
.action-btn:hover { background: var(--hover-bg); transform: translateX(4px); }
.action-icon {
  width: 40px; height: 40px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem;
}

/* Permissions */
.permissions-grid { padding: 1.25rem 1.5rem; display: flex; flex-wrap: wrap; gap: 0.5rem; }
.perm-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: var(--accent-light);
  color: var(--accent);
  font-size: 0.8rem;
  font-weight: 500;
  padding: 6px 14px;
  border-radius: 20px;
  transition: all 0.2s ease;
}
.perm-tag:hover { transform: translateY(-1px); box-shadow: 0 2px 8px var(--accent-glow); }
.empty-text { color: var(--text-muted); font-size: 0.88rem; }

/* Info Grid */
.info-grid { padding: 1rem 1.5rem; }
.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.7rem 0;
  border-bottom: 1px solid var(--divider);
}
.info-row:last-child { border-bottom: none; }
.info-label { color: var(--text-muted); font-weight: 500; font-size: 0.88rem; }
.info-value { color: var(--text-primary); font-weight: 600; font-size: 0.88rem; }
.role-badge {
  background: var(--stat-green-bg);
  color: var(--stat-green-text);
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

@media (max-width: 768px) {
  .welcome-banner { padding: 1.5rem; }
  .welcome-content { flex-direction: column; text-align: center; gap: 1rem; }
  .welcome-text h1 { font-size: 1.4rem; }
}
</style>
