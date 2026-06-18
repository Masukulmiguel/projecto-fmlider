<template>
  <div class="partners-page">
    <section class="page-hero">
      <div class="container">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb justify-content-center">
            <li class="breadcrumb-item"><router-link to="/" class="text-white-50">Início</router-link></li>
            <li class="breadcrumb-item active text-white">Parceiros</li>
          </ol>
        </nav>
        <h1 class="display-4 fw-bold text-white mb-3">Nossos Parceiros</h1>
        <p class="lead text-white-50 mx-auto" style="max-width: 650px;">Parcerias estratégicas que fortalecem a nossa rede logística e expandem o nosso alcance global.</p>
      </div>
    </section>

    <section class="fml-section">
      <div class="container">
        <div class="row align-items-center g-5">
          <div class="col-lg-6" v-reveal="'right'">
            <span class="fml-eyebrow">Quem Somos</span>
            <h2 class="section-title">Uma Rede de Parceiros de Confiança</h2>
            <p class="text-muted mb-4">
              Desde 2017, a FMLider tem construído relações sólidas com operadores logísticos de referência
              em Angola e no exterior. As nossas parcerias estratégicas permitem oferecer serviços integrados
              com qualidade, cobrindo todas as etapas da cadeia de abastecimento.
            </p>
            <p class="text-muted mb-4">
              Através destas alianças, conseguemos garantir soluções personalizadas para cada cliente,
              desde o desembaraço aduaneiro até ao transporte e armazenagem, sempre com a confiança
              e a segurança que o mercado exige.
            </p>
            <div class="row g-4 mt-2">
              <div class="col-6">
                <div class="stat-card">
                  <span class="stat-number">32+</span>
                  <span class="stat-label">Países com parcerias</span>
                </div>
              </div>
              <div class="col-6">
                <div class="stat-card">
                  <span class="stat-number">60+</span>
                  <span class="stat-label">Parceiros activos</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6" v-reveal="'left'">
            <div class="about-image-area">
              <img :src="getImage('partners', 'about', '/assets/img/construcao2020/image1.jpeg')" alt="Parcerias FMLider" class="about-img">
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="fml-section" style="background: #f8f9fa;">
      <div class="container">
        <div class="text-center mb-5" v-reveal="'fade'">
          <span class="fml-eyebrow">Vantagens</span>
          <h2 class="section-title">Por que ser parceiro da FMLider?</h2>
          <p class="text-muted mx-auto" style="max-width: 600px;">As nossas parcerias trazem benefícios concretos para todos os envolvidos na cadeia logística.</p>
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
          <span class="fml-eyebrow">Nossa Rede</span>
          <h2 class="section-title">Os Nossos Parceiros</h2>
          <p class="text-muted mx-auto" style="max-width: 600px;">Empresas e operadores que confiam na FMLider e que fazem parte da nossa rede logística internacional.</p>
        </div>
        <div class="row g-4">
          <div class="col-md-6 col-lg-4" v-for="partner in partners" :key="partner.id" v-reveal="'fade'">
            <div class="partner-card">
              <div class="partner-logo-area">
                <img :src="resolveLogo(partner.logo)" :alt="partner.name" class="partner-logo-img">
              </div>
              <div class="partner-body">
                <h5 class="partner-name">{{ partner.name }}</h5>
                <p class="partner-desc" v-if="partner.description">{{ partner.description }}</p>
                <a v-if="partner.website" :href="partner.website" target="_blank" rel="noopener" class="partner-link">
                  <i class="bi bi-box-arrow-up-right me-1"></i> Visitar site
                </a>
              </div>
            </div>
          </div>
        </div>
        <div v-if="partners.length === 0 && !loading" class="text-center py-5">
          <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
          <p class="text-muted mt-3">Nenhum parceiro encontrado.</p>
        </div>
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="text-muted mt-3">A carregar parceiros...</p>
        </div>
      </div>
    </section>

    <section class="fml-section cta-section" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);">
      <div class="container text-center" v-reveal="'scale'">
        <h2 class="display-5 fw-bold text-white mb-3">Quer tornar-se parceiro?</h2>
        <p class="lead text-white-50 mb-4 mx-auto" style="max-width: 550px;">Entre em contacto connosco e descubra como a parceria pode fortalecer o seu negócio.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <router-link to="/contacto" class="btn btn-gold btn-lg">
            <i class="bi bi-envelope me-2"></i> Fale Connosco
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
import { ref, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useSiteImages } from '@/composables/useSiteImages'

const { getImage, fetchAll } = useSiteImages()
const partners = ref([])
const loading = ref(true)

const benefits = [
  { icon: 'bi bi-globe-americas', title: 'Alcance Global', desc: 'Aceda a mercados internacionais através da nossa rede de parceiros em 32 países.' },
  { icon: 'bi bi-shield-check', title: 'Confiabilidade', desc: 'Parcerias baseadas em confiança, transparência e cumprimento rigoroso de prazos.' },
  { icon: 'bi bi-gear-wide-connected', title: 'Soluções Integradas', desc: 'Serviços completos de logística, armazenagem e desembaraço aduaneiro em package.' },
  { icon: 'bi bi-graph-up-arrow', title: 'Crescimento', desc: 'Expandimos juntos, criando valor para todos os membros da rede logística.' },
]

const resolveLogo = (logo) => {
  if (!logo) return ''
  if (logo.startsWith('http')) return logo
  if (logo.startsWith('/')) return logo
  return `/backend/storage/uploads/partners/${logo}`
}

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
</style>
