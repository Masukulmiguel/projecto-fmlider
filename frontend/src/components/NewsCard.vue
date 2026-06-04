<template>
  <div class="news-card">
    <img :src="`/assets/img/${news.image}`" :alt="news.title" class="card-image">
    <div class="card-content">
      <h5>{{ news.title }}</h5>
      <p class="text-muted">{{ news.date }}</p>
      <p>{{ news.description }}</p>
      <router-link :to="`/noticias/${news.slug}`" class="btn btn-sm btn-primary">Ler Mais</router-link>
    </div>
  </div>
</template>

<script setup>
defineProps({
  news: {
    type: Object,
    required: true
  }
})
</script>

<style scoped>
.news-card {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.news-card:hover {
  transform: translateY(-5px);
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
  margin-bottom: 0.5rem;
  flex: 1;
}
</style>
