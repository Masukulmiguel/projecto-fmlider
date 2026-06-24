<template>
  <div class="admin-dashboard">
    <div class="dashboard-header">
      <div class="header-left">
        <h1 class="welcome-title">{{ t('admin.dashboard_welcome') }}</h1>
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
      <div class="stats-grid mb-4">
        <div v-for="n in 5" :key="n" class="stat-card skeleton">
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
      <div class="stats-grid mb-4">
          <div class="stat-card">
            <div class="stat-icon stat-icon-clients">
              <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Clientes</div>
              <div class="stat-value">{{ data.clients.total }}</div>
              <div class="stat-trend">
                <span class="trend-info">{{ data.clients.active }} activos</span>
              </div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon stat-icon-employees">
              <i class="bi bi-person-badge-fill"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">Funcionários</div>
              <div class="stat-value">{{ data.employees.total }}</div>
              <div class="stat-trend">
                <span class="trend-info">{{ data.employees.active }} activos</span>
              </div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon stat-icon-visitors">
              <i class="bi bi-globe"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">{{ t('admin.visitors_title') }}</div>
              <div class="stat-value">{{ data.visitors.total }}</div>
              <div class="stat-trend">
                <span class="trend-info">{{ data.visitors.today }} {{ t('admin.dashboard_today') }}</span>
              </div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon stat-icon-messages">
              <i class="bi bi-chat-dots-fill"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">{{ t('admin.messages_title') }}</div>
              <div class="stat-value">{{ data.messages.total }}</div>
              <div class="stat-trend" v-if="data.messages.unread > 0">
                <span class="trend-badge badge-warning">{{ data.messages.unread }} {{ t('admin.dashboard_unread') }}</span>
              </div>
              <div class="stat-trend" v-else>
                <span class="trend-info">{{ t('admin.dashboard_all_read') }}</span>
              </div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon stat-icon-operations">
              <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="stat-content">
              <div class="stat-label">{{ t('admin.dashboard_operations') }}</div>
              <div class="stat-value">{{ totalOperations }}</div>
              <div class="stat-trend">
                <span class="trend-info">{{ data.embarques }} {{ t('admin.dashboard_shipments_count') }} · {{ data.cotacoes }} {{ t('admin.dashboard_quotes_count') }}</span>
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
                {{ t('admin.dashboard_activity_days') }} {{ statsDays }} {{ t('admin.dashboard_days') }}
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
                {{ t('admin.dashboard_top_countries') }}
              </h6>
            </div>
            <div class="chart-body">
              <Doughnut v-if="countryData.datasets[0].data.length" :data="countryData" :options="countryOptions" style="height: 320px" />
              <div v-else class="chart-empty">
                <i class="bi bi-globe2"></i>
                <p>{{ t('admin.dashboard_no_country_data') }}</p>
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
                {{ t('admin.total_clients') }}
              </h6>
              <router-link to="/admin/utilizadores" class="list-action">
                {{ t('admin.dashboard_view_all') }} <i class="bi bi-arrow-right"></i>
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
                {{ t('admin.dashboard_no_recent_clients') }}
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="list-card">
            <div class="list-header">
              <h6 class="list-title">
                <i class="bi bi-chat-dots-fill"></i>
                {{ t('admin.messages_title') }}
              </h6>
              <router-link to="/admin/mensagens" class="list-action">
                {{ t('admin.dashboard_view_chat') }} <i class="bi bi-arrow-right"></i>
              </router-link>
            </div>
            <div class="list-body">
              <div v-for="m in data.recent.messages" :key="m.id" class="list-item">
                <div class="list-item-avatar avatar-chat">
                  <i class="bi bi-chat-left-text"></i>
                </div>
                <div class="list-item-content">
                  <div class="list-item-name">{{ m.users?.name || t('admin.dashboard_client') }}</div>
                  <div class="list-item-meta text-truncate">{{ m.message }}</div>
                </div>
                <div class="list-item-time">{{ formatTime(m.created_at) }}</div>
              </div>
              <div v-if="data.recent.messages.length === 0" class="list-empty">
                {{ t('admin.dashboard_no_recent_messages') }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-if="showResetModal" class="reset-overlay" @click.self="showResetModal = false">
      <div class="reset-modal">
        <div class="reset-modal-header">
          <h5><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>{{ t('admin.dashboard_system_reset') }}</h5>
          <button class="btn-close" @click="showResetModal = false"></button>
        </div>
        <div class="reset-modal-body">
          <p class="text-danger fw-bold mb-3">{{ t('admin.dashboard_reset_warning') }}</p>
          <ul class="reset-list mb-3">
            <li><i class="bi bi-people-fill"></i> {{ t('admin.dashboard_reset_all_employees_clients') }}</li>
            <li><i class="bi bi-globe"></i> {{ t('admin.dashboard_reset_all_visitors') }}</li>
            <li><i class="bi bi-chat-dots-fill"></i> {{ t('admin.dashboard_reset_all_messages') }}</li>
            <li><i class="bi bi-box-seam-fill"></i> {{ t('admin.dashboard_reset_all_shipments') }}</li>
            <li><i class="bi bi-file-earmark"></i> {{ t('admin.dashboard_reset_all_documents') }}</li>
            <li><i class="bi bi-receipt"></i> {{ t('admin.dashboard_reset_all_quotes') }}</li>
            <li><i class="bi bi-person-rolodex"></i> {{ t('admin.dashboard_reset_all_contacts') }}</li>
          </ul>
          <p class="text-muted small mb-3">{{ t('admin.dashboard_admin_kept') }}</p>
          <div class="mb-3">
            <label class="form-label fw-bold">{{ t('admin.dashboard_secret_key') }}</label>
            <input v-model="resetSecretKey" type="password" class="form-control" :placeholder="t('admin.dashboard_enter_key')" @keyup.enter="executeReset">
          </div>
          <div v-if="resetError" class="alert alert-danger py-2">{{ resetError }}</div>
          <div v-if="resetSuccess" class="alert alert-success py-2">{{ resetSuccess }}</div>
        </div>
        <div class="reset-modal-footer">
          <button class="btn btn-secondary" @click="showResetModal = false">{{ t('common.cancel') }}</button>
          <button class="btn btn-danger" :disabled="!resetSecretKey || resetLoading" @click="executeReset">
            <span v-if="resetLoading" class="spinner-border spinner-border-sm me-1"></span>
            {{ resetLoading ? t('admin.dashboard_deleting') : t('admin.dashboard_delete_all') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { Line, Doughnut } from 'vue-chartjs'
import {
  Chart, LineElement, PointElement, LineController,
  CategoryScale, LinearScale, Tooltip, Legend, Filler,
  ArcElement, DoughnutController
} from 'chart.js'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()

Chart.register(
  LineElement, PointElement, LineController,
  CategoryScale, LinearScale, Tooltip, Legend, Filler,
  ArcElement, DoughnutController
)

const data = ref(null)
const loading = ref(false)
const statsDays = ref(30)

const showResetModal = ref(false)
const resetSecretKey = ref('')
const resetLoading = ref(false)
const resetError = ref('')
const resetSuccess = ref('')

const periods = [
  { label: t('admin.last_7_days'), value: 7 },
  { label: t('admin.last_30_days'), value: 30 },
  { label: t('admin.last_90_days'), value: 90 }
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

const changePeriod = (days) => {
  statsDays.value = days
  loadStats()
}

const loadStats = async () => {
  loading.value = true
  try {
    const since = new Date()
    since.setDate(since.getDate() - statsDays.value)
    const sinceISO = since.toISOString()

    const [allClientsRes, employeesRes, visitorsRes, messagesRes, embarquesRes, cotacoesRes, documentosRes] = await Promise.all([
      supabase.from('users').select('id, created_at, approval_status, name, email, photo').eq('role', 'cliente'),
      supabase.from('users').select('id, created_at, approval_status, name, email, photo').eq('role', 'funcionario'),
      supabase.from('visitors').select('id, visited_at, country').gte('visited_at', sinceISO),
      supabase.from('chat_messages').select('id, created_at, message, is_read, sender_id, users:sender_id(name)'),
      supabase.from('embarques').select('id, created_at'),
      supabase.from('cotacoes').select('id, created_at'),
      supabase.from('documentos').select('id, created_at'),
    ])

    const allClients = allClientsRes.data || []
    const employees = employeesRes.data || []
    const visitors = visitorsRes.data || []
    const messages = messagesRes.data || []
    const embarques = embarquesRes.data || []
    const cotacoes = cotacoesRes.data || []
    const documentos = documentosRes.data || []

    const today = new Date().toISOString().split('T')[0]
    const todayVisitors = visitors.filter(v => v.visited_at && v.visited_at.startsWith(today)).length
    const unreadMessages = messages.filter(m => !m.is_read).length

    const visitorsByDay = {}
    visitors.forEach(v => {
      if (v.visited_at) {
        const day = v.visited_at.slice(0, 10)
        visitorsByDay[day] = (visitorsByDay[day] || 0) + 1
      }
    })

    const messagesByDay = {}
    messages.forEach(m => {
      if (m.created_at) {
        const day = m.created_at.slice(0, 10)
        messagesByDay[day] = (messagesByDay[day] || 0) + 1
      }
    })

    const clientsByDay = {}
    allClients.forEach(c => {
      if (c.created_at) {
        const day = c.created_at.slice(0, 10)
        clientsByDay[day] = (clientsByDay[day] || 0) + 1
      }
    })

    const countryCounts = {}
    visitors.forEach(v => {
      if (v.country) {
        countryCounts[v.country] = (countryCounts[v.country] || 0) + 1
      }
    })

    data.value = {
      clients: {
        total: allClients.length,
        active: allClients.filter(c => c.approval_status === 'approved').length,
        trend: 0
      },
      employees: {
        total: employees.length,
        active: employees.filter(e => e.approval_status === 'approved').length
      },
      visitors: { total: visitors.length, today: todayVisitors },
      messages: { total: messages.length, unread: unreadMessages },
      embarques: embarques.length,
      cotacoes: cotacoes.length,
      documentos: documentos.length,
      recent: {
        clients: [...allClients].sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 5),
        messages: messages.slice(0, 5)
      },
      charts: {
        visitors_by_day: Object.entries(visitorsByDay).map(([d, n]) => ({ d, n: String(n) })),
        messages_by_day: Object.entries(messagesByDay).map(([d, n]) => ({ d, n: String(n) })),
        clients_by_day: Object.entries(clientsByDay).map(([d, n]) => ({ d, n: String(n) })),
        visitors_by_country: Object.entries(countryCounts).map(([country, n]) => ({ country, n: String(n) }))
      }
    }
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
    const tables = ['chat_messages', 'visitors', 'embarques', 'cotacoes', 'documentos', 'contacts']
    for (const table of tables) {
      await supabase.from(table).delete().neq('id', '00000000-0000-0000-0000-000000000000')
    }
    await supabase.from('users').delete().neq('role', 'admin')
    resetSuccess.value = t('admin.dashboard_reset_success')
    setTimeout(() => { window.location.reload() }, 2000)
  } catch (e) {
    resetError.value = e.message || t('admin.dashboard_reset_error')
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

const statusLabel = (s) => ({ pending: t('admin.embarques_pending'), approved: t('admin.approve'), rejected: t('admin.reject') }[s] || s)

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
  if (diff < 60) return t('admin.dashboard_now')
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
        label: t('admin.dashboard_chart_visitors'),
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
        label: t('admin.dashboard_chart_messages'),
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
        label: t('admin.dashboard_chart_new_clients'),
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
  background: #ffffff;
  min-height: 100vh;
  padding: 24px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
}

@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
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
  border: 1px solid #e4e6eb;
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
  border: 1px solid #e4e6eb;
  background: #ffffff;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #65676b;
  font-size: 1.1rem;
  transition: all 0.2s ease;
}

.refresh-btn:hover {
  background: #f0f2f5;
  border-color: #ccd0d5;
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
  border: 1px solid #e4e6eb;
  transition: all 0.2s ease;
  height: 100%;
}

.stat-card:hover {
  border-color: #ccd0d5;
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

.stat-icon-employees {
  background: #f3e8ff;
  color: #8b5cf6;
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
  border: 1px solid #e4e6eb;
  overflow: hidden;
}

.chart-header {
  padding: 16px 20px;
  border-bottom: 1px solid #e4e6eb;
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
  border: 1px solid #e4e6eb;
  overflow: hidden;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e4e6eb;
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
  border-bottom: 1px solid #e4e6eb;
  transition: background 0.2s ease;
}

.list-item:last-child {
  border-bottom: none;
}

.list-item:hover {
  background: #f0f2f5;
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
  background: linear-gradient(90deg, #e4e6eb 25%, #d8dadf 50%, #e4e6eb 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-content {
  flex: 1;
}

.skeleton-line {
  height: 12px;
  border-radius: 6px;
  background: linear-gradient(90deg, #e4e6eb 25%, #d8dadf 50%, #e4e6eb 75%);
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
  -webkit-backdrop-filter: blur(4px);
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
  border-bottom: 1px solid #e4e6eb;
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
  border-top: 1px solid #e4e6eb;
  background: #f0f2f5;
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

@media (max-width: 576px) {
  .stat-card { padding: 1rem; gap: 0.75rem; }
  .stat-icon { width: 42px; height: 42px; border-radius: 10px; font-size: 1.15rem; }
  .stat-label { font-size: 0.72rem; }
  .stat-value { font-size: 1.4rem; }
  .admin-dashboard { padding: 12px; }
  .welcome-title { font-size: 1.1rem; }
}
</style>
