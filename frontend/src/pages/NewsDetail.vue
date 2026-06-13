<template>
  <div class="news-detail-page">
    <!-- Hero -->
    <section class="nd-hero" :style="{ backgroundImage: `linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(${article.image})` }">
      <div class="container position-relative">
        <router-link to="/noticias" class="nd-back">
          <i class="bi bi-arrow-left"></i> Voltar às Notícias
        </router-link>
        <div class="nd-hero-content">
          <span class="nd-category">{{ article.category }}</span>
          <h1>{{ article.title }}</h1>
          <div class="nd-meta">
            <span><i class="bi bi-calendar3"></i> {{ formatDate(article.date) }}</span>
            <span><i class="bi bi-person"></i> FMLider</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Content -->
    <section class="nd-content fml-section">
      <div class="container">
        <div class="row g-5">
          <div class="col-lg-8">
            <article class="nd-article">
              <div class="nd-article-body" v-html="article.content"></div>
            </article>

            <!-- Share -->
            <div class="nd-share">
              <h6><i class="bi bi-share"></i> Partilhar</h6>
              <div class="nd-share-buttons">
                <a href="#" class="share-btn facebook"><i class="bi bi-facebook"></i></a>
                <a href="#" class="share-btn twitter"><i class="bi bi-twitter-x"></i></a>
                <a href="#" class="share-btn linkedin"><i class="bi bi-linkedin"></i></a>
                <a href="#" class="share-btn whatsapp"><i class="bi bi-whatsapp"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="nd-sidebar">
              <!-- Recent -->
              <div class="sidebar-box">
                <h5><i class="bi bi-clock-history"></i> Notícias Recentes</h5>
                <div class="sidebar-recent" v-for="item in recentNews" :key="item.id" @click="goToDetail(item.slug)">
                  <img :src="item.image" :alt="item.title">
                  <div>
                    <small class="text-muted">{{ formatDate(item.date) }}</small>
                    <h6>{{ item.title }}</h6>
                  </div>
                </div>
              </div>

              <!-- CTA -->
              <div class="sidebar-cta">
                <i class="bi bi-envelope-paper"></i>
                <h5>Contacte-nos</h5>
                <p>Precisa de mais informações? Fale connosco.</p>
                <router-link to="/contacto" class="btn btn-light btn-sm w-100">Contactar</router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { supabase } from '@/lib/supabase'

const route = useRoute()
const router = useRouter()
const article = ref({})
const allNews = ref([])

const fallbackNews = [
  {
    title: 'FMLider Investe em Novo Reachstacker Kalmar de 45 Toneladas',
    slug: 'investimento-reachstacker',
    image: '/assets/img/resachstacker/resachstacker2.jpeg',
    date: '2024-11-15',
    category: 'Investimentos',
    content: `
      <p class="nd-lead">A FMLider reforçou a sua capacidade operacional com a aquisição de um novo Reachstacker Kalmar de 45 toneladas.</p>
      <h3>Um Investimento Estratégico</h3>
      <p>A FMLider anunciou hoje a aquisição de um novo Reachstacker Kalmar de 45 toneladas, reforçando significativamente a sua capacidade de manuseamento de contentores e cargas especiais. Este investimento representa um marco importante na história da empresa.</p>
      <p>O novo equipamento permite-nos realizar até <strong>30 operações por hora</strong>, elevando significativamente a produtividade no Porto de Luanda. Com esta aquisição, a FMLider consolida a sua posição como referência em logística em Angola.</p>
      <h3>Especificações Técnicas</h3>
      <ul>
        <li>Capacidade de elevação: 45 toneladas</li>
        <li>Manuseamento de contentores 20" e 40"</li>
        <li>Stack de até 4 contentores</li>
        <li>Operadores certificados pela Kalmar</li>
        <li>Manutenção preventiva garantida</li>
      </ul>
      <h3>Impacto na Operação</h3>
      <p>Com o novo Reachstacker, a FMLider espera aumentar a sua capacidade de processamento em <strong>40%</strong>, permitindo servir mais clientes e reduzir tempos de espera. O investimento faz parte de um plano de expansão mais amplo que inclui a aquisição de novos camiões e a abertura de novos armazéns.</p>
    `,
  },
  {
    title: 'FMLider Alarga Cobertura para 30 Países na Região SADC',
    slug: 'expansao-sadc',
    image: '/assets/img/construcao2020/image3.jpeg',
    date: '2024-10-20',
    category: 'Expansão',
    content: `
      <p class="nd-lead">A FMLider agora oferece serviços logísticos completos em mais de 30 países.</p>
      <h3>Expansão Internacional</h3>
      <p>Com uma estratégia de expansão agressiva, a FMLider alargou a sua cobertura internacional para mais de 30 países. A empresa agora oferece serviços completos de logística, transporte e transitário em toda a região SADC.</p>
      <p>Os mercados prioritários incluem África do Sul, Moçambique, Zâmbia, Zimbabwe e Namíbia, com perspectivas de expansão para outros países do continente.</p>
      <h3>Países Cobertos</h3>
      <ul>
        <li>África do Sul</li>
        <li>Moçambique</li>
        <li>Zâmbia</li>
        <li>Zimbabwe</li>
        <li>Namíbia</li>
        <li>Botswana</li>
        <li>E mais de 24 países</li>
      </ul>
    `,
  },
  {
    title: 'Novo Armazém em Viana com 2.000m² de Área',
    slug: 'novo-armazem-viana',
    image: '/assets/img/servico/service-storage.jpg',
    date: '2024-09-10',
    category: 'Infraestrutura',
    content: `
      <p class="nd-lead">A FMLider inaugurou um novo armazém em Viana com 2.000m² de área de armazenagem.</p>
      <h3>Infraestrutura Moderna</h3>
      <p>O novo armazém conta com as mais modernas tecnologias de segurança e gestão, incluindo sistema de CCTV 24/7, inventário digital em tempo real e condições ideais para armazenagem de mercadorias de alto valor.</p>
      <h3>Características</h3>
      <ul>
        <li>2.000m² de área de armazenagem</li>
        <li>CCTV e segurança 24/7</li>
        <li>Sistema de inventário digital</li>
        <li>Controlo de temperatura e humidade</li>
        <li>Acesso directo para contentores</li>
      </ul>
    `,
  },
  {
    title: 'Parceria com Linhas Marítimas Internacionais',
    slug: 'parceria-maritima',
    image: '/assets/img/servico/Logística Marítima-1.jpg',
    date: '2024-08-05',
    category: 'Parcerias',
    content: `
      <p class="nd-lead">A FMLider firmou parcerias com as principais linhas marítimas internacionais.</p>
      <h3>Rotas Competitivas</h3>
      <p>Estas parcerias garantem aos nossos clientes condições competitivas e rotas optimizadas para destinos na Europa, América e Ásia. A FMLider agora oferece serviços completos de logística marítima com as melhores condições do mercado.</p>
    `,
  },
  {
    title: 'Certificação ISO 9001:2015 Renovada',
    slug: 'certificacao-iso',
    image: '/assets/img/pessoal/partner2.png',
    date: '2024-07-01',
    category: 'Qualidade',
    content: `
      <p class="nd-lead">A FMLider renovou com sucesso a sua certificação ISO 9001:2015.</p>
      <h3>Compromisso com a Qualidade</h3>
      <p>Após auditoria realizada por entidade certificadora internacional, a FMLider demonstrou o cumprimento rigoroso de todos os requisitos da norma ISO 9001:2015. Esta certificação confirma que os nossos processos cumprem os mais altos padrões de qualidade e segurança.</p>
    `,
  },
  {
    title: 'Campanha de Segurança Rodoviária',
    slug: 'campanha-seguranca',
    image: '/assets/img/construcao2020/image5.jpeg',
    date: '2024-06-15',
    category: 'Segurança',
    content: `
      <p class="nd-lead">A FMLider lançou uma campanha de segurança rodoviária para todos os seus colaboradores.</p>
      <h3>Segurança em Primeiro Lugar</h3>
      <p>No âmbito do Dia Mundial da Segurança Rodoviária, a FMLider lançou uma campanha de sensibilização para todos os seus colaboradores e condutores. A campanha inclui formações, palestras e distribuição de materiais educativos.</p>
    `,
  },
]

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('pt-PT', { year: 'numeric', month: 'long', day: 'numeric' })
}

const goToDetail = (slug) => {
  router.push(`/noticias/${slug}`)
}

const loadArticle = async () => {
  const slug = route.params.slug
  try {
    const { data, error } = await supabase.from('news').select('*')
    if (!error && data?.length) {
      allNews.value = data.map(n => ({
        id: n.id,
        title: n.title,
        slug: n.slug,
        image: n.image ? (n.image.startsWith('/') ? n.image : '/assets/img/' + n.image) : '/assets/img/construcao2020/image1.jpeg',
        date: n.published_at || n.created_at,
        category: n.category || 'Geral',
        content: n.content || '<p>Sem conteúdo disponível.</p>',
      }))
    } else {
      allNews.value = fallbackNews
    }
  } catch {
    allNews.value = fallbackNews
  }
  const found = allNews.value.find(n => n.slug === slug)
  article.value = found || fallbackNews[0]
}

const recentNews = computed(() => allNews.value.filter(n => n.slug !== route.params.slug).slice(0, 4))

watch(() => route.params.slug, loadArticle)
onMounted(loadArticle)
</script>

<style scoped>
/* HERO */
.nd-hero {
  position: relative;
  min-height: 45vh;
  display: flex;
  align-items: flex-end;
  background-size: cover;
  background-position: center;
  padding: 3rem 0;
}
.nd-back {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: rgba(255,255,255,0.8);
  text-decoration: none;
  font-size: 0.9rem;
  margin-bottom: 2rem;
  transition: color 0.2s;
}
.nd-back:hover { color: var(--fml-gold, #f59e0b); }
.nd-hero-content {
  position: relative;
  color: #fff;
}
.nd-category {
  display: inline-block;
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  padding: 0.3rem 1rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 1.25rem;
}
.nd-hero-content h1 {
  font-size: 2.5rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 1rem;
  line-height: 1.15;
  max-width: 800px;
}
.nd-meta {
  display: flex;
  gap: 1.5rem;
}
.nd-meta span {
  font-size: 0.9rem;
  color: rgba(255,255,255,0.7);
  display: flex;
  align-items: center;
  gap: 0.4rem;
}
.nd-meta i {
  color: var(--fml-gold, #f59e0b);
}

/* ARTICLE */
.nd-article {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 3rem;
}
.nd-article-body :deep(.nd-lead) {
  font-size: 1.2rem;
  color: #475569;
  line-height: 1.7;
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid #e5e7eb;
}
.nd-article-body :deep(h3) {
  font-size: 1.3rem;
  font-weight: 700;
  color: #0f172a;
  margin: 2rem 0 1rem;
}
.nd-article-body :deep(p) {
  color: #475569;
  line-height: 1.8;
  margin-bottom: 1rem;
}
.nd-article-body :deep(ul) {
  list-style: none;
  padding: 0;
  margin: 1rem 0;
}
.nd-article-body :deep(li) {
  padding: 0.4rem 0;
  color: #334155;
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
}
.nd-article-body :deep(li::before) {
  content: '✓';
  color: var(--fml-gold, #f59e0b);
  font-weight: 700;
  flex-shrink: 0;
}

/* SHARE */
.nd-share {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}
.nd-share h6 {
  font-weight: 600;
  color: #0f172a;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}
.nd-share-buttons {
  display: flex;
  gap: 0.5rem;
}
.share-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 1rem;
  text-decoration: none;
  transition: transform 0.2s;
}
.share-btn:hover { transform: scale(1.1); }
.share-btn.facebook { background: #1877f2; }
.share-btn.twitter { background: #000; }
.share-btn.linkedin { background: #0a66c2; }
.share-btn.whatsapp { background: #25d366; }

/* SIDEBAR */
.nd-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
}
.sidebar-box {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  padding: 1.5rem;
}
.sidebar-box h5 {
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1rem;
}
.sidebar-box h5 i {
  color: var(--fml-gold, #f59e0b);
}
.sidebar-recent {
  display: flex;
  gap: 0.75rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  transition: all 0.2s;
}
.sidebar-recent:last-child { border-bottom: none; }
.sidebar-recent:hover { background: #f8fafc; border-radius: 8px; padding-left: 0.5rem; }
.sidebar-recent img {
  width: 64px;
  height: 64px;
  border-radius: 10px;
  object-fit: cover;
  flex-shrink: 0;
}
.sidebar-recent h6 {
  font-size: 0.85rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0.25rem 0 0;
  line-height: 1.35;
}

.sidebar-cta {
  background: linear-gradient(135deg, var(--fml-navy, #0f172a), #1e3a5f);
  border-radius: 14px;
  padding: 1.75rem;
  text-align: center;
  color: #fff;
}
.sidebar-cta i {
  font-size: 2rem;
  color: var(--fml-gold, #f59e0b);
  margin-bottom: 0.75rem;
}
.sidebar-cta h5 { font-weight: 700; margin-bottom: 0.5rem; }
.sidebar-cta p { font-size: 0.85rem; color: rgba(255,255,255,0.7); margin-bottom: 1rem; }

@media (max-width: 767.98px) {
  .nd-hero { min-height: 35vh; }
  .nd-hero-content h1 { font-size: 1.8rem; }
  .nd-article { padding: 1.5rem; }
}
</style>
