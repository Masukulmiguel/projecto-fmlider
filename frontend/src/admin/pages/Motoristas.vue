<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <h2>Gestão de Motoristas</h2>
      <p class="text-muted mb-0">Gestão de motoristas e equipas de transporte</p>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid mb-4">
      <div class="stat-card">
        <div class="stat-icon" style="background: #dbeafe; color: #1e40af;">
          <i class="bi bi-people"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.total }}</span>
          <span class="stat-label">Total</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #d1fae5; color: #065f46;">
          <i class="bi bi-person-check"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.ativos }}</span>
          <span class="stat-label">Ativos</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #fee2e2; color: #991b1b;">
          <i class="bi bi-person-x"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.inativos }}</span>
          <span class="stat-label">Inativos</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #92400e;">
          <i class="bi bi-clock-history"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.expirados }}</span>
          <span class="stat-label">Docs. Expirados</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" placeholder="Pesquisar por nome, BI ou telefone..." @input="debounceSearch">
          </div>
          <select v-model="filters.estado" class="form-select" @change="fetchData">
            <option value="">Todos os estados</option>
            <option value="ativo">Ativo</option>
            <option value="inativo">Inativo</option>
          </select>
          <button class="btn btn-primary" @click="openCreate">
            <i class="bi bi-plus-lg me-1"></i>
            Novo Motorista
          </button>
        </div>

        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
          Nenhum motorista encontrado.
        </div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Nome</th>
                <th>BI</th>
                <th>Telefone</th>
                <th>Carta Condução</th>
                <th>Validade Carta</th>
                <th>Validade BI</th>
                <th>Estado</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>
                  <div class="fw-medium">{{ item.nome_completo }}</div>
                </td>
                <td><code class="tracking-code">{{ item.bilhete_identidade || '' }}</code></td>
                <td>{{ item.telefone || '' }}</td>
                <td>{{ item.carta_conducao || '' }}</td>
                <td><small class="text-muted">{{ formatDate(item.validade_carta) }}</small>
                  <span v-if="isExpired(item.validade_carta)" class="badge bg-danger ms-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Expirado</span>
                  <span v-else-if="isExpiringSoon(item.validade_carta)" class="badge bg-warning text-dark ms-1"><i class="bi bi-clock-fill me-1"></i>A expirar</span>
                </td>
                <td><small class="text-muted">{{ formatDate(item.validade_bi) }}</small>
                  <span v-if="isExpired(item.validade_bi)" class="badge bg-danger ms-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Expirado</span>
                  <span v-else-if="isExpiringSoon(item.validade_bi)" class="badge bg-warning text-dark ms-1"><i class="bi bi-clock-fill me-1"></i>A expirar</span>
                </td>
                <td><span class="status-badge" :class="'status-' + item.estado">{{ item.estado === 'ativo' ? 'Ativo' : 'Inativo' }}</span></td>
                <td>
                  <div class="action-buttons">
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
      <div class="modal-content">
        <div class="modal-header">
          <h5>{{ editingItem ? 'Editar Motorista' : 'Novo Motorista' }}</h5>
          <button class="btn-close" @click="closeModal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nome Completo <span class="text-danger">*</span></label>
            <input v-model="form.nome_completo" type="text" class="form-control" placeholder="Nome completo do motorista">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Bilhete de Identidade <span class="text-danger">*</span></label>
              <div class="input-group">
                <input v-model="form.bilhete_identidade" type="text" class="form-control" placeholder="006151112LA041" maxlength="14" :class="{'is-invalid': form.bilhete_identidade && !isValidBiFormat(form.bilhete_identidade)}" @blur="onBiBlur">
                <button class="btn btn-outline-primary" type="button" @click="consultarBI" :disabled="consultingBi || !form.bilhete_identidade">
                  <span v-if="consultingBi" class="spinner-border spinner-border-sm"></span>
                  <i v-else class="bi bi-search"></i>
                </button>
              </div>
              <div v-if="form.bilhete_identidade && !isValidBiFormat(form.bilhete_identidade)" class="invalid-feedback d-block">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>BI deve ter 14 caracteres (ex: 006151112LA041)
              </div>
              <div v-if="biLookupStatus" class="small mt-1" :class="biLookupStatus === 'error' ? 'text-danger' : 'text-success'">
                <i :class="biLookupStatus === 'error' ? 'bi bi-x-circle-fill' : 'bi bi-check-circle-fill'" class="me-1"></i>
                {{ biLookupMessage }}
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Validade BI</label>
              <input v-model="form.validade_bi" type="date" class="form-control" :class="{'is-invalid': form.validade_bi && isExpired(form.validade_bi)}">
              <div v-if="form.validade_bi && isExpired(form.validade_bi)" class="invalid-feedback">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>BI Expirado, Documento vencido!
              </div>
              <div v-else-if="form.validade_bi && isExpiringSoon(form.validade_bi)" class="text-warning small mt-1">
                <i class="bi bi-clock-fill me-1"></i>BI a expirar em breve
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Carta de Condução</label>
              <input v-model="form.carta_conducao" type="text" class="form-control" placeholder="Nº carta de condução">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Validade Carta</label>
              <input v-model="form.validade_carta" type="date" class="form-control" :class="{'is-invalid': form.validade_carta && isExpired(form.validade_carta)}">
              <div v-if="form.validade_carta && isExpired(form.validade_carta)" class="invalid-feedback">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>Carta Expirada, Documento vencido!
              </div>
              <div v-else-if="form.validade_carta && isExpiringSoon(form.validade_carta)" class="text-warning small mt-1">
                <i class="bi bi-clock-fill me-1"></i>Carta a expirar em breve
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Telefone</label>
            <input v-model="form.telefone" type="text" class="form-control" placeholder="Nº telefone">
          </div>
          <div class="mb-3">
            <label class="form-label">Estado</label>
            <select v-model="form.estado" class="form-select">
              <option value="ativo">Ativo</option>
              <option value="inativo">Inativo</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Observações</label>
            <textarea v-model="form.observacoes" class="form-control" rows="3" placeholder="Notas internas..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <div v-if="(form.validade_carta && isExpired(form.validade_carta)) || (form.validade_bi && isExpired(form.validade_bi))" class="alert-expired w-100">
            <i class="bi bi-exclamation-diamond-fill me-2"></i>
            <strong>Atenção:</strong> Este motorista possui documentos expirados. O sistema não permitirá o registo enquanto os documentos não estiverem válidos.
          </div>
          <button class="btn btn-secondary" @click="closeModal">Cancelar</button>
          <button class="btn btn-primary" @click="save" :disabled="saving || (form.validade_carta && isExpired(form.validade_carta)) || (form.validade_bi && isExpired(form.validade_bi))">
            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
            {{ editingItem ? 'Guardar' : 'Criar' }}
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
          <p>Tem certeza que deseja eliminar o motorista <strong>{{ deleteItem?.nome_completo }}</strong>?</p>
          <p class="text-muted small mb-0">Esta acção não pode ser desfeita.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDelete">Cancelar</button>
          <button class="btn btn-danger" @click="deleteMotorista" :disabled="deleting">
            <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
            Eliminar
          </button>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toast.show" class="toast-container" :class="'toast-' + toast.type">
      <i :class="toast.type === 'success' ? 'bi bi-check-circle-fill' : toast.type === 'warning' ? 'bi bi-exclamation-triangle-fill' : 'bi bi-exclamation-circle-fill'" class="me-2"></i>
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'

const items = ref([])
const loading = ref(false)
const filters = reactive({ q: '', estado: '' })
const currentPage = ref(1)
const pageSize = 10
const totalItems = ref(0)
let searchTimer = null

const fetchData = async () => {
  loading.value = true
  try {
    const q = (filters.q || '').trim()
    let query = supabase.from('motoristas').select('*', { count: 'exact' })
    if (filters.estado) query = query.eq('estado', filters.estado)
    if (q) {
      query = query.ilike('nome_completo', `%${q}%`)
    }
    const from = (currentPage.value - 1) * pageSize
    const to = from + pageSize - 1
    const { data, error, count } = await query.order('nome_completo', { ascending: true }).range(from, to)
    if (error) throw error
    items.value = data || []
    totalItems.value = count || 0
  } catch (e) {
    console.error('Erro ao buscar motoristas:', e)
  } finally {
    loading.value = false
  }
}

const totalPages = computed(() => Math.ceil(totalItems.value / pageSize))

const changePage = (page) => {
  currentPage.value = page
  fetchData()
}

const debounceSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    currentPage.value = 1
    fetchData()
  }, 300)
}

const stats = reactive({ total: 0, ativos: 0, inativos: 0, expirados: 0 })

const fetchStats = async () => {
  try {
    const [total, ativos, inativos] = await Promise.all([
      supabase.from('motoristas').select('id', { count: 'exact', head: true }),
      supabase.from('motoristas').select('id', { count: 'exact', head: true }).eq('estado', 'ativo'),
      supabase.from('motoristas').select('id', { count: 'exact', head: true }).eq('estado', 'inativo')
    ])
    const today = new Date().toISOString().split('T')[0]
    const { count: expirados } = await supabase.from('motoristas').select('id', { count: 'exact', head: true })
      .or(`validade_carta.lt.${today},validade_bi.lt.${today}`)

    stats.total = total.count || 0
    stats.ativos = ativos.count || 0
    stats.inativos = inativos.count || 0
    stats.expirados = expirados || 0
  } catch (e) {}
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : ''

const isExpired = (dateStr) => {
  if (!dateStr) return false
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return new Date(dateStr) < today
}

const isExpiringSoon = (dateStr) => {
  if (!dateStr) return false
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const date = new Date(dateStr)
  const diffDays = Math.ceil((date - today) / (1000 * 60 * 60 * 24))
  return diffDays > 0 && diffDays <= 30
}

// Create/Edit modal
const showModal = ref(false)
const editingItem = ref(null)
const saving = ref(false)
const form = reactive({
  nome_completo: '',
  bilhete_identidade: '',
  telefone: '',
  carta_conducao: '',
  validade_carta: '',
  validade_bi: '',
  estado: 'ativo',
  observacoes: ''
})

const resetForm = () => {
  form.nome_completo = ''
  form.bilhete_identidade = ''
  form.telefone = ''
  form.carta_conducao = ''
  form.validade_carta = ''
  form.validade_bi = ''
  form.estado = 'ativo'
  form.observacoes = ''
  biLookupStatus.value = ''
  biLookupMessage.value = ''
}

const openCreate = () => {
  editingItem.value = null
  resetForm()
  showModal.value = true
}

const openEdit = (item) => {
  editingItem.value = item
  form.nome_completo = item.nome_completo || ''
  form.bilhete_identidade = item.bilhete_identidade || ''
  form.telefone = item.telefone || ''
  form.carta_conducao = item.carta_conducao || ''
  form.validade_carta = item.validade_carta || ''
  form.validade_bi = item.validade_bi || ''
  form.estado = item.estado || 'ativo'
  form.observacoes = item.observacoes || ''
  biLookupStatus.value = ''
  biLookupMessage.value = ''
  showModal.value = true
}

const isValidBiFormat = (bi) => /^\d{9}[A-Z]{2}\d{3}$/.test(bi.toUpperCase())

const consultingBi = ref(false)
const biLookupStatus = ref('')
const biLookupMessage = ref('')

const onBiBlur = () => {
  if (form.bilhete_identidade && isValidBiFormat(form.bilhete_identidade)) {
    consultarBI()
  }
}

const consultarBI = async () => {
  if (!form.bilhete_identidade || !isValidBiFormat(form.bilhete_identidade)) {
    biLookupStatus.value = 'error'
    biLookupMessage.value = 'Formato de BI inválido'
    return
  }
  consultingBi.value = true
  biLookupStatus.value = ''
  biLookupMessage.value = ''
  const bi = form.bilhete_identidade.toUpperCase()

  try {
    const res = await fetch(`/api/bi-lookup/${bi}`, { signal: AbortSignal.timeout(15000) })
    const data = await res.json()
    if (data.success && data.data && data.data.nome) {
      biLookupStatus.value = 'success'
      biLookupMessage.value = `Titular: ${data.data.nome} (${data.data.fonte})`
      if (!form.nome_completo || !editingItem.value) {
        form.nome_completo = data.data.nome
      }
    } else if (data.success && data.data && data.data.validFormat) {
      biLookupStatus.value = 'success'
      biLookupMessage.value = `BI com formato válido (fonte: ${data.data.fonte})`
    } else {
      biLookupStatus.value = 'error'
      biLookupMessage.value = data.message || 'BI não encontrado'
    }
  } catch (e) {
    biLookupStatus.value = 'error'
    biLookupMessage.value = 'Erro ao consultar BI. Tente novamente.'
    console.error(e)
  } finally {
    consultingBi.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  editingItem.value = null
}

const save = async () => {
  if (!form.nome_completo.trim()) {
    showToast('error', 'O nome completo é obrigatório.')
    return
  }
  if (!form.bilhete_identidade.trim()) {
    showToast('error', 'O Bilhete de Identidade é obrigatório.')
    return
  }
  if (!isValidBiFormat(form.bilhete_identidade)) {
    showToast('error', 'Formato de BI inválido. Use 14 caracteres (ex: 006151112LA041).')
    return
  }
  saving.value = true
  try {
    const biUpper = form.bilhete_identidade.toUpperCase()
    const { data: existingBi } = await supabase.from('motoristas').select('id, nome_completo').eq('bilhete_identidade', biUpper).maybeSingle()
    if (existingBi && (!editingItem.value || existingBi.id !== editingItem.value.id)) {
      showToast('error', `Este BI já está registado para o motorista "${existingBi.nome_completo}".`)
      saving.value = false
      return
    }
    const payload = {
      nome_completo: form.nome_completo,
      bilhete_identidade: biUpper,
      telefone: form.telefone,
      carta_conducao: form.carta_conducao,
      validade_carta: form.validade_carta || null,
      validade_bi: form.validade_bi || null,
      estado: form.estado,
      observacoes: form.observacoes
    }
    if (editingItem.value) {
      const { error } = await supabase.from('motoristas').update(payload).eq('id', editingItem.value.id)
      if (error) throw error
      showToast('success', 'Motorista atualizado com sucesso!')
    } else {
      const { error } = await supabase.from('motoristas').insert(payload)
      if (error) throw error
      showToast('success', 'Motorista criado com sucesso!')
    }
    closeModal()
    fetchData()
    fetchStats()
    if (isExpiringSoon(form.validade_carta) || isExpiringSoon(form.validade_bi)) {
      showToast('warning', 'Atenção: O motorista possui documentos que irão expirar em breve.')
    }
  } catch (e) {
    console.error('Erro ao guardar motorista:', e)
    const msg = e?.message || e?.error?.message || JSON.stringify(e)
    showToast('error', 'Erro ao guardar motorista: ' + msg)
  } finally {
    saving.value = false
  }
}

// Delete modal
const showDeleteModal = ref(false)
const deleteItem = ref(null)
const deleting = ref(false)

const openDelete = (item) => {
  deleteItem.value = item
  showDeleteModal.value = true
}

const closeDelete = () => {
  showDeleteModal.value = false
  deleteItem.value = null
}

const deleteMotorista = async () => {
  deleting.value = true
  try {
    const { error } = await supabase.from('motoristas').delete().eq('id', deleteItem.value.id)
    if (error) throw error
    showToast('success', 'Motorista eliminado com sucesso!')
    closeDelete()
    fetchData()
    fetchStats()
  } catch (e) {
    showToast('error', 'Erro ao eliminar motorista.')
  } finally {
    deleting.value = false
  }
}

// Toast
const toast = reactive({ show: false, type: 'success', message: '' })
let toastTimer = null

const showToast = (type, message) => {
  toast.type = type
  toast.message = message
  toast.show = true
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.show = false }, 4000)
}

onMounted(() => {
  fetchData()
  fetchStats()
})
</script>

<style scoped>
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
.input-group .btn { z-index: 0; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

.status-badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 12px;
  font-size: 0.72rem;
  font-weight: 600;
}
.status-ativo { background: #d1fae5; color: #065f46; }
.status-inativo { background: #fee2e2; color: #991b1b; }

.action-buttons {
  display: flex;
  gap: 0.35rem;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-edit { background: #eff6ff; color: #2563eb; }
.btn-edit:hover { background: #dbeafe; color: #1d4ed8; }
.btn-delete { background: #fef2f2; color: #dc2626; }
.btn-delete:hover { background: #fee2e2; color: #b91c1c; }

.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1050; padding: 1rem; overflow-y: auto; }
.modal-content { background: white; border-radius: 12px; width: 100%; max-width: 520px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
.modal-content.modal-sm { max-width: 400px; }
.modal-content form { display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; }
.modal-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; gap: 0.75rem; }
.modal-header h5 { margin: 0; font-weight: 600; font-size: 1.05rem; min-width: 0; word-break: break-word; flex: 1; }
.modal-header .btn-close-modal { width: 32px; height: 32px; min-width: 32px; border: none; border-radius: 8px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; }
.modal-body { padding: 1.25rem; overflow-y: auto; flex: 1; min-height: 0; }
.modal-footer { padding: 0.75rem 1.25rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem; flex-shrink: 0; flex-wrap: wrap; }

.toast-container { position: fixed; top: 20px; right: 20px; padding: 0.75rem 1.25rem; border-radius: 8px; color: white; font-weight: 500; z-index: 1100; animation: slideIn 0.3s ease; }
.toast-success { background: #059669; }
.toast-warning { background: #d97706; }
.toast-error { background: #dc2626; }

.alert-expired {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 8px;
  color: #991b1b;
  font-size: 0.85rem;
  margin-bottom: 0.5rem;
}

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
  .modal-overlay { padding: 0.5rem; }
  .modal-content { border-radius: 12px; }
  .modal-header { padding: 0.85rem 1rem; }
  .modal-header h5 { font-size: 0.95rem; }
  .modal-body { padding: 1rem; }
  .modal-footer { padding: 0.75rem 1rem; gap: 0.4rem; flex-wrap: wrap; }
  .modal-footer .btn { flex: 1; min-width: 0; }
}
@media (max-width: 480px) {
  .stats-grid { grid-template-columns: 1fr; }
}
</style>