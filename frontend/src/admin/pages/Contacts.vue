<template>
  <div class="admin-page p-5">
    <h2 class="mb-4">Contactos</h2>
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Assunto</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="i in 5" :key="i">
            <td>Contacto {{ i }}</td>
            <td>user@example.com</td>
            <td>Assunto</td>
            <td><span class="badge bg-warning">Pendente</span></td>
            <td>
              <button class="btn btn-sm btn-info">Ver</button>
              <button class="btn btn-sm btn-success">Responder</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.admin-page { background: #f8f9fa; }
.card { border: none; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
</style>
