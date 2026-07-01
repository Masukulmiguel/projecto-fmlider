<template>
<!-- cache-bust-2 -->
  <div class="admin-page">
    <div class="page-header">
      <h1>Licenciamentos</h1>
      <div class="header-actions">
        <button class="btn btn-import" @click="showImportModal = true">
          <i class="bi bi-file-earmark-excel"></i>
          Importar Excel
        </button>
        <button class="btn btn-primary" @click="openCreateModal">
          <i class="bi bi-plus-lg"></i>
          Novo Licenciamento
        </button>
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

    <!-- Stat Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background: #dbeafe; color: #1e40af;">
          <i class="bi bi-file-earmark-text"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.total }}</span>
          <span class="stat-label">Total</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #92400e;">
          <i class="bi bi-clock"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.pendentes }}</span>
          <span class="stat-label">Pendentes</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #cffafe; color: #155e75;">
          <i class="bi bi-arrow-repeat"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.emAnalise }}</span>
          <span class="stat-label">Em Análise</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #d1fae5; color: #065f46;">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.aprovados }}</span>
          <span class="stat-label">Aprovados</span>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input
          v-model="filters.search"
          type="text"
          placeholder="Pesquisar por referência, processo ou cliente..."
          @input="debouncedSearch"
        />
      </div>
      <select v-model="filters.estado" @change="fetchLicenciamentos">
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
      <select v-model="filters.tipo" @change="fetchLicenciamentos">
        <option value="">Todos os tipos</option>
        <option value="importacao_definitiva">Importação Definitiva</option>
        <option value="importacao_temporaria">Importação Temporária</option>
        <option value="exportacao">Exportação</option>
        <option value="transito_aduaneiro">Trânsito Aduaneiro</option>
        <option value="cabotagem">Cabotagem</option>
      </select>
    </div>

    <!-- Table -->
    <div class="table-container">
      <table class="data-table">
        <thead>
          <tr>
            <th>Referência</th>
            <th>Nº Processo</th>
            <th>Cliente</th>
            <th>Shipper</th>
            <th>Tipo</th>
            <th>Estado</th>
            <th>Data Submissão</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="8" class="loading-cell">
              <div class="spinner"></div>
            </td>
          </tr>
          <tr v-else-if="licenciamentos.length === 0">
            <td colspan="8" class="empty-cell">
              Nenhum licenciamento encontrado.
            </td>
          </tr>
          <tr v-for="item in licenciamentos" :key="item.id">
            <td>{{ item.referencia }}</td>
            <td>{{ item.numero_processo }}</td>
            <td>{{ item.empresa || '-' }}</td>
            <td>{{ item.shipper || '-' }}</td>
            <td>{{ formatTipo(item.tipo) }}</td>
            <td>
              <span :class="['status-badge', `status-${item.estado}`]">
                {{ formatEstado(item.estado) }}
              </span>
            </td>
            <td>{{ formatDate(item.data_submissao) }}</td>
            <td class="actions-cell">
              <button class="btn-icon btn-view" @click="viewDetails(item)" :title="t('common.view')">
                <i class="bi bi-eye"></i>
              </button>
              <button class="btn-icon btn-edit" @click="openEditModal(item)" :title="t('common.edit')">
                <i class="bi bi-pencil-square"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="pagination">
      <button class="btn-page" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">
        <i class="bi bi-chevron-left"></i>
      </button>
      <span class="page-info">{{ currentPage }} / {{ totalPages }}</span>
      <button class="btn-page" :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
      <div class="custom-modal">
        <div class="modal-header">
          <h2>Editar Licenciamento</h2>
          <button class="btn-close" @click="closeEditModal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Estado</label>
            <select v-model="editForm.estado">
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
          </div>
          <div class="form-group">
            <label>Observações</label>
            <textarea v-model="editForm.observacoes" rows="4"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeEditModal">{{ t('common.cancel') }}</button>
          <button class="btn btn-primary" @click="saveEdit" :disabled="saving">
            <span v-if="saving" class="spinner-small"></span>
            {{ t('common.save') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Import Modal -->
    <div v-if="showImportModal" class="modal-overlay" @click.self="closeImportModal">
      <div class="custom-modal">
        <div class="modal-header">
          <h2>Importar Licenciamentos</h2>
          <button class="btn-close" @click="closeImportModal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="modal-body">
          <template v-if="showValidation">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0">Resultados da Importação</h6>
              <button class="btn btn-sm btn-outline-secondary" @click="showValidation = false; importData = []">
                <i class="bi bi-arrow-left me-1"></i>Novo ficheiro
              </button>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-4">
                <div class="validation-stat bg-success-subtle text-success">
                  <span class="stat-num">{{ importResults.filter(r => r.success).length }}</span>
                  <span class="stat-txt">Importados</span>
                </div>
              </div>
              <div class="col-4">
                <div class="validation-stat bg-danger-subtle text-danger">
                  <span class="stat-num">{{ importResults.filter(r => !r.success).length }}</span>
                  <span class="stat-txt">Rejeitados</span>
                </div>
              </div>
              <div class="col-4">
                <div class="validation-stat bg-warning-subtle text-warning">
                  <span class="stat-num">{{ importResults.filter(r => r.warnings.length > 0).length }}</span>
                  <span class="stat-txt">Avisos</span>
                </div>
              </div>
            </div>

            <div v-if="funcFailedRecords.length > 0" class="alert-rejected mb-3">
              <div class="alert-rejected-header">
                <i class="bi bi-shield-exclamation"></i>
                <span>Registos Rejeitados — Corrija o ficheiro Excel e volte a importar</span>
              </div>
              <div class="alert-rejected-list">
                <div v-for="r in funcFailedRecords" :key="r.idx" class="rejected-item">
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

            <div v-if="funcSuccessRecords.length > 0" class="alert-success-custom mb-3">
              <div class="alert-success-header">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ funcSuccessRecords.length }} registo(s) importado(s) com sucesso</span>
              </div>
            </div>

            <div v-if="funcUpdatedRecords.length > 0" class="alert-updated mb-3">
              <div class="alert-updated-header">
                <i class="bi bi-arrow-repeat"></i>
                <span>{{ funcUpdatedRecords.length }} registo(s) actualizado(s) com sucesso</span>
              </div>
              <div class="updated-list">
                <div v-for="r in funcUpdatedRecords" :key="r.idx" class="updated-item">
                  <span class="updated-ref">#{{ r.idx }} {{ r.referencia }}</span>
                  <span v-for="(w, i) in r.warnings" :key="i" class="updated-msg">{{ w }}</span>
                </div>
              </div>
            </div>

            <div v-if="funcWarningRecords.length > 0" class="alert-warning-custom mb-3">
              <div class="alert-warning-header">
                <i class="bi bi-info-circle-fill"></i>
                <span>{{ funcWarningRecords.length }} registo(s) com avisos</span>
              </div>
              <div class="warning-list">
                <div v-for="r in funcWarningRecords" :key="r.idx" class="warning-item">
                  <span class="warning-ref">#{{ r.idx }} {{ r.referencia }}</span>
                  <span v-for="(w, i) in r.warnings" :key="i" class="warning-msg">{{ w }}</span>
                </div>
              </div>
            </div>
          </template>
          <template v-else>
            <div v-if="importData.length === 0 && funcExcelSheets.length > 1 && !funcSelectedSheet">
              <p class="text-muted mb-3">Este ficheiro tem várias sheets. Qual pretende importar?</p>
              <div class="d-flex flex-wrap gap-2 mb-3">
                <button v-for="sheet in funcExcelSheets" :key="sheet" class="btn btn-outline-primary" @click="funcSelectedSheet = sheet; onFuncSheetSelect()">
                  <i class="bi bi-file-earmark-table me-1"></i>{{ sheet }}
                </button>
              </div>
            </div>
            <div v-else>
              <p class="text-muted mb-3">Selecione um ficheiro Excel (.xlsx, .xls ou .csv). O sistema importa os dados com as seguintes regras:</p>
              <ul class="text-muted small mb-3">
                <li><strong>Sheet Licenciamentos</strong> — valida o <strong>Cliente</strong> (deve existir como utilizador no sistema)</li>
                <li><strong>Sheet Observações</strong> — valida o <strong>Funcionário</strong> (deve existir como utilizador no sistema)</li>
                <li>Os dados são importados automaticamente ao clicar em "Validar e Importar"</li>
              </ul>
              <div class="upload-area" @dragover.prevent @drop.prevent="handleDrop">
                <i class="bi bi-cloud-upload"></i>
                <p>Arraste um ficheiro Excel ou clique para selecionar</p>
                <input type="file" ref="fileInput" accept=".xlsx,.xls,.csv" @change="handleFileSelect" hidden />
                <button class="btn btn-primary" @click="$refs.fileInput.click()">
                  Selecionar Ficheiro
                </button>
              </div>
              <div v-if="importData.length > 0" class="import-preview">
                <h3>Pré-visualização ({{ importData.length }} registo(s))</h3>
                <table class="preview-table">
                  <thead>
                    <tr>
                      <th>Referência</th>
                      <th>Cliente</th>
                      <th>Tipo</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, index) in importData.slice(0, 5)" :key="index">
                      <td>{{ row.referencia }}</td>
                      <td>{{ row.cliente }}</td>
                      <td>{{ row.tipo }}</td>
                    </tr>
                  </tbody>
                </table>
                <p v-if="importData.length > 5" class="more-records">
                  ...e mais {{ importData.length - 5 }} registo(s)
                </p>
              </div>
            </div>
          </template>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeImportModal">{{ t('common.cancel') }}</button>
          <div v-if="importData.length > 0 && !showValidation" class="d-flex align-items-center gap-3">
            <label class="update-mode-toggle">
              <input type="checkbox" v-model="updateMode" />
              <span class="toggle-slider"></span>
              <span class="toggle-label">Actualizar existentes (NIF + Tipo)</span>
            </label>
            <button class="btn btn-primary" @click="processImport" :disabled="importing || importData.length === 0">
              <span v-if="importing" class="spinner-small"></span>
              <i v-else class="bi bi-shield-check me-1"></i>
              Validar e Importar
            </button>
          </div>
          <button v-if="showValidation" class="btn btn-success" @click="closeImportModal">
            <i class="bi bi-check-lg me-1"></i>Concluir
          </button>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="showDetailsModal" class="modal-overlay" @click.self="closeDetailsModal">
      <div class="custom-modal custom-modal-large">
        <div class="modal-header">
          <h2>Detalhe do Licenciamento</h2>
          <button class="btn-close" @click="closeDetailsModal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="modal-body" v-if="selectedItem">
          <div class="details-grid">
            <div class="detail-item">
              <label>Referência</label>
              <span>{{ selectedItem.referencia }}</span>
            </div>
            <div class="detail-item">
              <label>Nº Processo</label>
              <span>{{ selectedItem.numero_processo || '-' }}</span>
            </div>
            <div class="detail-item">
              <label>Cliente</label>
              <span>{{ selectedItem.empresa || '-' }}</span>
            </div>
            <div class="detail-item">
              <label>Empresa</label>
              <span>{{ selectedItem.empresa || '-' }}</span>
            </div>
            <div class="detail-item">
              <label>Tipo</label>
              <span>{{ formatTipo(selectedItem.tipo) }}</span>
            </div>
            <div class="detail-item">
              <label>Estado</label>
              <span :class="['status-badge', `status-${selectedItem.estado}`]">
                {{ formatEstado(selectedItem.estado) }}
              </span>
            </div>
            <div class="detail-item">
              <label>Data Submissão</label>
              <span>{{ formatDate(selectedItem.data_submissao) }}</span>
            </div>
            <div class="detail-item">
              <label>Data Validade</label>
              <span>{{ formatDate(selectedItem.data_validade) }}</span>
            </div>
          </div>
          <div class="detail-item full-width" v-if="selectedItem.observacoes">
            <label>Observações</label>
            <p>{{ selectedItem.observacoes }}</p>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDetailsModal">{{ t('common.close') }}</button>
          <button class="btn btn-primary" @click="closeDetailsModal(); openEditModal(selectedItem)">
            {{ t('common.edit') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container">
      <div v-for="toast in toasts" :key="toast.id" :class="['toast', `toast-${toast.type}`]">
        <i :class="toast.icon"></i>
        <span>{{ toast.message }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useI18n } from '@/composables/useI18n'
import { useAuthStore } from '@/stores/authStore'
import * as XLSX from 'xlsx'

const { t } = useI18n()
const authStore = useAuthStore()

const loading = ref(false)
const saving = ref(false)
const importing = ref(false)
const updateMode = ref(false)
const licenciamentos = ref([])
const currentPage = ref(1)
const totalPages = ref(1)
const pageSize = 20
const schemaError = ref(false)
const sqlCopied = ref(false)

const stats = reactive({
  total: 0,
  pendentes: 0,
  emAnalise: 0,
  aprovados: 0
})

const filters = reactive({
  search: '',
  estado: '',
  tipo: ''
})

const showEditModal = ref(false)
const showImportModal = ref(false)
const showDetailsModal = ref(false)
const selectedItem = ref(null)
const editForm = reactive({
  id: null,
  estado: '',
  observacoes: ''
})

const importData = ref([])
const funcExcelObservations = ref([])
const toasts = ref([])

let searchTimeout = null

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    fetchLicenciamentos()
  }, 300)
}

const showToast = (message, type = 'success') => {
  const icons = {
    success: 'bi bi-check-circle-fill',
    error: 'bi bi-exclamation-circle-fill',
    warning: 'bi bi-exclamation-triangle-fill'
  }
  const toast = {
    id: Date.now(),
    message,
    type,
    icon: icons[type]
  }
  toasts.value.push(toast)
  setTimeout(() => {
    toasts.value = toasts.value.filter(t => t.id !== toast.id)
  }, 4000)
}

const fetchLicenciamentos = async () => {
  loading.value = true
  schemaError.value = false
  try {
    const from = (currentPage.value - 1) * pageSize
    const to = from + pageSize - 1

    let query = supabase
      .from('licenciamentos')
      .select('*')

    if (filters.search) {
      query = query.or(`referencia.ilike.%${filters.search}%,numero_processo.ilike.%${filters.search}%`)
    }
    if (filters.estado) {
      query = query.eq('estado', filters.estado)
    }
    if (filters.tipo) {
      query = query.eq('tipo', filters.tipo)
    }

    const { data, error } = await query
      .order('created_at', { ascending: false })
      .range(from, to)

    if (error) {
      console.error('Supabase error:', error)
      if (error.message && (error.message.includes('relationship') || error.message.includes('42P17'))) {
        schemaError.value = true
        return
      }
      throw error
    }

    licenciamentos.value = data || []

    const { count } = await supabase
      .from('licenciamentos')
      .select('id', { count: 'exact', head: true })
    totalPages.value = Math.ceil((count || 0) / pageSize)

    fetchStats().catch(() => {})
  } catch (error) {
    console.error('fetchLicenciamentos error:', error)
    if (error.message && error.message.includes('relationship')) {
      schemaError.value = true
    } else {
      showToast(error.message || 'Erro ao carregar licenciamentos', 'error')
    }
  } finally {
    loading.value = false
  }
}

const fetchStats = async () => {
  try {
    const { count: total } = await supabase
      .from('licenciamentos')
      .select('id', { count: 'exact', head: true })

    const { count: pendentes } = await supabase
      .from('licenciamentos')
      .select('id', { count: 'exact', head: true })
      .in('estado', ['pendente_cliente', 'submetido'])

    const { count: emAnalise } = await supabase
      .from('licenciamentos')
      .select('id', { count: 'exact', head: true })
      .eq('estado', 'em_analise')

    const { count: aprovados } = await supabase
      .from('licenciamentos')
      .select('id', { count: 'exact', head: true })
      .eq('estado', 'aprovado')

    stats.total = total || 0
    stats.pendentes = pendentes || 0
    stats.emAnalise = emAnalise || 0
    stats.aprovados = aprovados || 0
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const changePage = (page) => {
  currentPage.value = page
  fetchLicenciamentos()
}

const openCreateModal = () => {
  editForm.id = null
  editForm.estado = 'rascunho'
  editForm.observacoes = ''
  showEditModal.value = true
}

const openEditModal = (item) => {
  editForm.id = item.id
  editForm.estado = item.estado
  editForm.observacoes = item.observacoes || ''
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editForm.id = null
  editForm.estado = ''
  editForm.observacoes = ''
}

const saveEdit = async () => {
  saving.value = true
  try {
    const updateData = {
      estado: editForm.estado,
      observacoes: editForm.observacoes,
      updated_at: new Date().toISOString()
    }

    if (editForm.id) {
      const { error } = await supabase
        .from('licenciamentos')
        .update(updateData)
        .eq('id', editForm.id)

      if (error) throw error
      showToast('Licenciamento atualizado com sucesso!')
    } else {
      updateData.user_id = authStore.user?.id
      updateData.data_submissao = new Date().toISOString()

      const { error } = await supabase
        .from('licenciamentos')
        .insert([updateData])

      if (error) throw error
      showToast('Licenciamento criado com sucesso!')
    }

    closeEditModal()
    await fetchLicenciamentos()
  } catch (error) {
    showToast(error.message, 'error')
  } finally {
    saving.value = false
  }
}

const viewDetails = (item) => {
  selectedItem.value = item
  showDetailsModal.value = true
}

const closeDetailsModal = () => {
  showDetailsModal.value = false
  selectedItem.value = null
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) parseExcel(file)
}

const handleDrop = (event) => {
  const file = event.dataTransfer.files[0]
  if (file) parseExcel(file)
}

const funcColumnMap = {
  'referência': 'referencia', 'referencia': 'referencia', 'ref': 'referencia',
  'refª fmlider': 'numero_processo', 'refª cliente': 'ref_cliente',
  'cliente': 'empresa', 'cliente_nome': 'empresa', 'nome cliente': 'empresa', 'nome do cliente': 'empresa',
  'tipo': 'tipo', 'tipo licenciamento': 'tipo', 'tipo_licenciamento': 'tipo',
  'empresa': 'empresa', 'nome empresa': 'empresa', 'shipper': 'shipper', 'grupo': 'grupo',
  'nº processo': 'numero_processo', 'no processo': 'numero_processo', 'numero processo': 'numero_processo', 'processo': 'numero_processo',
  'nº registo': 'numero_processo', 'nº pedido': 'pedido', 'nº pfi': 'pfi',
  'nº licenciamento': 'num_licenciamento',
  'funcionário responsável': 'funcionario_responsavel', 'funcionario responsavel': 'funcionario_responsavel', 'funcionario': 'funcionario_responsavel',
  'user': 'funcionario_responsavel',
  'observações': 'observacoes', 'observacoes': 'observacoes', 'notas': 'observacoes',
  'descrição': 'descricao', 'descricao': 'descricao', 'produto/mercadoria': 'descricao',
  'data': 'data_submissao', 'data submissão': 'data_submissao',
  'validade': 'data_validade', 'data validade': 'data_validade',
  'estado': 'estado',
  'nif': 'nif_empresa', 'nif empresa': 'nif_empresa',
  'códº': 'codigo', 'cap': 'capacidade', 'qtd': 'quantidade',
  'aprovado': 'aprovado_data', 'nº licenciamento': 'num_licenciamento', 'motivo': 'motivo'
}

const funcExcelSheets = ref([])
const funcSelectedSheet = ref('')

const parseExcel = async (file) => {
  try {
    const data = await file.arrayBuffer()
    const workbook = XLSX.read(data)
    funcExcelSheets.value = workbook.SheetNames

    const licSheet = workbook.SheetNames.find(s => /licenciamento/i.test(s))
    const obsSheet = workbook.SheetNames.find(s => /observa/i.test(s))

    if (licSheet) {
      parseFuncSheet(workbook, licSheet)
      if (obsSheet) {
        funcExcelObservations.value = parseFuncObsSheet(workbook, obsSheet)
      }
    } else if (workbook.SheetNames.length === 1) {
      parseFuncSheet(workbook, workbook.SheetNames[0])
    } else {
      funcSelectedSheet.value = ''
      importData.value = []
      window._funcWorkbook = workbook
    }
  } catch (error) {
    showToast('Erro ao processar o ficheiro Excel.', 'error')
  }
}

const parseFuncSheet = (workbook, sheetName) => {
  const worksheet = workbook.Sheets[sheetName]
  const rawData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' })

  if (rawData.length === 0) {
    showToast('A sheet está vazia.', 'error')
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
    showToast('A sheet não contém dados válidos.', 'error')
    return
  }

  const mappedData = jsonData.map((row, idx) => {
    const mapped = {}
    Object.keys(row).forEach(h => {
      const key = funcColumnMap[h.toLowerCase().trim()] || h
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
    return {
      referencia: mapped.referencia || `LIC-${String(idx + 1).padStart(4, '0')}`,
      cliente: mapped.cliente || mapped.cliente_nome || '',
      tipo: mapped.tipo || '',
      empresa: mapped.empresa || '',
      numero_processo: mapped.numero_processo || '',
      funcionario_responsavel: mapped.funcionario_responsavel || '',
      observacoes: mapped.observacoes || '',
      descricao: mapped.descricao || '',
      data_submissao: mapped.data_submissao || '',
      data_validade: mapped.data_validade || '',
      estado: mapped.estado || ''
    }
  })

  importData.value = mappedData
  showToast(`${mappedData.length} registo(s) encontrado(s) no ficheiro`)
}

const onFuncSheetSelect = () => {
  if (funcSelectedSheet.value && window._funcWorkbook) {
    if (/observa/i.test(funcSelectedSheet.value)) {
      funcExcelObservations.value = parseFuncObsSheet(window._funcWorkbook, funcSelectedSheet.value)
      importData.value = []
    } else {
      parseFuncSheet(window._funcWorkbook, funcSelectedSheet.value)
    }
  }
}

const parseFuncObsSheet = (workbook, sheetName) => {
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

const importResults = ref([])

const funcFailedRecords = computed(() => importResults.value.filter(r => !r.success))
const funcSuccessRecords = computed(() => importResults.value.filter(r => r.success && r.action === 'insert' && r.warnings.length === 0))
const funcWarningRecords = computed(() => importResults.value.filter(r => r.warnings.length > 0))
const funcUpdatedRecords = computed(() => importResults.value.filter(r => r.success && r.action === 'update'))
const showValidation = ref(false)

const findClientFunc = (nome, nif, cliMap, cliNifMap) => {
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

const processImport = async () => {
  importing.value = true
  importResults.value = []
  showValidation.value = false

  try {
    const [{ data: funcionarios }, { data: companies }] = await Promise.all([
      supabase.from('users').select('id, name').eq('role', 'funcionario'),
      supabase.from('companies').select('user_id, company_name, nif')
    ])

    const funcMap = {}
    ;(funcionarios || []).forEach(f => {
      const name = (f.name || '').toLowerCase().trim()
      if (name) funcMap[name] = f
    })
    const cliMap = {}
    const cliNifMap = {}
    ;(companies || []).forEach(c => {
      const name = (c.company_name || '').toLowerCase().trim()
      const entry = { id: c.user_id, name: c.company_name, nif: c.nif }
      if (name) cliMap[name] = entry
      if (c.nif) cliNifMap[c.nif] = entry
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

    for (let idx = 0; idx < importData.value.length; idx++) {
      const item = importData.value[idx]
      const errors = []
      const warnings = []

      const clienteNome = (item.empresa || '').trim()
      const shipper = (item.shipper || '').trim()
      const funcName = (item.funcionario_responsavel || '').trim()
      const nifExcel = (item.nif_empresa || item.nif || '').trim()

      let referencia = item.referencia || `LIC-${String(idx + 1).padStart(4, '0')}`
      while (usedRefs.has(referencia)) {
        referencia = referencia.replace(/-\d+$/, '') + '-' + Math.random().toString(36).substring(2, 6).toUpperCase()
      }
      usedRefs.add(referencia)

      const { entry: cliEntry, matchType: cliMatch } = findClientFunc(clienteNome, nifExcel, cliMap, cliNifMap)

      if (!clienteNome) {
        errors.push('Nome do cliente em branco — obrigatório')
      } else if (!cliEntry) {
        warnings.push(`Cliente "${clienteNome}" não encontrado no sistema — será importado sem vinculação`)
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

      const tipo = item.tipo || item.tipo_licenciamento || 'Outro'

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
              estado: item.estado || existing.estado,
              data_submissao: item.data_submissao || existing.data_submissao,
              data_validade: item.data_validade || existing.data_validade,
              observacoes: item.observacoes || existing.observacoes,
              descricao: item.descricao || existing.descricao,
              numero_processo: item.numero_processo || existing.numero_processo,
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
          const finalUserId = cliEntry?.id || authStore.user?.id
          const record = {
            referencia,
            user_id: finalUserId,
            numero_processo: item.numero_processo || null,
            cliente_nome: clienteNome,
            empresa: clienteNome,
            shipper: shipper,
            tipo_licenciamento: tipo,
            tipo: tipo,
            estado: item.estado || 'submetido',
            data_submissao: item.data_submissao || new Date().toISOString(),
            data_validade: item.data_validade || null,
            funcionario_id: funcEntry?.id || null,
            funcionario_responsavel: funcEntry ? funcEntry.name : funcName,
            observacoes: item.observacoes || '',
            descricao: item.descricao || '',
            nif_empresa: nifExcel || null,
            fonte: 'excel'
          }

          const { error } = await supabase.from('licenciamentos').insert([record])
          if (error) {
            if (error.message.includes('duplicate') || error.message.includes('unique')) {
              record.referencia = referencia + '-' + Math.random().toString(36).substring(2, 5).toUpperCase()
              const { error: retryErr } = await supabase.from('licenciamentos').insert([record])
              if (retryErr) { errors.push(retryErr.message); failCount++ } else {
                successCount++
                if (finalUserId) {
                  await supabase.from('notifications').insert({
                    user_id: finalUserId,
                    type: 'licenciamento_novo',
                    title: 'Novo licenciamento adicionado',
                    body: `Foi adicionado o licenciamento ${record.referencia} (${clienteNome}) ao seu account.`,
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

    importResults.value = results
    showValidation.value = true

    if (funcExcelObservations.value.length > 0 && successCount > 0) {
      let obsImported = 0
      let obsSkipped = 0
      const { data: allLics } = await supabase.from('licenciamentos').select('id, numero_processo')
      const licMap = {}
      ;(allLics || []).forEach(l => {
        if (l.numero_processo) licMap[String(l.numero_processo).trim()] = l.id
      })

      for (const obs of funcExcelObservations.value) {
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
        showToast(`${obsImported} observação(ões) importada(s)!`)
      }
      if (obsSkipped > 0) {
        showToast(`${obsSkipped} observação(ões) ignorada(s) — utilizador não encontrado ou processo não existe`, 'error')
      }
    }

    if (successCount > 0) {
      showToast(`${successCount} licenciamento(s) importado(s) com sucesso!`)
      await fetchLicenciamentos()
    }
    if (updateCount > 0) {
      showToast(`${updateCount} licenciamento(s) actualizado(s) com sucesso!`)
      await fetchLicenciamentos()
    }
    if (failCount > 0) {
      showToast(`${failCount} registo(s) não importado(s). Verifique os detalhes.`, 'error')
    }
  } catch (error) {
    showToast(error.message || 'Erro ao processar o ficheiro Excel.', 'error')
  } finally {
    importing.value = false
  }
}

const closeImportModal = () => {
  showImportModal.value = false
  importData.value = []
  funcExcelObservations.value = []
  showValidation.value = false
  importResults.value = []
  funcExcelSheets.value = []
  funcSelectedSheet.value = ''
  if (window._funcWorkbook) delete window._funcWorkbook
}

const formatEstado = (estado) => {
  const labels = {
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
  }
  return labels[estado] || estado
}

const formatTipo = (tipo) => {
  const labels = {
    importacao_definitiva: 'Importação Definitiva',
    importacao_temporaria: 'Importação Temporária',
    exportacao: 'Exportação',
    transito_aduaneiro: 'Trânsito Aduaneiro',
    cabotagem: 'Cabotagem',
    importacao: 'Importação',
    transito: 'Trânsito',
    temporario: 'Temporário',
    seco: 'Seco',
    cong: 'Congelado',
    outro: 'Outro'
  }
  return labels[tipo] || tipo
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('pt-AO')
}

onMounted(() => {
  fetchLicenciamentos()
})

const fixSchemaSQL = `-- Execute este SQL no Supabase SQL Editor
-- Passo 1: Abra o Supabase Dashboard → SQL Editor
-- Passo 2: Cole este código e clique em "Run"

-- Adicionar colunas em falta
ALTER TABLE public.licenciamentos ADD COLUMN IF NOT EXISTS cliente_nome VARCHAR(255);
ALTER TABLE public.licenciamentos ADD COLUMN IF NOT EXISTS tipo VARCHAR(100);
ALTER TABLE public.licenciamentos ADD COLUMN IF NOT EXISTS funcionario_responsavel VARCHAR(255);

-- Remover todas as FK constraints da tabela licenciamentos
DO $$ DECLARE r RECORD; BEGIN
  FOR r IN (SELECT conname FROM pg_constraint WHERE conrelid = 'public.licenciamentos'::regclass AND contype = 'f') LOOP
    EXECUTE 'ALTER TABLE public.licenciamentos DROP CONSTRAINT IF EXISTS ' || quote_ident(r.conname);
  END LOOP;
END $$;

-- Remover FK constraints das tabelas auxiliares
DO $$ DECLARE r RECORD; BEGIN
  FOR r IN (SELECT conname FROM pg_constraint WHERE conrelid = 'public.licenciamento_historico'::regclass AND contype = 'f') LOOP
    EXECUTE 'ALTER TABLE public.licenciamento_historico DROP CONSTRAINT IF EXISTS ' || quote_ident(r.conname);
  END LOOP;
END $$;

DO $$ DECLARE r RECORD; BEGIN
  FOR r IN (SELECT conname FROM pg_constraint WHERE conrelid = 'public.licenciamento_estados_historico'::regclass AND contype = 'f') LOOP
    EXECUTE 'ALTER TABLE public.licenciamento_estados_historico DROP CONSTRAINT IF EXISTS ' || quote_ident(r.conname);
  END LOOP;
END $$;

-- Garantir RLS desactivado
ALTER TABLE public.licenciamentos DISABLE ROW LEVEL SECURITY;
ALTER TABLE public.licenciamento_historico DISABLE ROW LEVEL SECURITY;
ALTER TABLE public.licenciamento_estados_historico DISABLE ROW LEVEL SECURITY;

-- Recarregar schema do PostgREST
NOTIFY pgrst, 'reload schema';`

const copySQL = async () => {
  try {
    await navigator.clipboard.writeText(fixSchemaSQL)
    sqlCopied.value = true
    setTimeout(() => { sqlCopied.value = false }, 3000)
  } catch {
    const textarea = document.createElement('textarea')
    textarea.value = fixSchemaSQL
    document.body.appendChild(textarea)
    textarea.select()
    document.execCommand('copy')
    document.body.removeChild(textarea)
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

.schema-error-icon {
  color: #dc2626;
  font-size: 2rem;
  flex-shrink: 0;
}

.schema-error-content h4 {
  color: #991b1b;
  margin: 0 0 0.5rem 0;
  font-weight: 700;
}

.schema-error-content p {
  color: #7f1d1d;
  margin: 0 0 1rem 0;
}

.schema-error-content ol {
  color: #7f1d1d;
  margin-bottom: 1rem;
  padding-left: 1.25rem;
}

.schema-error-content ol li {
  margin-bottom: 0.35rem;
}

.schema-error-content a {
  color: #1d4ed8;
  font-weight: 600;
}

.sql-box {
  background: #1e293b;
  border-radius: 8px;
  overflow: hidden;
  position: relative;
}

.sql-box pre {
  margin: 0;
  padding: 1rem;
  overflow-x: auto;
}

.sql-box code {
  color: #e2e8f0;
  font-size: 0.8rem;
  line-height: 1.5;
  white-space: pre;
}

.btn-copy {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.35rem;
  transition: background 0.2s;
  z-index: 1;
}

.btn-copy:hover {
  background: #2563eb;
}

.admin-page {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 1.75rem;
  color: #1a365d;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 1rem;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary {
  background: #1a365d;
  color: white;
}

.btn-primary:hover {
  background: #2c5282;
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.btn-import {
  background: #065f46;
  color: white;
}

.btn-import:hover {
  background: #047857;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 0.75rem;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a365d;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-box i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.search-box input {
  width: 100%;
  padding: 0.625rem 1rem 0.625rem 2.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.search-box input:focus {
  outline: none;
  border-color: #1a365d;
  box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.1);
}

.filters select {
  padding: 0.625rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  background: white;
  min-width: 150px;
}

.filters select:focus {
  outline: none;
  border-color: #1a365d;
}

.table-container {
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  background: #f9fafb;
  padding: 0.875rem 1rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  font-size: 0.875rem;
  border-bottom: 1px solid #e5e7eb;
}

.data-table td {
  padding: 0.875rem 1rem;
  border-bottom: 1px solid #e5e7eb;
  font-size: 0.875rem;
}

.data-table tbody tr:hover {
  background: #f9fafb;
}

.loading-cell,
.empty-cell {
  text-align: center;
  padding: 3rem !important;
  color: #6b7280;
}

.spinner {
  width: 2rem;
  height: 2rem;
  border: 3px solid #e5e7eb;
  border-top-color: #1a365d;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status-rascunho {
  background: #f3f4f6;
  color: #374151;
}

.status-pendente_cliente {
  background: #fef3c7;
  color: #92400e;
}

.status-submetido {
  background: #dbeafe;
  color: #1e40af;
}

.status-em_analise {
  background: #cffafe;
  color: #155e75;
}

.status-aprovado {
  background: #d1fae5;
  color: #065f46;
}

.status-indeferido {
  background: #fee2e2;
  color: #991b1b;
}

.status-expira_brevemente {
  background: #ffedd5;
  color: #9a3412;
}

.status-expirado {
  background: #fee2e2;
  color: #991b1b;
}

.actions-cell {
  white-space: nowrap;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn-page {
  width: 2rem;
  height: 2rem;
  border: 1px solid #e5e7eb;
  background: white;
  border-radius: 0.375rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-page:hover:not(:disabled) {
  background: #f3f4f6;
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.875rem;
  color: #6b7280;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.custom-modal {
  background: white;
  border-radius: 0.75rem;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow: auto;
}

.custom-modal-large {
  max-width: 700px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #1a365d;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: #6b7280;
}

.modal-body {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
}

.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.625rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  font-size: 0.875rem;
}

.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #1a365d;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.25rem 1.5rem;
  border-top: 1px solid #e5e7eb;
}

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

.alert-warning-custom { border-radius: 12px; border: 1px solid #fde68a; background: #fffbeb; overflow: hidden; }
.alert-warning-header { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #fde68a; color: #92400e; font-weight: 600; font-size: 0.85rem; }
.alert-warning-header i { font-size: 1.1rem; }
.warning-list { padding: 8px; }
.warning-item { padding: 6px 10px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.warning-ref { font-size: 0.78rem; color: #92400e; font-weight: 600; background: #fde68a; padding: 2px 8px; border-radius: 20px; }
.warning-msg { font-size: 0.8rem; color: #a16207; }

.alert-updated { border-radius: 12px; border: 1px solid #bfdbfe; background: #eff6ff; overflow: hidden; }
.alert-updated-header { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #bfdbfe; color: #1e40af; font-weight: 600; font-size: 0.85rem; }
.alert-updated-header i { font-size: 1.1rem; }
.updated-list { padding: 8px; }
.updated-item { padding: 6px 10px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.updated-ref { font-size: 0.78rem; color: #1e40af; font-weight: 600; background: #bfdbfe; padding: 2px 8px; border-radius: 20px; }
.updated-msg { font-size: 0.8rem; color: #1d4ed8; }

.update-mode-toggle { display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
.update-mode-toggle input { display: none; }
.toggle-slider { width: 40px; height: 22px; background: #cbd5e1; border-radius: 11px; position: relative; transition: background 0.2s; flex-shrink: 0; }
.toggle-slider::after { content: ''; position: absolute; width: 18px; height: 18px; background: white; border-radius: 50%; top: 2px; left: 2px; transition: transform 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.update-mode-toggle input:checked + .toggle-slider { background: #2563eb; }
.update-mode-toggle input:checked + .toggle-slider::after { transform: translateX(18px); }
.toggle-label { font-size: 0.8rem; color: #475569; font-weight: 500; }

.upload-area {
  border: 2px dashed #e5e7eb;
  border-radius: 0.75rem;
  padding: 2rem;
  text-align: center;
  color: #6b7280;
}

.upload-area i {
  font-size: 2.5rem;
  margin-bottom: 1rem;
  color: #9ca3af;
}

.upload-area p {
  margin-bottom: 1rem;
}

.import-preview {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.import-preview h3 {
  font-size: 0.875rem;
  margin-bottom: 0.75rem;
  color: #374151;
}

.preview-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.75rem;
}

.preview-table th,
.preview-table td {
  padding: 0.5rem;
  border: 1px solid #e5e7eb;
  text-align: left;
}

.preview-table th {
  background: #f9fafb;
  font-weight: 600;
}

.more-records {
  margin-top: 0.75rem;
  font-size: 0.75rem;
  color: #6b7280;
  font-style: italic;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
}

.detail-item label {
  display: block;
  font-size: 0.75rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.detail-item span {
  font-weight: 500;
  color: #1a365d;
}

.detail-item.full-width {
  grid-column: 1 / -1;
  margin-top: 0.5rem;
}

.detail-item.full-width p {
  margin: 0.5rem 0 0;
  color: #374151;
  white-space: pre-wrap;
}

.spinner-small {
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  display: inline-block;
}

.toast-container {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 1100;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.toast {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.25rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  animation: slideIn 0.3s ease;
}

.toast-success {
  background: #d1fae5;
  color: #065f46;
}

.toast-error {
  background: #fee2e2;
  color: #991b1b;
}

.toast-warning {
  background: #ffedd5;
  color: #9a3412;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .admin-page {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
  }

  .header-actions .btn {
    flex: 1;
    justify-content: center;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }

  .filters {
    flex-direction: column;
  }

  .search-box {
    min-width: 100%;
  }

  .filters select {
    width: 100%;
  }

  .data-table {
    display: block;
    overflow-x: auto;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }

  .custom-modal {
    width: 95%;
    margin: 1rem;
  }
}
</style>
