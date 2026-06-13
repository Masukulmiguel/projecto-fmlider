<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0"><i class="bi bi-question-circle me-2"></i>FAQs</h4>
      <button class="btn btn-primary" @click="openModal()"><i class="bi bi-plus-lg me-1"></i>New</button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Question</th><th>Answer</th><th>Category</th><th>Status</th><th>Order</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in faqs" :key="item.id">
            <td style="max-width:250px">{{ item.question }}</td>
            <td class="text-truncate" style="max-width:300px">{{ item.answer }}</td>
            <td><span class="badge bg-info text-dark">{{ item.category || 'General' }}</span></td>
            <td><span class="badge" :class="item.status === 'published' ? 'bg-success' : 'bg-secondary'">{{ item.status }}</span></td>
            <td>{{ item.order_by }}</td>
            <td>
              <button class="btn btn-sm btn-outline-primary me-1" @click="openModal(item)"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-danger" @click="deleteItem(item.id)"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
          <tr v-if="!faqs.length"><td colspan="6" class="text-center text-muted py-4">No FAQs found.</td></tr>
        </tbody>
      </table>
    </div>

    <div class="modal fade" id="faqModal" tabindex="-1" ref="modalRef">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ form.id ? 'Edit' : 'New' }} FAQ</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Question *</label>
              <input v-model="form.question" type="text" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Answer *</label>
              <textarea v-model="form.answer" class="form-control" rows="5" required></textarea>
            </div>
            <div class="row mb-3">
              <div class="col">
                <label class="form-label">Category</label>
                <input v-model="form.category" type="text" class="form-control" placeholder="e.g. Pricing, Services">
              </div>
              <div class="col">
                <label class="form-label">Status</label>
                <select v-model="form.status" class="form-select">
                  <option value="published">Published</option>
                  <option value="draft">Draft</option>
                </select>
              </div>
              <div class="col">
                <label class="form-label">Order</label>
                <input v-model.number="form.order_by" type="number" class="form-control" min="0">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="save" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>Save
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { Modal } from 'bootstrap'

const api = axios.create({ baseURL: '' })
api.interceptors.request.use(config => {
  const token = localStorage.getItem('supabase_access_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

const faqs = ref([])
const loading = ref(true)
const saving = ref(false)
const modalRef = ref(null)
let bsModal = null

const defaultForm = { id: null, question: '', answer: '', category: '', status: 'published', order_by: 0 }
const form = reactive({ ...defaultForm })

onMounted(async () => {
  bsModal = new Modal(modalRef.value)
  await fetchAll()
})

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get('/api/faqs')
    faqs.value = data.faqs || data.data || []
  } catch (e) { console.error(e) }
  loading.value = false
}

function openModal(item = null) {
  if (item) {
    Object.assign(form, { ...item })
  } else {
    Object.assign(form, { ...defaultForm })
  }
  bsModal.show()
}

async function save() {
  if (!form.question || !form.answer) return alert('Question and answer are required.')
  saving.value = true
  try {
    if (form.id) {
      await api.put(`/api/admin/faqs/${form.id}`, form)
    } else {
      await api.post('/api/admin/faqs', form)
    }
    bsModal.hide()
    await fetchAll()
  } catch (e) { alert('Error saving: ' + (e.response?.data?.message || e.message)) }
  saving.value = false
}

async function deleteItem(id) {
  if (!confirm('Delete this FAQ?')) return
  try {
    await api.delete(`/api/admin/faqs/${id}`)
    await fetchAll()
  } catch (e) { alert('Error deleting.') }
}
</script>
