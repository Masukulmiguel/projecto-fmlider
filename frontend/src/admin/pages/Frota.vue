<template>
  <div class="fleet-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1 fw-bold">Gestão de Frota</h4>
        <small class="text-muted">Gerir equipamentos, contentores e veículos</small>
      </div>
      <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" @click="openCreate">
        <i class="bi bi-plus-lg"></i>
        <span>Novo Item</span>
      </button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" style="width:2rem;height:2rem"></div>
    </div>

    <div v-else-if="items.length === 0" class="empty-state">
      <i class="bi bi-truck"></i>
      <p>Nenhum item de frota registado</p>
      <button class="btn btn-primary btn-sm mt-2" @click="openCreate"><i class="bi bi-plus-lg me-1"></i> Adicionar</button>
    </div>

    <div v-else>
      <div v-for="(catItems, cat) in groupedByCategory" :key="cat" class="section-group mb-4">
        <div class="section-header">
          <div class="section-header-left">
            <span class="section-icon" :style="{ background: catColors[cat] || '#6b7280' }">
              <i :class="catIcons[cat] || 'bi bi-truck'"></i>
            </span>
            <div>
              <h6 class="mb-0 fw-semibold">{{ catLabels[cat] || cat }}</h6>
              <small class="text-muted">{{ catItems.length }} item{{ catItems.length !== 1 ? 'ens' : '' }}</small>
            </div>
          </div>
        </div>

        <div class="section-body">
          <div class="fleet-row" v-for="(item, idx) in catItems" :key="item.id">
            <div class="fleet-row-left">
              <div class="fleet-order">{{ idx + 1 }}</div>
              <div class="fleet-thumb" :style="{ backgroundImage: item.image ? `url(${item.image})` : 'none' }">
                <i v-if="!item.image" class="bi bi-image text-muted"></i>
              </div>
              <div class="fleet-info">
                <div class="fleet-title">{{ item.title }}</div>
                <div class="fleet-desc">{{ item.description || 'Sem descrição' }}</div>
                <div class="fleet-specs">
                  <span v-for="(spec, i) in (item.specs || []).slice(0, 3)" :key="i" class="spec-tag">
                    {{ spec.label }}: {{ spec.value }}
                  </span>
                  <span v-if="(item.specs || []).length > 3" class="spec-tag more">+{{ item.specs.length - 3 }}</span>
                </div>
              </div>
            </div>
            <div class="fleet-row-right">
              <span class="status-dot" :class="item.is_active ? 'active' : 'inactive'"></span>
              <div class="action-btns">
                <button class="action-btn edit" title="Editar" @click="openEdit(item)">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="action-btn delete" title="Eliminar" @click="openDelete(item)">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" ref="formModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold">{{ editingItem ? 'Editar Item' : 'Novo Item de Frota' }}</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body pt-2">
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label fw-medium small">Título <span class="text-danger">*</span></label>
                <input v-model="form.title" type="text" class="form-control form-control-sm" placeholder="Ex: Camião Tanque 30.000L">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-medium small">Categoria</label>
                <select v-model="form.category" class="form-select form-select-sm">
                  <option value="trucks">Camiões</option>
                  <option value="containers">Contentores</option>
                  <option value="equipment">Equipamentos</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-medium small">Descrição</label>
                <textarea v-model="form.description" class="form-control form-control-sm" rows="2" placeholder="Descrição curta"></textarea>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-medium small">Rótulo da Categoria</label>
                <input v-model="form.category_label" type="text" class="form-control form-control-sm" placeholder="Ex: Camião">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-medium small">Ordem</label>
                <input v-model.number="form.order_by" type="number" class="form-control form-control-sm" min="0">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-medium small">Estado</label>
                <select v-model="form.is_active" class="form-select form-select-sm">
                  <option :value="true">Ativo</option>
                  <option :value="false">Inativo</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-medium small">Imagem</label>
                <div class="upload-zone" @click="$refs.fileInput.click()" @dragover.prevent @drop.prevent="onDrop">
                  <input type="file" ref="fileInput" accept="image/*" class="d-none" @change="onFileChange">
                  <div v-if="imagePreview" class="upload-preview">
                    <img :src="imagePreview" alt="Preview" />
                    <button class="upload-remove" @click.stop="removeImage"><i class="bi bi-x-lg"></i></button>
                  </div>
                  <div v-else class="upload-placeholder">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p>Clique ou arraste uma imagem</p>
                    <small>JPG, PNG, WebP (máx. 2MB)</small>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label fw-medium small">Especificações</label>
                <div v-for="(spec, i) in form.specs" :key="i" class="d-flex gap-2 mb-2 align-items-center">
                  <input v-model="spec.label" type="text" class="form-control form-control-sm" placeholder="Ex: Capacidade" style="max-width:160px">
                  <input v-model="spec.value" type="text" class="form-control form-control-sm" placeholder="Ex: 45t">
                  <button class="action-btn delete flex-shrink-0" @click="removeSpec(i)"><i class="bi bi-x-lg"></i></button>
                </div>
                <button class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" @click="addSpec">
                  <i class="bi bi-plus"></i> Adicionar
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2" :disabled="saving || !form.title.trim()" @click="save">
              <span v-if="saving" class="spinner-border spinner-border-sm"></span>
              <i v-else class="bi bi-check-lg"></i>
              {{ editingItem ? 'Guardar' : 'Criar' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" ref="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-body text-center py-4">
            <div class="delete-icon mb-3"><i class="bi bi-trash3"></i></div>
            <h6 class="fw-bold mb-2">Eliminar item?</h6>
            <p class="text-muted small mb-0">{{ deleteItem?.title }}</p>
          </div>
          <div class="modal-footer border-0 justify-content-center pt-0 pb-3">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger btn-sm d-flex align-items-center gap-2" :disabled="deleting" @click="confirmDelete">
              <span v-if="deleting" class="spinner-border spinner-border-sm"></span>
              <i v-else class="bi bi-trash3"></i>
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { supabase } from '@/lib/supabase'
import { Modal } from 'bootstrap'
import { useToast } from '@/composables/useToast'

const toast = useToast()

const items = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const editingItem = ref(null)
const deleteItem = ref(null)
const imageFile = ref(null)
const imagePreview = ref('')

const formModal = ref(null)
const deleteModal = ref(null)
const fileInput = ref(null)

let formModalInstance = null
let deleteModalInstance = null

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

const catLabels = { trucks: 'Camiões', containers: 'Contentores', equipment: 'Equipamentos' }
const catIcons = { trucks: 'bi bi-truck', containers: 'bi bi-box-seam', equipment: 'bi bi-tools' }
const catColors = { trucks: '#3b82f6', containers: '#06b6d4', equipment: '#f59e0b' }

const groupedByCategory = computed(() => {
  const groups = {}
  const sorted = [...items.value].sort((a, b) => (a.order_by || 0) - (b.order_by || 0))
  sorted.forEach(item => {
    const cat = item.category || 'other'
    if (!groups[cat]) groups[cat] = []
    groups[cat].push(item)
  })
  return groups
})

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

function openCreate() {
  editingItem.value = null
  resetForm()
  formModalInstance.show()
}

function openEdit(item) {
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
  formModalInstance.show()
}

function addSpec() { form.specs.push({ label: '', value: '' }) }
function removeSpec(i) { form.specs.splice(i, 1) }

function onFileChange(e) { const f = e.target.files?.[0]; if (f) handleImage(f) }
function onDrop(e) { const f = e.dataTransfer.files?.[0]; if (f) handleImage(f) }
function handleImage(file) {
  if (!file.type.startsWith('image/')) return
  if (file.size > 2 * 1024 * 1024) { toast.error('Imagem muito grande. Máximo 2MB.'); return }
  imageFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}
function removeImage() { imageFile.value = null; imagePreview.value = ''; form.image = '' }

function fileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result)
    reader.onerror = reject
    reader.readAsDataURL(file)
  })
}

async function save() {
  if (!form.title.trim()) return
  saving.value = true
  try {
    let imageUrl = form.image
    if (imageFile.value) {
      imageUrl = await fileToBase64(imageFile.value)
    }
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
      toast.success('Item atualizado!')
    } else {
      const { error } = await supabase.from('fleet_items').insert(payload)
      if (error) throw error
      toast.success('Item criado!')
    }
    formModalInstance.hide()
    await fetchData()
  } catch (e) {
    toast.error(e.message || 'Erro ao guardar.')
  } finally { saving.value = false }
}

function openDelete(item) { deleteItem.value = item; deleteModalInstance.show() }

async function confirmDelete() {
  if (!deleteItem.value) return
  deleting.value = true
  try {
    const { error } = await supabase.from('fleet_items').delete().eq('id', deleteItem.value.id)
    if (error) throw error
    toast.success('Item eliminado!')
    deleteModalInstance.hide()
    await fetchData()
  } catch (e) { toast.error('Erro ao eliminar.') }
  finally { deleting.value = false }
}

async function fetchData() {
  loading.value = true
  try {
    const { data, error } = await supabase.from('fleet_items').select('*').order('order_by', { ascending: true })
    if (error) throw error
    items.value = data || []
  } catch (e) { console.error(e) }
  finally { loading.value = false }
}

onMounted(() => {
  formModalInstance = new Modal(formModal.value)
  deleteModalInstance = new Modal(deleteModal.value)
  fetchData()
})

onBeforeUnmount(() => {
  formModalInstance?.dispose()
  deleteModalInstance?.dispose()
})
</script>

<style scoped>
.fleet-page { padding: 1.5rem; }

.section-group {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.section-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
}

.section-header-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.section-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 0.95rem;
  flex-shrink: 0;
}

.section-body { padding: 0.25rem 0; }

.fleet-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1.25rem;
  transition: background 0.15s;
}

.fleet-row:hover { background: #f8fafc; }

.fleet-row-left {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  min-width: 0;
  flex: 1;
}

.fleet-order {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  background: #f1f5f9;
  color: #64748b;
  font-size: 0.75rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.fleet-thumb {
  width: 56px;
  height: 56px;
  border-radius: 10px;
  background: #f1f5f9;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 1px solid #e2e8f0;
}

.fleet-info { min-width: 0; flex: 1; }

.fleet-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
}

.fleet-desc {
  font-size: 0.75rem;
  color: #94a3b8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 400px;
}

.fleet-specs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  margin-top: 0.35rem;
}

.spec-tag {
  font-size: 0.675rem;
  padding: 0.15rem 0.5rem;
  border-radius: 6px;
  background: #f1f5f9;
  color: #475569;
  font-weight: 500;
}

.spec-tag.more {
  background: #e2e8f0;
  color: #64748b;
}

.fleet-row-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-shrink: 0;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.status-dot.active {
  background: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
}

.status-dot.inactive {
  background: #cbd5e1;
}

.action-btns {
  display: flex;
  gap: 0.25rem;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
  background: transparent;
  font-size: 0.85rem;
}

.action-btn.edit { color: #3b82f6; }
.action-btn.edit:hover { background: #eff6ff; color: #2563eb; }
.action-btn.delete { color: #ef4444; }
.action-btn.delete:hover { background: #fef2f2; color: #dc2626; }

.upload-zone {
  border: 2px dashed #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.upload-zone:hover { border-color: #3b82f6; background: #f8fafc; }

.upload-placeholder { color: #94a3b8; }
.upload-placeholder i { font-size: 2rem; margin-bottom: 0.5rem; display: block; }
.upload-placeholder p { margin-bottom: 0.25rem; font-size: 0.875rem; }
.upload-placeholder small { font-size: 0.75rem; }

.upload-preview { position: relative; display: inline-block; }
.upload-preview img { max-width: 100%; max-height: 200px; border-radius: 8px; }
.upload-remove {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: none;
  background: #ef4444;
  color: #fff;
  font-size: 0.65rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.delete-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #fef2f2;
  color: #ef4444;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin: 0 auto;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #94a3b8;
}

.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
</style>
