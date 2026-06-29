<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-chat-quote me-2"></i>{{ t('admin.testimonials_title') }}</h4>
      <button class="btn btn-primary" @click="openModal()"><i class="bi bi-plus-lg me-1"></i>{{ t('admin.testimonials_new') }}</button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>{{ t('admin.testimonials_name') }}</th><th>{{ t('admin.testimonials_position') }}</th><th>{{ t('admin.testimonials_company') }}</th><th>{{ t('admin.testimonials_message') }}</th><th>{{ t('admin.testimonials_rating') }}</th><th>{{ t('admin.testimonials_status') }}</th><th>{{ t('admin.testimonials_order') }}</th><th>{{ t('admin.testimonials_actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in testimonials" :key="item.id">
            <td>{{ item.name }}</td>
            <td>{{ item.position }}</td>
            <td>{{ item.company }}</td>
            <td class="text-truncate" style="max-width:200px">{{ item.message }}</td>
            <td><span v-for="s in 5" :key="s" class="text-warning"><i :class="s <= item.rating ? 'bi bi-star-fill' : 'bi bi-star'"></i></span></td>
            <td><span class="badge" :class="item.status === 'published' ? 'bg-success' : 'bg-secondary'">{{ item.status }}</span></td>
            <td>{{ item.order_by }}</td>
            <td>
        <button class="btn-icon btn-edit" @click="openModal(item)" :title="t('common.edit')"><i class="bi bi-pencil-square"></i></button>
        <button class="btn-icon btn-delete" @click="deleteItem(item.id)" :title="t('common.delete')"><i class="bi bi-trash3"></i></button>
            </td>
          </tr>
          <tr v-if="!testimonials.length"><td colspan="8" class="text-center text-muted py-4">{{ t('admin.testimonials_empty') }}</td></tr>
        </tbody>
      </table>
    </div>

    <div class="modal fade" id="testimonialModal" tabindex="-1" ref="modalRef">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ form.id ? t('admin.testimonials_edit') : t('admin.testimonials_new_testimonial') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">{{ t('admin.testimonials_name') }} *</label>
              <input v-model="form.name" type="text" class="form-control" required>
            </div>
            <div class="row mb-3">
              <div class="col">
                <label class="form-label">{{ t('admin.testimonials_position') }}</label>
                <input v-model="form.position" type="text" class="form-control">
              </div>
              <div class="col">
                <label class="form-label">{{ t('admin.testimonials_company') }}</label>
                <input v-model="form.company" type="text" class="form-control">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">{{ t('admin.testimonials_message') }} *</label>
              <textarea v-model="form.message" class="form-control" rows="4" required></textarea>
            </div>
            <div class="row mb-3">
              <div class="col">
                <label class="form-label">{{ t('admin.testimonials_rating') }}</label>
                <select v-model="form.rating" class="form-select">
                  <option v-for="r in 5" :key="r" :value="r">{{ r }} star{{ r > 1 ? 's' : '' }}</option>
                </select>
              </div>
              <div class="col">
                <label class="form-label">{{ t('admin.testimonials_status') }}</label>
                <select v-model="form.status" class="form-select">
                  <option value="published">{{ t('admin.testimonials_published') }}</option>
                  <option value="draft">{{ t('admin.testimonials_draft') }}</option>
                </select>
              </div>
              <div class="col">
                <label class="form-label">{{ t('admin.testimonials_order') }}</label>
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

const testimonials = ref([])
const loading = ref(true)
const saving = ref(false)
const modalRef = ref(null)
let bsModal = null

const defaultForm = { id: null, name: '', position: '', company: '', message: '', rating: 5, status: 'published', order_by: 0 }
const form = reactive({ ...defaultForm })

onMounted(async () => {
  bsModal = new Modal(modalRef.value)
  await fetchAll()
})

async function fetchAll() {
  loading.value = true
  try {
    const { data, error } = await supabase.from('testimonials').select('*').order('order_by', { ascending: true })
    if (!error) testimonials.value = data
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
  if (!form.name || !form.message) { toast.warning(t('admin.testimonials_required')); return }
  saving.value = true
  try {
    const payload = {
      name: form.name,
      position: form.position,
      company: form.company,
      message: form.message,
      rating: form.rating,
      status: form.status,
      order_by: form.order_by
    }

    if (form.id) {
      const { error } = await supabase.from('testimonials').update(payload).eq('id', form.id)
      if (error) throw error
    } else {
      const { error } = await supabase.from('testimonials').insert(payload)
      if (error) throw error
    }
    bsModal.hide()
    await fetchAll()
  } catch (e) { toast.error(t('admin.testimonials_error_saving') + ' ' + (e.message || e)) }
  saving.value = false
}

async function deleteItem(id) {
  if (!await confirm({ title: 'Confirmar eliminação', message: t('admin.testimonials_confirm_delete'), type: 'danger', confirmText: 'Eliminar', cancelText: 'Cancelar' })) return
  try {
    const { error } = await supabase.from('testimonials').delete().eq('id', id)
    if (error) throw error
    await fetchAll()
  } catch (e) { toast.error(t('admin.testimonials_error_deleting')) }
}
</script>
