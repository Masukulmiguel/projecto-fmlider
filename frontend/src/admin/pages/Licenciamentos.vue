<template>
<!-- cache-bust -->
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <div>
        <h2>Gestão de Licenciamentos</h2>
        <p class="text-muted mb-0">Gestão inteligente de processos de licenciamento e autorizações</p>
      </div>
    </div>

    <!-- Schema Error Banner -->
    <div v-if="schemaError" class="schema-error-banner">
      <div class="schema-error-icon">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>
      <div class="schema-error-content">
        <h4>Erro de Base de Dados</h4>
        <p>O Supabase não consegue carregar os licenciamentos. Precisa de executar um SQL uma única vez para corrigir.</p>
        <ol>
          <li>Abra o <a href="https://supabase.com/dashboard/project/vsupwqxtnzdnxklgbynn/sql/new" target="_blank">Supabase SQL Editor</a></li>
          <li>Cole o código SQL abaixo</li>
          <li>Clique em <strong>"Run"</strong></li>
          <li>Volte esta página e carregue em <strong>Actualizar</strong></li>
        </ol>
        <div class="sql-box">
          <button class="btn-copy" @click="copySQL">
            <i :class="sqlCopied ? 'bi bi-check-lg' : 'bi bi-clipboard'"></i>
            {{ sqlCopied ? 'Copiado!' : 'Copiar SQL' }}
          </button>
          <pre><code>{{ fixSchemaSQL }}</code></pre>
        </div>
      </div>
    </div>

    <div class="stats-row mb-4">
      <div class="stat-card" v-for="stat in stats" :key="stat.key">
        <div class="stat-value">{{ stat.value }}</div>
        <div class="stat-label">{{ stat.label }}</div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" placeholder="Pesquisar por referência, processo ou cliente..." @input="debounceSearch">
          </div>
          <select v-model="filters.estado" class="form-select" @change="fetchData">
            <option value="">Todos os estados</option>
            <option value="rascunho">Rascunho</option>
            <option value="pendente_cliente">Pendente Cliente</option>
            <option value="submetido">Submetido</option>
            <option value="em_analise">Em Análise</option>
            <option value="aprovado">Aprovado</option>
            <option value="indeferido">Indeferido</option>
            <option value="resubmetido">Re-Submetido</option>
            <option value="certificacao_solicitada">Certificação - Solicitada</option>
            <option value="expira_brevemente">Expira Brevemente</option>
            <option value="expirado">Expirado</option>
          </select>
          <select v-model="filters.tipo" class="form-select" @change="fetchData">
            <option value="">Todos os tipos</option>
            <option value="importacao">Importação</option>
            <option value="exportacao">Exportação</option>
            <option value="transito">Trânsito</option>
            <option value="temporario">Temporário</option>
            <option value="cabotagem">Cabotagem</option>
          </select>
          <button class="btn btn-outline-success" @click="showExcelImport = true">
            <i class="bi bi-file-earmark-excel me-1"></i>
            Importar Excel
          </button>
        </div>

        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
          Nenhum licenciamento encontrado.
        </div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Referência</th>
                <th>Processo</th>
                <th>Cliente</th>
                <th>Shipper</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Data Submissão</th>
                <th>{{ t('admin.actions_col') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id" class="cursor-pointer" @click="router.push('/admin/licenciamentos/' + item.id)">
                <td><code class="tracking-code">{{ item.referencia }}</code></td>
                <td>{{ item.numero_processo || '—' }}</td>
                <td>
                  <div class="fw-medium">{{ item.empresa }}</div>
                </td>
                <td>{{ item.shipper || '—' }}</td>
                <td>{{ tipoLabel(item.tipo) }}</td>
                <td><span class="status-badge" :class="'status-' + item.estado">{{ estadoLabel(item.estado) }}</span></td>
                <td><small class="text-muted">{{ formatDate(item.data_submissao) }}</small></td>
                <td @click.stop>
                  <div class="action-buttons">
                    <button class="btn-icon btn-edit" @click="openEdit(item)" :title="t('common.edit')">
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn-icon btn-delete" @click="openDelete(item)" :title="t('common.delete')">
                      <i class="bi bi-trash3"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="totalPages > 1" class="pagination-bar">
            <span class="page-info">{{ currentPage }} / {{ totalPages }}</span>
            <div class="page-btns">
              <button class="page-btn" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">
                <i class="bi bi-chevron-left"></i>
              </button>
              <button class="page-btn" :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)">
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEdit">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Editar Licenciamento</h5>
          <button class="btn-close" @click="closeEdit"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Estado</label>
            <select v-model="editForm.estado" class="form-select">
              <option value="rascunho">Rascunho</option>
              <option value="pendente_cliente">Pendente Cliente</option>
              <option value="submetido">Submetido</option>
              <option value="em_analise">Em Análise</option>
              <option value="aprovado">Aprovado</option>
              <option value="indeferido">Indeferido</option>
              <option value="expira_brevemente">Expira Brevemente</option>
              <option value="expirado">Expirado</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Funcionário Responsável</label>
            <input v-model="editForm.funcionario_responsavel" type="text" class="form-control" placeholder="Nome do funcionário">
          </div>
          <div class="mb-3">
            <label class="form-label">Observações</label>
            <textarea v-model="editForm.observacoes" class="form-control" rows="3" placeholder="Notas internas..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeEdit">{{ t('common.cancel') }}</button>
          <button class="btn btn-primary" @click="submitEdit" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
            {{ t('common.save') }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDelete">
      <div class="modal-content modal-sm">
        <div class="modal-header">
          <h5>{{ t('common.confirm') }}</h5>
          <button class="btn-close" @click="closeDelete"></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja eliminar o licenciamento <strong>{{ deleteItem?.referencia }}</strong>?</p>
          <p class="text-muted small mb-0">Esta acção não pode ser desfeita.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDelete">{{ t('common.cancel') }}</button>
          <button class="btn btn-danger" @click="submitDelete" :disabled="deleting">
            <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
            {{ t('common.delete') }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showExcelImport" class="modal-overlay" @click.self="closeExcelImport">
      <div class="modal-content modal-lg">
        <div class="modal-header">
          <h5>Importar Excel</h5>
          <button class="btn-close" @click="closeExcelImport"></button>
        </div>
        <div class="modal-body">
          <div v-if="showValidation">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0">Resultados da Importação</h6>
              <button class="btn btn-sm btn-outline-secondary" @click="showValidation = false; excelData = []">
                <i class="bi bi-arrow-left me-1"></i>Novo ficheiro
              </button>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-4">
                <div class="validation-stat bg-success-subtle text-success">
                  <span class="stat-num">{{ importValidationResults.filter(r => r.success).length }}</span>
                  <span class="stat-txt">Importados</span>
                </div>
              </div>
              <div class="col-4">
                <div class="validation-stat bg-danger-subtle text-danger">
                  <span class="stat-num">{{ importValidationResults.filter(r => !r.success).length }}</span>
                  <span class="stat-txt">Rejeitados</span>
                </div>
              </div>
              <div class="col-4">
                <div class="validation-stat bg-warning-subtle text-warning">
                  <span class="stat-num">{{ importValidationResults.filter(r => r.warnings.length > 0).length }}</span>
                  <span class="stat-txt">Avisos</span>
                </div>
              </div>
            </div>

            <div v-if="failedRecords.length > 0" class="alert-rejected mb-3">
              <div class="alert-rejected-header">
                <i class="bi bi-shield-exclamation"></i>
                <span>Registos Rejeitados — Corrija o ficheiro Excel e volte a importar</span>
              </div>
              <div class="alert-rejected-list">
                <div v-for="r in failedRecords" :key="r.idx" class="rejected-item">
                  <div class="rejected-item-header">
                    <span class="rejected-idx">#{{ r.idx }}</span>
                    <code class="rejected-ref">{{ r.referencia }}</code>
                    <span class="rejected-client">{{ r.clienteNome || '—' }}</span>
                    <span class="rejected-company">{{ r.empresaNome || '—' }}</span>
                  </div>
                  <div class="rejected-item-errors">
                    <div v-for="(e, i) in r.errors" :key="i" class="rejected-error">
                      <i class="bi bi-x-circle-fill"></i> {{ e }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="successRecords.length > 0" class="alert-success-custom mb-3">
              <div class="alert-success-header">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ successRecords.length }} registo(s) importado(s) com sucesso</span>
              </div>
            </div>

            <div v-if="updatedRecords.length > 0" class="alert-updated mb-3">
              <div class="alert-updated-header">
                <i class="bi bi-arrow-repeat"></i>
                <span>{{ updatedRecords.length }} registo(s) actualizado(s) com sucesso</span>
              </div>
              <div class="updated-list">
                <div v-for="r in updatedRecords" :key="r.idx" class="updated-item">
                  <span class="updated-ref">#{{ r.idx }} {{ r.referencia }}</span>
                  <span v-for="(w, i) in r.warnings" :key="i" class="updated-msg">{{ w }}</span>
                </div>
              </div>
            </div>

            <div v-if="warningRecords.length > 0" class="alert-warning-custom mb-3">
              <div class="alert-warning-header">
                <i class="bi bi-info-circle-fill"></i>
                <span>{{ warningRecords.length }} registo(s) com avisos</span>
              </div>
              <div class="warning-list">
                <div v-for="r in warningRecords" :key="r.idx" class="warning-item">
                  <span class="warning-ref">#{{ r.idx }} {{ r.referencia }}</span>
                  <span v-for="(w, i) in r.warnings" :key="i" class="warning-msg">{{ w }}</span>
                </div>
              </div>
            </div>
          </div>
          <template v-else>
            <div v-if="!excelData.length && excelSheets.length > 1 && !selectedSheet">
              <p class="text-muted mb-3">Este ficheiro tem várias sheets. Qual pretende importar?</p>
              <div class="d-flex flex-wrap gap-2 mb-3">
                <button v-for="sheet in excelSheets" :key="sheet" class="btn btn-outline-primary" @click="selectedSheet = sheet; onSheetSelect()">
                  <i class="bi bi-file-earmark-table me-1"></i>{{ sheet }}
                </button>
              </div>
              <div class="mt-2">
                <input ref="excelFileInput" type="file" accept=".xlsx,.xls,.csv" class="d-none" @change="handleExcelFile">
                <button class="btn btn-sm btn-outline-secondary" @click="$refs.excelFileInput.click()">
                  <i class="bi bi-arrow-left me-1"></i>Outro ficheiro
                </button>
              </div>
            </div>
            <div v-else-if="!excelData.length">
              <p class="text-muted mb-3">Selecione um ficheiro Excel (.xlsx, .xls ou .csv). O sistema importa os dados com as seguintes regras:</p>
              <ul class="text-muted small mb-3">
                <li><strong>Sheet Licenciamentos</strong> — valida o <strong>Cliente</strong> (deve existir como utilizador no sistema)</li>
                <li><strong>Sheet Observações</strong> — valida o <strong>Funcionário</strong> (deve existir como utilizador no sistema)</li>
                <li>Os dados são importados automaticamente ao clicar em "Validar e Importar"</li>
              </ul>
              <input ref="excelFileInput" type="file" accept=".xlsx,.xls,.csv" class="d-none" @change="handleExcelFile">
              <button class="btn btn-outline-primary" @click="$refs.excelFileInput.click()">
                <i class="bi bi-upload me-1"></i>
                Selecionar ficheiro
              </button>
              <div v-if="importError" class="alert alert-danger mt-3">{{ importError }}</div>
            </div>
            <div v-else>
              <div class="alert alert-info mb-3">
                {{ excelData.length }} registo(s) encontrado(s) no ficheiro — clique em "Validar e Importar" para analisar
              </div>
              <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th v-for="header in excelHeaders" :key="header">{{ header }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, idx) in excelData.slice(0, 10)" :key="idx">
                      <td v-for="header in excelHeaders" :key="header">{{ row[header] || '' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p v-if="excelData.length > 10" class="text-muted small">...e mais {{ excelData.length - 10 }} registo(s)</p>
            </div>
          </template>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeExcelImport">{{ t('common.cancel') }}</button>
          <div v-if="excelData.length && !showValidation" class="d-flex align-items-center gap-3">
            <label class="update-mode-toggle">
              <input type="checkbox" v-model="updateMode" />
              <span class="toggle-slider"></span>
              <span class="toggle-label">Actualizar existentes (NIF + Tipo)</span>
            </label>
            <button class="btn btn-primary" @click="submitExcelImport" :disabled="importing">
              <span v-if="importing" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="bi bi-shield-check me-1"></i>
              Validar e Importar
            </button>
          </div>
          <button v-if="showValidation" class="btn btn-success" @click="closeExcelImport">
            <i class="bi bi-check-lg me-1"></i>Concluir
          </button>
        </div>
      </div>
    </div>

    <div v-if="toast.show" class="toast-container" :class="'toast-' + toast.type">
      <i :class="toast.type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill'" class="me-2"></i>
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { supabase } from '@/lib/supabase'
import { useI18n } from '@/composables/useI18n'
import * as XLSX from 'xlsx'

const { t } = useI18n()
const router = useRouter()

const items = ref([])
const loading = ref(false)
const schemaError = ref(false)
const sqlCopied = ref(false)
const filters = reactive({ q: '', estado: '', tipo: '' })
const currentPage = ref(1)
const pageSize = 20
const totalItems = ref(0)
let searchTimer = null

const columnMap = {
  'referência': 'referencia',
  'referencia': 'referencia',
  'ref': 'referencia',
  'refª fmlider': 'numero_processo',
  'refª cliente': 'ref_cliente',
  'nº processo': 'numero_processo',
  'no processo': 'numero_processo',
  'numero processo': 'numero_processo',
  'processo': 'numero_processo',
  'número processo': 'numero_processo',
  'nº registo': 'numero_processo',
  'nº pedido': 'pedido',
  'nº pfi': 'pfi',
  'nº licenciamento': 'num_licenciamento',
  'cliente': 'empresa',
  'cliente_nome': 'empresa',
  'nome cliente': 'empresa',
  'nome do cliente': 'empresa',
  'empresa': 'empresa',
  'nome empresa': 'empresa',
  'shipper': 'shipper',
  'grupo': 'grupo',
  'tipo': 'tipo',
  'tipo licenciamento': 'tipo',
  'tipo_licenciamento': 'tipo',
  'estado': 'estado',
  'data': 'data_submissao',
  'data submissão': 'data_submissao',
  'data submissao': 'data_submissao',
  'data_submissao': 'data_submissao',
  'submissão': 'data_submissao',
  'validade': 'data_validade',
  'data validade': 'data_validade',
  'funcionário responsável': 'funcionario_responsavel',
  'funcionario responsavel': 'funcionario_responsavel',
  'funcionario_responsavel': 'funcionario_responsavel',
  'responsável': 'funcionario_responsavel',
  'responsavel': 'funcionario_responsavel',
  'funcionário': 'funcionario_responsavel',
  'funcionario': 'funcionario_responsavel',
  'user': 'funcionario_responsavel',
  'observações': 'observacoes',
  'observacoes': 'observacoes',
  'notas': 'observacoes',
  'descrição': 'descricao',
  'descricao': 'descricao',
  'produto/mercadoria': 'descricao',
  'nif': 'nif_empresa',
  'nif empresa': 'nif_empresa',
  'nif_empresa': 'nif_empresa',
  'códº': 'codigo',
  'cap': 'capacidade',
  'qtd': 'quantidade',
  'aprovado': 'aprovado_data',
  'nº licenciamento': 'num_licenciamento',
  'motivo': 'motivo'
}

const fetchData = async () => {
  loading.value = true
  schemaError.value = false
  try {
    let query = supabase.from('licenciamentos').select('*', { count: 'exact' })
    if (filters.estado) query = query.eq('estado', filters.estado)
    if (filters.tipo) query = query.eq('tipo', filters.tipo)
      if (filters.q) query = query.or(`referencia.ilike.%${filters.q}%,numero_processo.ilike.%${filters.q}%,empresa.ilike.%${filters.q}%,shipper.ilike.%${filters.q}%`)
    const from = (currentPage.value - 1) * pageSize
    const to = from + pageSize - 1
    const { data, error, count } = await query.order('created_at', { ascending: false }).range(from, to)
    if (error) {
      if (error.message && error.message.includes('relationship')) {
        schemaError.value = true
        return
      }
      throw error
    }
    items.value = data || []
    totalItems.value = count || 0
  } catch (e) {
    if (e.message && e.message.includes('relationship')) {
      schemaError.value = true
    }
  } finally { loading.value = false }
}

const totalPages = computed(() => Math.ceil(totalItems.value / pageSize))

const changePage = (page) => {
  currentPage.value = page
  fetchData()
}

watch(() => [filters.q, filters.estado, filters.tipo], () => {
  currentPage.value = 1
})

const debounceSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(() => { currentPage.value = 1; fetchData() }, 300) }

const stats = computed(() => {
  const all = items.value
  return [
    { key: 'total', label: 'Total', value: all.length },
    { key: 'rascunho', label: 'Rascunho', value: all.filter(i => i.estado === 'rascunho').length },
    { key: 'pendente_cliente', label: 'Pendente', value: all.filter(i => i.estado === 'pendente_cliente').length },
    { key: 'submetido', label: 'Submetido', value: all.filter(i => i.estado === 'submetido').length },
    { key: 'em_analise', label: 'Em Análise', value: all.filter(i => i.estado === 'em_analise').length },
    { key: 'aprovado', label: 'Aprovado', value: all.filter(i => i.estado === 'aprovado').length },
    { key: 'indeferido', label: 'Indeferido', value: all.filter(i => i.estado === 'indeferido').length },
    { key: 'expira_brevemente', label: 'Expira Breve', value: all.filter(i => i.estado === 'expira_brevemente').length },
    { key: 'expirado', label: 'Expirado', value: all.filter(i => i.estado === 'expirado').length }
  ]
})

const tipoLabel = (tipo) => ({
  importacao: 'Importação',
  exportacao: 'Exportação',
  transito: 'Trânsito',
  temporario: 'Temporário',
  cabotagem: 'Cabotagem'
}[tipo] || tipo)

const estadoLabel = (estado) => ({
  rascunho: 'Rascunho',
  pendente_cliente: 'Pendente Cliente',
  submetido: 'Submetido',
  em_analise: 'Em Análise',
  aprovado: 'Aprovado',
  indeferido: 'Indeferido',
  resubmetido: 'Re-Submetido',
  certificacao_solicitada: 'Certificação - Solicitada',
  expira_brevemente: 'Expira Brevemente',
  expirado: 'Expirado'
}[estado] || estado)

const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : '—'

const showEditModal = ref(false)
const saving = ref(false)
const editItem = ref(null)
const editForm = reactive({ estado: '', funcionario_responsavel: '', observacoes: '' })

const openEdit = (item) => {
  editItem.value = item
  editForm.estado = item.estado
  editForm.funcionario_responsavel = item.funcionario_responsavel || ''
  editForm.observacoes = item.observacoes || ''
  showEditModal.value = true
}

const closeEdit = () => {
  showEditModal.value = false
  editItem.value = null
}

const submitEdit = async () => {
  saving.value = true
  try {
    const { error } = await supabase.from('licenciamentos').update({
      estado: editForm.estado,
      funcionario_responsavel: editForm.funcionario_responsavel,
      observacoes: editForm.observacoes
    }).eq('id', editItem.value.id)
    if (error) throw error
    showToast('success', 'Licenciamento atualizado com sucesso!')
    closeEdit()
    fetchData()
  } catch (e) {
    showToast('error', 'Erro ao atualizar licenciamento.')
  } finally { saving.value = false }
}

const showDeleteModal = ref(false)
const deleting = ref(false)
const deleteItem = ref(null)

const openDelete = (item) => {
  deleteItem.value = item
  showDeleteModal.value = true
}

const closeDelete = () => {
  showDeleteModal.value = false
  deleteItem.value = null
}

const submitDelete = async () => {
  deleting.value = true
  try {
    const { error } = await supabase.from('licenciamentos').delete().eq('id', deleteItem.value.id)
    if (error) throw error
    showToast('success', 'Licenciamento eliminado com sucesso!')
    closeDelete()
    fetchData()
  } catch (e) {
    showToast('error', 'Erro ao eliminar licenciamento.')
  } finally { deleting.value = false }
}

const showExcelImport = ref(false)
const importing = ref(false)
const excelData = ref([])
const excelHeaders = ref([])
const excelFileInput = ref(null)
const importError = ref('')
const updateMode = ref(false)
const excelObservations = ref([])

const closeExcelImport = () => {
  showExcelImport.value = false
  excelData.value = []
  excelHeaders.value = []
  excelObservations.value = []
  importError.value = ''
  showValidation.value = false
  importValidationResults.value = []
  excelSheets.value = []
  selectedSheet.value = ''
  if (window._workbook) delete window._workbook
}

const excelSheets = ref([])
const selectedSheet = ref('')

const handleExcelFile = (event) => {
  const file = event.target.files[0]
  if (!file) return
  importError.value = ''

  const reader = new FileReader()
  reader.onload = (e) => {
    try {
      const workbook = XLSX.read(e.target.result, { type: 'array' })
      excelSheets.value = workbook.SheetNames

      const licSheet = workbook.SheetNames.find(s => /licenciamento/i.test(s))
      const obsSheet = workbook.SheetNames.find(s => /observa/i.test(s))

      if (licSheet) {
        parseSheet(workbook, licSheet)
        if (obsSheet) {
          excelObservations.value = parseObsSheet(workbook, obsSheet)
        }
      } else if (workbook.SheetNames.length === 1) {
        parseSheet(workbook, workbook.SheetNames[0])
      } else {
        selectedSheet.value = ''
        excelData.value = []
        window._workbook = workbook
      }
    } catch {
      importError.value = 'Erro ao processar o ficheiro Excel. Verifique o formato.'
    }
  }
  reader.readAsArrayBuffer(file)
}

const parseSheet = (workbook, sheetName) => {
  const worksheet = workbook.Sheets[sheetName]
  const rawData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' })

  if (rawData.length === 0) {
    importError.value = 'A sheet está vazia.'
    return
  }

  let headers = rawData[0]
  let dataStart = 1

  if (rawData.length > 1) {
    const row0Vals = rawData[0].filter(v => v !== '' && v !== null)
    const row1Vals = rawData[1].filter(v => v !== '' && v !== null)
    if (row0Vals.length < row1Vals.length && row1Vals.length > 5) {
      headers = rawData[1]
      dataStart = 2
    }
  }

  const jsonData = []
  for (let i = dataStart; i < rawData.length; i++) {
    const row = rawData[i]
    if (!row || row.every(v => v === '' || v === null)) continue
    const obj = {}
    headers.forEach((h, idx) => {
      if (h !== '' && h !== null && row[idx] !== undefined) {
        obj[h] = row[idx]
      }
    })
    if (Object.keys(obj).length > 0) jsonData.push(obj)
  }

  if (jsonData.length === 0) {
    importError.value = 'A sheet não contém dados válidos.'
    return
  }

  const rawHeaders = Object.keys(jsonData[0])
  const mappedData = jsonData.map(row => {
    const mapped = {}
    rawHeaders.forEach(h => {
      const key = columnMap[h.toLowerCase().trim()] || h
      let val = row[h]
      if (typeof val === 'number' && val > 40000 && val < 50000 && (key === 'data_submissao' || key === 'data_validade')) {
        try {
          const excelEpoch = new Date(1899, 11, 30)
          const d = new Date(excelEpoch.getTime() + val * 86400000)
          val = d.toISOString().slice(0, 10)
        } catch {}
      }
      mapped[key] = val
    })
    return mapped
  })

  const allKeys = new Set()
  mappedData.forEach(row => Object.keys(row).forEach(k => allKeys.add(k)))
  excelHeaders.value = [...allKeys]
  excelData.value = mappedData
}

const onSheetSelect = () => {
  if (selectedSheet.value && window._workbook) {
    parseSheet(window._workbook, selectedSheet.value)
  }
}

const parseObsSheet = (workbook, sheetName) => {
  const worksheet = workbook.Sheets[sheetName]
  const rawData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' })
  if (rawData.length < 2) return []

  let headers = rawData[0]
  let dataStart = 1
  if (rawData.length > 1) {
    const row0Vals = rawData[0].filter(v => v !== '' && v !== null)
    const row1Vals = rawData[1].filter(v => v !== '' && v !== null)
    if (row0Vals.length < row1Vals.length && row1Vals.length > 3) {
      headers = rawData[1]
      dataStart = 2
    }
  }

  const obsColMap = {
    'nº registo': 'numero_processo',
    'data': 'data',
    'observações': 'observacao',
    'observacoes': 'observacao',
    'user': 'user'
  }

  const observations = []
  for (let i = dataStart; i < rawData.length; i++) {
    const row = rawData[i]
    if (!row || row.every(v => v === '' || v === null)) continue
    const obs = {}
    headers.forEach((h, idx) => {
      if (h !== '' && h !== null && row[idx] !== undefined) {
        const key = obsColMap[h.toLowerCase().trim()] || h.toLowerCase().trim()
        let val = row[idx]
        if (typeof val === 'number' && val > 40000 && val < 50000 && key === 'data') {
          try {
            const d = new Date(1899, 11, 30)
            val = new Date(d.getTime() + val * 86400000).toISOString().slice(0, 10)
          } catch {}
        }
        obs[key] = val
      }
    })
    if (obs.numero_processo || obs.observacao) observations.push(obs)
  }
  return observations
}

const importValidationResults = ref([])
const showValidation = ref(false)

const failedRecords = computed(() => importValidationResults.value.filter(r => !r.success))
const successRecords = computed(() => importValidationResults.value.filter(r => r.success && r.action === 'insert' && r.warnings.length === 0))
const warningRecords = computed(() => importValidationResults.value.filter(r => r.warnings.length > 0))
const updatedRecords = computed(() => importValidationResults.value.filter(r => r.success && r.action === 'update'))

const findClient = (nome, nif, cliMap, cliNifMap) => {
  if (!nome) return { entry: null, matchType: null }
  const key = nome.toLowerCase().trim()
  if (cliMap[key]) return { entry: cliMap[key], matchType: 'exact' }
  if (nif && cliNifMap[nif]) return { entry: cliNifMap[nif], matchType: 'nif' }
  const words = key.split(/\s+/).filter(w => w.length > 2)
  let candidates = Object.keys(cliMap).filter(k =>
    words.some(w => k.includes(w)) || k.split(/\s+/).some(w => key.includes(w))
  )
  if (candidates.length === 1) return { entry: cliMap[candidates[0]], matchType: 'partial' }
  if (key.length >= 4) {
    candidates = Object.keys(cliMap).filter(k => k.includes(key) || key.includes(k))
    if (candidates.length === 1) return { entry: cliMap[candidates[0]], matchType: 'substring' }
  }
  return { entry: null, matchType: null }
}

const submitExcelImport = async () => {
  importing.value = true
  importValidationResults.value = []
  showValidation.value = false

  try {
    const [{ data: funcionarios }, { data: clientes }] = await Promise.all([
      supabase.from('users').select('id, name').eq('role', 'funcionario'),
      supabase.from('users').select('id, name, nif').eq('role', 'cliente')
    ])

    const funcMap = {}
    ;(funcionarios || []).forEach(f => {
      const name = (f.name || '').toLowerCase().trim()
      if (name) funcMap[name] = f
    })
    const cliMap = {}
    const cliNifMap = {}
    ;(clientes || []).forEach(c => {
      const name = (c.name || '').toLowerCase().trim()
      if (name) cliMap[name] = c
      if (c.nif) cliNifMap[c.nif] = c
    })

    const { data: existingRefs } = await supabase.from('licenciamentos').select('referencia')
    const usedRefs = new Set((existingRefs || []).map(r => r.referencia))

    let existingItems = []
    if (updateMode.value) {
      const { data } = await supabase.from('licenciamentos').select('*')
      existingItems = data || []
    }

    const results = []
    let successCount = 0
    let updateCount = 0
    let failCount = 0

    for (let idx = 0; idx < excelData.value.length; idx++) {
      const row = excelData.value[idx]
      const errors = []
      const warnings = []

      const clienteNome = (row.empresa || '').trim()
      const shipper = (row.shipper || '').trim()
      const funcName = (row.funcionario_responsavel || '').trim()
      const nifExcel = (row.nif_empresa || row.nif || '').trim()

      let referencia = row.referencia || `LIC-${String(idx + 1).padStart(4, '0')}`
      while (usedRefs.has(referencia)) {
        referencia = referencia.replace(/-\d+$/, '') + '-' + Math.random().toString(36).substring(2, 6).toUpperCase()
      }
      usedRefs.add(referencia)

      const { entry: cliEntry, matchType: cliMatch } = findClient(clienteNome, nifExcel, cliMap, cliNifMap)

      if (!clienteNome) {
        errors.push('Nome do cliente em branco — obrigatório')
      } else if (!cliEntry) {
        errors.push(`Cliente "${clienteNome}" não existe no sistema — registe o cliente primeiro`)
      } else if (cliMatch !== 'exact') {
        warnings.push(`Cliente "${clienteNome}" identificado como "${cliEntry.name}" (${cliMatch})`)
      }

      let funcEntry = null
      if (funcName) {
        const funcKey = funcName.toLowerCase().trim()
        funcEntry = funcMap[funcKey]
        if (!funcEntry) {
          const words = funcKey.split(/\s+/).filter(w => w.length > 2)
          const matches = Object.keys(funcMap).filter(k =>
            words.some(w => k.includes(w)) || k.split(/\s+/).some(w => funcKey.includes(w))
          )
          if (matches.length === 1) {
            funcEntry = funcMap[matches[0]]
          }
        }
      }

      const tipo = row.tipo || row.tipo_licenciamento || 'Outro'

      const success = errors.length === 0
      let action = 'insert'

      if (success) {
        if (updateMode.value && nifExcel) {
          const matchIdx = existingItems.findIndex(e =>
            e.nif_empresa === nifExcel && e.tipo_licenciamento === tipo
          )
          if (matchIdx !== -1) {
            action = 'update'
            const existing = existingItems[matchIdx]
            const updateData = {
              cliente_nome: clienteNome,
              empresa: clienteNome,
              shipper: shipper,
              user_id: cliEntry?.id || existing.user_id,
              funcionario_id: funcEntry?.id || existing.funcionario_id,
              funcionario_responsavel: funcEntry ? funcEntry.name : (funcName || existing.funcionario_responsavel),
              estado: row.estado || existing.estado,
              data_submissao: row.data_submissao || existing.data_submissao,
              data_validade: row.data_validade || existing.data_validade,
              observacoes: row.observacoes || existing.observacoes,
              descricao: row.descricao || existing.descricao,
              numero_processo: row.numero_processo || existing.numero_processo,
              nif_empresa: nifExcel || existing.nif_empresa
            }
            const { error } = await supabase.from('licenciamentos').update(updateData).eq('id', existing.id)
            if (error) {
              errors.push(error.message)
              failCount++
            } else {
              updateCount++
              warnings.push(`Registo actualizado (NIF: ${nifExcel}, Tipo: ${tipo})`)
            }
            existingItems.splice(matchIdx, 1)
          }
        }

        if (action === 'insert') {
          const finalUserId = cliEntry?.id || null
          const insertRow = {
            referencia,
            user_id: finalUserId,
            numero_processo: row.numero_processo || null,
            cliente_nome: clienteNome,
            empresa: clienteNome,
            shipper: shipper,
            tipo_licenciamento: tipo,
            tipo: tipo,
            estado: row.estado || 'submetido',
            data_submissao: row.data_submissao || null,
            data_validade: row.data_validade || null,
            funcionario_id: funcEntry?.id || null,
            funcionario_responsavel: funcEntry ? funcEntry.name : funcName,
            observacoes: row.observacoes || '',
            descricao: row.descricao || '',
            nif_empresa: nifExcel || null,
            fonte: 'excel'
          }

          const { error } = await supabase.from('licenciamentos').insert(insertRow)
          if (error) {
            if (error.message.includes('duplicate') || error.message.includes('unique')) {
              insertRow.referencia = referencia + '-' + Math.random().toString(36).substring(2, 5).toUpperCase()
              const { error: retryErr } = await supabase.from('licenciamentos').insert(insertRow)
              if (retryErr) { errors.push(retryErr.message); failCount++ } else {
                successCount++
                if (finalUserId) {
                  await supabase.from('notifications').insert({
                    user_id: finalUserId,
                    type: 'licenciamento_novo',
                    title: 'Novo licenciamento adicionado',
                    body: `Foi adicionado o licenciamento ${insertRow.referencia} (${clienteNome}) ao seu account.`,
                    link: '/licenciamentos',
                    icon: 'bi-file-earmark-plus',
                    is_read: false
                  })
                }
              }
            } else {
              errors.push(error.message)
              failCount++
            }
          } else {
            successCount++
            if (finalUserId) {
              await supabase.from('notifications').insert({
                user_id: finalUserId,
                type: 'licenciamento_novo',
                title: 'Novo licenciamento adicionado',
                body: `Foi adicionado o licenciamento ${referencia} (${clienteNome}) ao seu account.`,
                link: '/licenciamentos',
                icon: 'bi-file-earmark-plus',
                is_read: false
              })
            }
          }
        }
      } else if (errors.length > 0) {
        failCount++
      }

      results.push({ idx: idx + 1, referencia, clienteNome, empresaNome: clienteNome, success: errors.length === 0, errors, warnings, action })
    }

    importValidationResults.value = results
    showValidation.value = true

    if (excelObservations.value.length > 0 && successCount > 0) {
      let obsImported = 0
      let obsSkipped = 0
      const { data: allLics } = await supabase.from('licenciamentos').select('id, numero_processo')
      const licMap = {}
      ;(allLics || []).forEach(l => {
        if (l.numero_processo) licMap[String(l.numero_processo).trim()] = l.id
      })

      for (const obs of excelObservations.value) {
        const procNum = String(obs.numero_processo || '').trim()
        const licId = licMap[procNum]
        if (!licId) { obsSkipped++; continue }

        const userFieldName = (obs.user || '').toLowerCase().trim()
        let obsUserId = null
        if (userFieldName && funcMap[userFieldName]) {
          obsUserId = funcMap[userFieldName].id
        } else if (userFieldName) {
          const words = userFieldName.split(/[.\s]+/).filter(w => w.length > 2)
          const matches = Object.keys(funcMap).filter(k =>
            words.some(w => k.includes(w)) || k.split(/[.\s]+/).some(w => userFieldName.includes(w))
          )
          if (matches.length === 1) obsUserId = funcMap[matches[0]].id
          else { obsSkipped++; continue }
        }

        const userText = obs.user ? ` [${obs.user}]` : ''
        const dateText = obs.data ? `${obs.data} ` : ''
        const obsText = `${dateText}${obs.observacao || ''}${userText}`

        const { error } = await supabase.from('licenciamento_historico').insert({
          licenciamento_id: licId,
          user_id: obsUserId,
          campo: 'observacao_excel',
          valor_antigo: null,
          valor_novo: obsText
        })
        if (!error) obsImported++
      }
      if (obsImported > 0) {
        showToast('success', `${obsImported} observação(ões) importada(s)!`)
      }
      if (obsSkipped > 0) {
        showToast('error', `${obsSkipped} observação(ões) ignorada(s) — utilizador não encontrado ou processo não existe`)
      }
    }

    if (successCount > 0) {
      showToast('success', `${successCount} licenciamento(s) importado(s) com sucesso!`)
      fetchData()
    }
    if (updateCount > 0) {
      showToast('success', `${updateCount} licenciamento(s) actualizado(s) com sucesso!`)
      fetchData()
    }
    if (failCount > 0) {
      showToast('error', `${failCount} registo(s) não importado(s). Verifique os detalhes.`)
    }
  } catch (e) {
    showToast('error', 'Erro ao processar o ficheiro Excel: ' + (e.message || e))
  } finally { importing.value = false }
}

const toast = reactive({ show: false, type: 'success', message: '' })
let toastTimer = null

const showToast = (type, message) => {
  toast.type = type
  toast.message = message
  toast.show = true
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.show = false }, 3000)
}

onMounted(fetchData)

const fixSchemaSQL = `-- Execute este SQL no Supabase SQL Editor
-- Passo 1: Abra o Supabase Dashboard → SQL Editor
-- Passo 2: Cole este código e clique em "Run"

-- Adicionar colunas em falta
ALTER TABLE public.licenciamentos ADD COLUMN IF NOT EXISTS cliente_nome VARCHAR(255);
ALTER TABLE public.licenciamentos ADD COLUMN IF NOT EXISTS tipo VARCHAR(100);
ALTER TABLE public.licenciamentos ADD COLUMN IF NOT EXISTS funcionario_responsavel VARCHAR(255);

-- Remover FK constraints (se existirem)
DO $$ DECLARE r RECORD; BEGIN
  FOR r IN (SELECT conname FROM pg_constraint WHERE conrelid = 'public.licenciamentos'::regclass AND contype = 'f') LOOP
    EXECUTE 'ALTER TABLE public.licenciamentos DROP CONSTRAINT IF EXISTS ' || quote_ident(r.conname);
  END LOOP;
END $$;

-- Desactivar RLS
ALTER TABLE public.licenciamentos DISABLE ROW LEVEL SECURITY;
ALTER TABLE public.licenciamento_historico DISABLE ROW LEVEL SECURITY;
ALTER TABLE public.licenciamento_estados_historico DISABLE ROW LEVEL SECURITY;

-- Reload schema
NOTIFY pgrst, 'reload schema';`

const copySQL = async () => {
  try {
    await navigator.clipboard.writeText(fixSchemaSQL)
    sqlCopied.value = true
    setTimeout(() => { sqlCopied.value = false }, 3000)
  } catch {
    const ta = document.createElement('textarea')
    ta.value = fixSchemaSQL
    document.body.appendChild(ta)
    ta.select()
    document.execCommand('copy')
    document.body.removeChild(ta)
    sqlCopied.value = true
    setTimeout(() => { sqlCopied.value = false }, 3000)
  }
}
</script>

<style scoped>
.schema-error-banner {
  background: #fef2f2;
  border: 2px solid #dc2626;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  display: flex;
  gap: 1.25rem;
  align-items: flex-start;
}
.schema-error-icon { color: #dc2626; font-size: 2rem; flex-shrink: 0; }
.schema-error-content h4 { color: #991b1b; margin: 0 0 0.5rem 0; font-weight: 700; }
.schema-error-content p { color: #7f1d1d; margin: 0 0 1rem 0; }
.schema-error-content ol { color: #7f1d1d; margin-bottom: 1rem; padding-left: 1.25rem; }
.schema-error-content ol li { margin-bottom: 0.35rem; }
.schema-error-content a { color: #1d4ed8; font-weight: 600; }
.sql-box { background: #1e293b; border-radius: 8px; overflow: hidden; position: relative; }
.sql-box pre { margin: 0; padding: 1rem; overflow-x: auto; }
.sql-box code { color: #e2e8f0; font-size: 0.8rem; line-height: 1.5; white-space: pre; }
.btn-copy {
  position: absolute; top: 0.5rem; right: 0.5rem; background: #3b82f6; color: white;
  border: none; padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.8rem;
  font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.35rem;
  transition: background 0.2s; z-index: 1;
}
.btn-copy:hover { background: #2563eb; }

.admin-page { background: #f8f9fa; min-height: 100vh; position: relative; }
.cursor-pointer { cursor: pointer; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-body { padding: 1.5rem; }
.filters { display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; }
.search-box { position: relative; flex: 1; min-width: 240px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input { width: 100%; padding: 0.55rem 0.75rem 0.55rem 2.25rem; border: 2px solid #e2e8f0; border-radius: 8px; }
.search-box input:focus { border-color: #2563eb; outline: none; }
.form-select { max-width: 220px; border: 2px solid #e2e8f0; border-radius: 8px; }
.tracking-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }

.stats-row { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.stat-card { background: white; border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 1rem 1.25rem; min-width: 110px; text-align: center; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #1e293b; }
.stat-label { font-size: 0.7rem; color: #64748b; margin-top: 0.15rem; text-transform: uppercase; letter-spacing: 0.03em; }

.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.status-rascunho { background: #f1f5f9; color: #475569; }
.status-pendente_cliente { background: #fef9c3; color: #854d0e; }
.status-submetido { background: #dbeafe; color: #1e40af; }
.status-em_analise { background: #cffafe; color: #155e75; }
.status-aprovado { background: #d1fae5; color: #065f46; }
.status-indeferido { background: #fee2e2; color: #991b1b; }
.status-expira_brevemente { background: #ffedd5; color: #9a3412; }
.status-expirado { background: #fecaca; color: #7f1d1d; }

.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1050; }
.modal-content { background: white; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
.modal-content.modal-sm { max-width: 400px; }
.modal-content.modal-lg { max-width: 700px; }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
.modal-header h5 { margin: 0; font-weight: 600; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem; }

.validation-stat { border-radius: 10px; padding: 0.75rem; text-align: center; }
.validation-stat .stat-num { display: block; font-size: 1.5rem; font-weight: 700; }
.validation-stat .stat-txt { display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.03em; }

.alert-rejected { border-radius: 12px; border: 1px solid #fecdd3; background: #fff1f2; overflow: hidden; }
.alert-rejected-header { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #fecdd3; color: #991b1b; font-weight: 600; font-size: 0.85rem; }
.alert-rejected-header i { font-size: 1.1rem; }
.alert-rejected-list { padding: 8px; max-height: 250px; overflow-y: auto; }
.rejected-item { padding: 10px 12px; border-radius: 8px; background: #fff; margin-bottom: 6px; border: 1px solid #fecdd3; }
.rejected-item:last-child { margin-bottom: 0; }
.rejected-item-header { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 6px; }
.rejected-idx { font-size: 0.75rem; color: #991b1b; font-weight: 700; background: #fecdd3; padding: 2px 8px; border-radius: 20px; }
.rejected-ref { font-size: 0.8rem; color: #1e293b; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; border: 1px solid #e2e8f0; }
.rejected-client, .rejected-company { font-size: 0.8rem; color: #64748b; }
.rejected-item-errors { display: flex; flex-direction: column; gap: 3px; padding-left: 4px; }
.rejected-error { font-size: 0.8rem; color: #dc2626; display: flex; align-items: center; gap: 5px; }
.rejected-error i { font-size: 0.7rem; }

.alert-success-custom { border-radius: 12px; border: 1px solid #a7f3d0; background: #f0fdf4; overflow: hidden; }
.alert-success-header { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #a7f3d0; color: #065f46; font-weight: 600; font-size: 0.85rem; }
.alert-success-header i { font-size: 1.1rem; }

.alert-updated { border-radius: 12px; border: 1px solid #bfdbfe; background: #eff6ff; overflow: hidden; }
.alert-updated-header { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #bfdbfe; color: #1e40af; font-weight: 600; font-size: 0.85rem; }
.alert-updated-header i { font-size: 1.1rem; }
.updated-list { padding: 8px; }
.updated-item { padding: 6px 10px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.updated-ref { font-size: 0.78rem; color: #1e40af; font-weight: 600; background: #bfdbfe; padding: 2px 8px; border-radius: 20px; }
.updated-msg { font-size: 0.8rem; color: #1d4ed8; }

.alert-warning-custom { border-radius: 12px; border: 1px solid #fde68a; background: #fffbeb; overflow: hidden; }
.alert-warning-header { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #fde68a; color: #92400e; font-weight: 600; font-size: 0.85rem; }
.alert-warning-header i { font-size: 1.1rem; }
.warning-list { padding: 8px; }
.warning-item { padding: 6px 10px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.warning-ref { font-size: 0.78rem; color: #92400e; font-weight: 600; background: #fde68a; padding: 2px 8px; border-radius: 20px; }
.warning-msg { font-size: 0.8rem; color: #a16207; }

.update-mode-toggle { display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
.update-mode-toggle input { display: none; }
.toggle-slider { width: 40px; height: 22px; background: #cbd5e1; border-radius: 11px; position: relative; transition: background 0.2s; }
.toggle-slider::after { content: ''; position: absolute; width: 18px; height: 18px; background: white; border-radius: 50%; top: 2px; left: 2px; transition: transform 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.update-mode-toggle input:checked + .toggle-slider { background: #2563eb; }
.update-mode-toggle input:checked + .toggle-slider::after { transform: translateX(18px); }
.toggle-label { font-size: 0.8rem; color: #475569; font-weight: 500; }
.toast-container { position: fixed; top: 20px; right: 20px; padding: 0.75rem 1.25rem; border-radius: 8px; color: white; font-weight: 500; z-index: 1100; animation: slideIn 0.3s ease; }
.toast-success { background: #059669; }
.toast-error { background: #dc2626; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

.pagination-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1.25rem;
  border-top: 1px solid #f1f5f9;
}
.page-info {
  font-size: 0.82rem;
  color: #64748b;
  font-weight: 500;
}
.page-btns {
  display: flex;
  gap: 6px;
}
.page-btn {
  width: 34px;
  height: 34px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}
.page-btn:hover:not(:disabled) { border-color: #0f766e; color: #0f766e; background: #f0fdfa; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
</style>
