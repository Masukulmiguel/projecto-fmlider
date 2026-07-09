<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <h2>Gestão de Camiões</h2>
      <p class="text-muted mb-0">Gestão de frota e viaturas</p>
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
        <div class="stat-icon" style="background: #d1fae5; color: #065f46;">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.disponivel }}</span>
          <span class="stat-label">Disponíveis</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #92400e;">
          <i class="bi bi-gear-wide-connected"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.em_servico }}</span>
          <span class="stat-label">Em Serviço</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #fee2e2; color: #991b1b;">
          <i class="bi bi-wrench"></i>
        </div>
        <div class="stat-info">
          <span class="stat-value">{{ stats.em_manutencao }}</span>
          <span class="stat-label">Em Manutenção</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" placeholder="Pesquisar por código, matrícula, marca ou modelo..." @input="debounceSearch">
          </div>
          <select v-model="filters.estado" class="form-select" @change="fetchData">
            <option value="">Todos os estados</option>
            <option value="disponivel">Disponível</option>
            <option value="em_servico">Em Serviço</option>
            <option value="em_manutencao">Em Manutenção</option>
            <option value="inativo">Inativo</option>
          </select>
          <button class="btn btn-primary" @click="openCreate">
            <i class="bi bi-plus-lg me-1"></i>
            Novo Camião
          </button>
        </div>

        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
          Nenhum camião encontrado.
        </div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Código</th>
                <th>Matrícula</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Capacidade</th>
                <th>Estado</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td><code class="tracking-code">{{ item.codigo_interno }}</code></td>
                <td>{{ item.matricula || '' }}</td>
                <td>{{ item.marca || '' }}</td>
                <td>{{ item.modelo || '' }}</td>
                <td>{{ item.capacidade || '' }}</td>
                <td><span class="status-badge" :class="'status-' + item.estado">{{ estadoLabel(item.estado) }}</span></td>
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
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEdit">
      <div class="modal-content">
        <div class="modal-header">
          <h5>{{ editingItem ? 'Editar Camião' : 'Novo Camião' }}</h5>
          <button class="btn-close" @click="closeEdit"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Código Interno</label>
            <input v-model="editForm.codigo_interno" type="text" class="form-control" placeholder="Ex: CAM-001">
          </div>
          <div class="mb-3">
            <label class="form-label">Matrícula</label>
            <input v-model="editForm.matricula" type="text" class="form-control" placeholder="Ex: LD-12-34-AB">
          </div>
          <div class="mb-3">
            <label class="form-label">Marca</label>
            <input v-model="editForm.marca" type="text" class="form-control" placeholder="Ex: Volvo, Mercedes...">
          </div>
          <div class="mb-3">
            <label class="form-label">Modelo</label>
            <input v-model="editForm.modelo" type="text" class="form-control" placeholder="Ex: FH 500">
          </div>
          <div class="mb-3">
            <label class="form-label">Capacidade</label>
            <input v-model="editForm.capacidade" type="text" class="form-control" placeholder="Ex: 20 toneladas">
          </div>
          <div class="mb-3">
            <label class="form-label">Estado</label>
            <select v-model="editForm.estado" class="form-select">
              <option value="disponivel">Disponível</option>
              <option value="em_servico">Em Serviço</option>
              <option value="em_manutencao">Em Manutenção</option>
              <option value="inativo">Inativo</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Observações</label>
            <textarea v-model="editForm.observacoes" class="form-control" rows="3" placeholder="Notas internas..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeEdit">Cancelar</button>
          <button class="btn btn-primary" @click="save" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
            {{ editingItem ? 'Salvar' : 'Criar' }}
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
          <p>Tem certeza que deseja eliminar o camião <strong>{{ deletingItem?.codigo_interno }}</strong>?</p>
          <p class="text-muted small mb-0">Esta acção não pode ser desfeita.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDelete">Cancelar</button>
          <button class="btn btn-danger" @click="deleteItem" :disabled="deleting">
            <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
            Eliminar
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
import { ref, reactive, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'

const items = ref([])
const loading = ref(false)
const filters = reactive({ q: '', estado: '' })
let searchTimer = null

const stats = reactive({ total: 0, disponivel: 0, em_servico: 0, em_manutencao: 0 })

const fetchData = async () => {
  loading.value = true
  try {
    let query = supabase.from('camioes').select('*')
    if (filters.estado) query = query.eq('estado', filters.estado)
    if (filters.q) {
      query = query.or(`codigo_interno.ilike.%${filters.q}%,matricula.ilike.%${filters.q}%,marca.ilike.%${filters.q}%,modelo.ilike.%${filters.q}%`)
    }
    query = query.order('codigo_interno', { ascending: true })
    const { data, error } = await query
    if (error) throw error
    items.value = data || []
    computeStats()
  } catch (e) {
    showToast('error', 'Erro ao carregar camiões.')
  } finally { loading.value = false }
}

const computeStats = () => {
  const all = items.value
  stats.total = all.length
  stats.disponivel = all.filter(i => i.estado === 'disponivel').length
  stats.em_servico = all.filter(i => i.estado === 'em_servico').length
  stats.em_manutencao = all.filter(i => i.estado === 'em_manutencao').length
}

const debounceSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => fetchData(), 300)
}

const estadoLabel = (estado) => ({
  disponivel: 'Disponível',
  em_servico: 'Em Serviço',
  em_manutencao: 'Em Manutenção',
  inativo: 'Inativo'
}[estado] || estado)

const showEditModal = ref(false)
const editingItem = ref(null)
const saving = ref(false)
const editForm = reactive({
  codigo_interno: '', matricula: '', marca: '', modelo: '', capacidade: '', estado: 'disponivel', observacoes: ''
})

const openCreate = () => {
  editingItem.value = null
  Object.assign(editForm, { codigo_interno: '', matricula: '', marca: '', modelo: '', capacidade: '', estado: 'disponivel', observacoes: '' })
  showEditModal.value = true
}

const openEdit = (item) => {
  editingItem.value = item
  Object.assign(editForm, {
    codigo_interno: item.codigo_interno || '',
    matricula: item.matricula || '',
    marca: item.marca || '',
    modelo: item.modelo || '',
    capacidade: item.capacidade || '',
    estado: item.estado || 'disponivel',
    observacoes: item.observacoes || ''
  })
  showEditModal.value = true
}

const closeEdit = () => { showEditModal.value = false; editingItem.value = null }

const save = async () => {
  if (!editForm.codigo_interno.trim()) {
    showToast('error', 'O código interno é obrigatório.')
    return
  }
  if (!editForm.matricula.trim()) {
    showToast('error', 'A matrícula é obrigatória.')
    return
  }
  saving.value = true
  try {
    if (editingItem.value) {
      const { data: existingMat } = await supabase.from('camioes').select('id, codigo_interno').eq('matricula', editForm.matricula.trim()).maybeSingle()
      if (existingMat && existingMat.id !== editingItem.value.id) {
        showToast('error', `Esta matrícula já está registada no camião "${existingMat.codigo_interno}".`)
        saving.value = false
        return
      }
      const { error } = await supabase.from('camioes').update(editForm).eq('id', editingItem.value.id)
      if (error) throw error
      showToast('success', 'Camião atualizado com sucesso!')
    } else {
      const { data: existingCod } = await supabase.from('camioes').select('id').eq('codigo_interno', editForm.codigo_interno.trim()).maybeSingle()
      if (existingCod) {
        showToast('error', 'Este código interno já está em uso.')
        saving.value = false
        return
      }
      const { data: existingMat } = await supabase.from('camioes').select('id, codigo_interno').eq('matricula', editForm.matricula.trim()).maybeSingle()
      if (existingMat) {
        showToast('error', `Esta matrícula já está registada no camião "${existingMat.codigo_interno}".`)
        saving.value = false
        return
      }
      const { error } = await supabase.from('camioes').insert(editForm)
      if (error) throw error
      showToast('success', 'Camião criado com sucesso!')
    }
    closeEdit()
    fetchData()
  } catch (e) {
    console.error('Erro ao salvar camião:', e)
    const msg = e?.message || e?.error?.message || JSON.stringify(e)
    showToast('error', 'Erro ao salvar camião: ' + msg)
  } finally { saving.value = false }
}

const showDeleteModal = ref(false)
const deletingItem = ref(null)
const deleting = ref(false)

const openDelete = (item) => { deletingItem.value = item; showDeleteModal.value = true }
const closeDelete = () => { showDeleteModal.value = false; deletingItem.value = null }

const deleteItem = async () => {
  deleting.value = true
  try {
    const { error } = await supabase.from('camioes').delete().eq('id', deletingItem.value.id)
    if (error) throw error
    showToast('success', 'Camião eliminado com sucesso!')
    closeDelete()
    fetchData()
  } catch (e) {
    showToast('error', 'Erro ao eliminar camião.')
  } finally { deleting.value = false }
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

onMounted(() => fetchData())
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

.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.status-disponivel { background: #d1fae5; color: #065f46; }
.status-em_servico { background: #fef3c7; color: #92400e; }
.status-em_manutencao { background: #fee2e2; color: #991b1b; }
.status-inativo { background: #f1f5f9; color: #475569; }

.action-buttons { display: flex; gap: 0.35rem; }
.btn-icon {
  width: 32px; height: 32px; border: none; border-radius: 8px; display: flex;
  align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;
}
.btn-edit { background: #eff6ff; color: #1d4ed8; }
.btn-edit:hover { background: #dbeafe; }
.btn-delete { background: #fef2f2; color: #dc2626; }
.btn-delete:hover { background: #fee2e2; }

.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1050; }
.modal-content { background: white; border-radius: 12px; width: 100%; max-width: 520px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
.modal-content.modal-sm { max-width: 400px; }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
.modal-header h5 { margin: 0; font-weight: 600; }
.modal-body { padding: 1.5rem; overflow-y: auto; flex: 1; min-height: 0; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem; flex-shrink: 0; }

.toast-container { position: fixed; top: 20px; right: 20px; padding: 0.75rem 1.25rem; border-radius: 8px; color: white; font-weight: 500; z-index: 1100; animation: slideIn 0.3s ease; }
.toast-success { background: #059669; }
.toast-error { background: #dc2626; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

@media (max-width: 768px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
}
@media (max-width: 480px) {
  .stats-grid { grid-template-columns: 1fr; }
}
</style>
