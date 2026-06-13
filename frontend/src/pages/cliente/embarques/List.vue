<template>
  <div class="crud-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Embarques</h1>
        <p class="text-muted mb-0">Gerencie os embarques e o seu estado.</p>
      </div>
      <router-link to="/embarques/novo" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Novo embarque
      </router-link>
    </div>

    <div class="stats-grid mb-4">
      <div class="stat-card">
        <div class="stat-icon bg-primary-soft"><i class="bi bi-box-seam"></i></div>
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
        <div class="stat-icon bg-info-soft"><i class="bi bi-truck"></i></div>
        <div>
          <div class="stat-label">Em trânsito</div>
          <div class="stat-value">{{ stats.em_transito || 0 }}</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon bg-success-soft"><i class="bi bi-check-circle"></i></div>
        <div>
          <div class="stat-label">Entregues</div>
          <div class="stat-value">{{ stats.entregue || 0 }}</div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="filters">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" placeholder="Pesquisar por tracking, origem, destino..." @input="debounceSearch">
          </div>
          <select v-model="filters.status" class="form-select" @change="fetchData">
            <option value="">Todos os estados</option>
            <option value="pendente">Pendente</option>
            <option value="em_transito">Em trânsito</option>
            <option value="entregue">Entregue</option>
            <option value="cancelado">Cancelado</option>
          </select>
          <select v-model="filters.type" class="form-select" @change="fetchData">
            <option value="">Todos os tipos</option>
            <option value="maritimo">Marítimo</option>
            <option value="aereo">Aéreo</option>
            <option value="terrestre">Terrestre</option>
            <option value="ferroviario">Ferroviário</option>
            <option value="multimodal">Multimodal</option>
          </select>
        </div>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="empty-state">
          <i class="bi bi-inbox"></i>
          <p>Nenhum embarque encontrado.</p>
          <router-link to="/embarques/novo" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Criar primeiro embarque
          </router-link>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Tracking</th>
                <th>Origem → Destino</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Valor</th>
                <th>Data</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td><code class="tracking-code">{{ item.tracking_number }}</code></td>
                <td>
                  <div class="route-line">
                    <span>{{ item.origin }}</span>
                    <i class="bi bi-arrow-right text-muted mx-2"></i>
                    <span>{{ item.destination }}</span>
                  </div>
                </td>
                <td>
                  <span class="type-badge" :class="`type-${item.type}`">
                    <i :class="typeIcon(item.type)"></i> {{ typeLabel(item.type) }}
                  </span>
                </td>
                <td><span class="status-badge" :class="`status-${item.status}`">{{ statusLabel(item.status) }}</span></td>
                <td>{{ formatCurrency(item.declared_value, item.currency) }}</td>
                <td><small class="text-muted">{{ formatDate(item.created_at) }}</small></td>
                <td class="text-end">
                  <div class="action-buttons">
                    <router-link :to="`/embarques/${item.id}/editar`" class="btn btn-sm btn-outline-primary" title="Editar">
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
const filters = reactive({ q: '', status: '', type: '' })
let searchTimer = null

const authHeader = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } })

const fetchData = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.q) params.q = filters.q
    if (filters.status) params.status = filters.status
    if (filters.type) params.type = filters.type
    const response = await axios.get('/api/embarques', { ...authHeader(), params })
    if (response.data.success) items.value = response.data.data.embarques
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/embarques/stats', authHeader())
    if (response.data.success) stats.value = response.data.data.stats
  } catch (e) { stats.value = {} }
}

const debounceSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(fetchData, 300)
}

const confirmDelete = async (item) => {
  if (!confirm(`Eliminar embarque ${item.tracking_number}?`)) return
  try {
    await axios.delete(`/api/embarques/${item.id}`, authHeader())
    await fetchData()
    await fetchStats()
  } catch (error) {
    alert(error.response?.data?.message || 'Erro ao eliminar')
  }
}

const typeIcon = (t) => ({
  maritimo: 'bi bi-water',
  aereo: 'bi bi-airplane',
  terrestre: 'bi bi-truck',
  ferroviario: 'bi bi-train-front',
  multimodal: 'bi bi-arrow-left-right'
}[t] || 'bi bi-box')

const typeLabel = (t) => ({
  maritimo: 'Marítimo', aereo: 'Aéreo', terrestre: 'Terrestre', ferroviario: 'Ferroviário', multimodal: 'Multimodal'
}[t] || t)

const statusLabel = (s) => ({
  pendente: 'Pendente', em_transito: 'Em trânsito', entregue: 'Entregue', cancelado: 'Cancelado'
}[s] || s)

const formatCurrency = (v, c) => {
  if (!v || v == 0) return '—'
  return new Intl.NumberFormat('pt-AO', { style: 'currency', currency: c || 'AOA', maximumFractionDigits: 0 }).format(v)
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : '—'

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
.filters .form-select { max-width: 200px; border: 2px solid #e2e8f0; border-radius: 8px; }

.empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
.empty-state p { margin-bottom: 1rem; }

.tracking-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem; color: #334155; }
.route-line { display: flex; align-items: center; flex-wrap: wrap; }

.type-badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.3rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 500; background: #f1f5f9; color: #475569; }
.type-maritimo { background: #dbeafe; color: #1d4ed8; }
.type-aereo { background: #cffafe; color: #0e7490; }
.type-terrestre { background: #fef3c7; color: #b45309; }
.type-ferroviario { background: #fce7f3; color: #be185d; }
.type-multimodal { background: #e0e7ff; color: #4338ca; }

.status-badge { display: inline-block; padding: 0.25rem 0.65rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
.status-pendente { background: #fef3c7; color: #92400e; }
.status-em_transito { background: #cffafe; color: #155e75; }
.status-entregue { background: #d1fae5; color: #065f46; }
.status-cancelado { background: #fee2e2; color: #991b1b; }

.action-buttons { display: inline-flex; gap: 0.4rem; }
.action-buttons .btn-sm { padding: 0.25rem 0.6rem; }
</style>
