<template>
  <div class="services-page">
    <!-- Hero -->
    <section class="srv-hero">
      <div class="srv-hero-bg" :style="{ backgroundImage: `linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(${heroBg})` }"></div>
      <div class="container position-relative">
        <div class="srv-hero-content">
          <span class="fml-eyebrow">{{ t('services.hero_eyebrow') }}</span>
          <h1 class="srv-hero-title">{{ t('services.hero_title') }}</h1>
          <p class="srv-hero-subtitle">
            {{ t('services.hero_subtitle') }}
          </p>
        </div>
      </div>
    </section>

    <!-- Services Grid -->
    <section class="srv-grid-section fml-section">
      <div class="container">
        <div class="text-center mb-5" v-reveal="'up'">
          <span class="fml-eyebrow">{{ t('services.grid_eyebrow') }}</span>
          <h2 class="section-title">{{ t('services.grid_title') }}</h2>
          <p class="section-subtitle text-muted">{{ t('services.grid_subtitle') }}</p>
        </div>

        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-warning" role="status">
            <span class="visually-hidden">{{ t('services.loading') }}</span>
          </div>
        </div>

        <div v-else-if="services.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-inbox" style="font-size: 3rem;"></i>
          <p class="mt-3">{{ t('services.empty') }}</p>
        </div>

        <div v-else class="srv-cards">
          <router-link
            v-for="service in services"
            :key="service.id"
            :to="`/servicos/${service.slug}`"
            class="srv-card"
            :id="service.slug"
          >
            <div class="srv-card-image">
              <img
                :src="service.image || '/assets/img/servico/default-service.jpg'"
                :alt="service.title"
                @error="onImageError"
              >
              <div class="srv-card-overlay">
                <span class="srv-card-num">{{ String(service.order_by || service.id).padStart(2, '0') }}</span>
              </div>
            </div>
            <div class="srv-card-body">
              <h3>{{ service.title }}</h3>
              <p class="srv-card-desc">{{ service.description }}</p>
              <span class="srv-card-link">
                {{ t('services.card_link') }} <i class="bi bi-arrow-right ms-1"></i>
              </span>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Process -->
    <section class="srv-process fml-section bg-fml-navy text-white">
      <div class="container">
        <div class="text-center mb-5" v-reveal="'up'">
          <span class="fml-eyebrow">{{ t('services.process_eyebrow') }}</span>
          <h2 class="section-title text-white">{{ t('services.process_title') }}</h2>
          <p class="text-white">{{ t('services.process_subtitle') }}</p>
        </div>
        <div class="process-steps">
          <div class="process-step" v-for="(step, i) in processSteps" :key="i">
            <div class="step-num">{{ i + 1 }}</div>
            <div class="step-content">
              <h5>{{ step.title }}</h5>
              <p>{{ step.desc }}</p>
            </div>
            <div class="step-arrow" v-if="i < processSteps.length - 1">
              <i class="bi bi-arrow-right"></i>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Us -->
    <section class="srv-why fml-section">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6" v-reveal="'right'">
            <span class="fml-eyebrow">{{ t('services.why_eyebrow') }}</span>
            <h2 class="section-title">{{ t('services.why_title') }}</h2>
            <p class="text-muted mb-4">
              {{ t('services.why_text') }}
            </p>
            <div class="why-features">
              <div class="why-feature" v-for="feat in whyFeatures" :key="feat.title">
                <div class="why-feature-icon"><i :class="feat.icon"></i></div>
                <div>
                  <h6>{{ feat.title }}</h6>
                  <small>{{ feat.desc }}</small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6" v-reveal="'scale'">
            <div class="why-image">
              <img :src="whyImage" :alt="t('services.why_img_alt')" class="img-fluid rounded shadow-lg">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="srv-cta fml-section-gold">
      <div class="container text-center">
        <h2 class="display-5 fw-bold mb-3">{{ t('services.cta_title') }}</h2>
        <p class="lead mb-4">{{ t('services.cta_subtitle') }}</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <router-link to="/contacto" class="btn btn-dark btn-lg">
            <i class="bi bi-envelope me-2"></i> {{ t('services.cta_button') }}
          </router-link>
          <a href="tel:+244935141747" class="btn btn-outline-dark btn-lg">
            <i class="bi bi-telephone me-2"></i> +244 935 141 747
          </a>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useI18n } from '@/composables/useI18n'
import { useSiteImages } from '@/composables/useSiteImages'

const { t } = useI18n()
const { getImage, fetchAll } = useSiteImages()
const heroBg = ref('/assets/img/construcao2020/image4.jpeg')
const whyImage = ref('/assets/img/pessoal/partner1.webp')

const services = ref([])
const loading = ref(true)

const fetchServices = async () => {
  loading.value = true
  try {
    const { data, error } = await supabase.from('services').select('*').eq('status', 1).order('order_by')
    if (!error) {
      services.value = data || []
    }
  } catch (e) {
    console.error('Erro ao carregar serviços', e)
  } finally {
    loading.value = false
  }
}

const onImageError = (e) => {
  e.target.src = '/assets/img/servico/default-service.jpg'
}

onMounted(async () => {
  await fetchAll()
  heroBg.value = getImage('services', 'hero_bg', '/assets/img/construcao2020/image4.jpeg')
  whyImage.value = getImage('services', 'why_image', '/assets/img/pessoal/partner1.webp')
  fetchServices()
})

const processSteps = computed(() => [
  { title: t('services.process_step_1_title'), desc: t('services.process_step_1_desc') },
  { title: t('services.process_step_2_title'), desc: t('services.process_step_2_desc') },
  { title: t('services.process_step_3_title'), desc: t('services.process_step_3_desc') },
  { title: t('services.process_step_4_title'), desc: t('services.process_step_4_desc') },
])

const whyFeatures = computed(() => [
  { icon: 'bi bi-shield-check', title: t('services.why_feat_1_title'), desc: t('services.why_feat_1_desc') },
  { icon: 'bi bi-speedometer', title: t('services.why_feat_2_title'), desc: t('services.why_feat_2_desc') },
  { icon: 'bi bi-people', title: t('services.why_feat_3_title'), desc: t('services.why_feat_3_desc') },
  { icon: 'bi bi-graph-up-arrow', title: t('services.why_feat_4_title'), desc: t('services.why_feat_4_desc') },
  { icon: 'bi bi-clock-history', title: t('services.why_feat_5_title'), desc: t('services.why_feat_5_desc') },
  { icon: 'bi bi-globe', title: t('services.why_feat_6_title'), desc: t('services.why_feat_6_desc') },
])
</script>

<style scoped>
/* HERO */
.srv-hero {
  position: relative;
  height: 50vh;
  min-height: 380px;
  display: flex;
  align-items: center;
  overflow: hidden;
  background: var(--fml-navy, #0f172a);
}
.srv-hero-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
}
.srv-hero-content {
  position: relative;
  color: #fff;
  max-width: 700px;
}
.srv-hero-title {
  font-size: 3.5rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 1rem;
  letter-spacing: -0.03em;
}
.srv-hero-subtitle {
  font-size: 1.2rem;
  line-height: 1.7;
  opacity: 0.9;
}

/* SERVICES GRID */
.srv-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

.srv-card {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  transition: all 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: inherit;
}
.srv-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
  border-color: var(--fml-gold, #f59e0b);
}

.srv-card-image {
  position: relative;
  height: 220px;
  overflow: hidden;
}
.srv-card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}
.srv-card:hover .srv-card-image img {
  transform: scale(1.08);
}
.srv-card-overlay {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(15, 23, 42, 0.7);
  -webkit-backdrop-filter: blur(8px);
  backdrop-filter: blur(8px);
  color: var(--fml-gold, #f59e0b);
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.9rem;
}

.srv-card-body {
  padding: 1.75rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.srv-card-body h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.75rem;
}

.srv-card-desc {
  color: #64748b;
  font-size: 0.92rem;
  line-height: 1.65;
  margin-bottom: 1rem;
  flex: 1;
}

.srv-card-link {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  color: var(--fml-gold, #d97706);
  font-weight: 600;
  font-size: 0.9rem;
  margin-top: auto;
  transition: gap 0.25s ease;
}
.srv-card:hover .srv-card-link {
  gap: 0.5rem;
}

/* PROCESS */
.process-steps {
  display: flex;
  align-items: flex-start;
  justify-content: center;
  gap: 0;
  position: relative;
}
.process-step {
  flex: 1;
  max-width: 260px;
  text-align: center;
  position: relative;
  padding: 0 1rem;
}
.step-num {
  width: 72px;
  height: 72px;
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0 auto 1.25rem;
  box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
  position: relative;
  z-index: 2;
}
.step-content h5 {
  color: #fff;
  font-weight: 700;
  margin-bottom: 0.5rem;
}
.step-content p {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  margin: 0;
  line-height: 1.5;
}
.step-arrow {
  position: absolute;
  top: 36px;
  right: -16px;
  color: var(--fml-gold, #f59e0b);
  font-size: 1.5rem;
  z-index: 3;
}

/* WHY US */
.why-features {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
.why-feature {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  transition: all 0.25s ease;
}
.why-feature:hover {
  border-color: var(--fml-gold, #f59e0b);
  background: #fff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}
.why-feature-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  flex-shrink: 0;
}
.why-feature h6 {
  margin: 0;
  font-size: 0.95rem;
  color: #0f172a;
}
.why-feature small {
  color: #64748b;
  font-size: 0.82rem;
}

.why-image img {
  width: 100%;
}

/* CTA */
.srv-cta {
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  padding: 4.5rem 0;
}

/* RESPONSIVE */
@media (max-width: 1199.98px) {
  .srv-cards { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 991.98px) {
  .process-steps { flex-wrap: wrap; gap: 2rem; }
  .step-arrow { display: none; }
}

@media (max-width: 767.98px) {
  .srv-hero { height: 40vh; min-height: 300px; }
  .srv-hero-title { font-size: 2.2rem; }
  .srv-cards { grid-template-columns: 1fr; }
  .srv-card-image { height: 200px; }
}
</style>
