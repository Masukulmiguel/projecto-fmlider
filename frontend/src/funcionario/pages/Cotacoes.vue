<template>
  <div class="crud-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Cotações</h1>
        <p class="text-muted mb-0">Lista de cotações dos clientes.</p>
      </div>
    </div>
    <div class="card">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        <div v-else-if="items.length === 0" class="empty-state">
          <i class="bi bi-receipt"></i>
          <p>Sem cotações.</p>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Referência</th>
                <th>Cliente</th>
                <th>Rota</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Data</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in items" :key="c.id">
                <td><code class="ref">{{ c.reference }}</code></td>
                <td>{{ c.client_name || '—' }}</td>
                <td><span class="route">{{ c.origin }} → {{ c.destination }}</span></td>
                <td>{{ c.currency }} {{ parseFloat(c.estimated_value || 0).toLocaleString('pt-PT', { minimumFractionDigits: 2 }) }}</td>
                <td><span class="status-badge" :class="`status-${c.status}`">{{ statusLabel(c.status) }}</span></td>
                <td><small class="text-muted">{{ formatDate(c.created_at) }}</small></td>
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

const statusLabel = (s) => ({ pendente: 'Pendente', aprovada: 'Aprovada', rejeitada: 'Rejeitada', expirada: 'Expirada' }[s] || s)
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : '—'

const load = async () => {
  loading.value = true
  try {
    const r = await axios.get('/api/cotacoes', { headers: { Authorization: `Bearer ${authStore.token}` } })
    if (r.data.success) items.value = r.data.data.cotacoes
  } finally { loading.value = false }
}

onMounted(load)
</script>

<style scoped>
.crud-page { padding: 1.5rem; }
.page-header { margin-bottom: 1.5rem; }
.page-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
.ref { background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }
.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.status-pendente { background: #fef3c7; color: #92400e; }
.status-aprovada { background: #d1fae5; color: #065f46; }
.status-rejeitada { background: #fee2e2; color: #991b1b; }
.status-expirada { background: #e5e7eb; color: #374151; }
</style>
