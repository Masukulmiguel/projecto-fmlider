<template>
  <div class="admin-page p-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Notícias</h2>
      <button class="btn btn-primary" @click="openCreateModal">+ Nova Notícia</button>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
          </div>
        </div>

        <div v-else-if="news.length === 0" class="text-center py-5 text-muted">
          Nenhuma notícia encontrada.
        </div>

        <table v-else class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Imagem</th>
              <th>Título</th>
              <th>Categoria</th>
              <th>Status</th>
              <th>Data</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in news" :key="item.id">
              <td class="align-middle">
                <img
                  v-if="item.image"
                  :src="item.image"
                  :alt="item.title"
                  class="news-thumb"
                />
                <span v-else class="text-muted">Sem imagem</span>
              </td>
              <td class="align-middle">{{ item.title }}</td>
              <td class="align-middle">
                <span v-if="item.category" class="badge bg-info text-dark">{{ item.category }}</span>
                <span v-else class="text-muted">-</span>
              </td>
              <td class="align-middle">
                <span
                  :class="item.status === 'published' ? 'badge bg-success' : 'badge bg-secondary'"
                >
                  {{ item.status === 'published' ? 'Publicada' : 'Rascunho' }}
                </span>
              </td>
              <td class="align-middle">{{ formatDate(item.published_at) }}</td>
              <td class="align-middle text-end">
                <button
                  class="btn btn-sm btn-outline-primary me-2"
                  @click="openEditModal(item)"
                >
                  Editar
                </button>
                <button
                  class="btn btn-sm btn-outline-danger"
                  @click="confirmDelete(item)"
                >
                  Deletar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div
      class="modal fade"
      ref="newsModal"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ editing ? 'Editar Notícia' : 'Nova Notícia' }}
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label for="newsTitle" class="form-label">Título *</label>
                <input
                  id="newsTitle"
                  v-model="form.title"
                  type="text"
                  class="form-control"
                  required
                />
              </div>

              <div class="mb-3">
                <label for="newsDescription" class="form-label">Descrição</label>
                <textarea
                  id="newsDescription"
                  v-model="form.description"
                  class="form-control"
                  rows="2"
                ></textarea>
              </div>

              <div class="mb-3">
                <label for="newsContent" class="form-label">Conteúdo</label>
                <textarea
                  id="newsContent"
                  v-model="form.content"
                  class="form-control"
                  rows="6"
                ></textarea>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="newsCategory" class="form-label">Categoria</label>
                  <input
                    id="newsCategory"
                    v-model="form.category"
                    type="text"
                    class="form-control"
                    placeholder="Ex: Economia, Política..."
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label for="newsImage" class="form-label">Imagem</label>
                  <input
                    id="newsImage"
                    ref="imageInput"
                    type="file"
                    class="form-control"
                    accept="image/*"
                    @change="handleImageChange"
                  />
                  <div v-if="imagePreview || form.image" class="mt-2">
                    <img
                      :src="imagePreview || form.image"
                      alt="Preview"
                      class="news-preview"
                    />
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Status</label>
                  <div class="form-check form-switch mt-2">
                    <input
                      id="newsStatus"
                      v-model="isPublished"
                      class="form-check-input"
                      type="checkbox"
                    />
                    <label class="form-check-label" for="newsStatus">
                      {{ isPublished ? 'Publicada' : 'Rascunho' }}
                    </label>
                  </div>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="newsPublishedAt" class="form-label">Data de Publicação</label>
                  <input
                    id="newsPublishedAt"
                    v-model="form.published_at"
                    type="datetime-local"
                    class="form-control"
                  />
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-primary"
              :disabled="submitting"
              @click="submitForm"
            >
              <span
                v-if="submitting"
                class="spinner-border spinner-border-sm me-1"
              ></span>
              {{ editing ? 'Salvar' : 'Criar' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      class="modal fade"
      ref="deleteModal"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmar Exclusão</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            Tem certeza que deseja deletar a notícia
            <strong>{{ newsToDelete?.title }}</strong>?
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-danger"
              :disabled="deleting"
              @click="deleteNews"
            >
              <span
                v-if="deleting"
                class="spinner-border spinner-border-sm me-1"
              ></span>
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

const news = ref([])
const loading = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const editing = ref(false)
const editingId = ref(null)
const newsToDelete = ref(null)
const imageFile = ref(null)
const imagePreview = ref(null)

const newsModal = ref(null)
const deleteModal = ref(null)
const imageInput = ref(null)

let newsModalInstance = null
let deleteModalInstance = null

const form = reactive({
  title: '',
  description: '',
  content: '',
  category: '',
  image: '',
  status: 'draft',
  published_at: '',
})

const isPublished = computed({
  get: () => form.status === 'published',
  set: (val) => { form.status = val ? 'published' : 'draft' },
})

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('pt-AO', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function toDatetimeLocal(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

async function fetchNews() {
  loading.value = true
  try {
    const { data, error } = await supabase.from('news').select('*').order('published_at', { ascending: false })
    if (!error) news.value = data
  } catch (err) {
    console.error('Erro ao buscar notícias:', err)
    alert('Erro ao carregar notícias.')
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form.title = ''
  form.description = ''
  form.content = ''
  form.category = ''
  form.image = ''
  form.status = 'draft'
  form.published_at = ''
  imageFile.value = null
  imagePreview.value = null
  editing.value = false
  editingId.value = null
}

function openCreateModal() {
  resetForm()
  newsModalInstance.show()
}

function openEditModal(item) {
  editing.value = true
  editingId.value = item.id
  form.title = item.title || ''
  form.description = item.description || ''
  form.content = item.content || ''
  form.category = item.category || ''
  form.image = item.image || ''
  form.status = item.status || 'draft'
  form.published_at = toDatetimeLocal(item.published_at)
  imageFile.value = null
  imagePreview.value = null
  newsModalInstance.show()
}

function handleImageChange(event) {
  const file = event.target.files[0]
  if (!file) return
  imageFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}

async function uploadImage() {
  if (!imageFile.value) return form.image
  const fileExt = imageFile.value.name.split('.').pop()
  const fileName = `news/${Date.now()}.${fileExt}`
  const { data, error } = await supabase.storage.from('uploads').upload(fileName, imageFile.value)
  if (error) throw error
  const { data: urlData } = supabase.storage.from('uploads').getPublicUrl(fileName)
  return urlData.publicUrl
}

async function submitForm() {
  if (!form.title.trim()) {
    alert('O título é obrigatório.')
    return
  }
  submitting.value = true
  try {
    let imagePath = form.image
    if (imageFile.value) {
      imagePath = await uploadImage()
    }
    const payload = {
      title: form.title,
      description: form.description,
      content: form.content,
      category: form.category,
      image: imagePath,
      status: form.status,
      published_at: form.published_at || null,
    }
    if (editing.value) {
      const { error } = await supabase.from('news').update(payload).eq('id', editingId.value)
      if (error) throw error
    } else {
      const { error } = await supabase.from('news').insert(payload)
      if (error) throw error
    }
    newsModalInstance.hide()
    await fetchNews()
  } catch (err) {
    console.error('Erro ao salvar notícia:', err)
    alert('Erro ao salvar notícia. Tente novamente.')
  } finally {
    submitting.value = false
  }
}

function confirmDelete(item) {
  newsToDelete.value = item
  deleteModalInstance.show()
}

async function deleteNews() {
  if (!newsToDelete.value) return
  deleting.value = true
  try {
    const { error } = await supabase.from('news').delete().eq('id', newsToDelete.value.id)
    if (error) throw error
    deleteModalInstance.hide()
    await fetchNews()
  } catch (err) {
    console.error('Erro ao deletar notícia:', err)
    alert('Erro ao deletar notícia. Tente novamente.')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  newsModalInstance = new Modal(newsModal.value)
  deleteModalInstance = new Modal(deleteModal.value)
  fetchNews()
})

onBeforeUnmount(() => {
  newsModalInstance?.dispose()
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
.news-thumb {
  width: 80px;
  height: 50px;
  object-fit: cover;
  border-radius: 4px;
}
.news-preview {
  max-width: 100%;
  max-height: 200px;
  border-radius: 4px;
}
</style>
