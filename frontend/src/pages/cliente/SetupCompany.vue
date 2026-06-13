<template>
  <div class="setup-page py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card shadow-sm">
            <div class="card-body p-5">
              <div class="text-center mb-4">
                <h2 class="mb-2">Configurar Empresa</h2>
                <p class="text-muted mb-0">Bem-vindo, {{ authStore.user?.name }}. Preencha os dados da sua empresa para começar a usar o dashboard.</p>
              </div>

              <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

              <form @submit.prevent="handleSubmit" novalidate>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="company_name" class="form-label">Nome da empresa *</label>
                    <input type="text" id="company_name" class="form-control" :class="{'is-invalid': errors.company_name}" v-model="form.company_name" required>
                    <div class="invalid-feedback">{{ errors.company_name }}</div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="nif" class="form-label">NIF</label>
                    <input type="text" id="nif" class="form-control" v-model="form.nif" placeholder="Opcional">
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Logotipo</label>
                  <div class="logo-uploader">
                    <div class="logo-preview" :class="{ 'has-image': logoPreview }">
                      <img v-if="logoPreview" :src="resolveLogo(logoPreview)" alt="Logo" />
                      <span v-else class="placeholder">Sem logotipo</span>
                    </div>
                    <div class="logo-actions">
                      <label class="btn btn-outline-primary btn-sm mb-2">
                        Escolher imagem
                        <input type="file" accept="image/*" @change="onLogoChange" hidden>
                      </label>
                      <button v-if="logoFile" type="button" class="btn btn-sm btn-success me-2" :disabled="uploadingLogo" @click="handleLogoUpload">
                        {{ uploadingLogo ? 'A enviar...' : 'Carregar logotipo' }}
                      </button>
                      <small class="text-muted d-block">JPG, PNG, WEBP, GIF ou SVG. Máx 3MB.</small>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="address" class="form-label">Endereço *</label>
                  <input type="text" id="address" class="form-control" :class="{'is-invalid': errors.address}" v-model="form.address" required>
                  <div class="invalid-feedback">{{ errors.address }}</div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Número de telefone *</label>
                    <input type="tel" id="phone" class="form-control" :class="{'is-invalid': errors.phone}" v-model="form.phone" required>
                    <div class="invalid-feedback">{{ errors.phone }}</div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" class="form-control" :class="{'is-invalid': errors.email}" v-model="form.email" required>
                    <div class="invalid-feedback">{{ errors.email }}</div>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="service" class="form-label">Serviço *</label>
                  <input type="text" id="service" class="form-control" :class="{'is-invalid': errors.service}" v-model="form.service" placeholder="Ex.: Logística, Desembaraço Aduaneiro" required>
                  <div class="invalid-feedback">{{ errors.service }}</div>
                </div>

                <div class="mb-4">
                  <label for="case_description" class="form-label">Descrição do caso *</label>
                  <textarea id="case_description" rows="4" class="form-control" :class="{'is-invalid': errors.case_description}" v-model="form.case_description" placeholder="Descreva brevemente o motivo do seu contacto ou o que pretende." required></textarea>
                  <div class="invalid-feedback">{{ errors.case_description }}</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-outline-secondary" @click="handleLogout">Sair</button>
                  <button type="submit" class="btn btn-primary" :disabled="loading">
                    <span v-if="loading">A guardar...</span>
                    <span v-else>Concluir configuração</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useCompanyStore } from '@/stores/companyStore'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()
const companyStore = useCompanyStore()

const resolveLogo = (logo) => {
  if (!logo) return ''
  if (logo.startsWith('http') || logo.startsWith('data:')) return logo
  const base = axios.defaults.baseURL || ''
  return base + logo
}

const form = reactive({
  company_name: '',
  nif: '',
  address: '',
  phone: '',
  email: '',
  service: '',
  case_description: ''
})

const errors = ref({})
const errorMessage = ref('')
const loading = ref(false)
const logoFile = ref(null)
const logoPreview = ref('')
const uploadingLogo = ref(false)

onMounted(async () => {
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }
  await companyStore.fetch()
  if (companyStore.company) {
    Object.assign(form, {
      company_name: companyStore.company.company_name || '',
      nif: companyStore.company.nif || '',
      address: companyStore.company.address || '',
      phone: companyStore.company.phone || '',
      email: companyStore.company.email || '',
      service: companyStore.company.service || '',
      case_description: companyStore.company.case_description || '',
    })
    if (companyStore.company.logo) {
      logoPreview.value = companyStore.company.logo
    }
  }
})

const onLogoChange = (e) => {
  const file = e.target.files[0]
  if (!file) return
  logoFile.value = file
  const reader = new FileReader()
  reader.onload = (ev) => {
    logoPreview.value = ev.target.result
  }
  reader.readAsDataURL(file)
}

const handleLogoUpload = async () => {
  if (!logoFile.value) return
  uploadingLogo.value = true
  const result = await companyStore.uploadLogo(logoFile.value)
  uploadingLogo.value = false
  if (result.success) {
    logoFile.value = null
  } else {
    errorMessage.value = result.error
  }
}

const handleSubmit = async () => {
  errors.value = {}
  errorMessage.value = ''
  loading.value = true
  const result = await companyStore.save({ ...form })
  loading.value = false
  if (result.success) {
    await authStore.getProfile()
    router.push('/dashboard')
  } else {
    errorMessage.value = result.error
    if (result.fields) errors.value = result.fields
  }
}

const handleLogout = () => {
  authStore.logout()
  companyStore.clear()
  router.push('/login')
}
</script>

<style scoped>
.setup-page {
  background: #f5f7fa;
  min-height: 100vh;
}

.card {
  border: none;
  border-radius: 12px;
}

.logo-uploader {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.logo-preview {
  width: 120px;
  height: 120px;
  border: 2px dashed #d0d5dd;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  overflow: hidden;
}

.logo-preview img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.logo-preview.has-image {
  border-style: solid;
  border-color: #007bff;
}

.placeholder {
  color: #98a2b3;
  font-size: 0.9rem;
}

.logo-actions {
  flex: 1;
  min-width: 200px;
}
</style>
