<template>
  <div class="register-page py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body p-5">
              <h1 class="text-center mb-4">Registar</h1>
              <form @submit.prevent="handleRegister">
                <div class="mb-3">
                  <label for="name" class="form-label">Nome</label>
                  <input type="text" class="form-control" id="name" v-model="form.name" required>
                </div>
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" v-model="form.email" required>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Senha</label>
                  <input type="password" class="form-control" id="password" v-model="form.password" required>
                </div>
                <div class="mb-3">
                  <label for="password_confirm" class="form-label">Confirmar Senha</label>
                  <input type="password" class="form-control" id="password_confirm" v-model="form.password_confirm" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Registar</button>
              </form>
              <p class="text-center">
                Já tem conta? <router-link to="/login">Entrar</router-link>
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

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirm: ''
})

const handleRegister = async () => {
  if (form.value.password !== form.value.password_confirm) {
    alert('As senhas não coincidem')
    return
  }

  const result = await authStore.register(form.value.name, form.value.email, form.value.password)
  if (result.success) {
    alert('Registado com sucesso!')
    router.push('/login')
  } else {
    alert('Erro ao registar')
  }
}
</script>

<style scoped>
.register-page {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card {
  border: none;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  border-radius: 12px;
}
</style>
