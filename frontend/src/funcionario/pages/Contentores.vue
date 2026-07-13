<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <h2>Contentores</h2>
      <p class="text-muted mb-0">Consulta de contentores registados</p>
    </div>

    <div class="stats-grid mb-4">
      <div class="stat-card"><div class="stat-icon" style="background:#dbeafe;color:#1e40af"><i class="bi bi-box-seam"></i></div><div class="stat-info"><span class="stat-value">{{ stats.total }}</span><span class="stat-label">Total</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#d1fae5;color:#065f46"><i class="bi bi-house-check"></i></div><div class="stat-info"><span class="stat-value">{{ stats.na_base }}</span><span class="stat-label">Na Base</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#fef3c7;color:#92400e"><i class="bi bi-clock"></i></div><div class="stat-info"><span class="stat-value">{{ stats.agendados }}</span><span class="stat-label">Agendados</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#ede9fe;color:#6d28d9"><i class="bi bi-truck"></i></div><div class="stat-info"><span class="stat-value">{{ stats.em_transporte }}</span><span class="stat-label">Em Transporte</span></div></div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box"><i class="bi bi-search"></i><input v-model="filters.q" type="text" placeholder="Pesquisar por número, NS, processo..." @input="debounceSearch"></div>
          <select v-model="filters.estado" class="form-select" @change="fetchData"><option value="">Todos os estados</option><option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option></select>
          <select v-model="filters.cliente_id" class="form-select" @change="fetchData"><option value="">Todos os clientes</option><option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option></select>
        </div>

        <div v-if="loading" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">Nenhum contentor encontrado.</div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>Número</th><th>Tipologia</th><th>Cliente</th><th>Terminal</th><th>ETA</th><th>Estado</th><th>Ações</th></tr></thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td><code class="tracking-code">{{ item.numero }}</code></td>
                <td>{{ item.tipologia || '' }}</td>
                <td>{{ getClientName(item.cliente_id) }}</td>
                <td>{{ item.terminal || '' }}</td>
                <td><small class="text-muted">{{ formatDate(item.eta) }}</small></td>
                <td><span class="status-badge" :class="'status-' + item.estado">{{ estadoLabel(item.estado) }}</span></td>
                <td><button class="btn-icon btn-view" @click="openDetail(item)" title="Ver detalhes"><i class="bi bi-eye"></i></button></td>
              </tr>
            </tbody>
          </table>
          <div v-if="totalPages > 1" class="pagination-bar">
            <span class="page-info">{{ currentPage }} / {{ totalPages }}</span>
            <div class="page-btns">
              <button class="page-btn" :disabled="currentPage === 1" @click="changePage(currentPage - 1)"><i class="bi bi-chevron-left"></i></button>
              <button class="page-btn" :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)"><i class="bi bi-chevron-right"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetail && detailItem" class="modal-overlay" @click.self="showDetail = false">
      <div class="modal-content modal-xl">
        <div class="modal-header"><h5>Detalhes do Contentor</h5><button class="btn-close" @click="showDetail = false"></button></div>
        <div class="modal-body">
          <div class="form-section"><h6 class="form-section-title">Identificação</h6><div class="row g-3">
            <div class="col-md-3"><span class="detail-label">Número</span><div class="detail-value"><code>{{ detailItem.numero }}</code></div></div>
            <div class="col-md-3"><span class="detail-label">NS</span><div class="detail-value">{{ detailItem.ns || '' }}</div></div>
            <div class="col-md-3"><span class="detail-label">Selo</span><div class="detail-value">{{ detailItem.selo || '' }}</div></div>
            <div class="col-md-3"><span class="detail-label">Tipologia</span><div class="detail-value">{{ detailItem.tipologia || '' }}</div></div>
          </div></div>
          <div class="form-section"><h6 class="form-section-title">Especificações</h6><div class="row g-3">
            <div class="col-md-4"><span class="detail-label">Capacidade</span><div class="detail-value">{{ detailItem.capacidade || '' }}</div></div>
            <div class="col-md-4"><span class="detail-label">Peso</span><div class="detail-value">{{ detailItem.peso ? detailItem.peso + ' kg' : '' }}</div></div>
            <div class="col-md-4"><span class="detail-label">Taxas</span><div class="detail-value">{{ detailItem.taxas ? detailItem.taxas + ' Kz' : '' }}</div></div>
          </div></div>
          <div class="form-section"><h6 class="form-section-title">Datas</h6><div class="row g-3">
            <div class="col-md-3"><span class="detail-label">ETA</span><div class="detail-value">{{ formatDate(detailItem.eta) }}</div></div>
            <div class="col-md-3"><span class="detail-label">ATA</span><div class="detail-value">{{ formatDate(detailItem.ata) }}</div></div>
            <div class="col-md-3"><span class="detail-label">Data Descarga</span><div class="detail-value">{{ formatDate(detailItem.data_descarga) }}</div></div>
            <div class="col-md-3"><span class="detail-label">Previsão Saída</span><div class="detail-value">{{ formatDate(detailItem.previsao_saida) }}</div></div>
          </div></div>
          <div class="form-section"><h6 class="form-section-title">Logística</h6><div class="row g-3">
            <div class="col-md-3"><span class="detail-label">Terminal</span><div class="detail-value">{{ detailItem.terminal || '' }}</div></div>
            <div class="col-md-3"><span class="detail-label">Nº T1</span><div class="detail-value">{{ detailItem.numero_t1 || '' }}</div></div>
            <div class="col-md-3"><span class="detail-label">Data T1</span><div class="detail-value">{{ formatDate(detailItem.data_t1) }}</div></div>
            <div class="col-md-3"><span class="detail-label">Estado</span><div><span class="status-badge" :class="'status-' + detailItem.estado">{{ estadoLabel(detailItem.estado) }}</span></div></div>
          </div></div>
          <div class="form-section"><h6 class="form-section-title">Processo & Cliente</h6><div class="row g-3">
            <div class="col-md-3"><span class="detail-label">Cliente</span><div class="detail-value">{{ getClientName(detailItem.cliente_id) }}</div></div>
            <div class="col-md-3"><span class="detail-label">Nº Processo</span><div class="detail-value">{{ detailItem.numero_processo || '' }}</div></div>
            <div class="col-md-3"><span class="detail-label">Ref. FMLider</span><div class="detail-value">{{ detailItem.referencia_fmlider || '' }}</div></div>
            <div class="col-md-3"><span class="detail-label">Ref. Cliente</span><div class="detail-value">{{ detailItem.referencia_cliente || '' }}</div></div>
          </div></div>
          <div v-if="detailItem.observacoes" class="form-section"><h6 class="form-section-title">Observações</h6><p class="mb-0">{{ detailItem.observacoes }}</p></div>
        </div>
      </div>
    </div>

    <div v-if="toast.show" class="toast-container" :class="'toast-' + toast.type"><i :class="toast.type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill'" class="me-2"></i>{{ toast.message }}</div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'

const items = ref([])
const loading = ref(false)
const clients = ref([])
const filters = reactive({ q: '', estado: '', cliente_id: '' })
const currentPage = ref(1)
const pageSize = 10
const totalItems = ref(0)
let searchTimer = null

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

const stats = reactive({ total: 0, na_base: 0, agendados: 0, em_transporte: 0 })

const fetchData = async () => {
  loading.value = true
  try {
    let query = supabase.from('contentores').select('*', { count: 'exact' })
    if (filters.estado) query = query.eq('estado', filters.estado)
    if (filters.cliente_id) query = query.eq('cliente_id', filters.cliente_id)
    if (filters.q) query = query.or(`numero.ilike.%${filters.q}%,ns.ilike.%${filters.q}%,numero_processo.ilike.%${filters.q}%`)
    const from = (currentPage.value - 1) * pageSize
    const { data, error, count } = await query.order('created_at', { ascending: false }).range(from, from + pageSize - 1)
    if (error) throw error
    items.value = data || []
    totalItems.value = count || 0
    stats.total = totalItems.value
    stats.na_base = items.value.filter(i => i.estado === 'na_base').length
    stats.agendados = items.value.filter(i => i.estado === 'agendado_para_entrega').length
    stats.em_transporte = items.value.filter(i => i.estado === 'em_transporte').length
  } catch (e) { console.error(e) } finally { loading.value = false }
}

const totalPages = computed(() => Math.ceil(totalItems.value / pageSize))
const changePage = (page) => { currentPage.value = page; fetchData() }
const debounceSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(() => { currentPage.value = 1; fetchData() }, 300) }

const fetchClients = async () => { const { data } = await supabase.from('users').select('id, name').eq('role', 'cliente').order('name'); clients.value = data || [] }
const getClientName = (id) => clients.value.find(c => c.id === id)?.name || ''
const estadoLabel = (e) => estados.find(s => s.value === e)?.label || e
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : ''

const showDetail = ref(false)
const detailItem = ref(null)
const openDetail = (item) => { detailItem.value = item; showDetail.value = true }

const toast = reactive({ show: false, type: 'success', message: '' })
let toastTimer = null
const showToast = (type, message) => { toast.type = type; toast.message = message; toast.show = true; clearTimeout(toastTimer); toastTimer = setTimeout(() => { toast.show = false }, 4000) }

onMounted(() => { fetchData(); fetchClients() })
</script>

<style scoped>
.admin-page { background: #f8f9fa; min-height: 100vh; position: relative; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-body { padding: 1.5rem; }
.filters { display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; }
.search-box { position: relative; flex: 1; min-width: 240px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input { width: 100%; padding: 0.55rem 0.75rem 0.55rem 2.25rem; border: 2px solid #e2e8f0; border-radius: 8px; }
.search-box input:focus { border-color: #2563eb; outline: none; }
.form-select { max-width: 220px; border: 2px solid #e2e8f0; border-radius: 8px; }
.tracking-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1.25rem; }
.stat-card { background: white; border-radius: 0.75rem; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.stat-icon { width: 3rem; height: 3rem; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.stat-info { display: flex; flex-direction: column; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #1a365d; }
.stat-label { font-size: 0.875rem; color: #6b7280; }
.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.status-aguardando_chegada { background: #f3f4f6; color: #4b5563; }
.status-chegou_ao_porto { background: #cffafe; color: #155e75; }
.status-em_terminal { background: #dbeafe; color: #1e40af; }
.status-na_base { background: #d1fae5; color: #065f46; }
.status-agendado_para_entrega { background: #fef3c7; color: #92400e; }
.status-em_transporte { background: #ede9fe; color: #6d28d9; }
.status-entregue { background: #bbf7d0; color: #14532d; }
.status-devolvido { background: #f3f4f6; color: #1f2937; }
.status-cancelado { background: #fee2e2; color: #991b1b; }
.btn-icon { width: 32px; height: 32px; border: none; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-view { background: #f0f9ff; color: #0369a1; }
.btn-view:hover { background: #e0f2fe; color: #0284c7; }
.form-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; margin-bottom: 1rem; }
.form-section-title { font-weight: 600; color: #1a365d; margin-bottom: 0.75rem; font-size: 0.9rem; }
.detail-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; font-weight: 600; }
.detail-value { font-size: 0.9rem; color: #1e293b; font-weight: 500; margin-top: 0.25rem; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1050; }
.modal-content { background: white; border-radius: 12px; width: 100%; max-width: 520px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
.modal-content.modal-xl { max-width: 900px; }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
.modal-header h5 { margin: 0; font-weight: 600; }
.modal-body { padding: 1.5rem; overflow-y: auto; flex: 1; min-height: 0; }
.toast-container { position: fixed; top: 20px; right: 20px; padding: 0.75rem 1.25rem; border-radius: 8px; color: white; font-weight: 500; z-index: 1100; animation: slideIn 0.3s ease; }
.toast-success { background: #059669; }
.toast-error { background: #dc2626; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
.pagination-bar { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1.25rem; border-top: 1px solid #f1f5f9; }
.page-info { font-size: 0.82rem; color: #64748b; font-weight: 500; }
.page-btns { display: flex; gap: 6px; }
.page-btn { width: 34px; height: 34px; border: 1px solid #e2e8f0; border-radius: 8px; background: white; color: #475569; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.page-btn:hover:not(:disabled) { border-color: #0f766e; color: #0f766e; background: #f0fdfa; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
@media (max-width: 768px) { .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; } }
@media (max-width: 480px) { .stats-grid { grid-template-columns: 1fr; } }
</style>
