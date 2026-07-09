<template>
  <div class="about-page">
    <!-- Hero -->
    <section class="about-hero">
      <div class="about-hero-bg" :style="{ backgroundImage: `linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(${heroBg})` }"></div>
      <div class="container position-relative">
        <div class="about-hero-content">
          <span class="fml-eyebrow">{{ t('about.hero_eyebrow') }}</span>
          <h1 class="about-hero-title">{{ t('about.hero_title') }}</h1>
          <p class="about-hero-subtitle">
            {{ t('about.hero_subtitle') }}
          </p>
        </div>
      </div>
    </section>

    <!-- Stats Bar -->
    <section class="stats-bar">
      <div class="container">
        <div class="stats-grid">
          <div class="stat-item" v-for="stat in stats" :key="stat.label">
            <div class="stat-icon"><i :class="stat.icon"></i></div>
            <div class="stat-number">{{ stat.value }}</div>
            <div class="stat-label">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- History -->
    <section class="history-section fml-section">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6" v-reveal="'right'">
            <span class="fml-eyebrow">{{ t('about.history_eyebrow') }}</span>
            <h2 class="section-title">{{ t('about.history_title') }}</h2>
            <p class="text-muted mb-3" v-html="sanitize(t('about.history_p1'))"></p>
            <p class="text-muted mb-3" v-html="sanitize(t('about.history_p2'))"></p>
            <p class="text-muted mb-0" v-html="sanitize(t('about.history_p3'))"></p>
          </div>
          <div class="col-lg-6" v-reveal="'left'">
            <div class="history-image">
              <img :src="historyImage" :alt="t('about.history_img_alt')" class="img-fluid rounded shadow-lg">
              <div class="history-badge">
                <span class="badge-year">2017</span>
                <span class="badge-text">{{ t('about.history_badge') }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Timeline -->
    <section class="timeline-section fml-section bg-fml-navy text-white">
      <div class="container">
        <div class="text-center mb-5" v-reveal="'up'">
          <span class="fml-eyebrow">{{ t('about.timeline_eyebrow') }}</span>
          <h2 class="section-title text-white">{{ t('about.timeline_title') }}</h2>
        </div>
        <div class="timeline">
          <div class="timeline-item" v-for="(milestone, i) in milestones" :key="i" :class="{ reverse: i % 2 !== 0 }" v-reveal="'up'" :data-reveal-delay="i * 100">
            <div class="timeline-dot"></div>
            <div class="timeline-card">
              <span class="timeline-year">{{ milestone.year }}</span>
              <h5>{{ milestone.title }}</h5>
              <p>{{ milestone.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Mission Vision Values -->
    <section class="mvv-section fml-section">
      <div class="container">
        <div class="text-center mb-5" v-reveal="'up'">
          <span class="fml-eyebrow">{{ t('about.mvv_eyebrow') }}</span>
          <h2 class="section-title">{{ t('about.mvv_title') }}</h2>
        </div>
        <div class="row g-4">
          <div class="col-lg-4" v-reveal="'up'">
            <div class="mvv-card">
              <div class="mvv-icon"><i class="bi bi-bullseye"></i></div>
              <h4>{{ t('about.mvv_mission') }}</h4>
              <p>
                {{ t('about.mvv_mission_text') }}
              </p>
            </div>
          </div>
          <div class="col-lg-4" v-reveal="'up'" data-reveal-delay="120">
            <div class="mvv-card">
              <div class="mvv-icon"><i class="bi bi-eye"></i></div>
              <h4>{{ t('about.mvv_vision') }}</h4>
              <p>
                {{ t('about.mvv_vision_text') }}
              </p>
            </div>
          </div>
          <div class="col-lg-4" v-reveal="'up'" data-reveal-delay="240">
            <div class="mvv-card">
              <div class="mvv-icon"><i class="bi bi-gem"></i></div>
              <h4>{{ t('about.mvv_values') }}</h4>
              <ul class="mvv-values">
                <li><i class="bi bi-check-circle-fill"></i> {{ t('about.mvv_value_1') }}</li>
                <li><i class="bi bi-check-circle-fill"></i> {{ t('about.mvv_value_2') }}</li>
                <li><i class="bi bi-check-circle-fill"></i> {{ t('about.mvv_value_3') }}</li>
                <li><i class="bi bi-check-circle-fill"></i> {{ t('about.mvv_value_4') }}</li>
                <li><i class="bi bi-check-circle-fill"></i> {{ t('about.mvv_value_5') }}</li>
                <li><i class="bi bi-check-circle-fill"></i> {{ t('about.mvv_value_6') }}</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Infrastructure -->
    <section class="infra-section fml-section bg-fml-50">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6 order-lg-2" v-reveal="'left'">
            <span class="fml-eyebrow">{{ t('about.infra_eyebrow') }}</span>
            <h2 class="section-title">{{ t('about.infra_title') }}</h2>
            <p class="text-muted mb-4">
              {{ t('about.infra_text') }}
            </p>
            <div class="infra-features">
              <div class="infra-feature" v-for="feat in infraFeatures" :key="feat.title">
                <div class="infra-feature-icon"><i :class="feat.icon"></i></div>
                <div>
                  <h6>{{ feat.title }}</h6>
                  <small>{{ feat.desc }}</small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6 order-lg-1" v-reveal="'scale'">
            <div class="infra-image">
              <img :src="infraImage" :alt="t('about.infra_img_alt')" class="img-fluid rounded shadow-lg">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Certifications -->
    <section class="certs-section fml-section">
      <div class="container">
        <div class="text-center mb-5" v-reveal="'up'">
          <span class="fml-eyebrow">{{ t('about.certs_eyebrow') }}</span>
          <h2 class="section-title">{{ t('about.certs_title') }}</h2>
          <p class="section-subtitle text-muted">{{ t('about.certs_subtitle') }}</p>
        </div>
        <div class="row g-4 justify-content-center">
          <div class="col-md-6 col-lg-3" v-for="cert in certifications" :key="cert.title" v-reveal="'up'">
            <div class="cert-card-about">
              <div class="cert-icon"><i :class="cert.icon"></i></div>
              <h5>{{ cert.title }}</h5>
              <p>{{ cert.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Fleet Highlight -->
    <section class="fleet-about fml-section bg-fml-navy text-white">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6" v-reveal="'right'">
            <span class="fml-eyebrow">{{ t('about.fleet_eyebrow') }}</span>
            <h2 class="section-title text-white">{{ t('about.fleet_title') }}</h2>
            <p class="mb-4 text-white" v-html="sanitize(t('about.fleet_text'))"></p>
            <ul class="fleet-list">
              <li><i class="bi bi-check-circle-fill"></i> {{ t('about.fleet_feat_1') }}</li>
              <li><i class="bi bi-check-circle-fill"></i> {{ t('about.fleet_feat_2') }}</li>
              <li><i class="bi bi-check-circle-fill"></i> {{ t('about.fleet_feat_3') }}</li>
              <li><i class="bi bi-check-circle-fill"></i> {{ t('about.fleet_feat_4') }}</li>
            </ul>
            <router-link to="/frota" class="btn btn-gold mt-3">
              {{ t('about.fleet_cta') }} <i class="bi bi-arrow-right ms-2"></i>
            </router-link>
          </div>
          <div class="col-lg-6" v-reveal="'scale'">
            <div class="fleet-about-image">
              <img :src="fleetImage" :alt="t('about.fleet_img_alt')" class="img-fluid rounded shadow-lg">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta-about fml-section-gold">
      <div class="container text-center">
        <h2 class="display-5 fw-bold mb-3">{{ t('about.cta_title') }}</h2>
        <p class="lead mb-4">{{ t('about.cta_subtitle') }}</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <router-link to="/contacto" class="btn btn-dark btn-lg">
            <i class="bi bi-envelope me-2"></i> {{ t('about.cta_contact') }}
          </router-link>
          <router-link to="/servicos" class="btn btn-outline-dark btn-lg">
            <i class="bi bi-arrow-right-circle me-2"></i> {{ t('about.cta_services') }}
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from '@/composables/useI18n'
import { useSiteImages } from '@/composables/useSiteImages'
import { sanitize } from '@/utils/sanitize'

const { t } = useI18n()
const { getImage, fetchAll } = useSiteImages()
const heroBg = ref('/assets/img/construcao2020/image1.jpeg')
const historyImage = ref('/assets/img/construcao2020/image2.jpeg')
const infraImage = ref('/assets/img/construcao2020/image3.jpeg')
const fleetImage = ref('/assets/img/resachstacker/resachstacker2.jpeg')

const stats = computed(() => [
  { value: '+8', label: t('about.stat_years'), icon: 'bi bi-calendar-check' },
  { value: '+60', label: t('about.stat_employees'), icon: 'bi bi-people-fill' },
  { value: '+30', label: t('about.stat_countries'), icon: 'bi bi-globe2' },
  { value: '+1000', label: t('about.stat_clients'), icon: 'bi bi-hand-thumbs-up-fill' },
  { value: '+15', label: t('about.stat_trucks'), icon: 'bi bi-truck' },
  { value: '5000m²', label: t('about.stat_warehouses'), icon: 'bi bi-box-seam' },
])

const milestones = computed(() => [
  { year: '2017', title: t('about.milestone_2017_title'), desc: t('about.milestone_2017_desc') },
  { year: '2018', title: t('about.milestone_2018_title'), desc: t('about.milestone_2018_desc') },
  { year: '2019', title: t('about.milestone_2019_title'), desc: t('about.milestone_2019_desc') },
  { year: '2020', title: t('about.milestone_2020_title'), desc: t('about.milestone_2020_desc') },
  { year: '2022', title: t('about.milestone_2022_title'), desc: t('about.milestone_2022_desc') },
  { year: '2024', title: t('about.milestone_2024_title'), desc: t('about.milestone_2024_desc') },
])

const infraFeatures = computed(() => [
  { icon: 'bi bi-box-seam', title: t('about.infra_feat_1_title'), desc: t('about.infra_feat_1_desc') },
  { icon: 'bi bi-truck', title: t('about.infra_feat_2_title'), desc: t('about.infra_feat_2_desc') },
  { icon: 'bi bi-shield-lock', title: t('about.infra_feat_3_title'), desc: t('about.infra_feat_3_desc') },
  { icon: 'bi bi-geo-alt', title: t('about.infra_feat_4_title'), desc: t('about.infra_feat_4_desc') },
])

const certifications = computed(() => [
  { icon: 'bi bi-shield-check', title: t('about.cert_1_title'), desc: t('about.cert_1_desc') },
  { icon: 'bi bi-award', title: t('about.cert_2_title'), desc: t('about.cert_2_desc') },
  { icon: 'bi bi-people', title: t('about.cert_3_title'), desc: t('about.cert_3_desc') },
  { icon: 'bi bi-truck', title: t('about.cert_4_title'), desc: t('about.cert_4_desc') },
])

onMounted(async () => {
  await fetchAll()
  heroBg.value = getImage('about', 'hero_bg', '/assets/img/construcao2020/image1.jpeg')
  historyImage.value = getImage('about', 'history_image', '/assets/img/construcao2020/image2.jpeg')
  infraImage.value = getImage('about', 'infra_image', '/assets/img/construcao2020/image3.jpeg')
  fleetImage.value = getImage('about', 'fleet_image', '/assets/img/resachstacker/resachstacker2.jpeg')
})
</script>

<style scoped>
.about-page {
  background: #fff;
}

/* HERO */
.about-hero {
  position: relative;
  height: 50vh;
  min-height: 380px;
  display: flex;
  align-items: center;
  overflow: hidden;
  background: var(--fml-navy, #0f172a);
}
.about-hero-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
}
.about-hero-content {
  position: relative;
  color: #fff;
  max-width: 700px;
}
.about-hero-title {
  font-size: 3.5rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 1rem;
  letter-spacing: -0.03em;
}
.about-hero-subtitle {
  font-size: 1.2rem;
  line-height: 1.7;
  opacity: 0.9;
}

/* STATS BAR */
.stats-bar {
  background: var(--fml-navy, #0f172a);
  padding: 3rem 0;
  margin-top: -1px;
}
.stats-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 1.5rem;
}
.stat-item {
  text-align: center;
}
.stat-icon {
  font-size: 1.5rem;
  color: var(--fml-gold, #f59e0b);
  margin-bottom: 0.5rem;
}
.stat-number {
  font-size: 1.8rem;
  font-weight: 800;
  color: #fff;
  line-height: 1;
}
.stat-label {
  font-size: 0.78rem;
  color: rgba(255,255,255,1);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-top: 0.35rem;
}

/* HISTORY */
.history-image {
  position: relative;
}
.history-badge {
  position: absolute;
  bottom: -1rem;
  left: 2rem;
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  padding: 1rem 1.5rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
}
.badge-year {
  display: block;
  font-size: 1.6rem;
  font-weight: 800;
  line-height: 1;
}
.badge-text {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* TIMELINE */
.timeline {
  position: relative;
  max-width: 800px;
  margin: 0 auto;
}
.timeline::before {
  content: '';
  position: absolute;
  left: 50%;
  top: 0;
  bottom: 0;
  width: 2px;
  background: rgba(245, 158, 11, 0.3);
  transform: translateX(-50%);
}
.timeline-item {
  position: relative;
  padding: 0 0 3rem;
  display: flex;
  justify-content: flex-end;
  padding-right: calc(50% + 30px);
}
.timeline-item.reverse {
  justify-content: flex-start;
  padding-right: 0;
  padding-left: calc(50% + 30px);
}
.timeline-dot {
  position: absolute;
  left: 50%;
  top: 0.5rem;
  width: 16px;
  height: 16px;
  background: var(--fml-gold, #f59e0b);
  border: 3px solid var(--fml-navy, #0f172a);
  border-radius: 50%;
  transform: translateX(-50%);
  z-index: 2;
}
.timeline-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  max-width: 340px;
}
.timeline-year {
  display: inline-block;
  background: var(--fml-gold, #f59e0b);
  color: var(--fml-900, #1e293b);
  padding: 0.2rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
}
.timeline-card h5 {
  color: #fff;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
}
.timeline-card p {
  color: rgba(255, 255, 255, 1);
  font-size: 0.9rem;
  margin: 0;
  line-height: 1.6;
}

/* MVV */
.mvv-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 2.5rem 2rem;
  height: 100%;
  transition: all 0.3s ease;
  text-align: center;
}
.mvv-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
  border-color: var(--fml-gold, #f59e0b);
}
.mvv-icon {
  width: 72px;
  height: 72px;
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  margin: 0 auto 1.5rem;
}
.mvv-card h4 {
  font-weight: 700;
  color: var(--fml-900, #1e293b);
  margin-bottom: 1rem;
}
.mvv-card p {
  color: #64748b;
  line-height: 1.7;
  margin: 0;
}
.mvv-values {
  list-style: none;
  padding: 0;
  margin: 0;
  text-align: left;
}
.mvv-values li {
  padding: 0.5rem 0;
  color: #334155;
  display: flex;
  align-items: center;
  gap: 0.6rem;
}
.mvv-values i {
  color: var(--fml-gold, #f59e0b);
  font-size: 1rem;
}

/* INFRA */
.infra-features {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
.infra-feature {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  transition: all 0.2s ease;
}
.infra-feature:hover {
  border-color: var(--fml-gold, #f59e0b);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}
.infra-feature-icon {
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
.infra-feature h6 {
  margin: 0;
  font-size: 0.95rem;
  color: #0f172a;
}
.infra-feature small {
  color: #64748b;
  font-size: 0.82rem;
}

/* CERTS */
.cert-card-about {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 2rem 1.5rem;
  text-align: center;
  height: 100%;
  transition: all 0.3s ease;
}
.cert-card-about:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
  border-color: var(--fml-gold, #f59e0b);
}
.cert-icon {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  margin: 0 auto 1.25rem;
}
.cert-card-about h5 {
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.75rem;
}
.cert-card-about p {
  color: #64748b;
  font-size: 0.9rem;
  margin: 0;
  line-height: 1.6;
}

/* FLEET */
.fleet-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.fleet-list li {
  padding: 0.5rem 0;
  color: rgba(255, 255, 255, 0.85);
  display: flex;
  align-items: center;
  gap: 0.6rem;
}
.fleet-list i {
  color: var(--fml-gold, #f59e0b);
  font-size: 1.1rem;
}
.fleet-about-image img {
  width: 100%;
}

/* CTA */
.cta-about {
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  padding: 4.5rem 0;
}

/* RESPONSIVE */
@media (max-width: 991.98px) {
  .stats-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  .timeline::before { left: 20px; }
  .timeline-item,
  .timeline-item.reverse {
    padding-left: 50px;
    padding-right: 0;
    justify-content: flex-start;
  }
  .timeline-dot { left: 20px; }
}

@media (max-width: 767.98px) {
  .about-hero { height: 40vh; min-height: 300px; }
  .about-hero-title { font-size: 2.2rem; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
  .stat-number { font-size: 1.4rem; }
}

@media (max-width: 575.98px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
