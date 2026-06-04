<template>
  <div class="gallery-page py-5">
    <div class="container">
      <h1 class="mb-5">Galeria</h1>
      <div class="row">
        <div class="col-lg-3 col-md-6 mb-4" v-for="item in gallery" :key="item.id">
          <div class="gallery-item">
            <img :src="`/assets/img/${item.image}`" class="img-fluid rounded" :alt="item.title" @click="enlargeImage(item.image)">
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const gallery = ref([
  { id: 1, title: 'Imagem 1', image: 'service1.jpg' },
  { id: 2, title: 'Imagem 2', image: 'service2.jpg' },
  { id: 3, title: 'Imagem 3', image: 'service3.jpg' },
  { id: 4, title: 'Imagem 4', image: 'banner1.jpg' }
])

const enlargeImage = (image) => {
  console.log('Enlarging:', image)
}
</script>

<style scoped>
.gallery-page {
  background: #f9f9f9;
  min-height: 100vh;
}

.gallery-item {
  cursor: pointer;
  overflow: hidden;
  border-radius: 8px;
}

.gallery-item img {
  height: 250px;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.gallery-item:hover img {
  transform: scale(1.1);
}
</style>
