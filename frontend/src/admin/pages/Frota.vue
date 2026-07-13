<template>
  <div class="admin-page p-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
      <div>
        <h2>Gestão de Frota</h2>
        <p class="text-muted mb-0">Gerir equipamentos, contentores e veículos exibidos na página /frota</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">
        <i class="bi bi-plus-lg me-1"></i> Novo Item
      </button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div v-else-if="items.length === 0" class="text-center py-5 text-muted">
      <i class="bi bi-truck" style="font-size: 3rem; opacity: 0.3;"></i>
      <p class="mt-3">Nenhum item de frota registado.</p>
      <button class="btn btn-primary" @click="openCreate"><i class="bi bi-plus-lg me-1"></i> Adicionar Item</button>
    </div>

    <div v-else class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th style="width:50px">Ordem</th>
            <th style="width:80px">Imagem</th>
            <th>Título</th>
            <th>Categoria</th>
            <th>Specs</th>
            <th>Estado</th>
            <th style="width:120px">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in sortedItems" :key="item.id">
            <td><span class="badge bg-secondary">{{ item.order_by || 0 }}</span></td>
            <td>
              <div class="item-thumb">
                <img v-if="item.image" :src="item.image" :alt="item.title">
                <i v-else class="bi bi-image text-muted"></i>
              </div>
            </td>
            <td>
              <div class="fw-medium">{{ item.title }}</div>
              <small class="text-muted text-truncate d-inline-block" style="max-width:280px">{{ item.description || '' }}</small>
            </td>
            <td><span class="badge" :class="catBadge(item.category)">{{ item.category_label || item.category }}</span></td>
            <td>
              <span v-for="(spec, i) in (item.specs || []).slice(0, 2)" :key="i" class="badge bg-light text-dark me-1 mb-1">
                {{ spec.label }}: {{ spec.value }}
              </span>
              <span v-if="(item.specs || []).length > 2" class="badge bg-light text-muted">+{{ item.specs.length - 2 }}</span>
            </td>
            <td>
              <span class="status-badge" :class="item.is_active ? 'status-active' : 'status-inactive'">
                {{ item.is_active ? 'Ativo' : 'Inativo' }}
              </span>
            </td>
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

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content modal-lg">
        <div class="modal-header">
          <h5>{{ editingItem ? 'Editar Item' : 'Novo Item de Frota' }}</h5>
          <button class="btn-close" @click="closeModal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Título <span class="text-danger">*</span></label>
              <input v-model="form.title" type="text" class="form-control" placeholder="Ex: Camião Tanque 30.000L">
            </div>
            <div class="col-md-4">
              <label class="form-label">Categoria <span class="text-danger">*</span></label>
              <select v-model="form.category" class="form-select">
                <option value="trucks">Camiões</option>
                <option value="containers">Contentores</option>
                <option value="equipment">Equipamentos</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Descrição</label>
              <textarea v-model="form.description" class="form-control" rows="2" placeholder="Descrição curta do item"></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label">Rótulo da Categoria</label>
              <input v-model="form.category_label" type="text" class="form-control" placeholder="Ex: Camião">
            </div>
            <div class="col-md-4">
              <label class="form-label">Ordem</label>
              <input v-model.number="form.order_by" type="number" class="form-control" min="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Estado</label>
              <select v-model="form.is_active" class="form-select">
                <option :value="true">Ativo</option>
                <option :value="false">Inativo</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Imagem</label>
              <div class="image-upload-zone" @click="triggerFileInput" @dragover.prevent @drop.prevent="onDrop">
                <input type="file" ref="fileInput" accept="image/*" style="display:none" @change="onFileChange">
                <div v-if="imagePreview" class="image-preview-container">
                  <img :src="imagePreview" alt="Preview" class="image-preview">
                  <button class="btn-remove-image" @click.stop="removeImage"><i class="bi bi-x-lg"></i></button>
                </div>
                <div v-else class="upload-placeholder">
                  <i class="bi bi-cloud-arrow-up-fill" style="font-size:2rem; color:#94a3b8"></i>
                  <p class="mb-0 mt-1 small">Clique ou arraste uma imagem</p>
                  <small class="text-muted">JPG, PNG, WebP</small>
                </div>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Especificações</label>
              <div v-for="(spec, i) in form.specs" :key="i" class="d-flex gap-2 mb-2 align-items-center">
                <input v-model="spec.label" type="text" class="form-control form-control-sm" placeholder="Ex: Capacidade" style="max-width:160px">
                <input v-model="spec.value" type="text" class="form-control form-control-sm" placeholder="Ex: 45t">
                <button class="btn btn-sm btn-outline-danger" @click="removeSpec(i)"><i class="bi bi-x"></i></button>
              </div>
              <button class="btn btn-sm btn-outline-primary" @click="addSpec"><i class="bi bi-plus me-1"></i> Adicionar Spec</button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeModal">Cancelar</button>
          <button class="btn btn-primary" @click="save" :disabled="saving || !form.title.trim()">
            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
            {{ editingItem ? 'Guardar' : 'Criar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="closeDelete">
      <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
          <h5>Eliminar Item</h5>
          <button class="btn-close" @click="closeDelete"></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja eliminar <strong>{{ deleteItem?.title }}</strong>?</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeDelete">Cancelar</button>
          <button class="btn btn-danger" @click="confirmDelete" :disabled="deleting">
            <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
            Eliminar
          </button>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toast.show" class="toast-notification" :class="'toast-' + toast.type">
      <i :class="toast.type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill'" class="me-2"></i>
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'

const items = ref([])
const loading = ref(false)
const showModal = ref(false)
const editingItem = ref(null)
const saving = ref(false)
const fileInput = ref(null)
const imageFile = ref(null)
const imagePreview = ref('')
const showDeleteModal = ref(false)
const deleteItem = ref(null)
const deleting = ref(false)

const form = reactive({
  title: '',
  category: 'trucks',
  description: '',
  image: '',
  category_label: '',
  order_by: 0,
  is_active: true,
  specs: []
})

const sortedItems = computed(() => [...items.value].sort((a, b) => (a.order_by || 0) - (b.order_by || 0)))

const catBadge = (c) => ({
  trucks: 'bg-primary',
  containers: 'bg-info',
  equipment: 'bg-warning text-dark'
}[c] || 'bg-secondary')

const resetForm = () => {
  form.title = ''
  form.category = 'trucks'
  form.description = ''
  form.image = ''
  form.category_label = ''
  form.order_by = 0
  form.is_active = true
  form.specs = []
  imageFile.value = null
  imagePreview.value = ''
}

const openCreate = () => { editingItem.value = null; resetForm(); showModal.value = true }
const openEdit = (item) => {
  editingItem.value = item
  form.title = item.title
  form.category = item.category
  form.description = item.description || ''
  form.image = item.image || ''
  form.category_label = item.category_label || ''
  form.order_by = item.order_by || 0
  form.is_active = item.is_active !== false
  form.specs = JSON.parse(JSON.stringify(item.specs || []))
  imageFile.value = null
  imagePreview.value = item.image || ''
  showModal.value = true
}
const closeModal = () => { showModal.value = false; editingItem.value = null }

const addSpec = () => form.specs.push({ label: '', value: '' })
const removeSpec = (i) => form.specs.splice(i, 1)

const triggerFileInput = () => fileInput.value?.click()
const onFileChange = (e) => { const f = e.target.files[0]; if (f) handleImage(f) }
const onDrop = (e) => { const f = e.dataTransfer.files[0]; if (f) handleImage(f) }
const handleImage = (file) => {
  if (!file.type.startsWith('image/')) return
  imageFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}
const removeImage = () => { imageFile.value = null; imagePreview.value = ''; form.image = '' }

const uploadImage = async () => {
  if (!imageFile.value) return form.image
  const file = imageFile.value
  const fileExt = file.name.split('.').pop()
  const fileName = `fleet/${Date.now()}.${fileExt}`
  const { error: uploadError } = await supabase.storage.from('site-images').upload(fileName, file)
  if (uploadError) throw uploadError
  const { data } = supabase.storage.from('site-images').getPublicUrl(fileName)
  return data.publicUrl
}

const save = async () => {
  if (!form.title.trim()) return
  saving.value = true
  try {
    const imageUrl = await uploadImage()
    const payload = {
      title: form.title.trim(),
      category: form.category,
      description: form.description.trim() || null,
      image: imageUrl || null,
      category_label: form.category_label.trim() || null,
      order_by: form.order_by || 0,
      is_active: form.is_active,
      specs: form.specs.filter(s => s.label && s.value),
      updated_at: new Date().toISOString()
    }
    if (editingItem.value) {
      const { error } = await supabase.from('fleet_items').update(payload).eq('id', editingItem.value.id)
      if (error) throw error
      showToast('success', 'Item atualizado!')
    } else {
      const { error } = await supabase.from('fleet_items').insert(payload)
      if (error) throw error
      showToast('success', 'Item criado!')
    }
    closeModal()
    await fetchData()
  } catch (e) { showToast('error', e.message || 'Erro ao guardar.') }
  finally { saving.value = false }
}

const openDelete = (item) => { deleteItem.value = item; showDeleteModal.value = true }
const closeDelete = () => { showDeleteModal.value = false; deleteItem.value = null }
const confirmDelete = async () => {
  deleting.value = true
  try {
    const { error } = await supabase.from('fleet_items').delete().eq('id', deleteItem.value.id)
    if (error) throw error
    showToast('success', 'Item eliminado!')
    closeDelete()
    await fetchData()
  } catch (e) { showToast('error', 'Erro ao eliminar.') }
  finally { deleting.value = false }
}

const fetchData = async () => {
  loading.value = true
  try {
    const { data, error } = await supabase.from('fleet_items').select('*').order('order_by', { ascending: true })
    if (error) throw error
    items.value = data || []
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

const toast = reactive({ show: false, type: 'success', message: '' })
let toastTimer = null
const showToast = (type, message) => { toast.type = type; toast.message = message; toast.show = true; clearTimeout(toastTimer); toastTimer = setTimeout(() => { toast.show = false }, 4000) }

onMounted(fetchData)
</script>

<style scoped>
.admin-page { background: #f8f9fa; min-height: 100vh; }
.item-thumb { width: 60px; height: 45px; border-radius: 8px; overflow: hidden; background: #f1f5f9; display: flex; align-items: center; justify-content: center; }
.item-thumb img { width: 100%; height: 100%; object-fit: cover; }
.status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.72rem; font-weight: 600; }
.status-active { background: #d1fae5; color: #065f46; }
.status-inactive { background: #fee2e2; color: #991b1b; }
.action-buttons { display: flex; gap: 0.35rem; }
.btn-icon { width: 32px; height: 32px; border: none; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-edit { background: #eff6ff; color: #2563eb; }
.btn-edit:hover { background: #dbeafe; color: #1d4ed8; }
.btn-delete { background: #fef2f2; color: #dc2626; }
.btn-delete:hover { background: #fee2e2; color: #b91c1c; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1050; }
.modal-content { background: white; border-radius: 12px; width: 100%; max-width: 520px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
.modal-content.modal-lg { max-width: 720px; }
.modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
.modal-header h5 { margin: 0; font-weight: 600; }
.modal-body { padding: 1.5rem; overflow-y: auto; flex: 1; min-height: 0; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem; }
.image-upload-zone { border: 2px dashed #d1d5db; border-radius: 10px; padding: 1.5rem; text-align: center; cursor: pointer; transition: border-color 0.2s; position: relative; }
.image-upload-zone:hover { border-color: #2563eb; }
.upload-placeholder { color: #64748b; }
.image-preview-container { position: relative; display: inline-block; }
.image-preview { max-width: 100%; max-height: 200px; border-radius: 8px; object-fit: contain; }
.btn-remove-image { position: absolute; top: -8px; right: -8px; width: 24px; height: 24px; border-radius: 50%; border: none; background: #dc2626; color: white; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.toast-notification { position: fixed; top: 20px; right: 20px; z-index: 3000; padding: 12px 20px; border-radius: 8px; color: #fff; font-size: 0.9rem; font-weight: 500; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: slideIn 0.3s ease; }
.toast-success { background: #059669; }
.toast-error { background: #dc2626; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>
