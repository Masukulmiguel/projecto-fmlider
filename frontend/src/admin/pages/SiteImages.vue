<template>
  <div class="admin-page p-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Imagens do Site</h2>
      <button class="btn btn-primary" @click="openCreateModal">+ Nova Imagem</button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Carregando...</span>
      </div>
    </div>

    <div v-else-if="images.length === 0" class="card">
      <div class="card-body text-center py-5 text-muted">
        Nenhuma imagem encontrada.
      </div>
    </div>

    <div v-else>
      <div v-for="(items, section) in groupedImages" :key="section" class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0 text-capitalize">{{ sectionLabels[section] || section }}</h5>
          <span class="badge bg-primary">{{ items.length }}</span>
        </div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Chave</th>
                <th>Imagem</th>
                <th>Texto Alternativo</th>
                <th>Status</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="img in items" :key="img.id">
                <td class="align-middle">
                  <code>{{ img.key }}</code>
                </td>
                <td class="align-middle">
                  <img
                    v-if="img.image_url"
                    :src="img.image_url"
                    :alt="img.alt_text"
                    class="site-img-thumb"
                  />
                  <span v-else class="text-muted">Sem imagem</span>
                </td>
                <td class="align-middle">{{ img.alt_text || '—' }}</td>
                <td class="align-middle">
                  <span :class="img.status ? 'badge bg-success' : 'badge bg-secondary'">
                    {{ img.status ? 'Ativo' : 'Inativo' }}
                  </span>
                </td>
                <td class="align-middle text-end">
                  <button class="btn btn-sm btn-outline-primary me-2" @click="openEditModal(img)">
                    Editar
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(img)">
                    Deletar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" ref="formModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editing ? 'Editar Imagem' : 'Nova Imagem' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Secção *</label>
                  <select v-model="form.section" class="form-select" required>
                    <option value="">Selecionar secção...</option>
                    <option v-for="(label, key) in sectionLabels" :key="key" :value="key">{{ label }}</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Chave *</label>
                  <input v-model="form.key" type="text" class="form-control" placeholder="ex: logo, hero_bg" required />
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Imagem</label>
                <input ref="imageInput" type="file" class="form-control" accept="image/*" @change="handleImageChange" />
                <div v-if="imagePreview || form.image_url" class="mt-2">
                  <img :src="imagePreview || form.image_url" alt="Preview" class="site-img-preview" />
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">URL da Imagem (alternativo ao upload)</label>
                <input v-model="form.image_url" type="url" class="form-control" placeholder="https://... ou /assets/img/..." />
              </div>

              <div class="mb-3">
                <label class="form-label">Texto Alternativo</label>
                <input v-model="form.alt_text" type="text" class="form-control" placeholder="Descrição da imagem" />
              </div>

              <div class="mb-3">
                <label class="form-label">Status</label>
                <div class="form-check form-switch mt-2">
                  <input v-model="form.status" class="form-check-input" type="checkbox" id="imgStatus" />
                  <label class="form-check-label" for="imgStatus">{{ form.status ? 'Ativo' : 'Inativo' }}</label>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-primary" :disabled="submitting" @click="submitForm">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
              {{ editing ? 'Salvar' : 'Criar' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" ref="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmar Exclusão</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Tem certeza que deseja deletar a imagem
            <strong>{{ imageToDelete?.section }}/{{ imageToDelete?.key }}</strong>?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger" :disabled="deleting" @click="deleteImage">
              <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
              Deletar
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

const sectionLabels = {
  header: 'Cabeçalho (Header)',
  footer: 'Rodapé (Footer)',
  sidebar: 'Barras Laterais',
  home: 'Página Inicial',
  about: 'Sobre Nós',
  fleet: 'Frota',
  services: 'Serviços',
  news: 'Notícias',
  gallery: 'Galeria',
}

const form = reactive({
  section: '',
  key: '',
  image_url: '',
  alt_text: '',
  status: true,
})

const groupedImages = computed(() => {
  const groups = {}
  images.value.forEach(img => {
    if (!groups[img.section]) groups[img.section] = []
    groups[img.section].push(img)
  })
  return groups
})

async function fetchImages() {
  loading.value = true
  try {
    const { data, error } = await supabase.from('site_images').select('*').order('section').order('key')
    if (!error) images.value = data
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

function handleImageChange(event) {
  const file = event.target.files[0]
  if (!file) return
  imageFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}

async function uploadImage() {
  if (!imageFile.value) return form.image_url
  const fileExt = imageFile.value.name.split('.').pop()
  const fileName = `site-images/${form.section}-${form.key}-${Date.now()}.${fileExt}`
  const { error } = await supabase.storage.from('uploads').upload(fileName, imageFile.value)
  if (error) throw error
  const { data: urlData } = supabase.storage.from('uploads').getPublicUrl(fileName)
  return urlData.publicUrl
}

async function submitForm() {
  if (!form.section || !form.key.trim()) {
    alert('Secção e Chave são obrigatórios.')
    return
  }
  submitting.value = true
  try {
    let imageUrl = form.image_url
    if (imageFile.value) {
      imageUrl = await uploadImage()
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
      const { error } = await supabase.from('site_images').insert(payload)
      if (error) throw error
    }
    formModalInstance.hide()
    await fetchImages()
  } catch (err) {
    console.error('Erro ao salvar imagem:', err)
    alert('Erro ao salvar: ' + (err?.message || JSON.stringify(err)))
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
    await fetchImages()
  } catch (err) {
    console.error('Erro ao deletar:', err)
    alert('Erro ao deletar imagem.')
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
.admin-page {
  background: #f8f9fa;
}
.card {
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
.site-img-thumb {
  width: 80px;
  height: 50px;
  object-fit: cover;
  border-radius: 4px;
}
.site-img-preview {
  max-width: 100%;
  max-height: 200px;
  border-radius: 4px;
}
</style>
