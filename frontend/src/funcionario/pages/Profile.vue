<template>
  <div class="funcionario-profile p-4 p-md-5">
    <h1 class="page-title mb-4"><i class="bi bi-person-fill me-2"></i>Perfil</h1>
    <div class="row g-3">
      <div class="col-lg-4">
        <div class="card">
          <div class="card-body text-center py-4">
            <div class="avatar-wrapper" :class="{ 'has-photo': !!authStore.user?.photo }">
              <img v-if="authStore.user?.photo" :src="authStore.user.photo" :alt="authStore.user?.name" class="avatar-img">
              <div v-else class="avatar-xl">{{ initials(authStore.user?.name) }}</div>
              <label class="avatar-upload-btn" :class="{ disabled: uploadingPhoto }" title="Alterar foto">
                <i class="bi bi-camera-fill"></i>
                <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" @change="onPhotoChange" hidden>
              </label>
            </div>
            <div v-if="photoMessage" class="small mt-2" :class="photoError ? 'text-danger' : 'text-success'">
              {{ photoMessage }}
            </div>
            <h5 class="mt-3 mb-1">{{ authStore.user?.name }}</h5>
            <p class="text-muted small mb-2">{{ authStore.user?.email }}</p>
            <span class="badge bg-success mb-2">{{ authStore.user?.position || 'Funcionário' }}</span>
            <p class="text-muted small mb-0">@{{ authStore.user?.username }}</p>
          </div>
        </div>

        <div v-if="authStore.photoHistory && authStore.photoHistory.length > 1" class="card mt-3">
          <div class="card-header"><h6 class="mb-0">Histórico de fotos</h6></div>
          <div class="card-body">
            <div class="photo-history">
              <div
                v-for="p in authStore.photoHistory"
                :key="p.id"
                class="photo-thumb"
                :class="{ current: p.is_current, clickable: !p.is_current }"
                :title="formatDate(p.created_at) + (p.is_current ? ' · Atual' : '')"
                @click="restorePhoto(p)"
              >
                <img :src="p.photo" :alt="formatDate(p.created_at)">
                <span v-if="p.is_current" class="badge bg-success current-badge">Atual</span>
              </div>
            </div>
            <p class="text-muted small mb-0 mt-2">Clica numa foto antiga para a repor.</p>
          </div>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header"><h6 class="mb-0">Dados pessoais</h6></div>
          <div class="card-body">
            <form @submit.prevent="save">
              <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
              <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Nome *</label>
                  <input v-model="form.name" type="text" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Telefone</label>
                  <input v-model="form.phone" type="text" class="form-control">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input :value="authStore.user?.email" type="email" class="form-control" disabled>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Cargo</label>
                  <input :value="authStore.user?.position || 'Funcionário'" type="text" class="form-control" disabled>
                </div>
              </div>
              <div class="mt-3 d-flex justify-content-end">
                <button class="btn btn-primary" :disabled="saving">
                  <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                  Guardar alterações
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header"><h6 class="mb-0">Alterar senha</h6></div>
          <div class="card-body">
            <form @submit.prevent="changePassword">
              <div v-if="passwordError" class="alert alert-danger">{{ passwordError }}</div>
              <div v-if="passwordSuccess" class="alert alert-success">{{ passwordSuccess }}</div>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Senha atual</label>
                  <input v-model="pwd.current" type="password" class="form-control" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Nova senha</label>
                  <input v-model="pwd.new" type="password" class="form-control" minlength="6" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Confirmar</label>
                  <input v-model="pwd.confirm" type="password" class="form-control" minlength="6" required>
                </div>
              </div>
              <div class="mt-3 d-flex justify-content-end">
                <button class="btn btn-outline-primary" :disabled="changingPwd">Alterar senha</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { supabase } from '@/lib/supabase'

const authStore = useAuthStore()
const form = reactive({ name: '', phone: '' })
const pwd = reactive({ current: '', new: '', confirm: '' })
const saving = ref(false)
const changingPwd = ref(false)
const uploadingPhoto = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const passwordError = ref('')
const passwordSuccess = ref('')
const photoMessage = ref('')
const photoError = ref(false)

const initials = (n) => (n || '?').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()

const save = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  saving.value = true
  try {
    const { error } = await supabase.auth.updateUser({ data: form })
    if (error) throw error
    successMessage.value = 'Perfil atualizado.'
    await authStore.getProfile()
  } catch (e) {
    errorMessage.value = e.message || 'Erro ao guardar.'
  } finally {
    saving.value = false
  }
}

const changePassword = async () => {
  passwordError.value = ''
  passwordSuccess.value = ''
  if (pwd.new !== pwd.confirm) { passwordError.value = 'As senhas não coincidem.'; return }
  changingPwd.value = true
  try {
    const { error } = await supabase.auth.updateUser({ password: pwd.new })
    if (error) throw error
    passwordSuccess.value = 'Senha alterada com sucesso.'
    pwd.current = ''; pwd.new = ''; pwd.confirm = ''
    await authStore.getProfile()
  } catch (e) {
    passwordError.value = e.message || 'Erro ao alterar senha.'
  } finally {
    changingPwd.value = false
  }
}

const onPhotoChange = async (event) => {
  const file = event.target.files?.[0]
  event.target.value = ''
  if (!file) return
  if (file.size > 3 * 1024 * 1024) {
    photoError.value = true
    photoMessage.value = 'Imagem demasiado grande (máx 3MB).'
    return
  }
  uploadingPhoto.value = true
  photoMessage.value = ''
  photoError.value = false
  const result = await authStore.uploadPhoto(file)
  uploadingPhoto.value = false
  if (!result.success) {
    photoError.value = true
    photoMessage.value = result.error || 'Erro ao enviar foto.'
  } else {
    photoError.value = false
    photoMessage.value = 'Foto atualizada.'
  }
}

onMounted(async () => {
  await authStore.getProfile()
  form.name = authStore.user?.name || ''
  form.phone = authStore.user?.phone || ''
})

const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT', { day: '2-digit', month: 'short', year: 'numeric' }) : ''

const restorePhoto = async (p) => {
  if (p.is_current) return
  if (!confirm('Repor esta foto como atual?')) return
  photoError.value = true
  photoMessage.value = 'Funcionalidade de repor foto ainda não disponível.'
}
</script>

<style scoped>
.funcionario-profile { background: #f5f7fa; min-height: 100vh; }
.page-title { font-size: 1.6rem; font-weight: 700; color: #0f172a; }
.card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04); }
.card-header { background: white; border-bottom: 1px solid #eef0f3; padding: 0.85rem 1.25rem; }
.card-header h6 { font-weight: 700; color: #0f172a; }
.avatar-wrapper {
  position: relative;
  width: 110px;
  height: 110px;
  margin: 0 auto;
}
.avatar-xl {
  width: 110px; height: 110px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0f766e, #134e4a);
  color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 2rem;
  font-weight: 700;
  overflow: hidden;
}
.avatar-img {
  width: 110px; height: 110px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #0f766e;
}
.avatar-upload-btn {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 34px; height: 34px;
  border-radius: 50%;
  background: #0f766e;
  color: white;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  transition: background 0.2s;
  border: 2px solid white;
}
.avatar-upload-btn:hover { background: #134e4a; }
.avatar-upload-btn.disabled { opacity: 0.6; pointer-events: none; }
.avatar-upload-btn i { font-size: 0.9rem; }

.photo-history {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
}
.photo-thumb {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid transparent;
  background: #f1f5f9;
}
.photo-thumb img { width: 100%; height: 100%; object-fit: cover; }
.photo-thumb.current { border-color: #0f766e; cursor: default; }
.photo-thumb.clickable { cursor: pointer; }
.photo-thumb.clickable:hover { border-color: #94a3b8; }
.photo-thumb .current-badge {
  position: absolute;
  top: 4px; right: 4px;
  font-size: 0.65rem;
  padding: 2px 6px;
}
@media (max-width: 575.98px) {
  .photo-history { grid-template-columns: repeat(3, 1fr); }
}
</style>
