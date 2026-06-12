<template>
  <div class="crud-page">
    <div class="page-header">
      <div>
        <router-link to="/cotacoes" class="back-link">
          <i class="bi bi-arrow-left"></i> Voltar
        </router-link>
        <h1 class="page-title">{{ isEdit ? 'Editar Cotação' : 'Nova Cotação' }}</h1>
      </div>
    </div>

    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

    <form @submit.prevent="handleSubmit" class="card">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Origem *</label>
            <input v-model="form.origin" type="text" class="form-control" :class="{'is-invalid': errors.origin}" required>
            <div class="invalid-feedback">{{ errors.origin }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Destino *</label>
            <input v-model="form.destination" type="text" class="form-control" :class="{'is-invalid': errors.destination}" required>
            <div class="invalid-feedback">{{ errors.destination }}</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tipo</label>
            <select v-model="form.type" class="form-select">
              <option value="maritimo">Marítimo</option>
              <option value="aereo">Aéreo</option>
              <option value="terrestre">Terrestre</option>
              <option value="ferroviario">Ferroviário</option>
              <option value="multimodal">Multimodal</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Estado</label>
            <select v-model="form.status" class="form-select">
              <option value="pendente">Pendente</option>
              <option value="aprovada">Aprovada</option>
              <option value="rejeitada">Rejeitada</option>
              <option value="expirada">Expirada</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Peso (kg)</label>
            <input v-model="form.weight" type="number" step="0.01" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Valor estimado</label>
            <input v-model="form.estimated_value" type="number" step="0.01" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Moeda</label>
            <select v-model="form.currency" class="form-select">
              <option value="AOA">AOA</option>
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Válido até</label>
            <input v-model="form.valid_until" type="date" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Referência</label>
            <input :value="form.reference || 'Gerada automaticamente'" type="text" class="form-control" disabled>
          </div>
          <div class="col-12">
            <label class="form-label">Descrição</label>
            <textarea v-model="form.description" rows="3" class="form-control"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Notas</label>
            <textarea v-model="form.notes" rows="2" class="form-control"></textarea>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <router-link to="/cotacoes" class="btn btn-outline-secondary">Cancelar</router-link>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
          {{ isEdit ? 'Atualizar' : 'Criar cotação' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from '@/stores/authStore'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const isEdit = computed(() => !!route.params.id)
const form = reactive({ reference: '', origin: '', destination: '', type: 'maritimo', status: 'pendente', weight: 0, estimated_value: 0, currency: 'AOA', valid_until: '', description: '', notes: '' })
const errors = ref({})
const errorMessage = ref('')
const saving = ref(false)

const authHeader = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } })

onMounted(async () => {
  if (isEdit.value) {
    try {
      const r = await axios.get(`/api/cotacoes/${route.params.id}`, authHeader())
      if (r.data.success) Object.assign(form, r.data.data.cotacao)
    } catch (e) { errorMessage.value = 'Erro ao carregar' }
  }
})

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''
  saving.value = true
  try {
    if (isEdit.value) {
      await axios.put(`/api/cotacoes/${route.params.id}`, form, authHeader())
    } else {
      await axios.post('/api/cotacoes', form, authHeader())
    }
    router.push('/cotacoes')
  } catch (error) {
    const data = error.response?.data?.data || {}
    if (Object.keys(data).length) errors.value = data
    else errorMessage.value = error.response?.data?.message || 'Erro ao guardar'
  } finally { saving.value = false }
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
