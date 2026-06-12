<template>
  <div class="change-password-page">
    <div class="bg-slideshow">
      <div class="bg-slide bg-slide-1"></div>
      <div class="bg-slide bg-slide-2"></div>
      <div class="bg-overlay"></div>
    </div>

    <div class="container change-container">
      <div class="row align-items-center justify-content-center min-vh-100">
        <div class="col-md-7 col-lg-5">
          <div class="card change-card animate-fade-up">
            <div class="card-body p-4 p-md-5">
              <div class="text-center mb-4">
                <div class="icon-circle">
                  <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h1 class="form-title mt-3">Alterar Senha</h1>
                <p class="form-subtitle">
                  {{ isForced
                    ? 'Por segurança, defina uma nova senha para continuar.'
                    : 'Atualize a sua senha de acesso.' }}
                </p>
              </div>

              <div v-if="errorMessage" class="alert alert-danger animate-shake" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ errorMessage }}
              </div>
              <div v-if="successMessage" class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> {{ successMessage }}
              </div>

              <form @submit.prevent="handleSubmit" novalidate>
                <div v-if="!isForced" class="form-floating mb-3 animate-fade-up delay-1">
                  <input
                    v-model="form.current_password"
                    type="password"
                    class="form-control"
                    id="current_password"
                    placeholder="Senha atual"
                    required
                  >
                  <label for="current_password">Senha atual</label>
                </div>

                <div class="form-floating mb-3 animate-fade-up delay-2">
                  <input
                    v-model="form.new_password"
                    type="password"
                    class="form-control"
                    id="new_password"
                    placeholder="Nova senha"
                    minlength="6"
                    required
                  >
                  <label for="new_password">Nova senha (mín. 6 caracteres)</label>
                </div>

                <div class="form-floating mb-4 animate-fade-up delay-3">
                  <input
                    v-model="form.new_password_confirmation"
                    type="password"
                    class="form-control"
                    id="new_password_confirmation"
                    placeholder="Confirmar nova senha"
                    minlength="6"
                    required
                  >
                  <label for="new_password_confirmation">Confirmar nova senha</label>
                </div>

                <button
                  type="submit"
                  class="btn btn-primary w-100 submit-btn animate-fade-up delay-4"
                  :disabled="loading"
                >
                  <span v-if="loading" class="d-flex align-items-center justify-content-center gap-2">
                    <span class="spinner-border spinner-border-sm" role="status"></span>
                    A guardar...
                  </span>
                  <span v-else class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-check2-circle"></i>
                    Guardar nova senha
                  </span>
                </button>

                <div v-if="!isForced" class="text-center mt-3">
                  <button type="button" class="btn btn-link link-muted" @click="goBack">
                    <i class="bi bi-arrow-left me-1"></i> Voltar
                  </button>
                </div>
              </form>
            </div>
          </div>
          <p class="text-center text-white-50 small mt-3 mb-0">
            <i class="bi bi-shield-check me-1"></i> FMLider · Gestão segura
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})
const errorMessage = ref('')
const successMessage = ref('')
const loading = ref(false)

const isForced = computed(() => !!authStore.user?.must_change_password)

const goBack = () => {
  if (authStore.isAdmin) return router.push('/admin')
  if (authStore.isFuncionario) return router.push('/funcionario')
  if (authStore.isCliente) return router.push('/dashboard')
  router.push('/login')
}

onMounted(() => {
  if (!authStore.isAuthenticated) {
    router.replace('/login')
  }
})

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (form.value.new_password.length < 6) {
    errorMessage.value = 'A nova senha deve ter pelo menos 6 caracteres.'
    return
  }
  if (form.value.new_password !== form.value.new_password_confirmation) {
    errorMessage.value = 'A confirmação da nova senha não coincide.'
    return
  }

  const payload = {
    new_password: form.value.new_password,
    new_password_confirmation: form.value.new_password_confirmation,
  }
  if (!isForced.value) {
    payload.current_password = form.value.current_password
  }

  loading.value = true
  const result = await authStore.changePassword(payload)
  loading.value = false

  if (!result.success) {
    errorMessage.value = result.error || 'Não foi possível alterar a senha.'
    return
  }

  successMessage.value = 'Senha alterada com sucesso!'
  form.value.current_password = ''
  form.value.new_password = ''
  form.value.new_password_confirmation = ''

  setTimeout(() => {
    if (authStore.isAdmin) router.push('/admin')
    else if (authStore.isFuncionario) router.push('/funcionario')
    else if (authStore.isCliente) router.push('/dashboard')
    else router.push('/login')
  }, 800)
}
</script>

<style scoped>
.change-password-page {
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
  animation-delay: 9s;
}
.bg-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(10, 25, 41, 0.88) 0%, rgba(15, 39, 68, 0.78) 50%, rgba(10, 25, 41, 0.88) 100%);
  z-index: 1;
}

@keyframes kenBurns {
  0% { opacity: 0; transform: scale(1.15); }
  10% { opacity: 1; }
  50% { opacity: 1; transform: scale(1); }
  60% { opacity: 0; transform: scale(1.05); }
  100% { opacity: 0; transform: scale(1.05); }
}

.change-container {
  position: relative;
  z-index: 3;
}

.change-card {
  background: rgba(255, 255, 255, 0.98);
  border-radius: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  border: none;
}

.icon-circle {
  width: 78px;
  height: 78px;
  margin: 0 auto;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  font-size: 2rem;
  box-shadow: 0 10px 30px rgba(37, 99, 235, 0.4);
}

.form-title {
  font-size: 1.6rem;
  font-weight: 700;
  color: #0a1929;
  margin-bottom: 0.25rem;
}
.form-subtitle {
  color: #6b7280;
  font-size: 0.95rem;
  margin-bottom: 0;
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

.submit-btn {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  border: none;
  border-radius: 12px;
  padding: 0.85rem;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
}
.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.5);
}

.link-muted {
  color: #6b7280;
  text-decoration: none;
  font-size: 0.9rem;
}
.link-muted:hover {
  color: #2563eb;
}

.animate-fade-up {
  opacity: 0;
  transform: translateY(20px);
  animation: fadeUp 0.7s ease forwards;
}
.animate-shake {
  animation: shake 0.5s ease;
}
.delay-1 { animation-delay: 0.1s; }
.delay-2 { animation-delay: 0.2s; }
.delay-3 { animation-delay: 0.3s; }
.delay-4 { animation-delay: 0.4s; }

@keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-6px); }
  40%, 80% { transform: translateX(6px); }
}
</style>
