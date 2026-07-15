<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2>Plano de Trabalho</h2>
          <p class="text-muted mb-0">Gestão de processos de desembaraço aduaneiro</p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
          <select v-model="importClientId" class="form-select form-select-sm" style="max-width:200px">
            <option value="">Sem cliente</option>
            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <label class="btn btn-success btn-sm mb-0">
            <i class="bi bi-file-earmark-excel me-1"></i>Importar Plano
            <input type="file" accept=".xlsx,.xls" class="d-none" @change="handleImport">
          </label>
          <button class="btn btn-primary btn-sm" @click="openCreate">
            <i class="bi bi-plus-lg me-1"></i>Novo Processo
          </button>
        </div>
      </div>
    </div>

    <div v-if="importing" class="alert alert-info d-flex align-items-center mb-4">
      <div class="spinner-border spinner-border-sm me-2" role="status"></div>
      <span>A importar processos... {{ importProgress }}</span>
    </div>

    <div v-if="importResult" class="alert mb-4" :class="importResult.success ? 'alert-success' : 'alert-warning'">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <strong>{{ importResult.title }}</strong>
          <ul class="mb-0 mt-1">
            <li v-for="(msg, i) in importResult.messages" :key="i">{{ msg }}</li>
          </ul>
        </div>
        <button class="btn-close" @click="importResult = null"></button>
      </div>
    </div>

    <div class="stats-grid mb-4">
      <div class="stat-card"><div class="stat-icon" style="background:#dbeafe;color:#1e40af"><i class="bi bi-folder2-open"></i></div><div class="stat-info"><span class="stat-value">{{ stats.total }}</span><span class="stat-label">Total</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#fef3c7;color:#92400e"><i class="bi bi-hourglass-split"></i></div><div class="stat-info"><span class="stat-value">{{ stats.pedir_proforma }}</span><span class="stat-label">Pedir Proforma</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#e0e7ff;color:#3730a3"><i class="bi bi-file-earmark-check"></i></div><div class="stat-info"><span class="stat-value">{{ stats.bl_em_leg }}</span><span class="stat-label">BL em Legalização</span></div></div>
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
          <select v-model="filters.assigned_to" class="form-select" @change="fetchData">
            <option value="">Sem atribuição</option>
            <option v-for="f in funcionarios" :key="f.id" :value="f.auth_id">{{ f.name }}</option>
          </select>
          <select v-model="filters.cliente_id" class="form-select" @change="fetchData">
            <option value="">Todos os clientes</option>
            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>

        <div v-if="loading" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">Nenhum processo encontrado.</div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>File</th><th>Ref. Cliente</th><th>Importador</th><th>Agência</th><th>BL</th><th>ETA</th><th>Estado</th><th>Atribuído</th><th>Ações</th></tr></thead>
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
                  <span v-if="item.assigned_to" class="badge bg-primary">{{ getAssignedName(item.assigned_to) }}</span>
                  <span v-else class="text-muted"></span>
                </td>
                <td>
                  <div class="action-buttons">
                    <button class="btn-icon btn-info" @click="openDetail(item)" title="Ver Detalhes"><i class="bi bi-eye"></i></button>
                    <button class="btn-icon btn-edit" @click="openEdit(item)" title="Editar"><i class="bi bi-pencil-square"></i></button>
                    <button class="btn-icon btn-delete" @click="openDelete(item)" title="Eliminar"><i class="bi bi-trash3"></i></button>
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

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content modal-xl">
        <div class="modal-header"><h5>{{ editingItem ? 'Editar Processo' : 'Novo Processo' }}</h5><button class="btn-close" @click="closeModal"></button></div>
        <div class="modal-body">
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-info-circle me-1"></i> Identificação</h6>
            <div class="row g-3">
              <div class="col-md-3"><label class="form-label">File Number <span class="text-danger">*</span></label><input v-model="form.file_number" type="text" class="form-control" placeholder="Ex: 26100067"></div>
              <div class="col-md-3"><label class="form-label">Ref. Cliente</label><input v-model="form.ref_cliente" type="text" class="form-control" placeholder="Ex: 2025M126"></div>
              <div class="col-md-3"><label class="form-label">Tipo</label><select v-model="form.tipo" class="form-select"><option value="">Selecione</option><option value="SECO">Seco</option><option value="CONG">Congelado</option></select></div>
              <div class="col-md-3"><label class="form-label">Importador</label><input v-model="form.importador" type="text" class="form-control" placeholder="Ex: ACCORD"></div>
            </div>
          </div>
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-ship me-1"></i> Transporte Marítimo</h6>
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label">Agência</label><input v-model="form.agencia" type="text" class="form-control" placeholder="Ex: MSC, HAPAG..."></div>
              <div class="col-md-4"><label class="form-label">BL</label><input v-model="form.bl" type="text" class="form-control" placeholder="Bill of Lading"></div>
              <div class="col-md-4"><label class="form-label">BL Draft</label><input v-model="form.bl_draft" type="text" class="form-control" placeholder="BL Draft"></div>
            </div>
          </div>
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-calendar me-1"></i> Datas</h6>
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label">ETA</label><input v-model="form.eta" type="date" class="form-control"></div>
              <div class="col-md-4"><label class="form-label">ATA</label><input v-model="form.ata" type="date" class="form-control"></div>
              <div class="col-md-4"><label class="form-label">Nº Dias</label><input v-model.number="form.n_dias" type="number" class="form-control"></div>
            </div>
          </div>
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-gear me-1"></i> Estado</h6>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Estado</label>
                <select v-model="form.estado" class="form-select">
                  <option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option>
                </select>
              </div>
              <div class="col-md-4"><label class="form-label">Typo (Estado Actual)</label><input v-model="form.typo" type="text" class="form-control" placeholder="Ex: FT IMP EM KWANZA"></div>
              <div class="col-md-4">
                <label class="form-label">Atribuir A</label>
                <select v-model="form.assigned_to" class="form-select">
                  <option value="">Sem atribuição</option>
                  <option v-for="f in funcionarios" :key="f.id" :value="f.auth_id">{{ f.name }}</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-text-left me-1"></i> Observações</h6>
            <div class="row g-3">
              <div class="col-md-12"><label class="form-label">Estado Legalização</label><textarea v-model="form.estado_legalizacao" class="form-control" rows="2" placeholder="Descrição do estado de legalização"></textarea></div>
              <div class="col-md-12"><label class="form-label">Observações</label><textarea v-model="form.observacoes" class="form-control" rows="2" placeholder="Observações gerais"></textarea></div>
            </div>
          </div>
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-person me-1"></i> Cliente</h6>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Associar a Cliente</label>
                <select v-model="form.cliente_id" class="form-select">
                  <option value="">Sem cliente associado</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <small class="text-muted">Associe este processo a um cliente existente</small>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeModal">Cancelar</button>
          <button class="btn btn-primary" @click="saveItem" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
            {{ editingItem ? 'Guardar' : 'Criar' }}
          </button>
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
              <div class="detail-row"><span class="detail-label">Atribuído</span><span>{{ getAssignedName(detailItem.assigned_to) }}</span></div>
              <div class="detail-row"><span class="detail-label">Criado</span><span>{{ formatDateTime(detailItem.created_at) }}</span></div>
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
                <small v-if="trackingData.cached" class="text-muted">(dados de {{ formatDate(trackingData.cachedAt) }})</small>
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
              Clique em "Actualizar" para buscar dados de rastreamento do BL <code>{{ detailItem.bl }}</code>
            </div>
          </div>

          <div v-if="detailItem.observacoes" class="mt-3">
            <h6><i class="bi bi-sticky me-1"></i> Observações</h6>
            <p class="text-muted">{{ detailItem.observacoes }}</p>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDetail">Fechar</button>
          <button class="btn btn-primary" @click="closeDetail(); openEdit(detailItem)">Editar</button>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDelete">
      <div class="modal-content modal-sm">
        <div class="modal-header"><h5>Confirmar Eliminação</h5><button class="btn-close" @click="closeDelete"></button></div>
        <div class="modal-body">
          <p>Tem certeza que deseja eliminar o processo <strong>{{ deleteItem?.file_number }}</strong>?</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDelete">Cancelar</button>
          <button class="btn btn-danger" @click="deleteItemConfirm">Eliminar</button>
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
const saving = ref(false)
const items = ref([])
const clients = ref([])
const funcionarios = ref([])
const currentPage = ref(1)
const perPage = 10
const totalCount = ref(0)

const showModal = ref(false)
const showDetailModal = ref(false)
const showDeleteModal = ref(false)
const editingItem = ref(null)
const detailItem = ref(null)
const deleteItem = ref(null)

const trackingLoading = ref(false)
const trackingData = ref(null)
const trackingStatusClass = ref('bg-secondary')

const importing = ref(false)
const importProgress = ref('')
const importResult = ref(null)
const importClientId = ref('')

const toast = ref({ show: false, message: '', type: 'success', icon: 'bi bi-check-circle' })

const filters = reactive({ q: '', estado: '', tipo: '', assigned_to: '', cliente_id: '' })

const form = reactive({
  file_number: '', ref_cliente: '', tipo: '', importador: '', agencia: '', bl: '', bl_draft: '',
  typo: '', eta: '', ata: '', n_dias: null, estado: 'pedir_proforma', observacoes: '', estado_legalizacao: '',
  assigned_to: '', cliente_id: ''
})

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

const stats = reactive({ total: 0, pedir_proforma: 0, bl_em_leg: 0, a_submeter: 0, submetido: 0, pronto: 0 })

const totalPages = computed(() => Math.ceil(totalCount.value / perPage))

let debounceTimer = null
const debounceSearch = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { currentPage.value = 1; fetchData() }, 300)
}

const fetchStats = async () => {
  try {
    const { data } = await supabase.from('processos').select('estado')
    if (!data) return
    stats.total = data.length
    stats.pedir_proforma = data.filter(i => i.estado === 'pedir_proforma').length
    stats.bl_em_leg = data.filter(i => i.estado === 'bl_em_legalizacao').length
    stats.a_submeter = data.filter(i => ['a_submeter', 'com_aduane_submeter'].includes(i.estado)).length
    stats.submetido = data.filter(i => ['submetido', 'ep14_por_taxar', 'ep17_taxar_semi_pronto'].includes(i.estado)).length
    stats.pronto = data.filter(i => ['ep17_pago_comp_ok', 'dar_saida_pronto'].includes(i.estado)).length
  } catch (e) { console.error('Erro ao buscar stats:', e) }
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
    if (filters.assigned_to) query = query.eq('assigned_to', filters.assigned_to)
    if (filters.cliente_id) query = query.eq('cliente_id', filters.cliente_id)
    const from = (currentPage.value - 1) * perPage
    const { data, count, error } = await query.order('created_at', { ascending: false }).range(from, from + perPage - 1)
    if (error) throw error
    items.value = data || []
    totalCount.value = count || 0
  } catch (e) { console.error(e) }
  loading.value = false
}

const fetchClients = async () => {
  const { data } = await supabase.from('users').select('id, name, auth_id, role').eq('role', 'cliente').order('name')
  clients.value = data || []
}

const fetchFuncionarios = async () => {
  const { data } = await supabase.from('users').select('id, name, auth_id, departamento').in('role', ['funcionario', 'admin']).order('name')
  funcionarios.value = data || []
}

const getClientName = (id) => {
  if (!id) return ''
  const c = clients.value.find(cl => cl.id === id || cl.auth_id === id)
  return c?.name || ''
}

const getAssignedName = (authId) => {
  if (!authId) return ''
  const f = funcionarios.value.find(fn => fn.auth_id === authId)
  return f?.name || ''
}

const estadoLabel = (val) => {
  return estados.find(e => e.value === val)?.label || val
}

const formatDate = (d) => {
  if (!d) return ''
  return new Date(d).toLocaleDateString('pt-AO')
}

const formatDateTime = (d) => {
  if (!d) return ''
  return new Date(d).toLocaleString('pt-AO')
}

const showToast = (message, type = 'success') => {
  const icons = { success: 'bi bi-check-circle', error: 'bi bi-x-circle', warning: 'bi bi-exclamation-triangle', info: 'bi bi-info-circle' }
  toast.value = { show: true, message, type, icon: icons[type] || icons.success }
  setTimeout(() => { toast.value.show = false }, 3000)
}

const openCreate = () => {
  editingItem.value = null
  Object.assign(form, { file_number: '', ref_cliente: '', tipo: '', importador: '', agencia: '', bl: '', bl_draft: '', typo: '', eta: '', ata: '', n_dias: null, estado: 'pedir_proforma', observacoes: '', estado_legalizacao: '', assigned_to: '', cliente_id: '' })
  showModal.value = true
}

const openEdit = (item) => {
  editingItem.value = item
  Object.assign(form, {
    file_number: item.file_number || '', ref_cliente: item.ref_cliente || '', tipo: item.tipo || '',
    importador: item.importador || '', agencia: item.agencia || '', bl: item.bl || '', bl_draft: item.bl_draft || '',
    typo: item.typo || '', eta: item.eta ? item.eta.split('T')[0] : '', ata: item.ata ? item.ata.split('T')[0] : '',
    n_dias: item.n_dias, estado: item.estado || 'pedir_proforma', observacoes: item.observacoes || '',
    estado_legalizacao: item.estado_legalizacao || '', assigned_to: item.assigned_to || '', cliente_id: item.cliente_id || ''
  })
  showModal.value = true
}

const openDetail = (item) => { detailItem.value = item; showDetailModal.value = true; trackingData.value = null }
const openDelete = (item) => { deleteItem.value = item; showDeleteModal.value = true }
const closeModal = () => { showModal.value = false; editingItem.value = null }
const closeDetail = () => { showDetailModal.value = false; detailItem.value = null; trackingData.value = null }
const closeDelete = () => { showDeleteModal.value = false; deleteItem.value = null }

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

const saveItem = async () => {
  if (!form.file_number) { showToast('File Number é obrigatório', 'error'); return }
  saving.value = true
  try {
    const payload = { ...form }
    if (payload.n_dias === '' || payload.n_dias === null) payload.n_dias = null
    if (payload.assigned_to === '') payload.assigned_to = null
    if (payload.cliente_id === '') payload.cliente_id = null
    if (editingItem.value) {
      const { error } = await supabase.from('processos').update(payload).eq('id', editingItem.value.id)
      if (error) throw error
      showToast('Processo actualizado com sucesso')
    } else {
      const { error } = await supabase.from('processos').insert(payload)
      if (error) throw error
      showToast('Processo criado com sucesso')
    }
    closeModal()
    await fetchData()
    await fetchStats()
  } catch (e) { showToast(e.message || 'Erro ao guardar', 'error') }
  saving.value = false
}

const deleteItemConfirm = async () => {
  if (!deleteItem.value) return
  try {
    const { error } = await supabase.from('processos').delete().eq('id', deleteItem.value.id)
    if (error) throw error
    showToast('Processo eliminado')
    closeDelete()
    await fetchData()
    await fetchStats()
  } catch (e) { showToast(e.message || 'Erro ao eliminar', 'error') }
}

const changePage = (p) => { currentPage.value = p; fetchData() }

const handleImport = async (e) => {
  const file = e.target.files[0]
  if (!file) return
  importing.value = true
  importResult.value = null
  importProgress.value = 'A ler ficheiro...'

  try {
    if (!file.name.match(/\.xlsx?$/i)) {
      throw new Error('Formato inválido. Apenas ficheiros .xlsx ou .xls são aceites.')
    }

    const XLSX = await import('xlsx')
    const arrayBuffer = await file.arrayBuffer()
    const workbook = XLSX.read(arrayBuffer, { type: 'array' })
    const ws = workbook.Sheets[workbook.SheetNames[0]]
    if (!ws) { throw new Error('Ficheiro inválido ou vazio') }
    const allRows = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '' })
    if (allRows.length < 3) { throw new Error('Ficheiro vazio ou com poucos dados') }

    const allText = allRows.slice(0, 20).map(r => r.map(c => String(c).toUpperCase()).join(' ')).join(' ')
    const requiredKeywords = ['PLANO', 'TRABALHO']
    const sectionKeywords = ['PEDIR', 'PROFORMA', 'BL', 'SUBMETER', 'LEGALIZACAO', 'ESTADO']
    const missingRequired = requiredKeywords.filter(k => !allText.includes(k))
    const hasSections = sectionKeywords.filter(k => allText.includes(k))

    if (missingRequired.length > 0) {
      throw new Error(`Este ficheiro não é o Plano de Trabalho FMLider. Campos obrigatórios em falta: ${missingRequired.join(', ')}. Verifique se selecionou o ficheiro correcto.`)
    }
    if (hasSections.length < 2) {
      throw new Error(`Este ficheiro não contém as secções esperadas do Plano de Trabalho (PEDIR PROFORMA, BL, SUBMETER, etc.). Verifique se selecionou o ficheiro correcto.`)
    }

    const expectedColumns = ['POS', 'E.T.A', 'ESTADO', 'TYPE', 'AGENCIA', 'BL']
    const headerRow = allRows.find(r => String(r[0]).trim() === 'POS' || String(r[0]).trim() === 'N°')
    if (!headerRow) {
      throw new Error('Não foi encontrada a linha de cabeçalho (POS, FILE, REF.CLIENTE...). Verifique se o ficheiro é o Plano de Trabalho correcto.')
    }
    const headerText = headerRow.map(c => String(c).toUpperCase()).join(' ')
    const foundColumns = expectedColumns.filter(c => headerText.includes(c))
    if (foundColumns.length < 3) {
      throw new Error(`Estrutura de colunas inesperada. Esperava colunas como POS, FILE, E.T.A, ESTADO, TYPE, AGENCIA, BL. Encontrou: ${headerRow.filter(c => c).slice(0, 6).join(', ')}. Verifique se é o Plano de Trabalho correcto.`)
    }

    let sectionCount = 0
    for (const row of allRows) {
      const a = String(row[0] || '').trim()
      const b = String(row[1] || '').trim().toUpperCase()
      if (a === 'POS' || a === 'N°') {
        if (b.includes('PEDIR') || b.includes('BL') || b.includes('SUBMETER') || b.includes('LEGALIZACAO') || b.includes('TAXAR') || b.includes('SAIDA')) {
          sectionCount++
        }
      }
    }
    if (sectionCount < 3) {
      throw new Error(`Apenas ${sectionCount} secção(ões) encontrada(s). O Plano de Trabalho deve ter pelo menos 3 secções (ex: PEDIR PROFORMA, BL EM LEGALIZAÇÃO, A SUBMETER, SUBMETIDO, etc.). Verifique se é o ficheiro correcto.`)
    }

    const processosRaw = []
    let currentSection = 'pedir_proforma'

    const parseDate = (v) => {
      if (!v) return null
      if (v instanceof Date) return v.toISOString().split('T')[0]
      const s = String(v).trim()
      if (s === 'F' || s === '' || s === '0' || s === '0.0' || s.includes('F*')) return null
      const m = s.match(/(\d{4})-(\d{2})-(\d{2})/)
      if (m) return `${m[1]}-${m[2]}-${m[3]}`
      return null
    }

    for (let rowIdx = 2; rowIdx < allRows.length; rowIdx++) {
      const row = allRows[rowIdx]
      const colA = String(row[0] || '').trim()
      const colB = String(row[1] || '').trim()
      const colC = String(row[2] || '').trim()

      if (colA === 'POS' || colA === 'N°') {
        const header = colB.toUpperCase()
        if (header.includes('FT') || header.includes('PEDIR')) currentSection = 'pedir_proforma'
        else if (header.includes('BL') && header.includes('LEG')) currentSection = 'bl_em_legalizacao'
        else if (header.includes('COM ADUANE') || (header.includes('SUBMETER') && !header.includes('A SUBMETER'))) currentSection = 'com_aduane_submeter'
        else if (header === 'A SUBMETER') currentSection = 'a_submeter'
        else if (header === 'SUBMETIDO') currentSection = 'submetido'
        else if (header.includes('EP14')) currentSection = 'ep14_por_taxar'
        else if (header.includes('EP17') && header.includes('TAXAR')) currentSection = 'ep17_taxar_semi_pronto'
        else if (header.includes('COMPROVATIVO') || header.includes('CERTI')) currentSection = 'ep17_comprovativo'
        else if (header.includes('PAGO')) currentSection = 'ep17_pago_comp_ok'
        else if (header.includes('SAIDA') || header.includes('PRONTO')) currentSection = 'dar_saida_pronto'
        else if (header.includes('OUTROS')) currentSection = 'observacoes'
        continue
      }

      if (!colA || !colB || colB.length < 4) continue

      const nDias = parseInt(colA) || null
      const fileNum = colB
      const refCliente = colC || null
      const obs = String(row[6] || '').trim()
      const tipo = String(row[7] || '').trim()
      const imp = String(row[8] || '').trim()
      const agencia = String(row[9] || '').trim()
      const bl = String(row[10] || '').trim()
      const typo = String(row[11] || '').trim()
      const etaRaw = row[4]
      const ataRaw = row[5]

      processosRaw.push({
        file_number: fileNum,
        ref_cliente: refCliente || null,
        tipo: tipo || null,
        importador: imp || null,
        agencia: agencia || null,
        bl: bl || null,
        typo: typo || null,
        eta: parseDate(etaRaw),
        ata: parseDate(ataRaw),
        estado: currentSection,
        estado_legalizacao: obs || null,
        n_dias: nDias,
        observacoes: null,
        cliente_id: null,
        assigned_to: null
      })
    }

    importProgress.value = `A processar ${processosRaw.length} linhas do Excel...`

    const seen = new Map()
    for (const p of processosRaw) {
      if (!seen.has(p.file_number)) {
        seen.set(p.file_number, p)
      }
    }
    const uniqueFromFile = Array.from(seen.values())

    importProgress.value = `A verificar ${uniqueFromFile.length} processos na base de dados...`

    const { data: existingData, error: fetchError } = await supabase.from('processos').select('id, file_number, estado')
    if (fetchError) throw fetchError

    const existingMap = new Map()
    for (const ex of (existingData || [])) {
      existingMap.set(ex.file_number, ex)
    }

    const toInsert = []
    const toUpdate = []
    const unchanged = []

    for (const p of uniqueFromFile) {
      const existing = existingMap.get(p.file_number)
      if (existing) {
        if (existing.estado !== p.estado) {
          toUpdate.push({ id: existing.id, file_number: p.file_number, oldEstado: existing.estado, newEstado: p.estado, data: p })
        } else {
          unchanged.push(p.file_number)
        }
      } else {
        toInsert.push(p)
      }
    }

    let insertedCount = 0
    let updatedCount = 0
    let errorCount = 0

    if (toInsert.length > 0) {
      importProgress.value = `A inserir ${toInsert.length} novos processos...`
      const batchWithClient = toInsert.map(p => ({ ...p, cliente_id: importClientId.value || null }))
      for (let i = 0; i < batchWithClient.length; i += 50) {
        const batch = batchWithClient.slice(i, i + 50)
        const { error } = await supabase.from('processos').insert(batch)
        if (error) { console.error('Insert error:', error); errorCount += batch.length }
        else insertedCount += batch.length
      }
    }

    if (toUpdate.length > 0) {
      importProgress.value = `A actualizar ${toUpdate.length} processos...`
      for (const u of toUpdate) {
        const updatePayload = {
          estado: u.newEstado,
          ref_cliente: u.data.ref_cliente,
          tipo: u.data.tipo,
          importador: u.data.importador,
          agencia: u.data.agencia,
          bl: u.data.bl,
          typo: u.data.typo,
          eta: u.data.eta,
          ata: u.data.ata,
          estado_legalizacao: u.data.estado_legalizacao,
          n_dias: u.data.n_dias
        }
        if (importClientId.value) updatePayload.cliente_id = importClientId.value
        const { error } = await supabase.from('processos').update(updatePayload).eq('id', u.id)
        if (error) { console.error('Update error:', error); errorCount++ }
        else updatedCount++
      }
    }

    const messages = [`Linhas no Excel: ${processosRaw.length}`, `Processos únicos: ${uniqueFromFile.length}`]
    if (insertedCount > 0) messages.push(`Novos inseridos: ${insertedCount}`)
    if (updatedCount > 0) messages.push(`Estados actualizados: ${updatedCount}`)
    if (unchanged.length > 0) messages.push(`Sem alteração: ${unchanged.length}`)
    if (errorCount > 0) messages.push(`Erros: ${errorCount}`)

    importResult.value = {
      success: insertedCount > 0 || updatedCount > 0,
      title: `Importação concluída`,
      messages
    }
    await fetchData()
    await fetchStats()
  } catch (e) {
    console.error('Import error:', e)
    importResult.value = { success: false, title: 'Erro na importação', messages: [e.message || 'Erro desconhecido'] }
  }
  importing.value = false
  e.target.value = ''
}

onMounted(async () => {
  await Promise.all([fetchData(), fetchStats(), fetchClients(), fetchFuncionarios()])
})
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
.form-select { border: 1px solid #e4e6eb; border-radius: 8px; padding: 8px 12px; font-size: 0.875rem; min-width: 140px; }
.table { margin-bottom: 0; }
.table th { font-size: 0.8rem; font-weight: 600; color: #65676b; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 1px solid #e4e6eb; padding: 10px 12px; }
.table td { padding: 10px 12px; vertical-align: middle; font-size: 0.9rem; }
.table tbody tr { transition: background 0.15s; }
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
.btn-delete { color: #dc3545; }
.btn-info { color: #0dcaf0; }
.pagination-bar { display: flex; justify-content: space-between; align-items: center; padding: 12px 0 0; }
.page-info { font-size: 0.85rem; color: #65676b; }
.page-btns { display: flex; gap: 4px; }
.page-btn { width: 32px; height: 32px; border: 1px solid #e4e6eb; border-radius: 8px; background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 20px; overflow-y: auto; }
.modal-content { background: #fff; border-radius: 16px; width: 100%; max-width: 800px; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden; }
.modal-content.modal-sm { max-width: 400px; }
.modal-content form { display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; }
.modal-header { padding: 20px 24px; border-bottom: 1px solid #e4e6eb; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
.modal-header h5 { margin: 0; font-size: 1.1rem; font-weight: 700; }
.modal-body { padding: 24px; overflow-y: auto; flex: 1; min-height: 0; }
.modal-footer { padding: 16px 24px; border-top: 1px solid #e4e6eb; display: flex; justify-content: flex-end; gap: 8px; flex-shrink: 0; flex-wrap: wrap; }
.form-section { margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f0f2f5; }
.form-section:last-child { border-bottom: none; margin-bottom: 0; }
.form-section-title { font-size: 0.9rem; font-weight: 600; color: #050505; margin-bottom: 12px; }
.form-label { font-size: 0.8rem; font-weight: 600; color: #65676b; margin-bottom: 4px; }
.form-control, .form-select { border: 1px solid #e4e6eb; border-radius: 8px; padding: 8px 12px; font-size: 0.875rem; }
.form-control:focus, .form-select:focus { border-color: #1877f2; box-shadow: 0 0 0 2px rgba(24,119,242,0.15); }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.detail-section h6 { font-size: 0.85rem; font-weight: 700; color: #050505; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #f0f2f5; }
.detail-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.85rem; }
.detail-label { color: #65676b; }
.toast-notification { position: fixed; top: 20px; right: 20px; z-index: 3000; padding: 12px 20px; border-radius: 8px; color: #fff; font-size: 0.9rem; font-weight: 500; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.toast-success { background: #059669; }
.toast-error { background: #dc3545; }
.toast-warning { background: #f59e0b; }
.toast-info { background: #3b82f6; }
.tracking-panel { background: #f8f9fa; border: 1px solid #e4e6eb; border-radius: 12px; padding: 16px; }
.tracking-timeline-mini { margin-top: 8px; padding-left: 8px; border-left: 2px solid #dee2e6; }
.timeline-mini-item { position: relative; padding: 6px 0 6px 16px; }
.timeline-mini-dot { position: absolute; left: -9px; top: 10px; width: 10px; height: 10px; border-radius: 50%; background: #dee2e6; border: 2px solid #fff; }
.timeline-mini-dot.active { background: #d4af37; }
.timeline-mini-content { font-size: 0.85rem; }
@media (max-width: 768px) {
  .admin-page { padding: 12px !important; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
  .stat-card { padding: 0.65rem; gap: 0.4rem; }
  .stat-icon { width: 32px; height: 32px; border-radius: 8px; font-size: 0.9rem; }
  .stat-value { font-size: 1rem; }
  .stat-label { font-size: 0.6rem; }
  .filters { flex-direction: column; }
  .detail-grid { grid-template-columns: 1fr; }
  .page-header h2 { font-size: 1.1rem; }
  .table th, .table td { padding: 0.35rem 0.45rem; font-size: 0.78rem; }
}

@media (max-width: 480px) {
  .admin-page { padding: 0.75rem !important; }
  .stats-grid { grid-template-columns: 1fr; gap: 6px; }
  .stat-card { padding: 0.55rem; }
  .stat-icon { width: 28px; height: 28px; }
  .stat-value { font-size: 0.9rem; }
  .page-header h2 { font-size: 1rem; }
}
</style>
