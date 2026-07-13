<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2>Processos</h2>
          <p class="text-muted mb-0">Acompanhamento de processos de desembaraço aduaneiro</p>
        </div>
      </div>
    </div>

    <div class="stats-grid mb-4">
      <div class="stat-card"><div class="stat-icon" style="background:#dbeafe;color:#1e40af"><i class="bi bi-folder2-open"></i></div><div class="stat-info"><span class="stat-value">{{ stats.total }}</span><span class="stat-label">Total</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#fef3c7;color:#92400e"><i class="bi bi-hourglass-split"></i></div><div class="stat-info"><span class="stat-value">{{ stats.pedir_proforma }}</span><span class="stat-label">Pedir Proforma</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#cffafe;color:#155e75"><i class="bi bi-send"></i></div><div class="stat-info"><span class="stat-value">{{ stats.a_submeter }}</span><span class="stat-label">A Submeter</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#ede9fe;color:#6d28d9"><i class="bi bi-check2-all"></i></div><div class="stat-info"><span class="stat-value">{{ stats.submetido }}</span><span class="stat-label">Submetido</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#d1fae5;color:#065f46"><i class="bi bi-check-circle"></i></div><div class="stat-info"><span class="stat-value">{{ stats.pronto }}</span><span class="stat-label">Pronto</span></div></div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box"><i class="bi bi-search"></i><input v-model="filters.q" type="text" placeholder="Pesquisar por file, referência, BL..." @input="debounceSearch"></div>
          <select v-model="filters.estado" class="form-select" @change="fetchData">
            <option value="">Todos os estados</option>
            <option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option>
          </select>
          <select v-model="filters.tipo" class="form-select" @change="fetchData">
            <option value="">Todos os tipos</option>
            <option value="SECO">Seco</option>
            <option value="CONG">Congelado</option>
          </select>
        </div>

        <div v-if="loading" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">Nenhum processo encontrado.</div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>File</th><th>Ref. Cliente</th><th>Importador</th><th>Agência</th><th>BL</th><th>ETA</th><th>Estado</th><th>Ações</th></tr></thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td><code class="tracking-code">{{ item.file_number }}</code></td>
                <td>{{ item.ref_cliente || '' }}</td>
                <td>{{ item.importador || '' }}</td>
                <td>{{ item.agencia || '' }}</td>
                <td><small>{{ item.bl || '' }}</small></td>
                <td><small class="text-muted">{{ formatDate(item.eta) }}</small></td>
                <td><span class="status-badge" :class="'status-' + item.estado">{{ estadoLabel(item.estado) }}</span></td>
                <td>
                  <div class="action-buttons">
                    <button class="btn-icon btn-info" @click="openDetail(item)" title="Ver Detalhes"><i class="bi bi-eye"></i></button>
                    <button v-if="isDoc" class="btn-icon btn-edit" @click="openEdit(item)" title="Actualizar Estado"><i class="bi bi-pencil-square"></i></button>
                  </div>
                </td>
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
    <div v-if="showDetailModal" class="modal-overlay" @click.self="closeDetail">
      <div class="modal-content modal-xl">
        <div class="modal-header"><h5>Detalhes do Processo</h5><button class="btn-close" @click="closeDetail"></button></div>
        <div class="modal-body" v-if="detailItem">
          <div class="detail-grid">
            <div class="detail-section">
              <h6><i class="bi bi-info-circle me-1"></i> Identificação</h6>
              <div class="detail-row"><span class="detail-label">File</span><code>{{ detailItem.file_number }}</code></div>
              <div class="detail-row"><span class="detail-label">Ref. Cliente</span><span>{{ detailItem.ref_cliente || '' }}</span></div>
              <div class="detail-row"><span class="detail-label">Tipo</span><span>{{ detailItem.tipo || '' }}</span></div>
              <div class="detail-row"><span class="detail-label">Importador</span><span>{{ detailItem.importador || '' }}</span></div>
            </div>
            <div class="detail-section">
              <h6><i class="bi bi-ship me-1"></i> Transporte</h6>
              <div class="detail-row"><span class="detail-label">Agência</span><span>{{ detailItem.agencia || '' }}</span></div>
              <div class="detail-row"><span class="detail-label">BL</span><span>{{ detailItem.bl || '' }}</span></div>
              <div class="detail-row"><span class="detail-label">Typo</span><span class="badge bg-info">{{ detailItem.typo || '' }}</span></div>
            </div>
            <div class="detail-section">
              <h6><i class="bi bi-calendar me-1"></i> Datas</h6>
              <div class="detail-row"><span class="detail-label">ETA</span><span>{{ formatDate(detailItem.eta) }}</span></div>
              <div class="detail-row"><span class="detail-label">ATA</span><span>{{ formatDate(detailItem.ata) }}</span></div>
              <div class="detail-row"><span class="detail-label">Nº Dias</span><span>{{ detailItem.n_dias ?? '' }}</span></div>
            </div>
            <div class="detail-section">
              <h6><i class="bi bi-gear me-1"></i> Estado</h6>
              <div class="detail-row"><span class="detail-label">Estado</span><span class="status-badge" :class="'status-' + detailItem.estado">{{ estadoLabel(detailItem.estado) }}</span></div>
              <div class="detail-row"><span class="detail-label">Criado</span><span>{{ formatDateTime(detailItem.created_at) }}</span></div>
            </div>
          </div>
          <div v-if="detailItem.estado_legalizacao" class="mt-3">
            <h6><i class="bi bi-text-left me-1"></i> Estado Legalização</h6>
            <p class="text-muted">{{ detailItem.estado_legalizacao }}</p>
          </div>
          <div v-if="detailItem.observacoes" class="mt-3">
            <h6><i class="bi bi-sticky me-1"></i> Observações</h6>
            <p class="text-muted">{{ detailItem.observacoes }}</p>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDetail">Fechar</button>
          <button v-if="isDoc" class="btn btn-primary" @click="closeDetail(); openEdit(detailItem)">Actualizar Estado</button>
        </div>
      </div>
    </div>

    <!-- Edit Status Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content" style="max-width:500px">
        <div class="modal-header"><h5>Actualizar Estado</h5><button class="btn-close" @click="closeModal"></button></div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">File: <code>{{ editingItem?.file_number }}</code></label>
          </div>
          <div class="mb-3">
            <label class="form-label">Estado Actual: <span class="status-badge" :class="'status-' + editingItem?.estado">{{ estadoLabel(editingItem?.estado) }}</span></label>
          </div>
          <div class="mb-3">
            <label class="form-label">Novo Estado <span class="text-danger">*</span></label>
            <select v-model="newEstado" class="form-select">
              <option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Observações</label>
            <textarea v-model="newObservacoes" class="form-control" rows="3" placeholder="Observações sobre a mudança de estado..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeModal">Cancelar</button>
          <button class="btn btn-primary" @click="updateEstado" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>Guardar
          </button>
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
const isDoc = computed(() => authStore.user?.departamento === 'documentacao' || authStore.isAdmin)

const loading = ref(true)
const saving = ref(false)
const items = ref([])
const currentPage = ref(1)
const perPage = 10
const totalCount = ref(0)

const showModal = ref(false)
const showDetailModal = ref(false)
const editingItem = ref(null)
const detailItem = ref(null)
const newEstado = ref('')
const newObservacoes = ref('')

const toast = ref({ show: false, message: '', type: 'success', icon: 'bi bi-check-circle' })

const filters = reactive({ q: '', estado: '', tipo: '' })

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
  return {
    total: all.length,
    pedir_proforma: all.filter(i => i.estado === 'pedir_proforma').length,
    a_submeter: all.filter(i => ['a_submeter', 'com_aduane_submeter'].includes(i.estado)).length,
    submetido: all.filter(i => ['submetido', 'ep14_por_taxar', 'ep17_taxar_semi_pronto'].includes(i.estado)).length,
    pronto: all.filter(i => ['ep17_pago_comp_ok', 'dar_saida_pronto'].includes(i.estado)).length
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
    let query = supabase.from('processos').select('*', { count: 'exact' })
    if (filters.q) {
      query = query.or(`file_number.ilike.%${filters.q}%,ref_cliente.ilike.%${filters.q}%,bl.ilike.%${filters.q}%,importador.ilike.%${filters.q}%`)
    }
    if (filters.estado) query = query.eq('estado', filters.estado)
    if (filters.tipo) query = query.eq('tipo', filters.tipo)
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

const showToast = (message, type = 'success') => {
  const icons = { success: 'bi bi-check-circle', error: 'bi bi-x-circle' }
  toast.value = { show: true, message, type, icon: icons[type] || icons.success }
  setTimeout(() => { toast.value.show = false }, 3000)
}

const openDetail = (item) => { detailItem.value = item; showDetailModal.value = true }
const closeDetail = () => { showDetailModal.value = false; detailItem.value = null }

const openEdit = (item) => {
  editingItem.value = item
  newEstado.value = item.estado
  newObservacoes.value = ''
  showModal.value = true
}

const closeModal = () => { showModal.value = false; editingItem.value = null }

const updateEstado = async () => {
  saving.value = true
  try {
    const update = { estado: newEstado.value }
    if (newObservacoes.value) update.observacoes = newObservacoes.value
    const { error } = await supabase.from('processos').update(update).eq('id', editingItem.value.id)
    if (error) throw error
    showToast('Estado actualizado com sucesso')
    closeModal()
    await fetchData()
  } catch (e) { showToast(e.message || 'Erro ao actualizar', 'error') }
  saving.value = false
}

const changePage = (p) => { currentPage.value = p; fetchData() }

onMounted(() => { fetchData() })
</script>

<style scoped>
.admin-page { max-width: 1400px; margin: 0 auto; }
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
.form-select, .form-control { border: 1px solid #e4e6eb; border-radius: 8px; padding: 8px 12px; font-size: 0.875rem; }
.table { margin-bottom: 0; }
.table th { font-size: 0.8rem; font-weight: 600; color: #65676b; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 1px solid #e4e6eb; padding: 10px 12px; }
.table td { padding: 10px 12px; vertical-align: middle; font-size: 0.9rem; }
.table tbody tr:hover { background: #f8f9fa; }
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
.action-buttons { display: flex; gap: 4px; }
.btn-icon { width: 32px; height: 32px; border: 1px solid #e4e6eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #fff; cursor: pointer; transition: all 0.15s; font-size: 0.85rem; }
.btn-icon:hover { background: #f0f2f5; }
.btn-edit { color: #1877f2; }
.btn-info { color: #0dcaf0; }
.pagination-bar { display: flex; justify-content: space-between; align-items: center; padding: 12px 0 0; }
.page-btn { width: 32px; height: 32px; border: 1px solid #e4e6eb; border-radius: 8px; background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal-content { background: #fff; border-radius: 16px; width: 100%; max-width: 800px; max-height: 85vh; overflow-y: auto; }
.modal-header { padding: 20px 24px; border-bottom: 1px solid #e4e6eb; display: flex; justify-content: space-between; align-items: center; }
.modal-header h5 { margin: 0; font-size: 1.1rem; font-weight: 700; }
.modal-body { padding: 24px; }
.modal-footer { padding: 16px 24px; border-top: 1px solid #e4e6eb; display: flex; justify-content: flex-end; gap: 8px; }
.form-label { font-size: 0.8rem; font-weight: 600; color: #65676b; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.detail-section h6 { font-size: 0.85rem; font-weight: 700; color: #050505; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #f0f2f5; }
.detail-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.85rem; }
.detail-label { color: #65676b; }
.toast-notification { position: fixed; top: 20px; right: 20px; z-index: 3000; padding: 12px 20px; border-radius: 8px; color: #fff; font-size: 0.9rem; font-weight: 500; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.toast-success { background: #059669; }
.toast-error { background: #dc3545; }
@media (max-width: 768px) {
  .admin-page { padding: 16px !important; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .filters { flex-direction: column; }
  .detail-grid { grid-template-columns: 1fr; }
}
</style>
