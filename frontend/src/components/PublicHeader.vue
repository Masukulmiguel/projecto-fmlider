<template>
  <header class="fml-header" :class="{ scrolled: isScrolled }">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between">
        <router-link to="/" class="brand">
          <img src="/assets/img/logo.png" alt="FMLider" class="brand-logo">
          <div class="brand-text">
            <span class="brand-name">FMLider</span>
            <span class="brand-tagline">Logística & Transitário</span>
          </div>
        </router-link>

        <button class="mobile-toggle" @click="mobileOpen = !mobileOpen" :aria-expanded="mobileOpen">
          <i :class="mobileOpen ? 'bi bi-x-lg' : 'bi bi-list'"></i>
        </button>

        <nav class="main-nav" :class="{ open: mobileOpen }">
          <router-link to="/" class="nav-link" @click="mobileOpen = false">Início</router-link>
          <router-link to="/sobre" class="nav-link" @click="mobileOpen = false">Sobre</router-link>
          <router-link to="/servicos" class="nav-link" @click="mobileOpen = false">Serviços</router-link>
          <router-link to="/frota" class="nav-link" @click="mobileOpen = false">Frota</router-link>
          <router-link to="/noticias" class="nav-link" @click="mobileOpen = false">Notícias</router-link>
          <router-link to="/galeria" class="nav-link" @click="mobileOpen = false">Galeria</router-link>
          <router-link to="/contacto" class="nav-link" @click="mobileOpen = false">Contacto</router-link>
          <router-link to="/login" class="nav-cta" @click="mobileOpen = false">
            <i class="bi bi-person-circle me-1"></i> Entrar
          </router-link>
        </nav>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
const isScrolled = ref(false)
const mobileOpen = ref(false)
const onScroll = () => { isScrolled.value = window.scrollY > 20 }
onMounted(() => window.addEventListener('scroll', onScroll))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
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
.fml-header > .container { width: 100%; }

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
}

@media (max-width: 991px) {
  .mobile-toggle { display: flex; align-items: center; }
  .main-nav {
    position: fixed;
    top: var(--fml-header-h);
    right: 0;
    bottom: 0;
    width: 320px;
    max-width: 85vw;
    background: #fff;
    flex-direction: column;
    align-items: stretch;
    padding: 1.5rem;
    gap: 0.25rem;
    box-shadow: -10px 0 30px rgba(0, 0, 0, 0.1);
    transform: translateX(100%);
    transition: transform 0.3s var(--fml-ease);
  }
  .main-nav.open { transform: translateX(0); }
  .nav-link { padding: 0.85rem 1rem; font-size: 1rem; }
  .nav-link.router-link-active::after { display: none; }
  .nav-link.router-link-active {
    background: var(--fml-50);
    color: var(--fml-blue-2) !important;
  }
  .nav-cta { margin: 1rem 0 0; text-align: center; }
  .brand-tagline { display: none; }
}
</style>
