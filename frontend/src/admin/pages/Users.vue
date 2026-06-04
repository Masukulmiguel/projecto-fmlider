<template>
  <div class="admin-page p-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Utilizadores</h2>
      <button class="btn btn-primary" @click="showAddForm = true">+ Adicionar</button>
    </div>

    <div class="card">
      <div class="card-body">
        <table class="table">
          <thead>
            <tr>
              <th>Nome</th>
              <th>Email</th>
              <th>Telefone</th>
              <th>Função</th>
              <th>Status</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.phone }}</td>
              <td><span class="badge bg-info">{{ user.role }}</span></td>
              <td><span :class="user.status ? 'badge bg-success' : 'badge bg-danger'">{{ user.status ? 'Ativo' : 'Inativo' }}</span></td>
              <td>
                <button class="btn btn-sm btn-warning" @click="editUser(user)">Editar</button>
                <button class="btn btn-sm btn-danger" @click="deleteUser(user.id)">Deletar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Form -->
    <div class="modal" v-if="showAddForm">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingUser ? 'Editar' : 'Adicionar' }} Utilizador</h5>
            <button type="button" class="btn-close" @click="showAddForm = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveUser">
              <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" class="form-control" v-model="formData.name" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" v-model="formData.email" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Telefone</label>
                <input type="tel" class="form-control" v-model="formData.phone">
              </div>
              <div class="mb-3">
                <label class="form-label">Função</label>
                <select class="form-select" v-model="formData.role">
                  <option value="operator">Operador</option>
                  <option value="editor">Editor</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const users = ref([
  { id: 1, name: 'Admin User', email: 'admin@fmlider.co.ao', phone: '+244 935141747', role: 'admin', status: 1 }
])

const showAddForm = ref(false)
const editingUser = ref(null)
const formData = ref({
  name: '',
  email: '',
  phone: '',
  role: 'operator'
})

const editUser = (user) => {
  editingUser.value = user
  formData.value = { ...user }
  showAddForm.value = true
}

const saveUser = () => {
  if (editingUser.value) {
    Object.assign(editingUser.value, formData.value)
  } else {
    users.value.push({ ...formData.value, id: Date.now(), status: 1 })
  }
  showAddForm.value = false
  editingUser.value = null
  formData.value = { name: '', email: '', phone: '', role: 'operator' }
}

const deleteUser = (id) => {
  users.value = users.value.filter(u => u.id !== id)
}
</script>

<style scoped>
.admin-page {
  background: #f8f9fa;
  min-height: 100vh;
}

.modal {
  display: block;
  background: rgba(0, 0, 0, 0.5);
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1050;
}

.card {
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
