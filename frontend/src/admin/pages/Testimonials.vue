<template>
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0"><i class="bi bi-chat-quote me-2"></i>Testimonials</h4>
      <button class="btn btn-primary" @click="openModal()"><i class="bi bi-plus-lg me-1"></i>New</button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary"></div>
    </div>

    <div v-else class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Name</th><th>Position</th><th>Company</th><th>Message</th><th>Rating</th><th>Status</th><th>Order</th><th>Actions</th>
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
              <button class="btn btn-sm btn-outline-primary me-1" @click="openModal(item)"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-danger" @click="deleteItem(item.id)"><i class="bi bi-trash"></i></button>
            </td>
          </tr>
          <tr v-if="!testimonials.length"><td colspan="8" class="text-center text-muted py-4">No testimonials found.</td></tr>
        </tbody>
      </table>
    </div>

    <div class="modal fade" id="testimonialModal" tabindex="-1" ref="modalRef">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ form.id ? 'Edit' : 'New' }} Testimonial</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Name *</label>
              <input v-model="form.name" type="text" class="form-control" required>
            </div>
            <div class="row mb-3">
              <div class="col">
                <label class="form-label">Position</label>
                <input v-model="form.position" type="text" class="form-control">
              </div>
              <div class="col">
                <label class="form-label">Company</label>
                <input v-model="form.company" type="text" class="form-control">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Message *</label>
              <textarea v-model="form.message" class="form-control" rows="4" required></textarea>
            </div>
            <div class="row mb-3">
              <div class="col">
                <label class="form-label">Rating</label>
                <select v-model="form.rating" class="form-select">
                  <option v-for="r in 5" :key="r" :value="r">{{ r }} star{{ r > 1 ? 's' : '' }}</option>
                </select>
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
            <div class="mb-3">
              <label class="form-label">Photo</label>
              <input type="file" class="form-control" accept="image/*" @change="onFileChange">
              <img v-if="form.photo_url" :src="form.photo_url" class="mt-2 rounded" style="height:60px">
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
import { supabase } from '@/lib/supabase'
import { Modal } from 'bootstrap'

const testimonials = ref([])
const loading = ref(true)
const saving = ref(false)
const modalRef = ref(null)
let bsModal = null
const photoFile = ref(null)

const defaultForm = { id: null, name: '', position: '', company: '', message: '', rating: 5, status: 'published', order_by: 0, photo_url: '' }
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
  photoFile.value = null
  if (item) {
    Object.assign(form, { ...item })
  } else {
    Object.assign(form, { ...defaultForm })
  }
  bsModal.show()
}

function onFileChange(e) {
  const file = e.target.files[0]
  if (file) photoFile.value = file
}

async function save() {
  if (!form.name || !form.message) return alert('Name and message are required.')
  saving.value = true
  try {
    let photoUrl = form.photo_url
    if (photoFile.value) {
      const fileExt = photoFile.value.name.split('.').pop()
      const fileName = `testimonials/${Date.now()}.${fileExt}`
      const { data, error: uploadError } = await supabase.storage.from('uploads').upload(fileName, photoFile.value)
      if (uploadError) throw uploadError
      const { data: urlData } = supabase.storage.from('uploads').getPublicUrl(fileName)
      photoUrl = urlData.publicUrl
    }

    const payload = {
      name: form.name,
      position: form.position,
      company: form.company,
      message: form.message,
      rating: form.rating,
      status: form.status,
      order_by: form.order_by,
      photo_url: photoUrl
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
  } catch (e) { alert('Error saving: ' + (e.message || e)) }
  saving.value = false
}

async function deleteItem(id) {
  if (!confirm('Delete this testimonial?')) return
  try {
    const { error } = await supabase.from('testimonials').delete().eq('id', id)
    if (error) throw error
    await fetchAll()
  } catch (e) { alert('Error deleting.') }
}
</script>
