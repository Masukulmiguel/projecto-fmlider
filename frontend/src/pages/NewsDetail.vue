<template>
  <div class="news-detail-page">
    <!-- Hero -->
    <section class="nd-hero" :style="{ backgroundImage: `linear-gradient(135deg, rgba(15,23,42,0.88) 0%, rgba(30,58,138,0.78) 50%, rgba(15,23,42,0.88) 100%), url(${article.image})` }">
      <div class="container position-relative">
        <router-link to="/noticias" class="nd-back">
          <i class="bi bi-arrow-left"></i> {{ t('news.back') }}
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
              <div class="nd-article-body" v-html="sanitize(article.content)"></div>
            </article>

            <!-- Share -->
            <div class="nd-share">
              <h6><i class="bi bi-share"></i> {{ t('news.share') }}</h6>
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
                <h5><i class="bi bi-clock-history"></i> {{ t('news.recent_news') }}</h5>
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
                <h5>{{ t('news.contact_title') }}</h5>
                <p>{{ t('news.contact_text') }}</p>
                <router-link to="/contacto" class="btn btn-light btn-sm w-100">{{ t('news.contact_button') }}</router-link>
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
import { useI18n } from '@/composables/useI18n'
import { sanitize } from '@/utils/sanitize'

const { t, locale } = useI18n()
const route = useRoute()
const router = useRouter()
const article = ref({})
const allNews = ref([])

const fallbackNews = computed(() => [
  {
    title: t('news.fallback_title_1'),
    slug: 'investimento-reachstacker',
    image: '/assets/img/resachstacker/resachstacker2.jpeg',
    date: '2024-11-15',
    category: t('news.fallback_category_1'),
    content: `
      <p class="nd-lead">${t('news.fallback_lead_1')}</p>
      <h3>${t('news.fallback_h3_1a')}</h3>
      <p>${t('news.fallback_p_1a')}</p>
      <p>${t('news.fallback_p_1b')}</p>
      <h3>${t('news.fallback_h3_1b')}</h3>
      <ul>
        <li>${t('news.fallback_li_1a')}</li>
        <li>${t('news.fallback_li_1b')}</li>
        <li>${t('news.fallback_li_1c')}</li>
        <li>${t('news.fallback_li_1d')}</li>
        <li>${t('news.fallback_li_1e')}</li>
      </ul>
      <h3>${t('news.fallback_h3_1c')}</h3>
      <p>${t('news.fallback_p_1c')}</p>
    `,
  },
  {
    title: t('news.fallback_title_2'),
    slug: 'expansao-sadc',
    image: '/assets/img/construcao2020/image3.jpeg',
    date: '2024-10-20',
    category: t('news.fallback_category_2'),
    content: `
      <p class="nd-lead">${t('news.fallback_lead_2')}</p>
      <h3>${t('news.fallback_h3_2a')}</h3>
      <p>${t('news.fallback_p_2a')}</p>
      <p>${t('news.fallback_p_2b')}</p>
      <h3>${t('news.fallback_h3_2b')}</h3>
      <ul>
        <li>${t('news.fallback_li_2a')}</li>
        <li>${t('news.fallback_li_2b')}</li>
        <li>${t('news.fallback_li_2c')}</li>
        <li>${t('news.fallback_li_2d')}</li>
        <li>${t('news.fallback_li_2e')}</li>
        <li>${t('news.fallback_li_2f')}</li>
        <li>${t('news.fallback_li_2g')}</li>
      </ul>
    `,
  },
  {
    title: t('news.fallback_title_3'),
    slug: 'novo-armazem-viana',
    image: '/assets/img/servico/service-storage.jpg',
    date: '2024-09-10',
    category: t('news.fallback_category_3'),
    content: `
      <p class="nd-lead">${t('news.fallback_lead_3')}</p>
      <h3>${t('news.fallback_h3_3a')}</h3>
      <p>${t('news.fallback_p_3a')}</p>
      <h3>${t('news.fallback_h3_3b')}</h3>
      <ul>
        <li>${t('news.fallback_li_3a')}</li>
        <li>${t('news.fallback_li_3b')}</li>
        <li>${t('news.fallback_li_3c')}</li>
        <li>${t('news.fallback_li_3d')}</li>
        <li>${t('news.fallback_li_3e')}</li>
      </ul>
    `,
  },
  {
    title: t('news.fallback_title_4'),
    slug: 'parceria-maritima',
    image: '/assets/img/servico/Logística Marítima-1.jpg',
    date: '2024-08-05',
    category: t('news.fallback_category_4'),
    content: `
      <p class="nd-lead">${t('news.fallback_lead_4')}</p>
      <h3>${t('news.fallback_h3_4a')}</h3>
      <p>${t('news.fallback_p_4a')}</p>
    `,
  },
  {
    title: t('news.fallback_title_5'),
    slug: 'certificacao-iso',
    image: '/assets/img/pessoal/partner2.png',
    date: '2024-07-01',
    category: t('news.fallback_category_5'),
    content: `
      <p class="nd-lead">${t('news.fallback_lead_5')}</p>
      <h3>${t('news.fallback_h3_5a')}</h3>
      <p>${t('news.fallback_p_5a')}</p>
    `,
  },
  {
    title: t('news.fallback_title_6'),
    slug: 'campanha-seguranca',
    image: '/assets/img/construcao2020/image5.jpeg',
    date: '2024-06-15',
    category: t('news.fallback_category_6'),
    content: `
      <p class="nd-lead">${t('news.fallback_lead_6')}</p>
      <h3>${t('news.fallback_h3_6a')}</h3>
      <p>${t('news.fallback_p_6a')}</p>
    `,
  },
])

const dateLocale = computed(() => locale.value === 'pt' ? 'pt-PT' : locale.value === 'en' ? 'en-GB' : 'fr-FR')

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString(dateLocale.value, { year: 'numeric', month: 'long', day: 'numeric' })
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
        image: n.image ? (n.image.startsWith('http') ? n.image : (n.image.startsWith('/') ? n.image : '/assets/img/' + n.image)) : '/assets/img/construcao2020/image1.jpeg',
        date: n.published_at || n.created_at,
        category: n.category || 'Geral',
        content: n.content || `<p>${t('news.default_content')}</p>`,
      }))
    } else {
      allNews.value = fallbackNews.value
    }
  } catch {
    allNews.value = fallbackNews.value
  }
  const found = allNews.value.find(n => n.slug === slug)
  article.value = found || fallbackNews.value[0]
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
