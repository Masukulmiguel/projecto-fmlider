<template>
  <div class="forgot-page py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body p-5">
              <h1 class="text-center mb-4">Esqueci a Senha</h1>
              <form @submit.prevent="handleReset">
                <p class="mb-3">Insira o seu email para receber instruções de redefinição de senha.</p>
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" v-model="form.email" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Enviar</button>
              </form>
              <p class="text-center">
                <router-link to="/login">Voltar ao login</router-link>
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
  email: ''
})

const handleReset = async () => {
  const result = await authStore.resetPassword(form.value.email)
  if (result.success) {
    alert('Email enviado com sucesso!')
    router.push('/login')
  } else {
    alert('Erro ao enviar email')
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
