<template>
  <div class="carousel-container">
    <div class="carousel">
      <div class="carousel-item" v-for="(item, index) in galleryItems" :key="index" v-show="index === currentIndex">
        <img :src="`/assets/img/${item.image}`" :alt="item.title" class="img-fluid">
      </div>
    </div>
    <button class="carousel-prev" @click="previousSlide">←</button>
    <button class="carousel-next" @click="nextSlide">→</button>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  category: String
})

const currentIndex = ref(0)
const galleryItems = ref([
  { title: 'Item 1', image: 'banner1.jpg' },
  { title: 'Item 2', image: 'banner2.jpg' },
  { title: 'Item 3', image: 'banner3.jpg' }
])

const nextSlide = () => {
  currentIndex.value = (currentIndex.value + 1) % galleryItems.value.length
}

const previousSlide = () => {
  currentIndex.value = (currentIndex.value - 1 + galleryItems.value.length) % galleryItems.value.length
}
</script>

<style scoped>
.carousel-container {
  position: relative;
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
}

.carousel {
  width: 100%;
  border-radius: 8px;
  overflow: hidden;
}

.carousel-item {
  width: 100%;
}

.carousel-item img {
  width: 100%;
  height: 400px;
  object-fit: cover;
}

.carousel-prev,
.carousel-next {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.5);
  color: white;
  border: none;
  padding: 1rem;
  cursor: pointer;
  border-radius: 4px;
  font-size: 1.5rem;
}

.carousel-prev {
  left: 10px;
}

.carousel-next {
  right: 10px;
}
</style>
