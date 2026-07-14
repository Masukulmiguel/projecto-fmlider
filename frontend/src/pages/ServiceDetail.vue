<template>
  <div class="service-detail-page">
    <!-- Hero -->
    <section class="sd-hero" :style="{ backgroundImage: `linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(${service.image || defaultBg})` }">
      <div class="container position-relative">
        <router-link to="/servicos" class="sd-back">
          <i class="bi bi-arrow-left"></i> {{ t('service_detail.back') }}
        </router-link>
        <div class="sd-hero-content">
          <div class="sd-icon"><i :class="service.icon"></i></div>
          <h1>{{ service.title }}</h1>
          <p>{{ service.subtitle }}</p>
        </div>
      </div>
    </section>

    <!-- Content -->
    <section class="sd-content fml-section">
      <div class="container">
        <div class="row g-5">
          <div class="col-lg-8">
            <div class="sd-main">
              <h2>{{ service.title }}</h2>
              <p class="sd-lead">{{ service.description }}</p>

              <div class="sd-section" v-for="section in service.sections" :key="section.title">
                <h4><i :class="section.icon"></i> {{ section.title }}</h4>
                <p>{{ section.text }}</p>
                <ul v-if="section.list">
                  <li v-for="item in section.list" :key="item"><i class="bi bi-check2-circle"></i> {{ item }}</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="sd-sidebar">
              <div class="sd-cta-box">
                <h4>{{ t('service_detail.sidebar_title') }}</h4>
                <p>{{ t('service_detail.sidebar_text') }}</p>
                <router-link to="/contacto" class="btn btn-primary w-100 mb-2">
                  <i class="bi bi-envelope me-2"></i> {{ t('service_detail.cta_quote') }}
                </router-link>
                <a href="tel:+244935141747" class="btn btn-outline-primary w-100">
                  <i class="bi bi-telephone me-2"></i> {{ t('service_detail.cta_call') }}
                </a>
              </div>

              <div class="sd-features-box">
                <h5>{{ t('service_detail.features_title') }}</h5>
                <ul>
                  <li><i class="bi bi-shield-check"></i> {{ t('service_detail.feature_1') }}</li>
                  <li><i class="bi bi-speedometer"></i> {{ t('service_detail.feature_2') }}</li>
                  <li><i class="bi bi-clock"></i> {{ t('service_detail.feature_3') }}</li>
                  <li><i class="bi bi-people"></i> {{ t('service_detail.feature_4') }}</li>
                  <li><i class="bi bi-globe"></i> {{ t('service_detail.feature_5') }}</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from '@/composables/useI18n'

const route = useRoute()
const { t } = useI18n()

const defaultBg = '/assets/img/servico/service1.jpg'

const allServices = computed(() => ({
  'desembaraco-aduaneiro': {
    title: t('service_detail_pages.desembaraco_aduaneiro.title'),
    subtitle: t('service_detail_pages.desembaraco_aduaneiro.subtitle'),
    icon: 'bi bi-stamp',
    image: '/assets/img/servico/Desembaraço Aduaneiro.jpeg',
    description: t('service_detail_pages.desembaraco_aduaneiro.description'),
    sections: [
      {
        title: t('service_detail_pages.desembaraco_aduaneiro.section_1_title'),
        icon: 'bi bi-gear',
        text: t('service_detail_pages.desembaraco_aduaneiro.section_1_text'),
        list: t('service_detail_pages.desembaraco_aduaneiro.section_1_list'),
      },
      {
        title: t('service_detail_pages.desembaraco_aduaneiro.section_2_title'),
        icon: 'bi bi-trophy',
        text: t('service_detail_pages.desembaraco_aduaneiro.section_2_text'),
        list: t('service_detail_pages.desembaraco_aduaneiro.section_2_list'),
      }
    ]
  },
  'transportes': {
    title: t('service_detail_pages.transportes.title'),
    subtitle: t('service_detail_pages.transportes.subtitle'),
    icon: 'bi bi-truck',
    image: '/assets/img/servico/Transportes.jpg',
    description: t('service_detail_pages.transportes.description'),
    sections: [
      {
        title: t('service_detail_pages.transportes.section_1_title'),
        icon: 'bi bi-box-seam',
        text: t('service_detail_pages.transportes.section_1_text'),
        list: t('service_detail_pages.transportes.section_1_list'),
      },
      {
        title: t('service_detail_pages.transportes.section_2_title'),
        icon: 'bi bi-geo-alt',
        text: t('service_detail_pages.transportes.section_2_text'),
        list: t('service_detail_pages.transportes.section_2_list'),
      }
    ]
  },
  'armazenagem': {
    title: t('service_detail_pages.armazenagem.title'),
    subtitle: t('service_detail_pages.armazenagem.subtitle'),
    icon: 'bi bi-box-seam',
    image: '/assets/img/servico/service-storage.jpg',
    description: t('service_detail_pages.armazenagem.description'),
    sections: [
      {
        title: t('service_detail_pages.armazenagem.section_1_title'),
        icon: 'bi bi-building',
        text: t('service_detail_pages.armazenagem.section_1_text'),
        list: t('service_detail_pages.armazenagem.section_1_list'),
      },
      {
        title: t('service_detail_pages.armazenagem.section_2_title'),
        icon: 'bi bi-plus-circle',
        text: t('service_detail_pages.armazenagem.section_2_text'),
        list: t('service_detail_pages.armazenagem.section_2_list'),
      }
    ]
  },
  'door-to-door': {
    title: t('service_detail_pages.door_to_door.title'),
    subtitle: t('service_detail_pages.door_to_door.subtitle'),
    icon: 'bi bi-house-door',
    image: '/assets/img/servico/service-door.jpg',
    description: t('service_detail_pages.door_to_door.description'),
    sections: [
      {
        title: t('service_detail_pages.door_to_door.section_1_title'),
        icon: 'bi bi-list-check',
        text: t('service_detail_pages.door_to_door.section_1_text'),
        list: t('service_detail_pages.door_to_door.section_1_list'),
      },
      {
        title: t('service_detail_pages.door_to_door.section_2_title'),
        icon: 'bi bi-star',
        text: t('service_detail_pages.door_to_door.section_2_text'),
        list: t('service_detail_pages.door_to_door.section_2_list'),
      }
    ]
  },
  'consultoria': {
    title: t('service_detail_pages.consultoria.title'),
    subtitle: t('service_detail_pages.consultoria.subtitle'),
    icon: 'bi bi-people',
    image: '/assets/img/servico/service1.jpg',
    description: t('service_detail_pages.consultoria.description'),
    sections: [
      {
        title: t('service_detail_pages.consultoria.section_1_title'),
        icon: 'bi bi-lightbulb',
        text: t('service_detail_pages.consultoria.section_1_text'),
        list: t('service_detail_pages.consultoria.section_1_list'),
      },
      {
        title: t('service_detail_pages.consultoria.section_2_title'),
        icon: 'bi bi-graph-up',
        text: t('service_detail_pages.consultoria.section_2_text'),
        list: t('service_detail_pages.consultoria.section_2_list'),
      }
    ]
  },
  'logistica-maritima': {
    title: t('service_detail_pages.logistica_maritima.title'),
    subtitle: t('service_detail_pages.logistica_maritima.subtitle'),
    icon: 'bi bi-water',
    image: '/assets/img/servico/Logística Marítima-1.jpg',
    description: t('service_detail_pages.logistica_maritima.description'),
    sections: [
      {
        title: t('service_detail_pages.logistica_maritima.section_1_title'),
        icon: 'bi bi-ship',
        text: t('service_detail_pages.logistica_maritima.section_1_text'),
        list: t('service_detail_pages.logistica_maritima.section_1_list'),
      },
      {
        title: t('service_detail_pages.logistica_maritima.section_2_title'),
        icon: 'bi bi-handshake',
        text: t('service_detail_pages.logistica_maritima.section_2_text'),
        list: t('service_detail_pages.logistica_maritima.section_2_list'),
      }
    ]
  },
}))

const slug = computed(() => route.params.slug)
const service = computed(() => allServices.value[slug.value] || allServices.value['desembaraco-aduaneiro'])
</script>

<style scoped>
/* HERO */
.sd-hero {
  position: relative;
  min-height: 45vh;
  display: flex;
  align-items: flex-end;
  background-size: cover;
  background-position: center;
  padding: 3rem 0;
}
.sd-back {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: rgba(255,255,255,0.8);
  text-decoration: none;
  font-size: 0.9rem;
  margin-bottom: 2rem;
  transition: color 0.2s;
}
.sd-back:hover { color: var(--fml-gold, #f59e0b); }
.sd-hero-content {
  position: relative;
  color: #fff;
}
.sd-icon {
  width: 72px;
  height: 72px;
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin-bottom: 1.5rem;
}
.sd-hero-content h1 {
  font-size: 2.8rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 0.75rem;
}
.sd-hero-content p {
  font-size: 1.1rem;
  opacity: 0.9;
  max-width: 600px;
}

/* CONTENT */
.sd-main h2 {
  font-size: 1.8rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 1rem;
}
.sd-lead {
  font-size: 1.1rem;
  color: #475569;
  line-height: 1.7;
  margin-bottom: 2.5rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid #e5e7eb;
}

.sd-section {
  margin-bottom: 2.5rem;
}
.sd-section h4 {
  font-size: 1.2rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.sd-section h4 i {
  color: var(--fml-gold, #f59e0b);
}
.sd-section p {
  color: #475569;
  line-height: 1.7;
  margin-bottom: 1rem;
}
.sd-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.sd-section li {
  padding: 0.4rem 0;
  color: #334155;
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  font-size: 0.95rem;
}
.sd-section li i {
  color: var(--fml-gold, #f59e0b);
  margin-top: 0.25rem;
  flex-shrink: 0;
}

/* SIDEBAR */
.sd-cta-box {
  background: linear-gradient(135deg, var(--fml-navy, #0f172a), #1e3a5f);
  color: #fff;
  padding: 2rem;
  border-radius: 16px;
  margin-bottom: 1.5rem;
}
.sd-cta-box h4 {
  font-weight: 700;
  margin-bottom: 0.5rem;
}
.sd-cta-box p {
  color: rgba(255,255,255,0.7);
  font-size: 0.9rem;
  margin-bottom: 1.25rem;
}

.sd-features-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 1.5rem;
  border-radius: 16px;
}
.sd-features-box h5 {
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 1rem;
}
.sd-features-box ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.sd-features-box li {
  padding: 0.5rem 0;
  color: #334155;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.9rem;
  border-bottom: 1px solid #e5e7eb;
}
.sd-features-box li:last-child { border-bottom: none; }
.sd-features-box li i {
  color: var(--fml-gold, #f59e0b);
  flex-shrink: 0;
}

@media (max-width: 767.98px) {
  .sd-hero-content h1 { font-size: 2rem; }
  .sd-hero { min-height: 35vh; }
}
</style>
