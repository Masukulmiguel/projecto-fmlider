<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2>Gestão de Entregas</h2>
          <p class="text-muted mb-0">Gestão completa de entregas e contentores</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-success" @click="openImport">
            <i class="bi bi-file-earmark-excel me-1"></i>
            Importar Documento
          </button>
          <button class="btn btn-primary" @click="openCreate">
            <i class="bi bi-plus-lg me-1"></i>
            Nova Entrega
          </button>
        </div>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid mb-4">
      <div class="stat-card">
        <div class="stat-icon" style="background: #dbeafe; color: #1e40af;">
          <i class="bi bi-truck"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.total }}</span>
          <span class="stat-label">Total</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #f3f4f6; color: #4b5563;">
          <i class="bi bi-clock"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.pendente }}</span>
          <span class="stat-label">Pendentes</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #cffafe; color: #155e75;">
          <i class="bi bi-arrow-repeat"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.em_transporte }}</span>
          <span class="stat-label">Em Transporte</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #d1fae5; color: #065f46;">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.entregue }}</span>
          <span class="stat-label">Entregues</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #fee2e2; color: #991b1b;">
          <i class="bi bi-x-circle"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.cancelado }}</span>
          <span class="stat-label">Cancelados</span>
        </div>
      </div>
    </div>

    <!-- Filters & Table Card -->
    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" placeholder="Pesquisar por referência, cliente, origem ou destino..." @input="debounceSearch">
          </div>
          <select v-model="filters.estado" class="form-select" @change="fetchData">
            <option value="">Todos os estados</option>
            <option value="pendente">Pendente</option>
            <option value="em_preparacao">Em Preparação</option>
            <option value="saiu_da_base">Saiu da Base</option>
            <option value="em_transporte">Em Transporte</option>
            <option value="chegou_cliente">Chegou ao Cliente</option>
            <option value="entregue">Entregue</option>
            <option value="cancelado">Cancelado</option>
          </select>
          <select v-model="filters.motorista_id" class="form-select" @change="fetchData">
            <option value="">Todos os motoristas</option>
            <option v-for="m in motoristas" :key="m.id" :value="m.id">{{ m.nome_completo }}</option>
          </select>
          <input v-model="filters.destino" type="text" class="form-control filter-destino" placeholder="Destino..." @input="debounceSearch">
        </div>

        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
          Nenhuma entrega encontrada.
        </div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Ref FMLider</th>
                <th>Cliente</th>
                <th>Origem → Destino</th>
                <th>Motorista</th>
                <th>Camião</th>
                <th>Contentores</th>
                <th>Estado</th>
                <th>Data</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td><code class="tracking-code">{{ item.referencia_fmlider }}</code></td>
                <td>
                  <div class="fw-medium">{{ item.cliente_nome || '—' }}</div>
                  <small v-if="item.referencia_cliente" class="text-muted">Ref: {{ item.referencia_cliente }}</small>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-1">
                    <span>{{ item.origem || '—' }}</span>
                    <i class="bi bi-arrow-right text-muted"></i>
                    <span>{{ item.destino || '—' }}</span>
                  </div>
                </td>
                <td>{{ item.motorista_nome || '—' }}</td>
                <td>
                  <div v-if="item.camiao_matricula">{{ item.camiao_matricula }}</div>
                  <small v-if="item.camiao_codigo" class="text-muted">{{ item.camiao_codigo }}</small>
                  <span v-else>—</span>
                </td>
                <td>
                  <span class="contentor-badge">{{ (item.contentores || []).length }}</span>
                </td>
                <td><span class="status-badge" :class="'status-' + item.estado">{{ estadoLabel(item.estado) }}</span></td>
                <td><small class="text-muted">{{ formatDate(item.created_at) }}</small></td>
                <td>
                  <div class="action-buttons">
                    <button class="btn-icon btn-status" @click="openStatus(item)" title="Actualizar Estado">
                      <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <button class="btn-icon btn-edit" @click="openEdit(item)" title="Editar">
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn-icon btn-delete" @click="openDelete(item)" title="Eliminar">
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

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content modal-xl">
        <div class="modal-header">
          <h5>{{ editingItem ? 'Editar Entrega' : 'Nova Entrega' }}</h5>
          <button class="btn-close" @click="closeModal"></button>
        </div>
        <div class="modal-body">
          <!-- Dados Gerais -->
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-info-circle me-1"></i> Dados Gerais</h6>
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label">Referência FMLider</label>
                <input v-model="editForm.referencia_fmlider" type="text" class="form-control" disabled placeholder="Auto-gerado">
              </div>
              <div class="col-md-3">
                <label class="form-label">Referência Cliente</label>
                <input v-model="editForm.referencia_cliente" type="text" class="form-control" placeholder="Ref. do cliente">
              </div>
              <div class="col-md-3">
                <label class="form-label">Nº Processo</label>
                <input v-model="editForm.numero_processo" type="text" class="form-control" placeholder="Nº do processo">
              </div>
              <div class="col-md-3">
                <label class="form-label">Tipologia</label>
                <select v-model="editForm.tipologia" class="form-select">
                  <option value="">Selecione</option>
                  <option value="importacao">Importação</option>
                  <option value="exportacao">Exportação</option>
                  <option value="transito">Trânsito</option>
                  <option value="temporario">Temporário</option>
                  <option value="cabotagem">Cabotagem</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Origem</label>
                <input v-model="editForm.origem" type="text" class="form-control" placeholder="Cidade de origem">
              </div>
              <div class="col-md-4">
                <label class="form-label">Destino</label>
                <input v-model="editForm.destino" type="text" class="form-control" placeholder="Cidade de destino">
              </div>
              <div class="col-md-4">
                <label class="form-label">Cliente</label>
                <select v-model="editForm.cliente_id" class="form-select" @change="onClienteChange">
                  <option value="">Selecione o cliente</option>
                  <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Transporte -->
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-truck me-1"></i> Transporte</h6>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Motorista</label>
                <select v-model="editForm.motorista_id" class="form-select">
                  <option value="">Selecione o motorista</option>
                  <option v-for="m in motoristas" :key="m.id" :value="m.id">{{ m.nome_completo }}</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Camião</label>
                <select v-model="editForm.camiao_id" class="form-select" @change="onCamiaoChange">
                  <option value="">Selecione o camião</option>
                  <option v-for="c in camioes" :key="c.id" :value="c.id">{{ c.codigo_interno }} — {{ c.matricula }}</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Matrícula</label>
                <input v-model="editForm.matricula" type="text" class="form-control" placeholder="Auto-preenchido">
              </div>
            </div>
          </div>

          <!-- Contentores -->
          <div class="form-section">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="form-section-title mb-0"><i class="bi bi-box-seam me-1"></i> Contentores</h6>
              <button class="btn btn-sm btn-outline-primary" @click="addContentor">
                <i class="bi bi-plus-lg me-1"></i>Adicionar Contentor
              </button>
            </div>
            <div v-if="contentores.length === 0" class="text-muted text-center py-3">
              Nenhum contentor adicionado. Clique em "Adicionar Contentor".
            </div>
            <div v-for="(c, idx) in contentores" :key="idx" class="contentor-card">
              <div class="contentor-header">
                <span class="contentor-number">Contentor #{{ idx + 1 }}</span>
                <button class="btn-icon btn-delete btn-sm" @click="removeContentor(idx)" title="Remover contentor">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
              <div class="row g-2">
                <div class="col-md-3">
                  <label class="form-label">Nº Contentor</label>
                  <input v-model="c.numero" type="text" class="form-control form-control-sm" placeholder="Ex: MSKU1234567">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Tipo</label>
                  <select v-model="c.tipo" class="form-select form-select-sm">
                    <option value="">Selecione</option>
                    <option value="20gp">20' GP</option>
                    <option value="40gp">40' GP</option>
                    <option value="40hc">40' HC</option>
                    <option value="45hc">45' HC</option>
                    <option value="reefer">Reefer</option>
                    <option value="open_top">Open Top</option>
                    <option value="flat_rack">Flat Rack</option>
                    <option value="outro">Outro</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Estado</label>
                  <select v-model="c.estado" class="form-select form-select-sm">
                    <option value="">Selecione</option>
                    <option value="pendente">Pendente</option>
                    <option value="em_preparacao">Em Preparação</option>
                    <option value="carregado">Carregado</option>
                    <option value="em_transporte">Em Transporte</option>
                    <option value="entregue">Entregue</option>
                    <option value="devolvido">Devolvido</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-label">Data Entrega</label>
                  <input v-model="c.data_entrega" type="date" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                  <label class="form-label">Observações</label>
                  <input v-model="c.observacoes" type="text" class="form-control form-control-sm" placeholder="Notas...">
                </div>
              </div>
            </div>
          </div>

          <!-- Datas -->
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-calendar me-1"></i> Datas</h6>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Data Saída</label>
                <input v-model="editForm.data_saida" type="date" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">Data Prevista</label>
                <input v-model="editForm.data_prevista" type="date" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">Data Entrega</label>
                <input v-model="editForm.data_entrega" type="date" class="form-control">
              </div>
            </div>
          </div>

          <!-- Observações -->
          <div class="form-section">
            <h6 class="form-section-title"><i class="bi bi-chat-left-text me-1"></i> Observações</h6>
            <textarea v-model="editForm.observacoes" class="form-control" rows="3" placeholder="Notas internas sobre a entrega..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeModal">Cancelar</button>
          <button class="btn btn-primary" @click="save" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
            {{ editingItem ? 'Salvar' : 'Criar Entrega' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Status Update Modal -->
    <div v-if="showStatusModal" class="modal-overlay" @click.self="closeStatusModal">
      <div class="modal-content modal-sm">
        <div class="modal-header">
          <h5>Actualizar Estado</h5>
          <button class="btn-close" @click="closeStatusModal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">Entrega: <code class="tracking-code">{{ statusItem?.referencia_fmlider }}</code></p>
          <div class="mb-3">
            <label class="form-label">Novo Estado</label>
            <select v-model="statusForm.estado" class="form-select">
              <option value="pendente">Pendente</option>
              <option value="em_preparacao">Em Preparação</option>
              <option value="saiu_da_base">Saiu da Base</option>
              <option value="em_transporte">Em Transporte</option>
              <option value="chegou_cliente">Chegou ao Cliente</option>
              <option value="entregue">Entregue</option>
              <option value="cancelado">Cancelado</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Observações</label>
            <textarea v-model="statusForm.observacoes" class="form-control" rows="2" placeholder="Motivo da mudança..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeStatusModal">Cancelar</button>
          <button class="btn btn-primary" @click="updateStatus" :disabled="savingStatus">
            <span v-if="savingStatus" class="spinner-border spinner-border-sm me-1"></span>
            Actualizar Estado
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDelete">
      <div class="modal-content modal-sm">
        <div class="modal-header">
          <h5>Confirmar</h5>
          <button class="btn-close" @click="closeDelete"></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja eliminar a entrega <strong>{{ deleteItem?.referencia_fmlider }}</strong>?</p>
          <p class="text-muted small mb-0">Esta acção não pode ser desfeita. Todos os contentores associados serão removidos.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDelete">Cancelar</button>
          <button class="btn btn-danger" @click="deleteItemConfirm" :disabled="deleting">
            <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
            Eliminar
          </button>
        </div>
      </div>
    </div>

    <!-- Import Modal -->
    <div v-if="showImportModal" class="modal-overlay" @click.self="closeImport">
      <div class="modal-content modal-lg">
        <div class="modal-header">
          <h5>Importar Entregas</h5>
          <button class="btn-close" @click="closeImport"></button>
        </div>
        <div class="modal-body">
          <template v-if="!importPreview">
            <p class="text-muted mb-3">Selecione um ficheiro Excel (.xlsx, .xls) ou CSV para importar entregas em lote.</p>
            <ul class="text-muted small mb-3">
              <li>O ficheiro deve conter colunas como: <strong>origem, destino, cliente, tipologia</strong></li>
              <li>A referência FMLider será gerada automaticamente</li>
              <li>Os contentores podem ser importados com colunas: <strong>contentor_numero, contentor_tipo</strong></li>
            </ul>
            <input ref="importFileInput" type="file" accept=".xlsx,.xls,.csv,.json" class="d-none" @change="handleImportFile">
            <button class="btn btn-outline-primary" @click="$refs.importFileInput.click()">
              <i class="bi bi-upload me-1"></i>
              Selecionar ficheiro
            </button>
            <div v-if="importError" class="alert alert-danger mt-3">{{ importError }}</div>
          </template>
          <template v-else>
            <div v-if="!showValidation">
              <div class="alert alert-info mb-3">
                {{ importData.length }} registo(s) encontrado(s) no ficheiro.
              </div>
              <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th v-for="header in importHeaders" :key="header">{{ header }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, idx) in importData.slice(0, 20)" :key="idx">
                      <td v-for="header in importHeaders" :key="header">{{ row[header] || '' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p v-if="importData.length > 20" class="text-muted small">...e mais {{ importData.length - 20 }} registo(s)</p>
            </div>
            <div v-else>
              <div class="d-flex gap-3 mb-3 flex-wrap">
                <div class="validation-stat bg-success-subtle text-success">
                  <span class="stat-num">{{ successImportCount }}</span>
                  <span class="stat-txt">Sucesso</span>
                </div>
                <div class="validation-stat bg-danger-subtle text-danger">
                  <span class="stat-num">{{ failImportCount }}</span>
                  <span class="stat-txt">Erros</span>
                </div>
                <div class="validation-stat bg-warning-subtle text-warning">
                  <span class="stat-num">{{ warnImportCount }}</span>
                  <span class="stat-txt">Avisos</span>
                </div>
              </div>
              <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                <table class="table table-sm table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>Ref</th>
                      <th>Cliente</th>
                      <th>Estado</th>
                      <th>Detalhes</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(r, idx) in importValidationResults" :key="idx" :class="{'table-danger': !r.success, 'table-warning': r.warnings.length > 0 && r.success}">
                      <td>{{ idx + 1 }}</td>
                      <td><code>{{ r.ref || '—' }}</code></td>
                      <td>{{ r.cliente || '—' }}</td>
                      <td>
                        <span v-if="!r.success" class="badge bg-danger">Erro</span>
                        <span v-else-if="r.warnings.length > 0" class="badge bg-warning">Aviso</span>
                        <span v-else class="badge bg-success">OK</span>
                      </td>
                      <td>
                        <div v-if="r.errors.length" class="text-danger small">
                          <div v-for="e in r.errors" :key="e"><i class="bi bi-x-circle me-1"></i>{{ e }}</div>
                        </div>
                        <div v-if="r.warnings.length" class="text-warning small">
                          <div v-for="w in r.warnings" :key="w"><i class="bi bi-exclamation-triangle me-1"></i>{{ w }}</div>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </template>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeImport">Cancelar</button>
          <button v-if="importPreview" class="btn btn-outline-secondary me-auto" @click="importPreview = false; importData = []; importHeaders = []">
            <i class="bi bi-arrow-left me-1"></i>Outro ficheiro
          </button>
          <button v-if="importPreview" class="btn btn-success" @click="submitImport" :disabled="importing">
            <span v-if="importing" class="spinner-border spinner-border-sm me-1"></span>
            <i v-else class="bi bi-check-lg me-1"></i>
            Importar {{ importData.length }} registo(s)
          </button>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toast.show" class="toast-container" :class="'toast-' + toast.type">
      <i :class="toast.type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill'" class="me-2"></i>
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { supabase } from '@/lib/supabase'

const items = ref([])
const loading = ref(false)
const motoristas = ref([])
const camioes = ref([])
const clients = ref([])
const filters = reactive({ q: '', estado: '', motorista_id: '', destino: '' })
const currentPage = ref(1)
const pageSize = 20
let searchTimer = null

const stats = reactive({
  total: 0, pendente: 0, em_preparacao: 0, saiu_da_base: 0,
  em_transporte: 0, chegou_cliente: 0, entregue: 0, cancelado: 0
})

const fetchData = async () => {
  loading.value = true
  try {
    let query = supabase.from('entregas').select('*')
    if (filters.estado) query = query.eq('estado', filters.estado)
    if (filters.motorista_id) query = query.eq('motorista_id', filters.motorista_id)
    if (filters.destino) query = query.ilike('destino', `%${filters.destino}%`)
    if (filters.q) {
      query = query.or(`referencia_fmlider.ilike.%${filters.q}%,referencia_cliente.ilike.%${filters.q}%,numero_processo.ilike.%${filters.q}%,cliente_nome.ilike.%${filters.q}%,origem.ilike.%${filters.q}%,destino.ilike.%${filters.q}%,matricula.ilike.%${filters.q}%`)
    }
    query = query.order('created_at', { ascending: false })
    const { data, error } = await query
    if (error) throw error
    items.value = data || []

    const all = items.value
    stats.total = all.length
    stats.pendente = all.filter(i => i.estado === 'pendente').length
    stats.em_preparacao = all.filter(i => i.estado === 'em_preparacao').length
    stats.saiu_da_base = all.filter(i => i.estado === 'saiu_da_base').length
    stats.em_transporte = all.filter(i => i.estado === 'em_transporte').length
    stats.chegou_cliente = all.filter(i => i.estado === 'chegou_cliente').length
    stats.entregue = all.filter(i => i.estado === 'entregue').length
    stats.cancelado = all.filter(i => i.estado === 'cancelado').length

    for (const item of items.value) {
      const { data: cs } = await supabase.from('contentores').select('*').eq('entrega_id', item.id)
      item.contentores = cs || []
    }
  } catch (e) {
    showToast('error', 'Erro ao carregar entregas.')
  } finally { loading.value = false }
}

const fetchMotoristas = async () => {
  const { data } = await supabase.from('motoristas').select('*').eq('estado', 'ativo').order('nome_completo')
  motoristas.value = data || []
}

const fetchCamioes = async () => {
  const { data } = await supabase.from('camioes').select('*').order('matricula')
  camioes.value = data || []
}

const fetchClients = async () => {
  const { data } = await supabase.from('users').select('id, name, email').eq('role', 'cliente')
  clients.value = data || []
}

const debounceSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { currentPage.value = 1; fetchData() }, 300)
}

const estadoLabel = (estado) => ({
  pendente: 'Pendente', em_preparacao: 'Em Preparação', saiu_da_base: 'Saiu da Base',
  em_transporte: 'Em Transporte', chegou_cliente: 'Chegou ao Cliente',
  entregue: 'Entregue', cancelado: 'Cancelado'
}[estado] || estado)

const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : '—'

// ===== Create/Edit Modal =====
const showModal = ref(false)
const editingItem = ref(null)
const saving = ref(false)
const contentores = ref([])

const editForm = reactive({
  referencia_fmlider: '', referencia_cliente: '', numero_processo: '', tipologia: '',
  origem: '', destino: '', cliente_id: '', cliente_nome: '', motorista_id: '',
  camiao_id: '', matricula: '', estado: 'pendente', data_saida: '',
  data_prevista: '', data_entrega: '', observacoes: ''
})

const resetForm = () => {
  Object.assign(editForm, {
    referencia_fmlider: '', referencia_cliente: '', numero_processo: '', tipologia: '',
    origem: '', destino: '', cliente_id: '', cliente_nome: '', motorista_id: '',
    camiao_id: '', matricula: '', estado: 'pendente', data_saida: '',
    data_prevista: '', data_entrega: '', observacoes: ''
  })
  contentores.value = []
}

const openCreate = () => { editingItem.value = null; resetForm(); showModal.value = true }

const openEdit = (item) => {
  editingItem.value = item
  Object.assign(editForm, {
    referencia_fmlider: item.referencia_fmlider || '', referencia_cliente: item.referencia_cliente || '',
    numero_processo: item.numero_processo || '', tipologia: item.tipologia || '',
    origem: item.origem || '', destino: item.destino || '', cliente_id: item.cliente_id || '',
    cliente_nome: item.cliente_nome || '', motorista_id: item.motorista_id || '',
    camiao_id: item.camiao_id || '', matricula: item.matricula || '',
    estado: item.estado || 'pendente', data_saida: item.data_saida || '',
    data_prevista: item.data_prevista || '', data_entrega: item.data_entrega || '',
    observacoes: item.observacoes || ''
  })
  contentores.value = (item.contentores || []).map(c => ({
    numero: c.numero || '', tipo: c.tipo || '', estado: c.estado || '',
    data_entrega: c.data_entrega || '', observacoes: c.observacoes || ''
  }))
  if (contentores.value.length === 0) addContentor()
  showModal.value = true
}

const closeModal = () => { showModal.value = false; editingItem.value = null }

const onClienteChange = () => {
  const client = clients.value.find(c => c.id === editForm.cliente_id)
  editForm.cliente_nome = client ? client.name : ''
}

const onCamiaoChange = () => {
  const camiao = camioes.value.find(c => c.id == editForm.camiao_id)
  editForm.matricula = camiao ? (camiao.matricula || '') : ''
}

const addContentor = () => { contentores.value.push({ numero: '', tipo: '', estado: '', data_entrega: '', observacoes: '' }) }
const removeContentor = (idx) => { contentores.value.splice(idx, 1) }

const generateRef = () => {
  const now = new Date()
  const d = now.toISOString().slice(0, 10).replace(/-/g, '')
  const r = Math.random().toString(36).slice(2, 6).toUpperCase()
  return `ENT-${d}-${r}`
}

const save = async () => {
  saving.value = true
  try {
    const payload = {
      referencia_fmlider: editForm.referencia_fmlider || generateRef(),
      referencia_cliente: editForm.referencia_cliente || null,
      numero_processo: editForm.numero_processo || null,
      tipologia: editForm.tipologia || null,
      origem: editForm.origem,
      destino: editForm.destino,
      cliente_id: editForm.cliente_id || null,
      cliente_nome: editForm.cliente_nome || null,
      motorista_id: editForm.motorista_id ? Number(editForm.motorista_id) : null,
      camiao_id: editForm.camiao_id ? Number(editForm.camiao_id) : null,
      matricula: editForm.matricula || null,
      estado: editForm.estado || 'pendente',
      data_saida: editForm.data_saida || null,
      data_prevista: editForm.data_prevista || null,
      data_entrega: editForm.data_entrega || null,
      observacoes: editForm.observacoes || null
    }

    let entregaId
    if (editingItem.value) {
      const { error } = await supabase.from('entregas').update(payload).eq('id', editingItem.value.id)
      if (error) throw error
      entregaId = editingItem.value.id
      await supabase.from('contentores').delete().eq('entrega_id', entregaId)
    } else {
      const { data, error } = await supabase.from('entregas').insert(payload).select().single()
      if (error) throw error
      entregaId = data.id
    }

    const validContentores = contentores.value.filter(c => c.numero || c.tipo)
    if (validContentores.length > 0) {
      const cs = validContentores.map(c => ({
        entrega_id: entregaId, numero: c.numero || null, tipo: c.tipo || null,
        estado: c.estado || null, data_entrega: c.data_entrega || null, observacoes: c.observacoes || null
      }))
      await supabase.from('contentores').insert(cs)
    }

    await supabase.from('historico_entregas').insert({
      entrega_id: entregaId, estado_anterior: editingItem.value?.estado || null,
      estado_novo: payload.estado, utilizador_nome: 'Admin', observacoes: 'Entrega criada/atualizada'
    })

    showToast('success', editingItem.value ? 'Entrega actualizada!' : 'Entrega criada!')
    closeModal()
    fetchData()
  } catch (e) {
    showToast('error', e.message || 'Erro ao salvar entrega.')
  } finally { saving.value = false }
}

// ===== Delete =====
const showDeleteModal = ref(false)
const deleteItem = ref(null)
const deleting = ref(false)

const openDelete = (item) => { deleteItem.value = item; showDeleteModal.value = true }
const closeDelete = () => { showDeleteModal.value = false; deleteItem.value = null }

const deleteItemConfirm = async () => {
  deleting.value = true
  try {
    await supabase.from('contentores').delete().eq('entrega_id', deleteItem.value.id)
    await supabase.from('entregas').delete().eq('id', deleteItem.value.id)
    showToast('success', 'Entrega eliminada!')
    closeDelete()
    fetchData()
  } catch (e) {
    showToast('error', 'Erro ao eliminar entrega.')
  } finally { deleting.value = false }
}

// ===== Status Update =====
const showStatusModal = ref(false)
const statusItem = ref(null)
const savingStatus = ref(false)
const statusForm = reactive({ estado: '', observacoes: '' })

const openStatus = (item) => {
  statusItem.value = item
  statusForm.estado = item.estado || 'pendente'
  statusForm.observacoes = ''
  showStatusModal.value = true
}

const closeStatusModal = () => { showStatusModal.value = false; statusItem.value = null }

const updateStatus = async () => {
  savingStatus.value = true
  try {
    const { error } = await supabase.from('entregas')
      .update({ estado: statusForm.estado, data_entrega: statusForm.estado === 'entregue' ? new Date().toISOString() : null })
      .eq('id', statusItem.value.id)
    if (error) throw error

    await supabase.from('historico_entregas').insert({
      entrega_id: statusItem.value.id, estado_anterior: statusItem.value.estado,
      estado_novo: statusForm.estado, utilizador_nome: 'Admin',
      observacoes: statusForm.observacoes || null
    })

    showToast('success', 'Estado actualizado!')
    closeStatusModal()
    fetchData()
  } catch (e) {
    showToast('error', 'Erro ao actualizar estado.')
  } finally { savingStatus.value = false }
}

// ===== Import =====
const showImportModal = ref(false)
const importPreview = ref(false)
const importData = ref([])
const importHeaders = ref([])
const importError = ref('')
const importing = ref(false)
const showValidation = ref(false)
const importValidationResults = ref([])

const successImportCount = computed(() => importValidationResults.value.filter(r => r.success && r.warnings.length === 0).length)
const failImportCount = computed(() => importValidationResults.value.filter(r => !r.success).length)
const warnImportCount = computed(() => importValidationResults.value.filter(r => r.warnings.length > 0).length)

const openImport = () => { showImportModal.value = true }
const closeImport = () => { showImportModal.value = false; importPreview.value = false; importData.value = []; importHeaders.value = []; importError.value = ''; showValidation.value = false; importValidationResults.value = [] }

const handleImportFile = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  importError.value = ''
  const ext = file.name.split('.').pop().toLowerCase()

  if (ext === 'json') {
    try {
      const text = await file.text()
      const json = JSON.parse(text)
      const arr = Array.isArray(json) ? json : (json.entregas || json.data || [json])
      if (arr.length === 0) { importError.value = 'Nenhum registo encontrado.'; return }
      importHeaders.value = [...new Set(arr.flatMap(r => Object.keys(r)))]
      importData.value = arr
      importPreview.value = true
    } catch { importError.value = 'Erro ao processar JSON.' }
    return
  }

  try {
    const XLSX = await import('xlsx')
    const reader = new FileReader()
    reader.onload = (e) => {
      try {
        const wb = XLSX.read(e.target.result, { type: 'array' })
        const ws = wb.Sheets[wb.SheetNames[0]]
        const raw = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '' })
        if (raw.length < 2) { importError.value = 'Sem dados.'; return }
        let hdrs = raw[0], start = 1
        if (raw[0].filter(v => v).length < raw[1].filter(v => v).length && raw[1].filter(v => v).length > 3) { hdrs = raw[1]; start = 2 }
        const jsonData = []
        for (let i = start; i < raw.length; i++) {
          const row = raw[i]; if (!row || row.every(v => !v)) continue
          const obj = {}; hdrs.forEach((h, idx) => { if (h && row[idx] !== undefined) obj[h] = row[idx] })
          if (Object.keys(obj).length > 0) jsonData.push(obj)
        }
        if (jsonData.length === 0) { importError.value = 'Nenhum registo válido.'; return }
        importHeaders.value = [...new Set(jsonData.flatMap(r => Object.keys(r)))]
        importData.value = jsonData
        importPreview.value = true
      } catch { importError.value = 'Erro ao processar Excel.' }
    }
    reader.readAsArrayBuffer(file)
  } catch { importError.value = 'Use ficheiros JSON ou Excel.' }
}

const findClient = (nome, cliMap) => {
  if (!nome) return { entry: null, matchType: null }
  const key = nome.toLowerCase().trim()
  if (cliMap[key]) return { entry: cliMap[key], matchType: 'exact' }
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

const findMotorista = (nome, motoristaMap) => {
  if (!nome) return { entry: null, matchType: null }
  const key = nome.toLowerCase().trim()
  if (motoristaMap[key]) return { entry: motoristaMap[key], matchType: 'exact' }
  const words = key.split(/\s+/).filter(w => w.length > 2)
  let candidates = Object.keys(motoristaMap).filter(k =>
    words.some(w => k.includes(w)) || k.split(/\s+/).some(w => key.includes(w))
  )
  if (candidates.length === 1) return { entry: motoristaMap[candidates[0]], matchType: 'partial' }
  return { entry: null, matchType: null }
}

const submitImport = async () => {
  importing.value = true
  importValidationResults.value = []
  showValidation.value = false

  try {
    const [{ data: users }, { data: motoristasData }, { data: camioesData }] = await Promise.all([
      supabase.from('users').select('id, name').eq('role', 'cliente'),
      supabase.from('motoristas').select('id, nome_completo'),
      supabase.from('camioes').select('id, matricula, codigo_interno')
    ])

    const cliMap = {}
    ;(users || []).forEach(u => {
      const name = (u.name || '').toLowerCase().trim()
      if (name) cliMap[name] = u
    })

    const motoristaMap = {}
    ;(motoristasData || []).forEach(m => {
      const name = (m.nome_completo || '').toLowerCase().trim()
      if (name) motoristaMap[name] = m
    })

    const camiaoMap = {}
    ;(camioesData || []).forEach(c => {
      const mat = (c.matricula || '').toLowerCase().trim()
      const cod = (c.codigo_interno || '').toLowerCase().trim()
      if (mat) camiaoMap[mat] = c
      if (cod) camiaoMap[cod] = c
    })

    const results = []
    let successCount = 0
    let failCount = 0

    for (let idx = 0; idx < importData.value.length; idx++) {
      const row = importData.value[idx]
      const errors = []
      const warnings = []

      const clienteNome = (row.cliente || row.Cliente || row.cliente_nome || '').trim()
      const motoristaNome = (row.motorista || row.Motorista || '').trim()
      const camiaoRef = (row.camiao || row.Camião || row.matricula || '').trim()

      let clienteEntry = null
      if (clienteNome) {
        const { entry, matchType } = findClient(clienteNome, cliMap)
        if (!entry) {
          warnings.push(`Cliente "${clienteNome}" não encontrado no sistema`)
        } else {
          clienteEntry = entry
          if (matchType !== 'exact') warnings.push(`Cliente "${clienteNome}" identificado como "${entry.name}" (${matchType})`)
        }
      } else {
        errors.push('Cliente em branco — obrigatório')
      }

      let motoristaEntry = null
      if (motoristaNome) {
        const { entry, matchType } = findMotorista(motoristaNome, motoristaMap)
        if (!entry) {
          warnings.push(`Motorista "${motoristaNome}" não encontrado no sistema`)
        } else {
          motoristaEntry = entry
          if (matchType !== 'exact') warnings.push(`Motorista "${motoristaNome}" identificado como "${entry.nome_completo}" (${matchType})`)
        }
      }

      let camiaoEntry = null
      if (camiaoRef) {
        const camKey = camiaoRef.toLowerCase().trim()
        camiaoEntry = camiaoMap[camKey] || null
        if (!camiaoEntry) warnings.push(`Camião "${camiaoRef}" não encontrado no sistema`)
      }

      const ref = `ENT-${new Date().toISOString().slice(0, 10).replace(/-/g, '')}-${Math.random().toString(36).slice(2, 6).toUpperCase()}`

      const success = errors.length === 0
      if (success) successCount++
      else failCount++

      results.push({
        ref, cliente: clienteNome, success, errors, warnings,
        payload: success ? {
          referencia_fmlider: ref,
          origem: row.origem || row.Origem || null,
          destino: row.destino || row.Destino || null,
          cliente_id: clienteEntry?.id || null,
          cliente_nome: clienteEntry?.name || clienteNome,
          motorista_id: motoristaEntry?.id || null,
          camiao_id: camiaoEntry?.id || null,
          matricula: camiaoEntry?.matricula || row.matricula || null,
          numero_processo: row.numero_processo || row.processo || row['Nº Processo'] || null,
          referencia_cliente: row.referencia_cliente || row['Ref Cliente'] || null,
          tipologia: row.tipologia || row.tipo || row.Tipologia || null,
          estado: 'pendente',
          observacoes: row.observacoes || row.Observações || null
        } : null
      })
    }

    importValidationResults.value = results
    showValidation.value = true

    if (failCount === 0) {
      for (const r of results) {
        if (!r.payload) continue
        const { data: newEntrega, error } = await supabase.from('entregas').insert(r.payload).select().single()
        if (!error && newEntrega) {
          await supabase.from('historico_entregas').insert({
            entrega_id: newEntrega.id, estado_novo: 'pendente', utilizador_nome: 'Admin', observacoes: 'Importação em massa'
          })
        }
      }
      showToast('success', `${successCount} entrega(s) importada(s) com sucesso!`)
    } else {
      showToast('warning', `${successCount} sucesso, ${failCount} erros, ${results.filter(r => r.warnings.length > 0).length} avisos`)
    }
  } catch (e) {
    showToast('error', 'Erro ao importar.')
  } finally { importing.value = false }
}

// ===== Toast =====
const toast = reactive({ show: false, type: 'success', message: '' })
let toastTimer = null
const showToast = (type, message) => {
  toast.type = type; toast.message = message; toast.show = true
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.show = false }, 3000)
}

onMounted(() => { fetchData(); fetchMotoristas(); fetchCamioes(); fetchClients() })
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
.filter-destino { max-width: 180px; border: 2px solid #e2e8f0; border-radius: 8px; padding: 0.55rem 0.75rem; }
.tracking-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1.5rem;
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

.contentor-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 28px;
  border-radius: 14px;
  background: #dbeafe;
  color: #1e40af;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0 0.5rem;
}

.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.status-pendente { background: #f3f4f6; color: #4b5563; }
.status-em_preparacao { background: #fef9c3; color: #854d0e; }
.status-saiu_da_base { background: #dbeafe; color: #1e40af; }
.status-em_transporte { background: #cffafe; color: #155e75; }
.status-chegou_cliente { background: #ede9fe; color: #6d28d9; }
.status-entregue { background: #d1fae5; color: #065f46; }
.status-cancelado { background: #fee2e2; color: #991b1b; }

.form-section {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 1.25rem;
  margin-bottom: 1rem;
}

.form-section-title {
  font-weight: 600;
  color: #1a365d;
  margin-bottom: 0.75rem;
  font-size: 0.9rem;
}

.form-section .form-label {
  font-size: 0.8rem;
  font-weight: 500;
  color: #475569;
  margin-bottom: 0.25rem;
}

.contentor-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 0.75rem;
}

.contentor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.contentor-number {
  font-weight: 600;
  font-size: 0.85rem;
  color: #1e40af;
}

.btn-status {
  color: #0e7490;
  border: none;
}
.btn-status:hover {
  color: #0891b2;
  background: #cffafe;
}

.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1050; }
.modal-content { background: white; border-radius: 12px; width: 100%; max-width: 520px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
.modal-content.modal-sm { max-width: 400px; }
.modal-content.modal-lg { max-width: 800px; }
.modal-content.modal-xl { max-width: 960px; }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
.modal-header h5 { margin: 0; font-weight: 600; }
.modal-body { padding: 1.5rem; overflow-y: auto; flex: 1; min-height: 0; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem; flex-shrink: 0; }

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

@media (max-width: 768px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
}
@media (max-width: 480px) {
  .stats-grid { grid-template-columns: 1fr; }
}

.validation-stat { border-radius: 10px; padding: 0.75rem 1.25rem; text-align: center; min-width: 90px; }
.validation-stat .stat-num { display: block; font-size: 1.5rem; font-weight: 700; }
.validation-stat .stat-txt { display: block; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.03em; }
</style>
