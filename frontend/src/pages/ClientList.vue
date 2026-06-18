<template>
  <div class="clients-page">
    <section class="page-hero">
      <div class="container">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb justify-content-center">
            <li class="breadcrumb-item"><router-link to="/" class="text-white-50">Início</router-link></li>
            <li class="breadcrumb-item active text-white">Clientes</li>
          </ol>
        </nav>
        <h1 class="display-4 fw-bold text-white mb-3">Nossos Clientes</h1>
        <p class="lead text-white-50 mx-auto" style="max-width: 650px;">Empresas que confiam na FMLider para gestionar as suas necessidades logísticas todos os dias.</p>
      </div>
    </section>

    <section class="fml-section">
      <div class="container">
        <div class="row g-4">
          <div class="col-md-6 col-lg-4" v-for="client in clients" :key="client.id" v-reveal="'fade'">
            <div class="client-card">
              <div class="client-logo-area">
                <img :src="resolveLogo(client.logo)" :alt="client.company_name" class="client-logo-img">
              </div>
              <div class="client-body">
                <h5 class="client-name">{{ client.company_name }}</h5>
              </div>
            </div>
          </div>
        </div>
        <div v-if="clients.length === 0 && !loading" class="text-center py-5">
          <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
          <p class="text-muted mt-3">Nenhum cliente encontrado.</p>
        </div>
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="text-muted mt-3">A carregar clientes...</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'

const clients = ref([])
const loading = ref(true)

const resolveLogo = (logo) => {
  if (!logo) return ''
  if (logo.startsWith('http')) return logo
  if (logo.startsWith('data:')) return logo
  return logo
}

onMounted(async () => {
  try {
    const { data, error } = await supabase
      .from('companies')
      .select('id, company_name, logo')
      .eq('is_published', true)
      .order('company_name')
    if (!error && data) {
      clients.value = data
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

.client-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  text-align: center;
}

.client-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
}

.client-logo-area {
  height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: #f9fafb;
}

.client-logo-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.client-body {
  padding: 16px 24px 24px;
}

.client-name {
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}
</style>
