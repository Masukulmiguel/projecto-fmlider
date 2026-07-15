<template>
  <div class="partners-page">
    <section class="page-hero">
      <div class="container">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb justify-content-center">
            <li class="breadcrumb-item"><router-link to="/" class="text-white-50">{{ t('partners.breadcrumb_home') }}</router-link></li>
            <li class="breadcrumb-item active text-white">{{ t('partners.breadcrumb_partners') }}</li>
          </ol>
        </nav>
        <h1 class="display-4 fw-bold text-white mb-3">{{ t('partners.hero_title') }}</h1>
        <p class="lead text-white-50 mx-auto" style="max-width: 650px;">{{ t('partners.hero_subtitle') }}</p>
      </div>
    </section>

    <section class="fml-section">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6" v-reveal="'right'">
            <span class="fml-eyebrow">{{ t('partners.section_who') }}</span>
            <h2 class="section-title">{{ t('partners.title_network') }}</h2>
            <p class="text-muted mb-4">
              {{ t('partners.text_1') }}
            </p>
            <p class="text-muted mb-4">
              {{ t('partners.text_2') }}
            </p>
            <div class="row g-4 mt-2">
              <div class="col-6">
                <div class="stat-card">
                  <span class="stat-number">32+</span>
                  <span class="stat-label">{{ t('partners.stat_countries') }}</span>
                </div>
              </div>
              <div class="col-6">
                <div class="stat-card">
                  <span class="stat-number">60+</span>
                  <span class="stat-label">{{ t('partners.stat_partners') }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6" v-reveal="'left'">
            <div class="about-image-area">
              <img :src="getImage('partners', 'about', '/assets/img/construcao2020/image1.jpeg')" :alt="t('partners.img_alt')" class="about-img">
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="fml-section" style="background: #f8f9fa;">
      <div class="container">
        <div class="text-center mb-5" v-reveal="'fade'">
          <span class="fml-eyebrow">{{ t('partners.section_benefits') }}</span>
          <h2 class="section-title">{{ t('partners.title_why') }}</h2>
          <p class="text-muted mx-auto" style="max-width: 600px;">{{ t('partners.text_3') }}</p>
        </div>
        <div class="row g-4">
          <div class="col-md-6 col-lg-3" v-for="(benefit, i) in benefits" :key="i" v-reveal="'fade'">
            <div class="benefit-card">
              <div class="benefit-icon">
                <i :class="benefit.icon"></i>
              </div>
              <h5 class="benefit-title">{{ benefit.title }}</h5>
              <p class="benefit-desc">{{ benefit.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="fml-section">
      <div class="container">
        <div class="text-center mb-5" v-reveal="'fade'">
          <span class="fml-eyebrow">{{ t('partners.section_network') }}</span>
          <h2 class="section-title">{{ t('partners.title_partners') }}</h2>
          <p class="text-muted mx-auto" style="max-width: 600px;">{{ t('partners.network_description') }}</p>
        </div>
        <div class="row g-4">
          <div class="col-md-6 col-lg-4" v-for="partner in paginatedPartners" :key="partner.id" v-reveal="'fade'">
            <div class="partner-card">
              <div class="partner-logo-area">
                <img :src="resolveLogo(partner.logo)" :alt="partner.name" class="partner-logo-img">
              </div>
              <div class="partner-body">
                <h5 class="partner-name">{{ partner.name }}</h5>
                <p class="partner-desc" v-if="partner.description">{{ partner.description }}</p>
                <a v-if="partner.website" :href="partner.website" target="_blank" rel="noopener" class="partner-link">
                  <i class="bi bi-box-arrow-up-right me-1"></i> {{ t('partners.partner_link') }}
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div class="partners-pagination" v-if="totalPartnerPages > 1">
          <button class="partners-page-btn" :disabled="partnerPage === 1" @click="partnerPage--">
            <i class="bi bi-chevron-left"></i> {{ t('fleet.prev') || 'Anterior' }}
          </button>
          <div class="partners-page-dots">
            <button v-for="page in totalPartnerPages" :key="page" class="partners-page-dot" :class="{ active: partnerPage === page }" @click="partnerPage = page"></button>
          </div>
          <button class="partners-page-btn" :disabled="partnerPage === totalPartnerPages" @click="partnerPage++">
            {{ t('fleet.next') || 'Próximo' }} <i class="bi bi-chevron-right"></i>
          </button>
        </div>
        <div v-if="partners.length === 0 && !loading" class="text-center py-5">
          <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
          <p class="text-muted mt-3">{{ t('partners.empty') }}</p>
        </div>
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="text-muted mt-3">{{ t('partners.loading') }}</p>
        </div>
      </div>
    </section>

    <section class="fml-section cta-section" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);">
      <div class="container text-center" v-reveal="'scale'">
        <h2 class="display-5 fw-bold text-white mb-3">{{ t('partners.cta_title') }}</h2>
        <p class="lead text-white-50 mb-4 mx-auto" style="max-width: 550px;">{{ t('partners.cta_subtitle') }}</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <router-link to="/contacto" class="btn btn-gold btn-lg">
            <i class="bi bi-envelope me-2"></i> {{ t('partners.cta_button') }}
          </router-link>
          <a href="tel:+244935141747" class="btn btn-outline-light btn-lg">
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
import { useSiteImages } from '@/composables/useSiteImages'
import { useI18n } from '@/composables/useI18n'

const { t } = useI18n()
const { getImage, fetchAll } = useSiteImages()
const partners = ref([])
const loading = ref(true)
const partnerPage = ref(1)
const partnerPerPage = 6

const benefits = computed(() => [
  { icon: 'bi bi-globe-americas', title: t('partners.benefit_1_title'), desc: t('partners.benefit_1_desc') },
  { icon: 'bi bi-shield-check', title: t('partners.benefit_2_title'), desc: t('partners.benefit_2_desc') },
  { icon: 'bi bi-gear-wide-connected', title: t('partners.benefit_3_title'), desc: t('partners.benefit_3_desc') },
  { icon: 'bi bi-graph-up-arrow', title: t('partners.benefit_4_title'), desc: t('partners.benefit_4_desc') },
])

const resolveLogo = (logo) => {
  if (!logo) return ''
  if (logo.startsWith('http')) return logo
  if (logo.startsWith('/')) return logo
  return `/backend/storage/uploads/partners/${logo}`
}

const totalPartnerPages = computed(() => Math.ceil(partners.value.length / partnerPerPage))

const paginatedPartners = computed(() => {
  const start = (partnerPage.value - 1) * partnerPerPage
  return partners.value.slice(start, start + partnerPerPage)
})

onMounted(async () => {
  await fetchAll()
  try {
    const { data, error } = await supabase
      .from('partners')
      .select('id, name, logo, description, website')
      .eq('status', 1)
      .order('order_by')
    if (!error && data) {
      partners.value = data
    }
  } catch (e) {}
  loading.value = false
})
</script>

<style scoped>
.page-hero {
  background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
  padding: 6rem 0 3rem;
  text-align: center;
}

.about-image-area {
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
}

.about-img {
  width: 100%;
  height: 400px;
  object-fit: cover;
  display: block;
}

.stat-card {
  background: #f0f7ff;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
}

.stat-number {
  display: block;
  font-size: 2rem;
  font-weight: 800;
  color: var(--fml-gold);
}

.stat-label {
  font-size: 0.85rem;
  color: #6b7280;
  font-weight: 500;
}

.benefit-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 32px 24px;
  text-align: center;
  height: 100%;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.benefit-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
}

.benefit-icon {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: linear-gradient(135deg, #0f172a, #1e3a5f);
  color: var(--fml-gold);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin: 0 auto 20px;
}

.benefit-title {
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 8px;
}

.benefit-desc {
  color: #6b7280;
  font-size: 0.9rem;
  margin: 0;
}

.partner-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  height: 100%;
}

.partner-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
}

.partner-logo-area {
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px;
  background: #f9fafb;
}

.partner-logo-img {
  max-width: 70%;
  max-height: 70%;
  object-fit: contain;
}

.partner-body {
  padding: 16px 18px 20px;
}

.partner-name {
  font-weight: 700;
  margin-bottom: 6px;
  color: #0f172a;
  font-size: 0.95rem;
}

.partner-desc {
  color: #6b7280;
  font-size: 0.82rem;
  margin-bottom: 10px;
}

.partner-link {
  color: var(--fml-gold);
  font-weight: 600;
  font-size: 0.85rem;
  text-decoration: none;
}

.partner-link:hover {
  text-decoration: underline;
}

.btn-gold {
  background: var(--fml-gold);
  color: #0f172a;
  font-weight: 600;
  border: none;
}

.btn-gold:hover {
  background: #c9a227;
  color: #0f172a;
}

/* PAGINATION */
.partners-pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  margin-top: 3rem;
}
.partners-page-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.7rem 1.5rem;
  border: 2px solid #e2e8f0;
  background: #fff;
  border-radius: 50px;
  font-size: 0.9rem;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 0.25s ease;
}
.partners-page-btn:hover:not(:disabled) {
  border-color: var(--fml-gold, #f59e0b);
  color: var(--fml-gold, #f59e0b);
}
.partners-page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.partners-page-dots {
  display: flex;
  gap: 0.5rem;
}
.partners-page-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: none;
  background: #e2e8f0;
  cursor: pointer;
  transition: all 0.25s ease;
}
.partners-page-dot.active {
  background: var(--fml-gold, #f59e0b);
  transform: scale(1.3);
}

@media (max-width: 767.98px) {
  .partners-pagination { gap: 1rem; }
  .partners-page-btn { padding: 0.6rem 1.2rem; font-size: 0.85rem; }
}
</style>
