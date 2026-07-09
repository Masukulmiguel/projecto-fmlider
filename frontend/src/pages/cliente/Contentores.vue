<template>
  <div class="cliente-entregas p-4 p-md-5">
    <div class="welcome-card mb-4">
      <h2 class="mb-1">Os Meus Contentores</h2>
      <p class="text-muted mb-0">Acompanhe todos os seus contentores e respectivo estado</p>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-6 col-xl-3"><div class="stat-tile"><div class="stat-tile-icon bg-primary-soft"><i class="bi bi-box-seam"></i></div><div><div class="stat-tile-label">Total</div><div class="stat-tile-value">{{ counts.total }}</div></div></div></div>
      <div class="col-md-6 col-xl-3"><div class="stat-tile"><div class="stat-tile-icon bg-success-soft"><i class="bi bi-house-check"></i></div><div><div class="stat-tile-label">Na Base</div><div class="stat-tile-value">{{ counts.na_base }}</div></div></div></div>
      <div class="col-md-6 col-xl-3"><div class="stat-tile"><div class="stat-tile-icon bg-cyan-soft"><i class="bi bi-arrow-repeat"></i></div><div><div class="stat-tile-label">Em Transporte</div><div class="stat-tile-value">{{ counts.em_transporte }}</div></div></div></div>
      <div class="col-md-6 col-xl-3"><div class="stat-tile"><div class="stat-tile-icon bg-warning-soft"><i class="bi bi-check-circle"></i></div><div><div class="stat-tile-label">Entregues</div><div class="stat-tile-value">{{ counts.entregue }}</div></div></div></div>
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md-8"><label class="form-label fw-semibold small text-muted">Pesquisar</label><div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input v-model="search" type="text" class="form-control" placeholder="Número, processo, referência..."></div></div>
          <div class="col-md-4"><label class="form-label fw-semibold small text-muted">Estado</label><select v-model="estadoFilter" class="form-select"><option value="">Todos os estados</option><option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option></select></div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center"><h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Contentores</h6><span class="text-muted small">{{ filteredItems.length }} resultado(s)</span></div>
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="text-muted mt-2 small mb-0">A carregar contentores...</p></div>
        <div v-else-if="filteredItems.length === 0" class="empty-state text-center py-5"><i class="bi bi-inbox"></i><p class="mb-0 mt-2">Nenhum contentor encontrado</p></div>
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Número</th><th>Estado</th><th>Processo</th><th>Ref.</th><th>ETA</th><th>ATA</th><th>Terminal</th></tr></thead>
            <tbody>
              <tr v-for="item in filteredItems" :key="item.id" class="clickable-row" @click="openDetail(item)">
                <td><code class="ref-code">{{ item.numero }}</code></td>
                <td><span class="estado-badge" :style="{ background: estadoColor(item.estado), color: '#fff' }">{{ estadoLabel(item.estado) }}</span></td>
                <td>{{ item.numero_processo || '' }}</td>
                <td>{{ item.referencia_fmlider || item.referencia_cliente || '' }}</td>
                <td>{{ formatDate(item.eta) }}</td>
                <td>{{ formatDate(item.ata) }}</td>
                <td>{{ item.terminal || '' }}</td>
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
            <div class="modal-header modal-header-custom"><div><h5 class="mb-0 fw-bold">Detalhes do Contentor</h5><small class="text-muted">{{ selectedItem.numero }}</small></div><button type="button" class="btn-close" @click="showModal = false"></button></div>
            <div class="modal-body">
              <div class="detail-section"><h6 class="section-title"><i class="bi bi-info-circle me-1"></i> Identificação</h6><div class="row g-3">
                <div class="col-sm-3"><div class="detail-field"><span class="detail-label">Número</span><span class="detail-value"><code>{{ selectedItem.numero }}</code></span></div></div>
                <div class="col-sm-3"><div class="detail-field"><span class="detail-label">NS</span><span class="detail-value">{{ selectedItem.ns || '' }}</span></div></div>
                <div class="col-sm-3"><div class="detail-field"><span class="detail-label">Tipologia</span><span class="detail-value">{{ selectedItem.tipologia || '' }}</span></div></div>
                <div class="col-sm-3"><div class="detail-field"><span class="detail-label">Estado</span><span class="estado-badge" :style="{ background: estadoColor(selectedItem.estado), color: '#fff' }">{{ estadoLabel(selectedItem.estado) }}</span></div></div>
              </div></div>
              <div class="detail-section"><h6 class="section-title"><i class="bi bi-calendar me-1"></i> Datas</h6><div class="row g-3">
                <div class="col-sm-3"><div class="detail-field"><span class="detail-label">ETA</span><span class="detail-value">{{ formatDate(selectedItem.eta) }}</span></div></div>
                <div class="col-sm-3"><div class="detail-field"><span class="detail-label">ATA</span><span class="detail-value">{{ formatDate(selectedItem.ata) }}</span></div></div>
                <div class="col-sm-3"><div class="detail-field"><span class="detail-label">Descarga</span><span class="detail-value">{{ formatDate(selectedItem.data_descarga) }}</span></div></div>
                <div class="col-sm-3"><div class="detail-field"><span class="detail-label">Previsão Saída</span><span class="detail-value">{{ formatDate(selectedItem.previsao_saida) }}</span></div></div>
              </div></div>
              <div class="detail-section"><h6 class="section-title"><i class="bi bi-geo-alt me-1"></i> Logística</h6><div class="row g-3">
                <div class="col-sm-4"><div class="detail-field"><span class="detail-label">Terminal</span><span class="detail-value">{{ selectedItem.terminal || '' }}</span></div></div>
                <div class="col-sm-4"><div class="detail-field"><span class="detail-label">Nº T1</span><span class="detail-value">{{ selectedItem.numero_t1 || '' }}</span></div></div>
                <div class="col-sm-4"><div class="detail-field"><span class="detail-label">Data T1</span><span class="detail-value">{{ formatDate(selectedItem.data_t1) }}</span></div></div>
              </div></div>
              <div class="detail-section"><h6 class="section-title"><i class="bi bi-link-45deg me-1"></i> Processo</h6><div class="row g-3">
                <div class="col-sm-3"><div class="detail-field"><span class="detail-label">Nº Processo</span><span class="detail-value">{{ selectedItem.numero_processo || '' }}</span></div></div>
                <div class="col-sm-3"><div class="detail-field"><span class="detail-label">Ref. FMLider</span><span class="detail-value">{{ selectedItem.referencia_fmlider || '' }}</span></div></div>
                <div class="col-sm-3"><div class="detail-field"><span class="detail-label">Ref. Cliente</span><span class="detail-value">{{ selectedItem.referencia_cliente || '' }}</span></div></div>
                <div class="col-sm-3"><div class="detail-field"><span class="detail-label">Selo</span><span class="detail-value">{{ selectedItem.selo || '' }}</span></div></div>
              </div></div>
              <div v-if="selectedItem.observacoes" class="detail-section"><h6 class="section-title"><i class="bi bi-chat-left-text me-1"></i> Observações</h6><p class="mb-0 small">{{ selectedItem.observacoes }}</p></div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const items = ref([])
const loading = ref(true)
const search = ref('')
const estadoFilter = ref('')
const selectedItem = ref(null)
const showModal = ref(false)

const counts = reactive({ total: 0, na_base: 0, em_transporte: 0, entregue: 0 })

const estados = [
  { value: 'aguardando_chegada', label: 'Aguardando Chegada' },
  { value: 'chegou_ao_porto', label: 'Chegou ao Porto' },
  { value: 'em_terminal', label: 'Em Terminal' },
  { value: 'na_base', label: 'Na Base FMLider' },
  { value: 'agendado_para_entrega', label: 'Agendado p/ Entrega' },
  { value: 'em_transporte', label: 'Em Transporte' },
  { value: 'entregue', label: 'Entregue' },
  { value: 'devolvido', label: 'Devolvido' },
  { value: 'cancelado', label: 'Cancelado' }
]

const estadoColor = (e) => ({ aguardando_chegada:'#6b7280', chegou_ao_porto:'#06b6d4', em_terminal:'#2563eb', na_base:'#10b981', agendado_para_entrega:'#f59e0b', em_transporte:'#8b5cf6', entregue:'#059669', devolvido:'#64748b', cancelado:'#ef4444' }[e] || '#6b7280')
const estadoLabel = (e) => estados.find(s => s.value === e)?.label || e || ''
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT', { day: '2-digit', month: 'short', year: 'numeric' }) : ''

const filteredItems = computed(() => items.value.filter(item => {
  const matchSearch = !search.value || (item.numero || '').toLowerCase().includes(search.value.toLowerCase()) || (item.numero_processo || '').toLowerCase().includes(search.value.toLowerCase()) || (item.referencia_fmlider || '').toLowerCase().includes(search.value.toLowerCase()) || (item.referencia_cliente || '').toLowerCase().includes(search.value.toLowerCase())
  const matchEstado = !estadoFilter.value || item.estado === estadoFilter.value
  return matchSearch && matchEstado
}))

const openDetail = (item) => { selectedItem.value = item; showModal.value = true }

const fetchContentores = async () => {
  loading.value = true
  try {
    const { data: authData } = await supabase.auth.getUser()
    const userId = authData?.user?.id
    if (!userId) return
    const { data, error } = await supabase.from('contentores').select('*').eq('cliente_id', userId).order('created_at', { ascending: false })
    if (error) throw error
    items.value = data || []
    counts.total = items.value.length
    counts.na_base = items.value.filter(i => i.estado === 'na_base').length
    counts.em_transporte = items.value.filter(i => i.estado === 'em_transporte').length
    counts.entregue = items.value.filter(i => i.estado === 'entregue').length
  } catch (e) { console.error('Erro ao buscar contentores:', e); items.value = [] } finally { loading.value = false }
}

onMounted(fetchContentores)
</script>

<style scoped>
.cliente-entregas { background: #f5f7fa; min-height: 100vh; }
.welcome-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.stat-tile { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 1rem; height: 100%; }
.stat-tile-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
.bg-primary-soft { background: #dbeafe; color: #1d4ed8; }
.bg-success-soft { background: #d1fae5; color: #047857; }
.bg-cyan-soft { background: #cffafe; color: #0e7490; }
.bg-warning-soft { background: #fef3c7; color: #b45309; }
.stat-tile-label { color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stat-tile-value { font-size: 2rem; font-weight: 700; color: #0f172a; line-height: 1.2; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-header { background: white; border-bottom: 1px solid #eef0f3; padding: 1rem 1.25rem; }
.table th { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600; border-bottom: 1px solid #eef0f3; white-space: nowrap; padding: 0.85rem 1rem; }
.table td { padding: 0.85rem 1rem; font-size: 0.88rem; color: #334155; vertical-align: middle; }
.clickable-row { cursor: pointer; transition: background 0.15s ease; }
.clickable-row:hover { background: #f8fafc; }
.ref-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }
.estado-badge { display: inline-block; padding: 0.25rem 0.65rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; white-space: nowrap; }
.empty-state { color: #94a3b8; }
.empty-state i { font-size: 2.5rem; display: block; opacity: 0.4; }
.modal-backdrop-custom { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; display: flex; align-items: center; justify-content: center; padding: 1rem; }
.modal-content-custom { background: white; border-radius: 16px; width: 100%; max-width: 800px; max-height: 90vh; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
.modal-header-custom { border-bottom: 1px solid #eef0f3; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
.modal-body { padding: 1.5rem; overflow-y: auto; max-height: calc(90vh - 80px); }
.detail-section { margin-bottom: 1.5rem; }
.detail-section:last-child { margin-bottom: 0; }
.section-title { font-size: 0.9rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9; }
.detail-field { display: flex; flex-direction: column; gap: 0.25rem; }
.detail-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; font-weight: 600; }
.detail-value { font-size: 0.9rem; color: #1e293b; font-weight: 500; }
@media (max-width: 768px) { .cliente-entregas { padding: 1rem !important; } .stat-tile { padding: 1rem; gap: 0.75rem; } .stat-tile-icon { width: 42px; height: 42px; border-radius: 10px; font-size: 1.15rem; } .stat-tile-label { font-size: 0.72rem; } .stat-tile-value { font-size: 1.4rem; } .modal-content-custom { border-radius: 12px; } .modal-body { padding: 1rem; } }
</style>
