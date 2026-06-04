<template>
  <div class="news-page py-5">
    <div class="container">
      <h1 class="mb-5">Notícias</h1>
      <div class="row">
        <div class="col-lg-8">
          <div class="news-item mb-5" v-for="newsItem in news" :key="newsItem.id">
            <img :src="`/assets/img/${newsItem.image}`" class="img-fluid rounded mb-3" :alt="newsItem.title">
            <h3>{{ newsItem.title }}</h3>
            <p class="text-muted">{{ newsItem.date }}</p>
            <p>{{ newsItem.description }}</p>
            <router-link :to="`/noticias/${newsItem.slug}`" class="btn btn-primary">Ler Mais</router-link>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="sidebar">
            <h4>Categorias</h4>
            <ul class="list-group">
              <li class="list-group-item"><a href="#">Investimentos</a></li>
              <li class="list-group-item"><a href="#">Avisos</a></li>
              <li class="list-group-item"><a href="#">Manutenções</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const news = ref([
  { id: 1, title: 'Notícia 1', slug: 'noticia-1', image: 'banner1.jpg', date: '01/01/2024', description: 'Conteúdo da notícia...' },
  { id: 2, title: 'Notícia 2', slug: 'noticia-2', image: 'banner2.jpg', date: '02/01/2024', description: 'Conteúdo da notícia...' }
])
</script>

<style scoped>
.news-page {
  background: #f9f9f9;
  min-height: 100vh;
}

.news-item {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.sidebar {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.sidebar a {
  color: #007bff;
  text-decoration: none;
}

.sidebar a:hover {
  text-decoration: underline;
}
</style>
