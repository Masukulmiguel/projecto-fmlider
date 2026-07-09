<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2>Gestão de Contentores</h2>
          <p class="text-muted mb-0">Registo e acompanhamento de contentores</p>
        </div>
        <button class="btn btn-primary" @click="openCreate">
          <i class="bi bi-plus-lg me-1"></i>Novo Contentor
        </button>
      </div>
    </div>

    <div class="stats-grid mb-4">
      <div class="stat-card"><div class="stat-icon" style="background:#dbeafe;color:#1e40af"><i class="bi bi-box-seam"></i></div><div class="stat-info"><span class="stat-value">{{ stats.total }}</span><span class="stat-label">Total</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#d1fae5;color:#065f46"><i class="bi bi-house-check"></i></div><div class="stat-info"><span class="stat-value">{{ stats.na_base }}</span><span class="stat-label">Na Base</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#cffafe;color:#155e75"><i class="bi bi-building"></i></div><div class="stat-info"><span class="stat-value">{{ stats.em_terminal }}</span><span class="stat-label">Em Terminal</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#fef3c7;color:#92400e"><i class="bi bi-clock"></i></div><div class="stat-info"><span class="stat-value">{{ stats.agendados }}</span><span class="stat-label">Agendados</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#ede9fe;color:#6d28d9"><i class="bi bi-truck"></i></div><div class="stat-info"><span class="stat-value">{{ stats.em_transporte }}</span><span class="stat-label">Em Transporte</span></div></div>
      <div class="stat-card"><div class="stat-icon" style="background:#d1fae5;color:#047857"><i class="bi bi-check-circle"></i></div><div class="stat-info"><span class="stat-value">{{ stats.entregues }}</span><span class="stat-label">Entregues</span></div></div>
    </div>

    <div v-if="chartsLoaded" class="row g-4 mb-4">
      <div class="col-lg-6">
        <div class="card chart-card">
          <div class="card-header bg-white fw-bold"><i class="bi bi-pie-chart me-2"></i>Por Estado</div>
          <div class="card-body">
            <div v-if="chartData.byEstado.length === 0" class="text-center text-muted py-3">Sem dados</div>
            <div v-else class="bar-chart">
              <div v-for="item in chartData.byEstado" :key="item.label" class="bar-row">
                <span class="bar-label">{{ item.label }}</span>
                <div class="bar-track"><div class="bar-fill" :style="{ width: item.pct + '%', background: item.color }"></div></div>
                <span class="bar-value">{{ item.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card chart-card">
          <div class="card-header bg-white fw-bold"><i class="bi bi-people me-2"></i>Top Clientes</div>
          <div class="card-body">
            <div v-if="chartData.byCliente.length === 0" class="text-center text-muted py-3">Sem dados</div>
            <div v-else class="bar-chart">
              <div v-for="item in chartData.byCliente" :key="item.label" class="bar-row">
                <span class="bar-label">{{ item.label }}</span>
                <div class="bar-track"><div class="bar-fill" style="background:#2563eb" :style="{ width: item.pct + '%' }"></div></div>
                <span class="bar-value">{{ item.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card chart-card">
          <div class="card-header bg-white fw-bold"><i class="bi bi-graph-up me-2"></i>Evolução Mensal</div>
          <div class="card-body">
            <div v-if="chartData.monthly.length === 0" class="text-center text-muted py-3">Sem dados</div>
            <div v-else class="bar-chart">
              <div v-for="item in chartData.monthly" :key="item.label" class="bar-row">
                <span class="bar-label">{{ item.label }}</span>
                <div class="bar-track"><div class="bar-fill" style="background:#059669" :style="{ width: item.pct + '%' }"></div></div>
                <span class="bar-value">{{ item.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card chart-card">
          <div class="card-header bg-white fw-bold"><i class="bi bi-truck me-2"></i>Entregas Mensais</div>
          <div class="card-body">
            <div v-if="chartData.entregas.length === 0" class="text-center text-muted py-3">Sem dados</div>
            <div v-else class="bar-chart">
              <div v-for="item in chartData.entregas" :key="item.label" class="bar-row">
                <span class="bar-label">{{ item.label }}</span>
                <div class="bar-track"><div class="bar-fill" style="background:#7c3aed" :style="{ width: item.pct + '%' }"></div></div>
                <span class="bar-value">{{ item.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box"><i class="bi bi-search"></i><input v-model="filters.q" type="text" placeholder="Pesquisar por número, NS, processo..." @input="debounceSearch"></div>
          <select v-model="filters.estado" class="form-select" @change="fetchData"><option value="">Todos os estados</option><option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option></select>
          <select v-model="filters.cliente_id" class="form-select" @change="fetchData"><option value="">Todos os clientes</option><option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option></select>
          <select v-model="filters.terminal" class="form-select" @change="fetchData"><option value="">Todos os terminais</option><option v-for="t in terminals" :key="t" :value="t">{{ t }}</option></select>
        </div>

        <div v-if="loading" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">Nenhum contentor encontrado.</div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead><tr><th>Número</th><th>NS</th><th>Tipologia</th><th>Cliente</th><th>Terminal</th><th>ETA</th><th>ATA</th><th>Estado</th><th>Ações</th></tr></thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td><code class="tracking-code">{{ item.numero }}</code></td>
                <td>{{ item.ns || '' }}</td>
                <td>{{ item.tipologia || '' }}</td>
                <td>{{ getClientName(item.cliente_id) }}</td>
                <td>{{ item.terminal || '' }}</td>
                <td><small class="text-muted">{{ formatDate(item.eta) }}</small></td>
                <td><small class="text-muted">{{ formatDate(item.ata) }}</small></td>
                <td><span class="status-badge" :class="'status-' + item.estado">{{ estadoLabel(item.estado) }}</span></td>
                <td>
                  <div class="action-buttons">
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
        <div class="modal-header"><h5>{{ editingItem ? 'Editar Contentor' : 'Novo Contentor' }}</h5><button class="btn-close" @click="closeModal"></button></div>
        <div class="modal-body">
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-info-circle me-1"></i> Identificação</h6>
            <div class="row g-3">
              <div class="col-md-3"><label class="form-label">Número <span class="text-danger">*</span></label><input v-model="form.numero" type="text" class="form-control" placeholder="Ex: MSKU1234567"></div>
              <div class="col-md-3"><label class="form-label">NS</label><input v-model="form.ns" type="text" class="form-control" placeholder="NS"></div>
              <div class="col-md-3"><label class="form-label">Selo</label><input v-model="form.selo" type="text" class="form-control" placeholder="Selo"></div>
              <div class="col-md-3"><label class="form-label">Tipologia</label><select v-model="form.tipologia" class="form-select"><option value="">Selecione</option><option value="20gp">20' GP</option><option value="40gp">40' GP</option><option value="40hc">40' HC</option><option value="45hc">45' HC</option><option value="reefer">Reefer</option><option value="open_top">Open Top</option><option value="flat_rack">Flat Rack</option><option value="outro">Outro</option></select></div>
            </div>
          </div>
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-rulers me-1"></i> Especificações</h6>
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label">Capacidade</label><input v-model="form.capacidade" type="text" class="form-control" placeholder="Ex: 28 cbm"></div>
              <div class="col-md-4"><label class="form-label">Peso (kg)</label><input v-model.number="form.peso" type="number" class="form-control" placeholder="0.00"></div>
              <div class="col-md-4"><label class="form-label">Taxas</label><input v-model.number="form.taxas" type="number" class="form-control" placeholder="0.00"></div>
            </div>
          </div>
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-calendar me-1"></i> Datas</h6>
            <div class="row g-3">
              <div class="col-md-3"><label class="form-label">ETA</label><input v-model="form.eta" type="date" class="form-control"></div>
              <div class="col-md-3"><label class="form-label">ATA</label><input v-model="form.ata" type="date" class="form-control"></div>
              <div class="col-md-3"><label class="form-label">Data Descarga</label><input v-model="form.data_descarga" type="date" class="form-control"></div>
              <div class="col-md-3"><label class="form-label">Previsão Saída</label><input v-model="form.previsao_saida" type="date" class="form-control"></div>
            </div>
          </div>
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-geo-alt me-1"></i> Logística</h6>
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label">Terminal</label><input v-model="form.terminal" type="text" class="form-control" placeholder="Terminal"></div>
              <div class="col-md-4"><label class="form-label">Nº T1</label><input v-model="form.numero_t1" type="text" class="form-control" placeholder="Nº T1"></div>
              <div class="col-md-4"><label class="form-label">Data T1</label><input v-model="form.data_t1" type="date" class="form-control"></div>
            </div>
            <div class="row g-3 mt-1">
              <div class="col-md-4"><label class="form-label">Garantia</label><input v-model="form.garantia" type="text" class="form-control" placeholder="Garantia"></div>
              <div class="col-md-4"><label class="form-label">Passagem</label><input v-model="form.passagem" type="text" class="form-control" placeholder="Passagem"></div>
              <div class="col-md-4"><label class="form-label">Estado</label><select v-model="form.estado" class="form-select"><option v-for="e in estados" :key="e.value" :value="e.value">{{ e.label }}</option></select></div>
            </div>
          </div>
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-link-45deg me-1"></i> Processo & Cliente</h6>
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label">Cliente <span class="text-danger">*</span></label><select v-model="form.cliente_id" class="form-select"><option value="">Selecione o cliente</option><option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option></select></div>
              <div class="col-md-4"><label class="form-label">Nº Processo</label><input v-model="form.numero_processo" type="text" class="form-control" placeholder="Nº processo"></div>
              <div class="col-md-4"><label class="form-label">Ref. FMLider</label><input v-model="form.referencia_fmlider" type="text" class="form-control" placeholder="Ref. FMLider"></div>
            </div>
            <div class="row g-3 mt-1">
              <div class="col-md-4"><label class="form-label">Ref. Cliente</label><input v-model="form.referencia_cliente" type="text" class="form-control" placeholder="Ref. do cliente"></div>
            </div>
          </div>
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-chat-left-text me-1"></i> Observações</h6>
            <textarea v-model="form.observacoes" class="form-control" rows="3" placeholder="Notas internas..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeModal">Cancelar</button>
          <button class="btn btn-primary" @click="save" :disabled="saving"><span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>{{ editingItem ? 'Guardar' : 'Criar' }}</button>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDelete">
      <div class="modal-content modal-sm">
        <div class="modal-header"><h5>Confirmar</h5><button class="btn-close" @click="closeDelete"></button></div>
        <div class="modal-body"><p>Tem certeza que deseja eliminar o contentor <strong>{{ deleteItem?.numero }}</strong>?</p><p class="text-muted small mb-0">Esta acção não pode ser desfeita.</p></div>
        <div class="modal-footer"><button class="btn btn-secondary" @click="closeDelete">Cancelar</button><button class="btn btn-danger" @click="deleteItemConfirm" :disabled="deleting"><span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>Eliminar</button></div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toast.show" class="toast-container" :class="'toast-' + toast.type"><i :class="toast.type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill'" class="me-2"></i>{{ toast.message }}</div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'

const items = ref([])
const loading = ref(false)
const clients = ref([])
const filters = reactive({ q: '', estado: '', cliente_id: '', terminal: '' })
const currentPage = ref(1)
const pageSize = 20
const totalItems = ref(0)
let searchTimer = null

const chartsLoaded = ref(false)
const allContentores = ref([])
const chartData = reactive({ byEstado: [], byCliente: [], monthly: [], entregas: [] })

const estadoColors = {
  aguardando_chegada: '#94a3b8', chegou_ao_porto: '#0891b2', em_terminal: '#2563eb',
  na_base: '#059669', agendado_para_entrega: '#d97706', em_transporte: '#7c3aed',
  entregue: '#16a34a', devolvido: '#6b7280', cancelado: '#dc2626'
}

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

const stats = reactive({ total: 0, na_base: 0, em_terminal: 0, agendados: 0, em_transporte: 0, entregues: 0 })

const terminals = computed(() => {
  const set = new Set(items.value.map(i => i.terminal).filter(Boolean))
  return [...set].sort()
})

const fetchData = async () => {
  loading.value = true
  try {
    let query = supabase.from('contentores').select('*', { count: 'exact' })
    if (filters.estado) query = query.eq('estado', filters.estado)
    if (filters.cliente_id) query = query.eq('cliente_id', filters.cliente_id)
    if (filters.terminal) query = query.eq('terminal', filters.terminal)
    if (filters.q) query = query.or(`numero.ilike.%${filters.q}%,ns.ilike.%${filters.q}%,numero_processo.ilike.%${filters.q}%,referencia_fmlider.ilike.%${filters.q}%,referencia_cliente.ilike.%${filters.q}%`)
    const from = (currentPage.value - 1) * pageSize
    const to = from + pageSize - 1
    const { data, error, count } = await query.order('created_at', { ascending: false }).range(from, to)
    if (error) throw error
    items.value = data || []
    totalItems.value = count || 0

    stats.total = totalItems.value
    stats.na_base = items.value.filter(i => i.estado === 'na_base').length
    stats.em_terminal = items.value.filter(i => i.estado === 'em_terminal').length
    stats.agendados = items.value.filter(i => i.estado === 'agendado_para_entrega').length
    stats.em_transporte = items.value.filter(i => i.estado === 'em_transporte').length
    stats.entregues = items.value.filter(i => i.estado === 'entregue').length
  } catch (e) {
    console.error('Erro ao buscar contentores:', e)
  } finally { loading.value = false }
}

const totalPages = computed(() => Math.ceil(totalItems.value / pageSize))
const changePage = (page) => { currentPage.value = page; fetchData() }
const debounceSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(() => { currentPage.value = 1; fetchData() }, 300) }

const fetchClients = async () => {
  const { data } = await supabase.from('users').select('id, name').eq('role', 'cliente').order('name')
  clients.value = data || []
}

const fetchChartData = async () => {
  try {
    const { data } = await supabase.from('contentores').select('estado, cliente_id, created_at, data_descarga')
    if (!data) return
    allContentores.value = data

    const estadoMap = {}
    const clienteMap = {}
    const monthMap = {}
    const entregaMonthMap = {}

    for (const c of data) {
      const el = estadoLabel(c.estado)
      estadoMap[el] = (estadoMap[el] || 0) + 1

      const cn = getClientName(c.cliente_id) || 'Sem Cliente'
      clienteMap[cn] = (clienteMap[cn] || 0) + 1

      if (c.created_at) {
        const d = new Date(c.created_at)
        const mk = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
        monthMap[mk] = (monthMap[mk] || 0) + 1
      }

      if (c.data_descarga) {
        const d = new Date(c.data_descarga)
        const mk = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
        entregaMonthMap[mk] = (entregaMonthMap[mk] || 0) + 1
      }
    }

    const buildBar = (map, maxItems = 8) => {
      const entries = Object.entries(map).sort((a, b) => b[1] - a[1]).slice(0, maxItems)
      const max = entries.length > 0 ? entries[0][1] : 1
      return entries.map(([label, count]) => ({ label, count, pct: Math.round((count / max) * 100) }))
    }

    const estadoEntries = Object.entries(estadoMap).sort((a, b) => b[1] - a[1])
    const maxEstado = estadoEntries.length > 0 ? estadoEntries[0][1] : 1
    chartData.byEstado = estadoEntries.map(([label, count]) => ({
      label, count, pct: Math.round((count / maxEstado) * 100),
      color: Object.entries(estadoColors).find(([, v]) => true)?.[1] || '#2563eb'
    }))
    const estadoColorList = ['#94a3b8', '#0891b2', '#2563eb', '#059669', '#d97706', '#7c3aed', '#16a34a', '#6b7280', '#dc2626']
    chartData.byEstado = estadoEntries.map(([label, count], i) => ({
      label, count, pct: Math.round((count / maxEstado) * 100),
      color: estadoColorList[i % estadoColorList.length]
    }))

    chartData.byCliente = buildBar(clienteMap)

    const sortedMonths = Object.keys(monthMap).sort()
    const maxMonth = sortedMonths.length > 0 ? Math.max(...sortedMonths.map(k => monthMap[k])) : 1
    chartData.monthly = sortedMonths.map(k => {
      const [y, m] = k.split('-')
      const monthNames = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
      return { label: `${monthNames[parseInt(m) - 1]} ${y}`, count: monthMap[k], pct: Math.round((monthMap[k] / maxMonth) * 100) }
    })

    const sortedEntregas = Object.keys(entregaMonthMap).sort()
    const maxEntrega = sortedEntregas.length > 0 ? Math.max(...sortedEntregas.map(k => entregaMonthMap[k])) : 1
    chartData.entregas = sortedEntregas.map(k => {
      const [y, m] = k.split('-')
      const monthNames = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
      return { label: `${monthNames[parseInt(m) - 1]} ${y}`, count: entregaMonthMap[k], pct: Math.round((entregaMonthMap[k] / maxEntrega) * 100) }
    })

    chartsLoaded.value = true
  } catch (e) { console.error('Erro ao carregar gráficos:', e) }
}

const getClientName = (id) => clients.value.find(c => c.id === id)?.name || ''

const estadoLabel = (e) => estados.find(s => s.value === e)?.label || e
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : ''

const showModal = ref(false)
const editingItem = ref(null)
const saving = ref(false)
const form = reactive({ numero: '', ns: '', selo: '', tipologia: '', capacidade: '', peso: null, eta: '', ata: '', data_descarga: '', terminal: '', numero_t1: '', data_t1: '', garantia: '', passagem: '', previsao_saida: '', taxas: null, cliente_id: '', numero_processo: '', referencia_fmlider: '', referencia_cliente: '', estado: 'aguardando_chegada', observacoes: '' })

const resetForm = () => { Object.keys(form).forEach(k => form[k] = typeof form[k] === 'number' ? null : ''); form.estado = 'aguardando_chegada' }

const openCreate = () => { editingItem.value = null; resetForm(); showModal.value = true }
const openEdit = (item) => {
  editingItem.value = item
  Object.keys(form).forEach(k => { form[k] = item[k] ?? (typeof form[k] === 'number' ? null : '') })
  showModal.value = true
}
const closeModal = () => { showModal.value = false; editingItem.value = null }

const save = async () => {
  if (!form.numero?.trim()) { showToast('error', 'O número do contentor é obrigatório.'); return }
  if (!form.cliente_id) { showToast('error', 'O cliente é obrigatório.'); return }
  saving.value = true
  try {
    const payload = { ...form }
    Object.keys(payload).forEach(k => { if (payload[k] === '' || payload[k] === null) payload[k] = null })
    if (editingItem.value) {
      const { error } = await supabase.from('contentores').update(payload).eq('id', editingItem.value.id)
      if (error) throw error
      showToast('success', 'Contentor atualizado!')
    } else {
      const { error } = await supabase.from('contentores').insert(payload)
      if (error) throw error
      showToast('success', 'Contentor criado!')
    }
    closeModal(); fetchData()
  } catch (e) { showToast('error', e.message || 'Erro ao guardar contentor.') } finally { saving.value = false }
}

const showDeleteModal = ref(false)
const deleteItem = ref(null)
const deleting = ref(false)
const openDelete = (item) => { deleteItem.value = item; showDeleteModal.value = true }
const closeDelete = () => { showDeleteModal.value = false; deleteItem.value = null }
const deleteItemConfirm = async () => {
  deleting.value = true
  try {
    const { error } = await supabase.from('contentores').delete().eq('id', deleteItem.value.id)
    if (error) throw error
    showToast('success', 'Contentor eliminado!'); closeDelete(); fetchData()
  } catch (e) { showToast('error', 'Erro ao eliminar contentor.') } finally { deleting.value = false }
}

const toast = reactive({ show: false, type: 'success', message: '' })
let toastTimer = null
const showToast = (type, message) => { toast.type = type; toast.message = message; toast.show = true; clearTimeout(toastTimer); toastTimer = setTimeout(() => { toast.show = false }, 4000) }

onMounted(() => { fetchData(); fetchClients(); fetchChartData() })
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
.action-buttons { display: flex; gap: 0.35rem; }
.btn-icon { width: 32px; height: 32px; border: none; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-edit { background: #eff6ff; color: #2563eb; }
.btn-edit:hover { background: #dbeafe; color: #1d4ed8; }
.btn-delete { background: #fef2f2; color: #dc2626; }
.btn-delete:hover { background: #fee2e2; color: #b91c1c; }
.form-section { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; margin-bottom: 1rem; }
.form-section-title { font-weight: 600; color: #1a365d; margin-bottom: 0.75rem; font-size: 0.9rem; }
.form-section .form-label { font-size: 0.8rem; font-weight: 500; color: #475569; margin-bottom: 0.25rem; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1050; }
.modal-content { background: white; border-radius: 12px; width: 100%; max-width: 520px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
.modal-content.modal-sm { max-width: 400px; }
.modal-content.modal-xl { max-width: 900px; }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
.modal-header h5 { margin: 0; font-weight: 600; }
.modal-body { padding: 1.5rem; overflow-y: auto; flex: 1; min-height: 0; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem; flex-shrink: 0; }
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
.chart-card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.chart-card .card-header { border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
.bar-chart { display: flex; flex-direction: column; gap: 0.6rem; }
.bar-row { display: flex; align-items: center; gap: 0.75rem; }
.bar-label { min-width: 120px; max-width: 140px; font-size: 0.78rem; color: #475569; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bar-track { flex: 1; height: 22px; background: #f1f5f9; border-radius: 6px; overflow: hidden; }
.bar-fill { height: 100%; border-radius: 6px; transition: width 0.5s ease; min-width: 2px; }
.bar-value { min-width: 28px; font-size: 0.8rem; font-weight: 600; color: #1e293b; }
</style>
