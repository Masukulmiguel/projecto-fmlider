<template>
  <div class="register-page">
    <div class="bg-slideshow">
      <div class="bg-slide bg-slide-1"></div>
      <div class="bg-slide bg-slide-2"></div>
      <div class="bg-slide bg-slide-3"></div>
      <div class="bg-overlay"></div>
    </div>

    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    <div class="floating-shape shape-3"></div>

    <div class="container register-container py-4">
      <div class="row align-items-center justify-content-center min-vh-100">
        <div class="col-lg-10 col-xl-9">
          <div class="register-card animate-fade-up">
            <div class="row g-0">
              <div class="col-lg-6 d-none d-lg-block brand-side">
                <div class="brand-content">
                  <div class="brand-logo animate-pop">
                    <img src="/assets/img/logo.png" alt="FMLider">
                  </div>
                  <h2 class="brand-title animate-fade-up delay-1">Junte-se a nós</h2>
                  <p class="brand-subtitle animate-fade-up delay-2">
                    <span class="typewriter">{{ typedSubtitle }}</span><span class="cursor">|</span>
                  </p>
                  <ul class="brand-features animate-fade-up delay-3">
                    <li><span class="feature-icon">✓</span> Registo gratuito</li>
                    <li><span class="feature-icon">✓</span> Aprovação rápida</li>
                    <li><span class="feature-icon">✓</span> Dashboard personalizado</li>
                    <li><span class="feature-icon">✓</span> Suporte dedicado</li>
                  </ul>
                </div>
              </div>

              <div class="col-lg-6 form-side">
                <div class="form-wrapper">
                  <div class="text-center mb-4 d-lg-none">
                    <img src="/assets/img/logo.png" alt="FMLider" class="mobile-logo animate-pop">
                  </div>

                  <div class="form-header animate-fade-up">
                    <h1 class="form-title">Criar Conta</h1>
                    <p class="form-subtitle">Comece agora a sua jornada connosco.</p>
                  </div>

                  <div v-if="successMessage" class="alert alert-success animate-fade-in" role="alert">
                    {{ successMessage }}
                  </div>
                  <div v-if="errorMessage" class="alert alert-danger animate-shake" role="alert">
                    {{ errorMessage }}
                  </div>

                  <form @submit.prevent="handleRegister" novalidate>
                    <div class="row">
                      <div class="col-md-6 mb-3 animate-fade-up delay-1">
                        <div class="form-floating">
                          <input type="text" class="form-control" :class="{'is-invalid': errors.username}" id="username" v-model="form.username" placeholder="Utilizador" required>
                          <label for="username">Nome de utilizador *</label>
                          <div class="invalid-feedback">{{ errors.username }}</div>
                        </div>
                      </div>
                      <div class="col-md-6 mb-3 animate-fade-up delay-1">
                        <div class="form-floating">
                          <input type="text" class="form-control" :class="{'is-invalid': errors.name}" id="name" v-model="form.name" placeholder="Nome completo" required>
                          <label for="name">Nome completo *</label>
                          <div class="invalid-feedback">{{ errors.name }}</div>
                        </div>
                      </div>
                    </div>

                    <div class="mb-3 animate-fade-up delay-2">
                      <div class="form-floating">
                        <input type="email" class="form-control" :class="{'is-invalid': errors.email}" id="email" v-model="form.email" placeholder="Email" required>
                        <label for="email">Email *</label>
                        <div class="invalid-feedback">{{ errors.email }}</div>
                      </div>
                    </div>

                    <div class="mb-3 animate-fade-up delay-2">
                      <div class="form-floating">
                        <input type="tel" class="form-control" :class="{'is-invalid': errors.phone}" id="phone" v-model="form.phone" placeholder="Telefone" required>
                        <label for="phone">Número de telefone *</label>
                        <div class="invalid-feedback">{{ errors.phone }}</div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-3 animate-fade-up delay-3">
                        <div class="form-floating">
                          <input type="password" class="form-control" :class="{'is-invalid': errors.password}" id="password" v-model="form.password" placeholder="Senha" required>
                          <label for="password">Senha *</label>
                          <div class="invalid-feedback">{{ errors.password }}</div>
                        </div>
                      </div>
                      <div class="col-md-6 mb-3 animate-fade-up delay-3">
                        <div class="form-floating">
                          <input type="password" class="form-control" :class="{'is-invalid': errors.password_confirm}" id="password_confirm" v-model="form.password_confirm" placeholder="Confirmar" required>
                          <label for="password_confirm">Confirmar senha *</label>
                          <div class="invalid-feedback">{{ errors.password_confirm }}</div>
                        </div>
                      </div>
                    </div>

                    <div class="form-check mb-4 animate-fade-up delay-4">
                      <input class="form-check-input" type="checkbox" id="terms" v-model="form.acceptTerms" required>
                      <label class="form-check-label" for="terms">
                        Aceito os <a href="#" class="link-primary">termos e condições</a>
                      </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 register-btn animate-fade-up delay-4" :disabled="loading">
                      <span v-if="loading" class="d-flex align-items-center justify-content-center gap-2">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        A registar...
                      </span>
                      <span v-else class="d-flex align-items-center justify-content-center gap-2">
                        Criar conta
                        <span class="arrow">→</span>
                      </span>
                    </button>
                  </form>

                  <div class="links animate-fade-up delay-4">
                    <p class="text-center mb-0">
                      Já tem conta? <router-link to="/login" class="link-primary">Entrar</router-link>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  username: '',
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirm: '',
  acceptTerms: false
})

const errors = ref({})
const successMessage = ref('')
const errorMessage = ref('')
const loading = ref(false)

const subtitles = [
  'Crie a sua conta em segundos.',
  'Aprovação em poucas horas.',
  'O seu dashboard à distância de um clique.',
  'Transparência em cada etapa.'
]

const typedSubtitle = ref('')
let typeTimeout = null

const typeSubtitle = (text, i = 0) => {
  if (i <= text.length) {
    typedSubtitle.value = text.substring(0, i)
    typeTimeout = setTimeout(() => typeSubtitle(text, i + 1), 95)
  } else {
    typeTimeout = setTimeout(() => eraseSubtitle(text), 3000)
  }
}

const eraseSubtitle = (text) => {
  if (text.length > 0) {
    typeSubtitle(text.substring(0, text.length - 1))
  } else {
    const next = subtitles[(subtitles.indexOf(text) + 1) % subtitles.length]
    typeTimeout = setTimeout(() => typeSubtitle(next), 600)
  }
}

onMounted(() => {
  typeSubtitle(subtitles[0])
})

onBeforeUnmount(() => {
  if (typeTimeout) clearTimeout(typeTimeout)
})

const handleRegister = async () => {
  errors.value = {}
  successMessage.value = ''
  errorMessage.value = ''

  if (!form.acceptTerms) {
    errorMessage.value = 'Deve aceitar os termos e condições.'
    return
  }

  loading.value = true
  const result = await authStore.register({
    username: form.username,
    name: form.name,
    email: form.email,
    phone: form.phone,
    password: form.password,
    password_confirm: form.password_confirm
  })
  loading.value = false

  if (result.success) {
    successMessage.value = result.message || 'Conta criada! Aguarde aprovação do administrador.'
    setTimeout(() => router.push('/login'), 2500)
  } else {
    errorMessage.value = result.error
    if (result.fields) errors.value = result.fields
  }
}
</script>

<style scoped>
.register-page {
  position: relative;
  min-height: 100vh;
  overflow: hidden;
  background: #0a1929;
}

.bg-slideshow {
  position: absolute;
  inset: 0;
  z-index: 0;
}

.bg-slide {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  animation: kenBurns 18s ease-in-out infinite;
  opacity: 0;
  transform: scale(1.15);
}

.bg-slide-1 { background-image: url('/assets/img/logo.png'); animation-delay: 0s; }
.bg-slide-2 { background-image: url('/assets/img/construcao2020/image1.jpeg'); animation-delay: 6s; }
  .bg-slide-3 { background-image: url('/assets/img/construcao2020/image2.jpeg'); animation-delay: 12s; }

@keyframes kenBurns {
  0% { opacity: 0; transform: scale(1.15); }
  10% { opacity: 1; }
  33% { opacity: 1; transform: scale(1); }
  43% { opacity: 0; transform: scale(1.05); }
  100% { opacity: 0; transform: scale(1.05); }
}

.bg-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(10, 25, 41, 0.85) 0%, rgba(15, 39, 68, 0.75) 50%, rgba(10, 25, 41, 0.85) 100%);
  z-index: 1;
}

.floating-shape {
  position: absolute;
  z-index: 2;
  border-radius: 50%;
  filter: blur(40px);
  opacity: 0.5;
  animation: float 12s ease-in-out infinite;
}

.shape-1 { width: 320px; height: 320px; background: #2563eb; top: -100px; left: -100px; }
.shape-2 { width: 280px; height: 280px; background: #7c3aed; bottom: -80px; right: -80px; animation-delay: -4s; }
.shape-3 { width: 220px; height: 220px; background: #06b6d4; top: 50%; right: 10%; animation-delay: -8s; }

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -40px) scale(1.05); }
  66% { transform: translate(-20px, 30px) scale(0.95); }
}

.register-container {
  position: relative;
  z-index: 3;
}

.register-card {
  background: rgba(255, 255, 255, 0.98);
  border-radius: 24px;
  box-shadow:
    0 25px 50px -12px rgba(0, 0, 0, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  overflow: hidden;
  backdrop-filter: blur(20px);
}

.brand-side {
  position: relative;
  background: linear-gradient(135deg, #0a1929 0%, #1e3a5f 50%, #2563eb 100%);
  color: white;
  padding: 3rem;
  display: flex;
  align-items: center;
  min-height: 700px;
  overflow: hidden;
}

.brand-side::before {
  content: '';
  position: absolute;
  inset: 0;
  background: url('/assets/img/logo.png') center/cover;
  opacity: 0.08;
  filter: blur(2px);
}

.brand-side::after {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 20% 30%, rgba(37, 99, 235, 0.3) 0%, transparent 50%),
    radial-gradient(circle at 80% 70%, rgba(124, 58, 237, 0.3) 0%, transparent 50%);
}

.brand-content {
  position: relative;
  z-index: 1;
  width: 100%;
}

.brand-logo {
  width: 90px;
  height: 90px;
  border-radius: 20px;
  background: white;
  padding: 8px;
  margin-bottom: 1.5rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-logo img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 12px;
}

.brand-title {
  font-size: 2.2rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
  background: linear-gradient(135deg, #fff 0%, #93c5fd 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  letter-spacing: -0.5px;
}

.brand-subtitle {
  font-size: 1.05rem;
  opacity: 0.9;
  min-height: 1.6em;
  margin-bottom: 2rem;
  line-height: 1.6;
}

.typewriter { display: inline; }
.cursor {
  display: inline-block;
  margin-left: 2px;
  animation: blink 0.8s infinite;
  color: #93c5fd;
  font-weight: 700;
}

@keyframes blink {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0; }
}

.brand-features {
  list-style: none;
  padding: 0;
  margin: 0;
}

.brand-features li {
  padding: 0.6rem 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.95rem;
  opacity: 0.95;
}

.feature-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  font-size: 0.75rem;
  font-weight: 700;
}

.form-side {
  padding: 2.5rem 2rem;
  display: flex;
  align-items: center;
}

.form-wrapper {
  width: 100%;
}

.mobile-logo {
  width: 70px;
  height: 70px;
  border-radius: 16px;
  object-fit: contain;
  background: white;
  padding: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.form-title {
  font-size: 1.85rem;
  font-weight: 700;
  color: #0a1929;
  margin-bottom: 0.5rem;
  letter-spacing: -0.5px;
}

.form-subtitle {
  color: #6b7280;
  margin-bottom: 0;
  font-size: 0.95rem;
}

.form-floating > .form-control {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 1rem 0.85rem;
  height: 58px;
  transition: all 0.3s ease;
  background: #f9fafb;
}

.form-floating > .form-control:focus {
  border-color: #2563eb;
  background: white;
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

.form-floating > label {
  color: #9ca3af;
  padding: 1rem 0.85rem;
}

.form-check-input {
  width: 1.2em;
  height: 1.2em;
  margin-top: 0.15em;
  border: 2px solid #d1d5db;
  border-radius: 4px;
  cursor: pointer;
}

.form-check-input:checked {
  background-color: #2563eb;
  border-color: #2563eb;
}

.form-check-label {
  margin-left: 0.5rem;
  color: #4b5563;
  font-size: 0.9rem;
  cursor: pointer;
}

.register-btn {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  border: none;
  border-radius: 12px;
  padding: 0.85rem;
  font-weight: 600;
  font-size: 1rem;
  letter-spacing: 0.3px;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
}

.register-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.5);
}

.register-btn:active:not(:disabled) {
  transform: translateY(0);
}

.register-btn::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.register-btn:active::before {
  width: 300px;
  height: 300px;
}

.arrow {
  transition: transform 0.3s ease;
  display: inline-block;
}

.register-btn:hover .arrow {
  transform: translateX(4px);
}

.links {
  margin-top: 1.5rem;
}

.link-primary {
  color: #2563eb;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s;
}

.link-primary:hover {
  color: #1d4ed8;
  text-decoration: underline;
}

.animate-fade-up {
  opacity: 0;
  transform: translateY(20px);
  animation: fadeUp 0.7s ease forwards;
}

.animate-fade-in {
  animation: fadeIn 0.5s ease;
}

.animate-pop {
  opacity: 0;
  transform: scale(0.5);
  animation: pop 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
}

.animate-shake {
  animation: shake 0.5s ease;
}

.delay-1 { animation-delay: 0.15s; }
.delay-2 { animation-delay: 0.3s; }
.delay-3 { animation-delay: 0.45s; }
.delay-4 { animation-delay: 0.6s; }

@keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes pop { to { opacity: 1; transform: scale(1); } }
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-8px); }
  40%, 80% { transform: translateX(8px); }
}

@media (max-width: 991.98px) {
  .form-side { padding: 2rem 1.5rem; }
  .brand-side { display: none; }
}
</style>
