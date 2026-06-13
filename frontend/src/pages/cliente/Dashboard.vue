<template>
  <div class="cliente-dashboard p-4 p-md-5">
    <div class="welcome-card mb-4">
      <h2 class="mb-1">Olá, {{ authStore.user?.name }} 👋</h2>
      <p class="text-muted mb-0">Bem-vindo ao seu dashboard. Aqui pode gerir os embarques, cotações, documentos e contactos.</p>
    </div>

    <div v-if="companyStore.company" class="card company-card mb-4">
      <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <div class="logo-box">
            <img v-if="companyStore.company.logo" :src="companyStore.company.logo" alt="Logo">
            <span v-else class="text-muted small">Sem logo</span>
          </div>
          <div class="flex-grow-1">
            <h4 class="mb-1">{{ companyStore.company.company_name }}</h4>
            <p class="mb-1 text-muted"><i class="bi bi-geo-alt"></i> {{ companyStore.company.address }}</p>
            <p class="mb-0">
              <span class="badge bg-primary me-2">{{ companyStore.company.service }}</span>
              <span v-if="companyStore.company.nif" class="text-muted small">NIF: {{ companyStore.company.nif }}</span>
            </p>
          </div>
          <router-link to="/perfil" class="btn btn-outline-primary">Editar dados</router-link>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-6 col-xl-3">
        <router-link to="/embarques" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-primary-soft"><i class="bi bi-box-seam-fill"></i></div>
            <div>
              <div class="stat-tile-label">Embarques</div>
              <div class="stat-tile-value">{{ counts.embarques }}</div>
              <div class="stat-tile-meta">
                <span class="text-warning">{{ counts.embarques_pendente || 0 }} pendentes</span>
              </div>
            </div>
          </div>
        </router-link>
      </div>
      <div class="col-md-6 col-xl-3">
        <router-link to="/cotacoes" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-success-soft"><i class="bi bi-receipt"></i></div>
            <div>
              <div class="stat-tile-label">Cotações</div>
              <div class="stat-tile-value">{{ counts.cotacoes }}</div>
              <div class="stat-tile-meta">
                <span class="text-success">{{ counts.cotacoes_aprovada || 0 }} aprovadas</span>
              </div>
            </div>
          </div>
        </router-link>
      </div>
      <div class="col-md-6 col-xl-3">
        <router-link to="/documentos" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-info-soft"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div>
              <div class="stat-tile-label">Documentos</div>
              <div class="stat-tile-value">{{ counts.documentos }}</div>
              <div class="stat-tile-meta">arquivos carregados</div>
            </div>
          </div>
        </router-link>
      </div>
      <div class="col-md-6 col-xl-3">
        <router-link to="/contactos" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-warning-soft"><i class="bi bi-person-rolodex"></i></div>
            <div>
              <div class="stat-tile-label">Contactos</div>
              <div class="stat-tile-value">{{ counts.contactos }}</div>
              <div class="stat-tile-meta">na sua lista</div>
            </div>
          </div>
        </router-link>
      </div>
    </div>

    <div v-if="chartsReady" class="row g-4 mb-4">
      <div class="col-lg-7">
        <div class="card chart-card">
          <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Embarques vs Cotações (últimos 6 meses)</h6>
          </div>
          <div class="card-body">
            <Bar :data="monthlyData" :options="monthlyOptions" style="height: 280px" />
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card chart-card h-100">
          <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Estado dos embarques</h6>
          </div>
          <div class="card-body">
            <Doughnut v-if="statusData.datasets[0].data.some(v => v > 0)" :data="statusData" :options="statusOptions" style="height: 280px" />
            <div v-else class="text-center text-muted py-5">
              <i class="bi bi-inbox"></i>
              <p class="mb-0 mt-2 small">Sem embarques para mostrar.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Embarques recentes</h5>
            <router-link to="/embarques" class="btn btn-sm btn-outline-primary">Ver todos</router-link>
          </div>
          <div class="card-body">
            <div v-if="recentEmbarques.length === 0" class="empty-mini">
              <i class="bi bi-inbox"></i>
              <p class="mb-0">Sem embarques ainda.</p>
            </div>
            <ul v-else class="recent-list">
              <li v-for="e in recentEmbarques" :key="e.id">
                <code class="tracking-code">{{ e.tracking_number }}</code>
                <div class="route-mini">
                  <span>{{ e.origin }}</span>
                  <i class="bi bi-arrow-right text-muted mx-1"></i>
                  <span>{{ e.destination }}</span>
                </div>
                <span class="status-badge" :class="`status-${e.status}`">{{ statusLabel(e.status) }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="mb-0">Acesso rápido</h5>
          </div>
          <div class="card-body">
            <div class="quick-actions">
              <router-link to="/embarques/novo" class="quick-action">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Novo embarque</span>
              </router-link>
              <router-link to="/cotacoes/novo" class="quick-action">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Nova cotação</span>
              </router-link>
              <button class="quick-action" @click="goToDocumentos">
                <i class="bi bi-upload"></i>
                <span>Enviar documento</span>
              </button>
              <router-link to="/mensagens" class="quick-action">
                <i class="bi bi-chat-dots-fill"></i>
                <span>Falar com a equipa</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'
import { useCompanyStore } from '@/stores/companyStore'
import { Bar, Doughnut } from 'vue-chartjs'
import {
  Chart, BarElement, CategoryScale, LinearScale, Tooltip, Legend,
  ArcElement, DoughnutController, BarController
} from 'chart.js'

Chart.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend, ArcElement, DoughnutController, BarController)

const authStore = useAuthStore()
const companyStore = useCompanyStore()
const router = useRouter()

const counts = reactive({ embarques: 0, embarques_pendente: 0, cotacoes: 0, cotacoes_aprovada: 0, documentos: 0, contactos: 0 })
const recentEmbarques = ref([])
const embarques = ref([])
const cotacoes = ref([])

const authHeader = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } })

const statusLabel = (s) => ({ pendente: 'Pendente', em_transito: 'Em trânsito', entregue: 'Entregue', cancelado: 'Cancelado' }[s] || s)

const goToDocumentos = () => router.push('/documentos')

const loadCounts = async () => {
  try {
    const [emb, cot, docs, conts] = await Promise.all([
      axios.get('/api/embarques', authHeader()),
      axios.get('/api/cotacoes', authHeader()),
      axios.get('/api/documentos', authHeader()),
      axios.get('/api/contactos', authHeader())
    ])
    embarques.value = emb.data.data.embarques
    cotacoes.value = cot.data.data.cotacoes
    counts.embarques = embarques.value.length
    counts.embarques_pendente = embarques.value.filter(e => e.status === 'pendente').length
    counts.cotacoes = cotacoes.value.length
    counts.cotacoes_aprovada = cotacoes.value.filter(c => c.status === 'aprovada').length
    counts.documentos = docs.data.data.documentos.length
    counts.contactos = conts.data.data.contactos.length
    recentEmbarques.value = embarques.value.slice(0, 5)
  } catch (e) {
    console.error(e)
  }
}

const chartsReady = computed(() => embarques.value.length > 0 || cotacoes.value.length > 0)

const monthKey = (iso) => {
  if (!iso) return null
  const d = new Date(iso)
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
}

const monthlyData = computed(() => {
  const labels = []
  const mapEmb = new Map()
  const mapCot = new Map()
  const now = new Date()
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    const k = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
    labels.push(d.toLocaleDateString('pt-PT', { month: 'short' }))
    mapEmb.set(k, 0)
    mapCot.set(k, 0)
  }
  embarques.value.forEach(e => { const k = monthKey(e.created_at); if (mapEmb.has(k)) mapEmb.set(k, mapEmb.get(k) + 1) })
  cotacoes.value.forEach(c => { const k = monthKey(c.created_at); if (mapCot.has(k)) mapCot.set(k, mapCot.get(k) + 1) })
  return {
    labels,
    datasets: [
      { label: 'Embarques', data: [...mapEmb.values()], backgroundColor: '#2563eb', borderRadius: 6 },
      { label: 'Cotações', data: [...mapCot.values()], backgroundColor: '#10b981', borderRadius: 6 },
    ],
  }
})

const monthlyOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { position: 'top' } },
  scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } },
}

const statusData = computed(() => {
  const groups = { pendente: 0, em_transito: 0, entregue: 0, cancelado: 0 }
  embarques.value.forEach(e => { if (groups[e.status] !== undefined) groups[e.status]++ })
  return {
    labels: ['Pendente', 'Em trânsito', 'Entregue', 'Cancelado'],
    datasets: [{
      data: [groups.pendente, groups.em_transito, groups.entregue, groups.cancelado],
      backgroundColor: ['#f59e0b', '#06b6d4', '#10b981', '#ef4444'],
      borderWidth: 0,
    }],
  }
})

const statusOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } },
  cutout: '60%',
}

onMounted(async () => {
  if (!companyStore.company) await companyStore.fetch()
  await loadCounts()
})
</script>

<style scoped>
.cliente-dashboard {
  background: #f5f7fa;
  min-height: 100vh;
}

.welcome-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.company-card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.logo-box {
  width: 70px;
  height: 70px;
  background: #f8f9fa;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.logo-box img {
  width: 100%;
  height: 100%;
  object-fit: contain;
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
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
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
.chart-card .card-header h6 { font-weight: 700; color: #0f172a; font-size: 0.9rem; }

.recent-list { list-style: none; padding: 0; margin: 0; }
.recent-list li {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.85rem 0;
  border-bottom: 1px solid #f1f5f9;
  flex-wrap: wrap;
}
.recent-list li:last-child { border-bottom: none; }
.tracking-code { background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }
.route-mini { display: flex; align-items: center; flex: 1; font-size: 0.9rem; }
.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.status-pendente { background: #fef3c7; color: #92400e; }
.status-em_transito { background: #cffafe; color: #155e75; }
.status-entregue { background: #d1fae5; color: #065f46; }
.status-cancelado { background: #fee2e2; color: #991b1b; }

.quick-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
.quick-action {
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  padding: 1.25rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: #334155;
  font-weight: 500;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
}
.quick-action:hover {
  background: #2563eb;
  border-color: #2563eb;
  color: white;
  transform: translateY(-2px);
}
.quick-action i { font-size: 1.5rem; }

.empty-mini { text-align: center; color: #94a3b8; padding: 2rem 1rem; }
.empty-mini i { font-size: 2rem; display: block; margin-bottom: 0.5rem; }
</style>
