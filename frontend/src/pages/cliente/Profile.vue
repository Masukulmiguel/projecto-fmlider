<template>
  <div class="cliente-profile p-4 p-md-5">
    <h2 class="mb-4">Meu Perfil</h2>

    <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="mb-0">Foto de perfil</h5>
          </div>
          <div class="card-body">
            <div class="profile-photo-section">
              <div class="profile-photo-preview">
                <img v-if="photoPreview || authStore.user?.photo" :src="photoPreview || authStore.user?.photo" alt="Foto de perfil">
                <div v-else class="photo-placeholder">
                  <i class="bi bi-person-fill"></i>
                </div>
              </div>
              <div class="profile-photo-actions">
                <label class="btn btn-outline-primary btn-sm mb-2">
                  <i class="bi bi-camera-fill"></i> Alterar foto
                  <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" @change="onPhotoChange" hidden>
                </label>
                <small class="text-muted d-block">JPG, PNG, WEBP ou GIF · máx 3MB</small>
                <div v-if="photoMessage" class="alert mt-2 mb-0 py-2" :class="photoError ? 'alert-danger' : 'alert-success'">
                  {{ photoMessage }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">Dados pessoais</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="saveProfile">
              <div class="mb-3">
                <label class="form-label">Nome de utilizador</label>
                <input type="text" class="form-control" :value="authStore.user?.username" disabled>
              </div>
              <div class="mb-3">
                <label class="form-label">Nome completo *</label>
                <input type="text" class="form-control" v-model="profile.name" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" :value="authStore.user?.email" disabled>
              </div>
              <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="tel" class="form-control" v-model="profile.phone">
              </div>
              <button type="submit" class="btn btn-primary" :disabled="savingProfile">
                {{ savingProfile ? 'A guardar...' : 'Guardar alterações' }}
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Dados da empresa</h5>
            <span v-if="companyStore.company?.logo" class="badge" :class="companyStore.company.is_published ? 'bg-success' : 'bg-secondary'">
              {{ companyStore.company.is_published ? 'Visível no site' : 'Oculto do site' }}
            </span>
          </div>
          <div class="card-body">
            <div class="logo-block mb-4">
              <label class="form-label fw-semibold">Logotipo da empresa</label>
              <p class="text-muted small mb-2">Ao carregar o logotipo, ele aparece automaticamente no carrossel "Nossos Clientes" da página inicial.</p>
              <div class="logo-preview-row">
                <div class="logo-preview">
                  <img v-if="logoPreview" :src="resolveLogo(logoPreview)" alt="Logo">
                  <div v-else class="logo-placeholder">
                    <i class="bi bi-image"></i>
                  </div>
                </div>
                <div class="logo-actions">
                  <label class="btn btn-outline-primary btn-sm mb-2">
                    <i class="bi bi-upload"></i> Carregar logotipo
                    <input type="file" accept="image/*" @change="onLogoChange" hidden>
                  </label>
                  <small class="text-muted d-block">JPG, PNG, WEBP, GIF ou SVG · máx 3MB</small>
                </div>
              </div>
              <div v-if="logoMessage" class="alert mt-2 mb-0 py-2" :class="logoError ? 'alert-danger' : 'alert-success'">
                {{ logoMessage }}
              </div>
            </div>

            <form @submit.prevent="saveCompany">
              <div class="mb-3">
                <label class="form-label">Nome da empresa *</label>
                <input type="text" class="form-control" v-model="company.company_name" required>
              </div>
              <div class="mb-3">
                <label class="form-label">NIF</label>
                <input type="text" class="form-control" v-model="company.nif">
              </div>
              <div class="mb-3">
                <label class="form-label">Endereço *</label>
                <input type="text" class="form-control" v-model="company.address" required>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Telefone *</label>
                  <input type="tel" class="form-control" v-model="company.phone" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Email *</label>
                  <input type="email" class="form-control" v-model="company.email" required>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Serviço *</label>
                <input type="text" class="form-control" v-model="company.service" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Descrição do caso *</label>
                <textarea rows="4" class="form-control" v-model="company.case_description" required></textarea>
              </div>

              <div v-if="companyStore.company?.logo" class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="showInCarousel" :checked="!!companyStore.company.is_published" :disabled="togglingPublish" @change="onTogglePublish">
                <label class="form-check-label" for="showInCarousel">
                  Mostrar o logotipo no carrossel "Nossos Clientes" da página inicial
                </label>
              </div>

              <button type="submit" class="btn btn-primary" :disabled="savingCompany">
                {{ savingCompany ? 'A guardar...' : 'Atualizar empresa' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useCompanyStore } from '@/stores/companyStore'
import axios from 'axios'

const authStore = useAuthStore()
const companyStore = useCompanyStore()

const resolveLogo = (logo) => {
  if (!logo) return ''
  if (logo.startsWith('http') || logo.startsWith('data:')) return logo
  const base = axios.defaults.baseURL || ''
  return base + logo
}

const profile = reactive({ name: '', phone: '' })
const company = reactive({
  company_name: '',
  nif: '',
  address: '',
  phone: '',
  email: '',
  service: '',
  case_description: ''
})

const savingProfile = ref(false)
const savingCompany = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const logoPreview = ref('')
const logoMessage = ref('')
const logoError = ref(false)
const togglingPublish = ref(false)
const photoPreview = ref('')
const photoMessage = ref('')
const photoError = ref(false)

onMounted(async () => {
  await authStore.getProfile()
  profile.name = authStore.user?.name || ''
  profile.phone = authStore.user?.phone || ''

  await companyStore.fetch()
  logoPreview.value = companyStore.company?.logo || ''
  if (companyStore.company) {
    Object.assign(company, {
      company_name: companyStore.company.company_name || '',
      nif: companyStore.company.nif || '',
      address: companyStore.company.address || '',
      phone: companyStore.company.phone || '',
      email: companyStore.company.email || '',
      service: companyStore.company.service || '',
      case_description: companyStore.company.case_description || '',
    })
  }
})

const saveProfile = async () => {
  successMessage.value = ''
  errorMessage.value = ''
  savingProfile.value = true
  const result = await authStore.updateProfile(profile)
  savingProfile.value = false
  if (result.success) successMessage.value = 'Perfil atualizado.'
  else errorMessage.value = result.error
}

const saveCompany = async () => {
  successMessage.value = ''
  errorMessage.value = ''
  savingCompany.value = true
  const result = await companyStore.save({ ...company })
  savingCompany.value = false
  if (result.success) successMessage.value = 'Dados da empresa atualizados.'
  else errorMessage.value = result.error
}

const onLogoChange = async (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  logoMessage.value = ''
  logoError.value = false
  const reader = new FileReader()
  reader.onload = (ev) => { logoPreview.value = ev.target.result }
  reader.readAsDataURL(file)
  const result = await companyStore.uploadLogo(file)
  e.target.value = ''
  if (result.success) {
    logoError.value = false
    logoMessage.value = 'Logotipo carregado e publicado no site.'
  } else {
    logoError.value = true
    logoMessage.value = result.error
  }
}

const onTogglePublish = async (e) => {
  const wanted = !!e.target.checked
  togglingPublish.value = true
  const result = await companyStore.togglePublish(wanted)
  togglingPublish.value = false
  if (result.success) {
    logoError.value = false
    logoMessage.value = wanted ? 'Logotipo publicado no site.' : 'Logotipo removido do site.'
  } else {
    logoError.value = true
    logoMessage.value = result.error
    e.target.checked = !wanted
  }
}

const onPhotoChange = async (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  photoMessage.value = ''
  photoError.value = false
  const reader = new FileReader()
  reader.onload = (ev) => { photoPreview.value = ev.target.result }
  reader.readAsDataURL(file)
  const result = await authStore.uploadPhoto(file)
  e.target.value = ''
  if (result.success) {
    photoError.value = false
    photoMessage.value = 'Foto de perfil atualizada.'
    photoPreview.value = ''
  } else {
    photoError.value = true
    photoMessage.value = result.error
  }
}
</script>

<style scoped>
.cliente-profile {
  background: #f5f7fa;
  min-height: 100vh;
}

.card {
  border: none;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.card-header {
  background: white;
  border-bottom: 1px solid #eef0f3;
  padding: 1rem 1.25rem;
}

.logo-preview-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 10px;
}
.logo-preview {
  width: 88px;
  height: 88px;
  border-radius: 10px;
  background: white;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}
.logo-preview img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
.logo-placeholder {
  color: #94a3b8;
  font-size: 2rem;
}
.logo-actions {
  flex: 1;
  min-width: 0;
}

.profile-photo-section {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.profile-photo-preview {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  background: #f1f5f9;
  border: 3px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}

.profile-photo-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-placeholder {
  color: #94a3b8;
  font-size: 2.5rem;
}

.profile-photo-actions {
  flex: 1;
  min-width: 0;
}
</style>
