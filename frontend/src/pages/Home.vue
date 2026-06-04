<template>
  <div class="home">
    <!-- Hero Carousel -->
    <section class="hero-carousel">
      <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active" v-for="(slide, index) in slides" :key="index" :class="{ active: index === currentSlide }">
            <img :src="`/assets/img/${slide.image}`" :alt="slide.title" class="d-block w-100">
            <div class="carousel-overlay"></div>
            <div class="carousel-caption">
              <h1 class="hero-title" data-aos="fade-up">{{ slide.title }}</h1>
              <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="100">{{ slide.description }}</p>
              <button class="btn btn-primary btn-lg" @click="navigateToSlide(slide.link)" data-aos="fade-up" data-aos-delay="200">
                {{ slide.buttonText }}
              </button>
            </div>
          </div>
        </div>
        <button class="carousel-control-prev" @click="previousSlide">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" @click="nextSlide">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
    </section>

    <!-- About Company Section -->
    <section class="about-section py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <h2>Sobre a FMLider</h2>
            <p>A FMLider iniciou a sua actividade em Fevereiro de 2017 em Luanda. Atualmente conta com cerca de 60 colaboradores, instalações próprias e frota de camiões própria.</p>
            <div class="about-stats">
              <div class="stat">
                <h3>+8</h3>
                <p>Anos de Experiência</p>
              </div>
              <div class="stat">
                <h3>+60</h3>
                <p>Colaboradores</p>
              </div>
              <div class="stat">
                <h3>+32</h3>
                <p>Países Parceiros</p>
              </div>
              <div class="stat">
                <h3>+1000</h3>
                <p>Clientes Atendidos</p>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <img src="/assets/img/banner1.jpg" alt="FMLider" class="img-fluid rounded">
          </div>
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section class="services-section py-5 bg-light">
      <div class="container">
        <h2 class="section-title text-center mb-5">Serviços</h2>
        <div class="row">
          <div class="col-lg-3 col-md-6 mb-4" v-for="service in services" :key="service.id">
            <ServiceCard :service="service" />
          </div>
        </div>
      </div>
    </section>

    <!-- Numbers Section -->
    <section class="numbers-section py-5">
      <div class="container">
        <div class="row text-center">
          <div class="col-md-3">
            <Counter :endValue="8" label="Anos de Experiência" />
          </div>
          <div class="col-md-3">
            <Counter :endValue="60" label="Colaboradores" />
          </div>
          <div class="col-md-3">
            <Counter :endValue="32" label="Países Parceiros" />
          </div>
          <div class="col-md-3">
            <Counter :endValue="1000" label="Clientes Atendidos" />
          </div>
        </div>
      </div>
    </section>

    <!-- Fleet Section -->
    <section class="fleet-section py-5 bg-light">
      <div class="container">
        <h2 class="section-title text-center mb-5">Nossa Frota</h2>
        <div class="row">
          <div class="col-lg-12">
            <GalleryCarousel category="trucks" />
          </div>
        </div>
      </div>
    </section>

    <!-- Reachstacker Section -->
    <section class="reachstacker-section py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <img src="/assets/img/banner2.jpg" alt="Reachstacker" class="img-fluid rounded">
          </div>
          <div class="col-lg-6">
            <h2>Reachstacker Kalmar</h2>
            <p>A FMLider adquiriu em 2022 um Reachstacker Kalmar para optimizar o manuseamento de contentores, ferro e cargas especiais.</p>
            <p>Este investimento representa uma mais-valia significativa para a nossa estrutura e para os clientes.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- News Section -->
    <section class="news-section py-5 bg-light">
      <div class="container">
        <h2 class="section-title text-center mb-5">Notícias</h2>
        <div class="row">
          <div class="col-lg-4" v-for="newsItem in latestNews" :key="newsItem.id">
            <NewsCard :news="newsItem" />
          </div>
        </div>
      </div>
    </section>

    <!-- Partners Section -->
    <section class="partners-section py-5">
      <div class="container">
        <h2 class="section-title text-center mb-5">Parceiros</h2>
        <PartnersCarousel />
      </div>
    </section>

    <!-- Contact CTA -->
    <section class="contact-cta py-5 bg-primary text-white text-center">
      <div class="container">
        <h2>Tem um Projeto em Mente?</h2>
        <p>Entre em contacto connosco para mais informações</p>
        <router-link to="/contacto" class="btn btn-light btn-lg">Solicitar Cotação</router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import ServiceCard from '@/components/ServiceCard.vue'
import Counter from '@/components/Counter.vue'
import GalleryCarousel from '@/components/GalleryCarousel.vue'
import NewsCard from '@/components/NewsCard.vue'
import PartnersCarousel from '@/components/PartnersCarousel.vue'

const router = useRouter()
const currentSlide = ref(0)

const slides = [
  {
    image: 'banner1.jpg',
    title: 'DESEMBARAÇO ADUANEIRO',
    description: 'Especialistas em importação, exportação e processos aduaneiros em Angola.',
    buttonText: 'Saiba Mais',
    link: 'aduaneiro'
  },
  {
    image: 'banner2.jpg',
    title: 'FROTA PRÓPRIA DE TRANSPORTES',
    description: 'Transporte seguro de cargas em todo território nacional.',
    buttonText: 'Ver Frota',
    link: 'frota'
  },
  {
    image: 'banner3.jpg',
    title: 'ARMAZENAGEM SEGURA',
    description: 'Espaços preparados para armazenagem de mercadorias e contentores.',
    buttonText: 'Conhecer Serviço',
    link: 'armazenagem'
  }
]

const services = ref([
  { id: 1, title: 'Desembaraço Aduaneiro', slug: 'desembaraco-aduaneiro', image: 'service-aduaneiro.jpg' },
  { id: 2, title: 'Transportes', slug: 'transportes', image: 'service-truck.jpg' },
  { id: 3, title: 'Armazenagem', slug: 'armazenagem', image: 'service-storage.jpg' },
  { id: 4, title: 'Door To Door', slug: 'door-to-door', image: 'service-door.jpg' }
])

const latestNews = ref([])

const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % slides.length
}

const previousSlide = () => {
  currentSlide.value = (currentSlide.value - 1 + slides.length) % slides.length
}

const navigateToSlide = (link) => {
  if (link === 'frota') {
    router.push('/frota')
  } else if (link === 'aduaneiro' || link === 'armazenagem') {
    router.push('/servicos')
  }
}

onMounted(() => {
  // Auto-rotate carousel every 5 seconds
  setInterval(() => {
    nextSlide()
  }, 5000)
})
</script>

<style scoped>
.hero-carousel {
  position: relative;
  overflow: hidden;
  height: 600px;
}

.carousel-item {
  height: 600px;
  position: relative;
}

.carousel-item img {
  height: 100%;
  object-fit: cover;
}

.carousel-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
}

.carousel-caption {
  position: absolute;
  bottom: 50%;
  left: 50%;
  transform: translate(-50%, 50%);
  text-align: center;
  width: 100%;
  color: white;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.hero-subtitle {
  font-size: 1.5rem;
  margin-bottom: 2rem;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
}

.about-section {
  background: white;
}

.about-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
  margin-top: 2rem;
}

.stat {
  text-align: center;
}

.stat h3 {
  font-size: 2rem;
  color: #007bff;
  font-weight: bold;
}

.section-title {
  font-size: 2.5rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 2rem;
}

.numbers-section {
  background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
  color: white;
}

.numbers-section h3 {
  font-size: 2.5rem;
  font-weight: bold;
}

.numbers-section p {
  font-size: 1rem;
  margin-top: 0.5rem;
}

.contact-cta {
  background: #007bff !important;
}

.contact-cta h2 {
  font-size: 2rem;
  margin-bottom: 1rem;
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2rem;
  }

  .hero-subtitle {
    font-size: 1rem;
  }

  .about-stats {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
