<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
      <div>
        <h4 class="mb-1 fw-semibold">{{ t('admin.faqs_title') }}</h4>
        <p class="text-muted mb-0 small">Perguntas frequentes do site</p>
      </div>
      <button class="btn-add" @click="openModal()">
        <i class="bi bi-plus-lg"></i>
        <span>{{ t('admin.faqs_new') }}</span>
      </button>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner-border text-primary"></div>
    </div>

    <template v-else>
      <div class="section-group">
        <div
          v-for="(item, index) in faqs"
          :key="item.id"
          class="list-row"
        >
          <div class="row-order">{{ item.order_by }}</div>
          <div class="row-question">
            <div class="question-text">{{ item.question }}</div>
            <div class="question-answer">{{ item.answer }}</div>
          </div>
          <div class="row-category">
            <span class="category-badge">{{ item.category || 'Geral' }}</span>
          </div>
          <div class="row-status">
            <span class="status-dot" :class="{ active: item.status === 'published' }"></span>
            <span class="status-label">{{ item.status === 'published' ? t('admin.faqs_published') : t('admin.faqs_draft') }}</span>
          </div>
          <div class="row-actions">
            <button class="action-btn edit" @click="openModal(item)" :title="t('common.edit')">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="action-btn delete" @click="openDeleteModal(item)" :title="t('common.delete')">
              <i class="bi bi-trash3"></i>
            </button>
          </div>
        </div>

        <div v-if="!faqs.length" class="empty-state">
          <i class="bi bi-question-circle"></i>
          <p>{{ t('admin.faqs_empty') }}</p>
        </div>
      </div>
    </template>

    <div class="modal fade" id="faqModal" tabindex="-1" ref="modalRef">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ form.id ? t('admin.faqs_edit') : t('admin.faqs_new_faq') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label class="form-label">{{ t('admin.faqs_question') }} *</label>
              <input v-model="form.question" type="text" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">{{ t('admin.faqs_answer') }} *</label>
              <textarea v-model="form.answer" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">{{ t('admin.faqs_category') }}</label>
                <input v-model="form.category" type="text" class="form-control" :placeholder="t('admin.faqs_category_placeholder')">
              </div>
              <div class="form-group">
                <label class="form-label">{{ t('admin.faqs_status') }}</label>
                <select v-model="form.status" class="form-select">
                  <option value="published">{{ t('admin.faqs_published') }}</option>
                  <option value="draft">{{ t('admin.faqs_draft') }}</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">{{ t('admin.faqs_order') }}</label>
                <input v-model.number="form.order_by" type="number" class="form-control" min="0">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-cancel" data-bs-dismiss="modal">{{ t('common.cancel') }}</button>
            <button type="button" class="btn-save" @click="save" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
              {{ t('common.save') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" ref="deleteModalRef">
      <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
          <div class="modal-body text-center py-4">
            <div class="delete-icon-circle">
              <i class="bi bi-trash3"></i>
            </div>
            <h6 class="mt-3 mb-2 fw-semibold">{{ t('admin.faqs_confirm_delete') }}</h6>
            <p class="text-muted small mb-0">{{ t('admin.faqs_confirm_delete_message') }}</p>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn-cancel" data-bs-dismiss="modal">{{ t('common.cancel') }}</button>
            <button type="button" class="btn-delete-confirm" @click="confirmDelete" :disabled="deleting">
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
import { ref, reactive, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { Modal } from 'bootstrap'
import { useI18n } from '@/composables/useI18n'
import { useToast } from '@/composables/useToast'

const { t } = useI18n()
const toast = useToast()

const faqs = ref([])
const loading = ref(true)
const saving = ref(false)
const deleting = ref(false)
const modalRef = ref(null)
const deleteModalRef = ref(null)
let bsModal = null
let bsDeleteModal = null
let deleteTargetId = null

const defaultForm = { id: null, question: '', answer: '', category: '', status: 'published', order_by: 0 }
const form = reactive({ ...defaultForm })

onMounted(async () => {
  bsModal = new Modal(modalRef.value)
  bsDeleteModal = new Modal(deleteModalRef.value)
  await fetchAll()
})

async function fetchAll() {
  loading.value = true
  try {
    const { data, error } = await supabase.from('faqs').select('*').order('order_by', { ascending: true })
    if (!error) faqs.value = data
  } catch (e) {
    console.error(e)
  }
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
  if (!form.question || !form.answer) {
    toast.warning(t('admin.faqs_required'))
    return
  }
  saving.value = true
  try {
    const payload = {
      question: form.question,
      answer: form.answer,
      category: form.category,
      status: form.status,
      order_by: form.order_by
    }
    if (form.id) {
      const { error } = await supabase.from('faqs').update(payload).eq('id', form.id)
      if (error) throw error
    } else {
      const { error } = await supabase.from('faqs').insert(payload)
      if (error) throw error
    }
    bsModal.hide()
    await fetchAll()
    toast.success(form.id ? t('admin.faqs_updated') : t('admin.faqs_created'))
  } catch (e) {
    toast.error(t('admin.faqs_error_saving') + ' ' + (e.message || e))
  }
  saving.value = false
}

function openDeleteModal(item) {
  deleteTargetId = item.id
  bsDeleteModal.show()
}

async function confirmDelete() {
  if (!deleteTargetId) return
  deleting.value = true
  try {
    const { error } = await supabase.from('faqs').delete().eq('id', deleteTargetId)
    if (error) throw error
    bsDeleteModal.hide()
    await fetchAll()
    toast.success(t('admin.faqs_deleted'))
  } catch (e) {
    toast.error(t('admin.faqs_error_deleting'))
  }
  deleting.value = false
  deleteTargetId = null
}
</script>

<style scoped>
.btn-add {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-add:hover { background: #1d4ed8; }

.loading-state {
  display: flex;
  justify-content: center;
  padding: 3rem;
}

.section-group {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.list-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.15s;
}
.list-row:last-child { border-bottom: none; }
.list-row:hover { background: #f8fafc; }

.row-order {
  flex-shrink: 0;
  width: 2rem;
  text-align: center;
  font-size: 0.8125rem;
  font-weight: 600;
  color: #94a3b8;
}

.row-question {
  flex: 1;
  min-width: 0;
}
.question-text {
  font-weight: 500;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 400px;
  font-size: 0.9375rem;
}
.question-answer {
  font-size: 0.8125rem;
  color: #94a3b8;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 500px;
  margin-top: 0.125rem;
}

.row-category { flex-shrink: 0; }
.category-badge {
  display: inline-block;
  padding: 0.2rem 0.625rem;
  background: #f1f5f9;
  color: #475569;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 500;
}

.row-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
  min-width: 80px;
}
.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #cbd5e1;
}
.status-dot.active { background: #22c55e; }
.status-label {
  font-size: 0.8125rem;
  color: #64748b;
}

.row-actions {
  display: flex;
  gap: 0.375rem;
  flex-shrink: 0;
}
.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s;
  font-size: 0.8125rem;
}
.action-btn.edit {
  background: #eff6ff;
  color: #3b82f6;
}
.action-btn.edit:hover { background: #dbeafe; }
.action-btn.delete {
  background: #fef2f2;
  color: #ef4444;
}
.action-btn.delete:hover { background: #fee2e2; }

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 3rem;
  color: #94a3b8;
}
.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
}
.empty-state p {
  font-size: 0.9375rem;
}

.form-group { margin-bottom: 1rem; }
.form-group:last-child { margin-bottom: 0; }
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1rem;
}

@media (max-width: 768px) {
  .form-row { grid-template-columns: 1fr; }
  .question-text, .question-answer { max-width: 150px; }
}

.form-label {
  display: block;
  margin-bottom: 0.375rem;
  font-size: 0.8125rem;
  font-weight: 500;
  color: #374151;
}

.form-control, .form-select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.875rem;
  color: #1e293b;
  background: #fff;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.form-control:focus, .form-select:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  outline: none;
}

.btn-cancel {
  padding: 0.5rem 1rem;
  background: #f1f5f9;
  color: #64748b;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-cancel:hover { background: #e2e8f0; }

.btn-save {
  padding: 0.5rem 1.25rem;
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-save:hover { background: #1d4ed8; }
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }

.delete-icon-circle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  background: #fef2f2;
  color: #ef4444;
  border-radius: 50%;
  font-size: 1.25rem;
}

.btn-delete-confirm {
  padding: 0.5rem 1.25rem;
  background: #ef4444;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-delete-confirm:hover { background: #dc2626; }
.btn-delete-confirm:disabled { opacity: 0.6; cursor: not-allowed; }

:deep(.modal-content) {
  border: none;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
}
:deep(.modal-header) {
  border-bottom: 1px solid #f1f5f9;
  padding: 1rem 1.25rem;
}
:deep(.modal-title) {
  font-size: 1rem;
  font-weight: 600;
}
:deep(.modal-body) {
  padding: 1.25rem;
}
:deep(.modal-footer) {
  border-top: 1px solid #f1f5f9;
  padding: 0.75rem 1.25rem;
}
</style>
