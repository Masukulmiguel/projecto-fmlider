<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0"><i class="bi bi-envelope me-2"></i>Contacts</h4>
      <span class="badge bg-danger fs-6" v-if="unreadCount">{{ unreadCount }} unread</span>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:30px"></th><th>Name</th><th>Email</th><th>Phone</th><th>Subject</th><th>Date</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in contacts" :key="item.id" :class="{ 'table-light fw-bold': !item.is_read, 'cursor-pointer': true }" @click="viewItem(item)">
            <td><i v-if="!item.is_read" class="bi bi-envelope-fill text-primary"></i><i v-else class="bi bi-envelope-open text-muted"></i></td>
            <td>{{ item.name }}</td>
            <td>{{ item.email }}</td>
            <td>{{ item.phone || '-' }}</td>
            <td class="text-truncate" style="max-width:200px">{{ item.subject }}</td>
            <td>{{ formatDate(item.created_at) }}</td>
            <td @click.stop>
              <button v-if="!item.is_read" class="btn btn-sm btn-outline-success me-1" @click="markRead(item.id)" title="Mark as read"><i class="bi bi-check-lg"></i></button>
              <button class="btn btn-sm btn-outline-primary me-1" @click="openReply(item)" title="Reply"><i class="bi bi-reply"></i></button>
              <button class="btn btn-sm btn-outline-danger" @click="deleteItem(item.id)" title="Delete"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
          <tr v-if="!contacts.length"><td colspan="7" class="text-center text-muted py-4">No contacts found.</td></tr>
        </tbody>
      </table>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" ref="viewModalRef">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ viewing?.subject }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-6"><strong>From:</strong> {{ viewing?.name }}</div>
              <div class="col-6"><strong>Email:</strong> {{ viewing?.email }}</div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Phone:</strong> {{ viewing?.phone || '-' }}</div>
              <div class="col-6"><strong>Date:</strong> {{ formatDate(viewing?.created_at) }}</div>
            </div>
            <hr>
            <div class="p-3 bg-light rounded">{{ viewing?.message }}</div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline-success" v-if="viewing && !viewing.is_read" @click="markRead(viewing.id); viewing.is_read = true"><i class="bi bi-check-lg me-1"></i>Mark as Read</button>
            <button class="btn btn-primary" @click="bsViewModal.hide(); openReply(viewing)"><i class="bi bi-reply me-1"></i>Reply</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reply Modal -->
    <div class="modal fade" id="replyModal" tabindex="-1" ref="replyModalRef">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Reply to {{ replying?.name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">To</label>
              <input :value="replying?.email" type="email" class="form-control" disabled>
            </div>
            <div class="mb-3">
              <label class="form-label">Subject</label>
              <input :value="'Re: ' + replying?.subject" type="text" class="form-control" disabled>
            </div>
            <div class="mb-3">
              <label class="form-label">Message *</label>
              <textarea v-model="replyMessage" class="form-control" rows="6" placeholder="Write your reply..." required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="sendReply" :disabled="replying || !replyMessage.trim()">
              <span v-if="sendingReply" class="spinner-border spinner-border-sm me-1"></span>Send Reply
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { Modal } from 'bootstrap'

const api = axios.create({ baseURL: 'http://localhost:8000' })
api.interceptors.request.use(config => {
  const token = localStorage.getItem('supabase_access_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

const contacts = ref([])
const loading = ref(true)
const sendingReply = ref(false)

const viewModalRef = ref(null)
const replyModalRef = ref(null)
let bsViewModal = null
let bsReplyModal = null

const viewing = ref(null)
const replying = ref(null)
const replyMessage = ref('')

const unreadCount = computed(() => contacts.value.filter(c => !c.is_read).length)

onMounted(async () => {
  bsViewModal = new Modal(viewModalRef.value)
  bsReplyModal = new Modal(replyModalRef.value)
  await fetchAll()
})

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get('/api/admin/contacts')
    contacts.value = data.contacts || data.data || []
  } catch (e) { console.error(e) }
  loading.value = false
}

function formatDate(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function viewItem(item) {
  viewing.value = { ...item }
  bsViewModal.show()
  if (!item.is_read) markRead(item.id)
}

async function markRead(id) {
  try {
    await api.put(`/api/admin/contacts/${id}/mark-read`)
    const c = contacts.value.find(c => c.id === id)
    if (c) c.is_read = true
  } catch (e) { console.error(e) }
}

function openReply(item) {
  replying.value = { ...item }
  replyMessage.value = ''
  bsReplyModal.show()
}

async function sendReply() {
  if (!replyMessage.value.trim()) return alert('Please enter a message.')
  sendingReply.value = true
  try {
    await api.post(`/api/admin/contacts/${replying.value.id}/reply`, { message: replyMessage.value })
    bsReplyModal.hide()
    alert('Reply sent successfully.')
  } catch (e) { alert('Error sending reply: ' + (e.response?.data?.message || e.message)) }
  sendingReply.value = false
}

async function deleteItem(id) {
  if (!confirm('Delete this contact?')) return
  try {
    await api.delete(`/api/admin/contacts/${id}`)
    await fetchAll()
  } catch (e) { alert('Error deleting.') }
}
</script>

<style scoped>
.cursor-pointer { cursor: pointer; }
</style>
