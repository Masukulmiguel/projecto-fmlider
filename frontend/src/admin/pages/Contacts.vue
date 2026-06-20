<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-envelope me-2"></i>{{ t('admin.contacts_title') }}</h4>
      <span class="badge bg-danger fs-6" v-if="unreadCount">{{ unreadCount }} {{ t('admin.contacts_unread') }}</span>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:30px"></th><th>{{ t('admin.contacts_name') }}</th><th>{{ t('admin.contacts_email') }}</th><th>{{ t('admin.contacts_phone') }}</th><th>{{ t('admin.contacts_subject') }}</th><th>{{ t('admin.contacts_date') }}</th><th>{{ t('admin.contacts_actions') }}</th>
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
              <button v-if="!item.is_read" class="btn btn-sm btn-outline-success me-1" @click="markRead(item.id)" :title="t('admin.contacts_mark_read')"><i class="bi bi-check-lg"></i></button>
              <button class="btn btn-sm btn-outline-primary me-1" @click="openReply(item)" :title="t('admin.contacts_reply')"><i class="bi bi-reply"></i></button>
              <button class="btn btn-sm btn-outline-danger" @click="deleteItem(item.id)" :title="t('admin.contacts_delete')"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
          <tr v-if="!contacts.length"><td colspan="7" class="text-center text-muted py-4">{{ t('admin.contacts_empty') }}</td></tr>
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
              <div class="col-6"><strong>{{ t('admin.contacts_from') }}</strong> {{ viewing?.name }}</div>
              <div class="col-6"><strong>Email:</strong> {{ viewing?.email }}</div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>{{ t('admin.contacts_phone') }}:</strong> {{ viewing?.phone || '-' }}</div>
              <div class="col-6"><strong>{{ t('admin.contacts_date_label') }}</strong> {{ formatDate(viewing?.created_at) }}</div>
            </div>
            <hr>
            <div class="p-3 bg-light rounded">{{ viewing?.message }}</div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline-success" v-if="viewing && !viewing.is_read" @click="markRead(viewing.id); viewing.is_read = true"><i class="bi bi-check-lg me-1"></i>{{ t('admin.contacts_mark_as_read') }}</button>
            <button class="btn btn-primary" @click="bsViewModal.hide(); openReply(viewing)"><i class="bi bi-reply me-1"></i>{{ t('admin.contacts_reply') }}</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal">{{ t('common.close') }}</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reply Modal -->
    <div class="modal fade" id="replyModal" tabindex="-1" ref="replyModalRef">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ t('admin.contacts_reply_to') }} {{ replying?.name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">{{ t('admin.contacts_to') }}</label>
              <input :value="replying?.email" type="email" class="form-control" disabled>
            </div>
            <div class="mb-3">
              <label class="form-label">{{ t('admin.contacts_subject') }}</label>
              <input :value="'Re: ' + replying?.subject" type="text" class="form-control" disabled>
            </div>
            <div class="mb-3">
              <label class="form-label">{{ t('admin.contacts_message') }} *</label>
              <textarea v-model="replyMessage" class="form-control" rows="6" :placeholder="t('admin.contacts_write_reply')" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ t('common.cancel') }}</button>
            <button type="button" class="btn btn-primary" @click="sendReply" :disabled="replying || !replyMessage.trim()">
              <span v-if="sendingReply" class="spinner-border spinner-border-sm me-1"></span>{{ t('admin.contacts_send_reply') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { Modal } from 'bootstrap'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()

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
    const { data, error } = await supabase.from('contacts').select('*').order('created_at', { ascending: false })
    if (!error) contacts.value = data
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
    const { error } = await supabase.from('contacts').update({ is_read: true }).eq('id', id)
    if (!error) {
      const c = contacts.value.find(c => c.id === id)
      if (c) c.is_read = true
    }
  } catch (e) { console.error(e) }
}

function openReply(item) {
  replying.value = { ...item }
  replyMessage.value = ''
  bsReplyModal.show()
}

async function sendReply() {
  if (!replyMessage.value.trim()) return alert(t('admin.contacts_enter_message'))
  sendingReply.value = true
  try {
    const { error } = await supabase.from('contacts').update({ is_replied: true, reply_message: replyMessage.value }).eq('id', replying.value.id)
    if (error) throw error
    bsReplyModal.hide()
    alert(t('admin.contacts_reply_sent'))
  } catch (e) { alert(t('admin.contacts_error_sending') + ' ' + (e.message || e)) }
  sendingReply.value = false
}

async function deleteItem(id) {
  if (!confirm(t('admin.contacts_confirm_delete'))) return
  try {
    const { error } = await supabase.from('contacts').delete().eq('id', id)
    if (error) throw error
    await fetchAll()
  } catch (e) { alert(t('admin.contacts_error_deleting')) }
}
</script>

<style scoped>
.cursor-pointer { cursor: pointer; }
</style>
