<template>
  <div class="admin-dashboard p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="text-muted mb-0">Visão geral do sistema nos últimos {{ statsDays }} dias.</p>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <label class="text-muted small mb-0">Período:</label>
        <select v-model.number="statsDays" class="form-select form-select-sm" style="width: 130px;" @change="loadStats">
          <option :value="7">7 dias</option>
          <option :value="30">30 dias</option>
          <option :value="90">90 dias</option>
        </select>
        <button class="btn btn-sm btn-outline-secondary" @click="loadStats" :disabled="loading">
          <i class="bi bi-arrow-clockwise"></i>
        </button>
      </div>
    </div>

    <div v-if="loading && !data" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>
    <template v-else-if="data">
      <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
          <div class="stat-card stat-primary">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-body">
              <div class="stat-label">Clientes</div>
              <div class="stat-value">{{ data.clients.total }}</div>
              <div class="stat-meta">
                <span class="text-success">{{ data.clients.active }} ativos</span>
                <span v-if="data.clients.pending > 0" class="badge bg-warning text-dark ms-1">{{ data.clients.pending }} pendentes</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="stat-card stat-info">
            <div class="stat-icon"><i class="bi bi-globe"></i></div>
            <div class="stat-body">
              <div class="stat-label">Visitantes</div>
              <div class="stat-value">{{ data.visitors.total }}</div>
              <div class="stat-meta">
                <span class="text-info">{{ data.visitors.today }} hoje</span>
                <span class="text-muted ms-1">· {{ data.visitors.unique_sessions }} sessões</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="stat-card stat-success">
            <div class="stat-icon"><i class="bi bi-chat-dots-fill"></i></div>
            <div class="stat-body">
              <div class="stat-label">Mensagens</div>
              <div class="stat-value">{{ data.messages.total }}</div>
              <div class="stat-meta">
                <span class="text-muted">{{ data.messages.today }} hoje</span>
                <span v-if="data.messages.unread > 0" class="badge bg-danger ms-1">{{ data.messages.unread }} por ler</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-xl-3">
          <div class="stat-card stat-warning">
            <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
            <div class="stat-body">
              <div class="stat-label">Operações</div>
              <div class="stat-value">{{ data.embarques + data.cotacoes + data.documentos }}</div>
              <div class="stat-meta">
                <span>{{ data.embarques }} embarques</span>
                <span class="mx-1">·</span>
                <span>{{ data.cotacoes }} cotações</span>
                <span class="mx-1">·</span>
                <span>{{ data.documentos }} docs</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-lg-8">
          <div class="card chart-card">
            <div class="card-header">
              <h6 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Atividade nos últimos {{ statsDays }} dias</h6>
            </div>
            <div class="card-body">
              <Line :data="trendData" :options="trendOptions" style="height: 320px" />
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card chart-card h-100">
            <div class="card-header">
              <h6 class="mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Top países</h6>
            </div>
            <div class="card-body">
              <Doughnut v-if="countryData.datasets[0].data.length" :data="countryData" :options="countryOptions" style="height: 320px" />
              <div v-else class="text-center text-muted py-5">
                <i class="bi bi-globe2"></i>
                <p class="mb-0 mt-2 small">Sem dados de países.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h6 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i>Clientes recentes</h6>
              <router-link to="/admin/utilizadores" class="btn btn-sm btn-outline-primary">Ver todos</router-link>
            </div>
            <div class="card-body p-0">
              <ul class="recent-list">
                <li v-for="c in data.recent.clients" :key="c.id">
                  <div class="avatar-sm">{{ initials(c.name) }}</div>
                  <div class="flex-grow-1">
                    <div class="fw-medium">{{ c.name }}</div>
                    <small class="text-muted">{{ c.email }} · {{ c.company_name || '—' }}</small>
                  </div>
                  <span class="status-pill" :class="`status-${c.approval_status}`">{{ statusLabel(c.approval_status) }}</span>
                </li>
                <li v-if="data.recent.clients.length === 0" class="text-center text-muted py-4">
                  Sem clientes.
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h6 class="mb-0"><i class="bi bi-chat-dots-fill me-2"></i>Mensagens recentes</h6>
              <router-link to="/admin/mensagens" class="btn btn-sm btn-outline-primary">Ver chat</router-link>
            </div>
            <div class="card-body p-0">
              <ul class="recent-list">
                <li v-for="m in data.recent.messages" :key="m.id">
                  <div class="avatar-sm bg-info text-white"><i class="bi bi-chat-left-text"></i></div>
                  <div class="flex-grow-1">
                    <div class="fw-medium">{{ m.sender_name || 'Cliente' }}</div>
                    <small class="text-muted d-block text-truncate" style="max-width: 320px;">{{ m.message }}</small>
                  </div>
                  <small class="text-muted">{{ formatTime(m.created_at) }}</small>
                </li>
                <li v-if="data.recent.messages.length === 0" class="text-center text-muted py-4">
                  Sem mensagens.
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-if="showResetModal" class="reset-overlay" @click.self="showResetModal = false">
      <div class="reset-modal">
        <div class="reset-modal-header">
          <h5><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Reset do Sistema</h5>
          <button class="btn-close" @click="showResetModal = false"></button>
        </div>
        <div class="reset-modal-body">
          <p class="text-danger fw-bold mb-3">Esta ação irá apagar permanentemente:</p>
          <ul class="reset-list mb-3">
            <li><i class="bi bi-people-fill"></i> Todos os funcionários e clientes</li>
            <li><i class="bi bi-globe"></i> Todos os visitantes registados</li>
            <li><i class="bi bi-chat-dots-fill"></i> Todas as mensagens</li>
            <li><i class="bi bi-box-seam-fill"></i> Todos os embarques</li>
            <li><i class="bi bi-file-earmark"></i> Todos os documentos</li>
            <li><i class="bi bi-receipt"></i> Todas as cotações</li>
            <li><i class="bi bi-person-rolodex"></i> Todos os contactos</li>
          </ul>
          <p class="text-muted small mb-3">A conta de administrador será mantida.</p>
          <div class="mb-3">
            <label class="form-label fw-bold">Chave secreta:</label>
            <input v-model="resetSecretKey" type="password" class="form-control" placeholder="Insira a chave secreta" @keyup.enter="executeReset">
          </div>
          <div v-if="resetError" class="alert alert-danger py-2">{{ resetError }}</div>
          <div v-if="resetSuccess" class="alert alert-success py-2">{{ resetSuccess }}</div>
        </div>
        <div class="reset-modal-footer">
          <button class="btn btn-secondary" @click="showResetModal = false">Cancelar</button>
          <button class="btn btn-danger" :disabled="!resetSecretKey || resetLoading" @click="executeReset">
            <span v-if="resetLoading" class="spinner-border spinner-border-sm me-1"></span>
            {{ resetLoading ? 'A apagar...' : 'Apagar Tudo' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'
import { Line, Doughnut } from 'vue-chartjs'
import {
  Chart, LineElement, PointElement, LineController,
  CategoryScale, LinearScale, Tooltip, Legend, Filler,
  ArcElement, DoughnutController
} from 'chart.js'

Chart.register(
  LineElement, PointElement, LineController,
  CategoryScale, LinearScale, Tooltip, Legend, Filler,
  ArcElement, DoughnutController
)

const authStore = useAuthStore()
const data = ref(null)
const loading = ref(false)
const statsDays = ref(30)

const showResetModal = ref(false)
const resetSecretKey = ref('')
const resetLoading = ref(false)
const resetError = ref('')
const resetSuccess = ref('')

const authHeader = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } })

const loadStats = async () => {
  loading.value = true
  try {
    const r = await axios.get('/api/admin/dashboard/stats', { ...authHeader(), params: { days: statsDays.value } })
    if (r.data.success) data.value = r.data.data
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

const openResetModal = () => {
  showResetModal.value = true
  resetSecretKey.value = ''
  resetError.value = ''
  resetSuccess.value = ''
}

const executeReset = async () => {
  if (!resetSecretKey.value) return
  resetLoading.value = true
  resetError.value = ''
  resetSuccess.value = ''
  try {
    const r = await axios.post('/api/admin/secret-reset', { secret_key: resetSecretKey.value }, authHeader())
    if (r.data.success) {
      resetSuccess.value = 'Reset efetuado com sucesso! A recarregar...'
      setTimeout(() => { window.location.reload() }, 2000)
    }
  } catch (e) {
    resetError.value = e.response?.data?.message || 'Erro ao efetar reset'
  } finally {
    resetLoading.value = false
  }
}

const handleSecretKey = (e) => {
  if (e.ctrlKey && e.shiftKey && e.key === 'R') {
    e.preventDefault()
    openResetModal()
  }
}

const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()

const statusLabel = (s) => ({ pending: 'Pendente', approved: 'Aprovado', rejected: 'Rejeitado' }[s] || s)

const formatTime = (iso) => {
  if (!iso) return ''
  const d = new Date(iso)
  const now = new Date()
  const diff = (now - d) / 1000
  if (diff < 60) return 'agora'
  if (diff < 3600) return `${Math.floor(diff/60)}min`
  if (diff < 86400) return `${Math.floor(diff/3600)}h`
  return d.toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit' })
}

const fillDates = (rows, days) => {
  const map = new Map()
  rows.forEach(r => map.set(r.d, parseInt(r.n)))
  const result = []
  const labels = []
  for (let i = days - 1; i >= 0; i--) {
    const d = new Date()
    d.setDate(d.getDate() - i)
    const k = d.toISOString().slice(0, 10)
    labels.push(d.toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit' }))
    result.push(map.get(k) || 0)
  }
  return { labels, data: result }
}

const trendData = computed(() => {
  if (!data.value) return { labels: [], datasets: [] }
  const v = fillDates(data.value.charts.visitors_by_day, statsDays.value)
  const m = fillDates(data.value.charts.messages_by_day, statsDays.value)
  const c = fillDates(data.value.charts.clients_by_day, statsDays.value)
  return {
    labels: v.labels,
    datasets: [
      {
        label: 'Visitantes',
        data: v.data,
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37, 99, 235, 0.12)',
        tension: 0.35, fill: true, pointRadius: 3, pointHoverRadius: 5,
      },
      {
        label: 'Mensagens',
        data: m.data,
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.12)',
        tension: 0.35, fill: true, pointRadius: 3, pointHoverRadius: 5,
      },
      {
        label: 'Novos clientes',
        data: c.data,
        borderColor: '#f59e0b',
        backgroundColor: 'rgba(245, 158, 11, 0.12)',
        tension: 0.35, fill: true, pointRadius: 3, pointHoverRadius: 5,
      },
    ],
  }
})

const trendOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top' },
    tooltip: { mode: 'index', intersect: false },
  },
  scales: {
    y: { beginAtZero: true, ticks: { precision: 0 } },
    x: { grid: { display: false } },
  },
  interaction: { mode: 'nearest', intersect: false },
}

const countryData = computed(() => {
  const rows = (data.value?.charts?.visitors_by_country || []).filter(r => r.country && r.country !== 'Desconhecido')
  return {
    labels: rows.map(r => r.country),
    datasets: [{
      data: rows.map(r => parseInt(r.n)),
      backgroundColor: ['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#84cc16','#f97316','#ec4899','#14b8a6'],
      borderWidth: 0,
    }],
  }
})

const countryOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } },
  cutout: '60%',
}

onMounted(() => {
  loadStats()
  document.addEventListener('keydown', handleSecretKey)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleSecretKey)
})
</script>

<style scoped>
.admin-dashboard { background: #f5f7fa; min-height: 100vh; }
.page-title { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }

.stat-card {
  background: #fff;
  padding: 1.25rem;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
  display: flex;
  gap: 1rem;
  align-items: center;
  height: 100%;
  border-left: 4px solid #e2e8f0;
}
.stat-primary { border-left-color: #2563eb; }
.stat-info { border-left-color: #06b6d4; }
.stat-success { border-left-color: #10b981; }
.stat-warning { border-left-color: #f59e0b; }

.stat-icon {
  width: 52px; height: 52px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem;
  flex-shrink: 0;
}
.stat-primary .stat-icon { background: #dbeafe; color: #1d4ed8; }
.stat-info .stat-icon { background: #cffafe; color: #0e7490; }
.stat-success .stat-icon { background: #d1fae5; color: #047857; }
.stat-warning .stat-icon { background: #fef3c7; color: #b45309; }

.stat-body { flex: 1; min-width: 0; }
.stat-label { color: #64748b; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stat-value { font-size: 1.85rem; font-weight: 700; color: #0f172a; line-height: 1.1; }
.stat-meta { font-size: 0.78rem; color: #64748b; }

.chart-card { border: none; border-radius: 14px; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05); }
.chart-card .card-header { background: #fff; border-bottom: 1px solid #eef0f3; padding: 0.85rem 1.25rem; }
.chart-card .card-header h6 { font-weight: 700; color: #0f172a; }

.card { border: none; border-radius: 14px; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05); }
.card-header { background: #fff; border-bottom: 1px solid #eef0f3; padding: 0.85rem 1.25rem; }
.card-header h6 { font-weight: 700; color: #0f172a; }

.recent-list { list-style: none; padding: 0; margin: 0; }
.recent-list li {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.85rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
}
.recent-list li:last-child { border-bottom: none; }

.avatar-sm {
  width: 38px; height: 38px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.8rem; font-weight: 600;
  flex-shrink: 0;
}

.status-pill {
  font-size: 0.7rem; font-weight: 600;
  padding: 3px 10px;
  border-radius: 12px;
  white-space: nowrap;
}
.status-pending { background: #fef3c7; color: #92400e; }
.status-approved { background: #d1fae5; color: #065f46; }
.status-rejected { background: #fee2e2; color: #991b1b; }

.reset-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.6);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999; backdrop-filter: blur(4px);
}
.reset-modal {
  background: #fff; border-radius: 16px; width: 90%; max-width: 480px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden;
}
.reset-modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0;
}
.reset-modal-header h5 { margin: 0; font-size: 1.1rem; font-weight: 700; }
.reset-modal-body { padding: 1.5rem; }
.reset-modal-footer {
  display: flex; justify-content: flex-end; gap: 0.5rem;
  padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc;
}
.reset-list { list-style: none; padding: 0; margin: 0; }
.reset-list li {
  padding: 0.4rem 0; display: flex; align-items: center; gap: 0.5rem;
  font-size: 0.9rem; color: #334155;
}
.reset-list li i { color: #dc2626; width: 20px; text-align: center; }
</style>
