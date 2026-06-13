<template>
  <div class="crud-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Documentos</h1>
        <p class="text-muted mb-0">Gerencie os documentos do embarque.</p>
      </div>
      <button class="btn btn-primary" @click="openForm()">
        <i class="bi bi-upload me-1"></i> Enviar documento
      </button>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="filters">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input v-model="filters.q" type="text" placeholder="Pesquisar por nome..." @input="debounceSearch">
          </div>
          <select v-model="filters.type" class="form-select" @change="fetchData">
            <option value="">Todos os tipos</option>
            <option value="fatura">Fatura</option>
            <option value="conhecimento_carga">Conhecimento de carga</option>
            <option value="certificado">Certificado</option>
            <option value="contrato">Contrato</option>
            <option value="outro">Outro</option>
          </select>
        </div>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="empty-state">
          <i class="bi bi-file-earmark"></i>
          <p>Nenhum documento encontrado.</p>
          <button class="btn btn-primary btn-sm" @click="openForm()">
            <i class="bi bi-upload me-1"></i> Enviar primeiro documento
          </button>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>Documento</th>
                <th>Tipo</th>
                <th>Embarque</th>
                <th>Tamanho</th>
                <th>Data</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <i :class="fileIcon(item.mime_type)" class="file-icon-lg"></i>
                    <div>
                      <div class="fw-medium">{{ item.name }}</div>
                      <small class="text-muted">{{ item.description }}</small>
                    </div>
                  </div>
                </td>
                <td><span class="type-badge">{{ typeLabel(item.type) }}</span></td>
                <td>
                  <code v-if="item.tracking_number" class="tracking-code">{{ item.tracking_number }}</code>
                  <span v-else class="text-muted">—</span>
                </td>
                <td>{{ formatSize(item.file_size) }}</td>
                <td><small class="text-muted">{{ formatDate(item.created_at) }}</small></td>
                <td class="text-end">
                  <div class="action-buttons">
                    <a :href="item.file_path" target="_blank" class="btn btn-sm btn-outline-primary" title="Descarregar">
                      <i class="bi bi-download"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary" @click="openForm(item)" title="Editar">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(item)" title="Eliminar">
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

    <div class="modal fade show d-block" v-if="showForm" @click.self="closeForm" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editing ? 'Editar documento' : 'Enviar documento' }}</h5>
            <button type="button" class="btn-close" @click="closeForm"></button>
          </div>
          <form @submit.prevent="handleSubmit" novalidate>
            <div class="modal-body">
              <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
              <div v-if="editing" class="mb-3">
                <label class="form-label">Nome *</label>
                <input v-model="form.name" type="text" class="form-control" :class="{'is-invalid': errors.name}" required>
                <div class="invalid-feedback">{{ errors.name }}</div>
              </div>
              <div v-else>
                <div class="mb-3">
                  <label class="form-label">Ficheiro *</label>
                  <input ref="fileInput" type="file" class="form-control" :class="{'is-invalid': errors.file}" required>
                  <div class="invalid-feedback">{{ errors.file }}</div>
                  <small class="text-muted">Máx 20MB</small>
                </div>
                <div class="mb-3">
                  <label class="form-label">Nome do documento *</label>
                  <input v-model="form.name" type="text" class="form-control" :class="{'is-invalid': errors.name}" required>
                  <div class="invalid-feedback">{{ errors.name }}</div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Tipo</label>
                <select v-model="form.type" class="form-select">
                  <option value="fatura">Fatura</option>
                  <option value="conhecimento_carga">Conhecimento de carga</option>
                  <option value="certificado">Certificado</option>
                  <option value="contrato">Contrato</option>
                  <option value="outro">Outro</option>
                </select>
              </div>
              <div class="mb-3" v-if="!editing && embarques.length">
                <label class="form-label">Embarque (opcional)</label>
                <select v-model="form.embarque_id" class="form-select">
                  <option value="">— Sem embarque —</option>
                  <option v-for="e in embarques" :key="e.id" :value="e.id">{{ e.tracking_number }} — {{ e.origin }} → {{ e.destination }}</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea v-model="form.description" rows="2" class="form-control"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" @click="closeForm">Cancelar</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                {{ editing ? 'Atualizar' : 'Enviar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-if="showForm"></div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const items = ref([])
const embarques = ref([])
const loading = ref(false)
const saving = ref(false)
const showForm = ref(false)
const editing = ref(null)
const fileInput = ref(null)
const filters = reactive({ q: '', type: '' })
const form = reactive({ name: '', type: 'outro', embarque_id: '', description: '' })
const errors = ref({})
const errorMessage = ref('')
let searchTimer = null

const fetchData = async () => {
  loading.value = true
  try {
    const userId = authStore.user?.id
    if (!userId) return

    let query = supabase.from('documentos').select('*').eq('user_id', userId)
    if (filters.type) query = query.eq('type', filters.type)
    if (filters.q) {
      query = query.or(`name.ilike.%${filters.q}%,description.ilike.%${filters.q}%`)
    }
    const { data, error } = await query
    if (!error) items.value = data || []
  } finally {
    loading.value = false
  }
}

const fetchEmbarques = async () => {
  try {
    const userId = authStore.user?.id
    if (!userId) return
    const { data } = await supabase.from('embarques').select('id, tracking_number, origin, destination').eq('user_id', userId)
    embarques.value = data || []
  } catch (e) {}
}

const debounceSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(fetchData, 300)
}

const openForm = (item = null) => {
  editing.value = item
  if (item) {
    Object.assign(form, { name: item.name, type: item.type, description: item.description || '' })
  } else {
    Object.assign(form, { name: '', type: 'outro', embarque_id: '', description: '' })
    fetchEmbarques()
  }
  errorMessage.value = ''
  errors.value = {}
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
  editing.value = null
}

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''
  saving.value = true
  try {
    if (editing.value) {
      const { error } = await supabase.from('documentos').update({ name: form.name, type: form.type, description: form.description }).eq('id', editing.value.id)
      if (error) throw error
      closeForm()
      await fetchData()
    } else {
      if (!fileInput.value?.files[0]) {
        errors.value.file = 'Selecione um ficheiro'
        saving.value = false
        return
      }
      const file = fileInput.value.files[0]
      const userId = authStore.user?.id
      const fileExt = file.name.split('.').pop()
      const filePath = `${userId}/${Date.now()}.${fileExt}`

      const { error: uploadError } = await supabase.storage.from('documents').upload(filePath, file)
      if (uploadError) throw uploadError

      const { data: urlData } = supabase.storage.from('documents').getPublicUrl(filePath)

      const { error: insertError } = await supabase.from('documentos').insert({
        name: form.name,
        type: form.type,
        description: form.description || '',
        embarque_id: form.embarque_id || null,
        file_path: urlData.publicUrl,
        file_name: file.name,
        file_size: file.size,
        mime_type: file.type,
        user_id: userId,
      })
      if (insertError) throw insertError
      closeForm()
      await fetchData()
    }
  } catch (error) {
    errorMessage.value = error.message || 'Erro ao guardar'
  } finally {
    saving.value = false
  }
}

const confirmDelete = async (item) => {
  if (!confirm(`Eliminar "${item.name}"?`)) return
  try {
    const { error } = await supabase.from('documentos').delete().eq('id', item.id)
    if (error) throw error
    await fetchData()
  } catch (e) { alert('Erro ao eliminar') }
}

const typeLabel = (t) => ({ fatura: 'Fatura', conhecimento_carga: 'B/L', certificado: 'Certificado', contrato: 'Contrato', outro: 'Outro' }[t] || t)
const fileIcon = (m) => {
  if (!m) return 'bi bi-file-earmark'
  if (m.includes('pdf')) return 'bi bi-file-earmark-pdf text-danger'
  if (m.includes('image')) return 'bi bi-file-earmark-image text-primary'
  if (m.includes('word') || m.includes('document')) return 'bi bi-file-earmark-word text-primary'
  if (m.includes('sheet') || m.includes('excel')) return 'bi bi-file-earmark-excel text-success'
  return 'bi bi-file-earmark'
}
const formatSize = (b) => {
  if (!b) return '—'
  if (b < 1024) return b + ' B'
  if (b < 1024 * 1024) return (b / 1024).toFixed(1) + ' KB'
  return (b / 1024 / 1024).toFixed(2) + ' MB'
}
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT') : '—'

onMounted(fetchData)
</script>

<style scoped>
.crud-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.page-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }

.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-header { background: white; border-bottom: 1px solid #f1f5f9; padding: 1rem 1.25rem; }
.filters { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.search-box { position: relative; flex: 1; min-width: 240px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input { width: 100%; padding: 0.6rem 0.75rem 0.6rem 2.25rem; border: 2px solid #e2e8f0; border-radius: 8px; }
.search-box input:focus { border-color: #2563eb; outline: none; }
.filters .form-select { max-width: 220px; border: 2px solid #e2e8f0; border-radius: 8px; }

.empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
.empty-state p { margin-bottom: 1rem; }

.file-icon-lg { font-size: 1.5rem; }
.tracking-code { background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem; color: #334155; }
.type-badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.8rem; font-weight: 500; background: #f1f5f9; color: #475569; }
.action-buttons { display: inline-flex; gap: 0.4rem; }
.action-buttons .btn-sm { padding: 0.25rem 0.6rem; }

.modal-backdrop { z-index: 1040; }
.modal { z-index: 1050; }
</style>
