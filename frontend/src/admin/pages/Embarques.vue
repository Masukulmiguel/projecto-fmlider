<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <div>
        <h2>Embarques</h2>
        <p class="text-muted mb-0">Todos os embarques de todos os clientes.</p>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" placeholder="Pesquisar..." @input="debounceSearch">
          </div>
          <select v-model="filters.status" class="form-select" @change="fetchData">
            <option value="">Todos os estados</option>
            <option value="pendente">Pendente</option>
            <option value="em_transito">Em trânsito</option>
            <option value="entregue">Entregue</option>
            <option value="cancelado">Cancelado</option>
          </select>
        </div>

        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
          Nenhum embarque encontrado.
        </div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Tracking</th>
                <th>Cliente</th>
                <th>Rota</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Valor</th>
                <th>Data</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td><code class="tracking-code">{{ item.tracking_number }}</code></td>
                <td>
                  <div class="fw-medium">{{ item.company_name || item.client_name }}</div>
                  <small class="text-muted">{{ item.client_name }}</small>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-1">
                    <span>{{ item.origin }}</span>
                    <i class="bi bi-arrow-right text-muted"></i>
                    <span>{{ item.destination }}</span>
                  </div>
                </td>
                <td>{{ typeLabel(item.type) }}</td>
                <td><span class="status-badge" :class="`status-${item.status}`">{{ statusLabel(item.status) }}</span></td>
                <td>{{ formatCurrency(item.declared_value, item.currency) }}</td>
                <td><small class="text-muted">{{ formatDate(item.created_at) }}</small></td>
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
    const r = await axios.get('/api/embarques', { ...authHeader(), params })
    if (r.data.success) items.value = r.data.data.embarques
  } finally { loading.value = false }
}

const debounceSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(fetchData, 300) }

const typeLabel = (t) => ({ maritimo: 'Marítimo', aereo: 'Aéreo', terrestre: 'Terrestre', ferroviario: 'Ferroviário', multimodal: 'Multimodal' }[t] || t)
const statusLabel = (s) => ({ pendente: 'Pendente', em_transito: 'Em trânsito', entregue: 'Entregue', cancelado: 'Cancelado' }[s] || s)
const formatCurrency = (v, c) => v ? new Intl.NumberFormat('pt-AO', { style: 'currency', currency: c || 'AOA', maximumFractionDigits: 0 }).format(v) : '—'
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : '—'

onMounted(fetchData)
</script>

<style scoped>
.admin-page { background: #f8f9fa; min-height: 100vh; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-body { padding: 1.5rem; }
.filters { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.search-box { position: relative; flex: 1; min-width: 240px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input { width: 100%; padding: 0.55rem 0.75rem 0.55rem 2.25rem; border: 2px solid #e2e8f0; border-radius: 8px; }
.search-box input:focus { border-color: #2563eb; outline: none; }
.form-select { max-width: 200px; border: 2px solid #e2e8f0; border-radius: 8px; }

.tracking-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }
.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.status-pendente { background: #fef3c7; color: #92400e; }
.status-em_transito { background: #cffafe; color: #155e75; }
.status-entregue { background: #d1fae5; color: #065f46; }
.status-cancelado { background: #fee2e2; color: #991b1b; }
</style>
