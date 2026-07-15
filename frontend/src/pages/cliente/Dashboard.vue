<template>
  <div class="cliente-dashboard p-4 p-md-5">
    <div class="welcome-card mb-4">
      <h2 class="mb-1">{{ t('cliente.dashboard_greeting') }}, {{ authStore.user?.name }} 👋</h2>
      <p class="text-muted mb-0">{{ t('cliente.dashboard_welcome') }}</p>
    </div>

    <div v-if="companyStore.company" class="card company-card mb-4">
      <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <div class="logo-box">
            <img v-if="companyStore.company.logo" :src="companyStore.company.logo" alt="Logo">
            <span v-else class="text-muted small">{{ t('cliente.dashboard_no_logo') }}</span>
          </div>
          <div class="flex-grow-1">
            <h4 class="mb-1">{{ companyStore.company.company_name }}</h4>
            <p class="mb-1 text-muted"><i class="bi bi-geo-alt"></i> {{ companyStore.company.address }}</p>
            <p class="mb-0">
              <span class="badge bg-primary me-2">{{ companyStore.company.service }}</span>
              <span v-if="companyStore.company.nif" class="text-muted small">{{ t('cliente.dashboard_nif') }}: {{ companyStore.company.nif }}</span>
            </p>
          </div>
          <router-link to="/perfil" class="btn btn-outline-primary">{{ t('cliente.dashboard_edit') }}</router-link>
        </div>
      </div>
    </div>

    <div class="row g-3 g-md-4 mb-4">
      <div class="col-6 col-xl-3">
        <router-link to="/documentos" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-info-soft"><i class="bi bi-file-earmark-text-fill"></i></div>
            <div>
              <div class="stat-tile-label">{{ t('cliente.dashboard_documents') }}</div>
              <div class="stat-tile-value">{{ counts.documentos }}</div>
              <div class="stat-tile-meta">{{ t('cliente.dashboard_files') }}</div>
            </div>
          </div>
        </router-link>
      </div>
      <div class="col-6 col-xl-3">
        <router-link to="/contactos" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-warning-soft"><i class="bi bi-person-rolodex"></i></div>
            <div>
              <div class="stat-tile-label">{{ t('cliente.dashboard_contacts') }}</div>
              <div class="stat-tile-value">{{ counts.contactos }}</div>
              <div class="stat-tile-meta">{{ t('cliente.dashboard_list') }}</div>
            </div>
          </div>
        </router-link>
      </div>
      <div class="col-6 col-xl-3">
        <router-link to="/licenciamentos" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-purple-soft"><i class="bi bi-file-earmark-medical"></i></div>
            <div>
              <div class="stat-tile-label">Licenciamentos</div>
              <div class="stat-tile-value">{{ counts.licenciamentos }}</div>
              <div class="stat-tile-meta">
                <span class="text-success">{{ counts.licenciamentos_aprovado || 0 }} aprovados</span>
              </div>
            </div>
          </div>
        </router-link>
      </div>
      <div class="col-6 col-xl-3">
        <router-link to="/contentores" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-info-soft"><i class="bi bi-box-seam"></i></div>
            <div>
              <div class="stat-tile-label">Contentores</div>
              <div class="stat-tile-value">{{ counts.contentores }}</div>
              <div class="stat-tile-meta">{{ counts.contentores_entregue || 0 }} entregues</div>
            </div>
          </div>
        </router-link>
      </div>
      <div class="col-6 col-xl-3">
        <router-link to="/processos" class="stat-tile-link">
          <div class="stat-tile">
            <div class="stat-tile-icon bg-warning-soft"><i class="bi bi-clipboard2-data"></i></div>
            <div>
              <div class="stat-tile-label">Processos</div>
              <div class="stat-tile-value">{{ counts.processos }}</div>
              <div class="stat-tile-meta">{{ counts.processos_pendente || 0 }} em curso</div>
            </div>
          </div>
        </router-link>
      </div>
    </div>

    <div v-if="licencChartsReady" class="row g-4 mb-4">
      <div class="col-lg-5">
        <div class="card chart-card h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Licenciamentos por Estado</h6>
            <router-link to="/licenciamentos" class="btn btn-sm btn-outline-primary">Ver todos</router-link>
          </div>
          <div class="card-body d-flex align-items-center justify-content-center">
            <Doughnut v-if="licencStatusData.datasets[0].data.some(v => v > 0)" :data="licencStatusData" :options="licencStatusOptions" style="height: 260px; width: 100%" />
            <div v-else class="text-center text-muted py-5">
              <i class="bi bi-inbox" style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem; opacity: 0.4;"></i>
              <p class="mb-0 small">Sem licenciamentos registados</p>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="card chart-card">
          <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-bar-chart-line-fill me-2"></i>Licenciamentos por Mês</h6>
          </div>
          <div class="card-body">
            <Bar v-if="licencMonthlyData.datasets[0].data.some(v => v > 0)" :data="licencMonthlyData" :options="licencMonthlyOptions" style="height: 260px" />
            <div v-else class="text-center text-muted py-5">
              <i class="bi bi-inbox" style="font-size: 2.5rem; display: block; margin-bottom: 0.5rem; opacity: 0.4;"></i>
              <p class="mb-0 small">Sem dados para exibir</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-12">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="mb-0">{{ t('cliente.dashboard_quick_access') }}</h5>
          </div>
          <div class="card-body">
            <div class="quick-actions">
              <button class="quick-action" @click="goToDocumentos">
                <i class="bi bi-upload"></i>
                <span>{{ t('cliente.dashboard_send_doc') }}</span>
              </button>
              <router-link to="/mensagens" class="quick-action">
                <i class="bi bi-chat-dots-fill"></i>
                <span>{{ t('cliente.dashboard_talk_team') }}</span>
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
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'
import { useCompanyStore } from '@/stores/companyStore'
import { Bar, Doughnut } from 'vue-chartjs'
import {
  Chart, BarElement, CategoryScale, LinearScale, Tooltip, Legend,
  ArcElement, DoughnutController, BarController
} from 'chart.js'
import { useI18n } from '@/composables/useI18n'

const { t, locale } = useI18n()

Chart.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend, ArcElement, DoughnutController, BarController)

const authStore = useAuthStore()
const companyStore = useCompanyStore()
const router = useRouter()

const counts = reactive({ documentos: 0, contactos: 0, licenciamentos: 0, licenciamentos_aprovado: 0, contentores: 0, contentores_entregue: 0, processos: 0, processos_pendente: 0 })
const licenciamentos = ref([])

const goToDocumentos = () => router.push('/documentos')

const loadCounts = async () => {
  try {
    const userId = authStore.user?.id
    if (!userId) return

    const [docsRes, contsRes, licRes, contRes, procRes] = await Promise.all([
      supabase.from('documentos').select('*').eq('user_id', userId),
      supabase.from('contactos').select('*').eq('user_id', userId),
      supabase.from('licenciamentos').select('*').eq('user_id', userId),
      supabase.from('contentores').select('*').eq('cliente_id', userId),
      supabase.from('processos').select('*').eq('cliente_id', userId)
    ])

    licenciamentos.value = licRes.data || []
    counts.documentos = (docsRes.data || []).length
    counts.contactos = (contsRes.data || []).length
    counts.licenciamentos = licenciamentos.value.length
    counts.licenciamentos_aprovado = licenciamentos.value.filter(l => l.estado === 'aprovado').length
    counts.contentores = (contRes.data || []).length
    counts.contentores_entregue = (contRes.data || []).filter(c => c.estado === 'entregue').length
    counts.processos = (procRes.data || []).length
    counts.processos_pendente = (procRes.data || []).filter(p => !['dar_saida_pronto', 'ep17_pago_comp_ok'].includes(p.estado)).length
  } catch (e) {
    console.error(e)
  }
}

const licencChartsReady = computed(() => licenciamentos.value.length > 0)

const monthKey = (iso) => {
  if (!iso) return null
  const d = new Date(iso)
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
}

const licencEstadoLabel = (s) => ({
  pendente: 'Pendente',
  documentacao_recebida: 'Doc. Recebida',
  submetido: 'Submetido',
  em_analise: 'Em Análise',
  aprovado: 'Aprovado',
  indeferido: 'Indeferido',
  resubmetido: 'Re-Submetido',
  certificacao_solicitada: 'Certif. Solicitada'
}[s] || s || '')

const licencEstadoColor = (s) => ({
  pendente: '#e74c3c',
  documentacao_recebida: '#3498db',
  submetido: '#9b59b6',
  em_analise: '#e67e22',
  aprovado: '#2ecc71',
  indeferido: '#c0392b',
  resubmetido: '#e91e63',
  certificacao_solicitada: '#00bcd4'
}[s] || '#95a5a6')

const licencStatusData = computed(() => {
  const groups = {}
  licenciamentos.value.forEach(l => {
    const e = l.estado || 'pendente'
    groups[e] = (groups[e] || 0) + 1
  })
  const keys = Object.keys(groups).sort((a, b) => groups[b] - groups[a])
  return {
    labels: keys.map(licencEstadoLabel),
    datasets: [{
      data: keys.map(k => groups[k]),
      backgroundColor: keys.map(licencEstadoColor),
      borderWidth: 2,
      borderColor: '#ffffff',
      hoverOffset: 6
    }]
  }
})

const licencStatusOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '55%',
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        boxWidth: 12,
        padding: 12,
        font: { size: 11 },
        usePointStyle: true,
        pointStyleWidth: 10
      }
    }
  },
  layout: { padding: { bottom: 10 } }
}

const licencMonthlyData = computed(() => {
  const labels = []
  const mapEmb = new Map()
  const mapCot = new Map()
  const now = new Date()
  for (let i = 5; i >= 0; i--) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    const k = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
    labels.push(d.toLocaleDateString(locale.value === 'pt' ? 'pt-PT' : locale.value === 'en' ? 'en-US' : 'fr-FR', { month: 'short' }))
    mapEmb.set(k, 0)
    mapCot.set(k, 0)
  }
  licenciamentos.value.forEach(l => {
    const k = monthKey(l.created_at)
    if (mapEmb.has(k)) mapEmb.set(k, mapEmb.get(k) + 1)
  })
  return {
    labels,
    datasets: [
      { label: 'Licenciamentos', data: [...mapEmb.values()], backgroundColor: '#8b5cf6', borderRadius: 6, barPercentage: 0.6 },
    ],
  }
})

const licencMonthlyOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: { backgroundColor: '#1e293b', titleFont: { size: 12 }, bodyFont: { size: 11 }, cornerRadius: 8, padding: 10 }
  },
  scales: {
    y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } }, grid: { color: '#f1f5f9' } },
    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
  },
}

onMounted(async () => {
  if (!companyStore.company) await companyStore.fetch()
  await Promise.all([loadCounts()])
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
.bg-purple-soft { background: #ede9fe; color: #6d28d9; }

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

@media (max-width: 768px) {
  .cliente-dashboard { padding: 0.75rem; }
  .welcome-card { padding: 0.85rem; }
  .welcome-card h2 { font-size: 1.05rem; }
  .stat-tile { padding: 0.85rem; gap: 0.55rem; }
  .stat-tile-icon { width: 38px; height: 38px; border-radius: 10px; font-size: 1.05rem; }
  .stat-tile-label { font-size: 0.65rem; letter-spacing: 0.3px; }
  .stat-tile-value { font-size: 1.2rem; }
  .stat-tile-meta { font-size: 0.6rem; }
  .card { border-radius: 10px; }
  .card-header { padding: 0.75rem 1rem; }
  .quick-actions { gap: 0.5rem; }
  .quick-action { padding: 0.85rem 0.6rem; font-size: 0.75rem; gap: 0.35rem; }
  .quick-action i { font-size: 1.15rem; }
  .recent-list li { padding: 0.5rem 0; gap: 0.35rem; }
  .logo-box { width: 44px; height: 44px; }
}

@media (max-width: 480px) {
  .cliente-dashboard { padding: 0.5rem; }
  .welcome-card { padding: 0.7rem; }
  .welcome-card h2 { font-size: 0.95rem; }
  .stat-tile { padding: 0.7rem; gap: 0.45rem; }
  .stat-tile-icon { width: 32px; height: 32px; border-radius: 8px; font-size: 0.9rem; }
  .stat-tile-label { font-size: 0.58rem; }
  .stat-tile-value { font-size: 1rem; }
  .quick-actions { grid-template-columns: 1fr; gap: 0.4rem; }
  .quick-action { padding: 0.7rem 0.5rem; font-size: 0.7rem; }
}
</style>
