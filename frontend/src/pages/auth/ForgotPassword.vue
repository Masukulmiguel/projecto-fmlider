<template>
  <div class="forgot-page">
    <!-- Background -->
    <div class="forgot-bg" :style="{ backgroundImage: `url(${bg})` }"></div>
    <div class="forgot-overlay"></div>

    <!-- Floating shapes -->
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>

    <!-- Content -->
    <div class="forgot-content">
      <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
          <div class="col-md-5 col-lg-4">

            <!-- Logo -->
            <div class="text-center mb-4 animate-item">
              <div class="forgot-logo">
                <img src="/assets/img/logo.png" alt="FMLider" class="logo-img">
              </div>
            </div>

            <!-- Card -->
            <div class="forgot-card animate-item delay-1">
              <!-- Icon -->
              <div class="forgot-icon-wrapper">
                <div class="forgot-icon">
                  <i class="bi bi-key-fill"></i>
                </div>
              </div>

              <!-- Title -->
              <div class="text-center mb-4">
                <h1 class="forgot-title">{{ t('auth.forgot_title') || 'Esqueceu a Senha?' }}</h1>
                <p class="forgot-subtitle">{{ t('auth.forgot_subtitle') || 'Insira o seu email e enviaremos instruções para redefinir a sua senha.' }}</p>
              </div>

              <!-- Success state -->
              <div v-if="sent" class="success-state">
                <div class="success-icon">
                  <i class="bi bi-check-circle-fill"></i>
                </div>
                <h3 class="success-title">{{ t('auth.forgot_success') || 'Email enviado!' }}</h3>
                <p class="success-text">Verifique a sua caixa de entrada e siga as instruções para redefinir a sua senha.</p>
                <router-link to="/login" class="btn-forgot">
                  <i class="bi bi-arrow-left me-2"></i>{{ t('auth.forgot_back') || 'Voltar ao Login' }}
                </router-link>
              </div>

              <!-- Form -->
              <form v-else @submit.prevent="handleReset" class="forgot-form">
                <div class="input-group-custom">
                  <label class="input-label">{{ t('auth.login_email') || 'Email' }}</label>
                  <div class="input-wrapper">
                    <i class="bi bi-envelope-fill input-icon"></i>
                    <input
                      type="email"
                      class="input-field"
                      v-model="form.email"
                      required
                      placeholder="seunome@exemplo.com"
                    >
                  </div>
                </div>

                <button type="submit" class="btn-forgot" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-send-fill me-2"></i>
                  {{ t('auth.forgot_submit') || 'Enviar Instruções' }}
                </button>
              </form>

              <!-- Back link -->
              <div class="back-login">
                <router-link to="/login" class="back-link">
                  <i class="bi bi-arrow-left"></i>
                  <span>{{ t('auth.forgot_back') || 'Voltar ao Login' }}</span>
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
import { useAuthStore } from '@/stores/authStore'
import { useI18n } from '@/composables/useI18n'
import { useSiteImages } from '@/composables/useSiteImages'
import BgImageEditor from '@/components/BgImageEditor.vue'

const authStore = useAuthStore()
const { t } = useI18n()
const { getImage, fetchAll } = useSiteImages()

const bg = ref('/assets/img/auth/bg3.jpg')
const form = ref({ email: '' })
const loading = ref(false)
const sent = ref(false)

onMounted(async () => {
  await fetchAll()
  bg.value = getImage('auth', 'forgot_bg', getImage('auth', 'reset_bg', '/assets/img/auth/bg3.jpg'))
})

const handleReset = async () => {
  loading.value = true
  const result = await authStore.resetPassword(form.value.email)
  loading.value = false
  if (result.success) {
    sent.value = true
  }
}
</script>

<style scoped>
.forgot-page {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
}

/* Background */
.forgot-bg {
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

.forgot-overlay {
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
.forgot-content {
  position: relative;
  z-index: 2;
}

/* Logo */
.forgot-logo {
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
.forgot-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 24px;
  padding: 2.5rem 2rem;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
}

/* Icon */
.forgot-icon-wrapper { text-align: center; margin-bottom: 1.5rem; }
.forgot-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto;
  background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%);
  color: #b45309;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

/* Typography */
.forgot-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 0.5rem;
}
.forgot-subtitle {
  font-size: 0.9rem;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

/* Form */
.forgot-form { display: flex; flex-direction: column; gap: 1.1rem; }

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
  padding: 0.85rem 1rem 0.85rem 2.8rem;
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

/* Button */
.btn-forgot {
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
  text-decoration: none;
}
.btn-forgot:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35);
  color: #fff;
}
.btn-forgot:disabled {
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

/* Success state */
.success-state { text-align: center; padding: 1rem 0; }
.success-icon {
  width: 72px;
  height: 72px;
  margin: 0 auto 1.25rem;
  background: linear-gradient(135deg, #d1fae5, #ecfdf5);
  color: #047857;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
}
.success-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.5rem;
}
.success-text {
  font-size: 0.9rem;
  color: #64748b;
  line-height: 1.5;
  margin: 0 0 1.5rem;
}

/* Animations */
.animate-item { opacity: 0; transform: translateY(20px); animation: fadeUp 0.6s ease forwards; }
.delay-1 { animation-delay: 0.15s; }
@keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

/* Mobile */
@media (max-width: 576px) {
  .forgot-card { padding: 2rem 1.5rem; border-radius: 20px; }
  .forgot-title { font-size: 1.3rem; }
  .forgot-logo { width: 60px; height: 60px; border-radius: 16px; }
}
</style>
