<template>
  <div class="service-card">
    <img :src="`/assets/img/${service.image}`" :alt="service.title" class="card-image">
    <div class="card-content">
      <h5>{{ service.title }}</h5>
      <p>{{ service.description }}</p>
      <router-link :to="`/servicos/${service.slug}`" class="btn btn-sm btn-primary">Saiba Mais</router-link>
    </div>
  </div>
</template>

<script setup>
defineProps({
  service: {
    type: Object,
    required: true
  }
})
</script>

<style scoped>
.service-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.service-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.card-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.card-content {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.card-content h5 {
  margin-bottom: 0.5rem;
  color: #333;
}

.card-content p {
  color: #666;
  margin-bottom: 1rem;
  flex: 1;
}
</style>
