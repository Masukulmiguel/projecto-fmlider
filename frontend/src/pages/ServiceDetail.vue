<template>
  <div class="service-detail-page">
    <!-- Hero -->
    <section class="sd-hero" :style="{ backgroundImage: `linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(${service.image})` }">
      <div class="container position-relative">
        <router-link to="/servicos" class="sd-back">
          <i class="bi bi-arrow-left"></i> Voltar aos Serviços
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
                <h4>Interessado neste serviço?</h4>
                <p>Fale connosco para uma proposta personalizada.</p>
                <router-link to="/contacto" class="btn btn-primary w-100 mb-2">
                  <i class="bi bi-envelope me-2"></i> Solicitar Cotação
                </router-link>
                <a href="tel:+244935141747" class="btn btn-outline-primary w-100">
                  <i class="bi bi-telephone me-2"></i> Ligar Agora
                </a>
              </div>

              <div class="sd-features-box">
                <h5>Porquê a FMLider</h5>
                <ul>
                  <li><i class="bi bi-shield-check"></i> Seguro All Risks</li>
                  <li><i class="bi bi-speedometer"></i> Rastreamento GPS</li>
                  <li><i class="bi bi-clock"></i> Entrega pontual</li>
                  <li><i class="bi bi-people"></i> Equipas certificadas</li>
                  <li><i class="bi bi-globe"></i> Cobertura global</li>
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

const route = useRoute()

const allServices = {
  'desembaraco-aduaneiro': {
    title: 'Desembaraço Aduaneiro',
    subtitle: 'Especialistas em processos aduaneiros em Angola. Agilizamos a sua importação e exportação.',
    icon: 'bi bi-stamp',
    image: '/assets/img/servico/Desembaraço Aduaneiro.jpeg',
    description: 'A FMLider é especialista em desembaraço aduaneiro, oferecendo serviços completos de importação e exportação junto à Alfândega de Angola. A nossa equipa de despachantes certificados garante o cumprimento de todas as formalidades legais com rapidez e rigor.',
    sections: [
      {
        title: 'O Que Fazemos',
        icon: 'bi bi-gear',
        text: 'Gerimos todo o processo aduaneiro, desde a preparação da documentação até à libertação da carga. Trabalhamos com todos os tipos de mercadoria e modalidades de transporte.',
        list: [
          'Desalfandegamento de importação e exportação',
          'Elaboração de Declaração Única (DU)',
          'Obtenção de licenças e autorizações especiais',
          'Classificação fiscal e tributária',
          'Acompanhamento junto à Alfândega',
          'Resolução de pendências aduaneiras',
        ]
      },
      {
        title: 'Vantagens',
        icon: 'bi bi-trophy',
        text: 'Com a FMLider, o seu processo aduaneiro é tratado com profissionalismo e transparência.',
        list: [
          'Redução de tempos de espera na Alfândega',
          'Evitamento de multas e penalizações',
          'Consultoria sobre regulamentação actualizada',
          'Relatórios detalhados de cada operação',
        ]
      }
    ]
  },
  'transportes': {
    title: 'Transportes',
    subtitle: 'Frota própria e rastreamento GPS. Transporte seguro em todo o território nacional e internacional.',
    icon: 'bi bi-truck',
    image: '/assets/img/servico/Transportes.jpg',
    description: 'A FMLider dispõe de frota própria de camiões e equipamentos para transporte de cargas gerais e especiais. Com rastreamento GPS em tempo real, oferecemos segurança e visibilidade total durante todo o percurso.',
    sections: [
      {
        title: 'Modalidades de Transporte',
        icon: 'bi bi-box-seam',
        text: 'Oferecemos diferentes modalidades de transporte para se adaptar às necessidades de cada cliente.',
        list: [
          'Transporte rodoviário — cobertura nacional',
          'Transporte marítimo — cargas internacional',
          'Transporte aéreo — urgências e cargas valiosas',
          'Contentores 20" e 40"',
          'Carga geral e project cargo',
          'Carga perigosa e temperatura controlada',
        ]
      },
      {
        title: 'Rastreamento e Segurança',
        icon: 'bi bi-geo-alt',
        text: 'Todos os nossos veículos estão equipados com GPS, permitindo acompanhamento em tempo real.',
        list: [
          'Rastreamento GPS 24/7',
          'Notificações automáticas de estado',
          'Seguro All Risks em todas as cargas',
          'Condutores certificados e experientes',
        ]
      }
    ]
  },
  'armazenagem': {
    title: 'Armazenagem',
    subtitle: '5.000m² de armazéns próprios em Luanda, Viana e Benguela. Segurança e gestão profissional.',
    icon: 'bi bi-box-seam',
    image: '/assets/img/servico/service-storage.jpg',
    description: 'A FMLider disponibiliza 5.000m² de armazéns próprios estrategicamente localizados em Luanda, Viana e Benguela. Os nossos armazéns são equipados com sistemas de segurança de última geração e condições ideais para todos os tipos de mercadoria.',
    sections: [
      {
        title: 'Infraestrutura',
        icon: 'bi bi-building',
        text: 'Os nossos armazéns são projetados para garantir a segurança e integridade da sua mercadoria.',
        list: [
          '5.000m² de área de armazenagem',
          'CCTV e segurança 24/7',
          'Sistema de inventário digital',
          'Controlo de temperatura e humidade',
          'Acesso para contentores e camiões',
          'Estantes industriais para todos os volumes',
        ]
      },
      {
        title: 'Serviços de Valor Acrescentado',
        icon: 'bi bi-plus-circle',
        text: 'Para além da armazenagem, oferecemos serviços complementares.',
        list: [
          'Cross-docking e transhipment',
          'Picking e packing',
          'Etiquetagem e reembalagem',
          'Inventários periódicos',
          'Gestão de stock em tempo real',
        ]
      }
    ]
  },
  'door-to-door': {
    title: 'Door To Door',
    subtitle: 'Solução completa desde a origem internacional até à entrega final em Angola.',
    icon: 'bi bi-house-door',
    image: '/assets/img/servico/service-door.jpg',
    description: 'O serviço Door to Door da FMLider é a solução mais prática e completa. Cuidamos de todo o processo logístico — desde a recolha na origem internacional até à entrega no destino final em Angola — para que o cliente não se preocupe com nada.',
    sections: [
      {
        title: 'O Incluído',
        icon: 'bi bi-list-check',
        text: 'Um único ponto de contacto para toda a operação logística.',
        list: [
          'Recolha na origem internacional',
          'Transporte marítimo ou aéreo',
          'Desembaraço aduaneiro em Angola',
          'Transporte terrestre até ao destino',
          'Seguro da carga internacional',
          'Entrega com prova de entrega (POD)',
        ]
      },
      {
        title: 'Vantagens',
        icon: 'bi bi-star',
        text: 'Simplicidade e segurança em cada etapa.',
        list: [
          'Um único interlocutor para toda a operação',
          'Prazos definidos e cumpridos',
          'Custo total transparente',
          'Rastreamento de ponta a ponta',
        ]
      }
    ]
  },
  'consultoria': {
    title: 'Consultoria Logística',
    subtitle: 'Optimizamos a sua cadeia logística para reduzir custos e aumentar eficiência.',
    icon: 'bi bi-people',
    image: '/assets/img/servico/service1.jpg',
    description: 'A FMLider oferece serviços de consultoria logística para empresas que pretendem optimizar os seus processos. Analisamos a cadeia logística completa e implementamos melhorias concretas que resultam em redução de custos e aumento da eficiência.',
    sections: [
      {
        title: 'Áreas de Actuação',
        icon: 'bi bi-lightbulb',
        text: 'Trabalhamos nas principais áreas da cadeia logística.',
        list: [
          'Análise completa da cadeia logística',
          'Optimização de rotas e transportes',
          'Gestão de stock e inventário',
          'Planeamento de distribuição',
          'Selección de fornecedores logísticos',
          'Implementação de sistemas WMS',
        ]
      },
      {
        title: 'Resultados',
        icon: 'bi bi-graph-up',
        text: 'Os nossos clientes registam melhorias significativas.',
        list: [
          'Redução de 15-30% nos custos logísticos',
          'Melhoria nos tempos de entrega',
          'Maior visibilidade da cadeia',
          'Relatórios e indicadores KPI',
        ]
      }
    ]
  },
  'logistica-maritima': {
    title: 'Logística Marítima',
    subtitle: 'Operações completas no Porto de Luanda e outros portos de Angola.',
    icon: 'bi bi-water',
    image: '/assets/img/servico/Logística Marítima-1.jpg',
    description: 'A FMLider realiza operações completas de logística marítima no Porto de Luanda e noutros portos de Angola. Trabalhamos com as principais linhas marítimas internacionais, oferecendo condições competitivas para todos os tipos de carga.',
    sections: [
      {
        title: 'Serviços Marítimos',
        icon: 'bi bi-ship',
        text: 'Soluções completas para cargas marítimas.',
        list: [
          'Operações no Porto de Luanda',
          'Carga contentorizada 20" e 40"',
          'FCL (Full Container Load)',
          'LCL (Less than Container Load)',
          'Project cargo e cargas especiais',
          'Carga frigorífica',
        ]
      },
      {
        title: 'Parcerias',
        icon: 'bi bi-handshake',
        text: 'Trabalhamos com as melhores linhas marítimas.',
        list: [
          'Parceria com linhas internacionais',
          'Rotas para Europa, América e Ásia',
          'Condições especiais para volumes regulares',
          'Tracking marítimo em tempo real',
        ]
      }
    ]
  },
}

const slug = computed(() => route.params.slug)
const service = computed(() => allServices[slug.value] || allServices['desembaraco-aduaneiro'])
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
