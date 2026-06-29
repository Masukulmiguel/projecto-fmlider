<template>
  <div class="crud-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">{{ t('cliente.contactos_title') }}</h1>
        <p class="text-muted mb-0">{{ t('cliente.contactos_subtitle') }}</p>
      </div>
      <button class="btn btn-primary" @click="openForm()">
        <i class="bi bi-plus-lg me-1"></i> {{ t('cliente.contactos_new') }}
      </button>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="search-box">
          <i class="bi bi-search"></i>
          <input v-model="filters.q" type="text" :placeholder="t('cliente.contactos_search')" @input="debounceSearch">
        </div>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
        <div v-else-if="items.length === 0" class="empty-state">
          <i class="bi bi-person-rolodex"></i>
          <p>{{ t('cliente.contactos_empty') }}</p>
          <button class="btn btn-primary btn-sm" @click="openForm()">
            <i class="bi bi-plus-lg me-1"></i> {{ t('cliente.contactos_add_first') }}
          </button>
        </div>
        <div v-else class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>{{ t('cliente.contactos_col_name') }}</th>
                <th>{{ t('cliente.contactos_col_company') }}</th>
                <th>{{ t('cliente.contactos_col_email') }}</th>
                <th>{{ t('cliente.contactos_col_phone') }}</th>
                <th>{{ t('cliente.contactos_col_position') }}</th>
                <th class="text-end">{{ t('cliente.contactos_col_actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar">{{ initials(item.name) }}</div>
                    <div class="fw-medium">{{ item.name }}</div>
                  </div>
                </td>
                <td>{{ item.company || '—' }}</td>
                <td><a v-if="item.email" :href="`mailto:${item.email}`" class="text-decoration-none">{{ item.email }}</a><span v-else>—</span></td>
                <td><a v-if="item.phone" :href="`tel:${item.phone}`" class="text-decoration-none">{{ item.phone }}</a><span v-else>—</span></td>
                <td>{{ item.position || '—' }}</td>
                <td class="text-end">
                  <div class="action-buttons">
                    <button class="btn-icon btn-edit" @click="openForm(item)" :title="t('cliente.contactos_edit')">
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn-icon btn-delete" @click="confirmDelete(item)" :title="t('cliente.contactos_delete')">
                      <i class="bi bi-trash3"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="modal fade show d-block" v-if="showForm" @click.self="closeForm" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editing ? t('cliente.contactos_edit_title') : t('cliente.contactos_new_title') }}</h5>
            <button type="button" class="btn-close" @click="closeForm"></button>
          </div>
          <form @submit.prevent="handleSubmit" novalidate>
            <div class="modal-body">
              <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">{{ t('cliente.contactos_form_name') }} *</label>
                  <input v-model="form.name" type="text" class="form-control" :class="{'is-invalid': errors.name}" required>
                  <div class="invalid-feedback">{{ errors.name }}</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ t('cliente.contactos_form_company') }}</label>
                  <input v-model="form.company" type="text" class="form-control">
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ t('cliente.contactos_form_email') }}</label>
                  <input v-model="form.email" type="email" class="form-control" :class="{'is-invalid': errors.email}">
                  <div class="invalid-feedback">{{ errors.email }}</div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ t('cliente.contactos_form_phone') }}</label>
                  <input v-model="form.phone" type="text" class="form-control">
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ t('cliente.contactos_form_position') }}</label>
                  <input v-model="form.position" type="text" class="form-control">
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ t('cliente.contactos_form_address') }}</label>
                  <input v-model="form.address" type="text" class="form-control">
                </div>
                <div class="col-12">
                  <label class="form-label">{{ t('cliente.contactos_form_notes') }}</label>
                  <textarea v-model="form.notes" rows="3" class="form-control"></textarea>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" @click="closeForm">{{ t('cliente.contactos_cancel') }}</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                {{ editing ? t('cliente.contactos_update') : t('cliente.contactos_create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-if="showForm"></div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'
import { useI18n } from '@/composables/useI18n'
import { useToast } from '@/composables/useToast'
import { useConfirm } from '@/composables/useConfirm'

const { t } = useI18n()
const authStore = useAuthStore()
const toast = useToast()
const { confirm } = useConfirm()
const items = ref([])
const loading = ref(false)
const saving = ref(false)
const showForm = ref(false)
const editing = ref(null)
const filters = reactive({ q: '' })
const form = reactive({ name: '', company: '', email: '', phone: '', position: '', address: '', notes: '' })
const errors = ref({})
const errorMessage = ref('')
let searchTimer = null

const fetchData = async () => {
  loading.value = true
  try {
    const userId = authStore.user?.id
    if (!userId) return

    let query = supabase.from('contactos').select('*').eq('user_id', userId)
    if (filters.q) {
      query = query.or(`name.ilike.%${filters.q}%,company.ilike.%${filters.q}%,email.ilike.%${filters.q}%`)
    }
    const { data, error } = await query
    if (!error) items.value = data || []
  } finally {
    loading.value = false
  }
}

const debounceSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(fetchData, 300)
}

const openForm = (item = null) => {
  editing.value = item
  if (item) Object.assign(form, item)
  else Object.assign(form, { name: '', company: '', email: '', phone: '', position: '', address: '', notes: '' })
  errorMessage.value = ''
  errors.value = {}
  showForm.value = true
}

const closeForm = () => { showForm.value = false; editing.value = null }

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''
  saving.value = true
  try {
    const userId = authStore.user?.id
    if (editing.value) {
      const { error } = await supabase.from('contactos').update(form).eq('id', editing.value.id)
      if (error) throw error
    } else {
      const { error } = await supabase.from('contactos').insert({ ...form, user_id: userId })
      if (error) throw error
    }
    closeForm()
    await fetchData()
  } catch (error) {
    errorMessage.value = error.message || t('cliente.contactos_error_saving')
  } finally {
    saving.value = false
  }
}

const confirmDelete = async (item) => {
  if (!await confirm({ title: 'Confirmar eliminação', message: `${t('cliente.contactos_confirm_delete')} "${item.name}"?`, type: 'danger', confirmText: 'Eliminar', cancelText: 'Cancelar' })) return
  try {
    const { error } = await supabase.from('contactos').delete().eq('id', item.id)
    if (error) throw error
    await fetchData()
  } catch (e) { toast.error(t('cliente.contactos_error_deleting')) }
}

const initials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
}

onMounted(fetchData)
</script>

<style scoped>
.crud-page { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.page-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; color: #0f172a; }

.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-header { background: white; border-bottom: 1px solid #f1f5f9; padding: 1rem 1.25rem; }
.search-box { position: relative; max-width: 400px; }
.search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
.search-box input { width: 100%; padding: 0.6rem 0.75rem 0.6rem 2.25rem; border: 2px solid #e2e8f0; border-radius: 8px; }
.search-box input:focus { border-color: #2563eb; outline: none; }

.empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }

.avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #2563eb, #7c3aed); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 600; flex-shrink: 0; }
.action-buttons { display: inline-flex; gap: 0.4rem; }

.modal-backdrop { z-index: 1040; }
.modal { z-index: 1050; }
</style>
