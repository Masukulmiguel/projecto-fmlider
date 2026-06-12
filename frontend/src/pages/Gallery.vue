<template>
  <div class="gallery-page">
    <!-- Hero -->
    <section class="gal-hero">
      <div class="gal-hero-bg" style="background-image: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(/assets/img/construcao2020/image1.jpeg);"></div>
      <div class="container position-relative">
        <div class="gal-hero-content">
          <span class="fml-eyebrow">Galeria</span>
          <h1 class="gal-hero-title">A Nossa Galeria</h1>
          <p class="gal-hero-subtitle">
            Imagens da nossa infraestrutura, equipa, equipamentos e operações.
          </p>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section class="gal-stats">
      <div class="container">
        <div class="gal-stats-grid">
          <div class="gal-stat" v-for="stat in stats" :key="stat.label">
            <div class="gal-stat-icon"><i :class="stat.icon"></i></div>
            <div class="gal-stat-value">{{ stat.value }}</div>
            <div class="gal-stat-label">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Gallery -->
    <section class="gal-content fml-section">
      <div class="container">
        <!-- Filters -->
        <div class="gal-filters">
          <button
            v-for="cat in categories"
            :key="cat.key"
            class="gal-filter"
            :class="{ active: activeFilter === cat.key }"
            @click="activeFilter = cat.key"
          >
            <i :class="cat.icon"></i> {{ cat.label }}
          </button>
        </div>

        <!-- Grid -->
        <div class="gal-grid" ref="gridRef">
          <div
            class="gal-item"
            v-for="(item, index) in filteredImages"
            :key="item.id"
            :class="item.size"
            @click="openLightbox(index)"
            :style="{ animationDelay: `${index * 0.08}s` }"
          >
            <img :src="item.src" :alt="item.caption" loading="lazy">
            <div class="gal-item-overlay">
              <div class="gal-item-icon"><i class="bi bi-arrows-fullscreen"></i></div>
              <div class="gal-item-info">
                <span class="gal-item-category">{{ item.category }}</span>
                <h4>{{ item.caption }}</h4>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredImages.length === 0" class="gal-empty">
          <i class="bi bi-images"></i>
          <p>Nenhuma imagem encontrada nesta categoria.</p>
        </div>
      </div>
    </section>

    <!-- Lightbox -->
    <Teleport to="body">
      <div class="lightbox" v-if="lightboxOpen" @click.self="closeLightbox">
        <button class="lb-close" @click="closeLightbox"><i class="bi bi-x-lg"></i></button>
        <button class="lb-prev" @click="prevImage"><i class="bi bi-chevron-left"></i></button>
        <button class="lb-next" @click="nextImage"><i class="bi bi-chevron-right"></i></button>
        <div class="lb-content">
          <img :src="filteredImages[lightboxIndex]?.src" :alt="filteredImages[lightboxIndex]?.caption">
          <div class="lb-caption">
            <span class="lb-cat">{{ filteredImages[lightboxIndex]?.category }}</span>
            <h5>{{ filteredImages[lightboxIndex]?.caption }}</h5>
            <span class="lb-counter">{{ lightboxIndex + 1 }} / {{ filteredImages.length }}</span>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const activeFilter = ref('all')
const lightboxOpen = ref(false)
const lightboxIndex = ref(0)
const gridRef = ref(null)

const categories = [
  { key: 'all', label: 'Todas', icon: 'bi bi-grid' },
  { key: 'Construção', label: 'Construção', icon: 'bi bi-building' },
  { key: 'Pessoal', label: 'Equipa', icon: 'bi bi-people' },
  { key: 'Reachstacker', label: 'Reachstacker', icon: 'bi bi-tools' },
  { key: 'Serviços', label: 'Serviços', icon: 'bi bi-box-seam' },
]

const allImages = [
  // Construção
  { id: 1, src: '/assets/img/construcao2020/image1.jpeg', caption: 'Base FMLider — Vista panorâmica', category: 'Construção', size: 'large' },
  { id: 2, src: '/assets/img/construcao2020/image2.jpeg', caption: 'Armazéns e Infraestrutura', category: 'Construção', size: 'normal' },
  { id: 3, src: '/assets/img/construcao2020/image3.jpeg', caption: 'Zona de Operações', category: 'Construção', size: 'normal' },
  { id: 4, src: '/assets/img/construcao2020/image4.jpeg', caption: 'Instalações FMLider', category: 'Construção', size: 'normal' },
  { id: 5, src: '/assets/img/construcao2020/image5.jpeg', caption: 'Escritórios e Operações', category: 'Construção', size: 'normal' },

  // Pessoal
  { id: 6, src: '/assets/img/pessoal/service3.jpg', caption: 'A Nossa Equipa', category: 'Pessoal', size: 'large' },
  { id: 7, src: '/assets/img/pessoal/partner1.webp', caption: 'Colaboradores em Acção', category: 'Pessoal', size: 'normal' },
  { id: 8, src: '/assets/img/pessoal/partner2.png', caption: 'Reunião de Equipa', category: 'Pessoal', size: 'normal' },

  // Reachstacker
  { id: 9, src: '/assets/img/resachstacker/resachstacker1.jpeg', caption: 'Reachstacker Kalmar — Visão Geral', category: 'Reachstacker', size: 'large' },
  { id: 10, src: '/assets/img/resachstacker/resachstacker2.jpeg', caption: 'Manuseamento de Contentores', category: 'Reachstacker', size: 'normal' },
  { id: 11, src: '/assets/img/resachstacker/resachstacker3.jpeg', caption: 'Operação de Carga', category: 'Reachstacker', size: 'normal' },
  { id: 12, src: '/assets/img/resachstacker/resachstacker4.jpeg', caption: 'Contentores Empilhados', category: 'Reachstacker', size: 'normal' },
  { id: 13, src: '/assets/img/resachstacker/resachstacker5.jpeg', caption: 'Reachstacker em Acção', category: 'Reachstacker', size: 'normal' },
  { id: 14, src: '/assets/img/resachstacker/resachstacker6.jpeg', caption: 'Vista Lateral', category: 'Reachstacker', size: 'normal' },
  { id: 15, src: '/assets/img/resachstacker/resachstacker7.jpeg', caption: 'Operação Portuária', category: 'Reachstacker', size: 'normal' },
  { id: 16, src: '/assets/img/resachstacker/resachstacker8.jpeg', caption: 'Manobras de Carga', category: 'Reachstacker', size: 'normal' },
  { id: 17, src: '/assets/img/resachstacker/resachstacker9.jpeg', caption: 'Reachstacker Kalmar 45t', category: 'Reachstacker', size: 'normal' },
  { id: 18, src: '/assets/img/resachstacker/resachstacker10.jpeg', caption: 'Contentores 20" e 40"', category: 'Reachstacker', size: 'normal' },
  { id: 19, src: '/assets/img/resachstacker/resachstacker11.jpeg', caption: 'Zona de Armazenagem', category: 'Reachstacker', size: 'normal' },
  { id: 20, src: '/assets/img/resachstacker/resachstacker12.jpeg', caption: 'Frota de Equipamentos', category: 'Reachstacker', size: 'normal' },

  // Serviços
  { id: 21, src: '/assets/img/servico/service1.jpg', caption: 'Consultoria Logística', category: 'Serviços', size: 'normal' },
  { id: 22, src: '/assets/img/servico/Logística Marítima-1.jpg', caption: 'Logística Marítima', category: 'Serviços', size: 'normal' },
  { id: 23, src: '/assets/img/servico/service-storage.jpg', caption: 'Armazenagem Segura', category: 'Serviços', size: 'normal' },
  { id: 24, src: '/assets/img/servico/service-door.jpg', caption: 'Door To Door', category: 'Serviços', size: 'normal' },
  { id: 25, src: '/assets/img/servico/Desembaraço Aduaneiro.jpeg', caption: 'Desembaraço Aduaneiro', category: 'Serviços', size: 'normal' },
  { id: 26, src: '/assets/img/servico/Transportes.jpg', caption: 'Transportes', category: 'Serviços', size: 'normal' },
]

const filteredImages = computed(() => {
  if (activeFilter.value === 'all') return allImages
  return allImages.filter(img => img.category === activeFilter.value)
})

const stats = [
  { value: '24', label: 'Imagens', icon: 'bi bi-images' },
  { value: '4', label: 'Categorias', icon: 'bi bi-folder2' },
  { value: '+60', label: 'Colaboradores', icon: 'bi bi-people' },
  { value: '8+', label: 'Anos', icon: 'bi bi-calendar-check' },
]

const openLightbox = (index) => {
  lightboxIndex.value = index
  lightboxOpen.value = true
  document.body.style.overflow = 'hidden'
}

const closeLightbox = () => {
  lightboxOpen.value = false
  document.body.style.overflow = ''
}

const prevImage = () => {
  lightboxIndex.value = (lightboxIndex.value - 1 + filteredImages.value.length) % filteredImages.value.length
}

const nextImage = () => {
  lightboxIndex.value = (lightboxIndex.value + 1) % filteredImages.value.length
}

const handleKeydown = (e) => {
  if (!lightboxOpen.value) return
  if (e.key === 'Escape') closeLightbox()
  if (e.key === 'ArrowLeft') prevImage()
  if (e.key === 'ArrowRight') nextImage()
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})
</script>

<style scoped>
/* HERO */
.gal-hero {
  position: relative;
  height: 45vh;
  min-height: 340px;
  display: flex;
  align-items: center;
  overflow: hidden;
  background: var(--fml-navy, #0f172a);
}
.gal-hero-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
}
.gal-hero-content {
  position: relative;
  color: #fff;
  max-width: 700px;
}
.gal-hero-title {
  font-size: 3.2rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 1rem;
  letter-spacing: -0.03em;
}
.gal-hero-subtitle {
  font-size: 1.15rem;
  line-height: 1.7;
  opacity: 0.9;
}

/* STATS */
.gal-stats {
  background: var(--fml-navy, #0f172a);
  padding: 3rem 0;
  margin-top: -1px;
}
.gal-stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
}
.gal-stat { text-align: center; }
.gal-stat-icon {
  font-size: 1.5rem;
  color: var(--fml-gold, #f59e0b);
  margin-bottom: 0.5rem;
}
.gal-stat-value {
  font-size: 2rem;
  font-weight: 800;
  color: #fff;
  line-height: 1;
}
.gal-stat-label {
  font-size: 0.78rem;
  color: rgba(255,255,255,0.6);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-top: 0.35rem;
}

/* FILTERS */
.gal-filters {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  margin-bottom: 3rem;
  flex-wrap: wrap;
}
.gal-filter {
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
.gal-filter:hover {
  border-color: var(--fml-gold, #f59e0b);
  color: var(--fml-gold, #f59e0b);
}
.gal-filter.active {
  background: var(--fml-gold, #f59e0b);
  border-color: var(--fml-gold, #f59e0b);
  color: var(--fml-900, #1e293b);
}

/* GRID */
.gal-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.gal-item {
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  cursor: pointer;
  aspect-ratio: 4/3;
  opacity: 0;
  animation: fadeInUp 0.6s ease forwards;
}
.gal-item.large {
  grid-column: span 2;
  aspect-ratio: 16/9;
}

.gal-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.gal-item:hover img {
  transform: scale(1.1);
}

.gal-item-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(15, 23, 42, 0.9) 0%, rgba(15, 23, 42, 0.1) 50%, transparent 100%);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 1.5rem;
  opacity: 0;
  transition: opacity 0.4s ease;
}
.gal-item:hover .gal-item-overlay {
  opacity: 1;
}

.gal-item-icon {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0.5);
  width: 56px;
  height: 56px;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--fml-navy, #0f172a);
  font-size: 1.2rem;
  transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.gal-item:hover .gal-item-icon {
  transform: translate(-50%, -50%) scale(1);
}

.gal-item-category {
  display: inline-block;
  background: var(--fml-gold, #f59e0b);
  color: var(--fml-900, #1e293b);
  padding: 0.2rem 0.7rem;
  border-radius: 12px;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
  width: fit-content;
}
.gal-item-info h4 {
  color: #fff;
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
  transform: translateY(10px);
  transition: transform 0.4s ease;
}
.gal-item:hover .gal-item-info h4 {
  transform: translateY(0);
}

/* EMPTY */
.gal-empty {
  text-align: center;
  padding: 4rem 2rem;
  color: #94a3b8;
}
.gal-empty i {
  font-size: 3rem;
  margin-bottom: 1rem;
  display: block;
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
  animation: fadeIn 0.3s ease;
}
.lb-content {
  text-align: center;
  max-width: 90vw;
  max-height: 90vh;
}
.lb-content img {
  max-width: 100%;
  max-height: 75vh;
  border-radius: 8px;
  object-fit: contain;
  animation: zoomIn 0.3s ease;
}
.lb-caption {
  margin-top: 1rem;
}
.lb-cat {
  display: inline-block;
  background: var(--fml-gold, #f59e0b);
  color: var(--fml-900, #1e293b);
  padding: 0.2rem 0.7rem;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}
.lb-caption h5 {
  color: #fff;
  margin: 0.5rem 0;
}
.lb-counter {
  color: rgba(255,255,255,0.5);
  font-size: 0.85rem;
}
.lb-close, .lb-prev, .lb-next {
  position: absolute;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: #fff;
  cursor: pointer;
  transition: background 0.2s;
  z-index: 10;
}
.lb-close:hover, .lb-prev:hover, .lb-next:hover {
  background: rgba(255, 255, 255, 0.2);
}
.lb-close {
  top: 1.5rem;
  right: 1.5rem;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  font-size: 1.1rem;
}
.lb-prev, .lb-next {
  top: 50%;
  transform: translateY(-50%);
  width: 50px;
  height: 50px;
  border-radius: 50%;
  font-size: 1.5rem;
}
.lb-prev { left: 1.5rem; }
.lb-next { right: 1.5rem; }

/* ANIMATIONS */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes zoomIn {
  from { opacity: 0; transform: scale(0.9); }
  to { opacity: 1; transform: scale(1); }
}

/* RESPONSIVE */
@media (max-width: 991.98px) {
  .gal-grid { grid-template-columns: repeat(2, 1fr); }
  .gal-item.large { grid-column: span 2; }
}

@media (max-width: 767.98px) {
  .gal-hero { height: 35vh; min-height: 280px; }
  .gal-hero-title { font-size: 2rem; }
  .gal-grid { grid-template-columns: 1fr; }
  .gal-item.large { grid-column: span 1; aspect-ratio: 4/3; }
  .gal-stats-grid { grid-template-columns: repeat(2, 1fr); }
  .lb-prev, .lb-next { width: 40px; height: 40px; font-size: 1.2rem; }
}
</style>
