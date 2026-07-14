<template>
  <div class="reset-page min-vh-100 d-flex align-items-center justify-content-center" :style="{ backgroundImage: `url(${bg})` }">
    <div class="reset-overlay"></div>
    <div class="container position-relative" style="z-index: 2;">
      <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
          <div class="card border-0 shadow-lg">
            <div class="card-body p-4 p-md-5">
              <div class="text-center mb-4">
                <div class="reset-icon mx-auto mb-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="#1a365d" viewBox="0 0 16 16">
                    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/>
                  </svg>
                </div>
                <h1 class="h4 fw-bold text-dark mb-2">Redefinir Senha</h1>
                <p class="text-muted small mb-0">Insira a sua nova senha abaixo</p>
              </div>

              <div v-if="errorMessage" class="alert alert-danger d-flex align-items-center py-2" role="alert">
                <svg class="flex-shrink-0 me-2" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                </svg>
                <span>{{ errorMessage }}</span>
              </div>
              <div v-if="successMessage" class="alert alert-success d-flex align-items-center py-2" role="alert">
                <svg class="flex-shrink-0 me-2" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/>
                </svg>
                <span>{{ successMessage }}</span>
              </div>

              <div v-if="!sessionReady && !errorMessage" class="text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 2.5rem; height: 2.5rem;">
                  <span class="visually-hidden">A processar...</span>
                </div>
                <p class="text-muted small mb-0">A validar o link de redefinição...</p>
              </div>

              <form v-if="sessionReady" @submit.prevent="handleResetPassword">
                <div class="mb-3">
                  <label for="password" class="form-label fw-semibold small">Nova Senha</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#6c757d" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/></svg></span>
                    <input :type="showPassword ? 'text' : 'password'" class="form-control border-start-0 bg-light" id="password" v-model="form.password" required minlength="6" placeholder="Mínimo 6 caracteres">
                    <button type="button" class="btn btn-outline-secondary border-start-0" @click="showPassword = !showPassword" tabindex="-1">
                      <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg>
                      <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.948.826.826 0 0 0-.21.133.672.672 0 0 1-.943 0A6.944 6.944 0 0 1 1 13.011C.224 11.61 0 10.98 0 10c0-2.12 1.225-4.66 2.69-6.42.487-.563 1.055-.98 1.653-1.249C5.263 1.714 6.627 1.2 8 1.2c1.373 0 2.737.514 3.657 1.332.598.27 1.166.687 1.653 1.249C14.775 5.34 16 7.88 16 10c0 .98-.224 1.61-.641 2.09-.333.374-.76.594-1.157.684a4.49 4.49 0 0 1-.98.088A7.02 7.02 0 0 1 8 13.5c-1.158 0-2.234-.29-3.159-.806-.346-.2-.56-.378-.654-.475z"/></svg>
                    </button>
                  </div>
                </div>
                <div class="mb-4">
                  <label for="password_confirm" class="form-label fw-semibold small">Confirmar Senha</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#6c757d" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/></svg></span>
                    <input :type="showPassword ? 'text' : 'password'" class="form-control border-start-0 bg-light" id="password_confirm" v-model="form.password_confirm" required placeholder="Confirme a nova senha">
                  </div>
                </div>
                <button type="submit" class="btn btn-lg w-100 fw-semibold d-flex align-items-center justify-content-center gap-2" :disabled="loading" style="background: #1a365d; border-color: #1a365d; color: #fff;">
                  <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/></svg>
                  Redefinir Senha
                </button>
              </form>

              <div class="text-center mt-3 pt-3 border-top">
                <router-link to="/login" class="text-decoration-none small d-inline-flex align-items-center gap-1" style="color: #1a365d;">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                  </svg>
                  Voltar ao Login
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { supabase } from '@/lib/supabase'
import { useSiteImages } from '@/composables/useSiteImages'

const router = useRouter()
const { getImage, fetchAll } = useSiteImages()
const bg = ref('https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?w=1920&q=80')

const form = ref({
  password: '',
  password_confirm: ''
})

const sessionReady = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const showPassword = ref(false)

onMounted(async () => {
  await fetchAll()
  bg.value = getImage('auth', 'reset_bg', 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?w=1920&q=80')

  const { data: { session } } = await supabase.auth.getSession()

  if (session) {
    window.history.replaceState({}, '', '/redefinir-senha')
    sessionReady.value = true
    return
  }

  const { data: { subscription } } = supabase.auth.onAuthStateChange((event, newSession) => {
    if (event === 'PASSWORD_RECOVERY' && newSession) {
      window.history.replaceState({}, '', '/redefinir-senha')
      sessionReady.value = true
    }
  })

  setTimeout(() => {
    if (!sessionReady.value && !errorMessage.value) {
      errorMessage.value = 'Link de redefinição inválido ou expirado. Solicite um novo link.'
    }
    subscription?.unsubscribe()
  }, 8000)
})

const handleResetPassword = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (form.value.password !== form.value.password_confirm) {
    errorMessage.value = 'As senhas não coincidem.'
    return
  }

  if (form.value.password.length < 6) {
    errorMessage.value = 'A senha deve ter pelo menos 6 caracteres.'
    return
  }

  loading.value = true
  const { error } = await supabase.auth.updateUser({
    password: form.value.password
  })
  loading.value = false

  if (error) {
    errorMessage.value = error.message || 'Erro ao redefinir a senha.'
  } else {
    successMessage.value = 'Senha redefinida com sucesso! A redirecionar...'
    setTimeout(() => router.push('/login'), 2000)
  }
}
</script>

<style scoped>
.reset-page {
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  position: relative;
}

.reset-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(26, 54, 93, 0.85) 0%, rgba(45, 74, 122, 0.9) 100%);
  z-index: 1;
}

.card {
  border-radius: 16px;
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.95);
}

.reset-icon {
  width: 72px;
  height: 72px;
  background: linear-gradient(135deg, #e8f0fe 0%, #d4e4f7 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.form-control:focus {
  border-color: #1a365d;
  box-shadow: 0 0 0 0.2rem rgba(26, 54, 93, 0.15);
}

.form-control {
  border-color: #dee2e6;
}

.input-group-text {
  border-color: #dee2e6;
}
</style>
