<template>
  <div class="cliente-entregas p-4 p-md-5">
    <div class="welcome-card mb-4">
      <h2 class="mb-1">As Minhas Entregas</h2>
      <p class="text-muted mb-0">Acompanhe todas as suas entregas e contentores</p>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-6 col-xl-3">
        <div class="stat-tile">
          <div class="stat-tile-icon bg-primary-soft"><i class="bi bi-truck"></i></div>
          <div>
            <div class="stat-tile-label">Total Entregas</div>
            <div class="stat-tile-value">{{ counts.total }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="stat-tile">
          <div class="stat-tile-icon bg-cyan-soft"><i class="bi bi-arrow-repeat"></i></div>
          <div>
            <div class="stat-tile-label">Em Transporte</div>
            <div class="stat-tile-value">{{ counts.em_transporte }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="stat-tile">
          <div class="stat-tile-icon bg-success-soft"><i class="bi bi-check-circle-fill"></i></div>
          <div>
            <div class="stat-tile-label">Entregues</div>
            <div class="stat-tile-value">{{ counts.entregue }}</div>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="stat-tile">
          <div class="stat-tile-icon bg-warning-soft"><i class="bi bi-clock-fill"></i></div>
          <div>
            <div class="stat-tile-label">Pendentes</div>
            <div class="stat-tile-value">{{ counts.pendente }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md-8">
            <label class="form-label fw-semibold small text-muted">Pesquisar</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
              <input v-model="search" type="text" class="form-control" placeholder="Ref, processo, motorista, contentor...">
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold small text-muted">Estado</label>
            <select v-model="estadoFilter" class="form-select">
              <option value="">Todos os estados</option>
              <option value="pendente">Pendente</option>
              <option value="em_preparacao">Em Preparação</option>
              <option value="saiu_da_base">Saiu da Base</option>
              <option value="em_transporte">Em Transporte</option>
              <option value="chegou_cliente">Chegou ao Cliente</option>
              <option value="entregue">Entregue</option>
              <option value="cancelado">Cancelado</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Entregas</h6>
        <span class="text-muted small">{{ filteredItems.length }} resultado(s)</span>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">A carregar...</span>
          </div>
          <p class="text-muted mt-2 small mb-0">A carregar entregas...</p>
        </div>

        <div v-else-if="filteredItems.length === 0" class="empty-state text-center py-5">
          <i class="bi bi-inbox"></i>
          <p class="mb-0 mt-2">Nenhuma entrega encontrada</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Ref FMLider</th>
                <th>Processo</th>
                <th>Origem → Destino</th>
                <th>Motorista</th>
                <th>Camião</th>
                <th>Matrícula</th>
                <th class="text-center">Contentores</th>
                <th>Estado</th>
                <th>Data Prevista</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredItems" :key="item.id" class="clickable-row" @click="openDetail(item)">
                <td><code class="ref-code">{{ item.ref_fmlider || '—' }}</code></td>
                <td>{{ item.processo || '—' }}</td>
                <td>
                  <div class="route-cell">
                    <span>{{ item.origem || '—' }}</span>
                    <i class="bi bi-arrow-right text-muted mx-1"></i>
                    <span>{{ item.destino || '—' }}</span>
                  </div>
                </td>
                <td>{{ item.motorista || '—' }}</td>
                <td>{{ item.camiao || '—' }}</td>
                <td>{{ item.matricula || '—' }}</td>
                <td class="text-center">
                  <span class="badge bg-light text-dark border">{{ item.contentores_count || 0 }}</span>
                </td>
                <td>
                  <span class="estado-badge" :style="{ background: estadoColor(item.estado), color: '#fff' }">
                    {{ estadoLabel(item.estado) }}
                  </span>
                </td>
                <td>{{ formatDate(item.data_prevista) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showModal && selectedItem" class="modal-backdrop-custom" @click.self="showModal = false">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
              <div>
                <h5 class="mb-0 fw-bold">Detalhes da Entrega</h5>
                <small class="text-muted">{{ selectedItem.ref_fmlider || 'Sem referência' }}</small>
              </div>
              <button type="button" class="btn-close" @click="showModal = false"></button>
            </div>
            <div class="modal-body">
              <div class="detail-section">
                <h6 class="section-title"><i class="bi bi-info-circle me-1"></i> Informações Gerais</h6>
                <div class="row g-3">
                  <div class="col-sm-6">
                    <div class="detail-field">
                      <span class="detail-label">Ref FMLider</span>
                      <span class="detail-value"><code>{{ selectedItem.ref_fmlider || '—' }}</code></span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="detail-field">
                      <span class="detail-label">Processo</span>
                      <span class="detail-value">{{ selectedItem.processo || '—' }}</span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="detail-field">
                      <span class="detail-label">Origem</span>
                      <span class="detail-value">{{ selectedItem.origem || '—' }}</span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="detail-field">
                      <span class="detail-label">Destino</span>
                      <span class="detail-value">{{ selectedItem.destino || '—' }}</span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="detail-field">
                      <span class="detail-label">Estado</span>
                      <span class="estado-badge" :style="{ background: estadoColor(selectedItem.estado), color: '#fff' }">
                        {{ estadoLabel(selectedItem.estado) }}
                      </span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="detail-field">
                      <span class="detail-label">Data Prevista</span>
                      <span class="detail-value">{{ formatDate(selectedItem.data_prevista) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="detail-section">
                <h6 class="section-title"><i class="bi bi-truck me-1"></i> Transporte</h6>
                <div class="row g-3">
                  <div class="col-sm-4">
                    <div class="detail-field">
                      <span class="detail-label">Motorista</span>
                      <span class="detail-value">{{ selectedItem.motorista || '—' }}</span>
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="detail-field">
                      <span class="detail-label">Camião</span>
                      <span class="detail-value">{{ selectedItem.camiao || '—' }}</span>
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="detail-field">
                      <span class="detail-label">Matrícula</span>
                      <span class="detail-value">{{ selectedItem.matricula || '—' }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="selectedItem.contentores && selectedItem.contentores.length" class="detail-section">
                <h6 class="section-title"><i class="bi bi-box-seam me-1"></i> Contentores ({{ selectedItem.contentores.length }})</h6>
                <div class="table-responsive">
                  <table class="table table-sm table-borderless mb-0">
                    <thead>
                      <tr>
                        <th>Número</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Data Entrega</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(c, idx) in selectedItem.contentores" :key="idx">
                        <td><code class="ref-code">{{ c.numero }}</code></td>
                        <td>{{ c.tipo || '—' }}</td>
                        <td>
                          <span class="estado-badge" :style="{ background: estadoColor(c.estado), color: '#fff', fontSize: '0.7rem' }">
                            {{ estadoLabel(c.estado) }}
                          </span>
                        </td>
                        <td>{{ formatDate(c.data_entrega) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div v-if="selectedItem.historico && selectedItem.historico.length" class="detail-section">
                <h6 class="section-title"><i class="bi bi-clock-history me-1"></i> Histórico</h6>
                <div class="timeline">
                  <div v-for="(h, idx) in selectedItem.historico" :key="idx" class="timeline-item">
                    <div class="timeline-dot" :style="{ background: estadoColor(h.estado) }"></div>
                    <div class="timeline-content">
                      <div class="d-flex justify-content-between align-items-start">
                        <span class="fw-semibold">{{ estadoLabel(h.estado) }}</span>
                        <small class="text-muted">{{ formatDate(h.data) }}</small>
                      </div>
                      <p v-if="h.observacao" class="mb-0 mt-1 small text-muted">{{ h.observacao }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'

const items = ref([])
const loading = ref(true)
const search = ref('')
const estadoFilter = ref('')
const selectedItem = ref(null)
const showModal = ref(false)

const counts = reactive({
  total: 0,
  em_transporte: 0,
  entregue: 0,
  pendente: 0
})

const estadoColor = (e) => ({
  pendente: '#6b7280',
  em_preparacao: '#f59e0b',
  saiu_da_base: '#2563eb',
  em_transporte: '#06b6d4',
  chegou_cliente: '#8b5cf6',
  entregue: '#10b981',
  cancelado: '#ef4444'
}[e] || '#6b7280')

const estadoLabel = (e) => ({
  pendente: 'Pendente',
  em_preparacao: 'Em Preparação',
  saiu_da_base: 'Saiu da Base',
  em_transporte: 'Em Transporte',
  chegou_cliente: 'Chegou ao Cliente',
  entregue: 'Entregue',
  cancelado: 'Cancelado'
}[e] || e || '—')

const formatDate = (d) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('pt-PT', { day: '2-digit', month: 'short', year: 'numeric' })
}

const filteredItems = computed(() => {
  return items.value.filter(item => {
    const matchSearch = !search.value ||
      (item.ref_fmlider || '').toLowerCase().includes(search.value.toLowerCase()) ||
      (item.processo || '').toLowerCase().includes(search.value.toLowerCase()) ||
      (item.motorista || '').toLowerCase().includes(search.value.toLowerCase()) ||
      (item.origem || '').toLowerCase().includes(search.value.toLowerCase()) ||
      (item.destino || '').toLowerCase().includes(search.value.toLowerCase()) ||
      (item.matricula || '').toLowerCase().includes(search.value.toLowerCase()) ||
      (item.contentores || []).some(c => (c.numero || '').toLowerCase().includes(search.value.toLowerCase()))

    const matchEstado = !estadoFilter.value || item.estado === estadoFilter.value

    return matchSearch && matchEstado
  })
})

const openDetail = (item) => {
  selectedItem.value = item
  showModal.value = true
}

const fetchEntregas = async () => {
  loading.value = true
  try {
    const tokenData = JSON.parse(localStorage.getItem('sb-vsupwqxtnzdnxklgbynn-auth-token') || '{}')
    const token = tokenData.access_token

    const res = await fetch(`${import.meta.env.VITE_API_URL}/entregas`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    })

    if (!res.ok) throw new Error('Erro ao carregar entregas')

    const data = await res.json()
    items.value = Array.isArray(data) ? data : (data.data || [])

    counts.total = items.value.length
    counts.em_transporte = items.value.filter(i => ['em_transporte', 'saiu_da_base'].includes(i.estado)).length
    counts.entregue = items.value.filter(i => i.estado === 'entregue').length
    counts.pendente = items.value.filter(i => ['pendente', 'em_preparacao'].includes(i.estado)).length
  } catch (e) {
    console.error('Erro ao buscar entregas:', e)
    items.value = []
  } finally {
    loading.value = false
  }
}

onMounted(fetchEntregas)
</script>

<style scoped>
.cliente-entregas {
  background: #f5f7fa;
  min-height: 100vh;
}

.welcome-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.stat-tile {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  display: flex;
  align-items: center;
  gap: 1rem;
  height: 100%;
}

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
.bg-cyan-soft { background: #cffafe; color: #0e7490; }
.bg-warning-soft { background: #fef3c7; color: #b45309; }

.stat-tile-label { color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stat-tile-value { font-size: 2rem; font-weight: 700; color: #0f172a; line-height: 1.2; }

.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); }
.card-header { background: white; border-bottom: 1px solid #eef0f3; padding: 1rem 1.25rem; }

.form-control:focus,
.form-select:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
}

.input-group-text {
  border-color: #e2e8f0;
}

.table th {
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #64748b;
  font-weight: 600;
  border-bottom: 1px solid #eef0f3;
  white-space: nowrap;
  padding: 0.85rem 1rem;
}

.table td {
  padding: 0.85rem 1rem;
  font-size: 0.88rem;
  color: #334155;
  vertical-align: middle;
}

.clickable-row { cursor: pointer; transition: background 0.15s ease; }
.clickable-row:hover { background: #f8fafc; }

.ref-code {
  background: #f1f5f9;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  color: #334155;
}

.route-cell {
  display: flex;
  align-items: center;
  white-space: nowrap;
  font-size: 0.88rem;
}

.estado-badge {
  display: inline-block;
  padding: 0.25rem 0.65rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
}

.empty-state {
  color: #94a3b8;
}
.empty-state i {
  font-size: 2.5rem;
  display: block;
  opacity: 0.4;
}

.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-content-custom {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 800px;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.modal-header-custom {
  border-bottom: 1px solid #eef0f3;
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  max-height: calc(90vh - 80px);
}

.detail-section {
  margin-bottom: 1.5rem;
}
.detail-section:last-child { margin-bottom: 0; }

.section-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.detail-field {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-label {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #94a3b8;
  font-weight: 600;
}

.detail-value {
  font-size: 0.9rem;
  color: #1e293b;
  font-weight: 500;
}

.timeline {
  position: relative;
  padding-left: 1.5rem;
}
.timeline::before {
  content: '';
  position: absolute;
  left: 6px;
  top: 4px;
  bottom: 4px;
  width: 2px;
  background: #e2e8f0;
}

.timeline-item {
  position: relative;
  padding-bottom: 1rem;
}
.timeline-item:last-child { padding-bottom: 0; }

.timeline-dot {
  position: absolute;
  left: -1.5rem;
  top: 4px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 0 0 2px #e2e8f0;
}

.timeline-content {
  background: #f8fafc;
  border-radius: 8px;
  padding: 0.75rem 1rem;
}

@media (max-width: 768px) {
  .cliente-entregas { padding: 1rem !important; }
  .stat-tile { padding: 1rem; gap: 0.75rem; }
  .stat-tile-icon { width: 42px; height: 42px; border-radius: 10px; font-size: 1.15rem; }
  .stat-tile-label { font-size: 0.72rem; }
  .stat-tile-value { font-size: 1.4rem; }
  .route-cell { font-size: 0.8rem; }
  .modal-content-custom { border-radius: 12px; }
  .modal-body { padding: 1rem; }
}
</style>
