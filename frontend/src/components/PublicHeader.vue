<template>
  <header class="fml-header" :class="{ scrolled: isScrolled }">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between">
        <router-link to="/" class="brand">
          <img :src="logoUrl" alt="FMLider" class="brand-logo">
          <div class="brand-text">
            <span class="brand-name">FMLider</span>
            <span class="brand-tagline">Logística & Transitário</span>
          </div>
        </router-link>

        <button class="mobile-toggle" @click="mobileOpen = !mobileOpen" :aria-expanded="mobileOpen">
          <i :class="mobileOpen ? 'bi bi-x-lg' : 'bi bi-list'"></i>
        </button>

        <nav class="main-nav">
          <router-link to="/" class="nav-link" @click="closeMobile">Início</router-link>
          <router-link to="/sobre" class="nav-link" @click="closeMobile">Sobre</router-link>
          <router-link to="/servicos" class="nav-link" @click="closeMobile">Serviços</router-link>
          <router-link to="/frota" class="nav-link" @click="closeMobile">Frota</router-link>
          <router-link to="/noticias" class="nav-link" @click="closeMobile">Notícias</router-link>
          <router-link to="/galeria" class="nav-link" @click="closeMobile">Galeria</router-link>
          <router-link to="/faq" class="nav-link" @click="closeMobile">FAQ</router-link>
          <router-link to="/contacto" class="nav-link" @click="closeMobile">Contacto</router-link>
          <router-link to="/login" class="nav-cta" @click="closeMobile">
            <i class="bi bi-person-circle me-1"></i> Entrar
          </router-link>
        </nav>
      </div>
    </div>
  </header>

  <Teleport to="body">
    <div v-if="mobileOpen" class="mobile-nav-overlay" @click="closeMobile"></div>
    <div class="mobile-nav-panel" :class="{ open: mobileOpen }">
      <div class="mobile-nav-header">
        <span class="mobile-nav-title">Menu</span>
        <button class="mobile-nav-close" @click="closeMobile" aria-label="Fechar menu">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <nav class="mobile-nav-links">
        <router-link to="/" class="mobile-nav-link" @click="closeMobile"><i class="bi bi-house-door"></i> Início</router-link>
        <router-link to="/sobre" class="mobile-nav-link" @click="closeMobile"><i class="bi bi-info-circle"></i> Sobre</router-link>
        <router-link to="/servicos" class="mobile-nav-link" @click="closeMobile"><i class="bi bi-gear"></i> Serviços</router-link>
        <router-link to="/frota" class="mobile-nav-link" @click="closeMobile"><i class="bi bi-truck"></i> Frota</router-link>
        <router-link to="/noticias" class="mobile-nav-link" @click="closeMobile"><i class="bi bi-newspaper"></i> Notícias</router-link>
        <router-link to="/galeria" class="mobile-nav-link" @click="closeMobile"><i class="bi bi-images"></i> Galeria</router-link>
        <router-link to="/faq" class="mobile-nav-link" @click="closeMobile"><i class="bi bi-question-circle"></i> FAQ</router-link>
        <router-link to="/contacto" class="mobile-nav-link" @click="closeMobile"><i class="bi bi-envelope"></i> Contacto</router-link>
        <router-link to="/login" class="mobile-nav-cta" @click="closeMobile">
          <i class="bi bi-person-circle me-1"></i> Entrar
        </router-link>
      </nav>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useSiteImages } from '@/composables/useSiteImages'

const { getImage, fetchAll } = useSiteImages()
const logoUrl = ref('/assets/img/logo.png')

const isScrolled = ref(false)
const mobileOpen = ref(false)
const onScroll = () => { isScrolled.value = window.scrollY > 20 }

const closeMobile = () => {
  mobileOpen.value = false
}

watch(mobileOpen, (open) => {
  document.body.style.overflow = open ? 'hidden' : ''
})

onMounted(async () => {
  window.addEventListener('scroll', onScroll)
  await fetchAll()
  logoUrl.value = getImage('header', 'logo', '/assets/img/logo.png')
})
onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.fml-header {
  position: sticky;
  top: 0;
  z-index: 1030;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid transparent;
  transition: all 0.3s var(--fml-ease);
  height: var(--fml-header-h);
  display: flex;
  align-items: center;
}
.fml-header.scrolled {
  border-bottom-color: var(--fml-200);
  box-shadow: 0 2px 16px rgba(15, 23, 42, 0.04);
}
.fml-header > .container { width: 100%; padding-left: 1rem; padding-right: 1rem; }

.brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  color: var(--fml-900);
}
.brand-logo { height: 42px; width: auto; }
.brand-text { display: flex; flex-direction: column; line-height: 1.1; }
.brand-name {
  font-family: var(--fml-font-display);
  font-weight: 800;
  font-size: 1.35rem;
  color: var(--fml-navy);
  letter-spacing: -0.02em;
}
.brand-tagline {
  font-size: 0.7rem;
  color: var(--fml-gold);
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-top: 2px;
}

.main-nav {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.nav-link {
  position: relative;
  padding: 0.5rem 0.9rem;
  color: var(--fml-700) !important;
  font-weight: 500;
  font-size: 0.95rem;
  border-radius: var(--fml-radius-sm);
  transition: all 0.2s var(--fml-ease);
}
.nav-link:hover {
  color: var(--fml-blue-2) !important;
  background: var(--fml-50);
}
.nav-link.router-link-active {
  color: var(--fml-blue-2) !important;
  font-weight: 600;
}
.nav-link.router-link-active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 20px;
  height: 3px;
  background: var(--fml-gold);
  border-radius: 2px;
}

.nav-cta {
  margin-left: 0.5rem;
  padding: 0.55rem 1.25rem;
  background: var(--fml-blue-2);
  color: #fff !important;
  border-radius: var(--fml-radius-pill);
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.2s var(--fml-ease);
}
.nav-cta:hover {
  background: var(--fml-blue);
  color: #fff !important;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
}

.mobile-toggle {
  display: none;
  background: transparent;
  border: none;
  font-size: 1.5rem;
  color: var(--fml-900);
  padding: 0.5rem;
  cursor: pointer;
  z-index: 1040;
  position: relative;
}

@media (max-width: 991px) {
  .mobile-toggle { display: flex; align-items: center; }
  .main-nav { display: none; }
  .brand-tagline { display: none; }
}
@media (max-width: 480px) {
  .brand-logo { height: 34px; }
  .brand-name { font-size: 1.1rem; }
  .brand { gap: 0.5rem; }
}
</style>

<style>
.mobile-nav-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.6);
  z-index: 1040;
  animation: fadeIn 0.3s ease;
}
.mobile-nav-panel {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100vw;
  height: 100vh;
  max-width: none;
  background: #fff;
  z-index: 1050;
  transform: translateX(100%);
  transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  box-shadow: none;
}
.mobile-nav-panel.open {
  transform: translateX(0);
}
.mobile-nav-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e9ecef;
  min-height: 60px;
  background: #fff;
  flex-shrink: 0;
}
.mobile-nav-title {
  font-family: var(--fml-font-display);
  font-weight: 700;
  font-size: 1.15rem;
  color: var(--fml-navy, #0f172a);
}
.mobile-nav-close {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #64748b;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 6px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
}
.mobile-nav-close:hover {
  background: #f1f5f9;
  color: #0f172a;
}
.mobile-nav-links {
  display: flex;
  flex-direction: column;
  padding: 1rem 1rem;
  gap: 4px;
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  background: #fff;
}
.mobile-nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  color: #1e293b !important;
  font-weight: 500;
  font-size: 1.1rem;
  border-radius: 12px;
  transition: all 0.2s;
  text-decoration: none;
}
.mobile-nav-link i {
  font-size: 1.1rem;
  width: 24px;
  text-align: center;
  color: #64748b;
}
.mobile-nav-link:hover {
  background: #f1f5f9;
  color: var(--fml-navy, #0f172a) !important;
}
.mobile-nav-link.router-link-exact-active {
  background: var(--fml-50, #eff6ff);
  color: var(--fml-blue-2, #2563eb) !important;
  font-weight: 600;
}
.mobile-nav-link.router-link-exact-active i {
  color: var(--fml-blue-2, #2563eb);
}
.mobile-nav-cta {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
  padding: 0.85rem 1.25rem;
  background: var(--fml-blue-2, #2563eb);
  color: #fff !important;
  border-radius: 10px;
  font-weight: 600;
  font-size: 1rem;
  text-decoration: none;
  transition: all 0.2s;
}
.mobile-nav-cta:hover {
  background: var(--fml-blue, #1d4ed8);
  color: #fff !important;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
