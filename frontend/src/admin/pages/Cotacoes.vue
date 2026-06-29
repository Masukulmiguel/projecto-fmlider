<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <div>
        <h2>{{ t('admin.cotacoes_title') }}</h2>
        <p class="text-muted mb-0">{{ t('admin.cotacoes_description') }}</p>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" :placeholder="t('admin.cotacoes_search')" @input="debounceSearch">
          </div>
          <select v-model="filters.status" class="form-select" @change="fetchData">
            <option value="">{{ t('admin.cotacoes_all_statuses') }}</option>
            <option value="pendente">{{ t('admin.cotacoes_status_pendente') }}</option>
            <option value="aprovada">{{ t('admin.cotacoes_status_aprovada') }}</option>
            <option value="rejeitada">{{ t('admin.cotacoes_status_rejeitada') }}</option>
            <option value="expirada">{{ t('admin.cotacoes_expired') }}</option>
          </select>
        </div>

        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
          {{ t('admin.cotacoes_empty') }}
        </div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>{{ t('admin.cotacoes_ref') }}</th>
                <th>{{ t('admin.cotacoes_client') }}</th>
                <th>{{ t('admin.cotacoes_route') }}</th>
                <th>{{ t('admin.cotacoes_type') }}</th>
                <th>{{ t('admin.cotacoes_value_label') }}</th>
                <th>{{ t('admin.cotacoes_status') }}</th>
                <th>{{ t('admin.cotacoes_date') }}</th>
                <th>{{ t('admin.actions_col') }}</th>
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
        </div>
      </div>
    </div>

    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEdit">
      <div class="modal-content">
        <div class="modal-header">
          <h5>{{ t('admin.cotacoes_title') }}</h5>
          <button class="btn-close" @click="closeEdit"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">{{ t('admin.cotacoes_status_label') }}</label>
            <select v-model="editForm.status" class="form-select">
              <option value="pendente">{{ t('admin.cotacoes_status_pendente') }}</option>
              <option value="aprovada">{{ t('admin.cotacoes_status_aprovada') }}</option>
              <option value="rejeitada">{{ t('admin.cotacoes_status_rejeitada') }}</option>
              <option value="convertida">{{ t('admin.cotacoes_status_convertida') }}</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">{{ t('admin.cotacoes_value_label') }}</label>
            <input v-model="editForm.value" type="number" class="form-control" placeholder="0.00">
          </div>
          <div class="mb-3">
            <label class="form-label">{{ t('admin.cotacoes_notes') }}</label>
            <textarea v-model="editForm.notes" class="form-control" rows="3" :placeholder="t('admin.cotacoes_notes_placeholder')"></textarea>
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
          <p>{{ t('admin.cotacoes_confirm_delete') }} <strong>{{ deleteItem?.reference }}</strong>?</p>
          <p class="text-muted small mb-0">{{ t('admin.cotacoes_delete_warning') }}</p>
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

    <div v-if="toast.show" class="toast-container" :class="'toast-' + toast.type">
      <i :class="toast.type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill'" class="me-2"></i>
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()

const items = ref([])
const loading = ref(false)
const filters = reactive({ q: '', status: '' })
let searchTimer = null

const fetchData = async () => {
  loading.value = true
  try {
    let query = supabase.from('cotacoes').select('*')
    if (filters.status) query = query.eq('status', filters.status)
    if (filters.q) query = query.or(`reference.ilike.%${filters.q}%,client_name.ilike.%${filters.q}%,company_name.ilike.%${filters.q}%`)
    const { data, error } = await query.order('created_at', { ascending: false })
    if (!error) items.value = data
  } finally { loading.value = false }
}

const debounceSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(fetchData, 300) }

const typeLabel = (type) => ({ maritimo: t('admin.cotacoes_type_maritimo'), aereo: t('admin.cotacoes_type_aereo'), terrestre: t('admin.cotacoes_type_terrestre'), ferroviario: t('admin.cotacoes_type_ferroviario'), multimodal: t('admin.cotacoes_type_multimodal') }[type] || type)
const statusLabel = (status) => ({ pendente: t('admin.cotacoes_status_pendente'), aprovada: t('admin.cotacoes_status_aprovada'), rejeitada: t('admin.cotacoes_status_rejeitada'), expirada: t('admin.cotacoes_expired') }[status] || status)
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
    const { error } = await supabase.from('cotacoes').update({
      status: editForm.status,
      estimated_value: editForm.value,
      notes: editForm.notes
    }).eq('id', editItem.value.id)
    if (error) throw error
    showToast('success', t('admin.cotacoes_success_updated'))
    closeEdit()
    fetchData()
  } catch (e) {
    showToast('error', t('admin.cotacoes_error_updating'))
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
    const { error } = await supabase.from('cotacoes').delete().eq('id', deleteItem.value.id)
    if (error) throw error
    showToast('success', t('admin.cotacoes_success_deleted'))
    closeDelete()
    fetchData()
  } catch (e) {
    showToast('error', t('admin.cotacoes_error_deleting'))
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