<template>
  <div class="logistica-dashboard p-4 p-md-5">
    <div class="welcome-card mb-4">
      <h2 class="mb-1">Painel de Logística</h2>
      <p class="text-muted mb-0">Visão geral das operações de transporte e entregas</p>
    </div>

    <div class="row g-3 mb-4 stat-row">
      <div class="col-6 col-md-4 col-xl-2" v-for="stat in statCards" :key="stat.label">
        <div class="stat-tile">
          <div class="stat-tile-icon" :class="stat.iconClass"><i :class="stat.icon"></i></div>
          <div>
            <div class="stat-tile-label">{{ stat.label }}</div>
            <div class="stat-tile-value">{{ stat.value }}</div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="chartsReady" class="row g-4 mb-4">
      <div class="col-lg-7">
        <div class="card chart-card">
          <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Entregas por Mês</h6>
          </div>
          <div class="card-body">
            <Bar :data="monthlyData" :options="barOptions" style="height: 280px" />
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card chart-card h-100">
          <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Entregas por Estado</h6>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center">
            <Doughnut v-if="estadoData.datasets[0].data.some(v => v > 0)" :data="estadoData" :options="doughnutOptions" style="height: 280px" />
            <div v-else class="text-center text-muted py-5">
              <i class="bi bi-inbox"></i>
              <p class="mb-0 mt-2 small">Sem dados disponíveis</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="chartsReady" class="row g-4 mb-4">
      <div class="col-lg-6">
        <div class="card chart-card h-100">
          <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-people-fill me-2"></i>Top Clientes</h6>
          </div>
          <div class="card-body">
            <Bar v-if="topClientesData.datasets[0].data.length" :data="topClientesData" :options="barHorizontalOptions" style="height: 280px" />
            <div v-else class="text-center text-muted py-5">
              <i class="bi bi-inbox"></i>
              <p class="mb-0 mt-2 small">Sem dados disponíveis</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card chart-card h-100">
          <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-person-badge-fill me-2"></i>Top Motoristas</h6>
          </div>
          <div class="card-body">
            <Bar v-if="topMotoristasData.datasets[0].data.length" :data="topMotoristasData" :options="barHorizontalOptions" style="height: 280px" />
            <div v-else class="text-center text-muted py-5">
              <i class="bi bi-inbox"></i>
              <p class="mb-0 mt-2 small">Sem dados disponíveis</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="chartsReady" class="row g-4">
      <div class="col-lg-6">
        <div class="card chart-card h-100">
          <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-truck me-2"></i>Top Camiões</h6>
          </div>
          <div class="card-body">
            <Bar v-if="topCamioesData.datasets[0].data.length" :data="topCamioesData" :options="barHorizontalOptions" style="height: 280px" />
            <div v-else class="text-center text-muted py-5">
              <i class="bi bi-inbox"></i>
              <p class="mb-0 mt-2 small">Sem dados disponíveis</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card chart-card h-100">
          <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Contentores por Cliente</h6>
          </div>
          <div class="card-body">
            <Bar v-if="contentoresClienteData.datasets[0].data.length" :data="contentoresClienteData" :options="barHorizontalOptions" style="height: 280px" />
            <div v-else class="text-center text-muted py-5">
              <i class="bi bi-inbox"></i>
              <p class="mb-0 mt-2 small">Sem dados disponíveis</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, reactive, computed } from 'vue'
import { Bar, Doughnut } from 'vue-chartjs'
import {
  Chart, BarElement, CategoryScale, LinearScale, Tooltip, Legend,
  ArcElement, DoughnutController, BarController
} from 'chart.js'

Chart.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend, ArcElement, DoughnutController, BarController)

const stats = reactive({
  total: 0,
  total_contentores: 0,
  pendentes: 0,
  em_transito: 0,
  entregues: 0,
  cancelados: 0,
  by_estado: {},
  top_clientes: [],
  top_motoristas: [],
  top_camioes: [],
  contentores_por_cliente: []
})

const entregas = ref([])
const chartsReady = computed(() => entregas.value.length > 0)

const getAuthHeaders = () => {
  const token = JSON.parse(localStorage.getItem('sb-vsupwqxtnzdnxklgbynn-auth-token') || '{}').access_token
  return token ? { Authorization: `Bearer ${token}` } : {}
}

const statCards = computed(() => [
  { label: 'Total Entregas', value: stats.total, icon: 'bi bi-truck', iconClass: 'bg-primary-soft' },
  { label: 'Total Contentores', value: stats.total_contentores, icon: 'bi bi-box-seam', iconClass: 'bg-info-soft' },
  { label: 'Entregas Pendentes', value: stats.pendentes, icon: 'bi bi-clock', iconClass: 'bg-warning-soft' },
  { label: 'Em Transporte', value: stats.em_transito, icon: 'bi bi-arrow-repeat', iconClass: 'bg-purple-soft' },
  { label: 'Entregues', value: stats.entregues, icon: 'bi bi-check-circle', iconClass: 'bg-success-soft' },
  { label: 'Cancelados', value: stats.cancelados, icon: 'bi bi-x-circle', iconClass: 'bg-danger-soft' }
])

const monthKey = (iso) => {
  if (!iso) return null
  const d = new Date(iso)
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
}

const monthlyData = computed(() => {
  const labels = []
  const dataMap = new Map()
  const now = new Date()
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    const k = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
    labels.push(d.toLocaleDateString('pt-PT', { month: 'short' }))
    dataMap.set(k, 0)
  }
  entregas.value.forEach(e => {
    const k = monthKey(e.created_at)
    if (dataMap.has(k)) dataMap.set(k, dataMap.get(k) + 1)
  })
  return {
    labels,
    datasets: [
      { label: 'Entregas', data: [...dataMap.values()], backgroundColor: '#2563eb', borderRadius: 6 }
    ]
  }
})

const estadoData = computed(() => {
  const labels = ['Pendente', 'Em Trânsito', 'Entregue', 'Cancelado']
  const colors = ['#f59e0b', '#06b6d4', '#10b981', '#ef4444']
  const data = [
    stats.pendentes,
    stats.em_transito,
    stats.entregues,
    stats.cancelados
  ]
  return {
    labels,
    datasets: [{ data, backgroundColor: colors, borderWidth: 0 }]
  }
})

const topClientesData = computed(() => {
  const items = (stats.top_clientes || []).slice(0, 8)
  return {
    labels: items.map(i => i.cliente || i.nome || 'Desconhecido'),
    datasets: [{ label: 'Entregas', data: items.map(i => i.total || i.count || 0), backgroundColor: '#2563eb', borderRadius: 6, barPercentage: 0.6 }]
  }
})

const topMotoristasData = computed(() => {
  const items = (stats.top_motoristas || []).slice(0, 8)
  return {
    labels: items.map(i => i.motorista || i.nome || 'Desconhecido'),
    datasets: [{ label: 'Entregas', data: items.map(i => i.total || i.count || 0), backgroundColor: '#8b5cf6', borderRadius: 6, barPercentage: 0.6 }]
  }
})

const topCamioesData = computed(() => {
  const items = (stats.top_camioes || []).slice(0, 8)
  return {
    labels: items.map(i => i.camiao || i.matricula || i.nome || '—'),
    datasets: [{ label: 'Viagens', data: items.map(i => i.total || i.count || 0), backgroundColor: '#f97316', borderRadius: 6, barPercentage: 0.6 }]
  }
})

const contentoresClienteData = computed(() => {
  const items = (stats.contentores_por_cliente || []).slice(0, 8)
  return {
    labels: items.map(i => i.cliente || i.nome || 'Desconhecido'),
    datasets: [{ label: 'Contentores', data: items.map(i => i.total || i.count || 0), backgroundColor: '#06b6d4', borderRadius: 6, barPercentage: 0.6 }]
  }
})

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'top' } },
  scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } }
}

const barHorizontalOptions = {
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y',
  plugins: { legend: { display: false } },
  scales: { x: { beginAtZero: true, ticks: { precision: 0 } }, y: { grid: { display: false } } }
}

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '60%',
  plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } }
}

onMounted(async () => {
  try {
    const headers = getAuthHeaders()
    const apiUrl = import.meta.env.VITE_API_URL

    const [statsRes, entregasRes] = await Promise.all([
      fetch(`${apiUrl}/entregas/stats`, { headers }),
      fetch(`${apiUrl}/entregas`, { headers })
    ])

    if (statsRes.ok) {
      const data = await statsRes.json()
      stats.total = data.total || 0
      stats.total_contentores = data.total_contentores || 0
      stats.by_estado = data.by_estado || {}
      stats.pendentes = stats.by_estado.pendente || 0
      stats.em_transito = stats.by_estado.em_transito || 0
      stats.entregues = stats.by_estado.entregue || 0
      stats.cancelados = stats.by_estado.cancelado || 0
      stats.top_clientes = data.top_clientes || []
      stats.top_motoristas = data.top_motoristas || []
      stats.top_camioes = data.top_camioes || []
      stats.contentores_por_cliente = data.contentores_por_cliente || []
    }

    if (entregasRes.ok) {
      const data = await entregasRes.json()
      entregas.value = data.data || data || []
    }
  } catch (e) {
    console.error('Erro ao carregar dados do painel de logística:', e)
  }
})
</script>

<style scoped>
.logistica-dashboard {
  background: #f5f7fa;
  min-height: 100vh;
}

.welcome-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.stat-row .col-6 {
  flex: 0 0 auto;
  width: auto;
}

.stat-tile {
  background: white;
  padding: 1.25rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  display: flex;
  align-items: center;
  gap: 0.85rem;
  height: 100%;
  transition: all 0.2s ease;
}

.stat-tile:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
}

.stat-tile-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  flex-shrink: 0;
}

.bg-primary-soft { background: #dbeafe; color: #1d4ed8; }
.bg-info-soft { background: #cffafe; color: #0e7490; }
.bg-warning-soft { background: #fef3c7; color: #b45309; }
.bg-purple-soft { background: #ede9fe; color: #6d28d9; }
.bg-success-soft { background: #d1fae5; color: #047857; }
.bg-danger-soft { background: #fee2e2; color: #dc2626; }

.stat-tile-label { color: #64748b; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stat-tile-value { font-size: 1.75rem; font-weight: 700; color: #0f172a; line-height: 1.2; }

.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); }
.card-header { background: white; border-bottom: 1px solid #eef0f3; padding: 1rem 1.25rem; }
.chart-card .card-header h6 { font-weight: 700; color: #0f172a; font-size: 0.9rem; }

@media (max-width: 576px) {
  .logistica-dashboard { padding: 1rem; }
  .welcome-card { padding: 1rem; }
  .welcome-card h2 { font-size: 1.1rem; }
  .stat-tile { padding: 1rem; gap: 0.65rem; }
  .stat-tile-icon { width: 42px; height: 42px; border-radius: 10px; font-size: 1.15rem; }
  .stat-tile-label { font-size: 0.7rem; }
  .stat-tile-value { font-size: 1.3rem; }
  .card { border-radius: 10px; }
}
</style>
