<template>
  <div class="fleet-page">
    <!-- Hero -->
    <section class="fleet-hero">
      <div class="fleet-hero-bg" :style="{ backgroundImage: `linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(${heroBg})` }"></div>
      <div class="container position-relative">
        <div class="fleet-hero-content">
          <span class="fml-eyebrow">{{ t('fleet.hero_eyebrow') }}</span>
          <h1 class="fleet-hero-title">{{ t('fleet.hero_title') }}</h1>
          <p class="fleet-hero-subtitle">
            {{ t('fleet.hero_subtitle') }}
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
          <span class="fml-eyebrow">{{ t('fleet.section_eyebrow') }}</span>
          <h2 class="section-title">{{ t('fleet.section_title') }}</h2>
          <p class="section-subtitle text-muted">{{ t('fleet.section_subtitle') }}</p>
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
          <div class="col-lg-6" v-reveal="'right'">
            <span class="fml-eyebrow">{{ t('fleet.highlight_eyebrow') }}</span>
            <h2 class="section-title text-white">{{ t('fleet.highlight_title') }}</h2>
            <p class="mb-4 text-white">
              {{ t('fleet.highlight_text') }}
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
          <div class="col-lg-6" v-reveal="'scale'">
            <div class="highlight-image">
              <img :src="highlightImage" alt="Reachstacker Kalmar" class="img-fluid rounded shadow-lg">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Fleet Gallery -->
    <section class="fleet-gallery fml-section">
      <div class="container">
        <div class="text-center mb-5" v-reveal="'up'">
          <span class="fml-eyebrow">{{ t('fleet.gallery_eyebrow') }}</span>
          <h2 class="section-title">{{ t('fleet.gallery_title') }}</h2>
        </div>
        <div class="gallery-grid">
          <div class="gallery-item" v-for="(img, i) in galleryImages" :key="i" @click="openLightbox(i)" v-reveal="'scale'">
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
        <h2 class="display-5 fw-bold mb-3">{{ t('fleet.cta_title') }}</h2>
        <p class="lead mb-4">{{ t('fleet.cta_subtitle') }}</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <router-link to="/contacto" class="btn btn-dark btn-lg">
            <i class="bi bi-envelope me-2"></i> {{ t('fleet.cta_contact') }}
          </router-link>
          <router-link to="/cotacoes/novo" class="btn btn-outline-dark btn-lg">
            <i class="bi bi-receipt me-2"></i> {{ t('fleet.cta_quote') }}
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useSiteImages } from '@/composables/useSiteImages'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const { getImage, fetchAll } = useSiteImages()
const heroBg = ref('/assets/img/resachstacker/resachstacker1.jpeg')
const highlightImage = ref('/assets/img/resachstacker/resachstacker1.jpeg')

const selectedCategory = ref('all')
const lightboxOpen = ref(false)
const lightboxIndex = ref(0)
const dbItems = ref([])

const categories = computed(() => [
  { key: 'all', label: t('fleet.category_all'), icon: 'bi bi-grid' },
  { key: 'trucks', label: t('fleet.category_trucks'), icon: 'bi bi-truck' },
  { key: 'containers', label: t('fleet.category_containers'), icon: 'bi bi-box-seam' },
  { key: 'equipment', label: t('fleet.category_equipment'), icon: 'bi bi-tools' },
])

const fleetItems = computed(() => {
  if (dbItems.value.length > 0) {
    return dbItems.value.filter(i => i.is_active !== false).map(item => ({
      id: item.id,
      title: item.title,
      category: item.category,
      categoryLabel: item.category_label || item.category,
      image: item.image || '/assets/img/resachstacker/resachstacker1.jpeg',
      description: item.description || '',
      specs: item.specs || []
    }))
  }
  return hardcodedFleetItems
})

const hardcodedFleetItems = [
  {
    id: 1,
    title: t('fleet.truck_tanker'),
    category: 'trucks',
    categoryLabel: t('fleet.category_label_truck'),
    image: '/assets/img/resachstacker/resachstacker3.jpeg',
    description: t('fleet.truck_tanker_desc'),
    specs: [
      { label: t('fleet.spec_capacity'), value: '30.000L' },
      { label: t('fleet.spec_type'), value: 'Tanque' },
      { label: t('fleet.spec_certification'), value: 'ATEX' },
    ]
  },
  {
    id: 2,
    title: t('fleet.truck_frigorific'),
    category: 'trucks',
    categoryLabel: t('fleet.category_label_truck'),
    image: '/assets/img/resachstacker/resachstacker4.jpeg',
    description: t('fleet.truck_frigorific_desc'),
    specs: [
      { label: t('fleet.spec_temperature'), value: '-20°C a +30°C' },
      { label: t('fleet.spec_capacity'), value: '20t' },
      { label: t('fleet.spec_type'), value: '2 independently' },
    ]
  },
  {
    id: 3,
    title: t('fleet.truck_platform'),
    category: 'trucks',
    categoryLabel: t('fleet.category_label_truck'),
    image: '/assets/img/resachstacker/resachstacker5.jpeg',
    description: t('fleet.truck_platform_desc'),
    specs: [
      { label: t('fleet.spec_capacity'), value: '40t' },
      { label: t('fleet.spec_length'), value: '13.6m' },
      { label: t('fleet.spec_type'), value: 'Plataforma baixa' },
    ]
  },
  {
    id: 4,
    title: t('fleet.container_20'),
    category: 'containers',
    categoryLabel: t('fleet.category_label_container'),
    image: '/assets/img/resachstacker/resachstacker6.jpeg',
    description: t('fleet.container_20_desc'),
    specs: [
      { label: t('fleet.spec_size'), value: '20 pés' },
      { label: t('fleet.spec_capacity'), value: '28t' },
      { label: t('fleet.spec_volume'), value: '33m³' },
    ]
  },
  {
    id: 5,
    title: t('fleet.container_40'),
    category: 'containers',
    categoryLabel: t('fleet.category_label_container'),
    image: '/assets/img/resachstacker/resachstacker7.jpeg',
    description: t('fleet.container_40_desc'),
    specs: [
      { label: t('fleet.spec_size'), value: '40 pés' },
      { label: t('fleet.spec_capacity'), value: '28t' },
      { label: t('fleet.spec_volume'), value: '67m³' },
    ]
  },
  {
    id: 6,
    title: t('fleet.reachstacker'),
    category: 'equipment',
    categoryLabel: t('fleet.category_label_equipment'),
    image: '/assets/img/resachstacker/resachstacker8.jpeg',
    description: t('fleet.reachstacker_desc'),
    specs: [
      { label: t('fleet.spec_capacity'), value: '45t' },
      { label: t('fleet.spec_brand'), value: 'Kalmar' },
      { label: t('fleet.spec_stack'), value: '4 contentores' },
    ]
  },
]

const filteredItems = computed(() => {
  if (selectedCategory.value === 'all') return fleetItems.value
  return fleetItems.value.filter(item => item.category === selectedCategory.value)
})

const fleetStats = computed(() => [
  { value: '+15', label: t('fleet.stat_trucks'), icon: 'bi bi-truck' },
  { value: '45t', label: t('fleet.stat_reachstacker'), icon: 'bi bi-tools' },
  { value: '+50', label: t('fleet.stat_containers'), icon: 'bi bi-box-seam' },
  { value: '24/7', label: t('fleet.stat_operations'), icon: 'bi bi-clock-history' },
])

const reachstackerSpecs = computed(() => [
  { icon: 'bi bi-speedometer', label: t('fleet.reachstacker_spec_capacity_label'), value: t('fleet.reachstacker_spec_capacity_value') },
  { icon: 'bi bi-box-seam', label: t('fleet.reachstacker_spec_containers_label'), value: t('fleet.reachstacker_spec_containers_value') },
  { icon: 'bi bi-layout-stack', label: t('fleet.reachstacker_spec_lift_label'), value: t('fleet.reachstacker_spec_lift_value') },
  { icon: 'bi bi-award', label: t('fleet.reachstacker_spec_operators_label'), value: t('fleet.reachstacker_spec_operators_value') },
  { icon: 'bi bi-shield-check', label: t('fleet.reachstacker_spec_maintenance_label'), value: t('fleet.reachstacker_spec_maintenance_value') },
  { icon: 'bi bi-lightning', label: t('fleet.reachstacker_spec_productivity_label'), value: t('fleet.reachstacker_spec_productivity_value') },
])

const galleryImages = computed(() => [
  { src: '/assets/img/resachstacker/resachstacker1.jpeg', caption: t('fleet.gallery_img_1') },
  { src: '/assets/img/resachstacker/resachstacker2.jpeg', caption: t('fleet.gallery_img_2') },
  { src: '/assets/img/resachstacker/resachstacker9.jpeg', caption: t('fleet.gallery_img_3') },
  { src: '/assets/img/resachstacker/resachstacker10.jpeg', caption: t('fleet.gallery_img_4') },
])

const openLightbox = (index) => {
  lightboxIndex.value = index
  lightboxOpen.value = true
}
const prevImage = () => {
  lightboxIndex.value = (lightboxIndex.value - 1 + galleryImages.value.length) % galleryImages.value.length
}
const nextImage = () => {
  lightboxIndex.value = (lightboxIndex.value + 1) % galleryImages.value.length
}

onMounted(async () => {
  await Promise.all([
    fetchAll(),
    supabase.from('fleet_items').select('*').order('order_by', { ascending: true })
      .then(({ data }) => { if (data && data.length) dbItems.value = data })
      .catch(() => {})
  ])
  heroBg.value = getImage('fleet', 'hero_bg', '/assets/img/resachstacker/resachstacker1.jpeg')
  highlightImage.value = getImage('fleet', 'highlight_image', '/assets/img/resachstacker/resachstacker1.jpeg')
})
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
  color: rgba(255,255,255,1);
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
  -webkit-backdrop-filter: blur(8px);
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
  color: rgba(255,255,255,1);
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
