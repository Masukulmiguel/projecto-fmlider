<template>
  <div class="login-page">
    <div class="bg-slideshow">
      <div class="bg-slide bg-slide-1"></div>
      <div class="bg-slide bg-slide-2"></div>
      <div class="bg-slide bg-slide-3"></div>
      <div class="bg-overlay"></div>
    </div>

    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    <div class="floating-shape shape-3"></div>

    <div class="container login-container">
      <div class="row align-items-center justify-content-center min-vh-100">
        <div class="col-lg-10 col-xl-9">
          <div class="login-card animate-fade-up">
            <div class="row g-0">
              <div class="col-lg-6 d-none d-lg-block brand-side">
                <div class="brand-content">
                  <div class="brand-logo animate-pop">
                    <img src="/assets/img/logo.png" alt="FMLider">
                  </div>
                  <h2 class="brand-title animate-fade-up delay-1">FMLider</h2>
                  <p class="brand-subtitle animate-fade-up delay-2">
                    <span class="typewriter">{{ typedSubtitle }}</span><span class="cursor">|</span>
                  </p>
                  <ul class="brand-features animate-fade-up delay-3">
                    <li><span class="feature-icon">✓</span> Gestão simplificada</li>
                    <li><span class="feature-icon">✓</span> Acompanhamento em tempo real</li>
                    <li><span class="feature-icon">✓</span> Equipa dedicada a si</li>
                  </ul>
                </div>
              </div>

              <div class="col-lg-6 form-side">
                <div class="form-wrapper">
                  <div class="text-center mb-4 d-lg-none">
                    <img src="/assets/img/logo.png" alt="FMLider" class="mobile-logo animate-pop">
                  </div>

                  <div class="form-header animate-fade-up">
                    <h1 class="form-title">Bem-vindo de volta</h1>
                    <p class="form-subtitle">Inicie sessão para continuar a sua jornada.</p>
                  </div>

                  <div v-if="errorMessage" class="alert alert-danger animate-shake" role="alert">
                    {{ errorMessage }}
                  </div>

                  <form @submit.prevent="handleLogin" novalidate>
                    <div class="form-floating mb-3 animate-fade-up delay-1">
                      <input type="email" class="form-control" id="email" v-model="form.email" placeholder="nome@exemplo.com" required>
                      <label for="email">Email</label>
                    </div>
                    <div class="form-floating mb-3 animate-fade-up delay-2">
                      <input type="password" class="form-control" id="password" v-model="form.password" placeholder="Senha" required>
                      <label for="password">Senha</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 login-btn animate-fade-up delay-3" :disabled="loading">
                      <span v-if="loading" class="d-flex align-items-center justify-content-center gap-2">
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        A entrar...
                      </span>
                      <span v-else class="d-flex align-items-center justify-content-center gap-2">
                        Entrar
                        <span class="arrow">→</span>
                      </span>
                    </button>
                  </form>

                  <div class="links animate-fade-up delay-4">
                    <p class="text-center mb-1">
                      Não tem conta? <router-link to="/registro" class="link-primary">Criar conta</router-link>
                    </p>
                    <p class="text-center mb-0">
                      <router-link to="/esqueci-senha" class="link-muted">Esqueci a senha</router-link>
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
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({ email: '', password: '' })
const errorMessage = ref('')
const loading = ref(false)

const subtitles = [
  'Logística sem fronteiras.',
  'Soluções que movem o seu negócio.',
  'A sua carga, o nosso compromisso.',
  'Conectamos destinos, entregamos confiança.'
]

const typedSubtitle = ref('')
let typewriterInterval = null

const typeSubtitle = (text, i = 0) => {
  if (i <= text.length) {
    typedSubtitle.value = text.substring(0, i)
    setTimeout(() => typeSubtitle(text, i + 1), 95)
  } else {
    setTimeout(() => eraseSubtitle(text), 3000)
  }
}

const eraseSubtitle = (text) => {
  if (text.length > 0) {
    typeSubtitle(text.substring(0, text.length - 1))
  } else {
    const next = subtitles[(subtitles.indexOf(text) + 1) % subtitles.length]
    setTimeout(() => typeSubtitle(next), 600)
  }
}

onMounted(() => {
  typeSubtitle(subtitles[0])
})

onBeforeUnmount(() => {
  if (typewriterInterval) clearTimeout(typewriterInterval)
})

const handleLogin = async () => {
  errorMessage.value = ''
  loading.value = true
  const result = await authStore.login(form.value.email, form.value.password)
  loading.value = false

  if (!result.success) {
    errorMessage.value = result.error
    return
  }

  if (result.mustChangePassword) {
    router.push('/mudar-senha')
    return
  }

  const user = result.user
  if (user.role === 'admin') {
    router.push('/admin')
  } else if (user.role === 'funcionario') {
    router.push('/funcionario')
  } else if (user.approval_status === 'approved') {
    router.push(user.company_completed ? '/dashboard' : '/configurar-empresa')
  } else {
    authStore.logout()
    errorMessage.value = 'A sua conta ainda não foi aprovada.'
  }
}
</script>

<style scoped>
.login-page {
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

.bg-slide-1 {
  background-image: url('/assets/img/logo.png');
  animation-delay: 0s;
}
.bg-slide-2 {
  background-image: url('/assets/img/construcao2020/image1.jpeg');
  animation-delay: 6s;
}
.bg-slide-3 {
  background-image: url('/assets/img/construcao2020/image2.jpeg');
  animation-delay: 12s;
}

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

.shape-1 {
  width: 320px;
  height: 320px;
  background: #2563eb;
  top: -100px;
  left: -100px;
}
.shape-2 {
  width: 280px;
  height: 280px;
  background: #7c3aed;
  bottom: -80px;
  right: -80px;
  animation-delay: -4s;
}
.shape-3 {
  width: 220px;
  height: 220px;
  background: #06b6d4;
  top: 50%;
  right: 10%;
  animation-delay: -8s;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -40px) scale(1.05); }
  66% { transform: translate(-20px, 30px) scale(0.95); }
}

.login-container {
  position: relative;
  z-index: 3;
}

.login-card {
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
  min-height: 600px;
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

.typewriter {
  display: inline;
}

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
  padding: 3rem 2.5rem;
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

.login-btn {
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

.login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.5);
}

.login-btn:active:not(:disabled) {
  transform: translateY(0);
}

.login-btn::before {
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

.login-btn:active::before {
  width: 300px;
  height: 300px;
}

.arrow {
  transition: transform 0.3s ease;
  display: inline-block;
}

.login-btn:hover .arrow {
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

.link-muted {
  color: #6b7280;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.2s;
}

.link-muted:hover {
  color: #2563eb;
}

.animate-fade-up {
  opacity: 0;
  transform: translateY(20px);
  animation: fadeUp 0.7s ease forwards;
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

@keyframes fadeUp {
  to { opacity: 1; transform: translateY(0); }
}

@keyframes pop {
  to { opacity: 1; transform: scale(1); }
}

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
