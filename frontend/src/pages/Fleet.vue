<template>
  <div class="fleet-page py-5">
    <div class="container">
      <h1 class="mb-5">Nossa Frota</h1>
      <div class="row mb-5">
        <div class="col-lg-12">
          <div class="btn-group mb-4" role="group">
            <button type="button" class="btn btn-outline-primary" @click="selectedCategory = 'all'" :class="{ active: selectedCategory === 'all' }">Todos</button>
            <button type="button" class="btn btn-outline-primary" @click="selectedCategory = 'trucks'" :class="{ active: selectedCategory === 'trucks' }">Camiões</button>
            <button type="button" class="btn btn-outline-primary" @click="selectedCategory = 'containers'" :class="{ active: selectedCategory === 'containers' }">Contentores</button>
            <button type="button" class="btn btn-outline-primary" @click="selectedCategory = 'equipment'" :class="{ active: selectedCategory === 'equipment' }">Equipamentos</button>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-4 col-md-6 mb-4" v-for="item in galleryItems" :key="item.id">
          <div class="gallery-item">
            <img :src="`/assets/img/${item.image}`" class="img-fluid rounded" :alt="item.title" @click="enlargeImage(item.image)">
            <p class="mt-2">{{ item.title }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const selectedCategory = ref('all')
const galleryItems = ref([
  { id: 1, title: 'Camião 1', image: 'banner1.jpg', category: 'trucks' },
  { id: 2, title: 'Camião 2', image: 'banner2.jpg', category: 'trucks' },
  { id: 3, title: 'Contentor 1', image: 'banner3.jpg', category: 'containers' }
])

const enlargeImage = (image) => {
  // Open image in modal
  console.log('Enlarging:', image)
}
</script>

<style scoped>
.fleet-page {
  background: #f9f9f9;
  min-height: 100vh;
}

.gallery-item {
  cursor: pointer;
  transition: transform 0.3s ease;
}

.gallery-item:hover {
  transform: scale(1.05);
}

.gallery-item img {
  height: 250px;
  object-fit: cover;
}
</style>
