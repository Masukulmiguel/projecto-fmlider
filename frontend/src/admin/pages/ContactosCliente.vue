<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <div>
        <h2>Contactos dos Clientes</h2>
        <p class="text-muted mb-0">Lista consolidada de contactos.</p>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" placeholder="Pesquisar..." @input="debounceSearch">
          </div>
        </div>

        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
          Nenhum contacto encontrado.
        </div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Nome</th>
                <th>Cliente</th>
                <th>Empresa</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td><strong>{{ item.name }}</strong></td>
                <td>{{ item.company_name || item.client_name }}</td>
                <td>{{ item.company || '—' }}</td>
                <td>{{ item.email || '—' }}</td>
                <td>{{ item.phone || '—' }}</td>
                <td>
                  <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-info" @click="openDetail(item)" title="Ver Detalhe">
                      <i class="bi bi-eye"></i>
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

    <div v-if="showDetailModal" class="modal-overlay" @click.self="closeDetail">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Detalhe do Contacto</h5>
          <button class="btn-close" @click="closeDetail"></button>
        </div>
        <div class="modal-body">
          <div class="detail-row">
            <label class="form-label fw-bold">Nome</label>
            <p>{{ detailItem?.name || '—' }}</p>
          </div>
          <div class="detail-row">
            <label class="form-label fw-bold">Email</label>
            <p>{{ detailItem?.email || '—' }}</p>
          </div>
          <div class="detail-row">
            <label class="form-label fw-bold">Telefone</label>
            <p>{{ detailItem?.phone || '—' }}</p>
          </div>
          <div class="detail-row">
            <label class="form-label fw-bold">Empresa</label>
            <p>{{ detailItem?.company || '—' }}</p>
          </div>
          <div class="detail-row">
            <label class="form-label fw-bold">Cliente</label>
            <p>{{ detailItem?.company_name || detailItem?.client_name || '—' }}</p>
          </div>
          <div class="detail-row">
            <label class="form-label fw-bold">Assunto</label>
            <p>{{ detailItem?.subject || '—' }}</p>
          </div>
          <div class="detail-row">
            <label class="form-label fw-bold">Mensagem</label>
            <p class="message-text">{{ detailItem?.message || '—' }}</p>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDetail">Fechar</button>
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
          <p>Tem certeza que deseja eliminar o contacto de <strong>{{ deleteItem?.name }}</strong>?</p>
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
import { supabase } from '@/lib/supabase'

const items = ref([])
const loading = ref(false)
const filters = reactive({ q: '' })
let searchTimer = null

const fetchData = async () => {
  loading.value = true
  try {
    let query = supabase.from('contactos').select('*')
    if (filters.q) query = query.or(`name.ilike.%${filters.q}%,email.ilike.%${filters.q}%,company.ilike.%${filters.q}%`)
    const { data, error } = await query.order('created_at', { ascending: false })
    if (!error) items.value = data
  } finally { loading.value = false }
}

const debounceSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(fetchData, 300) }

const showDetailModal = ref(false)
const detailItem = ref(null)

const openDetail = (item) => {
  detailItem.value = item
  showDetailModal.value = true
}

const closeDetail = () => {
  showDetailModal.value = false
  detailItem.value = null
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
    const { error } = await supabase.from('contactos').delete().eq('id', deleteItem.value.id)
    if (error) throw error
    showToast('success', 'Contacto eliminado com sucesso.')
    closeDelete()
    fetchData()
  } catch (e) {
    showToast('error', 'Erro ao eliminar contacto.')
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
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1050; }
.modal-content { background: white; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
.modal-content.modal-sm { max-width: 400px; }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
.modal-header h5 { margin: 0; font-weight: 600; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem; }
.detail-row { margin-bottom: 1rem; }
.detail-row label { margin-bottom: 0.25rem; color: #64748b; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; }
.detail-row p { margin: 0; color: #1e293b; }
.message-text { white-space: pre-wrap; background: #f8fafc; padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0; }
.toast-container { position: fixed; top: 20px; right: 20px; padding: 0.75rem 1.25rem; border-radius: 8px; color: white; font-weight: 500; z-index: 1100; animation: slideIn 0.3s ease; }
.toast-success { background: #059669; }
.toast-error { background: #dc2626; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>