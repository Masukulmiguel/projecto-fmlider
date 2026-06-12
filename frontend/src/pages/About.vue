<template>
  <div class="about-page">
    <!-- Hero -->
    <section class="about-hero">
      <div class="about-hero-bg" style="background-image: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(/assets/img/construcao2020/image1.jpeg);"></div>
      <div class="container position-relative">
        <div class="about-hero-content">
          <span class="fml-eyebrow">Quem Somos</span>
          <h1 class="about-hero-title">A FMLider</h1>
          <p class="about-hero-subtitle">
            Há mais de 8 anos a mover o comércio de Angola para o mundo.
            Soluções integradas de logística, transporte e transitário com excelência.
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
          <div class="col-lg-6">
            <span class="fml-eyebrow">A Nossa História</span>
            <h2 class="section-title">De Luanda para o Mundo</h2>
            <p class="text-muted mb-3">
              A <strong>FMLider</strong> iniciou a sua actividade em <strong>Fevereiro de 2017</strong> em Luanda,
              Angola. Nascemos com o objectivo de oferecer soluções logísticas de excelência num mercado
              em constante crescimento.
            </p>
            <p class="text-muted mb-3">
              Ao longo dos anos, crescemos de forma sustentável, construindo uma reputação sólida
              baseada na confiança, no profissionalismo e na entrega consistente. Hoje, contamos com
              <strong>mais de 60 colaboradores</strong>, <strong>instalações próprias</strong> e uma
              <strong>frota de camiões e equipamentos</strong> que nos permitem servir clientes em
              todo o território nacional e além-fronteiras.
            </p>
            <p class="text-muted mb-0">
              Enfrentámos as dificuldades económicas de Angola com resiliência e inovação, adaptando-nos
              sempre às necessidades do mercado e dos nossos clientes. Cada desafio tornou-nos mais fortes
              e mais preparados.
            </p>
          </div>
          <div class="col-lg-6">
            <div class="history-image">
              <img src="/assets/img/construcao2020/image2.jpeg" alt="FMLider Base" class="img-fluid rounded shadow-lg">
              <div class="history-badge">
                <span class="badge-year">2017</span>
                <span class="badge-text">Fundação</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Timeline -->
    <section class="timeline-section fml-section bg-fml-navy text-white">
      <div class="container">
        <div class="text-center mb-5">
          <span class="fml-eyebrow">Trajetória</span>
          <h2 class="section-title text-white">Marcos Importantes</h2>
        </div>
        <div class="timeline">
          <div class="timeline-item" v-for="(milestone, i) in milestones" :key="i" :class="{ reverse: i % 2 !== 0 }">
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
        <div class="text-center mb-5">
          <span class="fml-eyebrow">Os Nossos Pilares</span>
          <h2 class="section-title">Missão, Visão e Valores</h2>
        </div>
        <div class="row g-4">
          <div class="col-lg-4">
            <div class="mvv-card">
              <div class="mvv-icon"><i class="bi bi-bullseye"></i></div>
              <h4>Missão</h4>
              <p>
                Prestar serviços de logística e transportes de excelência, com foco na satisfação
                do cliente. Oferecer soluções integradas, seguras e eficientes que contribuam para
                o crescimento dos negócios dos nossos parceiros.
              </p>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="mvv-card">
              <div class="mvv-icon"><i class="bi bi-eye"></i></div>
              <h4>Visão</h4>
              <p>
                Ser a empresa de referência em logística e transportes em Angola e na região SADC,
                reconhecida pela inovação, qualidade de serviço e compromisso com a sustentabilidade.
              </p>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="mvv-card">
              <div class="mvv-icon"><i class="bi bi-gem"></i></div>
              <h4>Valores</h4>
              <ul class="mvv-values">
                <li><i class="bi bi-check-circle-fill"></i> Profissionalismo</li>
                <li><i class="bi bi-check-circle-fill"></i> Confiança</li>
                <li><i class="bi bi-check-circle-fill"></i> Segurança</li>
                <li><i class="bi bi-check-circle-fill"></i> Inovação</li>
                <li><i class="bi bi-check-circle-fill"></i> Integridade</li>
                <li><i class="bi bi-check-circle-fill"></i> Compromisso</li>
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
          <div class="col-lg-6 order-lg-2">
            <span class="fml-eyebrow">Infraestrutura</span>
            <h2 class="section-title">Instalações & Equipamentos</h2>
            <p class="text-muted mb-4">
              Contamos com infraestrutura própria projetada para garantir a segurança e eficiência
              em todas as etapas do processo logístico.
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
          <div class="col-lg-6 order-lg-1">
            <div class="infra-image">
              <img src="/assets/img/construcao2020/image3.jpeg" alt="Armazém FMLider" class="img-fluid rounded shadow-lg">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Certifications -->
    <section class="certs-section fml-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="fml-eyebrow">Qualidade</span>
          <h2 class="section-title">Certificações & Credenciais</h2>
          <p class="section-subtitle text-muted">O nosso compromisso com a excelência é certificado.</p>
        </div>
        <div class="row g-4 justify-content-center">
          <div class="col-md-6 col-lg-3" v-for="cert in certifications" :key="cert.title">
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
          <div class="col-lg-6">
            <span class="fml-eyebrow">Frota Própria</span>
            <h2 class="section-title text-white">Reachstacker Kalmar de 45t</h2>
            <p class="text-white-50 mb-4">
              Em 2022 investimos num <strong>Reachstacker Kalmar</strong> de 45 toneladas,
              reforçando a nossa capacidade de manuseamento de contentores e cargas especiais.
              Esta máquina representa uma mais-valia significativa para a nossa operação.
            </p>
            <ul class="fleet-list">
              <li><i class="bi bi-check-circle-fill"></i> Capacidade de 45 toneladas</li>
              <li><i class="bi bi-check-circle-fill"></i> Contentores 20' e 40'</li>
              <li><i class="bi bi-check-circle-fill"></i> Stack de até 4 contentores</li>
              <li><i class="bi bi-check-circle-fill"></i> Operadores certificados</li>
            </ul>
            <router-link to="/frota" class="btn btn-gold mt-3">
              Ver Frota Completa <i class="bi bi-arrow-right ms-2"></i>
            </router-link>
          </div>
          <div class="col-lg-6">
            <div class="fleet-about-image">
              <img src="/assets/img/resachstacker/resachstacker2.jpeg" alt="Frota FMLider" class="img-fluid rounded shadow-lg">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="cta-about fml-section-gold">
      <div class="container text-center">
        <h2 class="display-5 fw-bold mb-3">Pronto para Trabalhar Connosco?</h2>
        <p class="lead mb-4 opacity-90">Solicite um orçamento ou fale com a nossa equipa.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <router-link to="/contacto" class="btn btn-dark btn-lg">
            <i class="bi bi-envelope me-2"></i> Contactar
          </router-link>
          <router-link to="/servicos" class="btn btn-outline-dark btn-lg">
            <i class="bi bi-arrow-right-circle me-2"></i> Ver Serviços
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
const stats = [
  { value: '+8', label: 'Anos de Actividade', icon: 'bi bi-calendar-check' },
  { value: '+60', label: 'Colaboradores', icon: 'bi bi-people-fill' },
  { value: '+30', label: 'Países Servidos', icon: 'bi bi-globe2' },
  { value: '+1000', label: 'Clientes Atendidos', icon: 'bi bi-hand-thumbs-up-fill' },
  { value: '+15', label: 'Camiões Próprios', icon: 'bi bi-truck' },
  { value: '5000m²', label: 'de Armazéns', icon: 'bi bi-box-seam' },
]

const milestones = [
  { year: '2017', title: 'Fundação', desc: 'FMLider inicia actividade em Luanda com uma equipa pequena e grandes ambições.' },
  { year: '2018', title: 'Expansão', desc: 'Alargámos os serviços de desembaraço aduaneiro e transporte rodoviário para todo o país.' },
  { year: '2019', title: 'Frota Própria', desc: 'Aquisição dos primeiros camiões próprios, reforçando a autonomia operacional.' },
  { year: '2020', title: 'Armazéns', desc: 'Inauguração de armazéns próprios em Luanda com 5.000m² de área de armazenagem.' },
  { year: '2022', title: 'Reachstacker', desc: 'Investimento num Reachstacker Kalmar de 45 toneladas para contentores e cargas especiais.' },
  { year: '2024', title: 'Referência Nacional', desc: 'Mais de 60 colaboradores e presença em mais de 30 países. FMLider é referência em Angola.' },
]

const infraFeatures = [
  { icon: 'bi bi-box-seam', title: 'Armazéns Próprios', desc: '5.000m² de armazenagem em Luanda, Viana e Benguela.' },
  { icon: 'bi bi-truck', title: 'Frota Moderna', desc: '+15 camiões, contentores e equipamentos de última geração.' },
  { icon: 'bi bi-shield-lock', title: 'Segurança 24h', desc: 'CCTV, sistema de alarme e segurança 24/7 nos armazéns.' },
  { icon: 'bi bi-geo-alt', title: 'Cobertura Nacional', desc: 'Operações em todas as províncias de Angola.' },
]

const certifications = [
  { icon: 'bi bi-shield-check', title: 'Seguro All Risks', desc: 'Cobertura internacional para todas as cargas transportadas.' },
  { icon: 'bi bi-award', title: 'Certificação ISO', desc: 'Processos certificados de qualidade e segurança.' },
  { icon: 'bi bi-people', title: 'Equipas Certificadas', desc: 'Colaboradores formados e certificados nas suas áreas.' },
  { icon: 'bi bi-truck', title: 'Frota Rastreada', desc: 'GPS em tempo real em todos os veículos da frota.' },
]
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
  color: rgba(255,255,255,0.6);
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
  color: rgba(255, 255, 255, 0.7);
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
