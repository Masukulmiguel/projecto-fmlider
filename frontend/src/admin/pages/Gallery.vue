<template>
  <div class="admin-page p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Galeria</h2>
      <button class="btn btn-primary" @click="openAddModal">
        <i class="bi bi-plus-lg me-1"></i> Adicionar Imagem
      </button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else-if="gallery.length === 0" class="text-center py-5 text-muted">
      <i class="bi bi-image" style="font-size:3rem"></i>
      <p class="mt-2">Nenhuma imagem na galeria.</p>
    </div>

    <div v-else class="gallery-grid">
      <div class="card gallery-item" v-for="item in sortedGallery" :key="item.id">
        <div class="gallery-img-wrapper" @click="openLightbox(item)">
          <img :src="getImageUrl(item.image)" :alt="item.alt_text || item.title" />
          <div class="gallery-overlay">
            <i class="bi bi-arrows-fullscreen"></i>
          </div>
        </div>
        <div class="card-body p-2">
          <h6 class="card-title mb-1 text-truncate">{{ item.title }}</h6>
          <span class="badge bg-secondary mb-1">{{ item.category || 'Sem categoria' }}</span>
          <p class="card-text small text-muted mb-1 text-truncate-2">{{ item.description }}</p>
          <small class="text-muted">Ordem: {{ item.order_by || 0 }}</small>
        </div>
        <div class="card-footer bg-transparent border-0 p-2 pt-0">
          <button class="btn btn-sm btn-outline-danger w-100" @click="confirmDelete(item)">
            <i class="bi bi-trash me-1"></i> Deletar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Adicionar/Editar -->
    <div class="modal fade" id="galleryModal" tabindex="-1" ref="galleryModalRef">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingId ? 'Editar Imagem' : 'Adicionar Imagem' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveItem">
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label">Título *</label>
                  <input type="text" class="form-control" v-model="form.title" required />
                </div>
                <div class="col-md-4">
                  <label class="form-label">Categoria</label>
                  <input type="text" class="form-control" v-model="form.category" list="categoryList" placeholder="Ex: Produtos" />
                  <datalist id="categoryList">
                    <option v-for="cat in categories" :key="cat" :value="cat"></option>
                  </datalist>
                </div>
                <div class="col-12">
                  <label class="form-label">Descrição</label>
                  <textarea class="form-control" v-model="form.description" rows="2"></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Texto Alt</label>
                  <input type="text" class="form-control" v-model="form.alt_text" placeholder="Descrição para acessibilidade" />
                </div>
                <div class="col-md-6">
                  <label class="form-label">Ordem</label>
                  <input type="number" class="form-control" v-model.number="form.order_by" min="0" />
                </div>
                <div class="col-12">
                  <label class="form-label">Imagem *</label>
                  <div
                    class="upload-zone"
                    :class="{ 'dragover': isDragging, 'has-image': previewUrl }"
                    @click="triggerFileInput"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                  >
                    <input
                      type="file"
                      ref="fileInput"
                      accept="image/*"
                      class="d-none"
                      @change="handleFileSelect"
                    />
                    <div v-if="!previewUrl" class="text-center py-4">
                      <i class="bi bi-cloud-arrow-up" style="font-size:2.5rem"></i>
                      <p class="mb-0 mt-2">Arraste uma imagem ou clique para selecionar</p>
                      <small class="text-muted">JPG, PNG, WEBP (máx. 5MB)</small>
                    </div>
                    <div v-else class="text-center">
                      <img :src="previewUrl" class="preview-img" />
                      <p class="mt-2 mb-0 small text-muted">Clique para trocar a imagem</p>
                    </div>
                  </div>
                  <div v-if="!editingId && !selectedFile" class="text-danger small mt-1">
                    Selecione uma imagem para fazer upload.
                  </div>
                </div>
              </div>
              <div class="modal-footer px-0 pb-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" :disabled="saving">
                  <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                  {{ saving ? 'Salvando...' : 'Salvar' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Confirmar Deleção -->
    <div class="modal fade" id="deleteModal" tabindex="-1" ref="deleteModalRef">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmar exclusão</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Tem certeza que deseja deletar <strong>{{ deletingItem?.title }}</strong>?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger btn-sm" @click="deleteItem" :disabled="deleting">
              <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
              {{ deleting ? 'Deletando...' : 'Deletar' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Lightbox -->
    <div v-if="lightboxItem" class="lightbox-overlay" @click.self="closeLightbox">
      <button class="lightbox-close" @click="closeLightbox">&times;</button>
      <img :src="getImageUrl(lightboxItem.image)" :alt="lightboxItem.alt_text || lightboxItem.title" />
      <div class="lightbox-caption text-center">
        <strong>{{ lightboxItem.title }}</strong>
        <span v-if="lightboxItem.description" class="ms-2 text-white-50">{{ lightboxItem.description }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { supabase } from '@/lib/supabase'
import { Modal } from 'bootstrap'

const gallery = ref([])
const loading = ref(true)
const saving = ref(false)
const deleting = ref(false)
const isDragging = ref(false)
const selectedFile = ref(null)
const previewUrl = ref(null)
const editingId = ref(null)
const deletingItem = ref(null)
const lightboxItem = ref(null)

const galleryModalRef = ref(null)
const deleteModalRef = ref(null)
const fileInput = ref(null)

let galleryModal = null
let deleteModal = null

const form = ref({
  title: '',
  category: '',
  description: '',
  alt_text: '',
  order_by: 0
})

const sortedGallery = computed(() => {
  return [...gallery.value].sort((a, b) => (a.order_by || 0) - (b.order_by || 0))
})

const categories = computed(() => {
  const cats = new Set(gallery.value.map(i => i.category).filter(Boolean))
  return [...cats]
})

function getImageUrl(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return path
}

async function fetchGallery() {
  loading.value = true
  try {
    const { data, error } = await supabase.from('gallery').select('*').order('order_by', { ascending: true })
    if (!error) gallery.value = data
  } catch (err) {
    console.error('Erro ao carregar galeria:', err)
  } finally {
    loading.value = false
  }
}

function openAddModal() {
  editingId.value = null
  form.value = { title: '', category: '', description: '', alt_text: '', order_by: 0 }
  selectedFile.value = null
  previewUrl.value = null
  galleryModal.show()
}

function triggerFileInput() {
  fileInput.value?.click()
}

function handleFileSelect(e) {
  const file = e.target.files?.[0]
  if (file) processFile(file)
}

function handleDrop(e) {
  isDragging.value = false
  const file = e.dataTransfer.files?.[0]
  if (file && file.type.startsWith('image/')) processFile(file)
}

function processFile(file) {
  if (file.size > 5 * 1024 * 1024) {
    alert('Arquivo muito grande. Máximo 5MB.')
    return
  }
  selectedFile.value = file
  previewUrl.value = URL.createObjectURL(file)
}

async function uploadImage() {
  const fileExt = selectedFile.value.name.split('.').pop()
  const fileName = `gallery/${Date.now()}.${fileExt}`
  const { data, error } = await supabase.storage.from('uploads').upload(fileName, selectedFile.value)
  if (error) throw error
  const { data: urlData } = supabase.storage.from('uploads').getPublicUrl(fileName)
  return urlData.publicUrl
}

async function saveItem() {
  saving.value = true
  try {
    let imagePath = null
    if (selectedFile.value) {
      imagePath = await uploadImage()
    }
    const payload = {
      title: form.value.title,
      category: form.value.category || null,
      description: form.value.description || null,
      alt_text: form.value.alt_text || null,
      order_by: form.value.order_by || 0,
      image: imagePath
    }
    if (editingId.value) {
      if (!imagePath) delete payload.image
      const { error } = await supabase.from('gallery').update(payload).eq('id', editingId.value)
      if (error) throw error
    } else {
      if (!imagePath) throw new Error('Selecione uma imagem')
      const { error } = await supabase.from('gallery').insert(payload)
      if (error) throw error
    }
    galleryModal.hide()
    await fetchGallery()
  } catch (err) {
    console.error('Erro ao salvar:', err)
    alert(err.message || 'Erro ao salvar item.')
  } finally {
    saving.value = false
  }
}

function confirmDelete(item) {
  deletingItem.value = item
  deleteModal.show()
}

async function deleteItem() {
  if (!deletingItem.value) return
  deleting.value = true
  try {
    const { error } = await supabase.from('gallery').delete().eq('id', deletingItem.value.id)
    if (error) throw error
    deleteModal.hide()
    gallery.value = gallery.value.filter(i => i.id !== deletingItem.value.id)
    deletingItem.value = null
  } catch (err) {
    console.error('Erro ao deletar:', err)
    alert('Erro ao deletar item.')
  } finally {
    deleting.value = false
  }
}

function openLightbox(item) {
  lightboxItem.value = item
}

function closeLightbox() {
  lightboxItem.value = null
}

onMounted(() => {
  galleryModal = new Modal(galleryModalRef.value)
  deleteModal = new Modal(deleteModalRef.value)
  fetchGallery()
})
</script>

<style scoped>
.admin-page {
  background: #f8f9fa;
  min-height: 100vh;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.25rem;
}

.gallery-item {
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s, box-shadow 0.2s;
}
.gallery-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.gallery-img-wrapper {
  position: relative;
  overflow: hidden;
  cursor: pointer;
}
.gallery-img-wrapper img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  display: block;
}
.gallery-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s;
}
.gallery-img-wrapper:hover .gallery-overlay {
  opacity: 1;
}
.gallery-overlay i {
  color: #fff;
  font-size: 1.5rem;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Upload zone */
.upload-zone {
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
  min-height: 140px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.upload-zone:hover,
.upload-zone.dragover {
  border-color: #0d6efd;
  background: #e7f1ff;
}
.upload-zone.has-image {
  border-style: solid;
  padding: 0.5rem;
}
.preview-img {
  max-height: 120px;
  border-radius: 4px;
  object-fit: contain;
}

/* Lightbox */
.lightbox-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.9);
  z-index: 9999;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.lightbox-overlay img {
  max-width: 90vw;
  max-height: 80vh;
  object-fit: contain;
  border-radius: 4px;
}
.lightbox-close {
  position: absolute;
  top: 1rem;
  right: 1.5rem;
  color: #fff;
  font-size: 2.5rem;
  background: none;
  border: none;
  cursor: pointer;
  line-height: 1;
}
.lightbox-caption {
  margin-top: 1rem;
  font-size: 1rem;
  color: #fff;
}
</style>
