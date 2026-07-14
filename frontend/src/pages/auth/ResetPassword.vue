<template>
  <div class="reset-page">
    <!-- Background -->
    <div class="reset-bg" :style="{ backgroundImage: `url(${bg})` }"></div>
    <div class="reset-overlay"></div>

    <!-- Floating shapes -->
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>

    <!-- Content -->
    <div class="reset-content">
      <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
          <div class="col-md-5 col-lg-4">

            <!-- Logo -->
            <div class="text-center mb-4 animate-item">
              <div class="reset-logo">
                <img src="/assets/img/logo.png" alt="FMLider" class="logo-img">
              </div>
            </div>

            <!-- Card -->
            <div class="reset-card animate-item delay-1">
              <!-- Icon -->
              <div class="reset-icon-wrapper">
                <div class="reset-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/>
                  </svg>
                </div>
              </div>

              <!-- Title -->
              <div class="text-center mb-4">
                <h1 class="reset-title">Esqueceu a Senha?</h1>
                <p class="reset-subtitle">Não se preocupe! Insira o seu email e enviaremos instruções para redefinir a sua senha.</p>
              </div>

              <!-- Error -->
              <div v-if="errorMessage" class="alert-custom alert-error animate-shake">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ errorMessage }}</span>
              </div>

              <!-- Success -->
              <div v-if="successMessage" class="alert-custom alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ successMessage }}</span>
              </div>

              <!-- Loading -->
              <div v-if="!sessionReady && !errorMessage" class="loading-state">
                <div class="spinner-wrapper">
                  <div class="spinner-ring"></div>
                  <i class="bi bi-shield-lock-fill spinner-icon"></i>
                </div>
                <p class="loading-text">A validar o link de redefinição...</p>
              </div>

              <!-- Reset Form -->
              <form v-if="sessionReady" @submit.prevent="handleResetPassword" class="reset-form">
                <div class="input-group-custom">
                  <label class="input-label">Nova Senha</label>
                  <div class="input-wrapper">
                    <i class="bi bi-lock-fill input-icon"></i>
                    <input
                      :type="showPassword ? 'text' : 'password'"
                      class="input-field"
                      v-model="form.password"
                      required
                      minlength="6"
                      placeholder="Mínimo 6 caracteres"
                    >
                    <button type="button" class="toggle-pass" @click="showPassword = !showPassword" tabindex="-1">
                      <i :class="showPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill'"></i>
                    </button>
                  </div>
                </div>

                <div class="input-group-custom">
                  <label class="input-label">Confirmar Senha</label>
                  <div class="input-wrapper">
                    <i class="bi bi-shield-lock-fill input-icon"></i>
                    <input
                      :type="showPassword ? 'text' : 'password'"
                      class="input-field"
                      v-model="form.password_confirm"
                      required
                      placeholder="Confirme a nova senha"
                    >
                  </div>
                </div>

                <button type="submit" class="btn-reset" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-arrow-repeat me-2"></i>
                  Redefinir Senha
                </button>
              </form>

              <!-- Back to login -->
              <div class="back-login">
                <router-link to="/login" class="back-link">
                  <i class="bi bi-arrow-left"></i>
                  <span>Voltar ao Login</span>
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
const bg = ref('/assets/img/auth/reset_bg.jpg')

const form = ref({ password: '', password_confirm: '' })
const sessionReady = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const showPassword = ref(false)

onMounted(async () => {
  await fetchAll()
  bg.value = getImage('auth', 'reset_bg', '/assets/img/auth/reset_bg.jpg')

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
  const { error } = await supabase.auth.updateUser({ password: form.value.password })
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
  min-height: 100vh;
  position: relative;
  overflow: hidden;
}

/* Background */
.reset-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  animation: zoomBg 20s ease-in-out infinite alternate;
}
@keyframes zoomBg {
  from { transform: scale(1); }
  to { transform: scale(1.08); }
}

.reset-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.88) 0%, rgba(30, 58, 138, 0.82) 100%);
  z-index: 1;
}

/* Floating shapes */
.floating-shape {
  position: absolute;
  border-radius: 50%;
  opacity: 0.04;
  background: #fff;
  z-index: 1;
}
.shape-1 { width: 400px; height: 400px; top: -100px; right: -100px; animation: float1 18s ease-in-out infinite; }
.shape-2 { width: 250px; height: 250px; bottom: -60px; left: -60px; animation: float2 14s ease-in-out infinite; }
@keyframes float1 { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(-30px, 30px); } }
@keyframes float2 { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(20px, -20px); } }

/* Content */
.reset-content {
  position: relative;
  z-index: 2;
}

/* Logo */
.reset-logo {
  width: 72px;
  height: 72px;
  margin: 0 auto;
  background: rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px;
}
.logo-img { width: 100%; height: 100%; object-fit: contain; }

/* Card */
.reset-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 24px;
  padding: 2.5rem 2rem;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
}

/* Icon */
.reset-icon-wrapper { text-align: center; margin-bottom: 1.5rem; }
.reset-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto;
  background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
  color: var(--fml-blue-2, #2563eb);
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

/* Typography */
.reset-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 0.5rem;
}
.reset-subtitle {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

/* Alerts */
.alert-custom {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.85rem 1rem;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 1.25rem;
}
.alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }

/* Loading */
.loading-state { text-align: center; padding: 2rem 0; }
.spinner-wrapper {
  width: 64px;
  height: 64px;
  margin: 0 auto 1rem;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}
.spinner-ring {
  position: absolute;
  inset: 0;
  border: 3px solid #e2e8f0;
  border-top-color: var(--fml-blue-2, #2563eb);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
.spinner-icon {
  font-size: 1.4rem;
  color: var(--fml-blue-2, #2563eb);
  z-index: 1;
}
@keyframes spin { to { transform: rotate(360deg); } }
.loading-text { color: #94a3b8; font-size: 0.85rem; margin: 0; }

/* Form */
.reset-form { display: flex; flex-direction: column; gap: 1.1rem; }

.input-group-custom { display: flex; flex-direction: column; gap: 0.4rem; }
.input-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #334155;
  letter-spacing: 0.3px;
}
.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}
.input-icon {
  position: absolute;
  left: 14px;
  color: #94a3b8;
  font-size: 0.95rem;
  z-index: 1;
}
.input-field {
  width: 100%;
  padding: 0.85rem 2.8rem 0.85rem 2.8rem;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  color: #0f172a;
  background: #f8fafc;
  transition: all 0.2s;
  outline: none;
}
.input-field:focus {
  border-color: var(--fml-blue-2, #2563eb);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  background: #fff;
}
.input-field::placeholder { color: #cbd5e1; }

.toggle-pass {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 4px;
  font-size: 1rem;
  transition: color 0.2s;
}
.toggle-pass:hover { color: #475569; }

/* Button */
.btn-reset {
  width: 100%;
  padding: 0.9rem;
  background: linear-gradient(135deg, var(--fml-blue, #1e3a8a), var(--fml-blue-2, #2563eb));
  color: #fff;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.25s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
}
.btn-reset:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35);
}
.btn-reset:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Back link */
.back-login {
  text-align: center;
  margin-top: 1.5rem;
  padding-top: 1.25rem;
  border-top: 1px solid #f1f5f9;
}
.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 500;
  text-decoration: none;
  transition: color 0.2s;
}
.back-link:hover { color: var(--fml-blue-2, #2563eb); }

/* Animations */
.animate-item { opacity: 0; transform: translateY(20px); animation: fadeUp 0.6s ease forwards; }
.delay-1 { animation-delay: 0.15s; }
@keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
.animate-shake { animation: shake 0.4s ease; }
@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 75% { transform: translateX(6px); } }

/* Mobile */
@media (max-width: 576px) {
  .reset-card { padding: 2rem 1.5rem; border-radius: 20px; }
  .reset-title { font-size: 1.3rem; }
  .reset-logo { width: 60px; height: 60px; border-radius: 16px; }
}
</style>
