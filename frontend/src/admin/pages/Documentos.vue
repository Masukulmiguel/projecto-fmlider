<template>
  <div class="admin-page p-5">
    <div class="page-header mb-4">
      <div>
        <h2>{{ t('admin.documentos_title') }}</h2>
        <p class="text-muted mb-0">{{ t('admin.documentos_description') }}</p>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filters mb-3">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" :placeholder="t('admin.documentos_search')" @input="debounceSearch">
          </div>
        </div>

        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
          {{ t('admin.documentos_no_results') }}
        </div>
        <div v-else class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>{{ t('admin.documentos_col_document') }}</th>
                <th>{{ t('admin.documentos_col_client') }}</th>
                <th>{{ t('admin.documentos_col_type') }}</th>
                <th>{{ t('admin.documentos_col_shipment') }}</th>
                <th>{{ t('admin.documentos_size') }}</th>
                <th>{{ t('admin.documentos_col_date') }}</th>
                <th>{{ t('admin.documentos_col_actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>
                  <i :class="fileIcon(item.mime_type)" class="me-2"></i>
                  {{ item.name }}
                </td>
                <td>
                  <div class="fw-medium">{{ item.company_name || item.client_name }}</div>
                </td>
                <td>{{ typeLabel(item.type) }}</td>
                <td><code v-if="item.tracking_number" class="tracking-code">{{ item.tracking_number }}</code><span v-else></span></td>
                <td>{{ formatSize(item.file_size) }}</td>
                <td><small class="text-muted">{{ formatDate(item.created_at) }}</small></td>
                <td>
                  <div class="d-flex gap-2">
                    <a :href="'/api/documentos/' + item.id + '/download'" target="_blank" class="btn btn-sm btn-outline-success" title="Download">
                      <i class="bi bi-download"></i>
                    </a>
              <button class="btn-icon btn-delete" @click="openDelete(item)" :title="t('admin.documentos_delete')">
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

    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDelete">
      <div class="modal-content modal-sm">
        <div class="modal-header">
          <h5>{{ t('common.confirm') }}</h5>
          <button class="btn-close" @click="closeDelete"></button>
        </div>
        <div class="modal-body">
          <p>{{ t('admin.documentos_delete_confirm') }} <strong>{{ deleteItem?.name }}</strong>?</p>
          <p class="text-muted small mb-0">{{ t('admin.documentos_delete_warning') }}</p>
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
const filters = reactive({ q: '' })
let searchTimer = null

const fetchData = async () => {
  loading.value = true
  try {
    let query = supabase.from('documentos').select('*')
    if (filters.q) query = query.or(`name.ilike.%${filters.q}%,client_name.ilike.%${filters.q}%,company_name.ilike.%${filters.q}%`)
    const { data, error } = await query.order('created_at', { ascending: false })
    if (!error) items.value = data
  } finally { loading.value = false }
}

const debounceSearch = () => { clearTimeout(searchTimer); searchTimer = setTimeout(fetchData, 300) }

const typeLabel = (type) => ({
  fatura: t('admin.documentos_type_fatura'),
  conhecimento_carga: t('admin.documentos_type_bl'),
  certificado: t('admin.documentos_type_certificado'),
  contrato: t('admin.documentos_type_contrato'),
  outro: t('admin.documentos_type_outro'),
}[type] || type)
const fileIcon = (m) => {
  if (!m) return 'bi bi-file-earmark'
  if (m.includes('pdf')) return 'bi bi-file-earmark-pdf text-danger'
  if (m.includes('image')) return 'bi bi-file-earmark-image text-primary'
  return 'bi bi-file-earmark'
}
const formatSize = (b) => !b ? '' : b < 1024 * 1024 ? (b/1024).toFixed(1)+' KB' : (b/1024/1024).toFixed(2)+' MB'
const formatDate = (d) => {
  if (!d) return ''
  const localeMap = { pt: 'pt-PT', en: 'en-GB', fr: 'fr-FR' }
  return new Date(d).toLocaleDateString(localeMap[useI18n().locale.value] || 'pt-PT')
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
    const { error } = await supabase.from('documentos').delete().eq('id', deleteItem.value.id)
    if (error) throw error
    showToast('success', t('admin.documentos_delete_success'))
    closeDelete()
    fetchData()
  } catch (e) {
    showToast('error', t('admin.documentos_delete_error'))
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
.tracking-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: #334155; }
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
.toast-error { background: #dc2626; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@media (max-width: 768px) {
  .admin-page { padding: 1rem !important; }
  .page-header h2 { font-size: 1.1rem; }
  .modal-overlay { padding: 0.5rem; }
  .modal-content { border-radius: 12px; }
  .modal-header { padding: 0.85rem 1rem; }
  .modal-header h5 { font-size: 0.95rem; }
  .modal-body { padding: 1rem; }
  .modal-footer { padding: 0.75rem 1rem; gap: 0.4rem; flex-wrap: wrap; }
  .modal-footer .btn { flex: 1; min-width: 0; }
}
@media (max-width: 480px) {
  .admin-page { padding: 0.75rem !important; }
}
</style>