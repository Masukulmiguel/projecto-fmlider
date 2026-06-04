<template>
  <div class="admin-page p-5">
    <div class="d-flex justify-content-between mb-4">
      <h2>Parceiros</h2>
      <button class="btn btn-primary">+ Adicionar</button>
    </div>
    <div class="card">
      <table class="table">
        <tbody>
          <tr v-for="i in 5" :key="i">
            <td>Partner {{ i }}</td>
            <td><button class="btn btn-sm btn-warning">Editar</button></td>
            <td><button class="btn btn-sm btn-danger">Deletar</button></td>
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
