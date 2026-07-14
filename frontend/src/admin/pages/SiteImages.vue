<template>
  <div class="site-images-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1 fw-bold">Imagens do Site</h4>
        <small class="text-muted">Gerir imagens de todas as secções do site</small>
      </div>
      <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" @click="openCreateModal">
        <i class="bi bi-plus-lg"></i>
        <span>Nova Imagem</span>
      </button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" style="width:2rem;height:2rem"></div>
    </div>

    <div v-else-if="images.length === 0" class="empty-state">
      <i class="bi bi-image-alt"></i>
      <p>Nenhuma imagem encontrada</p>
    </div>

    <div v-else>
      <div v-for="(items, section) in groupedImages" :key="section" class="section-group mb-4">
        <div class="section-header">
          <div class="section-header-left">
            <span class="section-icon" :style="{ background: sectionColors[section] || '#6b7280' }">
              <i :class="sectionIcons[section] || 'bi bi-image'"></i>
            </span>
            <div>
              <h6 class="mb-0 fw-semibold">{{ sectionLabels[section] || section }}</h6>
              <small class="text-muted">{{ items.length }} imagem{{ items.length !== 1 ? 'ens' : '' }}</small>
            </div>
          </div>
        </div>

        <div class="section-body">
          <div class="image-row" v-for="img in items" :key="img.id">
            <div class="image-row-left">
              <div class="image-thumb" :style="{ backgroundImage: img.image_url ? `url(${img.image_url})` : 'none' }">
                <i v-if="!img.image_url" class="bi bi-image text-muted"></i>
              </div>
              <div class="image-info">
                <div class="image-key">{{ img.key }}</div>
                <div class="image-alt">{{ img.alt_text || 'Sem descrição' }}</div>
              </div>
            </div>
            <div class="image-row-right">
              <span class="status-dot" :class="img.status ? 'active' : 'inactive'"></span>
              <div class="action-btns">
                <button class="action-btn edit" title="Editar" @click="openEditModal(img)">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="action-btn upload" title="Trocar imagem" @click="quickUpload(img)">
                  <i class="bi bi-camera-fill"></i>
                </button>
                <button class="action-btn delete" title="Eliminar" @click="confirmDelete(img)">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" ref="formModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header border-0 pb-0">
            <h6 class="modal-title fw-bold">{{ editing ? 'Editar Imagem' : 'Nova Imagem' }}</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body pt-2">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-medium small">Secção</label>
                <select v-model="form.section" class="form-select form-select-sm">
                  <option value="" disabled>Selecionar...</option>
                  <option v-for="(label, key) in sectionLabels" :key="key" :value="key">{{ label }}</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-medium small">Chave</label>
                <input v-model="form.key" type="text" class="form-control form-control-sm" placeholder="ex: hero_bg, logo" />
              </div>
              <div class="col-12">
                <label class="form-label fw-medium small">Imagem</label>
                <div class="upload-zone" @click="$refs.imageInput.click()" @dragover.prevent @drop.prevent="handleDrop">
                  <input ref="imageInput" type="file" accept="image/*" class="d-none" @change="handleImageChange" />
                  <div v-if="imagePreview || form.image_url" class="upload-preview">
                    <img :src="imagePreview || form.image_url" alt="Preview" />
                    <button class="upload-remove" @click.stop="clearImage"><i class="bi bi-x-lg"></i></button>
                  </div>
                  <div v-else class="upload-placeholder">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <p>Clique ou arraste uma imagem</p>
                    <small>PNG, JPG, WebP (máx. 2MB)</small>
                  </div>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label fw-medium small">URL alternativa</label>
                <input v-model="form.image_url" type="text" class="form-control form-control-sm" placeholder="https://... ou /assets/img/..." />
              </div>
              <div class="col-md-8">
                <label class="form-label fw-medium small">Texto alternativo</label>
                <input v-model="form.alt_text" type="text" class="form-control form-control-sm" placeholder="Descrição da imagem" />
              </div>
              <div class="col-md-4 d-flex align-items-end">
                <div class="form-check form-switch">
                  <input v-model="form.status" class="form-check-input" type="checkbox" id="imgStatus" />
                  <label class="form-check-label small" for="imgStatus">{{ form.status ? 'Ativo' : 'Inativo' }}</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-2" :disabled="submitting || !form.section || !form.key.trim()" @click="submitForm">
              <span v-if="submitting" class="spinner-border spinner-border-sm"></span>
              <i v-else class="bi bi-check-lg"></i>
              {{ editing ? 'Guardar' : 'Criar' }}
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
            <h6 class="fw-bold mb-2">Eliminar imagem?</h6>
            <p class="text-muted small mb-0">{{ imageToDelete?.section }} / {{ imageToDelete?.key }}</p>
          </div>
          <div class="modal-footer border-0 justify-content-center pt-0 pb-3">
            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger btn-sm d-flex align-items-center gap-2" :disabled="deleting" @click="deleteImage">
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
import { useI18n } from '@/composables/useI18n'
import { useToast } from '@/composables/useToast'

const { t } = useI18n()
const toast = useToast()

const images = ref([])
const loading = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const editing = ref(false)
const editingId = ref(null)
const imageToDelete = ref(null)
const imageFile = ref(null)
const imagePreview = ref(null)

const formModal = ref(null)
const deleteModal = ref(null)
const imageInput = ref(null)

let formModalInstance = null
let deleteModalInstance = null

const sectionLabels = computed(() => ({
  header: 'Cabeçalho',
  footer: 'Rodapé',
  sidebar: 'Barras Laterais',
  home: 'Página Inicial',
  about: 'Sobre Nós',
  fleet: 'Frota',
  services: 'Serviços',
  news: 'Notícias',
  gallery: 'Galeria',
  auth: 'Autenticação',
  service_detail: 'Detalhe do Serviço',
}))

const sectionIcons = {
  header: 'bi bi-browser-chrome',
  footer: 'bi bi-menu-button-wide',
  sidebar: 'bi bi-layout-sidebar',
  home: 'bi bi-house-door',
  about: 'bi bi-info-circle',
  fleet: 'bi bi-truck',
  services: 'bi bi-briefcase',
  news: 'bi bi-newspaper',
  gallery: 'bi bi-images',
  auth: 'bi bi-shield-lock',
  service_detail: 'bi bi-file-earmark-text',
}

const sectionColors = {
  header: '#3b82f6',
  footer: '#6366f1',
  sidebar: '#8b5cf6',
  home: '#10b981',
  about: '#f59e0b',
  fleet: '#ef4444',
  services: '#06b6d4',
  news: '#ec4899',
  gallery: '#14b8a6',
  auth: '#f97316',
  service_detail: '#64748b',
}

const form = reactive({
  section: '',
  key: '',
  image_url: '',
  alt_text: '',
  status: true,
})

const groupedImages = computed(() => {
  const order = ['header', 'footer', 'home', 'about', 'services', 'fleet', 'news', 'gallery', 'auth', 'service_detail', 'sidebar']
  const groups = {}
  images.value.forEach(img => {
    if (!groups[img.section]) groups[img.section] = []
    groups[img.section].push(img)
  })
  const sorted = {}
  order.forEach(s => { if (groups[s]) sorted[s] = groups[s] })
  Object.keys(groups).forEach(s => { if (!sorted[s]) sorted[s] = groups[s] })
  return sorted
})

async function fetchImages() {
  loading.value = true
  try {
    const { data, error } = await supabase.from('site_images').select('*').order('section').order('key')
    if (!error) images.value = data || []
  } catch (err) {
    console.error('Erro ao buscar imagens:', err)
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form.section = ''
  form.key = ''
  form.image_url = ''
  form.alt_text = ''
  form.status = true
  imageFile.value = null
  imagePreview.value = null
  editing.value = false
  editingId.value = null
}

function openCreateModal() {
  resetForm()
  formModalInstance.show()
}

function openEditModal(img) {
  editing.value = true
  editingId.value = img.id
  form.section = img.section || ''
  form.key = img.key || ''
  form.image_url = img.image_url || ''
  form.alt_text = img.alt_text || ''
  form.status = img.status ?? true
  imageFile.value = null
  imagePreview.value = null
  formModalInstance.show()
}

function quickUpload(img) {
  editing.value = true
  editingId.value = img.id
  form.section = img.section
  form.key = img.key
  form.image_url = img.image_url || ''
  form.alt_text = img.alt_text || ''
  form.status = img.status ?? true
  imageFile.value = null
  imagePreview.value = null
  setTimeout(() => imageInput.value?.click(), 100)
}

function handleImageChange(event) {
  const file = event.target.files?.[0]
  if (!file) return
  if (file.size > 2 * 1024 * 1024) {
    toast.error('Imagem muito grande. Máximo 2MB.')
    return
  }
  imageFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}

function handleDrop(event) {
  const file = event.dataTransfer.files?.[0]
  if (!file || !file.type.startsWith('image/')) return
  if (file.size > 2 * 1024 * 1024) {
    toast.error('Imagem muito grande. Máximo 2MB.')
    return
  }
  imageFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}

function clearImage() {
  imageFile.value = null
  imagePreview.value = null
  form.image_url = ''
}

function fileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result)
    reader.onerror = reject
    reader.readAsDataURL(file)
  })
}

async function submitForm() {
  if (!form.section || !form.key.trim()) {
    toast.warning('Secção e chave são obrigatórias')
    return
  }
  submitting.value = true
  try {
    let imageUrl = form.image_url
    if (imageFile.value) {
      imageUrl = await fileToBase64(imageFile.value)
    }
    const payload = {
      section: form.section,
      key: form.key.trim(),
      image_url: imageUrl,
      alt_text: form.alt_text,
      status: form.status ? 1 : 0,
    }
    if (editing.value) {
      const { error } = await supabase.from('site_images').update(payload).eq('id', editingId.value)
      if (error) throw error
    } else {
      const { error } = await supabase.from('site_images').upsert(payload, { onConflict: 'section,key' })
      if (error) throw error
    }
    formModalInstance.hide()
    toast.success(editing.value ? 'Imagem atualizada!' : 'Imagem criada!')
    await fetchImages()
  } catch (err) {
    console.error('Erro ao salvar:', err)
    toast.error('Erro ao guardar: ' + (err?.message || 'Erro desconhecido'))
  } finally {
    submitting.value = false
  }
}

function confirmDelete(img) {
  imageToDelete.value = img
  deleteModalInstance.show()
}

async function deleteImage() {
  if (!imageToDelete.value) return
  deleting.value = true
  try {
    const { error } = await supabase.from('site_images').delete().eq('id', imageToDelete.value.id)
    if (error) throw error
    deleteModalInstance.hide()
    toast.success('Imagem eliminada!')
    await fetchImages()
  } catch (err) {
    console.error('Erro ao deletar:', err)
    toast.error('Erro ao eliminar')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  formModalInstance = new Modal(formModal.value)
  deleteModalInstance = new Modal(deleteModal.value)
  fetchImages()
})

onBeforeUnmount(() => {
  formModalInstance?.dispose()
  deleteModalInstance?.dispose()
})
</script>

<style scoped>
.site-images-page {
  padding: 1.5rem;
}

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

.section-body {
  padding: 0.25rem 0;
}

.image-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1.25rem;
  transition: background 0.15s;
}

.image-row:hover {
  background: #f8fafc;
}

.image-row-left {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  min-width: 0;
}

.image-thumb {
  width: 48px;
  height: 48px;
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

.image-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 10px;
}

.image-info {
  min-width: 0;
}

.image-key {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.image-alt {
  font-size: 0.75rem;
  color: #94a3b8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.image-row-right {
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

.action-btn.edit {
  color: #3b82f6;
}

.action-btn.edit:hover {
  background: #eff6ff;
  color: #2563eb;
}

.action-btn.upload {
  color: #8b5cf6;
}

.action-btn.upload:hover {
  background: #f5f3ff;
  color: #7c3aed;
}

.action-btn.delete {
  color: #ef4444;
}

.action-btn.delete:hover {
  background: #fef2f2;
  color: #dc2626;
}

/* Upload Zone */
.upload-zone {
  border: 2px dashed #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
}

.upload-zone:hover {
  border-color: #3b82f6;
  background: #f8fafc;
}

.upload-placeholder {
  color: #94a3b8;
}

.upload-placeholder i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  display: block;
}

.upload-placeholder p {
  margin-bottom: 0.25rem;
  font-size: 0.875rem;
}

.upload-placeholder small {
  font-size: 0.75rem;
}

.upload-preview {
  position: relative;
  display: inline-block;
}

.upload-preview img {
  max-width: 100%;
  max-height: 200px;
  border-radius: 8px;
}

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

/* Delete Modal */
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

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  display: block;
}
</style>
