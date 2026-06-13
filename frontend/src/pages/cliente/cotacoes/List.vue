<template>
  <div class="crud-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Cotações</h1>
        <p class="text-muted mb-0">Gerencie os pedidos de cotação.</p>
      </div>
      <router-link to="/cotacoes/novo" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nova cotação
      </router-link>
    </div>

    <div class="stats-grid mb-4">
      <div class="stat-card">
        <div class="stat-icon bg-primary-soft"><i class="bi bi-file-text"></i></div>
        <div>
          <div class="stat-label">Total</div>
          <div class="stat-value">{{ stats.total || 0 }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-warning-soft"><i class="bi bi-hourglass-split"></i></div>
        <div>
          <div class="stat-label">Pendentes</div>
          <div class="stat-value">{{ stats.pendente || 0 }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-success-soft"><i class="bi bi-check-circle"></i></div>
        <div>
          <div class="stat-label">Aprovadas</div>
          <div class="stat-value">{{ stats.aprovada || 0 }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-danger-soft"><i class="bi bi-x-circle"></i></div>
        <div>
          <div class="stat-label">Rejeitadas</div>
          <div class="stat-value">{{ stats.rejeitada || 0 }}</div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="filters">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" placeholder="Pesquisar por referência, origem, destino..." @input="debounceSearch">
          </div>
          <select v-model="filters.status" class="form-select" @change="fetchData">
            <option value="">Todos os estados</option>
            <option value="pendente">Pendente</option>
            <option value="aprovada">Aprovada</option>
            <option value="rejeitada">Rejeitada</option>
            <option value="expirada">Expirada</option>
          </select>
        </div>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="empty-state">
          <i class="bi bi-receipt"></i>
          <p>Nenhuma cotação encontrada.</p>
          <router-link to="/cotacoes/novo" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Criar primeira cotação
          </router-link>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Referência</th>
                <th>Rota</th>
                <th>Tipo</th>
                <th>Peso</th>
                <th>Valor estimado</th>
                <th>Estado</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td><code class="ref-code">{{ item.reference }}</code></td>
                <td>
                  <div class="route-line">
                    <span>{{ item.origin }}</span>
                    <i class="bi bi-arrow-right text-muted mx-2"></i>
                    <span>{{ item.destination }}</span>
                  </div>
                </td>
                <td><span class="type-badge">{{ typeLabel(item.type) }}</span></td>
                <td>{{ item.weight || 0 }} kg</td>
                <td>{{ formatCurrency(item.estimated_value, item.currency) }}</td>
                <td><span class="status-badge" :class="`status-${item.status}`">{{ statusLabel(item.status) }}</span></td>
                <td class="text-end">
                  <div class="action-buttons">
                    <router-link :to="`/cotacoes/${item.id}/editar`" class="btn btn-sm btn-outline-primary" title="Editar">
                      <i class="bi bi-pencil"></i>
                    </router-link>
                    <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(item)" title="Eliminar">
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
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const items = ref([])
const stats = ref({})
const loading = ref(false)
const filters = reactive({ q: '', status: '' })
let searchTimer = null

const authHeader = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } })

const fetchData = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.q) params.q = filters.q
    if (filters.status) params.status = filters.status
    const r = await axios.get('/api/cotacoes', { ...authHeader(), params })
    if (r.data.success) items.value = r.data.data.cotacoes
  } finally { loading.value = false }
}

const fetchStats = async () => {
  try {
    const r = await axios.get('/api/cotacoes/stats', authHeader())
    if (r.data.success) stats.value = r.data.data.stats
  } catch (e) { stats.value = {} }
}

const debounceSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(fetchData, 300) }

const confirmDelete = async (item) => {
  if (!confirm(`Eliminar cotação ${item.reference}?`)) return
  try { await axios.delete(`/api/cotacoes/${item.id}`, authHeader()); await fetchData(); await fetchStats() }
  catch (e) { alert('Erro ao eliminar') }
}

const typeLabel = (t) => ({ maritimo: 'Marítimo', aereo: 'Aéreo', terrestre: 'Terrestre', ferroviario: 'Ferroviário', multimodal: 'Multimodal' }[t] || t)
const statusLabel = (s) => ({ pendente: 'Pendente', aprovada: 'Aprovada', rejeitada: 'Rejeitada', expirada: 'Expirada' }[s] || s)
const formatCurrency = (v, c) => v ? new Intl.NumberFormat('pt-AO', { style: 'currency', currency: c || 'AOA', maximumFractionDigits: 0 }).format(v) : '—'

onMounted(() => { fetchData(); fetchStats() })
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
.bg-success-soft { background: #d1fae5; color: #047857; }
.bg-danger-soft { background: #fee2e2; color: #b91c1c; }
.stat-label { color: #64748b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #0f172a; }

.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-header { background: white; border-bottom: 1px solid #f1f5f9; padding: 1rem 1.25rem; }
.filters { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.search-box { position: relative; flex: 1; min-width: 240px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input { width: 100%; padding: 0.6rem 0.75rem 0.6rem 2.25rem; border: 2px solid #e2e8f0; border-radius: 8px; }
.search-box input:focus { border-color: #2563eb; outline: none; }
.filters .form-select { max-width: 200px; border: 2px solid #e2e8f0; border-radius: 8px; }

.empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }

.ref-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem; color: #334155; }
.route-line { display: flex; align-items: center; flex-wrap: wrap; }
.type-badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 500; background: #f1f5f9; color: #475569; }
.status-badge { display: inline-block; padding: 0.25rem 0.65rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
.status-pendente { background: #fef3c7; color: #92400e; }
.status-aprovada { background: #d1fae5; color: #065f46; }
.status-rejeitada { background: #fee2e2; color: #991b1b; }
.status-expirada { background: #e5e7eb; color: #4b5563; }

.action-buttons { display: inline-flex; gap: 0.4rem; }
</style>
