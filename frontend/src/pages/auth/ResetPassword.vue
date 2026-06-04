<template>
  <div class="reset-page py-5 min-vh-100 d-flex align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card">
            <div class="card-body p-5">
              <h1 class="text-center mb-4">Redefinir Senha</h1>
              <form @submit.prevent="handleResetPassword">
                <div class="mb-3">
                  <label for="password" class="form-label">Nova Senha</label>
                  <input type="password" class="form-control" id="password" v-model="form.password" required>
                </div>
                <div class="mb-3">
                  <label for="password_confirm" class="form-label">Confirmar Senha</label>
                  <input type="password" class="form-control" id="password_confirm" v-model="form.password_confirm" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Redefinir</button>
              </form>
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

const form = ref({
  password: '',
  password_confirm: ''
})

const handleResetPassword = async () => {
  if (form.value.password !== form.value.password_confirm) {
    alert('As senhas não coincidem')
    return
  }

  alert('Senha redefinida com sucesso!')
  router.push('/login')
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
