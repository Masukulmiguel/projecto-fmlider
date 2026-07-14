<template>
  <div class="admin-page p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
      <div>
        <h1 class="page-title"><i class="bi bi-gear-wide-connected me-2"></i>{{ t('admin.services_title') }}</h1>
        <p class="text-muted mb-0">{{ t('admin.services_description_text') }}</p>
      </div>
      <button class="btn btn-primary" @click="openCreateModal">
        <i class="bi bi-plus-lg me-1"></i> {{ t('admin.services_new') }}
      </button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else-if="items.length === 0" class="card empty-card">
      <div class="card-body text-center py-5">
        <i class="bi bi-gear" style="font-size: 3rem; color: #94a3b8;"></i>
        <h5 class="mt-3 text-muted">{{ t('admin.services_empty') }}</h5>
        <p class="text-muted mb-3">{{ t('admin.services_add_first') }}</p>
        <button class="btn btn-primary" @click="openCreateModal">
          <i class="bi bi-plus-lg me-1"></i> {{ t('admin.services_create_service') }}
        </button>
      </div>
    </div>

    <div v-else class="card">
      <div class="card-body p-0">
        <div
          v-for="s in sortedItems"
          :key="s.id"
          class="service-row"
        >
          <div class="row-col-order">
            <span class="order-num">{{ s.order_by ?? 0 }}</span>
          </div>

          <div class="row-col-thumb">
            <div class="thumb-wrap">
              <img
                v-if="s.image"
                :src="s.image"
                :alt="s.title"
                class="thumb-img"
              />
              <div v-else class="thumb-placeholder">
                <i class="bi bi-image"></i>
              </div>
            </div>
          </div>

          <div class="row-col-title">
            <span class="title-text">{{ s.title }}</span>
            <span class="slug-text">{{ s.slug }}</span>
          </div>

          <div class="row-col-status">
            <span class="status-dot" :class="s.status == 1 ? 'active' : 'inactive'"></span>
          </div>

          <div class="row-col-actions">
            <button class="btn-icon btn-edit" @click="openEditModal(s)" :title="t('common.edit')">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn-icon btn-delete" @click="confirmDelete(s)" :title="t('common.delete')">
              <i class="bi bi-trash3"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      ref="serviceModal"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-gear-wide-connected me-2"></i>{{ editing ? t('admin.services_edit_service') : t('admin.services_new_service') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="submitForm" novalidate>
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label">{{ t('admin.services_title_label') }} *</label>
                  <input
                    v-model="form.title"
                    type="text"
                    class="form-control"
                    required
                    @input="autoSlug"
                  />
                </div>
                <div class="col-md-4">
                  <label class="form-label">Slug *</label>
                  <input
                    v-model="form.slug"
                    type="text"
                    class="form-control"
                    required
                  />
                </div>
                <div class="col-12">
                  <label class="form-label">{{ t('admin.services_short_description') }}</label>
                  <input
                    v-model="form.description"
                    type="text"
                    class="form-control"
                    :placeholder="t('admin.services_desc_placeholder')"
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ t('admin.services_icon') }}</label>
                  <input
                    v-model="form.icon"
                    type="text"
                    class="form-control"
                    placeholder="bi-truck, bi-box, ..."
                  />
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ t('admin.services_image') }}</label>
                  <div class="upload-zone" @click="triggerFileInput" @dragover.prevent @drop.prevent="handleDrop">
                    <input
                      ref="fileInput"
                      type="file"
                      accept="image/*"
                      class="d-none"
                      @change="handleImageChange"
                    />
                    <div v-if="imagePreview || form.image" class="preview-wrap">
                      <img :src="imagePreview || form.image" alt="Preview" class="preview-img" />
                      <button
                        type="button"
                        class="btn-remove-img"
                        @click.stop="removeImage"
                        :title="t('admin.services_remove_image')"
                      >
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </div>
                    <div v-else class="upload-placeholder">
                      <i class="bi bi-cloud-arrow-up"></i>
                      <p class="mb-0 mt-1">{{ t('admin.services_upload_hint') }}</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-8">
                  <label class="form-label">{{ t('admin.services_order') }}</label>
                  <input
                    v-model.number="form.order_by"
                    type="number"
                    class="form-control"
                    min="0"
                  />
                </div>
                <div class="col-md-4">
                  <label class="form-label">{{ t('admin.services_status_label') }}</label>
                  <div class="form-check form-switch mt-1">
                    <input
                      id="serviceStatus"
                      v-model="form.status"
                      class="form-check-input"
                      type="checkbox"
                    />
                    <label class="form-check-label" for="serviceStatus">
                      {{ form.status ? t('admin.services_active') : t('admin.services_inactive') }}
                    </label>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ t('common.cancel') }}</button>
              <button type="submit" class="btn btn-primary" :disabled="submitting">
                <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                {{ editing ? t('common.save') : t('admin.services_new') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      ref="deleteModal"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body text-center py-4">
            <div class="delete-icon-circle">
              <i class="bi bi-trash3"></i>
            </div>
            <h5 class="mt-3 mb-1">{{ t('common.confirm') }}</h5>
            <p class="text-muted mb-0">
              {{ t('admin.services_confirm_delete') }}
              <strong>{{ itemToDelete?.title }}</strong>?
            </p>
          </div>
          <div class="modal-footer justify-content-center border-0 pt-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              {{ t('common.cancel') }}
            </button>
            <button type="button" class="btn btn-danger" :disabled="deleting" @click="deleteItem">
              <span v-if="deleting" class="spinner-border spinner-border-sm me-1"></span>
              {{ t('common.delete') }}
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

const items = ref([])
const loading = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const editing = ref(false)
const editingId = ref(null)
const itemToDelete = ref(null)
const imagePreview = ref(null)
const imageBase64 = ref(null)

const serviceModal = ref(null)
const deleteModal = ref(null)
const fileInput = ref(null)

let serviceModalInstance = null
let deleteModalInstance = null

const MAX_SIZE = 2 * 1024 * 1024

const defaultForm = () => ({
  title: '',
  slug: '',
  description: '',
  icon: '',
  image: '',
  status: true,
  order_by: 0,
})
const form = reactive(defaultForm())

const sortedItems = computed(() =>
  [...items.value].sort((a, b) => (a.order_by ?? 0) - (b.order_by ?? 0))
)

const toSlug = (str) =>
  str.toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)+/g, '')

const autoSlug = () => {
  if (!editing.value) {
    form.slug = toSlug(form.title)
  }
}

async function fileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = () => resolve(reader.result)
    reader.onerror = reject
    reader.readAsDataURL(file)
  })
}

async function fetchItems() {
  loading.value = true
  try {
    const { data, error } = await supabase
      .from('services')
      .select('*')
      .order('order_by', { ascending: true })
    if (!error) items.value = data
  } catch (err) {
    console.error('Erro ao carregar serviços:', err)
    toast.error(t('admin.error_loading_services'))
  } finally {
    loading.value = false
  }
}

function resetForm() {
  Object.assign(form, defaultForm())
  imagePreview.value = null
  imageBase64.value = null
  editing.value = false
  editingId.value = null
}

function openCreateModal() {
  resetForm()
  serviceModalInstance.show()
}

function openEditModal(item) {
  editing.value = true
  editingId.value = item.id
  form.title = item.title || ''
  form.slug = item.slug || ''
  form.description = item.description || ''
  form.icon = item.icon || ''
  form.image = item.image || ''
  form.status = item.status == 1
  form.order_by = item.order_by || 0
  imagePreview.value = null
  imageBase64.value = null
  serviceModalInstance.show()
}

function triggerFileInput() {
  fileInput.value?.click()
}

async function handleImageChange(event) {
  const file = event.target.files[0]
  if (!file) return
  if (file.size > MAX_SIZE) {
    toast.warning(t('admin.services_image_too_large'))
    return
  }
  imageBase64.value = await fileToBase64(file)
  imagePreview.value = imageBase64.value
}

async function handleDrop(event) {
  const file = event.dataTransfer.files[0]
  if (!file || !file.type.startsWith('image/')) return
  if (file.size > MAX_SIZE) {
    toast.warning(t('admin.services_image_too_large'))
    return
  }
  imageBase64.value = await fileToBase64(file)
  imagePreview.value = imageBase64.value
}

function removeImage() {
  form.image = ''
  imagePreview.value = null
  imageBase64.value = null
  if (fileInput.value) fileInput.value.value = ''
}

async function submitForm() {
  if (!form.title.trim()) {
    toast.warning(t('admin.services_title_required'))
    return
  }
  submitting.value = true
  try {
    let imagePath = form.image
    if (imageBase64.value) {
      imagePath = imageBase64.value
    }
    const payload = {
      title: form.title.trim(),
      slug: form.slug || toSlug(form.title),
      description: form.description,
      icon: form.icon,
      image: imagePath,
      status: form.status ? 1 : 0,
      order_by: form.order_by,
    }
    if (editing.value) {
      const { error } = await supabase.from('services').update(payload).eq('id', editingId.value)
      if (error) throw error
    } else {
      const { error } = await supabase.from('services').insert(payload)
      if (error) throw error
    }
    serviceModalInstance.hide()
    await fetchItems()
    toast.success(editing.value ? t('admin.services_updated') : t('admin.services_created'))
  } catch (err) {
    console.error('Erro ao salvar serviço:', err)
    toast.error(t('admin.error_saving_service'))
  } finally {
    submitting.value = false
  }
}

function confirmDelete(item) {
  itemToDelete.value = item
  deleteModalInstance.show()
}

async function deleteItem() {
  if (!itemToDelete.value) return
  deleting.value = true
  try {
    const { error } = await supabase.from('services').delete().eq('id', itemToDelete.value.id)
    if (error) throw error
    deleteModalInstance.hide()
    await fetchItems()
    toast.success(t('admin.services_deleted'))
  } catch (err) {
    console.error('Erro ao deletar serviço:', err)
    toast.error(t('admin.error_deleting_service'))
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  serviceModalInstance = new Modal(serviceModal.value)
  deleteModalInstance = new Modal(deleteModal.value)
  fetchItems()
})

onBeforeUnmount(() => {
  serviceModalInstance?.dispose()
  deleteModalInstance?.dispose()
})
</script>

<style scoped>
.admin-page {
  background: #f8f9fa;
  min-height: 100vh;
}

.page-title {
  font-size: 1.6rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
  color: #0f172a;
}

.card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.empty-card {
  box-shadow: none;
  border: 1px dashed #e2e8f0;
}

.service-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.15s;
}

.service-row:last-child {
  border-bottom: none;
}

.service-row:hover {
  background: #f8fafc;
}

.row-col-order {
  width: 36px;
  flex-shrink: 0;
  text-align: center;
}

.order-num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: #f1f5f9;
  font-size: 0.8rem;
  font-weight: 600;
  color: #475569;
}

.row-col-thumb {
  flex-shrink: 0;
}

.thumb-wrap {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  overflow: hidden;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
}

.thumb-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumb-placeholder {
  color: #94a3b8;
  font-size: 1.1rem;
}

.row-col-title {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.title-text {
  font-weight: 600;
  color: #0f172a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.slug-text {
  font-size: 0.8rem;
  color: #94a3b8;
  font-family: monospace;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.row-col-status {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-dot.active {
  background: #22c55e;
}

.status-dot.inactive {
  background: #cbd5e1;
}

.row-col-actions {
  display: inline-flex;
  gap: 0.4rem;
  flex-shrink: 0;
}

.btn-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: background 0.15s, transform 0.1s;
}

.btn-icon:hover {
  transform: translateY(-1px);
}

.btn-icon.btn-edit {
  background: #eff6ff;
  color: #3b82f6;
}

.btn-icon.btn-edit:hover {
  background: #dbeafe;
}

.btn-icon.btn-delete {
  background: #fef2f2;
  color: #ef4444;
}

.btn-icon.btn-delete:hover {
  background: #fee2e2;
}

.upload-zone {
  border: 2px dashed #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
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
}

.preview-wrap {
  position: relative;
  display: inline-block;
}

.preview-img {
  max-width: 200px;
  max-height: 140px;
  border-radius: 8px;
  object-fit: cover;
}

.btn-remove-img {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #ef4444;
  color: #fff;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  cursor: pointer;
  line-height: 1;
}

.delete-icon-circle {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #fef2f2;
  color: #ef4444;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  margin: 0 auto;
}

.modal-backdrop {
  z-index: 1040;
}

.modal {
  z-index: 1050;
}

@media (max-width: 575.98px) {
  .service-row {
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
  }

  .row-col-order {
    width: auto;
  }

  .row-col-title {
    flex-basis: calc(100% - 100px);
  }

  .row-col-status {
    width: auto;
  }
}
</style>
