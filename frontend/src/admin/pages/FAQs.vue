<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-question-circle me-2"></i>{{ t('admin.faqs_title') }}</h4>
      <button class="btn btn-primary" @click="openModal()"><i class="bi bi-plus-lg me-1"></i>{{ t('admin.faqs_new') }}</button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>{{ t('admin.faqs_question') }}</th><th>{{ t('admin.faqs_answer') }}</th><th>{{ t('admin.faqs_category') }}</th><th>{{ t('admin.faqs_status') }}</th><th>{{ t('admin.faqs_order') }}</th><th>{{ t('admin.faqs_actions') }}</th>
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
        <button class="btn-icon btn-edit" @click="openModal(item)" :title="t('common.edit')"><i class="bi bi-pencil-square"></i></button>
        <button class="btn-icon btn-delete" @click="deleteItem(item.id)" :title="t('common.delete')"><i class="bi bi-trash3"></i></button>
            </td>
          </tr>
          <tr v-if="!faqs.length"><td colspan="6" class="text-center text-muted py-4">{{ t('admin.faqs_empty') }}</td></tr>
        </tbody>
      </table>
    </div>

    <div class="modal fade" id="faqModal" tabindex="-1" ref="modalRef">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ form.id ? t('admin.faqs_edit') : t('admin.faqs_new_faq') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">{{ t('admin.faqs_question') }} *</label>
              <input v-model="form.question" type="text" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">{{ t('admin.faqs_answer') }} *</label>
              <textarea v-model="form.answer" class="form-control" rows="5" required></textarea>
            </div>
            <div class="row mb-3">
              <div class="col">
                <label class="form-label">{{ t('admin.faqs_category') }}</label>
                <input v-model="form.category" type="text" class="form-control" placeholder="e.g. Pricing, Services">
              </div>
              <div class="col">
                <label class="form-label">{{ t('admin.faqs_status') }}</label>
                <select v-model="form.status" class="form-select">
                  <option value="published">{{ t('admin.testimonials_published') }}</option>
                  <option value="draft">{{ t('admin.testimonials_draft') }}</option>
                </select>
              </div>
              <div class="col">
                <label class="form-label">{{ t('admin.faqs_order') }}</label>
                <input v-model.number="form.order_by" type="number" class="form-control" min="0">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ t('common.cancel') }}</button>
            <button type="button" class="btn btn-primary" @click="save" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>{{ t('common.save') }}
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
import { useConfirm } from '@/composables/useConfirm'

const { t } = useI18n()
const toast = useToast()
const { confirm } = useConfirm()

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
    const { data, error } = await supabase.from('faqs').select('*').order('order_by', { ascending: true })
    if (!error) faqs.value = data
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
  if (!form.question || !form.answer) { toast.warning(t('admin.faqs_required')); return }
  saving.value = true
  try {
    if (form.id) {
      const { error } = await supabase.from('faqs').update({ question: form.question, answer: form.answer, category: form.category, status: form.status, order_by: form.order_by }).eq('id', form.id)
      if (error) throw error
    } else {
      const { error } = await supabase.from('faqs').insert({ question: form.question, answer: form.answer, category: form.category, status: form.status, order_by: form.order_by })
      if (error) throw error
    }
    bsModal.hide()
    await fetchAll()
  } catch (e) { toast.error(t('admin.faqs_error_saving') + ' ' + (e.message || e)) }
  saving.value = false
}

async function deleteItem(id) {
  if (!await confirm({ title: 'Confirmar eliminação', message: t('admin.faqs_confirm_delete'), type: 'danger', confirmText: 'Eliminar', cancelText: 'Cancelar' })) return
  try {
    const { error } = await supabase.from('faqs').delete().eq('id', id)
    if (error) throw error
    await fetchAll()
  } catch (e) { toast.error(t('admin.faqs_error_deleting')) }
}
</script>
