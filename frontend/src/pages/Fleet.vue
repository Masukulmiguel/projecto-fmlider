<template>
  <div class="fleet-page">
    <!-- Hero -->
    <section class="fleet-hero">
      <div class="fleet-hero-bg" style="background-image: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(/assets/img/resachstacker/resachstacker1.jpeg);"></div>
      <div class="container position-relative">
        <div class="fleet-hero-content">
          <span class="fml-eyebrow">Frota & Equipamentos</span>
          <h1 class="fleet-hero-title">A Nossa Frota</h1>
          <p class="fleet-hero-subtitle">
            Frota própria moderna e equipamentos de última geração para garantir
            operações seguras, rápidas e eficientes.
          </p>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section class="fleet-stats">
      <div class="container">
        <div class="fleet-stats-grid">
          <div class="fleet-stat" v-for="stat in fleetStats" :key="stat.label">
            <div class="fleet-stat-icon"><i :class="stat.icon"></i></div>
            <div class="fleet-stat-value">{{ stat.value }}</div>
            <div class="fleet-stat-label">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Fleet Categories -->
    <section class="fleet-main fml-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="fml-eyebrow">Os Nossos Equipamentos</span>
          <h2 class="section-title">Frota & Equipamentos</h2>
          <p class="section-subtitle text-muted">Invista na melhor infraestrutura logística de Angola.</p>
        </div>

        <!-- Category Tabs -->
        <div class="fleet-tabs">
          <button
            v-for="cat in categories"
            :key="cat.key"
            class="fleet-tab"
            :class="{ active: selectedCategory === cat.key }"
            @click="selectedCategory = cat.key"
          >
            <i :class="cat.icon"></i> {{ cat.label }}
          </button>
        </div>

        <!-- Fleet Grid -->
        <div class="fleet-grid">
          <div class="fleet-card" v-for="item in filteredItems" :key="item.id">
            <div class="fleet-card-image">
              <img :src="item.image" :alt="item.title">
              <span class="fleet-card-badge">{{ item.categoryLabel }}</span>
            </div>
            <div class="fleet-card-body">
              <h4>{{ item.title }}</h4>
              <p class="fleet-card-desc">{{ item.description }}</p>
              <div class="fleet-card-specs">
                <div class="spec" v-for="spec in item.specs" :key="spec.label">
                  <span class="spec-label">{{ spec.label }}</span>
                  <span class="spec-value">{{ spec.value }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Reachstacker Highlight -->
    <section class="fleet-highlight fml-section bg-fml-navy text-white">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <span class="fml-eyebrow">Destaque</span>
            <h2 class="section-title text-white">Reachstacker Kalmar</h2>
            <p class="text-white-50 mb-4">
              O nosso <strong>Reachstacker Kalmar de 45 toneladas</strong> é a peça central da nossa
              operação de manuseamento de contentores. Adquirido em 2022, este equipamento de última
              geração permite-nos oferecer serviços de carga e descarga mais rápidos, seguros e eficientes.
            </p>
            <div class="highlight-specs">
              <div class="highlight-spec" v-for="spec in reachstackerSpecs" :key="spec.label">
                <div class="highlight-spec-icon"><i :class="spec.icon"></i></div>
                <div>
                  <span class="highlight-spec-label">{{ spec.label }}</span>
                  <span class="highlight-spec-value">{{ spec.value }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="highlight-image">
              <img src="/assets/img/resachstacker/resachstacker1.jpeg" alt="Reachstacker Kalmar" class="img-fluid rounded shadow-lg">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Fleet Gallery -->
    <section class="fleet-gallery fml-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="fml-eyebrow">Galeria</span>
          <h2 class="section-title">A Nossa Frota em Acção</h2>
        </div>
        <div class="gallery-grid">
          <div class="gallery-item" v-for="(img, i) in galleryImages" :key="i" @click="openLightbox(i)">
            <img :src="img.src" :alt="img.caption">
            <div class="gallery-caption">
              <span>{{ img.caption }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Lightbox -->
    <div class="lightbox" v-if="lightboxOpen" @click.self="lightboxOpen = false">
      <button class="lightbox-close" @click="lightboxOpen = false"><i class="bi bi-x-lg"></i></button>
      <button class="lightbox-prev" @click="prevImage"><i class="bi bi-chevron-left"></i></button>
      <button class="lightbox-next" @click="nextImage"><i class="bi bi-chevron-right"></i></button>
      <div class="lightbox-content">
        <img :src="galleryImages[lightboxIndex].src" :alt="galleryImages[lightboxIndex].caption">
        <p>{{ galleryImages[lightboxIndex].caption }}</p>
      </div>
    </div>

    <!-- CTA -->
    <section class="fleet-cta fml-section-gold">
      <div class="container text-center">
        <h2 class="display-5 fw-bold mb-3">Precisa de Transporte ou Armazenagem?</h2>
        <p class="lead mb-4 opacity-90">A nossa frota está pronta para servir o seu negócio.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <router-link to="/contacto" class="btn btn-dark btn-lg">
            <i class="bi bi-envelope me-2"></i> Contactar
          </router-link>
          <router-link to="/cotacoes/novo" class="btn btn-outline-dark btn-lg">
            <i class="bi bi-receipt me-2"></i> Pedir Cotação
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const selectedCategory = ref('all')
const lightboxOpen = ref(false)
const lightboxIndex = ref(0)

const categories = [
  { key: 'all', label: 'Todos', icon: 'bi bi-grid' },
  { key: 'trucks', label: 'Camiões', icon: 'bi bi-truck' },
  { key: 'containers', label: 'Contentores', icon: 'bi bi-box-seam' },
  { key: 'equipment', label: 'Equipamentos', icon: 'bi bi-tools' },
]

const fleetItems = [
  {
    id: 1,
    title: 'Camião Tanque',
    category: 'trucks',
    categoryLabel: 'Camião',
    image: '/assets/img/resachstacker/resachstacker3.jpeg',
    description: 'Camião tanque para transporte de combustíveis, líquidos e gases. Certificado para operações de alto risco.',
    specs: [
      { label: 'Capacidade', value: '30.000L' },
      { label: 'Tipo', value: 'Tanque' },
      { label: 'Certificação', value: 'ATEX' },
    ]
  },
  {
    id: 2,
    title: 'Camião Frigorífico',
    category: 'trucks',
    categoryLabel: 'Camião',
    image: '/assets/img/resachstacker/resachstacker4.jpeg',
    description: 'Transporte de cargas que requerem controlo de temperatura. Ideal para produtos alimentares e farmacêuticos.',
    specs: [
      { label: 'Temperatura', value: '-20°C a +30°C' },
      { label: 'Capacidade', value: '20t' },
      { label: 'Zonas', value: '2 independently' },
    ]
  },
  {
    id: 3,
    title: 'Camião Plataforma',
    category: 'trucks',
    categoryLabel: 'Camião',
    image: '/assets/img/resachstacker/resachstacker5.jpeg',
    description: 'Transporte de cargas especiais, máquinas pesadas e project cargo. Plataforma reforçada para cargas excepcionais.',
    specs: [
      { label: 'Capacidade', value: '40t' },
      { label: 'Comprimento', value: '13.6m' },
      { label: 'Tipo', value: 'Plataforma baixa' },
    ]
  },
  {
    id: 4,
    title: 'Contentor 20"',
    category: 'containers',
    categoryLabel: 'Contentor',
    image: '/assets/img/resachstacker/resachstacker6.jpeg',
    description: 'Contentor standard de 20 pés para transporte marítimo e terrestre. Disponível seco ou frigorífico.',
    specs: [
      { label: 'Tamanho', value: '20 pés' },
      { label: 'Capacidade', value: '28t' },
      { label: 'Volume', value: '33m³' },
    ]
  },
  {
    id: 5,
    title: 'Contentor 40"',
    category: 'containers',
    categoryLabel: 'Contentor',
    image: '/assets/img/resachstacker/resachstacker7.jpeg',
    description: 'Contentor standard de 40 pés para grandes volumes. Ideal para cargas longas e volumosas.',
    specs: [
      { label: 'Tamanho', value: '40 pés' },
      { label: 'Capacidade', value: '28t' },
      { label: 'Volume', value: '67m³' },
    ]
  },
  {
    id: 6,
    title: 'Reachstacker Kalmar',
    category: 'equipment',
    categoryLabel: 'Equipamento',
    image: '/assets/img/resachstacker/resachstacker8.jpeg',
    description: 'Reachstacker de 45 toneladas para manuseamento de contentores. Equipamento de última geração para operações portuárias.',
    specs: [
      { label: 'Capacidade', value: '45t' },
      { label: 'Marca', value: 'Kalmar' },
      { label: 'Stack', value: '4 contentores' },
    ]
  },
]

const filteredItems = computed(() => {
  if (selectedCategory.value === 'all') return fleetItems
  return fleetItems.filter(item => item.category === selectedCategory.value)
})

const fleetStats = [
  { value: '+15', label: 'Camiões Próprios', icon: 'bi bi-truck' },
  { value: '45t', label: 'Reachstacker', icon: 'bi bi-tools' },
  { value: '+50', label: 'Contentores', icon: 'bi bi-box-seam' },
  { value: '24/7', label: 'Operação Contínua', icon: 'bi bi-clock-history' },
]

const reachstackerSpecs = [
  { icon: 'bi bi-speedometer', label: 'Capacidade', value: '45 toneladas' },
  { icon: 'bi bi-box-seam', label: 'Contentores', value: '20" e 40"' },
  { icon: 'bi bi-layout-stack', label: 'Elevação', value: 'Até 4 contentores' },
  { icon: 'bi bi-award', label: 'Operadores', value: 'Certificados Kalmar' },
  { icon: 'bi bi-shield-check', label: 'Manutenção', value: 'Preventiva garantida' },
  { icon: 'bi bi-lightning', label: 'Produtividade', value: '+30 operações/hora' },
]

const galleryImages = [
  { src: '/assets/img/resachstacker/resachstacker1.jpeg', caption: 'Reachstacker Kalmar — Visão Geral' },
  { src: '/assets/img/resachstacker/resachstacker2.jpeg', caption: 'Manuseamento de Contentores' },
  { src: '/assets/img/resachstacker/resachstacker9.jpeg', caption: 'Operação de Carga — Contentores 20" e 40"' },
  { src: '/assets/img/resachstacker/resachstacker10.jpeg', caption: 'Reachstacker em Acção — Porto de Luanda' },
]

const openLightbox = (index) => {
  lightboxIndex.value = index
  lightboxOpen.value = true
}
const prevImage = () => {
  lightboxIndex.value = (lightboxIndex.value - 1 + galleryImages.length) % galleryImages.length
}
const nextImage = () => {
  lightboxIndex.value = (lightboxIndex.value + 1) % galleryImages.length
}
</script>

<style scoped>
/* HERO */
.fleet-hero {
  position: relative;
  height: 50vh;
  min-height: 380px;
  display: flex;
  align-items: center;
  overflow: hidden;
  background: var(--fml-navy, #0f172a);
}
.fleet-hero-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
}
.fleet-hero-content {
  position: relative;
  color: #fff;
  max-width: 700px;
}
.fleet-hero-title {
  font-size: 3.5rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 1rem;
  letter-spacing: -0.03em;
}
.fleet-hero-subtitle {
  font-size: 1.2rem;
  line-height: 1.7;
  opacity: 0.9;
}

/* STATS */
.fleet-stats {
  background: var(--fml-navy, #0f172a);
  padding: 3rem 0;
  margin-top: -1px;
}
.fleet-stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
}
.fleet-stat {
  text-align: center;
}
.fleet-stat-icon {
  font-size: 1.5rem;
  color: var(--fml-gold, #f59e0b);
  margin-bottom: 0.5rem;
}
.fleet-stat-value {
  font-size: 2rem;
  font-weight: 800;
  color: #fff;
  line-height: 1;
}
.fleet-stat-label {
  font-size: 0.78rem;
  color: rgba(255,255,255,0.6);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-top: 0.35rem;
}

/* TABS */
.fleet-tabs {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  margin-bottom: 3rem;
  flex-wrap: wrap;
}
.fleet-tab {
  padding: 0.7rem 1.5rem;
  border: 2px solid #e2e8f0;
  background: #fff;
  border-radius: 50px;
  font-size: 0.9rem;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 0.25s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.fleet-tab:hover {
  border-color: var(--fml-gold, #f59e0b);
  color: var(--fml-gold, #f59e0b);
}
.fleet-tab.active {
  background: var(--fml-gold, #f59e0b);
  border-color: var(--fml-gold, #f59e0b);
  color: var(--fml-900, #1e293b);
}

/* GRID */
.fleet-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}
.fleet-card {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  transition: all 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.fleet-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
  border-color: var(--fml-gold, #f59e0b);
}
.fleet-card-image {
  position: relative;
  height: 220px;
  overflow: hidden;
}
.fleet-card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}
.fleet-card:hover .fleet-card-image img {
  transform: scale(1.08);
}
.fleet-card-badge {
  position: absolute;
  top: 1rem;
  left: 1rem;
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(8px);
  color: #fff;
  padding: 0.3rem 0.9rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.fleet-card-body {
  padding: 1.75rem;
}
.fleet-card-body h4 {
  font-size: 1.2rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.75rem;
}
.fleet-card-desc {
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.6;
  margin-bottom: 1.25rem;
}
.fleet-card-specs {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
  padding-top: 1rem;
  border-top: 1px solid #f1f5f9;
}
.spec {
  text-align: center;
}
.spec-label {
  display: block;
  font-size: 0.7rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.2rem;
}
.spec-value {
  display: block;
  font-size: 0.85rem;
  font-weight: 700;
  color: #0f172a;
}

/* HIGHLIGHT */
.highlight-specs {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}
.highlight-spec {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
}
.highlight-spec-icon {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  flex-shrink: 0;
}
.highlight-spec-label {
  display: block;
  font-size: 0.72rem;
  color: rgba(255,255,255,0.5);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.highlight-spec-value {
  display: block;
  font-size: 0.95rem;
  font-weight: 700;
  color: #fff;
}

/* GALLERY */
.gallery-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}
.gallery-item {
  position: relative;
  border-radius: 14px;
  overflow: hidden;
  cursor: pointer;
  aspect-ratio: 4/3;
}
.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}
.gallery-item:hover img {
  transform: scale(1.08);
}
.gallery-caption {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 1rem;
  background: linear-gradient(to top, rgba(15, 23, 42, 0.9), transparent);
  color: #fff;
  font-size: 0.85rem;
  font-weight: 500;
  opacity: 0;
  transform: translateY(8px);
  transition: all 0.3s ease;
}
.gallery-item:hover .gallery-caption {
  opacity: 1;
  transform: translateY(0);
}

/* LIGHTBOX */
.lightbox {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.95);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}
.lightbox-content {
  text-align: center;
  max-width: 90vw;
  max-height: 90vh;
}
.lightbox-content img {
  max-width: 100%;
  max-height: 80vh;
  border-radius: 8px;
  object-fit: contain;
}
.lightbox-content p {
  color: rgba(255,255,255,0.8);
  margin-top: 1rem;
  font-size: 0.95rem;
}
.lightbox-close {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: none;
  background: rgba(255,255,255,0.1);
  color: #fff;
  font-size: 1.2rem;
  cursor: pointer;
  transition: background 0.2s;
}
.lightbox-close:hover { background: rgba(255,255,255,0.2); }
.lightbox-prev, .lightbox-next {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 50px;
  height: 50px;
  border-radius: 50%;
  border: none;
  background: rgba(255,255,255,0.1);
  color: #fff;
  font-size: 1.5rem;
  cursor: pointer;
  transition: background 0.2s;
}
.lightbox-prev { left: 1.5rem; }
.lightbox-next { right: 1.5rem; }
.lightbox-prev:hover, .lightbox-next:hover { background: rgba(255,255,255,0.2); }

/* CTA */
.fleet-cta {
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  padding: 4.5rem 0;
}

/* RESPONSIVE */
@media (max-width: 991.98px) {
  .fleet-grid { grid-template-columns: repeat(2, 1fr); }
  .highlight-specs { grid-template-columns: 1fr; }
}

@media (max-width: 767.98px) {
  .fleet-hero { height: 40vh; min-height: 300px; }
  .fleet-hero-title { font-size: 2.2rem; }
  .fleet-grid { grid-template-columns: 1fr; }
  .fleet-stats-grid { grid-template-columns: repeat(2, 1fr); }
  .gallery-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
