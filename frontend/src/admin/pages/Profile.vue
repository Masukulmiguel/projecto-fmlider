<template>
  <div class="admin-profile p-4 p-md-5">
    <h1 class="page-title mb-4"><i class="bi bi-person-fill me-2"></i>{{ t('admin.profile_title') }}</h1>
    <div class="row g-3">
      <div class="col-lg-4">
        <div class="card">
          <div class="card-body text-center py-4">
            <div class="avatar-wrapper" :class="{ 'has-photo': !!authStore.user?.photo }">
              <img v-if="authStore.user?.photo" :src="authStore.user.photo" :alt="authStore.user?.name" class="avatar-img">
              <div v-else class="avatar-xl">{{ initials(authStore.user?.name) }}</div>
              <label class="avatar-upload-btn" :class="{ disabled: uploadingPhoto }" :title="t('admin.profile_change_photo')">
                <i class="bi bi-camera-fill"></i>
                <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" @change="onPhotoChange" hidden>
              </label>
            </div>
            <div v-if="photoMessage" class="small mt-2" :class="photoError ? 'text-danger' : 'text-success'">
              {{ photoMessage }}
            </div>
            <h5 class="mt-3 mb-1">{{ authStore.user?.name }}</h5>
            <p class="text-muted small mb-2">{{ authStore.user?.email }}</p>
            <span class="badge bg-primary mb-2">{{ authStore.user?.position || t('admin.profile_role_admin') }}</span>
            <p class="text-muted small mb-0">@{{ authStore.user?.username }}</p>
          </div>
        </div>

        <div v-if="authStore.photoHistory && authStore.photoHistory.length > 1" class="card mt-3">
          <div class="card-header"><h6 class="mb-0">{{ t('admin.profile_photo_history') }}</h6></div>
          <div class="card-body">
            <div class="photo-history">
              <div
                v-for="p in authStore.photoHistory"
                :key="p.id"
                class="photo-thumb"
                :class="{ current: p.is_current, clickable: !p.is_current }"
                :title="formatDate(p.created_at) + (p.is_current ? ' ' + t('admin.profile_current_suffix') : '')"
                @click="restorePhoto(p)"
              >
                <img :src="p.photo" :alt="formatDate(p.created_at)">
                <span v-if="p.is_current" class="badge bg-success current-badge">{{ t('admin.profile_current_label') }}</span>
              </div>
            </div>
            <p class="text-muted small mb-0 mt-2">{{ t('admin.profile_select_old') }}</p>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card">
          <div class="card-header"><h6 class="mb-0">{{ t('admin.profile_personal') }}</h6></div>
          <div class="card-body">
            <form @submit.prevent="save">
              <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
              <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">{{ t('admin.profile_name_label') }}</label>
                  <input v-model="form.name" type="text" class="form-control" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ t('admin.profile_phone_label') }}</label>
                  <input v-model="form.phone" type="text" class="form-control">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input :value="authStore.user?.email" type="email" class="form-control" disabled>
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ t('admin.profile_cargo_label') }}</label>
                  <input :value="authStore.user?.position || t('admin.profile_role_admin')" type="text" class="form-control" disabled>
                </div>
              </div>
              <div class="mt-3 d-flex justify-content-end">
                <button class="btn btn-primary" :disabled="saving">
                  <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                  {{ t('admin.profile_save') }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header"><h6 class="mb-0">{{ t('admin.profile_password') }}</h6></div>
          <div class="card-body">
            <form @submit.prevent="changePassword">
              <div v-if="passwordError" class="alert alert-danger">{{ passwordError }}</div>
              <div v-if="passwordSuccess" class="alert alert-success">{{ passwordSuccess }}</div>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">{{ t('admin.profile_password') }}</label>
                   <input v-model="pwd.current" type="password" class="form-control" required>
                 </div>
                 <div class="col-md-4">
                   <label class="form-label">{{ t('admin.profile_password') }}</label>
                   <input v-model="pwd.new" type="password" class="form-control" minlength="6" required>
                 </div>
                 <div class="col-md-4">
                   <label class="form-label">{{ t('common.confirm') }}</label>
                  <input v-model="pwd.confirm" type="password" class="form-control" minlength="6" required>
                </div>
              </div>
              <div class="mt-3 d-flex justify-content-end">
                <button class="btn btn-outline-primary" :disabled="changingPwd">{{ t('admin.profile_password') }}</button>
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
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/authStore'
import { useI18n } from '@/composables/useI18n'
import { useConfirm } from '@/composables/useConfirm'

const { t } = useI18n()
const { confirm } = useConfirm()

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
const formatDate = (d) => d ? new Date(d).toLocaleDateString('pt-PT', { day: '2-digit', month: 'short', year: 'numeric' }) : ''

const save = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  saving.value = true
  try {
    const { error } = await supabase.auth.updateUser({ data: form })
    if (error) throw error
    successMessage.value = t('admin.profile_profile_updated')
    await authStore.getProfile()
  } catch (e) {
    errorMessage.value = e.message || t('admin.profile_error_saving')
  } finally {
    saving.value = false
  }
}

const changePassword = async () => {
  passwordError.value = ''
  passwordSuccess.value = ''
  if (pwd.new !== pwd.confirm) { passwordError.value = t('admin.profile_passwords_mismatch'); return }
  changingPwd.value = true
  try {
    const { error } = await supabase.auth.updateUser({ password: pwd.new })
    if (error) throw error
    passwordSuccess.value = t('admin.profile_password_changed')
    pwd.current = ''; pwd.new = ''; pwd.confirm = ''
    await authStore.getProfile()
  } catch (e) {
    passwordError.value = e.message || t('admin.profile_error_changing_password')
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
    photoMessage.value = t('admin.profile_image_too_large')
    return
  }
  uploadingPhoto.value = true
  photoMessage.value = ''
  photoError.value = false
  const result = await authStore.uploadPhoto(file)
  uploadingPhoto.value = false
  if (!result.success) {
    photoError.value = true
    photoMessage.value = result.error || t('admin.profile_error_uploading_photo')
  } else {
    photoError.value = false
    photoMessage.value = t('admin.profile_photo_updated')
  }
}

const restorePhoto = async (p) => {
  if (p.is_current) return
  if (!await confirm({ title: 'Restaurar foto', message: t('admin.profile_restore_confirm'), type: 'warning', confirmText: 'Restaurar', cancelText: 'Cancelar' })) return
  photoError.value = true
  photoMessage.value = t('admin.profile_restore_not_available')
}

onMounted(async () => {
  await authStore.getProfile()
  form.name = authStore.user?.name || ''
  form.phone = authStore.user?.phone || ''
})
</script>

<style scoped>
.admin-profile { background: #f8f9fa; min-height: 100vh; }
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
  font-size: 2rem; font-weight: 700; overflow: hidden;
}
.avatar-img {
  width: 110px; height: 110px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #0f766e;
}
.avatar-upload-btn {
  position: absolute;
  bottom: 0; right: 0;
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
