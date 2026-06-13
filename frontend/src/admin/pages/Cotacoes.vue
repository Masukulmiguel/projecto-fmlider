<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <div>
        <h2>Cotações</h2>
        <p class="text-muted mb-0">Todas as cotações dos clientes.</p>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" placeholder="Pesquisar..." @input="debounceSearch">
          </div>
          <select v-model="filters.status" class="form-select" @change="fetchData">
            <option value="">Todos os estados</option>
            <option value="pendente">Pendente</option>
            <option value="aprovada">Aprovada</option>
            <option value="rejeitada">Rejeitada</option>
            <option value="expirada">Expirada</option>
          </select>
        </div>

        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
          Nenhuma cotação encontrada.
        </div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Referência</th>
                <th>Cliente</th>
                <th>Rota</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Estado</th>
                <th>Data</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td><code class="ref-code">{{ item.reference }}</code></td>
                <td>{{ item.company_name || item.client_name }}</td>
                <td>
                  <div class="d-flex align-items-center gap-1">
                    <span>{{ item.origin }}</span>
                    <i class="bi bi-arrow-right text-muted"></i>
                    <span>{{ item.destination }}</span>
                  </div>
                </td>
                <td>{{ typeLabel(item.type) }}</td>
                <td>{{ formatCurrency(item.estimated_value, item.currency) }}</td>
                <td><span class="status-badge" :class="'status-' + item.status">{{ statusLabel(item.status) }}</span></td>
                <td><small class="text-muted">{{ formatDate(item.created_at) }}</small></td>
                <td>
                  <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" @click="openEdit(item)" title="Editar">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="openDelete(item)" title="Eliminar">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEdit">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Editar Cotação</h5>
          <button class="btn-close" @click="closeEdit"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Estado</label>
            <select v-model="editForm.status" class="form-select">
              <option value="pendente">Pendente</option>
              <option value="aprovada">Aprovada</option>
              <option value="rejeitada">Rejeitada</option>
              <option value="convertida">Convertida</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Valor</label>
            <input v-model="editForm.value" type="number" class="form-control" placeholder="0.00">
          </div>
          <div class="mb-3">
            <label class="form-label">Notas</label>
            <textarea v-model="editForm.notes" class="form-control" rows="3" placeholder="Notas..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeEdit">Cancelar</button>
          <button class="btn btn-primary" @click="submitEdit" :disabled="saving">
            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
            Guardar
          </button>
        </div>
      </div>
    </div>

    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDelete">
      <div class="modal-content modal-sm">
        <div class="modal-header">
          <h5>Confirmar Eliminação</h5>
          <button class="btn-close" @click="closeDelete"></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja eliminar a cotação <strong>{{ deleteItem?.reference }}</strong>?</p>
          <p class="text-muted small mb-0">Esta ação não pode ser desfeita.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDelete">Cancelar</button>
          <button class="btn btn-danger" @click="submitDelete" :disabled="deleting">
            <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
            Eliminar
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
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const items = ref([])
const loading = ref(false)
const filters = reactive({ q: '', status: '' })
let searchTimer = null

const authHeader = () => ({ headers: { Authorization: 'Bearer ' + authStore.token } })

const fetchData = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.q) params.q = filters.q
    if (filters.status) params.status = filters.status
    const r = await axios.get('/api/cotacoes', { ...authHeader(), params })
    if (r.data.success) items.value = r.data.data.cotacoes
  } finally { loading.value = false }
}

const debounceSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(fetchData, 300) }

const typeLabel = (t) => ({ maritimo: 'Marítimo', aereo: 'Aéreo', terrestre: 'Terrestre', ferroviario: 'Ferroviário', multimodal: 'Multimodal' }[t] || t)
const statusLabel = (s) => ({ pendente: 'Pendente', aprovada: 'Aprovada', rejeitada: 'Rejeitada', expirada: 'Expirada' }[s] || s)
const formatCurrency = (v, c) => v ? new Intl.NumberFormat('pt-AO', { style: 'currency', currency: c || 'AOA', maximumFractionDigits: 0 }).format(v) : '—'
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : '—'

const showEditModal = ref(false)
const saving = ref(false)
const editItem = ref(null)
const editForm = reactive({ status: '', value: '', notes: '' })

const openEdit = (item) => {
  editItem.value = item
  editForm.status = item.status
  editForm.value = item.estimated_value || ''
  editForm.notes = item.notes || ''
  showEditModal.value = true
}

const closeEdit = () => {
  showEditModal.value = false
  editItem.value = null
}

const submitEdit = async () => {
  saving.value = true
  try {
    await axios.put('/api/cotacoes/' + editItem.value.id, {
      status: editForm.status,
      estimated_value: editForm.value,
      notes: editForm.notes
    }, authHeader())
    showToast('success', 'Cotação atualizada com sucesso.')
    closeEdit()
    fetchData()
  } catch (e) {
    showToast('error', 'Erro ao atualizar cotação.')
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
    await axios.delete('/api/cotacoes/' + deleteItem.value.id, authHeader())
    showToast('success', 'Cotação eliminada com sucesso.')
    closeDelete()
    fetchData()
  } catch (e) {
    showToast('error', 'Erro ao eliminar cotação.')
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

onMounted(fetchData)
</script>

<style scoped>
.admin-page { background: #f8f9fa; min-height: 100vh; position: relative; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-body { padding: 1.5rem; }
.filters { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.search-box { position: relative; flex: 1; min-width: 240px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input { width: 100%; padding: 0.55rem 0.75rem 0.55rem 2.25rem; border: 2px solid #e2e8f0; border-radius: 8px; }
.search-box input:focus { border-color: #2563eb; outline: none; }
.form-select { max-width: 200px; border: 2px solid #e2e8f0; border-radius: 8px; }
.ref-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }
.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.status-pendente { background: #fef3c7; color: #92400e; }
.status-aprovada { background: #d1fae5; color: #065f46; }
.status-rejeitada { background: #fee2e2; color: #991b1b; }
.status-expirada { background: #e5e7eb; color: #4b5563; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1050; }
.modal-content { background: white; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
.modal-content.modal-sm { max-width: 400px; }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
.modal-header h5 { margin: 0; font-weight: 600; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem; }
.toast-container { position: fixed; top: 20px; right: 20px; padding: 0.75rem 1.25rem; border-radius: 8px; color: white; font-weight: 500; z-index: 1100; animation: slideIn 0.3s ease; }
.toast-success { background: #059669; }
.toast-error { background: #dc2626; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>