<template>
  <div class="crud-page">
    <div class="page-header">
      <div>
        <router-link to="/embarques" class="back-link">
          <i class="bi bi-arrow-left"></i> {{ t('cliente.embarques_back') }}
        </router-link>
        <h1 class="page-title">{{ isEdit ? t('cliente.embarques_edit_title') : t('cliente.embarques_new_title') }}</h1>
      </div>
    </div>

    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
    <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>

    <form @submit.prevent="handleSubmit" class="card">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ t('cliente.embarques_origin') }} *</label>
            <input v-model="form.origin" type="text" class="form-control" :class="{'is-invalid': errors.origin}" required>
            <div class="invalid-feedback">{{ errors.origin }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ t('cliente.embarques_destination') }} *</label>
            <input v-model="form.destination" type="text" class="form-control" :class="{'is-invalid': errors.destination}" required>
            <div class="invalid-feedback">{{ errors.destination }}</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ t('cliente.embarques_type') }}</label>
            <select v-model="form.type" class="form-select">
              <option value="maritimo">{{ t('cliente.embarques_type_maritimo') }}</option>
              <option value="aereo">{{ t('cliente.embarques_type_aereo') }}</option>
              <option value="terrestre">{{ t('cliente.embarques_type_terrestre') }}</option>
              <option value="ferroviario">{{ t('cliente.embarques_type_ferroviario') }}</option>
              <option value="multimodal">{{ t('cliente.embarques_type_multimodal') }}</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ t('cliente.embarques_status') }}</label>
            <select v-model="form.status" class="form-select">
              <option value="pendente">{{ t('cliente.embarques_status_pendente') }}</option>
              <option value="em_transito">{{ t('cliente.embarques_status_em_transito') }}</option>
              <option value="entregue">{{ t('cliente.embarques_status_entregue') }}</option>
              <option value="cancelado">{{ t('cliente.embarques_status_cancelado') }}</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ t('cliente.embarques_tracking_label') }}</label>
            <input :value="form.tracking_number || t('cliente.embarques_auto_tracking')" type="text" class="form-control" disabled>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ t('cliente.embarques_weight') }}</label>
            <input v-model="form.weight" type="number" step="0.01" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ t('cliente.embarques_volume') }}</label>
            <input v-model="form.volume" type="number" step="0.0001" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ t('cliente.embarques_declared_value') }}</label>
            <input v-model="form.declared_value" type="number" step="0.01" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ t('cliente.embarques_currency') }}</label>
            <select v-model="form.currency" class="form-select">
              <option value="AOA">AOA</option>
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ t('cliente.embarques_ship_date') }}</label>
            <input v-model="form.ship_date" type="date" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ t('cliente.embarques_delivery_date') }}</label>
            <input v-model="form.delivery_date" type="date" class="form-control">
          </div>
          <div class="col-12">
            <label class="form-label">{{ t('cliente.embarques_description') }}</label>
            <textarea v-model="form.description" rows="3" class="form-control" :placeholder="t('cliente.embarques_desc_placeholder')"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">{{ t('cliente.embarques_notes') }}</label>
            <textarea v-model="form.notes" rows="2" class="form-control"></textarea>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <router-link to="/embarques" class="btn btn-outline-secondary">{{ t('cliente.embarques_cancel') }}</router-link>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
          {{ isEdit ? t('cliente.embarques_update') : t('cliente.embarques_create') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'
import { useI18n } from '@/composables/useI18n'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const { t } = useI18n()

const isEdit = computed(() => !!route.params.id)
const form = reactive({ tracking_number: '', origin: '', destination: '', type: 'maritimo', status: 'pendente', weight: 0, volume: 0, declared_value: 0, currency: 'AOA', ship_date: '', delivery_date: '', description: '', notes: '' })
const errors = ref({})
const errorMessage = ref('')
const successMessage = ref('')
const saving = ref(false)

onMounted(async () => {
  if (isEdit.value) {
    try {
      const { data, error } = await supabase.from('embarques').select('*').eq('id', route.params.id).single()
      if (error) throw error
      if (data) Object.assign(form, data)
    } catch (e) {
      errorMessage.value = t('cliente.embarques_error_loading')
    }
  }
})

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''
  successMessage.value = ''
  saving.value = true
  try {
    if (isEdit.value) {
      const { error } = await supabase.from('embarques').update(form).eq('id', route.params.id)
      if (error) throw error
      successMessage.value = t('cliente.embarques_success_updated')
    } else {
      const userId = authStore.user?.id
      const { error } = await supabase.from('embarques').insert({ ...form, user_id: userId })
      if (error) throw error
      successMessage.value = t('cliente.embarques_success_created')
      setTimeout(() => router.push('/embarques'), 1000)
    }
  } catch (error) {
    errorMessage.value = error.message || t('cliente.embarques_error_saving')
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.crud-page { padding: 1.5rem; }
.page-header { margin-bottom: 1.5rem; }
.back-link { color: #64748b; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem; }
.back-link:hover { color: #2563eb; }
.page-title { font-size: 1.75rem; font-weight: 700; color: #0f172a; margin: 0; }

.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.card-body { padding: 2rem; }
.card-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 1rem 2rem; display: flex; justify-content: flex-end; gap: 0.5rem; }

.form-label { font-weight: 500; color: #334155; font-size: 0.9rem; }
.form-control, .form-select { border: 2px solid #e2e8f0; border-radius: 8px; padding: 0.6rem 0.75rem; }
.form-control:focus, .form-select:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
</style>
