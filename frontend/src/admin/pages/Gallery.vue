<template>
  <div class="admin-page p-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Galeria</h2>
      <button class="btn btn-primary">+ Upload</button>
    </div>
    <div class="card">
      <div class="gallery-grid">
        <div class="gallery-item" v-for="i in 12" :key="i">
          <img src="/assets/img/banner1.jpg" alt="">
          <button class="btn btn-sm btn-danger">Deletar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin-page { background: #f8f9fa; }
.card { border: none; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
.gallery-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.gallery-item { position: relative; }
.gallery-item img { width: 100%; height: 150px; object-fit: cover; border-radius: 4px; }
</style>
