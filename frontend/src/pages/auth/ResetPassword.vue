<template>
  <div class="reset-page py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body p-5">
              <h1 class="text-center mb-4">{{ t('auth.reset_title') }}</h1>

              <div v-if="errorMessage" class="alert alert-danger" role="alert">
                {{ errorMessage }}
              </div>
              <div v-if="successMessage" class="alert alert-success" role="alert">
                {{ successMessage }}
              </div>

              <div v-if="!sessionReady && !errorMessage">
                <p class="text-center">{{ t('auth.reset_processing') }}</p>
                <div class="text-center">
                  <div class="spinner-border" role="status"></div>
                </div>
              </div>

              <form v-if="sessionReady" @submit.prevent="handleResetPassword">
                <div class="mb-3">
                  <label for="password" class="form-label">{{ t('auth.reset_new') }}</label>
                  <input type="password" class="form-control" id="password" v-model="form.password" required minlength="6">
                </div>
                <div class="mb-3">
                  <label for="password_confirm" class="form-label">{{ t('auth.reset_confirm') }}</label>
                  <input type="password" class="form-control" id="password_confirm" v-model="form.password_confirm" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  {{ t('auth.reset_submit') }}
                </button>
              </form>

              <p class="text-center mt-3">
                <router-link to="/login">{{ t('auth.reset_back') }}</router-link>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { supabase } from '@/lib/supabase'
import { useI18n } from '@/composables/useI18n'

const router = useRouter()
const route = useRoute()
const { t } = useI18n()

const form = ref({
  password: '',
  password_confirm: ''
})

const sessionReady = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

onMounted(async () => {
  const code = route.query.code
  if (code) {
    const { error } = await supabase.auth.exchangeCodeForSession(code)
    if (error) {
      errorMessage.value = t('auth.reset_invalid_link')
    } else {
      sessionReady.value = true
    }
  } else {
    errorMessage.value = t('auth.reset_invalid_link_2')
  }
})

const handleResetPassword = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (form.value.password !== form.value.password_confirm) {
    errorMessage.value = t('auth.reset_mismatch')
    return
  }

  if (form.value.password.length < 6) {
    errorMessage.value = t('auth.reset_min_chars')
    return
  }

  loading.value = true
  const { error } = await supabase.auth.updateUser({
    password: form.value.password
  })
  loading.value = false

  if (error) {
    errorMessage.value = error.message
  } else {
    successMessage.value = t('auth.reset_success')
    setTimeout(() => router.push('/login'), 2000)
  }
}
</script>

<style scoped>
.reset-page {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card {
  border: none;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  border-radius: 12px;
}
</style>
