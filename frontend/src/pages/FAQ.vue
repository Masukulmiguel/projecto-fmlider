<template>
  <div class="faq-page">
    <!-- Hero -->
    <section class="faq-hero">
      <div class="faq-hero-bg" :style="{ backgroundImage: `linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(${heroBg})` }"></div>
      <div class="container position-relative">
        <div class="faq-hero-content">
          <span class="fml-eyebrow">{{ t('faq.hero_eyebrow') || 'Ajuda' }}</span>
          <h1 class="faq-hero-title">{{ t('faq.hero_title') }}</h1>
          <p class="faq-hero-subtitle">{{ t('faq.hero_subtitle') }}</p>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section class="faq-stats">
      <div class="container">
        <div class="faq-stats-grid">
          <div class="faq-stat">
            <div class="faq-stat-icon"><i class="bi bi-chat-dots"></i></div>
            <div class="faq-stat-value">{{ faqs.length }}</div>
            <div class="faq-stat-label">{{ t('faq.stat_total') || 'Perguntas' }}</div>
          </div>
          <div class="faq-stat">
            <div class="faq-stat-icon"><i class="bi bi-folder2"></i></div>
            <div class="faq-stat-value">{{ categories.length - 1 }}</div>
            <div class="faq-stat-label">{{ t('faq.stat_categories') || 'Categorias' }}</div>
          </div>
          <div class="faq-stat">
            <div class="faq-stat-icon"><i class="bi bi-search"></i></div>
            <div class="faq-stat-value">{{ filteredFaqs.length }}</div>
            <div class="faq-stat-label">{{ t('faq.stat_results') || 'Resultados' }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Content -->
    <section class="faq-content fml-section">
      <div class="container">
        <!-- Loading -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-warning" role="status">
            <span class="visually-hidden">{{ t('faq.loading') }}</span>
          </div>
          <p class="mt-3 text-muted">{{ t('faq.loading') }}</p>
        </div>

        <!-- Empty -->
        <div v-else-if="faqs.length === 0 && !loading" class="faq-empty">
          <i class="bi bi-inbox"></i>
          <p>{{ t('faq.empty') }}</p>
        </div>

        <template v-else>
          <!-- Search -->
          <div class="faq-search-wrapper">
            <div class="faq-search">
              <i class="bi bi-search"></i>
              <input v-model="search" type="text" :placeholder="t('faq.search_placeholder')" />
              <button v-if="search" class="faq-search-clear" @click="search = ''">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
          </div>

          <!-- Categories -->
          <div v-if="categories.length > 1" class="faq-categories">
            <button
              v-for="cat in categories"
              :key="cat"
              class="faq-cat-btn"
              :class="{ active: activeCategory === cat }"
              @click="activeCategory = cat"
            >
              <i :class="getCategoryIcon(cat)"></i>
              {{ cat || t('faq.category_all') }}
            </button>
          </div>

          <!-- FAQ List -->
          <div class="faq-list">
            <div
              v-for="(faq, index) in filteredFaqs"
              :key="faq.id"
              class="faq-item"
              :class="{ open: openId === faq.id }"
              :style="{ animationDelay: `${index * 0.05}s` }"
            >
              <button class="faq-question" @click="toggleFaq(faq.id)">
                <div class="faq-question-left">
                  <span class="faq-num">{{ String(index + 1).padStart(2, '0') }}</span>
                  <span>{{ faq.question }}</span>
                </div>
                <div class="faq-question-right">
                  <span v-if="faq.category" class="faq-badge">{{ faq.category }}</span>
                  <i class="bi" :class="openId === faq.id ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </div>
              </button>
              <div v-if="openId === faq.id" class="faq-answer">
                <p>{{ faq.answer }}</p>
              </div>
            </div>
          </div>

          <!-- No results -->
          <div v-if="filteredFaqs.length === 0 && search" class="faq-no-results">
            <i class="bi bi-search"></i>
            <h5>{{ t('faq.no_results') || 'Nenhum resultado encontrado' }}</h5>
            <p>{{ t('faq.no_results_text') || 'Tente pesquisar com outras palavras-chave.' }}</p>
          </div>
        </template>
      </div>
    </section>

    <!-- CTA -->
    <section class="faq-cta fml-section-gold">
      <div class="container text-center">
        <h2 class="display-5 fw-bold mb-3">{{ t('faq.cta_title') || 'Ainda tem dúvidas?' }}</h2>
        <p class="lead mb-4">{{ t('faq.cta_subtitle') || 'A nossa equipa está pronta para ajudar.' }}</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <router-link to="/contacto" class="btn btn-dark btn-lg">
            <i class="bi bi-envelope me-2"></i> {{ t('faq.cta_contact') || 'Contactar' }}
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
import { ref, computed, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { useI18n } from '@/composables/useI18n'
import { useSiteImages } from '@/composables/useSiteImages'

const { t } = useI18n()
const { getImage, fetchAll } = useSiteImages()
const heroBg = ref('/assets/img/construcao2020/image3.jpeg')

const faqs = ref([])
const loading = ref(true)
const search = ref('')
const activeCategory = ref('')
const openId = ref(null)

const categories = computed(() => {
  const cats = [...new Set(faqs.value.map(f => f.category || ''))]
  return ['', ...cats.filter(Boolean)]
})

const filteredFaqs = computed(() => {
  let list = faqs.value
  if (activeCategory.value) {
    list = list.filter(f => (f.category || '') === activeCategory.value)
  }
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter(f =>
      (f.question || '').toLowerCase().includes(q) ||
      (f.answer || '').toLowerCase().includes(q)
    )
  }
  return list
})

const getCategoryIcon = (cat) => {
  if (!cat) return 'bi bi-grid'
  const icons = {
    'Geral': 'bi bi-info-circle',
    'Serviços': 'bi bi-box-seam',
    'Pagamento': 'bi bi-credit-card',
    'Entrega': 'bi bi-truck',
    'Rastreamento': 'bi bi-geo-alt',
    'Documentos': 'bi bi-file-text',
    'Account': 'bi bi-person',
  }
  return icons[cat] || 'bi bi-question-circle'
}

const toggleFaq = (id) => {
  openId.value = openId.value === id ? null : id
}

onMounted(async () => {
  await fetchAll()
  heroBg.value = getImage('faq', 'hero_bg', '/assets/img/construcao2020/image3.jpeg')
  try {
    const { data, error } = await supabase
      .from('faqs')
      .select('*')
      .order('order_by')
    if (!error) {
      faqs.value = (data || []).filter(f => f.status === 'published' || f.status === 1)
    }
  } catch (e) {
    faqs.value = []
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* HERO */
.faq-hero {
  position: relative;
  height: 45vh;
  min-height: 340px;
  display: flex;
  align-items: center;
  overflow: hidden;
  background: var(--fml-navy, #0f172a);
}
.faq-hero-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
}
.faq-hero-content {
  position: relative;
  color: #fff;
  max-width: 700px;
}
.faq-hero-title {
  font-size: 3.2rem;
  font-weight: 800;
  color: #fff;
  margin-bottom: 1rem;
  letter-spacing: -0.03em;
}
.faq-hero-subtitle {
  font-size: 1.15rem;
  line-height: 1.7;
  opacity: 0.9;
}

/* STATS */
.faq-stats {
  background: var(--fml-navy, #0f172a);
  padding: 3rem 0;
  margin-top: -1px;
}
.faq-stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}
.faq-stat { text-align: center; }
.faq-stat-icon {
  font-size: 1.5rem;
  color: var(--fml-gold, #f59e0b);
  margin-bottom: 0.5rem;
}
.faq-stat-value {
  font-size: 2rem;
  font-weight: 800;
  color: #fff;
  line-height: 1;
}
.faq-stat-label {
  font-size: 0.78rem;
  color: rgba(255,255,255,1);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-top: 0.35rem;
}

/* SEARCH */
.faq-search-wrapper {
  max-width: 640px;
  margin: 0 auto 2rem;
}
.faq-search {
  position: relative;
}
.faq-search i.bi-search {
  position: absolute;
  left: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 1.1rem;
}
.faq-search input {
  width: 100%;
  padding: 1rem 3rem 1rem 3.25rem;
  border: 2px solid #e2e8f0;
  border-radius: 16px;
  font-size: 1rem;
  background: #fff;
  outline: none;
  transition: all 0.25s ease;
}
.faq-search input:focus {
  border-color: var(--fml-gold, #f59e0b);
  box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
}
.faq-search-clear {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: #f1f5f9;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  font-size: 0.75rem;
  transition: all 0.2s;
}
.faq-search-clear:hover {
  background: #e2e8f0;
  color: #0f172a;
}

/* CATEGORIES */
.faq-categories {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  justify-content: center;
  margin-bottom: 2.5rem;
}
.faq-cat-btn {
  padding: 0.6rem 1.25rem;
  border: 2px solid #e2e8f0;
  border-radius: 50px;
  background: #fff;
  font-size: 0.9rem;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 0.25s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.faq-cat-btn:hover {
  border-color: var(--fml-gold, #f59e0b);
  color: var(--fml-gold, #f59e0b);
}
.faq-cat-btn.active {
  background: var(--fml-gold, #f59e0b);
  border-color: var(--fml-gold, #f59e0b);
  color: var(--fml-900, #1e293b);
}

/* FAQ LIST */
.faq-list {
  max-width: 850px;
  margin: 0 auto;
}

.faq-item {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  margin-bottom: 0.75rem;
  overflow: hidden;
  transition: all 0.3s ease;
  animation: fadeInUp 0.5s ease forwards;
  opacity: 0;
}
.faq-item:hover {
  border-color: #d1d5db;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}
.faq-item.open {
  border-color: var(--fml-gold, #f59e0b);
  box-shadow: 0 4px 20px rgba(245, 158, 11, 0.12);
}

.faq-question {
  width: 100%;
  padding: 1.25rem 1.5rem;
  border: none;
  background: none;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  text-align: left;
  gap: 1rem;
}
.faq-question-left {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}
.faq-num {
  font-size: 0.75rem;
  font-weight: 700;
  color: #94a3b8;
  min-width: 28px;
}
.faq-question-left span:last-child {
  font-size: 1rem;
  font-weight: 600;
  color: #0f172a;
}
.faq-question-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-shrink: 0;
}
.faq-badge {
  background: #f1f5f9;
  color: #64748b;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.faq-item.open .faq-badge {
  background: rgba(245, 158, 11, 0.15);
  color: #b45309;
}
.faq-question i {
  color: var(--fml-gold, #f59e0b);
  font-size: 1rem;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.faq-answer {
  padding: 0 1.5rem 1.5rem 3.5rem;
  color: #475569;
  font-size: 0.95rem;
  line-height: 1.7;
  animation: slideDown 0.3s ease;
}
.faq-answer p { margin: 0; }

/* EMPTY / NO RESULTS */
.faq-empty, .faq-no-results {
  text-align: center;
  padding: 4rem 2rem;
  color: #94a3b8;
}
.faq-empty i, .faq-no-results i {
  font-size: 3rem;
  margin-bottom: 1rem;
  display: block;
}
.faq-no-results h5 {
  color: #475569;
  font-weight: 600;
}
.faq-no-results p {
  font-size: 0.9rem;
}

/* ANIMATIONS */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* CTA */
.faq-cta {
  background: linear-gradient(135deg, var(--fml-gold, #f59e0b), #d97706);
  color: var(--fml-900, #1e293b);
  padding: 4.5rem 0;
}

/* RESPONSIVE */
@media (max-width: 767.98px) {
  .faq-hero { height: 35vh; min-height: 280px; }
  .faq-hero-title { font-size: 2rem; }
  .faq-stats-grid { grid-template-columns: repeat(3, 1fr); gap: 1rem; }
  .faq-stat-value { font-size: 1.5rem; }
  .faq-question { padding: 1rem 1.25rem; }
  .faq-question-left span:last-child { font-size: 0.9rem; }
  .faq-answer { padding: 0 1.25rem 1.25rem 2.5rem; font-size: 0.9rem; }
  .faq-badge { display: none; }
}
</style>
