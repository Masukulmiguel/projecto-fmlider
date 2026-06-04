<template>
  <div class="admin-page p-5">
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h5>Perfil</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="saveProfile">
              <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" class="form-control" v-model="profile.name">
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" v-model="profile.email">
              </div>
              <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="tel" class="form-control" v-model="profile.phone">
              </div>
              <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h5>Alterar Senha</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="changePassword">
              <div class="mb-3">
                <label class="form-label">Senha Atual</label>
                <input type="password" class="form-control" v-model="passwordForm.current">
              </div>
              <div class="mb-3">
                <label class="form-label">Nova Senha</label>
                <input type="password" class="form-control" v-model="passwordForm.new">
              </div>
              <div class="mb-3">
                <label class="form-label">Confirmar Senha</label>
                <input type="password" class="form-control" v-model="passwordForm.confirm">
              </div>
              <button type="submit" class="btn btn-primary">Alterar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const profile = ref({
  name: 'Admin User',
  email: 'admin@fmlider.co.ao',
  phone: '+244 935141747'
})

const passwordForm = ref({
  current: '',
  new: '',
  confirm: ''
})

const saveProfile = () => {
  alert('Perfil atualizado')
}

const changePassword = () => {
  if (passwordForm.value.new !== passwordForm.value.confirm) {
    alert('As senhas não coincidem')
    return
  }
  alert('Senha alterada')
}
</script>

<style scoped>
.admin-page { background: #f8f9fa; }
.card { border: none; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
.card-header { background: white; border-bottom: 1px solid #ddd; }
</style>
