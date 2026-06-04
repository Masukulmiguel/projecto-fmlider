<template>
  <div class="services-page py-5">
    <div class="container">
      <h1 class="mb-5">Nossos Serviços</h1>
      <div class="row">
        <div class="col-lg-3 col-md-6 mb-4" v-for="service in services" :key="service.id">
          <div class="card service-card h-100">
            <img :src="`/assets/img/${service.image}`" class="card-img-top" :alt="service.title">
            <div class="card-body">
              <h5 class="card-title">{{ service.title }}</h5>
              <p class="card-text">{{ service.description }}</p>
              <router-link :to="`/servicos/${service.slug}`" class="btn btn-primary">Saiba Mais</router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const services = ref([
  {
    id: 1,
    title: 'Desembaraço Aduaneiro',
    slug: 'desembaraco-aduaneiro',
    description: 'Especialistas em importação, exportação e processos aduaneiros em Angola.',
    image: 'service-aduaneiro.jpg'
  },
  {
    id: 2,
    title: 'Transportes',
    slug: 'transportes',
    description: 'Transporte seguro de cargas em todo território nacional.',
    image: 'service-truck.jpg'
  },
  {
    id: 3,
    title: 'Armazenagem',
    slug: 'armazenagem',
    description: 'Espaços preparados para armazenagem de mercadorias e contentores.',
    image: 'service-storage.jpg'
  },
  {
    id: 4,
    title: 'Door To Door',
    slug: 'door-to-door',
    description: 'Soluções completas desde a origem até ao destino.',
    image: 'service-door.jpg'
  }
])
</script>

<style scoped>
.services-page {
  background: #f9f9f9;
  min-height: 100vh;
}

.service-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.service-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

.card-img-top {
  height: 200px;
  object-fit: cover;
}
</style>
