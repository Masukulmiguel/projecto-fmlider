<template>
  <div class="home">
    <!-- Hero Slider -->
    <section class="hero-slider">
      <div class="hero-slide" v-for="(s, i) in slides" :key="i" :class="{ active: i === currentSlide }" :style="{ backgroundImage: `linear-gradient(135deg, rgba(15,23,42,0.85) 0%, rgba(30,58,138,0.75) 50%, rgba(15,23,42,0.85) 100%), url(/assets/img/${s.image})` }">
        <div class="container">
          <div class="hero-content">
            <span class="hero-eyebrow" data-aos="fade-up">{{ s.eyebrow }}</span>
            <h1 class="hero-title" data-aos="fade-up" data-aos-delay="100">{{ s.title }}</h1>
            <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="200">{{ s.subtitle }}</p>
            <div class="hero-ctas" data-aos="fade-up" data-aos-delay="300">
              <router-link to="/servicos" class="btn btn-gold btn-lg">
                <i class="bi bi-arrow-right-circle me-2"></i> {{ s.cta }}
              </router-link>
              <router-link to="/contacto" class="btn btn-outline-light btn-lg">
                <i class="bi bi-telephone me-2"></i> Falar Connosco
              </router-link>
            </div>
            <div class="hero-stats" data-aos="fade-up" data-aos-delay="400">
              <div class="hero-stat">
                <strong>+8</strong>
                <span>anos</span>
              </div>
              <div class="hero-stat">
                <strong>+60</strong>
                <span>colaboradores</span>
              </div>
              <div class="hero-stat">
                <strong>+32</strong>
                <span>países</span>
              </div>
              <div class="hero-stat">
                <strong>+1000</strong>
                <span>clientes</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <button class="hero-nav prev" @click="prevSlide" aria-label="Slide anterior">
        <i class="bi bi-chevron-left"></i>
      </button>
      <button class="hero-nav next" @click="nextSlide" aria-label="Slide seguinte">
        <i class="bi bi-chevron-right"></i>
      </button>

      <div class="hero-dots">
        <button v-for="(_, i) in slides" :key="i" :class="{ active: i === currentSlide }" @click="currentSlide = i" :aria-label="`Ir para slide ${i+1}`"></button>
      </div>
    </section>

    <!-- About strip -->
    <section class="about-strip">
      <div class="container">
        <div class="row align-items-center g-4">
          <div class="col-lg-7">
            <span class="fml-eyebrow">Quem Somos</span>
            <h2 class="section-title mb-3">Logística que move o seu negócio</h2>
            <p class="text-muted mb-0">
              A <strong>FMLider</strong> iniciou a sua actividade em Fevereiro de 2017 em Luanda.
              Actualmente conta com cerca de 60 colaboradores, instalações próprias e frota de camiões própria.
              Operamos em todo o território nacional e em mais de 30 países, com soluções integradas
              de transporte, transitário, armazenagem e logística.
            </p>
          </div>
          <div class="col-lg-5">
            <div class="cert-card">
              <div class="cert-item">
                <i class="bi bi-shield-check"></i>
                <div>
                  <h6>Seguro All Risks</h6>
                  <small>Cobertura internacional</small>
                </div>
              </div>
              <div class="cert-item">
                <i class="bi bi-award"></i>
                <div>
                  <h6>Certificação ISO</h6>
                  <small>Qualidade e segurança</small>
                </div>
              </div>
              <div class="cert-item">
                <i class="bi bi-truck"></i>
                <div>
                  <h6>Frota Própria</h6>
                  <small>+15 camiões e Reachstacker</small>
                </div>
              </div>
              <div class="cert-item">
                <i class="bi bi-geo-alt"></i>
                <div>
                  <h6>Cobertura Global</h6>
                  <small>+30 países servidos</small>
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
        <div class="text-center mb-5">
          <span class="fml-eyebrow">O Que Fazemos</span>
          <h2 class="section-title text-white">Serviços Integrados</h2>
          <p class="section-subtitle text-white-50">Soluções completas de logística, transporte e transitário adaptadas ao seu negócio.</p>
        </div>
        <div class="row g-4">
          <div class="col-lg-3 col-md-6" v-for="(s, i) in services" :key="i">
            <router-link :to="s.link" class="service-card">
              <div class="service-icon"><i :class="s.icon"></i></div>
              <h5>{{ s.title }}</h5>
              <p>{{ s.desc }}</p>
              <span class="service-link">Saber mais <i class="bi bi-arrow-right"></i></span>
            </router-link>
          </div>
        </div>
        <div class="text-center mt-5">
          <router-link to="/servicos" class="btn btn-gold btn-lg">
            Ver Todos os Serviços <i class="bi bi-arrow-right ms-2"></i>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Process -->
    <section class="process-section fml-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="fml-eyebrow">Como Trabalhamos</span>
          <h2 class="section-title">Do Pedido à Entrega em 4 Passos</h2>
        </div>
        <div class="row g-4 process-steps">
          <div class="col-md-6 col-lg-3" v-for="(step, i) in process" :key="i">
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
          <div class="col-lg-6">
            <span class="fml-eyebrow">Frota & Equipamentos</span>
            <h2 class="section-title">Reachstacker Kalmar de 45 toneladas</h2>
            <p class="text-muted mb-4">
              Em 2022 investimos num <strong>Reachstacker Kalmar</strong> para optimizar o
              manuseamento de contentores, ferro e cargas especiais. Esta máquina representa
              uma mais-valia significativa para a nossa estrutura e para os nossos clientes,
              permitindo operações de carga e descarga mais rápidas e seguras.
            </p>
            <ul class="feature-list">
              <li><i class="bi bi-check-circle-fill"></i> Capacidade de elevação de 45 toneladas</li>
              <li><i class="bi bi-check-circle-fill"></i> Manuseamento de contentores 20' e 40'</li>
              <li><i class="bi bi-check-circle-fill"></i> Stack de até 4 contentores</li>
              <li><i class="bi bi-check-circle-fill"></i> Operadores certificados e experientes</li>
            </ul>
            <router-link to="/frota" class="btn btn-primary mt-3">
              Ver a Nossa Frota <i class="bi bi-arrow-right ms-2"></i>
            </router-link>
          </div>
          <div class="col-lg-6">
            <div class="fleet-image">
              <img src="/assets/img/resachstacker/resachstacker1.jpeg" alt="Reachstacker FMLider" class="img-fluid rounded">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- News -->
    <section class="news-section fml-section">
      <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
          <div>
            <span class="fml-eyebrow">Novidades</span>
            <h2 class="section-title mb-0">Últimas Notícias</h2>
          </div>
          <router-link to="/noticias" class="btn btn-outline-primary">
            Ver Todas <i class="bi bi-arrow-right ms-1"></i>
          </router-link>
        </div>
        <div class="row g-4">
          <div class="col-lg-4" v-for="newsItem in latestNews" :key="newsItem.id">
            <NewsCard :news="newsItem" />
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section v-if="testimonials.length" class="testimonials-section fml-section bg-fml-navy text-white">
      <div class="container">
        <div class="text-center mb-5">
          <span class="fml-eyebrow">Confiança</span>
          <h2 class="section-title text-white">O Que Dizem os Nossos Clientes</h2>
        </div>
        <div class="row g-4">
          <div class="col-md-4" v-for="(t, i) in testimonials.slice(0, 3)" :key="i">
            <div class="testimonial-card">
              <div class="stars mb-2">
                <i v-for="n in t.rating" :key="n" class="bi bi-star-fill"></i>
              </div>
              <p class="testimonial-text">"{{ t.message }}"</p>
              <div class="testimonial-author">
                <div class="avatar">{{ t.name.charAt(0) }}</div>
                <div>
                  <strong>{{ t.name }}</strong>
                  <small v-if="t.company" class="d-block text-white-50">{{ t.position }} · {{ t.company }}</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Nossos Clientes -->
    <section class="partners-section fml-section-sm">
      <div class="container">
        <div class="text-center mb-4">
          <p class="text-uppercase small fw-bold mb-1" style="letter-spacing: 2px; color: #0f766e;">Confiam em nós</p>
          <h2 class="fw-bold mb-2">Nossos Clientes</h2>
          <p class="text-muted mb-0">Empresas que confiam na FMLider para o seu dia a dia logístico.</p>
        </div>
        <PartnersCarousel />
      </div>
    </section>

    <!-- CTA -->
    <section class="cta-section fml-section-gold">
      <div class="container text-center">
        <h2 class="display-5 fw-bold mb-3">Pronto para Enviar?</h2>
        <p class="lead mb-4 opacity-90">Solicite um orçamento gratuito em menos de 24 horas.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <router-link to="/cotacoes/novo" class="btn btn-dark btn-lg">
            <i class="bi bi-receipt me-2"></i> Pedir Cotação
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
          <div class="col-lg-5">
            <span class="fml-eyebrow">Onde Estamos</span>
            <h2 class="section-title">Visite-nos</h2>
            <p class="text-muted mb-4">Estamos localizados em Cacuaco, com acesso facilitado às principais vias e ao Porto de Luanda.</p>
            <ul class="location-info list-unstyled">
              <li class="d-flex align-items-start mb-3">
                <i class="bi bi-geo-alt-fill text-primary fs-4 me-3"></i>
                <div>
                  <strong class="d-block text-dark">Morada</strong>
                  FMLider Base, Estrada da Pedreira<br>
                  Bairro da Vidrul, Cacuaco, Luanda
                </div>
              </li>
              <li class="d-flex align-items-start mb-3">
                <i class="bi bi-telephone-fill text-primary fs-4 me-3"></i>
                <div>
                  <strong class="d-block text-dark">Telefone</strong>
                  <a href="tel:+244935141747" class="text-decoration-none text-dark">+244 935 141 747</a>
                </div>
              </li>
              <li class="d-flex align-items-start mb-3">
                <i class="bi bi-envelope-fill text-primary fs-4 me-3"></i>
                <div>
                  <strong class="d-block text-dark">Email</strong>
                  <a href="mailto:geral@fmlider.co.ao" class="text-decoration-none text-dark">geral@fmlider.co.ao</a>
                </div>
              </li>
              <li class="d-flex align-items-start mb-3">
                <i class="bi bi-clock-fill text-primary fs-4 me-3"></i>
                <div>
                  <strong class="d-block text-dark">Horário</strong>
                  Segunda a sexta: 08:00 - 18:00<br>
                  Sábado: 08:00 - 13:00
                </div>
              </li>
            </ul>
          </div>
          <div class="col-lg-7">
            <div class="map-container shadow">
              <iframe
                src="https://maps.google.com/maps?q=FMLider+Base+Cacuaco+Luanda+Angola&hl=pt&z=15&output=embed"
                width="100%" height="420" style="border:0; border-radius: 12px;"
                allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                title="Localização FMLider"
              ></iframe>
            </div>
            <a href="https://www.google.com/maps/place/FMLider+-+Base/@-8.7693538,13.3973228,359m/data=!3m1!1e3!4m6!3m5!1s0x1a51e5684ed42f1b:0x5630ab6f53efd403!8m2!3d-8.769266!4d13.3984122"
               target="_blank" rel="noopener"
               class="btn btn-outline-primary w-100 mt-3">
              <i class="bi bi-geo-alt-fill me-2"></i> Abrir no Google Maps
            </a>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import NewsCard from '@/components/NewsCard.vue'
import PartnersCarousel from '@/components/PartnersCarousel.vue'
import axios from 'axios'

const currentSlide = ref(0)
let interval = null

const slides = [
  {
    image: 'construcao2020/image1.jpeg',
    eyebrow: 'Desembaraço Aduaneiro',
    title: 'A sua carga, o nosso compromisso',
    subtitle: 'Especialistas em importação, exportação e processos aduaneiros em Angola. Agilizamos o seu negócio.',
    cta: 'Conhecer Serviços',
  },
  {
    image: 'construcao2020/image2.jpeg',
    eyebrow: 'Frota Própria',
    title: 'Transportes que chegam a todo o lado',
    subtitle: 'Frota moderna de camiões e contentores 20"/40". Rastreamento GPS em tempo real, cobertura nacional e SADC.',
    cta: 'Ver Frota',
  },
  {
    image: 'construcao2020/image3.jpeg',
    eyebrow: 'Armazenagem',
    title: 'Espaço seguro para a sua mercadoria',
    subtitle: '5.000m² de armazém em Luanda, Viana e Benguela. Picking, packing, cross-docking e CCTV 24h.',
    cta: 'Ver Armazéns',
  },
]

const services = [
  { icon: 'bi bi-shield-check', title: 'Despachante', desc: 'Desalfandegamento, DU, licenças de importação e exportação.', link: '/servicos/despachante' },
  { icon: 'bi bi-truck', title: 'Transportes', desc: 'Rodoviário, marítimo e aéreo com cobertura global.', link: '/servicos' },
  { icon: 'bi bi-box-seam', title: 'Armazenagem', desc: '5.000m² de armazéns próprios com CCTV 24h.', link: '/servicos' },
  { icon: 'bi bi-house-door', title: 'Door to Door', desc: 'Soluções completas desde a origem ao destino final.', link: '/servicos' },
]

const process = [
  { title: 'Pedido', desc: 'Solicite orçamento online ou por telefone em minutos.' },
  { title: 'Planeamento', desc: 'A nossa equipa define a melhor rota e modo de transporte.' },
  { title: 'Transporte', desc: 'Carga segue com rastreamento GPS em tempo real.' },
  { title: 'Entrega', desc: 'Desalfandegamento e entrega na morada indicada.' },
]

const latestNews = ref([])
const testimonials = ref([])

const fetchContent = async () => {
  try {
    const [news, tests] = await Promise.all([
      axios.get('/api/news'),
      axios.get('/api/testimonials'),
    ])
    if (news.data.success) latestNews.value = (news.data.news || []).slice(0, 3)
    if (tests.data.success) testimonials.value = tests.data.testimonials || []
  } catch (e) { /* silent */ }
}

const nextSlide = () => { currentSlide.value = (currentSlide.value + 1) % slides.length }
const prevSlide = () => { currentSlide.value = (currentSlide.value - 1 + slides.length) % slides.length }

onMounted(() => {
  fetchContent()
  interval = setInterval(nextSlide, 6000)
})
onUnmounted(() => { if (interval) clearInterval(interval) })
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
  opacity: 0.92;
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
.hero-stat span { font-size: 0.85rem; opacity: 0.8; margin-top: 0.25rem; text-transform: uppercase; letter-spacing: 1.5px; }

.hero-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 56px;
  height: 56px;
  border-radius: 50%;
  border: 1.5px solid rgba(255, 255, 255, 0.3);
  background: rgba(15, 23, 42, 0.4);
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
