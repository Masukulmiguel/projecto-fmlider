<template>
  <div class="admin-dashboard">
    <div class="dashboard-header">
      <div class="header-left">
        <h1 class="welcome-title">Bem-vindo, Admin</h1>
        <p class="welcome-subtitle">{{ currentDate }}</p>
      </div>
      <div class="header-right">
        <div class="period-selector">
          <button
            v-for="period in periods"
            :key="period.value"
            class="period-btn"
            :class="{ active: statsDays === period.value }"
            @click="changePeriod(period.value)"
          >
            {{ period.label }}
          </button>
        </div>
        <button class="refresh-btn" @click="loadStats" :disabled="loading">
          <i class="bi bi-arrow-clockwise" :class="{ 'spin': loading }"></i>
        </button>
      </div>
    </div>

    <template v-if="loading && !data">
      <div class="row g-4 mb-4">
        <div v-for="n in 4" :key="n" class="col-lg-3 col-md-6">
          <div class="stat-card skeleton">
            <div class="skeleton-icon"></div>
            <div class="skeleton-content">
              <div class="skeleton-line short"></div>
              <div class="skeleton-line tall"></div>
              <div class="skeleton-line medium"></div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else-if="data">
      <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
          <div class="stat-card">
            <div class="stat-icon stat-icon-clients">
              <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Clientes</div>
              <div class="stat-value">{{ data.clients.total }}</div>
              <div class="stat-trend" :class="trendClass(data.clients.trend)">
                <i class="bi" :class="trendIcon(data.clients.trend)"></i>
                <span>{{ Math.abs(data.clients.trend || 0) }}%</span>
                <span class="trend-period">vs anterior</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card">
            <div class="stat-icon stat-icon-visitors">
              <i class="bi bi-globe"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Visitantes</div>
              <div class="stat-value">{{ data.visitors.total }}</div>
              <div class="stat-trend">
                <span class="trend-info">{{ data.visitors.today }} hoje</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card">
            <div class="stat-icon stat-icon-messages">
              <i class="bi bi-chat-dots-fill"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Mensagens</div>
              <div class="stat-value">{{ data.messages.total }}</div>
              <div class="stat-trend" v-if="data.messages.unread > 0">
                <span class="trend-badge badge-warning">{{ data.messages.unread }} por ler</span>
              </div>
              <div class="stat-trend" v-else>
                <span class="trend-info">Todas lidas</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="stat-card">
            <div class="stat-icon stat-icon-operations">
              <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Operações</div>
              <div class="stat-value">{{ totalOperations }}</div>
              <div class="stat-trend">
                <span class="trend-info">{{ data.embarques }} embarques · {{ data.cotacoes }} cotações</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4 mb-4">
        <div class="col-lg-8">
          <div class="chart-card">
            <div class="chart-header">
              <h6 class="chart-title">
                <i class="bi bi-graph-up-arrow"></i>
                Atividade nos últimos {{ statsDays }} dias
              </h6>
            </div>
            <div class="chart-body">
              <Line :data="trendData" :options="trendOptions" style="height: 320px" />
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="chart-card h-100">
            <div class="chart-header">
              <h6 class="chart-title">
                <i class="bi bi-pie-chart-fill"></i>
                Top países
              </h6>
            </div>
            <div class="chart-body">
              <Doughnut v-if="countryData.datasets[0].data.length" :data="countryData" :options="countryOptions" style="height: 320px" />
              <div v-else class="chart-empty">
                <i class="bi bi-globe2"></i>
                <p>Sem dados de países</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-6">
          <div class="list-card">
            <div class="list-header">
              <h6 class="list-title">
                <i class="bi bi-person-plus-fill"></i>
                Clientes recentes
              </h6>
              <router-link to="/admin/utilizadores" class="list-action">
                Ver todos <i class="bi bi-arrow-right"></i>
              </router-link>
            </div>
            <div class="list-body">
              <div v-for="c in data.recent.clients" :key="c.id" class="list-item">
                <div class="list-item-avatar">
                  <img v-if="c.photo" :src="c.photo" :alt="c.name">
                  <span v-else>{{ initials(c.name) }}</span>
                </div>
                <div class="list-item-content">
                  <div class="list-item-name">{{ c.name }}</div>
                  <div class="list-item-meta">{{ c.email }}</div>
                </div>
                <span class="status-badge" :class="`status-${c.approval_status}`">
                  {{ statusLabel(c.approval_status) }}
                </span>
              </div>
              <div v-if="data.recent.clients.length === 0" class="list-empty">
                Sem clientes recentes
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="list-card">
            <div class="list-header">
              <h6 class="list-title">
                <i class="bi bi-chat-dots-fill"></i>
                Mensagens recentes
              </h6>
              <router-link to="/admin/mensagens" class="list-action">
                Ver chat <i class="bi bi-arrow-right"></i>
              </router-link>
            </div>
            <div class="list-body">
              <div v-for="m in data.recent.messages" :key="m.id" class="list-item">
                <div class="list-item-avatar avatar-chat">
                  <i class="bi bi-chat-left-text"></i>
                </div>
                <div class="list-item-content">
                  <div class="list-item-name">{{ m.sender_name || 'Cliente' }}</div>
                  <div class="list-item-meta text-truncate">{{ m.message }}</div>
                </div>
                <div class="list-item-time">{{ formatTime(m.created_at) }}</div>
              </div>
              <div v-if="data.recent.messages.length === 0" class="list-empty">
                Sem mensagens recentes
              </div>
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

const periods = [
  { label: '7 dias', value: 7 },
  { label: '30 dias', value: 30 },
  { label: '90 dias', value: 90 }
]

const currentDate = computed(() => {
  return new Date().toLocaleDateString('pt-PT', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const totalOperations = computed(() => {
  if (!data.value) return 0
  return data.value.embarques + data.value.cotacoes + data.value.documentos
})

const authHeader = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } })

const changePeriod = (days) => {
  statsDays.value = days
  loadStats()
}

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

const trendClass = (trend) => {
  if (!trend || trend === 0) return 'trend-neutral'
  return trend > 0 ? 'trend-up' : 'trend-down'
}

const trendIcon = (trend) => {
  if (!trend || trend === 0) return 'bi-dash'
  return trend > 0 ? 'bi-arrow-up' : 'bi-arrow-down'
}

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
        borderColor: '#1877f2',
        backgroundColor: 'rgba(24, 119, 242, 0.1)',
        tension: 0.4,
        fill: true,
        pointRadius: 3,
        pointHoverRadius: 6,
        pointBackgroundColor: '#1877f2',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        borderWidth: 2,
      },
      {
        label: 'Mensagens',
        data: m.data,
        borderColor: '#31a24c',
        backgroundColor: 'rgba(49, 162, 76, 0.1)',
        tension: 0.4,
        fill: true,
        pointRadius: 3,
        pointHoverRadius: 6,
        pointBackgroundColor: '#31a24c',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        borderWidth: 2,
      },
      {
        label: 'Novos clientes',
        data: c.data,
        borderColor: '#f7b928',
        backgroundColor: 'rgba(247, 185, 40, 0.1)',
        tension: 0.4,
        fill: true,
        pointRadius: 3,
        pointHoverRadius: 6,
        pointBackgroundColor: '#f7b928',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        borderWidth: 2,
      },
    ],
  }
})

const trendOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: {
        usePointStyle: true,
        pointStyle: 'circle',
        padding: 20,
        font: { size: 12, weight: '500' }
      }
    },
    tooltip: {
      mode: 'index',
      intersect: false,
      backgroundColor: '#1c1e21',
      titleFont: { size: 13, weight: '600' },
      bodyFont: { size: 12 },
      padding: 12,
      cornerRadius: 8,
      displayColors: true,
      boxPadding: 4
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: { precision: 0, font: { size: 11 } },
      grid: { color: '#f0f2f5' }
    },
    x: {
      grid: { display: false },
      ticks: { font: { size: 11 } }
    },
  },
  interaction: { mode: 'nearest', intersect: false },
}

const countryData = computed(() => {
  const rows = (data.value?.charts?.visitors_by_country || []).filter(r => r.country && r.country !== 'Desconhecido')
  return {
    labels: rows.map(r => r.country),
    datasets: [{
      data: rows.map(r => parseInt(r.n)),
      backgroundColor: ['#1877f2', '#31a24c', '#f7b928', '#dc3545', '#8b5cf6', '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#14b8a6'],
      borderWidth: 0,
      hoverOffset: 4,
    }],
  }
})

const countryOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'right',
      labels: {
        boxWidth: 12,
        padding: 12,
        font: { size: 11 }
      }
    },
    tooltip: {
      backgroundColor: '#1c1e21',
      padding: 12,
      cornerRadius: 8,
      titleFont: { size: 13, weight: '600' },
      bodyFont: { size: 12 }
    }
  },
  cutout: '65%',
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
.admin-dashboard {
  background: #f0f2f5;
  min-height: 100vh;
  padding: 24px;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.welcome-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1c1e21;
  margin: 0;
}

.welcome-subtitle {
  font-size: 0.9rem;
  color: #65676b;
  margin: 4px 0 0;
  text-transform: capitalize;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.period-selector {
  display: flex;
  background: #ffffff;
  border-radius: 8px;
  padding: 4px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.period-btn {
  padding: 8px 16px;
  border: none;
  background: transparent;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
  color: #65676b;
  cursor: pointer;
  transition: all 0.2s ease;
}

.period-btn:hover {
  background: #f0f2f5;
  color: #1c1e21;
}

.period-btn.active {
  background: #1877f2;
  color: #ffffff;
}

.refresh-btn {
  width: 40px;
  height: 40px;
  border: none;
  background: #ffffff;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #65676b;
  font-size: 1.1rem;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.refresh-btn:hover {
  background: #f0f2f5;
  color: #1c1e21;
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.stat-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
  height: 100%;
}

.stat-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  flex-shrink: 0;
}

.stat-icon-clients {
  background: #e7f3ff;
  color: #1877f2;
}

.stat-icon-visitors {
  background: #e7f6e9;
  color: #31a24c;
}

.stat-icon-messages {
  background: #fff3cd;
  color: #f7b928;
}

.stat-icon-operations {
  background: #fde8e8;
  color: #dc3545;
}

.stat-content {
  flex: 1;
  min-width: 0;
}

.stat-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #65676b;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1c1e21;
  line-height: 1.2;
  margin-bottom: 4px;
}

.stat-trend {
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 4px;
}

.trend-up {
  color: #31a24c;
}

.trend-down {
  color: #dc3545;
}

.trend-neutral {
  color: #65676b;
}

.trend-period {
  color: #65676b;
}

.trend-info {
  color: #65676b;
  font-size: 0.8rem;
}

.trend-badge {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 12px;
}

.badge-warning {
  background: #fff3cd;
  color: #856404;
}

.chart-card {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.chart-header {
  padding: 16px 20px;
  border-bottom: 1px solid #f0f2f5;
}

.chart-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1c1e21;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.chart-title i {
  color: #1877f2;
}

.chart-body {
  padding: 20px;
}

.chart-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 280px;
  color: #65676b;
}

.chart-empty i {
  font-size: 3rem;
  margin-bottom: 12px;
  opacity: 0.5;
}

.chart-empty p {
  margin: 0;
  font-size: 0.9rem;
}

.list-card {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #f0f2f5;
}

.list-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1c1e21;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.list-title i {
  color: #1877f2;
}

.list-action {
  font-size: 0.85rem;
  font-weight: 500;
  color: #1877f2;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 4px;
  transition: color 0.2s ease;
}

.list-action:hover {
  color: #0d5bbd;
}

.list-body {
  max-height: 360px;
  overflow-y: auto;
}

.list-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  border-bottom: 1px solid #f0f2f5;
  transition: background 0.2s ease;
}

.list-item:last-child {
  border-bottom: none;
}

.list-item:hover {
  background: #fafbfc;
}

.list-item-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1877f2, #0d5bbd);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 600;
  flex-shrink: 0;
  overflow: hidden;
}

.list-item-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.list-item-avatar.avatar-chat {
  background: linear-gradient(135deg, #31a24c, #218838);
}

.list-item-content {
  flex: 1;
  min-width: 0;
}

.list-item-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1c1e21;
  margin-bottom: 2px;
}

.list-item-meta {
  font-size: 0.8rem;
  color: #65676b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.list-item-time {
  font-size: 0.8rem;
  color: #65676b;
  flex-shrink: 0;
}

.list-empty {
  text-align: center;
  padding: 40px 20px;
  color: #65676b;
  font-size: 0.9rem;
}

.status-badge {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 12px;
  white-space: nowrap;
  flex-shrink: 0;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-approved {
  background: #d1fae5;
  color: #065f46;
}

.status-rejected {
  background: #fee2e2;
  color: #991b1b;
}

.skeleton {
  pointer-events: none;
}

.skeleton-icon {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  background: linear-gradient(90deg, #f0f2f5 25%, #e4e6e9 50%, #f0f2f5 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-content {
  flex: 1;
}

.skeleton-line {
  height: 12px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f0f2f5 25%, #e4e6e9 50%, #f0f2f5 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  margin-bottom: 8px;
}

.skeleton-line.short { width: 60px; }
.skeleton-line.tall { width: 80px; height: 24px; }
.skeleton-line.medium { width: 100px; }

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.reset-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  backdrop-filter: blur(4px);
}

.reset-modal {
  background: #ffffff;
  border-radius: 12px;
  width: 90%;
  max-width: 480px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

.reset-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #f0f2f5;
}

.reset-modal-header h5 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.reset-modal-body {
  padding: 20px;
}

.reset-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding: 16px 20px;
  border-top: 1px solid #f0f2f5;
  background: #fafbfc;
}

.reset-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.reset-list li {
  padding: 6px 0;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  color: #1c1e21;
}

.reset-list li i {
  color: #dc3545;
  width: 20px;
  text-align: center;
}

@media (max-width: 768px) {
  .admin-dashboard {
    padding: 16px;
  }

  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .welcome-title {
    font-size: 1.25rem;
  }
}
</style>
