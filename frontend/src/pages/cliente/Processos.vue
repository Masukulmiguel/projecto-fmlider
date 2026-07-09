<template>
  <div class="cliente-page p-4 p-md-5">
    <div class="page-header mb-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2>Os Meus Processos</h2>
          <p class="text-muted mb-0">Acompanhe o estado dos seus processos de desembaraço</p>
        </div>
      </div>
    </div>

    <div class="stats-grid mb-4">
      <div class="stat-card"><div class="stat-icon" style="background:#dbeafe;color:#1e40af"><i class="bi bi-folder2-open"></i></div><div class="stat-info"><span class="stat-value">{{ stats.total }}</span><span class="stat-label">Total</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#fef3c7;color:#92400e"><i class="bi bi-hourglass-split"></i></div><div class="stat-info"><span class="stat-value">{{ stats.em_curso }}</span><span class="stat-label">Em Curso</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#d1fae5;color:#065f46"><i class="bi bi-check-circle"></i></div><div class="stat-info"><span class="stat-value">{{ stats.pronto }}</span><span class="stat-label">Prontos</span></div></div>
    </div>

    <div class="card mb-4">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box"><i class="bi bi-search"></i><input v-model="filters.q" type="text" placeholder="Pesquisar por file, referência..." @input="debounceSearch"></div>
          <select v-model="filters.estado" class="form-select" @change="fetchData">
            <option value="">Todos os estados</option>
            <option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option>
          </select>
        </div>

        <div v-if="loading" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">Nenhum processo encontrado.</div>
        <div v-else>
          <div v-for="item in items" :key="item.id" class="processo-card mb-3" @click="openDetail(item)">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div class="d-flex align-items-center gap-3">
                <div class="processo-icon">
                  <i class="bi bi-folder2-open"></i>
                </div>
                <div>
                  <div class="processo-title">
                    <code class="tracking-code">{{ item.file_number }}</code>
                    <span v-if="item.ref_cliente" class="ms-2 text-muted small">Ref: {{ item.ref_cliente }}</span>
                  </div>
                  <div class="processo-meta mt-1">
                    <span v-if="item.importador"><i class="bi bi-building me-1"></i>{{ item.importador }}</span>
                    <span v-if="item.agencia" class="ms-2"><i class="bi bi-ship me-1"></i>{{ item.agencia }}</span>
                    <span v-if="item.bl" class="ms-2"><i class="bi bi-upc-scan me-1"></i>{{ item.bl }}</span>
                  </div>
                </div>
              </div>
              <div class="text-end">
                <span class="status-badge" :class="'status-' + item.estado">{{ estadoLabel(item.estado) }}</span>
                <div v-if="item.eta" class="mt-1"><small class="text-muted">ETA: {{ formatDate(item.eta) }}</small></div>
              </div>
            </div>
            <div v-if="item.estado_legalizacao" class="processo-desc mt-2">
              <small class="text-muted"><i class="bi bi-info-circle me-1"></i>{{ truncate(item.estado_legalizacao, 120) }}</small>
            </div>
          </div>

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
    <div v-if="showDetailModal" class="modal-overlay" @click.self="closeDetail">
      <div class="modal-content modal-xl">
        <div class="modal-header"><h5>Detalhes do Processo</h5><button class="btn-close" @click="closeDetail"></button></div>
        <div class="modal-body" v-if="detailItem">
          <div class="detail-grid">
            <div class="detail-section">
              <h6><i class="bi bi-info-circle me-1"></i> Identificação</h6>
              <div class="detail-row"><span class="detail-label">File</span><code>{{ detailItem.file_number }}</code></div>
              <div class="detail-row"><span class="detail-label">Ref. Cliente</span><span>{{ detailItem.ref_cliente || '' }}</span></div>
              <div class="detail-row"><span class="detail-label">Importador</span><span>{{ detailItem.importador || '' }}</span></div>
            </div>
            <div class="detail-section">
              <h6><i class="bi bi-ship me-1"></i> Transporte</h6>
              <div class="detail-row"><span class="detail-label">Agência</span><span>{{ detailItem.agencia || '' }}</span></div>
              <div class="detail-row"><span class="detail-label">BL</span><span>{{ detailItem.bl || '' }}</span></div>
              <div class="detail-row"><span class="detail-label">Typo</span><span>{{ detailItem.typo || '' }}</span></div>
            </div>
            <div class="detail-section">
              <h6><i class="bi bi-calendar me-1"></i> Datas</h6>
              <div class="detail-row"><span class="detail-label">ETA</span><span>{{ formatDate(detailItem.eta) }}</span></div>
              <div class="detail-row"><span class="detail-label">ATA</span><span>{{ formatDate(detailItem.ata) }}</span></div>
            </div>
            <div class="detail-section">
              <h6><i class="bi bi-gear me-1"></i> Estado</h6>
              <div class="detail-row"><span class="detail-label">Estado</span><span class="status-badge" :class="'status-' + detailItem.estado">{{ estadoLabel(detailItem.estado) }}</span></div>
              <div class="detail-row"><span class="detail-label">Actualizado</span><span>{{ formatDateTime(detailItem.updated_at) }}</span></div>
            </div>
          </div>
          <div v-if="detailItem.estado_legalizacao" class="mt-3">
            <h6><i class="bi bi-text-left me-1"></i> Estado Legalização</h6>
            <p class="text-muted">{{ detailItem.estado_legalizacao }}</p>
          </div>

          <div v-if="detailItem.bl" class="mt-3 tracking-panel">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0"><i class="bi bi-box-seam me-1"></i> Rastreamento do Contentor</h6>
              <button class="btn btn-outline-primary btn-sm" @click="fetchTracking(detailItem.bl)" :disabled="trackingLoading">
                <span v-if="trackingLoading" class="spinner-border spinner-border-sm me-1"></span>
                {{ trackingLoading ? 'A rastrear...' : 'Actualizar' }}
              </button>
            </div>
            <div v-if="trackingData">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge" :class="trackingStatusClass">{{ trackingData.fmliderStatusLabel }}</span>
                <small class="text-muted">{{ trackingData.carrier }}</small>
              </div>
              <div v-if="trackingData.events && trackingData.events.length > 0" class="tracking-timeline-mini">
                <div v-for="(evt, idx) in trackingData.events.slice(0, 5)" :key="idx" class="timeline-mini-item">
                  <div class="timeline-mini-dot" :class="{ 'active': idx === 0 }"></div>
                  <div class="timeline-mini-content">
                    <strong>{{ evt.status }}</strong>
                    <span v-if="evt.location" class="text-muted ms-1">- {{ evt.location }}</span>
                    <div v-if="evt.date" class="text-muted" style="font-size:0.8rem">{{ formatDate(evt.date) }}</div>
                  </div>
                </div>
              </div>
              <div v-else-if="!trackingLoading" class="text-muted" style="font-size:0.9rem">
                {{ trackingData.message || 'Nenhum evento de rastreamento encontrado.' }}
              </div>
            </div>
            <div v-else-if="!trackingLoading" class="text-muted" style="font-size:0.9rem">
              Clique em "Actualizar" para ver a localização actual do contentor.
            </div>
          </div>

          <!-- Timeline -->
          <div class="mt-4">
            <h6><i class="bi bi-list-ol me-1"></i> Progresso</h6>
            <div class="timeline">
              <div v-for="(e, idx) in estados" :key="e.value" class="timeline-item" :class="{ active: isEstadoActive(e.value, detailItem), current: e.value === detailItem.estado }">
                <div class="timeline-dot">
                  <i v-if="isEstadoActive(e.value, detailItem)" class="bi bi-check-lg"></i>
                  <i v-else-if="e.value === detailItem.estado" class="bi bi-arrow-right"></i>
                </div>
                <div class="timeline-content">
                  <span class="timeline-label">{{ e.label }}</span>
                  <span v-if="e.value === detailItem.estado" class="badge bg-primary ms-2">Actual</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDetail">Fechar</button>
        </div>
      </div>
    </div>

    <div v-if="toast.show" class="toast-notification" :class="'toast-' + toast.type">
      <i :class="toast.icon"></i> {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()

const loading = ref(true)
const items = ref([])
const currentPage = ref(1)
const perPage = 20
const totalCount = ref(0)

const showDetailModal = ref(false)
const detailItem = ref(null)
const toast = ref({ show: false, message: '', type: 'success', icon: 'bi bi-check-circle' })
const filters = reactive({ q: '', estado: '' })

const trackingLoading = ref(false)
const trackingData = ref(null)
const trackingStatusClass = ref('bg-secondary')

const estados = [
  { value: 'pedir_proforma', label: 'Pedir Proforma' },
  { value: 'bl_em_legalizacao', label: 'BL em Legalização' },
  { value: 'com_aduane_submeter', label: 'Com Aduane/Submeter' },
  { value: 'a_submeter', label: 'A Submeter' },
  { value: 'submetido', label: 'Submetido' },
  { value: 'ep14_por_taxar', label: 'EP14 Por Taxar' },
  { value: 'ep17_taxar_semi_pronto', label: 'EP17 Taxar/Semi Pronto' },
  { value: 'ep17_comprovativo', label: 'EP17 Comprovativo' },
  { value: 'ep17_pago_comp_ok', label: 'EP17 Pago/Comp OK' },
  { value: 'dar_saida_pronto', label: 'Dar Saída/Pronto' }
]

const stats = computed(() => {
  const all = items.value
  const prontoStates = ['ep17_pago_comp_ok', 'dar_saida_pronto']
  return {
    total: all.length,
    em_curso: all.filter(i => !prontoStates.includes(i.estado)).length,
    pronto: all.filter(i => prontoStates.includes(i.estado)).length
  }
})

const totalPages = computed(() => Math.ceil(totalCount.value / perPage))

let debounceTimer = null
const debounceSearch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { currentPage.value = 1; fetchData() }, 300)
}

const fetchData = async () => {
  loading.value = true
  try {
    let query = supabase.from('processos').select('*', { count: 'exact' }).eq('cliente_id', authStore.user?.id)
    if (filters.q) {
      query = query.or(`file_number.ilike.%${filters.q}%,ref_cliente.ilike.%${filters.q}%`)
    }
    if (filters.estado) query = query.eq('estado', filters.estado)
    const from = (currentPage.value - 1) * perPage
    const { data, count, error } = await query.order('created_at', { ascending: false }).range(from, from + perPage - 1)
    if (error) throw error
    items.value = data || []
    totalCount.value = count || 0
  } catch (e) { console.error(e) }
  loading.value = false
}

const estadoLabel = (val) => estados.find(e => e.value === val)?.label || val
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-AO') : ''
const formatDateTime = (d) => d ? new Date(d).toLocaleString('pt-AO') : ''
const truncate = (s, n) => s && s.length > n ? s.substring(0, n) + '...' : s

const isEstadoActive = (estadoVal, item) => {
  const idx = estados.findIndex(e => e.value === estadoVal)
  const currentIdx = estados.findIndex(e => e.value === item.estado)
  return idx < currentIdx
}

const openDetail = (item) => { detailItem.value = item; showDetailModal.value = true; trackingData.value = null }
const closeDetail = () => { showDetailModal.value = false; detailItem.value = null; trackingData.value = null }

const fetchTracking = async (bl) => {
  if (!bl) return
  trackingLoading.value = true
  trackingData.value = null
  try {
    const apiBase = import.meta.env.VITE_API_URL || ''
    const res = await fetch(`${apiBase}/api/tracking/process/${encodeURIComponent(bl)}`)
    const data = await res.json()
    if (data.success && data.data) {
      trackingData.value = data.data
      const status = data.data.fmliderStatus
      if (status === 'entregue') trackingStatusClass.value = 'bg-success'
      else if (status === 'na_base') trackingStatusClass.value = 'bg-primary'
      else if (status === 'em_transporte') trackingStatusClass.value = 'bg-warning text-dark'
      else if (status === 'em_transito') trackingStatusClass.value = 'bg-info text-dark'
      else if (status === 'chegou_ao_porto') trackingStatusClass.value = 'bg-secondary'
      else trackingStatusClass.value = 'bg-light text-dark'
    }
  } catch (e) {
    trackingData.value = { message: 'Erro ao buscar dados de rastreamento.' }
  } finally {
    trackingLoading.value = false
  }
}
const changePage = (p) => { currentPage.value = p; fetchData() }

onMounted(() => { fetchData() })
</script>

<style scoped>
.cliente-page { max-width: 1000px; margin: 0 auto; }
.page-header h2 { font-size: 1.5rem; font-weight: 700; color: #050505; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; }
.stat-card { background: #fff; border: 1px solid #e4e6eb; border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px; transition: box-shadow 0.2s; }
.stat-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.stat-info { display: flex; flex-direction: column; }
.stat-value { font-size: 1.4rem; font-weight: 700; color: #050505; line-height: 1.2; }
.stat-label { font-size: 0.78rem; color: #65676b; }
.card { border: 1px solid #e4e6eb; border-radius: 12px; }
.card-body { padding: 20px; }
.filters { display: flex; gap: 12px; flex-wrap: wrap; }
.search-box { display: flex; align-items: center; gap: 8px; background: #f0f2f5; border-radius: 8px; padding: 0 12px; flex: 1; min-width: 200px; }
.search-box input { border: none; background: transparent; padding: 10px 0; width: 100%; outline: none; }
.search-box i { color: #65676b; }
.form-select { border: 1px solid #e4e6eb; border-radius: 8px; padding: 8px 12px; font-size: 0.875rem; min-width: 140px; }
.processo-card { background: #fff; border: 1px solid #e4e6eb; border-radius: 12px; padding: 16px; cursor: pointer; transition: all 0.2s; }
.processo-card:hover { border-color: #1877f2; box-shadow: 0 2px 12px rgba(24,119,242,0.1); }
.processo-icon { width: 40px; height: 40px; border-radius: 10px; background: #e8f0fe; color: #1877f2; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.processo-title { font-size: 0.95rem; font-weight: 600; }
.processo-meta { font-size: 0.8rem; color: #65676b; }
.processo-desc { border-top: 1px solid #f0f2f5; padding-top: 8px; }
.tracking-code { font-size: 0.85rem; font-weight: 600; color: #1a365d; background: #e8f0fe; padding: 2px 8px; border-radius: 4px; }
.status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; white-space: nowrap; }
.status-pedir_proforma { background: #fef3c7; color: #92400e; }
.status-bl_em_legalizacao { background: #e0e7ff; color: #3730a3; }
.status-com_aduane_submeter { background: #dbeafe; color: #1e40af; }
.status-a_submeter { background: #cffafe; color: #155e75; }
.status-submetido { background: #ede9fe; color: #6d28d9; }
.status-ep14_por_taxar { background: #fce7f3; color: #9d174d; }
.status-ep17_taxar_semi_pronto { background: #fef3c7; color: #92400e; }
.status-ep17_comprovativo { background: #d1fae5; color: #065f46; }
.status-ep17_pago_comp_ok { background: #d1fae5; color: #047857; }
.status-dar_saida_pronto { background: #bbf7d0; color: #166534; }
.pagination-bar { display: flex; justify-content: space-between; align-items: center; padding: 12px 0 0; }
.page-btn { width: 32px; height: 32px; border: 1px solid #e4e6eb; border-radius: 8px; background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal-content { background: #fff; border-radius: 16px; width: 100%; max-width: 800px; max-height: 85vh; overflow-y: auto; }
.modal-header { padding: 20px 24px; border-bottom: 1px solid #e4e6eb; display: flex; justify-content: space-between; align-items: center; }
.modal-header h5 { margin: 0; font-size: 1.1rem; font-weight: 700; }
.modal-body { padding: 24px; }
.modal-footer { padding: 16px 24px; border-top: 1px solid #e4e6eb; display: flex; justify-content: flex-end; gap: 8px; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.detail-section h6 { font-size: 0.85rem; font-weight: 700; color: #050505; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #f0f2f5; }
.detail-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.85rem; }
.detail-label { color: #65676b; }
.timeline { position: relative; padding-left: 24px; }
.timeline::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 2px; background: #e4e6eb; }
.timeline-item { position: relative; padding: 8px 0; display: flex; align-items: center; gap: 12px; }
.timeline-dot { width: 18px; height: 18px; border-radius: 50%; background: #e4e6eb; display: flex; align-items: center; justify-content: center; position: absolute; left: -24px; font-size: 0.65rem; color: #fff; z-index: 1; }
.timeline-item.active .timeline-dot { background: #059669; }
.timeline-item.current .timeline-dot { background: #1877f2; }
.timeline-label { font-size: 0.85rem; color: #65676b; }
.timeline-item.active .timeline-label { color: #059669; font-weight: 600; }
.timeline-item.current .timeline-label { color: #1877f2; font-weight: 700; }
.toast-notification { position: fixed; top: 20px; right: 20px; z-index: 3000; padding: 12px 20px; border-radius: 8px; color: #fff; font-size: 0.9rem; font-weight: 500; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.toast-success { background: #059669; }
.toast-error { background: #dc3545; }
.tracking-panel { background: #f8f9fa; border: 1px solid #e4e6eb; border-radius: 12px; padding: 16px; }
.tracking-timeline-mini { margin-top: 8px; padding-left: 8px; border-left: 2px solid #dee2e6; }
.timeline-mini-item { position: relative; padding: 6px 0 6px 16px; }
.timeline-mini-dot { position: absolute; left: -9px; top: 10px; width: 10px; height: 10px; border-radius: 50%; background: #dee2e6; border: 2px solid #fff; }
.timeline-mini-dot.active { background: #d4af37; }
.timeline-mini-content { font-size: 0.85rem; }
@media (max-width: 768px) {
  .cliente-page { padding: 16px !important; }
  .stats-grid { grid-template-columns: repeat(3, 1fr); }
  .filters { flex-direction: column; }
  .detail-grid { grid-template-columns: 1fr; }
}
</style>
