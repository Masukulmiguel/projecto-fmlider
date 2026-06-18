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
        <p class="lead text-white-50 mx-auto" style="max-width: 650px;">Parceiros estratégicos que fortalecem a nossa rede logística e expandem o nosso alcance global.</p>
      </div>
    </section>

    <section class="fml-section">
      <div class="container">
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'

const partners = ref([])
const loading = ref(true)

const resolveLogo = (logo) => {
  if (!logo) return ''
  if (logo.startsWith('http')) return logo
  if (logo.startsWith('/')) return logo
  return `/backend/storage/uploads/partners/${logo}`
}

onMounted(async () => {
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

.partner-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.partner-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
}

.partner-logo-area {
  height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px;
  background: #f9fafb;
}

.partner-logo-img {
  max-width: 90%;
  max-height: 90%;
  object-fit: contain;
}

.partner-body {
  padding: 20px 24px 24px;
}

.partner-name {
  font-weight: 700;
  margin-bottom: 8px;
  color: #0f172a;
}

.partner-desc {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 12px;
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
</style>
