<template>
  <div class="home">
    <!-- Hero Slider -->
    <section class="hero-slider">
      <div v-if="loadingBanners" class="hero-slide active" :style="defaultSlideStyle">
        <div class="container">
          <div class="hero-content">
            <span class="hero-eyebrow">{{ t('home.hero_eyebrow') }}</span>
            <h1 class="hero-title">{{ t('home.hero_title') }}</h1>
            <p class="hero-subtitle">{{ t('home.hero_subtitle') }}</p>
            <div class="hero-ctas">
              <router-link to="/servicos" class="btn btn-gold btn-lg">
                <i class="bi bi-arrow-right-circle me-2"></i> {{ t('home.hero_cta_services') }}
              </router-link>
              <router-link to="/contacto" class="btn btn-outline-light btn-lg">
                <i class="bi bi-telephone me-2"></i> {{ t('home.hero_cta_contact') }}
              </router-link>
            </div>
            <div class="hero-stats">
              <div class="hero-stat"><strong>+8</strong><span>{{ t('home.stat_years') }}</span></div>
              <div class="hero-stat"><strong>+60</strong><span>{{ t('home.stat_employees') }}</span></div>
              <div class="hero-stat"><strong>+32</strong><span>{{ t('home.stat_countries') }}</span></div>
              <div class="hero-stat"><strong>+1000</strong><span>{{ t('home.stat_clients') }}</span></div>
            </div>
          </div>
        </div>
      </div>
      <template v-else-if="slides.length > 0">
        <div class="hero-slide" v-for="(s, i) in slides" :key="s.id || i" :class="{ active: i === currentSlide }" :style="{ backgroundImage: slideBg(s) }">
          <div class="container">
            <div class="hero-content">
              <span class="hero-eyebrow" data-aos="fade-up">{{ s.title }}</span>
              <h1 class="hero-title" data-aos="fade-up" data-aos-delay="100">{{ s.title }}</h1>
              <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="200">{{ s.description }}</p>
              <div class="hero-ctas" data-aos="fade-up" data-aos-delay="300">
                <router-link :to="s.link || '/servicos'" class="btn btn-gold btn-lg">
                  <i class="bi bi-arrow-right-circle me-2"></i> {{ t('home.learn_more') }}
                </router-link>
                <router-link to="/contacto" class="btn btn-outline-light btn-lg">
                  <i class="bi bi-telephone me-2"></i> {{ t('home.hero_cta_contact') }}
                </router-link>
              </div>
              <div class="hero-stats" data-aos="fade-up" data-aos-delay="400">
                <div class="hero-stat"><strong>+8</strong><span>{{ t('home.stat_years') }}</span></div>
                <div class="hero-stat"><strong>+60</strong><span>{{ t('home.stat_employees') }}</span></div>
                <div class="hero-stat"><strong>+32</strong><span>{{ t('home.stat_countries') }}</span></div>
                <div class="hero-stat"><strong>+1000</strong><span>{{ t('home.stat_clients') }}</span></div>
              </div>
            </div>
          </div>
        </div>
      </template>
      <div v-else class="hero-slide active" :style="defaultSlideStyle">
        <div class="container">
          <div class="hero-content">
            <span class="hero-eyebrow">{{ t('home.hero_eyebrow') }}</span>
            <h1 class="hero-title">{{ t('home.hero_title') }}</h1>
            <p class="hero-subtitle">{{ t('home.hero_subtitle') }}</p>
            <div class="hero-ctas">
              <router-link to="/servicos" class="btn btn-gold btn-lg">
                <i class="bi bi-arrow-right-circle me-2"></i> {{ t('home.hero_cta_services') }}
              </router-link>
              <router-link to="/contacto" class="btn btn-outline-light btn-lg">
                <i class="bi bi-telephone me-2"></i> {{ t('home.hero_cta_contact') }}
              </router-link>
            </div>
            <div class="hero-stats">
              <div class="hero-stat"><strong>+8</strong><span>{{ t('home.stat_years') }}</span></div>
              <div class="hero-stat"><strong>+60</strong><span>{{ t('home.stat_employees') }}</span></div>
              <div class="hero-stat"><strong>+32</strong><span>{{ t('home.stat_countries') }}</span></div>
              <div class="hero-stat"><strong>+1000</strong><span>{{ t('home.stat_clients') }}</span></div>
            </div>
          </div>
        </div>
      </div>

      <button class="hero-nav prev" @click="prevSlide" :aria-label="t('home.prev_slide')">
        <i class="bi bi-chevron-left"></i>
      </button>
      <button class="hero-nav next" @click="nextSlide" :aria-label="t('home.next_slide')">
        <i class="bi bi-chevron-right"></i>
      </button>

      <div class="hero-dots">
        <button v-for="(_, i) in slides" :key="i" :class="{ active: i === currentSlide }" @click="currentSlide = i" :aria-label="`${t('home.goto_slide')} ${i+1}`"></button>
      </div>
    </section>

    <!-- About strip -->
    <section class="about-strip">
      <div class="container">
        <div class="row align-items-center g-4">
          <div class="col-lg-7" v-reveal="'right'">
            <span class="fml-eyebrow">{{ t('home.about_eyebrow') }}</span>
            <h2 class="section-title mb-3">{{ t('home.about_title') }}</h2>
            <p class="text-muted mb-0" v-html="t('home.about_text')">
            </p>
          </div>
          <div class="col-lg-5" v-reveal="'left'">
            <div class="cert-card">
              <div class="cert-item">
                <i class="bi bi-shield-check"></i>
                <div>
                  <h6>{{ t('home.cert_all_risks') }}</h6>
                  <small>{{ t('home.cert_all_risks_desc') }}</small>
                </div>
              </div>
              <div class="cert-item">
                <i class="bi bi-award"></i>
                <div>
                  <h6>{{ t('home.cert_iso') }}</h6>
                  <small>{{ t('home.cert_iso_desc') }}</small>
                </div>
              </div>
              <div class="cert-item">
                <i class="bi bi-truck"></i>
                <div>
                  <h6>{{ t('home.cert_fleet') }}</h6>
                  <small>{{ t('home.cert_fleet_desc') }}</small>
                </div>
              </div>
              <div class="cert-item">
                <i class="bi bi-geo-alt"></i>
                <div>
                  <h6>{{ t('home.cert_global') }}</h6>
                  <small>{{ t('home.cert_global_desc') }}</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Services -->
    <section class="services-section fml-section bg-fml-navy text-white">
      <div class="container">
        <div class="text-center mb-5" v-reveal="'up'">
          <span class="fml-eyebrow">{{ t('home.services_eyebrow') }}</span>
          <h2 class="section-title text-white">{{ t('home.services_title') }}</h2>
          <p class="section-subtitle text-white">{{ t('home.services_subtitle') }}</p>
        </div>
        <div v-if="loadingServices" class="row g-4">
          <div class="col-lg-3 col-md-6" v-for="n in 4" :key="n">
            <div class="service-card">
              <div class="service-icon skeleton-icon"></div>
              <div class="skeleton-text mb-2"></div>
              <div class="skeleton-text short"></div>
            </div>
          </div>
        </div>
        <div v-else class="row g-4">
          <div class="col-lg-3 col-md-6" v-for="(s, i) in services" :key="s.id || i" v-reveal="'up'" :data-reveal-delay="i * 100">
            <router-link :to="`/servicos/${s.slug || ''}`" class="service-card">
              <div class="service-icon"><i :class="getServiceIcon(s.title)"></i></div>
              <h5>{{ s.title }}</h5>
              <p>{{ s.description }}</p>
              <span class="service-link">{{ t('home.service_learn_more') }} <i class="bi bi-arrow-right"></i></span>
            </router-link>
          </div>
        </div>
        <div class="text-center mt-5">
          <router-link to="/servicos" class="btn btn-gold btn-lg">
            {{ t('home.view_all_services') }} <i class="bi bi-arrow-right ms-2"></i>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Process -->
    <section class="process-section fml-section">
      <div class="container">
        <div class="text-center mb-5" v-reveal="'up'">
          <span class="fml-eyebrow">{{ t('home.process_eyebrow') }}</span>
          <h2 class="section-title">{{ t('home.process_title') }}</h2>
        </div>
        <div class="row g-4 process-steps">
          <div class="col-md-6 col-lg-3" v-for="(step, i) in processSteps" :key="i" v-reveal="'up'" :data-reveal-delay="i * 120">
            <div class="process-step">
              <div class="process-num">{{ i+1 }}</div>
              <h5>{{ step.title }}</h5>
              <p class="text-muted mb-0">{{ step.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Fleet highlight -->
    <section class="fleet-section fml-section bg-fml-50">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6" v-reveal="'right'">
            <span class="fml-eyebrow">{{ t('home.fleet_eyebrow') }}</span>
            <h2 class="section-title">{{ t('home.fleet_title') }}</h2>
            <p class="text-muted mb-4" v-html="t('home.fleet_text')">
            </p>
            <ul class="feature-list">
              <li><i class="bi bi-check-circle-fill"></i> {{ t('home.fleet_feat_1') }}</li>
              <li><i class="bi bi-check-circle-fill"></i> {{ t('home.fleet_feat_2') }}</li>
              <li><i class="bi bi-check-circle-fill"></i> {{ t('home.fleet_feat_3') }}</li>
              <li><i class="bi bi-check-circle-fill"></i> {{ t('home.fleet_feat_4') }}</li>
            </ul>
            <router-link to="/frota" class="btn btn-primary mt-3">
              {{ t('home.fleet_cta') }} <i class="bi bi-arrow-right ms-2"></i>
            </router-link>
          </div>
          <div class="col-lg-6" v-reveal="'scale'">
            <div class="fleet-image">
              <img :src="fleetImage" :alt="t('home.fleet_img_alt')" class="img-fluid rounded">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section v-if="testimonials.length" class="testimonials-section fml-section bg-fml-navy text-white">
      <div class="container">
        <div class="text-center mb-5" v-reveal="'up'">
          <span class="fml-eyebrow">{{ t('home.testimonials_eyebrow') }}</span>
          <h2 class="section-title text-white">{{ t('home.testimonials_title') }}</h2>
        </div>
        <div class="row g-4">
          <div class="col-md-4" v-for="(t, i) in testimonials.slice(0, 3)" :key="t.id || i" v-reveal="'up'" :data-reveal-delay="i * 120">
            <div class="testimonial-card">
              <div class="stars mb-2">
                <i v-for="n in Number(t.rating) || 5" :key="n" class="bi bi-star-fill"></i>
              </div>
              <p class="testimonial-text">"{{ t.message }}"</p>
              <div class="testimonial-author">
                <img v-if="t.photo" :src="t.photo" :alt="t.name" class="avatar-img">
                <div v-else class="avatar">{{ t.name.charAt(0) }}</div>
                <div>
                  <strong>{{ t.name }}</strong>
                  <small v-if="t.company" class="d-block text-white">{{ t.position }} · {{ t.company }}</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Nossos Clientes -->
    <section class="clients-section fml-section" v-reveal="'fade'">
      <div class="container">
        <div class="text-center mb-5">
          <p class="text-uppercase small fw-bold mb-1" style="letter-spacing: 2px; color: var(--fml-gold);">{{ t('home.clients_eyebrow') }}</p>
          <h2 class="fw-bold display-6 mb-3">{{ t('home.clients_title') }}</h2>
          <p class="text-muted mx-auto" style="max-width: 600px;">{{ t('home.clients_desc') }}</p>
        </div>
        <ClientsCarousel />
      </div>
    </section>

    <!-- CTA -->
    <section class="cta-section fml-section-gold" v-reveal="'scale'">
      <div class="container text-center">
        <h2 class="display-5 fw-bold mb-3">{{ t('home.cta_title') }}</h2>
        <p class="lead mb-4">{{ t('home.cta_subtitle') }}</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <router-link to="/cotacoes/novo" class="btn btn-dark btn-lg">
            <i class="bi bi-receipt me-2"></i> {{ t('home.cta_button') }}
          </router-link>
          <a href="tel:+244935141747" class="btn btn-outline-dark btn-lg">
            <i class="bi bi-telephone me-2"></i> +244 935 141 747
          </a>
        </div>
      </div>
    </section>

    <!-- Location -->
    <section class="location-section fml-section">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-5" v-reveal="'right'">
            <span class="fml-eyebrow">{{ t('home.location_eyebrow') }}</span>
            <h2 class="section-title">{{ t('home.location_title') }}</h2>
            <p class="text-muted mb-4">{{ t('home.location_desc') }}</p>
            <ul class="location-info list-unstyled">
              <li class="d-flex align-items-start mb-3">
                <i class="bi bi-geo-alt-fill text-primary fs-4 me-3"></i>
                <div>
                  <strong class="d-block text-dark">{{ t('home.location_address_label') }}</strong>
                  {{ t('home.location_address_line1') }}<br>
                  {{ t('home.location_address_line2') }}
                </div>
              </li>
              <li class="d-flex align-items-start mb-3">
                <i class="bi bi-telephone-fill text-primary fs-4 me-3"></i>
                <div>
                  <strong class="d-block text-dark">{{ t('home.location_phone_label') }}</strong>
                  <a href="tel:+244935141747" class="text-decoration-none text-dark">+244 935 141 747</a>
                </div>
              </li>
              <li class="d-flex align-items-start mb-3">
                <i class="bi bi-envelope-fill text-primary fs-4 me-3"></i>
                <div>
                  <strong class="d-block text-dark">{{ t('home.location_email_label') }}</strong>
                  <a href="mailto:geral@fmlider.co.ao" class="text-decoration-none text-dark">geral@fmlider.co.ao</a>
                </div>
              </li>
              <li class="d-flex align-items-start mb-3">
                <i class="bi bi-clock-fill text-primary fs-4 me-3"></i>
                <div>
                  <strong class="d-block text-dark">{{ t('home.location_hours_label') }}</strong>
                  {{ t('home.location_hours_line1') }}<br>
                  {{ t('home.location_hours_line2') }}
                </div>
              </li>
            </ul>
          </div>
          <div class="col-lg-7" v-reveal="'left'">
            <div class="map-container shadow">
              <iframe
                src="https://maps.google.com/maps?q=FMLider+Base+Cacuaco+Luanda+Angola&hl=pt&z=15&output=embed"
                width="100%" height="420" style="border:0; border-radius: 12px;"
                allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                :title="t('home.location_map_title')"
              ></iframe>
            </div>
            <a href="https://www.google.com/maps/place/FMLider+-+Base/@-8.7693538,13.3973228,359m/data=!3m1!1e3!4m6!3m5!1s0x1a51e5684ed42f1b:0x5630ab6f53efd403!8m2!3d-8.769266!4d13.3984122"
               target="_blank" rel="noopener"
               class="btn btn-outline-primary w-100 mt-3">
              <i class="bi bi-geo-alt-fill me-2"></i> {{ t('home.location_map_cta') }}
            </a>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import ClientsCarousel from '@/components/ClientsCarousel.vue'
import { supabase } from '@/lib/supabase'
import { useSiteImages } from '@/composables/useSiteImages'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const { getImage, fetchAll } = useSiteImages()
const heroBg = ref('/assets/img/construcao2020/image1.jpeg')
const fleetImage = ref('/assets/img/resachstacker/resachstacker1.jpeg')

const currentSlide = ref(0)
let interval = null

const slides = ref([])
const services = ref([])
const testimonials = ref([])

const loadingBanners = ref(true)
const loadingServices = ref(true)
const loadingTestimonials = ref(true)

const defaultSlideStyle = computed(() => ({
  backgroundImage: `linear-gradient(135deg, rgba(15,23,42,0.85) 0%, rgba(30,58,138,0.75) 50%, rgba(15,23,42,0.85) 100%), url(${heroBg.value})`
}))

const FALLBACK_BG = computed(() => `linear-gradient(135deg, rgba(15,23,42,0.85) 0%, rgba(30,58,138,0.75) 50%, rgba(15,23,42,0.85) 100%), url(${heroBg.value})`)

function slideBg(s) {
  if (s.image && (s.image.startsWith('http') || s.image.startsWith('/'))) {
    return `linear-gradient(135deg, rgba(15,23,42,0.85) 0%, rgba(30,58,138,0.75) 50%, rgba(15,23,42,0.85) 100%), url(${s.image})`
  }
  return FALLBACK_BG.value
}

const processSteps = computed(() => [
  { title: t('home.process_contact_title'), desc: t('home.process_contact_desc') },
  { title: t('home.process_quote_title'), desc: t('home.process_quote_desc') },
  { title: t('home.process_execution_title'), desc: t('home.process_execution_desc') },
  { title: t('home.process_delivery_title'), desc: t('home.process_delivery_desc') },
])

const serviceIconMap = {
  'despacho': 'bi bi-shield-check',
  'despachante': 'bi bi-shield-check',
  'desembaraço': 'bi bi-shield-check',
  'transporte': 'bi bi-truck',
  'transportes': 'bi bi-truck',
  'armazém': 'bi bi-box-seam',
  'armazenagem': 'bi bi-box-seam',
  'door': 'bi bi-house-door',
  'logística': 'bi bi-gear',
  'logistica': 'bi bi-gear',
  'consultoria': 'bi bi-lightbulb',
  'compliance': 'bi bi-clipboard-check',
  'aduaneiro': 'bi bi-sticky',
  'importação': 'bi bi-arrow-down-left-circle',
  'exportação': 'bi bi-arrow-up-right-circle',
}

function getServiceIcon(title) {
  if (!title) return 'bi bi-gear'
  const lower = title.toLowerCase()
  for (const [key, icon] of Object.entries(serviceIconMap)) {
    if (lower.includes(key)) return icon
  }
  return 'bi bi-gear'
}

  const fetchContent = async () => {
  await fetchAll()
  heroBg.value = getImage('home', 'hero_bg', '/assets/img/construcao2020/image1.jpeg')
  fleetImage.value = getImage('home', 'fleet_image', '/assets/img/resachstacker/resachstacker1.jpeg')

  const fetchBanners = supabase.from('banners').select('*').order('order_by')
  const fetchServices = supabase.from('services').select('*').order('order_by')
  const fetchTestimonials = supabase.from('testimonials').select('*').order('order_by')

  const [bannersRes, servicesRes, testsRes] = await Promise.all([
    fetchBanners, fetchServices, fetchTestimonials
  ])

  if (!bannersRes.error && bannersRes.data) {
    const raw = (bannersRes.data || []).filter(b => b.status === true || b.status === 1 || b.status === 'published')
    slides.value = raw.map(b => ({
      ...b,
      image: b.image?.startsWith('/') || b.image?.startsWith('http') ? b.image : `/assets/img/${b.image}`,
      link: b.link || '/servicos',
    }))
  }
  loadingBanners.value = false

  if (!servicesRes.error && servicesRes.data) {
    services.value = (servicesRes.data || []).filter(s => s.status === true || s.status === 1 || s.status === 'published')
  }
  loadingServices.value = false

  if (!testsRes.error && testsRes.data) {
    testimonials.value = (testsRes.data || []).filter(t => t.status === true || t.status === 1 || t.status === 'published')
  }
  loadingTestimonials.value = false

  if (slides.value.length && interval === null) {
    interval = setInterval(nextSlide, 6000)
  }
}

const nextSlide = () => {
  const total = slides.value.length
  if (!total) return
  currentSlide.value = (currentSlide.value + 1) % total
}
const prevSlide = () => {
  const total = slides.value.length
  if (!total) return
  currentSlide.value = (currentSlide.value - 1 + total) % total
}

onMounted(() => {
  fetchContent()
})
onUnmounted(() => {
  if (interval) clearInterval(interval)
})
</script>

<style scoped>
/* HERO SLIDER */
.hero-slider {
  position: relative;
  height: 100vh;
  min-height: 640px;
  overflow: hidden;
  background: var(--fml-navy);
}
.hero-slide {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  opacity: 0;
  transition: opacity 1s var(--fml-ease);
}
.hero-slide.active { opacity: 1; }
.hero-content {
  max-width: 800px;
  color: #fff;
  padding: 0 1rem;
}
.hero-eyebrow {
  display: inline-block;
  padding: 0.4rem 1rem;
  background: rgba(245, 158, 11, 0.15);
  border: 1px solid var(--fml-gold);
  color: var(--fml-gold);
  border-radius: var(--fml-radius-pill);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin-bottom: 1.5rem;
}
.hero-title {
  font-size: 4rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 1.5rem;
  letter-spacing: -0.03em;
  line-height: 1.05;
  text-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
}
.hero-subtitle {
  font-size: 1.25rem;
  line-height: 1.6;
  opacity: 0.95;
  margin-bottom: 2.5rem;
  max-width: 620px;
}
.hero-ctas { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 4rem; }
.hero-stats {
  display: flex;
  gap: 3rem;
  flex-wrap: wrap;
  padding-top: 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.15);
}
.hero-stat { display: flex; flex-direction: column; }
.hero-stat strong { font-size: 2.2rem; font-weight: 800; color: var(--fml-gold); line-height: 1; }
.hero-stat span { font-size: 0.85rem; opacity: 0.85; margin-top: 0.25rem; text-transform: uppercase; letter-spacing: 1.5px; }

.hero-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 56px;
  height: 56px;
  border-radius: 50%;
  border: 1.5px solid rgba(255, 255, 255, 0.3);
  background: rgba(15, 23, 42, 0.4);
  -webkit-backdrop-filter: blur(8px);
  backdrop-filter: blur(8px);
  color: #fff;
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.2s var(--fml-ease);
  z-index: 5;
}
.hero-nav:hover { background: var(--fml-gold); border-color: var(--fml-gold); color: var(--fml-900); }
.hero-nav.prev { left: 2rem; }
.hero-nav.next { right: 2rem; }

.hero-dots {
  position: absolute;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 0.5rem;
  z-index: 5;
}
.hero-dots button {
  width: 36px;
  height: 4px;
  border-radius: 2px;
  border: none;
  background: rgba(255, 255, 255, 0.3);
  cursor: pointer;
  transition: all 0.3s var(--fml-ease);
  padding: 0;
}
.hero-dots button.active { background: var(--fml-gold); width: 56px; }

@media (max-width: 768px) {
  .hero-slider { height: auto; min-height: 600px; padding: 5rem 0 3rem; }
  .hero-title { font-size: 2.2rem; }
  .hero-subtitle { font-size: 1rem; }
  .hero-ctas { flex-direction: column; }
  .hero-ctas .btn { width: 100%; }
  .hero-stats { gap: 1.5rem; }
  .hero-stat strong { font-size: 1.6rem; }
  .hero-nav { display: none; }
}

/* ABOUT STRIP */
.about-strip { padding: 5rem 0; }
.cert-card {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
.cert-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: #fff;
  border-radius: var(--fml-radius);
  border: 1px solid var(--fml-200);
  transition: all 0.2s var(--fml-ease);
}
.cert-item:hover { border-color: var(--fml-gold); transform: translateY(-2px); box-shadow: var(--fml-shadow); }
.cert-item i {
  font-size: 1.6rem;
  color: var(--fml-gold);
  flex-shrink: 0;
}
.cert-item h6 { margin: 0; font-size: 0.95rem; color: var(--fml-900); }
.cert-item small { color: var(--fml-500); font-size: 0.8rem; }

/* SERVICES */
.services-section { padding: 5rem 0; }
.service-card {
  display: block;
  padding: 2rem 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--fml-radius);
  color: #fff;
  text-decoration: none;
  transition: all 0.3s var(--fml-ease);
  height: 100%;
}
.service-card:hover {
  background: rgba(245, 158, 11, 0.1);
  border-color: var(--fml-gold);
  transform: translateY(-4px);
  color: #fff;
}
.service-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, var(--fml-gold), var(--fml-amber));
  border-radius: var(--fml-radius);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
  color: var(--fml-900);
  font-size: 1.75rem;
}
.service-card h5 { color: #fff; font-size: 1.25rem; margin-bottom: 0.75rem; }
.service-card p { color: var(--fml-300); font-size: 0.95rem; margin-bottom: 1rem; }
.service-link {
  color: var(--fml-gold);
  font-weight: 600;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}
.service-card:hover .service-link { gap: 0.75rem; }

/* SKELETON LOADING */
.skeleton-icon {
  background: rgba(255,255,255,0.1);
  animation: pulse 1.5s ease-in-out infinite;
}
.skeleton-text {
  height: 16px;
  border-radius: 4px;
  background: rgba(255,255,255,0.1);
  margin-bottom: 0.5rem;
  animation: pulse 1.5s ease-in-out infinite;
}
.skeleton-text.short { width: 60%; }
@keyframes pulse {
  0%, 100% { opacity: 0.4; }
  50% { opacity: 0.8; }
}

/* PROCESS */
.process-step {
  text-align: center;
  padding: 1.5rem 1rem;
  position: relative;
}
.process-num {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, var(--fml-blue-2), var(--fml-blue));
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0 auto 1.25rem;
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
}
.process-step h5 { color: var(--fml-900); margin-bottom: 0.5rem; }
.process-step p { font-size: 0.95rem; }

/* FLEET */
.fleet-image { position: relative; }
.fleet-image::before {
  content: '';
  position: absolute;
  inset: -1rem;
  background: linear-gradient(135deg, var(--fml-gold), var(--fml-amber));
  border-radius: var(--fml-radius-lg);
  z-index: -1;
  opacity: 0.3;
  filter: blur(30px);
}
.feature-list { list-style: none; padding: 0; margin: 0 0 1rem; }
.feature-list li { padding: 0.5rem 0; color: var(--fml-700); display: flex; align-items: center; gap: 0.6rem; }
.feature-list i { color: var(--fml-success); font-size: 1.1rem; }

/* TESTIMONIALS */
.testimonial-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--fml-radius);
  padding: 2rem;
  height: 100%;
}
.testimonial-text { color: var(--fml-200); font-style: italic; line-height: 1.7; margin-bottom: 1.5rem; }
.stars { color: var(--fml-gold); }
.testimonial-author { display: flex; align-items: center; gap: 0.75rem; }
.avatar {
  width: 44px; height: 44px; border-radius: 50%;
  background: linear-gradient(135deg, var(--fml-gold), var(--fml-amber));
  color: var(--fml-900);
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: 1.05rem;
  flex-shrink: 0;
}
.avatar-img {
  width: 44px; height: 44px; border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}
.testimonial-author strong { color: #fff; }
.testimonial-author small { color: var(--fml-400); }

/* CTA */
.cta-section {
  background: linear-gradient(135deg, var(--fml-gold) 0%, var(--fml-amber) 100%);
  color: var(--fml-900);
  padding: 4.5rem 0;
}

/* LOCATION */
.location-info li { font-size: 0.95rem; line-height: 1.5; }
.location-info strong { color: var(--fml-900); }
.map-container { border-radius: var(--fml-radius); overflow: hidden; }
</style>
