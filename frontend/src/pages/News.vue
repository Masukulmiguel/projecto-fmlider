<template>
  <div class="news-page">
    <!-- Hero -->
    <section class="news-hero">
      <div class="news-hero-bg" style="background-image: linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(/assets/img/construcao2020/image3.jpeg);"></div>
      <div class="container position-relative">
        <div class="news-hero-content">
          <span class="fml-eyebrow">Novidades</span>
          <h1 class="news-hero-title">Notícias & Actualidades</h1>
          <p class="news-hero-subtitle">
            Fique por dentro das últimas novidades da FMLider e do sector da logística em Angola.
          </p>
        </div>
      </div>
    </section>

    <!-- Content -->
    <section class="news-content fml-section">
      <div class="container">
        <div class="row g-5">
          <!-- Main -->
          <div class="col-lg-8">
            <!-- Featured -->
            <div v-if="featured" class="news-featured" @click="goToDetail(featured.slug)">
              <div class="news-featured-image">
                <img :src="featured.image" :alt="featured.title">
                <span class="news-featured-badge">Destaque</span>
              </div>
              <div class="news-featured-body">
                <div class="news-meta">
                  <span><i class="bi bi-calendar3"></i> {{ formatDate(featured.date) }}</span>
                  <span><i class="bi bi-tag"></i> {{ featured.category }}</span>
                </div>
                <h2>{{ featured.title }}</h2>
                <p>{{ featured.excerpt }}</p>
                <span class="news-read-more">Ler mais <i class="bi bi-arrow-right"></i></span>
              </div>
            </div>

            <!-- Grid -->
            <div class="news-grid">
              <div class="news-card" v-for="item in paginatedNews" :key="item.id" @click="goToDetail(item.slug)">
                <div class="news-card-image">
                  <img :src="item.image" :alt="item.title">
                  <span class="news-card-category">{{ item.category }}</span>
                </div>
                <div class="news-card-body">
                  <div class="news-meta">
                    <span><i class="bi bi-calendar3"></i> {{ formatDate(item.date) }}</span>
                  </div>
                  <h4>{{ item.title }}</h4>
                  <p>{{ item.excerpt }}</p>
                  <span class="news-read-more">Ler mais <i class="bi bi-arrow-right"></i></span>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div class="news-pagination" v-if="totalPages > 1">
              <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">
                <i class="bi bi-chevron-left"></i>
              </button>
              <button
                v-for="page in totalPages"
                :key="page"
                class="page-btn"
                :class="{ active: currentPage === page }"
                @click="currentPage = page"
              >{{ page }}</button>
              <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="col-lg-4">
            <div class="news-sidebar">
              <!-- Categories -->
              <div class="sidebar-box">
                <h5><i class="bi bi-folder2"></i> Categorias</h5>
                <ul class="sidebar-categories">
                  <li v-for="cat in categories" :key="cat.name" :class="{ active: selectedCategory === cat.name }" @click="filterByCategory(cat.name)">
                    <span>{{ cat.name }}</span>
                    <span class="cat-count">{{ cat.count }}</span>
                  </li>
                </ul>
              </div>

              <!-- Recent -->
              <div class="sidebar-box">
                <h5><i class="bi bi-clock-history"></i> Mais Recentes</h5>
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
                <h5>Receba Novidades</h5>
                <p>Subscreva para ficar a par de todas as novidades.</p>
                <router-link to="/contacto" class="btn btn-light btn-sm w-100">Subscrever</router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const allNews = ref([])
const loading = ref(true)
const currentPage = ref(1)
const selectedCategory = ref('Todos')
const perPage = 6

const fetchNews = async () => {
  loading.value = true
  try {
    const r = await axios.get('/api/news')
    if (r.data.success && r.data.news?.length) {
      allNews.value = r.data.news.map(n => ({
        id: n.id,
        title: n.title,
        slug: n.slug,
        image: n.image ? (n.image.startsWith('/') ? n.image : '/assets/img/' + n.image) : '/assets/img/construcao2020/image1.jpeg',
        date: n.published_at || n.created_at,
        category: n.category || 'Geral',
        excerpt: n.description || n.content?.substring(0, 150) + '...',
        content: n.content,
      }))
    } else {
      allNews.value = fallbackNews
    }
  } catch {
    allNews.value = fallbackNews
  } finally {
    loading.value = false
  }
}

const fallbackNews = [
  {
    id: 1,
    title: 'FMLider Investe em Novo Reachstacker Kalmar de 45 Toneladas',
    slug: 'investimento-reachstacker',
    image: '/assets/img/resachstacker/resachstacker1.jpeg',
    date: '2024-11-15',
    category: 'Investimentos',
    excerpt: 'A FMLider reforçou a sua capacidade operacional com a aquisição de um novo Reachstacker Kalmar de 45 toneladas, permitindo operações mais rápidas e seguras no manuseamento de contentores.',
    content: 'A FMLider anunciou hoje a aquisição de um novo Reachstacker Kalmar de 45 toneladas, reforçando significativamente a sua capacidade de manuseamento de contentores e cargas especiais. Este investimento, realizado em 2022, representa um marco importante na história da empresa e demonstra o nosso compromisso com a excelência operacional. O novo equipamento permite-nos realizar até 30 operações por hora, elevando a produtividade do Porto de Luanda. Com operadores certificados pela Kalmar, garantimos a segurança e eficiência em todas as operações.',
  },
  {
    id: 2,
    title: 'FMLider Alarga Cobertura para 30 Países na Região SADC',
    slug: 'expansao-sadc',
    image: '/assets/img/construcao2020/image2.jpeg',
    date: '2024-10-20',
    category: 'Expansão',
    excerpt: 'A FMLider agora oferece serviços logísticos completos em mais de 30 países, com foco na região SADC e em mercados estratégicos na África Austral.',
    content: 'Com uma estratégia de expansão agressiva, a FMLider alargou a sua cobertura internacional para mais de 30 países. A empresa agora oferece serviços completos de logística, transporte e transitário em toda a região SADC, incluindo África do Sul, Moçambique, Zâmbia, Zimbabwe e Namíbia. Esta expansão permite-nos servir melhor os nossos clientes que operam em mercados internacionais.',
  },
  {
    id: 3,
    title: 'Novo Armazém em Viana com 2.000m² de Área',
    slug: 'novo-armazem-viana',
    image: '/assets/img/servico/service-storage.jpg',
    date: '2024-09-10',
    category: 'Infraestrutura',
    excerpt: 'A FMLider inaugurou um novo armazém em Viana com 2.000m² de área, equipado com sistema de CCTV 24h e inventário digital.',
    content: 'A FMLider expandiu a sua rede de armazéns com a inauguração de uma nova instalação em Viana, com 2.000m² de área de armazenagem. O novo armazém conta com sistema de CCTV 24/7, inventário digital em tempo real e condições ideais para armazenagem de mercadorias de alto valor.',
  },
  {
    id: 4,
    title: 'Parceria com Linhas Marítimas Internacionais',
    slug: 'parceria-maritima',
    image: '/assets/img/servico/Logística Marítima-1.jpg',
    date: '2024-08-05',
    category: 'Parcerias',
    excerpt: 'A FMLider firmou parcerias com as principais linhas marítimas internacionais, garantindo rotas competitivas para Europa, América e Ásia.',
    content: 'A FMLider anunciou a firma de parcerias estratégicas com as principais linhas marítimas internacionais. Estas parcerias garantem aos nossos clientes condições competitivas e rotas optimizadas para destinos na Europa, América e Ásia.',
  },
  {
    id: 5,
    title: 'Certificação ISO 9001:2015 Renovada',
    slug: 'certificacao-iso',
    image: '/assets/img/pessoal/partner1.webp',
    date: '2024-07-01',
    category: 'Qualidade',
    excerpt: 'A FMLider renovou com sucesso a sua certificação ISO 9001:2015, confirmando o compromisso com a qualidade e excelência nos serviços.',
    content: 'A FMLider renovou com sucesso a sua certificação ISO 9001:2015, após auditoria realizada por entidade certificadora internacional. Esta certificação confirma que os nossos processos cumprem os mais altos padrões de qualidade e segurança.',
  },
  {
    id: 6,
    title: 'Campanha de Segurança Rodoviária',
    slug: 'campanha-seguranca',
    image: '/assets/img/construcao2020/image5.jpeg',
    date: '2024-06-15',
    category: 'Segurança',
    excerpt: 'A FMLider lançou uma campanha de segurança rodoviária para todos os seus colaboradores e condutores, reforçando o compromisso com a segurança.',
    content: 'No âmbito do Dia Mundial da Segurança Rodoviária, a FMLider lançou uma campanha de sensibilização para todos os seus colaboradores e condutores. A campanha inclui formações, palestras e distribuição de materiais educativos.',
  },
]

const filteredNews = computed(() => {
  if (selectedCategory.value === 'Todos') return allNews.value
  return allNews.value.filter(n => n.category === selectedCategory.value)
})

const featured = computed(() => filteredNews.value[0] || null)
const regularNews = computed(() => filteredNews.value.slice(1))
const totalPages = computed(() => Math.ceil(regularNews.value.length / perPage))
const paginatedNews = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return regularNews.value.slice(start, start + perPage)
})
const recentNews = computed(() => allNews.value.slice(0, 4))

const categories = computed(() => {
  const cats = { 'Todos': allNews.value.length }
  allNews.value.forEach(n => {
    cats[n.category] = (cats[n.category] || 0) + 1
  })
  return Object.entries(cats).map(([name, count]) => ({ name, count }))
})

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('pt-PT', { year: 'numeric', month: 'long', day: 'numeric' })
}

const filterByCategory = (cat) => {
  selectedCategory.value = cat
  currentPage.value = 1
}

const goToDetail = (slug) => {
  router.push(`/noticias/${slug}`)
}

onMounted(fetchNews)
</script>

<style scoped>
/* HERO */
.news-hero {
  position: relative;
  height: 45vh;
  min-height: 340px;
  display: flex;
  align-items: center;
  overflow: hidden;
  background: var(--fml-navy, #0f172a);
}
.news-hero-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
}
.news-hero-content {
  position: relative;
  color: #fff;
  max-width: 700px;
}
.news-hero-title {
  font-size: 3.2rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 1rem;
  letter-spacing: -0.03em;
}
.news-hero-subtitle {
  font-size: 1.15rem;
  line-height: 1.7;
  opacity: 0.9;
}

/* FEATURED */
.news-featured {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  margin-bottom: 2.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
}
.news-featured:hover {
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.1);
  transform: translateY(-4px);
}
.news-featured-image {
  position: relative;
  height: 360px;
  overflow: hidden;
}
.news-featured-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}
.news-featured:hover .news-featured-image img {
  transform: scale(1.05);
}
.news-featured-badge {
  position: absolute;
  top: 1.25rem;
  left: 1.25rem;
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  padding: 0.35rem 1rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.news-featured-body {
  padding: 2rem;
}
.news-featured-body h2 {
  font-size: 1.6rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.75rem;
}
.news-featured-body p {
  color: #64748b;
  line-height: 1.7;
  margin-bottom: 1rem;
}

/* META */
.news-meta {
  display: flex;
  gap: 1.25rem;
  margin-bottom: 0.75rem;
}
.news-meta span {
  font-size: 0.8rem;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: 0.35rem;
}
.news-meta i {
  color: var(--fml-gold, #f59e0b);
}

/* READ MORE */
.news-read-more {
  color: var(--fml-gold, #f59e0b);
  font-weight: 600;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  transition: gap 0.2s;
}
.news-featured:hover .news-read-more,
.news-card:hover .news-read-more {
  gap: 0.7rem;
}

/* GRID */
.news-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.75rem;
}
.news-card {
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  cursor: pointer;
  transition: all 0.3s ease;
}
.news-card:hover {
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
  transform: translateY(-4px);
  border-color: var(--fml-gold, #f59e0b);
}
.news-card-image {
  position: relative;
  height: 200px;
  overflow: hidden;
}
.news-card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}
.news-card:hover .news-card-image img {
  transform: scale(1.08);
}
.news-card-category {
  position: absolute;
  top: 0.75rem;
  left: 0.75rem;
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(8px);
  color: #fff;
  padding: 0.25rem 0.75rem;
  border-radius: 16px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.news-card-body {
  padding: 1.5rem;
}
.news-card-body h4 {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.5rem;
  line-height: 1.35;
}
.news-card-body p {
  color: #64748b;
  font-size: 0.88rem;
  line-height: 1.6;
  margin-bottom: 0.75rem;
}

/* PAGINATION */
.news-pagination {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 3rem;
}
.page-btn {
  width: 40px;
  height: 40px;
  border: 1px solid #e2e8f0;
  background: #fff;
  border-radius: 10px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}
.page-btn:hover:not(:disabled) {
  border-color: var(--fml-gold, #f59e0b);
  color: var(--fml-gold, #f59e0b);
}
.page-btn.active {
  background: var(--fml-gold, #f59e0b);
  border-color: var(--fml-gold, #f59e0b);
  color: var(--fml-900, #1e293b);
}
.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* SIDEBAR */
.news-sidebar {
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

.sidebar-categories {
  list-style: none;
  padding: 0;
  margin: 0;
}
.sidebar-categories li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.65rem 0.75rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
  color: #334155;
}
.sidebar-categories li:hover {
  background: #f8fafc;
}
.sidebar-categories li.active {
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  font-weight: 600;
}
.cat-count {
  background: rgba(0,0,0,0.08);
  padding: 0.15rem 0.55rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}
.sidebar-categories li.active .cat-count {
  background: rgba(0,0,0,0.15);
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
.sidebar-cta h5 {
  font-weight: 700;
  margin-bottom: 0.5rem;
}
.sidebar-cta p {
  font-size: 0.85rem;
  color: rgba(255,255,255,0.7);
  margin-bottom: 1rem;
}

@media (max-width: 991.98px) {
  .news-grid { grid-template-columns: 1fr; }
  .news-featured-image { height: 280px; }
}

@media (max-width: 767.98px) {
  .news-hero { height: 35vh; min-height: 280px; }
  .news-hero-title { font-size: 2rem; }
}
</style>
