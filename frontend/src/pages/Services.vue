<template>
  <div class="services-page">
    <!-- Hero -->
    <section class="srv-hero">
      <div class="srv-hero-bg" style="background-image: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(/assets/img/construcao2020/image4.jpeg);"></div>
      <div class="container position-relative">
        <div class="srv-hero-content">
          <span class="fml-eyebrow">O Que Fazemos</span>
          <h1 class="srv-hero-title">Serviços Integrados</h1>
          <p class="srv-hero-subtitle">
            Soluções completas de logística, transporte e transitário.
            Do desembaraço aduaneiro à entrega final — tudo num só lugar.
          </p>
        </div>
      </div>
    </section>

    <!-- Services Grid -->
    <section class="srv-grid-section fml-section">
      <div class="container">
        <div class="text-center mb-5">
          <span class="fml-eyebrow">Os Nossos Serviços</span>
          <h2 class="section-title">Soluções para Cada Necessidade</h2>
          <p class="section-subtitle text-muted">Oferecemos uma gama completa de serviços logísticos para empresas de todos os portes.</p>
        </div>

        <div class="srv-cards">
          <div class="srv-card" v-for="service in services" :key="service.id" :id="service.slug">
            <div class="srv-card-image">
              <img :src="service.image" :alt="service.title">
              <div class="srv-card-overlay">
                <span class="srv-card-num">{{ String(service.id).padStart(2, '0') }}</span>
              </div>
            </div>
            <div class="srv-card-body">
              <div class="srv-card-icon"><i :class="service.icon"></i></div>
              <h3>{{ service.title }}</h3>
              <p class="srv-card-desc">{{ service.description }}</p>
              <ul class="srv-card-features">
                <li v-for="feat in service.features" :key="feat"><i class="bi bi-check2-circle"></i> {{ feat }}</li>
              </ul>
              <router-link :to="`/contacto`" class="btn btn-outline-primary btn-sm mt-3">
                Solicitar Info <i class="bi bi-arrow-right ms-1"></i>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Process -->
    <section class="srv-process fml-section bg-fml-navy text-white">
      <div class="container">
        <div class="text-center mb-5">
          <span class="fml-eyebrow">Como Trabalhamos</span>
          <h2 class="section-title text-white">Do Pedido à Entrega</h2>
          <p class="text-white-50">Processo simples, rápido e transparente.</p>
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
          <div class="col-lg-6">
            <span class="fml-eyebrow">Porquê a FMLider</span>
            <h2 class="section-title">A Vantagem de Trabalhar Connosco</h2>
            <p class="text-muted mb-4">
              Não somos apenas um prestador de serviços — somos o seu parceiro logístico.
              Combinamos experiência, infraestrutura e compromisso para entregar resultados.
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
          <div class="col-lg-6">
            <div class="why-image">
              <img src="/assets/img/pessoal/partner1.webp" alt="FMLider Equipa" class="img-fluid rounded shadow-lg">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="srv-cta fml-section-gold">
      <div class="container text-center">
        <h2 class="display-5 fw-bold mb-3">Precisa de uma Solução Logística?</h2>
        <p class="lead mb-4 opacity-90">Fale com a nossa equipa e receba uma proposta personalizada em 24h.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <router-link to="/contacto" class="btn btn-dark btn-lg">
            <i class="bi bi-envelope me-2"></i> Contactar Agora
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
const services = [
  {
    id: 1,
    title: 'Desembaraço Aduaneiro',
    slug: 'desembaraco-aduaneiro',
    icon: 'bi bi-stamp',
    image: '/assets/img/servico/Desembaraço Aduaneiro.jpeg',
    description: 'Especialistas em processos de importação e exportação. Agilizamos o desalfandegamento da sua carga junto à Alfândega de Angola, garantindo cumprimento total da legislação aduaneira.',
    features: [
      'Desalfandegamento de importação e exportação',
      'Elaboração de Declaração Única (DU)',
      'Licenças e autorizações especiais',
      'Classificação fiscal e tributária',
      'Acompanhamento junto à Alfândega',
      'Consultoria aduaneira personalizada',
    ]
  },
  {
    id: 2,
    title: 'Transportes',
    slug: 'transportes',
    icon: 'bi bi-truck',
    image: '/assets/img/servico/Transportes.jpg',
    description: 'Frota própria de camiões e equipamentos para transporte rodoviário, marítimo e aéreo. Rastreamento GPS em tempo real e cobertura nacional e internacional.',
    features: [
      'Transporte rodoviário em todo o país',
      'Transporte marítimo e aéreo',
      'Contentores 20" e 40"',
      'Rastreamento GPS em tempo real',
      'Carga geral e especial',
      'Cobertura SADC e internacional',
    ]
  },
  {
    id: 3,
    title: 'Armazenagem',
    slug: 'armazenagem',
    icon: 'bi bi-box-seam',
    image: '/assets/img/servico/service-storage.jpg',
    description: '5.000m² de armazéns próprios em Luanda, Viana e Benguela. Espaços seguros com CCTV 24h, sistema de inventário e condições ideais para todos os tipos de mercadoria.',
    features: [
      '5.000m² de armazéns próprios',
      'CCTV e segurança 24/7',
      'Sistema de inventário digital',
      'Cross-docking e picking',
      'Armazenagem de contentores',
      'Controlo de temperatura e humidade',
    ]
  },
  {
    id: 4,
    title: 'Door To Door',
    slug: 'door-to-door',
    icon: 'bi bi-house-door',
    image: '/assets/img/servico/service-door.jpg',
    description: 'Solução completa desde a origem até ao destino final. Cuidamos de todo o processo logístico para que o cliente não se preocupe com nada.',
    features: [
      'Recolha na origem internacional',
      'Desembaraço aduaneiro',
      'Transporte até ao destino final',
      'Gestão documental completa',
      'Seguro da carga',
      'Entrega com prova de entrega',
    ]
  },
  {
    id: 5,
    title: 'Consultoria Logística',
    slug: 'consultoria',
    icon: 'bi bi-people',
    image: '/assets/img/servico/service1.jpg',
    description: 'Apoio especializado para optimizar a sua cadeia logística. Analisamos processos, identificamos melhorias e implementamos soluções que reduzem custos e aumentam eficiência.',
    features: [
      'Análise da cadeia logística',
      'Optimização de rotas e custos',
      'Gestão de stock e inventário',
      'Planeamento de rotas',
      'Relatórios e indicadores KPI',
      'Apoio na expansão internacional',
    ]
  },
  {
    id: 6,
    title: 'Logística Marítima',
    slug: 'logistica-maritima',
    icon: 'bi bi-water',
    image: '/assets/img/servico/Logística Marítima-1.jpg',
    description: 'Gestão completa de operações marítimas no Porto de Luanda e outros portos de Angola. Carga contentorizada e project cargo com as melhores condições.',
    features: [
      'Operações no Porto de Luanda',
      'Carga contentorizada 20" e 40"',
      'Project cargo e cargas pesadas',
      'LCL (Less than Container Load)',
      'FCL (Full Container Load)',
      'Liação com linhas marítimas internacionais',
    ]
  },
]

const processSteps = [
  { title: 'Contacto', desc: 'Solicite orçamento online, por telefone ou email.' },
  { title: 'Análise', desc: 'A nossa equipa analisa as necessidades e propõe a melhor solução.' },
  { title: 'Execução', desc: 'Executamos o serviço com rigor, transparência e prazo definido.' },
  { title: 'Entrega', desc: 'Entrega com documentação completa e acompanhamento até ao fim.' },
]

const whyFeatures = [
  { icon: 'bi bi-shield-check', title: 'Segurança Garantida', desc: 'Cobertura All Risks em todas as operações e armazéns com CCTV 24h.' },
  { icon: 'bi bi-speedometer', title: 'Rastreamento Tempo Real', desc: 'Saiba onde está a sua carga a qualquer momento com GPS integrado.' },
  { icon: 'bi bi-people', title: 'Equipas Especializadas', desc: 'Colaboradores certificados e com anos de experiência no sector.' },
  { icon: 'bi bi-graph-up-arrow', title: 'Custos Competitivos', desc: 'Relação preço-qualidade imbatível sem comprometer o serviço.' },
  { icon: 'bi bi-clock-history', title: 'Pontualidade', desc: 'Cumprimos prazos porque sabemos que o tempo do cliente vale dinheiro.' },
  { icon: 'bi bi-globe', title: 'Cobertura Global', desc: 'Presença em Angola e operações em mais de 30 países.' },
]
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

.srv-card-icon {
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-bottom: 1.25rem;
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
}

.srv-card-features {
  list-style: none;
  padding: 0;
  margin: 0;
  flex: 1;
}
.srv-card-features li {
  padding: 0.35rem 0;
  font-size: 0.85rem;
  color: #334155;
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
}
.srv-card-features i {
  color: var(--fml-gold, #f59e0b);
  margin-top: 0.2rem;
  flex-shrink: 0;
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
