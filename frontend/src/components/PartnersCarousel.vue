<template>
  <div class="partners-carousel">
    <div class="carousel-track">
      <div class="carousel-item" v-for="(partner, index) in partners" :key="index">
        <img :src="`/assets/img/${partner.logo}`" :alt="partner.name" class="partner-logo">
        <p>{{ partner.name }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const partners = ref([
  { name: 'Partner 1', logo: 'partner1.webp' },
  { name: 'Partner 2', logo: 'partner2.png' },
  { name: 'Partner 3', logo: 'partner3.png' },
  { name: 'Partner 4', logo: 'partner4.webp' }
])
</script>

<style scoped>
.partners-carousel {
  width: 100%;
  overflow: hidden;
}

.carousel-track {
  display: flex;
  justify-content: space-around;
  align-items: center;
  flex-wrap: wrap;
  gap: 2rem;
}

.carousel-item {
  text-align: center;
}

.partner-logo {
  max-width: 150px;
  height: 100px;
  object-fit: contain;
  transition: transform 0.3s ease;
}

.carousel-item:hover .partner-logo {
  transform: scale(1.1);
}
</style>
