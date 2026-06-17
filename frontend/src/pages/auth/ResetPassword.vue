<template>
  <div class="reset-page py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body p-5">
              <h1 class="text-center mb-4">Redefinir Senha</h1>

              <div v-if="errorMessage" class="alert alert-danger" role="alert">
                {{ errorMessage }}
              </div>
              <div v-if="successMessage" class="alert alert-success" role="alert">
                {{ successMessage }}
              </div>

              <form v-if="!successMessage" @submit.prevent="handleRequestReset">
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" v-model="email" required placeholder="Introduza o seu email">
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  Enviar pedido de redefinição
                </button>
              </form>

              <p class="text-center mt-3">
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

const router = useRouter()

const email = ref('')
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const handleRequestReset = async () => {
  errorMessage.value = ''
  loading.value = true

  try {
    const apiBase = import.meta.env.VITE_API_URL || ''
    const res = await fetch(`${apiBase}/api/auth/forgot-password`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value }),
    })
    const json = await res.json()
    loading.value = false

    if (json.success) {
      successMessage.value = json.message || 'Se o email existir, receberá instruções para redefinir a senha.'
    } else {
      errorMessage.value = json.message || 'Erro ao enviar pedido.'
    }
  } catch (err) {
    loading.value = false
    errorMessage.value = 'Erro de conexão.'
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
