<template>
  <div class="crud-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Licenciamentos</h1>
        <p class="text-muted mb-0">Acompanhe os seus processos de licenciamento</p>
      </div>
    </div>

    <div class="stats-grid mb-4">
      <div class="stat-card">
        <div class="stat-icon bg-primary-soft"><i class="bi bi-file-earmark-medical"></i></div>
        <div>
          <div class="stat-label">Total</div>
          <div class="stat-value">{{ stats.total || 0 }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-warning-soft"><i class="bi bi-hourglass-split"></i></div>
        <div>
          <div class="stat-label">Pendente</div>
          <div class="stat-value">{{ stats.pendente || 0 }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-info-soft"><i class="bi bi-search"></i></div>
        <div>
          <div class="stat-label">Em Análise</div>
          <div class="stat-value">{{ stats.em_analise || 0 }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-success-soft"><i class="bi bi-check-circle"></i></div>
        <div>
          <div class="stat-label">Aprovado</div>
          <div class="stat-value">{{ stats.aprovado || 0 }}</div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <div class="filters">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" placeholder="Pesquisar por referência, processo ou empresa..." @input="debounceSearch">
          </div>
          <select v-model="filters.status" class="form-select" @change="cardsPage = 1; fetchData()">
            <option value="">Todos os estados</option>
            <option value="pendente">Pendente</option>
            <option value="documentacao_recebida">Documentação Recebida</option>
            <option value="submetido">Submetido</option>
            <option value="em_analise">Em Análise</option>
            <option value="aprovado">Aprovado</option>
            <option value="indeferido">Indeferido</option>
            <option value="resubmetido">Re-Submetido</option>
            <option value="certificacao_solicitada">Certificação - Solicitada</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div v-else-if="items.length === 0" class="empty-state">
      <i class="bi bi-inbox"></i>
      <p>Nenhum licenciamento encontrado.</p>
    </div>

    <div v-else class="licenciamentos-grid">
      <div
        v-for="item in paginatedItems"
        :key="item.id"
        class="licenciamento-card"
        @click="router.push(`/licenciamentos/${item.id}`)"
      >
        <div class="licenciamento-card-header">
          <code class="ref-code">{{ item.referencia }}</code>
          <span class="status-badge" :class="`status-${item.estado}`">{{ statusLabel(item.estado) }}</span>
        </div>
        <div class="licenciamento-card-body">
          <div class="licenciamento-info">
            <div class="info-row">
              <i class="bi bi-hash"></i>
              <span class="info-label">Processo</span>
              <span class="info-value">{{ item.numero_processo || '' }}</span>
            </div>
            <div class="info-row">
              <i class="bi bi-building"></i>
              <span class="info-label">Cliente</span>
              <span class="info-value">{{ item.empresa || '' }}</span>
            </div>
            <div class="info-row">
              <i class="bi bi-tag"></i>
              <span class="info-label">Tipo</span>
              <span class="info-value">{{ tipoLabel(item.tipo) }}</span>
            </div>
          </div>
        </div>
        <div class="licenciamento-card-footer">
          <span class="date-text"><i class="bi bi-calendar3 me-1"></i>{{ formatDate(item.created_at) }}</span>
          <i class="bi bi-chevron-right arrow-icon"></i>
        </div>
      </div>
    </div>

    <div v-if="!loading && items.length > cardsPerPage" class="pagination-controls d-flex justify-content-center align-items-center gap-3 mt-4">
      <button class="btn btn-outline-secondary btn-sm" :disabled="cardsPage === 1" @click="cardsPage--">
        <i class="bi bi-chevron-left me-1"></i> Anterior
      </button>
      <span class="text-muted small">{{ cardsPage }} / {{ totalCardsPages }}</span>
      <button class="btn btn-outline-primary btn-sm" :disabled="cardsPage >= totalCardsPages" @click="cardsPage++">
        Próximo <i class="bi bi-chevron-right ms-1"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'
import { useI18n } from '@/composables/useI18n'

const { locale } = useI18n()
const authStore = useAuthStore()
const router = useRouter()
const items = ref([])
const stats = ref({})
const loading = ref(false)
const filters = reactive({ q: '', status: '' })
let searchTimer = null

const cardsPage = ref(1)
const cardsPerPage = 6

const totalCardsPages = computed(() => Math.ceil(items.value.length / cardsPerPage))

const paginatedItems = computed(() => {
  const start = (cardsPage.value - 1) * cardsPerPage
  return items.value.slice(start, start + cardsPerPage)
})

const computeStats = () => {
  const all = items.value
  stats.value = {
    total: all.length,
    pendente: all.filter(e => e.estado === 'pendente').length,
    em_analise: all.filter(e => e.estado === 'em_analise').length,
    aprovado: all.filter(e => e.estado === 'aprovado').length,
    indeferido: all.filter(e => e.estado === 'indeferido').length,
  }
}

const fetchData = async () => {
  loading.value = true
  try {
    const userId = authStore.user?.id
    if (!userId) return

    const userName = (authStore.user?.full_name || authStore.user?.name || '').trim().toLowerCase()

    let query = supabase.from('licenciamentos').select('*').eq('user_id', userId)
    if (filters.status) query = query.eq('estado', filters.status)
    if (filters.q) {
      query = query.or(`referencia.ilike.%${filters.q}%,numero_processo.ilike.%${filters.q}%,empresa.ilike.%${filters.q}%`)
    }
    const { data: byUserId, error: err1 } = await query

    let extra = []
    if (userName && userName.length >= 3) {
      let nameQuery = supabase.from('licenciamentos').select('*').neq('user_id', userId).ilike('empresa', `%${userName}%`)
      if (filters.status) nameQuery = nameQuery.eq('estado', filters.status)
      const { data: byName } = await nameQuery
      extra = byName || []
    }

    if (!err1) {
      const all = [...(byUserId || []), ...extra]
      const unique = [...new Map(all.map(item => [item.id, item])).values()]
      items.value = unique
      computeStats()
    }
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { cardsPage.value = 1; fetchData() }, 300)
}

const statusLabel = (status) => ({
  pendente: 'Pendente',
  documentacao_recebida: 'Documentação Recebida',
  submetido: 'Submetido',
  em_analise: 'Em Análise',
  aprovado: 'Aprovado',
  indeferido: 'Indeferido',
  resubmetido: 'Re-Submetido',
  certificacao_solicitada: 'Certificação - Solicitada'
}[status] || status)

const tipoLabel = (tipo) => ({
  importacao: 'Importação',
  exportacao: 'Exportação',
  trânsito: 'Trânsito',
  tranzito: 'Trânsito',
  licenca_especial: 'Licença Especial',
  outro: 'Outro'
}[tipo] || tipo || '')

const formatDate = (d) => d ? new Date(d).toLocaleDateString(locale.value === 'pt' ? 'pt-PT' : locale.value === 'en' ? 'en-US' : 'fr-FR') : ''

onMounted(fetchData)
</script>

<style scoped>
.crud-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.page-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.stat-card { background: white; border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.bg-primary-soft { background: #dbeafe; color: #1d4ed8; }
.bg-warning-soft { background: #fef3c7; color: #b45309; }
.bg-info-soft { background: #cffafe; color: #0e7490; }
.bg-success-soft { background: #d1fae5; color: #047857; }
.stat-label { color: #64748b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #0f172a; }

.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-header { background: white; border-bottom: 1px solid #f1f5f9; padding: 1rem 1.25rem; }
.filters { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.search-box { position: relative; flex: 1; min-width: 240px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input { width: 100%; padding: 0.6rem 0.75rem 0.6rem 2.25rem; border: 2px solid #e2e8f0; border-radius: 8px; }
.search-box input:focus { border-color: #2563eb; outline: none; }
.filters .form-select { max-width: 220px; border: 2px solid #e2e8f0; border-radius: 8px; }

.empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }

.licenciamentos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1rem; }

.licenciamento-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  cursor: pointer;
  transition: all 0.2s ease;
  overflow: hidden;
}
.licenciamento-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.08); }

.licenciamento-card-header {
  padding: 1rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f1f5f9;
}

.ref-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem; color: #334155; font-weight: 600; }

.status-badge { display: inline-block; padding: 0.25rem 0.65rem; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.status-pendente { background: #fef3c7; color: #92400e; }
.status-documentacao_recebida { background: #e0e7ff; color: #4338ca; }
.status-submetido { background: #cffafe; color: #155e75; }
.status-em_analise { background: #dbeafe; color: #1d4ed8; }
.status-aprovado { background: #d1fae5; color: #065f46; }
.status-indeferido { background: #fee2e2; color: #991b1b; }

.licenciamento-card-body { padding: 1rem 1.25rem; }

.licenciamento-info { display: flex; flex-direction: column; gap: 0.5rem; }
.info-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.88rem; }
.info-row i { color: #94a3b8; width: 16px; text-align: center; font-size: 0.85rem; }
.info-label { color: #64748b; min-width: 70px; }
.info-value { color: #0f172a; font-weight: 500; }

.licenciamento-card-footer {
  padding: 0.75rem 1.25rem;
  background: #fafbfc;
  border-top: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.date-text { font-size: 0.8rem; color: #64748b; }
.arrow-icon { color: #cbd5e1; font-size: 0.9rem; transition: transform 0.2s; }
.licenciamento-card:hover .arrow-icon { transform: translateX(3px); color: #2563eb; }

@media (max-width: 576px) {
  .licenciamentos-grid { grid-template-columns: 1fr; }
  .page-title { font-size: 1.35rem; }
  .stat-card { padding: 1rem; }
  .stat-icon { width: 40px; height: 40px; font-size: 1.2rem; }
  .stat-value { font-size: 1.25rem; }
}
</style>
