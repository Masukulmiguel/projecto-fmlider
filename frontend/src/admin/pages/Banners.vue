<template>
  <div class="admin-page p-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Banners</h2>
      <button class="btn btn-primary" @click="openCreateModal">+ Novo Banner</button>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
          </div>
        </div>

        <div v-else-if="banners.length === 0" class="text-center py-5 text-muted">
          Nenhum banner encontrado.
        </div>

        <table v-else class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Título</th>
              <th>Imagem</th>
              <th>Status</th>
              <th>Ordem</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="banner in banners" :key="banner.id">
              <td class="align-middle">{{ banner.title }}</td>
              <td class="align-middle">
                <img
                  v-if="banner.image"
                  :src="banner.image"
                  :alt="banner.title"
                  class="banner-thumb"
                />
                <span v-else class="text-muted">Sem imagem</span>
              </td>
              <td class="align-middle">
                <span
                  :class="banner.status ? 'badge bg-success' : 'badge bg-secondary'"
                >
                  {{ banner.status ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="align-middle">{{ banner.order_by }}</td>
              <td class="align-middle text-end">
                <button
                  class="btn btn-sm btn-outline-primary me-2"
                  @click="openEditModal(banner)"
                >
                  Editar
                </button>
                <button
                  class="btn btn-sm btn-outline-danger"
                  @click="confirmDelete(banner)"
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
      ref="bannerModal"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ editing ? 'Editar Banner' : 'Novo Banner' }}
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
                <label for="bannerTitle" class="form-label">Título *</label>
                <input
                  id="bannerTitle"
                  v-model="form.title"
                  type="text"
                  class="form-control"
                  required
                />
              </div>

              <div class="mb-3">
                <label for="bannerDescription" class="form-label">Descrição</label>
                <textarea
                  id="bannerDescription"
                  v-model="form.description"
                  class="form-control"
                  rows="3"
                ></textarea>
              </div>

              <div class="mb-3">
                <label for="bannerImage" class="form-label">Imagem</label>
                <input
                  id="bannerImage"
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
                    class="banner-preview"
                  />
                </div>
              </div>

              <div class="mb-3">
                <label for="bannerLink" class="form-label">Link</label>
                <input
                  id="bannerLink"
                  v-model="form.link"
                  type="url"
                  class="form-control"
                  placeholder="https://..."
                />
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="bannerOrder" class="form-label">Ordem</label>
                  <input
                    id="bannerOrder"
                    v-model.number="form.order_by"
                    type="number"
                    class="form-control"
                    min="0"
                  />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Status</label>
                  <div class="form-check form-switch mt-2">
                    <input
                      id="bannerStatus"
                      v-model="form.status"
                      class="form-check-input"
                      type="checkbox"
                    />
                    <label class="form-check-label" for="bannerStatus">
                      {{ form.status ? 'Ativo' : 'Inativo' }}
                    </label>
                  </div>
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
            Tem certeza que deseja deletar o banner
            <strong>{{ bannerToDelete?.title }}</strong>?
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
              @click="deleteBanner"
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
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { Modal } from 'bootstrap'

const API_URL = '/api/admin/banners'

const banners = ref([])
const loading = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const editing = ref(false)
const editingId = ref(null)
const bannerToDelete = ref(null)
const imageFile = ref(null)
const imagePreview = ref(null)

const bannerModal = ref(null)
const deleteModal = ref(null)
const imageInput = ref(null)

let bannerModalInstance = null
let deleteModalInstance = null

const form = reactive({
  title: '',
  description: '',
  image: '',
  link: '',
  status: true,
  order_by: 0,
})

function getHeaders() {
  const token = localStorage.getItem('supabase_access_token')
  return { Authorization: `Bearer ${token}` }
}

async function fetchBanners() {
  loading.value = true
  try {
    const { data } = await axios.get(API_URL, { headers: getHeaders() })
    banners.value = Array.isArray(data) ? data : (data.banners || data.data || [])
  } catch (err) {
    console.error('Erro ao buscar banners:', err)
    alert('Erro ao carregar banners.')
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form.title = ''
  form.description = ''
  form.image = ''
  form.link = ''
  form.status = true
  form.order_by = 0
  imageFile.value = null
  imagePreview.value = null
  editing.value = false
  editingId.value = null
}

function openCreateModal() {
  resetForm()
  bannerModalInstance.show()
}

function openEditModal(banner) {
  editing.value = true
  editingId.value = banner.id
  form.title = banner.title || ''
  form.description = banner.description || ''
  form.image = banner.image || ''
  form.link = banner.link || ''
  form.status = banner.status ?? true
  form.order_by = banner.order_by || 0
  imageFile.value = null
  imagePreview.value = null
  bannerModalInstance.show()
}

function handleImageChange(event) {
  const file = event.target.files[0]
  if (!file) return
  imageFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}

async function uploadImage() {
  if (!imageFile.value) return form.image
  const formData = new FormData()
  formData.append('image', imageFile.value)
  const { data } = await axios.post('/api/admin/gallery/upload', formData, {
    headers: {
      ...getHeaders(),
      'Content-Type': 'multipart/form-data',
    },
  })
  return data.path || data.url || data.image
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
      image: imagePath,
      link: form.link,
      status: form.status,
      order_by: form.order_by,
    }
    if (editing.value) {
      await axios.put(`${API_URL}/${editingId.value}`, payload, {
        headers: getHeaders(),
      })
    } else {
      await axios.post(API_URL, payload, { headers: getHeaders() })
    }
    bannerModalInstance.hide()
    await fetchBanners()
  } catch (err) {
    console.error('Erro ao salvar banner:', err)
    alert('Erro ao salvar banner. Tente novamente.')
  } finally {
    submitting.value = false
  }
}

function confirmDelete(banner) {
  bannerToDelete.value = banner
  deleteModalInstance.show()
}

async function deleteBanner() {
  if (!bannerToDelete.value) return
  deleting.value = true
  try {
    await axios.delete(`${API_URL}/${bannerToDelete.value.id}`, {
      headers: getHeaders(),
    })
    deleteModalInstance.hide()
    await fetchBanners()
  } catch (err) {
    console.error('Erro ao deletar banner:', err)
    alert('Erro ao deletar banner. Tente novamente.')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  bannerModalInstance = new Modal(bannerModal.value)
  deleteModalInstance = new Modal(deleteModal.value)
  fetchBanners()
})

onBeforeUnmount(() => {
  bannerModalInstance?.dispose()
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
.banner-thumb {
  width: 80px;
  height: 45px;
  object-fit: cover;
  border-radius: 4px;
}
.banner-preview {
  max-width: 100%;
  max-height: 200px;
  border-radius: 4px;
}
</style>
