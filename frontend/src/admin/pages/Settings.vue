<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0"><i class="bi bi-gear-fill me-2"></i>Configurações</h4>
      <button class="btn btn-primary" @click="saveSettings" :disabled="saving">
        <i class="bi bi-check-lg me-1" v-if="!saving"></i>
        <span class="spinner-border spinner-border-sm me-1" v-else></span>
        {{ saving ? 'A guardar...' : 'Guardar Alterações' }}
      </button>
    </div>

    <div v-if="message" :class="`alert alert-${message.type} alert-dismissible fade show`">
      {{ message.text }}
      <button type="button" class="btn-close" @click="message = null"></button>
    </div>

    <div class="row g-4" v-if="!loading">
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-building me-2"></i>Empresa</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Nome da Empresa</label>
              <input v-model="form.company_name" type="text" class="form-control" placeholder="Nome da empresa">
            </div>
            <div class="mb-3">
              <label class="form-label">Descrição</label>
              <textarea v-model="form.company_description" class="form-control" rows="3" placeholder="Descrição da empresa"></textarea>
            </div>
            <div class="mb-0">
              <label class="form-label">NIF</label>
              <input v-model="form.nif" type="text" class="form-control" placeholder="NIF da empresa">
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-telephone me-2"></i>Contacto</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Telefone</label>
              <input v-model="form.phone" type="text" class="form-control" placeholder="+244 9XX XXX XXX">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input v-model="form.email" type="email" class="form-control" placeholder="email@exemplo.com">
            </div>
            <div class="mb-3">
              <label class="form-label">Morada</label>
              <input v-model="form.address" type="text" class="form-control" placeholder="Morada completa">
            </div>
            <div class="mb-0">
              <label class="form-label">Horário de Funcionamento</label>
              <input v-model="form.working_hours" type="text" class="form-control" placeholder="Seg–Sex 08:00–18:00">
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-share me-2"></i>Redes Sociais</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Facebook</label>
              <input v-model="form.facebook" type="url" class="form-control" placeholder="https://facebook.com/...">
            </div>
            <div class="mb-3">
              <label class="form-label">Instagram</label>
              <input v-model="form.instagram" type="url" class="form-control" placeholder="https://instagram.com/...">
            </div>
            <div class="mb-3">
              <label class="form-label">LinkedIn</label>
              <input v-model="form.linkedin" type="url" class="form-control" placeholder="https://linkedin.com/...">
            </div>
            <div class="mb-0">
              <label class="form-label">WhatsApp</label>
              <input v-model="form.whatsapp" type="text" class="form-control" placeholder="244935141747">
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0"><i class="bi bi-search me-2"></i>SEO</h6>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Meta Title</label>
              <input v-model="form.meta_title" type="text" class="form-control" placeholder="Título para motores de busca">
            </div>
            <div class="mb-0">
              <label class="form-label">Meta Description</label>
              <textarea v-model="form.meta_description" class="form-control" rows="3" placeholder="Descrição para motores de busca"></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-5">
      <div class="spinner-border text-primary"></div>
      <p class="mt-3 text-muted">A carregar configurações...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const saving = ref(false)
const message = ref(null)

const form = reactive({
  company_name: '',
  company_description: '',
  nif: '',
  phone: '',
  email: '',
  address: '',
  working_hours: '',
  facebook: '',
  instagram: '',
  linkedin: '',
  whatsapp: '',
  meta_title: '',
  meta_description: ''
})

const getAuthHeaders = () => ({
  headers: { Authorization: `Bearer ${localStorage.getItem('supabase_access_token')}` }
})

const fetchSettings = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/settings')
    if (data.success && data.settings) {
      Object.keys(form).forEach(key => {
        if (data.settings[key] !== undefined) {
          form[key] = data.settings[key] || ''
        }
      })
    }
  } catch (e) {
    message.value = { type: 'danger', text: 'Erro ao carregar configurações.' }
  } finally {
    loading.value = false
  }
}

const saveSettings = async () => {
  saving.value = true
  message.value = null
  try {
    const { data } = await axios.put('/api/admin/settings', { settings: { ...form } }, getAuthHeaders())
    if (data.success) {
      message.value = { type: 'success', text: 'Configurações guardadas com sucesso!' }
    } else {
      message.value = { type: 'danger', text: data.message || 'Erro ao guardar configurações.' }
    }
  } catch (e) {
    message.value = { type: 'danger', text: e.response?.data?.message || 'Erro ao guardar configurações.' }
  } finally {
    saving.value = false
  }
}

onMounted(fetchSettings)
</script>
