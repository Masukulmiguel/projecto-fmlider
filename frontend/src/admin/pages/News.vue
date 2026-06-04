<template>
  <div class="admin-page p-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Notícias</h2>
      <button class="btn btn-primary">+ Adicionar</button>
    </div>
    <div class="card">
      <table class="table">
        <tbody>
          <tr v-for="item in news" :key="item.id">
            <td>{{ item.title }}</td>
            <td><button class="btn btn-sm btn-warning">Editar</button></td>
            <td><button class="btn btn-sm btn-danger">Deletar</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const news = ref([])
</script>

<style scoped>
.admin-page { background: #f8f9fa; }
.card { border: none; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
</style>
