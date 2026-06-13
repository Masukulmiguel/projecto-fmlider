<template>
  <div class="crud-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Embarques</h1>
        <p class="text-muted mb-0">Lista de embarques de todos os clientes.</p>
      </div>
    </div>
    <div class="card">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>
        <div v-else-if="items.length === 0" class="empty-state">
          <i class="bi bi-box-seam"></i>
          <p>Sem embarques.</p>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Tracking</th>
                <th>Cliente</th>
                <th>Rota</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Data</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="e in items" :key="e.id">
                <td><code class="tracking">{{ e.tracking_number }}</code></td>
                <td>{{ e.client_name || '—' }}<br><small class="text-muted">{{ e.company_name || '' }}</small></td>
                <td><span class="route">{{ e.origin }} → {{ e.destination }}</span></td>
                <td><span class="text-capitalize">{{ e.type }}</span></td>
                <td><span class="status-badge" :class="`status-${e.status}`">{{ statusLabel(e.status) }}</span></td>
                <td><small class="text-muted">{{ formatDate(e.created_at) }}</small></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const items = ref([])
const loading = ref(false)

const statusLabel = (s) => ({ pendente: 'Pendente', em_transito: 'Em trânsito', entregue: 'Entregue', cancelado: 'Cancelado' }[s] || s)
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : '—'

const load = async () => {
  loading.value = true
  try {
    const r = await axios.get('/api/embarques', { headers: { Authorization: `Bearer ${authStore.token}` } })
    if (r.data.success) items.value = r.data.data.embarques
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

onMounted(load)
</script>

<style scoped>
.crud-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.page-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
.tracking { background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }
.route { font-size: 0.9rem; }
.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.status-pendente { background: #fef3c7; color: #92400e; }
.status-em_transito { background: #cffafe; color: #155e75; }
.status-entregue { background: #d1fae5; color: #065f46; }
.status-cancelado { background: #fee2e2; color: #991b1b; }
</style>
