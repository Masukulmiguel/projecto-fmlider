<template>
  <div class="forgot-page py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body p-5">
              <h1 class="text-center mb-4">{{ t('auth.forgot_title') }}</h1>
              <form @submit.prevent="handleReset">
                <p class="mb-3">{{ t('auth.forgot_subtitle') }}</p>
                <div class="mb-3">
                  <label for="email" class="form-label">{{ t('auth.login_email') }}</label>
                  <input type="email" class="form-control" id="email" v-model="form.email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">{{ t('auth.forgot_submit') }}</button>
              </form>
              <p class="text-center">
                <router-link to="/login">{{ t('auth.forgot_back') }}</router-link>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useI18n } from '@/composables/useI18n'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const authStore = useAuthStore()
const { t } = useI18n()
const toast = useToast()

const form = ref({
  email: ''
})

const handleReset = async () => {
  const result = await authStore.resetPassword(form.value.email)
  if (result.success) {
    toast.success(t('auth.forgot_success'))
    router.push('/login')
  } else {
    toast.error(t('auth.forgot_error'))
  }
}
</script>

<style scoped>
.forgot-page {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card {
  border: none;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  border-radius: 12px;
}
</style>
