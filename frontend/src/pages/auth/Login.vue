<template>
  <div class="login-page py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body p-5">
              <h1 class="text-center mb-4">Login</h1>
              <form @submit.prevent="handleLogin">
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" v-model="form.email" required>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Senha</label>
                  <input type="password" class="form-control" id="password" v-model="form.password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Entrar</button>
              </form>
              <p class="text-center">
                Não tem conta? <router-link to="/registro">Registar-se</router-link>
              </p>
              <p class="text-center">
                <router-link to="/esqueci-senha">Esqueci a senha</router-link>
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
  email: 'admin@fmlider.co.ao',
  password: ''
})

const handleLogin = async () => {
  const result = await authStore.login(form.value.email, form.value.password)
  if (result.success) {
    router.push('/admin')
  } else {
    alert('Erro ao fazer login')
  }
}
</script>

<style scoped>
.login-page {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card {
  border: none;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  border-radius: 12px;
}
</style>
