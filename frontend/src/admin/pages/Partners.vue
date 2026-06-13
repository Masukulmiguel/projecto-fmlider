<template>
  <div class="admin-page p-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Parceiros</h2>
      <button class="btn btn-primary" @click="openCreateModal">+ Novo Parceiro</button>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
          </div>
        </div>

        <div v-else-if="partners.length === 0" class="text-center py-5 text-muted">
          Nenhum parceiro encontrado.
        </div>

        <table v-else class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Logo</th>
              <th>Nome</th>
              <th>Website</th>
              <th>Status</th>
              <th>Ordem</th>
              <th class="text-end">Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="partner in partners" :key="partner.id">
              <td class="align-middle">
                <img
                  v-if="partner.logo"
                  :src="partner.logo"
                  :alt="partner.name"
                  class="partner-thumb"
                />
                <span v-else class="text-muted">Sem logo</span>
              </td>
              <td class="align-middle">{{ partner.name }}</td>
              <td class="align-middle">
                <a
                  v-if="partner.website"
                  :href="partner.website"
                  target="_blank"
                  class="text-primary text-decoration-none"
                >
                  {{ partner.website }}
                </a>
                <span v-else class="text-muted">-</span>
              </td>
              <td class="align-middle">
                <span
                  :class="partner.status ? 'badge bg-success' : 'badge bg-secondary'"
                >
                  {{ partner.status ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="align-middle">{{ partner.order_by }}</td>
              <td class="align-middle text-end">
                <button
                  class="btn btn-sm btn-outline-primary me-2"
                  @click="openEditModal(partner)"
                >
                  Editar
                </button>
                <button
                  class="btn btn-sm btn-outline-danger"
                  @click="confirmDelete(partner)"
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
      ref="partnerModal"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ editing ? 'Editar Parceiro' : 'Novo Parceiro' }}
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
                <label for="partnerName" class="form-label">Nome *</label>
                <input
                  id="partnerName"
                  v-model="form.name"
                  type="text"
                  class="form-control"
                  required
                />
              </div>

              <div class="mb-3">
                <label for="partnerWebsite" class="form-label">Website</label>
                <input
                  id="partnerWebsite"
                  v-model="form.website"
                  type="url"
                  class="form-control"
                  placeholder="https://..."
                />
              </div>

              <div class="mb-3">
                <label for="partnerDescription" class="form-label">Descrição</label>
                <textarea
                  id="partnerDescription"
                  v-model="form.description"
                  class="form-control"
                  rows="3"
                ></textarea>
              </div>

              <div class="mb-3">
                <label for="partnerLogo" class="form-label">Logo</label>
                <input
                  id="partnerLogo"
                  ref="imageInput"
                  type="file"
                  class="form-control"
                  accept="image/*"
                  @change="handleImageChange"
                />
                <div v-if="imagePreview || form.logo" class="mt-2">
                  <img
                    :src="imagePreview || form.logo"
                    alt="Preview"
                    class="partner-preview"
                  />
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="partnerOrder" class="form-label">Ordem</label>
                  <input
                    id="partnerOrder"
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
                      id="partnerStatus"
                      v-model="form.status"
                      class="form-check-input"
                      type="checkbox"
                    />
                    <label class="form-check-label" for="partnerStatus">
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
            Tem certeza que deseja deletar o parceiro
            <strong>{{ partnerToDelete?.name }}</strong>?
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
              @click="deletePartner"
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
import { supabase } from '@/lib/supabase'
import { Modal } from 'bootstrap'

const partners = ref([])
const loading = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const editing = ref(false)
const editingId = ref(null)
const partnerToDelete = ref(null)
const imageFile = ref(null)
const imagePreview = ref(null)

const partnerModal = ref(null)
const deleteModal = ref(null)
const imageInput = ref(null)

let partnerModalInstance = null
let deleteModalInstance = null

const form = reactive({
  name: '',
  logo: '',
  website: '',
  description: '',
  status: true,
  order_by: 0,
})

async function fetchPartners() {
  loading.value = true
  try {
    const { data, error } = await supabase.from('partners').select('*').order('order_by', { ascending: true })
    if (!error) partners.value = data
  } catch (err) {
    console.error('Erro ao buscar parceiros:', err)
    alert('Erro ao carregar parceiros.')
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form.name = ''
  form.logo = ''
  form.website = ''
  form.description = ''
  form.status = true
  form.order_by = 0
  imageFile.value = null
  imagePreview.value = null
  editing.value = false
  editingId.value = null
}

function openCreateModal() {
  resetForm()
  partnerModalInstance.show()
}

function openEditModal(partner) {
  editing.value = true
  editingId.value = partner.id
  form.name = partner.name || ''
  form.logo = partner.logo || ''
  form.website = partner.website || ''
  form.description = partner.description || ''
  form.status = partner.status ?? true
  form.order_by = partner.order_by || 0
  imageFile.value = null
  imagePreview.value = null
  partnerModalInstance.show()
}

function handleImageChange(event) {
  const file = event.target.files[0]
  if (!file) return
  imageFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}

async function uploadImage() {
  if (!imageFile.value) return form.logo
  const fileExt = imageFile.value.name.split('.').pop()
  const fileName = `partners/${Date.now()}.${fileExt}`
  const { data, error } = await supabase.storage.from('uploads').upload(fileName, imageFile.value)
  if (error) throw error
  const { data: urlData } = supabase.storage.from('uploads').getPublicUrl(fileName)
  return urlData.publicUrl
}

async function submitForm() {
  if (!form.name.trim()) {
    alert('O nome é obrigatório.')
    return
  }
  submitting.value = true
  try {
    let logoPath = form.logo
    if (imageFile.value) {
      logoPath = await uploadImage()
    }
    const payload = {
      name: form.name,
      logo: logoPath,
      website: form.website,
      description: form.description,
      status: form.status,
      order_by: form.order_by,
    }
    if (editing.value) {
      const { error } = await supabase.from('partners').update(payload).eq('id', editingId.value)
      if (error) throw error
    } else {
      const { error } = await supabase.from('partners').insert(payload)
      if (error) throw error
    }
    partnerModalInstance.hide()
    await fetchPartners()
  } catch (err) {
    console.error('Erro ao salvar parceiro:', err)
    alert('Erro ao salvar parceiro. Tente novamente.')
  } finally {
    submitting.value = false
  }
}

function confirmDelete(partner) {
  partnerToDelete.value = partner
  deleteModalInstance.show()
}

async function deletePartner() {
  if (!partnerToDelete.value) return
  deleting.value = true
  try {
    const { error } = await supabase.from('partners').delete().eq('id', partnerToDelete.value.id)
    if (error) throw error
    deleteModalInstance.hide()
    await fetchPartners()
  } catch (err) {
    console.error('Erro ao deletar parceiro:', err)
    alert('Erro ao deletar parceiro. Tente novamente.')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  partnerModalInstance = new Modal(partnerModal.value)
  deleteModalInstance = new Modal(deleteModal.value)
  fetchPartners()
})

onBeforeUnmount(() => {
  partnerModalInstance?.dispose()
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
.partner-thumb {
  width: 60px;
  height: 40px;
  object-fit: contain;
  border-radius: 4px;
}
.partner-preview {
  max-width: 100%;
  max-height: 120px;
  object-fit: contain;
  border-radius: 4px;
}
</style>
