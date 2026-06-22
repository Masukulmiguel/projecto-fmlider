<template>
  <div class="legal-page">
    <div class="legal-hero">
      <div class="container">
        <h1>{{ t('legal.privacy_title') }}</h1>
        <p>{{ t('legal.privacy_subtitle') }}: {{ today }}</p>
      </div>
    </div>

    <div class="container py-5">
      <div class="row">
        <aside class="col-lg-3 d-none d-lg-block">
          <nav class="legal-toc sticky-top">
            <h6 class="text-uppercase text-muted small fw-bold">{{ t('legal.privacy_toc') }}</h6>
            <ul class="list-unstyled">
              <li v-for="(s, i) in sections" :key="i"><a :href="`#p${i+1}`">{{ i+1 }}. {{ s.title }}</a></li>
            </ul>
          </nav>
        </aside>
        <div class="col-lg-9">
          <section v-for="(s, i) in sections" :key="i" :id="`p${i+1}`" class="legal-section">
            <h2>{{ i+1 }}. {{ s.title }}</h2>
            <p v-for="(p, j) in s.body" :key="j" v-html="p"></p>
          </section>

          <div class="alert alert-info mt-4">
            <strong>{{ t('legal.privacy_dpo') }}</strong> {{ t('legal.privacy_dpo_text') }},
            contacte-nos em <a href="mailto:privacidade@fmlider.co.ao">privacidade@fmlider.co.ao</a> ou
            <a href="tel:+244935141747">+244 935 141 747</a>.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from '@/composables/useI18n'

const { t, locale } = useI18n()

const today = computed(() => new Date().toLocaleDateString(locale.value === 'pt' ? 'pt-PT' : locale.value === 'fr' ? 'fr-FR' : 'en-GB', { year: 'numeric', month: 'long', day: 'numeric' }))

const sections = computed(() => [
  { title: t('legal.privacy_s1_title'), body: [t('legal.privacy_s1_p1'), t('legal.privacy_s1_p2')] },
  { title: t('legal.privacy_s2_title'), body: [t('legal.privacy_s2_p1')] },
  { title: t('legal.privacy_s3_title'), body: [t('legal.privacy_s3_p1')] },
  { title: t('legal.privacy_s4_title'), body: [t('legal.privacy_s4_p1')] },
  { title: t('legal.privacy_s5_title'), body: [t('legal.privacy_s5_p1'), t('legal.privacy_s5_p2')] },
  { title: t('legal.privacy_s6_title'), body: [t('legal.privacy_s6_p1'), t('legal.privacy_s6_p2')] },
  { title: t('legal.privacy_s7_title'), body: [t('legal.privacy_s7_p1'), t('legal.privacy_s7_p2')] },
  { title: t('legal.privacy_s8_title'), body: [t('legal.privacy_s8_p1'), t('legal.privacy_s8_p2')] },
  { title: t('legal.privacy_s9_title'), body: [t('legal.privacy_s9_p1'), t('legal.privacy_s9_p2')] },
  { title: t('legal.privacy_s10_title'), body: [t('legal.privacy_s10_p1'), t('legal.privacy_s10_p2')] },
  { title: t('legal.privacy_s11_title'), body: [t('legal.privacy_s11_p1')] },
  { title: t('legal.privacy_s12_title'), body: [t('legal.privacy_s12_p1'), t('legal.privacy_s12_p2')] },
])
</script>

<style scoped>
.legal-hero {
  background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
  color: #fff;
  padding: 4rem 0 3rem;
  position: relative;
  overflow: hidden;
}
.legal-hero::after {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 20% 50%, rgba(251, 191, 36, 0.15) 0%, transparent 50%);
  pointer-events: none;
}
.legal-hero h1 {
  font-size: 2.5rem;
  font-weight: 800;
  margin: 0;
  position: relative;
  z-index: 1;
}
.legal-hero p {
  opacity: 0.8;
  margin: 0.5rem 0 0;
  position: relative;
  z-index: 1;
}

.legal-toc {
  background: #f8fafc;
  padding: 1.25rem;
  border-radius: 12px;
  border-left: 4px solid #f59e0b;
}
.legal-toc h6 { margin-bottom: 0.75rem; }
.legal-toc ul li { margin-bottom: 0.4rem; }
.legal-toc a {
  color: #475569;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.2s;
}
.legal-toc a:hover { color: #1e3a8a; }

.legal-section {
  margin-bottom: 2.5rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid #e2e8f0;
}
.legal-section:last-of-type { border-bottom: none; }
.legal-section h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #f59e0b;
  display: inline-block;
}
.legal-section p {
  color: #334155;
  line-height: 1.75;
  margin-bottom: 1rem;
}
.legal-section :deep(ul) {
  margin: 0.5rem 0 1rem 1.5rem;
  color: #334155;
}
.legal-section :deep(ul li) {
  margin-bottom: 0.4rem;
  line-height: 1.6;
}

@media (max-width: 768px) {
  .legal-hero h1 { font-size: 1.75rem; }
  .legal-hero { padding: 2.5rem 0 2rem; }
}
</style>
